<?php
ini_set('error_reporting', E_ALL);
ini_set('display_errors', true);
require_once 'library.php';

//debug_to_console('REQUEST_METHOD : ' . $_SERVER['REQUEST_METHOD']);
//session_start();

if ($_SERVER['REQUEST_METHOD'] != 'POST' || !isset($_POST['report_date'])) {
    http_response_code(400);
    die(json_encode(['upload_progress' => 'error', 'message_progress' => 'Неверный запрос', 'element_id' => 'ALL']));
}

// Инициализация прогресса
send_progress(0);
//session_write_close();

getUploadedFileName($_FILES['file_report'], $_FILES['file_report_csv'], $_POST['report_date']);
//echo json_encode(['upload_progress' => 'success', 'message' => 'Успешно', 'element_id' => 'progress_load']); 

header("Refresh:0");
