<?php
require "../core/core.php";
include "../core/function.lunar.php";
if($_SESSION[$cfgSessionName]["username"]!="admin")
{
	echo '<script language="javascript">
function CloseWin()   
{    
	window.opener=null;      
	window.open("","_self");    
	window.close();    
}
alert("已全部发送完成！");
CloseWin();
</script>';
	exit();
}
date_default_timezone_set('PRC');
header("Content-Type:text/html;charset=gb2312");

function request_by_other($remote_server, $post_string)
{
	global $tel_Account;
	$context = array(
		'http' => array(
			'method' => 'POST',
			'header' => 'Content-type: application/x-www-form-urlencoded' .
						'\r\n'.'User-Agent : Jimmy\'s POST Example beta' .
						'\r\n'.'Content-length:' . strlen($post_string) + 8,
			'content' => 'mypost=' . $post_string)
		);
	$stream_context = stream_context_create($context);
	$data = file_get_contents($remote_server, false, $stream_context);
	return $data;
}

function get_xml($xml)
{
	$xml = simplexml_load_string($xml);
	$aa=$xml->children();
	return $aa->errorcode;
}

function today($id,$hospital,&$i,&$d)
{
	global $tel_Account;
	$me_today=strtotime(date("Y-m-d"));
	$get_sql=mysql_query("select * from patient_{$id} where order_date>=$me_today");
	while($row=mysql_fetch_array($get_sql))
	{
		$tel=$row["tel"];
		$zhuanjia_num=$row["zhuanjia_num"];
		$name=$row["name"];
		if(preg_match("/1[3458]{1}\d{9}$/",$tel)){
			
			$strmsg="温馨提醒：{$name}你好，你之前预约{$hospital}的绿色就诊通道。预约号为{$zhuanjia_num}。请您于今日凭此号到店就诊。";
			$msg=urlencode ($strmsg);
			$post_string = "aaa=go&func=sendsms&username={$tel_Account[0]}&password={$tel_Account[1]}&mobiles={$tel}&message={$msg}";
			$t=request_by_other('http://sms.c8686.com/Api/BayouSmsApiEx.aspx',$post_string);
			if(get_xml(trim($t))==0)
			{
				$i++;
			}
			else
			{
				$d++;
			}
			
		}
	}
}

$con=mysql_connect($mysql_server[0],$mysql_server[1],$mysql_server[2]);
if(!$con)
{
	die("连接数据库错误！");
}
mysql_query("set names gbk");
mysql_select_db("guahao_x",$con);
$sql=mysql_query("select * from hospital");
$i=0;
$d=0;
while($row=mysql_fetch_array($sql))
{
	today($row["id"],$row["name"],$i,$d);
}

echo "已成功发送：<span style=\"color:#047b04\">".$i."</span>条<br>\r\n";
echo "发送失败：<span style=\"color:red\">".$d."</span>条<br>\r\n";
?>
<script language="javascript">
function CloseWin()   
{    
	window.opener=null;      
	window.open("","_self");    
	window.close();    
}
alert("已全部发送完成！");
CloseWin();
</script>