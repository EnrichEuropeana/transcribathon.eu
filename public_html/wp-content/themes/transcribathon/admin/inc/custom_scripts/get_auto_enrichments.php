<?php

require_once( $_SERVER["DOCUMENT_ROOT"].'/wp-load.php' );
require_once( $_SERVER["DOCUMENT_ROOT"].'/wp-admin/includes/post.php' );

$requestData = json_decode(file_get_contents('php://input'), true);

$storyId = $requestData['storyId'];
$itemId = !empty($requestData['itemId']) ?  $requestData['itemId'] : '';
$property = !empty($requestData['property']) ? $requestData['property'] : 'description';


if($storyId != '') {
    // Check first if the annotations are created
    $getEnrichmentOptions = [
        'http' => [
            'Content-Type: application/json'
        ], 
        'method' => 'GET'
    ];

    $enrichmentsUrl = 'https://dsi-demo.ait.ac.at/enrichment-web/enrichment/annotation?property=' . $property . '&storyId=' . $storyId . '&wskey=apidemo';
    if($itemId != '') {
        $enrichmentsUrl .= '&itemId=' . $itemId;
    }

    $autoEnrichments = sendQuery($enrichmentsUrl, $getEnrichmentOptions, true);

    if($autoEnrichments['total'] < 1) {
        // Get JWT token from europeana
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

        // Create annotations 
        $postEnrichmentOptions = [
            'http' => [
                'header' => [
                    'Content-Type: application/json',
                    'Authorization: Bearer' . $europeanaJwt
                ],
                'method' => 'POST'
            ]
        ];
    
        $enrichmentsUrl = $itemId != '' ? 'https://dsi-demo.ait.ac.at/enrichment-web/enrichment/annotation/' . $storyId . '/' . $itemId . '?property=' . $property : 'https://dsi-demo.ait.ac.at/enrichment-web/enrichment/annotation/' . $storyId . '?property=' . $property;

        $autoEnrichments = sendQuery($enrichmentsUrl, $postEnrichmentOptions, true);


    } 
    // GET translation and add it to the auto enrichments
    $getTranslationOptions = [
        'http' => [
            'Content-Type: text/plain'
        ],
        'method' => 'GET'
    ];
    $urlParams = '?property=' . $property . '&translationTool=Google&wskey=apidemo';
    $pathParams = $itemId != '' ? $storyId . '/' . $itemId : $storyId;
    
    $translationUrl = 'https://dsi-demo.ait.ac.at/enrichment-web/enrichment/translation/';
    
    $translationUrl .= $pathParams . $urlParams; 
    $translation = sendQuery($translationUrl, $getTranslationOptions);
    


    $autoEnrichments['translation'] = $translation;

    echo json_encode($autoEnrichments);


} else {
    echo json_encode('Sorry something went wrong!');
}


?>
