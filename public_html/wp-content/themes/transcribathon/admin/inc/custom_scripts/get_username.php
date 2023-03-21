<?php 
include($_SERVER['DOCUMENT_ROOT'] . '/wp-load.php');
require_once( $_SERVER["DOCUMENT_ROOT"].'/wp-admin/includes/post.php' );


if(isset($_POST['userId'])){
    $user = get_userdata($_POST['userId']);
    $user->user_email = '';
    $user->user_pass = '';
    $user = $user;
    
    $response = array ();
    $response = $user;
    echo $response;
} else {
    echo json_encode(array('a' => 1, 'b' => 2, 'c' => 3, 'd' => 4, 'e' => 5));
}