<?php

/*
// - 功能说明 : index.php
// - 创建作者 : 小陈 
// - 创建时间 : 2010-06-21
*/
require "core/core.php";

$_SESSION["root_url"] = $root_url = "http://".$_SERVER["HTTP_HOST"].str_replace("index.php", "", $_SERVER["REQUEST_URI"]);

$power = load_class("power", $db);
list($menu_stru, $menu_power) = $power->parse_menu($usermenu);
$menu_ids = array_keys($menu_power);
$menu_id_list = implode(",", $menu_ids);

$menu_data = array();
if ($tmp_data = $db->query("select id,title,link,isshow from sys_menu where id in ($menu_id_list) and isshow=1 order by sort")) {
	foreach ($tmp_data as $tmp_line) {
		$menu_data[$tmp_line["id"]] = array($tmp_line["title"], $tmp_line["link"]);
	}
}

// 验证&删除多余mid:
foreach ($menu_stru as $mainid => $mlevel1) {
	if (!array_key_exists($mainid, $menu_data)) {
		unset($menu_stru[$mainid]); continue;
	}
	foreach ($mlevel1 as $key => $itemid) {
		if (!array_key_exists($itemid, $menu_data)) {
			unset($mlevel1[$key]);
		}
	}
	$menu_stru[$mainid] = array_merge($mlevel1);
}

$menu_mids = json(array_keys($menu_stru));
$menu_stru_json = json($menu_stru);
$menu_data_json = json($menu_data);
$menu_shortcut = "";

$is_show_dyn_menu = isset($config["jsmenu"]) ? intval($config["jsmenu"]) : 1;
$is_show_shortcut = 0;
$submenu_pos = isset($config["submenu_pos"]) ? intval($config["submenu_pos"]) : 1;

$is_show_footer = isset($config['footer']) ? intval($config['footer']) : 0;

include "res/index.tpl.php";
?>