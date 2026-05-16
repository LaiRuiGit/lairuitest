<?php
/*
//  功能说明 : 部门新增、修改
*/

if ($_POST) {
	$r = array();
	if ($op == "add" && $_POST["part_id"] > 0) {
		$r["id"] = $ptid = intval($_POST["part_id"]);

		// 检查ID是否重复:
		$is_old = $db->query("select count(*) as count from $table where id=$ptid", 1, "count");
		if ($is_old > 0) {
			exit_html("对不起, ID和已有的重复,请返回重新填写! ");
		}
		// end 检查ID重复
	}
	$r["name"] = $_POST["name"];
	$r["pid"] = $_POST["parent_part_id"];

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

	//exit($sql);

	if ($db->query($sql)) {
		// 更新引用:
		if ($op == "edit" && $r["id"]) {
			$db->query("update $table set pid=".$r["id"]." where pid=".$id."");
		}
		msg_box("部门资料提交成功", "?", 1);
	} else {
		msg_box("资料提交失败，系统繁忙，请稍后再试。", "back", 1, 5);
	}
}


$title = $op == "edit" ? "修改部门定义" : "添加新的部门";

//$part_list = $db->query("select * from ".$tabpre."sys_part");

$part_list = get_part_list('array');

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
		alert("请输入“部门名称”！");
		oForm.name.focus();
		return false;
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
	<div class="d_item">1.请输入部门名称，且必须指定是隶属于哪个部门</div>
	<div class="d_item">2.部门之间的管理关系将应用到文档流等的管理中，请务必准确设置，且不应随意修改</div>
</div>

<div class="space"></div>

<form name="mainform" action="" method="POST" onsubmit="return Check()">
<table width="100%" class="edit">
	<tr>
		<td colspan="2" class="head">部门资料</td>
	</tr>

<?php if ($op == "add") { ?>
	<!-- 只有添加时可以指定一个ID -->
	<tr>
		<td class="left">部门ID：</td>
		<td class="right"><input name="part_id" value="<?php echo $line["id"]; ?>" class="input" style="width:80px"> <span class="intro">可以不指定，系统自动分配；如果指定，请确认不能和已有的重复，且提交成功后不能修改</span></td>
	</tr>
<?php } ?>

	<tr>
		<td class="left">部门名称：</td>
		<td class="right"><input name="name" value="<?php echo $line["name"]; ?>" class="input" style="width:200px"> <span class="intro">名称必须填写</span></td>
	</tr>
	<tr>
		<td class="left">上级部门：</td>
		<td class="right">
			<select name="parent_part_id" class="combo">
			<option value="0" style="color:gray">-没有上级部门-</option>
			<?php echo list_option($part_list, 'id', 'name', $line["pid"]); ?>
			</select>

			<span class="intro">上级部门名称</span>
		</td>
	</tr>
</table>
<input type="hidden" name="id" value="<?php echo $id; ?>">
<input type="hidden" name="op" value="<?php echo $op; ?>">

<div class="button_line"><input type="submit" class="submit" value="提交资料"></div>
</form>
</body>
</html>