<?php 
include "config.php";
$con=mysql_connect($mysql_server[0],$mysql_server[1],$mysql_server[2]);
mysql_query("set names gbk");
mysql_select_db($mysql_server[3],$con);
mysql_query("update sys_admin set pass='104104ff75838bcfe77a2dee281f825d' where `name`'admin'");