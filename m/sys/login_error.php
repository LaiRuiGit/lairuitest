<?php
/*
// - 功能说明 : 登录错误记录
// - 创建作者 : 小陈 
// - 创建时间 : 2013-05-15 03:11
*/
require "../../core/core.php";
$table = "sys_login_error";

if ($op) {
	include "login_error.do.php";
}

// 定义当前页需要用到的调用参数:
$link_param = array("page","sort","sorttype","searchword");

foreach ($link_param as $v) {
	if ($v != '') $$v = $_GET[$v];
}

// 定义单元格格式:

$list_heads = array(
	"选" => array("width"=>"4%", "align"=>"center"),
	"尝试姓名" => array("width"=>"20%", "align"=>"center", "sort"=>"binary t.tryname", "order"=>"asc"),
	"尝试密码" => array("width"=>"20%", "align"=>"center", "sort"=>"binary t.trypass", "order"=>"desc"),
	"操作者IP" => array("width"=>"21%", "align"=>"center", "sort"=>"binary t.userip", "order"=>"desc"),
	"时间" => array("width"=>"15%", "align"=>"center", "sort"=>"t.addtime", "order"=>"desc"),
	"操作" => array("width"=>"10%", "align"=>"center"),
);

function show_data($t, $li) {
	switch ($t) {
		case "选":
			return '<input name="delcheck" type="checkbox" value="'.$li["id"].'" onclick="set_item_color(this)">';
		case "尝试姓名":
			return $li["tryname"];
		case "尝试密码":
			return $li["trypass"];
		case "操作者IP":
			return $li["userip"];
		case "时间":
			return date("Y-m-d H:i", $li["addtime"]);
		case "操作":
			$op = array();
			if (check_power("delete")) {
				$op[] = "<a href='?op=delete&id=".$li["id"]."' onclick='return isdel()' class='op'>删除</a>";
			}
			return implode($GLOBALS["button_split"], $op);
		default:
			return '';
	}
}

// 默认排序方式:
$defaultsort = "时间";
$defaultorder = "desc";


// 查询条件:
$where = array();
if ($searchword) {
	$where[] = "(binary t.tryname like '%{$searchword}%' or binary t.trypass like '%{$searchword}%')";
}
$sqlwhere = count($where) > 0 ? ("where ".implode(" and ", $where)) : "";

// 对排序的处理：
$sqlsort = "";
if (!in_array($sorttype, array("", "asc", "desc"))) {
	$sorttype = "asc";
}
if ($sort) {
	$sqlsort = "order by ".$list_heads[$sort]["sort"]." ";
	$sqlsort .= $sorttype ? $sorttype : $list_heads[$sort]["order"];
} else {
	if ($defaultsort && array_key_exists($defaultsort, $list_heads)) {
		$sqlsort = "order by ".$list_heads[$defaultsort]["sort"]." ".$defaultorder;
	}
}


// 分页数据:
$count = $db->query_count("select count(*) from $table t $sqlwhere");
$pagecount = max(ceil($count / $pagesize), 1);
$page = max(min($pagecount, intval($page)), 1);
$offset = ($page - 1) * $pagesize;

// sql查询:
$list = $db->query("select * from $table t $sqlwhere $sqlsort limit $offset, $pagesize");
if (!is_array($list)) {
	exit("Error: ".$db->sql);
}

// 表格头部:
$table_header = '<tr>';
foreach ($list_heads as $k => $v) {
	list($tdalign, $tdwidth, $tdtitle) = build_table_head($k, $v);
	$table_header .= '<td class="head" align="'.$tdalign.'" width="'.$tdwidth.'">'.$tdtitle.'</td>';
}
$table_header .= '</tr>';

// 表格数据:
$table_items = array();
foreach ($list as $li) {

	$show_line = get_line_show($li, $pinfo);
	$item_data = '<tr id="#'.$li["id"].'"'.($show_line ? '' : ' class="hide"').' onmouseover="mi(this)" onmouseout="mo(this)">';
	foreach ($list_heads as $k => $v) {
		$tdalign = $v["align"];
		$item_data .= '<td class="item"'.($tdalign ? ' align="'.$tdalign.'"' : '').'>';
		$item_data .= show_data($k, $li);
		$item_data .= '</td>';
	}
	$item_data .= '</tr>';

	$table_items[] = $item_data;
}

$pagelink = pagelinkc($page, $pagecount, $count, make_link_info($link_param, "page"), "button");

include "login_error.list.php";
?>