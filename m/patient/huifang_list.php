<?php
/*
// - 功能说明 : 顾客列表
// - 创建作者 : 小陈 
// - 创建时间 : 2013-05-01 05:09
*/
require "../../core/core.php";
$table = "patient_".$user_hospital_id;

check_power('', $pinfo) or msg_box("没有打开权限...", "back", 1);
if ($user_hospital_id == 0) {
	exit_html("对不起，没有选择门店，不能执行该操作！");
}

// 操作的处理:
if ($op = $_GET["op"]) {
	switch ($op) {
		case "insert":
			check_power("i", $pinfo, $pagepower) or msg_box("没有新增权限...", "back", 1);
			header("location:".$pinfo["insertpage"]);
			break;

		case "delete":
			//!check_power("delete") && msg_box("没有删除权限...", "back", 1);

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

		case "setshow":
			check_power("h", $pinfo, $pagepower) or msg_box("没有开通和关闭权限...", "back", 1);

			$isshow_value = intval($_GET["value"]) > 0 ? 1 : 0;
			$ids = explode(",", $_GET["id"]);
			$set_ok = $set_bad = 0;
			foreach ($ids as $opid) {
				if (($opid = intval($opid)) > 0) {
					if ($db->query("update $table set isshow='$isshow_value' where id='$opid' limit 1")) {
						$set_ok++;
					} else {
						$set_bad++;
					}
				}
			}

			if ($set_bad > 0) {
				msg_box("操作成功完成 $set_ok 条，失败 $del_bad 条。", "back", 1);
			} else {
				msg_box("设置成功！", "back", 1);
			}

		default:
			msg_box("操作未定义...", "back", 1);
	}
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
	13=>array("title"=>"回访", "width"=>"10%", "align"=>"center"),
);


if (!$show_type) $show_type = "tomorrow";

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
<title>电话咨询统计表</title>
<meta http-equiv="Content-Type" content="text/html;charset=gb2312">
<link href="/res/base.css" rel="stylesheet" type="text/css">
<script src="/res/base.js" language="javascript"></script>
</head>

<body>
<!-- 头部 begin -->
<table width="98%" cellpadding="0" cellspacing="5">
	<tr>
		<td width="50%" align="right" height="40">
			<span style="font-size:24px; line-height:24px; font-weight:bold;">电话咨询统计表</span>
		</td>
		<td width="50%" align="right">
			门店：<span style="text-decoration:underline;"><?php echo $hospital_id_name[$user_hospital_id]; ?></span>
			<?php if ($zhuanjia) { ?>
			专家：<span style="text-decoration:underline;"><?php echo $zhuanjia; ?></span>
			<?php } ?>
			时间：<span style="text-decoration:underline;"><?php echo date("Y年n月j日", $begin_time); ?></span>
		</td>
	</tr>
</table>
<!-- 头部 end -->

<!-- 数据列表 begin -->
<div class="space"></div>
<table width="98%" align="center" class="list" style="border:1px solid black;">
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
		<td align="center" class="item"></td>
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