<?php
/*
// - 功能说明 : 顾客列表
// - 创建作者 : 小陈 
// - 创建时间 : 2013-05-01 05:09
*/
require "../../core/core.php";
$mod = "patient";
$table = "patient_".$user_hospital_id;

if ($user_hospital_id == 0) {
	exit_html("对不起，没有选择门店，不能执行该操作！");
}

// 颜色定义 2010-07-31
$line_color = array('black', 'red', 'silver', '#8AC6C6', '#8000FF','#18aa05','#09ded6','#ce09e5');
//$line_color_tip = array("等待", "已到", "未到", "过期", "回访","全流失","半流失","已诊治");
$line_color_tip = array("待预约", "已签约", "已放弃", "已量尺", "已报价");
//$area_id_name = array(1 => "局改", 2 => "全屋", 3 => "整装", 4 => "翻新", 5 => "家具", 6 => "软装");
$db = new mysql($mysql_server);
$area_id_name = $db->query("select id,name from doctor", "id", "name");


// 操作的处理:
if ($op = $_GET["op"]) {
	include "patient.op.php";
}

include "patient.list.php";

?>