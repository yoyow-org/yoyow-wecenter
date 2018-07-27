CREATE TABLE `[#DB_PREFIX#]active_data` (
  `active_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `uid` int(11) DEFAULT '0',
  `expire_time` int(10) DEFAULT NULL,
  `active_code` varchar(32) DEFAULT NULL,
  `active_type_code` varchar(16) DEFAULT NULL,
  `add_time` int(10) DEFAULT NULL,
  `add_ip` bigint(12) DEFAULT NULL,
  `active_time` int(10) DEFAULT NULL,
  `active_ip` bigint(12) DEFAULT NULL,
  PRIMARY KEY (`active_id`),
  KEY `active_code` (`active_code`),
  KEY `active_type_code` (`active_type_code`),
  KEY `uid` (`uid`)
) ENGINE=MyISAM AUTO_INCREMENT=37 DEFAULT CHARSET=utf8;

CREATE TABLE `[#DB_PREFIX#]answer` (
  `answer_id` int(11) NOT NULL AUTO_INCREMENT COMMENT '回答id',
  `question_id` int(11) NOT NULL COMMENT '问题id',
  `answer_content` text CHARACTER SET utf8mb4,
  `add_time` int(10) DEFAULT '0' COMMENT '添加时间',
  `against_count` int(11) NOT NULL DEFAULT '0' COMMENT '反对人数',
  `agree_count` int(11) NOT NULL DEFAULT '0' COMMENT '支持人数',
  `uid` int(11) DEFAULT '0' COMMENT '发布问题用户ID',
  `comment_count` int(11) DEFAULT '0' COMMENT '评论总数',
  `uninterested_count` int(11) DEFAULT '0' COMMENT '不感兴趣',
  `thanks_count` int(11) DEFAULT '0' COMMENT '感谢数量',
  `category_id` int(11) DEFAULT '0' COMMENT '分类id',
  `has_attach` tinyint(1) DEFAULT '0' COMMENT '是否存在附件',
  `ip` bigint(11) DEFAULT NULL,
  `force_fold` tinyint(1) DEFAULT '0' COMMENT '强制折叠',
  `anonymous` tinyint(1) DEFAULT '0',
  `publish_source` varchar(16) DEFAULT NULL,
  PRIMARY KEY (`answer_id`),
  KEY `question_id` (`question_id`),
  KEY `agree_count` (`agree_count`),
  KEY `against_count` (`against_count`),
  KEY `add_time` (`add_time`),
  KEY `uid` (`uid`),
  KEY `uninterested_count` (`uninterested_count`),
  KEY `force_fold` (`force_fold`),
  KEY `anonymous` (`anonymous`),
  KEY `publich_source` (`publish_source`)
) ENGINE=MyISAM AUTO_INCREMENT=333 DEFAULT CHARSET=utf8 COMMENT='回答';

CREATE TABLE `[#DB_PREFIX#]answer_comments` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `answer_id` int(11) DEFAULT '0',
  `uid` int(11) DEFAULT '0',
  `message` text CHARACTER SET utf8mb4,
  `time` int(10) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `answer_id` (`answer_id`),
  KEY `time` (`time`)
) ENGINE=MyISAM AUTO_INCREMENT=62 DEFAULT CHARSET=utf8;

CREATE TABLE `[#DB_PREFIX#]answer_thanks` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `uid` int(11) DEFAULT '0',
  `answer_id` int(11) DEFAULT '0',
  `user_name` varchar(255) DEFAULT NULL,
  `time` int(10) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `answer_id` (`answer_id`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;

CREATE TABLE `[#DB_PREFIX#]answer_uninterested` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `uid` int(11) DEFAULT '0',
  `answer_id` int(11) DEFAULT '0',
  `user_name` varchar(255) DEFAULT NULL,
  `time` int(10) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `answer_id` (`answer_id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

CREATE TABLE `[#DB_PREFIX#]answer_vote` (
  `voter_id` int(11) NOT NULL AUTO_INCREMENT COMMENT '自动ID',
  `answer_id` int(11) DEFAULT NULL COMMENT '回复id',
  `answer_uid` int(11) DEFAULT NULL COMMENT '回复作者id',
  `vote_uid` int(11) DEFAULT NULL COMMENT '用户ID',
  `add_time` int(10) DEFAULT NULL COMMENT '添加时间',
  `vote_value` tinyint(4) NOT NULL COMMENT '-1反对 1 支持',
  `reputation_factor` int(10) DEFAULT '0',
  PRIMARY KEY (`voter_id`),
  KEY `answer_id` (`answer_id`),
  KEY `vote_value` (`vote_value`)
) ENGINE=MyISAM AUTO_INCREMENT=425 DEFAULT CHARSET=utf8;

CREATE TABLE `[#DB_PREFIX#]approval` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `type` varchar(16) DEFAULT NULL,
  `data` mediumtext NOT NULL,
  `uid` int(11) NOT NULL DEFAULT '0',
  `time` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `type` (`type`),
  KEY `uid` (`uid`),
  KEY `time` (`time`)
) ENGINE=MyISAM AUTO_INCREMENT=40 DEFAULT CHARSET=utf8;

CREATE TABLE `[#DB_PREFIX#]article` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `uid` int(10) NOT NULL,
  `title` varchar(200) CHARACTER SET utf8mb4 NOT NULL,
  `message` text CHARACTER SET utf8mb4,
  `comments` int(10) DEFAULT '0',
  `views` int(10) DEFAULT '0',
  `add_time` int(10) DEFAULT NULL,
  `has_attach` tinyint(1) NOT NULL DEFAULT '0',
  `lock` int(1) NOT NULL DEFAULT '0',
  `votes` int(10) DEFAULT '0',
  `title_fulltext` text,
  `category_id` int(10) DEFAULT '0',
  `is_recommend` tinyint(1) DEFAULT '0',
  `chapter_id` int(10) unsigned DEFAULT NULL,
  `sort` tinyint(2) unsigned NOT NULL DEFAULT '0',
  `cover_file` varchar(255) DEFAULT NULL,
  `is_top` int(1) DEFAULT '0' COMMENT '0=>未置顶，1=>置',
  `set_top_time` int(11) DEFAULT NULL COMMENT '置顶时间',
  PRIMARY KEY (`id`),
  KEY `has_attach` (`has_attach`),
  KEY `uid` (`uid`),
  KEY `comments` (`comments`),
  KEY `views` (`views`),
  KEY `add_time` (`add_time`),
  KEY `lock` (`lock`),
  KEY `votes` (`votes`),
  KEY `category_id` (`category_id`),
  KEY `is_recommend` (`is_recommend`),
  KEY `chapter_id` (`chapter_id`),
  KEY `sort` (`sort`),
  FULLTEXT KEY `title_fulltext` (`title_fulltext`)
) ENGINE=MyISAM AUTO_INCREMENT=466 DEFAULT CHARSET=utf8;

CREATE TABLE `[#DB_PREFIX#]article_comments` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `uid` int(10) NOT NULL,
  `article_id` int(10) NOT NULL,
  `message` text CHARACTER SET utf8mb4,
  `add_time` int(10) NOT NULL,
  `at_uid` int(10) DEFAULT NULL,
  `votes` int(10) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `uid` (`uid`),
  KEY `article_id` (`article_id`),
  KEY `add_time` (`add_time`),
  KEY `votes` (`votes`)
) ENGINE=MyISAM AUTO_INCREMENT=146 DEFAULT CHARSET=utf8;

CREATE TABLE `[#DB_PREFIX#]article_vote` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `uid` int(10) NOT NULL,
  `type` varchar(16) DEFAULT NULL,
  `item_id` int(10) NOT NULL,
  `rating` tinyint(1) DEFAULT '0',
  `time` int(10) NOT NULL,
  `reputation_factor` int(10) DEFAULT '0',
  `item_uid` int(10) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `uid` (`uid`),
  KEY `type` (`type`),
  KEY `item_id` (`item_id`),
  KEY `time` (`time`),
  KEY `item_uid` (`item_uid`)
) ENGINE=MyISAM AUTO_INCREMENT=93 DEFAULT CHARSET=utf8;

CREATE TABLE `[#DB_PREFIX#]attach` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `file_name` varchar(255) DEFAULT NULL COMMENT '附件名称',
  `access_key` varchar(32) DEFAULT NULL COMMENT '批次 Key',
  `add_time` int(10) DEFAULT '0' COMMENT '上传时间',
  `file_location` varchar(255) DEFAULT NULL COMMENT '文件位置',
  `is_image` int(1) DEFAULT '0',
  `item_type` varchar(32) DEFAULT '0' COMMENT '关联类型',
  `item_id` bigint(20) DEFAULT '0' COMMENT '关联 ID',
  `wait_approval` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `access_key` (`access_key`),
  KEY `is_image` (`is_image`),
  KEY `fetch` (`item_id`,`item_type`),
  KEY `wait_approval` (`wait_approval`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

CREATE TABLE `[#DB_PREFIX#]category` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(128) DEFAULT NULL,
  `type` varchar(16) DEFAULT NULL,
  `icon` varchar(255) DEFAULT NULL,
  `parent_id` int(11) DEFAULT '0',
  `sort` smallint(6) DEFAULT '0',
  `url_token` varchar(32) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `parent_id` (`parent_id`),
  KEY `url_token` (`url_token`),
  KEY `title` (`title`)
) ENGINE=MyISAM AUTO_INCREMENT=12 DEFAULT CHARSET=utf8;

CREATE TABLE `[#DB_PREFIX#]column` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) DEFAULT NULL,
  `is_recommend` int(11) DEFAULT NULL COMMENT '是否推荐',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=159 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

CREATE TABLE `[#DB_PREFIX#]draft` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `uid` int(11) DEFAULT '0',
  `type` varchar(16) DEFAULT NULL,
  `item_id` int(11) DEFAULT NULL,
  `data` text,
  `time` int(10) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `uid` (`uid`),
  KEY `item_id` (`item_id`),
  KEY `time` (`time`)
) ENGINE=MyISAM AUTO_INCREMENT=255 DEFAULT CHARSET=utf8;

CREATE TABLE `[#DB_PREFIX#]edm_task` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `message` mediumtext NOT NULL,
  `subject` varchar(255) NOT NULL,
  `from_name` varchar(255) NOT NULL,
  `time` int(10) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `[#DB_PREFIX#]edm_taskdata` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `taskid` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `sent_time` int(10) NOT NULL,
  `view_time` int(10) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `taskid` (`taskid`),
  KEY `sent_time` (`sent_time`),
  KEY `view_time` (`view_time`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `[#DB_PREFIX#]edm_unsubscription` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(255) NOT NULL,
  `time` int(10) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `email` (`email`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `[#DB_PREFIX#]edm_userdata` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `usergroup` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `usergroup` (`usergroup`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `[#DB_PREFIX#]edm_usergroup` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `time` int(10) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `[#DB_PREFIX#]education_experience` (
  `education_id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) DEFAULT NULL COMMENT '用户UID',
  `education_years` int(11) DEFAULT NULL COMMENT '入学年份',
  `school_name` varchar(64) DEFAULT NULL COMMENT '学校名',
  `school_type` tinyint(4) DEFAULT NULL COMMENT '学校类别',
  `departments` varchar(64) DEFAULT NULL COMMENT '院系',
  `add_time` int(10) DEFAULT NULL COMMENT '记录添加时间',
  PRIMARY KEY (`education_id`),
  KEY `uid` (`uid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='教育经历';

CREATE TABLE `[#DB_PREFIX#]favorite` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `uid` int(11) DEFAULT '0',
  `item_id` int(11) DEFAULT '0',
  `time` int(10) DEFAULT '0',
  `type` varchar(16) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `uid` (`uid`),
  KEY `time` (`time`),
  KEY `item_id` (`item_id`),
  KEY `type` (`type`)
) ENGINE=MyISAM AUTO_INCREMENT=14 DEFAULT CHARSET=utf8;

CREATE TABLE `[#DB_PREFIX#]favorite_tag` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `uid` int(11) DEFAULT '0',
  `title` varchar(128) DEFAULT NULL,
  `item_id` int(11) DEFAULT '0',
  `type` varchar(16) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `uid` (`uid`),
  KEY `title` (`title`),
  KEY `type` (`type`),
  KEY `item_id` (`item_id`)
) ENGINE=MyISAM AUTO_INCREMENT=62 DEFAULT CHARSET=utf8;

CREATE TABLE `[#DB_PREFIX#]feature` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(200) DEFAULT NULL COMMENT '专题标题',
  `description` varchar(255) DEFAULT NULL COMMENT '专题描述',
  `icon` varchar(255) DEFAULT NULL COMMENT '专题图标',
  `topic_count` int(11) NOT NULL DEFAULT '0' COMMENT '话题计数',
  `css` text COMMENT '自定义CSS',
  `url_token` varchar(32) DEFAULT NULL,
  `seo_title` varchar(255) DEFAULT NULL,
  `enabled` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `url_token` (`url_token`),
  KEY `title` (`title`),
  KEY `enabled` (`enabled`)
) ENGINE=MyISAM AUTO_INCREMENT=9 DEFAULT CHARSET=utf8;

CREATE TABLE `[#DB_PREFIX#]feature_topic` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `feature_id` int(11) NOT NULL DEFAULT '0' COMMENT '专题ID',
  `topic_id` int(11) NOT NULL DEFAULT '0' COMMENT '话题ID',
  PRIMARY KEY (`id`),
  KEY `feature_id` (`feature_id`),
  KEY `topic_id` (`topic_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `[#DB_PREFIX#]geo_location` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `item_type` varchar(32) NOT NULL,
  `item_id` int(10) NOT NULL,
  `latitude` float NOT NULL,
  `longitude` float NOT NULL,
  `add_time` int(10) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `item_type` (`item_type`),
  KEY `add_time` (`add_time`),
  KEY `geo_location` (`latitude`,`longitude`),
  KEY `item_id` (`item_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `[#DB_PREFIX#]help_chapter` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `description` text,
  `url_token` varchar(32) DEFAULT NULL,
  `sort` tinyint(2) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `title` (`title`),
  KEY `url_token` (`url_token`),
  KEY `sort` (`sort`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COMMENT='帮助中心';

CREATE TABLE `[#DB_PREFIX#]inbox` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) DEFAULT NULL COMMENT '发送者 ID',
  `dialog_id` int(11) DEFAULT NULL COMMENT '对话id',
  `message` text CHARACTER SET utf8mb4 COMMENT '内容',
  `add_time` int(10) DEFAULT NULL COMMENT '添加时间',
  `sender_remove` tinyint(1) DEFAULT '0',
  `recipient_remove` tinyint(1) DEFAULT '0',
  `receipt` int(10) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `dialog_id` (`dialog_id`),
  KEY `uid` (`uid`),
  KEY `add_time` (`add_time`),
  KEY `sender_remove` (`sender_remove`),
  KEY `recipient_remove` (`recipient_remove`),
  KEY `sender_receipt` (`receipt`)
) ENGINE=MyISAM AUTO_INCREMENT=85 DEFAULT CHARSET=utf8;

CREATE TABLE `[#DB_PREFIX#]inbox_dialog` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '对话ID',
  `sender_uid` int(11) DEFAULT NULL COMMENT '发送者UID',
  `sender_unread` int(11) DEFAULT NULL COMMENT '发送者未读',
  `recipient_uid` int(11) DEFAULT NULL COMMENT '接收者UID',
  `recipient_unread` int(11) DEFAULT NULL COMMENT '接收者未读',
  `add_time` int(11) DEFAULT NULL COMMENT '添加时间',
  `update_time` int(11) DEFAULT NULL COMMENT '最后更新时间',
  `sender_count` int(11) DEFAULT NULL COMMENT '发送者显示对话条数',
  `recipient_count` int(11) DEFAULT NULL COMMENT '接收者显示对话条数',
  PRIMARY KEY (`id`),
  KEY `recipient_uid` (`recipient_uid`),
  KEY `sender_uid` (`sender_uid`),
  KEY `update_time` (`update_time`),
  KEY `add_time` (`add_time`)
) ENGINE=MyISAM AUTO_INCREMENT=12 DEFAULT CHARSET=utf8;

CREATE TABLE `[#DB_PREFIX#]index_activity` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `title` varchar(500) DEFAULT NULL COMMENT '标题',
  `linkurl` varchar(500) NOT NULL COMMENT '图片地址',
  `time` int(10) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;

CREATE TABLE `[#DB_PREFIX#]integral_action` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `action` varchar(16) DEFAULT NULL COMMENT '操作类型',
  `desc` varchar(30) DEFAULT NULL COMMENT '描述',
  `flag` char(1) DEFAULT NULL COMMENT '是否启用0：否 1：是',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COMMENT='积分操作类型表';

CREATE TABLE `[#DB_PREFIX#]integral_log` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `uid` int(11) DEFAULT '0',
  `action` varchar(26) DEFAULT NULL,
  `integral` int(11) DEFAULT NULL,
  `note` varchar(128) DEFAULT NULL,
  `balance` int(11) DEFAULT '0',
  `item_id` int(11) DEFAULT '0',
  `time` int(10) DEFAULT '0',
  `has_distribute` int(1) DEFAULT '0' COMMENT '是否已分配(0未分配，1已分配)',
  PRIMARY KEY (`id`),
  KEY `uid` (`uid`),
  KEY `action` (`action`),
  KEY `time` (`time`),
  KEY `integral` (`integral`)
) ENGINE=MyISAM AUTO_INCREMENT=2086 DEFAULT CHARSET=utf8;

CREATE TABLE `[#DB_PREFIX#]integral_yoyow_coin` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `integral_id` int(11) DEFAULT NULL COMMENT '积分Id',
  `coin_id` int(11) DEFAULT NULL COMMENT 'yoyow记录Id',
  `note` varchar(128) DEFAULT NULL COMMENT '备注',
  `coin` decimal(16,4) DEFAULT NULL COMMENT '分币数量',
  `distribute_time` int(10) DEFAULT NULL COMMENT '添加时间',
  `integral` int(11) DEFAULT NULL COMMENT '积分',
  `integral_time` int(10) DEFAULT NULL COMMENT '时间',
  PRIMARY KEY (`id`),
  KEY `integral_id` (`integral_id`)
) ENGINE=InnoDB AUTO_INCREMENT=5981 DEFAULT CHARSET=utf8;

CREATE TABLE `[#DB_PREFIX#]invitation` (
  `invitation_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '激活ID',
  `uid` int(11) DEFAULT '0' COMMENT '用户ID',
  `invitation_code` varchar(32) DEFAULT NULL COMMENT '激活码',
  `invitation_email` varchar(255) DEFAULT NULL COMMENT '激活email',
  `add_time` int(10) DEFAULT NULL COMMENT '添加时间',
  `add_ip` bigint(12) DEFAULT NULL COMMENT '添加IP',
  `active_expire` tinyint(1) DEFAULT '0' COMMENT '激活过期',
  `active_time` int(10) DEFAULT NULL COMMENT '激活时间',
  `active_ip` bigint(12) DEFAULT NULL COMMENT '激活IP',
  `active_status` tinyint(4) DEFAULT '0' COMMENT '1已使用0未使用-1已删除',
  `active_uid` int(11) DEFAULT NULL,
  `invitation_type` int(1) DEFAULT '0' COMMENT '邀请类型：0邮箱，1邀请码',
  PRIMARY KEY (`invitation_id`),
  KEY `uid` (`uid`),
  KEY `invitation_code` (`invitation_code`),
  KEY `invitation_email` (`invitation_email`),
  KEY `active_time` (`active_time`),
  KEY `active_ip` (`active_ip`),
  KEY `active_status` (`active_status`)
) ENGINE=MyISAM AUTO_INCREMENT=26 DEFAULT CHARSET=utf8;

CREATE TABLE `[#DB_PREFIX#]invitation_yoyow` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `invitation_id` int(10) DEFAULT NULL COMMENT '邀请记录ID',
  `coin` decimal(16,4) DEFAULT NULL,
  `coin_type` int(1) DEFAULT '0' COMMENT '分币类型：0自己奖励，1一级邀请用户奖励，2二级邀请用户奖励',
  `coin_uid` int(10) DEFAULT NULL COMMENT '分币给的用户',
  `base_uid` int(10) DEFAULT NULL COMMENT '注册用户ID',
  `has_ditribute` int(1) DEFAULT '0' COMMENT '是否已分币：0为分币，1已分币',
  `time` int(10) DEFAULT NULL,
  `second_name` varchar(255) DEFAULT NULL COMMENT '二级用户名',
  `first_name` varchar(255) DEFAULT NULL COMMENT '一级用户名',
  `effective` int(1) DEFAULT '0' COMMENT '是否有效 0无效  1有效',
  `withdrawal_time` int(10) DEFAULT NULL COMMENT '提现时间',
  `order_id` int(10) DEFAULT NULL COMMENT '订单ID',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=1023 DEFAULT CHARSET=utf8;

CREATE TABLE `[#DB_PREFIX#]jobs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `job_name` varchar(64) DEFAULT NULL COMMENT '职位名',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=39 DEFAULT CHARSET=utf8;

CREATE TABLE `[#DB_PREFIX#]lock_position` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(16) NOT NULL COMMENT '用户锁仓唯一编码',
  `uid` int(10) unsigned NOT NULL COMMENT '申请用户id',
  `sum_num` decimal(15,4) NOT NULL DEFAULT '0.0000' COMMENT '锁仓金额',
  `sum_day` smallint(6) NOT NULL DEFAULT '0' COMMENT '锁仓天数',
  `surplus_day` smallint(6) NOT NULL COMMENT '剩余天数',
  `num` int(10) unsigned NOT NULL COMMENT '天数 月数 年数',
  `unit` varchar(20) NOT NULL COMMENT '基础数值 年-月-日',
  `money_rate` float(10,2) NOT NULL DEFAULT '0.00' COMMENT '利率',
  `receive_time` int(10) NOT NULL DEFAULT '0' COMMENT '最后领取时间',
  `create_time` int(10) NOT NULL COMMENT '创建时间',
  `is_ pull` tinyint(3) NOT NULL DEFAULT '0' COMMENT '是否领取当日分红',
  `pull_num` decimal(15,4) NOT NULL DEFAULT '0.0000' COMMENT '分红金额',
  `rotp` decimal(15,4) NOT NULL COMMENT '本金每日返还',
  `rotp_sday` tinyint(3) NOT NULL COMMENT '本金返还剩余天数',
  `rotp_time` int(10) NOT NULL DEFAULT '0' COMMENT '最后返还日期',
  `end_time` int(10) NOT NULL DEFAULT '0' COMMENT '分红截至日期',
  `type` tinyint(3) NOT NULL DEFAULT '1' COMMENT ' 1为限制 2为不限制期限',
  `status` tinyint(3) NOT NULL DEFAULT '0' COMMENT '审核状态 0未审核 -1审核未通过 1进行中 2本金返还中  99已过期  ',
  `storehouse` tinyint(3) NOT NULL DEFAULT '0' COMMENT '是否申请解仓 1申请中 2申请成功 -1申请失败',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4;

CREATE TABLE `[#DB_PREFIX#]lock_position_config` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `num` int(10) unsigned NOT NULL COMMENT '基础数值 100天 1年',
  `unit` varchar(20) NOT NULL COMMENT '单位 年 月 日',
  `money_rate` float(10,2) NOT NULL COMMENT '利率',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=44 DEFAULT CHARSET=utf8mb4;

CREATE TABLE `[#DB_PREFIX#]lock_position_log` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `lid` int(10) unsigned NOT NULL COMMENT '锁仓记录id',
  `uid` int(10) NOT NULL COMMENT '用户id',
  `num` decimal(15,4) NOT NULL COMMENT '领取金额',
  `create_time` int(10) NOT NULL COMMENT '领取时间',
  `type` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '1为分红日志 2为本金返还日志',
  PRIMARY KEY (`id`),
  KEY `lid` (`lid`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=49 DEFAULT CHARSET=utf8mb4;

CREATE TABLE `[#DB_PREFIX#]mail_queue` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `send_to` varchar(255) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `is_error` tinyint(1) NOT NULL DEFAULT '0',
  `error_message` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `is_error` (`is_error`),
  KEY `send_to` (`send_to`)
) ENGINE=MyISAM AUTO_INCREMENT=339 DEFAULT CHARSET=utf8;

CREATE TABLE `[#DB_PREFIX#]nav_menu` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(128) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `type` varchar(16) DEFAULT NULL,
  `type_id` int(11) DEFAULT '0',
  `link` varchar(255) DEFAULT NULL COMMENT '链接',
  `icon` varchar(255) DEFAULT NULL COMMENT '图标',
  `sort` smallint(6) DEFAULT '0' COMMENT '排序',
  PRIMARY KEY (`id`),
  KEY `parent_id` (`link`)
) ENGINE=MyISAM AUTO_INCREMENT=15 DEFAULT CHARSET=utf8;

CREATE TABLE `[#DB_PREFIX#]newrule_invitation` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `invitation_uid` int(11) DEFAULT NULL COMMENT '邀请用户id',
  `regist_uid` int(11) DEFAULT NULL COMMENT '注册用户id',
  `regist_username` varchar(255) DEFAULT NULL COMMENT '注册用户名',
  `user_invitation_reward` decimal(16,4) DEFAULT NULL COMMENT '用户邀请奖励',
  `user_regist_reward` decimal(16,4) DEFAULT NULL COMMENT '用户注册奖励',
  `send_reward_days` int(11) DEFAULT NULL COMMENT '奖励发放天数',
  `send_reward_deadline` int(11) DEFAULT NULL COMMENT '奖励发放截止日期',
  `start_send_reward_date` int(11) DEFAULT NULL COMMENT '开始发放奖励时间',
  `last_send_reward_date` int(11) DEFAULT NULL COMMENT '最后发放奖励时间',
  `type` int(11) DEFAULT NULL COMMENT '类型，1=>注册奖励，2=>邀请奖励，3=>用户升级奖励',
  `status` int(11) DEFAULT NULL COMMENT '状态，1=>待发放，2=>正在发放，3=>已结束',
  `add_time` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `[#DB_PREFIX#]newrule_invitation_reward_log` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `uid` int(11) DEFAULT NULL COMMENT '用户id',
  `reward` decimal(16,4) DEFAULT NULL COMMENT '发放奖励金额',
  `type` int(11) DEFAULT NULL COMMENT '1=>注册奖励，2=>邀请奖励',
  `message` text COMMENT '备注信息',
  `statistical_date` int(11) DEFAULT NULL COMMENT '用于记录统计奖励发放到哪一天',
  `add_time` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

CREATE TABLE `[#DB_PREFIX#]notification` (
  `notification_id` int(11) NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `sender_uid` int(11) DEFAULT NULL COMMENT '发送者ID',
  `recipient_uid` int(11) DEFAULT '0' COMMENT '接收者ID',
  `action_type` int(4) DEFAULT NULL COMMENT '操作类型',
  `model_type` smallint(11) NOT NULL DEFAULT '0',
  `source_id` varchar(16) NOT NULL DEFAULT '0' COMMENT '关联 ID',
  `add_time` int(10) DEFAULT NULL COMMENT '添加时间',
  `read_flag` tinyint(1) DEFAULT '0' COMMENT '阅读状态',
  PRIMARY KEY (`notification_id`),
  KEY `recipient_read_flag` (`recipient_uid`,`read_flag`),
  KEY `sender_uid` (`sender_uid`),
  KEY `model_type` (`model_type`),
  KEY `source_id` (`source_id`),
  KEY `action_type` (`action_type`),
  KEY `add_time` (`add_time`)
) ENGINE=MyISAM AUTO_INCREMENT=2386 DEFAULT CHARSET=utf8 COMMENT='系统通知';

CREATE TABLE `[#DB_PREFIX#]notification_data` (
  `notification_id` int(11) unsigned NOT NULL,
  `data` text,
  PRIMARY KEY (`notification_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='系统通知数据表';

CREATE TABLE `[#DB_PREFIX#]pages` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `url_token` varchar(32) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `keywords` varchar(255) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `contents` text,
  `enabled` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `url_token` (`url_token`),
  KEY `enabled` (`enabled`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `[#DB_PREFIX#]posts_index` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `post_id` int(10) NOT NULL,
  `post_type` varchar(16) NOT NULL DEFAULT '',
  `add_time` int(10) NOT NULL,
  `update_time` int(10) DEFAULT '0',
  `category_id` int(10) DEFAULT '0',
  `is_recommend` tinyint(1) DEFAULT '0',
  `view_count` int(10) DEFAULT '0',
  `anonymous` tinyint(1) DEFAULT '0',
  `popular_value` int(10) DEFAULT '0',
  `uid` int(10) NOT NULL,
  `lock` tinyint(1) DEFAULT '0',
  `agree_count` int(10) DEFAULT '0',
  `answer_count` int(10) DEFAULT '0',
  `set_top` int(1) DEFAULT '0' COMMENT '是否已置顶：0为未置顶，1已置顶',
  `set_top_time` int(10) DEFAULT NULL COMMENT '置顶时间',
  `set_hide_top` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `set_hide_top_time` int(10) unsigned DEFAULT NULL,
  `score` decimal(15,2) NOT NULL DEFAULT '0.00',
  PRIMARY KEY (`id`),
  KEY `post_id` (`post_id`),
  KEY `post_type` (`post_type`),
  KEY `add_time` (`add_time`),
  KEY `update_time` (`update_time`),
  KEY `category_id` (`category_id`),
  KEY `is_recommend` (`is_recommend`),
  KEY `anonymous` (`anonymous`),
  KEY `popular_value` (`popular_value`),
  KEY `uid` (`uid`),
  KEY `lock` (`lock`),
  KEY `agree_count` (`agree_count`),
  KEY `answer_count` (`answer_count`),
  KEY `view_count` (`view_count`)
) ENGINE=MyISAM AUTO_INCREMENT=637 DEFAULT CHARSET=utf8;

CREATE TABLE `[#DB_PREFIX#]question` (
  `question_id` int(11) NOT NULL AUTO_INCREMENT,
  `question_content` varchar(200) CHARACTER SET utf8mb4 NOT NULL,
  `question_detail` text CHARACTER SET utf8mb4,
  `add_time` int(11) NOT NULL COMMENT '添加时间',
  `update_time` int(11) DEFAULT NULL,
  `published_uid` int(11) DEFAULT NULL COMMENT '发布用户UID',
  `answer_count` int(11) NOT NULL DEFAULT '0' COMMENT '回答计数',
  `answer_users` int(11) NOT NULL DEFAULT '0' COMMENT '回答人数',
  `view_count` int(11) NOT NULL DEFAULT '0' COMMENT '浏览次数',
  `focus_count` int(11) NOT NULL DEFAULT '0' COMMENT '关注数',
  `comment_count` int(11) NOT NULL DEFAULT '0' COMMENT '评论数',
  `action_history_id` int(11) NOT NULL DEFAULT '0' COMMENT '动作的记录表的关连id',
  `category_id` int(11) NOT NULL DEFAULT '0' COMMENT '分类 ID',
  `agree_count` int(11) NOT NULL DEFAULT '0' COMMENT '回复赞同数总和',
  `against_count` int(11) NOT NULL DEFAULT '0' COMMENT '回复反对数总和',
  `best_answer` int(11) NOT NULL DEFAULT '0' COMMENT '最佳回复 ID',
  `has_attach` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否存在附件',
  `unverified_modify` text,
  `unverified_modify_count` int(10) NOT NULL DEFAULT '0',
  `ip` bigint(11) DEFAULT NULL,
  `last_answer` int(11) NOT NULL DEFAULT '0' COMMENT '最后回答 ID',
  `popular_value` double NOT NULL DEFAULT '0',
  `popular_value_update` int(10) NOT NULL DEFAULT '0',
  `lock` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否锁定',
  `anonymous` tinyint(1) NOT NULL DEFAULT '0',
  `thanks_count` int(10) NOT NULL DEFAULT '0',
  `question_content_fulltext` text,
  `is_recommend` tinyint(1) NOT NULL DEFAULT '0',
  `weibo_msg_id` bigint(20) DEFAULT NULL,
  `received_email_id` int(10) DEFAULT NULL,
  `chapter_id` int(10) unsigned DEFAULT NULL,
  `sort` tinyint(2) unsigned NOT NULL DEFAULT '0',
  `set_top` int(1) DEFAULT '0' COMMENT '是否已置顶：0为未置顶，1已置顶',
  `set_top_time` int(10) DEFAULT NULL COMMENT '置顶时间',
  `del_flag` int(1) DEFAULT '0' COMMENT '是否删除：0未删除，1已删除',
  `set_hide_top` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `set_hide_top_time` int(10) unsigned DEFAULT NULL,
  `score` decimal(15,2) NOT NULL DEFAULT '0.00',
  `question_disagree_count` int(10) DEFAULT '0' COMMENT '问题反对数',
  `question_agree_count` int(10) DEFAULT '0' COMMENT '问题点赞数',
  `default_spread` smallint(5) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`question_id`),
  KEY `category_id` (`category_id`),
  KEY `update_time` (`update_time`),
  KEY `add_time` (`add_time`),
  KEY `published_uid` (`published_uid`),
  KEY `answer_count` (`answer_count`),
  KEY `agree_count` (`agree_count`),
  KEY `question_content` (`question_content`),
  KEY `lock` (`lock`),
  KEY `thanks_count` (`thanks_count`),
  KEY `anonymous` (`anonymous`),
  KEY `popular_value` (`popular_value`),
  KEY `best_answer` (`best_answer`),
  KEY `popular_value_update` (`popular_value_update`),
  KEY `against_count` (`against_count`),
  KEY `is_recommend` (`is_recommend`),
  KEY `weibo_msg_id` (`weibo_msg_id`),
  KEY `received_email_id` (`received_email_id`),
  KEY `unverified_modify_count` (`unverified_modify_count`),
  KEY `chapter_id` (`chapter_id`),
  KEY `sort` (`sort`),
  FULLTEXT KEY `question_content_fulltext` (`question_content_fulltext`)
) ENGINE=MyISAM AUTO_INCREMENT=176 DEFAULT CHARSET=utf8 COMMENT='问题列表';

CREATE TABLE `[#DB_PREFIX#]question_comments` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `question_id` int(11) DEFAULT '0',
  `uid` int(11) DEFAULT '0',
  `message` text CHARACTER SET utf8mb4,
  `time` int(10) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `question_id` (`question_id`),
  KEY `time` (`time`)
) ENGINE=MyISAM AUTO_INCREMENT=10 DEFAULT CHARSET=utf8;

CREATE TABLE `[#DB_PREFIX#]question_focus` (
  `focus_id` int(11) NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `question_id` int(11) DEFAULT NULL COMMENT '话题ID',
  `uid` int(11) DEFAULT NULL COMMENT '用户UID',
  `add_time` int(10) DEFAULT NULL,
  PRIMARY KEY (`focus_id`),
  KEY `question_id` (`question_id`),
  KEY `question_uid` (`question_id`,`uid`)
) ENGINE=MyISAM AUTO_INCREMENT=493 DEFAULT CHARSET=utf8 COMMENT='问题关注表';

CREATE TABLE `[#DB_PREFIX#]question_handpick_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '自动ID',
  `question_id` int(11) DEFAULT NULL COMMENT '问题id',
  `handpick` varchar(128) DEFAULT NULL COMMENT '精选关键字',
  `add_time` int(10) DEFAULT NULL COMMENT '添加时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='精选问题访问记录表';

CREATE TABLE `[#DB_PREFIX#]question_invite` (
  `question_invite_id` int(11) NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `question_id` int(11) NOT NULL COMMENT '问题ID',
  `sender_uid` int(11) NOT NULL,
  `recipients_uid` int(11) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL COMMENT '受邀Email',
  `add_time` int(10) DEFAULT '0' COMMENT '添加时间',
  `available_time` int(10) DEFAULT '0' COMMENT '生效时间',
  PRIMARY KEY (`question_invite_id`),
  KEY `question_id` (`question_id`),
  KEY `sender_uid` (`sender_uid`),
  KEY `recipients_uid` (`recipients_uid`),
  KEY `add_time` (`add_time`)
) ENGINE=MyISAM AUTO_INCREMENT=164 DEFAULT CHARSET=utf8 COMMENT='邀请问答';

CREATE TABLE `[#DB_PREFIX#]question_thanks` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `uid` int(11) DEFAULT '0',
  `question_id` int(11) DEFAULT '0',
  `user_name` varchar(255) DEFAULT NULL,
  `time` int(10) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `question_id` (`question_id`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;

CREATE TABLE `[#DB_PREFIX#]question_uninterested` (
  `interested_id` int(11) NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `question_id` int(11) DEFAULT NULL COMMENT '话题ID',
  `uid` int(11) DEFAULT NULL COMMENT '用户UID',
  `add_time` int(10) DEFAULT NULL,
  PRIMARY KEY (`interested_id`),
  KEY `uid` (`uid`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COMMENT='问题不感兴趣表';

CREATE TABLE `[#DB_PREFIX#]question_vote` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '自动ID',
  `question_id` int(11) DEFAULT NULL COMMENT '问题id',
  `question_uid` int(11) DEFAULT NULL COMMENT '问题作者id',
  `vote_uid` int(11) DEFAULT NULL COMMENT '赞踩用户ID',
  `add_time` int(10) DEFAULT NULL COMMENT '添加时间',
  `vote_value` tinyint(4) NOT NULL COMMENT '-1反对 1 支持',
  `reputation_factor` int(10) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=99810 DEFAULT CHARSET=utf8;

CREATE TABLE `[#DB_PREFIX#]ranking_list` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user_name` varchar(20) NOT NULL,
  `avatar_file` varchar(155) DEFAULT NULL,
  `ranking` int(11) NOT NULL,
  `invite_num` int(11) NOT NULL DEFAULT '0',
  `yoyow_num` decimal(16,4) NOT NULL DEFAULT '0.0000',
  `add_time` int(10) NOT NULL,
  `uid` int(11) NOT NULL,
  `time` int(10) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_name` (`user_name`),
  UNIQUE KEY `ranking` (`ranking`),
  UNIQUE KEY `uid` (`uid`)
) ENGINE=InnoDB AUTO_INCREMENT=255 DEFAULT CHARSET=utf8mb4;

CREATE TABLE `[#DB_PREFIX#]received_email` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `uid` int(10) NOT NULL,
  `config_id` int(10) NOT NULL,
  `message_id` varchar(255) NOT NULL,
  `date` int(10) NOT NULL,
  `from` varchar(255) NOT NULL,
  `subject` varchar(255) DEFAULT NULL,
  `content` text,
  `question_id` int(11) DEFAULT NULL,
  `ticket_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `uid` (`uid`),
  KEY `config_id` (`config_id`),
  KEY `message_id` (`message_id`),
  KEY `date` (`date`),
  KEY `ticket_id` (`ticket_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='已导入邮件列表';

CREATE TABLE `[#DB_PREFIX#]receiving_email_config` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `protocol` varchar(10) NOT NULL,
  `server` varchar(255) NOT NULL,
  `ssl` tinyint(1) NOT NULL DEFAULT '0',
  `port` smallint(5) DEFAULT NULL,
  `username` varchar(255) NOT NULL DEFAULT '',
  `password` varchar(255) NOT NULL DEFAULT '',
  `uid` int(10) NOT NULL,
  `access_key` varchar(32) NOT NULL,
  `has_attach` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `uid` (`uid`),
  KEY `server` (`server`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='邮件账号列表';

CREATE TABLE `[#DB_PREFIX#]redirect` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `item_id` int(11) DEFAULT '0',
  `target_id` int(11) DEFAULT '0',
  `time` int(10) DEFAULT NULL,
  `uid` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `item_id` (`item_id`),
  KEY `uid` (`uid`)
) ENGINE=MyISAM AUTO_INCREMENT=18 DEFAULT CHARSET=utf8;

CREATE TABLE `[#DB_PREFIX#]register_problem` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `right_key` varchar(20) NOT NULL,
  `status` tinyint(4) NOT NULL DEFAULT '1',
  `add_time` int(10) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `title` (`title`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4;

CREATE TABLE `[#DB_PREFIX#]register_reward_record` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `uid` int(11) DEFAULT NULL COMMENT '用户id',
  `remark` varchar(500) DEFAULT NULL COMMENT '说明备注',
  `coin` decimal(16,4) DEFAULT NULL COMMENT '分配币数',
  `time` int(10) DEFAULT NULL,
  `type` int(1) DEFAULT NULL COMMENT '0 用户威望奖励  1，注册奖励  2绑定奖励',
  `status` int(1) DEFAULT NULL COMMENT '0,发放成功  1，发放失败  2，未提现',
  `order_id` int(10) DEFAULT NULL COMMENT '订单ID',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=44 DEFAULT CHARSET=utf8;

CREATE TABLE `[#DB_PREFIX#]register_yoyow_record` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `uid` int(11) DEFAULT '0' COMMENT '用户id',
  `operate` char(1) DEFAULT NULL COMMENT '操作 1:用户注册 2:用户绑定yoyow账号',
  `coin` decimal(16,4) DEFAULT NULL COMMENT '获得币数量',
  `ope_time` int(10) DEFAULT NULL COMMENT '操作时间',
  `status` char(1) CHARACTER SET utf8mb4 DEFAULT NULL COMMENT '状态(0:未发放，1:已发放)',
  PRIMARY KEY (`id`),
  KEY `uid` (`uid`)
) ENGINE=MyISAM AUTO_INCREMENT=109 DEFAULT CHARSET=utf8 COMMENT='用户注册绑定yoyow操作记录表';

CREATE TABLE `[#DB_PREFIX#]related_links` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `uid` int(10) NOT NULL,
  `item_type` varchar(32) NOT NULL,
  `item_id` int(10) NOT NULL,
  `link` varchar(255) NOT NULL,
  `add_time` int(10) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `uid` (`uid`),
  KEY `item_type` (`item_type`),
  KEY `item_id` (`item_id`),
  KEY `add_time` (`add_time`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `[#DB_PREFIX#]related_topic` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `topic_id` int(11) DEFAULT '0' COMMENT '话题 ID',
  `related_id` int(11) DEFAULT '0' COMMENT '相关话题 ID',
  PRIMARY KEY (`id`),
  KEY `topic_id` (`topic_id`),
  KEY `related_id` (`related_id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

CREATE TABLE `[#DB_PREFIX#]report` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) DEFAULT '0' COMMENT '举报用户id',
  `type` varchar(50) DEFAULT NULL COMMENT '类别',
  `target_id` int(11) DEFAULT '0' COMMENT 'ID',
  `reason` varchar(255) DEFAULT NULL COMMENT '举报理由',
  `url` varchar(255) DEFAULT NULL,
  `add_time` int(11) DEFAULT '0' COMMENT '举报时间',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否处理',
  PRIMARY KEY (`id`),
  KEY `add_time` (`add_time`),
  KEY `status` (`status`)
) ENGINE=MyISAM AUTO_INCREMENT=14 DEFAULT CHARSET=utf8;

CREATE TABLE `[#DB_PREFIX#]reputation_category` (
  `auto_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `uid` int(10) DEFAULT '0',
  `category_id` smallint(4) DEFAULT '0',
  `update_time` int(10) DEFAULT '0',
  `reputation` int(10) DEFAULT '0',
  `thanks_count` int(10) DEFAULT '0',
  `agree_count` int(10) DEFAULT '0',
  `question_count` int(10) DEFAULT '0',
  PRIMARY KEY (`auto_id`),
  UNIQUE KEY `uid_category_id` (`uid`,`category_id`)
) ENGINE=MyISAM AUTO_INCREMENT=36 DEFAULT CHARSET=utf8;

CREATE TABLE `[#DB_PREFIX#]reputation_topic` (
  `auto_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `uid` int(11) DEFAULT '0',
  `topic_id` int(11) DEFAULT '0' COMMENT '话题ID',
  `topic_count` int(10) DEFAULT '0' COMMENT '威望问题话题计数',
  `update_time` int(10) DEFAULT NULL COMMENT '更新时间',
  `agree_count` int(10) DEFAULT '0' COMMENT '赞成',
  `thanks_count` int(10) DEFAULT '0' COMMENT '感谢',
  `reputation` int(10) DEFAULT '0',
  PRIMARY KEY (`auto_id`),
  KEY `topic_count` (`topic_count`),
  KEY `uid` (`uid`),
  KEY `topic_id` (`topic_id`),
  KEY `reputation` (`reputation`)
) ENGINE=MyISAM AUTO_INCREMENT=33 DEFAULT CHARSET=utf8;

CREATE TABLE `[#DB_PREFIX#]reputation_users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) DEFAULT NULL COMMENT '用户id',
  `reputation` int(10) DEFAULT NULL COMMENT '用户威望',
  `status` char(1) CHARACTER SET utf8mb4 DEFAULT '0' COMMENT '状态: 0:未发放 1:已发放',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

CREATE TABLE `[#DB_PREFIX#]school` (
  `school_id` int(11) NOT NULL COMMENT '自增ID',
  `school_type` tinyint(4) DEFAULT NULL COMMENT '学校类型ID',
  `school_code` int(11) DEFAULT NULL COMMENT '学校编码',
  `school_name` varchar(64) DEFAULT NULL COMMENT '学校名称',
  `area_code` int(11) DEFAULT NULL COMMENT '地区代码',
  PRIMARY KEY (`school_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='学校';

CREATE TABLE `[#DB_PREFIX#]search_cache` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `hash` varchar(32) NOT NULL,
  `data` mediumtext NOT NULL,
  `time` int(10) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `hash` (`hash`),
  KEY `time` (`time`)
) ENGINE=MyISAM AUTO_INCREMENT=1487 DEFAULT CHARSET=utf8;

CREATE TABLE `[#DB_PREFIX#]sessions` (
  `id` varchar(32) NOT NULL,
  `modified` int(10) NOT NULL,
  `data` text NOT NULL,
  `lifetime` int(10) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `modified` (`modified`),
  KEY `lifetime` (`lifetime`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `[#DB_PREFIX#]share_frist_record` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `aid` int(10) NOT NULL DEFAULT '0',
  `type` tinyint(1) NOT NULL DEFAULT '0' COMMENT '1分享问题、2文章、3回答',
  `addtime` int(10) DEFAULT '0',
  `uid` int(10) DEFAULT '0',
  `ip` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=47 DEFAULT CHARSET=utf8mb4;

CREATE TABLE `[#DB_PREFIX#]share_log` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `uid` int(10) NOT NULL DEFAULT '0' COMMENT '分享者id',
  `aid` int(10) NOT NULL DEFAULT '0' COMMENT '分享问题、文章、回答id',
  `type` tinyint(1) NOT NULL DEFAULT '0' COMMENT '1分享问题、2文章、3回答',
  `yoyow_coin` decimal(10,4) NOT NULL DEFAULT '0.0000' COMMENT '获得的奖励',
  `addtime` int(10) NOT NULL DEFAULT '0',
  `remarks` varchar(255) NOT NULL,
  `ip` varchar(255) NOT NULL COMMENT 'ip',
  `is_coin` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0未分币1已分币2分币失败',
  `coin_time` int(10) NOT NULL DEFAULT '0' COMMENT '分币操作时间',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0原始状态1未提现2已提现',
  `withdrawal_time` int(10) NOT NULL DEFAULT '0' COMMENT '提现时间',
  PRIMARY KEY (`id`),
  KEY `uid` (`uid`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=163 DEFAULT CHARSET=utf8mb4;

CREATE TABLE `[#DB_PREFIX#]site_announce` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `content` text COMMENT '内容',
  `status` varchar(255) NOT NULL COMMENT '0：启用，1：删除',
  `time` int(10) DEFAULT NULL,
  `title` varchar(500) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=9 DEFAULT CHARSET=utf8;

CREATE TABLE `[#DB_PREFIX#]site_announce_read_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) DEFAULT NULL COMMENT '用户id',
  `site_announce_id` int(11) DEFAULT NULL COMMENT '公告id',
  `add_time` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='公告浏览记录表';

CREATE TABLE `[#DB_PREFIX#]site_link` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `title` varchar(500) DEFAULT NULL COMMENT '标题',
  `linkurl` varchar(500) NOT NULL COMMENT '链接地址',
  `time` int(10) DEFAULT NULL,
  `sort` int(10) DEFAULT '0' COMMENT '序号',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=30 DEFAULT CHARSET=utf8;

CREATE TABLE `[#DB_PREFIX#]system_setting` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'id',
  `varname` varchar(255) NOT NULL COMMENT '字段名',
  `value` text COMMENT '变量值',
  PRIMARY KEY (`id`),
  UNIQUE KEY `varname` (`varname`)
) ENGINE=MyISAM AUTO_INCREMENT=230 DEFAULT CHARSET=utf8 COMMENT='系统设置';

CREATE TABLE `[#DB_PREFIX#]task_statistics` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `task_id` int(11) DEFAULT NULL COMMENT '任务ID',
  `success_num` int(11) DEFAULT NULL COMMENT '成功账户数量',
  `fail_num` int(11) DEFAULT NULL COMMENT '失败账户数量',
  `no_account` int(11) DEFAULT NULL COMMENT '没有绑定yoyow账号数量',
  `no_integral` int(11) DEFAULT NULL COMMENT '没有积分账户数量',
  PRIMARY KEY (`id`),
  KEY `task_id` (`task_id`)
) ENGINE=InnoDB AUTO_INCREMENT=172 DEFAULT CHARSET=utf8;

CREATE TABLE `[#DB_PREFIX#]topic` (
  `topic_id` int(11) NOT NULL AUTO_INCREMENT COMMENT '话题id',
  `topic_title` varchar(64) DEFAULT NULL COMMENT '话题标题',
  `add_time` int(10) DEFAULT NULL COMMENT '添加时间',
  `discuss_count` int(11) DEFAULT '0' COMMENT '讨论计数',
  `topic_description` text COMMENT '话题描述',
  `topic_pic` varchar(255) DEFAULT NULL COMMENT '话题图片',
  `topic_lock` tinyint(2) NOT NULL DEFAULT '0' COMMENT '话题是否锁定 1 锁定 0 未锁定',
  `focus_count` int(11) DEFAULT '0' COMMENT '关注计数',
  `user_related` tinyint(1) DEFAULT '0' COMMENT '是否被用户关联',
  `url_token` varchar(32) DEFAULT NULL,
  `merged_id` int(11) DEFAULT '0',
  `seo_title` varchar(255) DEFAULT NULL,
  `parent_id` int(10) DEFAULT '0',
  `is_parent` tinyint(1) DEFAULT '0',
  `discuss_count_last_week` int(10) DEFAULT '0',
  `discuss_count_last_month` int(10) DEFAULT '0',
  `discuss_count_update` int(10) DEFAULT '0',
  `type` int(11) NOT NULL DEFAULT '1' COMMENT '类别 1=>普通，2=>币种',
  `is_recommend` int(11) NOT NULL DEFAULT '0' COMMENT '是否推荐，1=>推荐',
  PRIMARY KEY (`topic_id`),
  UNIQUE KEY `topic_title` (`topic_title`),
  KEY `url_token` (`url_token`),
  KEY `merged_id` (`merged_id`),
  KEY `discuss_count` (`discuss_count`),
  KEY `add_time` (`add_time`),
  KEY `user_related` (`user_related`),
  KEY `focus_count` (`focus_count`),
  KEY `topic_lock` (`topic_lock`),
  KEY `parent_id` (`parent_id`),
  KEY `is_parent` (`is_parent`),
  KEY `discuss_count_last_week` (`discuss_count_last_week`),
  KEY `discuss_count_last_month` (`discuss_count_last_month`),
  KEY `discuss_count_update` (`discuss_count_update`)
) ENGINE=MyISAM AUTO_INCREMENT=52 DEFAULT CHARSET=utf8 COMMENT='话题';

CREATE TABLE `[#DB_PREFIX#]topic_focus` (
  `focus_id` int(11) NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `topic_id` int(11) DEFAULT NULL COMMENT '话题ID',
  `uid` int(11) DEFAULT NULL COMMENT '用户UID',
  `add_time` int(10) DEFAULT NULL COMMENT '添加时间',
  PRIMARY KEY (`focus_id`),
  KEY `uid` (`uid`),
  KEY `topic_id` (`topic_id`),
  KEY `topic_uid` (`topic_id`,`uid`)
) ENGINE=MyISAM AUTO_INCREMENT=130 DEFAULT CHARSET=utf8 COMMENT='话题关注表';

CREATE TABLE `[#DB_PREFIX#]topic_merge` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `source_id` int(11) NOT NULL DEFAULT '0',
  `target_id` int(11) NOT NULL DEFAULT '0',
  `uid` int(11) DEFAULT '0',
  `time` int(10) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `source_id` (`source_id`),
  KEY `target_id` (`target_id`),
  KEY `uid` (`uid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `[#DB_PREFIX#]topic_relation` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '自增 ID',
  `topic_id` int(11) DEFAULT '0' COMMENT '话题id',
  `item_id` int(11) DEFAULT '0',
  `add_time` int(10) DEFAULT '0' COMMENT '添加时间',
  `uid` int(11) DEFAULT '0' COMMENT '用户ID',
  `type` varchar(16) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `topic_id` (`topic_id`),
  KEY `uid` (`uid`),
  KEY `type` (`type`),
  KEY `item_id` (`item_id`)
) ENGINE=MyISAM AUTO_INCREMENT=234 DEFAULT CHARSET=utf8;

CREATE TABLE `[#DB_PREFIX#]user_action_history` (
  `history_id` int(11) NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `uid` int(11) NOT NULL COMMENT '用户id',
  `associate_type` tinyint(1) DEFAULT NULL COMMENT '关联类型: 1 问题 2 回答 3 评论 4 话题',
  `associate_action` smallint(3) DEFAULT NULL COMMENT '操作类型',
  `associate_id` int(11) DEFAULT NULL COMMENT '关联ID',
  `add_time` int(10) DEFAULT NULL COMMENT '添加时间',
  `associate_attached` int(11) DEFAULT NULL,
  `anonymous` tinyint(1) DEFAULT '0' COMMENT '是否匿名',
  `fold_status` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`history_id`),
  KEY `add_time` (`add_time`),
  KEY `uid` (`uid`),
  KEY `associate_id` (`associate_id`),
  KEY `anonymous` (`anonymous`),
  KEY `fold_status` (`fold_status`),
  KEY `associate` (`associate_type`,`associate_action`),
  KEY `associate_attached` (`associate_attached`),
  KEY `associate_with_id` (`associate_id`,`associate_type`,`associate_action`),
  KEY `associate_with_uid` (`uid`,`associate_type`,`associate_action`)
) ENGINE=MyISAM AUTO_INCREMENT=2670 DEFAULT CHARSET=utf8 COMMENT='用户操作记录';

CREATE TABLE `[#DB_PREFIX#]user_action_history_data` (
  `history_id` int(11) unsigned NOT NULL,
  `associate_content` text,
  `associate_attached` text,
  `addon_data` text COMMENT '附加数据',
  PRIMARY KEY (`history_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `[#DB_PREFIX#]user_action_history_fresh` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `history_id` int(11) NOT NULL,
  `associate_id` int(11) NOT NULL,
  `associate_type` tinyint(1) NOT NULL,
  `associate_action` smallint(3) NOT NULL,
  `add_time` int(10) NOT NULL DEFAULT '0',
  `uid` int(10) NOT NULL DEFAULT '0',
  `anonymous` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `associate` (`associate_type`,`associate_action`),
  KEY `add_time` (`add_time`),
  KEY `uid` (`uid`),
  KEY `history_id` (`history_id`),
  KEY `associate_with_id` (`id`,`associate_type`,`associate_action`),
  KEY `associate_with_uid` (`uid`,`associate_type`,`associate_action`),
  KEY `anonymous` (`anonymous`)
) ENGINE=MyISAM AUTO_INCREMENT=2670 DEFAULT CHARSET=utf8;

CREATE TABLE `[#DB_PREFIX#]user_follow` (
  `follow_id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `fans_uid` int(11) DEFAULT NULL COMMENT '关注人的UID',
  `friend_uid` int(11) DEFAULT NULL COMMENT '被关注人的uid',
  `add_time` int(10) DEFAULT NULL COMMENT '添加时间',
  PRIMARY KEY (`follow_id`),
  KEY `fans_uid` (`fans_uid`),
  KEY `friend_uid` (`friend_uid`),
  KEY `user_follow` (`fans_uid`,`friend_uid`)
) ENGINE=MyISAM AUTO_INCREMENT=412 DEFAULT CHARSET=utf8 COMMENT='用户关注表';

CREATE TABLE `[#DB_PREFIX#]user_yoyow_coin` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) DEFAULT NULL COMMENT '会员id',
  `coin` decimal(16,4) DEFAULT NULL,
  `origin` varchar(16) DEFAULT NULL COMMENT '常量，获取/使用yoyow币的途径(ASSIGN方案1手动分配,TRIGGER方案2自动触发分配)',
  `add_time` int(11) DEFAULT NULL COMMENT '获得/使用yoyow币的时间',
  `act_strat_time` int(10) DEFAULT NULL COMMENT '积分活动的开始时间',
  `act_end_time` int(10) DEFAULT NULL COMMENT '积分活动的结束时间',
  `remark` varchar(500) DEFAULT NULL COMMENT '备注内容：在2018年1月1日到2018年1月7日 积分活动 获得yoyow币x个',
  `task_id` int(11) DEFAULT NULL COMMENT '任务ID',
  `distribute_result` int(1) DEFAULT '1' COMMENT '执行结果(0失败，1成功)',
  `inteface_message` varchar(1024) DEFAULT NULL COMMENT '调用接口返回数据',
  `type` int(1) DEFAULT '0' COMMENT '分币类型：0普通分币，1提成分币',
  `order_id` int(10) DEFAULT NULL COMMENT '订单ID',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2003 DEFAULT CHARSET=utf8;

CREATE TABLE `[#DB_PREFIX#]users` (
  `uid` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '用户的 UID',
  `user_name` varchar(255) DEFAULT NULL COMMENT '用户名',
  `email` varchar(255) DEFAULT NULL COMMENT 'EMAIL',
  `mobile` varchar(16) DEFAULT NULL COMMENT '用户手机',
  `password` varchar(32) DEFAULT NULL COMMENT '用户密码',
  `salt` varchar(16) DEFAULT NULL COMMENT '用户附加混淆码',
  `avatar_file` varchar(128) DEFAULT NULL COMMENT '头像文件',
  `sex` tinyint(1) DEFAULT NULL COMMENT '性别',
  `birthday` int(10) DEFAULT NULL COMMENT '生日',
  `province` varchar(64) DEFAULT NULL COMMENT '省',
  `city` varchar(64) DEFAULT NULL COMMENT '市',
  `job_id` int(10) DEFAULT '0' COMMENT '职业ID',
  `reg_time` int(10) DEFAULT NULL COMMENT '注册时间',
  `reg_ip` bigint(12) DEFAULT NULL COMMENT '注册IP',
  `last_login` int(10) DEFAULT '0' COMMENT '最后登录时间',
  `last_ip` bigint(12) DEFAULT NULL COMMENT '最后登录 IP',
  `online_time` int(10) DEFAULT '0' COMMENT '在线时间',
  `last_active` int(10) DEFAULT NULL COMMENT '最后活跃时间',
  `notification_unread` int(11) NOT NULL DEFAULT '0' COMMENT '未读系统通知',
  `inbox_unread` int(11) NOT NULL DEFAULT '0' COMMENT '未读短信息',
  `inbox_recv` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0-所有人可以发给我,1-我关注的人',
  `fans_count` int(10) NOT NULL DEFAULT '0' COMMENT '粉丝数',
  `friend_count` int(10) NOT NULL DEFAULT '0' COMMENT '观众数',
  `invite_count` int(10) NOT NULL DEFAULT '0' COMMENT '邀请我回答数量',
  `article_count` int(10) NOT NULL DEFAULT '0' COMMENT '文章数量',
  `question_count` int(10) NOT NULL DEFAULT '0' COMMENT '问题数量',
  `answer_count` int(10) NOT NULL DEFAULT '0' COMMENT '回答数量',
  `topic_focus_count` int(10) NOT NULL DEFAULT '0' COMMENT '关注话题数量',
  `invitation_available` int(10) NOT NULL DEFAULT '0' COMMENT '邀请数量',
  `group_id` int(10) DEFAULT '0' COMMENT '用户组',
  `reputation_group` int(10) DEFAULT '0' COMMENT '威望对应组',
  `forbidden` tinyint(1) DEFAULT '0' COMMENT '是否禁止用户',
  `valid_email` tinyint(1) DEFAULT '0' COMMENT '邮箱验证',
  `is_first_login` tinyint(1) DEFAULT '1' COMMENT '首次登录标记',
  `agree_count` int(10) DEFAULT '0' COMMENT '赞同数量',
  `thanks_count` int(10) DEFAULT '0' COMMENT '感谢数量',
  `views_count` int(10) DEFAULT '0' COMMENT '个人主页查看数量',
  `reputation` int(10) DEFAULT '0' COMMENT '威望',
  `reputation_update_time` int(10) DEFAULT '0' COMMENT '威望更新',
  `weibo_visit` tinyint(1) DEFAULT '1' COMMENT '微博允许访问',
  `integral` int(10) DEFAULT '0',
  `draft_count` int(10) DEFAULT NULL,
  `common_email` varchar(255) DEFAULT NULL COMMENT '常用邮箱',
  `url_token` varchar(32) DEFAULT NULL COMMENT '个性网址',
  `url_token_update` int(10) DEFAULT '0',
  `verified` varchar(32) DEFAULT NULL,
  `default_timezone` varchar(32) DEFAULT NULL,
  `email_settings` varchar(255) DEFAULT '',
  `weixin_settings` varchar(255) DEFAULT '',
  `recent_topics` text,
  `login_continuous` int(10) DEFAULT '0' COMMENT '连续登录天数',
  `yoyow_coin` int(11) DEFAULT NULL COMMENT 'yoyow币余额（类似积分）',
  `praise_no_weight` decimal(5,2) DEFAULT '0.00' COMMENT '踩赞权重',
  `weight_balance` decimal(5,2) DEFAULT '0.00' COMMENT '剩余权重',
  `score` double(15,2) NOT NULL DEFAULT '0.00' COMMENT '用户发表文章得分数，用于专栏排序',
  `reputation_extend` int(10) DEFAULT '0' COMMENT '威望拓展字段',
  `whether_decrease_weight` int(1) DEFAULT '1' COMMENT '是否扣减权重  1 是  0否',
  `praise_weight` decimal(5,2) DEFAULT '0.00' COMMENT '权重拓展字段',
  `level` int(11) NOT NULL DEFAULT '1' COMMENT '用户等级 1=>普通，2=>高级',
  `type` int(11) NOT NULL DEFAULT '1' COMMENT '类别 1=>普通，2=>大咖，3=>项目圈',
  `user_big_avatar` varchar(255) NOT NULL COMMENT '大咖头像',
  `big_avatar_file` varchar(255) NOT NULL COMMENT '大咖头像',
  PRIMARY KEY (`uid`),
  KEY `user_name` (`user_name`),
  KEY `email` (`email`),
  KEY `reputation` (`reputation`),
  KEY `reputation_update_time` (`reputation_update_time`),
  KEY `group_id` (`group_id`),
  KEY `agree_count` (`agree_count`),
  KEY `thanks_count` (`thanks_count`),
  KEY `forbidden` (`forbidden`),
  KEY `valid_email` (`valid_email`),
  KEY `last_active` (`last_active`),
  KEY `integral` (`integral`),
  KEY `url_token` (`url_token`),
  KEY `verified` (`verified`),
  KEY `answer_count` (`answer_count`)
) ENGINE=MyISAM AUTO_INCREMENT=1909 DEFAULT CHARSET=utf8;

CREATE TABLE `[#DB_PREFIX#]users_attrib` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `uid` int(11) DEFAULT NULL COMMENT '用户UID',
  `introduction` varchar(255) DEFAULT NULL COMMENT '个人简介',
  `signature` varchar(255) DEFAULT NULL COMMENT '个人签名',
  `qq` bigint(15) DEFAULT NULL,
  `homepage` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `uid` (`uid`)
) ENGINE=MyISAM AUTO_INCREMENT=173 DEFAULT CHARSET=utf8 COMMENT='用户附加属性表';

CREATE TABLE `[#DB_PREFIX#]users_facebook` (
  `id` bigint(20) unsigned NOT NULL,
  `uid` int(11) unsigned NOT NULL,
  `name` varchar(128) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `link` varchar(255) DEFAULT NULL,
  `gender` varchar(8) DEFAULT NULL,
  `locale` varchar(16) DEFAULT NULL,
  `timezone` tinyint(3) DEFAULT NULL,
  `picture` varchar(255) DEFAULT NULL,
  `access_token` varchar(255) DEFAULT NULL,
  `expires_time` int(10) unsigned NOT NULL DEFAULT '0',
  `add_time` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uid` (`uid`),
  KEY `access_token` (`access_token`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `[#DB_PREFIX#]users_google` (
  `id` varchar(64) NOT NULL,
  `uid` int(11) unsigned NOT NULL,
  `name` varchar(128) DEFAULT NULL,
  `locale` varchar(16) DEFAULT NULL,
  `picture` varchar(255) DEFAULT NULL,
  `gender` varchar(8) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `link` varchar(255) DEFAULT NULL,
  `add_time` int(10) unsigned NOT NULL DEFAULT '0',
  `access_token` varchar(128) DEFAULT NULL,
  `refresh_token` varchar(128) DEFAULT NULL,
  `expires_time` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uid` (`uid`),
  KEY `access_token` (`access_token`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `[#DB_PREFIX#]users_group` (
  `group_id` int(11) NOT NULL AUTO_INCREMENT,
  `type` tinyint(3) DEFAULT '0' COMMENT '0-会员组 1-系统组',
  `custom` tinyint(1) DEFAULT '0' COMMENT '是否自定义',
  `group_name` varchar(50) NOT NULL,
  `reputation_lower` int(11) DEFAULT '0',
  `reputation_higer` int(11) DEFAULT '0',
  `reputation_factor` float DEFAULT '0' COMMENT '威望系数',
  `permission` text COMMENT '权限设置',
  `reputation_praise` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '点赞能量系数',
  PRIMARY KEY (`group_id`),
  KEY `type` (`type`),
  KEY `custom` (`custom`)
) ENGINE=MyISAM AUTO_INCREMENT=100 DEFAULT CHARSET=utf8 COMMENT='用户组';

CREATE TABLE `[#DB_PREFIX#]users_invitation_code` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `uid` int(10) DEFAULT NULL COMMENT '用户id',
  `invitation_code` varchar(20) NOT NULL COMMENT '邀请码',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=97 DEFAULT CHARSET=utf8;

CREATE TABLE `[#DB_PREFIX#]users_notification_setting` (
  `notice_setting_id` int(11) NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `uid` int(11) NOT NULL,
  `data` text COMMENT '设置数据',
  PRIMARY KEY (`notice_setting_id`),
  KEY `uid` (`uid`)
) ENGINE=MyISAM AUTO_INCREMENT=173 DEFAULT CHARSET=utf8 COMMENT='通知设定';

CREATE TABLE `[#DB_PREFIX#]users_online` (
  `uid` int(11) NOT NULL COMMENT '用户 ID',
  `last_active` int(11) DEFAULT '0' COMMENT '上次活动时间',
  `ip` bigint(12) DEFAULT '0' COMMENT '客户端ip',
  `active_url` varchar(255) DEFAULT NULL COMMENT '停留页面',
  `user_agent` varchar(255) DEFAULT NULL COMMENT '用户客户端信息',
  KEY `uid` (`uid`),
  KEY `last_active` (`last_active`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='在线用户列表';

CREATE TABLE `[#DB_PREFIX#]users_qq` (
  `id` bigint(11) unsigned NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL COMMENT '用户在本地的UID',
  `nickname` varchar(64) DEFAULT NULL,
  `openid` varchar(128) DEFAULT '',
  `gender` varchar(8) DEFAULT NULL,
  `add_time` int(10) DEFAULT NULL COMMENT '添加时间',
  `access_token` varchar(64) DEFAULT NULL,
  `refresh_token` varchar(64) DEFAULT NULL,
  `expires_time` int(10) DEFAULT NULL,
  `figureurl` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `uid` (`uid`),
  KEY `add_time` (`add_time`),
  KEY `access_token` (`access_token`),
  KEY `openid` (`openid`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

CREATE TABLE `[#DB_PREFIX#]users_sina` (
  `id` bigint(11) NOT NULL COMMENT '新浪用户 ID',
  `uid` int(11) NOT NULL COMMENT '用户在本地的UID',
  `name` varchar(64) DEFAULT NULL COMMENT '微博昵称',
  `location` varchar(255) DEFAULT NULL COMMENT '地址',
  `description` text COMMENT '个人描述',
  `url` varchar(255) DEFAULT NULL COMMENT '用户博客地址',
  `profile_image_url` varchar(255) DEFAULT NULL COMMENT 'Sina 自定义头像地址',
  `gender` varchar(8) DEFAULT NULL,
  `add_time` int(10) DEFAULT NULL COMMENT '添加时间',
  `expires_time` int(10) DEFAULT '0' COMMENT '过期时间',
  `access_token` varchar(64) DEFAULT NULL,
  `last_msg_id` bigint(20) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uid` (`uid`),
  KEY `access_token` (`access_token`),
  KEY `last_msg_id` (`last_msg_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `[#DB_PREFIX#]users_twitter` (
  `id` bigint(20) unsigned NOT NULL,
  `uid` int(11) unsigned NOT NULL,
  `name` varchar(128) DEFAULT NULL,
  `screen_name` varchar(128) DEFAULT NULL,
  `location` varchar(64) DEFAULT NULL,
  `time_zone` varchar(64) DEFAULT NULL,
  `lang` varchar(16) DEFAULT NULL,
  `profile_image_url` varchar(255) DEFAULT NULL,
  `add_time` int(10) unsigned NOT NULL DEFAULT '0',
  `access_token` varchar(255) NOT NULL DEFAULT 'a:2:{s:11:"oauth_token";s:0:"";s:18:"oauth_token_secret";s:0:"";}',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uid` (`uid`),
  KEY `access_token` (`access_token`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `[#DB_PREFIX#]users_ucenter` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `uid` int(11) DEFAULT '0',
  `uc_uid` int(11) DEFAULT '0',
  `username` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `uid` (`uid`),
  KEY `uc_uid` (`uc_uid`),
  KEY `email` (`email`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

CREATE TABLE `[#DB_PREFIX#]users_weixin` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `uid` int(10) NOT NULL,
  `openid` varchar(255) NOT NULL,
  `expires_in` int(10) DEFAULT NULL,
  `access_token` varchar(255) DEFAULT NULL,
  `refresh_token` varchar(255) DEFAULT NULL,
  `scope` varchar(64) DEFAULT NULL,
  `headimgurl` varchar(255) DEFAULT NULL,
  `nickname` varchar(64) DEFAULT NULL,
  `sex` tinyint(1) DEFAULT '0',
  `province` varchar(32) DEFAULT NULL,
  `city` varchar(32) DEFAULT NULL,
  `country` varchar(32) DEFAULT NULL,
  `add_time` int(10) NOT NULL,
  `latitude` float DEFAULT NULL,
  `longitude` float DEFAULT NULL,
  `location_update` int(10) DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uid` (`uid`),
  KEY `openid` (`openid`),
  KEY `expires_in` (`expires_in`),
  KEY `scope` (`scope`),
  KEY `sex` (`sex`),
  KEY `province` (`province`),
  KEY `city` (`city`),
  KEY `country` (`country`),
  KEY `add_time` (`add_time`),
  KEY `latitude` (`latitude`,`longitude`),
  KEY `location_update` (`location_update`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `[#DB_PREFIX#]users_yoyow` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) DEFAULT NULL,
  `yoyow` varchar(50) DEFAULT NULL,
  `bindtime` int(10) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=105 DEFAULT CHARSET=utf8mb4;

CREATE TABLE `[#DB_PREFIX#]verify_apply` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL,
  `reason` varchar(255) NOT NULL,
  `attach` varchar(255) DEFAULT NULL,
  `time` int(10) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `data` text,
  `status` tinyint(1) DEFAULT '0',
  `type` varchar(16) DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `uid` (`uid`),
  KEY `time` (`time`),
  KEY `name` (`name`,`status`),
  KEY `type` (`type`)
) ENGINE=MyISAM AUTO_INCREMENT=9 DEFAULT CHARSET=utf8;

CREATE TABLE `[#DB_PREFIX#]weibo_msg` (
  `id` bigint(20) NOT NULL,
  `created_at` int(10) NOT NULL,
  `msg_author_uid` bigint(20) NOT NULL,
  `text` varchar(255) NOT NULL,
  `access_key` varchar(32) NOT NULL,
  `has_attach` tinyint(1) NOT NULL DEFAULT '0',
  `uid` int(10) NOT NULL,
  `weibo_uid` bigint(20) NOT NULL,
  `question_id` int(11) DEFAULT NULL,
  `ticket_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `created_at` (`created_at`),
  KEY `uid` (`uid`),
  KEY `weibo_uid` (`weibo_uid`),
  KEY `question_id` (`question_id`),
  KEY `ticket_id` (`ticket_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='新浪微博消息列表';

CREATE TABLE `[#DB_PREFIX#]weixin_accounts` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `weixin_mp_token` varchar(255) NOT NULL,
  `weixin_account_role` varchar(20) DEFAULT 'base',
  `weixin_app_id` varchar(255) DEFAULT '',
  `weixin_app_secret` varchar(255) DEFAULT '',
  `weixin_mp_menu` text,
  `weixin_subscribe_message_key` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT '',
  `weixin_no_result_message_key` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT '',
  `weixin_encoding_aes_key` varchar(43) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `weixin_mp_token` (`weixin_mp_token`),
  KEY `weixin_account_role` (`weixin_account_role`),
  KEY `weixin_app_id` (`weixin_app_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='微信多账号设置';

CREATE TABLE `[#DB_PREFIX#]weixin_login` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `token` int(10) NOT NULL,
  `uid` int(10) DEFAULT NULL,
  `session_id` varchar(32) NOT NULL,
  `expire` int(10) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `session_id` (`session_id`),
  KEY `token` (`token`),
  KEY `expire` (`expire`)
) ENGINE=MyISAM AUTO_INCREMENT=109 DEFAULT CHARSET=utf8;

CREATE TABLE `[#DB_PREFIX#]weixin_message` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `weixin_id` varchar(32) NOT NULL,
  `content` varchar(255) NOT NULL,
  `action` text,
  `time` int(10) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `weixin_id` (`weixin_id`),
  KEY `time` (`time`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `[#DB_PREFIX#]weixin_msg` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `msg_id` bigint(20) NOT NULL,
  `group_name` varchar(255) NOT NULL DEFAULT '未分组',
  `status` varchar(15) NOT NULL DEFAULT 'unsent',
  `error_num` int(10) DEFAULT NULL,
  `main_msg` text,
  `articles_info` text,
  `questions_info` text,
  `create_time` int(10) NOT NULL,
  `filter_count` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `msg_id` (`msg_id`),
  KEY `group_name` (`group_name`),
  KEY `status` (`status`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='微信群发列表';

CREATE TABLE `[#DB_PREFIX#]weixin_qr_code` (
  `scene_id` mediumint(5) NOT NULL AUTO_INCREMENT,
  `ticket` varchar(255) DEFAULT NULL,
  `description` varchar(255) NOT NULL,
  `subscribe_num` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`scene_id`),
  KEY `ticket` (`ticket`),
  KEY `subscribe_num` (`subscribe_num`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='微信二维码';

CREATE TABLE `[#DB_PREFIX#]weixin_reply_rule` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `account_id` int(10) NOT NULL DEFAULT '0',
  `keyword` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `title` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `image_file` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT '',
  `description` text CHARACTER SET utf8 COLLATE utf8_unicode_ci,
  `link` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `enabled` tinyint(1) DEFAULT '0',
  `sort_status` int(10) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `account_id` (`account_id`),
  KEY `keyword` (`keyword`),
  KEY `enabled` (`enabled`),
  KEY `sort_status` (`sort_status`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `[#DB_PREFIX#]weixin_third_party_api` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `account_id` int(10) NOT NULL DEFAULT '0',
  `url` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `enabled` tinyint(1) NOT NULL DEFAULT '0',
  `rank` tinyint(2) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `account_id` (`account_id`),
  KEY `enabled` (`enabled`),
  KEY `rank` (`rank`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='微信第三方接入';

CREATE TABLE `[#DB_PREFIX#]work_experience` (
  `work_id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `uid` int(11) DEFAULT NULL COMMENT '用户ID',
  `start_year` int(11) DEFAULT NULL COMMENT '开始年份',
  `end_year` int(11) DEFAULT NULL COMMENT '结束年月',
  `company_name` varchar(64) DEFAULT NULL COMMENT '公司名',
  `job_id` int(11) DEFAULT NULL COMMENT '职位ID',
  `add_time` int(10) DEFAULT NULL COMMENT '添加时间',
  PRIMARY KEY (`work_id`),
  KEY `uid` (`uid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='工作经历';

CREATE TABLE `[#DB_PREFIX#]yoyow_assign_task` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `act_start_time` int(10) DEFAULT NULL COMMENT '获得积分开始时间',
  `act_end_time` int(10) DEFAULT NULL COMMENT '获得积分结束时间',
  `coin` decimal(16,4) DEFAULT NULL,
  `exec_time` int(11) DEFAULT NULL COMMENT '任务将在什么时间执行',
  `status` int(1) DEFAULT NULL COMMENT '任务的状态（0未执行可修改方案，1正在执行不可修改，2执行完成不可修改，3执行失败）',
  `used_time` int(10) DEFAULT NULL COMMENT '任务执行总耗时(保持跟exec_time同样的单位)',
  `remain` decimal(16,4) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=162 DEFAULT CHARSET=utf8;

CREATE TABLE `[#DB_PREFIX#]yoyow_login` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `token` int(10) NOT NULL,
  `yoyow` varchar(50) DEFAULT NULL,
  `session_id` varchar(32) NOT NULL,
  `expire` int(10) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `session_id` (`session_id`),
  KEY `token` (`token`),
  KEY `expire` (`expire`)
) ENGINE=MyISAM AUTO_INCREMENT=93 DEFAULT CHARSET=utf8;

CREATE TABLE `[#DB_PREFIX#]yoyow_ranking` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `uid` int(10) unsigned NOT NULL,
  `ranking` int(10) unsigned NOT NULL,
  `coin` decimal(16,4) NOT NULL,
  `status` tinyint(4) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uid` (`uid`),
  UNIQUE KEY `ranking` (`ranking`)
) ENGINE=InnoDB AUTO_INCREMENT=125 DEFAULT CHARSET=utf8mb4;

CREATE TABLE `[#DB_PREFIX#]yoyow_tranfer_order` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '自动ID',
  `from_uid` int(11) DEFAULT NULL COMMENT '转出者用户ID',
  `to_uid` int(11) DEFAULT NULL COMMENT '转入者用户ID',
  `from_yoyow` varchar(50) DEFAULT NULL COMMENT '转出者yoyow账号',
  `to_yoyow` varchar(50) DEFAULT NULL COMMENT '转入者yoyow账号',
  `from_user_name` varchar(255) DEFAULT NULL COMMENT '转出者用户名称',
  `to_user_name` varchar(255) DEFAULT NULL COMMENT '转入者用户名称',
  `add_time` int(10) DEFAULT NULL COMMENT '添加时间',
  `desc` varchar(1000) DEFAULT NULL COMMENT '订单备注说明',
  `coin` decimal(16,4) DEFAULT NULL COMMENT '金额',
  `tranfer_time` int(10) DEFAULT NULL COMMENT '实际转账时间',
  `status` varchar(10) DEFAULT NULL COMMENT '状态 0: 待发放 10000: 转账成功 20000: 转账失败 30000:待提现',
  `type` varchar(1) DEFAULT NULL COMMENT '1: 日常发币 2: 提成奖励 3: 注册奖励  4: 邀请奖励  5: 传播奖',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=52 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='转账订单表';

CREATE TABLE `[#DB_PREFIX#]yoyow_transfer_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '自动ID',
  `from_uid` int(11) DEFAULT NULL COMMENT '转出者用户ID',
  `to_uid` int(11) DEFAULT NULL COMMENT '转入者用户ID',
  `from_yoyow` varchar(50) DEFAULT NULL COMMENT '转出者yoyow账号 ',
  `to_yoyow` varchar(50) DEFAULT NULL COMMENT '转入者yoyow账号 ',
  `add_time` int(10) DEFAULT NULL COMMENT '添加时间',
  `param` varchar(1000) DEFAULT NULL COMMENT '请求参数or返回结果',
  `result` varchar(100) DEFAULT NULL COMMENT '操作结果',
  `flag` varchar(10) DEFAULT NULL COMMENT '键值对',
  `type` varchar(1) DEFAULT NULL COMMENT '1: 请求 2: 返回',
  `operate` varchar(1) DEFAULT NULL COMMENT 'operate: 1:日常奖励 2:提成奖励  3:提现',
  `coin` decimal(16,4) DEFAULT NULL COMMENT '金额',
  `task_id` int(11) DEFAULT NULL COMMENT '任务id',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=141 DEFAULT CHARSET=utf8mb4;

CREATE TABLE `[#DB_PREFIX#]yoyow_transfer_order` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '自动ID',
  `from_uid` int(11) DEFAULT NULL COMMENT '转出者用户ID',
  `to_uid` int(11) DEFAULT NULL COMMENT '转入者用户ID',
  `from_yoyow` varchar(50) DEFAULT NULL COMMENT '转出者yoyow账号',
  `to_yoyow` varchar(50) DEFAULT NULL COMMENT '转入者yoyow账号',
  `from_user_name` varchar(255) DEFAULT NULL COMMENT '转出者用户名称',
  `to_user_name` varchar(255) DEFAULT NULL COMMENT '转入者用户名称',
  `add_time` int(10) DEFAULT NULL COMMENT '添加时间',
  `desc` varchar(1000) DEFAULT NULL COMMENT '订单备注说明',
  `coin` decimal(16,4) DEFAULT NULL COMMENT '金额',
  `transfer_time` int(10) DEFAULT NULL COMMENT '实际转账时间',
  `status` varchar(10) DEFAULT NULL COMMENT '状态 0: 待发放 10000: 转账成功 20000: 转账失败 30000:待提现',
  `type` varchar(1) DEFAULT NULL COMMENT '1: 日常发币 2: 提成奖励 3: 注册奖励  4: 邀请奖励  5: 传播奖',
  `result` varchar(1000) DEFAULT NULL COMMENT '转账结果',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=62 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='转账订单表';

CREATE TABLE `[#DB_PREFIX#]yoyow_trigger_task` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `coin` int(11) DEFAULT NULL COMMENT '本次分配的币数',
  `exec_time` int(10) DEFAULT NULL COMMENT '任务执行时间',
  `used_time` int(10) DEFAULT NULL COMMENT '任务执行总耗时(保持跟exec_time同样的单位)',
  `remain` int(11) DEFAULT NULL COMMENT '分配任务完成后，还剩余多少没有分配',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `[#DB_PREFIX#]category`(`title`,`type`) VALUES
('默认分类', 'question');

INSERT INTO `[#DB_PREFIX#]nav_menu`(`title`,`description`,`type`,`type_id`) VALUES
('默认分类', '默认分类描述', 'category', 1);

INSERT INTO `[#DB_PREFIX#]jobs` (`id`, `job_name`) VALUES
(1, '销售'),
(2, '市场/市场拓展/公关'),
(3, '商务/采购/贸易'),
(4, '计算机软、硬件/互联网/IT'),
(5, '电子/半导体/仪表仪器'),
(6, '通信技术'),
(7, '客户服务/技术支持'),
(8, '行政/后勤'),
(9, '人力资源'),
(10, '高级管理'),
(11, '生产/加工/制造'),
(12, '质控/安检'),
(13, '工程机械'),
(14, '技工'),
(15, '财会/审计/统计'),
(16, '金融/银行/保险/证券/投资'),
(17, '建筑/房地产/装修/物业'),
(18, '交通/仓储/物流'),
(19, '普通劳动力/家政服务'),
(20, '零售业'),
(21, '教育/培训'),
(22, '咨询/顾问'),
(23, '学术/科研'),
(24, '法律'),
(25, '美术/设计/创意'),
(26, '编辑/文案/传媒/影视/新闻'),
(27, '酒店/餐饮/旅游/娱乐'),
(28, '化工'),
(29, '能源/矿产/地质勘查'),
(30, '医疗/护理/保健/美容'),
(31, '生物/制药/医疗器械'),
(32, '翻译（口译与笔译）'),
(33, '公务员'),
(34, '环境科学/环保'),
(35, '农/林/牧/渔业'),
(36, '兼职/临时/培训生/储备干部'),
(37, '在校学生'),
(38, '其他');

INSERT INTO `[#DB_PREFIX#]topic` (`topic_title`, `topic_description`) VALUES('默认话题', '默认话题');

INSERT INTO `[#DB_PREFIX#]users_group` (`group_id`, `type`, `custom`, `group_name`, `reputation_lower`, `reputation_higer`, `reputation_factor`,`reputation_praise`,`permission`) VALUES
(1, 0, 0, '超级管理员', 0, 0, 5, 1, 'a:15:{s:16:"is_administortar";s:1:"1";s:12:"is_moderator";s:1:"1";s:16:"publish_question";s:1:"1";s:21:"publish_approval_time";a:2:{s:5:"start";s:0:"";s:3:"end";s:0:"";}s:13:"edit_question";s:1:"1";s:10:"edit_topic";s:1:"1";s:12:"manage_topic";s:1:"1";s:12:"create_topic";s:1:"1";s:17:"redirect_question";s:1:"1";s:13:"upload_attach";s:1:"1";s:11:"publish_url";s:1:"1";s:15:"publish_article";s:1:"1";s:12:"edit_article";s:1:"1";s:19:"edit_question_topic";s:1:"1";s:15:"publish_comment";s:1:"1";}'),
(2, 0, 0, '前台管理员', 0, 0, 4, 1, 'a:14:{s:12:"is_moderator";s:1:"1";s:16:"publish_question";s:1:"1";s:21:"publish_approval_time";a:2:{s:5:"start";s:0:"";s:3:"end";s:0:"";}s:13:"edit_question";s:1:"1";s:10:"edit_topic";s:1:"1";s:12:"manage_topic";s:1:"1";s:12:"create_topic";s:1:"1";s:17:"redirect_question";s:1:"1";s:13:"upload_attach";s:1:"1";s:11:"publish_url";s:1:"1";s:15:"publish_article";s:1:"1";s:12:"edit_article";s:1:"1";s:19:"edit_question_topic";s:1:"1";s:15:"publish_comment";s:1:"1";}'),
(3, 0, 0, '未验证会员', 0, 0, 0, 1, 'a:5:{s:16:"publish_question";s:1:"1";s:21:"publish_approval_time";a:2:{s:5:"start";s:0:"";s:3:"end";s:0:"";}s:11:"human_valid";s:1:"1";s:19:"question_valid_hour";s:1:"2";s:17:"answer_valid_hour";s:1:"2";}'),
(4, 0, 0, '普通会员', 0, 0, 0, 1, 'a:3:{s:21:"publish_approval_time";a:2:{s:5:"start";s:0:"";s:3:"end";s:0:"";}s:19:"question_valid_hour";s:2:"10";s:17:"answer_valid_hour";s:2:"10";}'),
(5, 1, 0, '注册会员', 0, 100, 1, 1, 'a:6:{s:16:"publish_question";s:1:"1";s:21:"publish_approval_time";a:2:{s:5:"start";s:0:"";s:3:"end";s:0:"";}s:11:"human_valid";s:1:"1";s:19:"question_valid_hour";s:1:"5";s:17:"answer_valid_hour";s:1:"5";s:15:"publish_comment";s:1:"1";}'),
(6, 1, 0, '初级会员', 100, 200, 1, 1, 'a:8:{s:16:"publish_question";s:1:"1";s:21:"publish_approval_time";a:2:{s:5:"start";s:0:"";s:3:"end";s:0:"";}s:13:"upload_attach";s:1:"1";s:11:"publish_url";s:1:"1";s:19:"question_valid_hour";s:1:"5";s:17:"answer_valid_hour";s:1:"5";s:15:"publish_article";s:1:"1";s:19:"edit_question_topic";s:1:"1";}'),
(7, 1, 0, '中级会员', 200, 500, 1, 1, 'a:9:{s:16:"publish_question";s:1:"1";s:21:"publish_approval_time";a:2:{s:5:"start";s:0:"";s:3:"end";s:0:"";}s:10:"edit_topic";s:1:"1";s:12:"create_topic";s:1:"1";s:17:"redirect_question";s:1:"1";s:13:"upload_attach";s:1:"1";s:11:"publish_url";s:1:"1";s:15:"publish_article";s:1:"1";s:15:"publish_comment";s:1:"1";}'),
(8, 1, 0, '高级会员', 500, 1000, 1, 1, 'a:11:{s:16:"publish_question";s:1:"1";s:21:"publish_approval_time";a:2:{s:5:"start";s:0:"";s:3:"end";s:0:"";}s:13:"edit_question";s:1:"1";s:10:"edit_topic";s:1:"1";s:12:"create_topic";s:1:"1";s:17:"redirect_question";s:1:"1";s:13:"upload_attach";s:1:"1";s:11:"publish_url";s:1:"1";s:15:"publish_article";s:1:"1";s:19:"edit_question_topic";s:1:"1";s:15:"publish_comment";s:1:"1";}'),
(9, 1, 0, '核心会员', 1000, 999999, 1, 1, 'a:12:{s:16:"publish_question";s:1:"1";s:21:"publish_approval_time";a:2:{s:5:"start";s:0:"";s:3:"end";s:0:"";}s:13:"edit_question";s:1:"1";s:10:"edit_topic";s:1:"1";s:12:"manage_topic";s:1:"1";s:12:"create_topic";s:1:"1";s:17:"redirect_question";s:1:"1";s:13:"upload_attach";s:1:"1";s:11:"publish_url";s:1:"1";s:15:"publish_article";s:1:"1";s:19:"edit_question_topic";s:1:"1";s:15:"publish_comment";s:1:"1";}'),
(99, 0, 0, '游客', 0, 0, 0, 1, 'a:9:{s:10:"visit_site";s:1:"1";s:13:"visit_explore";s:1:"1";s:12:"search_avail";s:1:"1";s:14:"visit_question";s:1:"1";s:11:"visit_topic";s:1:"1";s:13:"visit_feature";s:1:"1";s:12:"visit_people";s:1:"1";s:13:"visit_chapter";s:1:"1";s:11:"answer_show";s:1:"1";}');
