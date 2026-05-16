<?php
require "../../core/core.php";

if($_POST or $_GET["op"])
{
	
	//date_default_timezone_set('Asia/Shanghai');
	$con=mysql_connect($mysql_server[0],$mysql_server[1],$mysql_server[2]);
	mysql_query("set names {$mysql_server[4]}");
	mysql_select_db($mysql_server[3]);
	
	$mdate=date("Y-m-d H:i:s");
	$mdate=strtotime($mdate);
	$mt=trim($_POST["t"]);
	$id=$_GET["id"];
	$op=trim($_GET["op"]);
	$ip=$_SERVER['REMOTE_ADDR'];
	if($op=="edit")
	{
		$sql=mysql_query("select count(*) as c from mtly where hospital='$id'");
		$row=mysql_fetch_array($sql);
		if($row["c"]>0)
		{
			mysql_query("update mtly set name='$mt',ip='$ip',addtime='$mdate',hospital='$id' where hospital='$id'");
			echo '<script language="javascript">location.href="mtly.php"</script>';
		}
		else
		{
			mysql_query("INSERT INTO mtly(name,ip,addtime,hospital) values('$mt','$ip','$mdate','$id')");
			echo '<script language="javascript">location.href="mtly.php"</script>';
		}
	}
}


















?>