<?php
/*
// - 功能说明 : 修改我的资料
// - 创建作者 : zhuwenya (zhuwenya@126.com)
// - 创建时间 : 2007-07-19 09:59
*/
require "../../core/core.php";
$table = "sys_admin";

if (!$uid) {
	exit_html("不能编辑资料...");
}

$uline = $db->query("select * from $table where id='$uid'", 1);

if ($_POST) {

	$detail = array();
	if ($uline["detail"]) {
		$detail = @unserialize($uline["detail"]);
	}

	$detail["电话"] = $_POST["电话"];
	$detail["手机"] = $_POST["手机"];
	$detail["QQ"] = $_POST["QQ"];
	$detail["电子邮箱"] = $_POST["电子邮箱"];
	$detail["个人简介"] = $_POST["个人简介"];

	$s = serialize($detail);

	$sql = "update $table set detail='$s' where id='$uid' limit 1";

	if ($db->query($sql)) {
		msg_box("个人资料修改成功", "back", 1, 2);
	} else {
		msg_box("资料提交失败，系统繁忙，请稍后再试", "back", 1, 5);
	}
}


if ($uline && $uline["detail"]) {
	$tm = @unserialize($uline["detail"]);
	$uline = array_merge($uline, $tm);
} else {
	//exit_html("无此资料...");
}

$title = "修改我的资料";
?>
<html xmlns=http://www.w3.org/1999/xhtml>
<head>
<title><?php echo $title; ?></title>
<meta http-equiv="Content-Type" content="text/html;charset=gb2312">
<link href="/res/base.css" rel="stylesheet" type="text/css">
<script src="/res/base.js" language="javascript"></script>
</head>

<body>
<!-- 头部 begin -->
<div class="headers">
	<div class="headers_title"><span class="tips"><?php echo $title; ?></span></div>
	<div class="headers_oprate"><input type="button" value="返回" onClick="history.back()" class="button"></div>
</div>
<!-- 头部 end -->

<div class="space"></div>

<div class="description">
	<div class="d_title">修改提示：</div>
	<li class="d_item">由于需要保持后台安全性和一致性，帐户名称一旦确定就不允许修改</li>
	<li class="d_item">为了方便别人和您取得联系，建议认真如实填写您的个人资料和联系方式</li>
	<li class="d_item">个人资料除真实姓名以外，未被授权“后台管理”->“管理员管理”->“查看”权限的人将不能查看</li>
</div>

<div class="space"></div>

<form name="mainform" action="" method="POST">
<table width="100%" class="edit">
	<tr>
		<td colspan="2" class="head">修改我的资料：</td>
	</tr>
	<tr>
		<td class="left">登录名：</td>
		<td class="right"><b><?php echo $uline["name"]; ?></b></td>
	</tr>
	<tr>
		<td class="left"><font color='red'>真实姓名：</font></td>
		<td class="right"><input name="realname" value="<?php echo $uline["realname"]; ?>" class="input" style="width:120px" disabled="true"> <span class="intro">真实姓名不能修改</span></td>
	</tr>
	<tr>
		<td class="left">电话：</td>
		<td class="right"><input name="电话" value="<?php echo $uline["电话"]; ?>" class="input" style="width:180px"></td>
	</tr>
	<tr>
		<td class="left">手机：</td>
		<td class="right"><input name="手机" value="<?php echo $uline["手机"]; ?>" class="input" style="width:120px"></td>
	</tr>
	<tr>
		<td class="left">房子面积：</td>
		<td class="right"><input name="QQ" value="<?php echo $uline["QQ"]; ?>" class="input" style="width:120px"></td>
	</tr>
	<tr>
		<td class="left">电子邮箱：</td>
		<td class="right"><input name="电子邮箱" value="<?php echo $uline["电子邮箱"]; ?>" class="input" style="width:250px"></td>
	</tr>
	<tr>
		<td class="left">个人简介：</td>
		<td class="right"><textarea class="input" name="个人简介" style="width:400px;height:80px"><?php echo $uline["个人简介"]; ?></textarea></td>
	</tr>
</table>

<div class="button_line"><input type="submit" class="submit" value="提交资料"></div>
<input type="hidden" name="back_url" value="<?php echo $_GET["back_url"]; ?>">
</form>

<div class="space"></div>
</body>
</html>