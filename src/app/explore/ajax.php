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

class ajax extends AWS_CONTROLLER
{
    public $per_page;
	public function get_access_rule()
	{
		$rule_action['rule_type'] = 'white';

		if ($this->user_info['permission']['visit_explore'])
		{
			$rule_action['actions'][] = 'list';
			$rule_action['actions'][] = 'site_announce_detail';
            $rule_action['actions'][] = 'site_announce_list';
            $rule_action['actions'][] = 'site_announce_list_model';
		}

		return $rule_action;
	}
    public function setup()
    {
        if (get_setting('index_per_page'))
        {
            $this->per_page = get_setting('index_per_page');
        }

        HTTP::no_cache_header();
    }

	public function list_action()
	{
		if ($_GET['feature_id'])
		{
			$topic_ids = $this->model('feature')->get_topics_by_feature_id($_GET['feature_id']);
		}
		else
		{
			$topic_ids = explode(',', $_GET['topic_id']);
		}

		if ($_GET['per_page'])
		{
			$per_page = intval($_GET['per_page']);
		}
		else
		{
			$per_page = get_setting('contents_per_page');
		}

		if ($_GET['sort_type'] == 'hot')
		{
			$posts_list = $this->model('posts')->get_hot_posts($_GET['post_type'], $_GET['category'], $topic_ids, $_GET['day'], $_GET['page'], $per_page);
		}
		else
		{
			$posts_list = $this->model('posts')->get_posts_list($_GET['post_type'], $_GET['page'], $per_page, $_GET['sort_type'], $topic_ids, $_GET['category'], $_GET['answer_count'], $_GET['day'], $_GET['is_recommend']);
		}

		if (!is_mobile() AND $posts_list)
		{
			foreach ($posts_list AS $key => $val)
			{
				if ($val['answer_count'])
				{
					$posts_list[$key]['answer_users'] = $this->model('question')->get_answer_users_by_question_id($val['question_id'], 2, $val['published_uid']);
				}
			}
		}

		TPL::assign('posts_list', $posts_list);

		if (is_mobile())
		{
			TPL::output('m/ajax/explore_list');
		}
		else
		{
			TPL::output('explore/ajax/list');
		}
	}

    public function site_announce_detail_action()
    {
        // 边栏热门用户
        if (TPL::is_output('block/sidebar_hot_users.tpl.htm', 'explore/index'))
        {
            TPL::assign('sidebar_hot_users', $this->model('module')->sidebar_hot_users($this->user_id, 5));
        }

        // 边栏热门话题
        if (TPL::is_output('block/sidebar_hot_topics.tpl.htm', 'explore/index'))
        {
            TPL::assign('sidebar_hot_topics', $this->model('module')->sidebar_hot_topics($category_info['id']));
        }
        if ($site_detail = $this->model('help')->query_announce_by_id($_GET["id"]))
        {
            TPL::assign('site_detail', $site_detail);
        }
        if($announce_list=$this->model('help')->fetch_all('site_announce','status = 0','time DESC','3')){
            TPL::assign('announce_list', $announce_list);
        }
        if($link_list=$this->model('help')->fetch_all('site_link',null,'sort ASC,id ASC')){
            TPL::assign('link_list', $link_list);
        }
        TPL::output('admin/announce/ajax/site_announce_details');
    }

    public function site_announce_list_action()
    {

        // 边栏热门用户
        if (TPL::is_output('block/sidebar_hot_users.tpl.htm', 'explore/index'))
        {
            TPL::assign('sidebar_hot_users', $this->model('module')->sidebar_hot_users($this->user_id, 5));
        }

        // 边栏热门话题
        if (TPL::is_output('block/sidebar_hot_topics.tpl.htm', 'explore/index'))
        {
            TPL::assign('sidebar_hot_topics', $this->model('module')->sidebar_hot_topics($category_info['id']));
        }
        if ($site_detail = $this->model('help')->query_announce_by_id($_GET["id"]))
        {
            TPL::assign('site_detail', $site_detail);
        }
        if($announce_list=$this->model('help')->fetch_all('site_announce','status = 0','time DESC','3')){
            TPL::assign('announce_list', $announce_list);
        }
        if($link_list=$this->model('help')->fetch_all('site_link',null,'sort ASC,id ASC')){
            TPL::assign('link_list', $link_list);
        }
        if($announce_lists = $this->model('help')->query_site_announce(intval($_GET['page']) * $this->per_page .', '. $this->per_page)){
            TPL::assign('announce_lists',$announce_lists);
        }
        TPL::output('admin/announce/ajax/site_announce_lists');
    }

    public function site_announce_list_model_action()
    {
        if($announce_list = $this->model('help')->query_site_announce(intval($_GET['page']) * $this->per_page .', '. $this->per_page)){
            TPL::assign('announce_list',$announce_list);
        }
        TPL::output('admin/announce/ajax/site_announce_listmodel');
    }
}