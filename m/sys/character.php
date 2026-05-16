<?php

/*

// - 功能说明 : character.php

// - 创建作者 : 小陈 

// - 创建时间 : 2013-05-15 02:19

*/

require "../../core/core.php";

$table = "sys_character";



if ($op) {

	include "character.do.php";

}



// 定义当前页需要用到的调用参数:

$link_param = array("page","sort","sorttype","searchword");

foreach ($link_param as $v) {

	if ($v != '') $$v = $_GET[$v];

}



// 定义单元格格式:

$list_heads = array(

	"选" => array("width"=>"4%", "align"=>"center"),

	"名称" => array("width"=>"11%", "align"=>"center", "sort"=>"binary t.name", "order"=>"asc"),

	"当前使用者" => array("width"=>"", "align"=>"left", "sort"=>""),

	"时间" => array("width"=>"15%", "align"=>"center", "sort"=>"binary t.addtime", "order"=>"desc"),

	"操作" => array("width"=>"12%", "align"=>"center"),

);



function show_data($t, $li) {

	switch ($t) {

		case "选":

			return '<input name="delcheck" type="checkbox" value="'.$li["id"].'" onclick="set_item_color(this)">';

		case "名称":

			return $li["name"];

		case "当前使用者":

			return @implode(" | ", $GLOBALS["ch_info"][$li["id"]]);

		case "时间":

			return date("Y-m-d H:i", $li["addtime"]);

		case "操作":

			$op = array();

			if (check_power("view")) {

				$op[] = "<a href='?op=view&id=".$li["id"]."' class='op' title='查看详情'>查看</a>";

			}

			if ($li["power_compare"] < 0) {

				if (check_power("edit")) {

					$op[] = "<a href='?op=edit&id=".$li["id"]."&back_url=".$GLOBALS["back_url"]."' class='op' title='修改内容'>修改</a>";

				}

				if (check_power("delete")) {

					$op[] = "<a href='?op=delete&id=".$li["id"]."' onclick='return isdel()' class='op'>删除</a>";

				}

			}

			return implode($GLOBALS["button_split"], $op);

		default:

			return '';

	}

}



// 默认排序方式:

$defaultsort = "时间";

$defaultorder = "asc";





// 查询条件:

$where = array();

if ($searchword) {

	$where[] = "(binary t.name like '%{$searchword}%' or binary t.author like '%{$searchword}%')";

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



$my_power = $power->get_user_power($username, $uinfo);



// 数据筛选:

$all_list = $db->query("select * from $table t $sqlwhere");

echo "<!--";
print_r("select * from $table t $sqlwhere");
echo "-->";

$use_list = array();

foreach ($all_list as $li) {

	$li["power_compare"] = $power->compare_power($li["menu"], $my_power);

	if ($li["power_compare"] <= 0) {

		$use_list[] = $li;

	}

}



// 分页数据:

$count = count($use_list);

$pagecount = max(ceil($count / $pagesize), 1);

$page = max(min($pagecount, intval($page)), 1);

$offset = ($page - 1) * $pagesize;



// sql查询:

//$list = $db->query("select * from $table t $sqlwhere $sqlsort limit $offset, $pagesize");

$list = array_slice($use_list, $offset, $pagesize);

if (!is_array($list)) {

	exit("Error: ".$db->sql);

}



// admin 信息:

$tm_admins = $db->query("select id,name,realname,character_id from sys_admin where powermode='2' order by binary realname");

$ch_info = array();

foreach ($tm_admins as $tm_ad_info) {

	$ch_info[$tm_ad_info["character_id"]][] = $tm_ad_info["realname"]."(".$tm_ad_info["name"].")";

}



$back_url = make_back_url();



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



include "character.list.php";

?>