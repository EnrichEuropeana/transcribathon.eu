<?php

require_once( $_SERVER["DOCUMENT_ROOT"].'/wp-load.php' );
require_once( $_SERVER["DOCUMENT_ROOT"].'/wp-admin/includes/post.php' );

$requestData = json_decode(file_get_contents('php://input'), true);

$url = $requestData['url'];
$type = $requestData['type'];
$data = $requestData['data'];
$token = $requestData['token'];


if(!empty($type) && !empty($url)) {

    if($type == "POST" && !empty($data)) {
        if($token === 'yes') {
            $options = array(
                "http" => array(
                    "method" => $type,
                    "header" => array(
                        "Content-type: application/json",
                        "Authorization: Bearer " . TP_API_V2_TOKEN
                    ),
                    "content" => json_encode($data)
                )
            );
        } else {
            $options = array(
                "http" => array(
                    "method" => $type,
                    "header" => "Content-type: application/json\r\n",
                    "Content-Length: " . strlen($data) . "\r\n",
                    "content" => json_encode($data)
                )
            );
        }

    } else if ($type == "GET") {
        $options = array(
            "http" => array(
                "method" => $type,
                "header" => "Content-type: application/json\r\n",
            )
        );
    } else if ($type == 'DELETE') {
        $options = array(
            "http" => array(
                "method" => $type,
                "header" => array(
                    "Content-type: application/json",
                    "Authorization: Bearer " . TP_API_V2_TOKEN
                )
            )
        );
    }
    
    $context = stream_context_create($options);
    $result = file_get_contents($url, false, $context);

    if ($result === false) {
        $error = error_get_last();
        echo json_encode("Request error: " . $error['message']);
    } else {
        echo json_encode($result);
    }

}

?>
