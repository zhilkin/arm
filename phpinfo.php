<?php

require_once 'library.php';
ini_set('error_reporting', E_ALL);
ini_set('display_errors', true);
$key_old = '01a843aa-81bf-42d8-aa71-b9507294f01d';
$key = '84071acc-62c3-41b1-b499-9916054a9b17';

$res = getListFromDatabase("SELECT * FROM op WHERE longitude=0");
foreach ($res as $row) {
    //debug_to_console($row, "row");
    $address = $row['address'];
    $name = null;//$row['name'];
    if (is_null($name)==false and strlen($name) > 0) {
        $url = "https://geocode-maps.yandex.ru/1.x/?apikey=" . $key . "&geocode=" . urlencode($name) . "&format=json";
        debug_to_console($url,"url");
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_REFERER, "http://zhilkin.su");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_URL, $url);
        $result = curl_exec($ch);
        $decoded = json_decode($result);
        debug_to_console($decoded,"result");
        $position = $decoded->response->GeoObjectCollection->featureMember[0]->GeoObject->Point->pos;
        $newaddress = $decoded->response->GeoObjectCollection->featureMember[0]->GeoObject->metaDataProperty->GeocoderMetaData->text;
        $space = strpos($position, ' ');
        $longitude = floatval(substr($position, 0, $space));
        $latitude = floatval(substr($position, $space));
        echo $name . " -> " . $newaddress . " : " . $position . "(" . $longitude . ";" . $latitude . ")";
        echo "<br>";
        $res1 = UpdateDatabase("UPDATE op SET longitude=" . $longitude . ", latitude = " . $latitude . " WHERE id=" . $row['ID']);
        debug_to_console($res1, "after UPDATE");
    }
    if (is_null($address)==false and strlen($address) > 0) {
        $url = "https://geocode-maps.yandex.ru/1.x/?apikey=" . $key . "&geocode=" . urlencode($address) . "&format=json";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_REFERER, "http://zhilkin.su");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_URL, $url);
        $result = curl_exec($ch);
        $decoded = json_decode($result);
        debug_to_console($decoded);
        $position = $decoded->response->GeoObjectCollection->featureMember[0]->GeoObject->Point->pos;
        $newaddress = $decoded->response->GeoObjectCollection->featureMember[0]->GeoObject->metaDataProperty->GeocoderMetaData->text;
        $space = strpos($position, ' ');
        $longitude = floatval(substr($position, 0, $space));
        $latitude = floatval(substr($position, $space));
        echo $address . " -> " . $newaddress . " : " . $position . "(" . $longitude . ";" . $latitude . ")";
        $res1 = UpdateDatabase("UPDATE op SET longitude=" . $longitude . ", latitude = " . $latitude . " WHERE id=" . $row['ID']);
        debug_to_console($res1, "after UPDATE");
    }
}
?>