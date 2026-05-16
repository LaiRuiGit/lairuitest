<?php

/*

// 说明: 查询pr值函数

// 作者: 小陈 

// 时间: 2010-04-01 10:58

*/



define('GMAG', 0xE6359A60);



function zeroFill($a, $b) {

	$z = hexdec(80000000);

	if ($z & $a) {

		$a = ($a>>1);

		$a &= (~$z);

		$a |= 0x40000000;

		$a = ($a>>($b-1));

	} else {

		$a = ($a>>$b);

	}

	return $a;

}





function mix($a,$b,$c) {

	$a -= $b; $a -= $c; $a ^= (zeroFill($c,13));

	$b -= $c; $b -= $a; $b ^= ($a<<8);

	$c -= $a; $c -= $b; $c ^= (zeroFill($b,13));

	$a -= $b; $a -= $c; $a ^= (zeroFill($c,12));

	$b -= $c; $b -= $a; $b ^= ($a<<16);

	$c -= $a; $c -= $b; $c ^= (zeroFill($b,5));

	$a -= $b; $a -= $c; $a ^= (zeroFill($c,3));

	$b -= $c; $b -= $a; $b ^= ($a<<10);

	$c -= $a; $c -= $b; $c ^= (zeroFill($b,15));



	return array($a,$b,$c);

}



function GCH($url, $length=null, $init=GMAG) {

	if(is_null($length)) {

		$length = sizeof($url);

	}

	$a = $b = 0x9E3779B9;

	$c = $init;

	$k = 0;

	$len = $length;

	while($len >= 12) {

		$a += ($url[$k+0] +($url[$k+1]<<8) +($url[$k+2]<<16) +($url[$k+3]<<24));

		$b += ($url[$k+4] +($url[$k+5]<<8) +($url[$k+6]<<16) +($url[$k+7]<<24));

		$c += ($url[$k+8] +($url[$k+9]<<8) +($url[$k+10]<<16)+($url[$k+11]<<24));

		$mix = mix($a,$b,$c);

		$a = $mix[0]; $b = $mix[1]; $c = $mix[2];

		$k += 12;

		$len -= 12;

	}



	$c += $length;

	switch($len) {

		case 11: $c+=($url[$k+10]<<24);

		case 10: $c+=($url[$k+9]<<16);

		case 9 : $c+=($url[$k+8]<<8);

		case 8 : $b+=($url[$k+7]<<24);

		case 7 : $b+=($url[$k+6]<<16);

		case 6 : $b+=($url[$k+5]<<8);

		case 5 : $b+=($url[$k+4]);

		case 4 : $a+=($url[$k+3]<<24);

		case 3 : $a+=($url[$k+2]<<16);

		case 2 : $a+=($url[$k+1]<<8);

		case 1 : $a+=($url[$k+0]);

	}

	$mix = mix($a,$b,$c);



	return $mix[2];

}



function strord($string) {

	for($i=0;$i<strlen($string);$i++) {

		$result[$i] = ord($string{$i});

	}

	return $result;

}



function get_pr($_url) {

	$url = 'info:'.$_url;

	$ch = GCH(strord($url));

	$url = 'info:'.urlencode($_url);

	$g = "http://www.google.com/search?client=navclient-auto&ch=6$ch&ie=UTF-8&oe=UTF-8&features=Rank&q=$url";

	$pr_str = '';



	$fp = fsockopen("www.google.com.hk", 80, $errno, $errstr, 30);

	if (!$fp) {

		echo "$errstr ($errno)<br />\n";

	} else {

		$out = "GET "."/search?client=navclient-auto&ch=6$ch&ie=UTF-8&oe=UTF-8&features=Rank&q=$url"." HTTP/1.1\r\n";

		$out .= "Host: www.google.com.hk\r\n";

		$out .= "Connection: Close\r\n\r\n";

		fwrite($fp, $out);

		while (!feof($fp)) {

			$pr_str .= fgets($fp, 128);

		}

		fclose($fp);

	}





	//$pr_str = file_get_contents($g);



	if (substr_count($pr_str, "403 Forbidden") > 0) {

		return "无法检测";

	}

	return substr($pr_str,strrpos($pr_str, ":")+1);

	//return $pr_str;

}



?>