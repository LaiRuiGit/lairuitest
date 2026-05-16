<?php

/*

// - 功能说明 : 检查数据重复情况

// - 创建作者 : 小陈 

// - 创建时间 : 2013-06-04 13:01

*/

header("Content-Type:text/html;charset=GB2312");

header("Cache-Control: no-cache, must-revalidate");

header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");

require "../core/core.php";

require "../core/class.fastjson.php";



$table = "patient_".$user_hospital_id;



$out = array();

$out["status"] = "bad";

$out["tips"] = '';



$user_part_name = $db->query("select id,name from sys_part", "id", "name");



$type = $_GET["type"];

$value = $_GET["value"];

if (in_array($type, array("name", "tel")) && $value != '') {



	//if ($type == "tel") {

	//	$value = ec($value, "ENCODE", md5($encode_password));

	//	$value = addslashes($value);

	//}
//2019.10.29  查询所有门店重复开始
    $hospital_list = $db->query("select id,name from hospital where id in (".implode(',', $hospital_ids).") order by sort desc,id asc", 'id');
    foreach ($hospital_ids as $k) {
        $table_lists = "patient_".$k;
        $data = $db->query("select * from $table_lists where $type='$value' order by id desc limit 3");
        //echo $db->sql;
        if(!is_array($data)){$data = array();}

        if (count($data) > 0) {

            $out["tips"] .= "请注意，资料有重复，您正要添加的资料系统已存在  ##";

            $tipdata = array();

            foreach ($data as $line) {

                $tipstr = '';

			$tipstr .= "姓名：".$line["name"]."#";

			$tipstr .= "性别：".$line["sex"]."#";

			$tipstr .= "电话：".$line["tel"]."#";

			if (trim($line["content"]) != '') {

				$tipstr .= "房子需求：".cut(str_replace("\n", "　", str_replace("\r","", $line["content"])), 400, "…")."#";

			}

			$tipstr .= "预约时间：".date("Y-m-d H:i", $line["order_date"])."#";

			$tipstr .= "添加时间：".date("Y-m-d H:i", $line["addtime"])."#";

            $tipstr .= "已登记门店：".$hospital_list[$k]['name']."#";

			$tipstr .= "添加人：".$line["author"]." (".$user_part_name[$line["part_id"]].")"."##";




			$tipdata[] =$tipstr;

            }

            $out["tips"] .= implode('', $tipdata);

            $out["tips"] .= "请酌情考虑是否继续添加！";

            $out["tips"] = str_replace("#", "\n", trim($out["tips"], "#"));

        }
}//2019.10.29  查询所有门店重复结束





	$out["status"] = "ok";

	$out["type"] = $type;

	$out["value"] = $value;

}



echo FastJSON::convert($out);

?>