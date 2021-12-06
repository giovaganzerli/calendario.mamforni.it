<?php

session_start();

header('Access-Control-Allow-Headers: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Origin: *');
header('Content-type: application/json');

if(isset($_SESSION['user']) && !empty($_SESSION['user'])) {
    
    $result['result'] = $_SESSION['user'];
    
} else {
    
    $result['result'] = array(
        'username' => 'guest',
        'role' => 'guest'
    );
}

echo json_encode($result);