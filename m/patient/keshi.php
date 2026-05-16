<?php

/* --------------------------------------------------------

// 说明: 按门店统计数据

// 作者: 小陈 

// 时间: 2010-04-06 14:56

// ----------------------------------------------------- */

require "../../core/core.php";

$title = "门店统计数据";



$media_from_array = array();

$media_from_array[] = "网络";

$media_from_array[] = "电话";

$media_from_array[] = "网挂";

$media_from_array[] = "市场";

$media_from_array[] = "地铁";

$media_from_array[] = "朋友介绍";

$media_from_array[] = "路牌";

$media_from_array[] = "电视";

$media_from_array[] = "电台";

$media_from_array[] = "短信";

$media_from_array[] = "路过";

$media_from_array[] = "车身";

$media_from_array[] = "广告";

$media_from_array[] = "报纸";

$media_from_array[] = "杂志";

$media_from_array[] = "其他";



//$status_array = array(0 => "所有", 1 => "已到", 2 => "未到", 3=> '预约未定');

$status_array = array(0=>"待预约",1=>"已签约", 2=>"已放弃",3=>"已量尺", 4=>"已报价");


if ($user_hospital_id == 0) {

	exit_html("对不起，没有选择门店，不能执行该操作！");

}

$table = "patient_".$user_hospital_id;



// 时间界限定义:

$time_today_begin = mktime(0,0,0);

$time_today_end = $time_today_begin + 24*3600;

$time_yesterday_begin = $time_today_begin - 24*3600;

$time_this_month_begin = mktime(0,0,0,date("m"),1);

$time_this_month_end = strtotime("+1 month", $time_this_month_begin);

$time_last_month_begin = strtotime("-1 month", $time_this_month_begin);





// 所有门店:

$keshi = $db->query("select id,name from depart where hospital_id=$user_hospital_id", "id", "name");



// 时间定义

$arr_time = array(

	"今日" => array($time_today_begin, $time_today_end),

	"昨日" => array($time_yesterday_begin, $time_today_begin),

	"本月" => array($time_this_month_begin, $time_this_month_end)

);



$where = $tips = array();



// 媒体来源:

if ($_GET["media"]) {

	$where[] = "media_from='".$_GET["media"]."'";

	$tips[] = $_GET["media"];

}



// 是否到店

if (!isset($_GET["status"])) {

	$_GET["status"] = 1; //默认查询已到的

}

if ($_GET["status"]) {

	if ($_GET["status"] == "1") {

		$where[] = "status=1";

	}

	if ($_GET["status"] == "2") {

		$where[] = "status!=1";

	}

	$tips[] = $status_array[$_GET["status"]];

}

$sql_where = count($where) ? ("and ".implode(" and ", $where)) : "";



$tips = implode(" ", $tips);



$num = array();



foreach ($arr_time as $tname => $t) {

	list($tb, $te) = $t;



	$num[$tname]["all"] = $db->query("select count(*) as count from $table where order_date>=$tb and order_date<$te $sql_where", 1, "count");



	if (count($keshi) > 0) {

		foreach ($keshi as $k => $v) {

			$num[$tname][$v] = $db->query("select count(*) as count from $table where order_date>=$tb and order_date<$te and depart=$k $sql_where", 1, "count");

		}

	}

}





?>

<html>

<head>

<title><?php echo $title; ?></title>

<meta http-equiv="Content-Type" content="text/html;charset=gbk">

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



<div style="padding-left:30px; padding-top:20px;">

	<form method="GET">

	筛选条件：

	<select class="combo" name="media">

		<option value="" style="color:gray">-媒体来源-</option>

		<?php echo list_option($media_from_array, "_value_", "_value_", $_GET["media"]); ?>

	</select>&nbsp;

	<select class="combo" name="status">

		<option value="0" style="color:gray">-到店情况-</option>

		<?php echo list_option($status_array, "_key_", "_value_", $_GET["status"]); ?>

	</select>&nbsp;

	<input type="submit" class="button" value="确定" />

	</form>

</div>



<?php foreach ($arr_time as $tname => $t) { ?>

<div style="float:left; margin-top:40px; padding-left:30px;">

<table width="200" class="edit">

	<tr>

		<td colspan="2" class="head"><?php echo $tname." ".$tips; ?></td>

	</tr>

	<tr>

		<td class="left" style="width:50%">总共：</td>

		<td class="right"><b><?=$num[$tname]["all"]?></b></td>

	</tr>



<!-- 各门店来院人数 -->

<?php foreach ($keshi as $k => $v) { ?>

	<tr>

		<td class="left" style="width:30%"><?=$v?>：</td>

		<td class="right"><b><?=$num[$tname][$v]?></b></td>

	</tr>

<?php } ?>



</table>

</div>

<?php } ?>



<div class="clear"></div>



<div style="margin-top:40px; padding-left:40px; ">

	本表显示按门店统计的到店人数 &nbsp; 数据统计时间: <b><?php echo date("Y-m-d H:i"); ?></b><br>

	<br>

	<button onClick="location.reload()" class="buttonb">重新统计</button>

</div>





</body>

</html>