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

class article_class extends AWS_MODEL
{
	public function get_article_info_by_id($article_id)
	{
		if (!is_digits($article_id))
		{
			return false;
		}

		static $articles;

		if (!$articles[$article_id])
		{
			$articles[$article_id] = $this->fetch_row('article', 'id = ' . $article_id);
		}

		return $articles[$article_id];
	}

	public function get_article_info_by_ids($article_ids)
	{
		if (!is_array($article_ids) OR sizeof($article_ids) == 0)
		{
			return false;
		}

		array_walk_recursive($article_ids, 'intval_string');

		if ($articles_list = $this->fetch_all('article', 'id IN(' . implode(',', $article_ids) . ')'))
		{
			foreach ($articles_list AS $key => $val)
			{
				$result[$val['id']] = $val;
			}
		}

		return $result;
	}

    public function get_comment_by_uid_and_article_id_and_atuid($uid,$article_id,$at_uid)
    {
        $comment = $this->fetch_one('article_comments', 'message','uid = ' . intval($uid).' AND article_id = ' . intval($article_id).  ' AND at_uid = ' . intval($at_uid), 'add_time DESC');

        return $comment;
    }

    public function get_last_article_by_uid($uid)
    {
        $article = $this->fetch_row('article','uid = ' . intval($uid), 'add_time DESC');

        return $article;
    }

	public function get_comment_by_id($comment_id)
	{
		if ($comment = $this->fetch_row('article_comments', 'id = ' . intval($comment_id)))
		{
			$comment_user_infos = $this->model('account')->get_user_info_by_uids(array(
				$comment['uid'],
				$comment['at_uid']
			));

			$comment['user_info'] = $comment_user_infos[$comment['uid']];
			$comment['at_user_info'] = $comment_user_infos[$comment['at_uid']];
		}

		return $comment;
	}

	public function get_comments_by_ids($comment_ids)
	{
		if (!is_array($comment_ids) OR !$comment_ids)
		{
			return false;
		}

		array_walk_recursive($comment_ids, 'intval_string');

		if ($comments = $this->fetch_all('article_comments', 'id IN (' . implode(',', $comment_ids) . ')'))
		{
			foreach ($comments AS $key => $val)
			{
				$article_comments[$val['id']] = $val;
			}
		}

		return $article_comments;
	}
    public function get_comments_new_by_ids($comment_ids)
    {
        if (!is_array($comment_ids) OR !$comment_ids)
        {
            return false;
        }
        $prefix=AWS_APP::config()->get('database')->prefix;
        array_walk_recursive($comment_ids, 'intval_string');
        $sql="select * from ".get_table('article_comments')."  a inner join 
                (
                select 
                max(add_time) as add_time,
                article_id
                from 
                ".get_table('article_comments')."  
                where article_id in (" . implode(',', $comment_ids) . ") 
                group by 
                article_id
                ) b on a.article_id = b.article_id and a.add_time = b.add_time";
        if ($comments = $this->query_all($sql))
        {
            foreach ($comments AS $key => $val)
            {
                $article_comments[$val['article_id']] = $val;
            }
        }
        return $article_comments;
    }

	public function get_comments($article_id, $page, $per_page)
	{
		if ($comments = $this->fetch_page('article_comments', 'article_id = ' . intval($article_id), 'add_time ASC', $page, $per_page))
		{
			foreach ($comments AS $key => $val)
			{
				$comment_uids[$val['uid']] = $val['uid'];

				if ($val['at_uid'])
				{
					$comment_uids[$val['at_uid']] = $val['at_uid'];
				}
			}

			if ($comment_uids)
			{
				$comment_user_infos = $this->model('account')->get_user_info_by_uids($comment_uids);
			}

			foreach ($comments AS $key => $val)
			{
				$comments[$key]['user_info'] = $comment_user_infos[$val['uid']];
				$comments[$key]['at_user_info'] = $comment_user_infos[$val['at_uid']];
			}
		}

		return $comments;
	}

	public function remove_article($article_id,$uid)
	{
		if (!$article_info = $this->get_article_info_by_id($article_id))
		{
			return false;
		}

		$this->delete('article_comments', "article_id = " . intval($article_id)); // 删除关联的回复内容

		$this->delete('topic_relation', "`type` = 'article' AND item_id = " . intval($article_id));		// 删除话题关联

		ACTION_LOG::delete_action_history('associate_type = ' . ACTION_LOG::CATEGORY_QUESTION . ' AND associate_action IN(' . ACTION_LOG::ADD_ARTICLE . ', ' . ACTION_LOG::ADD_AGREE_ARTICLE . ', ' . ACTION_LOG::ADD_COMMENT_ARTICLE . ') AND associate_id = ' . intval($article_id));	// 删除动作

		// 删除附件
		if ($attachs = $this->model('publish')->get_attach('article', $article_id))
		{
			foreach ($attachs as $key => $val)
			{
				$this->model('publish')->remove_attach($val['id'], $val['access_key']);
			}
		}

		$this->model('notify')->delete_notify('model_type = 8 AND source_id = ' . intval($article_id));	// 删除相关的通知

		$this->model('posts')->remove_posts_index($article_id, 'article');

		$this->shutdown_update('users', array(
			'article_count' => $this->count('article', 'uid = ' . intval($uid))
		), 'uid = ' . intval($uid));

        if(get_setting('integral_system_config_article_change_source')=="Y") {
            $this->model('integral')->process(intval($uid), 'DELETE_ARTICLE', get_setting('integral_system_config_article_delete'), '删除文章 #' . $article_id, $article_id);
        }

		return $this->delete('article', 'id = ' . intval($article_id));
	}

	public function remove_comment($comment_id)
	{
		$comment_info = $this->get_comment_by_id($comment_id);

		if (!$comment_info)
		{
			return false;
		}

		$this->delete('article_comments', 'id = ' . $comment_info['id']);

		$this->update('article', array(
			'comments' => $this->count('article_comments', 'article_id = ' . $comment_info['article_id'])
		), 'id = ' . $comment_info['article_id']);

		return true;
	}

	public function update_article($article_id, $uid, $title, $message, $topics, $category_id, $create_topic)
	{
		if (!$article_info = $this->model('article')->get_article_info_by_id($article_id))
		{
			return false;
		}

		$this->delete('topic_relation', 'item_id = ' . intval($article_id) . " AND `type` = 'article'");

		if (is_array($topics))
		{
			foreach ($topics as $key => $topic_title)
			{
				$topic_id = $this->model('topic')->save_topic($topic_title, $uid, $create_topic);

				$this->model('topic')->save_topic_relation($uid, $topic_id, $article_id, 'article');
			}
		}

		$this->model('search_fulltext')->push_index('article', htmlspecialchars($title), $article_info['id']);

		$this->update('article', array(
			'title' => htmlspecialchars($title),
			'message' => htmlspecialchars($message),
			'category_id' => intval($category_id)
		), 'id = ' . intval($article_id));

		$this->model('posts')->set_posts_index($article_id, 'article');

		return true;
	}

	public function get_articles_list($category_id, $page, $per_page, $order_by, $day = null)
	{
		$where = array();

		if ($category_id)
		{
			$where[] = 'category_id = ' . intval($category_id);
		}

		if ($day)
		{
			$where[] = 'add_time > ' . (time() - $day * 24 * 60 * 60);
		}

		return $this->fetch_page('article', implode(' AND ', $where), $order_by, $page, $per_page);
	}

	public function get_articles_list_by_topic_ids($page, $per_page, $order_by, $topic_ids)
	{
		if (!$topic_ids)
		{
			return false;
		}

		if (!is_array($topic_ids))
		{
			$topic_ids = array(
				$topic_ids
			);
		}

		array_walk_recursive($topic_ids, 'intval_string');

		$result_cache_key = 'article_list_by_topic_ids_' . md5(implode('_', $topic_ids) . $order_by . $page . $per_page);

		$found_rows_cache_key = 'article_list_by_topic_ids_found_rows_' . md5(implode('_', $topic_ids) . $order_by . $page . $per_page);

		if (!$result = AWS_APP::cache()->get($result_cache_key) OR $found_rows = AWS_APP::cache()->get($found_rows_cache_key))
		{
			$topic_relation_where[] = '`topic_id` IN(' . implode(',', $topic_ids) . ')';
			$topic_relation_where[] = "`type` = 'article'";

			if ($topic_relation_query = $this->query_all("SELECT item_id FROM " . get_table('topic_relation') . " WHERE " . implode(' AND ', $topic_relation_where)))
			{
				foreach ($topic_relation_query AS $key => $val)
				{
					$article_ids[$val['item_id']] = $val['item_id'];
				}
			}

			if (!$article_ids)
			{
				return false;
			}

			$where[] = "id IN (" . implode(',', $article_ids) . ")";
		}


		if (!$result)
		{
			$result = $this->fetch_page('article', implode(' AND ', $where), $order_by, $page, $per_page);

			AWS_APP::cache()->set($result_cache_key, $result, get_setting('cache_level_high'));
		}


		if (!$found_rows)
		{
			$found_rows = $this->found_rows();

			AWS_APP::cache()->set($found_rows_cache_key, $found_rows, get_setting('cache_level_high'));
		}

		$this->article_list_total = $found_rows;

		return $result;
	}

	public function lock_article($article_id, $lock_status = true)
	{
		return $this->update('article', array(
			'lock' => intval($lock_status)
		), 'id = ' . intval($article_id));
	}

	public function article_vote($type, $item_id, $rating, $uid, $reputation_factor, $item_uid)
	{
		if($type=="article"){
            $integral_log = $this->fetch_row('integral_log', 'uid=' . $item_uid . ' and item_id=' . $uid." and action in ('ARTICLE_PRAISE','ARTICLE_OPPOSE','ARTICLE_CANCEL_PRAISE','ARTICLE_CANCEL_OPPOSE')","id DESC","limit 1");
            $user_info = $this->model('account')->get_user_info_by_uid($uid);
            $weight_balance = $user_info['weight_balance'];
            $praise_no_weight=$user_info['praise_no_weight'];
            $whether_decrease_weight = $user_info['whether_decrease_weight'];
            $cost = get_setting('integral_system_config_praise_oppose_cost');
            $base = get_setting('integral_system_config_article_praise_oppose_base');

            if (!$vote_info = $this->get_article_vote_info($type,$item_id,$item_uid, $uid)) //添加记录
            {
                if ($cost <= $weight_balance) {
                    $action = $rating == 1 ? 'ARTICLE_PRAISE' : 'ARTICLE_OPPOSE';
                    $action_text = $rating == 1 ? '点赞' : '踩';
                    $integral = $rating == 1 ? $praise_no_weight * $base : -$praise_no_weight * $base;
                    $this->model('integral')->process($item_uid, $action,$integral , '用户' . $item_uid . '被' .$uid. $action_text . '获得积分' . $integral.'#'.$item_id, $uid);
                    if($whether_decrease_weight == 1){
                        $this->update('users', ["weight_balance" => $weight_balance - $cost], 'uid=' . $uid);
                    }
                }
            }
            else if ($vote_info['rating'] != $rating && $rating != '0' )
            {
                $last_action = $rating == 1 ? 'ARTICLE_OPPOSE' : 'ARTICLE_PRAISE';
                if ($integral_log and $integral_log['action'] == $last_action) {
                    $action = $rating == -1 ? 'ARTICLE_CANCEL_PRAISE' : 'ARTICLE_CANCEL_OPPOSE';
                    $action_text = $rating == -1 ? '取消点赞' : '取消踩';
                    $integral = -$integral_log['integral'];
                    $this->model('integral')->process($item_uid, $action,$integral , '用户' . $item_uid . '被' .$uid. $action_text . '获得积分' . $integral.'#'.$item_id, $uid);
                }
                if ($cost <= $weight_balance) {
                    $action = $vote_info['rating'] == 1 ? 'ARTICLE_OPPOSE' : 'ARTICLE_PRAISE';
                    $action_text = $vote_info['rating'] == 1 ? '踩' : '点赞';
                    $integral = $vote_info['rating'] == 1 ? -$praise_no_weight * $base : $praise_no_weight * $base;
                    $this->model('integral')->process($item_uid, $action,$integral , '用户' . $item_uid . '被' .$uid. $action_text . '获得积分' . $integral.'#'.$item_id, $uid);
                    if($whether_decrease_weight == 1){
                        $this->update('users', ["weight_balance" => $weight_balance - $cost], 'uid=' . $uid);
                    }
                }
            }
            else {
                $last_action = $vote_info['rating'] == 1 ? 'ARTICLE_PRAISE' : 'ARTICLE_OPPOSE';
                if ($integral_log and $integral_log['action'] == $last_action) {
                    $action = $vote_info['rating'] == 1 ? 'ARTICLE_CANCEL_PRAISE' : 'ARTICLE_CANCEL_OPPOSE';
                    $action_text = $vote_info['rating'] == 1 ? '取消点赞' : '取消踩';
                    $integral = -$integral_log['integral'];
                    $this->model('integral')->process($item_uid, $action, $integral, '用户' . $item_uid . '被' . $uid . $action_text . '获得积分' . $integral . '#' . $item_id, $uid);
                }

            }
        }

        $this->delete('article_vote', "`type` = '" . $this->quote($type) . "' AND item_id = " . intval($item_id) . ' AND uid = ' . intval($uid));
		if ($rating)
		{
			if ($article_vote = $this->fetch_row('article_vote', "`type` = '" . $this->quote($type) . "' AND item_id = " . intval($item_id) . " AND rating = " . intval($rating) . ' AND uid = ' . intval($uid)))
			{
				$this->update('article_vote', array(
					'rating' => intval($rating),
					'time' => time(),
					'reputation_factor' => $reputation_factor
				), 'id = ' . intval($article_vote['id']));
			}
			else
			{
				$this->insert('article_vote', array(
					'type' => $type,
					'item_id' => intval($item_id),
					'rating' => intval($rating),
					'time' => time(),
					'uid' => intval($uid),
					'item_uid' => intval($item_uid),
					'reputation_factor' => $reputation_factor
				));
			}
		}

		switch ($type)
		{
			case 'article':
				$this->update('article', array(
					'votes' => $this->count('article_vote', "`type` = '" . $this->quote($type) . "' AND item_id = " . intval($item_id) . " AND rating = 1")
				), 'id = ' . intval($item_id));

				switch ($rating)
				{
					case 1:
						ACTION_LOG::save_action($uid, $item_id, ACTION_LOG::CATEGORY_QUESTION, ACTION_LOG::ADD_AGREE_ARTICLE);
					break;

					case -1:
						ACTION_LOG::delete_action_history('associate_type = ' . ACTION_LOG::CATEGORY_QUESTION . ' AND associate_action = ' . ACTION_LOG::ADD_AGREE_ARTICLE . ' AND uid = ' . intval($uid) . ' AND associate_id = ' . intval($item_id));
					break;
				}
			break;

			case 'comment':
				$this->update('article_comments', array(
					'votes' => $this->count('article_vote', "`type` = '" . $this->quote($type) . "' AND item_id = " . intval($item_id) . " AND rating = 1")
				), 'id = ' . intval($item_id));
			break;
		}

		$this->model('account')->sum_user_agree_count($item_uid);

		return true;
	}

    public function delete_article_vote($id)
    {
        return $this->delete('article_vote', "id = " . intval($id));
    }
	public function get_article_vote_by_id($type, $item_id, $rating = null, $uid = null)
	{
		if ($article_vote = $this->get_article_vote_by_ids($type, array(
			$item_id
		), $rating, $uid))
		{
			return end($article_vote[$item_id]);
		}
	}

	public function  get_article_vote_info($type,$item_id,$item_uid, $uid){
        $where[] = "`type` = '" . $this->quote($type) . "'";
        $where[] = "item_id =". $item_id;
        $where[] = "item_uid = ".$item_uid;
        $where[] = 'uid = ' . intval($uid);


        return $this->fetch_row('article_vote', implode(' AND ', $where));
    }

	public function get_article_vote_by_ids($type, $item_ids, $rating = null, $uid = null)
	{
		if (!is_array($item_ids))
		{
			return false;
		}

		if (sizeof($item_ids) == 0)
		{
			return false;
		}

		array_walk_recursive($item_ids, 'intval_string');

		$where[] = "`type` = '" . $this->quote($type) . "'";
		$where[] = 'item_id IN(' . implode(',', $item_ids) . ')';

		if ($rating)
		{
			$where[] = 'rating = ' . intval($rating);
		}

		if ($uid)
		{
			$where[] = 'uid = ' . intval($uid);
		}

		if ($article_votes = $this->fetch_all('article_vote', implode(' AND ', $where)))
		{
			foreach ($article_votes AS $key => $val)
			{
				$result[$val['item_id']][] = $val;
			}
		}

		return $result;
	}
	public function get_article_against_vote($type,$item_id)
    {
        $where[] = "`type` = '" . $this->quote($type) . "'";
        $where[] = 'item_id = ' . intval($item_id);
        $against_count=0;
        if ($article_votes = $this->fetch_all('article_vote', implode(' AND ', $where))) {
            foreach ($article_votes AS $key => $val) {
                if($val['rating']== -1){
                    $against_count += 1;
                }
            }
            return $against_count;
        }else{
            return 0;
        }
    }

    public function get_article_vote_users_by_id($type, $item_id, $rating = null, $limit = null)
	{
		$where[] = "`type` = '" . $this->quote($type) . "'";
		$where[] = 'item_id = ' . intval($item_id);

		if ($rating)
		{
			$where[] = 'rating = ' . intval($rating);
		}

		if ($article_votes = $this->fetch_all('article_vote', implode(' AND ', $where)))
		{
			foreach ($article_votes AS $key => $val)
			{
				$uids[$val['uid']] = $val['uid'];
			}

			return $this->model('account')->get_user_info_by_uids($uids);
		}
	}

	public function get_article_vote_users_by_ids($type, $item_ids, $rating = null, $limit = null)
	{
		if (! is_array($item_ids))
		{
			return false;
		}

		if (sizeof($item_ids) == 0)
		{
			return false;
		}

		array_walk_recursive($item_ids, 'intval_string');

		$where[] = "`type` = '" . $this->quote($type) . "'";
		$where[] = 'item_id IN(' . implode(',', $item_ids) . ')';

		if ($rating)
		{
			$where[] = 'rating = ' . intval($rating);
		}

		if ($article_votes = $this->fetch_all('article_vote', implode(' AND ', $where)))
		{
			foreach ($article_votes AS $key => $val)
			{
				$uids[$val['uid']] = $val['uid'];
			}

			$users_info = $this->model('account')->get_user_info_by_uids($uids);

			foreach ($article_votes AS $key => $val)
			{
				$vote_users[$val['item_id']][$val['uid']] = $users_info[$val['uid']];
			}

			return $vote_users;
		}
	}

	public function update_views($article_id)
	{
		if (AWS_APP::cache()->get('update_views_article_' . md5(session_id()) . '_' . intval($article_id)))
		{
			return false;
		}

		AWS_APP::cache()->set('update_views_article_' . md5(session_id()) . '_' . intval($article_id), time(), 60);

		$this->shutdown_query("UPDATE " . $this->get_table('article') . " SET views = views + 1 WHERE id = " . intval($article_id));

		return true;
	}

    public function set_recommend($article_id,$uid)
    {
        $this->update('article', array(
            'is_recommend' => 1
        ), 'id = ' . intval($article_id));

        $this->model('integral')->process(intval($uid), 'RECOMMEND_ARTICLE', get_setting('integral_system_config_article_recommended'), '推荐文章 #' . $article_id, $article_id);

        $this->model('posts')->set_posts_index($article_id, 'article');
    }

    public function unset_recommend($article_id,$uid)
    {
        $this->update('article', array(
            'is_recommend' => 0
        ), 'id = ' . intval($article_id));

        $this->model('integral')->process(intval($uid), 'UNSET_RECOMMEND_ARTICLE', -get_setting('integral_system_config_article_recommended'), '取消推荐文章 #' . $article_id, $article_id);

        $this->model('posts')->set_posts_index($article_id, 'article');
    }

    public function get_article_uid($article_id)
    {
        return $this->fetch_one('article', 'uid', 'id = ' . intval($article_id));
    }

    public function get_article_lists_by_uid($page, $per_page, $order_by, $uid)
    {
        if (!$uid)
        {
            return false;
        }

        $column_result_cache_key = 'article_list_by_uid_' . md5($uid . $order_by . $page . $per_page);

        $column_found_rows_cache_key = 'article_list_by_uid__found_rows_' . md5($uid . $order_by . $page . $per_page);

        $result = AWS_APP::cache()->get($column_result_cache_key);

        $column_found_rows = AWS_APP::cache()->get($column_found_rows_cache_key);

        if (!$result)
        {
            $result = $this->fetch_page('article', 'uid = '.$uid, $order_by, $page, $per_page);

            AWS_APP::cache()->set($column_result_cache_key, $result, get_setting('cache_level_high'));
        }

        if (!$column_found_rows)
        {
            $column_found_rows = $this->found_rows();

            AWS_APP::cache()->set($column_found_rows_cache_key, $column_found_rows, get_setting('cache_level_high'));
        }

        $this->column_article_list_total = $column_found_rows;

        return $result;

    }

    public function get_article_lists_info_by_uid($uid)
    {
        if (!$uid)
        {
            return false;
        }

        $articles = $this->fetch_all('article','uid = '. intval($uid));

        return $articles;
    }

    public function get_user_all_views($sql)
    {
        return  $this->query_row($sql);
    }

    /**
     * 获取封面图文件路径
     *
     * 举个例子：$id=12345，那么封面图路径很可能(根据您部署的上传文件夹而定)会被存储为/uploads/article/cover/000/01/23/45_article_cover_min.jpg
     *
     * @param  int
     * @param  string
     * @param  int
     * @return string
     */
    public function get_cover($id, $size = 'min', $return_type = 0)
    {
        $size = in_array($size, array(
            'max',
            'mid',
            'min',
            '50',
            '150',
            'big',
            'middle',
            'small'
        )) ? $size : 'real';

        $id = abs(intval($id));
        $id = sprintf('%\'09d', $id);
        $dir1 = substr($id, 0, 3);
        $dir2 = substr($id, 3, 2);
        $dir3 = substr($id, 5, 2);

        if ($return_type == 1)
        {
            return $dir1 . '/' . $dir2 . '/' . $dir3 . '/';
        }

        if ($return_type == 2)
        {
            return substr($id, -2) . '_article_cover_' . $size . '.jpg';
        }

        return $dir1 . '/' . $dir2 . '/' . $dir3 . '/' . substr($id, -2) . '_article_cover_' . $size . '.jpg';
    }

    /**
     * 更新文章表字段
     *
     * @param array
     * @param uid
     * @return int
     */
    public function update_article_fields($update_data, $id)
    {
        return $this->update('article', $update_data, 'id = ' . intval($id));
    }

    /**
     * 返回专栏列表页文章模块数据
     * @param $sql
     * @param $limit
     * @param $offset
     * @return array
     */
    public function get_article_lists_for_column($sql,$limit,$offset)
    {
        return  $this->query_all($sql,$limit,$offset);
    }

    public function get_hot_article_by_uid($uid,$limit){
        return $this->fetch_all('article','uid = '.$uid,'views DESC',$limit);
    }

    /**
     * 置顶文章
     * @param $id
     * @throws Zend_Exception
     */
    public function set_top($id)
    {
        $this->update('article', array(
            'is_top' => 1,
            'set_top_time' => time()
        ), 'id = ' . intval($id));
//        $this->model('posts')->set_posts_index($id, 'set_top_question');
    }

    /**
     * 取消文章置顶
     * @param $id
     * @throws Zend_Exception
     */
    public function unset_top($id)
    {
        $this->update('article', array(
            'is_top' => 0,
            'set_top_time' => null
        ), 'id = ' . intval($id));

//        $this->model('posts')->set_posts_index($id, 'unset_top_question');
    }

    /**
     * 查询置顶文章数量
     * @return array
     * @throws Exception
     */
    public function select_set_top_num()
    {
        $sql = "select count(*) from ".get_table('article')." where is_top = 1";
        return $this->query_all($sql);
    }

    //查询文章赞踩记录
    public function query_article_vote_list($userName, $type, $startDate, $endDate, $voted_user_name,$page = null, $limit = 10){
        $sql = "SELECT a.* FROM ".$this->get_table('article_vote') ." as a inner join ".$this->get_table('users')." as b on a.uid = b.uid inner join ".$this->get_table('users')." as c on a.item_uid = c.uid where a.type = 'article' ";
        if($userName){
            $sql.= " and b.user_name like '%$userName%'";
        }
        if($voted_user_name){
            $sql.= " and c.user_name like '%$voted_user_name%'";
        }
        if($type){
            $sql.= " and a.rating= '$type'";
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

    public function query_article_vote_list_count($userName, $type, $startDate, $endDate,$voted_user_name){
        $sql = "SELECT count(1) as cnt FROM ".$this->get_table('article_vote') ." as a inner join ".$this->get_table('users')." as b on a.uid = b.uid inner join ".$this->get_table('users')." as c on a.item_uid = c.uid where a.type = 'article' ";
        if($userName){
            $sql.= " and b.user_name like '%$userName%'";
        }
        if($voted_user_name){
            $sql.= " and c.user_name like '%$voted_user_name%'";
        }
        if($type){
            $sql.= " and a.rating = '$type'";
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
}