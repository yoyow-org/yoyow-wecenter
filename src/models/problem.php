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

class problem_class extends AWS_MODEL
{
	public function save_problem($arr)
	{    
		 foreach ($arr['content'] as $key => $value) {

	 	 	    if(return_letter($key+1) == $arr['right_key'])
 	 	    	{    
                     if(!$value){
                     	  $check = -1;break;
                     }

 	 	    	}
	 	 }
	 	 
	 	 if($check == -1)return array('status'=>-1,'msg'=>'正确答案不可为空');
         

		 if($arr['id']) //保存
		 {   
             $rs['status'] = $this->query("UPDATE ". get_table('register_problem')." SET title='".$arr['title']."',content='".serialize(array_unique(array_filter($arr['content'])))."',right_key='".$arr['right_key']."',status=".$arr['status']." WHERE id=".$arr['id']);
		 	 return $rs;
		 	 
		 }else
		 {   
		 	 $rs['status'] = $this->query("INSERT ". get_table('register_problem')."(title,content,right_key,status,add_time)VALUES('".$arr['title']."','".serialize(array_unique(array_filter($arr['content'])))."','".$arr['right_key']."',".$arr['status'].",".time().")");
		 	 return $rs;

		 }
	}


	public function get_problem_list($where = null, $order = 'id DESC,add_time DESC', $limit = 10, $page = null)
	{   


		if ($problem_list = $this->fetch_page('register_problem', $where, $order, $page, $limit))
		{   


			foreach ($problem_list AS $key => $val)
			{   


                $arr = unserialize($val['content']);


                foreach ($arr as $k => $value) {
                	 $arr[$k] = return_letter($k+1).":".$value;
                }

				$problem_list[$key]['content'] = implode(",",$arr);
			}
		}


		return $problem_list;
	}



	public function get_problem_content($val)
	{   


		$arr = unserialize($val['content']);
        foreach ($arr as $k => $value) {
        	 $arr[$k] = array(return_letter($k+1),$value);
        }
		return $arr;
	}


	/**
	 *
	 * 得到单条问题
	 * @param int $problem_id 话题ID
	 *
	 * @return array
	 */
	public function get_problem_by_id($problem_id)
	{
		static $problems;

		if (! $problem_id)
		{
			return false;
		}

		if (! $problems[$problem_id])
		{
			$problems[$problem_id] = $this->fetch_row('register_problem', 'id = ' . intval($problem_id));

		}

		return $problems[$problem_id];
	}



	/**
	 * 物理删除问题
	 *
	 * @param  $problem_id
	 */
	public function remove_problem_by_ids($problem_id)
	{   
		if (!$problem_id)
		{
			return false;
		}

		if (is_array($problem_id))
		{
			$problem_ids = $problem_id;
		}
		else
		{
			$problem_ids[] = $problem_id;
		}

		array_walk_recursive($problem_ids, 'intval_string');

		foreach($problem_ids as $problem_id)
		{
			if (!$problem_info = $this->get_problem_by_id($problem_id))
			{
				continue;
			}
			
			$this->delete('register_problem', 'id = ' . intval($problem_id));
		}

		return true;
	}



	/**
	 *
	 * 锁定/解锁问题
	 * @param int $problem_id
	 * @param int $status
	 *
	 * @return boolean true|false
	 */
	public function lock_problem_by_ids($problem_ids, $status = 0)
	{   
		if (!$problem_ids)
		{
			return false;
		}

		if (!is_array($problem_ids))
		{
			$problem_ids = array(
				$problem_ids,
			);
		}

		array_walk_recursive($problem_ids, 'intval_string');

		return $this->update('register_problem', array(
			'status' => $status
		), 'id IN (' . implode(',', $problem_ids) . ')');

	}


    //判断是否开启注册回复问题验证 如开启则返回问题数组
	public function is_check_problem()
	{
	    if(get_setting('is_register_problem') == 'Y')
	    {    
	    	 if(get_setting('register_problem_num'))
	    	 {
	    	 	 foreach ($this->query_all('SELECT * FROM (SELECT id ,ROUND(RAND()) AS newno FROM '.get_table('register_problem').' GROUP BY id)AS t ORDER BY t.newno asc LIMIT '.get_setting('register_problem_num')) as $key => $value) {
	    	 	 	 $arr[$key] = $this->get_problem_by_id($value['id']);
	    	 	 	 $arr[$key]['content'] = $this->get_problem_content($arr[$key]);
	    	 	 }

	    	 	 return $arr;
	    	 }

	    	 return array();
             
	    }
	}
}