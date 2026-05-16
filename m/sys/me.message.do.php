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





?>