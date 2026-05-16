<?php

/*

// - 功能说明 : 搜索引擎设置

// - 创建作者 : 小陈 

// - 创建时间 : 2010-07-31 14:47

*/

require "../../core/core.php";

$table = $mod = "engine";



if ($op) {

	include $mod.".op.php";

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

	1=>array("title"=>"名称", "width"=>"60%", "align"=>"left", "sort"=>"binary name", "defaultorder"=>1),

	3=>array("title"=>"添加时间", "width"=>"20%", "align"=>"center", "sort"=>"addtime", "defaultorder"=>2),

	4=>array("title"=>"操作", "width"=>"12%", "align"=>"center"),

);



// 默认排序方式:

$defaultsort = 3;

$defaultorder = 1;





// 查询条件:

$where = array();

if ($searchword) {

	$where[] = "(binary name like '%{$searchword}%')";

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

//$sqlsort = "order by hospital, id asc";



// 分页数据:

$pagesize = 9999;

$count = $db->query_count("select count(*) from $table $sqlwhere");

$pagecount = max(ceil($count / $pagesize), 1);

$page = max(min($pagecount, intval($page)), 1);

$offset = ($page - 1) * $pagesize;



// 查询:

$data = $db->query("select * from $table $sqlwhere $sqlsort limit $offset,$pagesize");



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

	<div class="headers_title" style="width:50%"><span class="tips">搜索引擎来源(全局，应用于所有门店)</span></div>

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

		if (check_power("delete")) {

			$op[] = "<a href='?op=delete&id=$id' onclick='return isdel()' class='op'>删除</a>";

		}

		$op_button = implode(" ", $op);



		$hide_line = ($pinfo && $pinfo["ishide"] && $line["isshow"] != 1) ? 1 : 0;

?>

	<tr<?php echo $hide_line ? " class='hide'" : ""; ?>>

		<td align="center" class="item"><input name="delcheck" type="checkbox" value="<?php echo $id; ?>" onpropertychange="set_item_color(this)"></td>

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

	<div class="footer_op_left"><button onclick="select_all()" class="button">全选</button>&nbsp;<button onclick="unselect()" class="button">反选</button>&nbsp;<?php echo $power->show_button("hide"); ?></div>

	<div class="footer_op_right"><?php echo pagelinkc($page, $pagecount, $count, make_link_info($aLinkInfo, "page"), "button"); ?></div>

</div>

<!-- 分页链接 end -->



</body>

</html>