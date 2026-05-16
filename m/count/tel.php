<?php
// --------------------------------------------------------
// - 功能说明 : 电话
// - 创建作者 : 小陈 
// - 创建时间 : 2010-10-18
// --------------------------------------------------------
require "../../core/core.php";
$table = "count_tel";

// 所有可管理项目:
if ($debug_mode || in_array($uinfo["part_id"], array(9))) {
	$types = $db->query("select id,name from count_type where type='tel' order by sort desc, id asc", "id", "name");
} else {
	$hids = implode(",", $hospital_ids);
	$types = $db->query("select id,name from count_type where type='tel' and hid in ($hids) order by sort desc, id asc", "id", "name");
}
if (count($types) == 0) {
	exit("没有可以管理的项目");
}

$cur_type = $_SESSION["count_type_id_tel"];
if (!$cur_type) {
	$type_ids = array_keys($types);
	$cur_type = $_SESSION["count_type_id_tel"] = $type_ids[0];
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



// 操作的处理:
if ($op = $_REQUEST["op"]) {
	if ($op == "add") {
		include "tel.edit.php";
		exit;
	}

	if ($op == "edit") {
		include "tel.edit.php";
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
		$cur_type = $_SESSION["count_type_id_tel"] = intval($_GET["type_id"]);
	}

}


$type_detail = $db->query("select * from count_type where id=$cur_type limit 1", 1);
$kefu_list = $type_detail["kefu"] ? explode(",", $type_detail["kefu"]) : array();


// 该月结束:
$month_end = strtotime("+1 month", $date_time);

$b = date("Ymd", $date_time);
$e = date("Ymd", $month_end);


$cur_kefu = $_GET["kefu"];
if ($cur_kefu) {
	// 查询单个销售数据:
	$list = $db->query("select * from $table where type_id=$cur_type and kefu='$cur_kefu' and date>=$b and date<=$e order by date asc,kefu asc", "date");

	// 计算数据:
	foreach ($list as $k => $v) {
		// 咨询预约率:
		$list[$k]["per_1"] = @round($v["yuyue"] / $v["tel_all"] * 100, 2);
		// 预约就诊率:
		$list[$k]["per_2"] = @round($v["jiuzhen"] / $v["yuyue"] * 100, 2);
		// 咨询就诊率:
		$list[$k]["per_3"] = @round($v["jiuzhen"] / $v["tel_all"] * 100, 2);
		// 有效咨询率:
		$list[$k]["per_4"] = @round($v["tel_ok"] / $v["tel_all"] * 100, 2);
	}

	// 计算统计数据:
	$cal_field = explode(" ", "tel_all tel_ok yuyue jiuzhen wangluo zazhi laobao xinbao t400 t114 jieshao luguo qita per_1 per_2 per_3 per_4");
	// 处理:
	$sum_list = array();
	foreach ($list as $v) {
		foreach ($cal_field as $f) {
			$sum_list[$f] = floatval($sum_list[$f]) + $v[$f];
		}
	}
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
		$list[$k]["per_1"] = @round($v["yuyue"] / $v["tel_all"] * 100, 2);
		// 预约就诊率:
		$list[$k]["per_2"] = @round($v["jiuzhen"] / $v["yuyue"] * 100, 2);
		// 咨询就诊率:
		$list[$k]["per_3"] = @round($v["jiuzhen"] / $v["tel_all"] * 100, 2);
		// 有效咨询率:
		$list[$k]["per_4"] = @round($v["tel_ok"] / $v["tel_all"] * 100, 2);
	}

	// 计算统计数据:
	$cal_field = explode(" ", "tel_all tel_ok yuyue jiuzhen wangluo zazhi laobao xinbao t400 t114 jieshao luguo qita per_1 per_2 per_3 per_4");
	// 处理:
	$sum_list = array();
	foreach ($list as $v) {
		foreach ($cal_field as $f) {
			$sum_list[$f] = floatval($sum_list[$f]) + $v[$f];
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
			$s .= '<a href="#" onclick="'.$click.'">'.$v.'</a>';
		}
	}
	return $s;
}


// 页面开始 ------------------------
?>
<html>
<head>
<title>电话数据统计</title>
<meta http-equiv="Content-Type" content="text/html;charset=gb2312">
<link href="/res/base.css" rel="stylesheet" type="text/css">
<script src="/res/base.js" language="javascript"></script>
<style>
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
	</form>

</div>

<div class="main_title"><?php echo $type_detail["name"]; ?> - <?php echo date("Y-n", $date_time); ?> 电话统计数据</div>

<table width="100%" align="center" class="list">
	<tr style="position:relative; top:expression((this.offsetParent.scrollTop > 105) ? (this.offsetParent.scrollTop - 105) : 0);">
		<td class="head" align="center" width="60">日期</td>

		<td class="head" align="center">总电话</td>
		<td class="head" align="center" style="color:red">有效</td>
		<td class="head" align="center">预约</td>
		<td class="head" align="center" style="color:red">就诊</td>

		<td class="head" align="center">网络</td>
		<td class="head" align="center">杂志</td>
		<td class="head" align="center">劳报</td>
		<td class="head" align="center">新报</td>
		<td class="head" align="center">400</td>
		<td class="head" align="center">114</td>
		<td class="head" align="center">介绍</td>
		<td class="head" align="center">路过</td>
		<td class="head" align="center">其他</td>

		<td class="head" align="center">咨询预约率</td>
		<td class="head" align="center">预约就诊率</td>
		<td class="head" align="center">咨询就诊率</td>
		<td class="head" align="center">有效咨询率</td>

		<td class="head" align="center" width="60">操作</td>
	</tr>

<?php
foreach ($d_array as $i) {
	$cur_date = date("Ymd", strtotime(date("Y-m-", $date_time).$i." 0:0:0"));
	$li = $list[$cur_date];
	if (!is_array($li)) {
		$li = array();
	}

?>
	<tr>
		<td class="item" align="center"><?php echo date("n", $date_time); ?>月<?php echo $i; ?>日</td>
		<td class="item" align="center"><?php echo $li["tel_all"]; ?></td>
		<td class="item" align="center"><?php echo $li["tel_ok"]; ?></td>
		<td class="item" align="center"><?php echo $li["yuyue"]; ?></td>
		<td class="item" align="center"><?php echo $li["jiuzhen"]; ?></td>

		<td class="item" align="center"><?php echo $li["wangluo"]; ?></td>
		<td class="item" align="center"><?php echo $li["zazhi"]; ?></td>
		<td class="item" align="center"><?php echo $li["laobao"]; ?></td>
		<td class="item" align="center"><?php echo $li["xinbao"]; ?></td>
		<td class="item" align="center"><?php echo $li["t400"]; ?></td>
		<td class="item" align="center"><?php echo $li["t114"]; ?></td>
		<td class="item" align="center"><?php echo $li["jieshao"]; ?></td>
		<td class="item" align="center"><?php echo $li["luguo"]; ?></td>
		<td class="item" align="center"><?php echo $li["qita"]; ?></td>

		<td class="item" align="center"><?php echo floatval($li["per_1"]); ?>%</td>
		<td class="item" align="center"><?php echo floatval($li["per_2"]); ?>%</td>
		<td class="item" align="center"><?php echo floatval($li["per_3"]); ?>%</td>
		<td class="item" align="center"><?php echo floatval($li["per_4"]); ?>%</td>

		<td class="item" align="center">
<?php if ($cur_kefu && $can_edit_data) { ?>
			<?php if (!$li) { ?>
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
		<td class="item" align="center"><?php echo $sum_list["tel_all"]; ?></td>
		<td class="item" align="center"><?php echo $sum_list["tel_ok"]; ?></td>
		<td class="item" align="center"><?php echo $sum_list["yuyue"]; ?></td>
		<td class="item" align="center"><?php echo $sum_list["jiuzhen"]; ?></td>

		<td class="item" align="center"><?php echo $sum_list["wangluo"]; ?></td>
		<td class="item" align="center"><?php echo $sum_list["zazhi"]; ?></td>
		<td class="item" align="center"><?php echo $sum_list["laobao"]; ?></td>
		<td class="item" align="center"><?php echo $sum_list["xinbao"]; ?></td>
		<td class="item" align="center"><?php echo $sum_list["t400"]; ?></td>
		<td class="item" align="center"><?php echo $sum_list["t114"]; ?></td>
		<td class="item" align="center"><?php echo $sum_list["jieshao"]; ?></td>
		<td class="item" align="center"><?php echo $sum_list["luguo"]; ?></td>
		<td class="item" align="center"><?php echo $sum_list["qita"]; ?></td>

		<td class="item" align="center"><?php echo @round($sum_list["per_1"] / count($list), 2); ?>%</td>
		<td class="item" align="center"><?php echo @round($sum_list["per_2"] / count($list), 2); ?>%</td>
		<td class="item" align="center"><?php echo @round($sum_list["per_3"] / count($list), 2); ?>%</td>
		<td class="item" align="center"><?php echo @round($sum_list["per_4"] / count($list), 2); ?>%</td>

		<td class="item" align="center">
			-
		</td>
	</tr>
</table>

<br>
<br>

</body>
</html>