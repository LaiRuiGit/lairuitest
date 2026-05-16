<?php

/*
// 说明: 检查并更新表字段
// 作者: 小陈 
// 时间: 2010-10-21 14:45
*/

require "../../core/core.php";


// 从一个创建表的语句中解读字段

function parse_fields($s) {

	$list = explode("\n", $s);

	$out = array();

	foreach ($list as $k) {

		$k = trim($k);

		if (substr($k, 0, 1) == "`") {

			$fname = ltrim($k, "`");

			list($sa, $sb) = explode(" ", $fname, 2);

			$sa = rtrim($sa, "`");

			$out[$sa] = rtrim(trim($k), ',');

		}

	}



	return $out;

}



?>

<html>

<head>

<title>更新表结构</title>

<meta charset="gbk" />

<link href="/res/base.css" rel="stylesheet" type="text/css">

</head>



<body style="padding:30px 50px;">



<?php

if ($_GET["op"] == "update_table") {

	include_once ROOT."core/function.create_table.php";



	// 读取需更新的每家门店:

	$hids = $db->query("select id from hospital", "", "id");



	// 要处理的表:

	$table_names = array();

	$table_names[] = "patient";

	foreach ($hids as $hid) {

		$table_names[] = "patient_".$hid;

	}



	// 处理:

	foreach ($table_names as $table_name) {

		$cs = $db_tables["patient"];



		if (!table_exists($table_name, $db->dblink)) {

			//$db->query(str_replace("{hid}", $hid, $cs));

			//echo "创建表 ".$table_name." <br>";

		} else {

			$fields = parse_fields($cs);

			foreach ($fields as $f => $fs) {

				if (!field_exists($f, $table_name, $db->dblink)) {

					$tm = array_keys($fields);

					$tm2 = array_search($f, $tm);

					if ($tm2 == 0) {

						$pos = " first";

					} else {

						$pos = " after `".$tm[$tm2-1]."`";

					}

					$sql = "alter table `".$table_name."` add ".$fs.$pos;

					$db->query($sql);

					echo "表 ".$table_name." 添加字段 ".$f."<br>";

				}

			}

		}

	}



	echo "<br>全部完成";

	exit;

}

?>

<form method="GET">
	<input type="submit" class="buttonb" value="点击更新">
	<input type="hidden" name="op" value="update_table">
</form>

</body>
</html>