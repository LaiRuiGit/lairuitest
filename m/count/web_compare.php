<?php
// --------------------------------------------------------
// - 功能说明 : 网络 数据对比
// - 创建作者 : 小陈 
// - 创建时间 : 2010-10-27 09:46
// --------------------------------------------------------
require "../../core/core.php";
$table = "count_web";

// 所有可管理项目:
if ($debug_mode || in_array($uinfo["part_id"], array(9))) {
	$types = $db->query("select id,name from count_type where type='web' order by sort desc, id asc", "id", "name");
} else {
	$hids = implode(",", $hospital_ids);
	$types = $db->query("select id,name from count_type where type='web' and hid in ($hids) order by sort desc, id asc", "id", "name");
}
if (count($types) == 0) {
	exit("没有可以管理的项目");
}

$cur_type = $_SESSION["count_type_id_web"];
if (!$cur_type) {
	$type_ids = array_keys($types);
	$cur_type = $_SESSION["count_type_id_web"] = $type_ids[0];
}

// 操作的处理:
if ($op = $_REQUEST["op"]) {
	if ($op == "change_type") {
		$cur_type = $_SESSION["count_type_id_web"] = intval($_GET["type_id"]);
	}
}

$type_detail = $db->query("select * from count_type where id=$cur_type limit 1", 1);
$cur_kefu_list = $type_detail["kefu"] ? explode(",", $type_detail["kefu"]) : array();
$kefu_list = array();


// 初始值为本月:
if ($_GET["btime"] == '') {
	$_GET["btime"] = date("Y-m-d", mktime(0,0,0,date("m"), 1));
}
if ($_GET["etime"] == '') {
	$_GET["etime"] = date("Y-m-d", strtotime("+1 month", strtotime($_GET["btime"]." 0:0:0")) - 1);
}


// 处理数据:
if ($cur_type && $_GET["btime"] && $_GET["etime"]) {

	// 时间段:
	$btime = strtotime($_GET["btime"]." 0:0:0");
	$etime = strtotime($_GET["etime"]." 23:59:59");

	$b = date("Ymd", $btime);
	$e = date("Ymd", $etime);

	//查询总门店汇总数据:
	$tmp_list = $db->query("select * from $table where type_id=$cur_type and date>=$b and date<=$e order by kefu asc,date asc");

	// 计算汇总:
	$list = $dt_count = array();
	foreach ($tmp_list as $v) {
		$dt = $v["kefu"];
		if (!in_array($dt, $kefu_list)) {
			$kefu_list[] = $dt;
		}
		$dt_count[$dt] += 1;
		foreach ($v as $a => $b) {
			if ($b && is_numeric($b)) {
				$list[$dt][$a] = floatval($list[$dt][$a]) + $b;
			}
		}
	}

	// 计算数据:
	foreach ($list as $k => $v) {
		// 咨询预约率:
		$list[$k]["per_1"] = @round($v["talk"] / $v["click"] * 100, 2);
		// 预约就诊率:
		$list[$k]["per_2"] = @round($v["come"] / $v["orders"] * 100, 2);
		// 咨询就诊率:
		$list[$k]["per_3"] = @round($v["come"] / $v["click"] * 100, 2);
		// 有效咨询率:
		$list[$k]["per_4"] = @round($v["ok_click"] / $v["click"] * 100, 2);
		// 有效预约率:
		$list[$k]["per_5"] = @round($v["talk"] / $v["ok_click"] * 100, 2);
	}

	// 计算统计数据:
	$cal_field = explode(" ", "click click_local click_other zero_talk ok_click ok_click_local ok_click_other talk talk_local talk_other orders order_local order_other come come_local come_other");
	// 处理:
	$sum_list = array();
	foreach ($list as $v) {
		foreach ($cal_field as $f) {
			$sum_list[$f] = floatval($sum_list[$f]) + $v[$f];

			// 咨询预约率:
			$sum_list["per_1"] = @round($sum_list["talk"] / $sum_list["click"] * 100, 2);
			// 预约就诊率:
			$sum_list["per_2"] = @round($sum_list["come"] / $sum_list["orders"] * 100, 2);
			// 咨询就诊率:
			$sum_list["per_3"] = @round($sum_list["come"] / $sum_list["click"] * 100, 2);
			// 有效咨询率:
			$sum_list["per_4"] = @round($sum_list["ok_click"] / $sum_list["click"] * 100, 2);
			// 有效预约率:
			$sum_list["per_5"] = @round($sum_list["talk"] / $sum_list["ok_click"] * 100, 2);
		}
	}
}


// 是否能添加或修改数据:
$can_edit_data = 0;
if ($debug_mode || in_array($uinfo["part_id"], array(9)) || in_array($uid, explode(",", $type_detail["uids"]))) {
	$can_edit_data = 1;
}


/*
// ------------------ 函数 -------------------
*/
function my_show($arr, $default_value='', $click='') {
	$s = '';
	foreach ($arr as $v) {
		if ($v == $default_value) {
			$s .= '<b>'.$v.'</b>';
		} else {
			$s .= '<a href="javascript:void(0);" onclick="'.$click.'">'.$v.'</a>';
		}
	}
	return $s;
}


// 页面开始 ------------------------
?>
<html>
<head>
<title>网络数据统计</title>
<meta http-equiv="Content-Type" content="text/html;charset=gb2312">
<link href="/res/base.css" rel="stylesheet" type="text/css">
<script src="/res/base.js" language="javascript"></script>
<script src="/res/datejs/picker.js" language="javascript"></script>
<style>
* {font-family:"Tahoma"; }
body {padding:5px 8px; }
form {display:inline; }
#date_tips {float:left; font-weight:bold; padding-top:1px; }
#ch_date {float:left; margin-left:20px; }
.site_name {display:block; padding:4px 0px;}
.site_name, .site_name a {font-family:"Arial", "Tahoma"; }
.ch_date_a b, .ch_date_a a {font-family:"Arial"; }
.ch_date_a b {border:0px; padding:1px 5px 1px 5px; color:red; }
.ch_date_a a {border:0px; padding:1px 5px 1px 5px; }
.ch_date_a a:hover {border:1px solid silver; padding:0px 4px 0px 4px; }
.ch_date_b {padding-top:8px; text-align:left; width:80%; color:silver; }
.ch_date_b a {padding:0 3px; }

.main_title {margin:0 auto; padding-top:30px; padding-bottom:15px; text-align:center; font-weight:bold; font-size:12px; font-family:"宋体"; }

.item {padding:8px 3px 6px 3px !important; }

.rate_tips {padding:30px 0 0 30px; line-height:24px; }
</style>

<script language="javascript">
function update_date(type, o) {
	byid("date_"+type).value = parseInt(o.innerHTML, 10);

	var a = parseInt(byid("date_1").value, 10);
	var b = parseInt(byid("date_2").value, 10);

	var s = a + '' + (b<10 ? "0" : "") + b;

	byid("date").value = s;
	byid("ch_date").submit();
}

function hgo(dir, o) {
	var obj = byid("type_id");
	if (dir == "up") {
		if (obj.selectedIndex > 1) {
			obj.selectedIndex = obj.selectedIndex - 1;
			obj.onchange();
			o.disabled = true;
		} else {
			parent.msg_box("已经是最前了", 3);
		}
	}
	if (dir == "down") {
		if (obj.selectedIndex < obj.options.length-1) {
			obj.selectedIndex = obj.selectedIndex + 1;
			obj.onchange();
			o.disabled = true;
		} else {
			parent.msg_box("已经是最后一个了", 3);
		}
	}
}
</script>
</head>

<body>
<div style="margin:10px 0 0 0px;">
	<div id="date_tips">门店项目：</div>
	<form method="GET" style="margin-left:30px;">
		<select name="type_id" id="type_id" class="combo" onchange="this.form.submit()">
			<option value="" style="color:gray">-请选择项目-</option>
			<?php echo list_option($types, "_key_", "_value_", $cur_type); ?>
		</select>&nbsp;
		<button class="button" onclick="hgo('up',this);">上</button>&nbsp;
		<button class="button" onclick="hgo('down',this);">下</button>
		<input type="hidden" name="btime" value="<?php echo $_GET["btime"]; ?>">
		<input type="hidden" name="etime" value="<?php echo $_GET["etime"]; ?>">
		<input type="hidden" name="op" value="change_type">
	</form>&nbsp;&nbsp;&nbsp;

	<b>时间段：</b>
	<form method="GET">
		<input name="btime" id="begin_time" class="input" style="width:100px" value="<?php echo $_GET["btime"]; ?>"> <img src="/res/img/calendar.gif" id="order_date" onClick="picker({el:'begin_time',dateFmt:'yyyy-MM-dd'})" align="absmiddle" style="cursor:pointer" title="选择时间">&nbsp;&nbsp;到&nbsp;&nbsp;
		<input name="etime" id="end_time" class="input" style="width:100px" value="<?php echo $_GET["etime"]; ?>"> <img src="/res/img/calendar.gif" id="order_date" onClick="picker({el:'end_time',dateFmt:'yyyy-MM-dd'})" align="absmiddle" style="cursor:pointer" title="选择时间">&nbsp;&nbsp;
		<input type="submit" class="button" value="确定">
	</form>
</div>


<?php if ($cur_type && $_GET["btime"] && $_GET["etime"]) { ?>

<div class="main_title"><?php echo $type_detail["name"]; ?> <?php echo $_GET["btime"]; ?> 到 <?php echo $_GET["etime"]; ?> 销售数据对比</div>

<table width="100%" align="center" class="list">
	<tr>
		<td class="head" align="center" width="60">销售</td>
		<td class="head" align="center" style="color:red">总点击</td>
		<td class="head" align="center">本地</td>
		<td class="head" align="center">外地</td>
		<td class="head" align="center" style="color:red">总有效</td>
		<td class="head" align="center">本地</td>
		<td class="head" align="center">外地</td>

		<td class="head" align="center" style="color:red">预约</td>
		<td class="head" align="center" style="color:red">预计到店</td>
		<td class="head" align="center" style="color:red">实际到店</td>

		<td class="head" align="center" style="color:red">咨询预约率</td>
		<td class="head" align="center" style="color:red">预约就诊率</td>
		<td class="head" align="center" style="color:red">咨询就诊率</td>
		<td class="head" align="center" style="color:red">有效咨询率</td>
		<td class="head" align="center" style="color:red">有效预约率</td>
	</tr>

<?php
foreach ($kefu_list as $i) {
	$li = $list[$i];
	if (!is_array($li)) {
		$li = array();
	}
	$show_name = $i;
	if (!in_array($i, $cur_kefu_list)) {
		$show_name = ' <font color="red" style="text-decoration:;" title="此销售不在当前设置的名单中">'.$i.' *</font>';
	}

?>
	<tr>
		<td class="item" align="center"><?php echo $show_name; ?></td>
		<td class="item" align="center" style="color:red"><?php echo $li["click"]; ?></td>
		<td class="item" align="center"><?php echo $li["click_local"]; ?></td>
		<td class="item" align="center"><?php echo $li["click_other"]; ?></td>
		<td class="item" align="center" style="color:red"><?php echo $li["ok_click"]; ?></td>
		<td class="item" align="center"><?php echo $li["ok_click_local"]; ?></td>
		<td class="item" align="center"><?php echo $li["ok_click_other"]; ?></td>
		<td class="item" align="center" style="color:red"><?php echo $li["talk"]; ?></td>
		<td class="item" align="center" style="color:red"><?php echo $li["orders"]; ?></td>
		<td class="item" align="center" style="color:red"><?php echo $li["come"]; ?></td>

		<td class="item" align="center" style="color:red"><?php echo floatval($li["per_1"]); ?>%</td>
		<td class="item" align="center" style="color:red"><?php echo floatval($li["per_2"]); ?>%</td>
		<td class="item" align="center" style="color:red"><?php echo floatval($li["per_3"]); ?>%</td>
		<td class="item" align="center" style="color:red"><?php echo floatval($li["per_4"]); ?>%</td>
		<td class="item" align="center" style="color:red"><?php echo floatval($li["per_5"]); ?>%</td>
	</tr>

<?php } ?>

	<tr>
		<td colspan="30" class="tips">数据汇总</td>

	<tr>
		<td class="item" align="center">汇总</td>
		<td class="item" align="center" style="color:red"><?php echo $sum_list["click"]; ?></td>
		<td class="item" align="center"><?php echo $sum_list["click_local"]; ?></td>
		<td class="item" align="center"><?php echo $sum_list["click_other"]; ?></td>
		<td class="item" align="center" style="color:red"><?php echo $sum_list["ok_click"]; ?></td>
		<td class="item" align="center"><?php echo $sum_list["ok_click_local"]; ?></td>
		<td class="item" align="center"><?php echo $sum_list["ok_click_other"]; ?></td>

		<td class="item" align="center" style="color:red"><?php echo $sum_list["talk"]; ?></td>
		<td class="item" align="center" style="color:red"><?php echo $sum_list["orders"]; ?></td>
		<td class="item" align="center" style="color:red"><?php echo $sum_list["come"]; ?></td>

		<td class="item" align="center" style="color:red"><?php echo $sum_list["per_1"]; ?>%</td>
		<td class="item" align="center" style="color:red"><?php echo $sum_list["per_2"]; ?>%</td>
		<td class="item" align="center" style="color:red"><?php echo $sum_list["per_3"]; ?>%</td>
		<td class="item" align="center" style="color:red"><?php echo $sum_list["per_4"]; ?>%</td>
		<td class="item" align="center" style="color:red"><?php echo $sum_list["per_5"]; ?>%</td>

	</tr>
</table>

<div class="rate_tips">
咨询预约率 = 预约人数 / 总点击<br>
预约就诊率 = 实际到店人数 / 预计到店人数<br>
咨询就诊率 = 实际到店人数 / 总点击<br>
有效咨询率 = 有效点击 / 总点击<br>
有效预约率 = 预约人数 / 有效点击<br>
</div>

<?php } ?>

<br>
<br>


</body>
</html>
