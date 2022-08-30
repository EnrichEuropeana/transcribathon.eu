<?php

/*
Shortcode: item_page_htr
Description: Gets item data and builds the item page with htr editor
*/

// include required files
include($_SERVER["DOCUMENT_ROOT"].'/wp-load.php');




use FactsAndFiles\Transcribathon\TranskribusClient;


function _TCT_item_page_htr( $atts) {


    // Transkribus Client, include required files
    require(get_stylesheet_directory() . '/lib/transkribus-client/TranskribusClient.php');
    require(get_stylesheet_directory() . '/lib/transkribus-client/config.php');



    // create new Transkribus client and inject configuration
    $transkribusClient = new TranskribusClient($config);

    // get the HTR-transcribed data from database if there is one
    $htrDataJson = $transkribusClient->getDataFromTranscribathon($_GET['item']);

    // extract the data itself
    $htrDataArray = json_decode($htrDataJson, true);
    $htrData = $htrDataArray['data']['data'];

    // show data if existent
    // if ($htrData) {
    // 	echo $htrData;
    // }


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

    //print_r($imgInfo[0]);
    $imgLink = explode(',',$imgInfo[1]);

    $imgJson = str_replace('full/full/0/default.jpg"','info.json',$imgLink[0]);
    $imJLink = '';
    //print_r($imgJson);
    if (substr($imgJson,0,4) != 'http'){
    $imJLink = "https://";
    $imJLink .= $imgJson;
    } else {

    $imJLink = $imgJson;
    }




    if(strlen($htrData)< 1){
        $htrTranscription = "<?xml version=\"1.0\" encoding=\"UTF-8\" standalone=\"yes\"?><PcGts xmlns=\"\"
        xmlns:xsi=\"\" xsi:schemaLocation=\"\"> <Metadata> </Metadata> <Page>  </Page></PcGts>' ";
    } else {
        $htrTranscription = $htrData;
    }

    $content = '';

    $content .= '<form id="changeEditor" action="'.get_europeana_url().'/documents/story/item/item_page_htr/" method="get" style="position:absolute;bottom:10%;z-index:9999;">';
        $content .= '<input type="number" name="story" value="'.$_GET['story'].'" hidden>';
        $content .= '<input type="number" name="item" value="'.$_GET['item'].'" hidden>';
    $content .= '</form>';


    // Remove padding from page wrapper, otherwise it braks editor apearance
    $content .= "<style> #primary-full-width { padding: unset!important;} </style>";




//var_dump(htmlspecialchars($htrTranscription));
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



/* Sample Hardcoded Data
<?xml version=\"1.0\" encoding=\"UTF-8\" standalone=\"yes\"?><PcGts xmlns=\"http://schema.primaresearch.org/PAGE/gts/pagecontent/2013-07-15\"
            xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xsi:schemaLocation=\"http://schema.primaresearch.org/PAGE/gts/pagecontent/2013-07-15 http://schema.primaresearch.org/PAGE/gts/pagecontent.xsd\"> <Metadata> <Creator>prov=University of Rostock/Institute of Mathematics/CITlab/Gundram Leifert/gundram.leifert@uni-rostock.de:name=1791_Purm_Louw(htr_id=17307)::::v=2.4.2prov=University of Rostock/Institue of Mathematics/CITlab/Tobias Gruening/tobias.gruening@uni-rostock.de:name=de.uros.citlab.module.baseline2polygon.B2PSeamMultiOriented:v=2.4.2prov=University of Rostock/Institute of Mathematics/CITlab/Tobias Gruening/tobias.gruening@uni-rostock.de:name=/net_tf/LA73_249_0mod360.pb:de.uros.citlab.segmentation.CITlab_LA_ML:v=?0.1TRP</Creator>
            <created>2019-03-15T13:55:06.726+01:00</Created> <LastChange>2021-01-30T21:53:23.319+01:00</LastChange> </Metadata> <Page imageFilename=\"NL-HlmNHA_143_20_0003.jpg\" imageWidth=\"4128\" imageHeight=\"2676\"> <ReadingOrder> <OrderedGroup id=\"ro_1612040003371\" caption=\"Regions reading order\"> <RegionRefIndexed index=\"0\" regionRef=\"r1\"/> </OrderedGroup> </ReadingOrder> <TextRegion orientation=\"0.0\" id=\"r1\" custom=\"readingOrder {inex:0;}\"> <Coords points=\"2240,425 2240,2164 3887,2164 3887,425\"/> <TextLine id=\"r1l1\" custom=\"readingOrder {index:0;}\"> <Coords points=\"2598,530 3517,515 3517,396 3302,433 3203,388 2800,409 2695,343 2598,345\">
            <Baseline points=\"2609,507 2653,509 2698,509 2743,510 2788,510 2833,510 2878,510 2922,510 2967,510 3012,510 3057,509 3102,509 3147,509 3192,509 3236,509 3281,509 3326,509 3371,509 3416,509 3461,509 3506,510\"> <textEquiv> <Unicode>Vervolg</Unicode> </TextEquiv> </TextLine> <TextLine id=\"r1l2\" custom=\"readingOrder {index:1;}\"> <Coords points=\"2906,704 2943,716 2976,707 3027,725 3064,708 3124,728 3149,710 3186,722 3229,711 3228,596 3191,608 3154,590 3131,599 3091,578 3069,585 2999,565 2937,578 2904,572\">
            <Baseline points=\"2916,695 2941,693 2966,693 2991,693 3016,693 3041,693 3067,693 3092,693 3117,693 3142,693 3167,692 3192,690 3218,689\"> <TextEquiv> <Unicode>der</Unicode> </TextEquiv> </TextLine> <TextLine id=\"r1l4\" custom=\"readingOrder {index:2;}\"> <Coords points=\"2224,1012 2982,948 3333,1085 3534,997 3792,982 3791,665 3573,808 3052,832 2646,815 2396,672 2223,672\"/> <Baseline points=\"2235,961 2623,962 2684,960 2745,959 2806,959 2867,957 2928,957 3050,957 3111,957 3172,959 3233,959 3294,959 3355,959 3416,959 3477,959 3538,959 3599,957 3660,956 3721,956 3782,953\"/>
            <TextEquiv> <Unicode>Kronijk</Unicode> </TextEquiv> </TextLine> <TextLine id=\"r1l6\" custom=\"readingOrder {index:3;}\"> <Coords points=\"2942,1196 3024,1181 3039,1194 3119,1183 3189,1203 3218,1185 3241,1188 3242,1099 3211,1113 3119,1103 3083,1115 3030,1112 2989,1092 2944,1097\"/> <Baseline points=\"2952,1181 2977,1180 3002,1179 3027,1179 3053,1179 3078,1179 3103,1181 3128,1181 3154,1182 3179,1183 3204,1184 3230,1185\"> <TextEquiv> <Unicode>van</Unicode> </TextEquiv> </TextLine> <TextLine id=\"r1l7\" custom=\"readingOrder {index:4;} locatie {offset:0; length:10;}\">
            <Coords points=\"2230,1456 3897,1440 3880,1290 2836,1339 2539,1316 2397,1229 2230,1244\"/> <Baseline points=\"2240,1422 2322,1422 2404,1442 2487,1422 2569,1424 2651,1424 2734,1425 2816,1427 2898,1427 2981,1428 3063,1428 3145,1430 3228,1430 3310,1430 3392,1430 3475,1428 3557,1427 3639,1425 3722,1422 3804,1419 3887,1415\"/> <TextEquiv> <Unicode>PURMERENDE</unicode> </TextEquiv> </TextLine> <textLine id=\"r1l8\" custom=\"readingOrder {index:5;} textStyle {offset:0; length:11;italic:true; fontSize:0.0; kerning:0;}\"> <Coords points=\"2578,1754 2687,1691 2867,1751 2910,1715 3487,1712 3486,1615 3394,1595 3004,1612 2827,1569 2714,1603 2578,1562\"/>
            <Baseline points=\"2588,1698 2632,1698 2676,1699 2721,1700 2765,1700 2810,1700 2854,1700 2898,1700 2943,1701 2987,1701 3032,1701 3076,1701 3120,1701 3165,1700 3209,1700 3254,1700 3298,1698 3342,1698 3387,1697 3431,1695 3476,1694\"/> <textEquiv> <Unicode>TWEEDE DEEL</Unicode> </TextEquiv> </TextLine> <TextLine id=\"r1l9\" custom=\"readingOrder {index:6;}\"> <Coords points=\"2746,1845 2929,1867 3273,1845 3318,1862 3370,1850 3371,1753 3259,1777 3161,1749 3068,1782 3033,1752 2987,1785 2827,1786 2779,1763 2746,1779\"/> <Baseline points=\"2757,1836 2787,1838 2817,1839 2847,1840 2877,1842 2907,1842 2937,1843 2968,1844 2998,1844 3028,1845 3058,1845 3088,1845 3118,1845 3148,1845 3179,1845 3209,1845 3239,1844 3269,1844 3299,1853 3329,1842 3360,1841\"/>
            <TextEquiv> <Unicode>tweede Stuk.</Unicode> </TextEquiv> </TextLine> <TextLine id=\"r1l10\" custom=\"readingOrder {index:7;} datum {offset:0, length:4;datum:1775-xx-xx;} datum {offset:9; length:4;datum:1780-xx-xx;}\"> <Coords points=\"2535,2206 3081,2221 3294,2147 3586,2165 3658,2132 3686,1972 3340,1972 3133,2050 2989,1975 2689,1966 2536,2033\"/> <Baseline points=\"2547,2111 2603,2114 2659,2115 2716,2117 2772,2118 2829,2120 2885,2121 2941,2121 2998,2121 3054,2123 3111,2123 3167,2123 3223,2123 3280,2123 3336,2121 3393,2121 3449,2121 3505,2120 3562,2119 3618,2118 3675,2117\"/> <TextEquiv> <Unicode>1775 tot 1780.</Unicode> </TextEquiv> </TextLine> <TextEquiv> <Unicode>Vervolg&#xD;Kronijk&#xD;van&#xD;PURMERENDE&#xD;TWEEDE DEEL&#xD;tweede Stuk.&#xD1775 tot 1780.</Unicode> <TextEquiv> </TextRegion> </Page></PcGts>'
*/

/* Snippet for empty xml data, to overwrite the last shown transcription!
<?xml version=\"1.0\" encoding=\"UTF-8\" standalone=\"yes\"?><PcGts xmlns=\"\"
            xmlns:xsi=\"\" xsi:schemaLocation=\""> <Metadata> </Metadata> <Page>  </Page></PcGts>'
*/
?>
