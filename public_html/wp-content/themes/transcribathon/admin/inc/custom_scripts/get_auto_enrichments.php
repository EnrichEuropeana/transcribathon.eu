<?php

require_once( $_SERVER["DOCUMENT_ROOT"].'/wp-load.php' );
require_once( $_SERVER["DOCUMENT_ROOT"].'/wp-admin/includes/post.php' );

$requestData = json_decode(file_get_contents('php://input'), true);

$storyId = $requestData['storyId'];
$itemId = !empty($requestData['itemId']) ?  $requestData['itemId'] : '';
$property = !empty($requestData['property']) ? $requestData['property'] : 'description';

if(!empty($storyId)) {
    // Try first if there are already generated annotations
    $getAnnoOptions = [
        'http' => [
            'header' => [
                'Content-Type: application/json'
            ],
            'method' => 'GET'
        ]
    ];

    $url = 'https://dsi-demo.ait.ac.at/enrichment-web/enrichment/annotation?property=' . $property . '&storyId=' . $storyId . '&wskey=apidemo';
    if(!empty($itemId)) {
        $url .= '&itemId=' . $itemId;
    }

    $result = sendQuery($url, $getAnnoOptions, true);

    if($result['total'] > 0) {
        // If annotations exist send them back to item page
        echo json_encode($result);
    } else {
        // Else get europeana JWT token and send POST request to create annotation
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

        $postAnnoOptions = [
            'http' => [
                'header' => [
                    'Content-Type: application/json',
                    'Authorization: Bearer' . $europeanaJwt
                ],
                'method' => 'POST'
            ]
        ];

        $url = 'https://dsi-demo.ait.ac.at/enrichment-web/enrichment/annotation/' . $storyId . '/?property=' . $property;
        // add item ID for item specific enrichments
        if($itemId != '') {
            $url = 'https://dsi-demo.ait.ac.at/enrichment-web/enrichment/annotation/' . $storyId . '/' . $itemId . '/?property=' . $property;
        }

        $result = sendQuery($url, $postAnnoOptions, true);

        echo json_encode($result);

    }

}


?>
