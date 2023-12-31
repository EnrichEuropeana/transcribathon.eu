<?php

/*
Shortcode: item_page_htr
Description: Gets item data and builds the item page with htr editor
*/

// include required files
include($_SERVER["DOCUMENT_ROOT"].'/wp-load.php');

date_default_timezone_set('Europe/Berlin');

function _TCT_compare_transcriptions( $atts) {
    global $ultimatemember, $config;
    if (isset($_GET['item']) && $_GET['item'] != "") {

        $itemId = intval($_GET['item']);

	    $alpineJs = get_stylesheet_directory_uri(). '/js/alpinejs.3.10.4.min.js';

        // Set request parameters for image data
        $requestData = array(
            'key' => 'testKey'
        );
        $url = TP_API_HOST."/tp-api/items/".$itemId;
        $requestType = "GET";
        $isLoggedIn = is_user_logged_in();
        // Execude http request
        include dirname(__FILE__)."/../custom_scripts/send_api_request.php";
        // Save image data
        $itemData = json_decode($result, true);
        if ($itemData['StoryId'] != null) {
            // Set request parameters for story data
            $url = TP_API_HOST."/tp-api/itemPage/".$itemData['StoryId'];
            $requestType = "GET";
            // Execude http request
            include dirname(__FILE__)."/../custom_scripts/send_api_request.php";
            // Save story data
            $itemPageData = json_decode($result, true);
            $statusTypes = $itemPageData['CompletionStatus'];
            $fieldMappings = $itemPageData['FieldMappings'];
            $languages = $itemPageData['Languages'];
            $categories = $itemPageData['Categories'];
            $itemImages = $itemPageData['ItemImages'];
        }

        //include theme directory for text hovering
        $theme_sets = get_theme_mods();
        // Transkribus Client, include required files
        $getJsonOptions = [
            'http' => [
                'header' => [
                    'Content-type: application/json',
                    'Authorization: Bearer ' . TP_API_V2_TOKEN
                ],
                'method' => 'GET'
            ]
        ];
    
        $htrDataArray = sendQuery(TP_API_V2_ENDPOINT . '/htrdata?ItemId=' . $itemId, $getJsonOptions, true);        
        $htrData = $htrDataArray['data'][0]['TranscriptionData'];

    // Build required components for the page
    $content = "";

    // Lock item if user is not logged in or someone else is Enriching Item
    $locked = false;
    if ($isLoggedIn && ($itemData['LockedTime'] < date("Y-m-d H:i:s") || get_current_user_id() == $itemData['LockedUser'])) {
        $content .= '<script>
                        // Lock document
                        // Prepare data and send API request
                        data = {
                                };
                        var today = new Date();
                        today = new Date(today.getTime() + 60000);
                        var dateTime = today.getFullYear() + "-" + (today.getMonth()+1) + "-" + today.getDate() + " " + today.getHours() + ":" + today.getMinutes() + ":" + today.getSeconds();
                        data["LockedTime"] = dateTime;
                        data["LockedUser"] = '.get_current_user_id().';

                        var dataString= JSON.stringify(data);
                        jQuery.post("'.home_url().'/wp-content/themes/transcribathon/admin/inc/custom_scripts/send_ajax_api_request.php", {
                            "type": "POST",
                            "url": home_url + "/tp-api/items/" + '.$_GET['item'].',
                            "data": data
                        },
                        // Check success and create confirmation message
                        function(response) {
                        var response = JSON.parse(response);
                        if (response.code == "200") {
                            return 1;
                        }
                        else {
                        }
                        });
                        setInterval(function() {
                            // Prepare data and send API request
                            data = {
                                    };
                            var today = new Date();
                            today = new Date(today.getTime() + 60000);
                            var dateTime = today.getFullYear() + "-" + (today.getMonth()+1) + "-" + today.getDate() + " " + today.getHours() + ":" + today.getMinutes() + ":" + today.getSeconds();
                            data["LockedTime"] = dateTime;
                            data["LockedUser"] = '.get_current_user_id().';

                            var dataString= JSON.stringify(data);
                            jQuery.post("'.home_url().'/wp-content/themes/transcribathon/admin/inc/custom_scripts/send_ajax_api_request.php", {
                                "type": "POST",
                                "url": home_url + "/tp-api/items/" + '.$_GET['item'].',
                                "data": data
                            },
                            // Check success and create confirmation message
                            function(response) {
                            var response = JSON.parse(response);
                            if (response.code == "200") {
                                return 1;
                            }
                            });
                        }, 55 * 1000);
                    </script>';
    }
    else if ($isLoggedIn) {
        $locked = true;
    }

    $content .= "";
    // Large spinner
    $content .= "<div class='full-spinner-container'>";
        $content .= "<div class='spinner-full'></div>";
    $content .= "</div>";

    // Locked warning
    $content .= "<div id='locked-warning-container'>";
        $content .= "<div class='locked-warning-popup'>";
            $content .= '<i id="close-locked-window" class="fas fa-times view-switcher-icons theme-color"
                        onClick="jQuery(\'#locked-warning-container\').css(\'display\', \'none\')"></i>';
                $content .= "<h2 class='locked-text1'>";
                    $content .= "Someone else is currently editing this document";
                $content .= "</h2>";
                $content .= "<h4 class='locked-text2'>";
                    $content .= "Only one person can work on a document at a time";
                $content .= "</h4>";
        $content .= "</div>";
    $content .= "</div>";

    // Line height fix
    $content .= "<style>.entry-content{line-height:1.2em!important;}</style>";

    // Get the current transcription
    $currentTranscription = null;
    $transcriptionList = [];
    if($itemData['Transcriptions'] != null) {
        foreach($itemData['Transcriptions'] as $transcription) {
            if($transcription['CurrentVersion'] == '1') {
                $currentTranscription = $transcription;
            } else {
                array_push($transcriptionList, $transcription);
            }
        }
    }
    // Get the progress data
    $progressData = array(
        $itemData['TranscriptionStatusName'],
        $itemData['DescriptionStatusName'],
        $itemData['LocationStatusName'],
        $itemData['TaggingStatusName']
    );
    $progressCount = array(
        'Not Started' => 0,
        'Edit' => 0,
        'Review' => 0,
        'Completed' => 0
    );
    foreach($progressData as $status) {
        $progressCount[$status] += 1;
    }
    $imageData = explode(',', $itemData['ImageLink']);
    $imageWidth = explode(':', $imageData[2]);
    $imageHeight = explode(':', $imageData[3]);
    $imageLinkDirty = explode('":', $imageData[0]);
    $imageLink = str_replace('full/full/0/default.jpg', 'info.json', $imageLinkDirty[1]);

    // Image viewer
    $imageViewer = "";
    $imageViewer .= "<div id='openseadragon' style='height:600px;'>";
        //$imageViewer .= "<input type='hidden' id='image-data-holder' value='".$itemData['ImageLink']."'>";
        // Pass Image to the viewer
        $imageViewer .= '<div id="image-json-link" hidden>'. trim($imageLink, '"') .'</div>';
        $imageViewer .= '<div id="image-height" hidden>' . $imageHeight[1] . '</div>';
        $imageViewer .= '<div id="image-width" hidden>' . $imageWidth[1] . '</div>';
        // viewer buttons(regular viewe)
        $imageViewer .= "<div class='buttons' id='buttons'>";
            $imageViewer .= "<div id='zoom-in' class='theme-color theme-color-hover'><i class='fas fa-plus'></i></div>";
            $imageViewer .= "<div id='zoom-out' class='theme-color theme-color-hover'><i class='fas fa-minus'></i></div>";
            $imageViewer .= "<div id='home' title='View full image' class='theme-color theme-color-hover'><i class='fas fa-home'></i></div>";
            $imageViewer .= "<div id='rotate-right' class='theme-color theme-color-hover'><i class='fas fa-redo'></i></div>";
            $imageViewer .= "<div id='rotate-left' class='theme-color theme-color-hover'><i class='fas fa-undo'></i></div>";
        $imageViewer .= "</div>";
    $imageViewer .= "</div>"; // End of Image Viewer

    

    // Item progress bar
    $itemProgress = array(
        'Not Started' => 0,
        'Edit' => 0,
        'Review' => 0,
        'Completed' => 0
    );
    $itemProgress[$itemData['TranscriptionStatusName']] += 25;
    $itemProgress[$itemData['DescriptionStatusName']] += 25;
    $itemProgress[$itemData['LocationStatusName']] += 25;
    $itemProgress[$itemData['TaggingStatusName']] += 25;

    //$content .= "<div id='main-section'>";
        // Build Page Layout

        // Title
    $content .= "<section id='title-n-progress'>";
    $content .= "<div class='back-to-story'><a href='" . home_url() . "/documents/story/?story=" . $itemData['StoryId'] . "'><i class='fas fa-arrow-left' style='margin-right:7.5px;'></i> Back to the Story </a></div>";
    $content .= "<div class='back-to-story' style='bottom:35px;'><a href='" . home_url() . "/documents/story/item/?item=". $itemData['ItemId'] ."'><i class='fas fa-arrow-left' style='margin-right:7.5px;'></i> Back to the Item </a></div>";
        $content .= "<div class='title-n-btn'>";
            $content .= "<h4 id='item-header'><b>" . $itemData['Title'] . "</b></h4>";
        $content .= "</div>";

        $content .= "<div class='item-progress'>";
                 // Status changer placeholder
        $content .= "</div>";
    $content .= "</section>";

    $content .= "<section id='full-width-viewer'>";
        $content .= $imageViewer;
    $content .= "</section>";

    $content .= "<section id='viewer-n-transcription' class='collapsed'>";
        $content .= "<div id='full-view-container'>";
            // Mark as active checkboxes
            $content .= "<div class='mark-active' x-data='activeTranscription({$itemId})'>";
                $content .= "<label>";
                    $content .= "<input type='radio' name='mark_active' value='htr' x-model='source' :checked='source === \"htr\"'>";
                    $content .= "Mark HTR Transcription as active";
                $content .= "</label>";
                    $content .= "<label>";
                        $content .= "<input type='radio' name='mark_active' value='manual' x-model='source' :checked='source === \"manual\"'>";
                        $content .= "Mark Manual Transcription as active";
                    $content .= "</label>";
            $content .= "</div>";
        //$content .= "<section id='viewer-n-transcription'>";
            $content .= "<div id='full-view-l' style='margin-bottom:50px;'>";
                $content .= "<div id='htr-container'>";
                    $content .= "<a href='" . home_url() . "/documents/story/item-page-htr/?story=". $itemData['StoryId'] ."&item=" . $itemData['ItemId'] . "'><div id='startHtrTranscription' style='display:flex;flex-direction:row;justify-content:space-between;cursor:pointer;padding-left:0;padding-right:0;' title='click to open editor'>";
                        $content .= "<div style='display:inline-block;'><h5 style='color:#0a72cc;'><i style=\"font-size: 20px;margin-bottom:5px;\" class=\"fa fa-quote-right\" aria-hidden=\"true\"></i> HTR TRANSCRIPTION</h5></div>";
                        $content .= "<div>";
                            $content .= "<div id='htr-status' class='status-display' style='background-color:#fff700;'>";
                                $content .= "<span class='status-indicator-view' style='bottom:0px;'>EDIT</span>";
                            $content .= "</div>";
                            $content .= "<i class=\"fa fa-pencil right-i\" aria-hidden=\"true\"></i>";
                        $content .= "</div>";
                    $content .= "</div></a>";
                    $htrTranscriptionText = get_text_from_pagexml($htrData, '<br />');
                    $content .= "<div style='background-image:linear-gradient(14deg,rgba(255,255,255,1),rgba(238,236,237,0.4),rgba(255,255,255,1));height:5px'> &nbsp </div>";
                    if($itemData['Transcriptions'][0]['NoText'] == '1') {
                        $content .= "<div id='htr-no-text-placeholder'>";
                            $content .= "<p style='position:relative;top:30%;'><i class=\"far fa-check-circle\" ></i> <b>ITEM CONTAINS <br> NO TEXT</b></p>";
                        $content .= "</div>";
                    } else {

                        if(strlen($htrTranscriptionText) < 700 && strlen($htrTranscriptionText) != 0) {
                            $content .= "<div class='htr-current-transcription' style='padding-left:0px;'>";
                                $content .= $htrTranscriptionText;
                            $content .= "</div>";

                        } else if(strlen($htrTranscriptionText) != 0) {
                            $content .= "<div class='htr-current-transcription' style='padding-left:0px;'>";
                                $content .= $htrTranscriptionText;
                            $content .= "</div>";

                        } else {
                            $content .= "<script>
                                document.querySelector('#htr-status').style.backgroundColor = '#eeeeee';
                                document.querySelector('#htr-status span').textContent = 'NOT STARTED';
                            </script>";
                            $content .= "<div id='htr-no-text-placeholder'>";
                                $content .= "<p style='position:relative;top:40%;'><i style='top:-5px;' class='fas fa-robot'></i><span>RUN HTR TRANSCRIPTION</span></p>";
                            $content .= "</div>";
                        }
                    }
                $content .= "</div>";

            $content .= "</div>"; // end of transcription

            $content .= "<div id='full-view-r' style='margin-bottom:50px;'>";
            //var_dump($itemData);
                // Transcription
                $content .= "<div id='transcription-container'>";
                    $content .= "<a href='" . home_url() . "/documents/story/item/?item=". $itemData['ItemId'] ."&fs=true'><div id='startTranscription' style='display:flex;flex-direction:row;justify-content:space-between;cursor:pointer;padding-left:0;padding-right:0;' title='click to open editor'>";
                        $content .= "<div style='display:inline-block;'><h5 style='color:#0a72cc;'><i style=\"font-size: 20px;margin-bottom:5px;\" class=\"fa fa-quote-right\" aria-hidden=\"true\"></i> TRANSCRIPTION</h5></div>";
                        $content .= "<div>";
                            $content .= "<div class='status-display' style='background-color:".$itemData['TranscriptionStatusColorCode']."'>";
                                $content .= "<span class='status-indicator-view' style='bottom:0px;'>" . $itemData['TranscriptionStatusName'] . "</span>";
                            $content .= "</div>";
                            $content .= "<i class=\"fa fa-pencil right-i\" aria-hidden=\"true\"></i>";
                        $content .= "</div>";
                    $content .= "</div></a>";
                    $content .= "<div style='background-image:linear-gradient(14deg,rgba(255,255,255,1),rgba(238,236,237,0.4),rgba(255,255,255,1));height:5px'> &nbsp </div>";
                    if($itemData['Transcriptions'][0]['NoText'] == '1') {
                        $content .= "<div id='no-text-placeholder'>";
                            $content .= "<p style='position:relative;top:30%;'><i class=\"far fa-check-circle\" ></i> <b>ITEM CONTAINS <br> NO TEXT</b></p>";
                        $content .= "</div>";
                    } else {
                        if(!str_contains(strtolower($currentTranscription['Text']),'<script>')) {
                            $formattedTranscription = htmlspecialchars_decode($currentTranscription['Text']);
                        }
                        if(strlen($formattedTranscription) < 700 && strlen($formattedTranscription) != 0) {
                            $content .= "<div class='current-transcription' style='padding-left:24px;'>";
                                $content .= $formattedTranscription;
                            $content .= "</div>";

                        } else if(strlen($formattedTranscription) != 0) {
                            $content .= "<div class='current-transcription' style='padding-left:24px;'>";
                                $content .= $formattedTranscription;
                            $content .= "</div>";

                        } else {
                            $content .= "<div id='no-text-placeholder'>";
                                $content .= "<p style='position:relative;top:40%;'><a href='" . home_url() . "/documents/story/item/?item=". $itemData['ItemId'] ."&fs=true'><img src='".home_url()."/wp-content/themes/transcribathon/images/pen_in_circle.svg'></a></p>";
                            $content .= "</div></div>";
                        }
                    }
                $content .= "</div>";

            $content .= "</div>"; // end of transcription
        $content .= "</div>";
    $content .= "</section>";
    $content .= "<div style='clear:both;'></div>";

    




        $content .= "</div>"; // end of full-view-container
    $content .= "<style>
    #primary-full-width { padding: unset!important;}
    .highlight { color: #0a72cc;outline: 0.8px solid;}
    metadata {
        display: none;
    }
    .full-container {
        margin-top: 0!important;
    }
    #masthead nav[role=navigation] {
        position: unset;
        width: unset;
    }
    /* --- Back to story Button -- */
    .back-to-story {
        display: inline-block;
        font-size: 18px;
        position: absolute;
        bottom: 10px;
        left: 100px;
        font-family: var(--h-font-family);
    }
    .back-to-story:hover a, .back-to-story:hover i {
        text-decoration: none;
        color: var(--main-color)!important;
    }
    .back-to-story a, .back-to-story i {
        color: #000!important;
    }
    #full-width-viewer {
        margin-bottom: 50px;
    }
    
    .mark-active {
        display: flex;
        justify-content: space-between;
        width: 50vw;
        margin: 20px auto;
    }
    
    .mark-active label {
        cursor: pointer;
    }
    
    .mark-active input {
        margin-right: 10px;
    }

    </style>";

    //$content .= "</section>"; // End of main section

    $content .= "<script src='{$alpineJs}'></script>";

    return $content;
    }
}

add_shortcode( 'compare_transcriptions', '_TCT_compare_transcriptions' );

?>
