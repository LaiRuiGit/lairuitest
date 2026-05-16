<style type="text/css">
.line td {border:1px solid #F0F0F0;padding:5px 4px 3px 4px;color:silver;padding:12px 0;text-align:center;color:#3a8581;font-size:12px;font-weight:bolder;}
.line {color:#3a8581;}
.line td a{color:#3a8581;text-decoration:none;font-size:12px;font-weight:bolder;}
.line td a:hover{color:#3a8581;text-decoration:underline;}
img{border:0px;}
.mtr{color:#666;font-size:12px;text-align:center}
.mtr td{border:1px solid #F0F0F0;}
.mtrx{color:red;font-size:12px;text-align:center}
.mtrx td{border:1px solid #F0F0F0;}
.page{text-align:center;background:#f2f2f2;font-size:12px; color:#666;}
.page a{font-size:12px;color:#666;text-decoration:none;}
.pagex{text-align:center;background:#ffe9d2;}
.pagex td{text-align:left;height:25px;line-height:25px;font-size:12px;font-weight:bolder;color:red;padding-left:10px;}
.top{border:1px solid #CCC;height:22px;line-height:22px; font-size:12px;padding-left:8px;width:99%;margin:0px 10px 10px 0px;}
</style>
<script type="text/javascript">
function MM_jumpMenu(targ,selObj,restore){ //v3.0
  eval(targ+".location='"+selObj.options[selObj.selectedIndex].value+"'");
  if (restore) selObj.selectedIndex=0;
}
</script>
<div class="top"><?=$res_report?></div>
<table width="100%" border="0" cellspacing="0" cellpadding="0" style="border:2px solid #FFCFB9; border-collapse:collapse; margin:0px; padding:0px;">
  <tr class="line">
    <td width="82" height="20" bgcolor="#f2f2f2"><a title="点击按&ldquo;升序&rdquo;排序" href="#">姓名</a></td>
    <td width="82" bgcolor="#f2f2f2"><a href="#">电话</a></td>
    <td width="82" bgcolor="#f2f2f2" hidden><a href="#">QQ</a></td>
    <td width="82" bgcolor="#f2f2f2"><a title="点击按&ldquo;升序&rdquo;排序" href="#">年龄</a></td>
    <td width="82" bgcolor="#f2f2f2"><a title="点击按&ldquo;升序&rdquo;排序" href="#">所在小区</a></td>
    <td width="117" bgcolor="#f2f2f2"><a href="#">门店</a></td>
    <td width="138" bgcolor="#f2f2f2"><a href="#">登记时间</a></td>
    <td bgcolor="#f2f2f2">说明</td>
    <td width="80" bgcolor="#f2f2f2">操作</td>
  </tr>
  <?=mlist();?>
  <tr>
    <td height="35" colspan="9" class="page"><?=page();?></td>
  </tr>
</table>



