<?php

/*

// - 功能说明 : 门店列表

// - 创建作者 : 小陈 

// - 创建时间 : 2013-05-01 00:36

*/

require "../../core/core.php";

$mod = $table = "hospital";



// 操作的处理:

if ($op = $_GET["op"]) {

	include $mod.".op.php";

}



include $mod.".list.php";



?>