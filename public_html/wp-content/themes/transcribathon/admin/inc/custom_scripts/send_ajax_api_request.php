<?php 
//include(str_repeat("../",(sizeof(explode("/",substr((string)getcwd(),strrpos((string)getcwd(),"/wp-content"),strlen((string)getcwd()))))-2))."../wp-load.php");
require_once( $_SERVER["DOCUMENT_ROOT"].'/wp-admin/includes/post.php' );


if(isset($_POST['type']) && isset($_POST['url'])){
    // Set Post content
    $data = array(
    );
    if (isset($_POST['data']) && $_POST['data'] != null) {
        foreach ($_POST['data'] as $key => $value) {
            $data[$key] = $value;
        }
    }
    $postContent = json_encode($data);
    
    // Prepare new cURL resource
    $ch = curl_init($_POST['url']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLINFO_HEADER_OUT, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $_POST['type']);
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