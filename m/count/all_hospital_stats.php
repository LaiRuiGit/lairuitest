<?php
require "../../core/core.php";

$mod = "patient";
$table_type = $_GET["table_type"] ?? "miaofang";
$show = $_GET["show"] ?? "today";
$come = isset($_GET["come"]) ? intval($_GET["come"]) : -1;
$media = $_GET["media"] ?? "";
$time_type = $_GET["time_type"] ?? "order_date";

if ($table_type == "miaofang") {
    $hospital_ids = array(12, 13, 14, 15, 16, 17, 19, 20);
    $title = iconv("UTF-8", "GBK", "苗方统计");
} else {
    $hospital_ids = array(22, 23, 24, 25);
    $title = iconv("UTF-8", "GBK", "痘艺美统计");
}

$today_tb = mktime(0, 0, 0);
$today_te = $today_tb + 24 * 3600;
$yesterday_tb = $today_tb - 24 * 3600;
$month_tb = mktime(0, 0, 0, date("m"), 1);
$month_te = strtotime("+1 month", $month_tb);
$lastmonth_tb = strtotime("-1 month", $month_tb);
$lastmonth_te = $month_tb;

switch ($show) {
    case "today":
        $tb = $today_tb;
        $te = $today_te;
        $time_label = iconv("UTF-8", "GBK", "今天");
        break;
    case "yesterday":
        $tb = $yesterday_tb;
        $te = $today_tb;
        $time_label = iconv("UTF-8", "GBK", "昨天");
        break;
    case "thismonth":
        $tb = $month_tb;
        $te = $month_te;
        $time_label = iconv("UTF-8", "GBK", "本月");
        break;
    case "lastmonth":
        $tb = $lastmonth_tb;
        $te = $lastmonth_te;
        $time_label = iconv("UTF-8", "GBK", "上月");
        break;
    default:
        $tb = $today_tb;
        $te = $today_te;
        $time_label = iconv("UTF-8", "GBK", "今天");
}

$where = array();
$where[] = "1";
if (!$debug_mode) {
    $read_parts = get_manage_part();
    $manage_parts = explode(",", $read_parts);
    if ($uinfo["part_admin"] || $uinfo["part_manage"]) {
        $where[] = "(part_id in (" . $read_parts . ") or binary author='" . $realname . "')";
    } else {
        $where[] = "binary author='" . $realname . "'";
    }
}

if ($uinfo["character_id"] == 46) {
    $where[] = " author = '" . $realname . "' ";
}

if ($media != "") {
    $where[] = "media_from='" . addslashes($media) . "'";
}

$sqlwhere = implode(" and ", $where);

$all_content = 0;
$all_come = 0;
$all_not = 0;

foreach ($hospital_ids as $hospital_id) {
    $table = "patient_" . $hospital_id;
    $content = $db->query("select count(*) as count from $table where $sqlwhere and order_date>=$tb and order_date<$te and status<>3", 1, "count");
    $come_count = $db->query("select count(*) as count from $table where $sqlwhere and order_date>=$tb and order_date<$te and status=1", 1, "count");
    $all_content += $content;
    $all_come += $come_count;
    $all_not += ($content - $come_count);
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html;charset=gb2312">
    <title><?php echo $title; ?> - <?php echo $time_label; ?><?php echo iconv("UTF-8", "GBK", "统计"); ?></title>
    <link href="../../css/bootstrap.min.css" rel="stylesheet">
    <link href="../../css/style.min.css" rel="stylesheet">
</head>
<body>
<div class="container-fluid" style="padding:20px;">
    <div class="row">
        <div class="col-md-12">
            <div class="ibox">
                <div class="ibox-title">
                    <h3><?php echo $title; ?> - <?php echo $time_label; ?><?php echo iconv("UTF-8", "GBK", "统计详情"); ?></h3>
                </div>
                <div class="ibox-content">
                    <div class="row" style="margin-bottom:20px;">
                        <div class="col-md-12">
                            <strong><?php echo iconv("UTF-8", "GBK", "统计条件："); ?></strong>
                            <?php echo iconv("UTF-8", "GBK", "时间："); ?><?php echo $time_label; ?> |
                            <?php if ($come == 1): ?><?php echo iconv("UTF-8", "GBK", "状态：已到诊"); ?><?php elseif ($come == 0): ?><?php echo iconv("UTF-8", "GBK", "状态：未到诊"); ?><?php else: ?><?php echo iconv("UTF-8", "GBK", "状态：全部"); ?><?php endif; ?>
                            <?php if ($media): ?> | <?php echo iconv("UTF-8", "GBK", "媒体来源："); ?><?php echo $media; ?><?php endif; ?>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-3">
                            <div class="panel panel-primary">
                                <div class="panel-heading text-center"><h4><?php echo iconv("UTF-8", "GBK", "总人数"); ?></h4></div>
                                <div class="panel-body text-center" style="font-size:36px;"><strong><?php echo $all_content; ?></strong></div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="panel panel-success">
                                <div class="panel-heading text-center"><h4><?php echo iconv("UTF-8", "GBK", "已到诊"); ?></h4></div>
                                <div class="panel-body text-center" style="font-size:36px;"><strong><?php echo $all_come; ?></strong></div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="panel panel-warning">
                                <div class="panel-heading text-center"><h4><?php echo iconv("UTF-8", "GBK", "未到诊"); ?></h4></div>
                                <div class="panel-body text-center" style="font-size:36px;"><strong><?php echo $all_not; ?></strong></div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="panel panel-info">
                                <div class="panel-heading text-center"><h4><?php echo iconv("UTF-8", "GBK", "到诊率"); ?></h4></div>
                                <div class="panel-body text-center" style="font-size:36px;"><strong><?php echo $all_content > 0 ? round($all_come / $all_content * 100, 2) : 0; ?>%</strong></div>
                            </div>
                        </div>
                    </div>

                    <div class="row" style="margin-top:30px;">
                        <div class="col-md-12">
                            <h4><?php echo iconv("UTF-8", "GBK", "各医院统计明细"); ?></h4>
                            <table class="table table-bordered table-striped">
                                <thead><tr><th><?php echo iconv("UTF-8", "GBK", "医院ID"); ?></th><th><?php echo iconv("UTF-8", "GBK", "总人数"); ?></th><th><?php echo iconv("UTF-8", "GBK", "已到诊"); ?></th><th><?php echo iconv("UTF-8", "GBK", "未到诊"); ?></th><th><?php echo iconv("UTF-8", "GBK", "到诊率"); ?></th></tr></thead>
                                <tbody>
                                    <?php foreach ($hospital_ids as $hospital_id) {
                                        $table = "patient_" . $hospital_id;
                                        $content = $db->query("select count(*) as count from $table where $sqlwhere and order_date>=$tb and order_date<$te and status<>3", 1, "count");
                                        $come_count = $db->query("select count(*) as count from $table where $sqlwhere and order_date>=$tb and order_date<$te and status=1", 1, "count");
                                        $not_count = $content - $come_count;
                                        $rate = $content > 0 ? round($come_count / $content * 100, 2) : 0;
                                    ?>
                                    <tr><td><?php echo $hospital_id; ?></td><td><?php echo $content; ?></td><td><?php echo $come_count; ?></td><td><?php echo $not_count; ?></td><td><?php echo $rate; ?>%</td></tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="row" style="margin-top:30px;">
                        <div class="col-md-12 text-center">
                            <button class="btn btn-default" onclick="window.close();"><?php echo iconv("UTF-8", "GBK", "关闭页面"); ?></button>
                            <button class="btn btn-primary" onclick="window.history.back();"><?php echo iconv("UTF-8", "GBK", "返回上一页"); ?></button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="../../js/jquery.min.js"></script>
<script src="../../js/plugins/layer/layer.min.js"></script>
</body>
</html>