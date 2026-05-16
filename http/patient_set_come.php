<?php
/*
// - 功能说明 : 顾客设置已到/未到
// - 创建作者 : 小陈 
// - 创建时间 : 2013-05-01 22:42
*/
require "../core/core.php";
require "../core/class.fastjson.php";
header("Content-Type:text/html;charset=GB2312");
header("Cache-Control: no-cache, must-revalidate");
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
$out = array();
$out["status"] = 'bad';

$id = intval($_GET["id"]);
$come = intval($_GET["come"]);

$time = time();
if ($id > 0 && $come > 0) {
	$sqldata = '';
	if ($come == 1) {
		$sqldata = ", order_date=$time"; //已到的，将预约时间修改为当前时间
	}
	$db->query("update ".$tabpre."patient set status=$come, come_date=$time, jiedai='$realname' $sqldata where id=$id limit 1");

	$out["status"] = 'ok';
	$out["id"] = $id;
	$out["come"] = $come;
}

echo FastJSON::convert($out);
?>