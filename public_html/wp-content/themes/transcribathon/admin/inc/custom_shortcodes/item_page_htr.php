<?php

/*
Shortcode: item_page_htr
Description: Gets item data and builds the item page with htr editor
*/

// include required files
include($_SERVER["DOCUMENT_ROOT"].'/wp-load.php');

// Transkribus Client, include required files
require_once(get_stylesheet_directory() . '/htr-client/lib/TranskribusClient.php');
require_once(get_stylesheet_directory() . '/htr-client/config.php');

use FactsAndFiles\Transcribathon\TranskribusClient;

function _TCT_item_page_htr( $atts) {

    global $config;

    // create new Transkribus client and inject configuration
    $transkribusClient = new TranskribusClient($config);

    // get the HTR-transcribed data from database if there is one
    $htrDataJson = $transkribusClient->getDataFromTranscribathon(
        null,
        array(
            'itemId' => $_GET['item'],
		        'orderBy' => 'updated_at',
		        'orderDir' => 'desc'
        )
    );

    // extract the data itself
    $htrDataArray = json_decode($htrDataJson, true);
    $htrData = $htrDataArray['data'][0]['transcription_data'];

    $minimalPageXML = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
        . '<PcGts xmlns="" xmlns:xsi="" xsi:schemaLocation="">'
        .'  <Metadata></Metadata><Page></Page>'
        . '</PcGts>';

    $htrTranscription = strlen($htrData) < 1 ? $minimalPageXML : $htrData;

    if (isset($_GET['item']) && $_GET['item'] != "") {
        // Set request parameters for image data
        $requestData = array(
            'key' => 'testKey'
        );
        $url = TP_API_HOST."/tp-api/items/".$_GET['item'];
        $requestType = "GET";
        $isLoggedIn = is_user_logged_in();

        // Execude http request
        include dirname(__FILE__)."/../custom_scripts/send_api_request.php";

        // Save image data
        $itemData = json_decode($result, true);

    }

    $imgInfo = explode('":"',$itemData['ImageLink']);

    $imgLink = explode(',',$imgInfo[1]);

    $imgJson = str_replace('full/full/0/default.jpg"','info.json',$imgLink[0]);
    $imJLink = '';
    if (substr($imgJson,0,4) != 'http') {
        $imJLink = "https://";
        $imJLink .= $imgJson;
    } else {
        $imJLink = $imgJson;
    }

    $content = '';

    $content .= '<form id="changeEditor" action="'.get_europeana_url().'/documents/story/item/item_page_htr/" method="get" style="position:absolute;bottom:10%;z-index:9999;">';
        $content .= '<input type="number" name="story" value="'.$_GET['story'].'" hidden>';
        $content .= '<input type="number" name="item" value="'.$_GET['item'].'" hidden>';
    $content .= '</form>';


    // Remove padding from page wrapper, otherwise it breaks editor apearance
    $content .= "<style> #primary-full-width { padding: unset!important;} </style>";

/* var_dump(htmlspecialchars($htrTranscription)); */
if($_GET['editor'] == NULL || $_GET['editor'] == 'text') {

    //$content .= '';
    $content .= '<link href="/transkribus-texteditor/transkribus-texteditor-velehanden/custom.css" rel="stylesheet">';
    $content .= '<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>';
    $content .= '<link href="/transkribus-texteditor/transkribus-texteditor-velehanden/css/app.c2e7a107.css" rel="stylesheet" as="style">';
    $content .= '<link href="/transkribus-texteditor/transkribus-texteditor-velehanden/css/chunk-vendors.3ee89ce5.css" rel="stylesheet" as="style">';
    $content .= '<link href="/transkribus-texteditor/transkribus-texteditor-velehanden/js/app.9b333d52.js" rel="preload" as="script" >';
    $content .= '<link href="/transkribus-texteditor/transkribus-texteditor-velehanden/js/chunk-vendors.8c83230e.js" rel="preload" as="script">';
    $content .= '<link href="/transkribus-texteditor/transkribus-texteditor-velehanden/css/chunk-vendors.3ee89ce5.css rel="stylesheet" as="style">';
    $content .= '<link href="/transkribus-texteditor/transkribus-texteditor-velehanden/css/app.c2e7a107.css" rel="stylesheet" as="style">';

    $content .= "<input form='changeEditor' name='editor' value='layout' hidden>";
    $content .= "<input form='changeEditor' type='submit' value='Layout Editor' style='position:absolute;bottom:5%;z-index:9999;width:100px;margin:0auto;'>";
//////
$layoutTranscription = trim(preg_replace('/\s+/', ' ', $htrTranscription));

    $content .= "<div id='transkribusEditor' data-iiif-url='".$imJLink."' data-xml='".$layoutTranscription."'></div>";

    $content .= '<script src="/transkribus-texteditor/transkribus-texteditor-velehanden/js/chunk-vendors.8c83230e.js"></script>';
    $content .= '<script src="/transkribus-texteditor/transkribus-texteditor-velehanden/js/app.9b333d52.js"></script>';

} else {

    //$content = '';

    $content .= "<link rel=\"stylesheet\" href=\"https://fonts.googleapis.com/icon?family=Material+Icons\" />";
    $content .= "<link rel=\"stylesheet\" href=\"https://cdn.jsdelivr.net/npm/@mdi/font@5.8.55/css/materialdesignicons.min.css\" />";
    $content .= "<link rel=\"stylesheet\" href=\"https://use.fontawesome.com/releases/v5.2.0/css/all.css\" />";

    $content .= "<link href='/Layouteditor/dist/css/app.497aabb0.css' rel='preload' as='style' /> ";
    $content .= "<link href='/Layouteditor/dist/css/chunk-vendors.0d1a6cf4.css' rel='preload' as='style' />";
    $content .= "<link href='/Layouteditor/dist/js/app.159aa223.js' rel='preload' as='script' />";
    $content .= "<link href='/Layouteditor/dist/js/chunk-vendors.35d45b29.js' rel='preload' as='script' />";
    $content .= "<link href='/Layouteditor/dist/css/chunk-vendors.0d1a6cf4.css' rel='stylesheet' />";
    $content .= "<link href='/Layouteditor/dist/css/app.497aabb0.css' rel='stylesheet' />";

    $content .= "<input form='changeEditor' name='editor' value='text' hidden>";
    $content .= "<input form='changeEditor' type='submit' value='Text Editor' style='position:absolute;bottom:5%;right:0;z-index:9999;width:100px;margin:0auto;'>";


    $content .= '<div id="app"></div>';

    // Remove line breaks, otherwise layoueditor doesn't work
    $layoutTranscription = trim(preg_replace('/\s+/', ' ', $htrTranscription));
    // Get json file from IIIF
    $layoutImage = file_get_contents($imJLink);
    // Remove line breaks
    $cleanImage = trim(preg_replace('/\s+/', ' ', $layoutImage));

    // Pass data to layout editor
    $content .= '<script>
        window.layoutEditorConfig = {
            xml: \''.$layoutTranscription.'\',
            iiifJson: \''.$cleanImage.'\'
        }
        </script>';

    $content .= "<script src='/Layouteditor/dist/js/chunk-vendors.35d45b29.js'></script>";
    $content .= "<script src='/Layouteditor/dist/js/app.159aa223.js'></script>";


}

        return $content;
}

add_shortcode( 'item_page_htr', '_TCT_item_page_htr' );

?>
