<?php

/*

// 说明:

// 作者: 小陈 

// 时间:

*/

include "class.table.php";



$link_var = array("page", "sort", "order");

$param = array();

foreach ($link_var as $s) {

	$param[$s] = $_GET[$s];

}

extract($param);





$list_heads = array(

	"选" => array("width"=>"32", "align"=>"center"),

	"时间" => array("width"=>"80", "align"=>"left", "sort"=>"date", "order"=>"asc"),

	"IP" => array("width"=>"", "align"=>"center", "sort"=>"ip", "order"=>"desc"),

	"PV" => array("width"=>"", "align"=>"center", "sort"=>"pv", "order"=>"desc"),

	"点击" => array("width"=>"", "align"=>"center", "sort"=>"click", "order"=>"desc"),

	"有效点击" => array("width"=>"", "align"=>"center", "sort"=>"ok_click", "order"=>"desc"),

	"添加人" => array("width"=>"", "align"=>"center", "sort"=>"u_realname", "order"=>"desc"),

	"操作" => array("width"=>"150", "align"=>"center"),

);



$t = new table();

$t->set_head($list_heads, "时间", "desc");

$t->set_sort($_GET["sort"], $_GET["order"]);

$t->param = $param;





for ($i = 0; $i < 5; $i++) {

	$t->add(

		array(

			"选" => $i,

			"时间" => "时间".$i,

			"IP" => "-",

			"PV" => "-",

			"点击" => "-",

			"有效点击" => "-",

			"添加人" => "-",

			"操作" => "-",

		));

}



//echo "<pre>";



//print_r($t->lines);



$t->base_indent = '';



$s = $t->show();



//echo htmlspecialchars($s);



//echo "</pre>";





?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns=http://www.w3.org/1999/xhtml>

<head>

<title></title>

<meta http-equiv="Content-Type" content="text/html;charset=gb2312">

<style>

a {text-decoration:none; }

.list {border:1px solid #97E6A5; border-collapse:collapse; margin:0px; padding:0px; background:white;}

.list td {line-height:120%; }

.list .head {border:1px solid #E3E3E3; background-color:#F2F2F2; padding:4px 4px 2px 4px; color:#646464; font-weight:bold; font-size:12px;}

.list .group {border:1px solid #E3E3E3; background-color:#E1F0FF; padding:4px 4px 2px 4px; color:#000000; font-weight:bold; font-size:12px; text-align:left; padding-left:8px; }

.list .item {border:1px solid #F0F0F0; padding:5px 4px 3px 4px}

.list .hide {color:#C0C0C0; font-style:italic;}

.list .hide td {}

</style>

</head>



<body>





<?php echo $s; ?>





</body>

</html>