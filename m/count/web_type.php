<?php
// --------------------------------------------------------
// - 功能说明 : 统计 项目 管理
// - 创建作者 : 小陈 
// - 创建时间 : 2010-10-13 11:34
// --------------------------------------------------------
require "../../core/core.php";
$table = "count_type";

// 操作的处理:
if ($op) {
	if ($op == "add") {
		include "web_type.edit.php";
		exit;
	}

	if ($op == "edit") {
		include "web_type.edit.php";
		exit;
	}

	if ($op == "delete") {
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
	}
}

// 定义当前页需要用到的调用参数:
$link_param = array("page","sort","order","key");
$param = array();
foreach ($link_param as $s) {
	$param[$s] = $_GET[$s];
}
extract($param);

// 定义单元格格式:
$list_heads = array(
	"选" => array("width"=>"32", "align"=>"center"),
	"项目名称" => array("align"=>"left", "sort"=>"binary name", "order"=>"asc"),
	"销售" => array("align"=>"left", "sort"=>"kefu", "order"=>"asc"),
	"管理员" => array("align"=>"left", "sort"=>"uids", "order"=>"asc"),
	"添加时间" => array("width"=>"15%", "align"=>"center", "sort"=>"addtime", "order"=>"desc"),
	"排序" => array("width"=>"", "align"=>"center", "sort"=>"sort", "order"=>"desc"),
	"操作" => array("width"=>"12%", "align"=>"center"),
);

// 默认排序方式:
$default_sort = "排序";
$default_order = "desc";


// 列表显示类:
$t = load_class("table");
$t->set_head($list_heads, $default_sort, $default_order);
$t->set_sort($_GET["sort"], $_GET["order"]);
$t->param = $param;
$t->table_class = "new_list";


// 查询条件:
$where = array();
$where[] = "type='web'";
if ($key) {
	$where[] = "(binary name like '%{$key}%')";
}
$sqlwhere = $db->make_where($where);

// 对排序的处理：
$sqlsort = $db->make_sort($list_heads, $sort, $order, $default_sort, $default_order);

// 分页数据:
$pagesize = 9999;
$count = $db->query_count("select count(*) from $table $sqlwhere");
$pagecount = max(ceil($count / $pagesize), 1);
$page = max(min($pagecount, intval($page)), 1);
$offset = ($page - 1) * $pagesize;

// 查询:
$list = $db->query("select * from $table $sqlwhere $sqlsort limit $offset,$pagesize");

$admin_id_name = $db->query("select id,realname from sys_admin where isshow=1", "id", "realname");

foreach ($list as $id => $li) {
	$r = array();

	$r["选"] = '<input name="delcheck" type="checkbox" value="'.$li["id"].'" onclick="set_item_color(this)">';
	$r["项目名称"] = $li["name"];
	$r["销售"] = $li["kefu"];

	$uids = explode(",", $li["uids"]);
	$u_names = array();
	foreach ($uids as $v) {
		if (array_key_exists($v, $admin_id_name)) {
			$u_names[] = $admin_id_name[$v];
		}
	}
	$r["管理员"] = implode("、", $u_names);
	$r["添加时间"] = date("Y-m-d H:i", $li["addtime"]);
	$r["排序"] = intval($li["sort"]);

	$op = array();
	if (check_power("edit")) {
		$op[] = "<a href='?op=edit&id=".$li["id"]."&back_url=".$back_url."' class='op' title='修改内容'>修改</a>";
	}
	if (check_power("delete")) {
		$op[] = "<a href='?op=delete&id=".$li["id"]."' onclick='return isdel()' class='op'>删除</a>";
	}
	$r["操作"] = implode($GLOBALS["button_split"], $op);

	$t->add($r);
}


$pagelink = pagelinkc($page, $pagecount, $count, make_link_info($link_param, "page"), "button");

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
	<div class="headers_title" style="width:50%"><span class="tips">门店项目管理</span></div>
	<div class="header_center"><?php echo $power->show_button("add"); ?></div>
	<div class="headers_oprate"><form name="topform" method="GET">模糊搜索：<input name="key" value="<?php echo $_GET["key"]; ?>" class="input" size="8">&nbsp;<input type="submit" class="search" value="搜索" style="font-weight:bold" title="点击搜索">&nbsp;<button onclick="location='?'" class="search" title="退出条件查询">重置</button>&nbsp;&nbsp;<button onclick="history.back()" class="button" title="返回上一页">返回</button></form></div>
</div>
<!-- 头部 end -->

<div class="space"></div>

<!-- 数据列表 begin -->
<form name="mainform">
<?php echo $t->show(); ?>
</form>
<!-- 数据列表 end -->

<div class="space"></div>

<!-- 分页链接 begin -->
<div class="footer_op">
	<div class="footer_op_left"><button onclick="select_all()" class="button">全选</button>&nbsp;<button onclick="unselect()" class="button">反选</button>&nbsp;<?php echo $power->show_button("close,delete"); ?></div>
	<div class="footer_op_right"><?php echo $pagelink; ?></div>
</div>
<!-- 分页链接 end -->

</body>
</html>