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

class update_class extends AWS_MODEL
{
    //删除回复点赞、踩 更新数据
    public function update_answer_agree_disagree($id){
       foreach ($id as $key => $val){
           //查询该条赞踩记录信息
           $vote_info = $this->fetch_row('answer_vote','voter_id = '.$val);
           //删除赞踩对应的积分记录
           $action = $vote_info['vote_value'] == 1 ? "PRAISE" : "OPPOSE";
           if($this->model('integral')->fetch_row('integral_log','action ="'.$action.'" and time ='.$vote_info['add_time'].' and uid ='.$vote_info['answer_uid'].' and item_id ='.$vote_info['vote_uid'])){
               $this->model('integral')->delete_integral_log($action,$vote_info['add_time'],$vote_info['answer_uid'],$vote_info['vote_uid']);
           }

           //删除该赞踩记录
           $this->model('answer')->delete_answer_vote($vote_info['voter_id']);
           //更新answer回复的反对数
           $this->model('answer')->update_vote_count($vote_info['answer_id'], 'against');
           //更新answer回复的赞同数
           $this->model('answer')->update_vote_count($vote_info['answer_id'], 'agree');

           //更新问题下的所有回复的赞同数 反对数
           $vote_question_id = $this->model('account')->fetch_one('answer','question_id','answer_id ='.$vote_info['answer_id']);
           $this->model('answer')->update_question_vote_count($vote_question_id);

           //history数据删除
           ACTION_LOG::delete_action_history('associate_type = ' . ACTION_LOG::CATEGORY_QUESTION . ' AND associate_action = ' . ACTION_LOG::ADD_AGREE . ' AND uid = ' . intval($vote_info['vote_uid']) . ' AND associate_id = ' . intval($vote_question_id) . ' AND associate_attached = ' . intval($vote_info['answer_id']));

           // 更新回复作者的被赞同数
           $this->model('account')->sum_user_agree_count($vote_info['answer_uid']);
       }
    }
    //删除文章点赞、踩 更新数据
    public function update_article_agree_disagree($id){
        foreach ($id as $key => $val){
            //查询该条赞踩记录信息
            $vote_info = $this->fetch_row('article_vote','id = '.$val);
            //删除赞踩对应的积分记录
            $action = $vote_info['rating'] == 1 ? "ARTICLE_PRAISE" : "ARTICLE_OPPOSE";
            if($this->model('integral')->fetch_row('integral_log','action ="'.$action.'" and time ='.$vote_info['time'].' and uid ='.$vote_info['item_uid'].' and item_id ='.$vote_info['uid'])){
                $this->model('integral')->delete_integral_log($action,$vote_info['time'],$vote_info['item_uid'],$vote_info['uid']);
            }

            //删除该赞踩记录
            $this->model('answer')->delete('article_vote', "id = ".$vote_info['id']);
            //更新article文章的赞同数
            $this->model('answer')->update('article', array(
                'votes' => $this->count('article_vote', "`type` = 'article' AND item_id = " . intval($vote_info['item_id']) . " AND rating = 1")
            ), 'id = ' . intval($vote_info['item_id']));

            //history数据删除
            ACTION_LOG::delete_action_history('associate_type = ' . ACTION_LOG::CATEGORY_QUESTION . ' AND associate_action = ' . ACTION_LOG::ADD_AGREE_ARTICLE . ' AND uid = ' . intval($vote_info['uid']) . ' AND associate_id = ' . intval($vote_info['item_id']));


            // 更新回复作者的被赞同数
            $this->model('account')->sum_user_agree_count($vote_info['item_uid']);
        }
    }

    //更新邀请奖励数据
    public function update_invite(){
        $per_page = 20000;
        $page = 1;
        $invite_list=array();

        //当前记录奖励用户是否有效数组
        $sql = "select ifnull(a.cnt,0)/b.cnt bite, au.uid from ".$this->get_table('users')." au 
                    left join (
                    select count(1) cnt, coin_uid from (
                    select online_time,coin_uid from ".$this->get_table('users')." au 
                    inner join ".$this->get_table('invitation_yoyow')." aiy on au.uid = aiy.base_uid and aiy.coin_type != '0' and aiy.has_ditribute = 0
                    ) a where a.online_time < 5 group by coin_uid
                    ) a on au.uid = a.coin_uid
                    left join (
                    select count(1) cnt, coin_uid from (
                    select online_time,coin_uid from ".$this->get_table('users')." au 
                    inner join ".$this->get_table('invitation_yoyow')." aiy on au.uid = aiy.base_uid and aiy.coin_type != '0' and aiy.has_ditribute = 0
                    ) a group by coin_uid
                    ) b on au.uid = b.coin_uid where ifnull(a.cnt,0)/b.cnt is not null";
        $invite_coin_effective_list = $this->query_all($sql);
        foreach($invite_coin_effective_list as $ke => $va){
            if($va['bite']>0.9){
                $invite_coin_effective[$va['uid']] = 2;
            }else{
                $invite_coin_effective[$va['uid']] = 1;
            }
        }

        while(count($invite_list = $this->fetch_page('invitation_yoyow','has_ditribute = 0',null,$page, $per_page)) != 0){
            //循环处理
            //当前记录是有效数组
            $invite_effective= array();
            //当前记录是无效数组
            $invite_no_effective= array();
            foreach ($invite_list as $key => $val){
                if($val['coin_type']== 0 ){
                    if($this->fetch_one('users','online_time','uid = '.$val['coin_uid']) < 5){
                        $invite_no_effective[] = $val['id'];
                    }else{
                        $invite_effective[] = $val['id'];
                    }
                }else if($val['coin_type']== 1 || $val['coin_type']== 2){
                    //如果奖励用户是否有效数组中存在
                    if($invite_coin_effective[$val['coin_uid']]==1){
                        $invite_effective[] = $val['id'];
                    }else if($invite_coin_effective[$val['coin_uid']]==2){
                        $invite_no_effective[] = $val['id'];
                    }else{
                        //如果不存在，则去计算。计算结果存入两个数组中
                        if($this->all_invite_effective($val['coin_uid'])==1){
                            $invite_effective[] = $val['id'];
                            $invite_coin_effective[$val['coin_uid']] = 1;
                        }else{
                            $invite_no_effective[] = $val['id'];
                            $invite_coin_effective[$val['coin_uid']] = 2;
                        }
                    }
                }
            }
            //批量更新
            $update_invite_effective_sql = "update ".$this->get_table('invitation_yoyow')." set effective = 1 where id in(" . implode(',', $invite_effective) . ")";
            $update_invite_no_effective_sql = "update ".$this->get_table('invitation_yoyow')." set effective = 0 where id in(" . implode(',', $invite_no_effective) . ")";
            $this->query_all($update_invite_effective_sql);
            $this->query_all($update_invite_no_effective_sql);
            $page +=1;
        }
    }
    //计算奖励用户的所有邀请是否有效
    public function all_invite_effective($uid){
        $sql = "select a.cnt/b.cnt bite from ".$this->get_table('users')." au left join (
                select count(1) cnt,coin_uid from ".$this->get_table('users')." au 
                inner join ".$this->get_table('invitation_yoyow')." aiy on au.uid = aiy.base_uid and aiy.coin_uid = ".$uid." and online_time < 5
                ) a on au.uid = a.coin_uid
                left join (
                select count(1) cnt,coin_uid from ".$this->get_table('users')." au 
                inner join ".$this->get_table('invitation_yoyow')." aiy on au.uid = aiy.base_uid and aiy.coin_uid = ".$uid."
                ) b on au.uid = b.coin_uid
                where au.uid = ".$uid;

        $bite = $this->query_all($sql);

        if($bite[0]['bite']>0.9){
            return 0;
        }else{
            return 1;
        }
    }

    public function delete_answer_vote_oppose_lately_by_uid(){
        $answer_vote_oppose_sql = "select voter_id from ".$this->get_table('answer_vote')." where vote_uid in(24742,24807,24770,23978,24759,23813,23502,23583,24820,23849,24254,23905,23562,23874,23416,24741,24200,20924,23432) and vote_value = -1 and add_time >1527523200";
        $answer_vote_list = $this->query_all($answer_vote_oppose_sql);
        foreach ($answer_vote_list as $key => $val){
            $answer_vote_id[]= $val['voter_id'];
        }
        $this->update_answer_agree_disagree($answer_vote_id);
    }

    public function delete_article_vote_oppose_lately_by_uid(){
        $article_vote_oppose_sql = "select id from ".$this->get_table('article_vote')." where uid in(24742,24807,24770,23978,24759,23813,23502,23583,24820,23849,24254,23905,23562,23874,23416,24741,24200,20924,23432) and rating = -1 and time >1527523200";
        $article_vote_list = $this->query_all($article_vote_oppose_sql);
        foreach ($article_vote_list as $key => $val){
            $article_vote_id[]=$val['id'];
        }
        $this->update_article_agree_disagree($article_vote_id);
    }

}