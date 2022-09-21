<?php

require_once( $_SERVER["DOCUMENT_ROOT"].'/wp-load.php' );
require_once( $_SERVER["DOCUMENT_ROOT"].'/wp-admin/includes/post.php' );

$post = count($_POST) > 0 ? $_POST : json_decode(file_get_contents('php://input'), true);

if (!empty($post['type']) && !empty($post['url'])) {

    // Set Post content
    $data = array();
    if (isset($post['data']) && $post['data'] != null) {
        foreach ($post['data'] as $key => $value) {
            $data[$key] = $value;
        }
    }
    $postContent = json_encode($data);

    // Prepare new cURL resource
    $ch = curl_init($post['url']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLINFO_HEADER_OUT, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $post['type']);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postContent);

    // Set HTTP Header for request
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/json',
        'Content-Length: ' . strlen($postContent))
    );

    // Submit the request
    $result = curl_exec($ch);

    // Get response code
    $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    // Close cURL session handle
    curl_close($ch);

    // return response
    $response = array ();
    $response['content'] = "".$result;
    $response['code'] = "".$httpcode;
    $response['post'] = "".$data['PropertyValue'];
    echo json_encode($response);
}
