<?php

/*

// - 功能说明 : 选项设置

// - 创建作者 : 小陈 

// - 创建时间 : 2007-07-19 09:46

*/

require "../../core/core.php";

$table = "sys_admin";



if (!$uid) {

	exit_html("不能修改配置资料...");

}



if ($_POST) {

	$new_config = array();

	if ($uinfo["config"] != '') {

		$new_config = @unserialize($uinfo["config"]);

	}



	$new_config["jsmenu"] = intval($_POST["jsmenu"]);

	$new_config["shortcut"] = intval($_POST["shortcut"]);

	$new_config["submenu_pos"] = intval($_POST["submenu_pos"]);



	$new_str = serialize($new_config);



	if ($uid > 0) {

		$sql = "update $table set config='$new_str' where id=$uid limit 1";



		if ($db->query($sql)) {

			//msg_box("选项修改成功", "", 0);

			update_main_frame();

			exit;

		} else {

			msg_box("资料提交失败，系统繁忙，请稍后再试。", "back", 1, 5);

		}

	}

}



?>

<html xmlns=http://www.w3.org/1999/xhtml>

<head>

<title><?php echo $pinfo["title"]; ?></title>

<meta http-equiv="Content-Type" content="text/html;charset=gb2312">

<link href="/res/base.css" rel="stylesheet" type="text/css">

<script src="/res/base.js" language="javascript"></script>

</head>



<body>

<!-- 头部 begin -->

<div class="headers">

	<div class="headers_title"><span class="tips"><?php echo $pinfo["title"]; ?></span></div>

	<div class="headers_oprate"><input type="button" value="返回" onclick="history.back()" class="button"></div>

</div>

<!-- 头部 end -->



<div class="space"></div>



<div class="description">

	<div class="d_title">提示：</div>

	<li class="d_item">请依据您的个人喜好设置界面；本页选项提交后将会立即更新显示</li>

</div>



<div class="space"></div>



<form name="mainform" action="" method="POST">

<table width="100%" class="edit">

	<tr>

		<td colspan="2" class="head">界面选项</td>

	</tr>

	<tr>

		<td class="left" style="width:25%">动态下拉菜单: </td>

		<td class="right">

			<input type="checkbox" name="jsmenu" value="1" <?php echo $config["jsmenu"] ? "checked" : ""; ?> id="showjsmenu" class="check"><label for="showjsmenu">显示动态下拉菜单</label>

		</td>

	</tr>

	<tr>

		<td class="left">快捷方式: </td>

		<td class="right">

			<input type="checkbox" name="shortcut" value="1" <?php echo $config["shortcut"] ? "checked" : ""; ?> id="showshortcut" class="check"><label for="showshortcut">显示“快捷方式”栏</label>

		</td>

	</tr>

	<tr>

		<td class="left">侧栏显示位置：</td>

		<td class="right">

			<select name="submenu_pos" class="combo">

				<option value="1" <?php if (intval($config["submenu_pos"]) === 1) echo "selected"; ?>>左侧<?php if (intval($config["submenu_pos"]) === 1) echo " *"; ?></option>

				<option value="0" <?php if ($config["submenu_pos"] == 0) echo "selected"; ?>>不显示<?php if ($config["submenu_pos"] == 0) echo " *"; ?></option>

			</select>

		</td>

	</tr>

</table>



<div class="button_line"><input type="submit" class="submit" value="提交资料"></div>

<input type="hidden" name="back_url" value="<?php echo $_GET["back_url"]; ?>">

</form>



<div class="space"></div>

</body>

</html>