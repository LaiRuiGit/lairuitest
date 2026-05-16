<?php
defined("ROOT") or exit;

if ($_POST) {

	// 本数组已经加载（定义）过，其他值不会丢失
	$ym = $_POST["ym"];
	if (strlen($ym) != 6) {
		exit_html("月份不正确");
	}
	$line["config"][$ym]["jiangli_jishu"] = intval($_POST["jiangli_jishu"]);
	$line["config"][$ym]["jiangli_zhibiao"] = intval($_POST["jiangli_zhibiao"]);
	$line["config"][$ym]["jiuzhen_mubiao"] = intval($_POST["jiuzhen_mubiao"]);

	$config = serialize($line["config"]);

	$sql = "update $table set config='$config' where id='$id' limit 1";
	if ($db->query($sql)) {
		msg_box("提交成功", "?op=set_zhibiao&id=".$id."&ym=".$ym, 1);
	} else {
		msg_box("资料提交失败，系统繁忙，请稍后再试。", "back", 1, 5);
	}
}

$ym_array = array();
for ($i = 3; $i >= -6; $i--) {
	$d = strtotime(($i>=0 ? "+" : "").$i." month");
	$ym_array[date("Ym", $d)] = date("Y年m月", $d);
}

if (!isset($_GET["ym"])) {
	$_GET["ym"] = date("Ym");
}

?>
<html>
<head>
<title><?php echo $pinfo["title"]." - "."修改指标数据"; ?></title>
<meta http-equiv="Content-Type" content="text/html;charset=gb2312">
<link href="/res/base.css" rel="stylesheet" type="text/css">
<script src="/res/base.js" language="javascript"></script>
<script language="javascript">
function Check() {
	var oForm = document.mainform;
	return true;
}
</script>
</head>

<body>
<!-- 头部 begin -->
<div class="headers">
	<div class="headers_title"><span class="tips"><?php echo $pinfo["title"]." - "."修改指标数据"; ?></span></div>
	<div class="headers_oprate"><button onclick="location='?op=edit&id=<?php echo $id; ?>'" class="button">返回</button></div>
</div>
<!-- 头部 end -->

<div class="space"></div>

<!-- <div class="description">
	<div class="d_title">提示：</div>
	<div class="d_item">1.选择日期，填写指标数据，提交即可</div>
</div>
<div class="space"></div> -->

<form method="GET">
<div style="margin:40px 0 20px 0px; ">
	<b>　　　月份：</b>
	<select class="combo" name="ym" onchange="this.form.submit()">
		<option value="" style="color:gray">-请选择-</option>
		<?php echo list_option($ym_array, "_key_", "_value_", $_GET["ym"]); ?>
	</select> &nbsp;
	<span class="intro">请先选择要设置的月份，选择后，本页面将重新加载</span>
</div>
<input type="hidden" name="id" value="<?php echo $id; ?>">
<input type="hidden" name="op" value="<?php echo $op; ?>">
</form>

<?php if ($_GET["ym"]) { ?>
<form name="mainform" action="" method="POST" onsubmit="return Check()">
<table width="100%" class="edit">
	<tr>
		<td colspan="2" class="head">数据设置</td>
	</tr>

	<tr>
		<td class="left">奖励基数：</td>
		<td class="right"><input name="jiangli_jishu" value="<?php echo $line["config"][$_GET["ym"]]["jiangli_jishu"]; ?>" class="input" style="width:80px"> <span class="intro">请填写奖励基数</span></td>
	</tr>
	<tr>
		<td class="left">奖励指标：</td>
		<td class="right"><input name="jiangli_zhibiao" value="<?php echo $line["config"][$_GET["ym"]]["jiangli_zhibiao"]; ?>" class="input" style="width:80px"> <span class="intro">请填写奖励指标</span></td>
	</tr>
	<tr>
		<td class="left">目标就诊：</td>
		<td class="right"><input name="jiuzhen_mubiao" value="<?php echo $line["config"][$_GET["ym"]]["jiuzhen_mubiao"]; ?>" class="input" style="width:80px"> <span class="intro">请填写目标就诊</span></td>
	</tr>
</table>
<input type="hidden" name="id" value="<?php echo $id; ?>">
<input type="hidden" name="op" value="<?php echo $op; ?>">
<input type="hidden" name="ym" value="<?php echo $_GET["ym"]; ?>">
<input type="hidden" name="linkinfo" value="<?php echo $linkinfo; ?>">
<div class="button_line"><input type="submit" class="submit" value="提交资料"></div>
</form>
<?php } ?>

</body>
</html>