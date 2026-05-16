<?php defined("ROOT") or exit("Error."); ?>
<html xmlns=http://www.w3.org/1999/xhtml>
<head>
<title><?php echo $title; ?></title>
<meta http-equiv="Content-Type" content="text/html;charset=gb2312">
<link href="/res/base.css" rel="stylesheet" type="text/css">
<script src="/res/base.js" language="javascript"></script>
<script language="javascript">
function Check() {
	var oForm = document.mainform;
	if (oForm.ch_name.value == "") {
		alert("请输入“权限名称”！");
		oForm.ch_name.focus();
		return false;
	}
	return true;
}
</script>
</head>

<body>
<!-- 头部 begin -->
<div class="headers">
	<div class="headers_title"><span class="tips"><?php echo $title; ?></span></div>
	<div class="headers_oprate"><input type="button" value="返回" onclick="history.back()" class="button"></div>
</div>
<!-- 头部 end -->

<div class="space"></div>

<div class="description">
	<div class="d_title">提示：</div>
	<li class="d_item">请务必谨慎分配“系统管理”一项的权限，不熟悉系统的人员对系统随意设置将使其出现严重问题而不可用</li>
	<li class="d_item">“系统日志”这一栏目，牵涉系统重要操作数据，也需谨慎分配权限</li>
</div>

<div class="space"></div>

<form name="mainform" action="" method="POST" onsubmit="return Check()">
<table width="100%" class="edit">
	<tr>
		<td colspan="2" class="head">权限设置</td>
	</tr>
	<tr>
		<td class="left">权限名称：</td>
		<td class="right"><input name="ch_name" value="<?php echo $cline["name"]; ?>" class="input" size="30" style="width:200px"> <span class="intro">权限名称必须填写</span></td>
	</tr>
	<tr>
		<td class="left">权限明细：</td>
		<td class="right"><?php echo $power->show_power_table($usermenu, $cline["menu"]); ?></td>
	</tr>
</table>
<input type="hidden" name="id" value="<?php echo intval($id); ?>">
<input type="hidden" name="back_url" value="<?php echo $_GET["back_url"]; ?>">
<input type="hidden" name="op" value="<?php echo $op; ?>">

<div class="button_line"><input type="submit" class="submit" value="提交资料"></div>
</form>

<div class="space"></div>
</body>
</html>