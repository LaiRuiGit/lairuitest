<?php
/*
// - 功能说明 : 新增、修改顾客资料
// - 创建作者 : 小陈 
// - 创建时间 : 2013-05-01 05:57
*/
require "../../core/core.php";
if ($_POST) {
	$id=trim($_GET["id"]);
	$name = trim($_POST["name"]);
	$sex = trim($_POST["sex"]);
	$age = trim($_POST["age"]);
	$tel = trim($_POST["tel"]);
	$qq = trim($_POST["qq"]);
	$content = trim($_POST["content"]);
	$zhuanjia_num = trim($_POST["zhuanjia_num"]);
	$order_date = strtotime(trim($_POST["order_date"]));
	$memo = trim($_POST["memo"]);
	$status = trim($_POST["status"]);
	
	$d_id = $db->query("update yy_list set name='$name',sex='$sex',age='$age',tel='$tel',qq='$qq',content='$content',zhuanjia_num='$zhuanjia_num',order_date='$order_date',memo='$memo',status='$status' where id='{$id}'");
	
	echo '<script language="javascript">alert("修改成功！");</script>';
}

//选择菜单函数
function list_option_a($list, $key_field='_key_', $value_field='_value_', $default_value='') {
	$option = array();
	foreach ($list as $k => $li) {
		// option value=的值
		if ($key_field != '') {
			if ($key_field == "_key_" || $key_field == "_value_") {
				$value = $key_field == "_key_" ? $k : $li;
			} else {
				$value = $li[$key_field];
			}
		} else {
			$value = $li;
		}

		// 是否选择:
		$select = ($value == $default_value ? 'selected' : '');

		// 显示标题:
		if ($value_field != '') {
			if ($value_field == "_key_" || $value_field == "_value_") {
				$title = $value_field == "_key_" ? $k : $li;
			} else {
				$title = $li[$value_field];
			}
		} else {
			$title = $li;
		}
		// 如果为当前，显示一个 * 标记:
		if ($select) {
			$title .= " *";
		}
		$option[] = '<option value="'.$value.'" '.$select.'>'.$title.'</option>';
	}

	return implode('', $option);
}

$line = $db->query_first("select * from yy_list where id='$id' limit 1");
?>
<html>
<head>
<title></title>
<meta http-equiv="Content-Type" content="text/html;charset=gb2312">
<link href="/res/base.css" rel="stylesheet" type="text/css">
<script src="/res/base.js" language="javascript"></script>
<script src="/res/datejs/picker.js" language="javascript"></script>
<style>
.dischk {width:6em; height:16px; line-height:16px; vertical-align:middle; white-space:nowrap; text-overflow:ellipsis; overflow:hidden; padding:0; margin:0; }
</style>
<script language="javascript">
function input(id, value) {
	if (byid(id).disabled != true) {
		byid(id).value = value;
	}
}
</script>
</head>

<body>
<!-- 头部 begin -->
<div class="headers">
	<div class="headers_title"><span class="tips">修改顾客资料</span></div>
	<div class="header_center"><!-- <button onclick="if (check_data()) document.forms['mainform'].submit();" class="buttonb">提交数据</button> --></div>
	<div class="headers_oprate"><button onClick="history.back()" class="button">返回</button></div>
</div>
<!-- 头部 end -->

<div class="space"></div>
<div class="description">
	<div class="d_title">提示：</div>
	<div class="d_item">1.姓名必须填写；　2.电话号码如果填写，则必须是数字，不少于7位；　3.未尽资料填写于备注中。</div>
</div>

<div class="space"></div>
<form name="mainform" method="POST" onSubmit="return check_data()">
<table width="100%" class="edit">
	<tr>
		<td colspan="2" class="head">顾客基本资料</td>
	</tr>
	<tr>
		<td class="left">姓名：</td>
		<td class="right"><input name="name" id="name" value="<?php echo $line["name"]; ?>" class="input" style="width:200px" <?php echo $ce["name"]; ?>> <span class="intro">* 名称必须填写</span></td>
	</tr>
	<tr>
		<td class="left">性别：</td>
		<td class="right"><input name="sex" id="sex" value="<?php echo $line["sex"]; ?>" class="input" style="width:80px" <?php echo $ce["sex"]; ?>> <a href="javascript:input('sex', '男')">[男]</a> <a href="javascript:input('sex', '女')">[女]</a> <span class="intro">填写顾客性别</span></td>
	</tr>
	<tr>
		<td class="left">年龄：</td>
		<td class="right"><input name="age" id="age" value="<?php echo $line["age"]; ?>" class="input" style="width:80px" <?php echo $ce["age"]; ?>> <span class="intro">填写年龄</span></td>
	</tr>
<?php if ($op == "add" || ($op == "edit" && $line["author"] == $realname)) { ?>
	<tr>
		<td class="left">电话：</td>
		<td class="right"><input name="tel" id="tel" value="<?php echo $line["tel"]; ?>" class="input" style="width:200px" <?php echo $ce["tel"]; ?> onChange="check_repeat('tel', this)">  <span class="intro">电话号码或手机(可不填)</span></td>
	</tr>
<?php } ?>
	<tr hidden>
		<td class="left">QQ：</td>
		<td class="right"><input name="qq" value="<?php echo $line["qq"]; ?>" class="input" style="width:140px" <?php echo $ce["qq"]; ?>>  <span class="intro">顾客QQ号码</span></td>
	</tr>
	<tr>
		<td class="left" valign="top">房子需求：</td>
		<td class="right"><textarea name="content" style="width:60%; height:72px;vertical-align:middle;" <?php echo $ce["content"]; ?> class="input"><?php echo $line["content"]; ?></textarea> <span class="intro">房子需求总结</span></td>
	</tr>

	<tr>
		<td class="left">所属门店：</td>
		<td class="right" style="color:red"><?php echo $line["ks"]; ?></td>
	</tr>

	<tr>
		<td class="left">装修类型：</td>
		<td class="right">
        <?php echo $line["disease_id"]; ?>
        </td>
	</tr>

	<tr>
		<td class="left">所在小区：</td>
		<td class="right"><input name="zhuanjia_num" value="<?php echo $line["zhuanjia_num"]; ?>" class="input" size="30" style="width:200px"></td>
	</tr>
	<tr>
		<td class="left" valign="top">预约时间：</td>
		<td class="right"><input name="order_date" value="<?php echo $line["order_date"] ? @date('Y-m-d H:i:s', $line["order_date"]) : ''; ?>" class="input" style="width:150px" id="order_date" <?php echo $ce["order_date"]; ?>> <img src="/res/img/calendar.gif" id="order_date" onClick="picker({el:'order_date',dateFmt:'yyyy-MM-dd HH:mm:ss'})" align="absmiddle" style="cursor:pointer" title="选择时间"> <span class="intro">请注意，此处已调整，预约时间不能早于上个月<?php echo date("j"); ?>号，否则资料无法提交</span></td>
	</tr>

	<tr>
		<td class="left" valign="top">备注：</td>
		<td class="right"><textarea name="memo" style="width:60%; height:48px;vertical-align:middle;" class="input" <?php echo $ce["memo"]; ?>><?php echo $line["memo"]; ?></textarea> <span class="intro">其他备注信息</span></td>
	</tr>
<?php if ($line["edit_log"] && $line["author"] == $realname) { ?>
	<?php } ?>


<?php // 治疗项目 -------------  ?>
<?php
if (in_array($uinfo["part_id"], array(4,9,12)) && $line["status"] == 1) { ?>
	<!-- 治疗费用 -->
	<?php } ?>


<?php // 复查 -------------  ?>
<?php
if (in_array($uinfo["part_id"], array(4,9,12)) && $line["status"] == 1) { ?>
	<?php } ?>

</table>


<div class="space"></div>
<table width="100%" class="edit">
	<tr>
		<td colspan="2" class="head">是否到店</td>
	</tr>
	<tr>
		<td class="left">赴约状态：</td>
		<td class="right">
        <?php $me_status_array = array ( 0 => array ( 'id' => '0','name' => '待预约'),1 => array ( 'id' => '1','name' => '已签约'),2 => array ( 'id' => '2','name' => '已放弃'),3 => array ( 'id' => '3','name' => '已量尺'),4 => array ( 'id' => '4','name' => '已报价'));?>
			<select name="status" class="combo" <?php echo $ce["status"]; ?>> <!-- onchange="change_yisheng(this.value)" -->
				<option value="0" style="color:gray">--请选择--</option>
				<?php echo list_option_a($me_status_array, 'id', 'name', ($mode == "add" && $uinfo["part_id"] == 4) ? 1 : $line["status"]); ?>
			</select>
		</td>
	</tr>
  </table>

<div class="button_line"><input type="submit" class="submit" value="提交资料"></div>
</form>
</body>
</html>