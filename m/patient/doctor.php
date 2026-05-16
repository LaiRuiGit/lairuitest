<?php
/*
// - 功能说明 : 医生列表
// - 创建作者 : 小陈 
// - 创建时间 : 2013-05-02 16:40
*/
require "../../core/core.php";
$table = "doctor";

if ($user_hospital_id == 0) {
	exit_html("对不起，没有选择门店，不能执行该操作！");
}

// 操作的处理:
if ($op = $_GET["op"]) {
	switch ($op) {
		case "add":
			include "doctor_edit.php";
			exit;

		case "edit":
			$line = $db->query_first("select * from $table where id='$id' limit 1");
			include "doctor_edit.php";
			exit;

		case "insert":
			!check_power("i", $pinfo, $pagepower) && msg_box("没有新增权限...", "back", 1);
			header("location:".$pinfo["insertpage"]);
			break;

		case "delete":
			!check_power("delete") && msg_box("没有删除权限...", "back", 1);

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
			!check_power("h", $pinfo, $pagepower) && msg_box("没有开通和关闭权限...", "back", 1);

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
	"page" => "page",
	"sortid" => "sort",
	"sorttype" => "sorttype",
	"searchword" => "searchword",
);

// 读取页面调用参数:
foreach ($aLinkInfo as $local_var_name => $call_var_name) {
	$$local_var_name = $_GET[$call_var_name];
}

// 定义单元格格式:
$aOrderType = array(0 => "", 1 => "asc", 2 => "desc");
$aTdFormat = array(
	0=>array("title"=>"选", "width"=>"8%", "align"=>"center"),
	1=>array("title"=>"/", "width"=>"30%", "align"=>"left", "sort"=>"binary name", "defaultorder"=>1),
	2=>array("title"=>"名字", "width"=>"30%", "align"=>"left", "sort"=>"binary name", "defaultorder"=>1),
	3=>array("title"=>"添加时间", "width"=>"20%", "align"=>"center", "sort"=>"addtime", "defaultorder"=>2),
	4=>array("title"=>"操作", "width"=>"12%", "align"=>"center"),
);

// 默认排序方式:
$defaultsort = 3;
$defaultorder = 1;


// 查询条件:
$where = array();
$where[] = "hospital_id=$user_hospital_id";
if ($searchword) {
	$where[] = "(binary t.name like '%{$searchword}%')";
}
$sqlwhere = count($where) > 0 ? ("where ".implode(" and ", $where)) : "";

// 对排序的处理：
if ($sortid > 0) {
	$sqlsort = "order by ".$aTdFormat[$sortid]["sort"]." ";
	if ($sorttype > 0) {
		$sqlsort .= $aOrderType[$sorttype];
	} else {
		$sqlsort .= $aOrderType[$aTdFormat[$sortid]["defaultorder"]];
	}
} else {
	if ($defaultsort > 0 && array_key_exists($defaultsort, $aTdFormat)) {
		$sqlsort = "order by ".$aTdFormat[$defaultsort]["sort"]." ".$aOrderType[$defaultorder];
	} else {
		$sqlsort = "";
	}
}

// 分页数据:
$count = $db->query_count("select count(*) from $table $sqlwhere");
$pagecount = max(ceil($count / $pagesize), 1);
$page = max(min($pagecount, intval($page)), 1);
$offset = ($page - 1) * $pagesize;

// 查询:
$data = $db->query("select * from $table $sqlwhere $sqlsort limit $offset,$pagesize");

$hospital_id_name = $db->query("select id,name from ".$tabpre."hospital", 'id', 'name');

// 页面开始 ------------------------
?>
<html>
<head>
<title><?php echo $pinfo["title"]; ?></title>
<meta http-equiv="Content-Type" content="text/html;charset=gb2312">
<link href="/res/base.css" rel="stylesheet" type="text/css">
<script src="/res/base.js" language="javascript"></script>
<style>
form {display:inline; }
</style>
<script language="javascript"></script>
</head>

<body>
<!-- 头部 begin -->
<div class="headers">
	<div class="headers_title"><span class="tips"><?=$hospital_id_name[$user_hospital_id]?> - 装修类型</span></div>
	<div class="header_center"><?php echo $power->show_button("add"); ?></div>
	<div class="headers_oprate"><form name="topform" method="GET">模糊搜索：<input name="searchword" value="<?php echo $_GET["searchword"]; ?>" class="input" size="8">&nbsp;<input type="submit" class="search" value="搜索" style="font-weight:bold" title="点击搜索">&nbsp;<button onclick="location='?'" class="search" title="退出条件查询">退出</button>&nbsp;<button onclick="history.back()" class="button" title="返回上一页">返回</button></form></div>
</div>
<!-- 头部 end -->

<div class="space"></div>

<!-- 数据列表 begin -->
<form name="mainform">
<table width="100%" align="center" class="list">
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

		$op = array();
		if (check_power("edit")) {
			$op[] = "<a href='?op=edit&id=$id' class='op'>修改</a>";
		}
//		if (check_power("delete")) {
//			$op[] = "<a href='?op=delete&id=$id' onclick='return isdel()' class='op'>删除</a>";
//		}
		$op_button = implode("&nbsp;", $op);

		$hide_line = ($pinfo && $pinfo["ishide"] && $line["isshow"] != 1) ? 1 : 0;
?>
	<tr<?php echo $hide_line ? " class='hide'" : ""; ?>>
		<td align="center" class="item"><input name="delcheck" type="checkbox" value="<?php echo $id; ?>" onpropertychange="set_item_color(this)"></td>
		<td align="left" class="item"><?php echo $line["doctor_num"]; ?></td>
		<td align="left" class="item"><?php echo $line["name"]; ?></td>
		<td align="center" class="item"><?php echo date("Y-m-d H:i", $line["addtime"]); ?></td>
		<td align="center" class="item"><?php echo $op_button; ?></td>
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
</form>
<!-- 数据列表 end -->

<div class="space"></div>

<!-- 分页链接 begin -->
<div class="footer_op">
	<div class="footer_op_left"><button onclick="select_all()" class="button">全选</button>&nbsp;<button onclick="unselect()" class="button">反选</button>&nbsp;</div>
	<div class="footer_op_right"><?php echo pagelinkc($page, $pagecount, $count, make_link_info($aLinkInfo, "page"), "button"); ?></div>
</div>
<!-- 分页链接 end -->

</body>
</html>