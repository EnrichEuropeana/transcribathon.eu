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

    $textEditorUrl = get_stylesheet_directory_uri() . '/htr-client/texteditor/';
    $layoutEditorUrl = get_stylesheet_directory_uri() . '/htr-client/layouteditor/';
    $requestUri = get_stylesheet_directory_uri() . '/htr-client/request.php';
    $homeUri = home_url();

    $isLoggedIn = is_user_logged_in();

    if (empty($_GET['item'])) {
        echo '<h1 class="entry-title">No item found.</h1>';
        echo '<p>No item specified.</p>';
        return;
    }

    if (!$isLoggedIn) {
        echo '<h1 class="entry-title">Not logged in.</h1>';
        echo '<p>Please login to proceed.</p>';
        return;
    }

    // create new Transkribus client and inject configuration
    $transkribusClient = new TranskribusClient($config);

    // get the HTR-transcribed data from database if there is one
    $htrDataJson = $transkribusClient->getDataFromTranscribathon(
        null,
        array(
            'ItemId' => $_GET['item'],
		        'orderBy' => 'LastUpdated',
		        'orderDir' => 'desc'
        )
    );

    // extract the data itself
    $htrDataArray = json_decode($htrDataJson, true);
    $htrData = $htrDataArray['data'][0]['TranscriptionData'];


    $minimalPageXML = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
        . '<PcGts xmlns="" xmlns:xsi="" xsi:schemaLocation="">'
        .'  <Metadata></Metadata><Page></Page>'
        . '</PcGts>';

    $htrTranscription = strlen($htrData) < 1 ? $minimalPageXML : $htrData;

    // Set request parameters for image data
    $requestData = array('key' => 'testKey');
    $url = TP_API_HOST."/tp-api/items/".$_GET['item'];
    $requestType = "GET";

    // Execude http request
    include dirname(__FILE__)."/../custom_scripts/send_api_request.php";

    // Save image data
    $itemData = json_decode($result, true);
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

    $transcription = trim(preg_replace('/\s+/', ' ', $htrTranscription));
    $transcription = htmlspecialchars($transcription);







    $wpUserId = get_current_user_id();
    $url = TP_API_HOST."/tp-api/users/".$wpUserId;
    $requestType = "GET";
    include dirname(__FILE__)."/../custom_scripts/send_api_request.php";
    $userData = json_decode($result, true);
    $userId = $userData[0]['UserId'];
    $itemId = $_GET['item'];
    $storyId = $_GET['story'];

    $content = '';
    $content .= '<form id="changeEditor" action="'.get_europeana_url().'/documents/story/item/item_page_htr/" method="get" style="position:absolute;bottom:10%;z-index:9999;">';
        $content .= '<input type="number" name="story" value="'.$_GET['story'].'" hidden>';
        $content .= '<input type="number" name="item" value="'.$_GET['item'].'" hidden>';
    $content .= '</form>';


    // Remove padding from page wrapper, otherwise it breaks editor apearance
    $content .= "<style> #primary-full-width { padding: unset!important;} </style>";

    if($_GET['editor'] == NULL || $_GET['editor'] == 'text') {

        $htrEditor = <<<HED
<link href="{$textEditorUrl}css/app.c2e7a107.css" rel=preload as=style>
<link href="{$textEditorUrl}css/chunk-vendors.3ee89ce5.css" rel=preload as=style>
<link href="{$textEditorUrl}js/app.83160130.js" rel=preload as=script>
<link href="{$textEditorUrl}js/chunk-vendors.8c83230e.js" rel=preload as=script>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.1/jquery.min.js"></script>
<style>
button.button.is-success {
    background: #0a72cc!important;
}
footer._tct_footer, footer.site-footer {
    display: none;
}
#go-home {
    margin-top:8px;
    height:39px;
    padding:7px 5px 5px 5px;
    text-decoration:none;
    background:#0a72cc;
    color:#fff;
    text-transform:none;
    font-size: 14px;
    font-weight: bolder;
}
</style>
<link href="{$textEditorUrl}css/app.c2e7a107.css" rel="stylesheet" />
<link href="{$textEditorUrl}css/chunk-vendors.3ee89ce5.css" rel="stylesheet" />
<link href="{$textEditorUrl}custom.css" rel="stylesheet" />
<input form="changeEditor" name="editor" value="layout" hidden />
<input form="changeEditor" type="submit" value="Layout Editor" style="display:none;" />
<a id="go-home" href="{$homeUri}/documents/story/item/?story={$storyId}&item={$itemId}">Back to Item</a>;
<div
    id="transkribusEditor"
    ref="editor"
    data-iiif-url='{$imJLink}',
    data-xml= "{$transcription}"
>
</div>
<script>
var ready = (callback) => {
    if (document.readyState != "loading") callback();
    else document.addEventListener("DOMContentLoaded", callback);
}
// Replacement for jQuery document.ready; It runs the code after DOM is completely loaded
ready(() => {
    document.querySelector('button[title="Switch view"]').click();

    const backBtn = document.querySelector('#go-home');
    const controlBar = document.querySelector('.editor__header');

    controlBar.querySelector('nav').appendChild(backBtn);
    controlBar.querySelector('nav').style.overflow = 'hidden';

})
</script>
<script src="{$textEditorUrl}js/chunk-vendors.8c83230e.js"></script>
<script src="{$textEditorUrl}js/app.83160130.js"></script>
<script>




    window.eventBus.\$on('save', async (data) => {

        const payload = {
            ItemId: {$itemId},
            UserId: {$userId},
            TranscriptionData: data.xml
        };

        const sendData = await fetch('{$requestUri}', {
            method: 'POST',
            body: JSON.stringify(payload)
        });

        const result = await sendData.json();

        if (result && result.success === true) {

            alert('The entry has been updated.');

        } else {

            alert('The entry could not be saved.');

        }
    });
</script>
HED;

        $content .= $htrEditor;


    } else {

        // Remove line breaks, otherwise layoueditor doesn't work
        $layoutTranscription = trim(preg_replace('/\s+/', ' ', $htrTranscription));
        // Get json file from IIIF
        $layoutImage = file_get_contents($imJLink);
        // Remove line breaks
        $cleanImage = trim(preg_replace('/\s+/', ' ', $layoutImage));

        $layoutEdtitor = <<<LED
<link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@mdi/font@5.8.55/css/materialdesignicons.min.css" />
<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.2.0/css/all.css" />
<link href="{$layoutEditorUrl}css/chunk-vendors.0d1a6cf4.css" rel="stylesheet" />
<link href="{$layoutEditorUrl}css/app.497aabb0.css" rel="stylesheet" />
<input form="changeEditor" name="editor" value="text" hidden>
<input form="changeEditor" type="submit" value="Text Editor" style="position:absolute;bottom:5%;right:0;z-index:9999;width:100px;margin:0auto;">
<div id="app"></div>
<script>
    window.layoutEditorConfig = {
        xml: `{$layoutTranscription}`,
        iiifJson: '{$cleanImage}'
    }
</script>';
<script src="{$layoutEditorUrl}js/chunk-vendors.35d45b29.js"></script>
<script src="{$layoutEditorUrl}js/app.159aa223.js"></script>
<style>
footer._tct_footer, footer.site-footer {
    display: none;
}
body {
    overflow-y: hidden;
    height: 100vh;
}
</style>
<script>
var ready = (callback) => {
    if (document.readyState != "loading") callback();
    else document.addEventListener("DOMContentLoaded", callback);
}
ready(() => {

    window.onLayoutSave = async (xml) => {

        const payload = {
            ItemId: {$itemId},
            UserId: {$userId},
            TranscriptionData: xml
        };

        const sendData = await fetch('{$requestUri}', {
            method: 'POST',
            body: JSON.stringify(payload)
        });

        const result = await sendData.json();

        if (result && result.success === true) {

            alert('The entry has been updated.');

        } else {

            alert('The entry could not be saved.');

        }
    };

})
</script>
LED;

        $content .= $layoutEdtitor;

    }

    return $content;
}

add_shortcode( 'item_page_htr', '_TCT_item_page_htr' );

?>
