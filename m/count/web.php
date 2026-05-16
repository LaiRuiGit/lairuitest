<?php
// --------------------------------------------------------
// - 功能说明 : 网络
// - 创建作者 : 小陈 
// - 创建时间 : 2010-10-19
// --------------------------------------------------------
require "../../core/core.php";
$table = "count_web";

// 所有可管理项目:
if ($debug_mode || in_array($uinfo["part_id"], array(9))) {
	$types = $db->query("select id,name from count_type where type='web' order by sort desc,id asc", "id", "name");
} else {
	$hids = implode(",", $hospital_ids);
	$types = $db->query("select id,name from count_type where type='web' and hid in ($hids) order by sort desc,id asc", "id", "name");
}
if (count($types) == 0) {
	exit("没有可以管理的项目");
}

$cur_type = $_SESSION["count_type_id_web"];
if (!$cur_type) {
	$type_ids = array_keys($types);
	$cur_type = $_SESSION["count_type_id_web"] = $type_ids[0];
}


if ($_GET["date"] && strlen($_GET["date"]) == 6) {
	$date = $_GET["date"];
} else {
	$date = date("Ym"); //本月
	$_GET["date"] = $date;
}
$date_time = strtotime(substr($date,0,4)."-".substr($date,4,2)."-01 0:0:0");

// 可用 年,月 数组
$y_array = $m_array = $d_array = array();
for ($i = date("Y"); $i >= (date("Y") - 2); $i--) $y_array[] = $i;
for ($i = 1; $i <= 12; $i++) $m_array[] = $i;
for ($i = 1; $i <= 31; $i++) {
	if ($i <= 28 || checkdate(date("n", $date_time), $i, date("Y", $date_time))) {
		$d_array[] = $i;
	}
}


$type_detail = $db->query("select * from count_type where id=$cur_type limit 1", 1);


// 操作的处理:
if ($op = $_REQUEST["op"]) {
	if ($op == "add") {
		include "web.edit.php";
		exit;
	}

	if ($op == "edit") {
		include "web.edit.php";
		exit;
	}

	if ($op == "log") {
		include "web.log.php";
		exit;
	}

	if ($op == "delete") {
		$ids = explode(",", $_GET["id"]);
		$del_ok = $del_bad = 0; $op_data = array();
		foreach ($ids as $opid) {
			if (($opid = intval($opid)) > 0) {
				$tmp_data = $db->query_first("select * from $table where id='$opid' limit 1");
				if ($db->query("delete from $table where id='$opid' limit 1")) {
					$del_ok++;
					$op_data[] = $tmp_data;
				} else {
					$del_bad++;
				}
			}
		}

		if ($del_ok > 0) {
			$log->add("delete", "删除数据", serialize($op_data));
		}

		if ($del_bad > 0) {
			msg_box("删除成功 $del_ok 条资料，删除失败 $del_bad 条资料。", "back", 1);
		} else {
			msg_box("删除成功", "back", 1);
		}
	}

	if ($op == "change_type") {
		$cur_type = $_SESSION["count_type_id_web"] = intval($_GET["type_id"]);
		$type_detail = $db->query("select * from count_type where id=$cur_type limit 1", 1);
	}

}

// 销售:
$kefu_list = $type_detail["kefu"] ? explode(",", $type_detail["kefu"]) : array();


// 该月结束:
$month_end = strtotime("+1 month", $date_time) - 1;

$bt = $b = date("Ymd", $date_time);
$et = $e = date("Ymd", $month_end);


$cur_kefu = $_GET["kefu"];
if ($cur_kefu) {
	// 查询单个销售数据:
	$list = $db->query("select * from $table where type_id=$cur_type and kefu='$cur_kefu' and date>=$b and date<=$e order by date asc,kefu asc", "date");

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
		}
	}

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

} else {
	//查询总门店汇总数据:
	$tmp_list = $db->query("select * from $table where type_id=$cur_type and date>=$b and date<=$e order by date asc,kefu asc");

	// 计算汇总:
	$list = $dt_count = array();
	foreach ($tmp_list as $v) {
		$dt = $v["date"];
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
		}
	}


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


// 当日总数据:
$day_count = $db->query("select date,click_all,zero_talk from count_day where type_id='$cur_type' and date>=$bt and date<=$et", "date");

foreach ($day_count as $v) {
	$sum_list["click_all"] += $v["click_all"];
	$sum_list["zero_talk"] += $v["zero_talk"];
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
			$s .= '<a href="#" onclick="'.$click.'">'.$v.'</a>';
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

.main_title {margin:0 auto; padding-top:24px; padding-bottom:5px; text-align:left; font-weight:bold; font-size:12px; font-family:"宋体"; }

.item {padding:8px 3px 6px 3px !important; }
.list .head {padding-top:6px; padding-bottom:4px; background-color:#B4DADA; }
</style>

<script language="javascript">
function update_date(type, o) {
	byid("date_"+type).value = parseInt(o.innerHTML, 10);

	var a = parseInt(byid("date_1").value, 10);
	var b = parseInt(byid("date_2").value, 10);

	var s = a + '' + (b<10 ? "0" : "") + b;

	byid("date").value = s;
	byid("ch_date").submit();
	return false;
}

function ajax_submit(o) {
	var to = o.action;

	var s = new Array();
	var el = o.getElementsByTagName("input");
	for (var i = 0; i < el.length; i ++) {
		var r = el[i];
		if (r.name != '') {
			s[i] = r.name+"="+encodeURIComponent(r.value);
		}
	}
	var s = s.join("&");

	//alert(s);

	var xm = new ajax();
	xm.connect(to, "GET", s, ajax_submit_do);
}

function ajax_submit_do(o) {
	var out = ajax_out(o);
	if (out["tips"]) {
		alert(out["tips"]);
	}
}

function change_to_edit(o) {
	if (o.flag == "0") {
		// eidt mode:
		var cur = o.innerText;
		if (cur.substring(0, 1) == '(') {
			cur = '';
		}
		var s = '<input name="data" class="input" style="width:100%;" value="'+cur+'" onfocus="bw()" onblur="ajax_submit(this.form); change_to_view(this)">';
		o.innerHTML = s;
		o.flag = "1";
		o.getElementsByTagName("input")[0].focus();
	} else {
		//
	}
}

function change_to_view(o) {
	var cur = o.value;
	if (cur == '') {
		cur = '(添加)';
	}
	o.parentNode.flag = '0';
	o.parentNode.innerHTML = cur;
}

function bw() {
	var e = event.srcElement;
	var r =e.createTextRange();
	r.moveStart('character',e.value.length);
	r.collapse(true);
	r.select();
}


</script>
</head>

<body>
<div style="margin:10px 0 0 0px;">
	<div id="date_tips">请选择日期：</div>
	<form id="ch_date" method="GET">
		<span class="ch_date_a">年：<?php echo my_show($y_array, date("Y", $date_time), "return update_date(1,this)"); ?>&nbsp;&nbsp;&nbsp;</span>
		<span class="ch_date_a">月：<?php echo my_show($m_array, date("m", $date_time), "return update_date(2,this)"); ?>&nbsp;&nbsp;&nbsp;</span>

		<input type="hidden" id="date_1" value="<?php echo date("Y", $date_time); ?>">
		<input type="hidden" id="date_2" value="<?php echo date("n", $date_time); ?>">
		<input type="hidden" name="date" id="date" value="">
		<input type="hidden" name="kefu" value="<?php echo $kefu; ?>">
	</form>
	<div class="clear"></div>
</div>

<div style="margin:10px 0 0 0px;">
	<div id="date_tips">门店项目：</div>
	<form method="GET" style="margin-left:30px;">
	<select name="type_id" class="combo" onchange="this.form.submit()">
		<option value="" style="color:gray">-请选择项目-</option>
		<?php echo list_option($types, "_key_", "_value_", $cur_type); ?>
	</select>
	<input type="hidden" name="op" value="change_type">
	</form>&nbsp;&nbsp;&nbsp;

	<b>销售：</b>
	<form method="GET">
	<select name="kefu" class="combo" onchange="this.form.submit()">
		<option value="" style="color:gray">-整个门店-</option>
		<?php echo list_option($kefu_list, "_value_", "_value_", $_GET["kefu"]); ?>
	</select>
	<input type="hidden" name="date" value="<?php echo $date; ?>">
	</form>&nbsp;&nbsp;&nbsp;

	<button onclick="location='web_compare.php'" class="buttonb" title="查看销售数据对比">数据对比</button>&nbsp;&nbsp;
	<button onclick="location='web_compare_week.php?month=<?php echo date("Y-m", $date_time); ?>'" class="buttonb" title="查看周数据对比">周对比</button>&nbsp;&nbsp;
	<button onclick="location='web_report.php'" class="buttonb" title="查看统计数据">统计数据</button>&nbsp;&nbsp;

</div>

<div class="main_title"><?php echo $type_detail["name"]; ?> - <?php echo date("Y-n", $date_time); ?> 网络统计数据
<?php if ($debug_mode || $username == "admin") { ?>
	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href="?op=log" target="_blank">查看/搜索日志</a>
<?php } ?>
</div>

<table width="100%" align="center" class="list">
	<col width="70"></col>

	<col></col>
	<col></col>
	<col></col>
	<col></col>
	<col></col>
	<col></col>
	<col></col>
	<col></col>

	<col></col>
	<col></col>
	<col></col>
	<col></col>
	<col></col>
	<col></col>
	<col></col>
	<col></col>
	<col></col>

	<col></col>
	<col></col>
	<col></col>
	<col></col>
	<col></col>

	<col width="40"></col>

	<tr style="position:relative; top:expression((this.offsetParent.scrollTop > 105) ? (this.offsetParent.scrollTop - 105) : 0);">
		<td class="head" align="center" width="60">日期</td>

		<td class="head" align="center" style="color:red">系统总点击</td>
		<td class="head" align="center" style="color:red">总点击</td>
		<td class="head" align="center">本地</td>
		<td class="head" align="center">外地</td>
		<td class="head" align="center" style="color:red">总有效</td>
		<td class="head" align="center">本地</td>
		<td class="head" align="center">外地</td>
		<td class="head" align="center" style="color:red">零对话</td>

		<td class="head" align="center" style="color:red">当天约</td>
		<td class="head" align="center">本地</td>
		<td class="head" align="center">外地</td>
		<td class="head" align="center" style="color:red">预计到店</td>
		<td class="head" align="center">本地</td>
		<td class="head" align="center">外地</td>
		<td class="head" align="center" style="color:red">实际到店</td>
		<td class="head" align="center">本地</td>
		<td class="head" align="center">外地</td>

		<td class="head" align="center" style="color:red">咨询预约率</td>
		<td class="head" align="center" style="color:red">预约就诊率</td>
		<td class="head" align="center" style="color:red">咨询就诊率</td>
		<td class="head" align="center" style="color:red">有效咨询率</td>
		<td class="head" align="center" style="color:red">有效预约率</td>

		<td class="head" align="center">操作</td>
	</tr>

<?php
foreach ($d_array as $i) {
	$cur_date = date("Ymd", strtotime(date("Y-m-", $date_time).$i." 0:0:0"));
	$li = $list[$cur_date];
	if (!is_array($li)) {
		$li = array();
	}
	$mode = "add";
	if (!empty($li)) {
		$mode = "edit";
	}
	$li["kf_click"] = $day_count[$cur_date]["click_all"];
	$li["zero_talk"] = $day_count[$cur_date]["zero_talk"];

?>
	<tr>
		<td class="item" align="center"><?php echo date("n", $date_time); ?>月<?php echo $i; ?>日</td>
		<td class="item" align="center" style="color:red">
			<form action="ajax_update.php" onsubmit="ajax_submit(this); return false;">
				<span id="kf_click_<?php echo $i; ?>" style="cursor:pointer;" onclick="change_to_edit(this)" flag="0"><?php echo $li["kf_click"] ? $li["kf_click"] : '(添加)'; ?></a></span>
				<input type="hidden" name="date" value="<?php echo $cur_date; ?>">
				<input type="hidden" name="type" value="click_all">
			</form>
		</td>
		<td class="item" align="center" style="color:red"><?php echo $li["click"]; ?></td>
		<td class="item" align="center"><?php echo $li["click_local"]; ?></td>
		<td class="item" align="center"><?php echo $li["click_other"]; ?></td>
		<td class="item" align="center" style="color:red"><?php echo $li["ok_click"]; ?></td>
		<td class="item" align="center"><?php echo $li["ok_click_local"]; ?></td>
		<td class="item" align="center"><?php echo $li["ok_click_other"]; ?></td>

		<td class="item" align="center" style="color:red">
			<form action="ajax_update.php" onsubmit="ajax_submit(this); return false;">
				<span id="zero_talk_<?php echo $i; ?>" style="cursor:pointer;" onclick="change_to_edit(this)" flag="0"><?php echo $li["zero_talk"] ? $li["zero_talk"] : '(添加)'; ?></a></span>
				<input type="hidden" name="date" value="<?php echo $cur_date; ?>">
				<input type="hidden" name="type" value="zero_talk">
			</form>
		</td>

		<td class="item" align="center" style="color:red"><?php echo $li["talk"]; ?></td>
		<td class="item" align="center"><?php echo $li["talk_local"]; ?></td>
		<td class="item" align="center"><?php echo $li["talk_other"]; ?></td>
		<td class="item" align="center" style="color:red"><?php echo $li["orders"]; ?></td>
		<td class="item" align="center"><?php echo $li["order_local"]; ?></td>
		<td class="item" align="center"><?php echo $li["order_other"]; ?></td>
		<td class="item" align="center" style="color:red"><?php echo $li["come"]; ?></td>
		<td class="item" align="center"><?php echo $li["come_local"]; ?></td>
		<td class="item" align="center"><?php echo $li["come_other"]; ?></td>

		<td class="item" align="center" style="color:red"><?php echo floatval($li["per_1"]); ?>%</td>
		<td class="item" align="center" style="color:red"><?php echo floatval($li["per_2"]); ?>%</td>
		<td class="item" align="center" style="color:red"><?php echo floatval($li["per_3"]); ?>%</td>
		<td class="item" align="center" style="color:red"><?php echo floatval($li["per_4"]); ?>%</td>
		<td class="item" align="center" style="color:red"><?php echo floatval($li["per_5"]); ?>%</td>

		<td class="item" align="center">
<?php if ($cur_kefu && $can_edit_data) { ?>
			<?php if ($mode == "add") { ?>
			<a href="?op=add&kefu=<?php echo urlencode($cur_kefu); ?>&date=<?php echo date("Y-m-", $date_time).$i; ?>">添加</a>
			<?php } else { ?>
			<a href="?op=edit&kefu=<?php echo urlencode($cur_kefu); ?>&date=<?php echo date("Y-m-", $date_time).$i; ?>">修改</a>
			<?php } ?>
<?php } ?>
		</td>
	</tr>

<?php } ?>

	<tr>
		<td colspan="30" class="tips">数据汇总</td>

	<tr>
		<td class="item" align="center">汇总</td>
		<td class="item" align="center" style="color:red"><?php echo $sum_list["click_all"]; ?></td>
		<td class="item" align="center" style="color:red"><?php echo $sum_list["click"]; ?></td>
		<td class="item" align="center"><?php echo $sum_list["click_local"]; ?></td>
		<td class="item" align="center"><?php echo $sum_list["click_other"]; ?></td>
		<td class="item" align="center" style="color:red"><?php echo $sum_list["ok_click"]; ?></td>
		<td class="item" align="center"><?php echo $sum_list["ok_click_local"]; ?></td>
		<td class="item" align="center"><?php echo $sum_list["ok_click_other"]; ?></td>
		<td class="item" align="center" style="color:red"><?php echo $sum_list["zero_talk"]; ?></td>

		<td class="item" align="center" style="color:red"><?php echo $sum_list["talk"]; ?></td>
		<td class="item" align="center"><?php echo $sum_list["talk_local"]; ?></td>
		<td class="item" align="center"><?php echo $sum_list["talk_other"]; ?></td>
		<td class="item" align="center" style="color:red"><?php echo $sum_list["orders"]; ?></td>
		<td class="item" align="center"><?php echo $sum_list["order_local"]; ?></td>
		<td class="item" align="center"><?php echo $sum_list["order_other"]; ?></td>
		<td class="item" align="center" style="color:red"><?php echo $sum_list["come"]; ?></td>
		<td class="item" align="center"><?php echo $sum_list["come_local"]; ?></td>
		<td class="item" align="center"><?php echo $sum_list["come_other"]; ?></td>

		<td class="item" align="center" style="color:red"><?php echo @floatval($sum_list["per_1"]); ?>%</td>
		<td class="item" align="center" style="color:red"><?php echo @floatval($sum_list["per_2"]); ?>%</td>
		<td class="item" align="center" style="color:red"><?php echo @floatval($sum_list["per_3"]); ?>%</td>
		<td class="item" align="center" style="color:red"><?php echo @floatval($sum_list["per_4"]); ?>%</td>
		<td class="item" align="center" style="color:red"><?php echo @floatval($sum_list["per_5"]); ?>%</td>

		<td class="item" align="center">
			-
		</td>
	</tr>
</table>

<br>
<br>

</body>
</html>