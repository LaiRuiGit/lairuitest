<?php

/*

// 说明:

// 作者: 小陈 

// 时间:

*/



function pic_set_water($img, $water, $pos="right_bottom") {

	if (!file_exists($img) || !file_exists($water)) return false;

	@chmod($img, 0777);



	list($w, $h, $type, $attr) = getimagesize($img);



	if ($w < 200 || $h < 150) return false;



	// 依照图像格式创建一幅图片:

	$ext = strtolower(file_ext($img));

	switch ($ext) {

		case ".jpg":

			$im = imagecreatefromjpeg($img); break;

		case ".gif":

			return false;

			//$im = imagecreatefromgif($img); break;

		case ".png":

			$im = imagecreatefrompng($img); break;

		case ".bmp":

			$im = imagecreatefromwbmp($img); break;

		default:

			return false;

	}



	list($ww, $wh, $type, $attr) = getimagesize($water);



	// 计算位置:

	if ($pos == "left_top") {

		$wleft = 0;

		$wtop = 0;

	} else if ($pos == "right_top") {

		$wleft = $w-$ww-1;

		$wtop = 0;

	} else if ($pos == "left_bottom") {

		$wleft = 0;

		$wtop = $h-$wh-1;

	} else {

		$wleft = $w-$ww-1;

		$wtop = $h-$wh-1;

	}



	$wim = imagecreatefrompng($water);



	imagecopymerge_alpha($im, $wim, $wleft, $wtop, 0, 0, $ww-1, $wh-1, 100);



	switch ($ext) {

		case ".jpg":

			$rs = imagejpeg($im, $img, 90); break;

		case ".gif":

			return false;

		case ".png":

			$rs = imagepng($im, $img); break;

		case ".bmp":

			$rs = image2wbmp($im, $img); break;

		default:

			return false;

	}



	return $rs;

}



function imagecopymerge_alpha($dst_im, $src_im, $dst_x, $dst_y, $src_x, $src_y, $src_w, $src_h, $pct){

	$cut = imagecreatetruecolor($src_w, $src_h);

	imagecopy($cut, $dst_im, 0, 0, $dst_x, $dst_y, $src_w, $src_h);



	imagecopy($cut, $src_im, 0, 0, $src_x, $src_y, $src_w, $src_h);

	imagecopymerge($dst_im, $cut, $dst_x, $dst_y, $src_x, $src_y, $src_w, $src_h, $pct);

}



?>