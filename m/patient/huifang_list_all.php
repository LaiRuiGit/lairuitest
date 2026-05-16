<?php
/*
// - 功能说明 : 顾客列表
// - 创建作者 : 小陈 
// - 创建时间 : 2013-07-17 15:22
*/
require "../../core/core.php";
$table = "patient_".$user_hospital_id;

check_power('', $pinfo) or msg_box("没有打开权限...", "back", 1);
if ($user_hospital_id == 0) {
	exit_html("对不起，没有选择门店，不能执行该操作！");
}


// 定义当前页需要用到的调用参数:
$aLinkInfo = array(
	"zhuanjia" => "zhuanjia",
	"show_type" => "show_type"
);

// 读取页面调用参数:
foreach ($aLinkInfo as $local_var_name => $call_var_name) {
	$$local_var_name = $_GET[$call_var_name];
}

// 定义单元格格式:
$aOrderType = array(0 => "", 1 => "asc", 2 => "desc");
$aTdFormat = array(
	1=>array("title"=>"姓名", "width"=>"50", "align"=>"center"),
	2=>array("title"=>"性别", "width"=>"", "align"=>"center"),
	3=>array("title"=>"年龄", "width"=>"", "align"=>"center"),
	4=>array("title"=>"电话", "width"=>"", "align"=>"center"),
	5=>array("title"=>"专家", "width"=>"", "align"=>"center"),
	6=>array("title"=>"预约时间", "width"=>"8%", "align"=>"center"),
	7=>array("title"=>"装修类型", "width"=>"", "align"=>"center"),
	8=>array("title"=>"媒体来源", "width"=>"", "align"=>"center"),
	9=>array("title"=>"治疗项目", "width"=>"", "align"=>"center"),
	10=>array("title"=>"备注", "width"=>"", "align"=>"center"),
	11=>array("title"=>"部门", "width"=>"", "align"=>"center"),
	12=>array("title"=>"添加人", "width"=>"", "align"=>"center"),
);


if (!$show_type) $show_type = "today";

if ($show_type == 'today') {
	$begin_time = mktime(0, 0, 0);
	$end_time = strtotime("+1 day", $begin_time);
} else if ($show_type == 'tomorrow') {
	$begin_time = mktime(0, 0, 0) + 24 * 3600;
	$end_time = strtotime("+1 day", $begin_time);
}

// 查询条件:
$where = array();
$where[] = "status=1";
if ($zhuanjia) {
	$where[] = "doctor='$zhuanjia'";
}
$where[] = "rechecktime>=$begin_time and rechecktime<$end_time";

$sqlwhere = count($where) > 0 ? ("where ".implode(" and ", $where)) : "";

// 查询:
$data = $db->query("select * from $table $sqlwhere order by binary name");

// id => name:
$hospital_id_name = $db->query("select id,name from ".$tabpre."hospital", 'id', 'name');
$part_id_name = $db->query("select id,name from ".$tabpre."sys_part", 'id', 'name');
$disease_id_name = $db->query("select id,name from ".$tabpre."disease", 'id', 'name');


// 页面开始 ------------------------
?>
<html>
<head>
<title>今日应到复诊顾客 - <?php echo $hospital_id_name[$user_hospital_id]; ?> - <?php echo date("Y年n月j日", $begin_time); ?></title>
<meta http-equiv="Content-Type" content="text/html;charset=gb2312">
<link href="/res/base.css" rel="stylesheet" type="text/css">
<script src="/res/base.js" language="javascript"></script>
</head>

<body>

<!-- 数据列表 begin -->
<table width="100%" align="center" class="list" style="border:1px solid black;">
	<!-- 表头定义 begin -->
	<tr>
<?php
// 表头处理:
foreach ($aTdFormat as $tdid => $tdinfo) {
	list($tdalign, $tdwidth, $tdtitle) = make_td_head($tdid, $tdinfo);
?>
		<td class="head" align="<?php echo $tdalign; ?>" width="<?php echo $tdwidth; ?>"><?php echo $tdtitle; ?></td>
<? } ?>
	</tr>
	<!-- 表头定义 end -->

	<!-- 主要列表数据 begin -->
<?php
if (count($data) > 0) {
	foreach ($data as $line) {
		$id = $line["id"];
?>
	<tr>
		<td align="center" class="item"><?php echo $line["name"]; ?></td>
		<td align="center" class="item"><?php echo $line["sex"]; ?></td>
		<td align="center" class="item"><?php echo $line["age"] > 0 ? $line["age"] : ''; ?></td>
		<td align="center" class="item"><?php echo $line["tel"]; ?></td>
		<td align="center" class="item"><?php echo $line["doctor"] ? $line["doctor"] : ''; ?></td>
		<td align="center" class="item"><?php echo str_replace('|', '<br>', @date("Y-m-d|H:i", $line["order_date"])); ?></td>
		<td align="center" class="item"><?php echo $disease_id_name[$line["disease_id"]]; ?></td>
		<td align="center" class="item"><?php echo $line["media_from"]; ?></td>
		<td align="center" class="item"><?php echo $line["xiangmu"]; ?></td>
		<td align="center" class="item"><?php echo $line["memo"]; ?></td>
		<td align="center" class="item"><?php echo $part_id_name[$line["part_id"]]; ?></td>
		<td align="center" class="item"><?php echo $line["author"]; ?></td>
	</tr>
<?php
	}
} else {
?>
	<tr>
		<td colspan="<?php echo count($aTdFormat); ?>" align="center" class="nodata">(没有数据...)</td>
	</tr>
<?php } ?>
	<!-- 主要列表数据 end -->

</table>
<!-- 数据列表 end -->

</body>
</html>