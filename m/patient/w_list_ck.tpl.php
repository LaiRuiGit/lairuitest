<?php
// --------------------------------------------------------
// - 功能 : view 模板
// - 作者 : 小陈 
// - 时间 : 2011-04-23 16:57
// --------------------------------------------------------

// 检查是否包含调用
if (!$username) {
	exit("This page can not directly opened from browser...");
}
?>
<html>
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
	<div class="headers_oprate"><button onClick="history.back()" class="button">返回</button></div>
</div>
<!-- 头部 end -->

<div class="space"></div>

<table width="100%" class="edit" style="border:0;">
	<tr>
		<th class="head" width="25%" style="border:2px solid #BADCDC;">基本资料</th>
		<th class="head" width="25%" style="border:2px solid #BADCDC;">聊天记录</th>
		<th class="head" width="25%" style="border:2px solid #BADCDC;">回访记录</th>
		<th class="head" width="25%" style="border:2px solid #BADCDC;">备注资料</th>
	</tr>

	<tr>
		<td valign="top" style="border:2px solid #BADCDC; padding:0;">
			<table width="100%" class="edit" style="border:0;">
			<?php foreach ($viewdata[1] as $k => $v) { ?>
				<tr>
					<td class="left" style="width:40%"><?php echo $v[0]; ?>：</td>
					<td class="right" style="width:60%"><?php echo $v[1]; ?></td>
				</tr>
			<?php } ?>
			</table>
		</td>

		<td valign="top" style="border:2px solid #BADCDC; padding:0;">
			<table width="100%" class="edit" style="border:0;">
				<tr>
					<td class="right"><?php echo $viewdata[2][0][1] ? $viewdata[2][0][1] : "<center style='color:gray'>(暂无资料)</center>" ?></td>
				</tr>
			</table>
		</td>

		<td valign="top" style="border:2px solid #BADCDC; padding:0;">
			<table width="100%" class="edit" style="border:0;">
				<tr>
					<td class="right"><?php echo $viewdata[3][0][1] ? $viewdata[3][0][1] : "<center style='color:gray'>(暂无资料)</center>" ?></td>
				</tr>
			</table>
		</td>

		<td valign="top" style="border:2px solid #BADCDC; padding:0;">
			<table width="100%" class="edit" style="border:0;">
				<tr>
					<td class="right"><?php echo $viewdata[4][0][1] ? $viewdata[4][0][1] : "<center style='color:gray'>(暂无资料)</center>" ?></td>
				</tr>
			</table>
		</td>
	</tr>
</table>

<div class="button_line">
	<input type="button" class="submit" onClick="history.back()" value="返回">
</div>
</body>
</html>