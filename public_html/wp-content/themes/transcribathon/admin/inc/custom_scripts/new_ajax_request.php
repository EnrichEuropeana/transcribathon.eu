<?php

require_once( $_SERVER["DOCUMENT_ROOT"].'/wp-load.php' );
require_once( $_SERVER["DOCUMENT_ROOT"].'/wp-admin/includes/post.php' );

$requestData = json_decode(file_get_contents('php://input'), true);

$url = $requestData['url'];
$type = $requestData['type'];
$data = $requestData['data'];


if(!empty($type) && !empty($url)) {

    if($type == "POST" && !empty($data)) {
        $options = array(
            "http" => array(
                "method" => $type,
                "header" => "Content-type: application/json\r\n",
                "Content-Length: " . strlen($data) . "\r\n",
                "content" => json_encode($data)
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
