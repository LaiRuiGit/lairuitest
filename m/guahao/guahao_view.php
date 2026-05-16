<?php
/*
// - 功能说明 : 挂号资料查看
// - 创建作者 : 小陈 
// - 创建时间 : 2013-05-22 13:03
*/
require "../../core/core.php";
$table = "guahao";

if (!$user_hospital_id) {
	exit_html("对不起，没有选择门店，不能执行该操作！");
}

if ($id = $_GET["id"]) {
	$line = $db->query("select * from $table where hospital_id=$user_hospital_id and id='$id' limit 1", 1);
} else {
	msg_box("参数错误...", "back", 1);
}

check_power("v", $pinfo, $pagepower) or msg_box("对不起，您没有查看权限!", "back", 1);

$title = "查看挂号资料";

// 数据:
$viewdata = array(
	array("姓名", $line["name"]),
	array("性别", $line["sex"]),
	array("电话", $line["tel"]),
	array("E-Mail", $line["email"]),
	array("城市", $line["city"]),
	array("预约时间", $line["order_date"] > 0 ? date("Y-m-d H:i", $line["order_date"]) : '-'),
	array("预约门店", $line["depart"]),
	array("预约内容", text_show($line["content"])),
	array("预约医生", $line["doctor"]),
	array("备注", text_show($line["memo"])),
	array("发布者IP", $line["ip"]),
	array("IP对应地址", $line["ip_address"]),
	array("提交时间", date("Y-m-d H:i", $line["addtime"])),
	array("来源站点", $line["site"]),
	array("POST数据(供参考)", $line["postdata"]),
);

if ($debug_mode) {
	$viewdata = array_merge($viewdata, array(
		array("GET数据", $line["getdata"]),
		array("SERVER数据", $line["serverdata"])
	));
}

include "tpl/view.tpl.php";
?>