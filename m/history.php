<?php
/*
// - 功能说明 : 用户登录控制
// - 创建作者 : 小陈
// - 创建时间 : 2013-03-20 13:10
*/
error_reporting(0);
require "../core/session.php";
require "../core/config.php";
include "../vcode/function.php";
require "../core/function.php";
$db = new mysql($mysql_server);
require "../core/class.log.php";
$log = new log();

$timestamp = time();
$history = $db->query("select * from sys_admin order by thislogin desc");



?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
<head>
    <title>系统登录</title>
    <meta http-equiv="Content-Type" content="text/html;charset=gb2312">
    <link href="css/bootstrap.min.css?v=3.4.0" rel="stylesheet">
    <link href="css/font-awesome.min.css?v=4.3.0" rel="stylesheet">
    <link href="css/plugins/footable/footable.core.css" rel="stylesheet">

    <link href="css/animate.min.css" rel="stylesheet">
    <link href="css/style.min.css?v=3.1.0" rel="stylesheet">

</head>

<body style="background-color: #f3f3f4">



<!--        start-->
<div class="wrapper wrapper-content animated fadeInRight">
        <div class="row">
            <div class="col-sm-12">
                <div class="ibox float-e-margins">
                    <div class="ibox-title">
                        <h5>登录信息查询</h5>
                    </div>
                    <div class="ibox-content">
                        <input type="text" class="form-control input-sm m-b-xs" id="filter"
                               placeholder="搜索表格...">

                        <table class="footable table table-stripped" data-page-size="8" data-filter=#filter>
                            <thead>
                            <tr>
                                <th>账号</th>
                                <th>真实名称</th>
                                <th>最后登录时间</th>
                                <th>距上次登录已过</th>
                                <th>登录次数</th>
                            </tr>
                            </thead>
                            <tbody>

                            <?php

                            for($s =0;$s < count($history);$s++){
//                                print_r($history[$s]);
                                $lasttime = date('Y-m-d H:i:s',$history[$s]['thislogin']);
                                $timediff = time() - $history[$s]['thislogin'];
//计算天
                                $days = intval($timediff/86400);
//计算小时数
                                $remain = $timediff%86400;
                                $hours = intval($remain/3600);
//计算分钟数
                                $remain = $remain%3600;
                                $mins = intval($remain/60);
//计算秒数
                                $secs = $remain%60;
                                ?>
                            <tr class="gradeX">
                                <td><?php echo $history[$s]['name']?></td>
                                <td><?php echo $history[$s]['realname']?></td>
                                <td><?php echo $lasttime?></td>
                                <td class="center"><?php echo $days.'天'.$hours.'时'.$mins.'分'.$secs.'秒'?></td>
                                <td class="center"><?php echo $history[$s]['logintimes']?></td>
                            </tr>
                            <?php
                            }
                            ?>

                            </tbody>
                            <tfoot>
                            <tr>
                                <td colspan="5">
                                    <ul class="pagination pull-right"></ul>
                                </td>
                            </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
</div>
<!--        end-->








<!-- 全局js -->
<script src="js/jquery-2.1.1.min.js"></script>
<script src="js/bootstrap.min.js?v=3.4.0"></script>
<script src="js/plugins/footable/footable.all.min.js"></script>

<!-- 自定义js -->
<!--<script src="js/content.min.js?v=1.0.0"></script>-->
<script>
    $(document).ready(function() {

        $('.footable').footable();
        $('.footable2').footable();

    });

</script>
</body>
</html>