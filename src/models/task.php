<?php

/**
 * Created by PhpStorm.
 * User: 黄智强
 * Date: 2018-03-19
 * Time: 16:07
 */
class task_class extends AWS_MODEL
{
    /**
     * 通过id查询
     * @param int
     * @return 返回一条数据
     */
    public function query_by_id($id){
        $where="id='".$id."'";
        return $this->fetch_row("task_statistics",$where,null);
    }
}