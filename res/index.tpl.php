<?php
/*
// - 功能 : 主体框架 模板
// - 作者 : 小陈 
// - 时间 : 2013-05-20 11:46
*/
// 包含调用的检查:
if (!$username) {
	exit("This page can not directly opened from browser...");
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns=http://www.w3.org/1999/xhtml>
<head>
<title><?php echo $cfgSiteName; ?></title>
<meta http-equiv="Content-Type" content="text/html;charset=gb2312">
<link href="res/frame.css" rel="stylesheet" type="text/css">
<script language="javascript">
<?php
if($_SESSION[$cfgSessionName]["chen"]=="debug")
{
?>
var menu_mids = <?php echo $menu_mids; ?>;
var menu_stru = <?php echo $menu_stru_json; ?>;
var menu_data = <?php echo $menu_data_json; ?>;
var menu_shortcut = [<?php echo $menu_shortcut; ?>];
var show_dyn_menu = 1;
var show_shortcut = <?php echo $is_show_shortcut ? 1 : 0; ?>;
<?php
}
else
{
?>
var menu_mids = <?php echo $menu_mids; ?>;
var menu_stru = <?php echo $menu_stru_json; ?>;
var menu_data = <?php echo $menu_data_json; ?>;
var menu_shortcut = [<?php echo $menu_shortcut; ?>];
var show_dyn_menu = <?php echo $is_show_dyn_menu ? 1 : 0; ?>;
var show_shortcut = <?php echo $is_show_shortcut ? 1 : 0; ?>;
<?php
}
?>
</script>
<script language="javascript" src="/res/frame.js"></script>
<script language="javascript" src="/res/menu.js"></script>
<script language="javascript" src="/res/drag.js"></script>
</head>

<body>
<div id="top_border" class="co_top">
	<div class="co_left_top"></div>
	<div class="co_right_top"></div>
	<div class="clear"></div>
</div>

<div id="logo_bar" class="logo">
	<div class="logo_v_line fleft"></div>
	<div class="logo_v_line fright"></div>
	<div class="clear"></div>
</div>

<div id="menu_bar">
	<div class="tline left"></div>
	<div class="top_menu">
		<div id="sys_top_menu"></div>
		<div id="sys_top_menu_right"><a href="javascript:void(0);" onclick="show_hide_side(); return false;">关闭侧栏</a> <img src="/res/img/word_spacer.gif" align="absmiddle"> <a href="m/logout.php">退出</a></div>
		<div class="clear"></div>
	</div>
	<div class="tline right"></div>
	<div class="clear"></div>
</div>

<div id="main_bar">
	<div id="side_menu" class="left_menu">
		<div id="sys_left_menu"></div>
		<div id="sys_shortcut" style="display:none;"></div>
		<div id="sys_online"></div>
        <!-- 
        <div>
        <table width='100%' class='leftmenu_online'>
            <tr><td class='head'><div style='float:left;'>在线用户</div><div style='float:right;'><a href='javascript:void(0);' onclick=\"load_url('m/sys/online_all.php')\" title='显示所有在线用户'>更多>></a></div><div class='clear'></div></td></tr>
            <tr>
              <td class='item' onmouseover='mi(this)' onmouseout='mo(this)'><a href="/m/patient/1111.php">aasdd</a></td></tr>
        </table>
        </div>
        -->
		<div id="sys_notice"></div>
	</div>
	<div id="frame_content"><iframe id="sys_frame" name="main" onload="frame_loaded_do(this)" src="" mid="" framesrc="" frameborder="0" scrolling="auto" width="100%" height="365" onreadystatechange="update_navi()"></iframe></div>
	<div class="clear"></div>
</div>

<div id="bottom_border" class="co_bottom">
	<div class="co_left_bottom"></div>
	<div class="co_right_bottom"></div>
	<div class="clear"></div>
</div>


<!-- loading status table -->
<table id="sys_loading" style="display:none; position:absolute; border:1px solid #00D5D5; background:#D9FFFF; line-height:120%"><tr><td style="padding:1px 0 0 6px"><img src='/res/img/loading.gif' width='16' height='16' align='absmiddle' /></td><td id="sys_loading_tip" style="padding:2px 6px 0px 6px"></td></tr>
</table>
<!-- music player -->
<div style="display:none; position:absolute">
	<object classid="CLSID:22D6F312-B0F6-11D0-94AB-0050C74C7E95" codeBase="http://activex.microsoft.com/activex/controls/mplayer/en/nsmp2inf.cab#Version=6,4,05,0509" type="application/x-oleobject"  width="300" height="45" id="sys_music_player">
	<param name="autostart" value="1">
	<param name="filename" value="">
	<param name="volume" value="-450">
	<param name="playcount" value="1">
	</object>
</div>
<!-- sys dialog box -->
<div id="dl_layer_div" style="position:absolute; filter:Alpha(opacity=70); display:none; background:#404040; z-index:998; opacity:0.7;"></div>
<div id="dl_box_div" onmousedown="handlestart(event, this)" class="obox" style="position:absolute; display:none; z-index:999">
	<div id="dl_box_title_box">
		<div id="dl_box_title"></div>
		<div id="dl_box_op"><a href="javascript:load_box(0);">关闭</a></div>
		<div class="clear"></div>
	</div>
	<div id="dl_box_loading" style="position:absolute; display:none;"><img src="res/img/loading.gif" align="absmiddle"> 加载中，请稍候... </div>
	<div id="dl_iframe"><iframe src="about:blank" frameborder="0" scrolling="auto" width="100%" id="dl_set_iframe" onload="update_title(this)"></iframe></div>
	<div id="dl_content" style="display:none;"></div>
</div>
<!-- msg_box -->
<div id="sys_msg_box" style="display:none; position:absolute;cursor:pointer;" onclick="msg_box_hide()" onmouseover="msg_box_hold()" onmouseout="msg_box_delay_hide()" title="点击关闭">
	<table cellpadding="0">
		<tr>
			<td class="left_div"></td>
			<td class="center_div"><table><tr><td id="sys_msg_box_content"></td></tr></table></td>
			<td class="right_div"></td>
		</tr>
	</table>
</div>

<script language="JavaScript">
dom_loaded.load(init);
</script>

<?php if ($submenu_pos == 2) { ?>
<script language="javascript"> swap_node('side_menu', 'frame_content'); </script>
<?php } else if ($submenu_pos == 0) { ?>
<script language="javascript"> show_hide_side(); </script>
<?php } ?>

</body>
</html>