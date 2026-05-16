<?php

/*

// - 功能说明 : get_online.php

// - 创建作者 : 小陈 

// - 创建时间 : 2013-05-26 11:53

*/

ob_start();

require "../core/core.php";

require "../core/class.fastjson.php";

ob_end_clean();



ob_start();

error_reporting(0);

set_time_limit(5);



$out = array();

$out["status"] = "bad";



$timestamp = time();



// 保存当前用户本次在线的记录:

$db->query("update sys_admin set lastactiontime='$timestamp',online=1 where name='$username' limit 1");

// 更新用户在线状态:

$db->query("update sys_admin set online=if($timestamp-lastactiontime>60, '0', '1') where online=1");

// 读取在线用户:

$aUser = array();

$aUser[intval($uid)] = array("name"=>$username, "realname"=>$realname, "isowner"=>1);





$sqlwhere = '';

if ($user_hospital_id > 0) {

	$sqlwhere = "and concat(',',hospitals,',') like '%,{$user_hospital_id},%'";

}



/*

// 人员读取条件:

$hd_s = array();

foreach ($hospital_ids as $v) {

	$hd_s[] = ','.$v.',';

}

$hd = implode("", $hd_s);

$sqlwhere = " and '$hd' like concat('%,',replace(hospitals,',',',%,'),',%')";

*/





$data = $db->query("select id,name,realname,hospitals from sys_admin where name!='$username' and online='1' $sqlwhere order by thislogin desc limit 10");



foreach ($data as $line) {

	// 2013-05-09 16:55 修改：读取同一门店的在线用户

	$aUser[$line["id"]] = array("name"=>$line["name"], "realname"=>$line["realname"], "isowner"=>0);

}

$out["online_list"] = $aUser;





// 读取通知:

$cur_pid = intval($uinfo["part_id"]);

$cur_is_m = $uinfo["part_manage"] ? 1 : 0;

$time = time();

$data = $db->query("select * from sys_notice where (reader_type='all' or (reader_type='part' and concat(',',part_ids,',') like '%,{$cur_pid},%') or (reader_type='user' and concat(',',uids,',') like '%,{$uid},%') or (reader_type='manager' and 1='$cur_is_m')) and isshow=1 and (begintime=0 or (begintime>0 and begintime<=$time)) and (endtime=0 or (endtime>0 and endtime>=$time)) and concat(',', read_uids, ',') not like '%,{$uid},%' order by addtime desc limit 4");



$sql2 = $db->sql;



foreach ($data as $line) {

	$messid = $line["id"];

	$aMessInfo[$messid] = array(

		"type"=>"notice",

		"url"=>"/m/sys/notice.php?id=".$messid,

		"title"=>cut(("通知: ".my_replace(text_show($line["title"]))), 22, ".."),

	);

}





// 读取消息:

$data = $db->query("select * from sys_message where to_uid='$uid' and readtime=0 order by addtime desc limit 8");



foreach ($data as $line) {

	$messid = $line["id"];

	$aMessInfo[$messid] = array(

		"type"=>"message",

		"url"=>"/m/sys/message.php?id=".$messid,

		"title"=>cut(($line["from_realname"].": ".my_replace(text_show($line["content"]))), 22, ".."),

	);

	//$db->query("update sys_message set readtime=$timestamp where id=$messid limit 1");

}

$out["online_notice"] = $aMessInfo;



$out["status"] = "ok";



ob_end_clean();





// --------- 输出 ---------

echo 'var _online_data = '.FastJSON::convert($out)."\n";

echo 'get_online_do(_online_data);'."\n";



//echo '//'.$sql2;





function my_replace($s) {

	$s = str_replace("'", "", $s);

	$s = str_replace('"', "", $s);

	$s = str_replace("\r", "", $s);

	$s = str_replace("\n", " ", $s);

	return $s;

}

?>