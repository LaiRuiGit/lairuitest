<?php

/*

// 说明: admin.list

// 作者: 小陈 

// 时间: 2010-07-06

*/



// 定义当前页需要用到的调用参数:

$link_param = array("page","sort","sorttype","searchword");

foreach ($link_param as $v) {

	if ($v != '') $$v = $_GET[$v];

}



// 定义单元格格式:

$list_heads = array(

	"选" => array("width"=>"32", "align"=>"center"),

	"登录名" => array("width"=>"80", "align"=>"center", "sort"=>"binary t.name", "order"=>"asc"),

	"真实姓名" => array("width"=>"80", "align"=>"center", "sort"=>"binary t.realname", "order"=>"asc"),

	"权限" => array("width"=>"80", "align"=>"center", "sort"=>""),

	"门店" => array("width"=>"", "align"=>"left", "sort"=>"binary t.hospitals", "order"=>"asc"),

	"登录次数" => array("width"=>"80", "align"=>"center", "sort"=>"t.logintimes", "order"=>"desc"),

	"在线" => array("width"=>"60", "align"=>"center", "sort"=>"t.online ", "order"=>"desc"),

	"操作" => array("width"=>"150", "align"=>"center"),

);



function show_data($t, $li) {

	switch ($t) {

		case "选":

			return '<input name="delcheck" type="checkbox" value="'.$li["id"].'" onclick="set_item_color(this)">';

		case "登录名":

			return $li["name"];

		case "真实姓名":

			return $li["realname"];

		case "权限":

			return $li["powermode"] == 1 ? "<font color='gray'>直接设定</font>" : ("<a href='character.php?op=view&id=".$li["character_id"]."'>".$GLOBALS["ch_list"][$li["character_id"]]."</a>");

		case "门店":

			if ($li["name"] == "admin") {

				return '<font color="gray">所有门店</font>';

			} else {

				$hospitals = $GLOBALS["power"]->parse_hospitals($li["hospitals"]);

				$shows = array();

				foreach ($hospitals as $k => $v) {

					$shows[] = $GLOBALS["hospital_id_name"][$k];

				}

				return implode(" | ", $shows);

			}

		case "上次登录":

			return $li["thislogin"] > 0 ? date("Y-m-d H:i", $li["thislogin"]) : '-';

		case "登录次数":

			return $li["logintimes"];

		case "在线":

			return $li["online"] > 0 ? "是" : "-";

		case "操作":

			$op = array();

			if (check_power("view")) $op[] = "<a href='?op=view&id=".$li["id"]."' class='op'>查看</a>";

			if ($li["power_compare"] <= 0) {

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

$defaultsort = "在线";

$defaultorder = "desc";





// 查询条件:

$where = array();

if ($searchword) {

	$where[] = "(binary t.name like '%{$searchword}%' or binary t.realname like '%{$searchword}%')";

}

//if (($show == "" || $show == "cur_hospital") && $hospital_id > 0) {

	//$where[] = "concat(',',hospitals,',') like '%,".$hospital_id.",%'";

//}

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

	//if ($defaultsort && array_key_exists($defaultsort, $list_heads)) {

	//	$sqlsort = "order by ".$list_heads[$defaultsort]["sort"]." ".$defaultorder;

	//}

	$sqlsort = "order by is_curhospital desc, online desc, id asc";

}



// hostpital_id_name:

$hospital_id_name = $db->query("select id,name from hospital", "id", "name");



// 初始化角色数据:

if (!$power->ch_data) {

	$power->init_ch_data();

}



// 我的权限:

$my_power = $power->get_user_power($username, $uinfo);



// 筛选管理员:

$admin_list = $db->query("select *,if(concat(',',hospitals,',') like '%,".intval($hostpital_id).",%' or name='admin',1,0) as is_curhospital from $table t $sqlwhere $sqlsort", "id");

$use_list = array();

foreach ($admin_list as $k => $li) {

	$li["power_compare"] = $power->compare_user($li, $uinfo);

	$li["hospitals_compare"] = $power->compare_hospitals($li, $uinfo);



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

$list = array_slice($use_list, $offset, $pagesize);



// 数据分组:

if ($hospital_id > 0) {

	$groups = array();

	foreach ($list as $k => $v) {

		$groups[$v["is_curhospital"]][] = $v;

	}



	$list = array();

	$list[] = array('id'=>0, "name"=>$hospital_name." 管理人员 [".count($groups[1]).']');

	$list = array_merge($list, $groups[1]);

	$list[] = array('id'=>0, "name"=>"其他管理人员 [".count($groups[0]).']');

	$list = array_merge($list, $groups[0]);

}



if (!is_array($list)) {

	exit("Error: ".$db->sql);

}



// 角色系统数据：

//$ch_list = $db->query("select id,name from sys_character order by binary name", "id", "name");

$ch_list = $power->ch_id_name;



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

	if ($li["id"] > 0) {

		$show_line = get_line_show($li, $pinfo);

		$item_data = '<tr id="#'.$li["id"].'"'.($show_line ? '' : ' class="hide"').' onmouseover="mi(this)" onmouseout="mo(this)">';

		foreach ($list_heads as $k => $v) {

			$tdalign = $v["align"];

			$item_data .= '<td class="item"'.($tdalign ? ' align="'.$tdalign.'"' : '').'>';

			$item_data .= show_data($k, $li);

			$item_data .= '</td>';

		}

		$item_data .= '</tr>';

	} else {

		$item_data = '<tr class="line_tips"><td colspan="'.count($list_heads).'">'.$li["name"].'</td></tr>';

	}



	$table_items[] = $item_data;

}



$pagelink = pagelinkc($page, $pagecount, $count, make_link_info($link_param, "page"), "button");



include "admin.list.tpl.php";



?>