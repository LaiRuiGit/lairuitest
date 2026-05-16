<html>
<head>
<title>设置用户权限</title>
<meta http-equiv="Content-Type" content="text/html;charset=gb2312">
<link href="/res/base.css" rel="stylesheet" type="text/css">
<script src="/res/base.js" language="javascript"></script>

<style>
body {margin:0; padding:0; }
#out {height:500px; overflow-y:scroll; border:2px solid #DFDFDF; }
.power_1 {border:0px; }
</style>

<script language="javascript">
// parent.document.getElementById("sys_frame").contentWindow.set_hospital_power(1, "xxx");
</script>
</head>

<body>

<form name="mainform" action="?" method="POST">
	<div id="out">
	<?php echo $power->show_power_table($usermenu, $_GET["power"]); ?>
	</div>

	<input type="hidden" name="op" value="<?php echo $_GET["op"]; ?>">
	<input type="hidden" name="pid" value="<?php echo $_GET["pid"]; ?>">

	<div style="margin-top:10px; text-align:center;"><input type="submit" class="buttonb" value="提交"></div>
</form>

</body>
</html>