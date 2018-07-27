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
		$rule_action['rule_type'] = 'black';

		return $rule_action;
	}

	public function setup()
	{
		HTTP::no_cache_header();
	}

	/*public function run_action()
	{
		header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');             // Date in the past
		header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT'); // always modified
		header('Cache-Control: no-cache, must-revalidate');           // HTTP/1.1
		header('Pragma: no-cache');                                   // HTTP/1.0

		@set_time_limit(0);

		if ($call_actions = $this->model('crond')->start())
		{
			foreach ($call_actions AS $call_action)
			{
				if ($plugins = AWS_APP::plugins()->parse('crond', 'main', $call_action))
				{
					foreach ($plugins AS $plugin_file)
					{
						include($plugin_file);
					}
				}

				$call_function = $call_action;

				$this->model('crond')->$call_function();
			}
		}

		if (AWS_APP::config()->get('system')->debug)
		{
			TPL::output('global/debuger.tpl.htm');
		}
	}*/

	// 每半分钟执行
    public function half_minute_action()
    {   
        $this->model('edm')->run_task();
    }

	 // 每分钟执行
    public function minute_action()
    {   
        @unlink(TEMP_PATH . 'plugins_table.php');
        @unlink(TEMP_PATH . 'plugins_model.php');

        if ($this->model('reputation')->calculate(AWS_APP::cache()->get('reputation_calculate_start'), 2000))
        {
            AWS_APP::cache()->set('reputation_calculate_start', (intval(AWS_APP::cache()->get('reputation_calculate_start')) + 2000), 604800);
        }
        else
        {
            AWS_APP::cache()->set('reputation_calculate_start', 0, 604800);
        }

        $this->model('online')->delete_expire_users();

        if (check_extension_package('project'))
        {
            /*$expire_orders = $this->fetch_all('product_order', 'add_time < ' . (time() - 600) . ' AND payment_time = 0 AND cancel_time = 0 AND refund_time = 0');

            if ($expire_orders)
            {
                foreach ($expire_orders AS $order_info)
                {
                    $this->model('project')->cancel_project_order_by_id($order_info['id']);
                }
            }*/
        }

        $this->model('email')->send_mail_queue(120);

        $ids="auto";
        //执行手动分币任务
        $this->model('assigntask')->conin_task($ids);
        //执行分配失败的重新分配
//        $this->model('assigntask')->execute_failed_distribute();
        var_dump("执行完成");
    }

	// 每五分钟执行
    public function five_minutes_action()
    {
        if (check_extension_package('ticket'))
        {
            if (get_setting('weibo_msg_enabled') == 'ticket')
            {
                $this->model('ticket')->save_weibo_msg_to_ticket_crond();
            }

            $receiving_email_global_config = get_setting('receiving_email_global_config');

            if ($receiving_email_global_config['enabled'] == 'ticket')
            {
                $this->model('ticket')->save_received_email_to_ticket_crond();
            }
        }



        $this->model('admin')->notifications_crond();

        $this->model('active')->send_valid_email_crond();
    }


	// 每十分钟执行
    public function ten_minutes_action()
    {
        if (get_setting('weibo_msg_enabled') == 'Y')
        {
            $this->model('openid_weibo_weibo')->get_msg_from_sina_crond();
        }
    }


    // 每半小时执行
    public function half_hour_action()
    {
        $this->model('search_fulltext')->clean_cache();

        if (check_extension_package('project'))
        {
            $this->model('project')->send_project_open_close_notify();
        }

        $receiving_email_global_config = get_setting('receiving_email_global_config');

        if ($receiving_email_global_config['enabled'] == 'Y')
        {
            $this->model('edm')->receive_email_crond();
        }
    }

    // 每小时执行
    public function hour_action()
    {
        //$this->model('update')->update_invite();
        //$this->model('rankinglist')->get_ranking();
        $this->model('system')->clean_session();
/*
        $this->model("dingtalk")->flush_access_token();

        $this->model('rankinglist')->comment_week();

        $this->model('rankinglist')->comment();

        $this->model('rankinglist')->bonuspoints();

        $this->model('rankinglist')->bonuspoints_week();

        $this->model('rankinglist')->thumbup();

        $this->model('rankinglist')->thumbup_week();*/

    }



    // 每日时执行
    public function day_action()
    {
        $this->model('answer')->calc_best_answer();
        $this->model('question')->auto_lock_question();
        $this->model('active')->clean_expire();
        $this->model("account")->statistical_user_ranking();
        $this->model("account")->update_user_article_count();

        if ((!get_setting('db_engine') OR get_setting('db_engine') == 'MyISAM') AND !defined('IN_SAE'))
        {
           /* $this->query('OPTIMIZE TABLE `' . get_table('sessions') . '`');
            $this->query('OPTIMIZE TABLE `' . get_table('search_cache') . '`');
            $this->query('REPAIR TABLE `' . get_table('sessions') . '`');*/
        }
    }


    // 每周执行
    public function week_action()
    {
	    $this->model('notify')->clean_mark_read_notifications(2592000);
        $this->model('system')->clean_break_attach();
        $this->model('email')->mail_queue_error_clean();
    }
}