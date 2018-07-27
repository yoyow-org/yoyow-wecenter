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

class problem extends AWS_ADMIN_CONTROLLER
{
	public function setup()
	{
		TPL::assign('menu_list', $this->model('admin')->fetch_menu_list(320));
	}

	public function list_action()
	{   
		$this->crumb(AWS_APP::lang()->_t('注册问题管理'), 'admin/problem/list/');

        $where = '';

		$problem_list = $this->model('problem')->get_problem_list(implode(' AND ', $where), 'id DESC,add_time DESC', $this->per_page, $_GET['page']);
        
		$total_rows = $this->model('problem')->found_rows();

		

		TPL::assign('pagination', AWS_APP::pagination()->initialize(array(
			'base_url' => get_js_url('/admin/problem/list/'),
			'total_rows' => $total_rows,
			'per_page' => $this->per_page
		))->create_links());

		TPL::assign('problem_count', $total_rows);
		TPL::assign('list', $problem_list);
		TPL::assign('users_info', $users_info);
		TPL::output('admin/problem/list');
	}

	public function edit_action()
	{    

		if ($_GET['id'])
		{
			$this->crumb(AWS_APP::lang()->_t('话题编辑'), 'admin/problem/edit/');

			$problem_info = $this->model('problem')->get_problem_by_id($_GET['id']);
            
			if (!$problem_info)
			{
				H::redirect_msg(AWS_APP::lang()->_t('问题不存在'), '/admin/problem/list/');
			}

			$problem_info['content'] =  $this->model('problem')->get_problem_content($problem_info);
            
            
            TPL::assign('ABC',return_letter(sizeof($problem_info['content'])));
            TPL::assign('ABC_NUM',sizeof($problem_info['content']));
			TPL::assign('problem_info', $problem_info);
			TPL::assign('problen_content',json_encode($problem_info['content']));
		}
		else
		{   
			TPL::assign('ABC','A');
			TPL::assign('ABC_NUM',1);
			$this->crumb(AWS_APP::lang()->_t('新建话题'), 'admin/problem/edit/');
		}

		TPL::import_js('js/fileupload.js');

		TPL::output('admin/problem/edit');
	}
}