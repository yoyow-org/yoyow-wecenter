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

class yoyowcoin_class extends AWS_MODEL{

    /**
     * 通过id查询
     * @param int
     * @return 返回一条数据
     */
    public function query_by_id($id){
        $where="id='".$id."'";
        return $this->fetch_row("user_yoyow_coin",$where,null);
    }

}