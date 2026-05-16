<?php
/*
// - 功能说明 : 新增、修改顾客资料
// - 创建作者 : 小陈 
// - 创建时间 : 2014-04-03 11:57
*/
$mode = $op;

function request_by_other($remote_server, $post_string)
{
	global $tel_Account;
	$context = array(
		'http' => array(
			'method' => 'POST',
			'header' => 'Content-type: application/x-www-form-urlencoded' .
						'\r\n'.'User-Agent : Jimmy\'s POST Example beta' .
						'\r\n'.'Content-length:' . strlen($post_string) + 8,
			'content' => 'mypost=' . $post_string)
		);
	$stream_context = stream_context_create($context);
	$data = file_get_contents($remote_server, false, $stream_context);
	return $data;
}

if ($_POST) {
	$po = &$_POST; //引用 $_POST

	if ($mode == "edit") {
		$oldline = $db->query("select * from $table where id=$id limit 1", 1);
	} else {
		// 检查一个月内的顾客中有无重复的:
		$name = trim($po["name"]);
		$tel = trim($po["tel"]);
		if (strlen($tel) >= 7) {
			$thetime = strtotime("-1 month");
			$list = $db->query("select * from $table where tel='$tel' and addtime>$thetime limit 1", 1);
			if ($list && count($list) > 0) {
				msg_box("电话号码重复，提交失败", "back", 1, 5);
			}
		}
	}

	/*
	// 检查搜索引擎字段:
	if (!$oldline) {
		$test_line = $db->query("select * from $table limit 1", 1);
	} else {
		$test_line = $oldline;
	}

	// 自动检测字段:  后期可以去除
	if (!isset($test_line["engine"])) {
		$db->query("alter table `{$table}` add `engine` varchar(32) not null after `media_from`;");
	}
	if (!isset($test_line["engine_key"])) {
		$db->query("alter table `{$table}` add `engine_key` varchar(32) not null after `engine`;");
	}
	if (!isset($test_line["from_site"])) {
		$db->query("alter table `{$table}` add `from_site` varchar(40) not null after `engine_key`;");
	}
	*/


	// 销售添加疾病类型  2010-10-27
	if ($po["disease_id"] == -1) {
		$d_name = $po["disease_add"];
		$d_id = 0;
		if ($d_name != '') {
			$d_id = $db->query("insert into disease set hospital_id='$hid', name='$d_name', addtime='$time', author='$username'");
		}
		$po["disease_id"] = $d_id ? $d_id : 0;
	}


	$r = array();
	if (isset($po["name"])) $r["name"] = trim($po["name"]);
	if (isset($po["sex"])) $r["sex"] = $po["sex"];
	if (isset($po["qq"])) $r["qq"] = $po["qq"]; //2010-10-28
	if (isset($po["age"])) $r["age"] = $po["age"];
	if (isset($po["content"])) $r["content"] = $po["content"];
	if (isset($po["disease_id"])) $r["disease_id"] = $po["disease_id"];
	if (isset($po["depart"])) $r["depart"] = $po["depart"];
	if (isset($po["media_from"])) $r["media_from"] = $po["media_from"];
	if (isset($po["engine"])) $r["engine"] = $po["engine"];
	if (isset($po["engine_key"])) $r["engine_key"] = $po["engine_key"];
	if (isset($po["from_site"])) $r["from_site"] = $po["from_site"];
	if (isset($po["from_account"])) $r["from_account"] = $po["from_account"]; //2010-11-04
	if (isset($po["zhuanjia_num"])) $r["zhuanjia_num"] = $po["zhuanjia_num"];
	if (isset($po["is_local"])) $r["is_local"] = $po["is_local"];
	if (isset($po["area"])) $r["area"] = $po["area"];
	//if (isset($po["mtly"])) $r["mtly"] = $po["mtly"];
	
	if (($username == "admin") || !in_array($uinfo["part_id"], array(4))) { 
		if (isset($po["addtime"])) $r["addtime"] = strtotime($po["addtime"]);
	}
	// 修改时间:
	if (isset($po["order_date"])) {
		$order_date_post = @strtotime($po["order_date"]);
		if ($mode == "add") {

			// 如果修改，该时间不能被修改为当前时间的一个月之前(2011-01-15)
			if ($order_date_post < strtotime("-1 month")) {
				exit_html("预约时间不能是一个月之前。（请先检查您的电脑时间是否有误！）  请返回重新填写。");
			}

			$r["order_date"] = $order_date_post; //新增
		} else {
			//判断时间是否有修改
			if ($order_date_post != $oldline["order_date"]) {

				// 如果修改，该时间不能被修改为当前时间的一个月之前(2011-01-15)
				if ($order_date_post < strtotime("-1 month")) {
					exit_html("预约时间不能被修改到一个月之前。（请先检查您的电脑时间是否有误！）  请返回重新填写。");
				}

				$r["order_date"] = $order_date_post;
				$r["order_date_changes"] = intval($oldline["order_date_changes"])+1;
				$r["order_date_log"] = $oldline["order_date_log"].(date("Y-m-d H:i:s")." ".$realname." 修改 (".date("Y-m-d H:i", $oldline["order_date"])." => ".date("Y-m-d H:i", $order_date_post).")<br>");

				// 如果修改预约时间，自动修改状态为等待
				if ($oldline["status"] == 2) {
					$r["status"] = 0;
				}
			}
		}
	}

	if (isset($po["memo"])) $r["memo"] = $po["memo"];
	if (isset($po["status"])) $r["status"] = $po["status"];
	if (isset($po["fee"])) $r["fee"] = $po["fee"]; //2010-11-18

	// 将接待人修改为当前的导医:
	if ($mode == "edit" && $oldline["jiedai"] == '' && $uinfo["part_id"] == 4) {
		$r["jiedai"] = $realname;
	}

	// 导医添加直接设置为已到:
	if ($mode == "add" && $uinfo["part_id"] == 4) {
		$r["status"] = 1; //已到
		$r["jiedai"] = $realname;
	}

	if (isset($po["doctor"])) {
		$r["doctor"] = $po["doctor"];
	}

	// 已做的整形项目:
	if ($po["update_xiangmu"]) {
		$r["xiangmu"] = @implode(" ", $po["xiangmu"]);
	}

	if (isset($po["huifang"]) && trim($po["huifang"]) != '') {
		$r["huifang"] = $oldline["huifang"]."<b>".date("Y-m-d H:i")." [".$realname."]</b>:  ".$po["huifang"]."\n";
	}


	if ($mode == "edit") { //修改模式
		if (isset($po["jiedai_content"])) {
			$r["jiedai_content"] = $po["jiedai_content"];
		}

		// 修改记录？
		if ($oldline["author"] != $realname) {
			$r["edit_log"] = $oldline["edit_log"].$realname.' 于 '.date("Y-m-d H:i:s")." 修改过该资料<br>";
		}
	} else {         //新增模式
		$r["part_id"] = $uinfo["part_id"];
		$r["addtime"] = time();
		$r["author"] = $realname;
	}

	if (isset($po["tel"])) {
		$tel = trim($po["tel"]);
		//if (strlen($tel) > 20) $tel = substr($tel, 0, 20);
		//$r["tel"] = ec($tel, "ENCODE", md5($encode_password));
		$r["tel"] = $tel;
		if ($op == "add" && isset($tel_Account)) {
			//$user_hospital_id;
			$get_hospital = $db->query("select * from mtly where hospital='$user_hospital_id' limit 0,1", "id", "name");
			foreach($get_hospital as $tel_msg)
			{
				$get_msg=$tel_msg;
			}
			if(preg_match("/1[3458]{1}\d{9}$/",$tel)){
				$get_msg=$po["name"]."您好！\r\n".$get_msg;
				//{所在小区} zhuanjia_num
				$get_msg=str_replace("{所在小区}",$po["zhuanjia_num"],$get_msg);
				$msg=urlencode ($get_msg);
				$post_string = "aaa=go&func=sendsms&username={$tel_Account[0]}&password={$tel_Account[1]}&mobiles={$tel}&message={$msg}";
				$yz=request_by_other('http://sms.c8686.com/Api/BayouSmsApiEx.aspx',$post_string);
				
				/*$f=fopen("tel_Verification.txt","w");
				fwrite($f,$yz."\r\n".$get_msg);
				fclose($f);*/
			}
		}
	}

	if (isset($r["status"])) {
		if (($op == "add" && $r["status"] == 1) || ($op == "edit" && $oldline["status"] != 1 && $r["status"] == 1)) {
			$r["order_date"] = time();
		}
	}

	if ($mode == "edit" && isset($po["rechecktime"]) && $po["rechecktime"] != '') {
		if (strlen($po["rechecktime"]) <= 2 && is_numeric($po["rechecktime"])) {
			$rechecktime = ($r["order_date"] ? $r["order_date"] : $oldline["order_date"]) + intval($po["rechecktime"])*24*3600;
		} else {
			$rechecktime = strtotime($po["rechecktime"]." 0:0:0");
		}
		$r["rechecktime"] = $rechecktime;
	}

	$sqldata = $db->sqljoin($r);
	if ($mode == "edit") {
		$sql = "update $table set $sqldata where id='$id' limit 1";
	} else {
		$sql = "insert into $table set $sqldata";
	}

	$return = $db->query($sql);

	if ($return) {
		if ($op == "add") $id = $return;
		if ($mode == "edit") {
			//$log->add("edit", ("修改了顾客资料或状态: ".$oldline["name"]), $oldline, $table);
		} else {
			//$log->add("add", ("添加了顾客: ".$r["name"]), $r, $table);
		}
		msg_box("资料提交成功", history(2, $id), 1);
	} else {
		msg_box("资料提交失败，系统繁忙，请稍后再试。", "back", 1, 5);
	}
}

// 读取字典:
$hospital_list = $db->query("select id,name from hospital");
$disease_list = $db->query("select id,name from disease where hospital_id='$user_hospital_id' and isshow=1 order by sort desc,sort2 desc", "id", "name");
$doctor_list = $db->query("select id,name from doctor where hospital_id='$user_hospital_id'");
$part_id_name = $db->query("select id,name from sys_part", "id", "name");
$depart_list = $db->query("select id,name from depart where hospital_id='$user_hospital_id'");
$engine_list = $db->query("select id,name from engine", "id", "name");
$sites_list = $db->query("select id,url from sites where hid=$hid", "id", "url");
$account_list = $db->query("select id,concat(name,if(type='web',' (网络)',' (电话)')) as fname from count_type where hid=$hid order by id asc", "id", "fname");
$time1 = strtotime("-3 month");
$area_list = $db->query("select area, count(area) as c from $table where area!='' and addtime>$time1 group by area order by c desc limit 20", "", "area");


$account_first = 0;
if (count($account_list) > 0) {
	$tmp = @array_keys($account_list);
	$account_first = $tmp[0];
}

$status_array = array(
	array("id"=>0, "name"=>'待预约'),
	array("id"=>1, "name"=>'已签约'),
	array("id"=>2, "name"=>'已放弃'),
    array("id"=>3, "name"=>'已量尺'),
    array("id"=>4, "name"=>'已报价'),
);

$xiaofei_array = array(
	array("id"=>0, "name"=>'未消费'),
	array("id"=>1, "name"=>'已消费'),
);


// 取前30个病种:
$show_disease = array();
foreach ($disease_list as $k => $v) {
	$show_disease[$k] = $v;
	if (count($show_disease) >= 30) {
		break;
	}
}

$show_doctor = array();
foreach ($doctor_list as $k1 => $v1) {
    $show_doctor[$k1] = $v1;
    if (count($show_doctor) >= 30) {
        break;
    }
}


// 读取编辑 资料
$cur_disease_list = array();
if ($mode == "edit") {
	$line = $db->query_first("select * from $table where id='$id' limit 1");

	$cur_disease_list = explode(",", $line["disease_id"]);
	foreach ($cur_disease_list as $v) {
		if ($v && !array_key_exists($v, $show_disease)) {
			$show_disease[$v] = $disease_list[$v];
		}
	}
}


// 2010-05-18
$media_from_array = explode(" ", "电话"); // 网挂 杂志 市场 地铁 朋友介绍 路牌 电视 电台 短信 路过 车身 广告 报纸 其他
$media_from_array2 = $db->query("select name from media where hospital_id='$user_hospital_id'", "", "name");
foreach ($media_from_array2 as $v) {
	if (!in_array($v, $media_from_array)) {
		$media_from_array[] = $v;
	}
}

$mtly_from_array2 = $db->query("select name from mtly", "", "name");
foreach ($mtly_from_array2 as $v) {
		$mtly_from_array[] = $v;
}

// 2010-10-23

//$is_local_array = array(1 => "局改", 2 => "全屋", 3 => "整装", 4 => "翻新", 5 => "家具", 6 => "软装");
$is_local_array = $db->query("select id,name from doctor", "id", "name");

// 控制各选项是否可以编辑:
$all_field = explode(" ", "name sex age tel qq content disease_id media_from zhuanjia_num order_date doctor status xiaofei memo xiangmu huifang depart is_local from_account fee");

$ce = array(); // can_edit 的简写, 某字段是否能编辑
if ($mode == "edit") { // 修改模式
	$edit_field = array();
	if ($uinfo["part_id"] == 2 || $uinfo["part_id"] == 3) {
		// 未被修改过的资料，还能修改:
		if ($line["status"] == 0 || $line["status"] == 2) {
			if ($line["author"] == $realname) {
				$edit_field = explode(' ', 'qq content disease_id media_from zhuanjia_num memo order_date depart is_local from_account'); //自己修改
			} else {
				$edit_field[] = 'memo'; //不是自己的资料，能修改备注
			}
		} else if ($line["status"] == 1) {
			$edit_field[] = 'memo'; //已到的能修改备注
		}

		$edit_field[] = "order_date"; //修改回访，并能调整预约时间
		$edit_field[] = "huifang";

		if ($uinfo["part_id"] == 3) {
			$edit_field[] = 'xiangmu';
			$edit_field[] = "rechecktime";
		}
	} else if ($uinfo["part_id"] == 4) {
		//if ($line["author"] != $realname) {
		// 导医能修改 接待医生，赴约状态，消费，备注等资料
		if ($line["status"] == 1) {
			$edit_field[] = 'memo';
			$edit_field[] = 'xiangmu';
			$edit_field[] = 'rechecktime';
			$edit_field[] = 'fee';
		} else {
			$edit_field = explode(' ', 'name doctor status xiaofei memo');
		}
	} else if ($uinfo["part_id"] == 12) {
		// 电话回访部门
		$edit_field[] = 'order_date';
		$edit_field[] = 'memo';
		$edit_field[] = 'xiangmu';
		$edit_field[] = 'huifang';
		$edit_field[] = 'rechecktime';
	} else {
		// 管理员 修改所有的资料
		$edit_field = $all_field;
	}
} else { // 新增模式
	if ($uinfo["part_id"] == 2 || $uinfo["part_id"] == 3) { //销售添加
		$edit_field = explode(' ', 'name sex age tel qq content disease_id media_from zhuanjia_num order_date memo depart is_local from_account');
	} else if ($uinfo["part_id"] == 4) { //导医添加
		$edit_field = explode(' ', 'name sex age tel qq content disease_id media_from zhuanjia_num order_date doctor status memo depart is_local from_account');
	} else {
		$edit_field = $all_field;
	}
}

// 已设置为消费，并且是隔天的数据，将不能修改！
if ($line["status"] == 1 && (strtotime(date("Y-m-d 0:0:0")) > strtotime(date("Y-m-d 0:0:0", $line["come_date"])))) {
	//$edit_field = array(); //全部不能修改
}

// 每个字段是否能编辑:
foreach ($all_field as $v) {
	$ce[$v] = in_array($v, $edit_field) ? '' : ' disabled="true"';
}

// 2013-06-30 10:42 fix
if ($line["media_from"] == "网络销售") {
	$line["media_from"] = "网络";
} else if ($line["media_from"] == "电话销售") {
	$line["media_from"] = "电话";
}


$title = $mode == "edit" ? "修改顾客资料" : "添加新的顾客资料";

// page begin ----------------------------------------------------
?>
<html>
<head>
<title><?php echo $title; ?></title>
<meta http-equiv="Content-Type" content="text/html;charset=gb2312">
<link href="/res/base.css" rel="stylesheet" type="text/css">
<script src="/res/base.js" language="javascript"></script>
<script src="/res/datejs/picker.js" language="javascript"></script>
<style>
.dischk {width:6em; height:16px; line-height:16px; vertical-align:middle; white-space:nowrap; text-overflow:ellipsis; overflow:hidden; padding:0; margin:0; }
</style>
<script language="javascript">
function check_data() {
	var oForm = document.mainform;
	if (oForm.name.value == "") {
		alert("请输入顾客姓名！"); oForm.name.focus(); return false;
	}
	if (oForm.tel.value != "" && get_num(oForm.tel.value) == '') {
		alert("请正确输入顾客的联系电话！"); oForm.tel.focus(); return false;
	}
	if (oForm.sex.value == '') {
		alert("请输入“性别”！"); oForm.sex.focus(); return false;
	}
	if (oForm.media_from.value == '') {
		alert("请选择“媒体来源”！"); oForm.media_from.focus(); return false;
	}
	if (oForm.order_date.value.length < 12) {
		alert("请正确填写“预约时间”！"); oForm.order_date.focus(); return false;
	}
	<?php if (($username == "admin") || !in_array($uinfo["part_id"], array(4))) { ?>
	if (oForm.addtime.value == '') {
		alert("管理员账号必须选择登记时间！"); oForm.addtime.focus(); return false;
	}
	<?php }?>
	return true;
}
function input(id, value) {
	if (byid(id).disabled != true) {
		byid(id).value = value;
	}
}

function input_date(id, value) {
	var cv = byid(id).value;
	var time = cv.split(" ")[1];

	if (byid(id).disabled != true) {
		byid(id).value = value+" "+(time ? time : '00:00:00');
	}
}

function input_time(id, time) {
	var s = byid(id).value;
	if (s == '') {
		alert("请先填写日期，再填写时间！");
		return;
	}
	var date = s.split(" ")[0];
	var datetime = date+" "+time;

	if (byid(id).disabled != true) {
		byid(id).value = datetime;
	}
}

// 当状态为已到时, 显示选择接待医生:
function change_yisheng(v) {
	byid("yisheng").style.display = (v == 1 ? "inline" : "none");
}

// 检查数据重复:
function check_repeat(type, obj) {
	if (!byid("id") || (byid("id").value == '0' || byid("id").value == '')) {
		var value = obj.value;
		if (value != '') {
			var xm = new ajax();
			xm.connect("/http/check_repeat.php?type="+type+"&value="+value+"&r="+Math.random(), "GET", "", check_repeat_do);
		}
	}
}

function check_repeat_do(o) {
	var out = ajax_out(o);
	if (out["status"] == "ok") {
		if (out["tips"] != '') {
			alert(out["tips"]);
		}
	}
}

function show_hide_engine(o) {
	byid("engine_show").style.display = (o.value == "网络" ? "inline" : "none");
}

function show_hide_area(o) {
	byid("area_from_box").style.display = (o.value == "2" ? "inline" : "none");
}

function show_hide_disease_add(o) {
	byid("disease_add_box").style.display = (o.value == "-1" ? "inline" : "none");
}

function set_color(o) {
	if (o.checked) {
		o.nextSibling.style.color = "blue";
	} else {
		o.nextSibling.style.color = "";
	}
}

</script>
</head>

<body>
<!-- 头部 begin -->
<div class="headers">
	<div class="headers_title"><span class="tips"><?php echo $title; ?></span></div>
	<div class="header_center"><!-- <button onclick="if (check_data()) document.forms['mainform'].submit();" class="buttonb">提交数据</button> --></div>
	<div class="headers_oprate"><button onClick="history.back()" class="button">返回</button></div>
</div>
<!-- 头部 end -->

<div class="space"></div>
<div class="description">
	<div class="d_title">提示：</div>
	<div class="d_item">1.姓名必须填写；　2.电话号码如果填写，则必须是数字，不少于7位；　3.未尽资料填写于备注中。</div>
</div>

<div class="space"></div>
<form name="mainform" method="POST" onSubmit="return check_data()">
<table width="100%" class="edit">
	<tr>
		<td colspan="2" class="head">顾客基本资料</td>
	</tr>
	<tr>
		<td class="left">姓名：</td>
		<td class="right"><input name="name" id="name" value="<?php echo $line["name"]; ?>" class="input" style="width:200px" <?php echo $ce["name"]; ?> onChange="check_repeat('name', this)"> <span class="intro">* 名称必须填写</span></td>
	</tr>
	<tr>
		<td class="left">性别：</td>
		<td class="right"><input name="sex" id="sex" value="<?php echo $line["sex"]; ?>" class="input" style="width:80px" <?php echo $ce["sex"]; ?>> <a href="javascript:input('sex', '男')">[男]</a> <a href="javascript:input('sex', '女')">[女]</a> <span class="intro">填写顾客性别</span></td>
	</tr>
	<tr>
		<td class="left">年龄：</td>
		<td class="right"><input name="age" id="age" value="<?php echo $line["age"]; ?>" class="input" style="width:80px"> <span class="intro">填写年龄</span></td>
	</tr>
<?php if ($op == "add" || ($op == "edit" && $line["author"] == $realname) || $_SESSION[$cfgSessionName]["username"]=="admin") { ?>
	<tr>
		<td class="left">电话：</td>
		<td class="right"><input name="tel" id="tel" value="<?php echo $line["tel"]; ?>" class="input" style="width:200px" <?php echo $ce["tel"]; ?> onChange="check_repeat('tel', this)">  <span class="intro">电话号码或手机(可不填)</span></td>
	</tr>
<?php } ?>
	<tr>
		<td class="left" valign="top">房子需求：</td>
		<td class="right"><textarea name="content" style="width:60%; height:72px;vertical-align:middle;" <?php echo $ce["content"]; ?> class="input"><?php echo $line["content"]; ?></textarea> <span class="intro">房子需求总结</span></td>
	</tr>

	<tr>
		<td class="left" valign="top">装修房型：</td>
		<td class="right">
			<select name="doctor" class="combo" >
				<option value="" style="color:gray">--请选择--</option>
                <?php echo list_option($show_disease, '_key_', '_value_', $line["doctor"]); ?>

            </select>
		</td>
	</tr>

<?php if (count($depart_list) > 0) { ?>
	<tr>
		<td class="left">所属门店：</td>
		<td class="right">
			<select name="depart" class="combo" <?php echo $ce["depart"]; ?>>
				<option value="0" style="color:gray">--请选择--</option>
				<?php echo list_option($depart_list, 'id', 'name', $line["depart"]); ?>
			</select>
			<span class="intro">请选择门店门店</span>
		</td>
	</tr>
<?php } ?>

	<tr>
		<td class="left">媒体来源：</td>
		<td class="right">
			<select name="media_from" class="combo" <?php echo $ce["media_from"]; ?> onChange="show_hide_engine(this)">
				<option value="" style="color:gray">--请选择--</option>
				<?php echo list_option($media_from_array, '_value_', '_value_', $line["media_from"]); ?>
			</select>&nbsp;
			<span id="engine_show" style="display:<?php echo $line["media_from"] == "网络" ? "" : "none"; ?>" <?php echo $ce["media_from"]; ?>>
				<select name="engine" class="combo">
					<option value="" style="color:gray">--搜索引擎来源--</option>
					<?php echo list_option($engine_list, '_value_', '_value_', $line["engine"]); ?>
				</select>
				房型：<input name="engine_key" value="<?php echo $line["engine_key"]; ?>" class="input" size="15" <?php echo $ce["media_from"]; ?>>
				<select name="from_site" class="combo" <?php echo $ce["media_from"]; ?>>
					<option value="" style="color:gray">--来源网站--</option>
					<?php echo list_option($sites_list, '_value_', '_value_', $line["from_site"]); ?>
				</select>
			</span>
			<span class="intro">请选择顾客来源</span>
		</td>
	</tr>   

	<tr>
		<td class="left">装修类型：</td>
		<td class="right">
			<select name="disease_id" class="combo" <?php echo $ce["disease_id"]; ?> onChange="show_hide_area(this)">
				<option value="0" style="color:gray">--请选择--</option>
				<?php echo list_option($is_local_array, '_key_', '_value_', ($op == "add" ? 1 : $line["disease_id"])); ?>
			</select>&nbsp;

		</td>
	</tr>

	<!-- <tr>
		<td class="left">所属统计帐户：</td>
		<td class="right">
			<select name="from_account" class="combo" <?php echo $ce["from_account"]; ?>>
				<option value="0" style="color:gray">--请选择--</option>
				<?php echo list_option($account_list, '_key_', '_value_', ($op == "add" ? $account_first : $line["from_account"])); ?>
			</select>&nbsp;

			<span class="intro">请选择所属统计帐户</span>
		</td>
	</tr> -->

	<tr>
		<td class="left"><?php echo $uinfo["part_id"] == 4 ? "就诊号" : "所在小区"; ?>：</td>
		<td class="right"><input name="zhuanjia_num" value="<?php echo $line["zhuanjia_num"]; ?>" class="input" size="30" style="width:200px" <?php echo $ce["zhuanjia_num"]; ?>>  </td>
	</tr>

    <tr>
        <td class="left">房子面积：</td>
        <td class="right"><input name="qq" value="<?php echo $line["qq"]; ?>" class="input" style="width:140px" <?php echo $ce["qq"]; ?>>  <span class="intro">房子面积</span></td>
    </tr>
	<tr>
		<td class="left" valign="top">预约/回访时间：</td>
		<td class="right"><input name="order_date" value="<?php echo $line["order_date"] ? @date('Y-m-d H:i:s', $line["order_date"]) : ''; ?>" class="input" style="width:150px" id="order_date" <?php echo $ce["order_date"]; ?>> <img src="/res/img/calendar.gif" id="order_date" onClick="picker({el:'order_date',dateFmt:'yyyy-MM-dd HH:mm:ss'})" align="absmiddle" style="cursor:pointer" title="选择时间"> <span class="intro">已修改<?php echo intval($line["order_date_changes"]); ?>次</span> <span class="intro">请注意，此处已调整，预约时间不能早于上个月<?php echo date("j"); ?>号，否则资料无法提交。</span><?php if ($line["order_date_log"]) { ?><a href="javascript:void(0)" onClick="byid('order_date_log').style.display = (byid('order_date_log').style.display == 'none' ? 'block' : 'none'); ">查看修改记录</a><?php } ?>
		<?php
		$show_days = array(
			"今" => $today = date("Y-m-d"), //今天
			"明" => date("Y-m-d", strtotime("+1 day")), //明天
			"后" => date("Y-m-d", strtotime("+2 days")), //后天
			"大后天" => date("Y-m-d", strtotime("+3 days")), //大后天
			"周六" => date("Y-m-d", strtotime("next Saturday")), //周六
			"周日" => date("Y-m-d", strtotime("next Sunday")), // 周日
			"周一" => date("Y-m-d", strtotime("next Monday")), // 周一
			"一周后" => date("Y-m-d", strtotime("+7 days")), // 一周后
			"半月后" => date("Y-m-d", strtotime("+15 days")), //半个月后
		);
		if (!$ce["order_date"]) {
			echo '<div style="padding-top:6px;">日期: ';
			foreach ($show_days as $name => $value) {
				echo '<a href="javascript:input_date(\'order_date\', \''.$value.'\')">['.$name.']</a>&nbsp;';
			}
			echo '<br>时间: ';
			echo '<a href="javascript:input_time(\'order_date\',\'00:00:00\')">[时间不限]</a>&nbsp;';
			echo '<a href="javascript:input_time(\'order_date\',\'09:00:00\')">[上午9点]</a>&nbsp;';
			echo '<a href="javascript:input_time(\'order_date\',\'14:00:00\')">[下午2点]</a>&nbsp;</div>';
		}
		?>
		<?php if ($line["order_date_log"]) { ?>
		<div id="order_date_log" style="display:none; padding-top:6px;"><b>预约时间修改记录:</b> <br><?php echo strim($line["order_date_log"], '<br>'); ?></div>
		<?php } ?>
		</td>
	</tr>

	<tr>
		<td class="left" valign="top">备注：</td>
		<td class="right"><textarea name="memo" style="width:60%; height:48px;vertical-align:middle;" class="input" <?php echo $ce["memo"]; ?>><?php echo $line["memo"]; ?></textarea> <span class="intro">其他备注信息</span></td>
	</tr>
<?php if ($line["edit_log"] && $line["author"] == $realname) { ?>
	<tr>
		<td class="left" valign="top">资料修改记录：</td>
		<td class="right"><?php echo strim($line["edit_log"], '<br>'); ?></td>
	</tr>
<?php } ?>


<?php // 治疗项目 -------------  ?>
<?php
if (in_array($uinfo["part_id"], array(4,9,12)) && $line["status"] == 1) { ?>
	<tr>
		<td class="left">治疗项目：</td>
		<td class="right">
<?php
$xiangmu_str = $db->query("select xiangmu from disease where id=".$line["disease_id"]." limit 1", 1, "xiangmu");
$xiangmu = explode(" ", trim($xiangmu_str));
$cur_xiangmu = explode(" ", trim($line["xiangmu"]));
$xiangmu = array_unique(array_merge($cur_xiangmu, $xiangmu));
foreach ($xiangmu as $k) {
	if ($k == '') continue;
	$checked = in_array($k, $cur_xiangmu) ? " checked" : "";
	$makered = $checked ? ' style="color:red"' : '';
	echo '<input type="checkbox" name="xiangmu[]" value="'.$k.'"'.$checked.' id="xiangmu_'.$k.'"'. $ce["xiangmu"].'><label for="xiangmu_'.$k.'"'.$makered.'>'.$k.'</label>&nbsp;&nbsp;';
}
?>
<?php if (!$ce["xiangmu"]) { ?>
		<input type="hidden" name="update_xiangmu" value="1">
		<span id="xiangmu_user"></span>
		<span id="xiangmu_add"><b>增加：</b><input id="miangmu_my_add" class="input" size="10">&nbsp;<button onClick="xiangmu_user_add()" class="button">确定</button></span>
<script language="JavaScript">
function xiangmu_user_add() {
	var name = byid("miangmu_my_add").value;
	if (name == '') {
		alert("请输入新加的名字！"); return false;
	}
	var str = '<input type="checkbox" name="xiangmu[]" value="'+name+'" checked id="xiangmu_'+name+'"><label for="xiangmu_'+name+'">'+name+'</label>&nbsp;&nbsp;';
	byid("xiangmu_user").insertAdjacentHTML("beforeEnd", str);
	byid("miangmu_my_add").value = '';
}
</script>
<?php } ?>

		</td>
	</tr>

	<!-- 治疗费用 -->
<!--	<tr hidden>-->
<!--		<td class="left">治疗费用：</td>-->
<!--		<td class="right">-->
<!--			<input name="fee" id="fee" value="--><?php //echo $line["fee"] > 0 ? $line["fee"] : ''; ?><!--" class="input" --><?php //echo $ce["fee"]; ?><!-- size="20">-->
<!--			<span class="intro">治疗费用</span>-->
<!--		</td>-->
<!--	</tr>-->
<?php } ?>


<?php // 复查 -------------  ?>
<?php
if (in_array($uinfo["part_id"], array(4,9,12)) && $line["status"] == 1) { ?>
	<tr>
		<td class="left">复查时间：</td>
		<td class="right">
			<input name="rechecktime" id="rechecktime" value="<?php if ($line["rechecktime"]>0) echo date("Y-m-d", $line["rechecktime"]); ?>" class="input" <?php echo $ce["rechecktime"]; ?> size="20">
			<?php if ($line["rechecktime"]) echo intval(($line["rechecktime"] - $line["order_date"]) / 24/3600)."天 "; ?>
			 <img src="/res/img/calendar.gif" id="order_date" onClick="picker({el:'rechecktime',dateFmt:'yyyy-MM-dd'})" align="absmiddle" style="cursor:pointer" title="选择时间">
			<span class="intro">可填写天数(如 10 相对于预约时间推算)或具体时间(如 2013-10-1)</span>
		</td>
	</tr>
<?php } ?>

</table>


<?php if (in_array($uinfo["part_id"], array(1,4,9)) || ($username == "admin") || $debug_mode) { ?>
<div class="space"></div>
<table width="100%" class="edit">
	<tr>
		<td colspan="2" class="head">是否到店</td>
	</tr>
    <?php if (($username == "admin") || !in_array($uinfo["part_id"], array(4))) { ?>
    <tr>
	  <td class="left">修改登记时间：</td>
	  <td class="right"><input name="addtime" id="rechecktime" value="<?php if ($line["addtime"]>0) echo date("Y-m-d H:i:s", $line["addtime"]); ?>" class="input" <?php echo $ce["rechecktime"]; ?> size="20" onClick="picker({dateFmt:'yyyy-MM-dd HH:mm:ss'})"></td>
    </tr>
    <?php }?>
	<tr>
		<td class="left">赴约状态：</td>
		<td class="right">
        <?php $me_status_array = array ( 0 => array ( 'id' => '0','name' => '待预约'),1 => array ( 'id' => '1','name' => '已签约'),2 => array ( 'id' => '2','name' => '已放弃'),3 => array ( 'id' => '3','name' => '已量尺'),4 => array ( 'id' => '4','name' => '已报价'));?>
			<select name="status" class="combo" <?php echo $ce["status"]; ?>> <!-- onchange="change_yisheng(this.value)" -->
				<option value="0" style="color:gray">--请选择--</option>
				<?php echo list_option($me_status_array, 'id', 'name', ($mode == "add" && $uinfo["part_id"] == 4) ? 1 : $line["status"]); ?>
			</select> 
		</td>
	</tr>

</table>
<?php } ?>


<?php if (!in_array($uinfo["part_id"], array(1,4,9)) and ($username != "admin") || $debug_mode) { ?>

<div class="space"></div>
<table width="100%" class="edit">
	<tr>
		<td colspan="2" class="head">是否到店(前台账户只允许更改状态)</td>
	</tr>
	<tr>
		<td class="left">赴约状态：</td>
		<td class="right">
        <?php $me_status_array = array ( 0 => array ( 'id' => '0','name' => '待预约'),1 => array ( 'id' => '1','name' => '已签约'),2 => array ( 'id' => '2','name' => '已放弃'),3 => array ( 'id' => '3','name' => '已量尺'),4 => array ( 'id' => '4','name' => '已报价'));?>
			<select name="status" class="combo"> <!-- onchange="change_yisheng(this.value)" -->
				<option value="0" style="color:gray">--请选择--</option>
				<?php echo list_option($me_status_array, 'id', 'name', ($mode == "add" && $uinfo["part_id"] == 4) ? 1 : $line["status"]); ?>
			</select>
		</td>
	</tr>
  </table>
<?php } ?>


<?php if ($mode == "edit" && $line["status"] == 1 && ($debug_mode || in_array($uinfo["part_id"], array(1,4,9))) ) { ?>
<!-- 接待记录 -->
<div class="space"></div>
<table width="100%" class="edit">
	<tr>
		<td colspan="2" class="head">接待记录</td>
	</tr>
	<tr>
		<td class="left" valign="top">接待内容：</td>
		<td class="right"><textarea name="jiedai_content" style="width:60%; height:48px;vertical-align:middle;" class="input"><?php echo $line["jiedai_content"]; ?></textarea> <span class="intro">请填写接待内容</span></td>
	</tr>
</table>
<?php } ?>



<?php if ($mode == "edit" && (in_array("huifang", $edit_field) || $line["author"] == $username)) { ?>
<?php
	$huifang = trim($line["huifang"]);
?>
<div class="space"></div>
<table width="100%" class="edit">
	<tr>
		<td colspan="2" class="head">回访记录</td>
	</tr>
	<tr>
		<td class="left" valign="top">历次回访：</td>
		<td class="right"><?php echo $line["huifang"] ? text_show($line["huifang"]) : "<font color=gray>(暂无记录)</font>"; ?></td>
	</tr>
	<tr>
		<td class="left" valign="top">本次回访：</td>
		<td class="right"><textarea name="huifang" style="width:60%; height:48px;vertical-align:middle;" class="input"<?php echo $ce["huifang"]; ?>></textarea> <span class="intro">点评记录</span></td>
	</tr>
</table>
<?php } ?>

<input type="hidden" name="id" id="id" value="<?php echo $id; ?>">
<input type="hidden" name="op" value="<?php echo $mode; ?>">
<input type="hidden" name="go" value="<?php echo $_GET["go"]; ?>">

<div class="button_line"><input type="submit" class="submit" value="提交资料"></div>
</form>
</body>
</html>