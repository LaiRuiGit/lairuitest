<?php
require "../../core/core.php";

$mod = "patient";
$table_type = isset($_GET["table_type"]) ? $_GET["table_type"] : "miaofang";
$show = isset($_GET["show"]) ? $_GET["show"] : "today";
$come = isset($_GET["come"]) ? intval($_GET["come"]) : -1;
$media = isset($_GET["media"]) ? $_GET["media"] : "";
$time_type = isset($_GET["time_type"]) ? $_GET["time_type"] : "order_date";

function convert_encoding($str, $to_encoding, $from_encoding = "UTF-8") {
    if (function_exists('iconv')) {
        return iconv($from_encoding, $to_encoding, $str);
    } elseif (function_exists('mb_convert_encoding')) {
        return mb_convert_encoding($str, $to_encoding, $from_encoding);
    } else {
        return $str;
    }
}

if ($table_type == "miaofang") {
    $hospital_ids = array(12, 13, 14, 15, 16, 17, 19, 20);
    $title = convert_encoding("苗方统计", "GBK");
} else {
    $hospital_ids = array(22, 23, 24, 25);
    $title = convert_encoding("痘艺美统计", "GBK");
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
        $time_label = convert_encoding("今天", "GBK");
        break;
    case "yesterday":
        $tb = $yesterday_tb;
        $te = $today_tb;
        $time_label = convert_encoding("昨天", "GBK");
        break;
    case "thismonth":
        $tb = $month_tb;
        $te = $month_te;
        $time_label = convert_encoding("本月", "GBK");
        break;
    case "lastmonth":
        $tb = $lastmonth_tb;
        $te = $lastmonth_te;
        $time_label = convert_encoding("上月", "GBK");
        break;
    default:
        $tb = $today_tb;
        $te = $today_te;
        $time_label = convert_encoding("今天", "GBK");
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
    if ($come == -1) {
        $content = $db->query("select count(*) as count from $table where $sqlwhere and order_date>=$tb and order_date<$te and status<>3", 1, "count");
        $come_count = $db->query("select count(*) as count from $table where $sqlwhere and order_date>=$tb and order_date<$te and status=1", 1, "count");
        $all_content += $content;
        $all_come += $come_count;
        $all_not += ($content - $come_count);
    } elseif ($come == 1) {
        $content = $db->query("select count(*) as count from $table where $sqlwhere and order_date>=$tb and order_date<$te and status=1", 1, "count");
        $all_come += $content;
        $all_content = $all_come;
        $all_not = 0;
    } else {
        $content = $db->query("select count(*) as count from $table where $sqlwhere and order_date>=$tb and order_date<$te and status<>3", 1, "count");
        $come_count = $db->query("select count(*) as count from $table where $sqlwhere and order_date>=$tb and order_date<$te and status=1", 1, "count");
        $not_count = $content - $come_count;
        $all_not += $not_count;
        $all_content = $all_not;
        $all_come = 0;
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html;charset=gb2312">
    <title><?php echo $title; ?> - <?php echo $time_label; ?><?php echo convert_encoding("统计", "GBK"); ?></title>
    <link href="../../css/bootstrap.min.css" rel="stylesheet">
    <link href="../../css/style.min.css" rel="stylesheet">
</head>
<body>
<div class="container-fluid" style="padding:20px;">
    <div class="row">
        <div class="col-md-12">
            <div class="ibox">
                <div class="ibox-title">
                    <h3><?php echo $title; ?> - <?php echo $time_label; ?><?php echo convert_encoding("统计详情", "GBK"); ?></h3>
                </div>
                <div class="ibox-content">
                    <div class="row" style="margin-bottom:20px;">
                        <div class="col-md-12">
                            <strong><?php echo convert_encoding("统计条件：", "GBK"); ?></strong>
                            <?php echo convert_encoding("时间：", "GBK"); ?><?php echo $time_label; ?> |
                            <?php if ($come == 1): ?><?php echo convert_encoding("状态：已到诊", "GBK"); ?><?php elseif ($come == 0): ?><?php echo convert_encoding("状态：未到诊", "GBK"); ?><?php else: ?><?php echo convert_encoding("状态：全部", "GBK"); ?><?php endif; ?>
                            <?php if ($media): ?> | <?php echo convert_encoding("媒体来源：", "GBK"); ?><?php echo $media; ?><?php endif; ?>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-3">
                            <div class="panel panel-primary">
                                <div class="panel-heading text-center"><h4><?php echo convert_encoding("总人数", "GBK"); ?></h4></div>
                                <div class="panel-body text-center" style="font-size:36px;"><strong><?php echo $all_content; ?></strong></div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="panel panel-success">
                                <div class="panel-heading text-center"><h4><?php echo convert_encoding("已到诊", "GBK"); ?></h4></div>
                                <div class="panel-body text-center" style="font-size:36px;"><strong><?php echo $all_come; ?></strong></div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="panel panel-warning">
                                <div class="panel-heading text-center"><h4><?php echo convert_encoding("未到诊", "GBK"); ?></h4></div>
                                <div class="panel-body text-center" style="font-size:36px;"><strong><?php echo $all_not; ?></strong></div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="panel panel-info">
                                <div class="panel-heading text-center"><h4><?php echo convert_encoding("到诊率", "GBK"); ?></h4></div>
                                <div class="panel-body text-center" style="font-size:36px;"><strong><?php echo $all_content > 0 ? round($all_come / $all_content * 100, 2) : 0; ?>%</strong></div>
                            </div>
                        </div>
                    </div>

                    <div class="row" style="margin-top:30px;">
                        <div class="col-md-12">
                            <h4><?php echo convert_encoding("各医院统计明细", "GBK"); ?></h4>
                            <table class="table table-bordered table-striped">
                                <thead><tr><th><?php echo convert_encoding("医院ID", "GBK"); ?></th><th><?php echo convert_encoding("总人数", "GBK"); ?></th><th><?php echo convert_encoding("已到诊", "GBK"); ?></th><th><?php echo convert_encoding("未到诊", "GBK"); ?></th><th><?php echo convert_encoding("到诊率", "GBK"); ?></th></tr></thead>
                                <tbody>
                                    <?php foreach ($hospital_ids as $hospital_id) {
                                        $table = "patient_" . $hospital_id;
                                        if ($come == -1) {
                                            $content = $db->query("select count(*) as count from $table where $sqlwhere and order_date>=$tb and order_date<$te and status<>3", 1, "count");
                                            $come_count = $db->query("select count(*) as count from $table where $sqlwhere and order_date>=$tb and order_date<$te and status=1", 1, "count");
                                            $not_count = $content - $come_count;
                                            $rate = $content > 0 ? round($come_count / $content * 100, 2) : 0;
                                        } elseif ($come == 1) {
                                            $content = $db->query("select count(*) as count from $table where $sqlwhere and order_date>=$tb and order_date<$te and status=1", 1, "count");
                                            $come_count = $content;
                                            $not_count = 0;
                                            $rate = 100;
                                        } else {
                                            $content = $db->query("select count(*) as count from $table where $sqlwhere and order_date>=$tb and order_date<$te and status<>3", 1, "count");
                                            $come_count = $db->query("select count(*) as count from $table where $sqlwhere and order_date>=$tb and order_date<$te and status=1", 1, "count");
                                            $not_count = $content - $come_count;
                                            $content = $not_count;
                                            $come_count = 0;
                                            $rate = 0;
                                        }
                                    ?>
                                    <tr><td><?php echo $hospital_id; ?></td><td><?php echo $content; ?></td><td><?php echo $come_count; ?></td><td><?php echo $not_count; ?></td><td><?php echo $rate; ?>%</td></tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="row" style="margin-top:30px;">
                        <div class="col-md-12 text-center">
                            <button class="btn btn-default" onclick="window.close();"><?php echo convert_encoding("关闭页面", "GBK"); ?></button>
                            <button class="btn btn-primary" onclick="window.history.back();"><?php echo convert_encoding("返回上一页", "GBK"); ?></button>
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