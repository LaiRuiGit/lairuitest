<?php
/*
// 说明: admin.do.php
// 作者: 小陈 
// 时间: 2010-07-07
*/


// 添加和修改:
if ($op == "add" || $op == "edit") {

	// post:
	if ($_POST) {
		$edit_mode = $_POST["edit_mode"];

		if ($edit_mode == "all") {
			// 用户名和密码:
			$r = array();
			$name = $_POST["name"];
			if ($op == "add" && $db->query("select count(*) as count from $table where name='$name'", 1, "count") > 0) {
				msg_box("该帐户名称($name)已经有人使用，请尝试其他名称！", "back", 1, 4);
			}
			if ($op == "add") {
				$r["name"] = $name;
			}

			if (!$_POST["realname"]) {
				msg_box("真实姓名不能为空，请填写！", "back", 1);
			}
			$r["realname"] = $_POST["realname"];

			if ($_POST["pass"]) {
				$r["pass"] = md5($_POST["pass"]);
			}

			// 用户权限:
			$powermode = intval($_POST["powermode"]);
			if (!$powermode) {
				msg_box("授权模式必须指定！", "back", 1);
			}
			$r["powermode"] = $powermode;

			if ($powermode == 1) {
				$r["menu"] = $_POST["power_detail"];
			} else if ($powermode == 2) {
				$r["character_id"] = intval($_POST["character_id"]);
			}


			// 门店和部门:
			if (isset($_POST["hospital_ids"])) {
				$r["hospitals"] = implode(",", $_POST["hospital_ids"]);
			} else {
				$r["hospitals"] = $uinfo["hospitals"];
			}

			$r["part_id"] = $_POST["part_id"];
			$r["part_admin"] = $_POST["part_admin"] ? 1 : 0; //2013-05-19 10:29 是否部门管理员
			$r["part_manage"] = implode(",", $_POST["part_manage"]);
			$r["show_tel"] = intval($_POST["show_tel"]);


			if ($op == "add") {
				$r["addtime"] = time();
				//$r["showmodule"] = "jsmenu,shortcut,logobar";
			}
		} else {
			$r = array();

			// 我的hospitals:
			//$my_hospitals = array_keys($uinfo["hospital_ids"]);
			$my_hospitals = $uinfo["hospital_ids"];

			$old = $db->query("select * from $table where id=$id limit 1", 1);
			if (!$old) exit("Error...");

			$ori_hospitals = $power->parse_hospitals($old["hospitals"]);

			$all_hospitals = $db->query("select id from hospital", "", "id");
			foreach ($ori_hospitals as $k => $v) {
				if (!in_array($k, $all_hospitals)) {
					unset($ori_hospitals[$k]); //删除无效站点
				}
				if (in_array($k, $my_hospitals)) {
					unset($ori_hospitals[$k]);
				}
			}

			// 站点及权限:
			$the_hospitals = array();
			foreach ($ori_hospitals as $k => $v) {
				if ($k > 0) {
					$the_hospitals[] = $k.($v > 0 ? (":".$v) : '');
				}
			}

			foreach ($_POST["hospitals"] as $sid) {
				if (intval($_POST["hospital_group"][$sid]) > 0) {
					$the_hospitals[] = $sid.":".intval($_POST["hospital_group"][$sid]);
				} else {
					$the_hospitals[] = $sid;
				}
			}

			$r["hospitals"] = @implode(",", $the_hospitals);
		}

		$_GET["back_url"] = base64_decode($_POST["back_url"]);

		$sqldata = $db->sqljoin($r);
		if ($op == "edit") {
			$sql = "update $table set $sqldata where id='$id' limit 1";
		} else {
			$sql = "insert into $table set $sqldata";
		}

		if ($db->query($sql)) {
			msg_box("资料提交成功！", back_url($_POST["back_url"], $pinfo["link"], "#".$id), 1);
		} else {
			msg_box("资料提交失败，系统繁忙，请稍后再试。", "back", 1, 5);
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
		$edit_mode = "all";
	} else {
		$title = "修改资料";
		$user = $db->query("select * from $table where id='$id' limit 1", 1);

		$user["power_compare"] = $power->compare_user($user, $uinfo);
		$user["hospitals_compare"] = $power->compare_hospitals($user, $uinfo);

		// 资料修改模式:
		$edit_mode = "none";
		if ($user["id"] != $uid && $user["power_compare"] <= 0) {
			if ($user["hospitals_compare"] <= 0) {
				$edit_mode = "all";
			} else {
				$edit_mode = "hospital";
			}
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
			if (!$user = $db->query_first("select * from $table where name='$admin_name' limit 1")) {
				msg_box("系统无此用户: {$admin_name}", "back", 1);
			}
		} else {
			msg_box("参数错误...", "back", 1);
		}
	}

	$hospital_id_name = $db->query("select id,name from hospital", "id", "name");

	if ($user["name"] == "admin") {
		$user["hospital_str"] = '<font color="gray">(所有门店)</font>';
	} else {
		$hospitals = $power->parse_hospitals($user["hospitals"]);
		$show_hospitals = array();
		foreach ($hospitals as $k => $v) {
			$show_hospitals[] = $GLOBALS["hospital_id_name"][$k];
		}
		$user["hospital_str"] = implode(" | ", $show_hospitals);
	}

	$title = "查看管理员资料";
	if ($op == "view") {
		include "admin.view.php";
	} else {
		include "admin.view.web.php";
	}
	exit;
}


if ($op == "set_hospital_power") {
	if ($_POST) {
		$power = $power->get_power_from_post();
		echo '<script>';
		echo 'parent.document.getElementById("sys_frame").contentWindow.set_hospital_power('.$_POST["hospital"].', "'.$power.'");';
		echo 'parent.load_box(0);';
		echo '</script>';
		exit;
	}

	include "admin.set_hospital_power.php";
	exit;
}

if ($op == "set_power") {
	if ($_POST) {
		$power = $power->get_power_from_post();
		echo '<script>';
		echo 'parent.document.getElementById("sys_frame").contentWindow.set_power_do("'.$_POST["pid"].'", "'.$power.'");';
		echo 'parent.load_box(0);';
		echo '</script>';
		exit;
	}

	include "admin.set_power.php";
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
		msg_box("操作成功", "back", 1, 1);
	}
}

?>