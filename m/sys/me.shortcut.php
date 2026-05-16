<?php

/*

// - 功能说明 : 创建管理员个人的快捷方式菜单

// - 创建作者 : 小陈 

// - 创建时间 : 2006-12-16 01:13

*/

require "../../core/core.php";



if (!$uid) {

	exit_html("不能选择快捷方式...");

}



if ($_POST) {

	$theMenu = "";

	foreach ($_POST as $name => $value) {

		list($left, $right) = explode("_", $name);

		if ($left == "item") {

			$theMenu .= (strlen($theMenu)>0 ? "," : "") . $right;

		}

	}

	if ($db->query("update sys_admin set shortcut='$theMenu' where name='$username' limit 1")) {

		update_main_frame();

		exit;

		//msg_box("快捷方式修改成功", "back", 1);

	} else {

		msg_box("很抱歉，数据提交失败，请稍后再试。", "back", 1);

	}

}



?>

<html xmlns=http://www.w3.org/1999/xhtml>

<head>

<title>选择快捷方式</title>

<meta http-equiv="Content-Type" content="text/html;charset=gb2312">

<link href="/res/base.css" rel="stylesheet" type="text/css">

<script src="/res/base.js" language="javascript"></script>

<style>

.my_short {float:left; width:150px; margin:3px; }

</style>

</head>



<body>

<!-- 头部 begin -->

<div class="headers">

	<div class="headers_title"><span class="tips">选择快捷方式</span></div>

	<div class="headers_oprate"><input type="button" value="返回" onclick="history.back()" class="button"></div>

</div>

<!-- 头部 end -->



<div class="space"></div>



<div class="description">

	<div class="d_title">提示：</div>

	<li class="d_item">请勿选择过多的快捷菜单，否则将造成左侧菜单过长而显得拥挤，建议10个以内</li>

	<li class="d_item">如果全部不选，将显示系统默认的快捷菜单（其由系统总管理员在菜单创建时设定）</li>

	<li class="d_item">如果需要关闭“快捷方式”栏，请取消“我的后台”->“选项设置”->“常规选项”->“显示‘快捷方式’栏”的勾选</li>

	<li class="d_item">修改成功后，系统将立即更新显示</li>

</div>



<div class="space"></div>



<form method="POST" name="mainform">

<table width="100%" class="edit">

	<tr>

		<td colspan="2" class="head">请选择快捷方式：</td>

	</tr>



<?php

$aitems = explode(",", $shortcut);

$perline = 4;

$tdwidth = intval(100 / $perline) . "%";



$menu_data = array();

$tmp_data = $db->query("select id,title,link from sys_menu order by sort");

foreach ($tmp_data as $tmp_line) {

	$menu_data[$tmp_line["id"]] = $tmp_line;

}



list($m_stru, $m_power) = $power->parse_menu($usermenu);



foreach ($m_stru as $mid => $items) {

	if (count($items) > 0) {

?>

	<tr>

		<td class="left"><b><?php echo $menu_data[$mid]["title"]; ?></b>：</td>

		<td class="right">

<?php

foreach ($items as $v) {

	$title = $menu_data[$v]["title"];

?>

			<div class="my_short"><input type='checkbox' class='check' name='item_<?php echo $v; ?>' id='item_<?php echo $v; ?>'<?php echo (in_array($v, $aitems) ? " checked" : ""); ?>><label for='item_<?php echo $v; ?>'><?php echo $title; ?></label></div>

<?php

}

?>

		</td>

	</tr>

<?php

	}

}

?>

</table>



<div class="button_line"><input type="submit" class="submit" value="提交资料"></div>

<input type="hidden" name="formsubmit" value="1">

</form>



<div class="space"></div>

</body>

</html>