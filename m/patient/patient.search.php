<?php
/*
// - 功能说明 : 搜索
// - 创建作者 : 小陈 
// - 创建时间 : 2013-05-02 15:47
*/

$p_type = $uinfo["part_id"]; // 0,1,2,3,4

$title = '顾客搜索';

$admin_name = $db->query("select realname from sys_admin", "", "realname");
$author_name = $db->query("select distinct author from $table order by binary author", "", "author");
$kefu_23_list = array_intersect($admin_name, $author_name);

$kefu_4_list = $db->query("select name,realname from ".$tabpre."sys_admin where hospitals='$user_hospital_id' and part_id in (4)");
$doctor_list = $db->query("select name from ".$tabpre."doctor where hospital_id='$user_hospital_id'");

$disease_list = $db->query("select id,name from ".$tabpre."disease where hospital_id=$user_hospital_id");
$depart_list = $db->query("select id,name from ".$tabpre."depart where hospital_id=$user_hospital_id");

$media_list = $db->query("select name from media where hospital_id=$user_hospital_id order by id asc", "", "name");
$media_list = array_merge(array("电话"), $media_list);

// 时间定义
// 昨天
$yesterday_begin = strtotime("-1 day");
// 本月
$this_month_begin = mktime(0,0,0,date("m"), 1);
$this_month_end = strtotime("+1 month", $this_month_begin) - 1;
// 上个月
$last_month_end = $this_month_begin - 1;
$last_month_begin = strtotime("-1 month", $this_month_begin);
//今年
$this_year_begin = mktime(0,0,0,1,1);
$this_year_end = strtotime("+1 year", $this_year_begin) - 1;
// 最近一个月
$near_1_month_begin = strtotime("-1 month");
// 最近三个月
$near_3_month_begin = strtotime("-3 month");
// 最近一年
$near_1_year_begin = strtotime("-12 month");

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
	//if (oForm.name.value == "") {
	//	alert("请输入“疾病名称”！"); oForm.name.focus(); return false;
	//}
	return true;
}
function write_dt(da, db) {
	byid("begin_time").value = da;
	byid("end_time").value = db;
}
</script>
</head>

<body>
<!-- 头部 begin -->
<div class="headers">
	<div class="headers_title"><span class="tips"><?php echo $title; ?></span></div>
	<div class="headers_oprate"><button onClick="history.back()" class="button">返回</button></div>
</div>
<!-- 头部 end -->

<div class="space"></div>

<div class="description">
	<div class="d_title">提示：</div>
	<div class="d_item">输入搜索条件，点击提交按钮开始搜索，每个条件均是可选项。</div>
</div>

<div class="space"></div>

<form name="mainform" action="patient.php" method="GET" onSubmit="return Check()">
<table width="100%" class="edit">
	<tr>
		<td colspan="2" class="head">房型</td>
	</tr>
	<tr>
		<td class="left">房型：</td>
		<td class="right"><input name="searchword" class="input" style="width:150px" value=""> <span class="intro">(留空则忽略此条件)</span></td>
	</tr>
	<tr>
		<td colspan="2" class="head">时间限制</td>
	</tr>
	<tr>
		<td class="left">时间类型：</td>
		<td class="right">
			<select name="time_type" class="combo">
				<option value="" style="color:gray">--请选择--</option>
				<option value="order_date">预约时间</option>
				<option value="addtime">资料添加时间</option>
				<!-- <option value="come_date">顾客到店时间</option> -->
			</select>
			<span class="intro">选择搜索的时间类型，默认为预约时间</span>
		</td>
	</tr>
	<tr>
		<td class="left">起始时间：</td>
		<td class="right"><input name="btime" id="begin_time" class="input" style="width:150px" value="<?php //echo date("Y-m-d"); ?>"> <img src="/res/img/calendar.gif" id="order_date" onClick="picker({el:'begin_time',dateFmt:'yyyy-MM-dd'})" align="absmiddle" style="cursor:pointer" title="选择时间"> <br>速填：
		<a href="javascript:write_dt('<?php echo date("Y-m-d"); ?>','<?php echo date("Y-m-d"); ?>')">[今天]</a>
		<a href="javascript:write_dt('<?php echo date("Y-m-d", $yesterday_begin); ?>','<?php echo date("Y-m-d", $yesterday_begin); ?>')">[昨天]</a>
		<a href="javascript:write_dt('<?php echo date("Y-m-d", $this_month_begin); ?>','<?php echo date("Y-m-d", $this_month_end); ?>')">[本月]</a>
		<a href="javascript:write_dt('<?php echo date("Y-m-d", $last_month_begin); ?>','<?php echo date("Y-m-d", $last_month_end); ?>')">[上月]</a>
		<a href="javascript:write_dt('<?php echo date("Y-m-d", $this_year_begin); ?>','<?php echo date("Y-m-d", $this_year_end); ?>')">[今年]</a>
		<a href="javascript:write_dt('<?php echo date("Y-m-d", $near_1_month_begin); ?>','<?php echo date("Y-m-d"); ?>')">[近一个月]</a>
		<a href="javascript:write_dt('<?php echo date("Y-m-d", $near_3_month_begin); ?>','<?php echo date("Y-m-d"); ?>')">[近三个月]</a>
		<a href="javascript:write_dt('<?php echo date("Y-m-d", $near_1_year_begin); ?>','<?php echo date("Y-m-d"); ?>')">[近一年]</a>
		<!-- <span class="intro">请指定起始时间 (留空则忽略此条件)</span> --></td>
	</tr>
	<tr>
		<td class="left">终止时间：</td>
		<td class="right"><input name="etime" id="end_time" class="input" style="width:150px" value="<?php //echo date("Y-m-d"); ?>"> <img src="/res/img/calendar.gif" id="order_date" onClick="picker({el:'end_time',dateFmt:'yyyy-MM-dd'})" align="absmiddle" style="cursor:pointer" title="选择时间"> <!-- <span class="intro">请指定终止时间 (留空则忽略此条件)</span> --></td>
	</tr>

	<tr>
		<td colspan="2" class="head">人员搜索</td>
	</tr>

<?php //if ($debug_mode || $uinfo["part_admin"] || in_array($uinfo["part_id"], array(2,4))) { ?>
	<tr>
		<td class="left">搜销售：</td>
		<td class="right">
			<select name="kefu_23_name" class="combo">
				<option value='' style="color:gray">--请选择--</option>
				<?php echo list_option($kefu_23_list, '_value_', '_value_', ''); ?>
			</select>
			<span class="intro">指定要搜索的销售 (不选则忽略此条件)</span>
		</td>
	</tr>
<?php //} ?>

<?php if ($debug_mode || $uinfo["part_admin"] || in_array($uinfo["part_id"], array(3,4))) { ?>
	<tr hidden>
		<td class="left">搜导医：</td>
		<td class="right">
			<select name="kefu_4_name" class="combo">
				<option value='' style="color:gray">--请选择--</option>
				<?php echo list_option($kefu_4_list, 'realname', 'realname', ''); ?>
			</select>
			<span class="intro">指定要搜索的导医 (不选则忽略此条件)</span>
		</td>
	</tr>
<?php } ?>

<?php if ($debug_mode || $uinfo["part_admin"]) { ?>
	<tr hidden>
		<td class="left">搜医生：</td>
		<td class="right">
			<select name="doctor_name" class="combo">
				<option value='' style="color:gray">--请选择--</option>
				<?php echo list_option($doctor_list, 'name', 'name', ''); ?>
			</select>
			<span class="intro">指定要搜索的接待医生 (不选则忽略此条件)</span>
		</td>
	</tr>
<?php } ?>

	<tr>
		<td colspan="2" class="head">更多搜索项</td>
	</tr>

	<tr>
		<td class="left">赴约状态：</td>
		<td class="right">
			<select name="come" class="combo">
				<option value='' style="color:gray">--请选择--</option>
				<option value='0'>待预约</option>
				<option value='1'>已签约</option>
                <option value='2'>已放弃</option>
                <option value='3'>已量尺</option>
                <option value='4'>已报价</option>
			</select>
			<span class="intro">(不选则忽略此条件)</span>
		</td>
	</tr>
	<tr>
		<td class="left">装修房型：</td>
		<td class="right">
			<select name="doctor" class="combo">
				<option value='' style="color:gray">--请选择--</option>
				<?php echo list_option($doctor_list, "id", "name", ''); ?>
			</select>
			<span class="intro">(不选则忽略此条件)</span>
		</td>
	</tr>
<?php if ($debug_mode || $username == 'admin' || !in_array($uinfo["part_id"], array(2,3,4))) { ?>
	<tr hidden>
		<td class="left">部门：</td>
		<td class="right">
			<select name="part_id" class="combo">
				<option value='' style="color:gray">--请选择--</option>
				<option value='2'>网络</option>
				<option value='3'>电话</option>
				<option value='4'>导医</option>
                <option value='10'>网电</option>
                <option value='5'>市场</option>
			</select>
			<span class="intro">(不选则忽略此条件)</span>
		</td>
	</tr>
<?php } ?>

<?php if (count($depart_list) > 0) { ?>
	<tr>
		<td class="left">门店：</td>
		<td class="right">
			<select name="depart" class="combo">
				<option value='' style="color:gray">--请选择--</option>
				<?php echo list_option($depart_list, "id", "name", ''); ?>
			</select>
			<span class="intro">(不选则忽略此条件)</span>
		</td>
	</tr>
<?php } ?>

	<tr>
		<td class="left">媒体来源：</td>
		<td class="right">
			<select name="media" class="combo">
				<option value='' style="color:gray">--请选择--</option>
				<?php echo list_option($media_list, "_value_", "_value_", ''); ?>
			</select>
			<span class="intro">(不选则忽略此条件)</span>
		</td>
	</tr>

</table>

<input type="hidden" name="from" value="search">
<input type="hidden" name="sort" value="添加时间">
<input type="hidden" name="sorttype" value="desc">
<div class="button_line"><input type="submit" class="submit" value="搜索"></div>

</form>
</body>
</html>