<?php

/*

// 说明:

// 作者: 小陈 

// 时间:

*/



if ($op == "view") {

	$line = $db->query("select * from `$table` where id='$id' limit 1", 1);

	$title = "浏览操作日志";



	// 数据:

	$viewdata = array(

		array("操作时间", date("Y-m-d H:i:s", $line["addtime"])),

		array("操作类型", $line["type"]." ".$OpType[$line["type"]]),

		array("操作说明", $line["title"]),

		array("页面", $line["url"]),

		array("操作人", $line["username"]),

		array("IP地址", $line["ip"].(function_exists("GetIPArea") ? (" - ".GetIPArea($line["ip"])) : "")),

		array("操作数据", '<textarea style="width:90%;height:300px;">'.$line["data"].'</textarea>'),

	);



	include "log.view.php";

	exit;

}





// 操作的处理:

if ($op == "delete") {

	$ids = explode(",", $_GET["id"]);

	$del_ok = $del_bad = 0; $op_data = array();

	foreach ($ids as $opid) {

		if (($opid = intval($opid)) > 0) {

			$tmp_data = $db->query_first("select * from $table where id='$opid' limit 1");

			if ($db->query("delete from $table where id='$opid' limit 1")) {

				$del_ok++;

				$op_data[] = $tmp_data;

			} else {

				$del_bad++;

			}

		}

	}



	if ($del_ok > 0) {

		//log_add("delete", "删除数据", $op_data, $table, $db);

	}



	if ($del_bad > 0) {

		msg_box("删除成功 $del_ok 条资料，删除失败 $del_bad 条资料。", "back", 1);

	} else {

		msg_box("删除成功", "back", 1);

	}

	exit;

}





// 清空数据:

if ($op == "clear") {

	$db->query("truncate table $table");

	msg_box("清空成功", "back", 1);

}





// 删除一周之前的数据:

if ($op == "del_week") {

	$date = strtotime("-1 week");

	$db->query("delete from $table where addtime<$date");

	msg_box("删除数据成功", "back", 1);

}



?>