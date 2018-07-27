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

class openid_yoyow_class extends AWS_MODEL
{

    public function bind_account($yoyow_name,$uid, $is_ajax = false)
    {
        if (!$yoyow_name)
        {
            if ($is_ajax)
            {
                H::ajax_json_output(AWS_APP::RSM(null, -1, AWS_APP::lang()->_t('与YOYOW通信出错, 请重新登录')));
            }
            else
            {
                H::redirect_msg(AWS_APP::lang()->_t('与YOYOW通信出错, 请重新登录'));
            }
        }

        if($this->get_yoyow_user_by_name($yoyow_name, $uid)){
            return false;
        }

        if ($yoyow_info = $this->get_yoyow_user_by_uid($uid))
        {

            $this->update('users_yoyow', array(
                'yoyow' => $yoyow_name,
                'bindtime' => time()
            ),'uid= '.$uid);
        }else{

            $this->insert('users_yoyow', array(
                'uid' => intval($uid),
                'yoyow' => $yoyow_name,
                'bindtime' => time()
            ));
        }
        return true;
    }

    public function get_yoyow_user_by_uid($uid)
    {
        if(!$uid){
            return false;
        }
        return $this->fetch_row('users_yoyow', "uid = " . $uid);
    }

    public function get_yoyow_user_by_name($yoyow_name, $uid)
    {
        if(!$yoyow_name){
            return false;
        }
        if($uid){
            $whereuid = " and uid != ". $this->quote($uid);
        }

        return $this->fetch_row('users_yoyow', "yoyow = '" . $this->quote($yoyow_name) ."'". $whereuid);
    }

    public function is_yoyow_user_by_uid($uid)
    {
        if (!is_digits($uid))
        {
            return false;
        }

        static $yoyow_user_info;

        if (!$yoyow_user_info[$uid])
        {
            $yoyow_user_info[$uid] = $this->fetch_row('users_yoyow', 'uid = ' . $uid);
        }

        return $yoyow_user_info[$uid];
    }

    public function unbind_account($uid)
    {
        if (!is_digits($uid))
        {
            return false;
        }

        return $this->delete('users_yoyow', 'uid = ' . $uid);
    }

    public function request_client_login_token($session_id)
    {
        $this->delete('yoyow_login', "session_id = '" . $this->quote($session_id) . "'");
        $this->delete('yoyow_login', 'expire <' . time());

        $token = rand(11111111, 99999999);

        if ($this->fetch_row('yoyow_login', "token = " . $token))
        {
            return $this->request_client_login_token($session_id);
        }

        $this->insert('yoyow_login', array(
            'token' => $token,
            'session_id' => $session_id,
            'expire' => (time() + 300)
        ));

        return $token;
    }

    public function process_client_login($token, $yoyow)
    {
        if ($this->fetch_row('yoyow_login', 'yoyow = ' . intval($yoyow) . " AND token = '" . intval($token) . "'"))
        {
            return true;
        }

        return $this->update('yoyow_login', array(
            'yoyow' => intval($yoyow)
        ), "token = '" . intval($token) . "'");
    }

    public function yoyow_login_process($session_id)
    {
        $yoyow_login = $this->fetch_row('yoyow_login', "session_id = '" . $this->quote($session_id) . "' AND expire >= " . time());

        if ($yoyow_login['yoyow'])
        {
            $this->delete('yoyow_login', "session_id = '" . $this->quote($session_id) . "'");

            return $yoyow_login['yoyow'];
        }
    }
}
