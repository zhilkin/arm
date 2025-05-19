<?php
require_once 'library.php';

ini_set('error_reporting', E_ALL);
ini_set('display_errors', true);

$tableReportName = getTableReport();
$reportDate = substr($tableReportName, 7, 4) . '-' . substr($tableReportName, 11, 2) . '-' . substr($tableReportName, 13, 2);
//$res = getListFromDatabase("SELECT id, name, longitude as lon, latitude as lat FROM op WHERE longitude > 0 and isSubject = 1");
$res = getListFromDatabase("SELECT DISTINCT op.id as id, op.name as name, op.longitude as lon, op.latitude as lat FROM `op` JOIN ".$tableReportName." ON op.ID=".$tableReportName.".idOP WHERE op.isSubject = 1");
$op = "[";
foreach ($res as $row) {
    $op = $op . "[" . $row['id'] . ",'" . $row['name'] . "'," . $row['lat'] . "," . $row['lon'] . "],";
}
$op1 = substr($op, 0, strlen($op) - 1);
$op1 = $op1 . "]";
?>

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
        <!-- карта -->
        <script src="https://code.highcharts.com/maps/highmaps.js"></script>
        <script src="https://code.highcharts.com/maps/modules/exporting.js"></script>
        <script src="https://zhilkin.su/ARM/js/geo.js"></script>
        <!--Локальные стили-->
        <title>Инсталляции на карте</title>
    </head>
    <body >
        <div class="container position-absolute start-50 translate-middle-x" style="width:90%; height:90%" id="chartdiv"></div>
    </body>
    <script>
        (async () => {

            const topology = await fetch(
                    'https://code.highcharts.com/mapdata/countries/ru/custom/ru-all-disputed.topo.json'
                    ).then(response => response.json());

            // Prepare demo data. The data is joined to map using value of 'hc-key'
            // property by default. See API docs for 'joinBy' for more info on linking
            // data and map.
            const data = rf;
            const points = <?= $op1 ?>;

            // Create the chart
            Highcharts.mapChart('chartdiv', {
                chart: {
                    map: topology
                },

                title: {
                    text: 'Установки на карте'
                },

                subtitle: {
                    text: '<?= $reportDate ?>'
                },

                mapNavigation: {
                    enabled: true,
                    buttonOptions: {
                        verticalAlign: 'bottom'
                    }
                },
                tooltip: {
                    headerFormat: '<span style="color:{point.color}">\u25CF</span> ' +
                            '{point.key}<br/>',
                    pointFormat: '{series.name}'
                },
                plotOptions: {
                    mappoint: {
                        keys: ['id', 'name', 'lat', 'lon'],
                        marker: {
                            lineWidth: 1,
                            lineColor: '#007a39',
                            fillColor: '#ffdd00',
                            symbol: 'triangle-down'

                        },
                        dataLabels: {
                            enabled: false
                        }
                    }
                },

                series: [{
                        data: data,
                        name: 'Федеральные округа РФ',
                        states: {
                            hover: {
                                color: '#c6c8c9'
                            }
                        }
                    },
                    {
                        data: points,
                        name: 'Субъектовые прокуратуры РФ',
                        dataLabels: {
                            enabled: false
                        },
                        type: 'mappoint',
                        tooltip: {
                            headerFormat: '<span style="color:#007a39">\u25CF</span> ' +
                                    '{point.key}<br/>',
                            pointFormat: '<b>ID: {point.id}</b><br>{point.lon};{point.lat}'
                        }
                    }]
            });

        })();
    </script>
</html>