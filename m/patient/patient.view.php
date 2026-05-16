<?php
/*
// - 功能说明 : 顾客资料查看
// - 创建作者 : 小陈 
// - 创建时间 : 2013-05-02 17:28
*/

if ($id = $_GET["id"]) {
	$line = $db->query_first("select * from $table where id='$id' limit 1");
} else {
	msg_box("参数错误...", "back", 1);
}

//!check_power("v", $pinfo, $pagepower) && msg_box("对不起，您没有查看权限!", "back", 1);

$title = "查看顾客资料";

$disease_id_name = $db->query("select id,name from ".$tabpre."disease where hospital_id=$user_hospital_id", 'id', 'name');
$part_id_name = $db->query("select id,name from ".$tabpre."sys_part", 'id', 'name');

$dis_array = array();
foreach (explode(",", $line["disease_id"]) as $v) {
	if ($v > 0) {
		$dis_array[] = $disease_id_name[$v];
	}
}

if ($realname != $li["author"]) {
	$line["tel"] = '-';
}

// 数据:
$viewdata[1] = array(
	array("姓名", $line["name"]),
	array("性别", $line["sex"]),
	array("电话", $line["tel"]),
	array("房子面积", $line["qq"]),
	array("所在小区", $line["zhuanjia_num"]),
	array("装修类型", implode("、", $dis_array)),
	array("接待人", $line["jiedai"]),
	array("预约时间", @date("Y-m-d H:i", $line["order_date"])),
	array("媒体来源", $line["media_from"]),
	array("赴约状态", $status_array[$line["status"]]),
	array("赴约时间", ($line["status"] ==1 ? @date("Y-m-d H:i", $line["order_date"]) : "未赴约")),
	array("接待医生", in_array($uinfo["part_id"], array(2,3)) ? "<font color=gray>-不显示-</font>" : $line["doctor"]),
	array("接待内容", text_show($line["jiedai_content"])),
	array("添加时间", @date("Y-m-d H:i", $line["addtime"])),
	array("添加人", $line["author"]),
	array("所在部门", $part_id_name[$line["part_id"]]),
);

$viewdata[2] = array(
	array("房子需求", this_text_show($line["content"]))
);

$viewdata[3] = array(
	array("回访记录", text_show($line["huifang"])),
);

$viewdata[4] = array(
	array("备注", this_text_show($line["memo"])),
);


include "../../res/patient.view.tpl.php";


// --------- 函数 -----------
function this_text_show($s) {
	$s = str_replace(" ", "&nbsp;", $s);
	$s = str_replace("\r", "", $s);
	$s = str_replace("\n", "<br>", $s);
	for ($i=0; $i<5; $i++) {
		$s = str_replace("<br><br>", "<br>", $s);
	}
	$s = "<br>".$s;
	$s = preg_replace("/<br>([^>]*?\d{2}:\d{2}:\d{2})/", "<br><br><font color=blue>[\\1]</font>", $s);
	while (substr($s, 0, 4) == "<br>") {
		$s = substr($s, 4);
	}
	return $s;
}
?>