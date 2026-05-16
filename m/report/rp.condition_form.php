
<?php
$se = $_SESSION[$cfgSessionName]["rp_condition"];
?>

<script type="text/javascript">
function check_condition(f) {
	if (f.type.value == '') {
		msg_box("请选择“统计类型”"); f.type.focus(); return false;
	}
	if (f.btime.value == '') {
		msg_box("请设置“起始时间”"); f.btime.focus(); return false;
	}
	if (f.etime.value == '') {
		msg_box("请设置“结束时间”"); f.etime.focus(); return false;
	}
	byid("condition_submit").disabled = true;
	return true;
}
</script>

<div id="rp_condition_form">
	<form id="condition_form" method="GET" onsubmit="return check_condition(this)">
		<b>报表条件：</b>
		<select name="type" class="combo">
			<option value="" style="color:gray">-统计类型-</option>
			<?php echo list_option($type_arr, "_key_", "_value_", noe($_GET["type"], $se["type"], 2)); ?>
		</select>
		<select name="timetype" class="combo">
			<option value="" style="color:gray">-时间类型-</option>
			<?php echo list_option($timetype_arr, "_key_", "_value_", noe($_GET["timetype"], $se["timetype"])); ?>
		</select>
		起始时间：<input name="btime" id="btime" onClick="picker({el:'btime',dateFmt:'yyyy-MM-dd'})" readonly class="input" style="cursor:pointer; width:70px" value="<?php echo noe($_GET["btime"], $se["btime"], "2011-01-01"); ?>" title="点击选择起始时间">
		结束时间：<input name="etime" id="etime" onClick="picker({el:'etime',dateFmt:'yyyy-MM-dd'})" readonly class="input" style="cursor:pointer; width:70px" value="<?php echo noe($_GET["etime"], $se["etime"], date("Y-m-d")); ?>" title="点击选择结束时间">
		<select name="part" class="combo">
			<option value="" style="color:gray">-部门-</option>
			<?php echo list_option($part_arr, "_key_", "_value_", noe($_GET["part"], $se["part"])); ?>
		</select>
		<select name="media" class="combo">
			<option value="" style="color:gray">-媒体来源-</option>
			<?php echo list_option($media_arr, "_value_", "_value_", noe($_GET["media"], $se["media"])); ?>
		</select>
		<select name="come" class="combo">
			<option value="" style="color:gray">-到店状态-</option>
			<?php echo list_option($come_arr, "_key_", "_value_", noe($_GET["come"], $se["come"])); ?>
		</select>
		<input type="hidden" name="op" value="report" />
		<input type="submit" id="condition_submit" value="查询" class="button" />
	</form>
</div>

<?php if (empty($_GET["op"])) { ?>
<!-- 点击进入则自动开始查询 -->
<script type="text/javascript">
	byid("condition_form").submit();
	byid("condition_submit").disabled = true;
	msg_box("报表查询中，请稍候", 1);
</script>
<?php } ?>

