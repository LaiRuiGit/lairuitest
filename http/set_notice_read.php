<?php

/*

// 说明: 设置通知已读

// 作者: 小陈 

// 时间: 2010-09-17

*/

header("Content-Type:text/html;charset=GB2312");

header("Cache-Control: no-cache, must-revalidate");

header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");

require "../core/core.php";



$id = intval($_GET["id"]);



$old = $db->query("select * from sys_notice where id=$id limit 1", 1);

$new_uids = trim(trim($old["read_uids"]), ",");



// 检测是否已经存在:

if ($new_uids && substr_count(",".$new_uids.",", ",".$uid.",") > 0) {

	// 已存在, 无需再添加

} else {

	// 将UID加入已读行列:

	if ($new_uids) {

		$new_uids .= ",";

	}

	$new_uids .= $uid;

	$db->query("update sys_notice set read_uids='".$new_uids."' where id=$id limit 1", 1);

}



echo "done..";



?>