<?php 
include($_SERVER['DOCUMENT_ROOT'] . '/wp-load.php');
require_once( $_SERVER["DOCUMENT_ROOT"].'/wp-admin/includes/post.php' );

$userId = json_decode(file_get_contents("php://input"),true);


if(isset($userId['userId'])){
    $user = get_userdata($userId['userId']);
    $user->user_email = '';
    $user->user_pass = '';
    $user = $user;
    
    $response = array ();
    $response = $user;
    echo json_encode($response);
} 