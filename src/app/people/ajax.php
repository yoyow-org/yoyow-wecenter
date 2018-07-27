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

    public function get_access_rule()
    {
        $rule_action['rule_type'] = 'white'; //黑名单,黑名单中的检查  'white'白名单,白名单以外的检查

        $rule_action['actions'] = array(
            'user_info'
        );

        if ($this->user_info['permission']['visit_people'])
        {
            $rule_action['actions'][] = 'user_actions';
            $rule_action['actions'][] = 'follows';
            $rule_action['actions'][] = 'topics';
        }

        return $rule_action;
    }

    public function setup()
    {
        $this->per_page = get_setting('contents_per_page');

        HTTP::no_cache_header();
    }

    public function user_actions_action()
    {
        if ((isset($_GET['perpage']) AND intval($_GET['perpage']) > 0))
        {
            $this->per_page = intval($_GET['perpage']);
        }

        $data = $this->model('actions')->get_user_actions($_GET['uid'], (intval($_GET['page']) * $this->per_page) . ", {$this->per_page}", $_GET['actions'], $this->user_id);

        TPL::assign('list', $data);

        if (is_mobile())
        {
            $template_dir = 'm';
        }
        else
        {
            $template_dir = 'people';
        }

        if ($_GET['actions'] == '201')
        {
            TPL::output($template_dir . '/ajax/user_actions_questions_201');
        }
        else if ($_GET['actions'] == '101')
        {
            TPL::output($template_dir . '/ajax/user_actions_questions_101');
        }
        else
        {
            TPL::output($template_dir . '/ajax/user_actions');
        }
    }

    public function user_info_action()
    {
        if ($this->user_id == $_GET['uid'])
        {
            $user_info = $this->user_info;
        }
        else if (!$user_info = $this->model('account')->get_user_info_by_uid($_GET['uid'], ture))
        {
            H::ajax_json_output(array(
                'uid' => null
            ));
        }

        if ($this->user_id != $user_info['uid'])
        {
            $user_follow_check = $this->model('follow')->user_follow_check($this->user_id, $user_info['uid']);
        }

        H::ajax_json_output(array(
            'reputation' => $user_info['reputation'],
            'agree_count' => $user_info['agree_count'],
            'thanks_count' => $user_info['thanks_count'],
            'type' => 'people',
            'uid' => $user_info['uid'],
            'user_name' => $user_info['user_name'],
            'avatar_file' => get_avatar_url($user_info['uid'], 'mid'),
            'signature' => $user_info['signature'],
            'focus' => ($user_follow_check ? true : false),
            'is_me' => (($this->user_id == $user_info['uid']) ? true : false),
            'url' => get_js_url('/people/' . $user_info['url_token']),
            'category_enable' => ((get_setting('category_enable') == 'Y') ? 1 : 0),
            'verified' => $user_info['verified'],
            'fans_count' => $user_info['fans_count']
        ));
    }

    public function follows_action()
    {
        switch ($_GET['type'])
        {
            case 'follows':
                $users_list = $this->model('follow')->get_user_friends($_GET['uid'], (intval($_GET['page']) * $this->per_page) . ", {$this->per_page}");
                break;

            case 'fans':
                $users_list = $this->model('follow')->get_user_fans($_GET['uid'], (intval($_GET['page']) * $this->per_page) . ", {$this->per_page}");
                break;
        }

        if ($users_list AND $this->user_id)
        {
            foreach ($users_list as $key => $val)
            {
                $users_ids[] = $val['uid'];
            }

            if ($users_ids)
            {
                $follow_checks = $this->model('follow')->users_follow_check($this->user_id, $users_ids);

                foreach ($users_list as $key => $val)
                {
                    $users_list[$key]['follow_check'] = $follow_checks[$val['uid']];
                }
            }
        }

        TPL::assign('users_list', $users_list);

        TPL::output('people/ajax/follows');
    }

    public function topics_action()
    {
        if ($topic_list = $this->model('topic')->get_focus_topic_list($_GET['uid'], (intval($_GET['page']) * $this->per_page) . ", {$this->per_page}") AND $this->user_id)
        {
            $topic_ids = array();

            foreach ($topic_list as $key => $val)
            {
                $topic_ids[] = $val['topic_id'];
            }

            if ($topic_ids)
            {
                $topic_focus = $this->model('topic')->has_focus_topics($this->user_id, $topic_ids);

                foreach ($topic_list as $key => $val)
                {
                    $topic_list[$key]['has_focus'] = $topic_focus[$val['topic_id']];
                }
            }
        }

        TPL::assign('topic_list', $topic_list);

        TPL::output('people/ajax/topics');
    }

    /**
     * 日常奖励
     */
    public function income_log_action()
    {
        if ($log = $this->model('people')->get_user_income($this->user_id, $_GET['page'])) {
            //积分记录详情数据
            $arr_new_log = array();
            foreach($log as $distribute) {
                $where=' coin_id = ' . $distribute["id"];
                $integral_coin_list = $this->model('yoyowcoin')->fetch_all('integral_yoyow_coin', $where, 'integral_time DESC, id ASC');
                $integral_list = array( 'integral_list'=>$integral_coin_list);
                array_push($arr_new_log, $distribute + $integral_list);
                foreach($integral_coin_list as $single) {
                    $integral_single = $this->model('people')->fetch_row('integral_log','id = ' . $single['integral_id']);
                    $parse_items[$integral_single['id']] = array(
                        'item_id' => $integral_single['item_id'],
                        'action' => $integral_single['action'],
                        'note' =>$integral_single['note']
                    );
                }
            }
            TPL::assign('log', $arr_new_log);
            TPL::assign('integral_log_detail', $this->model('integral')->parse_yoyow_log_item($parse_items));
        }
        TPL::output('people/ajax/income_log');
    }


    /**
     * 转账接口
     */
    public function transfer_to_action(){

       /* if (! $_POST['money'])
        {
            H::ajax_json_output(AWS_APP::RSM(null, -1, AWS_APP::lang()->_t('请输入金额')));
        }
        if (! $_POST['password'])
        {
            H::ajax_json_output(AWS_APP::RSM(null, -1, AWS_APP::lang()->_t('请输入密码')));
        }
        $user_name=$this->model('account')->get_user_info_by_uid($_POST['uid']);
        $user_info = $this->model('account')->check_login($user_name['user_name'], $_POST['password']);
        if (! $user_info)
        {
            H::ajax_json_output(AWS_APP::RSM(null, -1, AWS_APP::lang()->_t('请输入正确的密码')));
        }
        //获取接口地址
        $req_api_url=get_setting('api_url', false);
        //转账账户
        $user_yoyow_account=$this->model('people')->fetch_one('users_yoyow','yoyow',('uid='.$_POST['uid']));
        //转账给指定用户的yoyow账户
        $to_user_yoyow_account=$this->model('people')->fetch_one('users_yoyow','yoyow',('uid='.$_POST['to_uid']));
        //调用接口yoyow币转账
        $send_obj = array(
            'from'=>$user_yoyow_account,
            'to' => $to_user_yoyow_account,
            'amount' => $_POST['money'],
            'memo' => 'memo',
            'time' => microtime(true) * 1000
        );
        $send_json=json_encode($send_obj);
        $yoyow_asc_key=get_setting('yoyow_aes_key', false);
        //请求参数
        $send_args = $this->model("AesSecurity")->encrypt($send_json,$yoyow_asc_key);
        $send_args_obj=json_decode($send_args);
        //转账请求地址
        $req_url_tran=$req_api_url.'/api/v1/transferFromUser';
        //进行请求
        $res_tran_content=AWS_APP::http()->post($req_url_tran,
            null, json_encode(array('ct'=>$send_args_obj->ct,'iv'=>$send_args_obj->iv,'s'=>$send_args_obj->s)));
        //返回值进行解码
        $res_tran_json=json_decode($res_tran_content,true);
        H::ajax_json_output(AWS_APP::RSM(null, -1, AWS_APP::lang()->_t('转账成功')));
        //转账成功
        if($res_tran_json['code'] == 0){

        }*/

    }

    /**
     * 提成奖励
     */
    public function income_commission_action()
    {
        if ($commission = $this->model('people')->get_user_income_commission($this->user_id, $_GET['page'])) {
            TPL::assign('commission', $commission);
        }
        TPL::output('people/ajax/income_commission');
    }

    /**
     * 注册奖励
     */
    public function income_register_action()
    {
        if ($register_list = $this->model('people')->get_user_income_register($this->user_id, $_GET['page'])) {
            TPL::assign('register_list', $register_list);
        }
        TPL::output('people/ajax/income_register');
    }
    /**
     * 邀请奖励
     */
    public function income_invite_action()
    {
        if ($register_list = $this->model('people')->get_user_income_invite($this->user_id, $_GET['page'])) {
            foreach ($register_list as $coin_single){
                $invitation_detail = $this->model('invitation')->fetch_row('invitation', ' invitation_id = '. $coin_single['invitation_id']);
                switch ($coin_single['coin_type']) {
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
                       /* $second_id = $this->model('invitation')->fetch_one('invitation', 'uid', ' invitation_id = '. $coin_single['invitation_id']);
                        $first = $this->model('invitation')->fetch_row('users', ' uid = '.$second_id);*/
                        $invitation_list[] = array_merge(
                            $coin_single,
                            array(
                                'invitation_detail'=>$invitation_detail,
                                'second_user_info'=>$second
                            )
                        );
                        break;
                }
            }
            TPL::assign('register_list', $invitation_list);
        }
        TPL::output('people/ajax/income_invite');
    }

    public function income_sum_action() {
        if($income_sum_list = $this->model('people')->get_yoyow_sum($this->user_id, $_GET['page'])){
            foreach ($income_sum_list AS $single) {
                if($single['type'] == 0){
                    //积分奖励
                    $income_sum[] = $single;
                }else if($single['type'] == 1){
                    //提成奖励
                    $income_sum[] = $single;
                }else if($single['type'] == 2){
                    //邀请奖励
                    $invitation_detail = $this->model('invitation')->fetch_row('invitation', ' invitation_id = '. $single['task_id']);
                    $income_sum[] = array_merge($single, array('invitation_detail'=>$invitation_detail));
                   /* switch ($single['distribute_result']) {
                        case 0:
                            $income_sum[] = array_merge($single, array('invitation_detail'=>$invitation_detail));
                            break;
                        case 1:
                            $child = $this->model('invitation')->fetch_row('users', ' uid = '.$single['act_strat_time']);
                            $income_sum[] = array_merge($single,
                                array(
                                    'invitation_detail'=>$invitation_detail,
                                    'first_user_info'=>$child
                                )
                            );
                            break;
                        case 2:
                            $second = $this->model('invitation')->fetch_row('users', ' uid = '.$single['act_strat_time']);
                            $second_id = $this->model('invitation')->fetch_one('invitation', 'uid', ' invitation_id = '. $single['task_id']);
                            $first = $this->model('invitation')->fetch_row('users', ' uid = '.$second_id);
                            $income_sum[] = array_merge(
                                $single,
                                array(
                                    'invitation_detail'=>$invitation_detail,
                                    'first_user_info'=>$first,
                                    'second_user_info'=>$second
                                )
                            );
                            break;
                    }*/
                }else if($single['type'] == 4){
                    //注册奖励--注册绑定
                    $income_sum[] = $single;
                }
            }
            TPL::assign('income_sum_list', $income_sum);
        }
        TPL::output('people/ajax/income_sum');
    }
}