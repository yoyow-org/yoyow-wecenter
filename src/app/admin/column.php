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

class column extends AWS_ADMIN_CONTROLLER
{
    public function list_action()
    {

        $this->crumb(AWS_APP::lang()->_t('专栏管理'), 'admin/column/list');

        if ($columns_list = $this->model('column')->fetch_page('column', '', '', $_GET['page'], $this->per_page))
        {
            $columns_total = $this->model('column')->found_rows();
        }

        if ($columns_list)
        {
            foreach ($columns_list AS $key => $val)
            {
                $columns_list_uids[$val['uid']] = $val['uid'];
            }

            if ($columns_list_uids)
            {
                $columns_list_user_infos = $this->model('account')->get_user_info_by_uids($columns_list_uids);
            }

            foreach ($columns_list AS $key => $val)
            {
                $columns_list[$key]['user_info'] = $columns_list_user_infos[$val['uid']];
            }
        }

        //获取推荐数量
        $recommend_num = count($this->model('column')->get_recommend_columns_list());

        TPL::assign('pagination', AWS_APP::pagination()->initialize(array(
            'base_url' => get_js_url('/admin/column/list/'),
            'total_rows' => $columns_total,
            'per_page' => $this->per_page
        ))->create_links());


        TPL::assign('menu_list', $this->model('admin')->fetch_menu_list(315));
        TPL::assign('columns_list', $columns_list);
        TPL::assign('recommend_num', $recommend_num);
        TPL::output('admin/column/list');
    }

    public function recommend_action()
    {
        $columns_list = $this->model('column')->get_recommend_columns_list();
        if ($columns_list)
        {
            foreach ($columns_list AS $key => $val)
            {
                $columns_list_uids[$val['uid']] = $val['uid'];
            }

            if ($columns_list_uids)
            {
                $columns_list_uids_infos = $this->model('account')->get_user_info_by_uids($columns_list_uids);
            }

            foreach ($columns_list AS $key => $val)
            {
                $columns_list[$key]['user_info'] = $columns_list_uids_infos[$val['uid']];
            }
        }

        TPL::assign('columns_list',$columns_list);
        TPL::assign('recommend_num', count($columns_list));
        TPL::assign('menu_list', $this->model('admin')->fetch_menu_list(315));
        TPL::output('admin/column/recommend');
    }

    /**
     * 设置用户推荐
     * @return bool
     */
    public function ajax_set_recommend_action()
    {
        $id = $_POST['id'] * 1;
        $val = $_POST['value'] * 1;
        $type = $_POST['type'] ? $_POST['type'] : "list";
        $page = $_POST['page'] ? $_POST['page'] : 1;

        if(!$id){
            return false;
        }

        $num = count($this->model('column')->get_recommend_columns_list());

        if($num >= 10 && $val == 1){
            return false;
        }

        $this->model('column')->update('column', array(
            'is_recommend' => $val
        ), 'id = '.intval($id));


        $columns_list = array();
        if($type == "list"){
            $columns_list = $this->model('column')->fetch_page('column', '', '', $page, $this->per_page);
        }else if($type == "recommend"){
            $columns_list = $this->model('column')->get_recommend_columns_list();
        }

        if ($columns_list)
        {
            foreach ($columns_list AS $key => $val)
            {
                $columns_list_uids[$val['uid']] = $val['uid'];
            }

            if ($columns_list_uids)
            {
                $columns_list_user_infos = $this->model('account')->get_user_info_by_uids($columns_list_uids);
            }

            foreach ($columns_list AS $key => $val)
            {
                $columns_list[$key]['user_info'] = $columns_list_user_infos[$val['uid']];
            }
        }

        //获取推荐数量
        $recommend_num = count($this->model('column')->get_recommend_columns_list());

        TPL::assign('type',$type);
        TPL::assign('columns_list',$columns_list);
        TPL::assign('recommend_num', $recommend_num);
        TPL::output('admin/column/ajax_set_recommend');
    }


}