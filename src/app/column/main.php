    <?php
/**
 * Created by PhpStorm.
 * User: 神游界外
 * Date: 2018/4/3
 * Time: 11:27
 */


if (!defined('IN_ANWSION'))
{
    die;
}

class main extends AWS_CONTROLLER
{
    public function get_access_rule()
    {
        $rule_action['rule_type'] = 'white';

        if ($this->user_info['permission']['visit_question'] AND $this->user_info['permission']['visit_site'])
        {
            $rule_action['actions'][] = 'detail';
            $rule_action['actions'][] = 'index';
        }

        return $rule_action;
    }

    public function index_action()
    {

        $this->crumb(AWS_APP::lang()->_t('专栏'), '/column/');

        //专栏部分
        $column_sort = 'column_score'; //默认得分排序
        $order = 'ORDER BY score DESC';

        $dbhj=AWS_APP::config()->get('database')->prefix; //获取表名前缀
        $sql = 'SELECT a.uid,a.user_name,a.avatar_file,a.fans_count,a.article_count,a.verified,all_votes,a.score,a.reg_time,all_views,c.all_coin FROM '.$dbhj.'users AS a LEFT JOIN(SELECT uid,sum(votes) AS all_votes,sum(views) AS all_views FROM  '.$dbhj.'article GROUP BY uid) AS b ON a.uid=b.uid LEFT JOIN (SELECT uid,sum(coin) AS all_coin FROM  '.$dbhj.'user_yoyow_coin GROUP BY uid) AS c ON c.uid = a.uid WHERE a.article_count > 0 '.$order ;

        $sql_count = 'SELECT count(*) FROM  '.$dbhj.'users AS a LEFT JOIN(SELECT uid,sum(votes) AS all_votes,sum(views) AS all_views FROM  '.$dbhj.'article GROUP BY uid) AS b ON a.uid=b.uid LEFT JOIN (SELECT uid,sum(coin) AS all_coin FROM  '.$dbhj.'user_yoyow_coin GROUP BY uid) AS c ON c.uid = a.uid WHERE a.article_count > 0 '.$order ;

        $limit = 14;
        $offset = 0;
        $user_lists_count = $this->model('account')->get_user_lists_count_for_column($sql_count);

        $user_lists = $this->model('account')->get_user_lists_for_column($sql,$limit,$offset);

        $fans_id = $this->user_id ? $this->user_id : null;

        $friend_uids = $this->model('follow')->get_user_friends_ids($fans_id);

        //文章部分
        $limit2 = 9;
        $offset = 0;
        $dbhj = AWS_APP::config()->get('database')->prefix; //获取表名前缀
        $sql = 'SELECT a.id,a.title,a.add_time,a.votes,a.cover_file,u.user_name FROM '.$dbhj.'article AS a LEFT JOIN '.$dbhj.'users AS u ON a.uid = u.uid ORDER BY add_time DESC';
        $article_lists = $this->model("article")->get_article_lists_for_column($sql,$limit2,$offset);


        //推荐作家部分
        $recommend_user_views = array();
        $dbhj = AWS_APP::config()->get('database')->prefix; //获取表名前缀
        $sql = 'SELECT c.uid,a.user_name,a.avatar_file,a.article_count,a.verified,all_views,a.score FROM '.$dbhj.'column as c LEFT JOIN '.$dbhj.'users AS a on c.uid = a.uid LEFT JOIN (SELECT uid,sum(views) AS all_views FROM '.$dbhj.'article GROUP BY uid) AS b ON c.uid=b.uid WHERE c.is_recommend = 1 ORDER BY a.score DESC';
        $recomend_lists = $this->model('column')->get_recommend_user_lists_for_column($sql);

        TPL::assign('user_lists', $user_lists);
        TPL::assign('user_lists_count', $user_lists_count);
        TPL::assign('column_sort', $column_sort);
        TPL::assign('friend_uids', $friend_uids);

        TPL::assign('article_lists', $article_lists);

        TPL::assign('recomend_lists', $recomend_lists);
        TPL::assign('recommend_user_views', $recommend_user_views);

        TPL::output('column/index');
    }

    public function detail_action()
    {

        $this->crumb(AWS_APP::lang()->_t('专栏'), '/column/');

        $uid = intval($_GET['uid']);
        $user = $this->model('account')->get_user_info_by_uid($uid);

        //所有文章列表
        $article_lists = $this->model('article')->get_article_lists_by_uid($_GET['page'],get_setting('contents_per_page'), 'add_time DESC', $uid);

        $article_list_total = $this->model('article')->column_article_list_total;

        $dbhj = AWS_APP::config()->get('database')->prefix; //获取表名前缀
        $sql = "SELECT uid,sum(views) as all_views FROM  ".$dbhj."article WHERE uid = ".$uid ." GROUP BY uid";
        $user_views = $this->model('article')->get_user_all_views($sql);

        $fans_id = $this->user_id ? $this->user_id : null;

        //热门文章
        $limit = 9;
        $hot_articles_list = $this->model('article')->get_hot_article_by_uid($uid,$limit);


        TPL::assign('user_follow_check', $this->model('follow')->user_follow_check($fans_id, $uid));

        TPL::assign('pagination', AWS_APP::pagination()->initialize(array(
            'base_url' => get_js_url('/column/detail/uid-' .$uid),
            'total_rows' => $article_list_total,
            'per_page' => get_setting('contents_per_page')
        ))->create_links());

        TPL::assign('user', $user);
        TPL::assign('user_views', $user_views);
        TPL::assign('article_lists', $article_lists);
        TPL::assign('hot_articles_list', $hot_articles_list);

        TPL::output('column/detail');
    }
}