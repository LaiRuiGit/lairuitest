<?php

/*

// 说明: 提交错误信息

// 作者: 小陈 

// 时间: 2013-10-13

*/

require "../../core/core.php";



if ($_POST) {

	$r = array();

	$r["detail"] = $_POST["detail"];

	$r["username"] = $username;

	$r["addtime"] = time();



	$sqldata = $db->sqljoin($r);

	$db->query("insert into sys_bugs set $sqldata");



	echo '<script>parent.load_box(0); parent.msg_box("内容提交成功！");</script>';

	exit;

}



?>

<html xmlns=http://www.w3.org/1999/xhtml>

<head>

<title>提交错误和改进建议</title>

<meta http-equiv="Content-Type" content="text/html;charset=gb2312">

<link href="/res/base.css" rel="stylesheet" type="text/css">

<script src="/res/base.js" language="javascript"></script>

<style>

#error_title {font-weight:bold; margin-top:5px;}

#error_box {margin-top:5px; }

#error_tips {color:silver; margin-top:5px; }

</style>

<script language="JavaScript">

function check_data(f) {

	if (f.detail.value == '') {

		alert("请输入详情后再提交！"); f.detail.focus(); return false;

	}

	return true;

}

</script>

</head>



<body>

<form method="POST" enctype="multipart/form-data" onsubmit="return check_data(this)">

<div id="error_title">请输入错误或建议：</div>

<div id="error_box"><textarea name="detail" id="detail" class="input" style="width:100%; height:200px;"></textarea></div>

<div id="error_tips">您描述的越详细，越有利于我们解决问题。</div>

<div class="button_line"><input type="submit" class="submit" value="提交资料"></div>

</form>

</body>

</html>