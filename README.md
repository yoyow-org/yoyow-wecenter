# yoyow-wecenter介绍
  yoyow-wecenter在wecenter社区功能基础上，**主要新增功能：**
  * 平台不能直接注册，必须通过绑定yoyow账号登录。
  * 用户邀请奖励，用户可以通过邀请获得一级、二级奖励
  * 引入了平台奖励机制。 在一定周期内，根据用户在平台的活跃度会获得一定的yoyow币奖励
     活跃度包括用户提问、回答、点赞、点踩等操作
    
    
# 运行环境
  * Nginx 1.8+
  * PHP 5.6+ 7.0-
  * Mysql 5.6+
  
  
# yoyow中间件
  yoyow-wecenter是基于yoyow链的内容平台，奖励的机制也是yoyow币。这就免不了与yoyow链进行交互，目前yoyow-wecenter通过调用yoyow中间件提供的接口方法进行平台与用户之间操作，主要包括yoyow授权登录、转账、查询用户信息
  yoyow中间件部署手册可参照: [如何部署yoyow中间件](https://github.com/yoyow-org/yoyow-node-sdk/tree/master/middleware)
  
  
# 项目安装
  假定读者已经安装好了运行环境，并将src目录下的代码放至服务器上，以及运行了yoyow中间件。这时候即可安装yoyow-wecenter,打开网站 http://服务器ip地址/ 会看到如下图：
  ![install](https://github.com/yoyow-org/yoyow-wecenter/blob/master/public/images/install.jpg)
  
