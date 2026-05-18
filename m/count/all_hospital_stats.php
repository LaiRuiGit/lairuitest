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
        $begin_time = $today_tb;
        $end_time = $today_te;
        $time_label = convert_encoding("今天", "GBK");
        break;
    case "yesterday":
        $begin_time = $yesterday_tb;
        $end_time = $today_tb;
        $time_label = convert_encoding("昨天", "GBK");
        break;
    case "thismonth":
        $begin_time = $month_tb;
        $end_time = $month_te;
        $time_label = convert_encoding("本月", "GBK");
        break;
    case "lastmonth":
        $begin_time = $lastmonth_tb;
        $end_time = $lastmonth_te;
        $time_label = convert_encoding("上月", "GBK");
        break;
    default:
        $begin_time = $today_tb;
        $end_time = $today_te;
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

if ($come != -1) {
    if ($come == 1) {
        $where[] = "status in (1,4,5,6)";
    } else {
        $where[] = "status in (0,2)";
    }
}

$sqlwhere = implode(" and ", $where);

$status_array = array(
    0 => array("text" => convert_encoding("等待", "GBK"), "class" => "label label-primary"),
    1 => array("text" => convert_encoding("已到", "GBK"), "class" => "label label-success"),
    2 => array("text" => convert_encoding("未到", "GBK"), "class" => "label label-danger"),
    3 => array("text" => convert_encoding("过期", "GBK"), "class" => "label label-default"),
    4 => array("text" => convert_encoding("回访", "GBK"), "class" => "label label-warning"),
    5 => array("text" => convert_encoding("退款", "GBK"), "class" => "label label-info"),
    6 => array("text" => convert_encoding("全退", "GBK"), "class" => "label label-purple")
);

$hospital_id_name = $db->query("select id,name from hospital", 'id', 'name');
$disease_id_name = $db->query("select id,name from disease", 'id', 'name');

$pagesize = 20;
$page = isset($_GET["page"]) ? intval($_GET["page"]) : 1;

$all_count = 0;
$all_data = array();

foreach ($hospital_ids as $hospital_id) {
    $table = "patient_" . $hospital_id;
    $count = $db->query("select count(*) as count from $table where $sqlwhere and order_date>=$begin_time and order_date<$end_time", 1, "count");
    $all_count += $count;
    
    $offset = ($page - 1) * $pagesize;
    $data = $db->query("select *, $hospital_id as hospital_id from $table where $sqlwhere and order_date>=$begin_time and order_date<$end_time order by order_date desc limit $offset,$pagesize");
    $all_data = array_merge($all_data, $data);
}

usort($all_data, function($a, $b) {
    return $b["order_date"] - $a["order_date"];
});

$pagecount = max(ceil($all_count / $pagesize), 1);
$page = max(min($pagecount, $page), 1);

$param_str = "table_type=$table_type&show=$show";
if ($come != -1) $param_str .= "&come=$come";
if ($media != "") $param_str .= "&media=" . urlencode($media);
?>
<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html;charset=gb2312">
    <title><?php echo $title; ?> - <?php echo $time_label; ?><?php echo convert_encoding("患者列表", "GBK"); ?></title>
    <link href="../../css/bootstrap.min.css" rel="stylesheet">
    <link href="../../css/style.min.css" rel="stylesheet">
    <style type="text/css">
        .label-purple {
            background-color: #9b59b6;
        }
        table {
            font-size: 12px;
        }
        th, td {
            text-align: center;
            vertical-align: middle !important;
        }
    </style>
</head>
<body>
<div class="container-fluid" style="padding:20px;">
    <div class="row">
        <div class="col-md-12">
            <div class="ibox">
                <div class="ibox-title">
                    <h3><?php echo $title; ?> - <?php echo $time_label; ?><?php echo convert_encoding("患者列表", "GBK"); ?></h3>
                </div>
                <div class="ibox-content">
                    <div class="row" style="margin-bottom:20px;">
                        <div class="col-md-12">
                            <strong><?php echo convert_encoding("统计条件：", "GBK"); ?></strong>
                            <?php echo convert_encoding("时间：", "GBK"); ?><?php echo $time_label; ?> |
                            <?php if ($come == 1): ?><?php echo convert_encoding("状态：已到诊", "GBK"); ?><?php elseif ($come == 0): ?><?php echo convert_encoding("状态：未到诊", "GBK"); ?><?php else: ?><?php echo convert_encoding("状态：全部", "GBK"); ?><?php endif; ?>
                            <?php if ($media): ?> | <?php echo convert_encoding("媒体来源：", "GBK"); ?><?php echo $media; ?><?php endif; ?>
                            | <?php echo convert_encoding("总人数：", "GBK"); ?><strong><?php echo $all_count; ?></strong>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th><?php echo convert_encoding("姓名", "GBK"); ?></th>
                                    <th><?php echo convert_encoding("性别", "GBK"); ?></th>
                                    <th><?php echo convert_encoding("年龄", "GBK"); ?></th>
                                    <th><?php echo convert_encoding("电话", "GBK"); ?></th>
                                    <th><?php echo convert_encoding("QQ", "GBK"); ?></th>
                                    <th><?php echo convert_encoding("专家号", "GBK"); ?></th>
                                    <th><?php echo convert_encoding("咨询内容", "GBK"); ?></th>
                                    <th><?php echo convert_encoding("接待", "GBK"); ?></th>
                                    <th><?php echo convert_encoding("预约时间", "GBK"); ?></th>
                                    <th><?php echo convert_encoding("病患类型", "GBK"); ?></th>
                                    <th><?php echo convert_encoding("媒体来源", "GBK"); ?></th>
                                    <th><?php echo convert_encoding("关键词", "GBK"); ?></th>
                                    <th><?php echo convert_encoding("地区", "GBK"); ?></th>
                                    <th><?php echo convert_encoding("备注", "GBK"); ?></th>
                                    <th><?php echo convert_encoding("客服", "GBK"); ?></th>
                                    <th><?php echo convert_encoding("回访", "GBK"); ?></th>
                                    <th><?php echo convert_encoding("治疗费用", "GBK"); ?></th>
                                    <th><?php echo convert_encoding("赴约情况", "GBK"); ?></th>
                                    <th><?php echo convert_encoding("添加时间", "GBK"); ?></th>
                                    <th><?php echo convert_encoding("医院", "GBK"); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($all_data) > 0): ?>
                                    <?php foreach ($all_data as $row): ?>
                                        <tr>
                                            <td><?php echo $row["name"]; ?></td>
                                            <td><?php echo $row["sex"] == 1 ? convert_encoding("男", "GBK") : convert_encoding("女", "GBK"); ?></td>
                                            <td><?php echo $row["age"] > 0 ? $row["age"] : ""; ?></td>
                                            <td>
                                                <?php if ($uinfo["show_tel"] == 1 || $row["author"] == $realname || $username == 'admin'): ?>
                                                    <?php echo $row["tel"]; ?>
                                                <?php else: ?>
                                                    -
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo $row["qq"]; ?></td>
                                            <td><?php echo $row["zhuanjia_num"]; ?></td>
                                            <td><?php echo $row["content"]; ?></td>
                                            <td><?php echo $row["jiedai"]; ?></td>
                                            <td><?php echo date("Y-m-d H:i", $row["order_date"]); ?></td>
                                            <td><?php echo isset($disease_id_name[$row["disease_id"]]) ? $disease_id_name[$row["disease_id"]] : ""; ?></td>
                                            <td><?php echo $row["media_from"]; ?></td>
                                            <td><?php echo $row["engine_key"]; ?></td>
                                            <td><?php echo $row["is_local"] == 1 ? convert_encoding("本市", "GBK") : convert_encoding("外地", "GBK"); ?></td>
                                            <td><?php echo $row["memo"]; ?></td>
                                            <td><?php echo $row["author"]; ?></td>
                                            <td><?php echo $row["huifang"] > 0 ? '<span class="label label-success">'.convert_encoding("已回访", "GBK").'</span>' : '<span class="label label-default">'.convert_encoding("未回访", "GBK").'</span>'; ?></td>
                                            <td><?php echo $row["fee"]; ?></td>
                                            <td><span class="<?php echo $status_array[$row["status"]]["class"]; ?>"><?php echo $status_array[$row["status"]]["text"]; ?></span></td>
                                            <td><?php echo date("Y-m-d H:i", $row["addtime"]); ?></td>
                                            <td><?php echo isset($hospital_id_name[$row["hospital_id"]]) ? $hospital_id_name[$row["hospital_id"]] : $row["hospital_id"]; ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr><td colspan="20" class="text-center"><?php echo convert_encoding("暂无数据", "GBK"); ?></td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="row" style="margin-top:20px;">
                        <div class="col-md-12 text-center">
                            <nav>
                                <ul class="pagination">
                                    <?php if ($page > 1): ?>
                                        <li><a href="?<?php echo $param_str; ?>&page=1"><?php echo convert_encoding("首页", "GBK"); ?></a></li>
                                        <li><a href="?<?php echo $param_str; ?>&page=<?php echo $page - 1; ?>"><?php echo convert_encoding("上一页", "GBK"); ?></a></li>
                                    <?php endif; ?>
                                    <?php for ($i = max(1, $page - 2); $i <= min($pagecount, $page + 2); $i++): ?>
                                        <li <?php if ($i == $page) echo 'class="active"'; ?>><a href="?<?php echo $param_str; ?>&page=<?php echo $i; ?>"><?php echo $i; ?></a></li>
                                    <?php endfor; ?>
                                    <?php if ($page < $pagecount): ?>
                                        <li><a href="?<?php echo $param_str; ?>&page=<?php echo $page + 1; ?>"><?php echo convert_encoding("下一页", "GBK"); ?></a></li>
                                        <li><a href="?<?php echo $param_str; ?>&page=<?php echo $pagecount; ?>"><?php echo convert_encoding("尾页", "GBK"); ?></a></li>
                                    <?php endif; ?>
                                </ul>
                            </nav>
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