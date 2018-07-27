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

class integral_class extends AWS_MODEL
{
    public function process($uid, $action, $integral, $note = '', $item_id = null)
    {
        /*if (get_setting('integral_system_enabled') == 'N')
        {
            return false;
        }*/

        if ($integral == 0)
        {
            return false;
        }

        $log_id = $this->log($uid, $action, $integral, $note, $item_id);

        $this->sum_integral($uid);

        return $log_id;
    }

    public function fetch_log($uid, $action)
    {
        return $this->fetch_row('integral_log', 'uid = ' . intval($uid) . ' AND action = \'' . $this->quote($action) . '\'');
    }

    public function fetch_last_log($uid, $action)
    {
        return $this->fetch_one('integral_log', 'time','uid = ' . intval($uid) . ' AND action = \'' . $this->quote($action) . '\'','time DESC');
    }

    public function log($uid, $action, $integral, $note = '', $item_id = null)
    {
        if ($user_info = $this->model('account')->get_user_info_by_uid($uid))
        {
            return $this->insert('integral_log', array(
                'uid' => intval($uid),
                'action' => $action,
                'integral' => (int)$integral,
                'balance' => ((int)$user_info['integral'] + (int)$integral),
                'note' => $note,
                'item_id' => (int)$item_id,
                'time' => time()
            ));
        }
    }
    public function get_integral_by_uid($uid,$start_time,$end_time){
        return $this->fetch_all('integral_log','time between '.$start_time.' and '. $end_time .' AND uid='.intval($uid),'time DESC');
    }

    // 根据日志计算积分
    public function sum_integral($uid)
    {
        return $this->update('users', array(
            'integral' => $this->sum('integral_log', 'integral', 'uid = ' . intval($uid))
        ), 'uid = ' . intval($uid));
    }

    public function query_list($userName, $type, $startDate, $endDate, $page = null, $limit = 10){
        $sql = "SELECT a.id, b.user_name, a.action, a.integral, a.note, a.balance, a.time FROM ".$this->get_table('integral_log') ." as a inner join ".$this->get_table('users')." as b on a.uid = b.uid where 1=1 ";
        if($userName){
            $sql.= " and b.user_name like '%$userName%'";
        }
        if($type){
            $sql.= " and a.action = '$type'";
        }
        if($startDate){
            $sql.= " and a.time >=".strtotime(base64_decode($startDate));
        }
        if($endDate){
            $sql.= " and a.time <=".strtotime(base64_decode($endDate));
        }
        $sql.= " ORDER BY a.time DESC LIMIT ". (($page-1)*$limit).",".$limit;

        $post_index = $this->query_all($sql);

        return $post_index;
    }

    public function query_list_count($userName, $type, $startDate, $endDate){
        $sql = "SELECT count(1) as cnt FROM ".$this->get_table('integral_log') ." as a inner join ".$this->get_table('users')." as b on a.uid = b.uid where 1=1 ";
        if($userName){
            $sql.= " and b.user_name like '%$userName%'";
        }
        if($type){
            $sql.= " and a.action = '$type'";
        }
        if($startDate){
            $sql.= " and a.time >=".strtotime(base64_decode($startDate));
        }
        if($endDate){
            $sql.= " and a.time <=".strtotime(base64_decode($endDate));
        }
        $sql.= " ORDER BY a.time DESC ";

        $post_index = $this->query_all($sql);

        return $post_index[0]['cnt'];
    }

    public function parse_log_item($parse_items)
    {
        if (!is_array($parse_items))
        {
            return false;
        }

        foreach ($parse_items AS $log_id => $item)
        {
            if (strstr($item['action'], 'ANSWER_FOLD_'))
            {
                $item['action'] = 'ANSWER_FOLD';
            }

            switch ($item['action'])
            {
                case 'NEW_QUESTION':
                case 'ANSWER_QUESTION':
                case 'QUESTION_ANSWER':
                case 'INVITE_ANSWER':
                case 'ANSWER_INVITE':
                case 'THANKS_QUESTION':
                case 'RECOMMEND_QUESTION':
                case 'UNSET_RECOMMEND_QUESTION':
                case 'QUESTION_THANKS':
                    $question_ids[] = $item['item_id'];
                    break;

                case 'ANSWER_THANKS':
                case 'THANKS_ANSWER':
                case 'ANSWER_FOLD':
                case 'BEST_ANSWER':
                case 'QUESTION_ANSWER_DISCUSS':
                case 'DISCUSS_QUESTION_ANSWER':
                    $answer_ids[] = $item['item_id'];
                    break;
                case 'ARTICLE_ANSWER_DISCUSS':
                case 'DISCUSS_ARTICLE_ANSWER':
                    $article_comment_ids[]=$item['item_id'];
                    break;

                case 'INVITE':
                    $user_ids[] = $item['item_id'];
                    break;
                case 'RECOMMEND_ARTICLE':
                case 'UNSET_RECOMMEND_ARTICLE':
                case 'NEW_ARTICLE':
                    $article_ids[]=$item['item_id'];
                    break;
                case 'OPPOSE':
                case 'PRAISE':
                case 'CANCEL_PRAISE':
                case 'CANCEL_OPPOSE':

                   if(strpos($item['note'],'#')){
                        $q_ids[] = substr(strstr($item['note'],'#'),1);
                    }


                break;
                case 'ARTICLE_OPPOSE':
                case 'ARTICLE_PRAISE':
                case 'ARTICLE_CANCEL_PRAISE':
                case 'ARTICLE_CANCEL_OPPOSE':

                    if(strpos($item['note'],'#')){
                        $a_ids[] = substr(strstr($item['note'],'#'),1);
                    }


                    break;
            }
        }
        if($q_ids){
            $q_info=$this->model('question')->get_question_info_by_ids($q_ids);
        }
        if($a_ids){
            $a_info=$this->model('article')->get_article_info_by_ids($a_ids);
        }
        if ($question_ids)
        {
            $questions_info = $this->model('question')->get_question_info_by_ids($question_ids);
        }
        if ($article_ids)
        {
            $articles_info = $this->model('article')->get_article_info_by_ids($article_ids);
        }

        if ($answer_ids)
        {
            $answers_info = $this->model('answer')->get_answers_by_ids($answer_ids);
        }

        if($article_comment_ids){
            $article_comment_info=$this->model('article')->get_comments_by_ids($article_comment_ids);
        }
        if ($user_ids)
        {
            $users_info = $this->model('account')->get_user_info_by_uids($user_ids);
        }

        foreach ($parse_items AS $log_id => $item)
        {
            if (!$item['item_id'])
            {
                continue;
            }

            if (strstr($item['action'], 'ANSWER_FOLD_'))
            {
                $item['action'] = 'ANSWER_FOLD';
            }

            switch ($item['action'])
            {
                case 'OPPOSE':
                case 'PRAISE':
                case 'CANCEL_PRAISE':
                case 'CANCEL_OPPOSE':
                    if ($q_info[substr(strstr($item['note'],'#'),1)])
                    {
                        $result[$log_id] = array(
                            'title' => '问题: ' . $q_info[substr(strstr($item['note'],'#'),1)]['question_content'],
                            'url' => get_js_url('/question/' . substr(strstr($item['note'],'#'),1))
                        );
                    }

                    break;
                case 'ARTICLE_OPPOSE':
                case 'ARTICLE_PRAISE':
                case 'ARTICLE_CANCEL_PRAISE':
                case 'ARTICLE_CANCEL_OPPOSE':

                if ($a_info[substr(strstr($item['note'],'#'),1)])
                {
                    $result[$log_id] = array(
                        'title' => '文章: ' . $a_info[substr(strstr($item['note'],'#'),1)]['title'],
                        'url' => get_js_url('/article/' . substr(strstr($item['note'],'#'),1))
                    );
                }

                break;
                case 'NEW_QUESTION':
                case 'ANSWER_INVITE':
                case 'ANSWER_QUESTION':
                case 'QUESTION_ANSWER':
                case 'INVITE_ANSWER':
                case 'THANKS_QUESTION':
                case 'RECOMMEND_QUESTION':
                case 'UNSET_RECOMMEND_QUESTION':
                case 'QUESTION_THANKS':
                    if ($questions_info[$item['item_id']])
                    {
                        $result[$log_id] = array(
                            'title' => '问题: ' . $questions_info[$item['item_id']]['question_content'],
                            'url' => get_js_url('/question/' . $item['item_id'])
                        );
                    }

                    break;

                case 'ANSWER_THANKS':
                case 'THANKS_ANSWER':
                case 'ANSWER_FOLD':
                case 'BEST_ANSWER':
                    if ($answers_info[$item['item_id']])
                    {
                        $result[$log_id] = array(
                            'title' => '答案: ' . cjk_substr($answers_info[$item['item_id']]['answer_content'], 0, 24, 'UTF-8', '...'),
                            'url' => get_js_url('/question/id-' . $answers_info[$item['item_id']]['question_id'] . '__answer_id-' . $item['item_id'] . '__single-TRUE')
                        );
                    }
                    break;
                case 'QUESTION_ANSWER_DISCUSS':
                case 'DISCUSS_QUESTION_ANSWER':
                    if ($answers_info[$item['item_id']])
                    {
                        $result[$log_id] = array(
                            'title' => '回复: ' . cjk_substr($answers_info[$item['item_id']]['answer_content'], 0, 24, 'UTF-8', '...'),
                            'url' => get_js_url('/question/id-' . $answers_info[$item['item_id']]['question_id'] . '__answer_id-' . $item['item_id'] . '__single-TRUE')
                        );
                    }
                    break;

                case 'ARTICLE_ANSWER_DISCUSS':
                case 'DISCUSS_ARTICLE_ANSWER':
                    if ($article_comment_info[$item['item_id']])
                    {
                        $result[$log_id] = array(
                            'title' => '回复: ' . cjk_substr($article_comment_info[$item['item_id']]['message'], 0, 24, 'UTF-8', '...'),
                            'url' => get_js_url('/article/id-' . $article_comment_info[$item['item_id']]['article_id'] . '__answer_id-' . $item['item_id'] . '__single-TRUE')
                        );
                    }
                    break;

                case 'INVITE':
                    if ($users_info[$item['item_id']])
                    {
                        $result[$log_id] = array(
                            'title' => '会员: ' . $users_info[$item['item_id']]['user_name'],
                            'url' => get_js_url('/people/' . $users_info[$item['item_id']]['uid'])
                        );
                    }
                    break;
                case 'RECOMMEND_ARTICLE':
                case 'UNSET_RECOMMEND_ARTICLE':
                case 'NEW_ARTICLE':
                    if ($articles_info[$item['item_id']])
                    {
                        $result[$log_id] = array(
                            'title' => '文章: ' . $articles_info[$item['item_id']]['title'],
                            'url' => get_js_url('/article/'.$item['item_id'])
                        );
                    }
                    break;
            }
        }

        return $result;
    }

    public function parse_yoyow_log_item($parse_items)
    {
        if (!is_array($parse_items))
        {
            return false;
        }

        foreach ($parse_items AS $log_id => $item)
        {
            if (strstr($item['action'], 'ANSWER_FOLD_'))
            {
                $item['action'] = 'ANSWER_FOLD';
            }

            switch ($item['action'])
            {
                case 'NEW_QUESTION':
                case 'ANSWER_QUESTION':
                case 'QUESTION_ANSWER':
                case 'INVITE_ANSWER':
                case 'ANSWER_INVITE':
                case 'THANKS_QUESTION':
                case 'RECOMMEND_QUESTION':
                case 'UNSET_RECOMMEND_QUESTION':
                case 'QUESTION_THANKS':
                    $question_ids[] = $item['item_id'];
                    break;

                case 'ANSWER_THANKS':
                case 'THANKS_ANSWER':
                case 'ANSWER_FOLD':
                case 'BEST_ANSWER':
                case 'QUESTION_ANSWER_DISCUSS':
                case 'DISCUSS_QUESTION_ANSWER':
                    $answer_ids[] = $item['item_id'];
                    break;
                case 'ARTICLE_ANSWER_DISCUSS':
                case 'DISCUSS_ARTICLE_ANSWER':
                    $article_comment_ids[]=$item['item_id'];
                    break;

                case 'INVITE':
                    $user_ids[] = $item['item_id'];
                    break;
                case 'RECOMMEND_ARTICLE':
                case 'UNSET_RECOMMEND_ARTICLE':
                case 'NEW_ARTICLE':
                    $article_ids[]=$item['item_id'];
                    break;
                case 'OPPOSE':
                case 'PRAISE':
                case 'CANCEL_PRAISE':
                case 'CANCEL_OPPOSE':

                    if(strpos($item['note'],'#')){
                        $q_ids[] = substr(strstr($item['note'],'#'),1);
                    }


                    break;
            }
        }
        if($q_ids){
            $q_info=$this->model('question')->get_question_info_by_ids($q_ids);
        }
        if ($question_ids)
        {
            $questions_info = $this->model('question')->get_question_info_by_ids($question_ids);
        }
        if ($article_ids)
        {
            $articles_info = $this->model('article')->get_article_info_by_ids($article_ids);
        }

        if ($answer_ids)
        {
            $answers_info = $this->model('answer')->get_answers_by_ids($answer_ids);
        }

        if($article_comment_ids){
            $article_comment_info=$this->model('article')->get_comments_by_ids($article_comment_ids);
        }
        if ($user_ids)
        {
            $users_info = $this->model('account')->get_user_info_by_uids($user_ids);
        }

        foreach ($parse_items AS $log_id => $item)
        {
            if (!$item['item_id'])
            {
                continue;
            }

            if (strstr($item['action'], 'ANSWER_FOLD_'))
            {
                $item['action'] = 'ANSWER_FOLD';
            }

            switch ($item['action'])
            {
                case 'LOGIN':
                    if ($q_info[substr(strstr($item['note'],'#'),1)])
                    {
                        $result[$log_id] = array(
                            'title' => substr(strstr($item['note'],'#'),1)
                        );
                    }
                    break;
                case 'OPPOSE':
                    if ($q_info[substr(strstr($item['note'],'#'),1)])
                    {
                        $result[$log_id] = array(
                            'title' => '问题:"' . $q_info[substr(strstr($item['note'],'#'),1)]['question_content'].'"被👎',
                            'url' => get_js_url('/question/' . substr(strstr($item['note'],'#'),1))
                        );
                    }
                    break;
                case 'PRAISE':
                if ($q_info[substr(strstr($item['note'],'#'),1)])
                {
                    $result[$log_id] = array(
                        'title' => '问题:"' . $q_info[substr(strstr($item['note'],'#'),1)]['question_content'].'"被👍"',
                        'url' => get_js_url('/question/' . substr(strstr($item['note'],'#'),1))
                    );
                }
                break;
                case 'CANCEL_PRAISE':
                if ($q_info[substr(strstr($item['note'],'#'),1)])
                {
                    $result[$log_id] = array(
                        'title' => '问题:"' . $q_info[substr(strstr($item['note'],'#'),1)]['question_content'].'"被取消👍"',
                        'url' => get_js_url('/question/' . substr(strstr($item['note'],'#'),1))
                    );
                }
                break;
                case 'CANCEL_OPPOSE':
                    if ($q_info[substr(strstr($item['note'],'#'),1)])
                    {
                        $result[$log_id] = array(
                            'title' => '问题:"' . $q_info[substr(strstr($item['note'],'#'),1)]['question_content'].'"被取消👎"',
                            'url' => get_js_url('/question/' . substr(strstr($item['note'],'#'),1))
                        );
                    }
                    break;
                case 'NEW_QUESTION':
                    if ($questions_info[$item['item_id']])
                    {
                        $result[$log_id] = array(
                            'title' => '发起新问题"' . $questions_info[$item['item_id']]['question_content'].'"',
                            'url' => get_js_url('/question/' . $item['item_id'])
                        );
                    }
                    break;
                case 'ANSWER_INVITE':
                    if ($questions_info[$item['item_id']])
                    {
                        $result[$log_id] = array(
                            'title' => '问题"' . $questions_info[$item['item_id']]['question_content'].'"回复邀请回答',
                            'url' => get_js_url('/question/' . $item['item_id'])
                        );
                    }
                    break;
                case 'ANSWER_QUESTION':
                    if ($questions_info[$item['item_id']])
                    {
                        $result[$log_id] = array(
                            'title' => '回复问题"' . $questions_info[$item['item_id']]['question_content'].'"',
                            'url' => get_js_url('/question/' . $item['item_id'])
                        );
                    }
                    break;
                case 'QUESTION_ANSWER':
                    if ($questions_info[$item['item_id']])
                    {
                        $result[$log_id] = array(
                            'title' => '问题"' . $questions_info[$item['item_id']]['question_content'].'"被回答',
                            'url' => get_js_url('/question/' . $item['item_id'])
                        );
                    }
                    break;
                case 'INVITE_ANSWER':
                    if ($questions_info[$item['item_id']])
                    {
                        $result[$log_id] = array(
                            'title' => '问题"' . $questions_info[$item['item_id']]['question_content'].'"邀请回答成功',
                            'url' => get_js_url('/question/' . $item['item_id'])
                        );
                    }
                    break;
                case 'THANKS_QUESTION':
                    if ($questions_info[$item['item_id']])
                    {
                        $result[$log_id] = array(
                            'title' => '问题"' . $questions_info[$item['item_id']]['question_content'].'"被感谢',
                            'url' => get_js_url('/question/' . $item['item_id'])
                        );
                    }
                    break;
                case 'RECOMMEND_QUESTION':
                    if ($questions_info[$item['item_id']])
                    {
                        $result[$log_id] = array(
                            'title' => '问题"' . $questions_info[$item['item_id']]['question_content'].'"被推荐',
                            'url' => get_js_url('/question/' . $item['item_id'])
                        );
                    }
                    break;
                case 'UNSET_RECOMMEND_QUESTION':
                    if ($questions_info[$item['item_id']])
                    {
                        $result[$log_id] = array(
                            'title' => '问题"' . $questions_info[$item['item_id']]['question_content'].'"被取消推荐',
                            'url' => get_js_url('/question/' . $item['item_id'])
                        );
                    }
                    break;
                case 'QUESTION_THANKS':
                    if ($questions_info[$item['item_id']])
                    {
                        $result[$log_id] = array(
                            'title' => '问题"' . $questions_info[$item['item_id']]['question_content'].'"被感谢',
                            'url' => get_js_url('/question/' . $item['item_id'])
                        );
                    }
                    break;
                case 'ANSWER_THANKS':
                    if ($questions_info[$item['item_id']])
                    {
                        $result[$log_id] = array(
                            'title' => '问题"' . $questions_info[$item['item_id']]['question_content'].'"感谢回复',
                            'url' => get_js_url('/question/' . $item['item_id'])
                        );
                    }
                    break;
                case 'THANKS_ANSWER':
                    if ($questions_info[$item['item_id']])
                    {
                        $result[$log_id] = array(
                            'title' => '问题"' . $questions_info[$item['item_id']]['question_content'].'"回复被感谢',
                            'url' => get_js_url('/question/' . $item['item_id'])
                        );
                    }
                    break;
                case 'ANSWER_FOLD':
                    if ($questions_info[$item['item_id']])
                    {
                        $result[$log_id] = array(
                            'title' => '问题"' . $questions_info[$item['item_id']]['question_content'].'"回复被折叠',
                            'url' => get_js_url('/question/' . $item['item_id'])
                        );
                    }
                    break;
                case 'BEST_ANSWER':
                    if ($answers_info[$item['item_id']])
                    {
                        $result[$log_id] = array(
                            'title' => '答案: ' . cjk_substr($answers_info[$item['item_id']]['answer_content'], 0, 24, 'UTF-8', '...').'"被评为最佳回复',
                            'url' => get_js_url('/question/id-' . $answers_info[$item['item_id']]['question_id'] . '__answer_id-' . $item['item_id'] . '__single-TRUE')
                        );
                    }
                    break;
                case 'QUESTION_ANSWER_DISCUSS':
                    if ($questions_info[$item['item_id']])
                    {
                        $result[$log_id] = array(
                            'title' => '问题"' . $questions_info[$item['item_id']]['question_content'].'"回复被评论',
                            'url' => get_js_url('/question/' . $item['item_id'])
                        );
                    }
                    break;
                case 'DISCUSS_QUESTION_ANSWER':
                    if ($answers_info[$item['item_id']])
                    {
                        $result[$log_id] = array(
                            'title' => '回复: ' . cjk_substr($answers_info[$item['item_id']]['answer_content'], 0, 24, 'UTF-8', '...'),
                            'url' => get_js_url('/question/id-' . $answers_info[$item['item_id']]['question_id'] . '__answer_id-' . $item['item_id'] . '__single-TRUE')
                        );
                    }
                    break;

                case 'ARTICLE_ANSWER_DISCUSS':
                    if ($questions_info[$item['item_id']])
                    {
                        $result[$log_id] = array(
                            'title' => '问题"' . $questions_info[$item['item_id']]['question_content'].'"回复被评论',
                            'url' => get_js_url('/question/' . $item['item_id'])
                        );
                    }
                    break;
                case 'DISCUSS_ARTICLE_ANSWER':
                    if ($article_comment_info[$item['item_id']])
                    {
                        $result[$log_id] = array(
                            'title' => '回复: ' . cjk_substr($article_comment_info[$item['item_id']]['message'], 0, 24, 'UTF-8', '...'),
                            'url' => get_js_url('/article/id-' . $article_comment_info[$item['item_id']]['article_id'] . '__answer_id-' . $item['item_id'] . '__single-TRUE')
                        );
                    }
                    break;

                case 'INVITE':
                    if ($users_info[$item['item_id']])
                    {
                        $result[$log_id] = array(
                            'title' => '邀请新会员" ' . $users_info[$item['item_id']]['user_name'].'"注册',
                            'url' => get_js_url('/people/' . $users_info[$item['item_id']]['uid'])
                        );
                    }
                    break;
                case 'RECOMMEND_ARTICLE':
                    if ($articles_info[$item['item_id']])
                    {
                        $result[$log_id] = array(
                            'title' => '文章"' . $articles_info[$item['item_id']]['title'].'"被推荐',
                            'url' => get_js_url('/article/'.$item['item_id'])
                        );
                    }
                    break;
                case 'UNSET_RECOMMEND_ARTICLE':
                    if ($articles_info[$item['item_id']])
                    {
                        $result[$log_id] = array(
                            'title' => '文章"' . $articles_info[$item['item_id']]['title'].'"被取消推荐',
                            'url' => get_js_url('/article/'.$item['item_id'])
                        );
                    }
                    break;
                case 'NEW_ARTICLE':
                    if ($articles_info[$item['item_id']])
                    {
                        $result[$log_id] = array(
                            'title' => '发起新文章"' . $articles_info[$item['item_id']]['title'].'"',
                            'url' => get_js_url('/article/'.$item['item_id'])
                        );
                    }
                    break;
            }
        }

        return $result;
    }

    /**
     * 在某个时间段内所有人积分数量总和
     * @param $start_stamp 开始时间
     * @param $end_time_stamp 结束时间
     * @return 计算结果
     */
    public function sum_integral_in_time($start_stamp,$end_time_stamp){
        $where='time>='.$start_stamp.' AND time<='.$end_time_stamp;
        return $this->fetch_one('integral_log','IFNULL(SUM(integral),0)',$where);
    }

    /**
     * 获取用户指定时间段指定用户的积分日志详情
     * @param $start_stamp
     * @param $end_time_stamp
     * @param $uid
     * @return array
     */
    public function list_integral_in_time($start_stamp,$end_time_stamp, $uid){
        $where='time BETWEEN '.$start_stamp.' AND '.$end_time_stamp . 'AND uid = ' . $uid ;
        return $this->fetch_all('integral_log', $where);
    }

    /**
     * 计算满足条件的用户积分数据
     * @return array
     * @throws Exception
     */
    public function special_sum_integral_in_time() {
        $sql='SELECT uid,IFNULL(SUM(integral),0) total FROM '.$this->get_table('integral_log').' WHERE time>= 1522512000 AND has_distribute = 0 GROUP BY uid HAVING IFNULL(SUM(integral),0)>0 ';
        return $this->query_all($sql);
    }

    /**
     * 在某个时间段内所有人积分数据
     * @param $start_stamp 开始时间
     * @param $end_time_stamp 结束时间
     * @return 所有人积分数据
     */
    public function user_integral_in_time($start_stamp,$end_time_stamp){
        $sql='SELECT uid,IFNULL(SUM(integral),0) total FROM '.
            $this->get_table('integral_log').' WHERE time>='.
            $start_stamp.' AND time<='.$end_time_stamp.
            ' AND uid IN( SELECT uid FROM ' .$this->get_table('users').' WHERE forbidden= 0 )'.

            ' GROUP BY uid HAVING IFNULL(SUM(integral),0)>0 ';
        return $this->query_all($sql);
    }
    public function get_integral_id_by_type($item_id,$action,$uid = null){
        if($uid){
            return $this->fetch_one('integral_log','id','action="'.$action.'" AND item_id='.$item_id.' AND uid='.$uid);
        }else{
            return $this->fetch_one('integral_log','id','action="'.$action.'" AND item_id='.$item_id);
        }

    }

    //删除赞踩积分记录
    public function delete_integral_log($action,$time,$uid,$item_id)
    {
        return $this->delete('integral_log', "action = '" . $action."' and time = ".$time." and uid = ".$uid." and item_id = ".$item_id);
    }

    //查询回复赞踩记录
    public function query_vote_list($userName, $type, $startDate, $endDate,$voted_user_name, $page = null, $limit = 10){
        $sql = "SELECT a.* FROM ".$this->get_table('answer_vote') ." as a inner join ".$this->get_table('users')." as b on a.vote_uid = b.uid inner join ".$this->get_table('users')." as c on a.answer_uid = c.uid where 1=1 ";
        if($userName){
            $sql.= " and b.user_name like '%$userName%'";
        }
        if($voted_user_name){
            $sql.= " and c.user_name like '%$voted_user_name%'";
        }
        if($type){
            $sql.= " and a.vote_value= '$type'";
        }
        if($startDate){
            $sql.= " and a.add_time >=".strtotime(base64_decode($startDate));
        }
        if($endDate){
            $sql.= " and a.add_time <=".strtotime(base64_decode($endDate));
        }
        $sql.= " ORDER BY a.add_time DESC LIMIT ". (($page-1)*$limit).",".$limit;

        $post_index = $this->query_all($sql);

        return $post_index;
    }

    public function query_vote_list_count($userName, $type, $startDate, $endDate,$voted_user_name){
        $sql = "SELECT count(1) as cnt FROM ".$this->get_table('answer_vote') ." as a inner join ".$this->get_table('users')." as b on a.vote_uid = b.uid inner join ".$this->get_table('users')." as c on a.answer_uid = c.uid where 1=1 ";
        if($userName){
            $sql.= " and b.user_name like '%$userName%'";
        }
        if($voted_user_name){
            $sql.= " and c.user_name like '%$voted_user_name%'";
        }
        if($type){
            $sql.= " and a.vote_value = '$type'";
        }
        if($startDate){
            $sql.= " and a.add_time >=".strtotime(base64_decode($startDate));
        }
        if($endDate){
            $sql.= " and a.add_time <=".strtotime(base64_decode($endDate));
        }
        $sql.= " ORDER BY a.add_time DESC ";

        $post_index = $this->query_all($sql);

        return $post_index[0]['cnt'];
    }

}