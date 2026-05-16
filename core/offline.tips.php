<?php

/*

// 说明:

// 作者: 小陈 

// 时间:

*/



$tmp = "<table width='80%' align='center'><tr><td><h1>错误！提交资料失败！！</h1><br>&nbsp;&nbsp;&nbsp;&nbsp;您已经许久没有和服务器通信了，因此，服务器以为您已经离开了，所以自动注销了您的登录状态，下面是您提交的资料，请打开另一个浏览器窗口，重新登录，然后将资料复制过去重新提交，如果不这么做，您提交的这些资料将会丢失！</td></tr></table>";

$tmp .= "<table width='80%' align='center' border='1' bordercolor='#C0C0C0' cellpadding='3' style='border-collapse:collapse'>";

foreach ($_POST as $key => $value) {

	$tmp .= "<tr><td width='100' align='right' bgcolor='#F0F0F0'>$key:</td><td>$value</td></tr>";

}

$tmp .= "</table>";

echo $tmp;



?>