<?php
/*
// 说明: 创建系统表
// 作者: 小陈 
// 时间: 2013-10-07 16:55
*/

$db_tables = array();

$db_tables["patient"] = "CREATE TABLE IF NOT EXISTS `patient_{hid}` (
	`id` int(10) NOT NULL AUTO_INCREMENT,
	`part_id` int(10) NOT NULL DEFAULT '0',
	`name` varchar(20) NOT NULL,
	`age` int(3) NOT NULL,
	`sex` varchar(6) NOT NULL COMMENT '性别',
	`disease_id` vachar(200) NOT NULL DEFAULT '0' COMMENT '装修类型',
	`depart` int(10) NOT NULL DEFAULT '0' COMMENT '门店',
	`is_local` tinyint(1) NOT NULL DEFAULT '1' COMMENT '是否本地顾客',
	`area` varchar(32) NOT NULL COMMENT '顾客来源地区',
	`tel` varchar(20) NOT NULL,
	`qq` varchar(20) NOT NULL,
	`zhuanjia_num` varchar(10) NOT NULL,
	`content` mediumtext NOT NULL,
	`jiedai` varchar(20) NOT NULL,
	`jiedai_content` text NOT NULL,
	`order_date` int(10) NOT NULL DEFAULT '0',
	`order_date_changes` int(4) NOT NULL DEFAULT '0' COMMENT '预约时间修改次数',
	`order_date_log` mediumtext NOT NULL,
	`media_from` varchar(20) NOT NULL,
	`engine` varchar(32) NOT NULL,
	`engine_key` varchar(32) NOT NULL,
	`from_site` varchar(40) NOT NULL,
	`from_account` int(10) NOT NULL DEFAULT '0' COMMENT '所属帐户',
	`memo` mediumtext NOT NULL,
	`status` int(2) NOT NULL DEFAULT '0',
	`come_date` int(10) NOT NULL DEFAULT '0',
	`doctor` varchar(32) NOT NULL COMMENT '接待医生',
	`xiaofei` int(2) NOT NULL DEFAULT '0' COMMENT '是否消费',
	`xiangmu` varchar(250) NOT NULL COMMENT '治疗项目',
	`huifang` mediumtext NOT NULL COMMENT '回访记录',
	`rechecktime` int(10) NOT NULL DEFAULT '0' COMMENT '复查时间',
	`addtime` int(10) NOT NULL DEFAULT '0',
	`author` varchar(32) NOT NULL,
	`edit_log` mediumtext NOT NULL COMMENT '修改记录',
	`mtly` varchar(32) COMMENT '媒体来源',
	PRIMARY KEY (`id`),
	KEY `part_id` (`part_id`),
	KEY `order_date` (`order_date`),
	KEY `addtime` (`addtime`),
	KEY `author` (`author`)
	) ENGINE=MyISAM  DEFAULT CHARSET=gbk;";


?>