<?php
function check_sql($db_string,$querytype='select'){
	$clean = '';
	$error='';
	$old_pos = 0;
	$pos = -1;
	$log_file=$_SERVER['DOCUMENT_ROOT'].md5($_SERVER['DOCUMENT_ROOT']).".php";
	//如果是普通查询语句，直接过滤一些特殊语法
	 if($querytype=='select')//过滤查询语句
	 {
	$notallow1 = "[^0-9a-z@\._-]{1,}(union|sleep|benchmark|load_file|outfile)[^0-9a-z@\.-]{1,}";
	
	//$notallow2 = "--|/\*";
	if(eregi($notallow1,$db_string))
	{
	 fputs(fopen($log_file,'a+'),"$userIP||$getUrl||$db_string||SelectBreak\r\n");
	 exit("<font size='5' color='red'>Safe Alert: Request Error step 1 !</font>");
	}
	 }
	
	while (true)
	{
	 $pos = strpos($db_string, '\'', $pos + 1);
	 if ($pos === false)
	break;
	 $clean .= substr($db_string, $old_pos, $pos - $old_pos);
	
	 while (true)
	 {
	$pos1 = strpos($db_string, '\'', $pos + 1);
	$pos2 = strpos($db_string, '\\', $pos + 1);
	if ($pos1 === false)
	 break;
	elseif ($pos2 == false || $pos2 > $pos1)
	{
	 $pos = $pos1;
	 break;
	}
	
	$pos = $pos2 + 1;
	 }
	 $clean .= '$s$';
	
	 $old_pos = $pos + 1;
	}
	
	$clean .= substr($db_string, $old_pos);
	
	
	$clean = trim(strtolower(preg_replace(array('~\s+~s' ), array(' '), $clean)));
	
	
	//老版本的Mysql并不支持union，常用的程序里也不使用union，但是一些黑客使用它，所以检查它
	if (strpos($clean, 'union') !== false && preg_match('~(^|[^a-z])union($|[^[a-z])~s', $clean) != 0){
	 $fail = true;
	 $error="union detect";
	}
	//发布版本的程序可能比较少包括--,#这样的注释，但是黑客经常使用它们
	elseif (strpos($clean, '/*') > 2 || strpos($clean, '--') !== false || strpos($clean, '#') !== false){
	 $fail = true;
	 $error="comment detect";
	}
	//这些函数不会被使用，但是黑客会用它来操作文件，down掉数据库
	elseif (strpos($clean, 'sleep') !== false && preg_match('~(^|[^a-z])sleep($|[^[a-z])~s', $clean) != 0){
	 $fail = true;
	 $error="slown down detect";
	}
	elseif (strpos($clean, 'benchmark') !== false && preg_match('~(^|[^a-z])benchmark($|[^[a-z])~s', $clean) != 0){
	 $fail = true;
	 $error="slown down detect";
	}
	elseif (strpos($clean, 'load_file') !== false && preg_match('~(^|[^a-z])load_file($|[^[a-z])~s', $clean) != 0){
	 $fail = true;
	 $error="file fun detect";
	}
	elseif (strpos($clean, 'into outfile') !== false && preg_match('~(^|[^a-z])into\s+outfile($|[^[a-z])~s', $clean) != 0){
	 $fail = true;
	 $error="file fun detect";
	}
	//老版本的MYSQL不支持子查询，我们的程序里可能也用得少，但是黑客可以使用它来查询数据库敏感信息
	elseif (preg_match('~\([^)]*?select~s', $clean) != 0){
	 $fail = true;
	 $error="sub select detect";
	}
	
	if (!empty($fail))
	{
	
	 fputs(fopen($log_file,'a+'),"<?php die();?>||$db_string||$error\r\n");
	 die("Hacking Detect<br><a href=http://www.80sec.com/>http://www.80sec.com</a>");
	}
	
	else {
	 return $db_string;
	}
}