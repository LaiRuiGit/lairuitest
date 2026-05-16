<?php

/*

// - 功能说明 : 销售报表 所有门店

// - 创建作者 : 小陈 

// - 创建时间 : 2010-05-17 10:21

*/

require "../../core/core.php";

check_power('', $pinfo) or msg_box("没有打开权限...", "back", 1);

set_time_limit(0);



// 门店列表:

$h_id_name = $db->query("select id,name from hospital order by sort desc,id asc", "id", "name");



// 可选月份:

$date_list = array();

for($i=0; $i<6; $i++) {

	$date_list[] = date("Y-m", strtotime("-{$i} month"));

}



$time_array = array("addtime"=>"添加时间", "order_date"=>"到店时间");

$status_array = array("all"=>"不限", "come"=>"已到", "not"=>"未到");





$op = $_GET["op"];



// 处理时间:

if ($op == "show") {

	if ($_GET["m"] == "") $_GET["m"] = date("Y-m");

	$m = $_GET["m"];

	$tb = strtotime($m);

	$te = strtotime("+1 month", $tb);



	$time_ty = "order_date";

	if ($ty = $_GET["ty"] && array_key_exists($ty, $time_array)) {

		$time_ty = $_GET["ty"];

	}

	$sqlwhere = "$time_ty>=$tb and $time_ty<$te";

	if ($_GET["status"] == '') $_GET["status"] = "come";

	if ($st = $_GET["status"]) {

		if ($st != "all") {

			$sqlwhere .= ($st == "come") ? " and status=1" : " and status!=1";

		}

	}

}



$title = '销售报表(按月)';

?>

<html>

<head>

<title><?php echo $title; ?></title>

<meta http-equiv="Content-Type" content="text/html;charset=gb2312">

<link href="/res/base.css" rel="stylesheet" type="text/css">

<script src="/res/base.js" language="javascript"></script>

<style>

#tiaojian {margin:10px 0 0 30px; }

form {display:inline; }



#result {margin-left:50px; }

.h_name {font-weight:bold; margin-top:20px; }

.h_kf {margin-left:20px; }

.kf_li {border-bottom:0px dotted silver; }

</style>

</head>



<body>

<!-- 头部 begin -->

<div class="headers">

	<div class="headers_title"><span class="tips"><?php echo $title; ?></span></div>

	<div class="headers_oprate"><button onclick="history.back()" class="button">返回</button></div>

</div>

<!-- 头部 end -->



<div class="space"></div>

<div id="tiaojian">

<span>请选择条件：</span>

<form method="GET">

	<select name="m" class="combo">

		<option value="" style="color:gray">-请选择月份-</option>

		<?php echo list_option($date_list, "_value_", "_value_", $_GET["m"]); ?>

	</select>&nbsp;

	<select name="ty" class="combo">

		<option value="" style="color:gray">-类型-</option>

		<?php echo list_option($time_array, "_key_", "_value_", $time_ty); ?>

	</select>&nbsp;

	<select name="status" class="combo">

		<option value="" style="color:gray">-是否到店-</option>

		<?php echo list_option($status_array, "_key_", "_value_", $_GET["status"]); ?>

	</select>&nbsp;

	<input type="submit" class="button" value="提交">

	<input type="hidden" name="op" value="show">

</form>

</div>



<?php if ($op == "show") { ?>

<div class="space"></div>

<div id="result">



<!-- begin 门店循环 -->

<?php

foreach ($h_id_name as $id => $name) {

?>

	<div class="h_name"><?php echo $name; ?></div>

	<div class="h_kf">

<?php

	$list = $db->query("select author, count(author) as count from patient_{$id} where $sqlwhere group by author");

	if (count($list) > 0) {

		foreach ($list as $li) {

			echo str_pad($li["author"]." ", 20, "-", STR_PAD_RIGHT)." ".$li["count"]."<br>";

		}

	} else {

		echo '-';

	}

	?>

	</div>

<?php

	flush();

	ob_flush();

}

?>

<!-- end 门店循环 -->



</div>

<?php } ?>





</body>

</html>