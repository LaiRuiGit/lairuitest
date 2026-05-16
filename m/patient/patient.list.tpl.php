<html>
<head>
<meta charset="gbk" />
<title><?php echo $pinfo["title"]; ?></title>
<link href="/res/base.css" rel="stylesheet" type="text/css">
<script src="/res/base.js" language="javascript"></script>
<script src="/res/datejs/picker.js" language="javascript"></script>
<style>
#color_tips {padding:0 0 8px 12px; }
</style>

<script language="javascript">
function set_come(id, come_value) {
	var xm = new ajax();
	xm.connect('http/patient_set_come.php', 'GET', 'id='+id+'&come='+come_value, set_come_do);
}

function set_come_do(o) {
	var out = ajax_out(o);
	if (out["status"] == 'ok') {
		byid("come_"+out["id"]).innerHTML = ['待预约', '已签约', '已签约','已量尺','已报价'][out["come"]];
		byid("come_"+out["id"]+"_"+out["come"]).style.display = 'none';
		byid("come_"+out["id"]+"_"+(out["come"]==1 ? 2 : 1)).style.display = 'inline';
		byid("list_line_"+out["id"]).style.color = ['', 'red', 'gray'][out["come"]];
	} else {
		alert("设置失败，请稍后再试！");
	}
}

function set_xiaofei(id, value) {
	var xm = new ajax();
	xm.connect('/http/patient_set_xiaofei.php', 'GET', 'id='+id+'&xiaofei='+value, set_xiaofei_do);
}

function set_xiaofei_do(o) {
	var out = ajax_out(o);
	if (out["status"] == 'ok') {
		if (out["xiaofei"] == '0') {
			var button = '<a href="#" onclick="set_xiaofei('+out["id"]+',1); return false;">×</a>';
		} else {
			var button = '<a href="#" onclick="set_xiaofei('+out["id"]+',0); return false;">√</a>';
		}
		byid("xiaofei_"+out["id"]).innerHTML = button;
	} else {
		alert("设置失败，请稍后再试！");
	}
}
</script>
</head>

<body>
<!-- 头部 begin -->
<div class="headers">
	<div class="headers_title" style="width:40%"><span class="tips"><?=$hospital_id_name[$user_hospital_id]?> - 预约列表</span></div>
	<div class="header_center">
		<?php echo $power->show_button("add"); ?>&nbsp;
		<button onClick="location='?op=search'" class="buttonb">高级搜索</button>
		<form action="?" method="GET" style="display:inline;">
			<input name="date" id="ch_date" onChange="this.form.submit();" value="<?php echo $_GET["date"] ? $_GET["date"] : date("Y-m-d"); ?>" style="width:0px; overflow:hidden; padding:0; margin:0; border:0;">
		</form>
        <button onClick="picker({el:'ch_date',dateFmt:'yyyy-MM-dd'})" class="buttonb">按日查看</button>
        <a id="outExcel" download="预约表.xls" style="color: red;font-weight: bold;border: 1px solid #ccc;padding: 0.25em 0.5em;background: linear-gradient(#fff, #F2F2F9);margin-left: 10px">导出表格</a>
	</div>
	<div class="headers_oprate"><form name="topform" method="GET">模糊搜索：<input name="key" value="<?php echo $_GET["key"].$l; ?>" class="input" size="8">&nbsp;<input type="submit" class="search" value="搜索" style="font-weight:bold" title="点击搜索">&nbsp;<button onClick="location='?'" class="search" title="退出条件查询">重置</button>&nbsp;&nbsp;<button onClick="history.back()" class="button" title="返回上一页">返回</button></form></div>
	<div class="clear"></div>
</div>
<!-- 头部 end -->

<!-- 统计数据 begin -->
<div class="space"></div>
<table width="100%" style="border:2px solid #D9D9D9; border-left:0; border-right:0; background-color:#F2F2F2; color:black; ">
	<tr>
		<td width="33%">&nbsp;<b>统计数据:</b> <?php echo $res_report; ?></td>
		<td align="center"><?php if (in_array($uinfo["part_id"], array(2,3))) { ?><b>部门今日:</b> <?php echo $part_report; ?><?php } ?></td>
		<td width="33%" align="right"><b>今日数据: </b> <?php echo $today_report; ?></td>
	</tr>
</table>
<!-- 搜索结果的统计数据 end -->

<div class="space"></div>

<div id="color_tips">
颜色标记：
<?php foreach ($line_color_tip as $k => $v) { ?>
<font color="<?php echo $line_color[$k]; ?>"><?php echo $v; ?></font>&nbsp;
<?php } ?>
</div>

<!-- 数据列表 begin -->
<?php echo $t->show(); ?>
<!-- 数据列表 end -->

<!-- 分页链接 begin -->
<div class="space"></div>
<div class="footer_op">
	<div class="footer_op_left"><button onClick="select_all()" class="button" disabled="true">全选</button></div>
	<div class="footer_op_right"><?php echo $pagelink; ?></div>
</div>
<!-- 分页链接 end -->

<!-- <?php echo $s_sql; ?> -->





<script>
    // 使用outerHTML属性获取整个table元素的HTML代码（包括<table>标签），然后包装成一个完整的HTML文档，设置charset为urf-8以防止中文乱码
    var html = "<html><head><meta charset='utf-8' /></head><body>" + document.getElementsByClassName("new_list")[0].outerHTML + "</body></html>";

    // 实例化一个Blob对象，其构造函数的第一个参数是包含文件内容的数组，第二个参数是包含文件类型属性的对象
    var blob = new Blob([html], { type: "application/vnd.ms-excel" });
    console.log(html);
    var a = document.getElementsByClassName("new_list")[0];
    a.href = URL.createObjectURL(blob);
    var aObj = document.getElementById("outExcel");
    aObj.href = a.href;
    a.download = "预约表.xls";
</script>
</body>
</html>