<?php

//ini_set('error_reporting', E_ALL);
//ini_set('display_errors', true);

require_once 'SimpleXLSX.php';
require_once 'SimpleXLSXGen.php';

//require_once 'spout-3.3.0/src/Spout/Autoloader/autoload.php';

use Shuchkin\SimpleXLSX;

//use Box\Spout\Reader\Common\Creator\ReaderEntityFactory;

$API_key = '01a843aa-81bf-42d8-aa71-b9507294f01d';
$API_key_new = '84071acc-62c3-41b1-b499-9916054a9b17';

$color_h = "#8600ff";
$color_hd = "#263967";

// Функция для отправки данных о прогрессе
function send_progress($progress, $message = '', $element_id = 'progress_load') {
    session_start();
    $_SESSION['upload_progress'] = $progress;
    $_SESSION['message_progress'] = $message;
    $_SESSION['element_id'] = $element_id;
    session_write_close();
}

function getWorkDays($date_start, $date_end) {
    $holidays_2024 = array('01.01.2024', '02.01.2024', '03.01.2024', '04.01.2024', '05.01.2024', '06.01.2024', '07.01.2024', '08.01.2024', '23.02.2024', '08.03.2024', '30.04.2024', '01.05.2024', '09.05.2024', '10.05.2024', '12.06.2024', '31.12.2024');
    $holidays_2025 = array('01.01.2025', '02.01.2025', '03.01.2025', '06.01.2025', '07.01.2025', '08.01.2025', '01.05.2025', '02.05.2025', '08.05.2025', '09.05.2025', '12.06.2025', '14.06.2025', '03.11.2025', '04.11.2025', '31.12.2025');
    $workdays_2025 = array('01.11.2025');

    $interval_date = date_diff(date_create($date_start), date_create($date_end))->days + 1;
    $days_count = 0;
    for ($i = 1; $i <= $interval_date; $i++) {
        $date_new = strtotime($date_start) + 86400 * ($i - 1);
        $dt_new = date('d.m.Y', $date_new);
        $day_of_week = date('w', $date_new);
        if ($day_of_week <> 0 AND $day_of_week <> 6 AND!in_array($dt_new, $holidays_2025) AND!in_array($dt_new, $holidays_2024)) {
            $days_count += 1;
        }
        if (in_array($dt_new, $workdays_2025)) {
            $days_count += 1;
        }
    }
    return $days_count;
}

function getTableReport() {
    $res = getValueFromDatabase("SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_TYPE = 'BASE TABLE' AND TABLE_SCHEMA='gp_data' and TABLE_NAME like 'report%' ORDER BY TABLE_NAME DESC LIMIT 1");
    return $res["TABLE_NAME"];
}

function getTableDomain() {
    $res = getValueFromDatabase("SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_TYPE = 'BASE TABLE' AND TABLE_SCHEMA='gp_data' and TABLE_NAME like 'domain%' ORDER BY TABLE_NAME DESC LIMIT 1");
    return $res["TABLE_NAME"];
}

function getLatestReportDate() {
    $res = getValueFromDatabase("SELECT LEFT(RIGHT(TABLE_NAME,23),8) as tbl_name FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_TYPE = 'BASE TABLE' AND TABLE_SCHEMA='gp_data' and (TABLE_NAME like 'report%' OR TABLE_NAME like 'domain%') ORDER BY tbl_name DESC LIMIT 1;");
    return $res["tbl_name"];
}

function getTableQuestion() {
    $res = getValueFromDatabase("SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_TYPE = 'BASE TABLE' AND TABLE_SCHEMA='gp_data' and TABLE_NAME like 'question%' ORDER BY TABLE_NAME DESC LIMIT 1");
    return $res["TABLE_NAME"];
}

function getListFromDatabase($querry) {
    // Подключение к базе данных
    $conn = mysqli_connect("localhost", "gpbduser", "gpbdCm611eT!", "gp_data");

    // Проверка соединения
    if (!$conn) {
        die("Connection failed: " . mysqli_connect_error());
    }

    // Выборка данных из таблицы
    $result = mysqli_query($conn, $querry);

    while ($row = mysqli_fetch_assoc($result)) {
        $value[] = $row;
    }

    // Закрытие соединения
    mysqli_close($conn);

    return $value;
}

function DataBaseRequestWitoutResult($querry) {
    // Подключение к базе данных
    $conn = mysqli_connect("localhost", "gpbduser", "gpbdCm611eT!", "gp_data");

    // Проверка соединения
    if (!$conn) {
        die("Connection failed: " . mysqli_connect_error());
    }

    // Выборка данных из таблицы
    $result = mysqli_query($conn, $querry);

    // Закрытие соединения
    mysqli_close($conn);

    return $result;
}

function getValueFromDatabase($querry) {
    // Подключение к базе данных
    $conn = mysqli_connect("localhost", "gpbduser", "gpbdCm611eT!", "gp_data");

    // Проверка соединения
    if (!$conn) {
        die("Connection failed: " . mysqli_connect_error());
    }

    // Выборка данных из таблицы
    $result = mysqli_query($conn, $querry);

    // Получение значения из результата
    while ($row = mysqli_fetch_assoc($result)) {
        $value = $row;
    }

    // Закрытие соединения
    mysqli_close($conn);

    return $value;
}

function debug_to_console($data, $context = 'Debug in Console') {

    // Buffering to solve problems frameworks, like header() in this and not a solid return.
    ob_start();

    $output = 'console.info(\'' . $context . ':\');';
    $output .= 'console.log(' . json_encode($data) . ');';
    $output = sprintf('<script>%s</script>', $output);

    echo $output;
}

function getGraphData($tblName, $dtblName, $repDate) {
    $resArray = array();

    // получим первую дату регистрации
    $minDate = getValueFromDatabase("SELECT MIN(`registrationDate`) as minDate FROM `" . $tblName . "`")['minDate'];
    // получим список заявок
    $listArm = getListFromDatabase("SELECT `registrationDate`, COUNT(`id`) as SN FROM `" . $tblName . "` GROUP BY `registrationDate` ORDER BY `registrationDate`");
    $numARM = 0;
    $listInstalled = getListFromDatabase("SELECT `EndDate`, COUNT(`id`) as SN FROM `" . $tblName . "` GROUP BY `EndDate` ORDER BY `EndDate`");
    $listClosed = getListFromDatabase("SELECT `CloseDate`, COUNT(`id`) as SN FROM `" . $tblName . "` GROUP BY `CloseDate` ORDER BY `CloseDate`");
    $listInDomain = getListFromDatabase("SELECT CONVERT(domainDate, DATE) as DomainDate, count(arm_name) as SN FROM " . $dtblName . " GROUP BY DomainDate order by DomainDate DESC;");
    //debug_to_console($listInstalled,'installed');
    $numInst = 0;
    $numClosed = 0;
    $numDomain = 0;
    //$listError = getListFromDatabase("SELECT `planDate`, COUNT(`id`) as SN FROM `" . $tblName . "` WHERE `planDate` > '0000-00-00' GROUP BY `planDate` ORDER BY `planDate`");
    //debug_to_console($listError,'Error');
    $numError = 0;
    $interval_date = date_diff(date_create($minDate), date_create($repDate))->days + 1;
    for ($i = 0; $i <= $interval_date; $i++) {
        $date_new = strtotime($minDate) + 86400 * ($i - 1);
        $dt_new = date('d.m.Y', $date_new);
        $dt_new_f = date('Y-m-d', $date_new);
        $tmplbl[] = $dt_new;
        foreach ($listArm as $row) {
            if ($dt_new_f == $row['registrationDate']) {
                $numARM += $row['SN'];
            }
        }
        foreach ($listInstalled as $row) {
            if ($dt_new_f == $row['EndDate']) {
                $numInst += $row['SN'];
            }
        }
        foreach ($listClosed as $row) {
            if ($dt_new_f == $row['CloseDate']) {
                $numClosed += $row['SN'];
            }
        }foreach ($listInDomain as $row) {
            if ($dt_new_f == $row['DomainDate']) {
                $numDomain += $row['SN'];
            }
        }
        


        //plan 4000 per month
        $cur_day = date('d', $date_new);
        $cur_month = date('n', $date_new);
        $str_date = "last day of " . date('M', $date_new) . " 2025";
        $last_day = date('d', strtotime($str_date));
        if ($cur_month < 2) {
            $numError = 0;
        } else {
            $numError = round(4000 / $last_day * $cur_day) + 4000 * ($cur_month - 2);
        }
        $tmpTotal[] = 20000;
        $tmpARM[] = $numARM;
        $tmpInst[] = $numInst;
        $tmpClosed[] = $numClosed;
        $tmpError[] = $numError;
        $tmpDomain[] = $numDomain;
    }
    $resArray['labels'] = $tmplbl;
    $resArray['total'] = $tmpTotal;
    $resArray['requested'] = $tmpARM;
    $resArray['installed'] = $tmpInst;
    $resArray['domain'] = $tmpDomain;
    $resArray['closed'] = $tmpClosed;
    $resArray['error'] = $tmpError;
    //debug_to_console($resArray, 'Graph Aray');
    return $resArray;
}

function getTableListFromDatabase($isQuestion = false) {
    // Подключение к базе данных
    $conn = mysqli_connect("localhost", "gpbduser", "gpbdCm611eT!", "gp_data");

    // Проверка соединения
    if (!$conn) {
        die("Connection failed: " . mysqli_connect_error());
    }
    $sql = "SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_TYPE = 'BASE TABLE' AND TABLE_SCHEMA='gp_data' and (TABLE_NAME like 'report%' OR TABLE_NAME like 'domain%')  ORDER BY CREATE_TIME DESC";
    if ($isQuestion) {
        $sql = "SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_TYPE = 'BASE TABLE' AND TABLE_SCHEMA='gp_data' and TABLE_NAME like 'question%' ORDER BY TABLE_NAME DESC";
    }

    // Выборка данных из таблицы
    $result = mysqli_query($conn, $sql);

    // Получение значения из результата
    $num = 1;
    while ($row = mysqli_fetch_assoc($result)) {
        $value = $row['TABLE_NAME'];
        //repoort_
        $start_offset = 7;
        if ($isQuestion) {
            //question_
            $start_offset = 9;
        }
        $table_date = substr($value, $start_offset + 6, 2) . '.' . substr($value, $start_offset + 4, 2) . '.' . substr($value, $start_offset, 4);
        $table_upload = substr($value, $start_offset + 15, 2) . '.' . substr($value, $start_offset + 13, 2) . '.' . substr($value, $start_offset + 9, 4);
        $table_time = substr($value, $start_offset + 17, 2) . ':' . substr($value, $start_offset + 19, 2) . ':' . substr($value, $start_offset + 21, 2);
        echo '<tr><th scope="row">' . $num . '</th><td>' . $table_date . '</th><td>' . $table_upload . '</td><td>' . $table_time . '</td><td>' . $value . '</td><td><button type="submit" class="btn btn-outline-danger" name="delete_table" value = "' . $value . '"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-trash" viewBox="0 0 16 16">'
        . '<path d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0z"></path>'
        . '<path d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1zM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4zM2.5 3h11V2h-11z"></path>'
        . '</svg></input></td><td>' . '</td></tr>';
        $num += 1;
    }

    // Закрытие соединения
    mysqli_close($conn);
}

function str2date($str) {
    $res = null;
    if (strlen($str) > 0) {
        $space_pos = strpos($str, " ");
        $res = date('Y-m-d', strtotime(substr($str, 0, $space_pos)));
    }
    return $res;
}

function addNewReport($file, $rdate) {
    // Подключение к базе данных
    $conn = mysqli_connect("localhost", "gpbduser", "gpbdCm611eT!", "gp_data");
    // Проверка соединения
    if (!$conn) {
        die(json_encode(['progress' => 'error', 'message' => 'Ошибка подключения: ' . mysqli_connect_error(), 'element_id' => 'progress_load']));
        //die("Connection failed: " . mysqli_connect_error());
    }
    send_progress(5, 'Начало обработки файла');
    // Создание новой таблицы в базе данных
    $curdate = date("YmdHis");
    //debug_to_console($rdate);
    $rd = date_create_from_format("Y-m-d", $rdate)->format('Ymd');
    //debug_to_console($rd);
    $tableName = "report_{$rd}_{$curdate}";
    $sql = "CREATE TABLE $tableName (
      `parent_id` int DEFAULT NULL,
      `registrationDate` date DEFAULT NULL,
      `idOP` varchar(4) DEFAULT NULL,
      `Host_name` varchar(100) DEFAULT NULL,
      `Host_address` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
      `ARMModel` varchar(100) DEFAULT NULL,
      `Serial_num` varchar(100) DEFAULT NULL,
      `not_installed` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
      `Serial_num_old` varchar(100) DEFAULT NULL,
      `Status` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
      `parent_close_date` date DEFAULT NULL,
      `id` varchar(7) NOT NULL,
      `task_start_date` date DEFAULT NULL,
      `curStatus` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
      `taskDescription` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
      `curGroup` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
      `Contractor` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
      `EndDate` date DEFAULT NULL,
      `ResultDescription` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
      `CloseDate` date DEFAULT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;";

    $res_sql = mysqli_query($conn, $sql);
    if (!$res_sql) {
        die(json_encode(['progress' => 'error', 'message' => 'Ошибка создания таблицы: ' . mysqli_error($conn), 'element_id' => 'progress_load']));
        //die("Table creation failed: " . mysqli_error($conn));
    }

    send_progress(10, 'Создана таблица ' . $tableName);
    $xlsx = new SimpleXLSX($file);
    // Вставка данных из XLSX-файла в новую таблицу
    send_progress(15, 'Файл прочитан, начало обрабтки');
    $row_num = 0;
    $row_success_num = 0;
    //send_progress(15, 'Файл прочитан. Начало обработки данных.');
    $totalRows = count($xlsx->rows());
    $batchSize = 1000; // Размер пакета для отчетов о прогрессе
    //send_progress(15, 'Начало импорта данных...');
    foreach ($xlsx->readRows() as $row) {
        if ($row_num > 0) {
            $parent_id = $row[0];
            $registrationDate = str2date($row[1]);
            $idOP = sprintf('%04d',(int)$row[2]);
            debug_to_console($idOP . ' <- '.$row[2]);
            $Host_name = $row[3];
            $Host_address = $row[4];
            $ARMModel = $row[5];
            $Serial_num = $row[6];
            $not_installed = $row[7];
            $Serial_num_old = $row[8];
            $Status = $row[9];
            $parent_close_date = str2date($row[10]);
            $id = $row[11];
            $task_start_date = str2date($row[12]);
            $curStatus = $row[13];
            $taskDescription = $row[14];
            $curGroup = $row[15];
            $Contractor = $row[16];
            $EndDate = str2date($row[17]);
            $ResultDescription = $row[18];
            $CloseDate = str2date($row[19]);
            $sql = "INSERT INTO $tableName "
                    . "(parent_id, registrationDate, idOP, Host_name, Host_address, ARMModel, Serial_num, not_installed, Serial_num_old, Status,"
                    . "parent_close_date, id, task_start_date, curStatus, taskDescription, curGroup, Contractor, EndDate, ResultDescription, CloseDate)"
                    . "VALUES ('$parent_id', '$registrationDate', '$idOP', '$Host_name', '$Host_address', '$ARMModel', '$Serial_num', '$not_installed',"
                    . "'$Serial_num_old', '$Status', '$parent_close_date', '$id', '$task_start_date', '$curStatus', '$taskDescription', '$curGroup',"
                    . "'$Contractor', '$EndDate', '$ResultDescription', '$CloseDate')";
            //debug_to_console($sql, 'SQL save');
            $row_success_num = mysqli_query($conn, $sql) > 0 ? $row_success_num + 1 : $row_success_num;
        }
        $row_num += 1;
        if ($row_num % $batchSize === 0 || $row_num >= $totalRows) {
            $progress = 15 + (85 * ($row_num / $totalRows));
            send_progress(round($progress), "Обработано $row_num из $totalRows строк...");
        }
    }
    send_progress(100, 'Загружено строк: ' . $row_num - 1 . ', из них удачно: ' . $row_success_num);
    echo json_encode(['progress' => '100', 'message' => 'Загружено строк: ' . $row_num - 1 . ', из них удачно: ' . $row_success_num, 'element_id' => 'progress_load']);
    //echo '<br><br><h1 class="fs-5 fw-bold text-info">Загружено строк: ' . $row_num - 1 . ', из них удачно: ' . $row_success_num . '</h1>';
    //echo '</table>';
    // Закрытие соединения
    mysqli_close($conn);
    return;
}

function addNewDomainInfo($file, $rdate) {
// Подключение к базе данных
    $conn = mysqli_connect("localhost", "gpbduser", "gpbdCm611eT!", "gp_data");
    // Проверка соединения
    if (!$conn) {
        die(json_encode(['progress' => 'error', 'message' => 'Ошибка подключения: ' . mysqli_connect_error(), 'element_id' => 'progress_load']));
        //die("Connection failed: " . mysqli_connect_error());
    }
    // Создание новой таблицы в базе данных
    send_progress(5, 'Начало обработки файла','progress_load2');
    $curdate = date("YmdHis");
    //debug_to_console($rdate);
    $rd = date_create_from_format("Y-m-d", $rdate)->format('Ymd');
    //debug_to_console($rd);
    $tableName = "domain_{$rd}_{$curdate}";
    $sql = "CREATE TABLE $tableName (
      `arm_name` varchar(15) DEFAULT NULL,
      `whenCreated` datetime DEFAULT NULL,
      `whenChanged` datetime DEFAULT NULL,
      `pwdLatSet` bigint DEFAULT NULL,
      `pwdLastSet2` datetime DEFAULT NULL,
      `dnsHostName` varchar(23) DEFAULT NULL,
      `subj_code` varchar(3) DEFAULT NULL,
      `op_id` varchar(4) DEFAULT NULL,
      `domainDate` datetime DEFAULT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;";

    $res_sql = mysqli_query($conn, $sql);
    if (!$res_sql) {
        die(json_encode(['progress' => 'error', 'message' => 'Ошибка создания таблицы: ' . mysqli_error($conn), 'element_id' => 'progress_load2']));
        //die("Table creation failed: " . mysqli_error($conn));
    }
    //echo '<br><br><h1 class="fs-3 fw-bold text-success">Создана таблица ' . $tableName . '</h1>';
    send_progress(10, 'Создана таблица ' . $tableName, 'progress_load2');
    $xlsx = new SimpleXLSX($file);
    // Вставка данных из XLSX-файла в новую таблицу
    send_progress(15, 'Файл прочитан, начало обрабтки', 'progress_load2');
    $row_num = 0;
    $row_domain_num = 0;
    $row_success_num = 0;

    $sheets = $xlsx->sheetNames();
    foreach ($sheets as $index => $name) {
        //debug_to_console('name : ' . $name . ', index : ' . $index);
        if ($name == 'Данные') {
            $totalRows = count($xlsx->rows($index));
            $batchSize = 1000; // Размер пакета для отчетов о прогрессе
            foreach ($xlsx->rows($index) as $r => $row) {
                if ($row_num > 0 and $row[10] <> '') {
                    $arm_name = $row[0];
                    $whenCreated = date('Y-m-d H:m:s', strtotime($row[1]));
                    $whenChanged = date('Y-m-d H:m:s', strtotime($row[2]));
                    $pwdLatSet = $row[3];
                    $pwdLastSet2 = $row[4];
                    $dnsHostName = $row[5];
                    $subj_code = $row[6];
                    $op_id = sprintf('%04d',(int)$row[7]);
                    $domainDate = $row[11];
                    $row_domain_num += 1;
                    $sql = "INSERT INTO $tableName "
                            . "(arm_name, whenCreated, whenChanged, pwdLatSet, pwdLastSet2, dnsHostName, subj_code, op_id, domainDate)"
                            . "VALUES ('$arm_name', '$whenCreated', '$whenChanged', '$pwdLatSet', '$pwdLastSet2', '$dnsHostName', '$subj_code', '$op_id', '$domainDate')";
                    //debug_to_console($sql, 'SQL save');
                    $row_success_num = mysqli_query($conn, $sql) > 0 ? $row_success_num + 1 : $row_success_num;
                }
                $row_num += 1;
                if ($row_num % $batchSize === 0 || $row_num >= $totalRows) {
                    $progress = 15 + (85 * ($row_num / $totalRows));
                    send_progress(round($progress), "Обработано $row_num из $totalRows строк...","progress_load2");
                }
            }
        }
    }
    send_progress(100, 'Загружено строк: ' . $row_success_num . '. Обработано строк: ' .$row_num - 1 .' из ' . $totalRows,'progress_load2');
    echo json_encode(['progress' => '100', 'message' => 'Загружено строк: ' . $row_success_num . '. Обработано строк: ' .$row_num - 1 .' из ' . $totalRows, 'element_id' => 'progress_load2']);
    // Закрытие соединения
    mysqli_close($conn);
    return $tableName;
}

function getUploadedFileName($file, $file_csv, $rdate) {
    send_progress(0, 'Начало загрузки файла отчета...');
    send_progress(0, 'Начало загрузки файла по подключению к домену...', 'progress_load2');
    // Проверяем, нет ли ошибок при загрузке
    //debug_to_console("getUploadedFileName: " . date('d-m-Y H:i:s:v', time()) . " start");
    //debug_to_console("file[error] : " . $file['error'] . ", file_csv[error] : " . $file_csv['error']."UPLOAD_ERR_OK = ".UPLOAD_ERR_OK);
    if ($file['error'] !== UPLOAD_ERR_OK and $file['error'] !== UPLOAD_ERR_NO_FILE) {
        //die(json_encode(['upload_progress' => 'error', 'message' => 'Ошибка при загрузке файла отчёта.', 'element_id' => 'progress_load']));
        //send_progress(10, 'Ошибка при загрузке файла отчёта.');
        //return;
        die(json_encode(['progress' => 'error', 'message' => 'Ошибка при загрузке файла отчета.', 'element_id' => 'progress_load']));
        //die("Ошибка при загрузке файла отчёта.");
    }
    if ($file_csv['error'] !== UPLOAD_ERR_OK and $file_csv['error'] !== UPLOAD_ERR_NO_FILE) {
        //send_progress(10, 'Ошибка при загрузке файла по подключению к домену.', 'progress_load2');
        //return;
        //die("Ошибка при загрузке файла по подключению к домену.");
        die(json_encode(['progress' => 'error', 'message' => 'Ошибка при загрузке файла по подключению к домену.', 'element_id' => 'progress_load2']));
    }

    if ($file['error'] == UPLOAD_ERR_NO_FILE and $file_csv['error'] == UPLOAD_ERR_NO_FILE) {
        die(json_encode(['progress' => 'error', 'message' => 'Ни один файл не загружен.', 'element_id' => 'ALL']));
        //send_progress('error', 'Ни один файл не загружен.', 'progress_load');
        return;
    }
    if ($file['error'] == UPLOAD_ERR_NO_FILE) {
        send_progress(0, 'Файл отчета не загружен.');
    }
    if ($file_csv['error'] == UPLOAD_ERR_NO_FILE) {
        send_progress(0, 'Файл  не загружен.', 'progress_load2');
    }

    // Проверяем, является ли файл .xlsx
    $fileType = pathinfo($file['name'], PATHINFO_EXTENSION);
    $fileTypeCSV = pathinfo($file_csv['name'], PATHINFO_EXTENSION);
    if ($file['error'] == UPLOAD_ERR_OK and strtolower($fileType) !== 'xlsx') {
        send_progress(10, 'Выбранный файл отчёта не является файлом .xlsx.');
        return;
        //die("Выбранный файл отчёта не является файлом .xlsx.");
    }

    if ($file_csv['error'] == UPLOAD_ERR_OK and strtolower($fileTypeCSV) !== 'xlsx') {
        send_progress(10, 'Выбранный файл по подключению к домену не является файлом .xlsx.', 'progress_load2');
        return;
        //die("Выбранный файл по подключению к домену не является файлом .xlsx.");
    }

    //создаем таблицу
    if ($file['error'] == UPLOAD_ERR_OK) {
        addNewReport($file['tmp_name'], $rdate);
        unlink($file['tmp_name']);
    }
    if ($file_csv['error'] == UPLOAD_ERR_OK) {
        addNewDomainInfo($file_csv['tmp_name'], $rdate);
        //echo json_encode(['progress' => '100', 'message' => 'Файл '. $file_csv['name'] .' успешно загружен', 'element_id' => 'progress_load2']);
        unlink($file_csv['tmp_name']);
    }
    return;
}

function addNewQuestions($file, $rdate) {
    // Подключение к базе данных
    // Подключение к базе данных
    $conn = mysqli_connect("localhost", "gpbduser", "gpbdCm611eT!", "gp_data");
    // Проверка соединения
    if (!$conn) {
        die("Connection failed: " . mysqli_connect_error());
    }
    // Создание новой таблицы в базе данных
    $curdate = date("YmdHis");
    debug_to_console($rdate);
    $rd = date_create_from_format("Y-m-d", $rdate)->format('Ymd');
    debug_to_console($rd);
    $tableName = "question_{$rd}_{$curdate}";
    $sql = "CREATE TABLE $tableName (
        `id` int NOT NULL,
        `Category` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
        `Question` varchar(512) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
        `QResult` varchar(512) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
        `Responsible` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
        `planDate` date DEFAULT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;";

    $res_sql = mysqli_query($conn, $sql);
    if (!$res_sql) {
        die("Table creation failed: " . mysqli_error($conn));
    }
    echo '<h1 class="fs-2 fw-bold text-success">Создана таблица ' . $tableName . '</h1>';
    echo '<a class="link-info text-end" href="index.php">Перейти к отчёту</a>';
    echo '<table class="table table-sm overflow-auto position-relative">';

    $xlsx = new SimpleXLSX($file);
    // Вставка данных из XLSX-файла в новую таблицу
    $row_num = 1;
    $data = $xlsx->rows();
    foreach ($data as $row) {
        if ($row_num == 1) {
            echo '<thead><tr><th scope="col">' . implode('</th><th scope="col">', $row) . '</th></tr></thead>';
        } else {
            $id = $row[0];
            $category = $row[1];
            $question = $row[2];
            $qresult = $row[3];
            $respons = $row[4];
            $planDate = str2date($row[5]);

            $sql = "INSERT INTO $tableName "
                    . "(id, Category, Question, QResult, Responsible, planDate) "
                    . "VALUES ('$id', '$category', '$question', '$qresult', '$respons','$planDate')";
            if (mysqli_query($conn, $sql)) {

                echo '<tr class="table-success"><td>' . implode('</td><td>', $row) . '</td></tr>';
            } else {
                echo '<tr class="table-danger"><td>' . implode('</td><td>', $row) . '</td></tr>';
            }
        }
        //$sql = "INSERT INTO $tableName (name, email) VALUES ('$name', '$email')";
        $row_num += 1;
    }
    echo '</table>';
    // Закрытие соединения
    mysqli_close($conn);
    return $tableName;
}

function getUploadedFileNameQ($file, $rdate) {
    // Проверяем, нет ли ошибок при загрузке

    if ($file['error'] !== UPLOAD_ERR_OK) {
        die("Ошибка при загрузке файла.");
    }
    // Проверяем, является ли файл .xlsx
    $fileType = pathinfo($file['name'], PATHINFO_EXTENSION);
    if (strtolower($fileType) !== 'xlsx') {
        die("Выбранный файл не является файлом .xlsx.");
    }

    //создаем таблицу
    $filePath = $file['tmp_name'];
    $newFilePath = $filePath . '.' . $fileType;
    move_uploaded_file($filePath, $newFilePath);
    $tbl = addNewQuestions($newFilePath, $rdate);
    // Получаем имя файла
    return $tbl;
}

function getListOfSubjects($tableReportName, $tableDomainName, $reportDate) {
    $sql = "CALL getList('".$tableReportName."','".$tableDomainName."','".$reportDate."');";
    $res = getListFromDatabase($sql);
    debug_to_console($res);
    $value = '<table class="table"> '
            . '<colgroup>'
            . '     <col span="1" style="width: 10%;">'
            . '     <col span="1" style="width: 54%;">'
            . '     <col span="1" style="width: 12%;">'
            . '     <col span="1" style="width: 12%;">'
            . '     <col span="1" style="width: 12%;">'
            . '</colgroup>'
            . '  <thead>'
            . '      <tr>'
            . '          <th scope="col" class="align-middle bg-secondary-subtle">Префикс</th>'
            . '          <th scope="col" class="align-middle bg-secondary-subtle">Название</th>'
            . '          <th scope="col" class="align-middle bg-secondary-subtle">Актов подписано</th>'
            . '          <th scope="col" class="align-middle bg-secondary-subtle">Установлено АРМ без АКТов</th>'
            . '          <th scope="col" class="align-middle bg-secondary-subtle">Осталось установить АРМ</th>'
            . '      </tr>'
            . '  </thead>'
            . '  <tbody>';
    $subj_num = 0;
    $op_num = 0;
    foreach ($res as $row) {
        $is_zero_class = $row['domain'] == 0 ? 'zero_arm_subj' : '';
        $hide_zero = $row['domain'] == 0 ? 'hide-arm="true"' : '';
        if ($row['isSubject'] == '2') {
            $subj_num += 1;
            //header
            $value = $value . ''
                    . '<tr class="fw-bold expand_chevrone ' . $is_zero_class . '" id="#subj' . $subj_num . '" rows-expanded="false" ' . $hide_zero . ' onclick="CollapseRows(\'subj' . $subj_num.'\')">'
                    . '     <th rowspan="2" class="align-middle chevrone bg-secondary-subtle"> ' . $row['prefix'] . '</th>'
                    . '     <td rowspan="2" class="align-middle bg-secondary-subtle">' . $row['name'] . ' (всего)</td>'
                    . '<td colspan="3" class=" bg-secondary-subtle">'
                    . ' <div class="progress" >'
                    . '     <div class="progress-bar bg-success" role="progressbar"  style="width: ' . $row['CloseCount'] / $row['total'] * 100 . '%;" aria-valuenow="' . $row['CloseCount'] . '" aria-valuemin="0" aria-valuemax="' . $row['total'] . '"></div>'
                    . '     <div class="progressbg-bar bg-warning" role="progressbar"  style="width: ' . ($row['domain'] - $row['CloseCount']) / $row['total'] * 100 . '%;" aria-valuenow="' . $row['domain'] - $row['CloseCount'] . '" aria-valuemin="0" aria-valuemax="' . $row['total'] . '"></div>'
                    . '     <div class="progress-bar bg-info-subtle" role="progressbar"  style="width: ' . ($row['total'] - $row['domain']) / $row['total'] * 100 . '%;" aria-valuenow="' . $row['total'] - $row['domain'] . '" aria-valuemin="0" aria-valuemax="' . $row['total'] . '"></div>'
                    . ' </div>'
                    . '</td>'
                    . '</tr>'
                    . '<tr class="' . $is_zero_class . '" ' . $hide_zero . '>'
                    . ' <td class="small text-center align-middle bg-secondary-subtle"><i class="cis-square icon bg-success"></i> ' . $row['CloseCount'] . ' </td>'
                    . ' <td class="small text-center align-middle bg-secondary-subtle"><i class="cis-square icon bg-warning"></i> ' . $row['domain'] - $row['CloseCount'] . '</td>'
                    . ' <td class="small text-center align-middle bg-secondary-subtle"><i class="cis-square icon bg-info-subtle"></i>' . $row['total'] - $row['domain'] . '</td>'
                    . '</tr>';
            $op_num = 0;
        } else {
            $op_num += 1;
            $charset = $row['isSubject'] == 0 ? '' : 'fw_semibold fst-italic text-decoration-underline text-bg-light';
            $is_zero_class_row = $row['domain'] == 0 ? 'zero_arm' : '';
            if ($row['total'] == '0') {
                $row['total'] = max($row['CloseCount'], $row['endCount'],$row['domain']);
            }
            $value = $value . ''
                    . '<tr class="collapse_row '. $charset . ' subj' . $subj_num . ' '.$is_zero_class_row.'" data-bs-parent=".table" show-row="false" style="border-bottom-style:none;" ' . $hide_zero . '>'
                    . ' <td rowspan="2" class="align-middle ' . $charset . '">' . $row['prefix'] . '_' . $row['idOP'] . '</td>'
                    . ' <td rowspan="2" class="align-middle ' . $charset . '">' . $row['name'] . '</td>'
                    . ' <td colspan="3" class="' . $charset . '">'
                    . '     <div class="progress text-center" style="margin-left:10%;width:80%;">'
                    . '         <div class="progress-bar bg-success" role="progressbar"  style="width: ' . $row['CloseCount'] / $row['total'] * 100 . '%;" aria-valuenow="' . $row['CloseCount'] . '" aria-valuemin="0" aria-valuemax="' . $row['total'] . '"></div>'
                    . '         <div class="progressbg-bar bg-warning" role="progressbar"  style="width: ' . ($row['domain'] - $row['CloseCount']) / $row['total'] * 100 . '%;" aria-valuenow="' . $row['domain'] - $row['CloseCount'] . '" aria-valuemin="0" aria-valuemax="' . $row['total'] . '"></div>'
                    . '         <div class="progress-bar bg-info-subtle" role="progressbar"  style="width: ' . ($row['total'] - $row['domain']) / $row['total'] * 100 . '%;" aria-valuenow="' . $row['total'] - $row['domain'] . '" aria-valuemin="0" aria-valuemax="' . $row['total'] . '"></div>'
                    . '     </div>'
                    . ' </td>'
                    . '</tr>'
                    . '<tr class="collapse_row ' . $charset . ' subj' . $subj_num . ' '.$is_zero_class_row.' " data-bs-parent=".table" show-row="false" ' . $hide_zero . '>'
                    . ' <td class="small text-center align-middle ' . $charset . '"><i class="cis-square icon bg-success"></i> ' . $row['CloseCount'] . ' </td>'
                    . ' <td class="small text-center align-middle ' . $charset . '"><i class="cis-square icon bg-warning"></i> ' . $row['domain'] - $row['CloseCount'] . '</td>'
                    . ' <td class="small text-center align-middle ' . $charset . '"><i class="cis-square icon bg-info-subtle"></i>' . $row['total'] - $row['domain'] . '</td>'
                    . '</tr>';
        }
    }
    $value = $value . '</tbody></table>';
    return $value;
}

function UpdateDatabase($querry) {
    // Подключение к базе данных
    $conn = mysqli_connect("localhost", "gpbduser", "gpbdCm611eT!", "gp_data");

    // Проверка соединения
    if (!$conn) {
        die("Connection failed: " . mysqli_connect_error());
    }

    // Выборка данных из таблицы
    $result = mysqli_query($conn, $querry);

    // Закрытие соединения
    mysqli_close($conn);

    return $result;
}
