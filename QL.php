<?php
ini_set('error_reporting', E_ALL);
ini_set('display_errors', true);
require_once 'library.php';

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
        <script src="https://cdn.jsdelivr.net/npm/cdbootstrap/js/bootstrap.min.js"></script>        
        <title>Загрузка файла отчёта</title>
    </head>
    <body class="text-center">
        <div class="collapse" id="navbarToggleExternalContent">
            <div class="bg-light p-4">
                <a class="btn btn-outline-primary" href="LoadReport.php" role="button">Загрузка файла отчёта</a>
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
            <h1 class="fs-1 fw-bold text-primary">Загрузка открытых вопросов по работам по установке и сборке, пуско-наладке автоматизированных рабочих мест</h1>
            <div lass="table-responsive" style="width:90%">                
                <?php
                // Проверяем, была ли отправлена форма
                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                    $tblName = getUploadedFileNameQ($_FILES['file_question'],$_POST['question_date']);
                }
                ?>
            </div>    
        </div>
    </body>
</html>