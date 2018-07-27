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

class assigntask_class extends AWS_MODEL{

    /**
     * 通过id查询
     * @param int
     * @return 返回一条数据
     */
    public function query_by_id($id){
        $where="id='".$id."'";
        return $this->fetch_row("yoyow_assign_task",$where,null);
    }

    /**
     * 手动分币任务
     */
    public function conin_task($ids){
        //执行分配失败的重新分配
        //$this->execute_failed_distribute();
        if($ids!="auto"){
            $ids = implode(",",explode("_",$ids));
            $task_where=' status=0 AND id IN ('.$ids.') AND  exec_time < unix_timestamp(now()) ';
        }else{
            $task_where=' status=0 AND exec_time < unix_timestamp(now()) ';
        }

        //如果没开启手动分配，则返回
        $yoyow_assign_task_switch=get_setting('yoyow_assign_task_switch', false);
        if($yoyow_assign_task_switch=='N'){
            HTTP::redirect('/admin/user/assign_task_list/');
            return;
        }
        //查询手动分币任务的条件
       // $task_where = ' id IN ('.$ids.')';

        //分币任务列表
        $task_list=$this->fetch_all("yoyow_assign_task",$task_where);
        //循环分币任务
        if(sizeof($task_list) == 0) {

            HTTP::redirect('/admin/user/assign_task_list/');
            return false;
        }
        foreach($task_list AS $task_value){
            //过了时间，执行分币
            $upd_task_where=' status=0 AND id='.$task_value['id'];
            if($this->update('yoyow_assign_task',array('status'=>'1'),$upd_task_where)==0){
                //任务已经执行，无法更新任务状态，则跳过
                continue;
            }
            //记录计算的开始时间
            $task_start_time=time();
            //事务操作
            try{
                //开启事务
                $this->begin_transaction();
                //用户yoyow的收益计算
                $task_value=$this->user_yoyow_profit($task_value);
                //记录计算的结束时间
                $task_end_time=time();
                //更新任务
                $upd_task_where=' status=1 AND id='.$task_value['id'];
                $this->update('yoyow_assign_task',array('status'=>'2','remain'=>$task_value['remain'],'used_time'=>$task_end_time-$task_start_time),$upd_task_where);
                //提交事务
                $this->commit();
                HTTP::redirect('/admin/user/assign_task_list/');
            }catch(Exception $ex){
                //出错回滚事务
                $this->roll_back();
                //记录计算的结束时间
                $task_end_time=time();
                //任务更新成失败状态
                $this->update('yoyow_assign_task',array('status'=>'3','used_time'=>$task_end_time-$task_start_time),$upd_task_where);
                HTTP::redirect('/admin/user/assign_task_list/');
            }
        }
        HTTP::redirect('/admin/user/assign_task_list/');
    }

    /**
     * 用户yoyow的收益计算
     * @param $task_value 任务
     * @return $task_value 任务
     */
    private function user_yoyow_profit($task_value){
        //查询在积分活动范围内的所有用户积分情况
        $user_integral_list=$this->model('integral')->user_integral_in_time($task_value['act_start_time'],$task_value['act_end_time']);
        $successNum=0;
        $failNum=0;
        $noAccountNum=0;
        $noIntegralNum=0;
        $sum_integral = 0;
        //循环计算所有人积分情况
        foreach ($user_integral_list AS $user_integral) {
            if($user_integral['total']<=0){
                continue;
            }else {
                $sum_integral  = $sum_integral + $user_integral['total'];
            }
        }
        if($sum_integral<=0){
            //所有人所得积分不大于0，则退出计算
            return $task_value;
        }
        foreach ($user_integral_list AS $user_integral){
            if($user_integral['total']<=0){
                //当前人所得积分不大于0，则跳过计算
                continue;
            }
            //计算每个人的积分数量，执行数据库更新操作
            $task_value=$this->calc_user_coin($task_value,$user_integral,$sum_integral);
            //计算分币统计数据
            switch ($task_value['distribute_result']) {
                case 'noAccount':
                    $noAccountNum++;
                    break;
                case 'noIntegral':
                    $noIntegralNum++;
                    break;
                case 'success':
                    $successNum++;
                    break;
                case 'fail':
                    $failNum++;
                    break;
                default:
                    null;
            }
        }
        //将结果数据插入任务统计表中
        $this->insert('task_statistics',
            array(
                'task_id'=>$task_value['id'],
                'success_num'=>$successNum,
                'fail_num'=>$failNum,
                'no_account'=>$noAccountNum,
                'no_integral'=>$noIntegralNum
            ));
        return $task_value;
    }

    /**
     * 计算手动分币每个人所得yoyow币，执行数据库更新操作
     * @param $task_value 分配任务
     * @param $user_integral 用户积分
     * @param $sum_integral 用户总的积分
     * @return $task_value 任务
     */
    private function calc_user_coin($task_value,$user_integral,$sum_integral){
        //计算用户可以分多少币
        $coin=substr(sprintf( '%.6f' , ($user_integral['total']/$sum_integral*$task_value['coin'])), 0, -2);
        if($coin<=0){
            $distribute_result = 'noIntegral';
            return array_merge($task_value, array(distribute_result => $distribute_result));
        }
        $is_bind_yoyow=$this->count('users_yoyow',('uid='.$user_integral['uid']));
        if($is_bind_yoyow==0){
            $distributeResult = 2;
            $distribute_result = 'noAccount';
        } else  {
            //获取接口地址
            $req_api_url=get_setting('api_url', false);
            //用户的yoyow账户
            $user_yoyow_account=$this->fetch_one('users_yoyow','yoyow',('uid='.$user_integral['uid']));
            //调用接口yoyow币转账
            $send_obj = array(
                'uid' => $user_yoyow_account,
                'amount' => $coin,
                'memo' => 'memo',
                'time' => microtime(true) * 1000
            );
            $send_json=json_encode($send_obj);
            $yoyow_asc_key=get_setting('yoyow_aes_key', false);
            //请求参数
            $send_args = $this->model("AesSecurity")->encrypt($send_json,$yoyow_asc_key);
            $send_args_obj=json_decode($send_args);
            //转账请求地址
            $req_url_tran=$req_api_url.'/api/v1/transfer';
            //进行请求
            try{
                $res_tran_content=AWS_APP::http()->post($req_url_tran,
                    null, json_encode(array('ct'=>$send_args_obj->ct,'iv'=>$send_args_obj->iv,'s'=>$send_args_obj->s)));
            }catch (Exception $e) {
                H::redirect_msg(AWS_APP::lang()->_t('分币失败～，调用中间件转账接口超时，请检查中间件配置'), '/admin/user/assign_task_list/');
            }
            //返回值进行解码
            $res_tran_json=json_decode($res_tran_content,true);
            if(!empty($res_tran_json) && $res_tran_json['code']==0){
                $distributeResult = 1;
                $distribute_result = 'success';
            }else {
                $distributeResult = 0;
                $distribute_result = 'fail';
            }
        }

        //保存分币记录
        $task_value['remain']=$task_value['remain']-$coin;
        $remark=sprintf('在%s到%s 积分活动 获得YOYOW币%s个',date('Y-m-d',$task_value['act_start_time']),date('Y-m-d',$task_value['act_end_time']),$coin);
        $data=array(
            'uid'=>$user_integral['uid'],
            'coin'=>$coin,
            'origin'=>'ASSIGN',
            'add_time'=>time(),
            'act_strat_time'=>$task_value['act_start_time'],
            'act_end_time'=>$task_value['act_end_time'],
            'remark'=>$remark,
            'task_id'=>$task_value['id'],
            'distribute_result'=>$distributeResult,
            'inteface_message'=> ($res_tran_json ? implode('', $res_tran_json) : '未提现')
        );
        //将用户积分和yoyow记录插入积分yoyow关联表中
        $coin_id = $this->insert('user_yoyow_coin',$data);
        //计算用户每条积分明细yoyow数据
        $this->add_integral_yoyow_detail($data, $coin_id, $user_integral['total']);
        //计算提成数据
        $this->calculate_commission($data['uid'], $data['coin'], $task_value['id']);
        //分币后，将分币结果推送给用户（失败、成功、和未提现均通知）
        $this->model('notify')->send(
            null,
            $user_integral['uid'],
            notify_class::TYPE_COIN_DISTRIBUTE,
            notify_class::YOYOW_COIN,
            $coin_id,
            array('coin' => $coin)
        );
        return array_merge($task_value, array(distribute_result => $distribute_result));
    }

    /**
     * 对于失败的分配币，定时任务定时去跑下
     */
    public function execute_failed_distribute() {//, " distribute_result = 0 "
        $fail_list = $this->model('yoyowcoin')->fetch_all("user_yoyow_coin", "distribute_result=0 ");
        if(!sizeof($fail_list)){return;}
        foreach($fail_list AS $single){
            $user_yoyow_account=$this->fetch_one('users_yoyow','yoyow',('uid='.$single['uid']));
            if($user_yoyow_account) {$this -> execute_single($user_yoyow_account, $single);}
        }
    }

    /**
     * @param $user_yoyow_account
     * @param $coin执行单个记录
     */
    private function execute_single ($user_yoyow_account, $single) {
        $start_time = time();
        //获取接口地址
        $req_api_url=get_setting('api_url', false);
        //调用接口yoyow币转账
        $send_obj = array(
            'uid' => $user_yoyow_account,
            'amount' => $single['coin'],
            'memo' => 'memo',
            'time' => microtime(true) * 1000
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
        //调用接口成功后，更新分币记录表和统计表
        if($res_tran_json['code'] == 0) {
            //更新分币记录表数据
            $update_data=array(
                'act_strat_time'=>$start_time,
                'act_end_time'=>time(),
                'distribute_result'=> 1
            );
            $this->update('user_yoyow_coin', $update_data, 'id=' . $single['id']);
            //更新分币统计表数据
            $success_num = $this->fetch_one('task_statistics','success_num',('task_id='.$single['task_id']));
            $fail_num = $this->fetch_one('task_statistics','fail_num',('task_id='.$single['task_id']));
            $update_data2 = array(
                'success_num'=>$success_num + 1,
                'fail_num'=>(($fail_num - 1 >0) ? ($fail_num - 1) : 0),
            );
            $this->update('task_statistics', $update_data2, 'task_id=' . $single['task_id']);
            //分币成功后，将分币结果推送给用户
            $this->model('notify')->send(
                null,
                $single['uid'],
                notify_class::TYPE_COIN_DISTRIBUTE,
                notify_class::YOYOW_COIN,
                $single['id'],
                array('coin' => $single['coin'])
            );
        }
    }

    /**
     * 更新转账成功后，更新相关表
     * @param $user_id
     * @throws Zend_Exception
     */
    public function update_statistics($user_id) {
        $data_list = $this->model('yoyowcoin')->fetch_all("user_yoyow_coin", "distribute_result != 1 AND uid = " . $user_id);
        foreach ($data_list as $single) {
            $update_data=array(
                'distribute_result'=> 1
            );
            $this->update('user_yoyow_coin', $update_data, 'id=' . $single['id']);
            $success_num = $this->fetch_one('task_statistics','success_num',('task_id='.$single['task_id']));
            $no_account_num = $this->fetch_one('task_statistics','no_account',('task_id='.$single['task_id']));
            $update_data2 = array(
                'success_num'=>$success_num + 1,
                'no_account'=>(($no_account_num - 1 >0) ? ($no_account_num - 1) : 0),
            );
            $this->update('task_statistics', $update_data2, 'task_id=' . $single['task_id']);
        }
        //注册奖励的提现更新表
        $update_data=array(
            'status'=> 0
        );
        $this->update('register_reward_record', $update_data, "status = 2 AND uid = " . $user_id);

        //邀请奖励的提现更新表
        $update_data=array(
            'has_ditribute'=> 1,
            'withdrawal_time'=>time()
        );
        $this->update('invitation_yoyow', $update_data, '(has_ditribute = 3 OR has_ditribute = 2) AND coin_uid = '.$user_id);

    }

    /**
     * 记录用户积分yoyow分配结果
     * @param $data
     * @param $coin_id
     * @param $user_integral_sum
     */
    public function add_integral_yoyow_detail($data, $coin_id, $user_integral_sum) {
            $integral_list = $this->fetch_all('integral_log','time between ' . $data['act_strat_time'] .' AND ' .   $data['act_end_time']  . ' AND uid = ' . $data['uid'] ,'time DESC');
            $temp = 0;
            foreach ($integral_list AS $single) {
                $temp++;
                $single_coin = substr(sprintf('%.6f', $single['integral'] / $user_integral_sum * $data['coin']), 0, -2);
                $integral_yoyow_data = array(
                    'integral_id' => $single['id'],
                    'coin_id' => $coin_id,
                    'note' => $single['note'],
                    'coin' => $single_coin,
                    'integral'=> $single['integral'],
                    'integral_time'=> $single['time'],
                    'distribute_time' => $data['add_time']
                );
                $this->insert('integral_yoyow_coin', $integral_yoyow_data);
                $update_data = array(
                    'has_distribute'=>1
                );
                $this->update('integral_log', $update_data, 'id = '.$single['id']);
            }
    }

    /**
     * 根据用户积分记录id获取其分得的yoyow，
     * 有返回实际，没有返回计算的
     *
     * @param $integral_id
     * @return int|mixed|string
     */
    public function get_integral_yoyow_by_integral_id($integral_id) {
        if(!$integral_id) {
            return '无积分记录Id';
        }
        //从积分分配记录表中获取当前分配的yoyow币数
        $yoyow_icon = $this->fetch_one('integral_yoyow_coin', 'coin', 'integral_id = ' . $integral_id);
        if($yoyow_icon) {
            return $yoyow_icon;
        }else if(is_numeric($yoyow_icon)&& $yoyow_icon == 0) {
            return 0;
        }else{
            //没有获取到时，从平台获取默认算出的
            $default_sum_yoyow = get_setting('default_distribute_yoyow_coin');

            if(!$default_sum_yoyow) {
                return 0;
            }else {
                $user_integral_list=$this->model('integral')->special_sum_integral_in_time();

                $sum_integral = 0;
                //循环计算所有人积分情况
                foreach ($user_integral_list AS $user_integral) {
                    if($user_integral['total']<=0){
                        continue;
                    }else {
                        $sum_integral  = $sum_integral + $user_integral['total'];
                    }
                }
                $integral = $this->fetch_one('integral_log', 'integral', 'id = '.$integral_id);
                if($sum_integral<=0){
                    return 0;
                }else{
                    return $integral? substr(sprintf( '%.6f', $integral/$sum_integral * $default_sum_yoyow), 0, -2) : 0;
                }

            }
        }
    }

    /**
     * @param $uid 被邀请的用户id
     * @param $yoyow 被邀请人分得的yoyow币数量
     * @param $task_id 本次任务id
     */
    public function calculate_commission($uid, $yoyow, $task_id){
        if(!$uid) { return;}
        $start_time = time();
        $invitatior_id = $this->fetch_one('invitation', 'uid','active_uid='.$uid);
        /********************** 判断是否是5-5号以后邀请注册的用户 jiangchengkai 20180-05-02 start *************************************/
        $startDate = explode('-','2018-05-03');
        $startTime = mktime(0,0,0,$startDate[1],$startDate[2],$startDate[0]);
        $isSatisfy = $this->fetch_one('users', 'uid','uid='.$uid.' and reg_time >='.$startTime);
        /********************** 判断是否是5-5号以后邀请注册的用户 jiangchengkai 20180-05-02 end*************************************/
        $percent = (get_setting('invitation_reward_percent')=='') ? 0 : get_setting('invitation_reward_percent');
        if($invitatior_id && $isSatisfy && $percent != 0){
            $user1 = $this->fetch_row('users', 'uid='.$uid);
            $user2 = $this->fetch_row('users', 'uid='.$invitatior_id . ' AND forbidden = 0 ');
            if(!$user2){ return;}
            $user_yoyow_account = $this->fetch_one('users_yoyow','yoyow',('uid='.$invitatior_id));

            $max = (get_setting('invitation_reward_limit')=='') ? 0 : get_setting('invitation_reward_limit');
            $calculate_result = substr(sprintf('%.6f', ($yoyow * $percent > $max? $max: $yoyow * $percent)), 0, -2);
            if($user_yoyow_account && $isSatisfy){
                //有yoyow账号时

                //获取接口地址
                $req_api_url=get_setting('api_url', false);
                //调用接口yoyow币转账
                $send_obj = array(
                    'uid' => $user_yoyow_account,
                    'amount' => $calculate_result,
                    'memo' => 'memo',
                    'time' => microtime(true) * 1000
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
                    $distributeResult = 1;
                }else {
                    $distributeResult = 0;
                }
            }else{
                //没有yoyow账号
                $distributeResult = 2;
            }
            //$remark=sprintf('被邀请用户%s获取yoyow时，自己获取的提成',$user1['user_name']);
            $data=array(
                'uid'=>$user2['uid'],
                'coin'=>$calculate_result,
                'origin'=>'ASSIGN',
                'add_time'=>time(),
                'task_id'=>$task_id,
                'act_strat_time'=>$start_time,
                'act_end_time'=>time(),
                'remark'=>$user1['user_name'],
                'distribute_result'=>$distributeResult,
                'inteface_message'=> ($res_tran_json ? implode('', $res_tran_json) : '未提现'),
                'type'=>1
            );
            //将用户积分和yoyow记录插入积分yoyow关联表中
            $this->insert('user_yoyow_coin',$data);
        }
    }

    /**
     * 平台转账给个人用户接口
     * @param $uid  用户yoyow账号
     * @param $coin 转账币数
     */
    public function transfer_to_by_yoyow($user_yoyow_account,$coin){
        $start_time = time();
        //获取接口地址
        $req_api_url=get_setting('api_url', false);

        //调用接口yoyow币转账
        $send_obj = array(
            'uid' => $user_yoyow_account,
            'amount' => $coin,
            'memo' => 'memo',
            'time' => microtime(true) * 1000
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
        //调用接口成功后，更新分币记录表和统计表
        return $res_tran_json;
    }
}
