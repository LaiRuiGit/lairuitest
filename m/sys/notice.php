<?php
/*
// 说明: 显示通知
// 作者: 小陈 
// 时间: 2010-09-17
*/
require "../../core/core.php";

$id = $_GET["id"];

$line = $db->query("select * from sys_notice where id=$id limit 1", 1);

//$title = cut("通知: ".$line["title"], 30, "..");

$title = "通知";


?>
<html xmlns=http://www.w3.org/1999/xhtml>
<head>
<title><?php echo $title; ?></title>
<meta http-equiv="Content-Type" content="text/html;charset=gb2312">
<link href="/res/base.css" rel="stylesheet" type="text/css">
<script src="/res/base.js" language="javascript"></script>
<style>
#m_title {padding:20px 0 0 0; font-weight:bold; font-size:16px; color:red; text-align:center; }
#m_info {padding:15px 0 0 0; text-align:center; color:gray; }
#m_content {margin:15px 0px 0px 0px; text-align:left; font-size:14px; height:350px; overflow:auto; }
</style>
<script language="javascript">
function update_read() {
	var id = byid("id").value;
	var xm = new ajax();
	xm.connect("/http/set_notice_read.php", "GET", "id="+id, update_read_do);
}

function update_read_do() {
	parent.load_box(0);
	parent.get_online(); //立即更新显示
}

</script>
</head>

<body>


<div id="m_title"><?php echo $line["title"]; ?></div>
<div id="m_info">发布人：<?php echo $line["u_realname"]; ?>　　时间：<?php echo date("Y-m-d H:i", $line["addtime"]); ?></div>
<div id="m_content"><?php echo text_show($line["content"]); ?></div>

<div style="padding:20px 0 5px 0; text-align:center; ">
	<input type="submit" class="submit" onclick="update_read()" value="阅读完毕">
</div>

<input type="hidden" id="id" value="<?php echo $id; ?>">


</body>
</html>