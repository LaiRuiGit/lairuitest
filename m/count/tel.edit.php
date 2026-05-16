<?php
// --------------------------------------------------------
// - 功能说明 : 添加、修改资料
// - 创建作者 : 小陈 
// - 创建时间 : 2010-10-05 13:31
// --------------------------------------------------------
$date = $_REQUEST["date"];

if (!$date) {
	exit("参数错误");
}

$date = date("Y-m-d", strtotime($date));

if ($username != "admin") {
	//if (!in_array($date, array(date("Y-m-d"), date("Y-m-d", strtotime("-1 day")), date("Y-m-d", strtotime("-2 day")) ) )) {
	//	exit_html("对不起，只能修改前天、昨天或今天的数据");
	//}
}

$kefu = $_GET["kefu"];
if (!$kefu) {
	exit("参数错误");
}

if ($_POST) {
	$r = array();

	// 判断是否已经添加:
	$mode = "add";
	$s_date = date("Ymd", strtotime($date." 0:0:0"));
	$kefu = $_POST["kefu"];
	$cur_data = $db->query("select * from $table where type_id=$cur_type and kefu='$kefu' and date='$s_date' limit 1", 1);
	$cur_id = $cur_data["id"];
	if ($cur_id > 0) {
		$mode = "edit";
		$id = $cur_id;
	}

	if ($mode == "add") {
		$r["type_id"] = $cur_type;
		$r["type_name"] = $db->query("select name from count_type where id=".$r["type_id"]." limit 1", 1, "name");
		$r["date"] = $s_date;
		$r["kefu"] = $_POST["kefu"];
	}

	$r["tel_all"] = $_POST["tel_all"];
	$r["tel_ok"] = $_POST["tel_ok"];
	$r["yuyue"] = $_POST["yuyue"];
	$r["jiuzhen"] = $_POST["jiuzhen"];
	$r["wangluo"] = $_POST["wangluo"];
	$r["zazhi"] = $_POST["zazhi"];
	$r["laobao"] = $_POST["laobao"];
	$r["xinbao"] = $_POST["xinbao"];
	$r["t400"] = $_POST["t400"];
	$r["t114"] = $_POST["t114"];
	$r["jieshao"] = $_POST["jieshao"];
	$r["luguo"] = $_POST["luguo"];
	$r["qita"] = $_POST["qita"];

	if ($mode == "add") {
		$r["uid"] = $uid;
		$r["uname"] = $realname;
		$r["addtime"] = time();
	}

	// 操作日志:
	if ($mode == "add") {
		$r["log"] = date("Y-m-d H:i")." ".$realname." 添加记录\r\n";
	} else {
		// 记录具体修改了哪些:
		$log_it = array();
		foreach ($r as $x => $y) {
			if ($cur_data[$x] != $y) {
				$log_it[] = $x.":".$cur_data[$x]."=>".$y;
			}
		}
		if (count($log_it) > 0) {
			$r["log"] = $cur_data["log"].date("Y-m-d H:i")." ".$realname." 修改: ".implode(", ", $log_it)." \r\n";
		}
	}

	$sqldata = $db->sqljoin($r);
	if ($mode == "add") {
		$sql = "insert into $table set $sqldata";
	} else {
		$sql = "update $table set $sqldata where id='$id' limit 1";
	}

	if ($nid = $db->query($sql)) {
		//exit("成功");
		msg_box("资料提交成功", "?date=".$date."&kefu=".urlencode($kefu), 1);
	} else {
		exit("资料添加失败");
		//msg_box("资料提交失败，系统繁忙，请稍后再试。", "back", 1, 5);
	}
}

if ($op == "edit") {
	$s_date = date("Ymd", strtotime($date." 0:0:0"));
	$line = $db->query("select * from $table where type_id=$cur_type and kefu='$kefu' and date='$s_date' limit 1", 1);
}


$title = $op == "edit" ? "修改资料" : "添加资料";
?>
<html>
<head>
<title><?php echo $title; ?></title>
<meta http-equiv="Content-Type" content="text/html;charset=gb2312">
<link href="/res/base.css" rel="stylesheet" type="text/css">
<script src="/res/base.js" language="javascript"></script>
<style>
.item {padding:8px 3px 6px 3px; }
</style>

<script language="javascript">
function check_data() {
	var oForm = document.mainform;
	if (oForm.code.value == "") {
		alert("请输入“编号”！"); oForm.code.focus(); return false;
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
	<div class="d_item">按要求输入各项资料，点击提交即可</div>
</div>

<div class="space"></div>

<form name="mainform" action="" method="POST" onsubmit="return check_data()">
<table width="100%" class="edit">
	<tr>
		<td colspan="2" class="head">电话详细资料</td>
	</tr>

	<tr>
		<td class="left">总电话：</td>
		<td class="right">
			<input name="tel_all" value="<?php echo $line["tel_all"]; ?>" class="input" style="width:100px">
		</td>
	</tr>
	<tr>
		<td class="left">有效：</td>
		<td class="right">
			<input name="tel_ok" value="<?php echo $line["tel_ok"]; ?>" class="input" style="width:100px">
		</td>
	</tr>
	<tr>
		<td class="left">预约：</td>
		<td class="right">
			<input name="yuyue" value="<?php echo $line["yuyue"]; ?>" class="input" style="width:100px">
		</td>
	</tr>
	<tr>
		<td class="left">就诊：</td>
		<td class="right">
			<input name="jiuzhen" value="<?php echo $line["jiuzhen"]; ?>" class="input" style="width:100px">
		</td>
	</tr>


	<tr>
		<td colspan="2" class="head">来源详细</td>
	</tr>
	<tr>
		<td class="left">网络：</td>
		<td class="right">
			<input name="wangluo" value="<?php echo $line["wangluo"]; ?>" class="input" style="width:100px">
		</td>
	</tr>
	<tr>
		<td class="left">杂志：</td>
		<td class="right">
			<input name="zazhi" value="<?php echo $line["zazhi"]; ?>" class="input" style="width:100px">
		</td>
	</tr>
	<tr>
		<td class="left">劳报：</td>
		<td class="right">
			<input name="laobao" value="<?php echo $line["laobao"]; ?>" class="input" style="width:100px">
		</td>
	</tr>
	<tr>
		<td class="left">新报：</td>
		<td class="right">
			<input name="xinbao" value="<?php echo $line["xinbao"]; ?>" class="input" style="width:100px">
		</td>
	</tr>
	<tr>
		<td class="left">400：</td>
		<td class="right">
			<input name="t400" value="<?php echo $line["t400"]; ?>" class="input" style="width:100px">
		</td>
	</tr>
	<tr>
		<td class="left">114：</td>
		<td class="right">
			<input name="t114" value="<?php echo $line["t114"]; ?>" class="input" style="width:100px">
		</td>
	</tr>
	<tr>
		<td class="left">介绍：</td>
		<td class="right">
			<input name="jieshao" value="<?php echo $line["jieshao"]; ?>" class="input" style="width:100px">
		</td>
	</tr>
	<tr>
		<td class="left">路过：</td>
		<td class="right">
			<input name="luguo" value="<?php echo $line["luguo"]; ?>" class="input" style="width:100px">
		</td>
	</tr>
	<tr>
		<td class="left">其他：</td>
		<td class="right">
			<input name="qita" value="<?php echo $line["qita"]; ?>" class="input" style="width:100px">
		</td>
	</tr>
</table>
<input type="hidden" name="linkinfo" value="<?php echo $linkinfo; ?>">
<input type="hidden" name="op" value="<?php echo $op; ?>">
<input type="hidden" name="date" value="<?php echo date("Y-m-d", strtotime($date." 0:0:0")); ?>">
<input type="hidden" name="kefu" value="<?php echo $kefu; ?>">

<div class="button_line"><input type="submit" class="submit" value="提交数据"></div>
</form>
</body>
</html>