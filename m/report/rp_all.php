<?php
/*
// 说明: 总体报表
// 作者: 小陈 
// 时间: 2011-11-23
*/
require "../../core/core.php";

// 报表核心定义:
include "rp.core.php";

$part = $_GET["part"];
$where = '';
$tips = "总数据";
if ($part == "web") {
	$where = "part_id=2 and ";
	$tips = "网络";
} else if ($part == "tel") {
	$where = "part_id=3 and ";
	$tips = "电话";
}
?>
<html>
<head>
<title>总体报表</title>
<meta http-equiv="Content-Type" content="text/html;charset=gb2312">
<link href="/res/base.css" rel="stylesheet" type="text/css">
<script src="/res/base.js" language="javascript"></script>
<style>
body {margin-top:10px; }
.head, .head a {font-family:"微软雅黑","Verdana"; }
.item {font-family:"Tahoma"; padding:8px 3px 6px 3px !important; }
.footer_op_left {font-family:"Tahoma"; }
.date_tips {padding:10px 0 10px 5px; font-weight:bold; }
form {display:inline; }
</style>
</head>

<body>

<div style="text-align:center;">
	<b><?php echo $h_name; ?></b>&nbsp;&nbsp;
	<form method="GET">
		<input type="hidden" name="part" value="all">
		<input type="submit" value="总数据" class="buttonb" <?php if ($part == "" || $part == "all") echo 'style="color:red;font-weight:bold;"'; ?> />
	</form>

	<form method="GET">
		<input type="hidden" name="part" value="web">
		<input type="submit" value="网络" class="button" <?php if ($part == "web") echo 'style="color:red;font-weight:bold;"'; ?> />
	</form>

	<form method="GET">
		<input type="hidden" name="part" value="tel">
		<input type="submit" value="电话" class="button" <?php if ($part == "tel") echo 'style="color:red;font-weight:bold;"'; ?> />
	</form>
    <a id="outExcel" download="数据报表.xls" style="color: red;font-weight: bold;border: 1px solid #ccc;padding: 0.25em 0.5em;background: linear-gradient(#fff, #F2F2F9);margin-left: 10px">导出表格</a>
</div>

<!-- 按年查看 -->
<!-- 今年，去年，前年的记录 -->
<?php
$y = intval(date("Y"));
$time_arr = array(
	"今年" => array(strtotime($y."-01-01 00:00:00"), strtotime($y."-12-31 00:00:00")),
	"去年" => array(strtotime(($y-1)."-01-01 00:00:00"), strtotime(($y-1)."-12-31 00:00:00")),
	"前年" => array(strtotime(($y-2)."-01-01 00:00:00"), strtotime(($y-2)."-12-31 00:00:00")),
);

// 计算统计数据:
$data = array();
foreach ($time_arr as $k => $v) {
	$data[$k]["预约"] = $db->query("select count(*) as c from $table where $where addtime>=".$v[0]." and addtime<=".$v[1]." ", 1, "c");
	$data[$k]["预到"] = $db->query("select count(*) as c from $table where $where order_date>=".$v[0]." and order_date<=".$v[1]." ", 1, "c");
	$data[$k]["已到"] = $db->query("select count(*) as c from $table where $where status=1 and order_date>=".$v[0]." and order_date<=".$v[1]." ", 1, "c");
	$data[$k]["未到"] = $data[$k]["预到"] - $data[$k]["已到"];
}
?>
<div class="date_tips">按年份输出(最近3年<?php echo $tips; ?>)：</div>
<table width="100%" align="center" class="list table2excel">
	<tr>
		<td class="head" align="center" width="10%">年份</td>
		<td class="head" align="center" width="18%">预约</td>
		<td class="head" align="center" width="18%">预到</td>
		<td class="head" align="center" width="18%">已到</td>
		<td class="head" align="center" width="18%">未到</td>
		<td class="head" align="center" width="18%">到店比例</td>
	</tr>

<?php foreach ($time_arr as $k => $v) { ?>
	<tr>
		<td class="item" align="center"><?php echo $k; ?></td>
		<td class="item" align="center"><?php echo $data[$k]["预约"]; ?></td>
		<td class="item" align="center"><?php echo $data[$k]["预到"]; ?></td>
		<td class="item" align="center"><?php echo $data[$k]["已到"]; ?></td>
		<td class="item" align="center"><?php echo $data[$k]["未到"]; ?></td>
		<td class="item" align="center"><?php echo @round(100 * $data[$k]["已到"] / $data[$k]["预到"], 1)."%"; ?></td>
	</tr>
<?php } ?>
</table>

<br>


<!-- 按月份查看 -->
<!-- 最近x个月的记录 -->
<?php

$time_arr = array();
for ($i = 0; $i < 12; $i++) {
	$m = strtotime("-".$i." month");
	$time_arr[date("Y-m", $m)] = array(strtotime(date("Y-m-01 00:00:00", $m)), strtotime(date("Y-m-31 23:59:59", $m)));
}

// 计算统计数据:
$data = array();
foreach ($time_arr as $k => $v) {
	$data[$k]["预约"] = $db->query("select count(*) as c from $table where $where addtime>=".$v[0]." and addtime<=".$v[1]." ", 1, "c");
	$data[$k]["预到"] = $db->query("select count(*) as c from $table where $where order_date>=".$v[0]." and order_date<=".$v[1]." ", 1, "c");
	$data[$k]["已到"] = $db->query("select count(*) as c from $table where $where status=1 and order_date>=".$v[0]." and order_date<=".$v[1]." ", 1, "c");
	$data[$k]["未到"] = $data[$k]["预到"] - $data[$k]["已到"];
}
?>
<div class="date_tips">按月份输出(最近12个月<?php echo $tips; ?>)：</div>
<table width="100%" align="center" class="list table2excel">
	<tr>
		<td class="head" align="center" width="10%">月份</td>
		<td class="head" align="center" width="18%">预约</td>
		<td class="head" align="center" width="18%">预到</td>
		<td class="head" align="center" width="18%">已到</td>
		<td class="head" align="center" width="18%">未到</td>
		<td class="head" align="center" width="18%">到店比例</td>
	</tr>

<?php foreach ($time_arr as $k => $v) { ?>
	<tr>
		<td class="item" align="center"><?php echo $k; ?></td>
		<td class="item" align="center"><?php echo $data[$k]["预约"]; ?></td>
		<td class="item" align="center"><?php echo $data[$k]["预到"]; ?></td>
		<td class="item" align="center"><?php echo $data[$k]["已到"]; ?></td>
		<td class="item" align="center"><?php echo $data[$k]["未到"]; ?></td>
		<td class="item" align="center"><?php echo @round(100 * $data[$k]["已到"] / $data[$k]["预到"], 1)."%"; ?></td>
	</tr>
<?php } ?>
</table>
<?php

?>

<script>
    // 使用outerHTML属性获取整个table元素的HTML代码（包括<table>标签），然后包装成一个完整的HTML文档，设置charset为urf-8以防止中文乱码
    var html = "<html><head><meta charset='utf-8' /></head><body>" + document.getElementsByClassName("list")[0].outerHTML + document.getElementsByClassName("list")[1].outerHTML + "</body></html>";

    // 实例化一个Blob对象，其构造函数的第一个参数是包含文件内容的数组，第二个参数是包含文件类型属性的对象
    var blob = new Blob([html], { type: "application/vnd.ms-excel" });
    // console.log(a.href);
    var a = document.getElementsByClassName("list")[0];
    a.href = URL.createObjectURL(blob);
    var aObj = document.getElementById("outExcel");
    aObj.href = a.href;
    a.download = "数据报表.xls";
</script>
</body>
</html>