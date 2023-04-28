<?php

require_once( $_SERVER["DOCUMENT_ROOT"].'/wp-load.php' );
require_once( $_SERVER["DOCUMENT_ROOT"].'/wp-admin/includes/post.php' );


$url = $_POST['url'];
$type = $_POST['type'];
$data = $_POST['data'];

if(!empty($type) && !empty($url)) {

    if($type == "POST" && !empty($data)) {
        $options = array(
            "http" => array(
                "method" => $type,
                "header" => "Content-type: application/json\r\n",
                "Content-Length: " . strlen($data) . "\r\n",
                "content" => $data
            )
        );
    } else if ($type == "GET") {
        $options = array(
            "http" => array(
                "method" => $type,
                "header" => "Content-type: application/json\r\n",
            )
        );
    }
    
    $context = stream_context_create($options);
    $result = file_get_contents($url, false, $context);
    echo json_encode($result);

}



?>
