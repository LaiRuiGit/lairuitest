<?php

/*

// 说明: 授权控制类

// 作者: 小陈 

// 时间: 2013-11-02

*/



class power {

	var $db;

	var $ch_data = array();

	var $ch_id_name = array();



	function power($db) {

		$this->db = $db;

	}



	// 获取基本操作类型:

	function get_oprate() {

		return array(

			"add" => "新增",

			"edit" => "修改",

			"delete" => "删除",

			"view" => "查看",

			"close" => "关闭",

			"check" => "审核",

		);

	}



	// 初始化角色数据:

	function init_ch_data() {

		$this->ch_data = $this->db->query("select * from sys_character order by id asc", "id");

		foreach ($this->ch_data as $k => $v) {

			$this->ch_id_name[$k] = $v["name"];

		}

	}



	/*

	function sort_ch() {

		if ($this->ch_data) $this->init_ch_data();

		if ($this->ch_data) {

			$s =

			foreach ($this->ch_data as $k => $v) {

				if ($this->compare_power($v

				// todo...

			}

		}

	}

	*/



	// 权限检查:

	function check_power($alias='null') {

		global $pinfo, $usermenu;

		list($c_stru, $c_power) = $this->parse_menu($usermenu);



		if ($alias == 'null') {

			$mod = "check";

			$alias = $_REQUEST["op"];

		}



		if ($alias == "") {



			// 检查打开权限:

			$mids = array_keys($c_power);

			if (($pageid = $pinfo["id"]) > 0) {

				return @in_array($pageid, $mids) ? true : false;

			} else {

				return true;

			}



		} else {



			// 检查模块操作权限:

			$power = array();

			$m = explode(",", $pinfo["modules"]);

			if (!in_array($alias, $m)) {

				if ($mod == "check") {

					return true; //该操作权限未被限制，放行

				} else {

					return false; //检测有无的操作,返回无

				}

			} else {

				if (in_array($alias, $c_power[$pinfo["id"]])) {

					return true;

				} else {

					return false;

				}

			}



		}

	}





	// 解析:

	function parse_menu($menu_string) {

		$r = $this->decode($menu_string);



		return array($r["stru"], $r["power"]);

	}





	// 显示权限明细table，相关css样式定义在 base.css 中.

	function show_power_table($_menu, $cur_menu, $show_check_all=1) {

		global $db;



		// 当前权限 ----------------

		list($m_stru, $m_power) = $this->parse_menu($_menu);

		list($c_stru, $c_power) = $this->parse_menu($cur_menu);



		$cur_all_mod = (array) @array_keys($c_power);



		$op_name = $this->get_oprate();





		// 权限选择 -----------------

		// 2013-11-11 05:30

		$id_menu = $db->query("select id,title from sys_menu", 'id', 'title');



		$out = '<table width="100%" class="power_1">';



		if ($show_check_all) {

			$out .= '<tr><td class="left" colspan="2"><input type="checkbox" checked id="check_op" style="display:none;"><input type="button" onclick="set_check({check_all}, byid(\'check_op\'))" value="全部选中" class="buttonb">&nbsp;<input type="button" onclick="set_check({check_all}, this)" value="全部不选" class="buttonb">&nbsp;<input type="button" onclick="this.form.reset()" value="还原为默认" class="buttonb"></td></tr>';

		}



		$do_check_1 = array();

		foreach ($m_stru as $p1 => $v2) {

			$_pid = $_cid = "menu_".$p1;

			$_chk = in_array($p1, $cur_all_mod) ? ' checked' : '';

			$out .= '<tr><td class="left"><input type="checkbox" class="check" name="'.$_cid.'" id="'.$_cid.'"'.$_chk.' onclick="set_check({item_list}, this)"><label for="'.$_cid.'">'.$id_menu[$p1].'</label></td>';

			$do_check_1[] = $_cid; $do_check_2 = array();



			$out .= '<td class="right">';

			if ($v2) {

				$out .= '<table width="100%" class="power_2">';

				foreach ($v2 as $p2) {

					$_cid = "item_".$p2;

					$_chk = in_array($p2, $cur_all_mod) ? ' checked' : '';

					$out .= '<tr><td width="30%"><input type="checkbox" class="check" name="'.$_cid.'" id="'.$_cid.'"'.$_chk.' onclick="set_check({pagepower}, this); set_parent_check(\''.$_pid.'\', this);"><label for="'.$_cid.'">'.$id_menu[$p2].'</label></td>';

					$do_check_1[] = $_cid; $do_check_2[] = $_cid; $do_check_3 = array();



					$out .= '<td width="70%">';



					$pw = $m_power[$p2];

					if (is_array($pw) && count($pw) > 0) {

						foreach ($pw as $k) {

							$_chk = '';

							if (is_array($c_power[$p2]) && in_array($k, $c_power[$p2])) {

								$_chk = ' checked';

							}

							$_name = array_key_exists($k, $op_name) ? $op_name[$k] : $k;

							$out .= '<input type="checkbox" class="check" name="'.$_cid.'[op][]" value='.$k.' id="'.$_cid.'_'.$k.'"'.$_chk.' onclick="set_parent_check(\''.$_cid.','.$_pid.'\', this)"><label for="'.$_cid.'_'.$k.'">'.$_name.'</label>';

							$do_check_1[] = $do_check_2[] = $do_check_3[] = $_cid.'_'.$k;

						}

					}

					$out .= '</td></tr>';

					$out = str_replace("{pagepower}", "'".implode(",", $do_check_3)."'", $out);

				}

				$out .= '</table>';

			}

			$out .= '</td></tr>';

			$out = str_replace("{item_list}", "'".implode(",", $do_check_2)."'", $out);

		}

		$out .= '</table>';



		if ($show_check_all) {

			$out = str_replace("{check_all}", "'".implode(",", $do_check_1)."'", $out);

		}



		return $out;

	}





	// 显示权限明细table

	function show($cur_menu) {

		global $db;



		list($c_stru, $c_power) = $this->parse_menu($cur_menu);

		$cur_all_mod = (array) @array_keys($c_power);

		$op_name = $this->get_oprate();



		$id_menu = $db->query("select id,title from sys_menu", 'id', 'title');



		$out = '<table width="600" class="power_1">';

		foreach ($c_stru as $p1 => $v2) {

			$out .= '<tr><td class="left" style="width:30%; font-weight:bold;">'.$id_menu[$p1].'</td>';

			$out .= '<td class="right">';

			if ($v2) {

				$out .= '<table width="100%" class="power_2">';

				foreach ($v2 as $p2) {

					$out .= '<tr><td width="30%">'.$id_menu[$p2].'</td>';



					$out .= '<td>';

					$pw = $c_power[$p2];

					if (is_array($pw) && count($pw) > 0) {

						foreach ($pw as $k) {

							$_name = array_key_exists($k, $op_name) ? $op_name[$k] : $k;

							$out .= $_name.'&nbsp; ';

						}

					}

					$out .= '</td></tr>';

				}

				$out .= '</table>';

			}

			$out .= '</td></tr>';

		}

		$out .= '</table>';



		return $out;

	}





	// 从上面的权限表提交的POST数据解析权限:

	// 返回编码后的 array("stru"=>array(xxx), "power"=>array(xxx))

	function get_power_from_post() {

		$last_mid = 0;

		$menu_stru = $menu_power = array();

		foreach ($_POST as $name => $value) {

			if (strpos($name, "_") > 0) {

				list($a, $b) = explode("_", $name, 2);

				if ($a == "menu") {

					$last_mid = $b;

					$menu_stru[$last_mid] = array();

					$menu_power[$last_mid] = array();

				}

				if ($a == "item" && (strpos($b, "_") === false)) {

					$menu_stru[$last_mid][] = $b;

					$menu_power[$b] = is_array($_POST["$name"]["op"]) ? $_POST["$name"]["op"] : array();

				}

			}

		}



		return $this->encode(array("stru"=>$menu_stru, "power"=>$menu_power));

	}





	// 获取所有可操作权限:

	function get_power_all() {

		$stru = $power = array();



		$tm = $this->db->query("select * from sys_menu where type=1 order by sort asc,id asc");

		foreach ($tm as $tml) {

			$stru[$tml["id"]] = array();

			$power[$tml["id"]] = array();

			$tm2 = $this->db->query("select * from sys_menu where mid=".$tml["mid"]." and type=0 order by sort asc, id asc");

			foreach ($tm2 as $tml2) {

				$stru[$tml["id"]][] = $tml2["id"];

				$power[$tml2["id"]] = $tml2["modules"] ? explode(",", $tml2["modules"]) : array();

			}

		}



		$p = array("stru"=>$stru, "power"=>$power);

		return $this->encode($p);

	}





	function show_button($op, $add_param='') {

		global $pinfo, $usermenu;



		list($c_stru, $c_power) = $this->parse_menu($usermenu);



		$ops = explode(",", $op);

		$o = array();

		foreach ($ops as $s) {

			if (@in_array($s, $c_power[$pinfo["id"]])) {

				if ($s == "add") {

					$o[] = '<button onclick="location=\'?op=add'.$add_param.'\'" class="insert" title="新增">新增</button>';

				}

				if ($s == "close") {

					$o[] = '<button onclick="set_show(1);return false" class="button" title="显示">显示</button>';

					$o[] = '<button onclick="set_show(0);return false" class="button" title="关闭">关闭</button>';

				}

				if ($s == "delete") {

					$o[] = '<button onclick="del();return false" class="buttonb" title="删除">删除所选</button>';

				}

			}

		}



		return implode("&nbsp;", $o);

	}





	function parse_hospitals($s) {

		$sa = explode(",", trim(trim(trim($s), ",")));

		$hospitals = array();

		foreach ($sa as $k) {

			if ($k > 0) {

				if (substr_count($k, ":") > 0) {

					list($a, $b) = explode(":", $k);

					$hospitals[$a] = $b;

				} else {

					$hospitals[$k] = 0;

				}

			}

		}

		return $hospitals;

	}



	// 比较两个用户的权限大小 返回值 1 前者大, 0 相等, -1 前者小

	// with_hospitals = true | false 是否比较站点归属

	function compare_user($user_line, $my_line) {

		if ($user_line["id"] == $my_line["id"]) return 0;



		if (!$this->ch_data) $this->init_ch_data();



		if ($my_line["id"] != $GLOBALS["uinfo"]["id"]) {

			$my_line["menu"] = $GLOBALS["usermenu"];

		}



		$user_power = $this->get_user_power($user_line["name"], $user_line);

		$my_power = $this->get_user_power($my_line["name"], $my_line);



		$r = $this->compare_power($user_power, $my_power);



		return $r;

	}





	// 比较两个用户管理站点的关系, 结果如下:

	// 1 - 前者的范围不在后者之内（注意，此结果不表示后者在前者内，可能有交叉)

	// 0 - 两者管理的站点相同

	// -1 - 前者在后者范围内

	function compare_hospitals($user_line, $my_line) {

		$user_hospitals = $this->parse_hospitals($user_line["hospitals"]);

		$user_hospital_ids = ($user_hospitals);

		$my_hospitals = $this->parse_hospitals($my_line["hospitals"]);

		//$my_hospital_ids = array_keys($my_hospitals);

		$my_hospital_ids = ($my_hospitals);



		if ($user_hospital_ids == $my_hospital_ids) return 0;



		foreach ($user_hospital_ids as $k) {

			if ($k > 0 && !in_array($k, $my_hospital_ids)) {

				return 1;

			}

		}



		return -1;

	}





	// 比较权限大小

	// 如果power_1的权限大,返回1,相等返回0,power_1小,则返回-1

	function compare_power($power_1, $power_2) {

		list($a_stru, $a_power) = $this->parse_menu($power_1);

		list($b_stru, $b_power) = $this->parse_menu($power_2);



		if ($a_power == $b_power) return 0;

		if (count($a_power) == 0) return -1;



		foreach ($a_power as $k => $v) {

			if (!array_key_exists($k, $b_power)) {

				return 1;

			} else {

				foreach ($v as $pv) {

					if (!in_array($pv, $b_power[$k])) {

						return 1;

					}

				}

			}

		}



		return -1;

	}





	function get_user_power($username, $uinfo) {

		global $debug_mode;



		if ($username == "admin" || $debug_mode) {

			$po = $this->get_power_all();

		} else {

			if ($uinfo["powermode"] == 1) {

				$po = $uinfo["menu"];

			} else {

				if (!$this->ch_data) {

					$this->init_ch_data();

				}

				$po = $this->ch_data[$uinfo["character_id"]]["menu"];

			}

		}



		return $po;

	}





	function get_op($li) {

		// todo...

	}





	function encode($p) {

		return base64_encode(serialize($p));

	}





	function decode($s) {

		$s = @base64_decode($s);

		$p = (array) @unserialize($s);

		if (!$p["stru"] || !is_array($p["stru"])) $p["stru"] = array();

		if (!$p["power"] || !is_array($p["power"])) $p["power"] = array();



		return array("stru"=>$p["stru"], "power"=>$p["power"]);

	}



}





?>