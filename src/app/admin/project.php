<?php
/*
+--------------------------------------------------------------------------
|   WeCenter [#RELEASE_VERSION#]
|   ========================================
|   by WeCenter Software
|   © 2011 - 2013 WeCenter. All Rights Reserved
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

class project extends AWS_ADMIN_CONTROLLER
{
    public function approval_list_action()
    {
        $this->crumb(AWS_APP::lang()->_t('活动审核'), '/admin/project/approval_list/');

        TPL::assign('menu_list', $this->model('admin')->fetch_menu_list(311));

        $approval_list = $this->model('project')->get_projects_list(null, 0, null, $_GET['page'], $this->per_page, 'id ASC');

        if ($approval_list)
        {
            $found_rows = $this->model('project')->found_rows();

            TPL::assign('approval_list', $approval_list);

            TPL::assign('pagination', AWS_APP::pagination()->initialize(array(
                'base_url' => get_setting('base_url') . '/?/admin/project/approval_list/',
                'total_rows' => $found_rows,
                'per_page' => $this->per_page
            ))->create_links());
        }

        TPL::output('admin/project/approval_list');
    }

    public function approval_batch_action()
    {
        define('IN_AJAX', TRUE);

        if (!is_array($_POST['approval_ids']))
        {
            H::ajax_json_output(AWS_APP::RSM(null, -1, AWS_APP::lang()->_t('请选择条目进行操作')));
        }

        switch ($_POST['batch_type'])
        {
            case 'approval':
            case 'decline':
                $func = 'set_project_' . $_POST['batch_type'];

                foreach ($_POST['approval_ids'] AS $approval_id)
                {
                    $this->model('project')->$func($approval_id);
                }

                break;
        }

        H::ajax_json_output(AWS_APP::RSM(null, 1, null));
    }

    public function project_list_action()
    {
        if ($project_list = $this->model('project')->get_projects_list(null, 1, null, $_GET['page'], $this->per_page, 'status DESC, id DESC'))
        {
            $found_rows = $this->model('project')->found_rows();

            TPL::assign('pagination', AWS_APP::pagination()->initialize(array(
                'base_url' => get_setting('base_url') . '/?/admin/project/project_list/',
                'total_rows' => $found_rows,
                'per_page' => $this->per_page
            ))->create_links());
        }

        $this->crumb(AWS_APP::lang()->_t('活动管理'), '/admin/project/project_list/');

        TPL::assign('approval_list', $project_list);
        TPL::assign('menu_list', $this->model('admin')->fetch_menu_list(310));

        TPL::output('admin/project/project_list');
    }

    public function status_batch_action()
    {
        define('IN_AJAX', TRUE);

        if (!is_array($_POST['approval_ids']))
        {
            H::ajax_json_output(AWS_APP::RSM(null, -1, AWS_APP::lang()->_t('请选择条目进行操作')));
        }

        switch ($_POST['batch_type'])
        {
            case 'ONLINE':
            case 'OFFLINE':
                foreach ($_POST['approval_ids'] AS $approval_id)
                {
                    $this->model('project')->set_project_status($approval_id, $_POST['batch_type']);
                }

                break;

            case 'delete':
                foreach ($_POST['approval_ids'] AS $approval_id)
                {
                    $this->model('project')->remove_project_by_project_id($approval_id);
                }

                break;
        }


        H::ajax_json_output(AWS_APP::RSM(null, 1, null));
    }

    public function order_list_action()
    {
        $this->crumb(AWS_APP::lang()->_t('订单管理'), '/admin/project/order_list/');

        TPL::assign('menu_list', $this->model('admin')->fetch_menu_list(312));

        $order_list = $this->model('project')->get_order_list(null, $_GET['page'], $this->per_page);

        if ($order_list)
        {
            $order_num = $this->model('project')->found_rows();

            foreach ($order_list AS $order_info)
            {
                $uids[] = $order_info['uid'];
            }

            $users_info = $this->model('account')->get_user_info_by_uids($uids);

            TPL::assign('order_list', $order_list);

            TPL::assign('users_info', $users_info);

            TPL::assign('pagination', AWS_APP::pagination()->initialize(array(
                'base_url' => get_setting('base_url') . '/?/admin/project/order_list/',
                'total_rows' => $order_num,
                'per_page' => $this->per_page
            ))->create_links());
        }

        TPL::output('admin/project/order_list');
    }

    public function edit_order_action()
    {
        $this->crumb(AWS_APP::lang()->_t('订单编辑'), '/admin/project/edit_order/');

        TPL::assign('menu_list', $this->model('admin')->fetch_menu_list(312));

        if (!$_GET['id'])
        {
            H::redirect_msg(AWS_APP::lang()->_t('请选择订单'), '/admin/project/order_list/');
        }

        $order_info = $this->model('project')->get_product_order_by_id($_GET['id']);

        if (!$order_info)
        {
            H::redirect_msg(AWS_APP::lang()->_t('订单不存在'), '/admin/project/order_list/');
        }

        TPL::assign('order_info', $order_info);

        TPL::assign('order_user', $this->model('account')->get_user_info_by_uid($order_info['uid']));

        TPL::output('admin/project/edit_order');
    }

    public function save_order_action()
    {
        define('IN_AJAX', TRUE);

        if (!$_POST['id'])
        {
            H::ajax_json_output(AWS_APP::RSM(null, -1, AWS_APP::lang()->_t('请选择订单')));
        }

        $this->model('project')->update_order($_POST['id'], $_POST);

        H::ajax_json_output(AWS_APP::RSM(null, 1, null));
    }


    public function register_bind_list_action()
    {
        $this->crumb(AWS_APP::lang()->_t('注册绑定活动管理'), '/admin/project/register_bind_list/');

        TPL::assign('menu_list', $this->model('admin')->fetch_menu_list(316));

        $page = 1;
        if($_GET['page']){
            $page = $_GET['page'];
        }

        $register_bind_list = $this->model('project')->get_register_bind_list($page, $this->per_page);
        $sql = "select sum(a.coin) coin from ".get_table('register_yoyow_record')." a LEFT JOIN ".get_table('users')." b on a.uid = b.uid where b.online_time > 0 AND a.`status`=0 or a.`status` is null and b.uid is not null";
        $issued_sum_yoyow = $this->model('project')->query_all($sql);
        if(!$issued_sum_yoyow[0]['coin']){
            $issued_sum_yoyows = 0;
        }else{
            $issued_sum_yoyows = $issued_sum_yoyow[0]['coin'];
        }
        if ($register_bind_list)
        {
            $sql1 = "select count(*) num from ".get_table('register_yoyow_record')." a LEFT JOIN ".get_table('users')." b on a.uid = b.uid where b.uid is not null";
            $register_bind_num = $this->model('project')->query_all($sql1);
            foreach ($register_bind_list as $k =>$val){
                $yoyow_status = $this->model('project')->fetch_row('register_reward_record','uid = '.$val['uid'].' AND type = '.$val['operate']);
                if($yoyow_status){
                    $register_bind_list[$k]['yoyow_status'] = $yoyow_status['status'];
                }else{
                    $register_bind_list[$k]['yoyow_status'] = 3;
                }

            }
            TPL::assign('register_bind_list', $register_bind_list);
            TPL::assign('issued_sum_yoyow', $issued_sum_yoyows);
            TPL::assign('pagination', AWS_APP::pagination()->initialize(array(
                'base_url' => get_setting('base_url') . '/?/admin/project/register_bind_list/',
                'total_rows' => $register_bind_num[0]['num'],
                'per_page' => $this->per_page
            ))->create_links());
        }
        //获取平台余额
        $api_url=get_setting('api_url');
        //调用接口获取平台账户信息 /api/v1/getAccount
        //请求地址
        $req_url = $api_url.'/api/v1/getAccount';
        //进行请求
        $res_content = http_get($req_url . '?uid=' . get_setting('platform_id'));
        $res_json = json_decode($res_content, true);
        TPL::assign('account_prepaid',$res_json['data']['statistics']['prepaid']/100000);

        TPL::output('admin/project/register_bind_list');
    }

    /**
     * 注册绑定参数后台页面
     */
    public function register_bind_setting_action()
    {
        $this->crumb(AWS_APP::lang()->_t('注册绑定活动管理'), '/admin/project/register_bind_list/');
        TPL::assign('menu_list', $this->model('admin')->fetch_menu_list(316));
        TPL::output('admin/project/register_bind_setting');
    }

    /**
     * 威望奖励发放后台页面
     */
    public function reputation_reward_list_action()
    {
        $this->crumb(AWS_APP::lang()->_t('威望奖励发放'), '/admin/project/reputation_reward_list/');

        TPL::assign('menu_list', $this->model('admin')->fetch_menu_list(316));

        $page = 1;
        if($_GET['page']){
            $page = $_GET['page'];
        }
        $reputation_reward_list = $this->model('project')->get_reputation_users_list($page, $this->per_page);
        foreach ($reputation_reward_list as $key =>$val){
            $reputation_reward_list[$key]['user_name'] = $this->model('project')->fetch_one('users','user_name','uid = '.$val['uid']);
            if(!$reputation_reward_record = $this->model('project')->fetch_row('register_reward_record','uid = '.$val['uid']." and type = 0")){
                $reputation_reward_list[$key]['yoyow_status'] = 3;
            }else{
                $reputation_reward_list[$key]['yoyow_status'] = $reputation_reward_record['status'];
            }
        }
        $reputation_users_num = $this->model('project')->query_all("select count(*) rows from ".get_table('reputation_users'));
        TPL::assign('reputation_reward_list', $reputation_reward_list);
        TPL::assign('pagination', AWS_APP::pagination()->initialize(array(
            'base_url' => get_setting('base_url') . '/?/admin/project/reputation_reward_list/',
            'total_rows' => $reputation_users_num[0]['rows'],
            'per_page' => $this->per_page
        ))->create_links());
        //获取平台余额
        $api_url=get_setting('api_url');
        //调用接口获取平台账户信息 /api/v1/getAccount
        //请求地址
        $req_url = $api_url.'/api/v1/getAccount';
        //进行请求
        $res_content = http_get($req_url . '?uid=' . get_setting('platform_id'));
        $res_json = json_decode($res_content, true);
        TPL::assign('account_prepaid',$res_json['data']['statistics']['prepaid']/100000);
        $sql = "select count(*) count from ".get_table('reputation_users')." where status = 0";
        $count = $this->model('project')->query_all($sql);
        if(!$count[0]['count']){
            $count = 0;
        }else{
            $count = $count[0]['count'];
        }
        TPL::assign('issued_sum_yoyow', $count*18);
        TPL::output('admin/project/reputation_reward_list');
    }
}
