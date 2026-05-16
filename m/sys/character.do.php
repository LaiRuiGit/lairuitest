<?php

/*

// 说明:

// 作者: 小陈 

// 时间:

*/



// 添加和修改:

if ($op == "add" || $op == "edit") {



	// post

	if ($_POST) {

		$record = array();

		$record["name"] = $_POST["ch_name"];

		$record["menu"] = $power->get_power_from_post();



		if ($op == "add") {

			$record["addtime"] = time();

			$record["author"] = $username;

		}



		$sqldata = $db->sqljoin($record);

		if ($op == "edit") {

			$sql = "update $table set $sqldata where id='$id' limit 1";

		} else {

			$sql = "insert into $table set $sqldata";

		}



		if ($db->query($sql)) {

			msg_box("权限资料提交成功", back_url($_POST["back_url"], $pinfo["link"], "#".$id), 1);

		} else {

			msg_box("资料提交失败，系统繁忙，请稍后再试。", "back", 1, 5);

		}

	}

	// end of post





	if ($op == "add") {

		$title = "创建新的权限";

	} else {

		$title = "修改权限定义";

		$cline = $db->query("select * from $table where id='$id' limit 1", 1);

		if ($power->compare_power($cline["menu"], $usermenu) >= 0) {

			msg_box("没有权限", "back", 1, 2);

		}

	}

	include "character.edit.php";

	exit;

}





if ($op == "view") {

	$line = $db->query("select * from $table where id='$id' limit 1", 1);



	include "character.view.php";

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



?>