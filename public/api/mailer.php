<?php

header('Access-Control-Allow-Headers: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Origin: *');
header('Content-type: application/json');

if(isset($_POST['to']) && !empty($_POST['to'])) {
    
    $from = $_POST['from'];
    $to = $_POST['to'];
    $subject = $_POST['subject'];
    $message = $_POST['message'];
    
    $headers = "From: MAM Forni - Calendario Ordini <" . $from .">"."\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=utf-8\r\n";
    
    mail($to, $subject, $message, $headers);
    
    $result['result'] = true;
    
} else {
    
    $result['result'] = false;
}

echo json_encode($result);