<?php

/*

// - 功能说明 : 日志记录系统

// - 创建作者 : 小陈 

// - 创建时间 : 2013-05-10 => 2013-05-23

*/



class log {

	var $close = 0; // 0-记录日志, 1-关闭日志系统

	var $table = "sys_log";

	var $outtime = 30; //过期时间，按天，将自动删除过期日志



	function add($type='login|delete|add|edit|...', $title, $data='', $table_name='', $view_url='') {

		global $debug_mode;

		if ($this->close || $debug_mode) return false;

		global $db, $uid, $username, $table;


		$r = array();
		$r["type"] = $type;
		$r["title"] = $title;
		$r["pagename"] = $_SERVER["REQUEST_URI"];
		$r["view_url"] = $view_url;
		$r["data"] = is_array($data) ? serialize($data) : $data;
		$r["table_name"] = $table_name ? $table_name : ("(".$table.")");
		$r["username"] = $username;

		$r["uid"] = $uid;

		$r["ip"] = get_ip();

		$r["addtime"] = time();



		$sqldata = $db->sqljoin($r);

		$res = $db->query("insert into ".$this->table." set $sqldata");

		if (!$res) exit($db->sql);





		// 过期日志删除:

		if (mt_rand(1, 1000) <= 10) {

			$this->log_clear();

		}

		return $res;

	}


	function log_clear() {

		global $db;

		if ($this->outtime > 0) {

			$outtime = strtotime("-".intval($this->outtime)." days");

			$db->query("delete from ".$this->table." where addtime<$outtime");

		}

	}

}

?>