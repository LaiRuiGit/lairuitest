<?php
/*
// - 功能说明 : ajax方式获取ip并返回
// - 创建作者 : 小陈 
// - 创建时间 : 2013-05-20 14:23
*/
require "../core/class.fastjson.php";
header("Content-Type:text/html;charset=GB2312");
header("Cache-Control: no-cache, must-revalidate");
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
$out = array();

$ip = $_GET["ip"];

$ipfunc = "../../ip/function.ip.php";
if (!file_exists($ipfunc)) {
	$out["status"] = "error";
	$out["ip"] = $ip;
	$out["tips"] = "查询错误，ip组件包不存在..";
} else {
	include_once $ipfunc;
	$ip_area = ip_area($ip);
	if ($ip_area !== false) {
		$out["status"] = "ok";
		$out["iparea"] = $ip_area;
	} else {
		$out["status"] = "error";
		$out["tips"] = "系统错误，查询不到结果...";
	}
	$out["ip"] = $ip;
}

echo FastJSON::convert($out);
?>