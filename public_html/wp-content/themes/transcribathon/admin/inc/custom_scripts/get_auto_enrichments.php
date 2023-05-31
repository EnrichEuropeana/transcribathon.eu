<?php

require_once( $_SERVER["DOCUMENT_ROOT"].'/wp-load.php' );
require_once( $_SERVER["DOCUMENT_ROOT"].'/wp-admin/includes/post.php' );

$requestData = json_decode(file_get_contents('php://input'), true);

$storyId = $requestData['storyId'];
$itemId = !empty($requestData['itemId']) ? '/' . $requestData['itemId'] . '/' : '';
$property = !empty($requestData['property']) ? $requestData['property'] : 'transcription';
/// get europeana jwt token
$getEuropeanaTokenOptions = [
    'http' => [
        'header' => [
            'Content-Type: application/x-www-form-urlencoded'
        ],
        'method' => 'POST',
        'content' => http_build_query([
            'username' => EUROPEANA_USER,
            'password' => EUROPEANA_PASS,
            'grant_type' => 'password',
            'client_id' => EUROPEANA_CLIENT_ID,
            'client_secret' => EUROPEANA_CLIENT_SECRET,
            'scope' => 'entities'
        ])
    ]
];
$europeanaJwtResponse = sendQuery('https://auth.europeana.eu/auth/realms/europeana/protocol/openid-connect/token', $getEuropeanaTokenOptions, true);
$europeanaJwt = $europeanaJwtResponse['access_token'];
///


$url = 'https://dsi-demo.ait.ac.at/enrichment-web/enrichment/annotation/' . $storyId . $itemId . '?property=' . $property;
$type = 'POST';



$options = array(
    'http' => [
        'header' => [
            'Content-Type: application/json',
            'Authorization: Bearer' . $europeanaJwt
        ],
        'method' => 'POST'
    ]
);

$context = stream_context_create($options);
$result = file_get_contents($url, false, $context);
echo json_encode($result);



?>
