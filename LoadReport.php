<?php
//ini_set('error_reporting', E_ALL);
//ini_set('display_errors', true);
require_once 'library.php';

// Обработка запроса на получение прогресса
if (isset($_GET['get_progress'])) {
    session_start();
    echo json_encode(['progress' => $_SESSION['upload_progress'] ?? 0, 'message' => $_SESSION['message_progress'], 'element_id' => $_SESSION['element_id']]);
    exit;
}

// Обработка загрузки файла
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['report_date'])) {
    require_once 'upload.php';    
    exit;
}

// Обработка удаления таблицы
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_table'])) {
    DataBaseRequestWitoutResult("DROP TABLE " . $_POST['delete_table']);
    header("Refresh:0");
    exit;
}
?>
<!DOCTYPE html>
<html lang="ru">

    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width,initial-scale=1">
        <script src="https://cdn.jsdelivr.net/npm/@coreui/coreui@5.3.1/dist/js/bootstrap.bundle.min.js" integrity="sha384-+BCVVYepeVZB2ejb1RVKb+0hoxtMs9CEjephXP6c6DV482N/m/hwq5FevVLVsETI" crossorigin="anonymous"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>

        <script src="//ajax.googleapis.com/ajax/libs/jquery/2.0.0/jquery.min.js"></script>

        <!-- coreUI for Bootstrap CSS -->
        <link href="https://cdn.jsdelivr.net/npm/@coreui/coreui@5.3.1/dist/css/themes/bootstrap/bootstrap.min.css" rel="stylesheet" integrity="sha384-leftDVIOA1OKkBjlGo3RtR+u4nHJPna0xV0zW3hSS3Os38Y84NF5bYB8/hOnHbfj" crossorigin="anonymous">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
        <!-- Icons -->
        <link rel="stylesheet" href="https://www.unpkg.com/@coreui/icons/css/all.min.css">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.9.1/font/bootstrap-icons.css">
        <!-- cdBootstrap -->
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/cdbootstrap/css/cdb.min.css" />  
        <title>Управление отчётами</title>
    </head>

    <body class="text-center">
        <div class="collapse" id="navbarToggleExternalContent">
            <div class="bg-light p-4">
                <a class="btn btn-outline-primary" href="LoadQuestions.php" role="button">Загрузка файла с вопросами</a>
                <a class="btn btn-outline-primary" href="index.php" role="button">Просмотр отчёта</a>
            </div>
        </div>
        <nav class="navbar navbar-light bg-light">
            <div class="container-fluid">
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarToggleExternalContent" aria-controls="navbarToggleExternalContent" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
            </div>
        </nav>
        <div class="position-absolute start-50 translate-middle-x" style="width:90%">
            <h1 class="fs-1 fw-bold text-primary">
                Управление отчётами
            </h1>
            <form class="mb-3" id="UploadForm" method="post" enctype="multipart/form-data">
                <label class="fs-3 fw-bold text-info form-control ">Загрузка нового отчёта</label>
                <div class="input-group">
                    <label class="fs-3 fw-bold text-info form-control " style="width:65%"></label>
                    <label class="input-group-text" for="reportDate" id="inputGroupreportDate">Дата отчета:</label>
                    <input type="date" class="form-control" id="reportDate" name="report_date" aria-describedby="inputGroupreportDate" value="<?php print date("Y-m-d"); ?>"/>
                </div>
                <div class="input-group">
                    <!--<input type="hidden" name="MAX_FILE_SIZE" value="3000000" />-->
                    <label class="input-group-text" style="width:25%" for="inputFileXLS" id="inputGroupFileXLS">Файл отчета инсталляций :</label>
                    <input type="file" accept=".xlsx" class="form-control" id="inputFileXLS" aria-describedby="inputGroupFileXLS" name="file_report" value="" />
                </div>    
                <div class="input-group">
                    <label class="input-group-text" style="width:25%" for="inputFileCSV" id="inputGroupFileCSV">Файл отчета по вводу в домен&nbsp;:</label>
                    <input type="file" accept=".xlsx" class="form-control" id="inputFileCSV" aria-describedby="inputGroupFileCSV" name="file_report_csv" value=""/>
                </div>
                <button class="btn btn-outline-secondary btn-sm mt-3" type="submit" id="UploadFiles"><i class="cil-cloud-upload icon ime-2"></i>Загрузить данные</button>

            </form> 
            <div class="progress-group progress_load_hidden" style="display:none;">
                <div class="progress-group-header align-items-end">
                    <i class="cil-paperclip progress-group-icon me-2"></i>
                    <div class ="progress_load_message" id="progress_load_message">Загрузка файла отчёта...</div>
                </div>
                <div class="progress-group-bars">
                    <div class="progress progress-thin">
                        <div class="progress-bar bg-success progress_load" id="progress_load" role="progressbar" style="width: 0%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                </div>
            </div>
            <div class="progress-group progress_load_hidden" style="display:none;">
                <div class="progress-group-header align-items-end">
                    <i class="cil-paperclip progress-group-icon me-2"></i>
                    <div class ="progress_load_message" id="progress_load2_message">Загрузка файла данных домена...</div>
                </div>
                <div class="progress-group-bars">
                    <div class="progress progress-thin">
                        <div class="progress-bar bg-success progress_load" id="progress_load2" role="progressbar" style="width: 0%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                </div>
            </div>
            <br>
            <h1 class="fs-2 fw-bold text-success">
                Список загруженных отчетов
            </h1>
            <form id="listOfTables" method="post">
                <table class="table position-relative">
                    <thead>
                        <tr>
                            <th scope="col">№</th>
                            <th scope="col">Дата отчёта</th>
                            <th scope="col">Дата загрузки</th>
                            <th scope="col">Время загрузки</th>
                            <th scope="col">Имя таблицы</th>
                            <th scope="col">Удалить</th>

                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        getTableListFromDatabase();
                        ?>
                    </tbody>
                </table>
            </form>
        </div>
        <script>
            function setAll(progress, message) {
                const collection_pl = document.getElementsByClassName("progress_load");
                for (let i = 0; i < collection_pl.length; i++) {
                    collection_pl[i].setAttribute("aria-valuenow", progress);
                    collection_pl[i].style.width = progress+"%";
                    collection_pl[i].text = progress+"%";
                }
                const collection_plm = document.getElementsByClassName("progress_load_message");
                for (let i = 0; i < collection_plm.length; i++) {
                    //console.log(collection_plm[i]);
                    collection_plm[i].innerHTML = message;
                }
            }
            function clearAllProgress() {
                // Сбрасываем прогресс
                setAll(0,'');
                const collection_plh = document.getElementsByClassName("progress_load_hidden");
                for (let i = 0; i < collection_plh.length; i++) {
                    collection_plh[i].style.display = "block";
                }
            }
            
            function setProgress(response) {
                //Устанавливаем прогресс для элемента
                var name = response.element_id;
                var name_msg = name + '_message';
                console.log("name : " + name + ", name_msg : " + name_msg + ", response.progress : " + response.progress+ ", response.message : " + response.message);
                var progress_element = document.getElementById(name);
                progress_element.setAttribute("aria-valuenow", response.progress);
                progress_element.style.width = response.progress + "%";
                progress_element.text = response.progress + "%";
                var progress_element_msg = document.getElementById(name_msg);
                progress_element_msg.innerHTML = response.message;
            }

            $(document).ready(function () {
                var progressInterval;
                $('#UploadForm').on('submit', function (e) {
                    e.preventDefault();
                    var formData = new FormData(this);
                    clearAllProgress();
                    // Отключаем кнопку отправки
                    //$('button[type="submit"]').prop('disabled', true);
                    // Запускаем опрос прогресса
                    progressInterval = setInterval(checkProgress, 500);
                    $.ajax({
                        url: 'LoadReport.php',
                        type: 'POST',
                        data: formData,
                        contentType: false,
                        processData: false,
                        success: function (response) {
                            clearInterval(progressInterval);
                            try {
                                var last_response = response.substring(response.lastIndexOf('{"'));
                                console.log('last_response: ' + last_response);
                                var data = JSON.parse(last_response);
                                if (data.progress === 'success') {
                                    console.log('success: ' + last_response);
                                    setProgress(data);
                                    //$('#status').text('Загрузка завершена! Обработано ' + data.processed + ' строк.');
                                    //$('#progressBar').width('100%');
                                    //$('#progressBar').text('100%');
                                } 
                                else {
                                    console.log('Ошибка: ' + response);
                                    if (data.element_id === 'ALL') {
                                        setAll(0, 'Ошибка: ' + data.message);
                                    } else {
                                        setProgress(data);
                                    }
                                    //$('#status').text('Ошибка: ' + data.message);
                                }
                            } 
                            catch (e) {
                                console.log('Ошибка обработки ответа сервера: "' +e+'"');
                                console.log('Response: "' +response+'"');
                                if (response !== null) {
                                    console.log('null');
                                    //setAll(0, 'Ошибка: ' + e);
                                } else {
                                    console.log(' not null');
                                }
                                //for (let i = 0; i < collection_plm.length; i++) {
                                //    collection_plm[i].innerHTML = 'Ошибка обработки ответа сервера' +e;
                                //}
                            }
                        },
                        error: function (xhr, status, error) {
                            clearInterval(progressInterval);
                            const collection_plm = document.getElementsByClassName("progress_load_message");
                            for (let i = 0; i < collection_plm.length; i++) {
                            //console.log(collection_plm[i]);
                            collection_plm[i].innerHTML = 'Ошибка: ' + error;
                            }
                        }
                    });
                });
                function checkProgress() {
                    $.get('LoadReport.php?get_progress', function (data) {
                        //console.log(data);
                        setProgress(JSON.parse(data));
                    }).fail(function () {
                        console.log('Ошибка запроса прогресса');
                    });
                }
            });

        </script>
    </body>

</html>