<?php
/*
// - ??????? : main.php
// - ???????? : §³?? 
// - ??????? : 2013-05-13 12:28
*/
require "../core/core.php";
include "../core/function.lunar.php";

// -------------------- 2013-05-01 23:39
if ($_GET["do"] == 'change') {
	$_SESSION[$cfgSessionName]["hospital_id"] = $_GET["hospital_id"];
	$user_hospital_id = $_SESSION[$cfgSessionName]["hospital_id"];
}
$hospital_list = $db->query("select id,name from hospital where id in (".implode(',', $hospital_ids).") order by sort desc,id asc", 'id');

$part_id_name = $db->query("select id,name from sys_part", 'id', 'name');
// --------------------

// ?????????:
$today_tb = mktime(0,0,0);
$today_te = $today_tb + 24*3600;
$yesterday_tb = $today_tb - 24*3600;
$month_tb = mktime(0,0,0,date("m"),1);
$month_te = strtotime("+1 month", $month_tb);
$lastmonth_tb = strtotime("-1 month", $month_tb);

// ??????????(2010-11-27):
$tb_tb = strtotime("-1 month", $month_tb);
$tb_te = strtotime("-1 month", time());

// ?¡À?:
$yuebi_tb = strtotime("-1 month", $today_tb);
if (date("d", $yuebi_tb) != date("d", $today_tb)) {
	$yuebi_tb = $yuebi_te = -1;
} else {
	$yuebi_te = $yuebi_tb + 24*3600;
}

// ???:
$zhoubi_tb = strtotime("-7 day", $today_tb);
$zhoubi_te = $zhoubi_tb + 24*3600;

// ???:
$tb_tb = strtotime("-1 month", $month_tb); //?????Ù³?
$tb_te = strtotime("-1 month", time()); //?????????




// ???§Ý?????????:
function wee($tb, $te, $time_type='order_date', $condition='', $condition2='' ) {
	global $table, $db;
	$time_type = $time_type == "addtime" ? "addtime" : "order_date";
	$where = array();
	if ($tb > 0) $where[] = $time_type.">=".intval($tb);
	if ($te > 0) $where[] = $time_type."<".intval($te);
	if ($condition) $where[] = $condition;
	if ($condition2) $where[] = $condition2;
	$sqlwhere = implode(" and ", $where);
	$sql = "select count(*) as c from $table where $sqlwhere limit 1";
	$sql_md5 = md5($sql);

	// ??????:
	$timeout = 60; //???›‰????
	$sql_result = -1;
	$cache_file = "cache/".$table;
	if (file_exists($cache_file)) {
		$tm = @explode("\n", str_replace("\r", "", file_get_contents($cache_file)));
		foreach ($tm as $tml) {
			list($a, $b, $c) = explode("|", trim($tml));
			if ($a == $sql_md5) {
				if (time() - $b < $timeout) {
					$sql_result = $c;
					break;
				}
			}
		}
	}

	if ($sql_result != -1) {
		return $sql_result;
	} else {
		$sql_result = $db->query($sql, 1, "c");

		// ??????????:
		$tm = array();
		$find = 0;
		$time = time();
		if (file_exists($cache_file)) {
			$tm = @explode("\n", str_replace("\r", "", file_get_contents($cache_file)));
			foreach ($tm as $k => $tml) {
				list($a, $b, $c) = explode("|", trim($tml));
				if ($a == $sql_md5) {
					$tm[$k] = $sql_md5."|".$time."|".intval($sql_result);
					$find = 1;
				} else {
					if ($time - $b > $timeout) {
						unset($tm[$k]); //???????
					}
				}
			}
		}
		if ($find == 0) {
			$tm[] = $sql_md5."|".$time."|".intval($sql_result);
		}
		@file_put_contents($cache_file, implode("\r\n", $tm));
		// ???????:

		return $sql_result;
	}
}
?>
<html>
<head>
<title>??????</title>
<meta http-equiv="Content-Type" content="text/html;charset=gb2312">
	<meta name="renderer" content="webkit">
	<link href="../res/base.css" rel="stylesheet" type="text/css">
	<script src="jquery-3.5.1.min.js"></script>
<!--<script src="jquery.js" language="javascript"></script>-->
	<script src="../res/base.js" language="javascript"></script>

<script language="javascript">
function hgo(dir) {
	var obj = byid("hospital_id");
	if (dir == "up") {
		if (obj.selectedIndex > 1) {
			obj.selectedIndex = obj.selectedIndex - 1;
			obj.onchange();
		} else {
			parent.msg_box("?????????????????", 3);
		}
	}
	if (dir == "down") {
		if (obj.selectedIndex < obj.options.length-1) {
			obj.selectedIndex = obj.selectedIndex + 1;
			obj.onchange();
		} else {
			parent.msg_box("?????????????????", 3);
		}
	}
}
</script>
</head>

<body>
<div style='padding:20px 12px 12px 40px;'>
	<div style="line-height:24px">
<?php
$str = '?????<font color="#FF0000"><b>'.$realname.'</b></font>';
if ($uinfo["hospitals"] || $uinfo["part_id"] > 0) {
	if ($uinfo["part_id"] > 0) {
		$str .= '??(?????'.$part_id_name[$uinfo["part_id"]].")";
	}
}

$onlines = $db->query("select count(*) as count from sys_admin where online=1", 1, "count");
$str .= '?????????? <font color="red"><b>'.$onlines.'</b></font> ??';

if ($uinfo["part_id"] == 12) {
	//$str .= '<br><a href="#" onclick="parent.load_box(1,\'src\',\'patient_huifang_list_all.php\')">[???§Ò?]</a>';
}

?>
	</div>

<?php if (count($hospital_ids) > 1) { ?>
	<div style="margin-top:20px;">
		<b>?§Ý????</b>
		<select name="hospital_id" id="hospital_id" class="combo" onChange="location='?do=change&hospital_id='+this.value" style="width:200px;">
			<option value="" style="color:gray">--?????--</option>
			<?php echo list_option($hospital_list, 'id', 'name', $_SESSION[$cfgSessionName]["hospital_id"]); ?>
		</select>&nbsp;
		<button class="button" onClick="hgo('up');">??</button>&nbsp;
		<button class="button" onClick="hgo('down');">??</button>&nbsp;
<?php if ($user_hospital_id > 0) { ?>
		<button class="buttonb" onClick="self.location='/m/patient/patient.php?time_type=order_date&sort=?????&show=today&come=0'" title="??????¦Ä?????¨´??">??¨´??</button>&nbsp;
		<button class="buttonb AllMsg"  title="???????????????">?¿B???</button>&nbsp;
        <button class="buttonb AllMsg2"  title="???????????????">?????????</button>&nbsp;
        <button class="buttonb"><a href="history.php" target="_blank">??????</a></button>
	<?php if ($debug_mode || $username == "admin" || $uinfo["part_id"] == 3) { ?>
		<button class="buttonb" onClick="self.location='/m/patient/patient.php?list_huifang=1'" title="?????????¨´?????">?????</button>&nbsp;
	<?php } ?>
<?php }?>

<?php
if($_SESSION[$cfgSessionName]["username"]=="admin")
{
//	echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input name="tel_go" type="button" value="????????????????" onClick="if(confirm(\'????????????????????????§ß???????????????\')){window.open(\'/gettel/index.php\');}">';
}
?>
	</div>
	<?php } else if ($user_hospital_id > 0) { ?>
        <div style="margin-top:20px;">??????<b><?php echo $hospital_list[$user_hospital_id]["name"];?></b>
        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
        <?php if ($user_hospital_id > 0) { ?>
            <button class="buttonb" onClick="self.location='/m/patient/patient.php?time_type=order_date&sort=?????&show=today&come=0'" title="??????¦Ä?????¨´??">??¨´??</button>&nbsp;
        <?php if ($debug_mode || $username == "admin" || $uinfo["part_id"] == 3) { ?>
            <button class="buttonb" onClick="self.location='/m/patient/patient.php?list_huifang=1'" title="?????????¨´?????">?????</button>&nbsp;
        <?php } ?>
    <?php }?>
    
    <?php
    if($_SESSION[$cfgSessionName]["username"]=="admin")
    {
//        echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input name="tel_go" type="button" value="????????????????" onClick="if(confirm(\'????????????????????????§ß???????????????\')){window.open(\'/gettel/index.php\');}">';
    }
    ?>
        
    </div>
<?php } else { ?>
	<div style="margin-top:20px;">??????????????????????????????????</div>
<?php }?>
</div>


<!-- ??????? -->
<?php if ($user_hospital_id > 0) { ?>

<!-- ????????? -->
<?php
$table = "patient_".$user_hospital_id;

$where = array();
$where[] = '1';
if (!$debug_mode) {
	$read_parts = get_manage_part(); //???????????????????????)
	$manage_parts = explode(",", $read_parts);
	if ($uinfo["part_admin"] || $uinfo["part_manage"]) { //??????????????????
		$where[] = "(part_id in (".$read_parts.") or binary author='".$realname."')";
	} else { //???????????????????
		$where[] = "binary author='".$realname."'";
	}
}

// ?´Â?????????????:
if ($uinfo["part_id"] == 12) {
	//$where[] = "status=1";
}

    //???¼@??????????
    if ($uinfo["character_id"] == 46) {
        $where[] = " author = '".$realname."' ";
    }

$sqlwhere = implode(" and ", $where);

$today_all = $db->query("select count(*) as count from $table where $sqlwhere and order_date>=$today_tb and order_date<$today_te and status<>3", 1, "count");
if ($_GET["show"] == "sql") {
	echo $db->sql."<br>";
}

	//?????????????*?¿B
	$todayAllContent = 0;   //???????§Ö?  ?????
	$todayAllCome = 0;  //???????§Ö?  ?? ???

	$yesterdayAllContent = 0;  //???????§Ö? ?????
	$yesterdayAllCome = 0;    //???????§Ö? ?? ???

	$thismonthAllContent = 0;  //????????§Ö? ?????
	$thismonthAllCome = 0;    //????????§Ö? ?? ???

	$lastmonthAllContent = 0;  //shang???????§Ö? ?????
	$lastmonthAllCome = 0;    //shang???????§Ö? ?? ???

	$tbAllContent = 0;  //??? ???§Ö?
	$tbAllCome = 0;



    //?????????????*??????
    $todayAllContent_D = 0;   //???????§Ö?  ?????
    $todayAllCome_D = 0;  //???????§Ö?  ?? ???

    $yesterdayAllContent_D = 0;  //???????§Ö? ?????
    $yesterdayAllCome_D = 0;    //???????§Ö? ?? ???

    $thismonthAllContent_D = 0;  //????????§Ö? ?????
    $thismonthAllCome_D = 0;    //????????§Ö? ?? ???

    $lastmonthAllContent_D = 0;  //shang???????§Ö? ?????
    $lastmonthAllCome_D = 0;    //shang???????§Ö? ?? ???

    $tbAllContent_D = 0;  //??? ???§Ö?
    $tbAllCome_D = 0;



	foreach ($hospital_list as $i=>$V) {
//	    ?¿B
//	    if($i < 22){

        if(in_array($i,array(12,13,14,15,16,17,19,20))){
            $totalTable = "patient_".$i;//?????????

            $tdC = $db->query("select count(*) as count from $totalTable where $sqlwhere and order_date>=$today_tb and order_date<$today_te and status<>3", 1, "count");
            $todayAllContent += $tdC;
            $tdCome = $db->query("select count(*) as count from $totalTable where $sqlwhere and order_date>=$today_tb and order_date<$today_te and status=1", 1, "count");
            $todayAllCome += $tdCome;

            $yeC = $db->query("select count(*) as count from $totalTable where $sqlwhere and order_date>=$yesterday_tb and order_date<$today_tb and status<>3", 1, "count");
            $yesterdayAllContent += $yeC;
            $yeCome = $db->query("select count(*) as count from $totalTable where $sqlwhere and order_date>=$yesterday_tb and order_date<$today_tb and status=1", 1, "count");
            $yesterdayAllCome += $yeCome;

            $tmC = $db->query("select count(*) as count from $totalTable where $sqlwhere and order_date>=$month_tb and order_date<$month_te and status<>3", 1, "count");
            $thismonthAllContent += $tmC;
            $tmCome = $db->query("select count(*) as count from $totalTable where $sqlwhere and order_date>=$month_tb and order_date<$month_te and status=1", 1, "count");
            $thismonthAllCome += $tmCome;

            $lmC = $db->query("select count(*) as count from $totalTable where $sqlwhere and order_date>=$lastmonth_tb and order_date<$month_tb and status<>3", 1, "count");
            $lastmonthAllContent += $lmC;
            $lmCome = $db->query("select count(*) as count from $totalTable where $sqlwhere and order_date>=$lastmonth_tb and order_date<$month_tb and status=1", 1, "count");
            $lastmonthAllCome += $lmCome;

            // ???:
            $tbC = $db->query("select count(*) as count from $totalTable where $sqlwhere and order_date>=$tb_tb and order_date<$tb_te and status<>3", 1, "count");
            $tbAllContent += $tbC;
            $tbCome = $db->query("select count(*) as count from $totalTable where $sqlwhere and order_date>=$tb_tb and order_date<$tb_te and status=1", 1, "count");
            $tbAllCome += $tbCome;
        }
        elseif(in_array($i,array(22,23,24,25))){
            $totalTable = "patient_".$i;//?????????

            $tdC_D = $db->query("select count(*) as count from $totalTable where $sqlwhere and order_date>=$today_tb and order_date<$today_te and status<>3", 1, "count");
            $todayAllContent_D += $tdC_D;
            $tdCome_D = $db->query("select count(*) as count from $totalTable where $sqlwhere and order_date>=$today_tb and order_date<$today_te and status=1", 1, "count");
            $todayAllCome_D += $tdCome_D;

            $yeC_D = $db->query("select count(*) as count from $totalTable where $sqlwhere and order_date>=$yesterday_tb and order_date<$today_tb and status<>3", 1, "count");
            $yesterdayAllContent_D += $yeC_D;
            $yeCome_D = $db->query("select count(*) as count from $totalTable where $sqlwhere and order_date>=$yesterday_tb and order_date<$today_tb and status=1", 1, "count");
            $yesterdayAllCome_D += $yeCome_D;

            $tmC_D = $db->query("select count(*) as count from $totalTable where $sqlwhere and order_date>=$month_tb and order_date<$month_te and status<>3", 1, "count");
            $thismonthAllContent_D += $tmC_D;
            $tmCome_D = $db->query("select count(*) as count from $totalTable where $sqlwhere and order_date>=$month_tb and order_date<$month_te and status=1", 1, "count");
            $thismonthAllCome_D += $tmCome_D;

            $lmC_D = $db->query("select count(*) as count from $totalTable where $sqlwhere and order_date>=$lastmonth_tb and order_date<$month_tb and status<>3", 1, "count");
            $lastmonthAllContent_D += $lmC_D;
            $lmCome_D = $db->query("select count(*) as count from $totalTable where $sqlwhere and order_date>=$lastmonth_tb and order_date<$month_tb and status=1", 1, "count");
            $lastmonthAllCome_D += $lmCome_D;

            // ???:
            $tbC_D = $db->query("select count(*) as count from $totalTable where $sqlwhere and order_date>=$tb_tb and order_date<$tb_te and status<>3", 1, "count");
            $tbAllContent_D += $tbC_D;
            $tbCome_D = $db->query("select count(*) as count from $totalTable where $sqlwhere and order_date>=$tb_tb and order_date<$tb_te and status=1", 1, "count");
            $tbAllCome_D += $tbCome_D;
        }

	}

//?¿B
	$todayAllNot = $todayAllContent - $todayAllCome;  //???????§Ö? ¦Ä??
	$yesterdayAllNot = $yesterdayAllContent - $yesterdayAllCome;    //???????§Ö?   ??¦Ä??
	$thismonthAllNot = $thismonthAllContent - $thismonthAllCome;   //????????§Ö? ??¦Ä??
	$lastmonthAllNot = $lastmonthAllContent - $lastmonthAllCome;   //shang???????§Ö? ??¦Ä??
	$tbAllNot = $tbAllContent - $tbAllCome;   //???§Ö? ???


//??????
    $todayAllNot_D = $todayAllContent_D - $todayAllCome_D;  //???????§Ö? ¦Ä??
    $yesterdayAllNot_D = $yesterdayAllContent_D - $yesterdayAllCome_D;    //???????§Ö?   ??¦Ä??
    $thismonthAllNot_D = $thismonthAllContent_D - $thismonthAllCome_D;   //????????§Ö? ??¦Ä??
    $lastmonthAllNot_D = $lastmonthAllContent_D - $lastmonthAllCome_D;   //shang???????§Ö? ??¦Ä??
    $tbAllNot_D = $tbAllContent_D - $tbAllCome_D;   //???§Ö? ???


//
$today_come = $db->query("select count(*) as count from $table where $sqlwhere and order_date>=$today_tb and order_date<$today_te and status=1", 1, "count");
$today_not = $today_all - $today_come;

$yesterday_all = $db->query("select count(*) as count from $table where $sqlwhere and order_date>=$yesterday_tb and order_date<$today_tb and status<>3", 1, "count");
$yesterday_come = $db->query("select count(*) as count from $table where $sqlwhere and order_date>=$yesterday_tb and order_date<$today_tb and status=1", 1, "count");
$yesterday_not = $yesterday_all - $yesterday_come;


$this_month_all = $db->query("select count(*) as count from $table where $sqlwhere and order_date>=$month_tb and order_date<$month_te and status<>3", 1, "count");
$this_month_come = $db->query("select count(*) as count from $table where $sqlwhere and order_date>=$month_tb and order_date<$month_te and status=1", 1, "count");
$this_month_not = $this_month_all - $this_month_come;

$last_month_all = $db->query("select count(*) as count from $table where $sqlwhere and order_date>=$lastmonth_tb and order_date<$month_tb and status<>3", 1, "count");
$last_month_come = $db->query("select count(*) as count from $table where $sqlwhere and order_date>=$lastmonth_tb and order_date<$month_tb and status=1", 1, "count");
$last_month_not = $last_month_all - $last_month_come;

// ???:
$tb_all = $db->query("select count(*) as count from $table where $sqlwhere and order_date>=$tb_tb and order_date<$tb_te and status<>3", 1, "count");
$tb_come = $db->query("select count(*) as count from $table where $sqlwhere and order_date>=$tb_tb and order_date<$tb_te and status=1", 1, "count");
$tb_not = $zhoubi_all - $zhoubi_come;

?>


<div style="float:left">
<table width="510" class="edit" style="margin-top:10px;margin-left:40px;">
	<tr>
		<td colspan="2" class="head">??????????</td>
	</tr>
	<tr>
		<td class="left">?????</td>
		<td class="right"><a href="/m/patient/patient.php?show=today">???: <b><?=$today_all?></b></a> &nbsp;&nbsp; <a href="/m/patient/patient.php?show=today&come=1">???: <b><?=$today_come?></b></a> &nbsp;&nbsp; <a href="/m/patient/patient.php?show=today&come=0">¦Ä??: <b><?=$today_not?></b></a></td>
	</tr>
	<tr>
		<td class="left">?????</td>
		<td class="right"><a href="/m/patient/patient.php?show=yesterday">???: <b><?=$yesterday_all?></b></a> &nbsp;&nbsp; <a href="/m/patient/patient.php?show=yesterday&come=1">???: <b><?=$yesterday_come?></b></a> &nbsp;&nbsp; <a href="/m/patient/patient.php?show=yesterday&come=0">¦Ä??: <b><?=$yesterday_not?></b></a></td>
	</tr>
	<tr>
		<td class="left">???¡ê?</td>
		<td class="right"><a href="/m/patient/patient.php?show=thismonth">???: <b><?=$this_month_all?></b></a> &nbsp;&nbsp; <a href="/m/patient/patient.php?show=thismonth&come=1">???: <b><?=$this_month_come?></b></a> &nbsp;&nbsp; <a href="/m/patient/patient.php?show=thismonth&come=0">¦Ä??: <b><?=$this_month_not?></b></a></td>
	</tr>
	<tr>
		<td class="left" style="color:silver">????</td>
		<td class="right" style="color:silver">???: <b><?=$tb_all?></b> &nbsp;&nbsp; ???: <b><?=$tb_come?></b> &nbsp;&nbsp; ¦Ä??: <b><?=$tb_not?></b></td>
	</tr>
	<tr>
		<td class="left">???¡ê?</td>
		<td class="right"><a href="/m/patient/patient.php?show=lastmonth">???: <b><?=$last_month_all?></b></a> &nbsp;&nbsp; <a href="/m/patient/patient.php?show=lastmonth&come=1">???: <b><?=$last_month_come?></b></a> &nbsp;&nbsp; <a href="/m/patient/patient.php?show=lastmonth&come=0">¦Ä??: <b><?=$last_month_not?></b></a></td>
	</tr>
</table>
<?php
$today_all = $db->query("select count(*) as count from $table where $sqlwhere and order_date>=$today_tb and order_date<$today_te", 1, "count");
if ($_GET["show"] == "sql") {
	echo $db->sql."<br>";
}
$today_come = $db->query("select count(*) as count from $table where $sqlwhere and order_date>=$today_tb and order_date<$today_te and status=3", 1, "count");
$today_not = $today_all - $today_come;

$yesterday_all = $db->query("select count(*) as count from $table where $sqlwhere and order_date>=$yesterday_tb and order_date<$today_tb", 1, "count");
$yesterday_come = $db->query("select count(*) as count from $table where $sqlwhere and order_date>=$yesterday_tb and order_date<$today_tb and status=3", 1, "count");
$yesterday_not = $yesterday_all - $yesterday_come;

$this_month_all = $db->query("select count(*) as count from $table where $sqlwhere and order_date>=$month_tb and order_date<$month_te", 1, "count");
$this_month_come = $db->query("select count(*) as count from $table where $sqlwhere and order_date>=$month_tb and order_date<$month_te and status=3", 1, "count");
$this_month_not = $this_month_all - $this_month_come;

$last_month_all = $db->query("select count(*) as count from $table where $sqlwhere and order_date>=$lastmonth_tb and order_date<$month_tb", 1, "count");
$last_month_come = $db->query("select count(*) as count from $table where $sqlwhere and order_date>=$lastmonth_tb and order_date<$month_tb and status=3", 1, "count");
$last_month_not = $last_month_all - $last_month_come;

// ???:
$tb_all = $db->query("select count(*) as count from $table where $sqlwhere and order_date>=$tb_tb and order_date<$tb_te", 1, "count");
$tb_come = $db->query("select count(*) as count from $table where $sqlwhere and order_date>=$tb_tb and order_date<$tb_te and status=3", 1, "count");
$tb_not = $zhoubi_all - $zhoubi_come;
?>
<table width="510" class="edit" style="margin-top:10px;margin-left:40px;">
	<tr>
		<td colspan="2" class="head">??¦Ä?????????</td>
	</tr>
	<tr>
		<td class="left">?????</td>
		<td class="right"><a href="/m/patient/patient.php?show=today&come=3">??: <b><?=$today_come?></b></a></td>
	</tr>
	<tr>
		<td class="left">?????</td>
		<td class="right"><a href="/m/patient/patient.php?show=yesterday&come=3">??: <b><?=$yesterday_come?></b></a></td>
	</tr>
	<tr>
		<td class="left">???¡ê?</td>
		<td class="right"><a href="/m/patient/patient.php?show=thismonth&come=3">??: <b><?=$this_month_come?></b></a></td>
	</tr>
	<tr>
		<td class="left" style="color:silver">????</td>
		<td class="right" style="color:silver">??: <b><?=$tb_come?></b></td>
	</tr>
	<tr>
		<td class="left">???¡ê?</td>
		<td class="right"><a href="/m/patient/patient.php?show=lastmonth&come=3">??: <b><?=$last_month_come?></b></a></td>
	</tr>
</table>
</div>

<?php
   $by_daoyuan_order = $db->query("select count(*) as count,author from $table where status = 1 and order_date>=$month_tb and order_date<$month_te and part_id!=4 group by author order by count desc limit 0,5");
   $by_yuyue_order = $db->query("select count(*) as count,author from $table where status != 3 and addtime>=$month_tb and addtime<$month_te and part_id!=4 group by author order by count desc limit 0,5");
   $sy_daoyuan_order = $db->query("select count(*) as count,author from $table where status = 1 and order_date>=$lastmonth_tb and order_date<$month_tb and part_id!=4 group by author order by count desc limit 0,5");
   $sy_yuyue_order = $db->query("select count(*) as count,author from $table where status != 3 and addtime>=$lastmonth_tb and addtime<$month_tb and part_id!=4 group by author order by count desc limit 0,5");
?>  
<style>
.paihangbang{margin-top:10px;padding-left:10px;float:left;width:380px;}
.paihangbang td{ height:26px;}
.paihangbang span{width:20px;height:20px;display:block;margin-left:10px;}
.paihangbang .crown{background:url("/res/img/crown.png") no-repeat; }
.paihangbang .moon{background:url("/res/img/moon.png") no-repeat; }
.paihangbang .star{background:url("/res/img/star.png") no-repeat; }
.paihangbang .sun{background:url("/res/img/sun.png") no-repeat; }
.paihangbang .nums{font-size:14px;font-weight:bold;padding-right:10px;}
</style>
<?php if($_SESSION[$cfgSessionName]["part_id"]!=5){?>
<div class="paihangbang">
  <div style="float:left">
    <table width="180" class="edit" border="0" cellpadding="0" cellspacing="0">
      <tr>
        <td colspan="3" class="head">???¦Ì??????§Ñ?</td>
      </tr>
   <?php 
      if(!is_array($by_daoyuan_order)){$by_daoyuan_order = array();}
	  
      foreach($by_daoyuan_order as $key=>$val){  
   ?>
      <tr>
        <td><?php if($key == 0){echo '<span class="crown"></span>';}elseif($key == 1){echo '<span class="sun"></span>';}elseif($key==2){echo '<span class="moon"></span>';}else{echo '<span class="star"></span>';}?></td>
        <td><?php echo $val['author'];?></td>
        <td class="nums"><?php echo $val['count'];?></td>
      </tr>
   <?php
      }
   ?>
    </table>
  </div>
  <div style="float:left; padding-left:10px;" >
    <table width="180" class="edit" border="0" cellpadding="0" cellspacing="0">
      <tr>
        <td colspan="3" class="head">?????????§Ñ?</td>
      </tr>
   <?php 
      if(!is_array($by_yuyue_order)){$by_yuyue_order = array();}

      foreach($by_yuyue_order as $key=>$val){  
   ?>
      <tr>
        <td><?php if($key == 0){echo '<span class="crown"></span>';}elseif($key == 1){echo '<span class="sun"></span>';}elseif($key==2){echo '<span class="moon"></span>';}else{echo '<span class="star"></span>';}?></td>
        <td><?php echo $val['author'];?></td>
        <td class="nums"><?php echo $val['count'];?></td>
      </tr>
   <?php
      }
   ?>
    </table>
  </div>
  <div class="clear"></div>
  <div style="float:left; margin-top:10px;">
    <table width="180" class="edit" border="0" cellpadding="0" cellspacing="0">
      <tr>
        <td colspan="3" class="head">???¦Ì??????§Ñ?</td>
      </tr>
   <?php 
      if(!is_array($sy_daoyuan_order)){$sy_daoyuan_order = array();}
      foreach($sy_daoyuan_order as $key=>$val){  
   ?>
      <tr>
        <td><?php if($key == 0){echo '<span class="crown"></span>';}elseif($key == 1){echo '<span class="sun"></span>';}elseif($key==2){echo '<span class="moon"></span>';}else{echo '<span class="star"></span>';}?></td>
        <td><?php echo $val['author'];?></td>
        <td class="nums"><?php echo $val['count'];?></td>
      </tr>
   <?php
      }
   ?>
    </table>
  </div>
  <div style="float:left; padding-left:10px; margin-top:10px;" >
    <table width="180" class="edit" border="0" cellpadding="0" cellspacing="0">
      <tr>
        <td colspan="3" class="head">?????????§Ñ?</td>
      </tr>
   <?php 
      if(!is_array($sy_yuyue_order)){$sy_yuyue_order = array();}
      foreach($sy_yuyue_order as $key=>$val){  
   ?>
      <tr>
        <td><?php if($key == 0){echo '<span class="crown"></span>';}elseif($key == 1){echo '<span class="sun"></span>';}elseif($key==2){echo '<span class="moon"></span>';}else{echo '<span class="star"></span>';}?></td>
        <td><?php echo $val['author'];?></td>
        <td class="nums"><?php echo $val['count'];?></td>
      </tr>
   <?php
      }
   ?>
    </table>
  </div>
</div>
<?php }?>
<div class="clear"></div>
<?php
$ar=$db->query("select * from media where hospital_id='$user_hospital_id' order by id desc","id","name");
foreach($ar as $key=>$value)
{
?>
<!-- ???????????????? -->
<?php if ($username == "admin" || $debug_mode || in_array($uinfo["part_id"], array(1,9)) || ($uinfo["part_admin"] && in_array(2,$manage_parts)) ) { ?>
<?php

$table = "patient_".$user_hospital_id;

$web_1 = $db->query("select count(*) as count from $table where media_from='{$value}' and addtime>=$today_tb and addtime<$today_te and status<>3", 1, "count");
$web_2 = $db->query("select count(*) as count from $table where media_from='{$value}' and addtime>=$yesterday_tb and addtime<$today_tb and status<>3", 1, "count");
$web_3 = $db->query("select count(*) as count from $table where media_from='{$value}' and addtime>=$month_tb and addtime<$month_te and status<>3", 1, "count");

$web_4 = $db->query("select count(*) as count from $table where media_from='{$value}' and status=1 and order_date>=$today_tb and order_date<$today_te  and status<>3", 1, "count");
$web_5 = $db->query("select count(*) as count from $table where media_from='{$value}' and status=1 and order_date>=$yesterday_tb and order_date<$today_tb  and status<>3", 1, "count");
$web_6 = $db->query("select count(*) as count from $table where media_from='{$value}' and status=1 and order_date>=$month_tb and order_date<$month_te  and status<>3", 1, "count");

$web_7 = $db->query("select count(*) as count from $table where media_from='{$value}' and order_date>=$today_tb and order_date<$today_te  and status<>3", 1, "count");
$web_8 = $db->query("select count(*) as count from $table where media_from='{$value}' and order_date>=$yesterday_tb and order_date<$today_tb and status<>3", 1, "count");
$web_9 = $db->query("select count(*) as count from $table where media_from='{$value}' and order_date>=$month_tb and order_date<$month_te and status<>3", 1, "count");

// ???
$web_tb1 = $db->query("select count(*) as count from $table where media_from='{$value}' and addtime>=$tb_tb and addtime<$tb_te and status<>3", 1, "count");
$web_tb2 = $db->query("select count(*) as count from $table where media_from='{$value}' and order_date>=$tb_tb and order_date<$tb_te and status<>3", 1, "count");
$web_tb3 = $db->query("select count(*) as count from $table where media_from='{$value}' and order_date>=$tb_tb and order_date<$tb_te and status=1", 1, "count");

?>
<div style="float:left; width:250px; padding-left:30px;">
<table width="250" class="edit" style="margin-top:10px;">
	<tr>
		<td colspan="2" class="head"><?php echo $value?></td>
	</tr>
	<tr>
		<td class="left" style="width:20%">?????</td>
		<td class="right">
			<span title="??????????????">?:<a href="/m/patient/patient.php?show=today&time_type=addtime&part_id=&media=<?php echo $value?>">&nbsp;<b><?php echo $web_1; ?></b>&nbsp;</a></span>
			<span title="??????????????">???:<a href="/m/patient/patient.php?show=today&part_id=&media=<?php echo $value?>">&nbsp;<b><?php echo $web_7; ?></b>&nbsp;</a></span>
			<span title="???????????????">??:<a href="/m/patient/patient.php?show=today&part_id=&media=<?php echo $value?>&come=1">&nbsp;<b><?php echo $web_4; ?></b>&nbsp;</a></span>
		</td>
	</tr>
	<tr>
		<td class="left">?????</td>
		<td class="right">
			<span title="??????????????">?:<a href="/m/patient/patient.php?show=yesterday&time_type=addtime&part_id=&media=<?php echo $value?>">&nbsp;<b><?php echo $web_2; ?></b>&nbsp;</a></span>
			<span title="??????????????">???:<a href="/m/patient/patient.php?show=yesterday&part_id=&media=<?php echo $value?>">&nbsp;<b><?php echo $web_8; ?></b>&nbsp;</a></span>
			<span title="???????????????">??:<a href="/m/patient/patient.php?show=yesterday&part_id=&media=<?php echo $value?>&come=1">&nbsp;<b><?php echo $web_5; ?></b>&nbsp;</a></span>
		</td>
	</tr>
	<tr>
		<td class="left">???¡ê?</td>
		<td class="right">
			<span title="??????????????">?:<a href="/m/patient/patient.php?show=thismonth&time_type=addtime&part_id=&media=<?php echo $value?>">&nbsp;<b><?php echo $web_3; ?></b>&nbsp;</a></span>
			<span title="??????????????">???:<a href="/m/patient/patient.php?show=thismonth&part_id=&media=<?php echo $value?>">&nbsp;<b><?php echo $web_9; ?></b>&nbsp;</a></span>
			<span title="???????????????">??:<a href="/m/patient/patient.php?show=thismonth&part_id=&media=<?php echo $value?>&come=1">&nbsp;<b><?php echo $web_6; ?></b>&nbsp;</a></span>
		</td>
	</tr>
	<tr>
		<td class="left" style="color:silver">????</td>
		<td class="right" style="color:silver">
			?:<b>&nbsp;<?=$web_tb1?>&nbsp;</b>
			???:<b>&nbsp;<?=$web_tb2?>&nbsp;</b>
			??:<b>&nbsp;<?=$web_tb3?>&nbsp;</b>
		</td>
	</tr>
</table>
</div>



<?php 
}
} ?>



	<!--?¿B????	start-->
	<div class="seeAll" style="width: 95%;;height: auto;position: absolute;top: 45px;left: 2.5%;border: 4px solid #FFCFB9;background-color: #FFF;display: none;padding-bottom: 20em;">
		<button class="closeAll" style="position: absolute;left: -2px;top: -22px;">X</button>
        <h3>???????????-?¿B????</h3>

		<table width="510" class="edit" style="margin-top:10px;margin-left:40px;">
			<tr>
				<td colspan="2" class="head">??????????</td>
			</tr>
			<tr>
				<td class="left">?????</td>
				<td class="right">
					<a href="/m/count/all_hospital_stats.php?table_type=miaofang&show=today" target="_blank">???: <b><?=$todayAllContent?></b></a>&nbsp;&nbsp; 
					<a href="/m/count/all_hospital_stats.php?table_type=miaofang&show=today&come=1" target="_blank">???: <b><?=$todayAllCome;  ?></b></a>&nbsp;&nbsp;
					<a href="/m/count/all_hospital_stats.php?table_type=miaofang&show=today&come=0" target="_blank">¦Ä??: <b><?=$todayAllNot?></b></a>
				</td>
			</tr>
			<tr>
				<td class="left">?????</td>
				<td class="right">
					<a href="/m/count/all_hospital_stats.php?table_type=miaofang&show=yesterday" target="_blank">???: <b><?=$yesterdayAllContent?></b></a> &nbsp;&nbsp;
					<a href="/m/count/all_hospital_stats.php?table_type=miaofang&show=yesterday&come=1" target="_blank">???: <b><?=$yesterdayAllCome?></b></a> &nbsp;&nbsp; 
					<a href="/m/count/all_hospital_stats.php?table_type=miaofang&show=yesterday&come=0" target="_blank">¦Ä??: <b><?=$yesterdayAllNot?></b></a></td>
			</tr>
			<tr>
				<td class="left">???¡ê?</td>
				<td class="right">
					<a href="/m/count/all_hospital_stats.php?table_type=miaofang&show=thismonth" target="_blank">???: <b><?=$thismonthAllContent?></b></a> &nbsp;&nbsp; 
					<a href="/m/count/all_hospital_stats.php?table_type=miaofang&show=thismonth&come=1" target="_blank">???: <b><?=$thismonthAllCome?></b></a> &nbsp;&nbsp; 
					<a href="/m/count/all_hospital_stats.php?table_type=miaofang&show=thismonth&come=0" target="_blank">¦Ä??: <b><?=$thismonthAllNot?></b></a>
				</td>
			</tr>
			<tr>
				<td class="left" style="color:silver">????</td>
				<td class="right" style="color:silver">???: 
					<b><?=$tbAllContent?></b> &nbsp;&nbsp; ???: <b><?=$tbAllCome?></b> &nbsp;&nbsp; ¦Ä??: <b><?=$tbAllNot?></b></td>
			</tr>
			<tr>
				<td class="left">???¡ê?</td>
				<td class="right">
					<a href="/m/count/all_hospital_stats.php?table_type=miaofang&show=lastmonth" target="_blank">???: <b><?=$lastmonthAllContent?></b></a> &nbsp;&nbsp; 
					<a href="/m/count/all_hospital_stats.php?table_type=miaofang&show=lastmonth&come=1" target="_blank">???: <b><?=$lastmonthAllCome?></b></a> &nbsp;&nbsp; 
					<a href="/m/count/all_hospital_stats.php?table_type=miaofang&show=lastmonth&come=0" target="_blank">¦Ä??: <b><?=$lastmonthAllNot?></b></a>
				</td>
			</tr>
		</table>

		<?php
		$ar=$db->query("select * from media where hospital_id='$user_hospital_id' order by id desc","id","name");
		foreach($ar as $key=>$value)
		{
			?>
			<!-- ???????????????? -->
			<?php if ($username == "admin" || $debug_mode || in_array($uinfo["part_id"], array(1,9)) || ($uinfo["part_admin"] && in_array(2,$manage_parts)) ) { ?>
			<?php
			$table = "patient_".$user_hospital_id;
			//?????????????
			$newW_1 = 0;
			$newW_2 = 0;
			$newW_3 = 0;
			$newW_4 = 0;
			$newW_5 = 0;
			$newW_6 = 0;
			$newW_7 = 0;
			$newW_8 = 0;
			$newW_9 = 0;
			//???
			$new_tb1 = 0;
			$new_tb2 = 0;
			$new_tb3 = 0;
			foreach ($hospital_list as $i=>$V) {
//			    if($i < 22){
                if(in_array($i,array(12,13,14,15,16,17,19,20))){
                    $totalTable = "patient_".$i;//?????????

                    $w_1 = $db->query("select count(*) as count from $totalTable where media_from='{$value}' and addtime>=$today_tb and addtime<$today_te and status<>3", 1, "count");
                    $newW_1 += $w_1;
                    $w_2 = $db->query("select count(*) as count from $totalTable where media_from='{$value}' and addtime>=$yesterday_tb and addtime<$today_tb and status<>3", 1, "count");
                    $newW_2 += $w_2;
                    $w_3 = $db->query("select count(*) as count from $totalTable where media_from='{$value}' and addtime>=$month_tb and addtime<$month_te and status<>3", 1, "count");
                    $newW_3 += $w_3;

                    $w_4 = $db->query("select count(*) as count from $totalTable where media_from='{$value}' and status=1 and order_date>=$today_tb and order_date<$today_te  and status<>3", 1, "count");
                    $newW_4 += $w_4;
                    $w_5 = $db->query("select count(*) as count from $totalTable where media_from='{$value}' and status=1 and order_date>=$yesterday_tb and order_date<$today_tb  and status<>3", 1, "count");
                    $newW_5 += $w_5;
                    $w_6 = $db->query("select count(*) as count from $totalTable where media_from='{$value}' and status=1 and order_date>=$month_tb and order_date<$month_te  and status<>3", 1, "count");
                    $newW_6 += $w_6;

                    $w_7 = $db->query("select count(*) as count from $totalTable where media_from='{$value}' and order_date>=$today_tb and order_date<$today_te  and status<>3", 1, "count");
                    $newW_7 += $w_7;
                    $w_8 = $db->query("select count(*) as count from $totalTable where media_from='{$value}' and order_date>=$yesterday_tb and order_date<$today_tb and status<>3", 1, "count");
                    $newW_8 += $w_8;
                    $w_9 = $db->query("select count(*) as count from $totalTable where media_from='{$value}' and order_date>=$month_tb and order_date<$month_te and status<>3", 1, "count");
                    $newW_9 += $w_9;

                    $w_tb1 = $db->query("select count(*) as count from $totalTable where media_from='{$value}' and addtime>=$tb_tb and addtime<$tb_te and status<>3", 1, "count");
                    $new_tb1 += $w_tb1;
                    $w_tb2 = $db->query("select count(*) as count from $totalTable where media_from='{$value}' and order_date>=$tb_tb and order_date<$tb_te and status<>3", 1, "count");
                    $new_tb2 += $w_tb2;
                    $w_tb3 = $db->query("select count(*) as count from $totalTable where media_from='{$value}' and order_date>=$tb_tb and order_date<$tb_te and status=1", 1, "count");
                    $new_tb3 += $w_tb3;
                }

			}

			?>
			<div style="float:left; width:250px; padding-left:30px;">

				<table width="250" class="edit" style="margin-top:10px;">
					<tr>
						<td colspan="2" class="head"><?php echo $value?></td>
					</tr>
					<tr>
						<td class="left" style="width:20%">?????</td>
						<td class="right">
							<span title="??????????????">?:
								<a href="/m/count/all_hospital_stats.php?table_type=miaofang&show=today&time_type=addtime&media=<?php echo $value?>" target="_blank">&nbsp;
									<b><?php echo $newW_1; ?></b>&nbsp;
								</a>
							</span>
							<span title="??????????????">???:<a href="/m/count/all_hospital_stats.php?table_type=miaofang&show=today&media=<?php echo $value?>" target="_blank">&nbsp;<b><?php echo $newW_7; ?></b>&nbsp;</a></span>
							<span title="???????????????">??:<a href="/m/count/all_hospital_stats.php?table_type=miaofang&show=today&media=<?php echo $value?>&come=1" target="_blank">&nbsp;<b><?php echo $newW_4; ?></b>&nbsp;</a></span>
						</td>
					</tr>
					<tr>
						<td class="left">?????</td>
						<td class="right">
							<span title="??????????????">?:<a href="/m/count/all_hospital_stats.php?table_type=miaofang&show=yesterday&time_type=addtime&media=<?php echo $value?>" target="_blank">&nbsp;<b><?php echo $newW_2; ?></b>&nbsp;</a></span>
							<span title="??????????????">???:<a href="/m/count/all_hospital_stats.php?table_type=miaofang&show=yesterday&media=<?php echo $value?>" target="_blank">&nbsp;<b><?php echo $newW_8; ?></b>&nbsp;</a></span>
							<span title="???????????????">??:<a href="/m/count/all_hospital_stats.php?table_type=miaofang&show=yesterday&media=<?php echo $value?>&come=1" target="_blank">&nbsp;<b><?php echo $newW_5; ?></b>&nbsp;</a></span>
						</td>
					</tr>
					<tr>
						<td class="left">???¡ê?</td>
						<td class="right">
							<span title="??????????????">?:<a href="/m/count/all_hospital_stats.php?table_type=miaofang&show=thismonth&time_type=addtime&media=<?php echo $value?>" target="_blank">&nbsp;<b><?php echo $newW_3; ?></b>&nbsp;</a></span>
							<span title="??????????????">???:<a href="/m/count/all_hospital_stats.php?table_type=miaofang&show=thismonth&media=<?php echo $value?>" target="_blank">&nbsp;<b><?php echo $newW_9; ?></b>&nbsp;</a></span>
							<span title="???????????????">??:<a href="/m/count/all_hospital_stats.php?table_type=miaofang&show=thismonth&media=<?php echo $value?>&come=1" target="_blank">&nbsp;<b><?php echo $newW_6; ?></b>&nbsp;</a></span>
						</td>
					</tr>
					<tr>
						<td class="left" style="color:silver">????</td>
						<td class="right" style="color:silver">
							?:<b>&nbsp;<?=$new_tb1?>&nbsp;</b>
							???:<b>&nbsp;<?=$new_tb2?>&nbsp;</b>
							??:<b>&nbsp;<?=$new_tb3?>&nbsp;</b>
						</td>
					</tr>
				</table>
			</div>
			<?php
		}
		} ?>
	</div>
<!--?¿B????	end-->


<!--??????	start-->
    <div class="seeAll2" style="width: 95%;;height: auto;position: absolute;top: 45px;left: 2.5%;border: 4px solid #FFCFB9;background-color: #FFF;display: none;padding-bottom: 20em;">
        <button class="closeAll2" style="position: absolute;left: -2px;top: -22px;">X</button>
        <h3>???????????-??????</h3>

        <table width="510" class="edit" style="margin-top:10px;margin-left:40px;">
            <tr>
                <td colspan="2" class="head">??????????</td>
            </tr>
            <tr>
                <td class="left">?????</td>
                <td class="right">
                    <a href="/m/count/all_hospital_stats.php?table_type=douyimei&show=today" target="_blank">???: <b><?=$todayAllContent_D?></b></a>&nbsp;&nbsp;
                    <a href="/m/count/all_hospital_stats.php?table_type=douyimei&show=today&come=1" target="_blank">???: <b><?=$todayAllCome_D;  ?></b></a>&nbsp;&nbsp;
                    <a href="/m/count/all_hospital_stats.php?table_type=douyimei&show=today&come=0" target="_blank">¦Ä??: <b><?=$todayAllNot_D?></b></a>
                </td>
            </tr>
            <tr>
                <td class="left">?????</td>
                <td class="right">
                    <a href="/m/count/all_hospital_stats.php?table_type=douyimei&show=yesterday" target="_blank">???: <b><?=$yesterdayAllContent_D?></b></a> &nbsp;&nbsp;
                    <a href="/m/count/all_hospital_stats.php?table_type=douyimei&show=yesterday&come=1" target="_blank">???: <b><?=$yesterdayAllCome_D?></b></a> &nbsp;&nbsp;
                    <a href="/m/count/all_hospital_stats.php?table_type=douyimei&show=yesterday&come=0" target="_blank">¦Ä??: <b><?=$yesterdayAllNot_D?></b></a></td>
            </tr>
            <tr>
                <td class="left">???¡ê?</td>
                <td class="right">
                    <a href="/m/count/all_hospital_stats.php?table_type=douyimei&show=thismonth" target="_blank">???: <b><?=$thismonthAllContent_D?></b></a> &nbsp;&nbsp;
                    <a href="/m/count/all_hospital_stats.php?table_type=douyimei&show=thismonth&come=1" target="_blank">???: <b><?=$thismonthAllCome_D?></b></a> &nbsp;&nbsp;
                    <a href="/m/count/all_hospital_stats.php?table_type=douyimei&show=thismonth&come=0" target="_blank">¦Ä??: <b><?=$thismonthAllNot_D?></b></a>
                </td>
            </tr>
            <tr>
                <td class="left" style="color:silver">????</td>
                <td class="right" style="color:silver">???:
                    <b><?=$tbAllContent_D?></b> &nbsp;&nbsp; ???: <b><?=$tbAllCome_D?></b> &nbsp;&nbsp; ¦Ä??: <b><?=$tbAllNot_D?></b></td>
            </tr>
            <tr>
                <td class="left">???¡ê?</td>
                <td class="right">
                    <a href="/m/count/all_hospital_stats.php?table_type=douyimei&show=lastmonth" target="_blank">???: <b><?=$lastmonthAllContent_D?></b></a> &nbsp;&nbsp;
                    <a href="/m/count/all_hospital_stats.php?table_type=douyimei&show=lastmonth&come=1" target="_blank">???: <b><?=$lastmonthAllCome_D?></b></a> &nbsp;&nbsp;
                    <a href="/m/count/all_hospital_stats.php?table_type=douyimei&show=lastmonth&come=0" target="_blank">¦Ä??: <b><?=$lastmonthAllNot_D?></b></a>
                </td>
            </tr>
        </table>

        <?php
        $ar=$db->query("select * from media where hospital_id='$user_hospital_id' order by id desc","id","name");
        foreach($ar as $key=>$value)
        {
            ?>
            <!-- ???????????????? -->
            <?php if ($username == "admin" || $debug_mode || in_array($uinfo["part_id"], array(1,9)) || ($uinfo["part_admin"] && in_array(2,$manage_parts)) ) { ?>
            <?php
            $table = "patient_".$user_hospital_id;
            //?????????????
            $newW_1_D = 0;
            $newW_2_D = 0;
            $newW_3_D = 0;
            $newW_4_D = 0;
            $newW_5_D = 0;
            $newW_6_D = 0;
            $newW_7_D = 0;
            $newW_8_D = 0;
            $newW_9_D = 0;
            //???
            $new_tb1_D = 0;
            $new_tb2_D = 0;
            $new_tb3_D = 0;
            foreach ($hospital_list as $i=>$V) {
//                if($i >= 22){
                if(in_array($i,array(22,23,24,25))){
                    $totalTable = "patient_".$i;//?????????

                    $w_1_D = $db->query("select count(*) as count from $totalTable where media_from='{$value}' and addtime>=$today_tb and addtime<$today_te and status<>3", 1, "count");
                    $newW_1_D += $w_1_D;
                    $w_2_D = $db->query("select count(*) as count from $totalTable where media_from='{$value}' and addtime>=$yesterday_tb and addtime<$today_tb and status<>3", 1, "count");
                    $newW_2_D += $w_2_D;
                    $w_3_D = $db->query("select count(*) as count from $totalTable where media_from='{$value}' and addtime>=$month_tb and addtime<$month_te and status<>3", 1, "count");
                    $newW_3_D += $w_3_D;

                    $w_4_D = $db->query("select count(*) as count from $totalTable where media_from='{$value}' and status=1 and order_date>=$today_tb and order_date<$today_te  and status<>3", 1, "count");
                    $newW_4_D += $w_4_D;
                    $w_5_D = $db->query("select count(*) as count from $totalTable where media_from='{$value}' and status=1 and order_date>=$yesterday_tb and order_date<$today_tb  and status<>3", 1, "count");
                    $newW_5_D += $w_5_D;
                    $w_6_D = $db->query("select count(*) as count from $totalTable where media_from='{$value}' and status=1 and order_date>=$month_tb and order_date<$month_te  and status<>3", 1, "count");
                    $newW_6_D += $w_6_D;

                    $w_7_D = $db->query("select count(*) as count from $totalTable where media_from='{$value}' and order_date>=$today_tb and order_date<$today_te  and status<>3", 1, "count");
                    $newW_7_D += $w_7_D;
                    $w_8_D = $db->query("select count(*) as count from $totalTable where media_from='{$value}' and order_date>=$yesterday_tb and order_date<$today_tb and status<>3", 1, "count");
                    $newW_8_D += $w_8_D;
                    $w_9_D = $db->query("select count(*) as count from $totalTable where media_from='{$value}' and order_date>=$month_tb and order_date<$month_te and status<>3", 1, "count");
                    $newW_9_D += $w_9_D;

                    $w_tb1_D = $db->query("select count(*) as count from $totalTable where media_from='{$value}' and addtime>=$tb_tb and addtime<$tb_te and status<>3", 1, "count");
                    $new_tb1_D += $w_tb1_D;
                    $w_tb2_D = $db->query("select count(*) as count from $totalTable where media_from='{$value}' and order_date>=$tb_tb and order_date<$tb_te and status<>3", 1, "count");
                    $new_tb2_D += $w_tb2_D;
                    $w_tb3_D = $db->query("select count(*) as count from $totalTable where media_from='{$value}' and order_date>=$tb_tb and order_date<$tb_te and status=1", 1, "count");
                    $new_tb3_D += $w_tb3_D;
                }

            }

            ?>
            <div style="float:left; width:250px; padding-left:30px;">

                <table width="250" class="edit" style="margin-top:10px;">
                    <tr>
                        <td colspan="2" class="head"><?php echo $value?></td>
                    </tr>
                    <tr>
                        <td class="left" style="width:20%">?????</td>
                        <td class="right">
							<span title="??????????????">?:
								<a href="/m/count/all_hospital_stats.php?table_type=douyimei&show=today&time_type=addtime&media=<?php echo $value?>" target="_blank">&nbsp;
									<b><?php echo $newW_1_D; ?></b>&nbsp;
								</a>
							</span>
                            <span title="??????????????">???:<a href="/m/count/all_hospital_stats.php?table_type=douyimei&show=today&media=<?php echo $value?>" target="_blank">&nbsp;<b><?php echo $newW_7_D; ?></b>&nbsp;</a></span>
                            <span title="???????????????">??:<a href="/m/count/all_hospital_stats.php?table_type=douyimei&show=today&media=<?php echo $value?>&come=1" target="_blank">&nbsp;<b><?php echo $newW_4_D; ?></b>&nbsp;</a></span>
                        </td>
                    </tr>
                    <tr>
                        <td class="left">?????</td>
                        <td class="right">
                            <span title="??????????????">?:<a href="/m/count/all_hospital_stats.php?table_type=douyimei&show=yesterday&time_type=addtime&media=<?php echo $value?>" target="_blank">&nbsp;<b><?php echo $newW_2_D; ?></b>&nbsp;</a></span>
                            <span title="??????????????">???:<a href="/m/count/all_hospital_stats.php?table_type=douyimei&show=yesterday&media=<?php echo $value?>" target="_blank">&nbsp;<b><?php echo $newW_8_D; ?></b>&nbsp;</a></span>
                            <span title="???????????????">??:<a href="/m/count/all_hospital_stats.php?table_type=douyimei&show=yesterday&media=<?php echo $value?>&come=1" target="_blank">&nbsp;<b><?php echo $newW_5_D; ?></b>&nbsp;</a></span>
                        </td>
                    </tr>
                    <tr>
                        <td class="left">???¡ê?</td>
                        <td class="right">
                            <span title="??????????????">?:<a href="/m/count/all_hospital_stats.php?table_type=douyimei&show=thismonth&time_type=addtime&media=<?php echo $value?>" target="_blank">&nbsp;<b><?php echo $newW_3_D; ?></b>&nbsp;</a></span>
                            <span title="??????????????">???:<a href="/m/count/all_hospital_stats.php?table_type=douyimei&show=thismonth&media=<?php echo $value?>" target="_blank">&nbsp;<b><?php echo $newW_9_D; ?></b>&nbsp;</a></span>
                            <span title="???????????????">??:<a href="/m/count/all_hospital_stats.php?table_type=douyimei&show=thismonth&media=<?php echo $value?>&come=1" target="_blank">&nbsp;<b><?php echo $newW_6_D; ?></b>&nbsp;</a></span>
                        </td>
                    </tr>
                    <tr>
                        <td class="left" style="color:silver">????</td>
                        <td class="right" style="color:silver">
                            ?:<b>&nbsp;<?=$new_tb1_D?>&nbsp;</b>
                            ???:<b>&nbsp;<?=$new_tb2_D?>&nbsp;</b>
                            ??:<b>&nbsp;<?=$new_tb3_D?>&nbsp;</b>
                        </td>
                    </tr>
                </table>
            </div>
            <?php
        }
        } ?>
    </div>
    <!--??????	end-->

<div class="clear"></div>

<!-- ??? -->
<div style="padding-top:40px; padding-left:50px;">
	* <b>???</b>??????¦Ì????????????íà??????3??28???????????2??1????2??28?????????????<br>
</div>

<?php } ?>

</body>

<script>
$(".closeAll").click(function(){
	$(".seeAll").css("display","none");
});
$(".AllMsg").click(function(){
    $(".seeAll").css("display","block");
});
$(".closeAll2").click(function(){
    $(".seeAll2").css("display","none");
});
$(".AllMsg2").click(function(){
    $(".seeAll2").css("display","block");
})
</script>

</html>