<?php

/*

// - 功能说明 : 搜索引擎新增、修改

// - 创建作者 : 小陈 

// - 创建时间 : 2010-07-31 14:56

*/



if ($_POST) {

	$r = array();

	$r["name"] = $_POST["name"];



	if ($op == "add") {

		$r["addtime"] = time();

		$r["author"] = $username;

	}



	$sqldata = $db->sqljoin($r);

	if ($op == "edit") {

		$sql = "update $table set $sqldata where id='$id' limit 1";

	} else {

		$sql = "insert into $table set $sqldata";

	}



	if ($db->query($sql)) {

		msg_box("资料提交成功", "?", 1);

	} else {

		msg_box("资料提交失败，系统繁忙，请稍后再试。", "back", 1, 5);

	}

}



$title = $op == "edit" ? "搜索引擎来源 - 修改" : "搜索引擎来源 - 新增";

?>

<html>

<head>

<title><?php echo $title; ?></title>

<meta http-equiv="Content-Type" content="text/html;charset=gb2312">

<link href="/res/base.css" rel="stylesheet" type="text/css">

<script src="/res/base.js" language="javascript"></script>

<script language="javascript">

function Check() {

	var oForm = document.mainform;

	if (oForm.name.value == "") {

		alert("请输入“名称”！"); oForm.name.focus(); return false;

	}

	return true;

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

	<div class="d_title">提示：</div>

	<div class="d_item">1.输入名称，点击提交即可</div>

</div>



<div class="space"></div>



<form name="mainform" action="" method="POST" onsubmit="return Check()">

<table width="100%" class="edit">

	<tr>

		<td colspan="2" class="head">搜索引擎资料</td>

	</tr>

	<tr>

		<td class="left">名称：</td>

		<td class="right"><input name="name" value="<?php echo $line["name"]; ?>" class="input" size="30" style="width:200px"> <span class="intro">名称必须填写</span></td>

	</tr>

</table>

<input type="hidden" name="id" value="<?php echo $id; ?>">

<input type="hidden" name="op" value="<?php echo $op; ?>">



<div class="button_line"><input type="submit" class="submit" value="提交资料"></div>

</form>

</body>

</html>