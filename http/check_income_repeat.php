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

$table = "income";



if (!$hid) exit("ERROR");



$out = array();

$out["status"] = "ok";



$date = intval($_GET["date"]);

$doctor_id = intval($_GET["doctor_id"]);

$fee_type = intval($_GET["fee_type"]);

if (strlen($date) == 8 && $doctor_id > 0) {

	$line = $db->query("select * from $table where hid=$hid and fee_type=$fee_type and doctor_id=$doctor_id && date=$date limit 1", 1);

	if ($line["id"] > 0) {

		//$out["tips"] = "该医生的数据已添加，点击“确定”载入修改；点击“取消”，添加其他医生的数据。";

		$out["id"] = $line["id"];

	} else {

		//$out["tips"] = "";

		$out["id"] = 0;

	}

}

echo FastJSON::convert($out);

?>