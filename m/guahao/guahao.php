<?php
/*
// - 功能说明 : 预约顾客列表
// - 创建作者 : 小陈 
// - 创建时间 : 2013-05-18 21:40
*/
require "../../core/core.php";
$table = "guahao";

if (!$user_hospital_id) {
	exit_html("对不起，没有选择门店，不能执行该操作！");
}

// 数据库对照:
$hospital_id_name = $db->query("select id,name from ".$tabpre."hospital", 'id', 'name');

check_power('', $pinfo) or msg_box("没有打开权限...", "back", 1);

// 操作的处理:
if ($op = $_GET["op"]) {
	switch ($op) {
		case "insert":
			check_power("i", $pinfo, $pagepower) or msg_box("没有新增权限...", "back", 1);
			header("location:".$pinfo["insertpage"]);
			break;

		case "delete":
			check_power("delete") or msg_box("没有删除权限...", "back", 1);

			$ids = explode(",", $_GET["id"]);
			$del_ok = $del_bad = 0; $op_data = array();
			foreach ($ids as $opid) {
				if (($opid = intval($opid)) > 0) {
					$tmp_data = $db->query("select * from $table where id='$opid' limit 1", 1);
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

if ($_GET["btime"]) {
	$_GET["begin_time"] = strtotime($_GET["btime"]);
}
if ($_GET["etime"]) {
	$_GET["end_time"] = strtotime($_GET["etime"]);
}

// 定义当前页需要用到的调用参数:
$aLinkInfo = array(
	"page" => "page",
	"sortid" => "sort",
	"sorttype" => "sorttype",
	"searchword" => "searchword",
	"begin_time" => "begin_time",
	"end_time" => "end_time",
);

// 读取页面调用参数:
foreach ($aLinkInfo as $local_var_name => $call_var_name) {
	$$local_var_name = $_GET[$call_var_name];
}

// 定义单元格格式:
$aOrderType = array(0 => "", 1 => "asc", 2 => "desc");
$aTdFormat = array(
	0=>array("title"=>"选", "width"=>"40", "align"=>"center"),
	1=>array("title"=>"姓名", "width"=>"50", "align"=>"center", "sort"=>"name", "defaultorder"=>1),
	2=>array("title"=>"性别", "width"=>"", "align"=>"center", "sort"=>"sex", "defaultorder"=>1),
	3=>array("title"=>"电话", "width"=>"", "align"=>"center", "sort"=>"tel", "defaultorder"=>1),
	10=>array("title"=>"城市", "width"=>"", "align"=>"center", "sort"=>"city", "defaultorder"=>1),
	4=>array("title"=>"EMAIL", "width"=>"", "align"=>"center", "sort"=>"email", "defaultorder"=>1),
	5=>array("title"=>"预约时间", "width"=>"8%", "align"=>"center", "sort"=>"order_date", "defaultorder"=>2),
	6=>array("title"=>"房子需求", "width"=>"", "align"=>"center", "sort"=>"content", "defaultorder"=>1),
	7=>array("title"=>"备注", "width"=>"", "align"=>"center", "sort"=>"memo", "defaultorder"=>2),
	8=>array("title"=>"添加时间", "width"=>"8%", "align"=>"center", "sort"=>"addtime", "defaultorder"=>2),
	11=>array("title"=>"提交网站", "width"=>"", "align"=>"center", "sort"=>"site", "defaultorder"=>1),
	9=>array("title"=>"操作", "width"=>"80", "align"=>"center"),
);

// 默认排序方式:
$defaultsort = 8;
$defaultorder = 2;

// 查询条件:
$where = array();
$where[] = "hospital_id=$user_hospital_id";
if ($searchword) {
	$where[] = "(binary name like '%{$searchword}%' or tel like '%{$searchword}%' or content like '%{$searchword}%' or memo like '%{$searchword}%')";
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
	if ($aTdFormat[$sortid]["sort2"]) {
		$sqlsort .= ','.$aTdFormat[$sortid]["sort2"];
	}
} else {
	if ($defaultsort > 0 && array_key_exists($defaultsort, $aTdFormat)) {
		$sqlsort = "order by ".$aTdFormat[$defaultsort]["sort"]." ".$aOrderType[$defaultorder];
	} else {
		$sqlsort = "";
	}
}
$sqlsort = $sqlsort ? ($sqlsort.",addtime asc") : "addtime desc";

// 分页数据:
$count = $db->query("select count(*) as count from $table $sqlwhere", 1, "count");
$pagecount = max(ceil($count / $pagesize), 1);
$page = max(min($pagecount, intval($page)), 1);
$offset = ($page - 1) * $pagesize;

// 查询:
$data = $db->query("select * from $table $sqlwhere $sqlsort limit $offset,$pagesize");

// 对列表数据分组:
if ($sortid == 8 || ($sortid == 0 && $defaultsort == 8)) {

	$today_begin = mktime(0,0,0);
	$today_end = $today_begin + 24*3600;
	$yesterday_begin = $today_begin - 24*3600;

	$data_part = array();
	foreach ($data as $line) {
		if ($line["addtime"] < $yesterday_begin) {
			$data_part[3][] = $line;
		} else if ($line["addtime"] < $today_begin) {
			$data_part[2][] = $line;
		} else if ($line["addtime"] < $today_end) {
			$data_part[1][] = $line;
		}
	}

	$data = array();
	if (count($data_part[1]) > 0) {
		$data[] = array("id"=>0, "name"=>"今天 [".count($data_part[1])."]");
		$data = array_merge($data, $data_part[1]);
	}
	if (count($data_part[2]) > 0) {
		$data[] = array("id"=>0, "name"=>"昨天 [".count($data_part[2])."]");
		$data = array_merge($data, $data_part[2]);
	}
	if (count($data_part[3]) > 0) {
		$data[] = array("id"=>0, "name"=>"前天或更早 [".count($data_part[3])."]");
		$data = array_merge($data, $data_part[3]);
	}
	unset($data_part);
}

// 页面开始 ------------------------
?>
<html>
<head>
<title><?php echo $pinfo["title"]; ?></title>
<meta http-equiv="Content-Type" content="text/html;charset=gb2312">
<link href="/res/base.css" rel="stylesheet" type="text/css">
<script src="/res/base.js" language="javascript"></script>
</head>

<body>
<!-- 头部 begin -->
<div class="headers">
	<div class="headers_title" style="width:45%"><span class="tips"><?=$hospital_id_name[$user_hospital_id]?> - 挂号列表</span></div>
	<div class="header_center"><?php echo $power->show_button("add"); ?></div>
	<div class="headers_oprate"><form name="topform" method="GET">模糊搜索：<input name="searchword" value="<?php echo $_GET["searchword"]; ?>" class="input" size="8">&nbsp;<input type="submit" class="search" value="搜索" style="font-weight:bold" title="点击搜索">&nbsp;<button onClick="location='?'" class="search" title="退出条件查询">退出</button>&nbsp;<button onClick="history.back()" class="button" title="返回上一页">返回</button></form></div>
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
		if ($id == 0) {
?>
	<tr>
		<td colspan="<?php echo count($aTdFormat); ?>" align="left" class="group"><?php echo $line["name"]; ?></td>
	</tr>
<?php
		} else {

		$op = array();
		if (check_power("v", $pinfo, $pagepower)) {
			$op[] = "<a href='".$pinfo["viewpage"]."?id=$id' class='op'><img src='/res/img/b_detail.gif' align='absmiddle' title='查看' alt=''></a>";
		}
		if (check_power("edit")) {
			$op[] = "<a href='".$pinfo["editpage"]."?id=$id&go=back' class='op'>修改</a>";
		}
		if (check_power("delete")) {
			$op[] = "<a href='?op=delete&id=$id' onclick='return isdel()' class='op'>删除</a>";
		}
		$op_button = implode("&nbsp;", $op);

		$hide_line = ($pinfo && $pinfo["ishide"] && $line["isshow"] != 1) ? 1 : 0;

?>
	<tr<?php echo $hide_line ? " class='hide'" : ""; ?>>
		<td align="center" class="item"><input name="delcheck" type="checkbox" value="<?php echo $id; ?>" onpropertychange="set_item_color(this)"></td>
		<td align="center" class="item"><?php echo $line["name"]; ?></td>
		<td align="center" class="item"><?php echo $line["sex"]; ?></td>

		<td align="center" class="item"><?php echo $line["tel"]; ?></td>
		<td align="center" class="item"><?php echo $line["city"]; ?></td>
		<td align="center" class="item"><?php echo $line["email"]; ?></td>
		<td align="center" class="item"><?php echo $line["order_date"] > 0 ? str_replace("*", "<br>", date("Y-m-d*H:i", $line["order_date"])) : ''; ?></td>
		<td align="left" class="item"><?php echo $line["content"]; ?></td>
		<td align="left" class="item"><?php echo $line["memo"]; ?></td>
		<td align="center" class="item"><?php echo str_replace("*", "<br>", date("Y-m-d*H:i", $line["addtime"])); ?></td>
		<td align="center" class="item"><?php echo $line["site"]; ?></td>
		<td align="center" class="item"><?php echo $op_button; ?></td>
	</tr>
<?php
		}
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

<!-- 分页链接 begin -->
<div class="space"></div>
<div class="footer_op">
	<div class="footer_op_left"><button onClick="select_all()" class="button">全选</button>&nbsp;<button onClick="unselect()" class="button">反选</button>&nbsp;<?php echo $power->show_button("hdie,delete"); ?></div>
	<div class="footer_op_right"><?php echo pagelinkc($page, $pagecount, $count, make_link_info($aLinkInfo, "page"), "button"); ?></div>
</div>
<!-- 分页链接 end -->

</body>
</html>