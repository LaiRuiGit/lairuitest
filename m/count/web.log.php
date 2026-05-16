<?php
// --------------------------------------------------------
// - 功能说明 : 查看和处理日志
// - 创建作者 : 小陈 
// - 创建时间 : 2011-03-22 15:09
// --------------------------------------------------------

if (!($debug_mode || $username == "admin")) {
	exit("没有权限");
}

if (!($cur_type > 0)) {
	exit("门店项目没有选择");
}


// 默认显示最近3天的操作日志 按天列出
if (!isset($_GET["btime"])) {
	$_GET["btime"] = date("Y-m-d", strtotime("-3 days"));
}
if (!isset($_GET["etime"])) {
	$_GET["etime"] = date("Y-m-d");
}


$begin = date("Ymd", strtotime($_GET["btime"]));
$end = date("Ymd", strtotime($_GET["etime"]));

$list = $db->query("select id,date,kefu,addtime,u_realname,log from $table where type_id=$cur_type and date>=$begin and date<=$end order by date desc,kefu asc", "id");

$last_date = '';


// 读取翻译字典:
$tmps = @explode("\n", str_replace("\r", "", trim(file_get_contents("dict.txt"))));
$dicts = array();
if (is_array($tmps) && count($tmps) > 0) {
	foreach ($tmps as $v) {
		$v = trim($v);
		if ($v) {
			while (substr_count($v, "  ") > 0) {
				$v = str_replace("  ", " ", $v);
			}
			list($a, $b) = explode(" ", $v);
			$dicts[$a] = $b;
		}
	}
}


// 将日志中的字段翻译成汉字名字
function trans($str) {
	global $dicts;
	if (count($dicts) > 0) {
		foreach ($dicts as $k => $v) {
			$str = str_replace($k, $v, $str);
		}
	}

	return $str;
}

// 页面开始 ------------------------
?>
<html>
<head>
<title>查看操作日志</title>
<meta http-equiv="Content-Type" content="text/html;charset=gb2312">
<link href="/res/base.css" rel="stylesheet" type="text/css">
<script src="/res/base.js" language="javascript"></script>
<script src="/res/datejs/picker.js" language="javascript"></script>
<style>
body {padding:5px 8px; }
form {display:inline; }
#date_tips {float:left; font-weight:bold; padding-top:4px; }
#ch_date {float:left; margin-left:20px; }
.site_name {display:block; padding:4px 0px;}
.site_name, .site_name a {font-family:"Arial", "Tahoma"; }
.ch_date_a b, .ch_date_a a {font-family:"Arial"; }
.ch_date_a b {border:0px; padding:1px 5px 1px 5px; color:red; }
.ch_date_a a {border:0px; padding:1px 5px 1px 5px; }
.ch_date_a a:hover {border:1px solid silver; padding:0px 4px 0px 4px; }
.ch_date_b {padding-top:8px; text-align:left; width:80%; color:silver; }
.ch_date_b a {padding:0 3px; }

.main_title {margin:0 auto; padding-top:30px; padding-bottom:15px; text-align:center; font-weight:bold; font-size:12px; font-family:"宋体"; }

.item {padding:8px 3px 6px 3px !important; }
.rate_tips {padding:30px 0 0 30px; line-height:24px; }

.li_date {margin-left:20px; font-weight:bold; color:red; margin-top:20px;  }
.li_line {margin-left:60px; margin-top:5px; }
.li_kefu {display:inline; width:120px; color:#405050; float:left; }
.log_data {float:left; }
</style>

<script language="javascript">
function update_date(type, o) {
	byid("date_"+type).value = parseInt(o.innerHTML, 10);

	var a = parseInt(byid("date_1").value, 10);
	var b = parseInt(byid("date_2").value, 10);

	var s = a + '' + (b<10 ? "0" : "") + b;

	byid("date").value = s;
	byid("ch_date").submit();
}

function hgo(dir, o) {
	var obj = byid("type_id");
	if (dir == "up") {
		if (obj.selectedIndex > 1) {
			obj.selectedIndex = obj.selectedIndex - 1;
			obj.onchange();
			o.disabled = true;
		} else {
			parent.msg_box("已经是最前了", 3);
		}
	}
	if (dir == "down") {
		if (obj.selectedIndex < obj.options.length-1) {
			obj.selectedIndex = obj.selectedIndex + 1;
			obj.onchange();
			o.disabled = true;
		} else {
			parent.msg_box("已经是最后一个了", 3);
		}
	}
}
</script>
</head>

<body>
<div style="margin:10px 0 0 0px;">
	<div id="date_tips">门店项目：<?php echo $types[$cur_type]; ?></div>&nbsp;&nbsp;&nbsp;

	<b>时间段：</b>
	<form method="GET">
		<input name="btime" id="begin_time" class="input" style="width:100px" value="<?php echo $_GET["btime"]; ?>"> <img src="/res/img/calendar.gif" id="order_date" onClick="picker({el:'begin_time',dateFmt:'yyyy-MM-dd'})" align="absmiddle" style="cursor:pointer" title="选择时间">&nbsp;&nbsp;到&nbsp;&nbsp;
		<input name="etime" id="end_time" class="input" style="width:100px" value="<?php echo $_GET["etime"]; ?>"> <img src="/res/img/calendar.gif" id="order_date" onClick="picker({el:'end_time',dateFmt:'yyyy-MM-dd'})" align="absmiddle" style="cursor:pointer" title="选择时间">&nbsp;&nbsp;
		<input type="submit" class="button" value="确定">
		<input type="hidden" name="op" value="log">
	</form>
</div>

<div class="clear"></div>

<?php
foreach ($list as $id => $li) {
	if ($li["date"] != $last_date) {
		echo '<div class="li_date">'.substr($li["date"], 0, 4)."-".substr($li["date"], 4, 2)."-".substr($li["date"], 6, 2).'</div>';
		$last_date = $li["date"];
	}
	echo '<div class="li_line"><div class="li_kefu">销售：'.$li["kefu"].'</div><div class="log_data">'.str_replace("\n", "<br>", str_replace("\r", "", trim(trans($li["log"])))).'</div><div class="clear"></div></div>';
}
?>


<br>
<br>

</body>
</html>
