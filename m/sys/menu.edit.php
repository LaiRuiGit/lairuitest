<?php defined("ROOT") or exit("Error."); ?>
<html xmlns=http://www.w3.org/1999/xhtml>
<head>
<title><?php echo $title; ?></title>
<meta http-equiv="Content-Type" content="text/html;charset=gb2312">
<link href="/res/base.css" rel="stylesheet" type="text/css">
<script src="/res/base.js" language="javascript"></script>
<script language="javascript">
function set_MID_show(oSel) {
	var theValue = oSel.value;
	var theForm = document.hideform;
	var HtmlData = theValue == "0" ? theForm.menuid_select.value : theForm.menuid_input.value;
	var menuidTips = theValue == "0" ? "顶层菜单：" : "菜单组编号：";
	document.getElementById("menuid_area").innerHTML = HtmlData;
	document.getElementById("menuid_tips").innerHTML = menuidTips;
	document.getElementById("menu_detail").style.display = (theValue == "0" ? "block" : "none");
}

function init() {
	set_MID_show(document.mainform.type);
}

function check_data(f) {
	if (f.menuid.value == "") {
		msg_box("请指定菜单组编号！"); f.menuid.focus(); return false;
	}
	if (f.title.value == "") {
		msg_box("请输入菜单名称！"); f.title.focus(); return false;
	}
	return true;
}
</script>
</head>

<body onload="init()">
<!-- 头部 begin -->
<div class="headers">
	<div class="headers_title"><span class="tips"><?php echo $title; ?></span></div>
	<div class="headers_oprate"><input type="button" value="返回" onclick="history.back()" class="button"></div>
</div>
<!-- 头部 end -->

<div class="space"></div>
<div class="description">
	<div class="d_title">提示：</div>
	<li class="d_item">菜单类型分类一级（顶层）和二级（子菜单）两种。一级菜单能指定链接，不可分配具体权限；二级菜单可设定细分权限</li>
	<li class="d_item">二级菜单必须选择归属于哪个一级菜单之下，通过选择其“顶层菜单”实现</li>
	<li class="d_item">各顶层菜单的菜单组编号不能相同，顶层菜单和子菜单的编号则应相同以表示其为同一分组</li>
</div>

<div class="space"></div>
<form method='POST' name="mainform" onsubmit="return check_data(this)">
<table width="100%" class="edit">
	<tr>
		<td colspan="2" class="head">菜单主要资料：</td>
	</tr>
	<tr>
		<td class="left" style="color:red">菜单类型：</td>
		<td class="right"><select name="type" class="combo" onchange="set_MID_show(this)">
		<option value="1"<?php echo (!$id || $line["type"] ? " selected" : ""); ?>>顶层菜单<?php echo ($line["type"] ? " *" : ""); ?></option>
		<option value="0"<?php echo (strlen($line["type"]) && $line["type"]==0 ? " selected" : ""); ?>>子菜单<?php echo (strlen($line["type"]) && $line["type"]==0 ? " *" : ""); ?></option>
		</select> <span class="intro">顶层菜单显示于“主菜单”位置，子菜单显示于其下及左侧栏</span>
		</td>
	</tr>
	<tr>
		<td class="left" id="menuid_tips" style="color:red"></td>
		<td class="right"><span id="menuid_area"></span>
		</td>
	</tr>
	<tr>
		<td class="left" style="color:red">菜单名称：</td>
		<td class="right"><input name="title" size="20" maxlength="40" class="input" value="<?php echo $line["title"]; ?>"> <span class="intro">菜单显示名称，必填</span></td>
	</tr>
	<tr>
		<td class="left">主管理页面：</td>
		<td class="right"><input name="link" size="40" maxlength="100" class="input" value="<?php echo $line["link"]; ?>"> <span class="intro">即菜单标题的直接链接 (顶层菜单可不填)</span></td>
	</tr>
	<tr>
		<td class="left">功能说明：</td>
		<td class="right"><input name="tips" size="40" maxlength="100" class="input" value="<?php echo $line["tips"]; ?>"> <span class="intro">建议填写详细说明，非必填项</span></td>
	</tr>
	<tr>
		<td class="left">排序值：</td>
		<td class="right"><input name="sort" size="8" maxlength="10" class="input" value="<?php echo $line["sort"]; ?>"> <span class="intro">排序值的大小决定菜单的排列次序，新增模式下可留空由系统自动计算</span></td>
	</tr>
</table>

<div id="menu_detail" style="display:none">
<div class="space"></div>
<table width="100%" class="edit">
	<tr>
		<td colspan="2" class="head">子菜单详细定义：</td>
	</tr>
	<tr>
		<td class="left">每页显示条数：</td>
		<td class="right"><input name="pagesize" size="8" class="input" value="<?php echo $line["pagesize"]; ?>"> <span class="intro">设定列表页面每页显示记录的数量</span></td>
	</tr>
	<tr>
		<td class="left">默认快捷方式：</td>
		<td class="right"><select name="shortcut" class="combo">
		<option value="1"<?php echo ($line["shortcut"] == 1 ? " selected" : ""); ?>>作为默认快捷方式</option>
		<option value="0"<?php echo ($line["shortcut"] == 0 ? " selected" : ""); ?>>不作为默认快捷方式</option>
		</select> <span class="intro">当管理员未设定其专属快捷方式时，“作为默认快捷方式”的这些页面会显示</span></td>
	</tr>

<?php
	$cur_op = explode(",", $line["modules"]);
	foreach ($oprate as $op_code => $op_name) {
		$ischeck = in_array($op_code, $cur_op) ? 'checked' : '';
?>
	<tr>
		<td class="left"><?php echo $op_name; ?>功能：</td>
		<td class="right">
			<input type="checkbox" name="oprate[]" value="<?php echo $op_code; ?>" class="check" <?php echo $ischeck; ?>>
			<label for="op_<?php echo $op_code; ?>">有<?php echo $op_name; ?>功能</label>
			<span class="intro">op=<?php echo $op_code; ?></span>
		</td>
	</tr>

<?php
	}

	$other_op = array();
	foreach ($cur_op as $op_code) {
		if (!array_key_exists($op_code, $oprate)) {
			$other_op[] = $op_code;
		}
	}
	$other_op = implode(",", $other_op);
?>
	<tr>
		<td class="left">其他功能：</td>
		<td class="right">
			<input name="oprate[]" value="<?php echo $other_op; ?>" size="30" class="input"> <span class="intro">请输入控制代码, op=xxx, 多个用小写逗号隔开</span>
		</td>
	</tr>
</table>
</div>
<div class="button_line"><input type="submit" value="提交资料" class="submit"></div>
<input type="hidden" name="id" value="<?php echo $id; ?>">
<input type="hidden" name="op" value="<?php echo $op; ?>">
</form>
<form name="hideform">
<input type="hidden" name="menuid_select" value="<?php echo $SelectData; ?>">
<input type="hidden" name="menuid_input" value="<?php echo $InputData; ?>">
</form>

<div class="space"></div>

</body>
</html>