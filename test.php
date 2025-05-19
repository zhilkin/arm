<?php
require_once 'library.php';

ini_set('error_reporting', E_ALL);
ini_set('display_errors', true);

$url = 'https://code.highcharts.com/mapdata/countries/ru/custom/ru-all-disputed.topo.json';
$ch = curl_init();
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_URL, $url);
$result = curl_exec($ch);
$decoded = json_decode($result);
echo '<table>';
foreach ($decoded->objects->default->geometries as $geo_data) {
    debug_to_console($geo_data);
    echo '<tr><td>'.$geo_data->id.'</td><td>'.$geo_data->properties->hc-key.'</td></tr>';
}
echo '</table>';
?>




