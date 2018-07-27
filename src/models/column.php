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

class column_class extends AWS_MODEL
{
    public function get_column_by_id($id)
    {
        return $this->fetch_all('column','id = '.$id);
    }

    /**
     * 获取推荐列表
     * @return array
     */
    public function get_recommend_columns_list()
    {
        return $this->fetch_all('column','is_recommend = 1');
    }

    public function get_recommend_user_lists_for_column($sql)
    {
        return  $this->query_all($sql);
    }

    public function remove_user_by_uid($uid)
    {
        $this->delete('column', 'uid = ' .$uid);
    }


}
