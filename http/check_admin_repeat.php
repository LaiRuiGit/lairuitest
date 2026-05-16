<?php

/*

// - 功能说明 : 检查姓名重复情况

// - 创建作者 : 小陈 

// - 创建时间 : 2010-06-09 15:40

*/

header("Content-Type:text/html;charset=GB2312");

header("Cache-Control: no-cache, must-revalidate");

header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");

require "../core/core.php";

require "../core/class.fastjson.php";



$table = "sys_admin";



$out = array();

$out["status"] = "bad";

$out["tips"] = '';



$s = $_GET["s"];

$type = $_GET["type"];

if (!in_array($type, array("name", "realname"))) {

	echo FastJSON::convert($out);

	exit;

}



$line = $db->query("select * from $table where $type='$s' limit 1", 1);



$out["status"] = "ok";

$out["type"] = $type;



if ($line) {

	$out["tips"] = "请注意，名称“".$s."”有重复，请修改！";

} else {

	$out["tips"] = "";

}



echo FastJSON::convert($out);

?>