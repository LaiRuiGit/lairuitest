<?php

/*

// - 功能说明 : 搜索

// - 创建作者 : 小陈 

// - 创建时间 : 2013-05-02 15:47

*/

require "../../core/core.php";

$table = "patient_".$user_hospital_id;



if ($user_hospital_id == 0) {

	exit_html("对不起，没有选择门店，不能执行该操作！");

}



if ($_POST) {

	$fromname = $_POST["fromname"];

	$toname = $_POST["toname"];

	if ($fromname != '' && $toname != '') {

		if ($db->query("update $table set author='$toname' where binary author='$fromname'")) {

			msg_box("处理成功！", "?", 1);

		}

	}

}





$title = '资料转移工具';



$kefu_23_list = $db->query("select author,count(author) as acount from $table where author!='' group by author order by binary author");

foreach ($kefu_23_list as $k => $li) {

	$kefu_23_list[$k]["author_name"] = $li["author"]." (".$li["acount"].")";

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

	if (oForm.fromname.value == "") {

		alert("请输入“原名字”！"); oForm.fromname.focus(); return false;

	}

	if (oForm.toname.value == "") {

		alert("请输入“新名字”！"); oForm.toname.focus(); return false;

	}

	return true;

}

</script>

</head>



<body>

<!-- 头部 begin -->

<div class="headers" >

	<div class="headers_title"><span class="tips"><?php echo $title; ?></span></div>

	<div class="headers_oprate"><button onclick="history.back()" class="button">返回</button></div>

</div>

<!-- 头部 end -->



<div class="space"></div>



<div class="description">

	<div class="d_title">提示：</div>

	<div class="d_item">定义转移条件，点击提交按钮开始转移</div>

</div>



<div class="space"></div>



<form name="mainform" action="?action=move" method="POST" onsubmit="return Check()">

<table width="100%" class="edit">

	<tr>

		<td colspan="2" class="head">转移设置</td>

	</tr>

	<tr>

		<td class="left red">原名字：</td>

		<td class="right">

			<select name="fromname" class="combo">

				<option value='' style="color:gray">--请选择--</option>

				<?php echo list_option($kefu_23_list, 'author', 'author_name', ''); ?>

			</select>

			<span class="intro">原名字，不能为空</span>

		</td>

	</tr>

	<tr>

		<td class="left red">新名字：</td>

		<td class="right">

			<input name="toname" id="toname" class="input" style="width:150px">

			<span class="intro">新的名字，不能为空</span>

		</td>

	</tr>

</table>



<div class="button_line"><input type="submit" class="submit" value="提交"></div>



</form>

</body>

</html>