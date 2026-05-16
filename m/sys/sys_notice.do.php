<?php

/*

// 说明:

// 作者: 小陈 

// 时间:

*/



// 添加和修改:

if ($op == "add" || $op == "edit") {



	if ($id) {

		$line = $db->query("select * from $table where id=$id limit 1", 1);

	}



	// post

	if ($_POST) {

		$r = array();



		// 通知接收者:

		if ($_POST["reader_type"] == '') {

			$_POST["reader_type"] = "all";

		}

		$r["reader_type"] = $reader = $_POST["reader_type"];

		if ($reader == "part") {

			$r["part_ids"] = implode(",", array_keys($part->get_sub_part($_POST["part"], 1)));

		} else {

			$r["part_ids"] = '';

		}

		if ($reader == "user") {

			$r["uids"] = implode(",", $_POST["rec_user"]);

		} else {

			$r["uids"] = '';

		}



		// 通知内容:

		$r["title"] = $_POST["title"];

		$r["content"] = $_POST["content"];



		// 通知有效期:

		$r["begintime"] = $_POST["begin_date"] ? strtotime($_POST["begin_date"]) : 0;

		$r["endtime"] = $_POST["end_date"] ? strtotime($_POST["end_date"]) : 0;



		if ($op == 'add') {

			$r["isshow"] = 1;

			$r["uid"] = $uid;

			$r["u_realname"] = $realname;

			$r["addtime"] = time();

		}



		$sqldata = $db->sqljoin($r);

		if ($op == 'add') {

			$sql = "insert into $table set $sqldata";

		} else {

			$sql = "update $table set $sqldata where id=$id limit 1";

		}

		if ($db->query($sql)) {

			msg_box("资料提交成功！", back_url($_POST["back_url"], $pinfo["link"], "#".$id), 1);

		} else {

			msg_box("资料提交失败，系统繁忙，请稍后再试。", "back", 1, 5);

		}

		exit;

	}



	$title = $pinfo["title"]." - ".($op == "add" ? "新增" : "修改");



	include "sys_notice.edit.php";

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