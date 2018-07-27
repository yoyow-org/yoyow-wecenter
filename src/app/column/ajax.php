<?php
/*
+--------------------------------------------------------------------------
|   WeCenter [#RELEASE_VERSION#]
|   ========================================
|   by Tatfook Network Team
|   © 2011 - 2014 WeCenter. All Rights Reserved
|   http://www.wecenter.com
|   ========================================
|   Support: WeCenter@qq.com
|
+---------------------------------------------------------------------------
*/

define('IN_AJAX', TRUE);

if (!defined('IN_ANWSION'))
{
    die;
}

class ajax extends AWS_CONTROLLER
{
    public function get_access_rule()
    {
        $rule_action['rule_type'] = 'white';

        $rule_action['actions'] = array(
            'list'
        );

        return $rule_action;
    }

    public function setup()
    {
        HTTP::no_cache_header();
    }

    public function column_list_content_action()
    {
        //专栏部分
        $current_page = $_POST['page'] ? $_POST['page'] : 1;
        $type = $_POST['type'];
        $column_sort = $_POST['column_sort'] ? $_POST['column_sort'] : 'column_score';

        switch ($column_sort)
        {
            case 'column_hot': //浏览量
                $order = 'ORDER BY all_views DESC';
                break;

            case 'column_new': //注册时间
                $order = 'ORDER BY reg_time DESC';
                break;

            case 'column_score': //用户文章得分
                $order = 'ORDER BY score DESC';
                break;
        }

        $dbhj = AWS_APP::config()->get('database')->prefix; //获取表名前缀
        $sql = 'SELECT a.uid,a.user_name,a.avatar_file,a.fans_count,a.article_count,a.verified,all_votes,a.score,a.reg_time,all_views,c.all_coin FROM '.$dbhj.'users AS a LEFT JOIN(SELECT uid,sum(votes) AS all_votes,sum(views) AS all_views FROM  '.$dbhj.'article GROUP BY uid) AS b ON a.uid=b.uid LEFT JOIN (SELECT uid,sum(coin) AS all_coin FROM  '.$dbhj.'user_yoyow_coin GROUP BY uid) AS c ON c.uid = a.uid WHERE a.article_count > 0 '.$order ;

        $sql_count = 'SELECT count(*) FROM  '.$dbhj.'users AS a LEFT JOIN(SELECT uid,sum(votes) AS all_votes,sum(views) AS all_views FROM  '.$dbhj.'article GROUP BY uid) AS b ON a.uid=b.uid LEFT JOIN (SELECT uid,sum(coin) AS all_coin FROM  '.$dbhj.'user_yoyow_coin GROUP BY uid) AS c ON c.uid = a.uid WHERE a.article_count > 0 '.$order ;

        $limit = 14;
        $user_lists_count = $this->model('account')->get_user_lists_count_for_column($sql_count);
        $all_page = intval(ceil( $user_lists_count['count(*)'] / $limit));

        if($type == 'sort'){
            $next_page = 1;
            $offset = 0;
        }else{
            $next_page = $current_page < $all_page ? $current_page + 1 : 1;//如果数据取到最后一页，下一次则从第一页重新开始
            $offset = ($next_page - 1) * $limit;
        }
        $user_lists = $this->model('account')->get_user_lists_for_column($sql,$limit,$offset);

        $fans_id = $this->user_id ? $this->user_id : null;

        $friend_uids = $this->model('follow')->get_user_friends_ids($fans_id);

        TPL::assign('user_lists', $user_lists);
        TPL::assign('user_lists_count', $user_lists_count);
        TPL::assign('next_page', $next_page);
        TPL::assign('column_sort', $column_sort);
        TPL::assign('friend_uids', $friend_uids);

        TPL::output('column/ajax/column_list_content');
    }

    public function article_list_content_action()
    {
        $current_page = $_POST['page'] ? $_POST['page'] : 1;
        $type = $_POST['type'];
        $article_sort = $_POST['article_sort'] ? $_POST['article_sort'] : 'article_new';

        $dbhj = AWS_APP::config()->get('database')->prefix; //获取表名前缀
        $sql = "";
        switch ($article_sort)
        {
            case 'article_new': //注册时间
                $sql = 'SELECT a.id,a.title,a.add_time,a.votes,a.cover_file,u.user_name FROM '.$dbhj.'article AS a LEFT JOIN '.$dbhj.'users AS u ON a.uid = u.uid ORDER BY add_time DESC';
                break;

            case 'article_hot': //浏览量 和 点赞数 各占50%
                $sql = 'SELECT a.id,a.title,a.add_time,a.votes,a.cover_file,(views+votes)/2 num ,u.user_name FROM '.$dbhj.'article AS a LEFT JOIN '.$dbhj.'users AS u ON a.uid = u.uid ORDER BY num DESC';
                break;
        }

        $limit = 9;
        $article_count = $this->model("article")->get_articles_list() ?  count($this->model("article")->get_articles_list()) : 0;
        $all_page = ceil($article_count / $limit);
        if($type == 'sort'){
            $next_page = 1;
            $offset = 0;
        }else{
            $next_page = $current_page < $all_page  ? $current_page + 1 : 1;//如果数据取到最后一页，下一次则从第一页重新开始
            $offset = ($next_page - 1) * $limit;
        }

        $article_lists = $this->model("article")->get_article_lists_for_column($sql,$limit,$offset);

        TPL::assign('article_lists', $article_lists);
        TPL::assign('next_page', $next_page);
        TPL::assign('article_sort', $article_sort);

        TPL::output('column/ajax/article_list_content');
    }

}