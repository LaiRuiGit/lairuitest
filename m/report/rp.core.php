<?php
/*
// 说明: 报表核心数据定义
// 作者: 小陈 
*/
if (empty($hid)) {
	exit_html("对不起，没有选择归属门店，不能执行该操作！");
}
$table = "patient_{$hid}";
$h_name = $db->query("select name from hospital where id=$hid limit 1", 1, "name");

// 统计结果类型
$type_arr = array(1=>"按年统计", 2=>"按月统计", /*3=>"按周统计(实现中)",*/ 4=>"按日统计", 5=>"按时间段统计");

// 部门
$part_arr = array(3=>"电话");

// 来店状态
//$come_arr = array(1=>"已到", 2=>"未到");
$come_arr = array(0=>"待预约",1=>"已签约", 2=>"已放弃",3=>"已量尺", 4=>"已报价");

// 时间格式
$timetype_arr = array("order_date"=>"到店时间", "addtime"=>"添加时间");

// 销售 下面方法效率差，还是按需调用的好 (如果从人员表中取，怕有些销售离职被删除了，但其数据还在):
if ($need_kf_arr) {
	$kf_arr = $db->query("select author,count(author) as c from $table where addtime>".strtotime("-1 year")." group by author order by c desc", "author", "c");
}

// 媒体来源:
$media_arr = array("网络", "电话"); //内置媒体来院
$media_arr2 = $db->query("select * from media where (hospital_id=0 or hospital_id=$hid) order by id asc", "", "name");
if (is_array($media_arr2) && count($media_arr2) > 0) {
	$media_arr = array_merge($media_arr, $media_arr2);
}


/*
// 对查询条件的处理------------------------
*/
if ($_GET["op"] == "report") {

	// 将参数记录到 session，别的几个报表使用同样条件
	$_SESSION[$cfgSessionName]["rp_condition"] = $_GET;

	// 统计结果类型:
	$type = noe($_GET["type"], 1);
	$type_tips = $type_arr[$type];

	// 查询类型及其时间定义:
	$max_tb = $tb = strtotime($_GET["btime"]." 00:00:00");
	$max_te = $te = strtotime($_GET["etime"]." 23:59:59");
	if (date("Ymd", $tb) > date("Ymd", $te)) {
		exit_html("结束时间必须大于开始时间。请返回重新设置。");
	}
	$final_dt_arr = array(); //包含时间起止点和key的数组，最终用于循环查询和输出
	if ($type == 1) { //年
		$y_begin = date("Y", $tb);
		$y_end = date("Y", $te);

		// 最大化时间范围:
		$max_tb = strtotime("{$y_begin}-01-01 00:00:00");
		$max_te = strtotime("{$y_end}-12-31 23:59:59");

		$y_arr = array();
		if ($y_begin == $y_end) {
			$y_arr = array($y_begin);
			$final_dt_arr[$y_begin] = array(strtotime("{$y_begin}-01-01 00:00:00"), strtotime("{$y_begin}-12-31 23:59:59"));
		} else {
			for ($i = $y_end; $i >= $y_begin; $i--) {
				$y_arr[] = $i;
				$final_dt_arr[$i] = array(strtotime("{$i}-01-01 00:00:00"), strtotime("{$i}-12-31 23:59:59"));
			}
		}
	} else if ($type == 2) { //月
		$m_begin = date("Y-m", $tb);
		$m_end = date("Y-m", $te);

		// 最大化时间范围:
		$max_tb = strtotime("{$m_begin}-01 00:00:00");
		$max_te = strtotime("+1 month", strtotime("{$m_end}-01 00:00:00")) - 1;

		$m_arr = array();
		if ($m_begin == $m_end) {
			$m_arr = array($m_begin);
			$final_dt_arr[$m_begin] = array(strtotime("{$m_begin}-01 00:00:00"), (strtotime("+1 month", strtotime("{$m_begin}-01 00:00:00") - 1)));
		} else {
			$tmp = 0;
			do {
				$m_arr[] = $_dt = date("Y-m", strtotime("-".$tmp." month", $te)); //有潜在问题，如果日期选择为31日，-1 month就会有问题了
				$final_dt_arr[$_dt] = array(strtotime("{$_dt}-01 00:00:00"), (strtotime("+1 month", strtotime("{$_dt}-01 00:00:00") - 1)));
				$tmp++;
				if ($tmp > 36) break; //限制最多x个月
			} while (intval(str_replace("-", "", $_dt)) > intval(str_replace("-", "", $m_begin)));
		}
	} else if ($type == 3) { //周
		exit_html("按周统计功能延后开发中....");
	} else if ($type == 4) { //天
		$d_begin = date("Y-m-d", $tb);
		$d_end = date("Y-m-d", $te);

		// 最大化时间范围:
		$max_tb = strtotime("{$d_begin} 00:00:00");
		$max_te = strtotime("{$d_end} 23:59:59");

		$d_arr = array();
		if ($d_begin == $d_end) {
			$d_arr = array($d_begin);
			$final_dt_arr[$d_begin] = array(strtotime("{$d_begin} 00:00:00"), strtotime("{$d_begin} 23:59:59"));
		} else {
			$tmp = 0;
			do {
				$d_arr[] = $_dt = date("Y-m-d", strtotime("-".$tmp." day", $te));
				$final_dt_arr[$_dt] = array(strtotime("{$_dt} 00:00:00"), strtotime("{$_dt} 23:59:59"));
				$tmp++;
				if ($tmp > 31) break; //限制最多x天
			} while (intval(str_replace("-", "", $_dt)) > intval(str_replace("-", "", $d_begin)));
		}
	} else if ($type == 5) { //时段(叠加)
		$sd_arr = array();
		for ($i = 6; $i <= 23; $i++) {
			$sd_arr[] = $i;
			$final_dt_arr[$i."~".($i+1)] = $i;
		}
	}


	// 时间类型:
	$timetype = noe($_GET["timetype"], "order_date");
	$timetype_tips = $timetype_arr[$timetype];


	// 条件限定：
	$w = array();
	if ($_GET["part"]) {
		$w[] = "part_id=".intval($_GET["part"]);
	}
	if ($_GET["media"]) {
		$w[] = "media_from='".$_GET["media"]."'";
	}
	if ($_GET["come"]) {
		if ($_GET["come"] == 1) {
			$w[] = "status=1";
		} else {
			$w[] = "status!=1";
		}
	}

	$where = '';
	if (count($w) > 0) {
		$where = implode(" and ", $w)." and ";
	}
}

?>