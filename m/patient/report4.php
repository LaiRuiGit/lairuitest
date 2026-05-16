<?php
/*
// 说明: 曲线图报表
// 作者: 小陈 
// 时间: 2011-01-15
*/
require "../../core/core.php";
include "../../res/chart/FusionCharts_Gen.php";

if ($op == "run") {

	if (count($hospital_ids) == 0) {
		exit_html("没有可以显示的门店！");
	}

	// 循环显示门店:
	if ($_GET["show"] == "next") {
		$last_hid = $_SESSION["rhid"];
		if (!$last_hid) {
			$cur_hid = $hospital_ids[0];
		} else {
			foreach ($hospital_ids as $k => $v) {
				if ($v == $last_hid) {
					if ($hospital_ids[$k+1]) {
						$cur_hid = $hospital_ids[$k+1];
					} else {
						$cur_hid = $hospital_ids[0];
					}
					break;
				}
			}
		}
	} else {
		$cur_hid = $hospital_ids[0];
	}

	$_SESSION["rhid"] = $cur_hid;

	//$cur_hid = 69; //测试之用

	// 门店信息:
	$h_info = $db->query("select * from hospital where id=$cur_hid limit 1", "1");
	$h_name = $h_info["name"];
	$hc = @unserialize($h_info["config"]); //门店配置信息（内含额定指标等数据）

	$table = "patient_".$cur_hid;

	// 查询过去N个月的预约/就诊数据，以及参考线
	$ms = array(); //月份
	for ($i = 6; $i>=0; $i--) {
		$dt = date("Y-m", strtotime("-".$i." month", time()));
		$ms[str_replace("-", "", $dt)] = $dt;
	}

	$come = $jishu = $zhibiao = $mubiao = array(); // 到店数据
	foreach ($ms as $ndt => $dt) {
		// 参考线数据:
		$jishu[$ndt] = intval($hc[$ndt]["jiangli_jishu"]);
		$zhibiao[$ndt] = intval($hc[$ndt]["jiangli_zhibiao"]);
		$mubiao[$ndt] = intval($hc[$ndt]["jiuzhen_mubiao"]);

		// 统计:
		$timebegin = strtotime($dt."-01 0:0:0"); //该月起始
		$timeend = strtotime("+1 month", $timebegin); //该月结束
		$come[$ndt] = @intval($db->query("select count(*) as c from $table where order_date>=$timebegin and order_date<$timeend and status=1", 1, "c"));
	}



	// 输出报表部分:
	$FC = new FusionCharts("MSColumn2DLineDY","1000","500", "", 1);
	$FC->setSWFPath("/res/chart/");
	$FC->setChartParams("decimalPrecision=0; formatNumberScale=0; baseFontSize=12; baseFont=Arial; chartBottomMargin=0; outCnvBaseFontSize=12;" );

	foreach ($ms as $dt) {
		$FC->addCategory($dt);
	}

	$FC->addDataset("到店量","numberPrefix=;showValues=1");
	foreach ($come as $v) {
		$FC->addChartData($v);
	}

	$FC->addDataset("奖励基数","parentYAxis=S");
	foreach ($jishu as $v) {
		$FC->addChartData($v);
	}

	$FC->addDataset("奖励指标","parentYAxis=S");
	foreach ($zhibiao as $v) {
		$FC->addChartData($v);
	}

	$FC->addDataset("目标就诊","parentYAxis=S");
	foreach ($mubiao as $v) {
		$FC->addChartData($v);
	}

	$max = @max(max($mubiao), max($come), max($zhibiao), max($jishu));
	$ymax = 10 * ceil(($max + 50) / 10);
	$FC->setChartParams("PYAxisMaxValue={$ymax}");
	$FC->setChartParams("SYAxisMaxValue={$ymax}");
}

?>
<html>
<head>
<title>当日预约/到店报表</title>
<meta http-equiv="Content-Type" content="text/html;charset=gbk">
<link href="/res/base.css" rel="stylesheet" type="text/css">
<script src="/res/base.js" language="javascript"></script>
<script src='/res/chart/FusionCharts.js' language='javascript'></script>
<style>
.w400 {width:400px }
.w800 {width:1000px; }
.hr {border:0; margin:0; padding:0; height:3px; line-height:0; font-size:0; background-color:red; color:white; border-top:1px solid silver; }
.h_name {font-size:16px; color:black; font-family:"微软雅黑"; font-weight:bold; margin-top:20px; margin-bottom:20px; }

.a_button {font-size:14px; font-weight:bold; line-height:30px; text-align:center; width:100px; height:30px; background:url('/res/img/button_submit.gif');}
</style>

<script type="text/javascript">
function show_next() {
	location = "?op=run&show=next";
}
</script>
</head>

<body>

<?php if ($op == "run") { ?>

<div style="width:100%; margin:0 auto; text-align:center;">

	<div class="h_name"><?php echo $h_name." <font color=white>".$cur_hid."</font>"; ?></div>

	<?php $FC->renderChart(); ?>
	<!-- <div class="w800" style="text-align:center"><?php echo "<b>"."目标/到店曲线</b>"; ?></div> -->

	<br>

	<?php //$FC2->renderChart(); ?>
	<!-- <div class="w800" style="text-align:center"><?php echo "<b>".date("Y年n月j日 H:i")." 即时预约数量</b>"; ?></div> -->

</div>

<?php if ($d_jishu == 0 || $d_zhibiao == 0 || $d_mubiao == 0) { ?>
<!-- <div style="padding:20px; text-align:center;">
<b>提示</b>：请设置奖励基数、奖励指标和目标就诊数据(在门店列表中)
</div> -->
<?php } ?>

<script type="text/javascript">
setTimeout("show_next()", 12000);
</script>

<?php } else { ?>

<div style="margin:50px;">
	<a href="report4.php?op=run" class="a_button" target="_blank">按月份查看</a> &nbsp; &nbsp;
	<a href="report4_d.php?op=run" class="a_button" target="_blank">按日查看</a>
</div>

<?php } ?>

</body>
</html>