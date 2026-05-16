<?php

/* --------------------------------------------------------

// 说明: 杂志

// 作者: 小陈 

// 时间: 2010-03-13 12:28

// ----------------------------------------------------- */

require "../../core/core.php";

$title = "杂志统计数据";



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



$num = array();



foreach ($arr_time as $tname => $t) {

	list($tb, $te) = $t;



	$num[$tname]["all"] = $db->query("select count(*) as count from $table where status=1 and media_from='杂志' and order_date>=$tb and order_date<$te", 1, "count");



	if (count($keshi) > 0) {

		foreach ($keshi as $k => $v) {

			$num[$tname][$v] = $db->query("select count(*) as count from $table where status=1 and media_from='杂志' and order_date>=$tb and order_date<$te and depart=$k", 1, "count");

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

	<div class="headers_oprate"><button onclick="history.back()" class="button">返回</button></div>

</div>

<!-- 头部 end -->



<div class="space"></div>



<?php foreach ($arr_time as $tname => $t) { ?>

<div style="float:left; margin-top:40px; padding-left:30px;">

<table width="200" class="edit">

	<tr>

		<td colspan="2" class="head"><?php echo $tname; ?></td>

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

	本表显示媒体来源是杂志的到店人数 &nbsp; 数据统计时间: <b><?php echo date("Y-m-d H:i"); ?></b> &nbsp;

	<button onclick="location.reload()" class="buttonb">重新统计</button>

</div>





</body>

</html>