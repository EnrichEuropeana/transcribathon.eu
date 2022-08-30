<?php 
include(str_repeat("../",(sizeof(explode("/",substr((string)getcwd(),strrpos((string)getcwd(),"/wp-content"),strlen((string)getcwd()))))-2))."../wp-load.php");
require_once( $_SERVER["DOCUMENT_ROOT"].'/wp-admin/includes/post.php' );


if(isset($_POST['text'])){
    $newText = strip_tags($_POST['text']);

    echo $newText;
}