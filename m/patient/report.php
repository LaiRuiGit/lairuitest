<?php
/*
// - 功能说明 : 报表
// - 创建作者 : 小陈 
// - 创建时间 : 2013-05-25 15:45
*/
require "../../core/core.php";

if ($hid == 0) {
	msg_box("对不起，没有选择门店，不能执行该操作！", "back", 1, 5);
}

$table = "patient_".$hid;

// 门店名称:
$h_name = $db->query("select name from hospital where id=$hid limit 1", "1", "name");

// 时间定义:
$today_begin = mktime(0,0,0); //今天开始
$today_end = $today_begin + 24*3600 - 1; //今天结束
$yesterday_begin = $today_begin - 24*3600; //昨天开始
$yesterday_end = $today_begin - 1; //昨天结束
$thismonth_begin = mktime(0,0,0,date("m"),1); //本月开始
$thismonth_end = strtotime("+1 month", $thismonth_begin) - 1; //本月开始
$lastmonth_begin = strtotime("-1 month", $thismonth_begin); //上月开始
$lastmonth_end = $thismonth_begin - 1; //上月开始


$date_array = array(
	"今日" => array($today_begin, $today_end),
	"昨日" => array($yesterday_begin, $yesterday_end),
	"本月" => array($thismonth_begin, $thismonth_end),
	"上月" => array($lastmonth_begin, $lastmonth_end),
);

$tf = "order_date";

$kefu = array();
// 所有网络销售:
$kefu[2] = $db->query("select distinct author from $table where part_id=2 and $tf>=$lastmonth_begin and $tf<=$thismonth_end order by author", "", "author");

// 所有电话销售:
$kefu[3] = $db->query("select distinct author from $table where part_id=3 and $tf>=$lastmonth_begin and $tf<=$thismonth_end order by author", "", "author");

$data = array();
foreach ($kefu as $ptid => $kfs) {
	foreach ($kfs as $kf) {
		foreach ($date_array as $tname => $t) {
			$b = $t[0];
			$e = $t[1];

			// 预计总到店:
			$data[$ptid][$kf][$tname]["all"] = $d1 = $db->query("select count(*) as c from $table where part_id=$ptid and author='$kf' and $tf>=$b and $tf<=$e", 1, "c");
			// 已到:
			$data[$ptid][$kf][$tname]["come"] = $d2 = $db->query("select count(*) as c from $table where part_id=$ptid and author='$kf' and $tf>=$b and $tf<=$e and status=1", 1, "c");
			// 未到:
			$data[$ptid][$kf][$tname]["leave"] = $d1 - $d2;
		}
	}
}

?>
<html>
<head>
<title>数据报表</title>
<meta http-equiv="Content-Type" content="text/html;charset=gb2312">
<link href="/res/base.css" rel="stylesheet" type="text/css">
<script src="/res/base.js" language="javascript"></script>
<script src="/res/datejs/picker.js" language="javascript"></script>
<style>
.red {color:red !important; }

.report_tips {padding:20px 0 10px 0; text-align:center; font-size:14px; font-weight:bold;  }

.list {border:2px solid #43A75C !important; }
.head {}
.item {text-align:center; padding:6px 3px 4px 3px !important; }

.hl {border-left:2px solid #ADE0BA !important; }
.hr {border-right:2px solid #ADE0BA !important; }
.ht {border-top:2px solid #ADE0BA !important; }
.hb {border-bottom:2px solid #ADE0BA !important; }
</style>
</head>

<body>
<div class="report_tips"><?php echo $h_name; ?> 网络部 销售预约情况</div>

<table class="list" width="100%">
	<tr>
		<th class="head hb"></th>
		<th class="head hl hb red" colspan="3">今日</th>
		<th class="head hl hb red" colspan="3">昨日</th>
		<th class="head hl hb red" colspan="3">本月</th>
		<th class="head hl hb red" colspan="3">上月</th>
	</tr>

	<tr>
		<th class="head hb">销售</th>

		<th class="head hl hb">总共</th>
		<th class="head hb">已到</th>
		<th class="head hb">未到</th>

		<th class="head hl hb">总共</th>
		<th class="head hb">已到</th>
		<th class="head hb">未到</th>

		<th class="head hl hb">总共</th>
		<th class="head hb">已到</th>
		<th class="head hb">未到</th>

		<th class="head hl hb">总共</th>
		<th class="head hb">已到</th>
		<th class="head hb">未到</th>
	</tr>

<?php foreach ((array) $data[2] as $kf => $arr) { ?>

	<tr onmouseover="mi(this)" onmouseout="mo(this)">
		<td class="item"><?php echo $kf; ?></td>

		<td class="item hl"><?php echo $arr["今日"]["all"]; ?></td>
		<td class="item"><?php echo $arr["今日"]["come"]; ?></td>
		<td class="item"><?php echo $arr["今日"]["leave"]; ?></td>

		<td class="item hl"><?php echo $arr["昨日"]["all"]; ?></td>
		<td class="item"><?php echo $arr["昨日"]["come"]; ?></td>
		<td class="item"><?php echo $arr["昨日"]["leave"]; ?></td>

		<td class="item hl"><?php echo $arr["本月"]["all"]; ?></td>
		<td class="item"><?php echo $arr["本月"]["come"]; ?></td>
		<td class="item"><?php echo $arr["本月"]["leave"]; ?></td>

		<td class="item hl"><?php echo $arr["上月"]["all"]; ?></td>
		<td class="item"><?php echo $arr["上月"]["come"]; ?></td>
		<td class="item"><?php echo $arr["上月"]["leave"]; ?></td>
	</tr>

<?php } ?>

</table>


<!-- 电话组 -->

<div class="report_tips" style="margin-top:20px;"><?php echo $h_name; ?> 电话部 销售预约情况</div>

<table class="list" width="100%">
	<tr>
		<th class="head hb"></th>
		<th class="head hl hb red" colspan="3">今日</th>
		<th class="head hl hb red" colspan="3">昨日</th>
		<th class="head hl hb red" colspan="3">本月</th>
		<th class="head hl hb red" colspan="3">上月</th>
	</tr>

	<tr>
		<th class="head hb">销售</th>

		<th class="head hl hb">总共</th>
		<th class="head hb">已到</th>
		<th class="head hb">未到</th>

		<th class="head hl hb">总共</th>
		<th class="head hb">已到</th>
		<th class="head hb">未到</th>

		<th class="head hl hb">总共</th>
		<th class="head hb">已到</th>
		<th class="head hb">未到</th>

		<th class="head hl hb">总共</th>
		<th class="head hb">已到</th>
		<th class="head hb">未到</th>
	</tr>

<?php foreach ((array) $data[3] as $kf => $arr) { ?>

	<tr onmouseover="mi(this)" onmouseout="mo(this)">
		<td class="item"><?php echo $kf; ?></td>

		<td class="item hl"><?php echo $arr["今日"]["all"]; ?></td>
		<td class="item"><?php echo $arr["今日"]["come"]; ?></td>
		<td class="item"><?php echo $arr["今日"]["leave"]; ?></td>

		<td class="item hl"><?php echo $arr["昨日"]["all"]; ?></td>
		<td class="item"><?php echo $arr["昨日"]["come"]; ?></td>
		<td class="item"><?php echo $arr["昨日"]["leave"]; ?></td>

		<td class="item hl"><?php echo $arr["本月"]["all"]; ?></td>
		<td class="item"><?php echo $arr["本月"]["come"]; ?></td>
		<td class="item"><?php echo $arr["本月"]["leave"]; ?></td>

		<td class="item hl"><?php echo $arr["上月"]["all"]; ?></td>
		<td class="item"><?php echo $arr["上月"]["come"]; ?></td>
		<td class="item"><?php echo $arr["上月"]["leave"]; ?></td>
	</tr>

<?php } ?>

</table>

<br>
<br>
<b>备注：</b>上述数据，由顾客预约到店时间进行统计，而不是顾客资料的添加时间。<br>
<br>

</body>
</html>