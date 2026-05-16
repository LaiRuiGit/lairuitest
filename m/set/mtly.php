<html>
<head>
<title>搜索引擎设置</title>
<meta http-equiv="Content-Type" content="text/html;charset=gb2312">
<link href="/res/base.css" rel="stylesheet" type="text/css">
<script src="/res/base.js" language="javascript"></script>
</head>

<body>
<?php
require "../../core/core.php";
if($_SESSION[$cfgSessionName]["chen"]!="debug")
{
	echo "对不起，您不是本站管理员无法操作当前页！";
	exit();
}
$menu_data = array();
?>
<!-- 头部 begin -->
<div class="headers">
	<div class="headers_title" style="width:50%"><span class="tips">短信通知接口(全局，应用于所有门店)</span></div>
	<div class="headers_oprate"></div>
</div>
<!-- 头部 end -->
<div class="space"></div>
<!-- 数据列表 begin -->

<table width="100%" align="center" class="list">
	<!-- 表头定义 begin -->
	<tr>
		<td class="head" align="center" width="14%">门店</td>
		<td class="head" align="left" width="54%"><a href='#' title=''>名称</a></td>
		<td class="head" align="center" width="12%">操作</td>
	</tr>
	<!-- 表头定义 end -->
	<!-- 主要列表数据 begin -->
    <?php
	function get_title($id)
	{
		global $db;
		$str=$db->query("select * from mtly where hospital='{$id}'");
		$str=$str[0];
		return $str["name"];
	}
	if ($tmp_data = $db->query("select * from hospital order by id desc")) {
		foreach ($tmp_data as $tmp_line) {
			
		?>
		<form id="mainform<?php echo $tmp_line["id"];?>" action="mtly_update.php?op=edit&id=<?php echo $tmp_line["id"];?>" method="post">
		<tr>
			<td height="90" align="center" class="item"><?=$tmp_line["name"]?></td>
<td align="left" class="item"><textarea name="t" cols="50" rows="5"><?=get_title($tmp_line["id"]);?></textarea></td>
			<td align="center" class="item"><a href='#' class='op' onClick="document.getElementById('mainform<?php echo $tmp_line["id"];?>').submit()">修改</a></td>
		</tr>
		</form>
		<?php
		}
	}
	?>
    <tr>
	  <td height="30" align="center" class="item">&nbsp;</td>
	  <td align="center" valign="bottom" class="item">
      </td>
	  <td align="center" class="item">&nbsp;</td>
  </tr>
	<!-- 主要列表数据 end -->
</table>

<!-- 数据列表 end -->
<div class="space"></div>
<!-- 分页链接 begin -->

<div class="footer_op">
  
  <div class="footer_op_right"><div class="pagelink">
  <!-- 
  <div class="pagelink_tips">第<span class="pagelink_cur_page">1</span>/<span class="pagelink_all_page">1</span>页&nbsp;共<span class="pagelink_all_rec">2</span>条</div>
  -->
</div>
<!-- 分页链接 end -->
</body>
</html>
