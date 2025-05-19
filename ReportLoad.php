<?php
require_once 'library.php';
debug_to_console("ReportLoad");
$tblName = "";
?>
<!doctype html>
<html lang="ru">
    <head>
        <!-- Обязательные метатеги -->
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width,initial-scale=1">
        <!-- Bootstrap CSS -->
        <!-- Bootstrap CSS -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-gH2yIJqKdNHPEq0n4Mqa/HGKIhSkIHeL5AyhkYV8i59U5AR6csBvApHHNl/vI1Bx" crossorigin="anonymous">
        <link rel="stylesheet" href="https://bootstrap5.ru/css/docs.css">
        <!--Icons-->
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.9.1/font/bootstrap-icons.css">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/cdbootstrap/css/bootstrap.min.css" />
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/cdbootstrap/css/cdb.min.css" />
        <script src="https://cdn.jsdelivr.net/npm/cdbootstrap/js/cdb.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/cdbootstrap/js/bootstrap.min.js"></script>       <!--Локальные стили-->
        <title>Загрузка файла отчёта</title>
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
            <h1 class="fs-1 fw-bold text-primary">Загрузка отчёта по статусу работ по установке и сборке, пуско-наладке автоматизированных рабочих мест</h1>
            <div class="progress-group">
                <div class="progress-group-header align-items-end">
                    <i class="cil-globe-alt progress-group-icon me-2"></i>
                    <div id="progress_load_message">Загрузка файла отчёта...</div>
                </div>
                <div class="progress-group-bars">
                    <div class="progress progress-thin">
                        <div class="progress-bar bg-success" id="progress_load" role="progressbar" style="width: 0%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                </div>
            </div>
            <div class="progress-group">
                <div class="progress-group-header align-items-end">
                    <i class="cil-globe-alt progress-group-icon me-2"></i>
                    <div id="progress_load_message">Загрузка файла отчёта...</div>
                </div>
                <div class="progress-group-bars">
                    <div class="progress progress-thin">
                        <div class="progress-bar bg-success" id="progress_load" role="progressbar" style="width: 0%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                </div>
            </div><div class="progress-group">
                <div class="progress-group-header align-items-end">
                    <i class="cil-globe-alt progress-group-icon me-2"></i>
                    <div id="progress_load2_message">Загрузка файла данных домена...</div>
                </div>
                <div class="progress-group-bars">
                    <div class="progress progress-thin">
                        <div class="progress-bar bg-success" id="progress_load2" role="progressbar" style="width: 0%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                </div>
            </div>
            <div lass="table-responsive" style="width:90%">
                <?php
                // Проверяем, была ли отправлена форма
                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                    $tblName = getUploadedFileName($_FILES['file_report'], $_FILES['file_report_csv'], $_POST['report_date']);
                }
                ?>
            </div>    
        </div>
    <script>
    $(document).ready(function() {
        $('#uploadForm').on('submit', function(e) {
            e.preventDefault();
            
            var formData = new FormData(this);
            var xhr = new XMLHttpRequest();
            
            // Сбрасываем прогресс
            $('#progressBar').css('width', '0%').text('0%');
            $('#status').text('Начало загрузки...');
            
            xhr.open('POST', 'upload.php', true);
            
            // Обработка прогресса
            xhr.onreadystatechange = function() {
                if (xhr.readyState === 3) {
                    try {
                        var response = JSON.parse(xhr.responseText);
                        $('#progressBar').css('width', response.progress + '%')
                                        .text(response.progress + '%');
                        $('#status').text(response.message);
                    } catch(e) {
                        // Пропускаем неполные JSON данные
                    }
                } else if (xhr.readyState === 4) {
                    if (xhr.status === 200) {
                        try {
                            var response = JSON.parse(xhr.responseText);
                            $('#status').text(response.message);
                        } catch(e) {
                            $('#status').text('Импорт завершен!');
                        }
                    } else {
                        $('#status').text('Ошибка: ' + xhr.statusText);
                    }
                }
            };
            
            xhr.send(formData);
        });
    });
    </script>
    </body>
</html>