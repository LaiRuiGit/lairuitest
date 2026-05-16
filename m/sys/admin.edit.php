<?php defined("ROOT") or exit("Error."); ?>
<html xmlns=http://www.w3.org/1999/xhtml>
<head>
<title><?php echo $title; ?></title>
<meta http-equiv="Content-Type" content="text/html;charset=gb2312">
<link href="/res/base.css" rel="stylesheet" type="text/css">
<script src="/res/base.js" language="javascript"></script>
<style>
.man_check {float:left; width:200px; }
</style>
<script language="javascript">

var mode = "<?php echo $op; ?>";

function show_pass() {
	var pass = document.mainform.pass.value;
	if (pass != "") {
		msg_box("您输入的密码是： " + pass + "  ");
	} else {
		msg_box("您还没有输入密码！");
	}
}

function check_data() {
	oForm = document.mainform;
	if (oForm.name.value.length < 2) {
		msg_box("必须输入用户名，且长度至少2位");
		oForm.name.focus();
		return false;
	}
	if (oForm.realname.value == "") {
		msg_box("请输入真实姓名！");
		oForm.realname.focus();
		return false;
	}
	if (mode == "add" && oForm.pass.value.length < 6) {
		msg_box("必须输入密码，且长度最低6位");
		oForm.pass.focus();
		return false;
	}
	if (byid("powermode").value == "") {
		msg_box("请选择授权方式！");
		oForm.powermode.focus();
		return false;
	}
	if (byid("powermode").value == "2" && byid("character_id").value == "0") {
		msg_box("请选择权限！");
		oForm.character_id.focus();
		return false;
	}
	if (!confirm("每一项都填好了吧？请检查清楚哦，如果还想再看一下，请点击“取消”")) {
		return false;
	}
	return true;
}

function set_power_do(pid, str) {
	byid(pid).value = str;
}

function set_power(pid) {
	parent.load_box(1,'src','m/sys/admin.php?op=set_power&pid='+pid+'&power='+byid(pid).value);
}

function show_hide_detail(o) {
	if (o.value == "-1") {
		byid("detail_button").style.display = "inline";
		byid("powermode").value = "1"; //自定义
	} else {
		byid("detail_button").style.display = "none";
		byid("powermode").value = "2"; //角色
	}
}

function check_repeat(o, type) {
	if (mode == "add") {
		if (o.value == '') {
			byid(type+"_tips").innerHTML = '';
		} else {
			var s = o.value;
			var xm = new ajax();
			xm.connect("/http/check_admin_repeat.php", "GET", "&s="+(s)+"&type="+(type), check_repeat_do);
		}
	}
}

function check_repeat_do(o) {
	var out = ajax_out(o);
	if (out["status"] == "ok") {
		if (out["tips"] != '') {
			byid(out["type"]+"_tips").innerHTML = '<font color=red>'+out["tips"]+"</font> ";
			//alert("请注意，"+out["tips"]);
		} else {
			byid(out["type"]+"_tips").innerHTML = "√ ";
		}
	}
}

function update_check_color(o) {
	o.parentNode.getElementsByTagName("label")[0].style.color = o.checked ? "blue" : "";
}
</script>
</head>

<body>
<!-- 头部 begin -->
<div class="headers">
	<div class="headers_title"><span class="tips"><?php echo $title; ?></span></div>
	<div class="headers_oprate"><input type="button" value="返回" onclick="history.back()" class="button"></div>
</div>
<!-- 头部 end -->

<div class="space"></div>

<div class="description">
	<div class="d_title">修改提示：</div>
	<li class="d_item">本页数据<font color="red"><b>每项均需要认真填写</b></font>，如果填写不正确，可能导致非常严重的后果，<font color="red"><b>比如数据丢失，账号无法登录等</b></font>。若对填写有疑问，请咨询开发人员。</li>
</div>

<div class="space"></div>

<form name="mainform" method="POST" onsubmit="return check_data()">
<table width="100%" class="edit">
	<tr>
		<td colspan="2" class="head">基本数据</td>
	</tr>
	<tr>
		<td class="left"><font color="red">登录名：</font></td>
		<td class="right"><input name="name" value="<?php echo $user["name"]; ?>" class="input" size="20" style="width:120px" onchange="check_repeat(this,'name')" <?php if ($id > 0) echo "disabled"; ?>> <span id="name_tips"></span><span class="intro">创建后不能更改</span></td>
	</tr>
	<tr>
		<td class="left"><font color='red'>真实姓名：</font></td>
		<td class="right"><input name="realname" value="<?php echo $user["realname"]; ?>" class="input" size="20" style="width:120px" onchange="check_repeat(this,'realname')" <?php if ($id > 0) echo "disabled"; ?>> <span id="realname_tips"></span><span class='intro'>真实姓名仅用于显示</span></td>
	</tr>
	<tr>
		<td class="left"><font color="red">密码：</font></td>
		<td class="right"><input type="password" name="pass" value="" class="input" size="20" style="width:120px"> <a href="javascript:void(0);" onclick="show_pass();return false;">[显示我输入的密码]</a> <?php if ($id) echo "<span class='intro'>输入新的密码将覆盖原密码</span>"; ?></td>
	</tr>
</table>

<div class="space"></div>
<table width="100%" class="edit">
	<tr>
		<td colspan="2" class="head">授权</td>
	</tr>

	<tr>
		<td class="left"><font color="red">门店：</font></td>
		<td class="right">
<?php
	$hs_ids = implode(",", $hospital_ids);
	$hs_id_name = $db->query("select id,name from hospital where id in ($hs_ids) order by sort desc,id asc", "id", "name");
	foreach ($hs_id_name as $id => $name) {
		$checked = in_array($id, explode(",", $user["hospitals"])) ? "checked" : "";
?>
			<div class="man_check"><input type="checkbox" class="check" name="hospital_ids[]" value="<?php echo $id; ?>" id="hc_<?php echo $id; ?>" <?php echo $checked; ?> onclick="update_check_color(this)"><label for="hc_<?php echo $id; ?>" <?php if ($checked) echo ' style="color:red"'; ?>><?php echo $name; ?></label></div>
<?php } ?>

		</td>
	</tr>



<?php
	$select_ch = $user["powermode"] == 1 ? 0 : $user["character_id"];
?>
	<tr>
		<td class="left" id="power_detail_title"><font color="red">选择权限：</font></td>
		<td class="right" id="power_detail_data">
			<input type="hidden" name="powermode" id="powermode" value="<?php echo $user["powermode"]; ?>">
			<select name="character_id" onchange="show_hide_detail(this)" class="combo">
				<option value="0" style="color:gray">--请选择--</option>
				<!-- <option value="-1" style="color:red"<?php if ($user["powermode"]==1) echo " selected"; ?>>--自定义--</option> -->
				<?php echo list_option($ch_data, "id", "name", $select_ch); ?>
			</select> &nbsp;
			<button id="detail_button" onclick="set_power('power_detail'); return false;" class="buttonb"<?php if ($user["powermode"]!=1) echo ' style="display:none"'; ?>>自定义...</button><span class="intro">必须设定权限</span>
			<input type="hidden" name="power_detail" id="power_detail" value="<?php echo $user["menu"]; ?>">
		</td>
	</tr>


	<tr>
		<td class="left"><font color="red">部门：</font></td>
		<td class="right" valign="top">
			<select name="part_id" class="combo">
			<?php //echo list_option($part->get_sub_part_list(intval($uinfo["part_id"]), 1), "_key_", "_value_", $user["part_id"]); ?>
			<?php echo list_option(get_part_list('array'), "id", "name", $user["part_id"]); ?>
			</select>
<?php if ($debug_mode || $username == "admin" || $uinfo["part_admin"]) { ?>
			<input type="checkbox" class="check" name="part_admin" value="1" id="part_admin" <?php if ($user["part_admin"]) echo "checked"; ?>><label for="part_admin">部门管理员</label>
<?php } ?>
			<span class='intro'>部门必须选择。如果选择部门管理员，则该人员有权管理其本部门（包括所有下属部门）数据</span>
		</td>
	</tr>

	<tr>
		<td class="left"><font color="red">数据管理：</font></td>
		<td class="right">
			<table width="100%">
				<tr>
					<td width="100%" align="left">
<?php
//$part_level = $part->get_sub_part(intval($uinfo["part_id"]), 1);
$part_level = get_part_list('array');
$cur_part_m = explode(",", $user["part_manage"]);
foreach ($part_level as $v) {
	$p_id = $v["id"];
	$p_level = $v["level"];
		//echo str_repeat("&nbsp;&nbsp;&nbsp;&nbsp;", $p_level)."<input type='checkbox' name='part_manage[]' value='".$p_id."' id='chp_".$p_id."'".(in_array($p_id, $cur_part_m) ? " checked" : "")."><label for='chp_".$p_id."'>".$part->part_id_name[$p_id]."</label><br>";
		echo "<input type='checkbox' name='part_manage[]' value='".$p_id."' id='chp_".$p_id."'".(in_array($p_id, $cur_part_m) ? " checked" : "")."><label for='chp_".$p_id."'>".$v["name"]."</label><br>";

}

?>
						<div class="clear"></div>
					</td>
					<!-- <td width="" align="left">
						<span class='intro'>选择则表示该人员可以管理所选部门的数据<br>包括其所有下属部门</span>
					</td> -->
				</tr>
			</table>
		</td>
	</tr>

<?php if ($debug_mode || $username == "admin") { ?>
	<tr>
		<td class="left"><font color="red">显示号码：</font></td>
		<td class="right"><input type="checkbox" name="show_tel" value="1" <?php if ($user["show_tel"]) echo "checked"; ?> id="chk0001"><label for="chk0001">显示其他顾客的电话号码</label> (例如，电话回访人员需要勾选此项)</td>
	</tr>
<?php } ?>


</table>

<div class="space"></div>

<input type="hidden" name="id" value="<?php echo $user["id"]; ?>">
<input type="hidden" name="back_url" value="<?php echo $_GET["back_url"]; ?>">
<input type="hidden" name="op" value="<?php echo $op; ?>">
<input type="hidden" name="edit_mode" value="all">
<div class="button_line"><input type="submit" class="submit" value="提交资料"></div>


</form>

<div class="space"></div>
</body>
</html>