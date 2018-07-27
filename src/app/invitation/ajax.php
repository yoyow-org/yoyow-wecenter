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

class ajax extends AWS_CONTROLLER
{
    var $per_page = 10;

    public function setup()
    {
        HTTP::no_cache_header();
    }

    public function invitation_list_action()
    {
        $limit = intval($_GET['page']) * $this->per_page . ', ' . $this->per_page;

        if ($invitation_list = $this->model('invitation')->get_invitation_list($this->user_id, $limit))
        {
            foreach ($invitation_list as $key => $val)
            {
                if ($val['active_status'] == 1)
                {
                    $uids[$val['active_uid']] = $val['active_uid'];
                }
            }

            if ($uids)
            {
                if ($user_infos = $this->model('account')->get_user_info_by_uids($uids))
                {
                    foreach ($invitation_list as $key => $val)
                    {
                        if ($val['active_status'] == '1')
                        {
                            $invitation_list[$key]['user_info'] = $user_infos[$val['active_uid']];
                        }
                    }
                }
            }
        }

        TPL::assign('invitation_list', $invitation_list);

        TPL::output('invitation/ajax/invitation_list');
    }

    public function invitation_list2_action()
    {   


        $this->per_page = 10;
        $limit = intval($_GET['page']) * $this->per_page . ', ' . $this->per_page;
        $invitation_detial_list = $this->model('invitation')->fetch_all('invitation_yoyow', 'coin_uid = ' . $this->user_id.' and effective = 1', ' time desc', $limit);

        
        foreach ($invitation_detial_list as $k => $coin_single){
             
            if(intval($_GET['page']) > 0 )
            {
                   $ranking = intval($_GET['page']*$this->per_page + ($k+1));

            }else
            { 
                   $ranking = intval($k + 1);
            }

            

            $invitation_detail = $this->model('invitation')->fetch_row('invitation', ' invitation_id = '. $coin_single['invitation_id']);

            $coin_single['ranking'] = $ranking;
            $invitation_list[] = array_merge($coin_single, array('invitation_detail'=>$invitation_detail));
           /* switch ($coin_single['coin_type']) {
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
                            'second_user_info'=>$second,
                             
                        )
                    );
                    break;
            }*/


        }
        //        if ($invitation_list = $this->model('invitation')->get_invitation_list($this->user_id, $limit))
        //        {
        //            foreach ($invitation_list as $key => $val)
        //            {
        //                if ($val['active_status'] == 1)
        //                {
        //                    $uids[$val['active_uid']] = $val['active_uid'];
        //                }
        //                $user_info = $this->model('invitation')->fetch_row('users', 'uid='.$val['active_uid']);
        //                $invitation_list[$key]['user_info'] = $user_info;
        //            }
        //        }
        //        var_dump($invitation_list); die;
        TPL::assign('invitation_list', $invitation_list);
        if (is_mobile()) {
            TPL::output('invitation/ajax/invitation_list_mobile');
            die;
        } else {
            TPL::output('invitation/ajax/invitation_list2');
            die;
        }

        
        TPL::output('invitation/ajax/invitation_list2');
    }

    public function invite_action()
    {
        if (!$this->user_info['email'])
        {
            H::ajax_json_output(AWS_APP::RSM(null, -1, AWS_APP::lang()->_t('当前帐号没有提供 Email, 此功能不可用')));
        }

        if (! H::valid_email($_POST['email']))
        {
            H::ajax_json_output(AWS_APP::RSM(null, -1, AWS_APP::lang()->_t('请填写正确的邮箱')));
        }

        if ($this->user_info['invitation_available'] < 1)
        {
            H::ajax_json_output(AWS_APP::RSM(null, -1, AWS_APP::lang()->_t('已经没有可使用的邀请名额')));
        }

        if ($uid = $this->model('account')->check_email($_POST['email']))
        {
            if ($uid == $this->user_id)
            {
                H::ajax_json_output(AWS_APP::RSM(null, -1, AWS_APP::lang()->_t('你不能邀请自己')));
            }

            H::ajax_json_output(AWS_APP::RSM(null, -1, AWS_APP::lang()->_t('此邮箱已在本站注册帐号')));
        }

        // 若再次填入已邀请过的邮箱，则再发送一次邀请邮件
        if ($invitation_info = $this->model('invitation')->get_active_invitation_by_email($_POST['email']))
        {
            if ($invitation_info['active_status'] == 0)
            {
                if ($invitation_info['uid'] == $this->user_id)
                {
                    $this->model('invitation')->send_invitation_email($invitation_info['invitation_id']);

                    H::ajax_json_output(AWS_APP::RSM(null, -1, AWS_APP::lang()->_t('重发邀请成功')));
                }
                else
                {
                    H::ajax_json_output(AWS_APP::RSM(null, -1, AWS_APP::lang()->_t('此邮箱已接收过本站发出的邀请')));
                }
            }
        }

        $invitation_code = $this->model('invitation')->get_unique_invitation_code();

        if ($invitation_id = $this->model('invitation')->add_invitation($this->user_id, $invitation_code, $_POST['email'], time(), ip2long($_SERVER['REMOTE_ADDR'])))
        {
            $this->model('invitation')->send_invitation_email($invitation_id);

            H::ajax_json_output(AWS_APP::RSM(null, 1, null));
        }
    }

    public function invite_resend_action()
    {
        $this->model('invitation')->send_invitation_email($_GET['invitation_id']);

        H::ajax_json_output(AWS_APP::RSM(null, 1, null));
    }

    public function invite_cancel_action()
    {
        if (! $_GET['invitation_id'])
        {
            H::ajax_json_output(AWS_APP::RSM(null, -1, AWS_APP::lang()->_t('邀请记录不存在')));
        }

        if (! $this->model('invitation')->get_invitation_by_id($_GET['invitation_id']))
        {
            H::ajax_json_output(AWS_APP::RSM(null, -1, AWS_APP::lang()->_t('邀请记录不存在')));
        }

        $this->model('invitation')->cancel_invitation_by_id($_GET['invitation_id']);

        H::ajax_json_output(AWS_APP::RSM(null, 1, null));
    }

    public function invitation_sort_action(){
        $total_rows = $this->model('invitation')->count('ranking_list');
        TPL::assign('total_page',($total_rows%20==0) ? intval($total_rows/20) : (intval($total_rows/20) +1));
        //获取排名
        $sort = $this->model('invitation')->fetch_one('ranking_list','ranking','uid = '.$this->user_id);
        TPL::assign('sort',$sort);
        TPL::output('invitation/ajax/invitation_sort');
    }
    public function invitation_sort_list_action()
    {
        $this->per_page = 20;
        $limit = intval($_GET['page']) * $this->per_page . ', ' . $this->per_page;
        $invitation_sort_list = $this->model('invitation')->fetch_all('ranking_list','','',$limit);
        TPL::assign('invitation_sort_list', $invitation_sort_list);
        TPL::output('invitation/ajax/invitation_sort_list');
    }
}