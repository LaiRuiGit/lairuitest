<?php

/*

// - 功能说明 : 数据清理工具

// - 创建作者 : 小陈 

// - 创建时间 : 2013-06-12 21:50

*/

require "../../core/core.php";

$table = "patient_".$user_hospital_id;



if ($user_hospital_id == 0) {

	exit_html("对不起，没有选择门店，不能执行该操作！");

}



if ($_POST) {

	$author = $_POST["author"];

	$media = $_POST["media"];

	if ($author != '' && $media != '') {

		if ($db->query("update $table set media_from='$media' where binary author='$author'")) {

			msg_box("处理成功！", "patient_change_media.php", 1);

		}

	}

}





$title = '顾客资料清理工具';



$date = $db->query("select min(addtime) as btime, max(addtime) as etime from $table", 1);

$btime = $date["btime"];

$etime = $date["etime"];



$begin_year = date("Y", $btime);

$end_year = date("Y", $etime);



$lists = array();

for ($i=$begin_year; $i<=$end_year; $i++) {

	$date_begin = mktime(0,0,0,0,0,$i);

	$date_end = mktime(0,0,0,0,0,$i+1);



	$count = $db->query("select count(id) as count from $table where addtime>=$date_begin and addtime<$date_end", 1, "count");

	if ($i == date("Y")) {

		$lists[$i] = $i."年 (".$count." 条) [不能清理]";

	} else {

		$lists[$i] = $i."年 (".$count." 条)";

	}

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

	if (byid("server_year").value == oForm.year.value) {

		alert("不能清理今年的数据！"); oForm.year.focus(); return false;

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

	<div class="d_title">提示：</div>

	<div class="d_item" style="color:red;">操作前请联系管理员备份数据，否则可能会有危险！</div>

</div>



<div class="space"></div>



<form name="mainform" action="?action=do" method="POST" onsubmit="return Check()">

<table width="100%" class="edit">

	<tr>

		<td colspan="2" class="head"></td>

	</tr>

	<tr>

		<td class="left red">清理年份：</td>

		<td class="right">

			<select name="year" class="combo">

				<option value='' style="color:gray">--请选择--</option>

				<?php echo list_option($lists, "_key_", "_value_"); ?>

			</select>

			<span class="intro">必须选择</span>

		</td>

	</tr>

</table>



<div class="button_line"><input type="submit" class="submit" value="提交"></div>



<input id="server_year" type="hidden" value="<?php echo date("Y"); ?>">



</form>

</body>

</html>