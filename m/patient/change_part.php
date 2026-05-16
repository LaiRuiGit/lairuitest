<?php

/*

// - 功能说明 : 部门统一转换工具

// - 创建作者 : 小陈 

// - 创建时间 : 2013-06-06 14:50

*/

require "../../core/core.php";

$table = "patient_".$user_hospital_id;



if ($user_hospital_id == 0) {

	exit_html("对不起，没有选择门店，不能执行该操作！");

}



if ($_POST) {

	$author = $_POST["author"];

	$part_id = $_POST["part_id"];

	if ($author != '' && $part_id != '') {

		if ($db->query("update $table set part_id='$part_id' where binary author='$author'")) {

			msg_box("处理成功！", "?", 1);

		}

	}

}



if ($_GET["do"] == "all") {

	$kefu_list = $db->query("select id,realname,part_id from sys_admin where hospitals='$user_hospital_id' and part_id in (2,3,4)");

	foreach ($kefu_list as $li) {

		$author = $li["realname"];

		$part_id = $li["part_id"];

		if ($author != '' && $part_id > 0) {

			$db->query("update $table set part_id=$part_id where binary author='$author'");

		}

	}

	msg_box("全部处理成功！", "?", 1);

}





$title = '部门调整工具';



$part_id_name = $db->query("select id,name from sys_part", "id", "name");



$kefu_list = $db->query("select id,realname,part_id from sys_admin where hospitals='$user_hospital_id' and part_id in (1,2,3,4)");

foreach ($kefu_list as $k => $li) {

	$kefu_list[$k]["showname"] = $li["realname"]." (".$part_id_name[$li["part_id"]].")";

}

?>

<html>

<head>

<title><?php echo $title; ?></title>

<meta http-equiv="Content-Type" content="text/html;charset=gb2312">

<link href="/res/base.css" rel="stylesheet" type="text/css">

<script src="/res/base.js" language="javascript"></script>

<script src="/res/datejs/picker.js" language="javascript"></script>

<script language="javascript">

function Check() {

	var oForm = document.mainform;

	if (oForm.author.value == "") {

		alert("请选择“名字”！"); oForm.author.focus(); return false;

	}

	if (oForm.part_id.value == "") {

		alert("请选择要设置的新的部门！"); oForm.part_id.focus(); return false;

	}

	if (confirm("是否确定？再仔细看清楚确认下，别弄错了哦！")) {

		return true;

	} else {

		return false;

	}

}

</script>

</head>



<body>

<!-- 头部 begin -->

<div class="headers">

	<div class="headers_title"><span class="tips"><?php echo $title; ?></span></div>

	<div class="headers_oprate"><button onclick="history.back()" class="button">返回</button></div>

</div>

<!-- 头部 end -->



<div class="space"></div>



<div class="description">

	<div class="d_title">这个工具是解决什么问题的？</div>

	<div class="d_item">&nbsp;&nbsp;本工具是为了解决如下问题而开发的：某人，比如“张三”，在创建帐户的时候，部门是“网络销售”，但实际上该人是电话销售，所以添加的顾客资料将只在网络销售这个部门里显示，而不会显示在电话部门里。后来，即使将此人的部门重新调整到电话部门，那些旧的顾客资料也将不会自动变更过来。<br>那为何修改部门的时候不自动调整下顾客资料的部门呢？因为正常情况下，不需要这样的操作：如果张三之前的确是在网络销售部工作，那他在网络销售部添加的资料，是属于网络销售部的。不会因为他以后转到电话销售部，资料也一起带到电话销售部。实际上，他以前在网络销售部加的顾客资料应保持不变。</div>

	<div class="d_item">&nbsp;&nbsp;请只调整必须要调整的顾客数据，不相干的不要处理。</div>

</div>



<div class="space"></div>

<form name="mainform" action="?action=move" method="POST" onsubmit="return Check()">

<table width="100%" class="edit">

	<tr>

		<td colspan="2" class="head"></td>

	</tr>

	<tr>

		<td class="left red">选择要操作的人：</td>

		<td class="right">

			<select name="author" class="combo">

				<option value='' style="color:gray">--请选择--</option>

				<?php echo list_option($kefu_list, 'realname', 'showname', $_GET["author"]); ?>

			</select>

			<span class="intro">您要操作谁的资料？请选择他的名字</span>

		</td>

	</tr>

	<tr>

		<td class="left red">部门统一修改为：</td>

		<td class="right">

			<select name="part_id" class="combo">

				<option value='' style="color:gray">--请选择--</option>

				<?php echo list_option($part_id_name, "_key_", "_value_", ""); ?>

			</select>

			<span class="intro">必须选择</span>

		</td>

	</tr>

</table>

<div class="button_line">

<input type="submit" class="submit" value="提交">

 &nbsp;&nbsp;&nbsp;&nbsp; 逐个用户处理太慢？

<button onclick="if (confirm('是否确定要全部处理？')) {location='?do=all'; this.disabled=true;}" class="buttonb">全部处理</button> (注：只处理 网络、电话、导医)

</div>

</form>



<table width="100%" class="list">

	<tr>

		<td class="head" align="center">姓名</td>

		<td class="head">顾客资料状态</td>

	</tr>



<?php foreach ($kefu_list as $li) {

	$author = $li["realname"];

	$data = $db->query("select part_id, count(part_id) as count, min(addtime) as begintime, max(addtime) as endtime from $table where binary author='$author' group by part_id");

	$tmp = array();

	foreach ($data as $tm) {

		$tmp[] = $part_id_name[$tm["part_id"]]." (".$tm["count"].") &nbsp;&nbsp; 从 ".date("Y-m-d", $tm["begintime"])." 到 ".date("Y-m-d", $tm["endtime"])."";

	}

	$tmp = implode("<br>", $tmp);

?>

	<tr>

		<td class="item" align="center"><?php echo $li["showname"]; ?></td>

		<td class="item" align="left"><?php echo $tmp; ?></td>

	</tr>

<?php } ?>



</table>



<br>

<br>



</body>

</html>