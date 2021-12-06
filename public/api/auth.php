<?php

session_start();

header('Access-Control-Allow-Headers: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Origin: *');
header('Content-type: application/json');

require_once('./users.php');

$result['result'] = false;

if(isset($_POST['auth']) && $_POST['auth'] == 'login') {
    
    $user_name = $_POST['username'];
    $user_pw = $_POST['password'];
    
    if($user_pw == $users[$user_name]['pw']) {
        
        // Valid login
        $_SESSION['user'] = array(
            'username' => $users[$user_name]['username'],
            'role' => $users[$user_name]['role']
        );
        
        $result['result'] = $_SESSION['user'];
        
    } else {
        
        // Invalid Login
        $result['result'] = false;
    }
    
} elseif(isset($_POST['auth']) && $_POST['auth'] == 'logout') {
    
    $_SESSION['user'] = array(
        'username' => 'guest',
        'role' => 'guest'
    );
    $result['result'] = $_SESSION['user'];
}

echo json_encode($result);