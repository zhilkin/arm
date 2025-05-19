<?php
require_once 'library.php';

ini_set('error_reporting', E_ALL);
ini_set('display_errors', true);

$tableReportName = getTableReport();
$reportDate = substr($tableReportName, 7, 4) . '-' . substr($tableReportName, 11, 2) . '-' . substr($tableReportName, 13, 2);
//$res = getListFromDatabase("SELECT id, name, longitude as lon, latitude as lat FROM op WHERE longitude > 0 and isSubject = 1");
$subjList = getListFromDatabase("SELECT DISTINCT id, name FROM op WHERE id IN (SELECT idOP FROM " . $tableReportName . ") AND isSubject=1");
$res = getListFromDatabase("SELECT DISTINCT op.id as id, op.name as name, op.longitude as lon, op.latitude as lat, isSubject as subj, op.parent_id as parent, op.address as addr, COUNT(" . $tableReportName . ".id) as arm FROM `op` JOIN " . $tableReportName . " ON op.ID=" . $tableReportName . ".idOP group by " . $tableReportName . ".idOP "); //WHERE isSubject=1
debug_to_console($res);
$coord_all = getListFromDatabase("SELECT MIN(`latitude`) as minY, MIN(`longitude`) as minX, MAX(`latitude`) as maхY, MAX(`longitude`) as maхX FROM `op` WHERE id IN (SELECT idOP from " . $tableReportName . " group by idop)");
$coord_subj = getListFromDatabase("SELECT MIN(`latitude`) as minY, MIN(`longitude`) as minX, MAX(`latitude`) as maxY, MAX(`longitude`) as maxX FROM `op` WHERE id IN (SELECT idOP from " . $tableReportName . " group by idop) AND isSubject =1");
$coord_subjs = getListFromDatabase("SELECT parent_id, MIN(`latitude`) as minY, MIN(`longitude`) as minX, MAX(`latitude`) as maxY, MAX(`longitude`) as maxX FROM `op` WHERE id IN (SELECT idOP from " . $tableReportName . " group by idop) group by parent_id");
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
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
        <!-- Yandex MAP -->
        <script src="https://api-maps.yandex.ru/v3/?apikey=<?=$API_key_new?>&lang=ru_RU"></script>
        <script>
            window.map = null;
            initMap();
            async function initMap() {
                await ymaps3.ready;
                const {YMap, YMapDefaultSchemeLayer, YMapDefaultFeaturesLayer, YMapMarker} = ymaps3;
                map = new YMap(
                        document.getElementById('map'),
                        {
                            location: {
                                center: [37.621202, 55.753544],
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
                    content<?= $row['id'] ?>.classList.add("marker");
                    content<?= $row['id'] ?>.classList.add("subject_<?= $row['subj'] ?>");
                    content<?= $row['id'] ?>.classList.add("parent_<?= $row['parent'] ?>");
                    const marker<?= $row['id'] ?> = new YMapMarker(
                            {
                                coordinates: [<?= $row['lon'] ?>,<?= $row['lat'] ?>],
                                draggable: false
                            },
                            content<?= $row['id'] ?>
                            );
                    // Добавляем маркер на карту
                    map.addChild(marker<?= $row['id'] ?>);
                    // Добавьте произвольную HTML-разметку внутрь содержимого маркера
    <?php $count_ready = getValueFromDatabase("SELECT count(id) as val FROM " . $tableReportName . " WHERE idOP = '" . $row['id'] . "' and CloseDate >'0000-00-00'"); ?>
                    content<?= $row['id'] ?>.innerHTML =
                           '<img src="pic/marker_grey.png" class="img_<?= $row['subj'] ?>" id="marker_<?= $row['id'] ?>"></img>\
                            <div class="progress pr_<?= $row['subj'] ?>" style="font-size:0.65em">\
                                <div class="progress-bar" role="progressbar" style="width: <?= $count_ready['val'] / $row['arm'] * 100 ?>%" aria-valuenow="<?= ($count_ready['val']) ?>" aria-valuemin="0" aria-valuemax="<?= $row['arm'] ?>"></div>\
                            </div>';
                    content<?= $row['id'] ?>.onclick = () => {
                        showPopup(map, getMarkerPopupContent(map, '<?= $row['name'] ?>', '<?= $row['id'] ?>',<?= $row['arm'] ?>, <?= $count_ready['val'] ?>, <?= $row['subj'] ?>, '<?= $row['addr'] ?>'), [<?= $row['lon'] ?>, <?= $row['lat'] ?>]);
                    };
    <?php
}

?>

                map.setLocation({
                    bounds: [
                        [<?= $coord_all[0]['minX'] ?>-0.005, <?= $coord_all[0]['minY'] ?>-0.005],
                        [<?= $coord_all[0]['maхX'] ?>+0.005, <?= $coord_all[0]['maхY'] ?>+0.005]
                    ]
                });
                
                const selectElement = document.getElementById("object_selector");   
                selectElement.addEventListener("change", (event) => {
                        var objList = document.getElementsByClassName("marker");
                        switch (event.target.value) {
                            case '-2': 
                                Array.from(objList).forEach((mel)=>{
                                    mel.style.visibility='visible';
                                });
                                map.setLocation({
                                    bounds: [
                                        [<?= $coord_all[0]['minX'] ?>-0.005, <?= $coord_all[0]['minY'] ?>-0.005],
                                        [<?= $coord_all[0]['maхX'] ?>+0.005, <?= $coord_all[0]['maхY'] ?>+0.005]
                                    ]
                                });
                                break;
                            case '-1':
                                Array.from(objList).forEach((mel)=>{                        
                                    if (mel.classList.contains('subject_1')) {
                                        mel.style.visibility='visible';    
                                    }
                                    else {
                                        mel.style.visibility='hidden';
                                    }   
                                    
                                    });
                                map.setLocation({
                                    bounds: [
                                        [<?= $coord_subj[0]['minX'] ?>-0.005, <?= $coord_subj[0]['minY'] ?>-0.005],
                                        [<?= $coord_subj[0]['maxX'] ?>+0.005, <?= $coord_subj[0]['maxY'] ?>+0.005]
                                    ]
                                });
                                break;
                            default:
                                Array.from(objList).forEach((mel)=>{                        
                                    if (mel.classList.contains('parent_'+event.target.value)) {
                                        mel.style.visibility='visible';    
                                    }
                                    else {
                                        mel.style.visibility='hidden';
                                    }   
                                });
                                const coords = <?php echo json_encode($coord_subjs) ?>;
                                console.debug(coords);
                                let coord_res = coords.find(({ parent_id }) => parent_id === event.target.value);
                                map.setLocation({
                                    bounds: [
                                        [ coord_res['minX'], coord_res['minY']],
                                        [ coord_res['maxX'], coord_res['maxY']]
                                    ]
                                });
                                break;
                        }
                    });
            }

            function getMarkerPopupContent(map, strName, strId, strARM, strReady, strSubj, strAddress) {
                const node = document.createElement("div");
                node.classList.add("card");
                node.classList.add("popup_"+strSubj);
                node.id = "popup" + strId;
                const percent = strReady / strARM * 100;
                console.debug('percent (' + strName + '): ' + percent);
                node.innerHTML =
                        '<div class="card-body">\
                            <div class="card-title" style="font-size:0.75em">' + strName + ' (' + strId + ')' + '</div>\
                            <div class="card-text" style="font-size:0.5em">'+strAddress+'</div>\
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
            width: 30px;
            height: 30px;
            left: -15px;
        }
        .img_0 {
            position:relative;
            width: 16px;
            height: 16px;
            left: -8px;
        }
        .pr_1 {
            position:relative;
            width: 40px;
            height: 5px;
            left: -20px;
            top: -36px;
            background-color: #c8c8c8;
        }
        .pr_0 {
            position:relative;
            width: 26px;
            height: 3px;
            left: -13px;
            top: -22px;
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
        <nav class="navbar navbar-expand-lg bg-body-tertiary">
            <div class="container-fluid"
                <form class="d-flex">
                    <div class="p-2 me-auto h3">Статус на карте</div>
                    <div class="p-2 me-2">Показать</div>
                    <select class="selectpicker p-2 me-2" id="object_selector">
                        <option selected value = -2>Все субъекты с объектами</option>
                        <option value="-1">Все субъекты</option>
                        <optgroup label="Выбрать субъект">
<?php
foreach ($subjList as $subj) {
?>
                            <option value="<?=$subj['id'] ?>"><?=$subj['name']?></option>
<?php
}
?>
                        </optgroup>
                    </select>
                </div> 
            </div>
        </nav>
        <div class="container position-absolute start-50 translate-middle-x" style="width:90%; height:90%" id="map"></div>
    </body>
</html>