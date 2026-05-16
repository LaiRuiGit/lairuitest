<?php defined("ROOT") or exit("Error."); ?>
<html xmlns=http://www.w3.org/1999/xhtml>
<head>
<title>查看权限明细</title>
<meta http-equiv="Content-Type" content="text/html;charset=gb2312">
<link href="/res/base.css" rel="stylesheet" type="text/css">
<script src="/res/base.js" language="javascript"></script>
</head>

<body>
<!-- 头部 begin -->
<div class="headers">
	<div class="headers_title"><span class="tips">权限明细</span></div>
	<div class="headers_oprate"><input type="button" value="返回" onclick="history.back()" class="button"></div>
</div>
<!-- 头部 end -->

<div class="space"></div>

<table width="100%" class="edit">
	<tr>
		<td class="head" colspan="2">详细资料</td>
	</tr>
	<tr>
		<td class="left">名称：</td>
		<td class="right"><b><?php echo $line["name"]; ?></b></td>
	</tr>
	<tr>
		<td class="left">权限：</td>
		<td class="right"><?php echo $power->show($line["menu"]); ?></td>
	</tr>
	<tr>
		<td class="left">真实姓名：</td>
		<td class="right"><?php echo $line["author"]; ?></td>
	</tr>
	<tr>
		<td class="left">管理站点：</td>
		<td class="right"><?php echo date("Y-m-d H:i:s", $line["addtime"]); ?></td>
	</tr>
</table>

<div class="button_line"><button onclick="history.back()" class="buttonb">返回</button></div>
<div class="space"></div>
</body>
</html>