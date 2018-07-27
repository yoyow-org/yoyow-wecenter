<?php
/**
 * WeCenter Framework
 *
 * An open source application development framework for PHP 5.2.2 or newer
 *
 * @package     WeCenter Framework
 * @author      WeCenter Dev Team
 * @copyright   Copyright (c) 2011 - 2014, WeCenter, Inc.
 * @license     http://www.wecenter.com/license/
 * @link        http://www.wecenter.com/
 * @since       Version 1.0
 * @filesource
 */

/**
 * WeCenter APP 函数类
 *
 * @package     WeCenter
 * @subpackage  App
 * @category    Model
 * @author      WeCenter Dev Team
 */


if (!defined('IN_ANWSION'))
{
    die;
}

class account_class extends AWS_MODEL
{
    /**
     * 检查用户名是否已经存在
     *
     * @param string
     * @return boolean
     */
    public function check_username($user_name)
    {
    	$user_name = trim($user_name);

        return $this->fetch_one('users', 'uid', "user_name = '" . $this->quote($user_name) . "' OR url_token = '" . $this->quote($user_name) . "'");
    }

    /**
     * 检查用户名中是否包含敏感词或用户信息保留字
     *
     * @param string
     * @return boolean
     */
    public function check_username_sensitive_words($user_name)
    {
        if (H::sensitive_word_exists($user_name))
        {
            return true;
        }

        if (!get_setting('censoruser'))
        {
            return false;
        }

        if ($censorusers = explode("\n", get_setting('censoruser')))
        {
            foreach ($censorusers as $name)
            {
                if (!$name = trim($name))
                {
                    continue;
                }

                if (preg_match('/(' . $name . ')/is', $user_name))
                {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * 检查用户 ID 是否已经存在
     *
     * @param string
     * @return boolean
     */

    public function check_uid($uid)
    {
        return $this->fetch_one('users', 'uid', 'uid = ' . intval($uid));
    }

    /**
     * 检查电子邮件地址是否已经存在
     *
     * @param string
     * @return boolean
     */
    public function check_email($email)
    {
        if (! H::valid_email($email))
        {
            return TRUE;
        }

        return $this->fetch_one('users', 'uid', "email = '" . $this->quote($email) . "'");
    }

    public function check_mobile($mobile)
    {

        return $this->fetch_one('users', 'uid', "mobile = '" . $this->quote($mobile) . "'");
    }

    /**
     * 用户登录验证
     *
     * @param string
     * @param string
     * @return array
     */
    public function check_login($user_name, $password)
    {
        if (!$user_name OR !$password)
        {
            return false;
        }

        if($user_name=="123@qq.com"){
            return false;
        }
        if (H::valid_email($user_name))
        {
            $user_info = $this->get_user_info_by_email($user_name);
        }

        if (! $user_info)
        {
            if (! $user_info = $this->get_user_info_by_username($user_name))
            {
                if(!$user_info=$this->get_user_info_by_mobile($user_name)){
                    return false;
                }

            }
        }

        if (! $this->check_password($password, $user_info['password'], $user_info['salt']))
        {
            return false;
        }
        else
        {
            return $user_info;
        }

    }

    /**
     * 用户登录验证 (MD5 验证)
     *
     * @param string
     * @param string
     * @return array
     */
    public function check_hash_login($user_name, $password_md5)
    {
        if (!$user_name OR !$password_md5)
        {
            return false;
        }

        if (H::valid_email($user_name))
        {
            $user_info = $this->get_user_info_by_email($user_name);
        }

        if (! $user_info)
        {
            if (! $user_info = $this->get_user_info_by_username($user_name))
            {
                if(!$user_info = $this->get_user_info_by_mobile($user_name)){
                    return false;
                }

            }
        }

        if ( $password_md5 != $user_info['password'])
        {
            return false;
        }
        else
        {
            return $user_info;
        }

    }

    /**
     * 用户密码验证
     *
     * @param string
     * @param string
     * @param string
     * @return boolean
     */
    public function check_password($password, $db_password, $salt)
    {
        $password = compile_password($password, $salt);

        if ($password == $db_password)
        {
            return true;
        }

        return false;

    }

    /**
     * 通过用户名获取用户信息
     *
     * $attrb 为是否获取附加表信息, $cache_result 为是否缓存结果
     *
     * @param string
     * @param boolean
     * @param boolean
     * @return array
     */
    public function get_user_info_by_username($user_name, $attrb = false, $cache_result = true)
    {
        if ($uid = $this->fetch_one('users', 'uid', "user_name = '" . $this->quote($user_name) . "'"))
        {
            return $this->get_user_info_by_uid($uid, $attrb, $cache_result);
        }
    }

    /**
     * 通过用户邮箱获取用户信息
     *
     * $cache_result 为是否缓存结果
     *
     * @param string
     * @return array
     */
    public function get_user_info_by_email($email, $cache_result = true)
    {
        if (!H::valid_email($email))
        {
            return false;
        }

        if ($uid = $this->fetch_one('users', 'uid', "email = '" . $this->quote($email) . "'"))
        {
            return $this->get_user_info_by_uid($uid, $attrb, $cache_result);
        }
    }

    public function get_user_info_by_mobile($mobile, $cache_result = true)
    {

        if ($uid = $this->fetch_one('users', 'uid', "mobile = '" . $this->quote($mobile) . "'"))
        {
            return $this->get_user_info_by_uid($uid, $attrb, $cache_result);
        }
    }

    /**
     * 通过 URL TOKEN 获取用户信息
     *
     * $cache_result 为是否缓存结果
     *
     * @param string
     * @param boolean
     * @param boolean
     * @return array
     */
    public function get_user_info_by_url_token($url_token, $attrb = false, $cache_result = true)
    {
        if (!$url_token)
        {
            return false;
        }

        if ($uid = $this->fetch_one('users', 'uid', "url_token = '" . $this->quote($url_token) . "'"))
        {
            return $this->get_user_info_by_uid($uid, $attrb, $cache_result);
        }
    }

    /**
     * 通过 UID 获取用户信息
     *
     * $cache_result 为是否缓存结果
     *
     * @param string
     * @param boolean
     * @param boolean
     * @return array
     */
    public function get_user_info_by_uid($uid, $attrib = false, $cache_result = true)
    {
        if (! $uid)
        {
            return false;
        }

        if ($uid == -1)
        {
            return array(
                'uid' => -1,
                'user_name' => AWS_APP::lang()->_t('[已注销]'),
            );
        }

        if ($cache_result)
        {
            static $users_info;

            if ($users_info[$uid . '_attrib'])
            {
                return $users_info[$uid . '_attrib'];
            }
            else if ($users_info[$uid])
            {
                return $users_info[$uid];
            }
        }

        if (! $user_info = $this->fetch_row('users', 'uid = ' . intval($uid)))
        {
            return false;
        }

        if ($attrib)
        {
	        if ($user_attrib = $this->fetch_row('users_attrib', 'uid = ' . intval($uid)))
	        {
		        foreach ($user_attrib AS $key => $val)
		        {
			        $user_info[$key] = $val;
		        }
	        }
        }

        if (!$user_info['url_token'] AND $user_info['user_name'])
        {
            $user_info['url_token'] = urlencode($user_info['user_name']);
        }

        if ($user_info['email_settings'])
        {
            $user_info['email_settings'] = unserialize($user_info['email_settings']);
        }
        else
        {
            $user_info['email_settings'] = array();
        }

        if ($user_info['weixin_settings'])
        {
            $user_info['weixin_settings'] = unserialize($user_info['weixin_settings']);
        }
        else
        {
            $user_info['weixin_settings'] = array();
        }

        if($user_info['uid'])
        {
            $user_info['invitation_code'] = $this->fetch_one('users_invitation_code','invitation_code','uid='.$user_info['uid']);
        }

        

        $users_info[$uid] = $user_info;

        if ($attrib)
        {
            unset($users_info[$uid]);

            $users_info[$uid . '_attrib'] = $user_info;
        }

        return $user_info;
    }

    /**
     * 通过 UID 获取用户信息
     *
     * $cache_result 为是否缓存结果
     *
     * @param string
     * @param boolean
     * @param boolean
     * @return array
     */
    public function get_reputation_user_info_by_uid($uid, $attrib = false, $cache_result = true)
    {
        if (! $uid)
        {
            return false;
        }

        if ($uid == -1)
        {
            return array(
                'uid' => -1,
                'user_name' => AWS_APP::lang()->_t('[已注销]'),
            );
        }

        if ($cache_result)
        {
            static $users_info;

            if ($users_info[$uid . '_attrib'])
            {
                return $users_info[$uid . '_attrib'];
            }
            else if ($users_info[$uid])
            {
                return $users_info[$uid];
            }
        }

        if (! $user_info = $this->fetch_row('users', 'uid = ' . intval($uid)))
        {
            return false;
        }

        if ($attrib)
        {
            if ($user_attrib = $this->fetch_row('users_attrib', 'uid = ' . intval($uid)))
            {
                foreach ($user_attrib AS $key => $val)
                {
                    $user_info[$key] = $val;
                }
            }
        }

        if (!$user_info['url_token'] AND $user_info['user_name'])
        {
            $user_info['url_token'] = urlencode($user_info['user_name']);
        }

        if ($user_info['email_settings'])
        {
            $user_info['email_settings'] = unserialize($user_info['email_settings']);
        }
        else
        {
            $user_info['email_settings'] = array();
        }

        if ($user_info['weixin_settings'])
        {
            $user_info['weixin_settings'] = unserialize($user_info['weixin_settings']);
        }
        else
        {
            $user_info['weixin_settings'] = array();
        }

        if($user_info['uid'])
        {
            $user_info['invitation_code'] = $this->fetch_one('users_invitation_code','invitation_code','uid='.$user_info['uid']);
        }



        $users_info[$uid] = $user_info;

        if ($attrib)
        {
            unset($users_info[$uid]);

            $users_info[$uid . '_attrib'] = $user_info;
        }

        return $user_info;
    }

    /**
     * 通过 UID 数组获取用户信息
     *
     * @param arrary
     * @param boolean
     * @return array
     */
    public function get_user_info_by_uids($uids, $attrib = false)
    {
        if (! is_array($uids) OR sizeof($uids) == 0)
        {
            return false;
        }

        array_walk_recursive($uids, 'intval_string');

        $uids = array_unique($uids);

        if (sizeof($uids) == 1)
        {
            if ($one_user_info = $this->get_user_info_by_uid(end($uids), $attrib))
            {
                return array(
                    end($uids) => $one_user_info
                );
            }

        }

        static $users_info;

        if ($users_info[implode('_', $uids) . '_attrib'])
        {
            return $users_info[implode('_', $uids) . '_attrib'];
        }
        else if ($users_info[implode('_', $uids)])
        {
            return $users_info[implode('_', $uids)];
        }

        if ($user_info = $this->fetch_all('users', "uid IN(" . implode(',', $uids) . ")"))
        {
            foreach ($user_info as $key => $val)
            {
                if (!$val['url_token'])
                {
                    $val['url_token'] = urlencode($val['user_name']);
                }

                if ($val['email_settings'])
                {
                    $val['email_settings'] = unserialize($val['email_settings']);
                }
                else
                {
                    $val['email_settings'] = array();
                }

                if ($val['weixin_settings'])
                {
                    $val['weixin_settings'] = unserialize($val['weixin_settings']);
                }
                else
                {
                    $val['weixin_settings'] = array();
                }

                unset($val['password'], $val['salt']);

                $data[$val['uid']] = $val;

                $query_uids[] = $val['uid'];
            }

            foreach ($uids AS $uid)
            {
                if ($uid == -1)
                {
                    $result['-1'] = array(
                        'uid' => -1,
                        'user_name' => AWS_APP::lang()->_t('[已注销]'),
                    );
                }
                else if ($data[$uid])
                {
                    $result[$uid] = $data[$uid];
                }
            }

            $users_info[implode('_', $uids)] = $data;
        }

        if ($attrib AND $query_uids)
        {
            if ($users_attrib = $this->fetch_all('users_attrib', 'uid IN(' . implode(',', $query_uids) . ')'))
            {
                foreach ($users_attrib AS $key => $val)
                {
                    unset($val['id']);

                    foreach ($val AS $attrib_key => $attrib_val)
                    {
                        $result[$val['uid']][$attrib_key] = $attrib_val;
                    }
                }
            }

            unset($users_info[implode('_', $uids)]);

            $users_info[implode('_', $uids) . '_attrib'] = $result;
        }

        return $result;
    }

    /**
     * 根据用户ID获取用户通知设置
     * @param $uid
     */
    public function get_notification_setting_by_uid($uid)
    {
        if (!$setting = $this->fetch_row('users_notification_setting', 'uid = ' . intval($uid)))
        {
            return array('data' => array());
        }

        $setting['data'] = unserialize($setting['data']);

        if (!$setting['data'])
        {
            $setting['data'] = array();
        }

        return $setting;
    }

    /**
     * 插入用户数据
     *
     * @param string
     * @param string
     * @param string
     * @param int
     * @param string
     * @return int
     */
    public function insert_user($user_name, $password, $email = null, $sex = 0, $mobile = null)
    {
        if (!$user_name OR !$password)
        {
            return false;
        }

        /*if ($this->check_username($user_name))
        {
            return false;
        }*/

        /*if ($email AND $user_info = $this->get_user_info_by_email($email, false))
        {
            return false;
        }*/

        if ($mobile AND $user_info = $this->get_user_info_by_mobile($mobile, false))
        {
            return false;
        }

        $salt = fetch_salt(4);
        if ($uid = $this->insert('users', array(
            'user_name' => htmlspecialchars($user_name),
            'password' => compile_password($password, $salt),
            'salt' => $salt,
            'email' => htmlspecialchars($email),
            'sex' => intval($sex),
            'mobile' => htmlspecialchars($mobile),
            'reg_time' => time(),
            'reg_ip' => ip2long(fetch_ip()),
            'email_settings' => serialize(get_setting('new_user_email_setting'))
        )))
        {

            $this->insert('users_attrib', array(
                'uid' => $uid
            ));

            $this->update_notification_setting_fields(get_setting('new_user_notification_setting'), $uid);

            //$this->model('search_fulltext')->push_index('user', $user_name, $uid);
        }

        return $uid;
    }

    /**
     * 注册用户
     *
     * @param string
     * @param string
     * @param string
     * @param string
     * @return int
     */
    public function user_register($user_name, $password = null, $email = null, $mobile)
    {
        if ($uid = $this->insert_user($user_name, $password, $email,0,$mobile))
        {
            if ($def_focus_uids_str = get_setting('def_focus_uids'))
            {
                $def_focus_uids = explode(',', $def_focus_uids_str);

                foreach ($def_focus_uids as $key => $val)
                {
                    $this->model('follow')->user_follow_add($uid, $val);
                }
            }

            $this->update('users', array(
                'group_id' => 3,
                'reputation_group' => 5,
                'invitation_available' => get_setting('newer_invitation_num'),
                'is_first_login' => 1
            ), 'uid = ' . intval($uid));

            $this->model('integral')->process($uid, 'REGISTER', get_setting('integral_system_config_register'), '初始资本');
        }

        return $uid;
    }

    /**
     * 发送欢迎信息
     *
     * @param int
     * @param string
     */
    public function welcome_message($uid, $user_name)
    {
        if (get_setting('welcome_message_pm'))
        {
            $this->model('message')->send_message($uid, $uid, str_replace(array('{username}', '{time}', '{sitename}'), array($user_name, date('Y-m-d H:i:s', time()), get_setting('site_name')), get_setting('welcome_message_pm')));
        }
    }

    /**
     * 更新用户表字段
     *
     * @param array
     * @param uid
     * @return int
     */
    public function update_users_fields($update_data, $uid)
    {
        return $this->update('users', $update_data, 'uid = ' . intval($uid));
    }

    /**
     * 更新用户名
     *
     * @param string
     * @param uid
     */
    public function update_user_name($user_name, $uid)
    {
        $this->update('users', array(
            'user_name' => htmlspecialchars($user_name),
        ), 'uid = ' . intval($uid));

        //return $this->model('search_fulltext')->push_index('user', $user_name, $uid);

        return true;
    }

    /**
     * 更新用户附加表状态或字段
     *
     * @param array
     * @param uid
     * @return int
     */
    public function update_users_attrib_fields($update_data, $uid)
    {
        return $this->update('users_attrib', $update_data, 'uid = ' . intval($uid));
    }

    /**
     * 更改用户密码
     *
     * @param  string
     * @param  string
     * @param  int
     * @param  string
     */
    public function update_user_password($oldpassword, $password, $uid, $salt)
    {
        if (!$salt OR !$uid)
        {
            return false;
        }

        $oldpassword = compile_password($oldpassword, $salt);

        if ($this->count('users', "uid = " . intval($uid) . " AND password = '" . $this->quote($oldpassword) . "'") != 1)
        {
            return false;
        }

        return $this->update_user_password_ingore_oldpassword($password, $uid, $salt);
    }

    /**
     * 更改用户不用旧密码密码
     *
     * @param  string
     * @param  int
     * @param  string
     */
    public function update_user_password_ingore_oldpassword($password, $uid, $salt)
    {
        if (!$salt OR !$password OR !$uid)
        {
            return false;
        }

        $this->update('users', array(
            'password' => compile_password($password, $salt),
            'salt' => $salt
        ), 'uid = ' . intval($uid));

        return true;
    }

    /**
     * 去除首次登录标记
     *
     * @param  int
     * @return  boolean
     */
    public function clean_first_login($uid)
    {
        if (! $this->shutdown_update('users', array(
            'is_first_login' => 0
        ), 'uid = ' . intval($uid)))
        {
            return false;
        }
        else
        {
            return true;
        }
    }

    /**
     * 更新用户最后登录时间
     *
     * @param  int
     */
    public function update_user_last_login($uid)
    {
        if (! $uid)
        {
            return false;
        }

        return $this->shutdown_update('users', array(
            'last_login' => time(),
            'last_ip' => ip2long(fetch_ip())
        ), 'uid = ' . intval($uid));
    }

    /**
     * 更新用户通知设置
     *
     * @param  array
     * @param  int
     * @return boolean
     */
    public function update_notification_setting_fields($data, $uid)
    {
        if (!$this->count('users_notification_setting', 'uid = ' . intval($uid)))
        {
            $this->insert('users_notification_setting', array(
                'data' => serialize($data),
                'uid' => intval($uid)
            ));
        }
        else
        {
            $this->update('users_notification_setting', array(
                'data' => serialize($data)
            ), 'uid = ' . intval($uid));
        }

        return true;
    }

    public function update_notification_unread($uid)
    {
        return $this->shutdown_update('users', array(
            'notification_unread' => $this->count('notification', 'read_flag = 0 AND recipient_uid = ' . intval($uid))
        ), 'uid = ' . intval($uid));
    }

    public function update_question_invite_count($uid)
    {
        return $this->update('users', array(
            'invite_count' => $this->count('question_invite', 'recipients_uid = ' . intval($uid))
        ), 'uid = ' . intval($uid));
    }

    public function update_inbox_unread($uid)
    {
        return $this->shutdown_update('users', array(
            'inbox_unread' => ($this->sum('inbox_dialog', 'sender_unread', 'sender_uid = ' . intval($uid)) + $this->sum('inbox_dialog', 'recipient_unread', 'recipient_uid = ' . intval($uid)))
        ), 'uid = ' . intval($uid));
    }


    public function setcookie_login($uid, $user_name, $password, $salt, $expire = null, $hash_password = true)
    {
        if (! $uid)
        {
            return false;
        }

        if (! $expire)
        {
            HTTP::set_cookie('_user_login', get_login_cookie_hash($user_name, $password, $salt, $uid, $hash_password), null, '/', null, false, true);
        }
        else
        {
            HTTP::set_cookie('_user_login', get_login_cookie_hash($user_name, $password, $salt, $uid, $hash_password), (time() + $expire), '/', null, false, true);
        }

        return true;
    }

    public function logout()
    {
        HTTP::set_cookie('_user_login', '', time() - 3600);

        if (isset(AWS_APP::session()->client_info))
        {
            unset(AWS_APP::session()->client_info);
        }

        if (isset(AWS_APP::session()->permission))
        {
            unset(AWS_APP::session()->permission);
        }
    }

    public function check_username_char($user_name)
    {
        if (is_digits($user_name))
        {
            return AWS_APP::lang()->_t('用户名不能为纯数字');
        }

        if (strstr($user_name, '-') OR strstr($user_name, '.') OR strstr($user_name, '/') OR strstr($user_name, '%') OR strstr($user_name, '__'))
        {
            return AWS_APP::lang()->_t('用户名不能包含 - / . % 与连续的下划线');
        }

        $length = strlen(convert_encoding($user_name, 'UTF-8', 'GB2312'));
        
        //上一步会把user_name转化为gbk，故要转化回来
        $user_name = convert_encoding($user_name,'GBK', 'UTF-8');

        $length_min = intval(get_setting('username_length_min'));
        $length_max = intval(get_setting('username_length_max'));

        if ($length < $length_min || $length > $length_max)
        {
            $flag = true;
        }

        switch(get_setting('username_rule'))
        {
            default:

            break;

            case 1:
                if (!preg_match('/^[\x{4e00}-\x{9fa5}_a-zA-Z0-9]+$/u', $user_name) OR $flag)
                {
                    return AWS_APP::lang()->_t('请输入大于 %s 字节的用户名, 允许汉字、字母与数字', ($length_min . ' - ' . $length_max));
                }
            break;

            case 2:
                if (!preg_match("/^[a-zA-Z0-9_]+$/i", $user_name) OR $flag)
                {
                    return AWS_APP::lang()->_t('请输入 %s 个字母、数字或下划线', ($length_min . ' - ' . $length_max));
                }
            break;

            case 3:
                if (!preg_match("/^[\x{4e00}-\x{9fa5}]+$/u", $user_name) OR $flag)
                {
                    return AWS_APP::lang()->_t('请输入 %s 个汉字', (ceil($length_min / 2) . ' - ' . floor($length_max / 2)));
                }
            break;
        }

        return false;
    }

    public function get_users_list($where, $limit = 10, $attrib = false, $exclude_self = true, $orderby = 'uid DESC')
    {
        if ($where)
        {
            $where = '(' . $where . ') AND forbidden = 0 AND group_id <> 3';
        }
        else
        {
            $where = 'forbidden = 0 AND group_id <> 3';
        }

        if ($exclude_self)
        {
            if ($where)
            {
                $where = '(' . $where . ') AND uid <> ' . AWS_APP::user()->get_info('uid');
            }
            else
            {
                $where = 'uid <> ' . AWS_APP::user()->get_info('uid');
            }
        }

        $result = $this->fetch_all('users', $where, $orderby, $limit);

        if ($result)
        {
            foreach ($result AS $key => $val)
            {
            	unset($val['password'], $val['salt']);

                $data[$val['uid']] = $val;

                if (!$val['url_token'] AND $val['user_name'])
                {
                    $data[$val['uid']]['url_token'] = urlencode($val['user_name']);
                }

                if ($val['email_settings'])
                {
                    $data[$val['uid']]['email_settings'] = unserialize($val['email_settings']);
                }

                if ($val['weixin_settings'])
                {
                    $data[$val['uid']]['weixin_settings'] = unserialize($val['weixin_settings']);
                }

                $uids[] = $val['uid'];
            }

            if ($attrib AND $uids)
            {
                if ($users_attrib = $this->fetch_all('users_attrib', 'uid IN(' . implode(',', $uids) . ')'))
                {
                    foreach ($users_attrib AS $key => $val)
                    {
                        unset($val['id']);

                        foreach ($val AS $attrib_key => $attrib_val)
                        {
                            $data[$val['uid']][$attrib_key] = $attrib_val;
                        }
                    }
                }
            }
        }

        return $data;
    }

    /**
     * 根据 WHERE 条件获取用户数量
     *
     * @param string
     * @return int
     */
    public function get_user_count($where = null)
    {
        return $this->count('users', $where);
    }

    public function get_user_recommend_v2($uid, $limit = 10)
    {
        if ($users_list = AWS_APP::cache()->get('user_recommend_' . $uid))
        {
            return $users_list;
        }

        if (!$friends = $this->model('follow')->get_user_friends($uid, 100))
        {
            return $this->get_users_list(null, $limit, true);
        }

        foreach ($friends as $key => $val)
        {
            $follow_uids[] = $val['uid'];
            $follow_users_info[$val['uid']] = $val;
        }

        if ($users_focus = $this->query_all("SELECT DISTINCT friend_uid, fans_uid FROM " . $this->get_table('user_follow') . " WHERE fans_uid IN (" . implode(',', $follow_uids) . ") ORDER BY follow_id DESC", $limit))
        {
            foreach ($users_focus as $key => $val)
            {
                $friend_uids[$val['friend_uid']] = $val['friend_uid'];

                $users_ids_recommend[$val['friend_uid']] = array(
                    'type' => 'friend',
                    'fans_uid' => $val['fans_uid']
                );
            }
        }

        // 取我关注的话题
        if ($my_focus_topics = $this->model('topic')->get_focus_topic_list($uid, null))
        {
            foreach ($my_focus_topics as $key => $val)
            {
                $my_focus_topics_ids[] = $val['topic_id'];
                $my_focus_topics_info[$val['topic_id']] = $val;
            }

            if (sizeof($my_focus_topics_ids) > 0)
            {
                array_walk_recursive($my_focus_topics_ids, 'intval_string');

                if ($topic_focus_uids = $this->query_all("SELECT DISTINCT uid, topic_id FROM " . $this->get_table('topic_focus') . " WHERE topic_id IN(" . implode(',', $my_focus_topics_ids) . ")"))
                {
                    foreach ($topic_focus_uids as $key => $val)
                    {
                        if ($friend_uids[$val['uid']])
                        {
                            continue;
                        }

                        $friend_uids[$val['uid']] = $val['uid'];

                        $users_ids_recommend[$val['uid']] = array(
                            'type' => 'topic',
                            'topic_id' => $val['topic_id']
                        );
                    }
                }
            }
        }

        if (! $friend_uids)
        {
            return $this->get_users_list('uid NOT IN (' . implode($follow_uids, ',') . ')', $limit, true);
        }

        if ($users_list = $this->get_users_list('uid IN(' . implode($friend_uids, ',') . ') AND uid NOT IN (' . implode($follow_uids, ',') . ')', $limit, true, true))
        {
            foreach ($users_list as $key => $val)
            {
                $users_list[$key]['type'] = $users_ids_recommend[$val['uid']]['type'];

                if ($users_ids_recommend[$val['uid']]['type'] == 'friend')
                {
                    $users_list[$key]['friend_users'] = $follow_users_info[$users_ids_recommend[$val['uid']]['fans_uid']];
                }
                else if ($users_ids_recommend[$val['uid']]['type'] == 'topic')
                {
                    $users_list[$key]['topic_info'] = $my_focus_topics_info[$users_ids_recommend[$val['uid']]['topic_id']];
                }
            }

            AWS_APP::cache()->set('user_recommend_' . $uid, $users_list, get_setting('cache_level_normal'));
        }

        return $users_list;
    }

    /**
     * 根据职位 ID 获取职位信息
     *
     * @param int
     * @return array
     */
    public function get_jobs_by_id($id)
    {
        if (!$id)
        {
            return false;
        }

        static $jobs_info;

        if (!$jobs_info[$id])
        {
            $jobs_info[$id] = $this->fetch_row('jobs', 'id = ' . intval($id));
        }

        return $jobs_info[$id];
    }

    /**
     * 获取头像地址
     *
     * 举个例子：$uid=12345，那么头像路径很可能(根据您部署的上传文件夹而定)会被存储为/uploads/000/01/23/45_avatar_min.jpg
     *
     * @param  int
     * @param  string
     * @param  int
     * @return string
     */
    public function get_avatar($uid, $size = 'min', $return_type = 0)
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

        $uid = abs(intval($uid));
        $uid = sprintf('%\'09d', $uid);
        $dir1 = substr($uid, 0, 3);
        $dir2 = substr($uid, 3, 2);
        $dir3 = substr($uid, 5, 2);

        if ($return_type == 1)
        {
            return $dir1 . '/' . $dir2 . '/' . $dir3 . '/';
        }

        if ($return_type == 2)
        {
            return substr($uid, -2) . '_avatar_' . $size . '.jpg';
        }

        return $dir1 . '/' . $dir2 . '/' . $dir3 . '/' . substr($uid, -2) . '_avatar_' . $size . '.jpg';
    }

    /**
     * 删除用户头像
     *
     * @param int
     * @return boolean
     */
    public function delete_avatar($uid)
    {
        if (!$uid)
        {
            return false;
        }

        foreach(AWS_APP::config()->get('image')->avatar_thumbnail as $key => $val)
        {
            @unlink(get_setting('upload_dir') . '/avatar/' . $this->get_avatar($uid, $key, 1) . $this->get_avatar($uid, $key, 2));
        }

        return $this->update_users_fields(array('avatar_file' => ''), $uid);
    }

    public function update_thanks_count($uid)
    {
        $answer_counter = $this->sum('answer', 'thanks_count', 'uid = ' . intval($uid));
        $question_counter = $this->sum('question', 'thanks_count', 'published_uid = ' . intval($uid));

        return $this->update('users', array(
            'thanks_count' => ($answer_counter + $question_counter)
        ), "uid = " . intval($uid));
    }

    // 获取活跃用户 (非垃圾用户)
    public function get_activity_random_users($limit = 10)
    {
        // 好友 & 粉丝 > 5, 回复 > 5, 根据登陆时间, 倒序
        return $this->get_users_list("fans_count > 5 AND friend_count > 5 AND answer_count > 1", $limit, true, true, 'last_login DESC');
    }

    public function add_user_group($group_name, $type, $reputation_lower = 0, $reputation_higer = 0, $reputation_factor = 0)
    {
        return $this->insert('users_group', array(
            'type' => intval($type),
            'custom' => 1,
            'group_name' => $group_name,
            'reputation_lower' => $reputation_lower,
            'reputation_higer' => $reputation_higer,
            'reputation_factor' => $reputation_factor,
        ));
    }

    public function delete_user_group_by_id($group_id)
    {
        $this->update('users', array(
            'group_id' => 4,
        ), 'group_id = ' . intval($group_id));

        return $this->delete('users_group', 'group_id = ' . intval($group_id));
    }

    public function update_user_group_data($group_id, $data)
    {
        return $this->update('users_group', $data, 'group_id = ' . intval($group_id));
    }

    public function get_user_group_by_id($group_id, $field = null)
    {
        if (!$group_id)
        {
            return false;
        }

        static $user_groups;

        if (isset($user_groups[$group_id]))
        {
            if ($field)
            {
                return $user_groups[$group_id][$field];
            }
            else
            {
                return $user_groups[$group_id];
            }
        }

        if (!$user_group = AWS_APP::cache()->get('user_group_' . intval($group_id)))
        {
            $user_group = $this->fetch_row('users_group', 'group_id = ' . intval($group_id));

            if ($user_group['permission'])
            {
                $user_group['permission'] = unserialize($user_group['permission']);
            }

            AWS_APP::cache()->set('user_group_' . intval($group_id), $user_group, get_setting('cache_level_normal'), 'users_group');
        }

        $user_groups[$group_id] = $user_group;

        if ($field)
        {
            return $user_group[$field];
        }
        else
        {
            return $user_group;
        }
    }

    public function get_user_group_list($type = 0, $custom = null)
    {
        $type = intval($type);

        $where[] = (check_extension_package('ticket') AND $type === 0) ? 'type IN (0, 2)' : 'type = ' . $type;

        if (isset($custom))
        {
            $where[] = 'custom = ' . intval($custom);
        }

        if ($users_groups = $this->fetch_all('users_group', implode(' AND ', $where)))
        {
            foreach ($users_groups as $key => $val)
            {
                $group[$val['group_id']] = $val;
            }
        }

        return $group;
    }

    public function get_user_group_by_reputation($reputation, $field = null)
    {
        if ($mem_groups = $this->get_user_group_list(1))
        {
            foreach ($mem_groups as $key => $val)
            {
                if ((intval($reputation) >= intval($val['reputation_lower'])) AND (intval($reputation) < intval($val['reputation_higer'])))
                {
                    $group = $val;

                    break;
                }
            }
        }
        else    // 若会员组为空，则返回为普通会员组
        {
            $group = $this->get_user_group(4);
        }

        if ($field)
        {
            return $group[$field];
        }

        return $group;
    }

    public function update_user_reputation_group($uid)
    {
        if (!$user_info = $this->get_user_info_by_uid($uid) OR !$user_group = $this->get_user_group($user_info['group_id']))
        {
            return false;
        }

        if ($user_group['custom'] == 1)
        {
            if ($user_info['reputation_group'])
            {
                $this->update_users_fields(array(
                    'reputation_group' => 0
                ), $uid);
            }

            return false;
        }

        $reputation_group = $this->get_user_group_by_reputation($user_info['reputation'], 'group_id');

        if ($reputation_group != $user_info['reputation_group'])
        {
            return $this->update_users_fields(array(
                'reputation_group' => intval($reputation_group)
            ), $uid);
        }

        return false;
    }

    public function get_user_group($group_id, $reputation_group = 0)
    {
        if ($group_id == 4 AND $reputation_group)
        {
            if ($user_group = $this->model('account')->get_user_group_by_id($reputation_group))
            {
                return $user_group;
            }
        }

        return $this->model('account')->get_user_group_by_id($group_id);
    }

    public function check_url_token($url_token, $uid)
    {
        return $this->count('users', "(url_token = '" . $this->quote($url_token) . "' OR user_name = '" . $this->quote($url_token) . "') AND uid != " . intval($uid));
    }

    public function update_url_token($url_token, $uid)
    {
        return $this->update('users', array(
            'url_token' => $url_token,
            'url_token_update' => time()
        ), 'uid = ' . intval($uid));
    }

    public function forbidden_user_by_uid($uid, $status, $admin_uid)
    {
        if (!$uid)
        {
            return false;
        }

        return $this->model('account')->update_users_fields(array(
            'forbidden' => intval($status)
        ), $uid);
    }
    public function clean_user_reputation_by_uid($uid)
    {
        if (!$uid)
        {
            return false;
        }
        $prefix=AWS_APP::config()->get('database')->prefix;
        $sql_delete="delete from ".$prefix."answer_vote  where answer_id in (select answer_id from ".$prefix."answer where uid = ".$uid.")";
        $sql_article_delete="delete from ".$prefix."article_vote  where item_uid = ".$uid;
        $sql_update="update ".$prefix."users set reputation = 0 ,agree_count = 0 ,praise_no_weight = 0, weight_balance = 0,praise_weight = 0,reputation_extend = 0 where uid = ".$uid;
        $sql_update_best_answer="update ".$prefix."question set best_answer =0 WHERE best_answer in (SELECT answer_id FROM ".$prefix."answer WHERE uid = ".$uid.")";
        $this->query_all($sql_delete);
        $this->query_all($sql_article_delete);
        $this->query_all($sql_update);
        $this->query_all($sql_update_best_answer);
    }

    public function set_default_timezone($time_zone, $uid)
    {
        return $this->update('users', array(
            'default_timezone' => htmlspecialchars($time_zone)
        ), 'uid = ' . intval($uid));
    }

    public function send_delete_message($uid, $title, $message)
    {
        $delete_message = AWS_APP::lang()->_t('你发表的内容 %s 已被管理员删除', $title);
        $delete_message .= "\r\n----- " . AWS_APP::lang()->_t('内容') . " -----\r\n" . $message;
        $delete_message .= "\r\n-----------------------------\r\n";
        $delete_message .= AWS_APP::lang()->_t('如有疑问, 请联系管理员');

        $this->model('email')->action_email('QUESTION_DEL', $uid, get_js_url('/inbox/'), array(
            'question_title' => $title,
            'question_detail' => $delete_message
        ));

        return true;
    }

    public function save_recent_topics($uid, $topic_title)
    {
        if (!$user_info = $this->get_user_info_by_uid($uid))
        {
            return false;
        }

        if ($user_info['recent_topics'])
        {
            $recent_topics = unserialize($user_info['recent_topics']);
        }

        $new_recent_topics[0] = $topic_title;

        if ($recent_topics)
        {
            foreach ($recent_topics AS $key => $val)
            {
                if ($val != $topic_title)
                {
                    $new_recent_topics[] = $val;
                }
            }
        }

        if (count($new_recent_topics) > 10)
        {
            $new_recent_topics = array_slice($new_recent_topics, 0, 10);
        }

        return $this->update('users', array(
            'recent_topics' => serialize($new_recent_topics)
        ), 'uid = ' . intval($uid));
    }

    public function sum_user_agree_count($uid)
    {
        return $this->update('users', array(
            'agree_count' => ($this->count('answer_vote', 'vote_value = 1 AND answer_uid = ' . intval($uid)) + $this->count('article_vote', 'rating = 1 AND item_uid = ' . intval($uid)))
        ), 'uid = ' . intval($uid));
    }

    public function associate_remote_avatar($uid, $headimgurl)
    {
        if (!$headimgurl)
        {
            return false;
        }

        if (!$user_info = $this->get_user_info_by_uid($uid))
        {
            return false;
        }

        if ($user_info['avatar_file'])
        {
            return false;
        }

        if (!$avatar_stream = file_get_contents($headimgurl))
        {
            return false;
        }

        $avatar_location = get_setting('upload_dir') . '/avatar/' . $this->get_avatar($uid, '');

        $avatar_dir = dirname($avatar_location) . '/';

        if (!file_exists($avatar_dir))
        {
            make_dir($avatar_dir);
        }

        if (!@file_put_contents($avatar_location, $avatar_stream))
        {
            return false;
        }

        foreach(AWS_APP::config()->get('image')->avatar_thumbnail AS $key => $val)
        {
            AWS_APP::image()->initialize(array(
                'quality' => 90,
                'source_image' => $avatar_location,
                'new_image' => $avatar_dir . $this->get_avatar($uid, $key, 2),
                'width' => $val['w'],
                'height' => $val['h']
            ))->resize();
        }

        $this->update('users', array(
            'avatar_file' => $this->get_avatar($uid)
        ), 'uid = ' . intval($uid));

        if (!$this->model('integral')->fetch_log($new_user_id, 'UPLOAD_AVATAR'))
        {
            $this->model('integral')->process($new_user_id, 'UPLOAD_AVATAR', round((get_setting('integral_system_config_profile') * 0.2)), '上传头像');
        }

        return true;
    }


    public function get_task_yoyow_end_time($start_time,$id){
        if($id!=''){
            return $this->fetch_row('yoyow_assign_task','act_end_time >="'.$start_time.'" AND status != 1 AND id !='.$id);
        }else{
            return $this->fetch_row('yoyow_assign_task','act_end_time >="'.$start_time.'" AND status != 1');
        }
    }


    /**
     * 定时任务
     *
     * 每天统计用户文章得分总数，用于专栏排名
     * 单篇文章得分计算公式 ： (p - 1) / (t + 2) ^ 1.5
     * p：点赞数
     * t: 文章发表时间，单位：小时
     * 1.5: 开平方的数额
     *
     */
    public function statistical_user_ranking()
    {
        $users = $this->fetch_all(users,'forbidden = 0');//获取所有未被封禁的用户
        if(count($users) > 0)
        {
            foreach($users as $user)
            {
                $all_score = 0.00;
                $articles = $this->model(article)->get_article_lists_info_by_uid($user['uid']);
                if(count($articles) > 0)
                {
                    foreach($articles as $article)
                    {
                        $votes  = $article['votes'];//文章点赞数
                        $hours = ceil((time() - $article['add_time']) / 3600);//文章发布时间
                        $formula = ($votes - 1) / pow($hours + 2,1.5);
                        $score = $formula > 0 ? $formula : 0;
                        $all_score += $score;
                    }
                }
                $this->update('users', array(
                    'score' => round($all_score,2)
                ),'uid = '.$user['uid']);
            }

        }
    }

    /**
     * 专栏列表页数据
     *
     * @param $sql
     * @param $limit
     * @param $offset
     * @return array
     */
    public function get_user_lists_for_column($sql,$limit,$offset)
    {
        return  $this->query_all($sql,$limit,$offset);
    }

    /**
     * 获取专栏列表页总数
     * @param $sql
     * @return array
     */
    public function get_user_lists_count_for_column($sql)
    {
        return  $this->query_row($sql);
    }

    /**
     * 定时任务
     *
     * 每日统计更新用户发表文章总数
     */
    public function update_user_article_count()
    {
        $users = $this->fetch_all(users);
        if(count($users) > 0){
            foreach($users as $user){
                $articles = $this->fetch_all(article,'uid = '.$user['uid']);
                $this->update('users', array('article_count' => count($articles)),'uid = '.$user['uid']);
            }
        }
    }
    
    /**
     * 注册用户、绑定yoyow号送币
     * @param $uid 用户id
     * @param $operate 操作 1:用户注册 2:用户绑定yoyow账号
     */
    public function register_send_yoyow($uid, $operate, $coin, $status=0)
    {
        $this->insert('register_yoyow_record', array(
            'uid' => $uid,
            'operate' => $operate,
            'coin' => $coin,
            'ope_time' => time(),
            'status' => $status
        ));
    }

    public function get_register_send_yoyow_record($uid, $operate)
    {
        return $this->fetch_row('register_yoyow_record','uid ="'.$uid.'" AND operate ='.$operate);
    }

    public function get_recommend_users(){
        return $this->fetch_all('users','','score DESC',10,0);
    }

    /**
     * 判断是否能继续发币
     */
    public function is_send_yoyow(){
        $total = $this->fetch_one('register_yoyow_record', 'sum(coin)');
        $startDate=explode('-',get_setting('register_bind_start'));
        $startTime=mktime(0,0,0,$startDate[1],$startDate[2],$startDate[0]);
        $endDate=explode('-',get_setting('register_bind_end'));
        $endTime=mktime(0,0,0,$endDate[1],$endDate[2],$endDate[0]);
        // 判断是否在活动期间并且奖励还未发完
        if($total < get_setting('register_bind_total') && $startTime < time() && time() < $endTime){
            return true;
        }
        return false;
    }

    public function insert_column($uid)
    {
        $this->insert('column', array(
            'uid' => $uid,
            'is_recommend' => 0
        ));
    }

    /**插入注册 绑定 威望奖励表
     */
    public function update_register_reward_record($uid,$remark,$coin,$type,$status)
    {
         $result = $this->insert('register_reward_record', array(
            'uid' => $uid,
            'remark' => $remark,
            'coin' => $coin,
            'time' => time(),
            'type' => $type,
            'status' => $status
        ));
         return $result;
    }

    /**
     * 获取用户分享海报图片路径
     * @param $uid              用户id
     * @param $user_name        用户名
     * @param $invitation_code  用户邀请码
     * @return string
     */
    public function get_user_poster($uid,$user_name,$invitation_code)
    {
        $poster_pic_name = $uid.'_poster.jpg';
        //       $dir = get_setting('upload_dir')."/uploads/poster/poster_img";
//        $dir = $_SERVER['DOCUMENT_ROOT']."/../uploads/poster/poster_img"; // 47环境路径
       $dir = $_SERVER['DOCUMENT_ROOT']."/uploads/poster/poster_img";  //本地测试路径 & live路径

        $file_full_path = $dir.'/'.$poster_pic_name;

        if(!is_dir($dir))
        {
            mkdir($dir,0777,true);
        }

        $url = $invitation_code ? base_url() . '/?/account/register/invitation_code-' . $invitation_code : base_url() . '/?/account/register/';
        $qrcode_url = $this->model('weixin')->generate_qrcode_img($url,$uid);
        $left = $this->get_str_length_px($user_name);
        $config = array(
            'text'=>array(
                array( //用户名
                    'text'=> $user_name,
                    'left'=>$left,
                    'top'=>890,
                    'fontPath'=> $_SERVER['DOCUMENT_ROOT'].'/static/fonts/simhei.ttf',     //字体文件
                    'fontSize'=>60,             //字号
                    'fontColor'=>'128,128,128',       //字体颜色
                    'angle'=>0,
                ),
//                array( //获取yoyow币数量
//                    'text'=> get_setting('register_reward_amount'),
//                    'left'=> 450,
//                    'top'=>1540,
//                    'fontPath'=> $_SERVER['DOCUMENT_ROOT'].'/static/fonts/simhei.ttf',     //字体文件
//                    'fontSize'=>90,             //字号
//                    'fontColor'=>'100,149,237',       //字体颜色
//                    'angle'=>0,
//                )
            ),
            'image'=>array(
                array( //二维码图片
                    'url' => $qrcode_url,
                    'stream' => 0,
                    'left' => 300,
                    'top' => 905,
                    'right' => 0,
                    'bottom' => 0,
                    'width' => 481,
                    'height' => 481,
                    'opacity' => 100
                ),
//                array(  //用户头像
//                    'url' => get_avatar_url($uid, "mid"),
//                    'stream' => 0,
//                    'left' => 125,
//                    'top' => 2300,
//                    'right' => 0,
//                    'bottom' => 0,
//                    'width' => 150,
//                    'height' => 150,
//                    'opacity' => 100
//                ),
            ),
            'background' => G_STATIC_URL . '/common/bg.png'       //背景图
        );
        $poster_url = $this->model('weixin')->createPoster($config,$file_full_path);
        if(file_exists($poster_url)){
            $file_url = "uploads/poster/poster_img/".$poster_pic_name;
        }else{
            $file_url = "";
        }
        return $file_url;
    }

    /**
     * @param $str
     * @return float|int
     */
    public function get_str_length_px($str)
    {
        $length = mb_strlen($str);//字符串总长度

        preg_match_all('/[\x{4e00}-\x{9fff}]+/u', $str, $matches);
        $str = join('', $matches[0]);

        //获取字符串中中文字符长度
        $str_len = mb_strlen($str);

        $other_len = $length - $str_len;//字符串除汉字之外字符长度

        $pixel = 38;    //普通字符宽度
        $text_pixel = 78;    //汉字字符宽度
        $left_start = 185;
        $width = 700;

        //字符串总长度
        $all_length = $str_len * $text_pixel + $other_len * $pixel;

        if($all_length >= $width){
            $result = $left_start;
        }else{
            $result = $left_start +  ceil(($width - $all_length) / 2);
        }

        return $result;
    }
}
