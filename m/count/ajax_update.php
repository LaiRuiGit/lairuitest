<?php
/*
// 说明: ajax 提交数据
// 作者: 小陈 
// 时间: 2010-11-24 16:53
*/
require "../../core/core.php";
require "../../core/class.fastjson.php";
$table = "count_day";

$cur_type = $_SESSION["count_type_id_web"];
$type_detail = $db->query("select * from count_type where id=$cur_type limit 1", 1);

$date = intval($_GET["date"]);
$type = $_GET["type"];
$data = floatval($_GET["data"]);

$out = array();
$out["status"] = 'error';

if (strlen($date) == 8 && $type != '') {

	// 判断是否已经添加
	$old = $db->query("select * from $table where type_id='$cur_type' and date='$date' limit 1", 1);

	$r = array();

	$mode = "add";
	if ($old) {
		$r[$type] = $data;
		$mode = "edit";
	} else {
		$r["`type`"] = "web";
		$r["type_id"] = $cur_type;
		$r["type_name"] = $type_detail["name"];
		$r["date"] = $date;
		$r[$type] = $data;
		$r["addtime"] = time();
		$r["uid"] = $uid;
		$r["u_name"] = $realname;
	}

	// 操作日志:
	if ($mode == "add") {
		$r["log"] = date("Y-m-d H:i")." ".$realname." 添加: ".$type.":".$r[$type]."\r\n";
	} else {
		$r["log"] = $old["log"].date("Y-m-d H:i")." ".$realname." 修改: ".$type.":".$old[$type]."=>".$r[$type]."\r\n";
	}

	$sqldata = $db->sqljoin($r);

	if ($mode == "add") {
		$rs = $db->query("insert into $table set $sqldata");
	} else {
		$rs = $db->query("update $table set $sqldata where type_id='$cur_type' and date='$date' limit 1");
	}

	$out["status"] = "ok";
}


echo FastJSON::convert($out);
?>