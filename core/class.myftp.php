<?php

class myftp {

	var $user;

	var $pw;

	var $host;

	var $con_id;

	var $ERR = true; //错误对象

	var $mConnect = true; //连接错误对象



	function myftp($host, $user, $pw) { //初始化ftp

		$this->host = $host;

		$this->user = $user;

		$this->pw = $pw;

		if ($this->connect ()) {

			$this->mConnect = true;

		} else {

			$this->mConnect = false;

			return false;

		}

	}



	function connect() { //建立ftp连接

		if ($this->con_id = @ ftp_connect ( $this->host )) {

			if (ftp_login ( $this->con_id, $this->user, $this->pw )) {return true;}

		}

		return false;

	}



	function dir_list($dir_path) { // 排序显示所有文件及文件夹

		$list = ftp_rawlist ( $this->con_id, $dir_path ); // Returns a list of files and folders

		//global $folders;

		//global $files;

		$folders = $files = array();

		for($i = 0; $i < sizeof ( $list ); $i ++) {



			// 兼容写法 by hp168.cn 2013-10-18

			// 研究了多种ftp软件的返回格式，文件名始终在最后，其次就是判断是否目录还是文件了

			$part = explode(" ", rtrim($list[$i]));

			if (count($part) > 3) {

				$filename = trim(array_pop($part));

				if ($filename != '.' && $filename != '..') {

					if (substr(ltrim($list[$i]), 0, 1) == 'd' || strpos($list[$i], "<DIR>") !== false) {

						$folders[] = $filename;

					} else {

						$files[] = $filename;

					}

				}

			}



/*

	//格式参照，片段

    [2] => 10-18-09  10:18AM       <DIR>          dianxingbingli

    [3] => 10-18-09  10:12AM       <DIR>          guahao

    [4] => 10-18-09  12:50AM       <DIR>          houtai

    [5] => 10-18-09  12:27AM                 1497 html.php

    [6] => 10-18-09  12:31AM       <DIR>          images

*/





/*

	...

    [8] => drwxrwxrwx    2 80       8911          512 Oct 17 11:18 8

    [9] => drwxrwxrwx    2 80       8911          512 Oct 17 11:18 9

    [10] => drwxr-xr-x    2 8911     8911         1024 Oct 10 18:14 baojianyinshi

    [11] => drwxr-xr-x    2 8911     8911          512 Oct 10 18:14 bingrenhuli

    [12] => drwxr-xr-x    2 8911     8911          512 Oct 10 18:15 changyongyaowu

    [13] => -rw-r--r--    1 8911     8911         5757 Oct 10 18:18 clean_up.php

*/



/*

    [0] => total 135

    [1] => drw-rw-rw-   1 user     group           0 Oct 16 21:00 !tools

    [2] => drw-rw-rw-   1 user     group           0 Oct 18 01:06 .

    [3] => drw-rw-rw-   1 user     group           0 Oct 18 01:06 ..

    [4] => drw-rw-rw-   1 user     group           0 Oct 17 23:59 cache

    [5] => drw-rw-rw-   1 user     group           0 Oct 14 22:49 css

    [6] => drw-rw-rw-   1 user     group           0 Oct 15 01:19 dianxingbingli

    [7] => drw-rw-rw-   1 user     group           0 Oct 18 01:07 guahao

    [8] => drw-rw-rw-   1 user     group           0 Oct 17 19:47 houtai

    [9] => -rw-rw-rw-   1 user     group        1497 Oct 16 20:45 html.php

	...

*/



			// 下面是原有的实现

			//list ( $permissions, $next ) = split ( " ", $list [$i], 2 );

			//list ( $num, $next ) = split ( " ", $this->cutspaces ( $next ), 2 );

			//list ( $owner, $next ) = split ( " ", $this->cutspaces ( $next ), 2 );

			//list ( $group, $next ) = split ( " ", $this->cutspaces ( $next ), 2 );

			//list ( $size, $next ) = split ( " ", $this->cutspaces ( $next ), 2 );

			//list ( $month, $next ) = split ( " ", $this->cutspaces ( $next ), 2 );

			//list ( $day, $next ) = split ( " ", $this->cutspaces ( $next ), 2 );

			//list ( $year_time, $filename ) = split ( " ", $this->cutspaces ( $next ), 2 );

			//if ($filename != "." && $filename != "..") {

				//if (substr ( $permissions, 0, 1 ) == "d") {

					//$folders [] = $filename;

				//} else {

					//$files [] = $filename;

				//}

			//}

		}

		@sort ( $folders );

		@sort ( $files );

		$result = array ($folders, $files ); //$result[0]为目录,$resutl[1]为文件

		return $result;

	}



	function mk_dir($name) { //创建新文件夹

		$names = explode("/",$name);

		foreach ($names as $v){

			$p .= "/".$v;

			@! ftp_mkdir ( $this->con_id, $p );

			@! ftp_chmod  ( $this->con_id, 0777, $p );

		}

		return true;

	}



	function del_dir($obj) { //删除文件夹

		$list = ftp_rawlist ( $this->con_id, $obj ); // Returns a list of files and folders

		for($i = 0; $i < sizeof ( $list ); $i ++) {

			list ( $permissions, $next ) = split ( " ", $list [$i], 2 );

			list ( $num, $next ) = split ( " ", $this->cutspaces ( $next ), 2 );

			list ( $owner, $next ) = split ( " ", $this->cutspaces ( $next ), 2 );

			list ( $group, $next ) = split ( " ", $this->cutspaces ( $next ), 2 );

			list ( $size, $next ) = split ( " ", $this->cutspaces ( $next ), 2 );

			list ( $month, $next ) = split ( " ", $this->cutspaces ( $next ), 2 );

			list ( $day, $next ) = split ( " ", $this->cutspaces ( $next ), 2 );

			list ( $year_time, $filename ) = split ( " ", $this->cutspaces ( $next ), 2 );

			if ($filename != "." && $filename != "..") {

				if (substr ( $permissions, 0, 1 ) == "d") {

					$this->del_dir ( "$obj/$filename" );

				} else {

					$this->del_file ( "$obj/$filename" );

				}

			}

		}

		@ftp_rmdir ( $this->con_id, $obj );

	}



	function upload_file($file, $tofile) { //服务器上传文件到ftp服务器

		if (! ftp_put ( $this->con_id, $tofile, $file, FTP_BINARY )) { //FTP_BINARY二进制模式,FTP_ASCII文本模式

			$this->error ( "不能上传文件 <b>&quot;" . $tofile . "&quot;</b>" );

		}

		@! ftp_chmod  ( $this->con_id, 0777, $tofile );

		return true;

	}



	function download_file($remote_file, $localfile) {

		if (! ftp_get($this->con_id, $localfile, $remote_file, FTP_BINARY)) {

			$this->error ( "不能下载文件 <b>&quot;" . $remote_file . "&quot;</b>" );

			return false;

		}

		return true;

	}



	function del_file($obj) { //删除文件

		if (@! ftp_delete ( $this->con_id, $obj )) {return false;}

		return true;

	}



	function close() { //关闭ftp

		ftp_quit ( $this->con_id );

	}



	function ren_name($name, $toname) { //重命名文件,移动文件,移动目录,重命名目录

		if (@! ftp_rename ( $this->con_id, $name, $toname )) {

			$this->error ( "不能对<b>&quot;" . $name . "&quot;</b>进行改变" );

			return false;

		}

		return true;

	}



	function cutspaces($str) {

		while ( substr ( $str, 0, 1 ) == " " ) {

			$str = substr ( $str, 1 );

		}

		return $str;

	}



	function error($err_str = "") { //打印错误信息

		if ($this->ERR) echo "[" . $err_str . "]<br>\n";

	}

}

?>