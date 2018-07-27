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

class people_class extends AWS_MODEL
{
    public function update_views($uid)
    {
        if (AWS_APP::cache()->get('update_views_people_' . md5(session_id()) . '_' . intval($uid)))
        {
            return false;
        }

        AWS_APP::cache()->set('update_views_people_' . md5(session_id()) . '_' . intval($uid), time(), get_setting('cache_level_normal'));

        return $this->query('UPDATE ' . $this->get_table('users') . ' SET views_count = views_count + 1 WHERE uid = ' . intval($uid));
    }

    public function get_user_reputation_topic($uid, $user_reputation, $limit = 10)
    {
        $reputation_topics = $this->get_users_reputation_topic(array(
            $uid
        ), array(
            $uid => $user_reputation
        ), $limit);

        return $reputation_topics[$uid];
    }

    public function get_users_reputation_topic($uids, $users_reputation, $limit = 10)
    {
        if ($users_reputation_topics = $this->model('reputation')->get_reputation_topic($uids))
        {
            foreach ($users_reputation_topics as $key => $val)
            {
                if ($val['reputation'] < 1 OR $val['agree_count'] < 1)
                {
                    continue;
                }

                $reputation_topics[$val['uid']][] = $val;
            }
        }

        if ($reputation_topics)
        {
            foreach ($reputation_topics AS $uid => $reputation_topic)
            {
                $reputation_topic = array_slice(aasort($reputation_topic, 'reputation', 'DESC'), 0, $limit);

                foreach ($reputation_topic as $key => $val)
                {
                    $topic_ids[$val['topic_id']] = $val['topic_id'];
                }

                foreach ($reputation_topic as $key => $val)
                {
                    $reputation_topic[$key]['topic_title'] = $topics[$val['topic_id']]['topic_title'];
                    $reputation_topic[$key]['url_token'] = $topics[$val['topic_id']]['url_token'];
                }

                $reputation_topics[$uid] = $reputation_topic;
            }

            $topics = $this->model('topic')->get_topics_by_ids($topic_ids);

            foreach ($reputation_topics as $uid => $reputation_topic)
            {
                foreach ($reputation_topic as $key => $val)
                {
                    $reputation_topics[$uid][$key]['topic_title'] = $topics[$val['topic_id']]['topic_title'];
                    $reputation_topics[$uid][$key]['url_token'] = $topics[$val['topic_id']]['url_token'];
                }
            }
        }

        return $reputation_topics;
    }

    public function get_near_by_users($longitude, $latitude, $uid, $limit = 10)
    {
        $squares = $this->model('geo')->get_square_point($longitude, $latitude, 50);

        if ($weixin_users = $this->fetch_all('users_weixin', "`uid` != " . intval($uid) . " AND `location_update` > 0 AND `latitude` > " . $squares['BR']['latitude'] . " AND `latitude` < " . $squares['TL']['latitude'] . " AND `longitude` > " . $squares['TL']['longitude'] . " AND `longitude` < " . $squares['BR']['longitude'], 'location_update DESC', null, $limit))
        {
            foreach ($weixin_users AS $key => $val)
            {
                $near_by_uids[] = $val['uid'];
                $near_by_location_update[$val['uid']] = $val['location_update'];
                $near_by_location_longitude[$val['uid']] = $val['longitude'];
                $near_by_location_latitude[$val['uid']] = $val['latitude'];
            }

        }

        if ($near_by_uids)
        {
            if ($near_by_users = $this->model('account')->get_user_info_by_uids($near_by_uids))
            {
                foreach ($near_by_users AS $key => $val)
                {
                    $near_by_users[$key]['location_update'] = $near_by_location_update[$val['uid']];

                    $near_by_users[$key]['distance'] = $this->model('geo')->get_distance($longitude, $latitude, $near_by_location_longitude[$val['uid']], $near_by_location_latitude[$val['uid']]);
                }
            }
        }

        return $near_by_users;
    }

    public function get_user_income($uid,$page){
        return $this->fetch_all('user_yoyow_coin', "`uid`= " . $uid." AND type = 0", 'add_time DESC', (intval($page) * 10) . ', 10');

    }

    /**注册奖励
     * @param $uid
     * @param $page
     * @return array
     */
    public function get_user_income_register($uid,$page){
        //return $this->fetch_all('register_reward_record', "`uid`= " . $uid , 'time DESC', (intval($page) * 10) . ', 10');
        $sql = "SELECT * FROM (SELECT
			      coin_uid as id,
			      coin,
			      '注册奖励' as remark,
			      time
		        FROM ".get_table('invitation_yoyow')."
			      
		        WHERE coin_type = 0 and has_ditribute != 0 and has_ditribute != 4
		        UNION ALL
			    SELECT
				  t.uid AS id,
				  t.coin AS coin,
				  t.remark as remark,
				  t.time AS time
			    FROM ".get_table('register_reward_record')." t
	            ) tmp
                 WHERE
	            tmp.id = ".$uid."
                ORDER BY
	            time DESC";
        try{
            $result = $this->query_all($sql, (intval($page) * 10) . ', 10', null);
        }catch (Exception $e){
            var_dump($e); die;
        }
        return $result;

    }

    /**邀请奖励
     * @param $uid
     * @param $page
     * @return array
     */
    public function get_user_income_invite($uid,$page){
        return $this->fetch_all('invitation_yoyow', "`coin_uid`= " . $uid .' AND has_ditribute != 0 AND has_ditribute != 4 AND coin_type != 0', 'time DESC', (intval($page) * 10) . ', 10');
    }

    public function  get_yoyow_sum($uid, $page) {
        $sql = 'SELECT * FROM ( SELECT id, task_id, coin, distribute_result, uid, act_strat_time, add_time, origin, act_end_time, remark, inteface_message, type FROM '. $this->get_table('user_yoyow_coin').
            ' where type =0 UNION ALL SELECT
			a.task_id as id,
			a.task_id as task_id,
            sum(a.coin) as coin,
            a.distribute_result as distribute_result,
			a.uid as uid,
		    b.act_start_time as act_strat_time,
			a.add_time as add_time,
			a.origin as origin,
            b.act_end_time as act_end_time,
			a.remark as remark,
			a.inteface_message as inteface_message,
			a.type as type
		    FROM '.$this->get_table('user_yoyow_coin').' a INNER  JOIN '.$this->get_table('yoyow_assign_task').' b ON a.task_id = b.id
            where a.type =1 and a.uid = '.$uid.'
            GROUP BY a.task_id'.
            ' UNION ALL SELECT t.id AS id, t.invitation_id AS task_id,'.
            ' t.coin AS coin, t.coin_type AS distribute_result,t.coin_uid '.
            ' AS uid,t.base_uid AS act_strat_time, t.time AS add_time,t.second_name AS '.
            ' origin, 0 AS act_end_time,t.first_name AS remark,0 AS inteface_message, '.
            ' 2 AS type FROM  '.$this->get_table('invitation_yoyow').
            ' t WHERE t.has_ditribute != 0 and t.has_ditribute != 4 UNION ALL select rr.id as id,rr.id as task_id,rr.coin as coin,4 as distribute_result,rr.uid as uid,rr.time as act_strat_time,rr.time as add_time,0 as origin,0 as act_end_time,rr.remark as remark,0 as inteface_message,4 as type FROM '.$this->get_table('register_reward_record').' rr) tmp WHERE tmp.uid = '.$uid.' ORDER BY tmp.add_time DESC ';
        try{
            $result = $this->query_all($sql, (intval($page) * 10) . ', 10', null);
        }catch (Exception $e){
            var_dump($e); die;
        }
        return $result;

    }

    public function get_user_income_commission($uid,$page){
        return $this->fetch_all('user_yoyow_coin', "`uid`= " . $uid." AND type = 1", 'add_time DESC', (intval($page) * 10) . ', 10');

    }
    
    public function get_yoyow_by_id($uid, $type){
        if($type) {
             $coin_withdraw1  = $this->fetch_one("user_yoyow_coin", "sum(coin)", "`uid`= " . $uid." AND distribute_result != 1");
             $coin_withdraw2  = $this->fetch_one("register_reward_record", "sum(coin)", "`uid`= " . $uid." AND status != 0");
             $coin_withdraw3  = $this->fetch_one("invitation_yoyow", "sum(coin)", "`coin_uid`= " . $uid." AND (has_ditribute = 3 OR has_ditribute = 2)");
            return $coin_withdraw1+$coin_withdraw2+$coin_withdraw3;
        }else {
            $coin1 = $this->fetch_one("user_yoyow_coin", "sum(coin)", "`uid`= " . $uid." AND distribute_result != 0");
            $coin2 = $this->fetch_one('invitation_yoyow','sum(coin)','coin_uid = '.$uid.' AND has_ditribute != 0 AND has_ditribute != 4');
            $coin3 = $this->fetch_one("register_reward_record", "sum(coin)", "`uid`= " . $uid);
            return $coin1+$coin2+$coin3;

        }
    }

    public function get_issued_yoyow_by_id($uid){
//        $register_coin = $this->fetch_one("register_yoyow_record", "sum(coin)", "(status = 0 or status is null)and uid= " . $uid);
//        if($this->fetch_one("reputation_users", "id", "(status = 0 or status is null) and uid= " . $uid)){
//            $register_coin += 18;
//        }
        return $this->fetch_one("invitation_yoyow", "coin", "(has_ditribute = 0 or has_ditribute is null)and coin_type =0 and effective = 1 and coin_uid= " . $uid);
    }

    public function get_invitation_issued_yoyow_by_id($uid){
        return $this->fetch_one("invitation_yoyow", "sum(coin)", "(has_ditribute = 0 or has_ditribute is null) AND coin_type !=0 and effective = 1 and coin_uid= " . $uid);;
    }

    public function get_yoyow_uid($uid)
    {
        return $this->fetch_one('users_yoyow','yoyow','uid='.$uid);
    }

    public function get_is_out_money($uid){
        $coin_withdraw1  = $this->fetch_one("user_yoyow_coin", "sum(coin)", "`uid`= " . $uid." AND distribute_result != 1");
        $coin_withdraw2  = $this->fetch_one("register_reward_record", "sum(coin)", "`uid`= " . $uid." AND status != 0");
        $coin_withdraw3  = $this->fetch_one("invitation_yoyow", "sum(coin)", "`coin_uid`= " . $uid." AND (has_ditribute = 3 OR has_ditribute = 2)");
        return $coin_withdraw1+$coin_withdraw2+$coin_withdraw3;
    }
}