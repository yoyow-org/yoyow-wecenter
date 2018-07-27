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

class main extends AWS_ADMIN_CONTROLLER
{
    public function index_action()
    {
        $this->crumb(AWS_APP::lang()->_t('概述'), 'admin/main/');

        if (!defined('IN_SAE'))
        {
            $writable_check = array(
                'cache' => is_really_writable(ROOT_PATH . 'cache/'),
                'tmp' => is_really_writable(ROOT_PATH . './tmp/'),
                get_setting('upload_dir') => is_really_writable(get_setting('upload_dir'))
            );

            TPL::assign('writable_check', $writable_check);
        }

        TPL::assign('users_count', $this->model('system')->count('users'));
        TPL::assign('users_valid_email_count', $this->model('system')->count('users', 'valid_email = 1'));
        TPL::assign('question_count', $this->model('system')->count('question'));
        TPL::assign('answer_count', $this->model('system')->count('answer'));
        TPL::assign('question_count', $this->model('system')->count('question'));
        TPL::assign('question_no_answer_count', $this->model('system')->count('question', 'answer_count = 0'));
        TPL::assign('best_answer_count', $this->model('system')->count('question', 'best_answer > 0'));
        TPL::assign('topic_count', $this->model('system')->count('topic'));
        TPL::assign('attach_count', $this->model('system')->count('attach'));
        TPL::assign('approval_question_count', $this->model('publish')->count('approval', "type = 'question'"));
        TPL::assign('approval_answer_count', $this->model('publish')->count('approval', "type = 'answer'"));

        $admin_menu = (array)AWS_APP::config()->get('admin_menu');

        $admin_menu[0]['select'] = true;

        TPL::assign('menu_list', $admin_menu);

        TPL::output('admin/index');
    }

    public function login_action()
    {
        if (!$this->user_info['permission']['is_administortar'] AND !$this->user_info['permission']['is_moderator'])
        {
            H::redirect_msg(AWS_APP::lang()->_t('你没有访问权限, 请重新登录'), '/');
        }
        else if (AWS_APP::session()->admin_login)
        {
            $admin_info = json_decode(AWS_APP::crypt()->decode(AWS_APP::session()->admin_login), true);

            if ($admin_info['uid'])
            {
                HTTP::redirect('/admin/');
            }
        }

        TPL::import_css('admin/css/login.css');

        TPL::output('admin/login');
    }

    public function logout_action($return_url = '/')
    {
        $this->model('admin')->admin_logout();

        HTTP::redirect($return_url);
    }

    public function settings_action()
    {
        $this->crumb(AWS_APP::lang()->_t('系统设置'), 'admin/settings/');
        TPL::import_js('admin/js/setting_extend.js');
        if (!$this->user_info['permission']['is_administortar'])
        {
            H::redirect_msg(AWS_APP::lang()->_t('你没有访问权限, 请重新登录'), '/');
        }

        if (!$_GET['category'])
        {
            $_GET['category'] = 'site';
        }

        switch ($_GET['category'])
        {
            case 'interface':
                TPL::assign('styles', $this->model('setting')->get_ui_styles());
            break;
            case 'register':
                TPL::assign('notification_settings', get_setting('new_user_notification_setting'));
                TPL::assign('notify_actions', $this->model('notify')->notify_action_details);
            break;
        }

        TPL::assign('setting', get_setting(null, false));
        

        TPL::assign('menu_list', $this->model('admin')->fetch_menu_list('SETTINGS_' . strtoupper($_GET['category'])));

        TPL::output('admin/settings');
    }

    public function nav_menu_action()
    {
        $this->crumb(AWS_APP::lang()->_t('导航设置'), 'admin/nav_menu/');

        if (!$this->user_info['permission']['is_administortar'])
        {
            H::redirect_msg(AWS_APP::lang()->_t('你没有访问权限, 请重新登录'), '/');
        }

        TPL::assign('nav_menu_list', $this->model('menu')->get_nav_menu_list());

        TPL::assign('category_list', $this->model('system')->build_category_html('question', 0, 0, null, true));

        TPL::assign('setting', get_setting());

        TPL::import_js(array(
            'js/fileupload.js',
        ));

        TPL::assign('menu_list', $this->model('admin')->fetch_menu_list(307));

        TPL::output('admin/nav_menu');
    }

    public function site_announce_list_action(){

        if($announce_list=$this->model('help')->fetch_all('site_announce','status = 0')){
            TPL::assign('announce_list', $announce_list);
        }
        $this->crumb(AWS_APP::lang()->_t('网站公告'), 'admin/announce/site_announce_list');
        TPL::assign('menu_list', $this->model('admin')->fetch_menu_list(313));
        TPL::output('admin/announce/site_announce_list');
    }

    public function site_announce_edit_action(){
        if(!empty($_GET["id"])){
            $announce=$this->model('help')->query_announce_by_id($_GET["id"]);
            $this->crumb(AWS_APP::lang()->_t('编辑网站公告'), 'admin/announce/site_announce_list');
            TPL::assign('announce', $announce);
        }else{
            $this->crumb(AWS_APP::lang()->_t('新增网站公告'), 'admin/announce/site_announce_list');
        }
        TPL::assign('menu_list', $this->model('admin')->fetch_menu_list(313));
        TPL::output('admin/announce/site_announce_edit');
    }

    public function site_announce_del_action(){
        if(!empty($_POST["id"])){
            if(is_array($_POST["id"])){
                $where="id in (".implode(',',$_POST["id"]).")";
                    $save_data=array(
                        'status'=>'1'
                    );

                    $this->model('help')->update('site_announce',$save_data,$where);
            }else{
                $where='id = '.$_POST['id'];
                $save_data=array(
                    'status'=>1
                );
                $this->model('help')->update('site_announce',$save_data,$where);
            }
            H::ajax_json_output(AWS_APP::RSM(array(
                'url' => get_js_url('/admin/site_announce_list/')
            ), 1, null));
        }else{
            H::ajax_json_output(AWS_APP::RSM(null, -1, AWS_APP::lang()->_t('请选择要删除的任务')));
        }
    }


    public function site_link_list_action(){
        if($link_list=$this->model('help')->fetch_all('site_link',null,'sort ASC,id ASC')){
            TPL::assign('link_list', $link_list);
        }
        $this->crumb(AWS_APP::lang()->_t('网站底部链接'), 'admin/sitelinkurl/site_link_url_list');
        TPL::assign('menu_list', $this->model('admin')->fetch_menu_list(314));
        TPL::output('admin/sitelinkurl/site_link_url_list');
    }

    public function site_link_edit_action(){
        if(!empty($_GET["id"])){
            $linkurl=$this->model('help')->query_link_by_id($_GET["id"]);
            $this->crumb(AWS_APP::lang()->_t('编辑网站底部链接'), 'admin/sitelinkurl/site_link_url_list');
            TPL::assign('linkurl', $linkurl);
        }else{
            $this->crumb(AWS_APP::lang()->_t('新增网站底部链接'), 'admin/sitelinkurl/site_link_url_list');
        }
        TPL::assign('menu_list', $this->model('admin')->fetch_menu_list(314));
        TPL::output('admin/sitelinkurl/site_link_url_edit');
    }


    public function site_link_del_action(){
        if(!empty($_POST["id"])){
            if(is_array($_POST["id"])){
                $where="id in (".implode(',',$_POST["id"]).")";
                $this->model('help')->delete('site_link',$where);
            }else{
                $where='id = '.$_POST['id'];

                $this->model('help')->delete('site_link',$where);
            }
            H::ajax_json_output(AWS_APP::RSM(array(
                'url' => get_js_url('/admin/site_link_list/')
            ), 1, null));
        }else{
            H::ajax_json_output(AWS_APP::RSM(null, -1, AWS_APP::lang()->_t('请选择要删除的任务')));
        }
    }

    /**
     * 首页活动
     */
    public function index_activity_list_action(){
        if($index_activity_list=$this->model('help')->fetch_all('index_activity')){
            TPL::assign('index_activity_list', $index_activity_list);
        }
        $this->crumb(AWS_APP::lang()->_t('首页活动配置'), 'admin/indexactivity/index_activity_list');
        TPL::assign('menu_list', $this->model('admin')->fetch_menu_list(317));
        TPL::output('admin/indexactivity/index_activity_list');
    }


    public function index_activity_edit_action(){
        if(!empty($_GET["id"])){
            $index_activity=$this->model('help')->query_index_activity_by_id($_GET["id"]);
            $this->crumb(AWS_APP::lang()->_t('编辑首页活动配置'), 'admin/indexactivity/index_activity_list');
            TPL::assign('index_activity', $index_activity);
        }else{
            $this->crumb(AWS_APP::lang()->_t('新增首页活动配置'), 'admin/indexactivity/index_activity_list');
        }
        TPL::assign('menu_list', $this->model('admin')->fetch_menu_list(317));
        TPL::output('admin/indexactivity/index_activity_edit');
    }

    public function  index_activity_del_action(){
        if(!empty($_POST["id"])){
            if(is_array($_POST["id"])){
                $where="id in (".implode(',',$_POST["id"]).")";
                $this->model('help')->delete('index_activity',$where);
            }else{
                $where='id = '.$_POST['id'];

                $this->model('help')->delete('index_activity',$where);
            }
            H::ajax_json_output(AWS_APP::RSM(array(
                'url' => get_js_url('/admin/index_activity_list/')
            ), 1, null));
        }else{
            H::ajax_json_output(AWS_APP::RSM(null, -1, AWS_APP::lang()->_t('请选择要删除的任务')));
        }
    }
}