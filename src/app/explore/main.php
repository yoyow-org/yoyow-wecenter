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
    public function get_access_rule()
    {
        $rule_action['rule_type'] = "white"; //'black'黑名单,黑名单中的检查  'white'白名单,白名单以外的检查

        if ($this->user_info['permission']['visit_explore'] AND $this->user_info['permission']['visit_site'])
        {
            $rule_action['actions'][] = 'index';
        }

        return $rule_action;
    }

    public function setup()
    {
        if (is_mobile() AND !$_GET['ignore_ua_check'])
        {
            switch ($_GET['app'])
            {
                default:
                    HTTP::redirect('/m/');
                    break;
            }
        }
    }

    public function index_action()
    {
        if (is_mobile())
        {
            HTTP::redirect('/m/explore/' . $_GET['id']);
        }

        if ($this->user_id)
        {
            $this->crumb(AWS_APP::lang()->_t('发现'), '/explore');

            if (! $this->user_info['email'])
            {
                HTTP::redirect('/account/complete_profile/');
            }
        }

        if ($_GET['category'])
        {
            if (is_digits($_GET['category']))
            {
                $category_info = $this->model('system')->get_category_info($_GET['category']);
            }
            else
            {
                $category_info = $this->model('system')->get_category_info_by_url_token($_GET['category']);
            }
        }

        if ($category_info)
        {
            TPL::assign('category_info', $category_info);

            $this->crumb($category_info['title'], '/category-' . $category_info['id']);

            $meta_description = $category_info['title'];

            if ($category_info['description'])
            {
                $meta_description .= ' - ' . $category_info['description'];
            }

            TPL::set_meta('description', $meta_description);
        }

        // 导航
        if (TPL::is_output('block/content_nav_menu.tpl.htm', 'explore/index'))
        {
            TPL::assign('content_nav_menu', $this->model('menu')->get_nav_menu_list('explore'));
        }

        // 边栏可能感兴趣的人
        if (TPL::is_output('block/sidebar_recommend_users_topics.tpl.htm', 'explore/index'))
        {
            TPL::assign('sidebar_recommend_users_topics', $this->model('module')->recommend_users_topics($this->user_id));
        }

        // 边栏热门用户
        if (TPL::is_output('block/sidebar_hot_users.tpl.htm', 'explore/index'))
        {
            TPL::assign('sidebar_hot_users', $this->model('module')->sidebar_hot_users($this->user_id, 5));
        }

        // 边栏热门话题
        if (TPL::is_output('block/sidebar_hot_topics.tpl.htm', 'explore/index'))
        {
            TPL::assign('sidebar_hot_topics', $this->model('module')->sidebar_hot_topics($category_info['id']));
        }

        // 边栏专题
        if (TPL::is_output('block/sidebar_feature.tpl.htm', 'explore/index'))
        {
            TPL::assign('feature_list', $this->model('module')->feature_list());
        }

        //边栏首页的二维码图片
        if(TPL::is_output('block/sidebar_hot_users.tpl.htm', 'explore/index')){
            TPL::assign('index_activity', $this->model('module')->fetch_all("index_activity"));
        }
        if (! $_GET['sort_type'] AND !$_GET['is_recommend'])
        {
            $_GET['sort_type'] = 'new';
        }

        if ($_GET['sort_type'] == 'hot')
        {

            $posts_list = $this->model('posts')->get_hot_posts(null, $category_info['id'], null, $_GET['day'], $_GET['page'], get_setting('contents_per_page'));
        }
        else
        {

            $posts_list = $this->model('posts')->get_posts_list(null, $_GET['page'], get_setting('contents_per_page'), $_GET['sort_type'], null, $category_info['id'], $_GET['answer_count'], $_GET['day'], $_GET['is_recommend']);
        }



        if ($posts_list)
        {
            foreach ($posts_list AS $key => $val)
            {
                if ($val['answer_count'])
                {
                    $posts_list[$key]['answer_users'] = $this->model('question')->get_answer_users_by_question_id($val['question_id'], 2, $val['published_uid']);
                }
                if($val['answer_count']>0){
                   /* $answer = $this->model('integral')->fetch_row('answer','answer_id ='.$val['answer_info']['answer_id']);
                    //计算点赞的积分
                    $praise_oppose_list=$this->model('integral')->fetch_all('answer_vote','answer_id = '.$val['answer_info']['answer_id']);
                    $praise_oppose_integrals = 0;
                    $answer_question_integrals = 0;
                    foreach ($praise_oppose_list AS $ke=>$vl){
                        if($int_id = $this->model('integral')->fetch_one('integral_log','id','action = "'.($vl['vote_value'] == 1 ? 'PRAISE':'OPPOSE').'" and uid ='.$vl['answer_uid'].' and item_id ='.$vl['vote_uid'].' and time ='.$vl['add_time'])){
                            $praise_oppose_integral = $this->model('assigntask')->get_integral_yoyow_by_integral_id($int_id);
                            $praise_oppose_integrals += $praise_oppose_integral;
                        }
                    }
                    //计算该回复获取的总积分
                    $answer_question_list = $this->model('integral')->fetch_all('integral_log','action = "QUESTION_ANSWER_DISCUSS" AND uid = '.$answer['uid'].' AND item_id = '.$answer['answer_id']);
                    foreach ($answer_question_list as $k=>$v){
                        $answer_question_integral = $this->model('assigntask')->get_integral_yoyow_by_integral_id($v['id']);
                        $answer_question_integrals += $answer_question_integral;
                    }

                    $integral_id=$this->model('integral')->get_integral_id_by_type($answer['question_id'],"ANSWER_QUESTION",$answer['uid']);
                    $answer_yoyow_income=$this->model('assigntask')->get_integral_yoyow_by_integral_id($integral_id);
                    if($answer_yoyow_income=="无积分记录Id" || !$answer_yoyow_income){
                        $answer_yoyow_income=0;
                    }

                    //计算总积分
                    $yoyow_income =$answer_yoyow_income + $praise_oppose_integrals + $answer_question_integrals;
                    $posts_list[$key]['yoyow_answer']=$yoyow_income*((get_setting('yoyow_rmb_rate')=='') ? 0: get_setting('yoyow_rmb_rate'));*/
                }
                //计算问题下回复的奖励
                $answer_incomes = 0;
                $answer_list = $this->model('question')->fetch_all('integral_log','action = "QUESTION_ANSWER" AND item_id = '.$val['question_id']);
                foreach($answer_list AS $ks=>$vs){
                    $answer_income = $this->model('assigntask')->get_integral_yoyow_by_integral_id($vs['id']);
                    $answer_incomes += $answer_income;
                }

                //发起问题的奖励
                $integral_id=$this->model('integral')->get_integral_id_by_type($val['question_id'],"NEW_QUESTION");
                $question_income=$this->model('assigntask')->get_integral_yoyow_by_integral_id($integral_id);
                if($question_income=="无积分记录Id" || !$question_income){
                    $question_income=0;
                }
                $yoyow_question_income = $question_income + $answer_incomes;
                $posts_list[$key]['yoyow_question']=$yoyow_question_income*((get_setting('yoyow_rmb_rate')=='') ? 0: get_setting('yoyow_rmb_rate'));

            }
        }

        TPL::assign('pagination', AWS_APP::pagination()->initialize(array(
            'base_url' => get_js_url('/sort_type-' . preg_replace("/[\(\)\.;']/", '', $_GET['sort_type']) . '__category-' . $category_info['id'] . '__day-' . intval($_GET['day']) . '__is_recommend-' . intval($_GET['is_recommend'])),
            'total_rows' => $this->model('posts')->get_posts_list_total(),
            'per_page' => get_setting('contents_per_page')
        ))->create_links());

        TPL::assign('posts_list', $posts_list);


        TPL::assign('posts_list_bit', TPL::output('explore/ajax/list', false));
        if($announce_list=$this->model('help')->fetch_all('site_announce','status = 0','time DESC','3')){
            TPL::assign('announce_list', $announce_list);
        }

        if($link_list=$this->model('help')->fetch_all('site_link',null,'sort ASC,id ASC')){
            TPL::assign('link_list', $link_list);
        }
        TPL::output('explore/index');
    }
}