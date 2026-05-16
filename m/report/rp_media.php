<?php
/*
// 说明: 报表
// 作者: 小陈 
// 时间: 2011-11-24
*/
require "../../core/core.php";

// 报表核心定义:
include "rp.core.php";

$tongji_tips = " - 媒体来源统计 - ".$type_tips;
?>
<html>
<head>
<title>媒体来源报表</title>
<meta http-equiv="Content-Type" content="text/html;charset=gb2312">
<link href="/res/base.css" rel="stylesheet" type="text/css">
<script src="/res/base.js" language="javascript"></script>
<script src="/res/datejs/picker.js" language="javascript"></script>
<style>
body {margin-top:6px; }
#rp_condition_form {text-align:center; }
.head, .head a {font-family:"微软雅黑","Verdana"; }
.item {font-family:"Tahoma"; padding:8px 3px 6px 3px !important; }
.footer_op_left {font-family:"Tahoma"; }
.date_tips {padding:15px 0 15px 0px; font-weight:bold; text-align:center; font-size:15px; font-family:"微软雅黑","Verdana"; }
form {display:inline; }
.red {color:red !important;  }
</style>
</head>

<body>

<?php include_once "rp.condition_form.php"; ?>

<?php if ($_GET["op"] == "report") { ?>
<?php

// $media_arr 在 rp.core.php 中定义,为系统字典

if (in_array($type, array(1,2,3,4))) {
	// 计算统计数据:
	$data = array();
	foreach ($final_dt_arr as $k => $v) {
		$data[$k]["总"] = $db->query("select count(*) as c from $table where $where {$timetype}>=".$v[0]." and {$timetype}<=".$v[1]." ", 1, "c");

		foreach ($media_arr as $me) {
			$data[$k][$me] = $db->query("select count(*) as c from $table where $where media_from='{$me}' and {$timetype}>=".$v[0]." and {$timetype}<=".$v[1]." ", 1, "c");
		}
	}
} else if ($type == 5) {
	$arr = array();
	$arr["总"] = $db->query("select from_unixtime({$timetype},'%k') as sd,count(from_unixtime({$timetype},'%k')) as c from $table where $where {$timetype}>=".$tb." and {$timetype}<=".$te." group by from_unixtime({$timetype},'%k')", "sd", "c");

	foreach ($media_arr as $me) {
		$arr[$me] = $db->query("select from_unixtime({$timetype},'%k') as sd,count(from_unixtime({$timetype},'%k')) as c from $table where media_from='{$me}' and $where {$timetype}>=".$tb." and {$timetype}<=".$te." group by from_unixtime({$timetype},'%k')", "sd", "c");
	}

	$data = array();
	foreach ($final_dt_arr as $k => $v) {
		$data[$k]["总"] = intval($arr["总"][$v]);
		foreach ($media_arr as $me) {
			$data[$k][$me] = intval($arr[$me][$v]);
		}
	}
}


?>
<div class="date_tips"><?php echo $h_name.$tongji_tips.$tips; ?></div>
<table width="100%" align="center" class="list">
	<tr>
		<td class="head" align="center">时间</td>
		<td class="head red" align="center">总计</td>
<?php foreach ($media_arr as $me) { ?>
		<td class="head" align="center"><?php echo $me; ?></td>
<?php } ?>
	</tr>

<?php foreach ($final_dt_arr as $k => $v) { ?>
	<tr>
		<td class="item" align="center"><?php echo $k; ?></td>
		<td class="item" align="center"><?php echo $data[$k]["总"]; ?></td>
<?php   foreach ($media_arr as $me) { ?>
		<td class="item" align="center"><?php echo $data[$k][$me]; ?></td>
<?php   } ?>
	</tr>
<?php } ?>
</table>

<br>
<?php } ?>


</body>
</html>