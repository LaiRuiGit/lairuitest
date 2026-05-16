<?php

/* --------------------------------------------------------

// 说明:

// 作者: 小陈 

// 时间:

// ----------------------------------------------------- */



// ~~~~~~~~~~ 简化式链接(占用较少的位置，速度也较快)

function pagelinks($page, $pagecount, $linkbase='', $showpageinfo=1)

{

	$sp = '&nbsp;';

	$base = $linkbase ? ($linkbase . "&") : "?";

	$pagelink = $showpageinfo ? "第<font color=red><b>$page</b></font>页 总<font color=red><b>$pagecount</b></font>页$sp$sp" : "";

	$pagelink .= $page>1 ? "<a href='{$base}page=1'>[首页]</a>$sp" : "[首页]$sp";

	$pagelink .= ($page>1 ? "<a href='{$base}page=" . ($page - 1) ."'>[上页]</a>$sp" : "[上页]$sp");

	$pagelink .= ($page<$pagecount ? "<a href='{$base}page=" . ($page + 1) . "'>[下页]</a>$sp" : "[下页]$sp");

	$pagelink .= $page<$pagecount ? "<a href='{$base}page=$pagecount'>[末页]</a>$sp" : "[末页]$sp";



	return $pagelink;

}



// ~~~~~~~~~~ 普通方式显示页面链接，用于前台页面较多

function pagelinkn($page, $pagecount, $linkbase='', $showpageinfo=0)

{

	$sp = '&nbsp;';

	$base = $linkbase ? ($linkbase . "&") : "?";

	$pagelink = $showpageinfo ? "第<font color=red><b>$page</b></font>/<font color=blue><b>$pagecount</b></font>页$sp$sp" : "";

	$pagelink .= ($page>1 ? "<a href='{$base}page=1'>首页</a>" : "首页") . "$sp|$sp";

	$pagelink .= ($page>1 ? "<a href='{$base}page=" . ($page - 1) ."'>上一页</a>$sp" : "上一页") . "$sp|$sp";

	$pagelink .= ($page<$pagecount ? "<a href='{$base}page=" . ($page + 1) . "'>下一页</a>" : "下一页") . "$sp|$sp";

	$pagelink .= ($page<$pagecount ? "<a href='{$base}page=$pagecount'>[末页]</a>" : "[末页]") . "$sp";



	return $pagelink;

}





// ~~~~~~~~~~ 长链接形式的标签式页链接(占用较多的位置，且长度变化较大)

function pagelink($page, $pagecount, $linkbase='', $count=-1)

{

	$sp = '&nbsp;';

	$base = $linkbase ? ($linkbase . "&") : "?";

	$pagecount = $pagecount<=0 ? 1 : $pagecount;



	$linksize = 10; $pagebegin = 1; $pageend = $linksize;

	while ($page > $pageend)

	{

		$pagebegin += $linksize; $pageend += $linksize;

	}

	$pageend = $pageend>$pagecount ? $pagecount : $pageend;



	$pagelink = "第<font color=red><b>$page</b></font>/<font color=blue><b>$pagecount</b></font>页";

	$pagelink .= ($count>-1 ? " 共<font color=red><b>$count</b></font>条" : "") . "$sp$sp";

	if ($pagecount > $linksize)

	{

		$pre10 = $pagebegin - $linksize;

		$pagelink .= ($pagebegin>$linksize ? "<a href='{$base}page=$pre10' title='前一列'>[前列]</a>$sp" : "");

	}

	$pagelink .= ($page>1 ? "<a href='{$base}page=" . ($page - 1) ."'>[上页]</a>$sp" : "[上页]$sp");

	for ($ni=$pagebegin; $ni<=$pageend; $ni++)

	{

		$pagelink .= ($ni<>$page ? "<a href='{$base}page=$ni'>[$ni]</a>$sp" : "[<font color=red><b>$ni</b></font>]$sp");

	}

	$pagelink .= ($page<$pagecount ? "<a href='{$base}page=" . ($page + 1) . "'>[下页]</a>$sp" : "[下页]$sp");

	if ($pagecount > $linksize)

	{

		$next10 = $pagebegin + $linksize;

		$pagelink .= ($next10<$pagecount ? "<a href='{$base}page=$next10' title='后一列'>[后列]</a>$sp" : "");

	}



	return $pagelink;

}



function in($string, $letter) {

	if (trim($letter) == "") return 0;

	if (strpos($string, $letter) !== false) {

		return 1;

	} else {

		return 0;

	}

}



// 测试表中是否包含所给字段, FieldList 可以是用“|”隔开的几个，只要一个没有，就返回0

function has_field($FieldList, $table) {

	global $db;

	$FieldLists = explode("|", $FieldList);

	if (count($FieldLists) > 0)

	{

		global $mysql;

		$Fields = mysql_list_fields($db->dbname, $table);

		$Columns = mysql_num_fields($Fields);

		for ($ni = 0; $ni < $Columns; $ni++)

		{

			$TableFields[] = mysql_field_name($Fields, $ni);

		}

		foreach ($FieldLists as $FieldName)

		{

			if (!in_array($FieldName, $TableFields))

			{

				return 0;

			}

		}

	}

	return 1;

}





function convert($string, $from_char_set, $to_char_set) {

	if (function_exists("iconv")) {

		return iconv($from_char_set, $to_char_set, $string);

	} else if (function_exists("mb_convert_encoding")) {

		return mb_convert_encoding($string, $to_char_set, $from_char_set);

	} else {

		exit("服务器php环境没有安装编码转换函数集，请联系服务器管理员解决，否则本系统无法运行...");

	}

}





function check_post_get() {

	$not_allow_post = array(";", "union");

	foreach ($_POST as $key => $value) {

		if (!in_array(strtolower($key), array("content"))) {

			if (! @test_allow($value, $not_allow_post)) {

				return false;

			}

		}

	}



	$not_allow_get = array("union", "'", '"');

	foreach ($_GET as $key => $value) {

		if (! @test_allow($value, $not_allow_get)) {

			return false;

		}

	}



	return true;

}



function test_allow($c_string, $a_notallow) {

	foreach ($a_notallow as $key => $value) {

		if (eregi($value, $c_string)) {

			return false;

		}

	}

	return true;

}





// 显示某用户能够管理的部门列表:

// $type:  select|array|string

// $select_part_id 选中的 part_id，只在前一个参数是 select 才有效。

function get_part_list($type, $select_part_id=0) {

	global $db, $uinfo;

	$part_id = $uinfo["part_id"];

	$li = $db->query("select * from sys_part where id='$part_id' limit 1", 1);

	$part_name = $li["name"];



	if ($type == 'select') { //下拉选择

		$parts = '<select name="part_id" class="combo">';

		$parts .= '<option value="'.$part_id.'"'.($select_part_id == $part_id ? ' selected' : '').'>'.$part_name.($select_part_id == $part_id ? ' *' : '').'</option>';

		$parts .= get_option($part_id, 1, $select_part_id);

		$parts .= '</select>';

	} else if ($type == 'array' || $type == 'string') { //数组或者串

		global $parts;

		$parts = array();

		$parts[] = $li;

		get_part_array($part_id, 1);

	}



	// 返回 string 格式的id编号 形如: 2,3,4,7,8

	if ($type == 'string') {

		$sa = array();

		foreach ($parts as $li) {

			$sa[] = $li["id"];

		}

		$parts = implode(",", $sa);

	}



	return $parts;

}



// 上一函数的option递归部分

function get_option($parent_id, $deep, $sel_id=0) {

	global $db;

	if ($deep > 10) return ''; //防止无穷递归出现

	$parts = '';

	$list = $db->query("select id,name from sys_part where pid='$parent_id'", 'id', 'name');



	if (count($list) > 0) {

		foreach ($list as $id => $name) {

			$_select = ($id == $sel_id ? ' selected' : '');

			$name .= $_select ? ' *' : '';

			$parts .= '<option value="'.$id.'"'.$_select.'>'.str_repeat('&nbsp;&nbsp;', $deep).$name.'</option>';

			$parts .= get_option($id, $deep+1, $sel_id);

		}

	}



	return $parts;

}



function get_part_array($parent_id, $deep) {

	global $db, $parts;

	if ($deep > 10) return ''; //防止无穷递归出现



	$list = $db->query("select * from sys_part where pid='$parent_id'", "id");

	foreach ($list as $id => $_li) {

		$_li["name"] = str_repeat('&nbsp;&nbsp;&nbsp;&nbsp;', $deep).$_li["name"];

		$parts[] = $_li;

		get_part_array($id, $deep+1);

	}



	return;

}



// 搜索关键词加颜色

function search_key_red($str, $key, $color="red") {

	$keys = explode(" ", $key);

	foreach ($keys as $s) {

		$s = trim($s);

		if ($s != '') {

			$str = str_replace($s, '<font color="'.$color.'">'.$s.'</font>', $str);

		}

	}

	return $str;

}







?>