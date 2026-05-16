<?php

/*

// 说明:

// 作者: 小陈 

// 时间:

*/



// 操作的处理:



if ($op == "add") {

	include $mod.".edit.php";

	exit;

}



if ($op == "edit") {

	$line = $db->query("select * from $table where id='$id' limit 1", 1);

	include $mod.".edit.php";

	exit;

}





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

		$log->add("delete", "删除数据", serialize($op_data));

	}



	if ($del_bad > 0) {

		msg_box("删除成功 $del_ok 条资料，删除失败 $del_bad 条资料。", "back", 1);

	} else {

		msg_box("删除成功", "back", 1);

	}

}



if ($op == "setshow") {

	$isshow_value = intval($_GET["value"]) > 0 ? 1 : 0;

	$ids = explode(",", $_GET["id"]);

	$set_ok = $set_bad = 0;

	foreach ($ids as $opid) {

		if (($opid = intval($opid)) > 0) {

			if ($db->query("update $table set isshow='$isshow_value' where id='$opid' limit 1")) {

				$set_ok++;

			} else {

				$set_bad++;

			}

		}

	}



	if ($set_bad > 0) {

		msg_box("操作成功完成 $set_ok 条，失败 $del_bad 条。", "back", 1);

	} else {

		msg_box("设置成功！", "back", 1);

	}

}



?>