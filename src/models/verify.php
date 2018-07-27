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

class verify_class extends AWS_MODEL
{
	public function add_apply($uid, $name, $reason, $type, $data = array(), $attach = null)
	{
		if ($verify_apply = $this->fetch_apply($uid))
		{
			$this->remove_apply($verify_apply['uid']);
		}

		return $this->insert('verify_apply', array(
			'uid' => $uid,
			'name' => htmlspecialchars($name),
			'reason' => htmlspecialchars($reason),
			'data' => serialize($data),
			'type' => htmlspecialchars($type),
			'attach' => $attach,
			'time' => time()
		));
	}

	public function update_apply($uid, $name = null, $reason = null, $data = null, $attach = null, $type = null)
	{
		if ($attach)
		{
			if ($verify_apply = $this->fetch_row('verify_apply', 'uid = ' . intval($uid)))
			{
				if ($verify_apply['attach'])
				{
					unlink(get_setting('upload_dir') . '/verify/' . $verify_apply['attach']);
				}

				$this->update('verify_apply', array(
					'attach' => $attach
				), 'uid = ' . intval($uid));
			}
		}

		$to_update_apply = array();

		if (isset($name))
		{
			$to_update_apply['name'] = htmlspecialchars($name);
		}

		if (isset($reason))
		{
			$to_update_apply['reason'] = htmlspecialchars($reason);
		}

		if (isset($data) AND is_array($data))
		{
			$to_update_apply['data'] = serialize($data);
		}

		if (isset($type))
		{
			$to_update_apply['type'] = $type;
		}

		return $this->update('verify_apply', $to_update_apply, 'uid = ' . intval($uid));
	}

	public function fetch_apply($uid)
	{
		if ($verify_apply = $this->fetch_row('verify_apply', 'uid = ' . intval($uid)))
		{
			$verify_apply['data'] = unserialize($verify_apply['data']);
		}

		return $verify_apply;
	}

	public function remove_apply($uid)
	{
		if ($verify_apply = $this->fetch_row('verify_apply', 'uid = ' . intval($uid)))
		{
			if ($verify_apply['attach'])
			{
				unlink(get_setting('upload_dir') . '/verify/' . $verify_apply['attach']);
			}

			return $this->delete('verify_apply', 'id = ' . intval($verify_apply['id']));
		}
	}

	public function approval_list($page, $status, $limit)
	{
		if ($approval_list = $this->fetch_page('verify_apply', '`status` = ' . intval($status), 'time ASC', $page, $limit))
		{
			foreach ($approval_list AS $key => $val)
			{
				$approval_list[$key]['data'] = unserialize($val['data']);
			}
		}

		return $approval_list;
	}

	public function approval_verify($id)
	{
		if (!$verify_apply = $this->fetch_row('verify_apply', 'id = ' . intval($id)))
		{
			return false;
		}

        try {
		    $this->begin_transaction();
            $this->update('verify_apply', array(
                'status' => 1
            ), 'id = ' . intval($id));

            if ($verify_apply['type'])
            {
                $verified = $verify_apply['type'];
            }
            else
            {
                $verified = 'personal';
            }
            //是否启用积分
            $integral_system_enabled=get_setting('integral_system_enabled', false);
            //个人认证积分开关
            $integral_pep_auth_config_switch=get_setting('integral_pep_auth_config_switch', false);
            //企业认证积分开关
            $integral_org_auth_config_switch=get_setting('integral_org_auth_config_switch', false);
            if($integral_system_enabled == 'Y'){
                //启用积分
                $log_action='AUTH';
                //查询积分赠送记录
                $apply_where=sprintf(" uid=%s AND action='%s' ",$verify_apply["uid"],$log_action);
                $apply_num=$this->count("integral_log",$apply_where);
                $user_integral=$this->sum("users",integral,sprintf(" uid=%s ",$verify_apply["uid"]));
                //认证类型的处理
                if($verify_apply['type']=='personal' && $apply_num==0 && $integral_pep_auth_config_switch=='Y'){
                    //个人认证赠送
                    $note='个人认证赠送积分';
                    $integral_pep_auth_config=get_setting('integral_pep_auth_config', false);
                    if($integral_pep_auth_config>0){
                        //共通处理，赠送积分，积分日志记录
                        $this->model("integral")->process($verify_apply["uid"],$log_action,$integral_pep_auth_config,$note);
                    }
                }else if($verify_apply['type']=='enterprise' && $apply_num==0 && $integral_org_auth_config_switch=='Y'){
                    //机构认证赠送
                    $note='机构认证赠送积分';
                    $integral_org_auth_config=get_setting('integral_org_auth_config', false);
                    if($integral_org_auth_config>0){
                        //共通处理，赠送积分，积分日志记录
                        $this->model("integral")->process($verify_apply["uid"],$log_action,$integral_org_auth_config,$note);
                    }
                }
            }

            $upd_row=$this->update('users', array(
                'verified' => $verified
            ), 'uid = ' . intval($verify_apply['uid']));
            $this->commit();
            return $upd_row;
        }catch (Exception $e){
            $this->roll_back();
        }
	}

	public function decline_verify($id)
	{
		if (!$verify_apply = $this->fetch_row('verify_apply', 'id = ' . intval($id)))
		{
			return false;
		}

		return $this->update('verify_apply', array(
			'status' => -1
		), 'id = ' . intval($id));
	}
}