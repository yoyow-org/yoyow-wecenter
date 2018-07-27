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

define('IN_AJAX', TRUE);


if (!defined('IN_ANWSION'))
{
	die;
}

define('IN_MOBILE', true);

class ajax extends AWS_CONTROLLER
{
	public function get_access_rule()
	{
		$rule_action['rule_type'] = 'white';
		$rule_action['actions'] = array(
			'hot_topics_list'
		);

		return $rule_action;
	}

	public function setup()
	{
		HTTP::no_cache_header();
	}

	public function favorite_list_action()
	{
		if ($_GET['tag'])
		{
			$this->crumb(AWS_APP::lang()->_t('标签') . ': ' . $_GET['tag'], '/favorite/tag-' . $_GET['tag']);
		}

		if ($action_list = $this->model('favorite')->get_item_list($_GET['tag'], $this->user_id, calc_page_limit($_GET['page'], get_setting('contents_per_page'))))
		{
			foreach ($action_list AS $key => $val)
			{
				$item_ids[] = $val['item_id'];
			}

			TPL::assign('list', $action_list);
		}
		else
		{
			if (!$_GET['page'] OR $_GET['page'] == 1)
			{
				$this->model('favorite')->remove_favorite_tag(null, null, $_GET['tag'], $this->user_id);
			}
		}

		TPL::output('m/ajax/favorite_list');
	}

	public function inbox_list_action()
	{
		if ($inbox_dialog = $this->model('message')->get_inbox_message($_GET['page'], get_setting('contents_per_page'), $this->user_id))
		{
			foreach ($inbox_dialog as $key => $val)
			{
				$dialog_ids[] = $val['id'];

				if ($this->user_id == $val['recipient_uid'])
				{
					$inbox_dialog_uids[] = $val['sender_uid'];
				}
				else
				{
					$inbox_dialog_uids[] = $val['recipient_uid'];
				}
			}
		}

		if ($inbox_dialog_uids)
		{
			if ($users_info_query = $this->model('account')->get_user_info_by_uids($inbox_dialog_uids))
			{
				foreach ($users_info_query as $user)
				{
					$users_info[$user['uid']] = $user;
				}
			}
		}

		if ($dialog_ids)
		{
			$last_message = $this->model('message')->get_last_messages($dialog_ids);
		}

		if ($inbox_dialog)
		{
			foreach ($inbox_dialog as $key => $value)
			{
				if ($value['recipient_uid'] == $this->user_id AND $value['recipient_count']) // 当前处于接收用户
				{
					$data[$key]['user_name'] = $users_info[$value['sender_uid']]['user_name'];
					$data[$key]['url_token'] = $users_info[$value['sender_uid']]['url_token'];

					$data[$key]['unread'] = $value['recipient_unread'];
					$data[$key]['count'] = $value['recipient_count'];

					$data[$key]['uid'] = $value['sender_uid'];
				}
				else if ($value['sender_uid'] == $this->user_id AND $value['sender_count']) // 当前处于发送用户
				{
					$data[$key]['user_name'] = $users_info[$value['recipient_uid']]['user_name'];
					$data[$key]['url_token'] = $users_info[$value['recipient_uid']]['url_token'];

					$data[$key]['unread'] = $value['sender_unread'];
					$data[$key]['count'] = $value['sender_count'];
					$data[$key]['uid'] = $value['recipient_uid'];
				}

				$data[$key]['last_message'] = $last_message[$value['id']];
				$data[$key]['update_time'] = $value['update_time'];
				$data[$key]['id'] = $value['id'];
			}
		}

		TPL::assign('list', $data);

		TPL::output('m/ajax/inbox_list');
	}

	public function hot_topics_list_action()
	{
		TPL::assign('hot_topics_list', $this->model('topic')->get_topic_list(null, 'discuss_count DESC', 5, $_GET['page']));

		TPL::output('m/ajax/hot_topics_list');
	}

    /**
     * 收益概述
     */
	public function ajax_people_income_sum_action()
    {
        $current_page = $_POST['page'] ? $_POST['page'] : 1;
        $result = array();
        if($income_sum_list = $this->model('people')->get_yoyow_sum($this->user_id, $current_page - 1)){
            foreach ($income_sum_list AS $single) {
                if($single['type'] == 0){
                    //积分奖励
                    $income_sum[] = $single;
                }else if($single['type'] == 1){
                    //提成奖励
                    $income_sum[] = $single;
                }else if($single['type'] == 2){
                    //注册奖励
                    $invitation_detail = $this->model('invitation')->fetch_row('invitation', ' invitation_id = '. $single['task_id']);
                    switch ($single['distribute_result']) {
                        case 0:
                            $income_sum[] = array_merge($single, array('invitation_detail'=>$invitation_detail));
                            break;
                        case 1:
                            $child = $this->model('invitation')->fetch_row('users', ' uid = '.$single['act_strat_time']);
                            $income_sum[] = array_merge($single,
                                array(
                                    'invitation_detail'=>$invitation_detail,
                                    'first_user_info'=>$child
                                )
                            );
                            break;
                        case 2:
                            $second = $this->model('invitation')->fetch_row('users', ' uid = '.$single['act_strat_time']);
                            $second_id = $this->model('invitation')->fetch_one('invitation', 'uid', ' invitation_id = '. $single['task_id']);
                            $first = $this->model('invitation')->fetch_row('users', ' uid = '.$second_id);
                            $income_sum[] = array_merge(
                                $single,
                                array(
                                    'invitation_detail'=>$invitation_detail,
                                    'first_user_info'=>$first,
                                    'second_user_info'=>$second
                                )
                            );
                            break;
                    }
                }else if($single['type'] == 4){
                    //注册奖励--注册绑定
                    $income_sum[] = $single;
                }
            }

            TPL::assign('income_sum_list', $income_sum);
        }
        TPL::output('m/ajax/ajax_people_income_sum');
    }

    /**
     * 日常奖励
     */
    public function ajax_people_income_integral_action()
    {
        $result = array();
        $current_page = $_POST['page'] ? $_POST['page'] : 1;
        if ($log = $this->model('people')->get_user_income($this->user_id, $current_page - 1)) {
            $result = $log;
            foreach($result as $key => $val){
                $result[$key]['act_strat_time'] = date("Y-m-d", $val['act_strat_time']);
                $result[$key]['act_end_time'] = date("Y-m-d", $val['act_end_time']);
            }
        }
        TPL::assign('log', $result);
        TPL::output('m/ajax/ajax_people_income_integral');
    }

    /**
     * 日常奖励详情
     */
    public function ajax_people_income_integral_detail_action()
    {
        //积分记录详情数据
        $result = array();
        if (intval($_GET["id"])) {
            $where = ' coin_id = ' . intval($_GET["id"]);
            $current_page = $_GET['page'] ? $_GET['page'] : 1;
            $limit = 10;
            $offset = intval($current_page - 1) * $limit;
            $integral_coin_list = $this->model('yoyowcoin')->fetch_all('integral_yoyow_coin', $where, 'integral_time DESC, id ASC', $limit, $offset);
            if ($integral_coin_list) {
                $result = $integral_coin_list;
                foreach($result as $key => $val){
                    $result[$key]['integral_time'] = date("Y-m-d", $val['integral_time']);
                    if(strpos($val['note'], '点赞') >0 || strpos($val['note'],'踩') >0 ||
                        strpos($val['note'], '取消点赞') >0 || strpos($val['note'], '取消踩')>0){
                        $result[$key]['note'] = substr($val['note'],0,strrpos($val['note'],'获'));
                    }
                }
            }
            TPL::assign('integral_detail', $result);
        }
        TPL::output('m/ajax/ajax_people_income_integral_detail');
    }


    /**
     * 提成奖励
     */
    public function ajax_people_income_commission_action()
    {
        $current_page = $_POST['page'] ? $_POST['page'] : 1;
        if ($commission = $this->model('people')->get_user_income_commission($this->user_id, $current_page - 1)) {
            TPL::assign('commission', $commission);
        }

        TPL::output('m/ajax/ajax_people_income_commission');
    }

    /**
     * 注册奖励
     */
    public function ajax_people_income_register_action()
    {
        $current_page = $_POST['page'] ? $_POST['page'] : 1;
        if ($register_list = $this->model('people')->get_user_income_register($this->user_id, $current_page - 1)) {
            TPL::assign('register_list', $register_list);
        }
        TPL::output('m/ajax/ajax_people_income_register');
    }

    /**
     * 邀请奖励
     */
    public function ajax_people_income_invite_action()
    {
        $current_page = $_POST['page'] ? $_POST['page'] : 1;
        if ($register_list = $this->model('people')->get_user_income_invite($this->user_id, $current_page - 1)) {
            foreach ($register_list as $coin_single){
                $invitation_detail = $this->model('invitation')->fetch_row('invitation', ' invitation_id = '. $coin_single['invitation_id']);
                switch ($coin_single['coin_type']) {
                    case 0:
                        $invitation_list[] = array_merge($coin_single, array('invitation_detail'=>$invitation_detail));
                        break;
                    case 1:
                        $child = $this->model('invitation')->fetch_row('users', ' uid = '.$coin_single['base_uid']);
                        $invitation_list[] = array_merge($coin_single,
                            array(
                                'invitation_detail'=>$invitation_detail,
                                'first_user_info'=>$child
                            )
                        );
                        break;
                    case 2:
                        $second = $this->model('invitation')->fetch_row('users', ' uid = '.$coin_single['base_uid']);
                        $second_id = $this->model('invitation')->fetch_one('invitation', 'uid', ' invitation_id = '. $coin_single['invitation_id']);
                        $first = $this->model('invitation')->fetch_row('users', ' uid = '.$second_id);
                        $invitation_list[] = array_merge(
                            $coin_single,
                            array(
                                'invitation_detail'=>$invitation_detail,
                                'first_user_info'=>$first,
                                'second_user_info'=>$second
                            )
                        );
                        break;
                }
            }
            TPL::assign('invitation_list', $invitation_list);
        }
        TPL::output('m/ajax/ajax_people_income_invite');
    }



    public function downImg_action()
    { 
        $serverPath = $_SERVER['DOCUMENT_ROOT'].'/..'.$_GET['src'];
        // $serverPath = @base_url().$_GET['src'];
        $filename = '邀请海报.jpg';
        if($serverPath){
            $filesize = filesize($serverPath);
            
            $fp = fopen($serverPath, 'rb');
            header("Accept-Ranges: bytes");
            header("Content-Type: application/octet-stream");
            header('content-disposition:attachment;filename='. $filename);
            header('content-length:'.$filesize);
            header("Cache-Control: must-revalidate, post-check=0, pre-check=0" );
            header("Pragma: no-cache" );
            header("Expires: 0" );
            fpassthru($fp);
            // exit($fp);
            // echo $file;
        }

    }



}