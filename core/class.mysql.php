<?php

class mysql {

	var $host;

	var $user;

	var $pwd;

	var $dbname;

	var $charset = 'gbk';

	var $dblink;

	var $result;

	var $sql = '';

	var $show_error = 1; 

	var $error = '';

	var $slow_query = 0; 

	var $slow_query_path = ""; 



	// 类初始化

	function mysql($mysql_server = array()) {

		if (!$mysql_server) {

			global $mysql_server;

		}

		list($host, $user, $pwd, $dbname, $charset) = $mysql_server;

		if (!@$this->connect($host, $user, $pwd, $dbname, $charset)) {

			exit('mysql error: connect failed, please check the connect parameters.');

		}

	}



	// 创建mysql连接:

	function connect($host, $user, $pwd, $dbname, $charset = '') {

		list($this->host, $this->user, $this->pwd, $this->dbname) = array($host, $user, $pwd, $dbname);

		if (isset($charset)) {

			$this->charset = $charset;

		}
		if ($this->host && $this->user) {

			if (!$this->dblink = @mysql_connect($host, $user, $pwd)) {

				$this->error();

				return false;

			}


			if ($this->dbname) {

				$this->select_db($this->dbname);

			}

			if ($this->charset) {

				@mysql_query("SET NAMES '".$this->charset."'", $this->dblink);

			}

			@mysql_query("SET sql_mode=''");

			return true;

		} else {

			exit('mysql error: connect parameters not enough.');

		}

	}



	// 选择到 db

	function select_db($dbname) {

		if(@mysql_select_db($dbname,$this->dblink)) {

			$this->dbname = $dbname;

			return true;

		} else {

			exit("mysql error: the database '{$dbname}' not exists.");

		}

	}



	// 将数组合成 sql 插入数据格式

	function sqljoin($data) {

		$data_array = array();

		foreach ($data as $k => $v) {

			$k = trim($k, "`");

			$data_array[] = "`$k`='{$v}'";

		}

		return implode(",", $data_array);

	}




	function query($sql, $return_count_or_key_field = '', $value_field = '') {

		$this->sql = trim($sql);



		// 分析查询的类型,根据sql第一个词 insert select update delete ...

		list($query_type, $other) = explode(' ', $this->sql, 2);

		$query_type = strtolower($query_type); //统一为小写



		// 慢查询的起始:

		if ($query_type == "select" && $this->slow_query > 0) {

			$begin_time = $this->now_time();

		}



		// 执行查询:

		$this->result = @mysql_query($this->sql, $this->dblink);



		// 记录慢查询?

		if ($query_type == "select" && $this->slow_query > 0) {

			$end_time = $this->now_time();

			if ($end_time - $begin_time > $this->slow_query) {

				$this->log_slow_query($end_time - $begin_time);

			}

		}



		// 处理错误:

		if (!$this->result) {

			$this->error();

			return false;

		}



		// 查询结果处理:

		if ($query_type == "select" || $query_type == "show") {

			// 对参数 return_count_or_key_field 的处理(判断其为数值则表示查询返回条数,否则表示返回数组要使用的键名)

			if ($return_count_or_key_field !== "") {

				if (is_numeric($return_count_or_key_field)) {

					$return_count = $return_count_or_key_field;

				} else {

					$key_field = $return_count_or_key_field;

				}

			}



			// select 结果:

			$rs = array();

			while ($row = @mysql_fetch_assoc($this->result)) {

				if ($return_count == 1) {

					return $value_field ? $row[$value_field] : $row;

				}

				if ($key_field) {

					$rs[$row[$key_field]] = $value_field ? $row[$value_field] : $row;

				} else {

					$rs[] = $value_field ? $row[$value_field] : $row;

				}

			}

			if ($return_count == 1 && $value_field != '') {

				return false;

			}

			return $rs;

		} elseif ($query_type == "insert") {

			return @mysql_insert_id($this->dblink);

		}



		// 其他查询情况如果正确执行均返回成功:

		return true;

	}



	// 查询并获取结果集中的第一条资料

	function query_first($sql) {

		return $this->query($sql, 1);

	}



	function query_count($sql) {

		return $this->query($sql, 1, "count(*)");

	}



	// 查询表($table)中,字段($field)的值为$value的记录，返回其另外一个字段($need_field)的值 (weelia@2013-05-01 00:23)

	function lookup($table, $field, $value, $need_field) {

		$tm = $this->query_first("select {$need_field} from {$table} where {$field}='{$value}' limit 1");

		if (is_array($tm) && count($tm) > 0) {

			return $tm[$need_field];

		}

		return false;

	}



	function affected_row() {

		return mysql_affected_rows($this->dblink);

	}



	function make_where($w, $with_where = 1) {

		return count($w) ? ($with_where ? "where " : "").implode(" and ", $w) : "";

	}



	function make_sort($heads, $sort='', $order='', $default_sort='', $default_order='') {

		$s = '';

		if ($sort && array_key_exists($sort, $heads) && $heads[$sort]["sort"]) {

			$s = $heads[$sort]["sort"];

			if (in_array(strtolower($order), array('', 'asc', 'desc'))) {

				$s .= " ".$order;

			}

		} else {

			if ($default_sort && array_key_exists($default_sort, $heads) && $heads[$default_sort]["sort"]) {

				$s = $heads[$default_sort]["sort"];

				if (in_array(strtolower($default_order), array('', 'asc', 'desc'))) {

					$s .= " ".$default_order;

				}

			}

		}

		if ($s != '') {

			$s = "order by ".$s;

		}



		return $s;

	}



	// 获取当前时间:

	function now_time() {

		list($usec, $sec) = explode(" ", microtime());

		return ((float)$usec + (float)$sec);

	}



	// 记录慢速查询sql到文件

	function log_slow_query($sql_running_time = 0) {

		$log_filename = $this->slow_query_path."mysql_slow_query_".date("Ymd").".log";



		$time = date("Y-m-d H:i:s");

		$pagename = $_SERVER["PHP_SELF"];

		$sql = $this->sql;

		$aff_rows = @mysql_affected_rows($this->dblink);



		$s = $time." ".$pagename."\n".sprintf("[%8s] [%8s] ", round($sql_running_time, 3), $aff_rows).$sql."\n\n";



		if ($handle = @fopen($log_filename, "a+")) {

			fwrite($handle, $s);

			fclose($handle);

			return true;

		}



		return false;

	}



	// 显示错误

	function error() {

		if (!$this->show_error) return;

		$this->error = '<br />';

		if ($this->dblink) $this->error .= @mysql_error($this->dblink).'<br />';

		if ($this->sql) $this->error .= $this->sql.'<br />';

		echo $this->error;

	}

}

?>