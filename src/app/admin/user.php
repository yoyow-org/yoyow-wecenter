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

class user extends AWS_ADMIN_CONTROLLER
{
    public function list_action()
    {
        if ($_POST['action'] == 'search')
        {
            foreach ($_POST as $key => $val)
            {
                if (in_array($key, array('user_name', 'email')))
                {
                    $val = rawurlencode($val);
                }


                $param[] = $key . '-' . $val;
            }

            H::ajax_json_output(AWS_APP::RSM(array(
                'url' => get_js_url('/admin/user/list/' . implode('__', $param))
            ), 1, null));
        }

        $where = array();

        if ($_GET['type'] == 'forbidden')
        {
            $where[] = 'forbidden = 1';
        }
        if((!is_numeric($_GET['uid'])) && $_GET['uid'] ){
            H::redirect_msg(AWS_APP::lang()->_t('请输入正确的用户ID'), '/admin/user/list/');
        }
        if ($_GET['uid'])
        {
            $where[] = "uid =" . $this->model('people')->quote($_GET['uid']);
        }

        if ($_GET['user_name'])
        {
            $where[] = "user_name LIKE '%" . $this->model('people')->quote($_GET['user_name']) . "%'";
        }

        if ($_GET['email'])
        {
            $where[] = "email = '" . $this->model('people')->quote($_GET['email']) . "'";
        }

        if ($_GET['group_id'])
        {
            $where[] = 'group_id = ' . intval($_GET['group_id']);
        }

        if ($_GET['ip'] AND preg_match('/(\d{1,3}\.){3}(\d{1,3}|\*)/', $_GET['ip']))
        {
            if (substr($_GET['ip'], -2, 2) == '.*')
            {
                $ip_base = ip2long(str_replace('.*', '.0', $_GET['ip']));

                if ($ip_base)
                {
                    $where[] = 'last_ip BETWEEN ' . $ip_base . ' AND ' . ($ip_base + 255);
                }
            }
            else
            {
                $ip_base = ip2long($_GET['ip']);

                if ($ip_base)
                {
                    $where[] = 'last_ip = ' . $ip_base;
                }
            }
        }

        if ($_GET['integral_min'])
        {
            $where[] = 'integral >= ' . intval($_GET['integral_min']);
        }

        if ($_GET['integral_max'])
        {
            $where[] = 'integral <= ' . intval($_GET['integral_max']);
        }

        if ($_GET['reputation_min'])
        {
            $where[] = 'reputation >= ' . intval($_GET['reputation_min']);
        }

        if ($_GET['reputation_max'])
        {
            $where[] = 'reputation <= ' . intval($_GET['reputation_max']);
        }

        if ($_GET['job_id'])
        {
            $where[] = 'job_id = ' . intval($_GET['job_id']);
        }

        if ($_GET['province'])
        {
            $where[] = "province = '" . $this->model('people')->quote($_GET['province']) . "'";
        }

        if ($_GET['city'])
        {
            $where[] = "city = '" . $this->model('people')->quote($_GET['city']) . "'";
        }

        $user_list = $this->model('people')->fetch_page('users', implode(' AND ', $where), 'uid DESC', $_GET['page'], $this->per_page);

        $total_rows = $this->model('people')->found_rows();

        $url_param = array();

        foreach($_GET as $key => $val)
        {
            if (!in_array($key, array('app', 'c', 'act', 'page')))
            {
                $url_param[] = $key . '-' . $val;
            }
        }

        TPL::assign('pagination', AWS_APP::pagination()->initialize(array(
            'base_url' => get_js_url('/admin/user/list/') . implode('__', $url_param),
            'total_rows' => $total_rows,
            'per_page' => $this->per_page
        ))->create_links());

        $this->crumb(AWS_APP::lang()->_t('会员列表'), "admin/user/list/");

        TPL::assign('mem_group', $this->model('account')->get_user_group_list(1));
        TPL::assign('system_group', $this->model('account')->get_user_group_list(0));
        TPL::assign('job_list', $this->model('work')->get_jobs_list());
        TPL::assign('total_rows', $total_rows);
        TPL::assign('list', $user_list);
        TPL::assign('menu_list', $this->model('admin')->fetch_menu_list(402));

        TPL::output('admin/user/list');
    }

    public function integral_list_action()
    {
        if ($_POST['action'] == 'search')
        {
            foreach ($_POST as $key => $val)
            {
                if ($key == 'start_date' OR $key == 'end_date')
                {
                    $val = base64_encode($val);
                }

                if ($key == 'integral_type' OR $key == 'user_name')
                {
                    $val = rawurlencode($val);
                }

                $param[] = $key . '-' . $val;
            }

            H::ajax_json_output(AWS_APP::RSM(array(
                'url' => get_js_url('/admin/user/integral_list/' . implode('__', $param))
            ), 1, null));
        }

        $where = array();

        $page = 1;
        if($_GET['page']){
            $page = $_GET['page'];
        }
        $user_list = $this->model('integral')->query_list($_GET['user_name'], $_GET['integral_type'], $_GET['start_date'], $_GET['end_date'], $page, $this->per_page);

        $total_rows = $this->model('integral')->query_list_count($_GET['user_name'], $_GET['integral_type'], $_GET['start_date'], $_GET['end_date']);

        $url_param = array();

        foreach($_GET as $key => $val)
        {
            if (!in_array($key, array('app', 'c', 'act', 'page')))
            {
                $url_param[] = $key . '-' . $val;
            }
        }

        TPL::assign('pagination', AWS_APP::pagination()->initialize(array(
            'base_url' => get_js_url('/admin/user/integral_list/') . implode('__', $url_param),
            'total_rows' => $total_rows,
            'per_page' => $this->per_page
        ))->create_links());

        $this->crumb(AWS_APP::lang()->_t('会员积分记录列表'), "admin/user/integral_list/");

        TPL::assign('total_rows', $total_rows);
        TPL::assign('list', $user_list);
        TPL::assign('menu_list', $this->model('admin')->fetch_menu_list(404));

        TPL::output('admin/user/integral_list');
    }

    public function group_list_action()
    {
        $this->crumb(AWS_APP::lang()->_t('用户组管理'), "admin/user/group_list/");

        if (!$this->user_info['permission']['is_administortar'])
        {
            H::redirect_msg(AWS_APP::lang()->_t('你没有访问权限, 请重新登录'), '/');
        }
        TPL::assign('mem_group', $this->model('account')->get_user_group_list(1));
        TPL::assign('system_group', $this->model('account')->get_user_group_list(0, 0));
        TPL::assign('custom_group', $this->model('account')->get_user_group_list(0, 1));
        TPL::assign('menu_list', $this->model('admin')->fetch_menu_list(403));
        TPL::output('admin/user/group_list');
    }

    public function group_edit_action()
    {
        $this->crumb(AWS_APP::lang()->_t('修改用户组'), "admin/user/group_list/");

        if (!$this->user_info['permission']['is_administortar'])
        {
            H::redirect_msg(AWS_APP::lang()->_t('你没有访问权限, 请重新登录'), '/');
        }

        if (! $group = $this->model('account')->get_user_group_by_id($_GET['group_id']))
        {
            H::redirect_msg(AWS_APP::lang()->_t('用户组不存在'), '/admin/user/group_list/');
        }

        TPL::assign('group', $group);
        TPL::assign('group_pms', $group['permission']);
        TPL::assign('menu_list', $this->model('admin')->fetch_menu_list(403));
        TPL::output('admin/user/group_edit');
    }

    public function edit_action()
    {
        $this->crumb(AWS_APP::lang()->_t('编辑用户资料'), 'admin/user/edit/');

        if (!$user = $this->model('account')->get_user_info_by_uid($_GET['uid'], TRUE))
        {
            H::redirect_msg(AWS_APP::lang()->_t('用户不存在'), '/admin/user/list/');
        }else{
            if($_GET['uid'])
            {
                if($invite_uid = $this->model('account')->fetch_one('invitation_yoyow','coin_uid','base_uid ='.$_GET['uid'].' and coin_type = 1')){
                    $user['invite_user_name'] = $this->model('account')->fetch_one('users','user_name','uid = '.$invite_uid);
                }
            }
        }

        if ($user['group_id'] == 1 AND !$this->user_info['permission']['is_administortar'])
        {
            H::redirect_msg(AWS_APP::lang()->_t('你没有权限编辑管理员账号'), '/admin/user/list/');
        }

        TPL::assign('job_list', $this->model('work')->get_jobs_list());
        TPL::assign('mem_group', $this->model('account')->get_user_group_by_id($user['reputation_group']));
        TPL::assign('system_group', $this->model('account')->get_user_group_list(0));
        TPL::assign('user', $user);
        TPL::assign('menu_list', $this->model('admin')->fetch_menu_list(402));

        TPL::output('admin/user/edit');
    }

    public function user_add_action()
    {
        $this->crumb(AWS_APP::lang()->_t('添加用户'), "admin/user/list/user_add/");

        TPL::assign('job_list', $this->model('work')->get_jobs_list());

        TPL::assign('menu_list', $this->model('admin')->fetch_menu_list(402));

        TPL::assign('system_group', $this->model('account')->get_user_group_list(0));

        TPL::output('admin/user/add');
    }

    public function invites_action()
    {
        $this->crumb(AWS_APP::lang()->_t('批量邀请'), "admin/user/invites/");

        TPL::assign('menu_list', $this->model('admin')->fetch_menu_list(406));
        TPL::output('admin/user/invites');
    }

    public function job_list_action()
    {
        TPL::assign('job_list', $this->model('work')->get_jobs_list());

        $this->crumb(AWS_APP::lang()->_t('职位设置'), "admin/user/job_list/");

        TPL::assign('menu_list', $this->model('admin')->fetch_menu_list(407));
        TPL::output('admin/user/job_list');
    }

    public function verify_approval_list_action()
    {
        $approval_list = $this->model('verify')->approval_list($_GET['page'], $_GET['status'], $this->per_page);

        $total_rows = $this->model('verify')->found_rows();

        foreach ($approval_list AS $key => $val)
        {
            if (!$uids[$val['uid']])
            {
                $uids[$val['uid']] = $val['uid'];
            }
        }

        TPL::assign('pagination', AWS_APP::pagination()->initialize(array(
            'base_url' => get_js_url('/admin/user/verify_approval_list/status-' . $_GET['status']),
            'total_rows' => $total_rows,
            'per_page' => $this->per_page
        ))->create_links());

        $this->crumb(AWS_APP::lang()->_t('认证审核'), 'admin/user/verify_approval_list/');

        TPL::assign('users_info', $this->model('account')->get_user_info_by_uids($uids));
        TPL::assign('approval_list', $approval_list);
        TPL::assign('menu_list', $this->model('admin')->fetch_menu_list(401));

        TPL::output('admin/user/verify_approval_list');
    }

    public function register_approval_list_action()
    {
        if (get_setting('register_valid_type') != 'approval')
        {
            H::redirect_msg(AWS_APP::lang()->_t('未启用新用户注册审核'), '/admin/');
        }

        $user_list = $this->model('people')->fetch_page('users', 'group_id = 3', 'uid ASC', $_GET['page'], $this->per_page);

        $total_rows = $this->model('people')->found_rows();

        TPL::assign('pagination', AWS_APP::pagination()->initialize(array(
            'base_url' => get_js_url('/admin/user/register_approval_list/'),
            'total_rows' => $total_rows,
            'per_page' => $this->per_page
        ))->create_links());

        $this->crumb(AWS_APP::lang()->_t('注册审核'), 'admin/user/register_approval_list/');

        TPL::assign('list', $user_list);
        TPL::assign('menu_list', $this->model('admin')->fetch_menu_list(408));

        TPL::output('admin/user/register_approval_list');
    }

    public function verify_approval_edit_action()
    {
        if (!$verify_apply = $this->model('verify')->fetch_apply($_GET['id']))
        {
            H::redirect_msg(AWS_APP::lang()->_t('审核认证不存在'), '/admin/user/register_approval_list/');
        }

        TPL::assign('verify_apply', $verify_apply);
        TPL::assign('user', $this->model('account')->get_user_info_by_uid($_GET['id']));

        TPL::assign('menu_list', $this->model('admin')->fetch_menu_list(401));

        $this->crumb(AWS_APP::lang()->_t('编辑认证审核资料'), 'admin/user/verify_approval_list/');

        TPL::output('admin/user/verify_approval_edit');
    }

    public function integral_log_action()
    {
        if ($log = $this->model('integral')->fetch_page('integral_log', 'uid = ' . intval($_GET['uid']), 'time DESC', $_GET['page'], 50))
        {
            TPL::assign('pagination', AWS_APP::pagination()->initialize(array(
                'base_url' => get_js_url('/admin/user/integral_log/uid-' . intval($_GET['uid'])),
                'total_rows' => $this->model('integral')->found_rows(),
                'per_page' => 50
            ))->create_links());

            foreach ($log AS $key => $val)
            {
                $parse_items[$val['id']] = array(
                    'item_id' => $val['item_id'],
                    'action' => $val['action']
                );
            }

            TPL::assign('integral_log', $log);
            TPL::assign('integral_log_detail', $this->model('integral')->parse_log_item($parse_items));
        }

        TPL::assign('user', $this->model('account')->get_user_info_by_uid($_GET['uid']));
        TPL::assign('menu_list', $this->model('admin')->fetch_menu_list(402));

        $this->crumb(AWS_APP::lang()->_t('积分日志'), '/admin/user/integral_log/uid-' . $_GET['uid']);

        TPL::output('admin/user/integral_log');
    }

    /**
     * 手动分配任务列表
     */
    public function assign_task_list_action()
    {
        if ($task_list=$this->model('assigntask')->fetch_page('yoyow_assign_task', null, 'exec_time DESC', $_GET['page'], $this->per_page))
        {
            TPL::assign('task_list', $task_list);
            $total_rows=$this->model('assigntask')->count('yoyow_assign_task',null);
            TPL::assign('pagination', AWS_APP::pagination()->initialize(array(
                'base_url' => get_js_url('/admin/user/assign_task_list/'),
                'total_rows' => $total_rows,
                'per_page' => $this->per_page
            ))->create_links());
        }

        $this->crumb(AWS_APP::lang()->_t('手动分配任务列表'), 'admin/user/assign_task_list');
        TPL::assign('menu_list', $this->model('admin')->fetch_menu_list(420));
        TPL::output('admin/user/assign_task_list');
    }

    /**
     * 编辑一个手动分配任务列表
     */
    public function assign_task_edit_action(){
        $api_url=get_setting('api_url');
        //调用接口获取平台账户信息 /api/v1/getAccount
        //请求地址
        $req_url = $api_url.'/api/v1/getAccount';
        //进行请求
        $res_content = http_get($req_url . '?uid=' . get_setting('platform_id'));
        $res_json = json_decode($res_content, true);
        if(!empty($_GET["id"])){
            $task=$this->model('assigntask')->query_by_id($_GET["id"]);
            $this->crumb(AWS_APP::lang()->_t('编辑手动分配任务'), 'admin/user/assign_task_list');
            TPL::assign('task', $task);
        }else{
            $this->crumb(AWS_APP::lang()->_t('添加手动分配任务'), 'admin/user/assign_task_list');
        }
        TPL::assign('account_prepaid',$res_json['data']['statistics']['prepaid']/100000);
        TPL::assign('menu_list', $this->model('admin')->fetch_menu_list(420));
        TPL::output('admin/user/assign_task_edit');
    }

    /**
     * 自动分配任务列表
     */
    public function assign_trigger_list_action()
    {
        if ($task_list=$this->model('triggertask')->fetch_page('yoyow_trigger_task', null, 'exec_time DESC', $_GET['page'], $this->per_page))
        {
            TPL::assign('task_list', $task_list);
            $total_rows=$this->model('triggertask')->count('yoyow_trigger_task',null);
            TPL::assign('pagination', AWS_APP::pagination()->initialize(array(
                'base_url' => get_js_url('/admin/user/assign_trigger_list/'),
                'total_rows' => $total_rows,
                'per_page' => $this->per_page
            ))->create_links());
        }

        $this->crumb(AWS_APP::lang()->_t('自动分配任务列表'), 'admin/user/assign_trigger_list');
        TPL::assign('menu_list', $this->model('admin')->fetch_menu_list(420));
        TPL::output('admin/user/assign_trigger_list');
    }

    /**
     * 会员yoyow币记录
     */
    public function user_yoyow_coin_list_action()
    {
        $tb_name=$this->model('yoyowcoin')->get_table('users');
        $where=' 1=1 ';
        if($_POST['action']=='search'){
            if(!empty($_POST['user_name'])){
                $where=$where.' AND uid IN (SELECT uid FROM '.$tb_name.' WHERE user_name LIKE \'%'.$_POST['user_name'].'%\' ) ';
            }
            if(!empty($_POST['coin_min'])){
                $where=$where.' AND ABS(coin) >=ABS('.$_POST['coin_min'].') ';
            }
            if(!empty($_POST['coin_max'])){
                $where=$where.' AND ABS(coin) <=ABS('.$_POST['coin_max'].') ';
            }
            if(!empty($_POST['origin'])){
                $where=$where.' AND origin=\''.$_POST['origin'].'\' ';
            }
            if(!empty($_POST['start_date'])){
                $where=$where.' AND add_time>=' . strtotime($_POST['start_date']);
            }
            if(!empty($_POST['end_date'])){
                $where=$where.' AND add_time<=' . strtotime($_POST['end_date']);
            }
        }
        if ($coin_list=$this->model('yoyowcoin')->fetch_page('user_yoyow_coin', $where, 'add_time DESC', $_GET['page'], $this->per_page))
        {
            for ($i=0; $i<count($coin_list); $i++)
            {
                $coin_list[$i]['user']=$this->model('account')->get_user_info_by_uid($coin_list[$i]['uid']);
            }
            TPL::assign('coin_list', $coin_list);
            $total_rows=$this->model('yoyowcoin')->count('user_yoyow_coin',$where);
            TPL::assign('pagination', AWS_APP::pagination()->initialize(array(
                'base_url' => get_js_url('/admin/user/user_yoyow_coin_list/'),
                'total_rows' => $total_rows,
                'per_page' => $this->per_page
            ))->create_links());
            TPL::assign('total_rows', $total_rows);
        }

        $this->crumb(AWS_APP::lang()->_t('用户YOYOW币记录'), 'admin/user/user_yoyow_coin_list');
        TPL::assign('menu_list', $this->model('admin')->fetch_menu_list(421));
        TPL::output('admin/user/user_yoyow_coin_list');
    }

    /**
     * 会员yoyow币设置
     */
    public function yoyow_coin_settings_action(){
        $yoyow_assign_task_switch=get_setting('yoyow_assign_task_switch', false);
        $yoyow_trigger_task_switch=get_setting('yoyow_trigger_task_switch', false);
        $yoyow_trigger_task_scale=get_setting('yoyow_trigger_task_scale', false);
        $yoyow_trigger_task_time=get_setting('yoyow_trigger_task_time', false);
        $default_distribute_yoyow_coin=get_setting('default_distribute_yoyow_coin', false);
        $setting_array=array(
            "yoyow_assign_task_switch"=>$yoyow_assign_task_switch,
            "yoyow_trigger_task_switch"=>$yoyow_trigger_task_switch,
            "yoyow_trigger_task_scale"=>$yoyow_trigger_task_scale,
            "yoyow_trigger_task_time"=>explode(':',$yoyow_trigger_task_time),
            "default_distribute_yoyow_coin"=>$default_distribute_yoyow_coin
            );

        TPL::assign('setting', $setting_array);

        $this->crumb(AWS_APP::lang()->_t('YOYOW币设置'), 'admin/user/user_yoyow_coin_list');
        TPL::assign('menu_list', $this->model('admin')->fetch_menu_list(420));
        TPL::output('admin/user/yoyow_coin_settings');
    }

    public function user_trigger_task_execute_action(){
        $this->model('triggertask')->conin_task();
    }

        public function user_assign_task_execute_action(){
        $this->model('assigntask')->conin_task($_GET['ids']);
    }


    /**
     * 手动分配记录列表
     */
    public function assign_record_list_action(){
        $where=' task_id = ' . $_GET["id"];
//        if ($coin_list=$this->model('yoyowcoin')->fetch_page('user_yoyow_coin', $where, 'add_time DESC', $_GET['page'], $this->per_page))
        if ($coin_list=$this->model('yoyowcoin')->fetch_all('user_yoyow_coin', $where . ' AND type = 0 ', 'coin DESC, add_time DESC'))
        {
            for ($i=0; $i<count($coin_list); $i++)
            {
                $coin_list[$i]['user']=$this->model('account')->get_user_info_by_uid($coin_list[$i]['uid']);
            }
            TPL::assign('coin_list', $coin_list);
            $total_rows=$this->model('yoyowcoin')->count('user_yoyow_coin',$where. ' AND type = 0 ');
            /*TPL::assign('pagination', AWS_APP::pagination()->initialize(array(
                'base_url' => get_js_url('/admin/user/assign_record_list/'),
                'total_rows' => $total_rows,
                'per_page' => $this->per_page
            ))->create_links());*/
            TPL::assign('total_rows', $total_rows);
        }
        $statistics_result = $this->model('task')->fetch_all('task_statistics', $where);
        if(sizeof($statistics_result) == 1) {
            TPL::assign('statistics_result', $statistics_result[0]);
        }
        $this->crumb(AWS_APP::lang()->_t('用户YOYOW币记录'), 'admin/user/assign_record_list');
        TPL::assign('menu_list', $this->model('admin')->fetch_menu_list(421));
        TPL::output('admin/user/assign_record_list');
    }

    public function assign_integral_yoyow_record_list_action(){
        $coin_id = $_GET["coin_id"];
        $commission_single = $this->model('yoyowcoin')->fetch_row('user_yoyow_coin', ' id = '.$coin_id);
        $sql = 'SELECT add_time AS integral_time, "--" AS integral, coin, remark AS note FROM '. get_table('user_yoyow_coin').' WHERE type = 1 AND uid = '.$commission_single['uid']. ' AND task_id = '.$commission_single['task_id'];
        $commission_list = $this->model('yoyowcoin')->query_all($sql);
        $integral_coin_list=$this->model('yoyowcoin')->fetch_all('integral_yoyow_coin', ' coin_id = ' . $coin_id, 'integral_time DESC');
        TPL::assign('integral_coin_list', array_merge($integral_coin_list, $commission_list));
        TPL::assign('menu_list', $this->model('admin')->fetch_menu_list(421));
        TPL::output('admin/user/assign_integral_yoyow_list');
    }

    /**
     * 奖励分配任务
     */
    public function reward_task_list_action(){
        if ($task_list=$this->model('assigntask')->fetch_page('invitation_yoyow', 'effective = 1', 'time DESC, id DESC', $_GET['page'], $this->per_page))
        {
            foreach ($task_list AS $key =>$val){
                $task_list[$key]['coin_user_name'] = $this->model('assigntask')->fetch_one('users','user_name','uid ='.$val['coin_uid']);
                $task_list[$key]['base_user_name'] = $this->model('assigntask')->fetch_one('users','user_name','uid ='.$val['base_uid']);
                if($this->model('assigntask')->fetch_one('users_yoyow','yoyow','uid='.$val['coin_uid'])){
                    $task_list[$key]['bind'] = 1;
                }else{
                    $task_list[$key]['bind'] = 0;
                }
                switch ($val['coin_type']) {
                    case 0;
                        $task_list[$key]['mark'] = '注册奖励';
                        break;
                    case 1:
                        if($task_list[$key]['base_user_name'] == ''){
                            $task_list[$key]['mark'] = '被邀请用户--注册,获取的奖励';
                        }else{
                            $task_list[$key]['mark'] = '被邀请用户'.$task_list[$key]['base_user_name'].'注册,获取的奖励';
                        }

                        break;
                    case 2:
                        $second_id = $this->model('invitation')->fetch_one('invitation', 'uid', ' invitation_id = '. $val['invitation_id']);
                        if($second_id ==''){
                            $first['user_name']="--";
                        }else{
                            $first = $this->model('invitation')->fetch_row('users', ' uid = '.$second_id);
                        }

                        $task_list[$key]['mark'] = '被邀请用户'.$first['user_name'].'邀请新用户'.$task_list[$key]['base_user_name'].'注册,获取的奖励';
                        break;
                }
            }
            TPL::assign('task_list', $task_list);
            $total_rows=$this->model('assigntask')->count('invitation_yoyow','effective = 1');
            TPL::assign('pagination', AWS_APP::pagination()->initialize(array(
                'base_url' => get_js_url('/admin/user/reward_task_list/'),
                'total_rows' => $total_rows,
                'per_page' => $this->per_page
            ))->create_links());
        }
        $sql = "select sum(coin) coin from ".get_table('invitation_yoyow')."  where `has_ditribute`=0 and effective = 1";
        $issued_sum_yoyow = $this->model('project')->query_all($sql);
        if(!$issued_sum_yoyow[0]['coin']){
            $issued_sum_yoyows = 0;
        }else{
            $issued_sum_yoyows = $issued_sum_yoyow[0]['coin'];
        }
        TPL::assign('issued_sum_yoyow', $issued_sum_yoyows);
        //获取平台余额
        $api_url=get_setting('api_url');
        //调用接口获取平台账户信息 /api/v1/getAccount
        //请求地址
        $req_url = $api_url.'/api/v1/getAccount';
        //进行请求
        $res_content = http_get($req_url . '?uid=' . get_setting('platform_id'));
        $res_json = json_decode($res_content, true);
        TPL::assign('account_prepaid',$res_json['data']['statistics']['prepaid']/100000);

        $this->crumb(AWS_APP::lang()->_t('奖励分配任务'), 'admin/user/reward_task_list');
        TPL::assign('menu_list', $this->model('admin')->fetch_menu_list(420));
        TPL::output('admin/user/reward_task_list');
    }

    /**
     * 分享配置
     */
    public function share_setting_action(){

        $share_content=get_setting('share_content', false);
        $share_title=get_setting('share_title', false);
        $invitation_code_remark=get_setting('invitation_code_remark', false);
        $invite_start_time=get_setting('invite_start_time', false);
        $setting_array=array(
            "share_content"=>$share_content,
            "share_title"=>$share_title,
            "invitation_code_remark"=>$invitation_code_remark,
            "invite_start_time"=>$invite_start_time
        );

        TPL::assign('setting', $setting_array);
        $this->crumb(AWS_APP::lang()->_t('分享配置'), 'admin/user/share_config');
        TPL::assign('menu_list', $this->model('admin')->fetch_menu_list(422));
        TPL::output('admin/user/share_config');
    }


    //用户问题回复赞踩记录
    public function vote_list_action()
    {
        if ($_POST['action'] == 'search')
        {
            foreach ($_POST as $key => $val)
            {
                if ($key == 'start_date' OR $key == 'end_date')
                {
                    $val = base64_encode($val);
                }

                if ($key == 'type' OR $key == 'user_name' OR $key == 'voted_name')
                {
                    $val = rawurlencode($val);
                }

                $param[] = $key . '-' . $val;
            }

            H::ajax_json_output(AWS_APP::RSM(array(
                'url' => get_js_url('/admin/user/vote_list/' . implode('__', $param))
            ), 1, null));
        }

        $where = array();

        $page = 1;
        if($_GET['page']){
            $page = $_GET['page'];
        }
        $user_list = $this->model('integral')->query_vote_list($_GET['user_name'], $_GET['type'], $_GET['start_date'], $_GET['end_date'],$_GET['voted_name'], $page, $this->per_page);

        $total_rows = $this->model('integral')->query_vote_list_count($_GET['user_name'], $_GET['type'], $_GET['start_date'], $_GET['end_date'],$_GET['voted_name']);

        foreach ($user_list as $k =>$v){
            $user_list[$k]['answer_user_name'] = $this->model('account')->fetch_one('users','user_name','uid ='.$v['answer_uid']);
            $user_list[$k]['vote_user_name'] = $this->model('account')->fetch_one('users','user_name','uid ='.$v['vote_uid']);
            $user_list[$k]['answer_title'] = $this->model('account')->fetch_one('answer','answer_content','answer_id ='.$v['answer_id']);
            $vote_question_id = $this->model('account')->fetch_one('answer','question_id','answer_id ='.$v['answer_id']);
            $user_list[$k]['question_title'] = $this->model('account')->fetch_one('question','question_content','question_id ='.$vote_question_id);
        }
        $url_param = array();

        foreach($_GET as $key => $val)
        {
            if (!in_array($key, array('app', 'c', 'act', 'page')))
            {
                $url_param[] = $key . '-' . $val;
            }
        }

        TPL::assign('pagination', AWS_APP::pagination()->initialize(array(
            'base_url' => get_js_url('/admin/user/vote_list/') . implode('__', $url_param),
            'total_rows' => $total_rows,
            'per_page' => $this->per_page
        ))->create_links());

        $this->crumb(AWS_APP::lang()->_t('会员赞踩记录列表'), "admin/user/vote_list/");

        TPL::assign('total_rows', $total_rows);
        TPL::assign('list', $user_list);
        TPL::assign('menu_list', $this->model('admin')->fetch_menu_list(405));

        TPL::output('admin/user/vote_list');
    }

    //用户文章赞踩记录
    public function article_vote_list_action()
    {
        if ($_POST['action'] == 'search') {
            foreach ($_POST as $key => $val) {
                if ($key == 'start_date' OR $key == 'end_date') {
                    $val = base64_encode($val);
                }

                if ($key == 'type' OR $key == 'user_name' OR $key == 'voted_name') {
                    $val = rawurlencode($val);
                }

                $param[] = $key . '-' . $val;
            }

            H::ajax_json_output(AWS_APP::RSM(array(
                'url' => get_js_url('/admin/user/article_vote_list/' . implode('__', $param))
            ), 1, null));
        }

        $where = array();

        $page = 1;
        if ($_GET['page']) {
            $page = $_GET['page'];
        }
        $user_list = $this->model('article')->query_article_vote_list($_GET['user_name'], $_GET['type'], $_GET['start_date'], $_GET['end_date'], $_GET['voted_name'], $page, $this->per_page);

        $total_rows = $this->model('article')->query_article_vote_list_count($_GET['user_name'], $_GET['type'], $_GET['start_date'], $_GET['end_date'], $_GET['voted_name']);

        foreach ($user_list as $k => $v) {
            $user_list[$k]['article_user_name'] = $this->model('account')->fetch_one('users', 'user_name', 'uid =' . $v['item_uid']);
            $user_list[$k]['vote_user_name'] = $this->model('account')->fetch_one('users', 'user_name', 'uid =' . $v['uid']);
            $user_list[$k]['article_title'] = $this->model('account')->fetch_one('article', 'title', 'id =' . $v['item_id']);
        }
        $url_param = array();

        foreach ($_GET as $key => $val) {
            if (!in_array($key, array('app', 'c', 'act', 'page'))) {
                $url_param[] = $key . '-' . $val;
            }
        }

        TPL::assign('pagination', AWS_APP::pagination()->initialize(array(
            'base_url' => get_js_url('/admin/user/article_vote_list/') . implode('__', $url_param),
            'total_rows' => $total_rows,
            'per_page' => $this->per_page
        ))->create_links());

        $this->crumb(AWS_APP::lang()->_t('会员赞踩记录列表'), "admin/user/article_vote_list/");

        TPL::assign('total_rows', $total_rows);
        TPL::assign('list', $user_list);
        TPL::assign('menu_list', $this->model('admin')->fetch_menu_list(409));

        TPL::output('admin/user/article_vote_list');
    }

    /**
     * 查看邀请下线
     */
    public function invite_list_action()
    {
        $this->per_page = 20;
        $this->crumb(AWS_APP::lang()->_t('查看邀请下线'), 'admin/user/invite_list/');

        $invite_list = $this->model('account')->fetch_page('invitation_yoyow', 'coin_uid = '.$_GET['uid'].' and coin_type !=0', 'time desc,id desc', $_GET['page'], $this->per_page);
        TPL::assign('invite_list', $invite_list);
        TPL::assign('menu_list', $this->model('admin')->fetch_menu_list(402));
        $total_rows=$this->model('yoyowcoin')->count('invitation_yoyow','coin_uid = '.$_GET['uid'].' and coin_type !=0');
        //一级邀请数量
        $first_invite_num = $this->model('account')->count('invitation_yoyow','coin_uid = '.$_GET['uid'].' and coin_type =1');
        //二级邀请数量
        $second_invite_num = $this->model('account')->count('invitation_yoyow','coin_uid = '.$_GET['uid'].' and coin_type =2');
        TPL::assign('first_invite_num', $first_invite_num);
        TPL::assign('second_invite_num', $second_invite_num);
        TPL::assign('pagination', AWS_APP::pagination()->initialize(array(
            'base_url' => get_js_url('/admin/user/invite_list/uid-'.$_GET['uid']),
            'total_rows' => $total_rows,
            'per_page' => $this->per_page
        ))->create_links());
        TPL::output('admin/user/invite_list');

    }
}