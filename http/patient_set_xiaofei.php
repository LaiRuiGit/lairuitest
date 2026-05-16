<?php
/*
// - 功能说明 : 顾客设置消费
// - 创建作者 : 小陈 
// - 创建时间 : 2013-05-02 14:30
*/
require "../core/core.php";
require "../core/class.fastjson.php";
header("Content-Type:text/html;charset=GB2312");
header("Cache-Control: no-cache, must-revalidate");
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
$out = array();
$out["status"] = 'bad';

$id = intval($_GET["id"]);
$xiaofei = intval($_GET["xiaofei"]);

$time = time();
if ($id > 0) {
	$db->query("update ".$tabpre."patient set xiaofei=$xiaofei where id=$id limit 1");
	$out["status"] = 'ok';
	$out["id"] = $id;
	$out["xiaofei"] = $xiaofei;
}

echo FastJSON::convert($out);
?>