<?php defined("ROOT") or exit("Error."); ?>
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
	<div class="headers_oprate"><input type="button" value="返回" onclick="history.back()" class="button"></div>
</div>
<!-- 头部 end -->

<div class="space"></div>

<table width="100%" class="edit">
	<tr>
		<td colspan="2" class="head">日志详细记录：</td>
	</tr>
<?php foreach ($viewdata as $k => $v) { ?>
	<tr>
		<td class="left"><?php echo $v[0]; ?>：</td>
		<td class="right"><?php echo $v[1]; ?></td>
	</tr>
<?php } ?>
</table>

<div class="button_line">
	<input type="button" class="submit" onclick="return history.back()" value="返回">
</div>

<div class="space"></div>
</body>
</html>