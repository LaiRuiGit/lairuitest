<?php
/*
// - 功能说明 : 导出顾客
// - 创建作者 : 小陈 
// - 创建时间 : 2011-02-28
*/
require "../../core/core.php";
set_time_limit(0);

if ($user_hospital_id == 0) {
	exit_html("对不起，没有选择门店，不能执行该操作！");
}

$table = "patient_".$user_hospital_id;

$time_array = array("order_date"=>"到店时间", "addtime"=>"添加时间");
$status_array = array("all"=>"不限", "come"=>"已到", "not"=>"未到");
$sort_array = array("order_date"=>"到店时间", "name"=>"名字");
$part_array = array( "3"=>"电话");
$depart_array = $db->query("select id,name from depart where hospital_id='$user_hospital_id'", "id", "name");


$op = $_GET["op"];

// 处理时间:
if ($op == "show") {
	$where = "";

	$time_ty = "order_date";
	if ($_GET["ty"] && array_key_exists($_GET["ty"], $time_array)) {
		$time_ty = $_GET["ty"];
	}

	if ($_GET["btime"]) {
		$tb = strtotime($_GET["btime"]." 0:0:0");
		$where[] = "$time_ty>=$tb";
	}
	if ($_GET["etime"]) {
		$te = strtotime($_GET["etime"]." 23:59:59");
		$where[] = "$time_ty<$te";
	}

	if ($_GET["status"] == '') $_GET["status"] = "come";
	if ($st = $_GET["status"]) {
		if ($st != "all") {
			$where[] = ($st == "come") ? "status=1" : "status!=1";
		}
	}

	if ($_GET["part"]) {
		$where[] = "part_id=".intval($_GET["part"]);
	}

	if ($_GET["depart"]) {
		$where[] = "depart=".intval($_GET["depart"]);
	}

	$sqlwhere = count($where) ? ("where ".implode(" and ", $where)) : "";

	$sort = $_GET["sort"] ? $_GET["sort"] : "order_date";


	$list = $db->query("select * from $table $sqlwhere order by $sort asc", "");


	// 输出:
	$fields = $_GET["fields"];

	// 疾病类型转换:
	if (in_array("disease_id", $fields)) {
		$disease_id_name = $db->query("select id,name from disease", "id", "name");
	}

	$output_name = array();
	foreach ($list as $li) {
		$line = array();
		foreach ($fields as $x) {
			if ($x == "order_date" || $x == "addtime") {
				$y = @date("Y-m-d", $li[$x]);
			} else if ($x == "disease_id") {
				$y = $disease_id_name[$li[$x]];
			} else {
				$y = $li[$x];
			}
			// 替换所有回车换行为空格:
			$y = str_replace("\n", " ", str_replace("\r", "", $y));
			// 多个空格替换为一个:
			while (substr_count($y, "  ") > 0) {
				$y = str_replace("  ", " ", $y);
			}
			// 空值显示横线作为占位
			$line[] = (trim($y) == "" ? "-" : $y);
		}
		$output_name[] = @implode("\t", $line);
	}

	$output_name = implode("\r\n", $output_name);

}

$title = '导出顾客';
?>
<html>
<head>
<title><?php echo $title; ?></title>
<meta http-equiv="Content-Type" content="text/html;charset=gb2312">
<link href="/res/base.css" rel="stylesheet" type="text/css">
<script src="/res/base.js" language="javascript"></script>
<script src="/res/datejs/picker.js" language="javascript"></script>
<style>
#tiaojian {margin:10px 0 0 30px; }
form {display:inline; }

#result {margin-left:30px; margin-top:10px; }
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
<span>设置条件：</span>
<form method="GET">
	<select name="ty" class="combo">
		<option value="" style="color:gray">-时间类型-</option>
		<?php echo list_option($time_array, "_key_", "_value_", $time_ty); ?>
	</select>&nbsp;
	<input name="btime" id="begin_time" class="input" style="width:80px" value="<?php echo $_GET["btime"] ? $_GET["btime"] : date("Y-m-01"); ?>">
	<img src="/res/img/calendar.gif" id="order_date" onClick="picker({el:'begin_time',dateFmt:'yyyy-MM-dd'})" align="absmiddle" style="cursor:pointer" title="选择时间">

	<input name="etime" id="end_time" class="input" style="width:80px" value="<?php echo $_GET["etime"] ? $_GET["etime"] : date("Y-m-d"); ?>">
	<img src="/res/img/calendar.gif" id="order_date" onClick="picker({el:'end_time',dateFmt:'yyyy-MM-dd'})" align="absmiddle" style="cursor:pointer" title="选择时间">

	<select name="status" class="combo">
		<option value="" style="color:gray">-是否到店-</option>
		<?php echo list_option($status_array, "_key_", "_value_", $_GET["status"]); ?>
	</select>&nbsp;
	<select name="sort" class="combo">
		<option value="" style="color:gray">-结果排序-</option>
		<?php echo list_option($sort_array, "_key_", "_value_", $_GET["sort"]); ?>
	</select>&nbsp;
	<select name="part" class="combo">
		<option value="" style="color:gray">-部门-</option>
		<?php echo list_option($part_array, "_key_", "_value_", $_GET["part"]); ?>
	</select>&nbsp;
	<select name="depart" class="combo">
		<option value="" style="color:gray">-门店-</option>
		<?php echo list_option($depart_array, "_key_", "_value_", $_GET["depart"]); ?>
	</select>&nbsp;<br>
	输出字段：
	<input type="checkbox" name="fields[]" id="ch1" value="name" checked><label for="ch1">姓名</label>&nbsp;
	<input type="checkbox" name="fields[]" id="ch2" value="sex" <?php echo (@in_array("sex", $_GET["fields"]) ? "checked" : ""); ?>><label for="ch2">性别</label>&nbsp;
	<input type="checkbox" name="fields[]" id="ch3" value="age" <?php echo (@in_array("age", $_GET["fields"]) ? "checked" : ""); ?>><label for="ch3">年龄</label>&nbsp;
	<input type="checkbox" name="fields[]" id="ch4" value="tel" <?php echo (@in_array("tel", $_GET["fields"]) ? "checked" : ""); ?>><label for="ch4">电话号码</label>&nbsp;
	<input type="checkbox" name="fields[]" id="ch5" value="zhuanjia_num" <?php echo (@in_array("zhuanjia_num", $_GET["fields"]) ? "checked" : ""); ?>><label for="ch5">所在小区</label>&nbsp;
	<input type="checkbox" name="fields[]" id="ch6" value="disease_id" <?php echo (@in_array("disease_id", $_GET["fields"]) ? "checked" : ""); ?>><label for="ch6">疾病类型</label>&nbsp;
	<input type="checkbox" name="fields[]" id="ch7" value="content" <?php echo (@in_array("content", $_GET["fields"]) ? "checked" : ""); ?>><label for="ch7">房子需求</label>&nbsp;
	<input type="checkbox" name="fields[]" id="ch8" value="media_from" <?php echo (@in_array("media_from", $_GET["fields"]) ? "checked" : ""); ?>><label for="ch8">媒体来源</label>&nbsp;
	<input type="checkbox" name="fields[]" id="ch9" value="memo" <?php echo (@in_array("memo", $_GET["fields"]) ? "checked" : ""); ?>><label for="ch9">小区</label>&nbsp;
	<input type="checkbox" name="fields[]" id="ch10" value="author" <?php echo (@in_array("author", $_GET["fields"]) ? "checked" : ""); ?>><label for="ch10">销售</label>&nbsp;
	<input type="checkbox" name="fields[]" id="ch11" value="order_date" <?php echo (@in_array("order_date", $_GET["fields"]) ? "checked" : ""); ?>><label for="ch11">预约时间</label>&nbsp;
	<input type="checkbox" name="fields[]" id="ch12" value="addtime" <?php echo (@in_array("addtime", $_GET["fields"]) ? "checked" : ""); ?>><label for="ch12">添加时间</label>&nbsp;

	<input type="submit" class="button" value="提交">
	<input type="hidden" name="op" value="show">
</form>
</div>

<?php if ($op == "show") { ?>
<div class="space"></div>
<div id="result">

	<textarea id="result_box" style="width:95%; height:450px;" class="input"><?php echo $output_name; ?></textarea><br>
	<br>
	说明：上表导出的结果复制到Excel中，会自动分列显示。<br>
	<br>

</div>
<?php } ?>


</body>
</html>