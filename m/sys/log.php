<?php

/*

// - 功能说明 : 操作日志

// - 创建作者 : 小陈 

// - 创建时间 : 2013-05-15 02:32

*/

require "../../core/core.php";

$table = "sys_log";



// 更新 uid (2011-01-05):

$db->query("update sys_log g, sys_admin u set g.uid=u.id where g.uid=0 and g.username=u.name");



// 按门店读取日志:

$tm_uids_str = '';

if ($hid > 0) {

	$tm_uids = $db->query("select id from sys_admin where concat(',',hospitals,',') like '%,{$hid},%'", "", "id");

	if (count($tm_uids) > 0) {

		$tm_uids[] = 1; //1 是admin的uid

	}

	$tm_uids_str = implode(",", $tm_uids);

}





if ($op) {

	include "log.do.php";

}



// 定义当前页需要用到的调用参数:

$link_param = array("page","sort","sorttype","searchword");

foreach ($link_param as $v) {

	if ($v != '') $$v = $_GET[$v];

}



// 定义单元格格式:

$list_heads = array(

	"选" => array("width"=>"4%", "align"=>"center"),

	"类型" => array("width"=>"10%", "align"=>"center", "sort"=>"binary t.type", "order"=>"asc"),

	"说明" => array("width"=>"25%", "align"=>"left", "sort"=>"binary t.title", "order"=>"asc"),

	"页面" => array("width"=>"26%", "align"=>"left", "sort"=>"binary t.url", "order"=>"desc"),

	"用户" => array("width"=>"10%", "align"=>"center", "sort"=>"binary t.username", "order"=>"desc"),

	"时间" => array("width"=>"15%", "align"=>"center", "sort"=>"t.addtime", "order"=>"desc"),

	"操作" => array("width"=>"10%", "align"=>"center"),

);



function show_data($t, $li) {

	switch ($t) {

		case "选":

			return '<input name="delcheck" type="checkbox" value="'.$li["id"].'" onclick="set_item_color(this)">';

		case "类型":

			return $li["type"];

		case "说明":

			return $li["title"];

		case "页面":

			return $li["url"];

		case "用户":

			return $li["username"];

		case "时间":

			return date("Y-m-d H:i", $li["addtime"]);

		case "操作":

			$op = array();

			if (check_power("view")) {

				$op[] = "<a href='?op=view&id=".$li["id"]."' class='op'>查看</a>";

			}

			if (check_power("edit")) {

				$op[] = "<a href='?op=edit&id=".$li["id"]."&back_url=".$GLOBALS["back_url"]."' class='op' title='修改内容'>修改</a>";

			}

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

	$where[] = "(binary t.title like '%{$searchword}%' or binary t.username like '%{$searchword}%' or binary t.data like '%{$searchword}%')";

}



if ($tm_uids_str) {

	$where[] = "uid in (".$tm_uids_str.")";

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



include "log.list.php";

?>