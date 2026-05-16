<?php
/*
// 说明: sys_admin.op.php
// 作者: 小陈 
// 时间: 2010-10-16 13:39
*/
if (!defined("ROOT")) exit("Error.");

// 添加和修改:
if ($op == "add" || $op == "edit") {

	// post:
	if ($_POST) {
		// 用户名和密码:
		$r = array();
		$name = $_POST["name"];
		if ($op == "add" && $db->query("select count(*) as count from $table where name='$name'", 1, "count") > 0) {
			exit("该帐户名称($name)已经有人使用，请尝试其他名称！");
		}
		if ($op == "add") {
			$r["name"] = $name;
		}

		if ($op == "add") {
			if (!$_POST["realname"]) {
				exit("真实姓名不能为空，请填写！");
			}
			$r["realname"] = $_POST["realname"];
		}

		if ($_POST["pass"]) {
			$r["pass"] = md5($_POST["pass"]);
		}

		// 用户权限:
		$powermode = intval($_POST["powermode"]);
		if (!$powermode) {
			exit("授权模式必须指定！");
		}
		$r["powermode"] = $powermode;

		if ($powermode == 1) {
			$r["menu"] = $_POST["power_detail"];
		} else if ($powermode == 2) {
			$r["character_id"] = intval($_POST["character_id"]);
		}


		// 门店和部门:
		if (isset($_POST["hospital_ids"])) {
			asort($_POST["hospital_ids"]);
			$r["hospitals"] = implode(",", $_POST["hospital_ids"]);
		} else {
			//$r["hospitals"] = $uinfo["hospitals"];
			$r["hospitals"] = '';
		}

		$r["part_id"] = $_POST["part_id"];
		$r["part_admin"] = $_POST["part_admin"] ? 1 : 0; //2013-05-19 10:29 是否部门管理员
		$r["part_manage"] = implode(",", $_POST["part_manage"]);

		if ($debug_mode || $username == "admin") {
			$r["show_tel"] = intval($_POST["show_tel"]);
		}


		if ($op == "add") {
			$r["addtime"] = time();
			$r["author"] = $username;
		}

		$_GET["back_url"] = base64_decode($_POST["back_url"]);

		$sqldata = $db->sqljoin($r);
		if ($op == "edit") {
			$sql = "update $table set $sqldata where id='$id' limit 1";
		} else {
			$sql = "insert into $table set $sqldata";
		}

		if ($db->query($sql)) {
			if ($op == "edit") {
				if ($old["part_id"] != $_POST["part_id"]) {
					echo '<script> parent.update_content(); </script>';
				}
				echo '<script> parent.msg_box("资料提交成功", 2); </script>';
				echo '<script> parent.load_src(0); </script>';
			} else {
				msg_box("提交成功", "?refresh", 1);
			}
		} else {
			exit("资料提交失败，系统繁忙，请稍后再试。");
		}
	}
	// end of post

	$hospital_list = $db->query("select id,name from hospital where id in ($hospitals)", "id");
	$power->init_ch_data();
	$ch_data = $power->ch_data;
	foreach ($ch_data as $k => $v) {
		if ($power->compare_power($v["menu"], $usermenu) > 0) {
			unset($ch_data[$k]);
		}
	}


	if ($op == "add") {
		$title = "添加人员";
	} else {
		$title = "修改资料";
		$user = $db->query("select * from sys_admin where id='$id' limit 1", 1);

		// 检查修改权限:
		// 1. 检查门店修改权限:
		$uh = explode(",", trim($user["hospitals"], ","));
		foreach ($uh as $uhid) {
			if ($uhid > 0 && !in_array($uhid, $hospital_ids)) {
				exit("修改权限不够，门店超出您的管理范围。");
			}
		}
		// 2. 检查部门:
		$my_parts = array_keys($part->get_sub_part(intval($uinfo["part_id"]), 1));
		if ($user["part_id"] > 0 && !in_array($user["part_id"], $my_parts)) {
			exit("修改权限不够，部门超出您的管理范围。");
		}
		// 3. 检查管理部门:
		$part_manages = explode(",", trim($user["part_manage"], ','));
		if ($part_manages) {
			foreach ($part_manages as $_pid) {
				if ($_pid > 0 && !in_array($_pid, $my_parts)) {
					exit("修改权限不够，管理部门超出您的管理范围。");
				}
			}
		}
		// 4. 功能权限:
		if ($user["power_mode"] == 1) {
			$user_power = $user["menu"];
		} else {
			$user_power = $db->query("select menu from sys_character where id=".$user["character_id"]." limit 1", 1, "menu");
		}
		if ($power->compare_power($user_power, $usermenu) > 0) {
			exit("修改权限不够，权限超出您的管理范围。");
		}

	}

	include "admin.edit.php";
	exit;
}


if ($op == "view" || $op == "viewweb") {
	if ($nID = $_GET["id"]) {
		$user = $db->query("select * from $table where id='$nID' limit 1", 1);
	} else {
		if ($admin_name = $_GET["name"]) {
			if (!$user = $db->query("select * from $table where name='$admin_name' limit 1", 1)) {
				msg_box("系统无此用户: {$admin_name}", "back", 1);
			}
		} else {
			msg_box("参数错误...", "back", 1);
		}
	}

	$hospital_id_name = $db->query("select id,name from hospital", "id", "name");

	if ($user["name"] == "admin") {
		$user["hs_str"] = '<font color="gray">(所有门店)</font>';
	} else {
		$hs = explode(",", $user["hospitals"]);
		$show_hs = array();
		foreach ($hs as $v) {
			$show_hs[] = $hospital_id_name[$v];
		}
		$user["hs_str"] = implode(" | ", $show_hs);
	}

	$title = "查看管理员资料";
	if ($op == "view") {
		include "admin.view.php";
	} else {
		include "admin.view.web.php";
	}
	exit;
}

if ($op == "delete") {
	$ids = $_POST["uid"];
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

if ($op == "open" || $op == "close") {
	$isshow_value = ($op == "open" ? 1 : 0);
	$ids = $_POST["uid"];
	if (count($ids) > 0) {
		foreach ($ids as $opid) {
			if (($opid = intval($opid)) > 0) {
				$db->query("update sys_admin set isshow='$isshow_value' where id='$opid' limit 1");
			}
		}
		msg_box("操作成功", "back", 1, 1);
	} else {
		exit("没有选择人员");
	}
}

if ($op == "change_group_type") {
	$cur_group = $_SESSION["admin_group_type"] = intval($_GET["group"]);
}

if ($op == "set_ch") {
	$uids = $_POST["uid"];
	$new_ch = intval($_POST["ch_id"]);
	if (@count($uids) > 0 && $new_ch > 0) {
		foreach ($uids as $v) {
			$db->query("update sys_admin set powermode=2, character_id='$new_ch' where id='$v' limit 1");
		}
		msg_box("设置成功", "?refresh", 1);
	} else {
		exit("参数错误");
	}
}

?>