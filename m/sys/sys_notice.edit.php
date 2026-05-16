<?php defined("ROOT") or exit("Error."); ?>
<html xmlns=http://www.w3.org/1999/xhtml>
<head>
<title><?php echo $title; ?></title>
<meta http-equiv="Content-Type" content="text/html;charset=gb2312">
<link href="/res/base.css" rel="stylesheet" type="text/css">
<script src="/res/base.js" language="javascript"></script>
<script src="/res/datejs/picker.js" language="javascript"></script>
<style>
#rec_part, #rec_user {margin-top:6px; }
.rec_user_b {width:100px; float:left; }
.rec_group_part {clear:both; margin:10px 0 5px 0; font-weight:bold; }
</style>
<script language="javascript">
function check_data(f) {
	return true;
}
function change_rec(v) {
	byid("rec_part").style.display = "none";
	byid("rec_user").style.display = "none";
	if (v == "part") {
		byid("rec_part").style.display = "block";
	} else if (v == "user") {
		byid("rec_user").style.display = "block";
	}
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
	<li class="d_item"></li>
</div>

<div class="space"></div>
<form method='POST' name="mainform" onsubmit="return check_data(this)">
<table width="100%" class="edit">
	<tr>
		<td colspan="2" class="head">通知详细资料：</td>
	</tr>
	<tr>
		<td class="left" style="color:red">接收者：</td>
		<td class="right">
			<select name="reader_type" class="combo" onchange="change_rec(this.value)">
				<option value="" style="color:silver;">-请选择接收者类型-</option>
<?php
$reader_type_arr = array(
	"all" => "所有人",
	"part" => "指定部门",
	"user" => "指定到人",
	"manager" => "所有管理人员"
);
echo list_option($reader_type_arr, "_key_", "_value_", $line["reader_type"]);
?>
			</select>
			<span class="intro">必须选择</span>

			<!-- 部门选择 -->
			<div id="rec_part" style="display:<?php echo ($line["reader_type"] == "part") ? "block" : "none"; ?>">
				<select name="part" class="combo">
					<option value="" style="color:silver;">-请选择部门-</option>
					<?php echo list_option($part->get_sub_part_list(0, 1), "_key_", "_value_", $line["part_ids"]); ?>
				</select>
			</div>

			<!-- 用户选择 -->
			<div id="rec_user" style="display:<?php echo ($line["reader_type"] == "user") ? "block" : "none"; ?>">
<?php
$cur_select = $line["uids"] ? explode(",", $line["uids"]) : array();
$part_id_name = $db->query("select id,name from sys_part", "id", "name");
$all_admin = $db->query("select id,realname,part_id from sys_admin where isshow=1 order by part_id,realname", "id");
$last_part = 0;
foreach ($all_admin as $a => $b) {
	if ($b["part_id"] != $last_part) {
		echo '<div class="rec_group_part">'.$part_id_name[$b["part_id"]].'</div>';
		$last_part = $b["part_id"];
	}
	echo '<div class="rec_user_b"><input type="checkbox" name="rec_user[]" value="'.$a.'" id="rec_user_'.$a.'"'.(in_array($a, $cur_select) ? " checked" : "").'><label for="rec_user_'.$a.'">'.$b["realname"].'</label></div>';
}
?>
				<div class="clear"></div>
			</div>

		</td>
	</tr>
	<tr>
		<td class="left" style="color:red">标题：</td>
		<td class="right"><input name="title" maxlength="40" class="input" value="<?php echo $line["title"]; ?>" style="width:40%;"> <span class="intro">通知标题必须填写</span></td>
	</tr>
	<tr>
		<td class="left" style="color:red">内容：</td>
		<td class="right"><textarea name="content" class="input" style="width:70%; height:300px; "><?php echo $line["content"]; ?></textarea></td>
	</tr>
	<tr>
		<td class="left">开始时间：</td>
		<td class="right"><input name="begin_date" id="begin_time" class="input" style="width:150px" value="<?php echo $line["begintime"] ? date("Y-m-d", $line["begintime"]) : ""; ?>"> <img src="/res/img/calendar.gif" id="order_date" onClick="picker({el:'begin_time',dateFmt:'yyyy-MM-dd'})" align="absmiddle" style="cursor:pointer" title="选择时间"></td>
	</tr>
	<tr>
		<td class="left">结束时间：</td>
		<td class="right"><input name="end_date" id="end_time" class="input" style="width:150px" value="<?php echo $line["endtime"] ? date("Y-m-d", $line["endtime"]) : ""; ?>"> <img src="/res/img/calendar.gif" id="order_date" onClick="picker({el:'end_time',dateFmt:'yyyy-MM-dd'})" align="absmiddle" style="cursor:pointer" title="选择时间"> </td>
	</tr>
</table>

<div class="button_line"><input type="submit" value="提交资料" class="submit"></div>
<input type="hidden" name="id" value="<?php echo $id; ?>">
<input type="hidden" name="op" value="<?php echo $op; ?>">
</form>

<div class="space"></div>

</body>
</html>