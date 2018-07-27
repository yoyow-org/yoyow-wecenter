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

class rankinglist_class extends AWS_MODEL
{      
		
	   /**
		 * 生成实时邀请yoyow排行榜
		 * @return int
		 */
	   public function get_ranking()
	   {    
            
            $this->delete('ranking_list','add_time > 0');
            
            $ranking = 1;

            foreach ($this->query_all("SELECT coin_uid AS uid,`time`,SUM(coin) as yoyow_num,COUNT(base_uid) as invite_num , (SELECT user_name FROM ".get_table('users')." where uid = coin_uid) AS user_name FROM " . get_table('invitation_yoyow') ." where coin_type !=0 and effective =1 GROUP BY coin_uid ORDER BY yoyow_num DESC,invite_num DESC,time DESC") as $k => $value) {
                
                if($value['user_name'])
                {
                	    $value['ranking'] = $ranking++;
		                $value['avatar_file'] = get_avatar_url($value['uid']);
		                $value['add_time'] = time();
		                
		                $this->insert('ranking_list',$value);
                }
                

            }


	   }
	   

	   //获取实时yoyow排行榜
	   public function get_thumbup($page = 1,$per_page = 10){
	         $limit = intval($page-1) * $per_page . ', ' . $per_page;
	         return $this->fetch_all('ranking_list','add_time > 0','ranking ASC',$limit);
	   }

       //获取个人yoyow排行信息
	   public function get_myinfo($uid)
	   {     
	   	     if($uid)return $this->fetch_row('ranking_list','uid='.$uid);
	   	       return array();
             
	   }


	   

	
}