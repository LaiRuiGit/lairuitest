<?php

/*

// 说明:

// 作者: 小陈 

// 时间:

*/



// 操作的处理:

if ($op == "delete") {

	$ids = explode(",", $_GET["id"]);

	$del_ok = $del_bad = 0; $op_data = array();

	foreach ($ids as $opid) {

		if (($opid = intval($opid)) > 0) {

			$tmp_data = $db->query("select * from $table where id='$opid' limit 1", 1);

			if ($db->query("delete from $table where id='$opid' limit 1")) {

				$del_ok++;

				$op_data[] = $tmp_data;

			} else {

				$del_bad++;

			}

		}

	}



	if ($del_ok > 0) {

		$log->add("delete", "删除数据", serialize($op_data));

	}



	if ($del_bad > 0) {

		msg_box("删除成功 $del_ok 条资料，删除失败 $del_bad 条资料。", "back", 1);

	} else {

		msg_box("删除成功", "back", 1);

	}



}



if ($op == "clear") {

	$db->query("truncate table $table");

	msg_box("清空成功", "back", 1);

}



if ($op == "del_week") {

	$date = strtotime("-1 week");

	$db->query("delete from $table where addtime<$date");

	msg_box("删除数据成功", "back", 1);

}



?>