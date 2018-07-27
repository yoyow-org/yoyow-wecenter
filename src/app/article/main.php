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

class main extends AWS_CONTROLLER
{
	public function get_access_rule()
	{
		$rule_action['rule_type'] = 'white';

		if ($this->user_info['permission']['visit_question'] AND $this->user_info['permission']['visit_site'])
		{
			$rule_action['actions'][] = 'square';
			$rule_action['actions'][] = 'index';
		}

		return $rule_action;
	}

	public function index_action()
	{   

		if ($_GET['notification_id'])
		{
			$this->model('notify')->read_notification($_GET['notification_id'], $this->user_id);
		}

		if (is_mobile())
		{
			HTTP::redirect('/m/article/' . $_GET['id']);
		}

		if (! $article_info = $this->model('article')->get_article_info_by_id($_GET['id']))
		{
			HTTP::error_404();
		}

		if ($article_info['has_attach'])
		{
			$article_info['attachs'] = $this->model('publish')->get_attach('article', $article_info['id'], 'min');

			$article_info['attachs_ids'] = FORMAT::parse_attachs($article_info['message'], true);
		}

		$article_info['user_info'] = $this->model('account')->get_user_info_by_uid($article_info['uid'], true);

		$article_info['message'] = $this->model('question')->parse_at_user(FORMAT::parse_attachs(nl2br(FORMAT::parse_bbcode($article_info['message']))));

		if ($this->user_id)
		{
			$article_info['vote_info'] = $this->model('article')->get_article_vote_by_id('article', $article_info['id'], null, $this->user_id);
		}
		$article_info['vote_users'] = $this->model('article')->get_article_vote_users_by_id('article', $article_info['id'], 1, 10);
		$article_info['against_count']= $this->model('article')->get_article_against_vote('article',$article_info['id']);
		TPL::assign('article_info', $article_info);

		$article_topics = $this->model('topic')->get_topics_by_item_id($article_info['id'], 'article');

		if ($article_topics)
		{
			TPL::assign('article_topics', $article_topics);

			foreach ($article_topics AS $topic_info)
			{
				$article_topic_ids[] = $topic_info['topic_id'];
			}
		}

		TPL::assign('reputation_topics', $this->model('people')->get_user_reputation_topic($article_info['user_info']['uid'], $user['reputation'], 5));

		$this->crumb($article_info['title'], '/article/' . $article_info['id']);

		TPL::assign('human_valid', human_valid('answer_valid_hour'));

		if ($_GET['item_id'])
		{
			$comments[] = $this->model('article')->get_comment_by_id($_GET['item_id']);
		}
		else
		{
			$comments = $this->model('article')->get_comments($article_info['id'], $_GET['page'], 100);
		}

		if ($comments AND $this->user_id)
		{
			foreach ($comments AS $key => $val)
			{
				$comments[$key]['vote_info'] = $this->model('article')->get_article_vote_by_id('comment', $val['id'], 1, $this->user_id);
				$comments[$key]['message'] = $this->model('question')->parse_at_user($val['message']);
                $integral_id=$this->model('integral')->get_integral_id_by_type($val['id'],"DISCUSS_ARTICLE");
                $yoyow_article_comment_income=$this->model('assigntask')->get_integral_yoyow_by_integral_id($integral_id);
                if($yoyow_article_comment_income=="无积分记录Id" || !$yoyow_article_comment_income){
                    $yoyow_article_comment_income=0;
                }
                $comments[$key]['yoyow_article_comment_income'] = $yoyow_article_comment_income*((get_setting('yoyow_rmb_rate')=='') ? 0: get_setting('yoyow_rmb_rate'));
			}
		}

		if ($this->user_id)
		{
			TPL::assign('user_follow_check', $this->model('follow')->user_follow_check($this->user_id, $article_info['uid']));
		}

		TPL::assign('question_related_list', $this->model('question')->get_related_question_list(null, $article_info['title']));

		$this->model('article')->update_views($article_info['id']);

		TPL::assign('comments', $comments);
		TPL::assign('comments_count', $article_info['comments']);

		TPL::assign('human_valid', human_valid('answer_valid_hour'));

		TPL::assign('pagination', AWS_APP::pagination()->initialize(array(
			'base_url' => get_js_url('/article/id-' . $article_info['id']),
			'total_rows' => $article_info['comments'],
			'per_page' => 100
		))->create_links());

		TPL::set_meta('keywords', implode(',', $this->model('system')->analysis_keyword($article_info['title'])));

		TPL::set_meta('description', $article_info['title'] . ' - ' . cjk_substr(str_replace("\r\n", ' ', strip_tags($article_info['message'])), 0, 128, 'UTF-8', '...'));

		TPL::assign('attach_access_key', md5($this->user_id . time()));

		$recommend_posts = $this->model('posts')->get_recommend_posts_by_topic_ids($article_topic_ids);

		if ($recommend_posts)
		{
			foreach ($recommend_posts as $key => $value)
			{
				if ($value['id'] AND $value['id'] == $article_info['id'])
				{
					unset($recommend_posts[$key]);

					break;
				}
			}

			TPL::assign('recommend_posts', $recommend_posts);
		}
        //计算点赞的积分
        $praise_oppose_list=$this->model('integral')->fetch_all('article_vote','item_id = '.$article_info['id'].' AND type="article"');
        $praise_oppose_integrals = 0;
        foreach ($praise_oppose_list AS $ke=>$vl){
            if($int_id = $this->model('integral')->fetch_one('integral_log','id','action = "'.($vl['rating'] == 1 ? 'ARTICLE_PRAISE':'ARTICLE_OPPOSE').'" and uid ='.$vl['item_uid'].' and item_id ='.$vl['uid'].' and time ='.$vl['time'])){
                $praise_oppose_integral = $this->model('assigntask')->get_integral_yoyow_by_integral_id($int_id);
                $praise_oppose_integrals += $praise_oppose_integral;
            }
        }
        //计算文章下回复的奖励
        $article_incomes = 0;
        $article_answer_list = $this->model('article')->fetch_all('integral_log','action = "ARTICLE_DISCUSS" AND note = "文章被评论 #'.$article_info['id'].'"');
        foreach($article_answer_list AS $ks=>$vs){
            $article_income = $this->model('assigntask')->get_integral_yoyow_by_integral_id($vs['id']);
            $article_incomes += $article_income;
        }
        $integral_id=$this->model('integral')->get_integral_id_by_type($article_info['id'],"NEW_ARTICLE");
        $article_new_income=$this->model('assigntask')->get_integral_yoyow_by_integral_id($integral_id);
        if($article_new_income=="无积分记录Id" || !$article_new_income){
            $article_new_income=0;
        }
        $yoyow_income = $article_incomes + $article_new_income + $praise_oppose_integrals;
        TPL::assign('yoyow_article_income',$yoyow_income*((get_setting('yoyow_rmb_rate')=='') ? 0: get_setting('yoyow_rmb_rate')));

		TPL::output('article/index');
	}

	public function index_square_action()
	{   
		if (is_mobile())
		{
			HTTP::redirect('/m/article/');
		}

		$this->crumb(AWS_APP::lang()->_t('文章'), '/article/');

		if ($_GET['category'])
		{
			if (is_digits($_GET['category']))
			{
				$category_info = $this->model('system')->get_category_info($_GET['category']);
			}
			else
			{
				$category_info = $this->model('system')->get_category_info_by_url_token($_GET['category']);
			}
		}

		if ($_GET['feature_id'])
		{
			$article_list = $this->model('article')->get_articles_list_by_topic_ids($_GET['page'], get_setting('contents_per_page'), 'is_top DESC,set_top_time DESC,add_time DESC', $this->model('feature')->get_topics_by_feature_id($_GET['feature_id']));

			$article_list_total = $this->model('article')->article_list_total;

			if ($feature_info = $this->model('feature')->get_feature_by_id($_GET['feature_id']))
			{
				$this->crumb($feature_info['title'], '/article/feature_id-' . $feature_info['id']);

				TPL::assign('feature_info', $feature_info);
			}
		}
		else
		{
			$article_list = $this->model('article')->get_articles_list($category_info['id'], $_GET['page'], get_setting('contents_per_page'), 'is_top DESC,set_top_time DESC,add_time DESC');

			$article_list_total = $this->model('article')->found_rows();
		}

		if ($article_list)
		{
			foreach ($article_list AS $key => $val)
			{
				$article_ids[] = $val['id'];

				$article_uids[$val['uid']] = $val['uid'];
			}

			$article_topics = $this->model('topic')->get_topics_by_item_ids($article_ids, 'article');
			$article_users_info = $this->model('account')->get_user_info_by_uids($article_uids);

			foreach ($article_list AS $key => $val)
			{
				$article_list[$key]['user_info'] = $article_users_info[$val['uid']];
				$article_list[$key]['cover_file'] = get_setting('upload_url') . '/article/cover/'.$val['cover_file'];

                //计算点赞的积分
                $praise_oppose_list=$this->model('integral')->fetch_all('article_vote','item_id = '.$val['id'].' AND type="article"');
                $praise_oppose_integrals = 0;
                foreach ($praise_oppose_list AS $ke=>$vl){
                    if($int_id = $this->model('integral')->fetch_one('integral_log','id','action = "'.($vl['rating'] == 1 ? 'ARTICLE_PRAISE':'ARTICLE_OPPOSE').'" and uid ='.$vl['item_uid'].' and item_id ='.$vl['uid'].' and time ='.$vl['time'])){
                        $praise_oppose_integral = $this->model('assigntask')->get_integral_yoyow_by_integral_id($int_id);
                        $praise_oppose_integrals += $praise_oppose_integral;
                    }
                }
                //计算文章下回复的奖励
                $article_incomes = 0;
                $article_integral_list = $this->model('article')->fetch_all('integral_log','action = "ARTICLE_DISCUSS" AND note = "文章被评论 #'.$val['id'].'"');
                if ($article_integral_list){
                    foreach($article_integral_list AS $ks=>$vs){
                        $article_income = $this->model('assigntask')->get_integral_yoyow_by_integral_id($vs['id']);
                        $article_incomes += $article_income;
                    }
                }
                $integral_id=$this->model('integral')->get_integral_id_by_type($val['id'],"NEW_ARTICLE");
                $article_new_income=$this->model('assigntask')->get_integral_yoyow_by_integral_id($integral_id);
                if($article_new_income=="无积分记录Id" || !$article_new_income){
                    $article_new_income=0;
                }
                $yoyow_article_income = $article_incomes + $article_new_income + $praise_oppose_integrals;
                $article_list[$key]['yoyow_article_income'] = $yoyow_article_income*((get_setting('yoyow_rmb_rate')=='') ? 0: get_setting('yoyow_rmb_rate'));
			}
		}

		// 导航
		if (TPL::is_output('block/content_nav_menu.tpl.htm', 'article/square'))
		{
			TPL::assign('content_nav_menu', $this->model('menu')->get_nav_menu_list('article'));
		}

		//边栏热门话题
		if (TPL::is_output('block/sidebar_hot_topics.tpl.htm', 'article/square'))
		{   

			TPL::assign('sidebar_hot_topics', $this->model('module')->sidebar_hot_topics($category_info['id']));
		}

		if ($category_info)
		{
			TPL::assign('category_info', $category_info);

			$this->crumb($category_info['title'], '/article/category-' . $category_info['id']);

			$meta_description = $category_info['title'];

			if ($category_info['description'])
			{
				$meta_description .= ' - ' . $category_info['description'];
			}

			TPL::set_meta('description', $meta_description);
		}

		TPL::assign('article_list', $article_list);
		TPL::assign('article_topics', $article_topics);
		TPL::assign('hot_articles', $this->model('article')->get_articles_list(null, 1, 10, 'votes DESC', 30));
        
			
		TPL::assign('pagination', AWS_APP::pagination()->initialize(array(
			'base_url' => get_js_url('/article/category_id-' . $_GET['category_id'] . '__feature_id-' . $_GET['feature_id']),
			'total_rows' => $article_list_total,
			'per_page' => get_setting('contents_per_page')
		))->create_links());

		TPL::output('article/square');
	}
}
