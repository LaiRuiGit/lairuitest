<?php
/*
// - 功能说明 : 退出网站管理系统
// - 创建作者 : 小陈 
// - 创建时间 : 2013-05-15 11:39
*/
require "../core/core.php";
$db->query("update sys_admin set online='0', lastactiontime='0' where name='$username' limit 1");

$_SESSION[$cfgSessionName] = array();
$_SESSION = array();
session_destroy();

if (!$debug_mode) {
	//$log->add("logout", "管理员 $username 退出系统");
}

header("location:../m/login.php?username=$username");
?>