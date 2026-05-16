<?php
/*
// - 功能说明 : core.php
// - 创建作者 : 小陈 
// - 创建时间 : 2013-10-19 09:31
*/
//error_reporting(E_ALL ^ E_NOTICE);
error_reporting(0);
ob_start();
define("ROOT", str_replace("\\", "/", dirname(dirname(__FILE__)))."/");

$time = $timestamp = time();
$page_begin_time = $time.substr(microtime(), 1, 7);
$islocal = @file_exists("D:/Server/") ? true : false;

//脚本最大执行时间
set_time_limit(300);

require ROOT."core/config.php";
require ROOT."core/class.mysql.php";
$db = new mysql($mysql_server);
//if (!$islocal) {
	$db->show_error = false;
//}

// session 处理:
require ROOT."core/session.php";

// 加载核心文件
include ROOT."core/config.more.php";
require ROOT."core/function.php";

$log = load_class("log");
$power = load_class("power", $db);
$part = load_class("part", $db);

// 初始化数据
$username = $_SESSION[$cfgSessionName]["username"];
$debug_mode = $_SESSION[$cfgSessionName]["debug"] ? 1 : 0;
$config = array();
if (!isset($nochecklogin) || !$nochecklogin) {
	if (empty($username)) {
		if ($_POST) {
			include ROOT."core/offline.tips.php";
			exit;
		}
		exit("<script> top.location = '".$_SESSION["root_url"]."/m/login.php'; </script>");
	} else {
		$uinfo = load_user_info($username);
		$uid = $uinfo["id"];
		$usermenu = $uinfo["menu"];
		$shortcut = $uinfo["shortcut"];
		$realname = $uinfo["realname"];
		if ($uinfo["config"] != '') {
			$config = @unserialize($uinfo["config"]);
		}
	}
}
$uid = intval($uid);

// 页面信息:
$pinfo = load_page_info();
$pagesize = 25;
if ($pinfo) {
	$pagesize = noe($pinfo["pagesize"], 20);
	$pagepower = $pinfo["pagepower"];
}

$op = $_REQUEST["op"];
if (isset($_REQUEST["id"])) {
	$id = intval($_REQUEST["id"]);
}


// 2013-05-19 11:35
if ($debug_mode || $username == 'admin') {
	$hospital_ids = $db->query("select id from hospital", '', 'id');
} else {
	if ($uinfo["hospitals"] != '') {
		$hospital_ids = explode(",", $uinfo["hospitals"]);
	} else {
		$hospital_ids = array();
	}
}
if (count($hospital_ids) == 1) {
	$_SESSION[$cfgSessionName]["hospital_id"] = intval($hospital_ids[0]);
}
$hospitals = implode(",", $hospital_ids);

$hid = $user_hospital_id = intval($_SESSION[$cfgSessionName]["hospital_id"]);

$hinfo = $hconfig = array();
if ($hid > 0) {
	$hinfo = $db->query("select * from hospital where id='$hid' limit 1", 1);
	if ($hinfo["config"]) {
		$hconfig = unserialize($hinfo["config"]);
	}
}

// 页面历史记录:
if (!$_POST && $_SERVER["REQUEST_URI"] != '') {
	if (empty($_SESSION["history"]) || (count($_SESSION["history"]) && $_SESSION["history"][count($_SESSION["history"]) - 1] != $_SERVER["REQUEST_URI"])) {
		if (substr_count($_SERVER["REQUEST_URI"], "/http/") == 0 && $_SERVER["REQUEST_URI"] != "/") {
			$_SESSION["history"][] = $_SERVER["REQUEST_URI"];
			if (count($_SESSION["history"]) > 20) {
				array_shift($_SESSION["history"]);
			}
		}
	}
}

$power->check_power() or msg_box("没有权限", "back", 1);

?>