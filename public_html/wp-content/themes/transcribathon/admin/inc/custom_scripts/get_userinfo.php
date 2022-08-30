<?php 
include(str_repeat("../",(sizeof(explode("/",substr((string)getcwd(),strrpos((string)getcwd(),"/wp-content"),strlen((string)getcwd()))))-2))."../wp-load.php");
require_once( $_SERVER["DOCUMENT_ROOT"].'/wp-admin/includes/post.php' );


if(isset($_POST['userId'])){
    $i = $_POST['index'];
    $user = get_userdata($_POST['userId']);
    
    $response = array ();
    $response['index'] = $i;
    $response['user'] = $user;
    echo json_encode($response);
}