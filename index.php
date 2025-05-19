<?php
ini_set('error_reporting', E_ALL);
ini_set('display_errors', true);

require_once 'library.php';

$tableReportName = getTableReport();
$tableDomainName = getTableDomain();
$reptDate = getLatestReportDate();
$tableQuestionName = getTableQuestion();
//$table_date = substr($tableReportName, 13, 2) . '.' . substr($tableReportName, 11, 2) . '.' . substr($tableReportName, 7, 4);
$maxReportDate = substr($reptDate, 0, 4) . '-' . substr($reptDate, 4, 2) . '-' . substr($reptDate, 6, 2);
////debug_to_console($maxReportDate);
$reportDate = $maxReportDate;
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['rep_date'])) {

    $reportDate = $_POST['rep_date'];
    debug_to_console('POST: ' . $reportDate);
}

////debug_to_console($reportDate);
//$table_time = substr($tableReportName, 16, 2) . ':' . substr($tableReportName, 18, 2) . ':' . substr($tableReportName, 20, 2);
////debug_to_console("GraphData: " . date('d-m-Y H:i:s:u', time()) . " start");
$graph_data = getGraphData($tableReportName, $tableDomainName, $reportDate);
////debug_to_console("GraphData: " . date('d-m-Y H:i:s:u', time()) . " end");
$question_data = getListFromDatabase("SELECT * FROM `" . $tableQuestionName . "`");
//debug_to_console("QuestionData: " . date('d-m-Y H:i:s:u', time()) . " end");

$wd_total = getWorkDays('01.02.2025', '30.06.2025');
$wd_now = getWorkDays('01.02.2025', $reportDate);
$wd_percentage = round($wd_now / $wd_total * 100, 0);

$sql_all = "SELECT COUNT(`Serial_num`) as res FROM `" . $tableReportName . "`"
        . " UNION"
        . " SELECT COUNT(`Serial_num`) FROM `" . $tableReportName . "` WHERE `EndDate` > '0000-00-00' AND `EndDate` <= '" . $reportDate . "'"
        . " UNION"
        . " SELECT COUNT(`Serial_num`) FROM `" . $tableReportName . "` WHERE `CloseDate` > '0000-00-00' AND `CloseDate` <= '" . $reportDate . "'"
        . " UNION"
        . " SELECT COUNT(`Serial_num`) FROM `" . $tableReportName . "` WHERE `EndDate` = '" . $reportDate . "'"
        . " UNION"
        . " SELECT COUNT( DISTINCT`parent_id`) FROM `" . $tableReportName . "`"
        . " UNION"
        . " SELECT COUNT(arm_name) FROM " . $tableDomainName . " WHERE `domainDate` > '0000-00-00' AND `domainDate` <= '" . $reportDate . "'"
        . " UNION"
        . " SELECT COUNT(arm_name) FROM " . $tableDomainName . " WHERE `domainDate` >= '" . $reportDate . "' AND `domainDate` < DATE_ADD('" . $reportDate . "', INTERVAL 1 DAY)";
//debug_to_console($sql_all);
$res_all = getListFromDatabase($sql_all);

$res = $res_all[0]['res']; //getValueFromDatabase("SELECT COUNT(`Serial_num`) FROM `" . $tableReportName . "`");
$res_install = $res_all[1]['res']; //= getValueFromDatabase("SELECT COUNT(`Serial_num`) FROM `" . $tableReportName . "` WHERE `EndDate` > '0000-00-00' AND `EndDate` <= '" . $reportDate . "'");
$res_ok = $res_all[2]['res']; //getValueFromDatabase("SELECT COUNT(`Serial_num`) FROM `" . $tableReportName . "` WHERE `CloseDate` > '0000-00-00' AND `CloseDate` <= '" . $reportDate . "'");
$res_per_day = $res_all[3]['res']; //getValueFromDatabase("SELECT COUNT(`Serial_num`) FROM `" . $tableReportName . "` WHERE `EndDate` = '" . $reportDate . "'");
$res_req = $res_all[4]['res']; //getValueFromDatabase("SELECT COUNT( DISTINCT`parent_id`) as numReq FROM `" . $tableReportName . "`");
$res_d_all = $res_all[5]['res'];
$res_d_per_day = array_key_exists(6, $res_all) ? $res_all[6]['res'] : 0;
//debug_to_console("Today results: " . date('d-m-Y H:i:s:u', time()) . " end");

$cur_day = date('d', strtotime($reportDate));
$cur_month = date('n', strtotime($reportDate));
$str_date = "last day of " . date('M', strtotime($reportDate)) . " 2025";
$last_day = date('d', strtotime($str_date));
if ($cur_month < 2) {
    $res_bad = 0;
} else {
    $res_bad = round(4000 / $last_day * $cur_day) + 4000 * ($cur_month - 2);
}

$res_speed = (20000 - $res_install) / (getWorkDays($reportDate, '30.06.2025') - 1);
$res_r = round($res_speed, 0);
if ($res_r < $res) {
    $res_r += 1;
}
//debug_to_console('getWorkDays('.$reportDate.', '.$str_date.')');
$div = getWorkDays($reportDate, $str_date) > 1 ? getWorkDays($reportDate, $str_date) - 1 : 1;
$res_month = (4000 * ($cur_month - 1) - $res_install) / $div;
$res_mr = round($res_month, 0);
if ($res_mr < $res_month) {
    $res_mr += 1;
}
//debug_to_console("Prepare: " . date('d-m-Y H:i:s:u', time()) . " end");
?>

<!doctype html>
<html lang="ru">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width,initial-scale=1">

        <script src="https://cdn.jsdelivr.net/npm/@coreui/coreui@5.3.1/dist/js/bootstrap.bundle.min.js" integrity="sha384-+BCVVYepeVZB2ejb1RVKb+0hoxtMs9CEjephXP6c6DV482N/m/hwq5FevVLVsETI" crossorigin="anonymous"></script><script src="https://cdn.jsdelivr.net/npm/@coreui/coreui@5.3.1/dist/js/bootstrap.bundle.min.js" integrity="sha384-+BCVVYepeVZB2ejb1RVKb+0hoxtMs9CEjephXP6c6DV482N/m/hwq5FevVLVsETI" crossorigin="anonymous"></script>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>

        <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js" integrity="sha512-GsLlZN/3F2ErC5ifS5QtgpiJtWd43JWSuIgh7mbzZ8zBps+dvLusV+eNQATqgA/HdeKFVgA5v3S/cIrLF7QnIg==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
        <script src='https://code.jquery.com/jquery-3.7.0.min.js'></script>
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script src="js/library.js"></script>


        <!-- coreUI for Bootstrap CSS -->
        <link href="https://cdn.jsdelivr.net/npm/@coreui/coreui@5.3.1/dist/css/themes/bootstrap/bootstrap.min.css" rel="stylesheet" integrity="sha384-leftDVIOA1OKkBjlGo3RtR+u4nHJPna0xV0zW3hSS3Os38Y84NF5bYB8/hOnHbfj" crossorigin="anonymous"><link href="https://cdn.jsdelivr.net/npm/@coreui/coreui@5.3.1/dist/css/themes/bootstrap/bootstrap.min.css" rel="stylesheet" integrity="sha384-leftDVIOA1OKkBjlGo3RtR+u4nHJPna0xV0zW3hSS3Os38Y84NF5bYB8/hOnHbfj" crossorigin="anonymous">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">

        <!-- Icons -->
        <link rel="stylesheet" href="https://www.unpkg.com/@coreui/icons/css/all.min.css">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.9.1/font/bootstrap-icons.css">
        <!--Локальные стили-->
        <title>Статистика установки АРМ</title>
    </head>
    <style>
        .chart-container {
            width: 90%;
            height: 100%;
            margin: auto;
        }
        .expand_chevrone[rows-expanded="false"] .chevrone::after {
            content: url('pic/cil-plus.svg');
            display: inline-block;
            width: 15px;
            height: 15px;
            margin-left: 5px;
        }
        .expand_chevrone[rows-expanded="true"] .chevrone::after {
            content: url('pic/cil-minus.svg');
            display: inline-block;
            width: 15px;
            height: 15px;
            margin-left: 5px;
        }
        .zero_arm_subj[hide-arm="true"]{
            display:none;
        }
        .zero_arm_subj[hide-arm="false"]{
            display:table-row;
        }
        .collapse_row[show-row="true"]{
            display:table-row;
        }
        .collapse_row[show-row="false"]{
            display:none;
        }
        .collapse_row [show-row="false"][hide-arm="true"]{
            display:none;
        }
        .collapse_row [show-row="false"][hide-arm="flase"]{
            display:none;
        }
        .collapse_row[show-row="true"][hide-arm="true"]{
            display:none;
        }
        .collapse_row[show-row="true"][hide-arm="flase"]{
            display:table-row;
        }

        .rotate {
            -moz-transform: rotate(90deg);
            -webkit-transform: rotate(90deg);
            -o-transform: rotate(90deg);
            -ms-transform: rotate(90deg);
            transform: rotate(90deg);
        }
    </style>
    <body class="text-center p-3 m-0 border-0 docs-example m-0 border-0">
        <div class="collapse" id="navbarToggleExternalContent">
            <div class="p-4" style="background-color: #e3e8ec;">
                <a class="btn btn-outline-dark"  href="LoadReport.php" role="button">Загрузка файла отчёта</a> <a class="btn btn-outline-dark" href="LoadQuestions.php" role="button">Загрузка файла с вопросами</a> <a type="button" id="buttonPDF" class="btn btn-outline-dark">Сгенерировать отчёт</a><a type="button" id="buttonMAP" href="map.php" class="btn btn-outline-dark">Посмотреть на карте</a>
            </div>
        </div>        
        <nav class="navbar" style="background-color: #e3e8ec;">
            <div class="container-fluid">
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarToggleExternalContent" aria-controls="navbarToggleExternalContent" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
            </div>
        </nav>
        <div class="container position-absolute start-50 translate-middle-x" style="width:90%" id="makepdf1">
            <div id ="makepdf">
                <br> 
                <div class="row">
                    <h1 class="fs-2 fw-bold" style="color:#263967;">Статус работ по установке и сборке, пуско-наладке автоматизированных рабочих мест</h1>
                </div>
                <div class="row">
                    <div class="progress-group">
                        <div class="progress-group-header align-items-end">
                            <i class="cil-calendar icon icon-xl progress-group-icon me-2"></i>
                            <div class="font-weight-bold me-2 align-bottom">Сроки исполнения работ: 01.02.2025 - 30.06.2025</div>
                            <form class="input-group ms-auto font-weight-bold me-2" style="width:30%;" method="post">
                                <label class="input-group-text font-weight-bold" for="rep_date" id="inputGroupreportDate" style>Дата отчета:</label>
                                <input class="form-control font-weight-bold" id="rep_date" name="rep_date" type="date" value="<?= $reportDate ?>" min="2025-02-01" max="<?= $maxReportDate ?>"/>
                                <button class="btn btn-outline-secondary" type="submit" id="change_rep_date" style="background: url(pic/cil-calendar-check.svg); background-repeat: no-repeat; background-position: center;"></button>
                            </form>
                        </div>
                        <div class="progress-group-bars bg-transparent" style="height:14px;">
                            <div class="progress-stacked bg-transparent" style="height:14px;">
                                <div class="progress" role="progressbar"  style="width:<?= $wd_percentage ?>%; height:14px;" aria-valuenow="<?= $wd_percentage ?>" aria-valuemin="0" aria-valuemax="100">
                                    <div class="progress-bar bg-warning-subtle text-muted small">Прошло <?= $wd_now ?> из <?= $wd_total ?> (<?= $wd_percentage ?>%)</div>
                                </div>
                                <div class="progress" role="progressbar"  style="width:<?= 100 - $wd_percentage ?>%; height:14px;" aria-valuenow="<?= 100 - $wd_percentage ?>" aria-valuemin="0" aria-valuemax="100">
                                    <div class="progress-bar bg-success-subtle text-muted small">Осталось <?= $wd_total - $wd_now ?> из <?= $wd_total ?> (<?= 100 - $wd_percentage ?>%)</div>
                                </div>

                            </div>
                        </div>
                    </div>
                    <div class="progress-group">
                        <div class="progress-group-header align-items-end">
                            <i class="cil-screen-desktop icon icon-xl progress-group-icon me-2"></i>
                            <div class="font-weight-bold me-2 align-bottom">Введено в домен <?= number_format($res_d_all, 0, ",", " ") ?> АРМ из <?= number_format($res_bad, 0, ",", " ") ?> по плану</div>
                            <div class="ms-auto font-weight-bold me-2">Получено <?= number_format($res_req, 0, ",", " ") ?> заявок на установку <?= number_format($res, 0, ",", " ") ?> АРМ из 20 000</div>
                        </div>
                        <div class="progress-group-bars bg-transparent" style="height:14px;">
                            <div class="progress-stacked bg-transparent" style="height:14px;">
                                <?php
                                $installed = ($res_d_all > $res_bad) ? $res_d_all : $res_bad;
                                if ($res_d_all <= $res_bad) {
                                    ?>
                                    <!-- В домене ($res_d_all)/20000 -->
                                    <div class="progress" role="progressbar" aria-valuenow="<?= ($res_d_all) / 200 ?>" aria-valuemin="0" aria-valuemax="100" style="width: <?= ($res_d_all) / 200 ?>%; height:14px;">
                                        <div class="progress-bar bg-success-subtle text-muted small"><?= number_format(($res_d_all), 0, ",", " ") ?></div>
                                    </div>
                                    <!-- По плану ($res_bad-$res_d_all)/20000 -->
                                    <div class="progress" role="progressbar" aria-valuenow="<?= ($res_bad - $res_d_all) / 200 ?>" aria-valuemin="0" aria-valuemax="100" style="width: <?= ($res_bad - $res_d_all) / 200 ?>%; height:14px;">
                                        <div class="progress-bar bg-danger-subtle text-muted small"><?= number_format(($res_bad - $res_d_all), 0, ",", " ") ?></div>
                                    </div>
                                <?php } else { ?>
                                    <!-- По плану ($res_bad)/20000 
                                    <div class="progress" role="progressbar" aria-valuenow="<?= $res_bad / 200 ?>" aria-valuemin="0" aria-valuemax="100" style="width: <?= $res_bad / 200 ?>%; height:14px;">
                                        <div class="progress-bar bg-info-subtle text-muted small"><?= number_format($res_bad, 0, ",", " ") ?></div>
                                    </div>
                                    -->
                                    <!-- По плану ($res_d_all - $res_install-$res_bad)/20000 -->
                                    <div class="progress" role="progressbar" aria-valuenow="<?= ($res_d_all) / 200 ?>" aria-valuemin="0" aria-valuemax="100" style="width: <?= ($res_d_all) / 200 ?>%; height:14px;">
                                        <div class="progress-bar bg-success-subtle text-muted small"><?= number_format(($res_d_all), 0, ",", " ") ?></div>
                                    </div>
                                <?php } ?>
                                <?php if ($res < 20000) { ?>
                                    <!-- Заявок-->
                                    <div class="progress" role="progressbar" aria-valuenow="<?= ($res - $installed) / 200 ?>" aria-valuemin="0" aria-valuemax="100" style="width: <?= ($res - $installed) / 200 ?>%; height:14px;">
                                        <div class="progress-bar bg-info-subtle text-muted small"><?= number_format($res - $installed, 0, ",", " ") ?></div>
                                    </div>
                                    <!-- Без заявок-->
                                    <div class="progress" role="progressbar" aria-valuenow="<?= (20000 - $res) / 200 ?>" aria-valuemin="0" aria-valuemax="100" style="width: <?= (20000 - $res) / 200 ?>%; height:14px;">
                                        <div class="progress-bar bg-success-subtle text-muted small"><?= number_format(20000 - $res, 0, ",", " ") ?></div>
                                    </div>
                                <?php } else { ?>
                                    <!-- Заявок-->
                                    <div class="progress" role="progressbar" aria-valuenow="<?= (20000 - $installed) / 200 ?>" aria-valuemin="0" aria-valuemax="100" style="width: <?= (20000 - $installed) / 200 ?>%; height:14px;">
                                        <div class="progress-bar bg-info-subtle text-muted small"><?= number_format(20000 - $installed, 0, ",", " ") ?></div>
                                    </div>
                                <?php } ?>
                            </div>
                        </div>
                        <div class="progress-group-header align-items-end">
                            <div class="align-bottom">
                                <span class="small font-weight-bold">Легенда:&nbsp;&nbsp;</span> 
                                <i class="cis-square icon progress-group-icon bg-success-subtle"></i>
                                <span class="small">- Введено в домен &nbsp;&nbsp;</span>
                                <i class="cis-square icon progress-group-icon bg-danger-subtle"></i>
                                <span class=" small">- Просрочено&nbsp;&nbsp;</span>
                                <i class="cis-square icon progress-group-icon bg-info-subtle"></i>
                                <span class="small">- В работе&nbsp;&nbsp;</span>
                                <?php if ($res < 20000) { ?>
                                    <i class="cis-square icon progress-group-icon bg-warning-subtle"></i>
                                    <span class="small">- Не в работе</span>
                                <?php } ?>
                            </div>
                        </div>
                    </div>
                    <div class="progress-group">
                        <div class="progress-group-header align-items-end">
                            <i class="cil-task icon icon-xl progress-group-icon me-2"></i>
                            <div class="font-weight-bold me-2 align-bottom">Собрано актов: <?= number_format($res_ok, 0, ",", " ") ?>, на проверке <?= number_format(-$res_ok + $res_install, 0, ",", " ") ?> актов.</div>
                            <div class="ms-auto font-weight-bold me-2">Актов в работе: <?= number_format(($res_d_all - $res_install), 0, ",", " ") ?></div>
                        </div>
                        <div class="progress-group-bars bg-transparent" style="height:14px;">
                            <div class="progress-stacked bg-transparent" style="height:14px;">
                                <div class="progress" role="progressbar" style="width:<?= round($res_ok / $res_d_all * 100, 0) ?>%; height:14px;" aria-valuenow="<?= round($res_ok / $res_d_all * 100, 0) ?>" aria-valuemin="0" aria-valuemax="100">
                                    <div class="progress-bar bg-success-subtle text-muted small" ><?= number_format($res_ok, 0, ",", " ") ?></div>
                                </div>
                                <div class="progress" role="progressbar" style="width:<?= round(($res_install - $res_ok) / $res_d_all * 100, 0) ?>%; height:14px;" aria-valuenow="<?= round(($res_install - $res_ok) / $res_d_all * 100, 0) ?>" aria-valuemin="0" aria-valuemax="100">
                                    <div class="progress-bar bg-warning-subtle text-muted small" ><?= number_format($res_install - $res_ok, 0, ",", " ") ?></div>
                                </div>
                                <div class="progress" role="progressbar" style="width:<?= round(($res_d_all - $res_install) / $res_d_all * 100, 0) ?>%; height:14px;" aria-valuenow="<?= round(($res_d_all - $res_install) / $res_d_all * 100, 0) ?>" aria-valuemin="0" aria-valuemax="100">
                                    <div class="progress-bar bg-danger-subtle text-muted small" ><?= number_format(($res_d_all - $res_install), 0, ",", " ") ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="progress-group">
                        <div class="progress-group-header align-items-end">
                            <i class="cil-speedometer icon icon-xl progress-group-icon me-2"></i>
                            <div class="font-weight-bold me-2 align-bottom">Скорость установок АРМ</div>
                        </div>
                        <div class="progress-group-bars bg-transparent">
                            <div class="progress bg-transparent" style="height:4px;">
                                <div class="progress-bar bg-info-subtle text-muted small" role="progressbar" style="width: <?= 198 / max($res_mr, $res_r, $res_per_day, $res_d_per_day, 198) * 100 ?>%" aria-valuenow="<?= 198 / max($res_mr, $res_r, $res_per_day, $res_d_per_day, 198) * 100 ?>" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                            <div class="progress bg-transparent" style="height:4px;">
                                <div class="progress-bar bg-success text-muted small" role="progressbar" style="width: <?= $res_d_per_day / max($res_mr, $res_r, $res_per_day, $res_d_per_day, 198) * 100 ?>%" aria-valuenow="<?= $res_per_day / max($res_mr, $res_r, $res_per_day, $res_d_per_day, 198) * 100 ?>" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                            <div class="progress bg-transparent" style="height:4px;">
                                <div class="progress-bar bg-success-subtle text-muted small" role="progressbar" style="width: <?= $res_per_day / max($res_mr, $res_r, $res_per_day, $res_d_per_day, 198) * 100 ?>%" aria-valuenow="<?= $res_per_day / max($res_mr, $res_r, $res_per_day, $res_d_per_day, 198) * 100 ?>" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                            <div class="progress bg-transparent" style="height:4px;">
                                <div class="progress-bar bg-warning-subtle text-muted small" role="progressbar" style="width: <?= $res_mr / max($res_mr, $res_r, $res_per_day, $res_d_per_day, 198) * 100 ?>%" aria-valuenow="<?= $res_mr / max($res_mr, $res_r, $res_per_day, $res_d_per_day, 198) * 100 ?>" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                            <div class="progress bg-transparent" style="height:4px;">
                                <div class="progress-bar bg-danger-subtle text-muted small" role="progressbar" style="width: <?= $res_r / max($res_mr, $res_r, $res_per_day, $res_d_per_day, 198) * 100 ?>%" aria-valuenow="<?= $res_r / max($res_mr, $res_r, $res_per_day, $res_d_per_day, 198) * 100 ?>" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>  
                        </div>
                        <div class="progress-group-header align-items-end">
                            <div class="align-bottom">
                                <span class="small font-weight-bold">Скорость установки АРМ в день:&nbsp;&nbsp;</span> 
                                <i class="cis-square icon progress-group-icon bg-info-subtle"></i>
                                <span class="small">- план (198)&nbsp;&nbsp;&nbsp;&nbsp;</span>
                                <i class="cis-square icon progress-group-icon bg-success"></i>
                                <span class=" small">-факт по вводу (<?= $res_d_per_day ?>)&nbsp;&nbsp;</span>
                                <i class="cis-square icon progress-group-icon bg-success-subtle"></i>
                                <span class=" small">-факт по актам (<?= $res_per_day ?>)&nbsp;&nbsp;</span>
                                <i class="cis-square icon progress-group-icon bg-warning-subtle"></i>
                                <span class="small">- для 4 000 в месяц (<?= $res_mr ?>)&nbsp;&nbsp;</span>
                                <i class="cis-square icon progress-group-icon bg-danger-subtle"></i>
                                <span class="small">- для 20 000 до 30.06.25 (<?= $res_r ?>)</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row beforeClass">
                    <h1 class="fs-2 fw-bold" style="color:#263967;">Статус в динамике</h1>
                </div>
                <div class="row card chart-container">
                    <canvas id="chart"></canvas>
                    <br><br>
                </div>
                <div class="row beforeClass">
                    <div class="col">
                        <h1 class="fs-2 fw-bold" style="color:#263967;">Открытые вопросы</h1>
                    </div>
                </div>
                <div class="row text-center">
                    <table class="table table-sm text-center table-hover">
                        <thead>
                            <tr>
                                <th scope="col">№ П/п</th>
                                <th scope="col">Категория</th>
                                <th scope="col">Системные вопросы по инсталляции АРМ</th>
                                <th scope="col">Принятые меры</th>
                                <th scope="col">Ответственный</th>
                                <th scope="col">Ожидаемый срок устранения проблемы</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            //debug_to_console("Questions: " . date('d-m-Y H:i:s:u', time()) . " start");
                            foreach ($question_data as $row) {
                                if ($row['planDate'] > $reportDate) {
                                    echo '<tr class="table-default"><td>' . implode('</td><td>', $row) . '</td></tr>';
                                } else {
                                    echo '<tr class="table-danger"><td>' . implode('</td><td>', $row) . '</td></tr>';
                                }
                            }
                            //debug_to_console("Questions: " . date('d-m-Y H:i:s:u', time()) . " end");
                            ?>
                        </tbody>
                    </table>
                </div>
                <div class="row beforeClass">
                    <div class="col">
                        <h1 class="fs-2 fw-bold"  style="color:#263967;">Подробная статистика</h1>
                    </div>
                </div>
                <div class="row justify-content-end">
                    <div class="col-3 form-check form-switch">
                        <input class="form-check-input" type="checkbox" role="switch" id="flexSwitchCheckChecked" checked onClick="ShowZeroARMs()">
                        <label class="form-check-label" for="flexSwitchCheckChecked">Убрать объекты без инсталляций</label>
                    </div>
                </div>
                <div class="row ">
                    <div class="col">
                        <?php
                        debug_to_console("getListOfSubjects: " . date('d-m-Y H:i:s:v', time()) . " start");
                        echo getListOfSubjects($tableReportName, $tableDomainName, $reportDate);
                        debug_to_console("getListOfSubjects: " . date('d-m-Y H:i:s:v', time()) . " end");
                        ?>
                    </div>
                </div>

            </div>
        </div>
        <!--- Для генерации отчета --->
        <script>
//            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
//            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
//                return new bootstrap.Tooltip(tooltipTriggerEl);
//            });
            let button = document.getElementById("buttonPDF");
            let makepdf = document.getElementById("makepdf");
            var opt = {
                pagebreak: {before: '.beforeClass'},
                margin: 3,
                filename: 'Отчёт АРМ <?= $reportDate ?>.pdf',
                image: {type: 'jpeg', quality: 0.98},
                jsPDF: {orientation: 'l', unit: 'mm', format: 'a4'},
                html2canvas: {scale: 2}
            };
            button.addEventListener("click", function () {
                html2pdf().set(opt).from(makepdf).save();
            });
        </script>
        <!--- Для графика --->
        <script>
            const ctx = document.getElementById("chart").getContext('2d');
            const myChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: <?php echo json_encode($graph_data['labels']) ?>,
                    datasets: [
                        {
                            label: 'По ГК',
                            backgroundColor: 'rgba(0,0,0,0)',
                            borderColor: 'rgb(0, 0, 0)',
                            borderWidth: 1,
                            borderDash: [10, 10],
                            pointStyle: 'circle',
                            pointRadius: 0,
                            fill: false,
                            order: 5,
                            data: <?php echo json_encode($graph_data['total']) ?>,
                        },
                        {
                            label: 'Заявки',
                            backgroundColor: 'rgba(13, 110, 253, 0.1)',
                            borderColor: 'rgb(13, 110, 253)',
                            borderWidth: 1,
                            borderDash: [5, 5],
                            pointStyle: 'circle',
                            pointRadius: 0,
                            fill: true,
                            order: 4,
                            data: <?php echo json_encode($graph_data['requested']) ?>
                        },
                        {
                            label: 'План установок',
                            backgroundColor: 'rgba(229, 83, 83, 0)',
                            borderColor: 'rgb(229, 83, 83)',
                            borderWidth: 1,
                            pointStyle: 'circle',
                            pointRadius: 0,
                            stepped: false,
                            fill: false,
                            order: 3,
                            data: <?php echo json_encode($graph_data['error']) ?>
                        },
                        {
                            label: 'Введено в домен',
                            backgroundColor: 'rgba(203, 237, 214, 0.7)',
                            borderColor: 'rgb(25, 135, 84)',
                            borderWidth: 1,
                            pointStyle: 'circle',
                            pointRadius: 2,
                            fill: true,
                            order: 2,
                            data: <?php echo json_encode($graph_data['domain']) ?>
                        },
                        {
                            label: 'Актов подписано',
                            backgroundColor: 'rgba(0, 255, 0, 1)',
                            borderColor: 'rgb(50, 205, 50)',
                            borderWidth: 1,
                            pointStyle: 'circle',
                            pointRadius: 2,
                            fill: true,
                            order: 1,
                            data: <?php echo json_encode($graph_data['closed']) ?>
                        }
                    ]
                },
                options: {
                    plugins: {
                        legend: {
                            position: 'top'
                        },
                        title: {
                            display: true,
                            text: 'Статистика установким АРМ'
                        }
                    },
                    responsive: true,
                    interaction: {
                        mode: 'index',
                        intersect: false
                    },
                    elements: {
                        line: {
                            stepped: false
                        }
                    },
                    scales: {
                        x: {
                            min: '01.02.2025',
                            title: {
                                display: true,
                                text: 'Дата'
                            }
                        },
                        y: {
                            suggestedMax: 21000,
                            ticks: {
                                stepSize: 1000
                            },
                            title: {
                                display: true,
                                text: 'Количество АРМ'
                            }
                        }
                    }
                }
            });
        </script>
    </body>
</html>