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
    var $per_page = 10;
	public function get_access_rule()
	{
		$rule_action['rule_type'] = 'white'; //黑名单,黑名单中的检查  'white'白名单,白名单以外的检查
		$rule_action['actions'] = array();

		return $rule_action;
	}

	public function index_action()
	{
		if (!$this->user_info['email'])
		{
			H::redirect_msg(AWS_APP::lang()->_t('当前帐号没有提供 Email, 此功能不可用'));
		}
		$invitation_code = $this->get_invitation_code();
        TPL::assign('invitation_code', $invitation_code);
		$this->crumb(AWS_APP::lang()->_t('邀请好友'), '/invitation/');
        $poster_url = $this->model('account')->get_user_poster($this->user_info['uid'],$this->user_info['user_name'],$invitation_code);
        TPL::assign('poster_url', $poster_url);
        $pic_url = get_js_url(base_url() . '/'.$poster_url);
        TPL::assign('pic_url', $pic_url);
        //获取排名
        $sort = $this->model('invitation')->fetch_one('ranking_list','ranking','uid = '.$this->user_id);
        TPL::assign('sort',$sort);
        //邀请总收益
        $invitation_sum_yoyow = $this->model('invitation')->fetch_one('invitation_yoyow','sum(coin)', 'coin_uid = ' . $this->user_id.' and coin_type != 0 and effective = 1');
        TPL::assign('invitation_sum_yoyow', $invitation_sum_yoyow);
        $sql = "select count(*) total_rows from ".get_table('invitation_yoyow')." where coin_uid = ".$this->user_id." and coin_type != 0 and effective = 1";
        $total_rows = $this->model('invitation')->query_all($sql);
        TPL::assign('invitation_num', $total_rows[0]['total_rows']);
        TPL::assign('total_page',($total_rows[0]['total_rows']%10==0) ? intval($total_rows[0]['total_rows']/10) : (intval($total_rows[0]['total_rows']/10) +1));
		TPL::output('invitation/index');
	}
	public function get_invitation_code() {
        $uid = $this->user_id;
        $az=['a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z','A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z',0,1,2,3,4,5,6,7,8,9];
        $invitation_code = $this->model('invitation')->fetch_one('users_invitation_code','invitation_code','uid='.$uid);
        if(!$invitation_code) {
            $invitation_code_new = $az[rand(0,61)].$az[rand(0,61)].$az[rand(0,61)].$az[rand(0,61)].$az[rand(0,61)].$az[rand(0,61)].$az[rand(0,61)].$az[rand(0,61)].'';
            $uid = $this->model('invitation')->fetch_one('users_invitation_code','uid','invitation_code=\''.$invitation_code_new.'\'');
            if($uid){
                return $this->get_invitation_code();
            }else{
                $this->model('invitation')->insert('users_invitation_code',
                    array(
                        'uid'=>$this->user_id,
                        'invitation_code'=>$invitation_code_new
                    ));
                return $invitation_code_new;
            }
        }else{
            return $invitation_code;
        }
    }
}
