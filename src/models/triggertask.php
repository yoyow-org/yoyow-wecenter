<?php
/*
+--------------------------------------------------------------------------
|   WeCenter [#RELEASE_VERSION#]
|   ========================================
|   by WeCenter Software
|   © 2011 - 2014 WeCenter. All Rights Reserved
|   http://www.wecenter.com
|   ========================================
|   Support: WeCenter@qq.com
|
+---------------------------------------------------------------------------
*/

if (!defined('IN_ANWSION'))
{
    die;
}

class triggertask_class extends AWS_MODEL{

    /**
     * 通过id查询
     * @param int
     * @return 返回一条数据
     */
    public function query_by_id($id){
        $where="id='".$id."'";
        return $this->fetch_row("yoyow_trigger_task",$where,null);
    }

    /**
     * 自动分币任务
     */
    public function conin_task(){
        //获取设置的分币时间
        $yoyow_trigger_task_time=get_setting('yoyow_trigger_task_time', false);
        $yoyow_trigger_task_time_array=explode(':',$yoyow_trigger_task_time);
        $now=getdate();
        //到当前分币时间，则继续执行
        if($yoyow_trigger_task_time_array[0]!=$now['hours'] || $yoyow_trigger_task_time_array[1]!=$now['minutes']){
            return;
        }
        //如果没开启自动分配，则返回
        $yoyow_trigger_task_switch=get_setting('yoyow_trigger_task_switch', false);
        if($yoyow_trigger_task_switch=='N'){
            return;
        }
        //查询yoyow账户的可分配币数
        $coin=0;
        //分配比例
        $yoyow_trigger_task_scale=get_setting('yoyow_trigger_task_scale', false);
        if($yoyow_trigger_task_scale<=0){
            return;
        }
        //查询上次分币的时间
        $act_start_time=$this->max("yoyow_trigger_task",'exec_time','');
        if(empty($act_start_time)){
            $act_start_time=0;
        }
        //构造任务数组对象
        $task=array(
            'act_start_time'=>$act_start_time,
            'act_end_time'=>time(),
            'coin'=>$coin,
            'remain'=>$coin,
            'yoyow_trigger_task_scale'=>$yoyow_trigger_task_scale
        );
        //记录计算的开始时间
        $task_start_time=time();
        //事务操作
        try{
            //开启事务
            $this->begin_transaction();
            //用户yoyow的收益计算
            $task=$this->user_yoyow_profit($task);
            //记录计算的结束时间
            $task_end_time=time();
            $this->insert('yoyow_trigger_task',array('coin'=>$coin,'remain'=>$task['remain'],'exec_time'=>$task_start_time,'used_time'=>$task_end_time-$task_start_time));
            //给每个yoyow账户分yoyow币
            //扣除yoyow平台的账户yoyow币
            //提交事务
            $this->commit();
        }catch(Exception $ex){
            //出错回滚事务
            $this->roll_back();
        }
    }

    /**
     * 用户yoyow的收益计算
     * @param $task 任务
     * @return $task 任务
     */
    private function user_yoyow_profit($task){
        //查询在积分活动范围内的所有用户总积分
        $sum_integral=$this->model('integral')->sum_integral_in_time($task['act_start_time'],$task['act_end_time']);
        //查询在积分活动范围内的所有用户积分情况
        $user_integral_list=$this->model('integral')->user_integral_in_time($task['act_start_time'],$task['act_end_time']);
        if($sum_integral<=0){
            //所有人所得积分不大于0，则退出计算
            return $task;
        }
        //循环计算所有人积分情况
        foreach ($user_integral_list AS $user_integral){
            if($user_integral['total']<=0){
                //当前人所得积分不大于0，则跳过计算
                continue;
            }
            //计算每个人的积分数量，执行数据库更新操作
            $task=$this->calc_user_coin($task,$user_integral,$sum_integral);
        }
        return $task;
    }

    /**
     * 计算手动分币每个人所得yoyow币，执行数据库更新操作
     * @param $task 分配任务
     * @param $user_integral 用户积分
     * @param $sum_integral 用户总的积分
     * @return $task 任务
     */
    private function calc_user_coin($task,$user_integral,$sum_integral){
        //用户没有绑定yoyow账户，不执行分币操作
        $is_bind_yoyow=$this->count('users_yoyow',('uid='.$user_integral['uid']));
        if($is_bind_yoyow==0){
            return $task;
        }
        //计算用户可以分多少币
        $yoyow_trigger_task_scale=$task['yoyow_trigger_task_scale'];
        $coin=(int)($task['coin']*$yoyow_trigger_task_scale/100*$user_integral['total']/$sum_integral);
        if($coin<=0){
            return $task;
        }
        //获取接口地址
        $req_api_url=get_setting('api_url', false);
        //用户的yoyow账户
        $user_yoyow_account=$this->fetch_one('users_yoyow','yoyow',('uid='.$user_integral['uid']));
        //调用接口yoyow币转账
        $send_obj = array(
            'uid' => $user_yoyow_account,
            'amount' => $coin,
            'memo' => 'memo',
            'time' => microtime(true)
        );
        $send_json=json_encode($send_obj);
        $yoyow_asc_key=get_setting('yoyow_aes_key', false);
        //请求参数
        $send_args = $this->model("AesSecurity")->encrypt($send_json,$yoyow_asc_key);
        $send_args_obj=json_decode($send_args);
        //转账请求地址
        $req_url_tran=$req_api_url.'/api/v1/transfer';
        //进行请求
        $res_tran_content=AWS_APP::http()->post($req_url_tran,
            null, json_encode(array('ct'=>$send_args_obj->ct,'iv'=>$send_args_obj->iv,'s'=>$send_args_obj->s)));
        //返回值进行解码
        $res_tran_json=json_decode($res_tran_content,true);

        if(!empty($res_tran_json) && $res_tran_json['code']==0){
            //转账成功，保存分币记录
            $task['remain']=$task['remain']-$coin;
            $remark=sprintf('在%s到%s 积分活动 获得YOYOW币%s个',date('Y-m-d',$task['act_start_time']),date('Y-m-d',$task['act_end_time']),$coin);
            $data=array(
                'uid'=>$user_integral['uid'],
                'coin'=>$coin,
                'origin'=>'TRIGGER',
                'add_time'=>time(),
                'act_strat_time'=>$task['act_start_time'],
                'act_end_time'=>$task['act_end_time'],
                'remark'=>$remark,
                //toFixed 未配置该参数
                'task_id'=>$task['id']
            );
            $this->insert('user_yoyow_coin',$data);
        }
        return $task;
    }

}