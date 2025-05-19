<?php
require_once 'library.php';

ini_set('error_reporting', E_ALL);
ini_set('display_errors', true);

$tableReportName = getTableReport();
$reportDate = substr($tableReportName, 7, 4) . '-' . substr($tableReportName, 11, 2) . '-' . substr($tableReportName, 13, 2);
//$res = getListFromDatabase("SELECT id, name, longitude as lon, latitude as lat FROM op WHERE longitude > 0 and isSubject = 1");
$res = getListFromDatabase("SELECT DISTINCT op.id as id, op.name as name, op.longitude as lon, op.latitude as lat, isSubject as subj, COUNT(" . $tableReportName . ".Serial_num) as arm FROM `op` JOIN " . $tableReportName . " ON op.ID=" . $tableReportName . ".idOP group by " . $tableReportName . ".idOP "); //WHERE isSubject=1
?>

<!DOCTYPE html>
<html>
    <head>
        <title>Установки на карте</title>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width,initial-scale=1">
        <!-- Bootstrap CSS -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-gH2yIJqKdNHPEq0n4Mqa/HGKIhSkIHeL5AyhkYV8i59U5AR6csBvApHHNl/vI1Bx" crossorigin="anonymous">
        <link rel="stylesheet" href="https://bootstrap5.ru/css/docs.css">
        <script src="https://api-maps.yandex.ru/v3/?apikey=01a843aa-81bf-42d8-aa71-b9507294f01d&lang=ru_RU"></script>
        <script>
            var popup = null;
            var minX = 37.588144;
            var maxX = 37.588144;
            var minY = 55.733842;
            var maxY = 55.733842;
            initMap();
            async function initMap() {
                await ymaps3.ready;
                const {YMap, YMapDefaultSchemeLayer, YMapDefaultFeaturesLayer, YMapMarker} = ymaps3;
                const map = new YMap(
                        document.getElementById('map'),
                        {
                            location: {
                                center: [37.588144, 55.733842],
                                zoom: 10
                            }
                        }
                );
                map.addChild(new YMapDefaultSchemeLayer());
                map.addChild(new YMapDefaultFeaturesLayer());
<?php
foreach ($res as $row) {
    //начало формирования маркеров
    ?>
                    const content<?= $row['id'] ?> = document.createElement('section');
                    content<?= $row['id'] ?>.setAttribute("id", "subj<?= $row['id'] ?>");
                    minX = Math.min(minX, <?= $row['lon'] ?>);
                    maxX = Math.max(maxX, <?= $row['lon'] ?>);
                    minY = Math.min(minY, <?= $row['lat'] ?>);
                    maxY = Math.max(maxY, <?= $row['lat'] ?>);
                    const marker<?= $row['id'] ?> = new YMapMarker(
                            {
                                coordinates: [<?= $row['lon'] ?>, <?= $row['lat'] ?>],
                                draggable: false
                            },
                            content<?= $row['id'] ?>
                            );
                    // Добавьте маркер на карту
                    map.addChild(marker<?= $row['id'] ?>);
                    // Добавьте произвольную HTML-разметку внутрь содержимого маркера
    <?php $count_ready = getValueFromDatabase("SELECT count(serial_num) as val FROM " . $tableReportName . " WHERE idOP = " . $row['id'] . " and CloseDate >'0000-00-00'"); ?>
                    content<?= $row['id'] ?>.innerHTML =
                           '<img src="pic/marker_grey.png" class="img_<?= $row['subj'] ?>" id="marker_<?= $row['id'] ?>"></img>\
                            <div class="progress pr_<?= $row['subj'] ?>" style="font-size:0.65em">\
                                <div class="progress-bar" role="progressbar" style="width: <?= $count_ready['val'] / $row['arm'] * 100 ?>%" aria-valuenow="<?= ($count_ready['val']) ?>" aria-valuemin="0" aria-valuemax="<?= $row['arm'] ?>"></div>\
                            </div>';
                    content<?= $row['id'] ?>.onclick = () => {
                        showPopup(map, getMarkerPopupContent(map, '<?= $row['name'] ?>', '<?= $row['id'] ?>',<?= $row['arm'] ?>, <?= $count_ready['val'] ?>, <?= $row['subj'] ?>), [<?= $row['lon'] ?>, <?= $row['lat'] ?>]);
                    };
    <?php
}
?>
                console.debug('minX: ' + minX);
                console.debug('maxX: ' + maxX);
                console.debug('minY: ' + minY);
                console.debug('maxY: ' + maxY);
                map.setLocation({
                    bounds: [
                        [minX, minY],
                        [maxX, maxY]
                    ]
                });
            }

            function getMarkerPopupContent(map, strName, strId, strARM, strReady, strSubj) {
                const node = document.createElement("div");
                node.classList.add("card");
                node.classList.add("popup_"+strSubj);
                node.id = "popup" + strId;
                const percent = strReady / strARM * 100;
                console.debug('percent (' + strName + '): ' + percent);
                node.innerHTML =
                        '<div class="card-body">\
                            <div class="card-title" style="font-size:0.75em">' + strName + ' (' + strId + ')' + '</div>\
                            <div class="card-text" style="font-size:0.65em">Количество АРМ к установке: ' + strARM + '<p>Количество АРМ установлено: ' + strReady + '\
                                <div class="progress" >\
                                    <div class="progress-bar overflow-visible text-dark" style="font-size:0.65em; width: ' + Math.round(percent * 10) / 10 + '%" aria-valuenow="' + strReady + '" aria-valuemin="0" aria-valuemax="' + strARM + '">' + Math.round(percent * 10) / 10 + '%</div>' + '\
                                </div>\
                            </div>\
                        </div>';
                const closeBtn = document.createElement('button');
                closeBtn.className = 'btn btn-outline-primary btn-sm';
                closeBtn.innerHTML = 'Закрыть';
                closeBtn.onclick = () => {
                    cur_popup = document.getElementById("popup" + strId);
                    cur_popup.remove(); 
                    img_element = document.getElementById("marker_"+strId);
                    img_element.src="pic/marker_grey.png";
                };
                node.append(closeBtn);
                img_element = document.getElementById("marker_"+strId);
                img_element.src="pic/marker_red.png";
                return  node;
            }


            function showPopup(map, content, coords) {
                const {YMapMarker} = ymaps3;
                popup = new YMapMarker({coordinates: coords}, content);
                map.addChild(popup);
            }


        </script>
    </head>
    <style>
        .img_1 {            
            position:relative;
            width: 42px;
            height: 42px;
            left: -21px;
        }
        .img_0 {
            position:relative;
            width: 28px;
            height: 28px;
            left: -14px;
        }
        .pr_1 {
            position:relative;
            width: 52px;
            height: 5px;
            left: -26px;
            top: -45px;
            background-color: #c8c8c8;
        }
        .pr_0 {
            position:relative;
            width: 38px;
            height: 3px;
            left: -19px;
            top: -30px;
            background-color: #c8c8c8;
        }
        .popup_1 {
            width: 18rem;
            left: -50%;
            top: 45px;
        }
        .popup_0 {
            width: 18rem;
            left: -50%;
            top: 28px; 
        }
    </style>
    <body>
        <div class="container">
            <h1 class="fs-4 fw-bold text-primary text-center"  >Статус работ по установке и сборке, пуско-наладке автоматизированных рабочих мест</h1>
        </div>
        <div class="container position-absolute start-50 translate-middle-x" style="width:90%; height:90%" id="map"></div>
    </body>
</html>