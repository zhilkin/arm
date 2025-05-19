<?php
require_once 'library.php';

//phpinfo();

//exit;
?>

<!DOCTYPE html>
<html lang="ru">

    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <!-- Bootstrap CSS -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-gH2yIJqKdNHPEq0n4Mqa/HGKIhSkIHeL5AyhkYV8i59U5AR6csBvApHHNl/vI1Bx" crossorigin="anonymous">
        <link rel="stylesheet" href="https://bootstrap5.ru/css/docs.css">
        <!--Icons-->
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.9.1/font/bootstrap-icons.css">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/cdbootstrap/css/bootstrap.min.css" />
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/cdbootstrap/css/cdb.min.css" />
        <script src="https://cdn.jsdelivr.net/npm/cdbootstrap/js/cdb.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/cdbootstrap/js/bootstrap.min.js"></script>    
        <title>Управление "Открытыми вопросами"</title>
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
            <h1 class="fs-1 fw-bold text-primary">
                Управление "Открытыми вопросами"
            </h1>
            <form class="mb-3" action="QL.php" method="POST" enctype="multipart/form-data">
                <h1 class="fs-2 fw-bold text-success">
                    Загрузка нового списка открытых вопросов
                </h1>
                <div class="input-group mb-3" style="width:100%">
                    <input type="hidden" name="MAX_FILE_SIZE" value="30000" />
                    <input type="file" accept=".xlsx" class="form-control" id="inputFileName" aria-describedby="inputGroupFileAddon04" aria-label="Upload" name="file_question" style="width:70%">
                    <input type="date" class="form-control" id="reportDate" name="question_date" style="width:10%" value="<?php print date("Y-m-d"); ?>"/>
                    <input class="btn btn-outline-secondary" type="submit" id="inputGroupFileAddon04" value="Загрузить" style="width:10%">
                </div>
            </form> 
            <br>
           <h1 class="fs-2 fw-bold text-success">
                Список загруженных отчетов
            </h1>
            <form method="post">
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
                      getTableListFromDatabase(true);
                      ?>
                  </tbody>
                </table>
            </form>

        </div>
    </body>

</html>