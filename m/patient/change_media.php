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

	$author = $_POST["author"];

	$media = $_POST["media"];

	if ($author != '' && $media != '') {

		if ($db->query("update $table set media_from='$media' where binary author='$author'")) {

			msg_box("处理成功！", "?", 1);

		}

	}

}





$title = '媒体来源批量设置工具';



$part_id_name = array(2 => "网络销售", 3 => "电话销售");



$kefu_23_list = $db->query("select id,realname,part_id from sys_admin where hospitals='$user_hospital_id' and part_id in (2,3)");

foreach ($kefu_23_list as $k => $li) {

	$kefu_23_list[$k]["showname"] = $li["realname"]." (".$part_id_name[$li["part_id"]].")";

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

	if (oForm.media.value == "") {

		alert("请选择“新的媒体来源”！"); oForm.media.focus(); return false;

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

	<div class="d_item">请注意，此工具能够正确处理您的命令，但不当的命令造成数据损坏将无法恢复，请务必谨慎！</div>

</div>



<div class="space"></div>



<form name="mainform" action="?action=move" method="POST" onsubmit="return Check()">

<table width="100%" class="edit">

	<tr>

		<td colspan="2" class="head"></td>

	</tr>

	<tr>

		<td class="left red">名字：</td>

		<td class="right">

			<select name="author" class="combo" onchange="window.location='?author='+this.value">

				<option value='' style="color:gray">--请选择--</option>

				<?php echo list_option($kefu_23_list, 'realname', 'showname', $_GET["author"]); ?>

			</select>

			<span class="intro">名字必须选择</span>

		</td>

	</tr>

<?php if ($_GET["author"] != '') {

	$author = $_GET["author"];

	$medias = $db->query("select media_from, count(media_from) as count from $table where author='$author' group by media_from", "media_from", "count");

	$s = array();

	foreach ($medias as $k => $v) {

		$s[] = $k." (".$v.")";

	}

	$s = implode("<br>", $s);

?>

	<tr>

		<td class="left red">当前媒体来源：</td>

		<td class="right">

			<?php echo $s; ?>

		</td>

	</tr>

<?php } ?>

	<tr>

		<td class="left red">新的媒体来源：</td>

		<td class="right">

			<select name="media" class="combo">

				<option value='' style="color:gray">--请选择--</option>

				<?php echo list_option($part_id_name, '', '', ''); ?>

			</select>

			<span class="intro">必须选择</span>

		</td>

	</tr>

</table>



<div class="button_line"><input type="submit" class="submit" value="提交"></div>



</form>

</body>

</html>