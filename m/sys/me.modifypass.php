<?php
/*
// - 功能说明 : 修改当前登录用户密码
// - 创建作者 : zhuwenya (zhuwenya@126.com)
// - 创建时间 : 2007-01-06 20:52
*/
require "../../core/core.php";
$table = "sys_admin";

if (!$uid) {
	exit_html("不能修改密码...");
}

if ($_POST) {
	$OldPass = $_POST["oldpass"];
	$NewPass = $_POST["newpass"];
	$NewPass1 = $_POST["newpass1"];

	if ($NewPass != $NewPass1) {
		msg_box("两次密码输入不一致，请重新输入！", "back", 1);
	}
	if (strlen($NewPass) < 3) {
		msg_box("新密码长度至少要设定三位及以上，请重新设定。", "back", 1);
	}

	$EnPass = md5($NewPass);

	if ($old = $db->query_first("select * from $table where name='$username' limit 1")) {
		if (md5($OldPass) == $old["pass"]) {
			if ($db->query("update $table set pass='$EnPass' where name='$username' limit 1")) {
				msg_box("密码修改成功，下次登录请使用新密码！", "back", 1);
			} else {
				msg_box("密码修改失败！请稍后再试", "back", 1, 5);
			}
		} else {
			msg_box("原密码输入不正确，请重新输入原密码！", "back", 1);
		}
	}
}

?>
<html xmlns=http://www.w3.org/1999/xhtml>
<head>
<title>修改密码</title>
<meta http-equiv="Content-Type" content="text/html;charset=gb2312">
<link href="/res/base.css" rel="stylesheet" type="text/css">
<script src="/res/base.js" language="javascript"></script>
<script language="javascript">
function check_data(f) {
	if (f.oldpass.value == "") {
		msg_box("请输入您的当前密码！",2); f.oldpass.focus(); return false;
	}
	if (f.newpass.value == "") {
		msg_box("请输入您的新密码！",2); f.newpass.focus(); return false;
	}
	if (f.newpass.value.length < 3) {
		msg_box("新密码长度至少要有3位！",2); f.newpass.focus(); return false;
	}
	if (f.newpass1.value == "") {
		msg_box("请再次输入您的新密码！",2); f.newpass1.focus(); return false;
	}
	if (f.newpass.value != f.newpass1.value) {
		msg_box("两次密码输入不一致！",2); f.newpass1.focus(); return false;
	}
	return true;
}
</script>
</head>

<body>
<!-- 头部 begin -->
<div class="headers">
	<div class="headers_title"><span class="tips">修改密码</span></div>
	<div class="headers_oprate"><input type="button" value="返回" onclick="history.back()" class="button"></div>
</div>
<!-- 头部 end -->

<div class="space"></div>
<div class="description">
	<div class="d_title">修改提示：</div>
	<li class="d_item">必须输入正确的原密码，和两次至少6位的新密码</li>
	<li class="d_item">成功修改后的新密码即刻生效，在任何需要使用您的个人密码的地方都应使用此新密码</li>
</div>

<div class="space"></div>
<form method='POST' onsubmit="return check_data(this);">
<table width="100%" class="edit">
	<tr>
		<td colspan="2" class="head">修改登录密码：</td>
	</tr>
	<tr>
		<td class="left">原密码：</td>
		<td class="right"><input name='oldpass' type='password' style='width:120' class='input'> <span class="intro">您的当前密码</span></td>
	</tr>
	<tr>
		<td class="left">新密码：</td>
		<td class="right"><input name='newpass' type='password' style='width:120' class='input'> <span class="intro">新的密码，至少6位</span></td>
	</tr>
	<tr>
		<td class="left">确认新密码：</td>
		<td class="right"><input name='newpass1' type='password' style='width:120' class='input'> <span class="intro">再输入一次以确认新密码</span></td>
	</tr>
</table>

<div class="button_line"><input type="submit" value="修改密码" class="submit"></div>
</form>

<div class="space"></div>
</body>
</html>