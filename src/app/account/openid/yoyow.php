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
require_once(AWS_PATH . 'yoyowauth/yoyowauth.php');

if (!defined('IN_ANWSION')) {
    die;
}

class openid_yoyow extends AWS_CONTROLLER
{
    public function get_access_rule()
    {
        $rule_action['rule_type'] = 'white';

        $rule_action['actions'] = array(
            'bind',
            'unbind',
            'login',
            'qr_login',
            'bind_user'
        );

        return $rule_action;
    }

    public function setup()
    {
        HTTP::no_cache_header();

        if (get_setting('yoyow_login_enabled') != 'Y' OR !get_setting('api_url') OR !get_setting('platform_id')) {
            H::redirect_msg(AWS_APP::lang()->_t('本站未开通 YOYOW 登录'), '/');
        }
    }

    public function bind_action()
    {
        $yoyow_auth = new yoyowauth;
        if ($_GET['yoyow'] && $_GET['time'] && $_GET['sign'] && $_GET['state']) {
            $yoyow = $yoyow_auth->callback($_GET['yoyow'], $_GET['time'], $_GET['sign']);
            if ($yoyow) {
                if ((string)$_GET['state'] == 'edit' || $this->user_id) {
                    $res = $this->model('openid_yoyow')->bind_account($_GET['yoyow'], intval($this->user_id), true);
                    $redirect_url =  $_GET['redirecturl'] ? $_GET['redirecturl'] : '/account/setting/openid/message-1';
                    if (!$res) {
                        $message = "绑定失败,YOYOW号".$_GET['yoyow']."已绑定其他账号";
                        if($_GET['redirecturl']) {
                            H::redirect_msg(AWS_APP::lang()->_t($message), '/people/' . $this->user_info['user_name']);
                            return;
                        }
                    } else{
                        /**************** 20180426-20180430活动 绑定yoyow账号送10个yoyow币 jiangchengkai  2018-04-19 start *************************/
                        if(!$this->model('account')->get_register_send_yoyow_record($this->user_id, 2) &&
                            $this->model('account')->get_register_send_yoyow_record($this->user_id, 1) &&
                            $this->model('account')->is_send_yoyow()){
                            $this->model('account')->register_send_yoyow($this->user_id, 2, get_setting('register_bind_reward'));
                        }
                        /**************** 20180426-20180430活动 绑定yoyow账号送10个yoyow币 jiangchengkai  2018-04-19 end *************************/
                        if ((string)$_GET['state'] == 'edit') {
                            $message = "修改成功";
                        } else {
                            $message = "绑定成功";
                        }
                        if($_GET['redirecturl']) {
                            HTTP::redirect($_GET['redirecturl']);
                            return;
                        }
                    }
                    setcookie('message_' . $this->user_id, $message, time() + 3600 * 24, "/");
                    HTTP::redirect($redirect_url);
                } else {
                    if (!$yoyow_user = $this->model('openid_yoyow')->get_yoyow_user_by_name($_GET['yoyow'])) {
                        $this->crumb(AWS_APP::lang()->_t('完善资料'), '/account/login/');
                        TPL::assign('register_url', 'account/ajax/yoyow/register/');
                        TPL::assign('user_name', $_GET['yoyow']);
                        TPL::import_css('css/register.css');
                        TPL::output('account/openid/callback');
                    } else {
                        $user = $this->model('account')->get_user_info_by_uid($yoyow_user['uid']);
                        if (get_setting('register_valid_type') == 'approval' AND $user['group_id'] == 3) {
                            $redirect_url = $_GET['redirecturl'] ? $_GET['redirecturl'] : '/account/valid_approval/';
                        } else {
                            if (get_setting('ucenter_enabled') == 'Y') {
                                $redirect_url = '/account/sync_login/';
                            } else {
                                $redirect_url = '/';
                            }
                            HTTP::set_cookie('_user_login', get_login_cookie_hash($user['user_name'], $user['password'], $user['salt'], $user['uid'], false));
                        }
                        HTTP::redirect($redirect_url);
                    }
                }
            } else{
                H::redirect_msg(AWS_APP::lang()->_t('绑定失败'), '/');
            }
        }
        else {
            $url = get_setting('api_url') . '/auth/sign';
            $yoyow_array = http_get($url);
            if ($_GET['type'] == 'edit') {
                $yoyow_auth->login($yoyow_array, '', $_GET['type'], $_GET['redirecturl']);
            } else {
                $yoyow_auth->login($yoyow_array, '', '', $_GET['redirecturl']);
            }
        }
    }

    public function login_action()
    {
        $yoyow_auth = new yoyowauth;
        if ($_GET['yoyow'] && $_GET['time'] && $_GET['sign'] && $_GET['state']) {
            $yoyow = $yoyow_auth->callback($_GET['yoyow'], $_GET['time'], $_GET['sign']);
            if ($yoyow) {
                $this->model('openid_yoyow')->bind_account($_GET['yoyow'], intval($_GET['state']), true);

                $user = $this->model('account')->get_user_info_by_uid(intval($_GET['state']));
                if (get_setting('register_valid_type') == 'approval' AND $user['group_id'] == 3) {
                    $redirect_url = '/account/valid_approval/';
                } else {
                    if (get_setting('ucenter_enabled') == 'Y') {
                        $redirect_url = '/account/sync_login/';
                    } else {
                        setcookie('yoyow_message_' . $this->user_id, $_GET['yoyow'], time() + 3600 * 24);
                        $redirect_url = '/';
                    }
                    HTTP::set_cookie('_user_login', get_login_cookie_hash($user['user_name'], $user['password'], $user['salt'], $user['uid'], false));
                }
                HTTP::redirect($redirect_url);
            }
        } else {
            $url = get_setting('api_url') . '/auth/sign';
            $yoyow_array = http_get($url);
            $yoyow_auth->login($yoyow_array, $_GET['uid']);
        }
    }

    public function qr_login_action()
    {
        $yoyow_auth = new yoyowauth;

        // 处理回调参数
        $postbody_str = file_get_contents('php://input');
        $post_arr = json_decode($postbody_str, true);
        $state_arr = json_decode($post_arr['state'], true);

        // 校验yoyow回调参数
        $yoyow = $yoyow_auth->callback($post_arr['yoyow'], $post_arr['time'], $post_arr['sign']);
        if(!$yoyow) {
            $rntMsg = array(
                'code' => 1,
                'message' => '授权失败',
            );
        } else {
            if ($this->model('openid_yoyow')->process_client_login($state_arr['token'], $post_arr['yoyow'])) {
                $rntMsg = array(
                    'code' => 0,
                    'message' => 'success',
                );
            }
            else {
                $rntMsg = array(
                    'code' => 1,
                    'message' => '二维码超时，请重新扫码',
                );
            }
        }

        H::ajax_json_output($rntMsg);
    }

    public function unbind_action()
    {
        $this->model('openid_yoyow')->unbind_account($this->user_id);

        HTTP::redirect('/account/setting/openid/');
    }

    public function bind_user_action()
    {
        $this->crumb(AWS_APP::lang()->_t('完善资料'), '/account/login/');
        TPL::assign('register_url', 'account/ajax/yoyow/register/');
        TPL::assign('user_name', $_GET['yoyow']);
        TPL::import_css('css/register.css');
        TPL::output('account/openid/callback');
    }
}
