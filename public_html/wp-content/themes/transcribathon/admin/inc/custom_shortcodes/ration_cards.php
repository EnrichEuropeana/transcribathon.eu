<?php

/*
Shortcode: ration_cards
Description: Gets item data and builds the ration cards page for Zagreb material
*/

include($_SERVER["DOCUMENT_ROOT"].'/wp-load.php');

use FactsAndFiles\Transcribathon\TranskribusClient;

date_default_timezone_set('Europe/Berlin');

function _TCT_ration_cards($atts)
{
    global $config;

    if (empty($_GET['item'])) {
        return;
    }

    $itemId = intval($_GET['item']);

    $isLoggedIn = is_user_logged_in();

    $getJsonOptions = [
        'http' => [
            'header' => [ 'Content-type: application/json' ],
            'method' => 'GET'
        ]
    ];

    $itemData = sendQuery(TP_API_HOST . '/tp-api/items/' . $itemId, $getJsonOptions, true);

    if (empty($itemData['StoryId'])) {
        return;
    }

    $storyId = $itemData['StoryId'];;

    $pageData = sendQuery(TP_API_HOST . '/tp-api/itemPage/' . $storyId, $getJsonOptions, true);

    $statusTypes = $pageData['CompletionStatus'];
    $languages = $pageData['Languages'];
    $categories = $pageData['Categories'];
    $itemImages = $pageData['ItemImages'];
    
    // Automatic Enrichments
    $getAutoJsonOptions = [
        'http' => [
            'header' => [
                 'Content-type: application/json',
                 'Authorization: Bearer ' . TP_API_V2_TOKEN
                ],
            'method' => 'GET'
        ]
    ];

    $itemAutoE = sendQuery(TP_API_V2_ENDPOINT . '/items/' . $itemId . '/autoenrichments', $getAutoJsonOptions, true);
    $storyAutoE = sendQuery(TP_API_V2_ENDPOINT . '/stories/' . $storyId . '/autoenrichments', $getAutoJsonOptions, true);

    $itemAutoPlaces = [];
    $itemAutoPpl = [];
    
    if(!empty($itemAutoE['data'])) {
        foreach($itemAutoE['data'] as $itm) {
            if($itm['Type'] == 'Place') {
                array_push($itemAutoPlaces, $itm);
            } else {
                array_push($itemAutoPpl, $itm);
            }
        }
    }

    // Check which Transcription is active
    $trCheck = json_decode(checkActiveTranscription($itemData['ItemId']));
    $activeTr = $trCheck->data->TranscriptionSource;


    // Get English translation of story description
    //$engDescription = sendQuery('https://dsi-demo2.ait.ac.at/enrichment-web-test/enrichment/translation/' . $storyId . '/?property=description&wskey=apidemo', $getJsonOptions, false);


    // Build required components for the page
    $content = "";

    // TODO MOVE THIS TO THE APPROPRIATE PLACE

    $content .= '<script>
        window.onclick = function(event) {
            if (event.target.id != "transcription-status-indicator") {
                var dropdown = document.getElementById("transcription-status-dropdown");
                if (dropdown.classList.contains("show")) {
                    dropdown.classList.remove("show");
                }
            }
            if (event.target.id != "description-status-indicator") {
                var dropdown = document.getElementById("description-status-dropdown");
                if (dropdown.classList.contains("show")) {
                    dropdown.classList.remove("show");
                }
            }
            if (event.target.id != "location-status-indicator") {
                var dropdown = document.getElementById("location-status-dropdown");
                if (dropdown.classList.contains("show")) {
                    dropdown.classList.remove("show");
                }
            }
            if (event.target.id != "tagging-status-indicator") {
                var dropdown = document.getElementById("tagging-status-dropdown");
                if (dropdown.classList.contains("show")) {
                    dropdown.classList.remove("show");
                }
            }
        }
    </script>';
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
                "url": home_url + "/tp-api/items/" + '. $itemData['ItemId'] .',
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

    $currentTranscription = null;

    // Transkribus Client, include required files
    require_once(get_stylesheet_directory() . '/htr-client/lib/TranskribusClient.php');
    require_once(get_stylesheet_directory() . '/htr-client/config.php');

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
    $htrTranscription = json_decode($htrDataJson) -> data[0] -> TranscriptionData;
    $htrTranscription = get_text_from_pagexml($htrTranscription, '<br />');

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
    $imageData = json_decode($itemData['ImageLink'], true);
    $imageData['service'] = extractImageService($imageData);
    $imageDataJson = json_encode($imageData);

    // Image viewer
    $imageViewer = "";
    $imageViewer .= "<div id='openseadragon' style='height:560px;'>";
        // Pass Image to the viewer
        $imageViewer .= "<input type='hidden' id='image-data-holder' value='". $imageDataJson ."'>";
        // viewer buttons(regular viewe)
        $imageViewer .= "<div class='buttons' id='buttons'>";
            $imageViewer .= "<div id='zoom-in' class='theme-color theme-color-hover'><i class='fas fa-plus'></i></div>";
            $imageViewer .= "<div id='zoom-out' class='theme-color theme-color-hover'><i class='fas fa-minus'></i></div>";
            $imageViewer .= "<div id='home' title='View full image' class='theme-color theme-color-hover'><i class='fas fa-home'></i></div>";
            $imageViewer .= "<div id='full-width' title='Fit image width to frame' class='theme-color theme-color-hover'><i class='fas fa-arrows-alt-h'></i></div>";
            $imageViewer .= "<div id='rotate-right' class='theme-color theme-color-hover'><i class='fas fa-redo'></i></div>";
            $imageViewer .= "<div id='rotate-left' class='theme-color theme-color-hover'><i class='fas fa-undo'></i></div>";
            $imageViewer .= "<div id='filterButton' class='theme-color theme-color-hover'><i class='fas fa-sliders-h'></i></div>";
            $imageViewer .= "<div id='full-page' title='Full Screen' class='theme-color theme-color-hover'><i class='fas fa-expand-arrows-alt'></i></div>";
        $imageViewer .= "</div>";
    $imageViewer .= "</div>"; // End of Image Viewer


    // Mapbox
    $mapBox .= "";
    $mapBox .= "<div id='full-view-map' style='height:400px;'>";
        $mapBox .= "<script src='https://api.mapbox.com/mapbox-gl-js/plugins/mapbox-gl-geocoder/v4.4.1/mapbox-gl-geocoder.min.js'></script>";
        $mapBox .= "<link rel='stylesheet' href='https://api.mapbox.com/mapbox-gl-js/plugins/mapbox-gl-geocoder/v4.4.1/mapbox-gl-geocoder.css' type='text/css' />";
        $mapBox .= "<i class='fas fa-map map-placeholder'></i>";
    $mapBox .= "</div>";

    // Locations Display
    $locationDisplay = "";
    $locationDisplay .= "<div id='location-editor' class='location-display-container' style='margin-top:20px;'>";
        foreach($itemData['Places'] as $place) {
            $locationDisplay .= "<div id='location-" . $place['PlaceId'] . "'>";
                $locationDisplay .= "<div id='location-data-output-" . $place['PlaceId'] . "' class='location-single'>";
                    $locationDisplay .= "<img src='".home_url()."/wp-content/themes/transcribathon/images/location-icon.svg' height='20px' width='20px' alt='location-icon'>";
                    $locationDisplay .= "<p><b>" . $place['Name'] . "</b> (" . $place['Latitude'] . ", " . $place['Longitude'] . ")</p>";
                    if($place['Comment'] != 'NULL' && $place['Comment'] != "") {
                        $locationDisplay .= "<p style='margin-top:0px;font-size:13px;'>Description: <b>" . $place['Comment'] . "</b></p>";
                    }
                    if($place['WikidataId'] != 'NULL' && $place['WikidataId'] != "") {
                        $locationDisplay .= "<p style='margin-top:0px;font-size:13px;margin-left:30px;'>Wikidata Reference: <b><a href='http://wikidata.org/wiki/". $place['WikidataId'] . "' style='text-decoration: none;' target='_blank'>" . $place['WikidataName'] . ", " . $place['WikidataId'] . "</a></b></p>";
                    }

                    $locationDisplay .= "<div class='edit-delete-btns'>";
                        $locationDisplay .= "<i class='login-required edit-item-data-icon fas fa-pencil theme-color-hover' onClick='openLocationEdit(" . $place['PlaceId'] . ")'></i>";
                        $locationDisplay .= "<i class='login-required edit-item-data-icon fas fa-trash-alt theme-color-hover'
                                        onCLick='deleteItemData(\"places\", " . $place['PlaceId'] . ", " . $_GET['item'] . ", \"place\", " . get_current_user_id() . ")' ></i>";
                    $locationDisplay .= "</div>";


                $locationDisplay .= "</div>";

                $locationDisplay .= "<div id='location-data-edit-" . $place['PlaceId'] . "' class='location-data-edit-container' style='display:none;'>";

                    $locationDisplay .= "<div class='location-input-section-top'>";
                        $locationDisplay .= "<div class='location-input-name-container' style='min-height:25px;'>";
                            $locationDisplay .= "<label>Location Name: </label>";
                            $locationDisplay .= "<input type='text' class='edit-input' value='" . ($place['Name'] != 'NULL' ? htmlspecialchars($place['Name'], ENT_QUOTES, 'UTF-8') : '') . "' name='' placeholder=''>";
                        $locationDisplay .= "</div>";

                        $locationDisplay .= "<div class='location-input-coordinates-container' style='min-height:25px;'>";
                            $locationDisplay .= "<label>Coordinates: </label>";
                            $locationDisplay .= "<span class='required-field'>*</span>";
                            $locationDisplay .= "<input class='edit-input' type='text' value='" . ($place['Latitude'] != 'NULL' ? htmlspecialchars($place['Latitude'], ENT_QUOTES, 'UTF-8') : '') . ", "
                                . ($place['Longitude'] != 'NULL' ? htmlspecialchars($place['Longitude'], ENT_QUOTES, 'UTF-8') : '') . "' name='' placeholder=''>";
                        $locationDisplay .= "</div>";

                        $locationDisplay .= "<div style='clear:both;'></div>";
                    $locationDisplay .= "</div>";

                    $locationDisplay .= "<div class='location-input-geonames-container location-search-container' style='min-height:25px;margin: 5px 0;'>";
                        $locationDisplay .= "<label>Wikidata Reference:";
                            $locationDisplay .= "<i class='fas fa-question-circle' style='font-size:16px;cursor:pointer;margin-left:4px;' title='Identify this location by searching its name or code on WikiData'></i>";
                        $locationDisplay .= "</label>";
                        if($place['WikidataId'] != 'NULL' && $place['WikidataId'] != '' && $place['WikidataName'] != 'NULL' && $place['WikidataName'] != '') {
                            $locationDisplay .= "<input class='edit-input' type='text' placeholder='' name='' value='"
                                . htmlspecialchars($place['WikidataName'], ENT_QUOTES, 'UTF-8') . "; "
                                . htmlspecialchars($place['WikidataId'], ENT_QUOTES, 'UTF-8') . "'>";
                        } else {
                            $locationDisplay .= "<input class='edit-input' type='text' placeholder='' name=''>";
                        }
                    $locationDisplay .= "</div>";
                    $locationDisplay .= "<div class='location-input-description-container' style='height:50px;'>";
                        $locationDisplay .= "<label>";
                            $locationDisplay .= "Description: ";
                            $locationDisplay .= "<i class='fas fa-question-circle' style='font-size: 16px;cursor: pointer; margin-left: 4px;'
                                            tite='Add more information about this location, e.g. building name, or it's significance to the item...'></i>";
                        $locationDisplay .= "</label>";
                        $locationDisplay .= "<textarea rows='2' class='edit-input gsearch-form' style='resize:none;' type='text' id='ldsc'>";
                        if($place['Comment']) {
                            $locationDisplay .= htmlspecialchars($place['Comment'], ENT_QUOTES, 'UTF-8');
                        }
                        $locationDisplay .= "</textarea>";
                    $locationDisplay .= "</div>";

                    $locationDisplay .= "<div class='form-buttons-right'>";
                        $locationDisplay .= "<div class='form-btn-left'>";
                            $locationDisplay .= "<button class='theme-color-background edit-location-cancel' onClick='openLocationEdit(" . $place['PlaceId'] . ")'>";
                                $locationDisplay .= "CANCEL";
                            $locationDisplay .= "</button>";
                        $locationDisplay .= "</div>";

                        $locationDisplay .= "<div class='form-btn-right'>";
                            $locationDisplay .= "<button class='item-page-save-button theme-color-background edit-location-save'
                                            onClick='editItemLocation(" . $place['PlaceId'] . ", " . $_GET['item'] . ", " . get_current_user_id() . ")'>";
                                $locationDisplay .= "SAVE";
                            $locationDisplay .= "</button>";
                        $locationDisplay .= "</div>";

                        $locationDisplay .= "<div id='item-location-" . $place['PlaceId'] . "-spinner-container' class='spinner-container spinner-container-right'>";
                            $locationDisplay .= "<div class='spinner'></div>";
                        $locationDisplay .= "</div>";
                        $locationDisplay .= "<div style='clear:both;'></div>";
                    $locationDisplay .= "</div>";

                    $locationDisplay .= "<div style='clear:both;'></div>";
                $locationDisplay .= "</div>";
            $locationDisplay .= "</div>"; // End of single location
        }
        if($itemData['StoryPlaceName'] != null && $itemData['StoryPlaceName'] != "" && $itemData['StoryPlaceName'] != "NULL") {
            $locationDisplay .= "<div class='location-single story-location'>";
                $locationDisplay .= "<img src='".home_url()."/wp-content/themes/transcribathon/images/location-icon.svg' alt='location-icon' height='20px' width='20px' style='float:left;height:20px;margin-right:10px;position:relative;top:1px;filter:saturate(0.4)'>";
                $locationDisplay .= "<p><b>" . $itemData['StoryPlaceName'] . "</b> (" . $itemData['StoryPlaceLatitude'] . ", " . $itemData['StoryPlaceLongitude'] . ")</p>";
                $locationDisplay .= "<p style='font-size:13px;'>Story Location</p>";
            $locationDisplay .= "</div>";
        }
        if(!empty($itemAutoPlaces)) {
            $locationDisplay .= "<p class='auto-h'> Automatically Identified Places </p>";
            foreach($itemAutoPlaces as $place) {
                $wikiIdArr = explode('/', $place['WikiData']);
                $wikiId = array_pop($wikiIdArr);
                $locationDisplay .= "<div class='location-single'>";
                        $locationDisplay .= "<img src='".home_url()."/wp-content/themes/transcribathon/images/location-icon.svg' alt='location-icon' height='20px' width='20px' style='float:left;height:20px;margin-right:10px;position:relative;top:1px;filter:saturate(0.4)'>";
                        $locationDisplay .= "<p><b>";
                            $locationDisplay .= $place['Name'];
                        $locationDisplay .= "</p></b>";
                        $locationDisplay .= "<p style='margin-top:0;font-size:13px;'>Wikidata Reference: <a href='" . $place['WikiData'] . "' target='_blank'>" . $place['Name'] . ',' . $wikiId . "</a></p>";
                        $locationDisplay .= "<i class='fas fa-trash-alt auto-delete' onClick='deleteAutoEnrichment(".$place['AutoEnrichmentId'].", event)'></i>";
                $locationDisplay .= "</div>";
            }
        }

    $locationDisplay .= "</div>";

    // Map Editor
    $mapEditor = "";
    //$mapEditor .= "<script src='https://api.mapbox.com/mapbox-gl-js/plugins/mapbox-gl-geocoder/v4.4.1/mapbox-gl-geocoder.min.js'></script>";
    //$mapEditor .= "<link rel='stylesheet' href='https://api.mapbox.com/mapbox-gl-js/plugins/mapbox-gl-geocoder/v4.4.1/mapbox-gl-geocoder.css' type='text/css' />";

    $mapEditor .= "<div id='location-section' class='item-page-section'>";
        $mapEditor .= "<div id='location-hide' class='item-page-section-headline-container login-required'>";

            $mapEditor .= "<div id='location-position' class='theme-color item-page-section-headline collapse-headline' title='Click to add a location'>";
                $mapEditor .= "<span class='headline-header'>Locations</span>";
                $mapEditor .= "<i class='fas fa-plus-circle'></i>";

            // Status changer
            //$mapEditor .= "<div class='item-page-section-headline-right-site'>";
                $mapEditor .= "<div id='location-status-changer' class='status-changer section-status-changer login-required' style='background-color:" . $itemData['LocationStatusColorCode'] . ";'>";
                    if(current_user_can('administrator')) {
                        $mapEditor .= "<span id='location-status-indicator' class='status-indicator'
                                        onClick='event.stopPropagation(); document.getElementById(\"location-status-dropdown\").classList.toggle(\"show\")'> " . $itemData['LocationStatusName'] . " </span>";
                    } else {
                        $mapEditor .= "<span id='location-status-indicator' class='status-indicator'> " . $itemData['LocationStatusName'] . " </span>";
                    }

                    $mapEditor .= "<div id='location-status-dropdown' class='sub-status status-dropdown-content'>";
                        foreach($statusTypes as $statusType) {
                            if($statusType['CompletionStatusId'] != 4 || current_user_can('administrator')) {
                                if($itemData['LocationStatusId'] == $statusType['CompletionStatusId']) {
                                    $mapEditor .= "<div class='status-dropdown-option status-dropdown-option-current'
                                                    onClick=\"changeStatus(" . $_GET['item'] . ", null, '" . $statusType['Name'] . "', 'locationStatusId', " . $statusType['CompletionStatusId'] . ", '" . $statusType['ColorCode'] . "', " . sizeof($progressData) . ", this)\">";
                                        $mapEditor .= "<i class='fal fa-circle' style='color: transparent; background-image: -webkit-gradient(linear, left top, left bottom, color-stop(0, " . $statusType['ColorCode'] . "), color-stop(1," . $statusType['ColorCodeGradient'] . "));'></i>";
                                        $mapEditor .= $statusType['Name'];
                                    $mapEditor .= "</div>";
                                } else {
                                    $mapEditor .= "<div class='status-dropdown-option' onclick=\"changeStatus(".$_GET['item'].", null, '".$statusType['Name']."', 'locationStatusId', ".$statusType['CompletionStatusId'].", '".$statusType['ColorCode']."', ".sizeof($progressData).", this)\">";
                                        $mapEditor .= "<i class='fal fa-circle' style='color: transparent; background-image: -webkit-gradient(linear, left top, left bottom, color-stop(0, " . $statusType['ColorCode'] . "), color-stop(1," . $statusType['ColorCodeGradient'] . "));'></i>";
                                        $mapEditor .= $statusType['Name'];
                                    $mapEditor .= "</div>";
                                }
                            }
                        }
                    $mapEditor .= "</div>";
                $mapEditor .= "</div>";
            $mapEditor .= "</div>";
        $mapEditor .= "</div>";

        if(sizeof($itemData['Places']) < 1) {
            $mapEditor .= "<div id='location-input-section' class='login-required' style='display:block;'>";
        } else {
            $mapEditor .= "<div id='location-input-section' class='login-required' style='display:none;'>";
        }
            $mapEditor .= "<div class='location-input-section-second'>";
                $mapEditor .= "<div class='location-input-name-container location-input-container'>";
                    $mapEditor .= "<span class='required-field'>*</span>";
                $mapEditor .= "</div>";
            $mapEditor .= "</div>";

            $mapEditor .= "<div class='location-input-section-top'>";
                $mapEditor .= "<div id='location-name-display' style='margin-right: 16px;min-height:25px;' class='location-display location-name-container location-input-container'>";
                    $mapEditor .= "<label>Location Name:</label>";
                    $mapEditor .= "<span class='required-field'>*</span>";
                    $mapEditor .= "<input type='text' name='' placeholder='&nbsp&nbsp&nbsp e.g. Berlin' style='display:inline-block;'>";
                    $mapEditor .= '<div id="loc-name-check" class="loc-input-fa"><i class="fas fa-check" style="float:right;color:#61e02f;display:none;"></i></div>';
                $mapEditor .= "</div>";

                $mapEditor .= "<div class='location-display location-input-coordinates-container location-input-container' style='min-height:25px;'>";
                    $mapEditor .= "<label>Coordinates:</label>";
                    $mapEditor .= "<span class='required-field'>*</span>";
                    $mapEditor .= "<input id='loc-coord' type='text' name='' placeholder='&nbsp&nbsp&nbsp e.g. 10.0123, 15.2345'>";
                    $mapEditor .= '<div id="loc-save-lock" class="loc-input-fa"><i class="fas fa-lock-open" style="float:right;"></i></div>';
                $mapEditor .= "</div>";
                $mapEditor .= "<div style='clear:both;'></div>";
            $mapEditor .= "</div>";

            $mapEditor .= "<div id='location-input-geonames-search-container' class='location-input-container location-search-container' style='margin-top:9px;min-height:25px;'>";
                $mapEditor .= "<label>WikiData Reference:<i class='fas fa-question-circle' style='font-size:16px;cursor:pointer;margin-left:4px;' title='Identify this location by searching its name or code on WikiData'></i></label>";
                $mapEditor .= "<input type='text' id='lgns' class='wiki-input' palceholder='&nbsp&nbsp&nbsp e.g.: Q64' name=''>";
            $mapEditor .= "</div>";

            $mapEditor .= "<div class='location-input-description-container location-input-container'>";
                $mapEditor .= "<label>Description:<i class='fas fa-question-circle' style='font-size:16px;cursor:pointer;margin-left:4px;' title='Add more information to this location, e.g. the building name, or its significance to the item'></i></label>";
                $mapEditor .= "<textarea rows='2' style='resize:none;' class='gsearch-form' type='text' id='ldsc' placeholder='' name=''></textarea>";
            $mapEditor .= "</div>";

            $mapEditor .= "<div style='clear:both;'></div>";
            $mapEditor .= "<div>";
                $mapEditor .= '<div id="clear-loc-input" class="loc-input-fa">Clear All <i class="fas fa-times"></i></div>';
                $mapEditor .= "<button class='item-page-save-button theme-color-background location-save-btn'
                                onClick='saveItemLocation(" . $itemData['ItemId'] . ", " . get_current_user_id() . ", \"" .$statusTypes[1]['ColorCode'] . "\", " . sizeof($progressData) . ")'>";
                    $mapEditor .= "SAVE";
                $mapEditor .= "</button>";
                $mapEditor .= "<div id='item-location-spinner-container' class='spinner-container spinner-container-right'>";
                    $mapEditor .= "<div class='spinner'></div>";
                $mapEditor .= "</div>";
                $mapEditor .= "<div style='clear:both;'></div>";
            $mapEditor .= "</div>";
            $mapEditor .= "<div style='clear:both;'></div>";
        $mapEditor .= "</div>";

        $mapEditor .= "<div style='clear:both;'></div>";
        // Editor Location Display and Location Edit/Delete
        $mapEditor .= $locationDisplay;


    $mapEditor .= "</div>";

    // Enrichments Editor
    $enrichmentTab = "";
    $enrichmentTab .= "<div id='tagging-section' class='item-page-section'>";

        $enrichmentTab .= "<div class='item-page-section-headline-container'>";

            $enrichmentTab .= "<div class='theme-color item-page-section-headline'>";
                $enrichmentTab .= "<span class='headline-header'>PEOPLE</span>";
                $enrichmentTab .= "<i id='show-ppl-input' class='fas fa-plus-circle'></i>";
            //$enrichmentTab .= "<div class='item-page-headline-right-site'>";
                $enrichmentTab .= "<div id='tagging-status-changer' class='status-changer section-status-changer login-required' style='background-color:" . $itemData['TaggingStatusColorCode'] . ";'>";
                    if(current_user_can('administrator')) {
                        $enrichmentTab .= "<span id='tagging-status-indicator' class='status-indicator'
                                            onClick='event.stopPropagation(); document.getElementById(\"tagging-status-dropdown\").classList.toggle(\"show\")'> " . $itemData['TaggingStatusName'] . " </span>";
                    } else {
                        $enrichmentTab .= "<span id='tagging-status-indicator' class='status-indicator'> " . $itemData['TaggingStatusName'] . " </span>";
                    }

                    $enrichmentTab .= "<div id='tagging-status-dropdown' class='sub-status status-dropdown-content'>";
                        foreach($statusTypes as $statusType) {
                            if($statusType['CompletionStatusId'] != 4 || current_user_can('administrator')) {
                                if($itemData['TaggingStatusId'] == $statusType['CompletionStatusId']) {
                                    $enrichmentTab .= "<div class='status-dropdown-option status-dropdown-option-current'
                                                        onClick='changeStatus(" . $_GET['item'] . ", null, \"" . $statusType['Name'] . "\", \"taggingStatusId\", " . $statusType['CompletionStatusId'] . ",
                                                        \"" .$statusType['ColorCode'] . "\", " . sizeof($progressData) . ", this)'>";
                                        $enrichmentTab .= "<i class='fal fa-circle' style='color:transparent; background-image: -webkit-gradient(linear, left top, left bottom, color-stop(0," . $statusType['ColorCode'] . "), color-stop(1, " . $statusType['ColorCodeGradient'] . "));'></i>";
                                        $enrichmentTab .= $statusType['Name'];
                                    $enrichmentTab .= "</div>";
                                } else {
                                    $enrichmentTab .= "<div class='status-dropdown-option'
                                                        onClick='changeStatus(" . $_GET['item'] . ", null, \"" . $statusType['Name'] . "\", \"taggingStatusId\", " . $statusType['CompletionStatusId'] . ",
                                                        \"" . $statusType['ColorCode'] . "\", " . sizeof($progressData) . ", this)'>";
                                                        $enrichmentTab .= "<i class='fal fa-circle' style='color:transparent; background-image: -webkit-gradient(linear, left top, left bottom, color-stop(0," . $statusType['ColorCode'] . "), color-stop(1, " . $statusType['ColorCodeGradient'] . "));'></i>";
                                                        $enrichmentTab .= $statusType['Name'];
                                    $enrichmentTab .= "</div>";
                                }
                            }
                        }
                    $enrichmentTab .= "</div>";
                $enrichmentTab .= "</div>";
            $enrichmentTab .= "</div>";
        $enrichmentTab .= "</div>";

        // PERSON ENTRY
        $enrichmentTab .= "<div class='item-page-person-container'>";
            $enrichmentTab .= "<div id='item-page-person-headline' class='theme-color'>";
                $enrichmentTab .= "<h6 class='theme-color item-data-input-headline login-required' title='Click to tag a person'>";
                    $enrichmentTab .= "People ";
                $enrichmentTab .= "<i id='people-open' class=\"fas fa-edit\"></i></h6>";
            $enrichmentTab .= "</div>";
            // add person form
            if(count($itemData['Persons']) > 0) {
                $enrichmentTab .= '<div class="collapse person-item-data-container" id="person-input-container" style="position:relative;">';
            } else {
                $enrichmentTab .= '<div class="collapse person-item-data-container show login-required" id="person-input-container" style="position:relative;">';
            }
                $enrichmentTab .= '<div class="person-input-names-container">';
                    $enrichmentTab .= '<input type="text" id="person-firstName-input" class="input-response person-input-field" name="" placeholder="&nbsp First Name" style="width:48.5%;">';
                    $enrichmentTab .= '<input type="text" id="person-lastName-input" class="input-response person-input-field" name="" placeholder="&nbsp Last Name" style="width:48.5%;float:right;">';
                $enrichmentTab .= '</div>';

                // Enrich Person Changes
                $enrichmentTab .= "<div class='person-input-desc-cont'>";
                    $enrichmentTab .= "<div class='person-desc-left' style='margin-bottom: 0!important;'>";
                        $enrichmentTab .= "<div class='person-description-input'>";
                            $enrichmentTab .= "<input id='person-description-input-field' type='text' placeholder='&nbsp Add more info to this person...' title='e.g. their profession, or their significance to the document' class='input-response person-input-field'>";
                        $enrichmentTab .= "</div>";
                        $enrichmentTab .= "<div class='person-description-input'>";
                            $enrichmentTab .= "<input id='person-wiki-input-field' type='text' placeholder='&nbsp Add Wikidata Id to this person...' title='e.g. Wikidata Title Id' class='input-response person-input-field'>";
                        $enrichmentTab .= "</div>";
                    $enrichmentTab .= "</div>";
                    $enrichmentTab .= "<div class='person-desc-right'>";
                        $enrichmentTab .= "<div class='person-role-input' style='margin-bottom: 0!important;'>";
                            $enrichmentTab .= "<label id='document-creator'>";
                                $enrichmentTab .= "<input type='radio' id='doc-creator' name='person-role' value='document-creator' required>";
                                $enrichmentTab .= "<span> Document Creator</span>";
                            $enrichmentTab .= "</label>";
                            $enrichmentTab .= "</br>";
                            $enrichmentTab .= "<label id='important-person'>";
                                $enrichmentTab .= "<input type='radio' id='main-actor' name='person-role' value='main-actor'>";
                                $enrichmentTab .= "<span> Person Addressed </span>";
                            $enrichmentTab .= "</label>";
                            $enrichmentTab .= "</br>";
                            $enrichmentTab .= "<label id='others'>";
                                $enrichmentTab .= "<input type='radio' id='other-ppl' name='person-role' value='other-people'>";
                                $enrichmentTab .= "<span> Person Mentioned </span>";
                            $enrichmentTab .= "</label>";
                            $enrichmentTab .= "</br>";
                        $enrichmentTab .= "</div>";
                        $enrichmentTab .= "<i class='fas fa-question-circle'></i>";
                    $enrichmentTab .= "</div>";
                    $enrichmentTab .= "<div style='clear:both;'></div>";
                $enrichmentTab .= "</div>";


                // $enrichmentTab .= "<div class='person-description-input'>";
                //     $enrichmentTab .= "<input id='person-description-input-field' type='text' placeholder='&nbsp Add more info to this person...' title='e.g. their profession, or their significance to the dcument' class='input-response person-input-field'>";
                // $enrichmentTab .= "</div>";

                // $enrichmentTab .= '<div class="person-description-input">';
                //     $enrichmentTab .= '<input id="person-wiki-input-field" type="text" placeholder="&nbsp Add Wikidata Id to this person..." title="e.g. Wikidata Title Id" class="input-response person-input-field">';
                // $enrichmentTab .= '</div>';

                $enrichmentTab .= '<div class="person-location-birth-inputs">';
                    $enrichmentTab .= '<input type="text" id="person-birthPlace-input" class="input-response person-input-field" name="" placeholder="&nbsp Birth Location">';
                    $enrichmentTab .= '<span style="display:inline-block;width:48.5%;" class="input-response"><input type="text" id="person-birthDate-input" class="date-input-response person-input-field datepicker-input-field" name="" placeholder="&nbsp Birth: dd/mm/yyyy" style="width:100%;margin-left:15px;"></span>';
                $enrichmentTab .= '</div>';

                $enrichmentTab .= '<div class="person-location-death-inputs">';
                    $enrichmentTab .= '<input type="text" id="person-deathPlace-input" class="input-response person-input-field" name="" placeholder="&nbsp Death Location">';
                    $enrichmentTab .= '<span style="display:inline-block;width:48.5%;" class="input-response"><input type="text" id="person-deathDate-input" class="date-input-response person-input-field datepicker-input-field" name="" placeholder="&nbsp Death: dd/mm/yyyy" style="width:100%;margin-left:15px;"></span>';
                $enrichmentTab .= '</div>';

                $enrichmentTab .= "<div class='person form-buttons-right' style='display:block;margin-top:0px;'>";
                    $enrichmentTab .= "<button id='save-personinfo-button' class='edit-data-save-right' id='person-save-button'
                                    onClick='savePerson(" . $itemData['ItemId'] . ", " . get_current_user_id() . ", \"" . $statusTypes[1]['ColorCode'] . "\", " . sizeof($progressData) . ")'>";
                        $enrichmentTab .= "<i style='margin-left:5px;font-size:20px;' class='fas fa-save'></i>";
                    $enrichmentTab .= "</button>";
                    $enrichmentTab .= "<div id='item-person-spinner-container' class='spinner-container spinner-container-left'>";
                        $enrichmentTab .= "<div class='spinner'></div>";
                    $enrichmentTab .= "</div>";
                    $enrichmentTab .= "<div style='clear:both;'></div>";
                $enrichmentTab .= "</div>";
                $enrichmentTab .= "<div style='clear:both;'></div>";
            $enrichmentTab .= "</div>";

            $enrichmentTab .= '<div id="item-person-list" class="item-data-output-list">';
                foreach($itemData['Persons'] as $person) {
                    $person['BirthDate'] = $person['BirthDate'] !== 'NULL'
                        ? date('d/m/Y', strtotime($person['BirthDate']))
                        : 'NULL';
                    $person['DeathDate'] = $person['DeathDate'] !== 'NULL'
                        ? date('d/m/Y', strtotime($person['DeathDate']))
                        : 'NULL';
                    $enrichmentTab .= "<div id='person-" . $person['PersonId'] . "'>";
                        $enrichmentTab .= "<div class='single-person'>";
                            $enrichmentTab .= "<i class='fas fa-user person-i' style='float:left;margin-right: 5px;'></i>";
                            $enrichmentTab .= "<p class='person-data'>";
                                $enrichmentTab .= "<span style='font-weight:500;'>" . htmlspecialchars_decode(($person['FirstName'] != 'NULL' ? $person['FirstName'] : '')) . " " . htmlspecialchars_decode($person['LastName'] != 'NULL' ? $person['LastName'] : ''). "</span>";
                                if($person['BirthDate'] != 'NULL' && $person['DeathDate'] != 'NULL') {
                                    $enrichmentTab .= " (" . $person['BirthDate'];
                                    if($person['BirthPlace'] != 'NULL') {
                                        $enrichmentTab .= ", " . $person['BirthPlace'];
                                    }
                                    $enrichmentTab .= " - " . $person['DeathDate'];
                                    if($person['DeathPlace'] != 'NULL') {
                                        $enrichmentTab .= ", " . $person['DeathPlace'];
                                    }
                                    $enrichmentTab .= ")";
                                } else if($person['BirthDate'] != 'NULL') {
                                    $enrichmentTab .= " (Birth: " . $person['BirthDate'];
                                    if($person['BirthPlace'] != 'NULL') {
                                        $enrichmentTab .= ", " . $person['BirthPlace'];
                                    }
                                    $enrichmentTab .= ")";
                                } else if($person['DeathDate'] != 'NULL') {
                                    $enrichmentTab .= " (Death: " . $person['DeathDate'];
                                    if($person['DeathPlace'] != 'NULL') {
                                        $enrichmentTab .= ", " . $person['DeathPlace'];
                                    }
                                    $enrichmentTab .= ")";
                                }

                            $enrichmentTab .= "</p>";

                            if($person['Description'] != 'NULL' && $person['Description'] != null) {
                                $enrichmentTab .= "<p class='person-description'>" . $person['Description'] . "</p>";
                            }
                            if($person['Link'] != 'NULL' && $person['Link'] != null) {
                                $enrichmentTab .= "<p class='person-description'>Wikidata ID: <b><a href='http://www.wikidata.org/wiki/" . $person['Link'] . "' target='_blank'>" . $person['Link'] . "</a></b></p>";
                            }
                            // Edit/Delete buttons
                            $enrichmentTab .= "<div class='edit-del-person'>";
                                $enrichmentTab .= "<i class='login-required edit-item-data-icon fas fa-pencil theme-color-hover'
                                                onClick='openPersonEdit(" . $person['PersonId'] . ")'></i>";
                                $enrichmentTab .= "<i class='login-required edit-item-data-icon fas fa-trash-alt theme-color-hover'
                                                onClick='deleteItemData(\"persons\", " . $person['PersonId'] . ", " . $_GET['item'] . ", \"person\", " . get_current_user_id() . ")'></i>";
                            $enrichmentTab .= "</div>";
                            $enrichmentTab .= "<div style='clear:both;'></div>";
                        $enrichmentTab .= "</div>";

                        $enrichmentTab .= "<div id='person-data-edit-". $person['PersonId'] . "' class='person-data-edit-container person-item-data-container'>";
                            $enrichmentTab .= "<div class='person-input-names-container'>";
                                $enrichmentTab .= "<input type='text' id='person-" . $person['PersonId'] . "-firstName-edit' class='input-response person-input-field person-re-edit'
                                                placeholder='&nbsp First Name' value='" . htmlspecialchars($person['FirstName']) . "'>";
                                $enrichmentTab .= "<input type='text' id='person-" . $person['PersonId'] . "-lastName-edit' class='input-response person-input-field person-re-edit-right'
                                                placeholder='&nbsp Last Name' value='" . ($person['LastName'] != 'NULL' ? $person['LastName'] : '') . "'>";
                            $enrichmentTab .= "</div>";

                            $enrichmentTab .= "<div class='person-description-input'>";
                                $enrichmentTab .= "<input type='text' id='person-" . $person['PersonId'] . "-description-edit' class='input-response person-edit-field'
                                                placeholder='&nbsp Add more info to this person...' value='" . ($person['Description'] != 'NULL' ? htmlspecialchars($person['Description']) : '') . "'>";
                            $enrichmentTab .= "</div>";

                            $enrichmentTab .= "<div class='person-description-input'>";
                                $enrichmentTab .= "<input type='text' id='person-" . $person['PersonId'] . "-wiki-edit' placeholder='&nbsp Add Wikidata ID to this person'
                                                title='e.g. Wikidata Title ID' value='" . ($person['Link'] != 'NULL' ? htmlspecialchars($person['Link']) : '') . "'>";
                            $enrichmentTab .= "</div>";

                            $enrichmentTab .= "<div class='person-location-birth-inputs' style='margin-top:5px;position:relative;'>";
                                $enrichmentTab .= "<input type='text' id='person-" . $person['PersonId'] . "-birthPlace-edit' class='input-response person-input-field person-re-edit'
                                                value='" . ($person['BirthPlace'] != 'NULL' ? htmlspecialchars($person['BirthPlace']) : '') . "' placeholder='&nbsp Birth Location'>";
                                $enrichmentTab .= "<span class='input-response'><input type='text' id='person-" . $person['PersonId'] . "-birthDate-edit'
                                                class='date-input-response person-input-field datepicker-input-field person-re-edit-right' value='" . ($person['BirthDate'] != 'NULL' ? htmlspecialchars($person['BirthDate']) : '') .
                                                "' placeholder='&nbsp Birth: dd/mm/yyyy'></span>";
                            $enrichmentTab .= "</div>";

                            $enrichmentTab .= "<div class='person-location-death-inputs' style='margin-top:5px;position:relative;'>";
                                $enrichmentTab .= "<input type='text' id='person-" . $person['PersonId'] . "-deathPlace-edit' class='input-response person-input-field person-re-edit'
                                                value='" . ($person['DeathPlace'] != 'NULL' ? htmlspecialchars($person['DeathPlace']) : '') . "' placeholder='&nbsp Death Location'>";
                                $enrichmentTab .= "<span class='input-response'><input type='text' id='person-" . $person['PersonId'] . "-deathDate-edit'
                                                class='date-input-response person-input-field datepicker-input-field person-re-edit-right' value='" . ($person['DeathDate'] != 'NULL' ? htmlspecialchars($person['DeathDate']) : '') .
                                                "' placeholder='&nbsp Death: dd/mm/yyyy'></span>";
                            $enrichmentTab .= "</div>";

                            $enrichmentTab .= "<div class='form-buttons-right'>";
                                $enrichmentTab .= "<div class='person-btn-left'>";
                                    $enrichmentTab .= "<button class='theme-color-background' onClick='openPersonEdit(" . $person['PersonId'] . ")'>";
                                        $enrichmentTab .= "CANCEL";
                                    $enrichmentTab .= "</button>";
                                $enrichmentTab .= "</div>";
                                $enrichmentTab .= "<div class='person-btn-right'>";
                                    $enrichmentTab .= "<button class='theme-color-background'
                                                    onClick='editPerson(" . $person['PersonId'] . ", " . $_GET['item'] . ", " . get_current_user_id() . ")'>";
                                        $enrichmentTab .= "SAVE";
                                    $enrichmentTab .= "</button>";
                                $enrichmentTab .= "</div>";
                                $enrichmentTab .= "<div id='item-person-" . $person['PersonId']  ."-spinner-container' class='spinner-container spinner-container-left'>";
                                    $enrichmentTab .= "<div class='spinner'></div>";
                                $enrichmentTab .= "</div>";
                                $enrichmentTab .= "<div style='clear:both;'></diV>";
                            $enrichmentTab .= "</div>";
                            $enrichmentTab .= "<div style='clear:both;'></div>";
                        $enrichmentTab .= "</div>";
                    $enrichmentTab .= "</div>";

                }
                // AutoEnrichment People
                if(sizeof($itemAutoPpl) > 0) {
                    $enrichmentTab .= "<p class='auto-h'> Automatically Identified People </p>";
                    //dd($itemAutoPpl);
                    foreach($itemAutoPpl as $person) {
                        $wikiIdArr = explode('/', $person['WikiData']);
                        $wikiId = array_pop($wikiIdArr);
                        $enrichmentTab .= "<div class='single-person'>";
                                $enrichmentTab .= "<i class='fas fa-user person-i' style='float:left;margin-right:5px;'></i>";
                                $enrichmentTab .= "<p class='person-data' style='font-weight:600;'>";
                                    $enrichmentTab .= $person['Name'];
                                 //   $enrichmentTab .= "<i class='fas fa-trash-alt auto-delete' onClick='deleteAutoEnrichment(".$person['AutoEnrichmentId'].")'></i>";
                                $enrichmentTab .= "</p>";
                                $enrichmentTab .= "<p class='person-description'> Wikidata Reference: <a href='" . $person['WikiData'] . "' target='_blank'>" . $wikiId . "</a></p>";
                                $enrichmentTab .= "<i class='fas fa-trash-alt auto-delete' onClick='deleteAutoEnrichment(".$person['AutoEnrichmentId'].", event);'></i>";
                        $enrichmentTab .= "</div>";
                    }
                }

            $enrichmentTab .= "</div>"; // End of people display container

        $enrichmentTab .= "</div>";

    $enrichmentTab .= "</div>";

    // Transcription History
    $trHistory = "";
    // if($currentTranscription['Text'] == null ) {
    //     $trHistory .= "<div class='tr-history-section' style='display:none;'>";
    // } else {
         $trHistory .= "<div class='tr-history-section' style='display:block;'>";
    // }
        $trHistory .= "<div class='item-page-section-headline-container collapse-headline item-page-section-collapse-headline collapse-controller' data-toggle='collapse' href='#transcription-history'
                        onClick='jQuery(this).find(\"collapse-icon\").toggleClass(\"fa-caret-circle-down\")
                        jQuery(this).find(\"collapse-icon\").toggleClass(\"fa-caret-circle-up\")' style='margin-bottom: 0;'>";
            $trHistory .= "<h4 id='transcription-history-collapse-heading' class='theme-color item-page-section-headline'>";
                $trHistory .= "TRANSCRIPTION HISTORY";
            $trHistory .= "</h4>";
            $trHistory .= "<i class='far fa-caret-circle-down collapse-icon theme-color' style='font-size:17px; float:left; margin-right:8px; margin-top:9px;'></i>";
        $trHistory .= "</div>";
        $trHistory .= "<div style='clear:both;'></div>";

        $trHistory .= "<div id='transcription-history' class='collapse'>";
        // Get User data
        $user = get_userdata($currentTranscription['WP_UserId']);

            $trHistory .= "<div class='transcription-toggle' data-toggle='collapse' data-target='#transcription-0'>";
                $trHistory .= "<i class='far fa-calendar-day' style='margin-right:6px;'></i>";
                // Get Transcription Timestamp
                $trDate = strtotime($currentTranscription['Timestamp']);
                $trHistory .= "<span class='day-n-time'>";
                    $trHistory .= $currentTranscription['Timestamp'];
                $trHistory .= "</span>";
                $trHistory .= "<i class='fas fa-user-alt' style='margin: 0 6px;'></i>";
                $trHistory .= "<span class='day-n-time'>";
                    $trHistory .= "<a target='_blank' href='" . network_home_url() . "profile/" . $user->data->user_nicename . "'>";
                        $trHistory .= $user->data->user_nicename;
                    $trHistory .= "</a>";
                $trHistory .= "</span>";
                $trHistory .= "<i class='fas fa-angle-down' style='float:right;'></i>";
            $trHistory .= "</div>";

            $trHistory .= "<div id='transcription-0' class='collapse transcription-history-collapse-content'>";
                $trHistory .= "<p>";
                    $trHistory .= $currentTranscription['TextNoTags'];
                $trHistory .= "</p>";
            $trHistory .= "</div>";

            $i = 1;

            if($transcriptionList != null) {
                foreach($transcriptionList as $transcription) {
                    $user = get_userdata($transcription['WP_UserId']);
                    $trHistory .= "<div class='transcription-toggle' data-toggle='collapse' data-target='#transcription-" . $i . "'>";
                        $trHistory .= "<i class='fas fa-calendar-day' style='margin-right: 6px;'></i>";
                        $date = strtotime($transcription['Timestamp']);
                        $trHistory .= "<span class='day-n-time'>";
                            $trHistory .= $transcription['Timestamp'];
                        $trHistory .= "</span>";
                        $trHistory .= "<i class='fas fa-user-alt' style='margin: 0 6px;'></i>";
                        $trHistory .= "<span class='day-n-time'>";
                            $trHistory .= "<a target='_blank' href='" . network_home_url() . "profile/" . $user->data->user_nicename . "'>";
                                $trHistory .= $user->data->user_nicename;
                            $trHistory .= "</a>";
                        $trHistory .= "</span>";
                        $trHistory .= "<i class='fas fa-angle-down' style='float:right;'></i>";
                    $trHistory .= "</div>";

                    $trHistory .= "<div id='transcription-" . $i . "' class='collapse transcription-history-collapse-content'>";
                        $trHistory .= "<p>";
                            $trHistory .= $transcription['TextNoTags'];
                        $trHistory .= "</p>";
                        $trHistory .= "<input class='transcription-comparison-button theme-color-background' type='button'
                                        onClick='compareTranscription(" . htmlentities(json_encode($transcriptionList[$i]['TextNoTags']), ENT_QUOTES) . ",
                                        " . htmlentities(json_encode($currentTranscription['TextNoTags']), ENT_QUOTES)."," . $i . ")' value='Compare to current transcription'>";
                        $trHistory .= "<div id='transcription-comparison-output-" . $i . "' class='transcription-comparison-output'></div>";
                    $trHistory .= "</div>";
                    $i ++;
                }
            }
        $trHistory .= "</div>";
    $trHistory .= "</div>";
    // Editor Tab
    $editorTab = "";
    $editorTab .= "<div style='display:none;'><span id='completion-status-indicator' class='status-indicator'></span></div>";
    $editorTab .= "<div id='transcription-section' class='item-page-section'>";
        $editorTab .= "<div class='item-page-section-headline-container transcription-headline-header'>";
            $editorTab .= "<div class='theme-color item-page-section-headline'>";
                $editorTab .= "<span class='headline-header'>TRANSCRIPTION</span>";

                $editorTab .= "<div id='transcription-status-changer' class='status-changer section-status-changer login-required' style='background-color:" . $itemData['TranscriptionStatusColorCode'] . ";'>";
                    $editorTab .= "<span id='transcription-status-indicator' class='status-indicator'
                                onclick='event.stopPropagation(); document.getElementById(\"transcription-status-dropdown\").classList.toggle(\"show\")'> " .$itemData['TranscriptionStatusName'] . " </span>";
                    $editorTab .= "<div id='transcription-status-dropdown' class='sub-status status-dropdown-content'>";

                    foreach($statusTypes as $statusTyp) {
                        if($statusTyp['CompletionStatusId'] != 4 || current_user_can('administrator')) {
                            if($itemData['TranscriptionStatusId'] == $statusTyp['CompletionStatusId']) {
                                $editorTab .= "<div class='status-dropdown-option status-dropdown-option-current'
                                            onclick='changeStatus(" . $_GET['item'] . ", null, \"" . $statusTyp['Name'] . "\", \"transcriptionStatusId\", " . $statusTyp['CompletionStatusId'] . ", \"" . $statusTyp['ColorCode'] . "\", " . sizeof($progressData) . ", this)'>";
                                    $editorTab .= "<i class='fal fa-circle' style='color:transparent;background-image: -webkit-gradient(linear, left top, left bottom, color-stop(0, " . $statusTyp['ColorCode'] . "), color-stop(1, " . $statusTyp['ColorCodeGradient'] . "));'></i>";
                                    $editorTab .= $statusTyp['Name'];
                                $editorTab .= "</div>";
                            } else {
                                $editorTab .= "<div class='status-dropdown-option'
                                            onclick='changeStatus(" . $_GET['item'] . ", null, \"" . $statusTyp['Name'] . "\", \"transcriptionStatusId\", " . $statusTyp['CompletionStatusId'] . ", \"" . $statusTyp['ColorCode'] . "\", " . sizeof($progressData) . ", this)'>";
                                    $editorTab .= "<i class='fal fa-circle' style='color: transparent; background-image: -webkit-gradient(linear, left top, left bottom, color-stop(0, " . $statusTyp['ColorCode'] . "), color-stop(1, " . $statusTyp['ColorCodeGradient'] . "));'></i>";
                                    $editorTab .= $statusTyp['Name'];
                                $editorTab .= "</div>";
                            }
                        }
                    }
                    $editorTab .= "</div>";
                $editorTab .= "</div>";
                $editorTab .= "<div id='popout-language-holder'></div>";
            $editorTab .= "</div>"; // End of inner Header
            $editorTab .= "<div id='switch-tr-view' style='float:right;'><i class='fa fa-pencil' style='font-size:30px;color:#0a72cc;cursor:pointer;margin-top:5px;'></i></div>";
        $editorTab .= "</div>"; // End of header
        $editorTab .= "<div style='clear:both;'></div>";
        // Editor and Language Selector
        if($activeTr == 'htr' || ($currentTranscription['Text'] == null && $htrTranscription != null)) {
          //  $editorTab .= "<div id='transcription-edit-container' style='display:none;'>";
            $mtrTranscription = $currentTranscription['Text'];
            $currentTranscription['Text'] = $htrTranscription;

            $editorTab .= "<div id='transcription-edit-container' style='display:none;'>";
                // MCE Editor
                $editorTab .= "<div id='mce-wrapper-transcription' class='login-required'>";
                    $editorTab .= "<div id='mytoolbar-transcription'></div>";
                    $editorTab .= "<div id='item-page-transcription-text' rows='8'>";
                        if($mtrTranscription != null) {
                            $editorTab .= $mtrTranscription;
                        }
                    $editorTab .= "</div>";
                $editorTab .= "</div>";


            ////
            // $editorTab .= "<div id='transcription-edit-container' style='display:none;'>";
            // // MCE Editor
            // $editorTab .= "<div id='mce-wrapper-transcription' class='login-required htr-active-tr'>";
            //     $editorTab .= "<div id='mytoolbar-transcription'></div>";
            //     $editorTab .= "<div id='item-page-transcription-text' rows='8'>";
            //         $editorTab .= "<img src='".home_url()."/wp-content/themes/transcribathon/images/htr_active.svg' style='margin-left:15px;'>";
            //     $editorTab .= "</div>";
            // $editorTab .= "</div>";

            $editorTab .= "<script>
                document.querySelector('.transcription-headline-header span').textContent = 'HTR TRANSCRIPTION';
                document.querySelector('#switch-tr-view').classList.add('htr-trans');
            </script>";

        } else {
            $editorTab .= "<div id='transcription-edit-container' style='display:none;'>";
                // MCE Editor
                $editorTab .= "<div id='mce-wrapper-transcription' class='login-required'>";
                    $editorTab .= "<div id='mytoolbar-transcription'></div>";
                    
                    $editorTab .= "<div id='item-page-transcription-text' rows='8'>";
                        // if($currentTranscription != null) {
                            $editorTab .= $currentTranscription['Text'];
                        // }
                       // $editorTab .= $formTr;
                    $editorTab .= "</div>";
                $editorTab .= "</div>";
        }
            // Language Selector
            $editorTab .= "<div class='transcription-mini-metadata'>";
                $editorTab .= "<div id='transcription-language-selector' class='language-selector-background language-selector login-required'>";
                    $editorTab .= "<select>";
                        $editorTab .= "<option value='' disabled selected hidden>";
                            $editorTab .= "Language<span class='headline-header'>(s) of the Document</span>:";
                        $editorTab .= "</option>";
                        foreach($languages as $language) {
                            $editorTab .= "<option value='" . $language['LanguageId'] . "'>";
                                $editorTab .=  $language['Name'] . " (" . $language['NameEnglish'] . ")";
                            $editorTab .= "</option>";
                        }
                    $editorTab .= "</select>";
                $editorTab .= "</div>";
                $editorTab .= "<div id='transcription-selected-languages' class='language-selected'>";
                    $editorTab .= "<ul>";
                        if($itemData['Transcriptions'][0]['Languages'] != null) {
                            $transcriptionLanguages = $itemData['Transcriptions'][0]['Languages'];
                            foreach($transcriptionLanguages as $trLanguage) {
                                $editorTab .= "<li class='theme-colored-data-box'>";
                                    $editorTab .= $trLanguage['Name'] . " (" . $trLanguage['NameEnglish'] . ")";
                                    $editorTab .= '<script>
                                        jQuery("#transcription-language-selector option[value=\'' . $trLanguage['LanguageId'] . '\']").prop("disabled", true)
                                        </script>';
                                    $editorTab .= "<i class='far fa-times' onClick='removeTranscriptionLanguage(" . $trLanguage['LanguageId'] . ", this)'></i>";
                                $editorTab .= "</li>";
                            }
                        }
                    $editorTab .= "</ul>";
                $editorTab .= "</div>";

                $editorTab .= "<div class='transcription-metadata-container'>";
                    if($currentTranscription['Text'] != null) {
                        $editorTab .= "<div id='no-text-selector' style='display:none;'>";
                    } else {
                        $editorTab .= "<div id='no-text-selector'>";
                    }
                        $editorTab .= "<label class='square-checkbox-container login-required'>";
                            $editorTab .= "<span>No Text</span>";
                            $noTextChecked = "";
                            if($currentTranscription != null) {
                                if($currentTranscription['NoText'] == '1') {
                                    $noTextChecked = "checked";
                                }
                            }
                            $editorTab .= "<input id='no-text-checkbox' type='checkbox' " . $noTextChecked . ">";
                            $editorTab .= "<span class='theme-color-background item-checkmark checkmark'></span>";
                        $editorTab .= "</label>";
                    $editorTab .= "</div>";

                    $editorTab .= "<button class='item-page-save-button language-tooltip' id='transcription-update-button' style='display:block!important;background:#0a72cc;border-color:#0a72cc;color:#fff;'
                                    onClick='updateItemTranscription(" . $itemData["ItemId"] . ", " . get_current_user_id() . ", \"" . $statusTypes[1]['ColorCode'] . "\", " . sizeof($progressData) . ")'>";
                        $editorTab .= "SAVE"; // Save transcription
                        $editorTab .= "<span class='language-tooltip-text'>Please select a language</span>";
                    $editorTab .= "</button>";




                    $editorTab .= "<div id='item-transcription-spinner-container' class='spinner-container spinner-container-right'>";
                        $editorTab .= "<div class='spinner'></div>";
                    $editorTab .= "</div>";

                    $editorTab .= "<div style='clear:both;'></div>";
                $editorTab .= "</div>";
                $editorTab .= "<div style='clear:both;'></div>";
            $editorTab .= "</div>";
        $editorTab .= "</div>"; // End of 'editable' section

        $editorTab .= "<div id='transcription-view-container' style='display:block;'>";
            $editorTab .= "<div id='current-tr-view' style='padding-top: 11px;'>";
                $editorTab .= $currentTranscription['Text'];
            $editorTab .= "</div>";
            // Transcription Translation
            $editorTab .= "<h4 class='item-page-section-headline' id='translate-tr' style='cursor:pointer;'>";
                $editorTab .= "<i class='far fa-caret-circle-down' style='margin-right:8px;font-size:17px;'></i>";
                $editorTab .= "English Translation";
            $editorTab .= "</h4>";
            $editorTab .= "<div id='translated-tr' style='display:none;'></div>";
            $editorTab .= $trHistory;
            $editorTab .= "<div style='min-height:20px;'>&nbsp</div>";
        $editorTab .= "</div>";

    $editorTab .= "</div>"; // End of Transcription-section

    // Description tab
    $descriptionTab = "";
    $descriptionTab .= "<div id='description-editor' class='item-page-section'>";
        $descriptionTab .= "<div class='item-page-section-headline-container'>";
            $descriptionTab .= "<div id='description-collapse-heading' class='theme-color item-page-section-headline'>";
                $descriptionTab .= "<span class='headline-header'>DESCRIPTION</span>";
            // Save button placeholder
        //$descriptionTab .= "</div>";
        // description status  changer
                $descriptionTab .= "<div id='description-status-changer' class='status-changer section-status-changer login-required' style='background-color:" . $itemData['DescriptionStatusColorCode'] . ";' >";
                if(current_user_can('administrator')) {
                    $descriptionTab .= "<span id='description-status-indicator' class='status-indicator'
                                        onClick='event.stopPropagation(); document.getElementById(\"description-status-dropdown\").classList.toggle(\"show\")'> " . $itemData['DescriptionStatusName'] . " </span>";
                } else {
                    $descriptionTab .= "<span id='description-status-indicator' class='status-indicator'> " . $itemData['DescriptionStatusName'] . " </span>";
                }

                    $descriptionTab .= "<div id='description-status-dropdown' class='sub-status status-dropdown-content'>";
                        foreach($statusTypes as $sType) {
                            if($sType['CompletionStatusId'] != 4 || current_user_can('administrator')){
                                if($itemData['DescriptionStatusId'] == $sType['CompletionStatusId']) {
                                    $descriptionTab .= "<div class='status-dropdown-option status-dropdown-option-current'
                                                        onClick=\"changeStatus(" . $_GET['item'] . ", null, '" . $sType['Name'] . "', 'descriptionStatusId', " . $sType['CompletionStatusId'] . ", '" . $sType['ColorCode'] . "', " . sizeof($progressData) . ", this)\">";
                                        $descriptionTab .= "<i class='fal fa-circle' style='color:transparent; background-image: -webkit-gradient(linear, left top, left bottom, color-stop(0, ". $sType['ColorCode'] ."), color-stop(1, ". $sType['ColorCodeGradient'] ."));'></i>";
                                        $descriptionTab .= $sType['Name'];
                                    $descriptionTab .= "</div>";
                                } else {
                                    $descriptionTab .= "<div class='status-dropdown-option'
                                                        onClick=\"changeStatus(" . $_GET['item'] . ", null, '" . $sType['Name'] . "', 'descriptionStatusId', " . $sType['CompletionStatusId'] . ", '" . $sType['ColorCode'] . "', " . sizeof($progressData) . ", this)\">";
                                        $descriptionTab .= "<i class='fal fa-circle' style='color:transparent; background-image: -webkit-gradient(linear, left top, left bottom, color-stop(0, ". $sType['ColorCode'] ."), color-stop(1, ". $sType['ColorCodeGradient'] ."));'></i>";
                                        $descriptionTab .= $sType['Name'];
                                    $descriptionTab .= "</div>";
                                }
                            }
                        } 
                    $descriptionTab .= "</div>";
                $descriptionTab .= "</div>";
            $descriptionTab .= "</div>";
        $descriptionTab .= "</div>";

        $descriptionTab .= "<div style='clear:both;'></div>";

        /// Document Date, before on enrichments tab

        $descriptionTab .= "<div id='item-date-container'>";
            $descriptionTab .= "<h6 class='theme-color item-data-input-headline login-required'>";
                $descriptionTab .= "Document Date ";
               // $descriptionTab .= "<i style='margin-left: 5px;' class='fas fa-plus-circle' onClick='this.parentElement.parentElement.classList.toggle(\"show\");'></i>";
                $descriptionTab .= "<i id='date-open' class=\"fas fa-edit\"></i>";
            $descriptionTab .= "</h6>";
            if($itemData['DateStartDisplay'] != null || $itemData['DateEndDisplay'] != null) {
                $descriptionTab .= "<div class='document-date-container'>";
                    $descriptionTab .= "<div class='date-top'>";
                        $descriptionTab .= "<div style='float:left;display:inline-block;'>Start Date:</div>";
                        $descriptionTab .= "<div style='float:right;display:inline-block;margin-right:60%;'>End Date:</div>";
                    $descriptionTab .= "</div>";
                    $descriptionTab .= "<div style='clear:both;'></div>";
                    $descriptionTab .= "<div class='date-bottom'>";
                        $descriptionTab .= "<div class='start-date' style='float:left;display:inline-block;'>" . $itemData['DateStartDisplay'] . "</div>";
                        $descriptionTab .= "<div class='end-date' style='float:right;display:inline-block;margin-right:60%;'>" . $itemData['DateEndDisplay'] . "</div>";
                    $descriptionTab .= "</div>";
                $descriptionTab .= "</div>";
            }
    
            $descriptionTab .= "<div class='item-date-inner-container'>";
                $descriptionTab .= "<label>Start Date</label>";
                if($itemData['DateStartDisplay'] != null) {
                    $startTimestamp = strtotime($itemData['DateStart']);
                    $dateStart = date('d/m/Y', $startTimestamp);
                    $descriptionTab .= "<div class='item-date-display-container'>";
                        $descriptionTab .= "<span type='text' id='startdateDisplay' class='item-date-display'>";
                            $descriptionTab .= $itemData['DateStartDisplay'];
                        $descriptionTab .= "</span>";
                        $descriptionTab .= "<span class='edit-item-date edit-item-data-icon login-required'><img class='calendar-img' src='".home_url()."/wp-content/themes/transcribathon/admin/inc/custom_shortcodes/upload-images/icon_calendar.svg'></img></span>";
                    $descriptionTab .= "</div>";
                    $descriptionTab .= "<div class='item-date-input-container' style='display:none;'>";
                        $descriptionTab .= "<input type='text' id='startdateentry' placeholder='dd/mm/yyyy' class='datepicker-input-field' value='" .$dateStart . "'>";
                    $descriptionTab .= "</div>";
                } else {
                    $descriptionTab .= "<div class='item-date-display-container' style='display:none;'>";
                        $descriptionTab .= "<span type='text' id='startdateDisplay' class='item-date-display'></span>";
                        $descriptionTab .= "<span class='edit-item-date edit-item-data-icon login-required'><img class='calendar-img' src='".home_url()."/wp-content/themes/transcribathon/admin/inc/custom_shortcodes/upload-images/icon_calendar.svg'></img></span>";
                    $descriptionTab .= "</div>";
                    $descriptionTab .= "<div class='item-date-input-container'>";
                        $descriptionTab .= "<input type='text' id='startdateentry' class='login-required datepicker-input-field' placeholder='dd/mm/yyyy'>";
                    $descriptionTab .= "</div>";
                }
            $descriptionTab .= "</div>";
    
            $descriptionTab .= "<div class='item-date-inner-container'>";
                $descriptionTab .= "<label>End Date</label>";
                if($itemData['DateEndDisplay'] != null) {
                    $endTimestamp = strtotime($itemData['DateEnd']);
                    $dateEnd = date('d/m/Y', $endTimestamp);
                    $descriptionTab .= "<div class='item-date-display-container'>";
                        $descriptionTab .= "<span type='text' id='enddateDisplay' class='item-date-display'>";
                            $descriptionTab .= $itemData['DateEndDisplay'];
                        $descriptionTab .= "</span>";
                        $descriptionTab .= "<span class='edit-item-date edit-item-data-icon login-required'><img class='calendar-img' src='".home_url()."/wp-content/themes/transcribathon/admin/inc/custom_shortcodes/upload-images/icon_calendar.svg'></img></span>";
                    $descriptionTab .= "</div>";
                    $descriptionTab .= "<div class='item-date-input-container' style='display:none;'>";
                        $descriptionTab .= "<input type='text' id='enddateentry' class='datepicker-input-field' placeholder='dd/mm/yyyy' value='" . $dateEnd . "'>";
                    $descriptionTab .= "</div>";
                } else {
                    $descriptionTab .= "<div class='item-date-display-container' style='display:none;'>";
                        $descriptionTab .= "<span type='text' id='enddateDisplay' class='item-date-display'>";
                        $descriptionTab .= "</span>";
                        $descriptionTab .= "<span class='edit-item-date edit-item-data-icon login-required'><img class='calendar-img' src='".home_url()."/wp-content/themes/transcribathon/admin/inc/custom_shortcodes/upload-images/icon_calendar.svg'></img></span>";
                    $descriptionTab .= "</div>";
                    $descriptionTab .= "<div class='item-date-input-container'>";
                        $descriptionTab .= "<input type='text' id='enddateentry' class='login-required datepicker-input-field' placeholder='dd/mm/yyyy'>";
                    $descriptionTab .= "</div>";
                }
            $descriptionTab .= "</div>";
            
            // Date type checkmark
            $descriptionTab .= "<div class='creation-date-container'>";
                $descriptionTab .= "<label class='date-checkbox-container'> Creation Date <i class='fas fa-question-circle' title='Is this the date when the document was created?'></i>";
                    $descriptionTab .= "<input class='date-type-check' type='checkbox' name='CreationDate' value='Creation Date'>";
                    $descriptionTab .= "<span class='date-checkmark'></span>";
                $descriptionTab .= "</label>";
            $descriptionTab .= "</div>";
    
            $descriptionTab .= "<button class='item-page-save-button login-required' id='item-date-save-button'
                                onClick='saveItemDate(" . $itemData['ItemId'] . ", " . get_current_user_id() . ", \"" . $statusTypes[1]['ColorCode'] . "\", " . sizeof($progressData) . ")'>";
                $descriptionTab .= "<i class='fas fa-save'></i>";
            $descriptionTab .= "</button>";
            $descriptionTab .= "<div id='item-date-spinner-container' class='spinner-container spinner-container-right'>";
                $descriptionTab .= "<div class='spinner'></div>";
            $descriptionTab .= "</div>";
            $descriptionTab .= "<div style='clear:both;'></div>";
        $descriptionTab .= "</div>";

        $descriptionTab .= "<div id='doc-type-area' class='description-save'>";
            $descriptionTab .= "<h6 class='theme-color item-data-input-headline login-required'>";
                $descriptionTab .= "Document Type ";
              //  $descriptionTab .= "<i style='margin-left: 5px;' class='fas fa-plus-circle' onClick='this.parentElement.parentElement.classList.toggle(\"show\");'></i>";
                $descriptionTab .= "<i id='media-open' class=\"fas fa-edit\"></i>";
            $descriptionTab .= "</h6>";

            // Document type, view only
            $descriptionTab .= "<div id='doc-type-view'>";
            foreach($itemData['Properties'] as $property) {
                if($property['PropertyType'] == "Category") {
                    $descriptionTab .= "<div class='keyword-single' >" . $property['PropertyValue'] . "</div>";
                }
            }
            $descriptionTab .= "</div>";

            $descriptionTab .= "<div id='category-checkboxes' class='login-required'>";
            foreach($categories as $category) {
                $checked = "";
                if($itemData['Properties'] != null) {
                    foreach($itemData['Properties'] as $itemProp) {
                        if($itemProp['PropertyId'] == $category['PropertyId']) {
                            $checked = "checked";
                            break;
                        }
                    }
                }
                $descriptionTab .= "<label class='square-checkbox-container'>";
                    $descriptionTab .= $category['PropertyValue'];
                    $descriptionTab .= "<input class='category-checkbox' id='type-" . $category['PropertyValue'] . "-checkbox' type='checkbox' " . $checked . "
                                        name='" . $category['PropertyValue'] . "' value='" . $category['PropertyId'] . "'
                                        onClick='addItemProperty(" . $_GET['item'] . ", " . get_current_user_id() . ", \"category\", \"" .$statusTypes[1]['ColorCode'] . "\", " . sizeof($progressData) . ", \"" . $category['PropertyValue'] . "\", this)'/>";
                    $descriptionTab .= "<span class='item-checkmark checkmark'></span>";
                $descriptionTab .= "</label>";
            }
            $descriptionTab .= "<div style='clear:both;'></div>";
            $descriptionTab .= "</div>";
        $descriptionTab .= "</div>";
        $descriptionTab .= "<div id='description-area'>";
            $descriptionTab .= "<h6 class='theme-color item-data-input-headline login-required'>";
                $descriptionTab .= "Item Description ";
             //   $descriptionTab .= "<i style='margin-left: 5px;' class='fas fa-plus-circle' onClick='this.parentElement.parentElement.classList.toggle(\"show\");'></i>";
                $descriptionTab .= "<i id='description-open' class=\"fas fa-edit\"></i>";
            $descriptionTab .= "</h6>";

            // Description text view only
            $descriptionTab .= "<div class='current-description'>";
                $descriptionTab .= $itemData['Description'];
            $descriptionTab .= "</div>";

            $descriptionLanguage = "";
            foreach($languages as $language) {
                if($itemData['DescriptionLanguage'] == $language['LanguageId']) {
                    $descriptionLanguage = $language['Name'];
                }
            }
            if($descriptionLanguage != '') {
                $descriptionTab .= "<div class='description-language'>";
            } else {
                $descriptionTab .= "<div class='description-language' style='display:none;'>";
            }
            $descriptionTab .= "<h6 class='enrich-language'> Language of Description </h6>";
            $descriptionTab .= "<div>";
       
                $descriptionTab .= "<div class='language-single'>" . $descriptionLanguage . "</div>";
            $descriptionTab .= "</div></div>";

            $descriptionTab .= "<textarea id='item-page-description-text' class='login-required' name='description' rows='2'>";
                if($itemData['Description'] != null) {
                    $descriptionTab .= htmlspecialchars($itemData['Description'], ENT_QUOTES, 'UTF-8');
                }
            $descriptionTab .= "</textarea>";

            // $descriptionTab .= "<div style='margin-top:6px;'>";

            // New position for Description Language
            $descriptionTab .= "<div id='description-language-selector' class='language-selector-background language-selector login-required'>";
            if($itemData['DescriptionLanguage'] != null) {
                $descriptionTab .= "<span id='language-sel-placeholder' class='language-select-selected' style='margin-right:5px;'>Language of Description: </span>";
            }
                $descriptionTab .= "<select>";
                    if($itemData['DescriptionLanguage'] == null) {
                        $descriptionTab .= "<option value='' disabled selected hidden>";
                            $descriptionTab .= "Language of the Description";
                        $descriptionTab .= "</option>";
                        foreach($languages as $language) {
                            $descriptionTab .= "<option value='" . $language['LanguageId'] . "'>";
                                $descriptionTab .= $language['Name'] . " (" . $language['NameEnglish'] . ")";
                            $descriptionTab .= "</option>";
                        }
                    } else {
                        foreach($languages as $language) {
                            if($itemData['DescriptionLanguage'] == $language['LanguageId']) {
                                $descriptionTab .= "<option value='" . $language['LanguageId'] . "' selected>";
                                    $descriptionTab .= $language['Name'];
                                $descriptionTab .= "</option>";
                            } else {
                                $descriptionTab .= "<option value='" . $language['LanguageId'] . "'>";
                                    $descriptionTab .= $language['Name'];
                                $descriptionTab .= "</option>";
                            }

                        }
                    }
                $descriptionTab .= "</select>";
            $descriptionTab .= "</div>";
            

                $descriptionTab .= "<button disabled class='language-tooltip' id='description-update-button' style='float:right'
                                    onClick='updateItemDescription(" . $itemData['ItemId'] . ", " . get_current_user_id() . ", \"" . $statusTypes[1]['ColorCode'] . "\", " . sizeof($progressData) . ")' >";
                    $descriptionTab .= "SAVE"; // save description
                    $descriptionTab .= "<span class='language-tooltip-text'>Please select a language</span>";
                $descriptionTab .= "</button>";

                $descriptionTab .= "<div id='item-description-spinner-container' class='spinner-container spinner-container-right'>";
                    $descriptionTab .= "<div class='spinner'></div>";
                $descriptionTab .= "</div>";

                $descriptionTab .= "<div style='clear:both;'></div>";
            // $descriptionTab .= "</div>";
            // $descriptionTab .= "<div style='clear:both;'></div>";
            // $descriptionTab .= "</div>";
            // $descriptionTab .= "<span id='description-update-message'></span>";
        $descriptionTab .= "</div>";

        // Keywords
        $descriptionTab .= "<div id='item-page-keyword-container'>";
            $descriptionTab .= '<div id="item-page-keyword-headline" class="keyword-collapse">';
                $descriptionTab .= '<h6 class="theme-color item-data-input-headline login-required" title="Click to add keywords">';
                $descriptionTab .= 'Keywords ';
                // $taggingTab .= "<button id='keyword-plus-button' type='submit' class='edit-data-save-right'
                // onClick='document.querySelector(\"#keyword-save-button\").click();'>";
                $descriptionTab .= '<i style="margin-left: 5px;" class="fas fa-plus-circle"></i>';
                // $taggingtab .= "</button>";
                $descriptionTab .= '<i id=\'keywords-open\' class="fas fa-edit"></i></h6>';
            $descriptionTab .= '</div>';

            $descriptionTab .= '<div  style="position:relative;width:100%;">';
                $descriptionTab .= '<div id="keyword-input-container" class="" style="display:none;margin-right:10px;margin-bottom:10px;">';
                    $descriptionTab .= '<input type="text" id="keyword-input" name="" placeholder="&nbsp Add a Keyword">';
                    $descriptionTab .= "<button id='keyword-save-button' type='submit' class='theme-color' style='display:block;background:none;border:none'
                                        onClick='saveKeyword(".htmlspecialchars($itemData['ItemId'], ENT_QUOTES, 'UTF-8').", ".get_current_user_id()."
                                        , \"".$statusTypes[1]['ColorCode']."\", ".sizeof($progressData).")'>";
                        $descriptionTab .= '<i style="font-size:20px;" class="fas fa-save"></i>';
                    $descriptionTab .= '</button>';
                    $descriptionTab .= '<div id="item-keyword-spinner-container" class="spinner-container spinner-container-left">';
                        $descriptionTab .= '<div class="spinner"></div>';
                    $descriptionTab .= "</div>";
                    $descriptionTab .= '<div style="clear: both;"></div>';
                $descriptionTab .= '</div>';
            $descriptionTab .= '</div>';

            $descriptionTab .= '<div id="item-keyword-list" class="item-data-output-listt">';

                foreach ($itemData['Properties'] as $property) {
                    if ($property['PropertyType'] == "Keyword") {
                        $descriptionTab .= '<div id="'.$property['PropertyId'].'" class="keyword-single">';
                            $descriptionTab .= htmlspecialchars_decode($property['PropertyValue']);
                            $descriptionTab .= '<i class="login-required delete-item-datas far fa-times" style="margin-left:5px;"
                                                onClick="deleteItemData(\'properties\', '.$property['PropertyId'].', '.$_GET['item'].', \'keyword\', '.get_current_user_id().')"></i>';
                        $descriptionTab .= '</div>';
                    }
                }

                $descriptionTab .= '</div>';
        $descriptionTab .= "</div>";

            // Other Sources
            $descriptionTab .= "<div id='item-page-link-container'>";
            $descriptionTab .= '<div class="collapse-headline collapse-controller" data-toggle="collapse" href="#link-input-container">';
                $descriptionTab .= '<h6 class="theme-color item-data-input-headline login-required" title="Click to add a link">';
                    $descriptionTab .= 'External Web Resources ';
                    // $taggingTab .= "<button type='submit' class='edit-data-save-right' id='link-save-button'
                    // onClick='saveLink(".$itemData['ItemId'].", ".get_current_user_id()."
                    // , \"".$statusTypes[1]['ColorCode']."\", ".sizeof($progressData).")'>";
                    // $taggingTab .= '<div>Save</div>';
                    // $taggingTab .= "</button>";
                    $descriptionTab .= '<i style="margin-left: 5px;" class="fas fa-plus-circle"></i>';
                $descriptionTab .= '<i id="links-open" class="fas fa-edit"></i></h6>';
            $descriptionTab .= '</div>';

            $descriptionTab .= '<div id="link-input-container" class="collapse" style="padding-right:70px;position:relative;">';

                $descriptionTab .= '<div class="link-url-input">';
                    $descriptionTab .= '<input type="url" name="" placeholder="&nbsp Enter URL here">';
                $descriptionTab .= '</div>';

                $descriptionTab .= '<div class="link-description-input">';
                // $taggingTab .= '<label>Additional description:</label><br/>';
                    $descriptionTab .= '<textarea rows= "3" type="text" placeholder="&nbsp Add description of the link" name=""></textarea>';
                $descriptionTab .= '</div>';
                $descriptionTab .= "<div class='form-buttons-right' style='display:inline-block;position:absolute;right:40px;top:-10px;'>";
                    $descriptionTab .= "<button type='submit' class='theme-color edit-data-save-right' id='link-save-button'
                                    onClick='saveLink(".$itemData['ItemId'].", ".get_current_user_id()."
                                    , \"".$statusTypes[1]['ColorCode']."\", ".sizeof($progressData).")'>";
                        $descriptionTab .= "<i style='font-size:20px;' class='fas fa-save'></i>";
                    $descriptionTab .= "</button>";
                    $descriptionTab .= '<div id="item-link-spinner-container" class="spinner-container spinner-container-left">';
                        $descriptionTab .= '<div class="spinner"></div>';
                    $descriptionTab .= "</div>";
                    $descriptionTab .= '<div style="clear:both;"></div>';
                $descriptionTab .=    "</div>";
                $descriptionTab .= '<div style="clear:both;"></div>';
            $descriptionTab .=    "</div>";

            $descriptionTab .= '<div id="item-link-list" class="item-data-output-list">';
            foreach ($itemData['Properties'] as $property) {
                if($property['PropertyDescription'] != 'NULL') {
                    $propDescription =  $property['PropertyDescription'];
                    $descPHolder = $property['PropertyDescription'];
                } else {
                    $propDescription = "";
                    $descPHolder = "";
                }
                if($property['PropertyType'] == "Link") {
                    $descriptionTab .= "<div id='link-" . $property['PropertyId'] . "'>";
                        $descriptionTab .= "<div id='link-data-output-" . $property['PropertyId'] . "' class='link-single'>";
                            $descriptionTab .= "<div id='link-data-output-display-" . $property['PropertyId'] . "' class='link-data-output-content'>";
                                $descriptionTab .= "<i class='far fa-external-link' style='margin-left: 3px;margin-right:5px;color:#0a72cc;font-size:14px;'></i>";
                                $descriptionTab .= "<a href='". $property['PropertyValue'] . "' target='_blank'>" . htmlspecialchars_decode($property['PropertyValue']) . "</a>";
                            $descriptionTab .= "</div>";
                            $descriptionTab .= "<div class='edit-del-link'>";
                                $descriptionTab .= "<i class='edit-item-data-icon fas fa-pencil theme-color-hover login-required'
                                    onClick='openLinksourceEdit(" . $property['PropertyId'] . ")'></i>";
                                $descriptionTab .= "<i class='edit-item-data-icon delete-item-data fas fa-trash-alt theme-color-hover login-required'
                                    onClick='deleteItemData(\"properties\", " . $property['PropertyId'] . ", " . $itemData['ItemId'] . ", \"link\", " . get_current_user_id() .")'></i>";
                            $descriptionTab .= "</div>";
                            $descriptionTab .= "<div class='prop-desc' style='bottom:6px;padding-left:23px;'>" . $propDescription . "</div>";
                        $descriptionTab .= "</div>";
        
                        $descriptionTab .= "<div class='link-data-edit-container' id='link-data-edit-" . $property['PropertyId'] . "'>";
                            $descriptionTab .= "<div id='link-" . $property['PropertyId'] . "-url-input' class='link-url-input'>";
                                $descriptionTab .= "<input type='url' value='" . htmlspecialchars($property['PropertyValue'], ENT_QUOTES, 'UTF-8') . "' placeholder='Enter URL here'>";
                            $descriptionTab .= "</div>";
                            $descriptionTab .= "<div id='link-" . $property['PropertyId'] . "-description-input' class='link-description-input'>";
                                $descriptionTab .= "<textarea rows='3' type='text' placeholder='' name=''>" . htmlspecialchars_decode($descPHolder) . "</textarea>";
                            $descriptionTab .= "</div>";
                            $descriptionTab .= "<div class='form-buttons-right'>";
                                $descriptionTab .= "<div class='link-btn-right'>" ;
                                    $descriptionTab .= "<button class='theme-color-background'
                                        onClick='editLink(" . $property['PropertyId'] . ", " . $itemData['ItemId'] . ", " . get_current_user_id() . ")'>";
                                        $descriptionTab .= "SAVE";
                                    $descriptionTab .= "</button>";
                                $descriptionTab .= "</div>";
                                $descriptionTab .= "<div class='link-btn-left'>";
                                    $descriptionTab .= "<button class='theme-color-background'
                                        onClick='openLinksourceEdit(" . $property['PropertyId'] . ")'>";
                                        $descriptionTab .= "CANCEL";
                                    $descriptionTab .= "</button>";
                                $descriptionTab .= "</div>";
                                $descriptionTab .= "<div id='item-link-" . $property['PropertyId'] . "-spinner-container' class='spinner-container spinner-container-left'>";
                                    $descriptionTab .= "<div class='spinner'></div>";
                                $descriptionTab .= "</div>";
                                $descriptionTab .= "<div style='clear:both;'></div>";
                            $descriptionTab .= "</div>";
                        $descriptionTab .= "</div>";
                    $descriptionTab .= "</div>";
                }
            }

    // $enrichmentTab .= '</ul>';
            $descriptionTab .= '</div>';
    // $enrichmentTab .= "<div id='save-all-tags'>";
    //     $enrichmentTab .= "<span style='display:inline-block;'>SAVE</span>";
    // $enrichmentTab .= "</div>";
        $descriptionTab .= "</div>";

    $descriptionTab .= "</div>"; // end of 'item-page-section'

    // // Image Slider
    // $numOfPhotos = count($itemImages);
    // // Get the image of the Current Item
    // $startingSlide = array_search($_GET['item'], array_column($itemImages, 'ItemId'));

    // $allImages = [];

    // for($x = 0; $x < $numOfPhotos; $x++) {
    //     $sliderImg = json_decode($itemImages[$x]['ImageLink'], true);
    //     $sliderImgLink = createImageLinkFromData($sliderImg, array('size' => '200,200'));

    //     if($sliderImg['height'] == null) {
    //         $sliderImgLink = str_replace('full', '50,50,1800,1100', $sliderImgLink);
    //     }

    //     array_push($allImages, ($sliderImgLink . ' || ' . $itemImages[$x]['ItemId'] . ' || ' . $itemImages[$x]['CompletionStatusColorCode'] . ' || ' . $isActive));
    // }

    // $imageSlider = "";
    // $imageSlider .= "<div id='slider-images' style='display:none;'>" . json_encode($allImages) . "</div>";
    // $imageSlider .= "<div id='story-id' style='display:none;'>" . $itemData['StoryId'] . "</div>";
    // $startingSlide = array_search($_GET['item'], array_column($itemImages, 'ItemId'));
    // $imageSlider .= "<div id='current-itm' style='display:none;'>" . $startingSlide . "</div>";
    // $imageSlider .= "<div id='img-slider'>";
    //     $imageSlider .= "<div id='slider-container'>";
    //         $imageSlider .= "<button class='prev-slide' type='button' aria-label='Previous'><i class='fas fa-chevron-left'></i></button>";
    //         $imageSlider .= "<button class='next-slide' type='button' aria-label='Next'><i class='fas fa-chevron-right'></i></button>";
    //         $imageSlider .= "<div id='inner-slider'></div>";
    //         $imageSlider .= "<div id='dot-indicators'></div>";
    //     $imageSlider .= "</div>";
    // $imageSlider .= "</div>";


    // Metadata
    $metaData .= "";
    $metaData .= "<div id='meta-container'>";

        // Contributor
        if($itemData['StorydcContributor']) {
            $metaData .= "<div class='single-meta'>";
                $metaData .= "<p class='mb-1'> Contributor </p>";
                $metaData .= "<p class='meta-p'>" . str_replace(' || ', ' | ', $itemData['StorydcContributor']) . "</p>";
            $metaData .= "</div>";
        }

        //Creator
        if($itemData['StorydcCreator']) {
            $metaData .= "<div class='single-meta'>";
                $metaData .= "<p class='mb-1'>Creator</p>";
                $metaData .= "<p class='meta-p'>" . str_replace(' || ', ';', $itemData['StorydcCreator']) . "</p>";
            $metaData .= "</div>";
        }

        // Date
        if($itemData['StorydcDate']) {
            $metaData .= "<div class='single-meta'>";
                $metaData .= "<p class='mb-1'>Date</p>";
                $storyDates = array_unique(explode(' || ', $itemData['StorydcDate']));
                foreach($storyDates as $date){
                    if(substr($date, 0, 4) == 'http'){
                        // $content .= "<p class='meta-p'><a target='_blank' href='".$date."'>" . $date . "</a></p>";
                        continue;
                    } else {
                        $metaData .= "<p class='meta-p'>" . $date . ";</p>";
                    }
                }
            $metaData .= "</div>";
        }

        // Institution
        if($itemData['StoryedmDataProvider']) {
            $metaData .= "<div class='single-meta'>";
                $metaData .= "<p class='mb-1'>Institution</p>";
                $metaData .= "<p class='meta-p'>".$itemData['StoryedmDataProvider']."</p>";
            $metaData .= "</div>";
        }

        //Identifier
        if($itemData['StoryExternalRecordId']) {
            $metaData .= "<div class='single-meta'>";
                $metaData .= "<p class='mb-1'>Identifier</p>";
                if(substr($itemData['StoryExternalRecordId'], 0, 4) == 'http'){
                    $metaData .= "<p class='meta-p'><a target='_blank' href='".$itemData['StoryExternalRecordId']."'>" . substr($itemData['StoryExternalRecordId'], 0, 45) . "</a></p>";
                } else {
                    $metaData .= "<p class='meta-p'>" . $itemData['StoryExternalRecordId'] . "</p>";
                }
            $metaData .= "</div>";
        }

        //Document Language
        if($itemData['StorydcLanguage']) {
            $metaData .= "<div class='single-meta'>";
                $metaData .= "<p class='mb-1'>Document Language</p>";
                $dcLanguage = array_unique(explode(' || ', $itemData['StorydcLanguage']));
                $metaData .= "<p class='meta-p'>" . implode(';', $dcLanguage) . "</p>";
            $metaData .= "</div>";
        }

        // Creation Start
        if($itemData['StoryedmBegin']) {
            $metaData .= "<div class='single-meta'>";
                $metaData .= "<p class='mb-1'>Creation Start</p>";
                $metaData .= "<p class='meta-p'>" . str_replace(' || ', ";", $itemData['StoryedmBegin']) . "</p>";
            $metaData .= "</div>";
        }

        // Creation End
        if($itemData['StoryedmEnd']) {
            $metaData .= "<div class='single-meta'>";
                $metaData .= "<p class='mb-1'>Creation End</p>";
                $metaData .= "<p class='meta-p'>" . str_replace(' || ', ";", $itemData['StoryedmEnd']) . "</p>";
            $metaData .= "</div>";
        }

        // Story Source
        if($itemData['StorydcSource']) {
            $metaData .= "<div class='single-meta'>";
                $metaData .= "<p class='mb-1'>Story Source</p>";
                $source = array_unique(explode(' || ', $itemData['StorydcSource']));
                $metaData .= "<p class='meta-p'>" . implode('</br>', $source) . "</p>";
            $metaData .= "</div>";
        }

        // Story Title
        $metaData .= "<div class='single-meta'>";
            $metaData .= "<p class='mb-1'>Story Title</p>";
            $metaData .= "<p class='meta-p'>". str_replace(' || ', ";", $itemData['StorydcTitle']) . "</p>";
        $metaData .= "</div>";

        // dctermsProvenance
        if($itemData['StorydctermsProvenance']) {
            $metaData .= "<div class='meta-sticker'>";
                $metaData .= "<p class='mb-1'>Provenance</p>";
                $provenance = array_unique(explode(' || ', $itemData['StorydctermsProvenance']));
                $metaData .= "<p class='meta-p'>". implode(';' , $provenance) ."</p>";
            $metaData .= "</div>";
        }

        // Type
        if($itemData['StorydcType']) {
            $metaData .= "<div class='single-meta'>";
                $metaData .= "<p class='mb-1'>Type</p>";
                $metaData .= "<p class='meta-p'>" . str_replace(' || ', ';', $itemData['StorydcType']) . "</p>";
            $metaData .= "</div>";
        }

        // Rights
        if($itemData['StoryedmRights']) {
            $metaData .= "<div class='single-meta'>";
                $metaData .= "<p class='mb-1'>Rights</p>";
                $edmRights = array_unique(explode(' || ', $itemData['StoryedmRights']));
                foreach($edmRights as $right) {
                    if(substr($right, 0, 4) == 'http'){
                        $metaData .= "<p class='meta-p'><a target='_blank' href='".$right."'>" . $right . ";</a></p>";
                    } else {
                        $metaData .= "<p class='meta-p'>" . $right . ";</p>";
                    }
                }
            $metaData .= "</div>";
        }

        // Image Rights
        if($itemData['StorydcRights']) {
            $metaData .= "<div class='single-meta'>";
                $metaData .= "<p class='mb-1'>Image Rights</p>";
                $imgRights = array_unique(explode(' || ', $itemData['StorydcRights']));
                foreach($imgRights as $iRight) {
                    if(substr($iRight, 0, 4) == 'http'){
                        $metaData .= "<p class='meta-p'><a target='_blank' href='".$iRight."'>" . $iRight . "</a></p>";
                    } else {
                        $metaData .= "<p class='meta-p'>" . $iRight . ";</p>";
                    }
                }
            $metaData .= "</div>";
        }

        // Provider
        if($itemData['StoryedmProvider']) {
            $metaData .= "<div class='single-meta'>";
                $metaData .= "<p class='mb-1'>Provider Language</p>";
                $metaData .= "<p class='meta-p'>".$itemData['StoryedmProvider']."</p>";
            $metaData .= "</div>";
        }

        // Providing Country
        if($itemData['StoryedmCountry']) {
            $metaData .= "<div class='single-meta'>";
                $metaData .= "<p class='mb-1'>Providing Country</p>";
                $metaData .= "<p class='meta-p'>".$itemData['StoryedmCountry']."</p>";
            $metaData .= "</div>";
        }

        // Provider Language
        if($itemData['StoryedmLanguage']) {
            $metaData .= "<div class='single-meta'>";
                $metaData .= "<p class='mb-1'>Provider Language</p>";
                $metaData .= "<p class='meta-p'>".$itemData['StoryedmLanguage']."</p>";
            $metaData .= "</div>";
        }

        // Dataset
        if($itemData['StoryedmDatasetName']) {
            $metaData .= "<div class='single-meta'>";
                $metaData .= "<p class='mb-1'>Dataset</p>";
                $metaData .= "<p class='meta-p'>".$itemData['StoryedmDatasetName']."</p>";
            $metaData .= "</div>";
        }

        // Publisher
        if($itemData['StoryedmProvider']) {
            $metaData .= "<div class='single-meta'>";
                $metaData .= "<p class='mb-1'>Publisher</p>";
                if(substr($itemData['StoryedmProvider'], 0, 4) == 'http'){
                    $metaData .= "<p class='meta-p'><a target='_blank' href='".$itemData['StoryedmProvider']."'>" . $itemData['StoryedmProvider'] . "</a></p>";
                } else {
                    $metaData .= "<p class='meta-p'>" . $itemData['StoryedmProvider'] . "</p>";
                }
            $metaData .= "</div>";
        }

        // Medium
        if($itemData['StorydctermsMedium']) {
            $metaData .= "<div class='single-meta'>";
                $metaData .= "<p class='mb-1'>Medium</p>";
                $metaData .= "<p class='meta-p'>" . str_replace(' || ', ';', $itemData['StorydctermsMedium']) . "</p>";
            $metaData .= "</div>";
        }

        // Source Url
        if($itemData['StoryedmIsShownAt']) {
            $metaData .= "<div class='single-meta'>";
                $metaData .= "<p class='mb-1'>Source Url</span>";
                if(substr($itemData['StoryedmIsShownAt'], 0, 4) == 'http'){
                    $metaData .= "<a class='meta-p' target='_blank' href='".$itemData['StoryedmIsShownAt']."'>" . $itemData['StoryedmIsShownAt'] . "</a>";
                } else {
                    $metaData .= "<p class='meta-p'>" . $itemData['StoryedmIsShownAt'] . "</p>";
                }
            $metaData .= "</div>";
        }

        // Story Landing Page
        if($itemData['StoryedmLandingPage']) {
            $metaData .= "<div class='single-meta'>";
                $metaData .= "<p class='mb-1'>Landing Page</p>";
                if(substr($itemData['StoryedmLandingPage'], 0, 4) == 'http'){
                    $metaData .= "<p class='meta-p'><a target='_blank' href='".$itemData['StoryedmLandingPage']."'>" . substr($itemData['StoryedmLandingPage'], 0, 45) . "</a></p>";
                } else {
                    $metaData .= "<p class='meta-p'>" . $itemData['StoryedmLandingPage'] . "</p>";
                }
            $metaData .= "</div>";
        }

        // Parent Story
        if($itemData['StoryParentStory']) {
            $metaData .= "<div class='single-meta'>";
                $metaData .= "<p class='mb-1'>Parent Story</p>";
                if(substr($itemData['StoryParentStory'], 0, 4) == 'http'){
                    $metaData .= "<p class='meta-p'><a target='_blank' href='".$itemData['StoryParentStory']."'>" . $itemData['StoryParentStory'] . "</a></p>";
                } else {
                    $metaData .= "<p class='meta-p'>" . $itemData['StoryParentStory'] . "</p>";
                }
            $metaData .= "</div>";
        }


    $metaData .= "</div>"; // End of meta container

    // Story Description
    $storyDescription .= "<div id='storydesc'>";
        $storyDescription .= "<p class='mb-1'> Story Description</p>";
        $storyDescriptions = array_unique(explode(" || ", $itemData['StorydcDescription']));
        foreach($storyDescriptions as $description) {
            $storyDescription .= "<p class='meta-p'>" . $description . "</p>";
        }
    $storyDescription .= "</div>";
    // Item progress bar

    //$content .= "<div id='main-section'>";
        // Build Page Layout
    $content .= "<section id='image-slider-section' style='padding:0;height:50px;background-color:#f8f8f8;'>";
        $content .= "<div id='story-id' style='display:none;'>" . $itemData['StoryId'] . "</div>";
       // $content .= $imageSlider;
       $content .= "<div id='testdiv'></div>";
        //$content .= "<div class='back-to-story'><a href='" . home_url() . "/documents/story/?story=" . $itemData['StoryId'] . "'><i class='fas fa-arrow-left' style='margin-right:7.5px;'></i> Back to the Story </a></div>";
    $content .= "</section>";

        // Title
    $content .= "<section id='title-n-progress'>";
        $content .= "<div class='title-n-btn'>";
            $content .= "<div id='missing-info' style='display:none;'>" . get_current_user_id() . "</div>";
            $content .= "<h4 id='item-header' title='Back to the Story Page'><b><a href='" . home_url() . "/documents/story/?story=" . $itemData['StoryId'] . "' style='text-decoration:none;'><span id='back-to-story-title' class='storypg-title'><i class='fas fa-chevron-right' style='margin-right:5px;font-size:14px;bottom:2px;position:relative;'></i>" . $itemData['StorydcTitle'] . "</span></a><span> <i class='fas fa-chevron-right' style='margin-right:5px;font-size:14px;bottom:2px;position:relative;'></i> Item " . ($startingSlide + 1) . "</span></b></h4>";
        $content .= "</div>";
        // if(current_user_can('administrator')) {
        //     $content .= "<div class='tr-comp-btn' style='float:right;cursor:pointer;margin-right:25px;'>";
        //         $content .= "<a href='" . home_url() . "/documents/story/transcription-comparison/?story=" . $itemData['StoryId'] . "&item=" . $itemData['ItemId'] . "'>COMPARE TRANSCRIPTIONS</a>";
        //     $content .= "</div>";
        // }

        $content .= "<div class='item-progress'>";
        if($isLoggedIn) {
            $content .= "<div class='change-all-status'>CHANGE ITEM STATUS</div>";

            $content .= "<div id='item-status-selector' style='display:none;'>";
                // Not Started
                $content .= "<div id='all-not-started' class='status-dropdown-option'>";
                    $content .= "<i class='fal fa-circle' style='color: transparent; background-image: -webkit-gradient(linear, left top, left bottom, color-stop(0, #eeeeee), color-stop(1, #eeeeee));'></i>";
                    $content .= "Mark item as Not Started";
                $content .= "</div>";
                $content .= "<script>
                    const allNotStarted = document.querySelector('#all-not-started');
                allNotStarted.addEventListener('click', function() {
                    changeStatus(" . $_GET['item'] . ", null, 'Not Started', 'taggingStatusId', 1, '#eeeeee', " . sizeof($progressData) . ", this);
                    changeStatus(" . $_GET['item'] . ", null, 'Not Started', 'locationStatusId', 1, '#eeeeee', " . sizeof($progressData) . ", this);
                    changeStatus(" . $_GET['item'] . ", null, 'Not Started', 'descriptionStatusId', 1, '#eeeeee', " . sizeof($progressData) . ", this);
                    changeStatus(" . $_GET['item'] . ", null, 'Not Started', 'transcriptionStatusId', 1, '#eeeeee', " . sizeof($progressData) . ", this);
                    document.querySelector('#item-status-selector').style.display = 'none';
                });
                </script>";
                // Edit
                $content .= "<div id='all-edit' class='status-dropdown-option'>";
                    $content .= "<i class='fal fa-circle' style='color: transparent; background-image: -webkit-gradient(linear, left top, left bottom, color-stop(0, #fff700), color-stop(1, #ffd800));'></i>";
                    $content .= "Mark item as Edit";
                $content .= "</div>";
                $content .= "<script>
                    const allEdit = document.querySelector('#all-edit');
                allEdit.addEventListener('click', function() {
                    changeStatus(" . $_GET['item'] . ", null, 'Edit', 'taggingStatusId', 2, '#fff700', " . sizeof($progressData) . ", this);
                    changeStatus(" . $_GET['item'] . ", null, 'Edit', 'locationStatusId', 2, '#fff700', " . sizeof($progressData) . ", this);
                    changeStatus(" . $_GET['item'] . ", null, 'Edit', 'descriptionStatusId', 2, '#fff700', " . sizeof($progressData) . ", this);
                    changeStatus(" . $_GET['item'] . ", null, 'Edit', 'transcriptionStatusId', 2, '#fff700', " . sizeof($progressData) . ", this);
                    document.querySelector('#item-status-selector').style.display = 'none';
                });
                </script>";
                // Review
                $content .= "<div id='all-review' class='status-dropdown-option'>";
                    $content .= "<i class='fal fa-circle' style='color: transparent; background-image: -webkit-gradient(linear, left top, left bottom, color-stop(0, #ffc720), color-stop(1, #f0b146));'></i>";
                    $content .= "Mark item as Review";
                $content .= "</div>";
                $content .= "<script>
                    const allReview = document.querySelector('#all-review');
                allReview.addEventListener('click', function() {
                    changeStatus(" . $_GET['item'] . ", null, 'Review', 'taggingStatusId', 3, '#ffc720', " . sizeof($progressData) . ", this);
                    changeStatus(" . $_GET['item'] . ", null, 'Review', 'locationStatusId', 3, '#ffc720', " . sizeof($progressData) . ", this);
                    changeStatus(" . $_GET['item'] . ", null, 'Review', 'descriptionStatusId', 3, '#ffc720', " . sizeof($progressData) . ", this);
                    changeStatus(" . $_GET['item'] . ", null, 'Review', 'transcriptionStatusId', 3, '#ffc720', " . sizeof($progressData) . ", this);
                    document.querySelector('#item-status-selector').style.display = 'none';
                });
                </script>";
                // Completed
                $content .= "<div id='all-complete' class='status-dropdown-option'>";
                    $content .= "<i class='fal fa-circle' style='color: transparent; background-image: -webkit-gradient(linear, left top, left bottom, color-stop(0, #61e02f), color-stop(1, #4dcd1c));'></i>";
                    $content .= "Mark item as Completed";
                $content .= "</div>";
                $content .= "<script>
                    const allComplete = document.querySelector('#all-complete');
                allComplete.addEventListener('click', function() {
                    changeStatus(" . $_GET['item'] . ", null, 'Completed', 'taggingStatusId', 4, '#61e02f', " . sizeof($progressData) . ", this);
                    changeStatus(" . $_GET['item'] . ", null, 'Completed', 'locationStatusId', 4, '#61e02f', " . sizeof($progressData) . ", this);
                    changeStatus(" . $_GET['item'] . ", null, 'Completed', 'descriptionStatusId', 4, '#61e02f', " . sizeof($progressData) . ", this);
                    changeStatus(" . $_GET['item'] . ", null, 'Completed', 'transcriptionStatusId', 4, '#61e02f', " . sizeof($progressData) . ", this);
                    document.querySelector('#item-status-selector').style.display = 'none';
                });
                </script>";
            $content .= "</div>";
        }
        $content .= "</div>";
    $content .= "</section>";

    $content .= "<section id='viewer-n-transcription' class='collapsed'>";
        $content .= "<div id='full-view-container'>";
        //$content .= "<section id='viewer-n-transcription'>";
            $content .= "<div id='full-view-l'>";
                $content .= $imageViewer;

                $content .= "<div class='htr-btns'>";
                if(current_user_can('administrator')) {
                    $content .= "<div>";
                        $content .= "<a href='". home_url() ."/import-htr-transcription/?itemId=". $itemData['ItemId'] ."'>Run Transkribus HTR ";
                        $content .= "<i class='fas fa-desktop'></i></a>";
                    $content .= "</div>";
                    if($htrTranscription != '') {
                        $content .= "<div>";
                            $content .= "<a href='" . home_url() . "/documents/story/item-page-htr/?story=". $itemData['StoryId'] ."&item=" . $itemData['ItemId'] . "'>HTR Editor ";
                            $content .= "<i class='fas fa-keyboard'></i></a>";
                        $content .= "</div>";

                        $content .= "<div>";
                            $content .= "<a href='" . home_url() . "/documents/story/transcription-comparison/?story=" . $itemData['StoryId'] . "&item=" . $itemData['ItemId'] . "'>Compare Transcriptions <i class=\"far fa-columns\"></i></a>";
                        $content .= "</div>";
                    }
                }

                    //$content .= "<div style='clear:both;'></div>";
                $content .= "</div>";

            $content .= "</div>";
            $content .= "<div id='full-view-r'>";
            //var_dump($itemData);
                // Transcription
                $content .= "<div id='transcription-container' style='height:600px;'>";
                    $content .= "<div id='startTranscription' class='mtr-active' style='display:flex;flex-direction:row;justify-content:space-between;cursor:pointer;' title='click to open editor'>";
                        $content .= "<div style='display:inline-block;'><h5 style='color:#0a72cc;'><i style=\"font-size: 20px;margin-bottom:5px;\" class=\"fa fa-quote-right\" aria-hidden=\"true\"></i> TRANSCRIPTION</h5></div>";
                        $content .= "<div>";
                            $content .= "<div class='status-display' style='line-height: normal;background-color:".$itemData['TranscriptionStatusColorCode']."'>";
                                $content .= "<span class='status-indicator-view'>" . $itemData['TranscriptionStatusName'] . "</span>";
                            $content .= "</div>";
                            $content .= "<i class=\"fa fa-pencil right-i\" aria-hidden=\"true\"></i>";
                        $content .= "</div>";
                    $content .= "</div>";
                    $content .= "<div style='background-image:linear-gradient(14deg,rgba(255,255,255,1),rgba(238,236,237,0.4),rgba(255,255,255,1));height:5px'> &nbsp </div>";
                    if($itemData['Transcriptions'][0]['NoText'] == '1') {
                        $content .= "<div id='no-text-placeholder'>";
                            $content .= "<p style='position:relative;top:30%;'><i class=\"far fa-check-circle\" ></i> <b>ITEM CONTAINS <br> NO TEXT</b></p>";
                        $content .= "</div>";
                        $content .= "<div class='current-transcription' style='display:none;'></div>";
                        $content .= "<div class='transcription-language' style='display:none;'>";
                            $content .= "<h6 class='enrich-language'> Language(s) of Transcription </h6>";
                            $content .= "<div style='padding-left:24px;'></div>";
                        $content .= "</div>";
                    } else {
                        if(!str_contains(strtolower($currentTranscription['Text']),'<script>')) {
                            if($activeTr == 'htr' || ($currentTranscription['Text'] == null && $htrTranscription != null)) {
                                $formattedTranscription = $htrTranscription;
                                $content .= "<script>
                                    document.querySelector('#startTranscription h5').textContent = 'HTR TRANSCRIPTION';
                                    document.querySelector('#startTranscription').classList.replace('mtr-active', 'htr-active');
                                    document.querySelector('#startTranscription').addEventListener('click', function() {
                                        location.href = '" . home_url() . "/documents/story/item-page-htr/?story=". $itemData['StoryId'] ."&item=" . $itemData['ItemId'] . "';
                                    });
                                </script>";
                            } else {
                                $formattedTranscription = htmlspecialchars_decode($currentTranscription['Text']);
                            }
                        }
                        if(strlen($formattedTranscription) < 700 && strlen($formattedTranscription) != 0) {
                            $content .= "<div class='current-transcription' style='padding-left:24px;'>";
                                $content .= $formattedTranscription;
                            $content .= "</div>";

                            $content .= "<div class='transcription-language'>";
                                $content .= "<h6 class='enrich-language'> Language(s) of Transcription </h6>";
                                $content .= "<div style='padding-left:24px;'>";
                                foreach($currentTranscription['Languages'] as $language) {
                                    $content .= "<div class='language-single'>" . $language['Name'] . "</div>";
                                }
                                $content .= "</div>";
                        } else if(strlen($formattedTranscription) != 0) {
                            $content .= "<div class='current-transcription' style='padding-left:24px;'>";
                                $content .= $formattedTranscription;
                            $content .= "</div>";
                            $content .= "<div id='transcription-collapse-btn'> Show More </div>";

                            $content .= "<div class='transcription-language'>";
                                $content .= "<h6 class='enrich-language'> Language(s) of Transcription </h6>";
                                $content .= "<div style='padding-left:24px;'>";
                                if(!empty($currentTranscription['Languages'])) {
                                    foreach($currentTranscription['Languages'] as $language) {
                                        $content .= "<div class='language-single'>" . $language['Name'] . "</div>";
                                    }
                                }
                                $content .= "</div>";
                            } else {
                                $content .= "<div id='no-text-placeholder'>";
                                    $content .= "<p style='position:relative;top:40%;'><img src='".home_url()."/wp-content/themes/transcribathon/images/pen_in_circle.svg'></p>";
                                $content .= "</div>";
                                $content .= "<div class='current-transcription' style='display:none;'></div>";
                                $content .= "<div class='transcription-language' style='display:none;'>";
                                    $content .= "<h6 class='enrich-language'> Language(s) of Transcription </h6>";
                                    $content .= "<div style='padding-left: 24px;'></div>";
                                $content .= "</div>";
                            }
                    }
                    $content .= "</div>";

                $content .= "</div>"; // end of transcription
            $content .= "</div>";
        $content .= "<div style='clear:both;'></div>";
    $content .= "</section>";
    // $content .= "<div style='clear:both;'></div>";

    $content .= "<section id='location-n-enrichments'>";
        $content .= "<div id='map-left'>";
            // Location Header
            $content .= "<div id='startLocation' class='enrich-header' style='display:flex;flex-direction:row;justify-content:space-between;margin:10px 0;'>";
                $content .= "<div style='display:inline-block;'><h5 style='color:#0a72cc;'><img src='".home_url()."/wp-content/themes/transcribathon/images/location-icon.svg' alt='location-icon' width='28px' height='28px'> LOCATION</h5></div>";
                $content .= "<div>";
                    $content .= "<div class='status-display' style='background-color:".$itemData['LocationStatusColorCode']."'>";
                        $content .= "<span class='status-indicator-view'>" . $itemData['LocationStatusName'] . "</span>";
                    $content .= "</div>";
                    $content .= "<i class=\"fa fa-pencil right-i\" aria-hidden=\"true\"></i>";
                $content .= "</div>";
            $content .= "</div>";
            // $content .= "<div style='background-image:linear-gradient(14deg,rgba(255,255,255,1),rgba(238,236,237,0.4),rgba(255,255,255,1));height:5px;position:relative;bottom:20px;'> &nbsp </div>";
            $content .= "<div id='normal-map' style='height:400px;'>";
                $content .= $mapBox;
            $content .= "</div>";
            $content .= $mapEditor;
        $content .= "</div>"; // end of left side

        $content .= "<div id='enrich-right'>";
            // Right side
            $content .= "<div id='startDescription' class='enrich-header' style='display:flex;flex-direction:row;justify-content:space-between;margin-top:10px;margin-bottom:5px;'>";
                $content .= "<div style='display:inline-block;'><h5 style='color:#0a72cc;'><i style=\"font-size: 20px;margin-bottom:5px;\" class=\"fa fa-book\" aria-hidden=\"true\"></i> ABOUT THIS DOCUMENT</h5></div>";
                $content .= "<div>";
                    if($itemData['DescriptionStatusId'] < $itemData['TaggingStatusId'] && $itemData['DescriptionStatusId'] != 1) {
                        $content .= "<div class='status-display' style='background-color:".$itemData['DescriptionStatusColorCode']."'>";
                            $content .= "<span class='status-indicator-view'>" . $itemData['DescriptionStatusName'] . "</span>";
                        $content .= "</div>";
                    } else if ($itemData['TaggingStatusId'] < $itemData['DescriptionStatusId'] && $itemData['TaggingStatusId'] != 1) {
                        $content .= "<div class='status-display' style='background-color:".$itemData['TaggingStatusColorCode']."'>";
                            $content .= "<span class='status-indicator-view'>" . $itemData['TaggingStatusName'] . "</span>";
                        $content .= "</div>";
                    } else {
                        $content .= "<div class='status-display' style='background-color:".$itemData['TaggingStatusColorCode']."'>";
                            $content .= "<span class='status-indicator-view'>" . $itemData['TaggingStatusName'] . "</span>";
                        $content .= "</div>";
                    }
                $content .= "<i class=\"fa fa-pencil right-i\" aria-hidden=\"true\"></i>";
                $content .= "</div>";
            $content .= "</div>";
            $content .= "<div style='background-image:linear-gradient(14deg,rgba(255,255,255,1),rgba(238,236,237,0.4),rgba(255,255,255,1));height:5px;position:relative;bottom:5px;'> &nbsp </div>";

            // $content .= "<div id='description-container'>";
                $content .= "<div id='description-view'>";
                    $content .= $descriptionTab;
                $content .= "</div>";

                $content .= "<div id='enrich-view'>";
                    $content .= $enrichmentTab;
                $content .= "</div>";
            // $content .= "</div>"; // end of enrichment

        $content .= "</div>"; // end of right side
        $content .= "<div style='clear:both;'></div>";
    $content .= "</section>";

    $content .= "<section id='story-info' class='collapsed' style='height:325px;'>";
        $content .= "<div id='meta-collapse' class='add-info enrich-header' style='color:#0a72cc;font-size:1.2em;cursor:pointer;margin:25px 0;' role='button' aria-expanded='false'>";
            $content .= "<span><h5><i style='margin-right:14px;' class=\"fa fa-info-circle\" aria-hidden=\"true\"></i>STORY INFORMATION</span><span style='float:right;padding-right:10px;'><i id='angle-i' style='font-size:25px;' class='fas fa-angle-down'></i></h5></span>";
        $content .= "</div>";
        $content .= "<div style='background-image:linear-gradient(14deg,rgba(255,255,255,1),rgba(238,236,237,0.4),rgba(255,255,255,1));height:5px;position:relative;bottom:25px;'> &nbsp </div>";
        $content .= "<div id='meta-left'>";
            // Metadata
            $content .= $metaData;
        $content .= "</div>";
        $content .= "<div id='meta-right'>";
            $content .= $storyDescription;
        $content .= "</div>";
        $content .= "<div style='clear:both;'></div>";
        $content .= "<div id='meta-cover'><i class='fas fa-angle-double-down'></i></div>";
    $content .= "</section>";

    $content .= "<div id='image-view-container' class='panel-container-horizontal' style='display:none;overflow:hidden;'>";
        // Image Section
        $content .= "<div id='item-image-section' class='panel-left'>";
        // Viewer will be added here in 'Full Screen Mode'
        $content .= "</div>";
        // Splitter
        $content .= "<div id='item-splitter' class='splitter-vertical'></div>";
        // Data Section
        $content .= "<div id='item-data-section' class='panel-right'>";
            $content .= "<div id='item-data-header'>";
                $content .= "<div class='fs-title'>" . $itemData['Title'] . "</div>";
                $content .= '<div class="view-switcher" id="switcher-casephase" style="display:inline-block;">';
                    $content .= '<ul id="item-switch-list" class="switch-list" style="z-index:10;top:0;right:0;">';
                        $content .= "<li>";
                            $content .= '<div id="popout" class="view-switcher-icons" style="display:inline-block;width: 20px;"
                                onclick="switchItemView(event, \'popout\')"><img src="'.home_url().'/wp-content/themes/transcribathon/images/icon_float.svg"></div>';
                        $content .= "</li>";

                        $content .= "<li>";
                            $content .= '<div id="vertical-split" class="view-switcher-icons" style="display:inline-block;width: 20px;"
                                onclick="switchItemView(event, \'vertical\')"><img src="'.home_url().'/wp-content/themes/transcribathon/images/icon_below.svg"></div>';
                        $content .= "</li>";

                        $content .= "<li>";
                            $content .= '<div id="horizontal-split" class="view-switcher-icons active theme-color" style="font-size:12px;display:inline-block;width: 20px;"
                                onclick="switchItemView(event, \'horizontal\')"><img src="'.home_url().'/wp-content/themes/transcribathon/images/icon_side.svg"></div>';
                        $content .= "</li>";

                        $content .= "<li style='position:relative;bottom:2px;'>";
                            $content .= '<div class="switch-i"><i id="horizontal-close" class="fas fa-window-minimize view-switcher-icons" style="position:relative;bottom:3px;"
                                onclick="switchItemView(event, \'closewindow\')"></i></div>';
                        $content .= "</li>";

                        $content .= "<li style='position:relative;bottom:2px;'>";
                            $content .= '<div class="switch-i"><i id="close-window-view" class="fas fa-times view-switcher-icons" onClick="switchItemPageView()" style="position:relative;bottom:1px;color:#2b2b2b;"></i></div>';
                        $content .= "</li>";

                    $content .= '</ul>';
                $content .= '</div>';
                $content .= "<div style='clear:both;'></div>";
                // Tab menu
                $content .= '<ul id="item-tab-list" class="tab-list" style="list-style: none;">';

                    $content .= "<li>";
                        $content .= "<div class='theme-color tablinks' title='Tutorial'
                            onclick='switchItemTab(event, \"help-tab\")'>";
                            $content .= '<i class="fas fa-file-spreadsheet"></i>';
                            $content .= "<p class='tab-h it'><span><b>RATION CARD</b></span></p>";
                        $content .= "</div>";
                    $content .= "</li>";

                    $content .= "<li>";
                        $content .= "<div id='tr-tab' class='theme-color tablinks active' title='Transcription'
                            onclick='switchItemTab(event, \"editor-tab\")'>";
                            $content .= '<i class="fa fa-quote-right tab-i"></i>';
                            $content .= "<p class='tab-h'><i class='tab-status fal fa-circle' style='color:".$itemData['TranscriptionStatusColorCode'].";background-color:".$itemData['TranscriptionStatusColorCode'].";'></i>";
                            $content .= "<span ><b> TRANSCRIPTION</b></span></p>";
                        $content .= "</div>";
                    $content .= "</li>";

                    $content .= "<li>";
                        $content .= "<div id='loc-tab' class='theme-color tablinks' title='Locations'
                            onclick='switchItemTab(event, \"tagging-tab\");'>";
                           $content .= "<img src='".home_url()."/wp-content/themes/transcribathon/images/location-icon.svg' alt='location-icon' height='40px' width='40px' style='height:28px;position:relative;bottom:3px;'>";
                           $content .= "<p class='tab-h'><i class='tab-status fal fa-circle' style='color:".$itemData['LocationStatusColorCode'].";background-color:".$itemData['LocationStatusColorCode'].";'></i>";
                           $content .= "<span><b> LOCATION</b></span></p>";
                        $content .= "</div>";
                    $content .= "</li>";

                    $content .= "<li>";
                        $content .= "<div id='desc-tab' class='theme-color tablinks' title='Description' onclick='switchItemTab(event, \"description-tab\");'>";
                            $content .= "<i class='fa fa-tag tab-i'></i>";
                            $content .= "<p class='tab-h'><i class='tab-status fal fa-circle' style='color:".$itemData['DescriptionStatusColorCode'].";background-color:".$itemData['DescriptionStatusColorCode'].";'></i>";
                            $content .= "<span><b> DESCRIPTION</b></span></p>";
                        $content .= "</div>";
                    $content .= "</li>";
                    $content .= "<li>";
                        $content .= "<div id='tagi-tab' class='theme-color tablinks' title='Enrichments/Tagging' onclick='switchItemTab(event, \"tag-tab\");'>";
                            $content .= "<i class='fa fa-user tab-i' aria-hidden='true'></i>";
                            $content .= "<p class='tab-h'><i class='tab-status fal fa-circle' style='color:".$itemData['TaggingStatusColorCode'].";background-color:".$itemData['TaggingStatusColorCode'].";'></i>";
                            $content .= "<span><b> PEOPLE</b></span></p>";
                        $content .= "</div>";
                    $content .= "</li>";
                    // $content .= "<li style='max-width:10px;'>";
                    //     $content .= "<div>&nbsp</div>";
                    // $content .= "</li>";
                    $content .= "<li>";
                        $content .= "<div class='theme-color tablinks' title='More Information'
                            onclick='switchItemTab(event, \"info-tab\")'>";
                            $content .= '<i class="fa fa-info-circle tab-i"></i>';
                            $content .= "<p class='tab-h it'><span><b> STORY INFO</b></span></p>";
                        $content .= "</div>";
                    $content .= "</li>";
                $content .= '</ul>';
            $content .= "</div>";

            $content .= "<div id='item-data-content' class='panel-right-tab-menu'>";
                // Editor tab
                $content .= "<div id='editor-tab' class='tabcontent'>";
                    // Content will be added here in switchItemPageView function
                    $content .= $editorTab;

                    //$content .= $trHistory;
                    // Automatic Enrichments 
                    if(empty($itemAutoE['data'])) {
                        $content .= "<div id='run-itm-enrich'> Analyse Transcription for Automatic Translation and Enrichments </div>";
                        $content .= "<div style='position:relative;'><div id='auto-itm-spinner-container' class='spinner-container'>";
                            $content .= "<div class='spinner'></div>";
                        $content .= "</div></div>";
                    }
                $content .= "</div>";
                // Description Tab
                $content .= "<div id='description-tab' class='tabcontent' style='display:none;'>";
                   // $content .= $descriptionTab;
                $content .= "</div>";
                // Info tab
                $content .= "<div id='info-tab' class='tabcontent' style='display:none;'>";
                    $content .= "<div class='item-page-section-headline theme-color'>" . $itemData['StorydcTitle'] . "</div>";
                    $content .= "<div id='full-v-story-description' style='max-height:40vh;'>";

                    $content .= "</div>";
                    if($itemData['StorydcDescription'] != null && $itemData['StorydcDescription'] != 'NULL' && strlen($storyDescription) > 1300) {
                        $content .= "<div id='story-full-collapse'>Show More</div>";
                    }
                    if($engDescription != null) {
                        $content .= "<div id='eng-desc-fs'>";
                            $content .= "<p class='mb-1'> English Translation </p>";
                            $content .= $engDescription;
                        $content .= "</div>";
                    }
                    $content .= "<div id='full-v-metadata'>";
                    if(empty($storyAutoE['data'])) {
                        $content .= "<div id='run-stry-enrich'> Analyse Story Description for Automatic Enrichments </div>";
                    
                        $content .= "<h3 id='verify-h' style='display:none;'> Verify Automatically Identified Enrichments </h3>";
                        $content .= "<div id='auto-enrich-story' style='position:relative;'>";
                            $content .= "<div id='auto-story-spinner-container' class='spinner-container'>";
                                $content .= "<div class='spinner'></div>";
                            $content .= "</div>";
                        $content .= "</div>";
                        $content .= "<div id='accept-story-enrich' style='display:none;'> SUBMIT </div>";
                    } elseif(!empty($storyAutoE['data'])) {
                        $content .= "<p class='auto-h'> Automatically Identified Enrichments </p>";
                        $content .= "<div id='auto-enrich-story' style='position:relative;'>";
                        foreach($storyAutoE['data'] as $enrichment) {
                            $wikiIdArr = explode('/', $enrichment['WikiData']);
                            $wikiId = array_pop($wikiIdArr);
                            $content .= "<div class='enrich-view'>";
                                $content .= "<p>";
                                    $content .= $enrichment['Type'] == 'Place' ? "<img src='".home_url()."/wp-content/themes/transcribathon/images/location-icon.svg' height='20px' width='20px' alt='location-icon'>" : "<i class='fas fa-user left-i'></i>";
                                    $content .= "<span class='enrich-label'>" . $enrichment['Name'] . "</span>";
                                $content .= "</p>";
                                $content .= "<p class='enrich-wiki'> Wikidata Reference: <a href='" . $enrichment['WikiData'] . "' target='_blank'>" . $wikiId . "</a></p>";
                                $content .= "<i class='fas fa-trash-alt auto-delete' onClick='deleteAutoEnrichment(".$enrichment['AutoEnrichmentId'].",event)'></i>";
                            $content .= "</div>";

                        }
                        $content .= "</div>";
                    }
                    $content .= "</div>";
                // Content will be added here in switchItemPageView function
                $content .= "</div>";
                // Location tab
                $content .= "<div id='tagging-tab' class='tabcontent' style='display:none;'>";
                    // Content will be added here in switchItemPageView function
                    $content .= "<div id='full-screen-map-placeholder'></div>";
                    $content .= "<h3 id='loc-verify' style='display:none;'> Verify Automatically Identified Locations </h3>";
                    $content .= "<div id='loc-auto-enrich'></div>";
                     $content .= "<div id='accept-loc-enrich' style='display:none;'> SUBMIT </div>";
                    // $content .= $mapEditor;
                $content .= "</div>";
                // Tag tab
                $content .= "<div id='tag-tab' class='tabcontent' style='display:none'>";
                    // $enrichmentTab;
                    $content .= "<div id='ppl-auto-e-container'>";
                        $content .= "<h3 id='ppl-verify' style='display: none;'> Verify Automatically Identified Persons </h3>";
                        $content .= "<div id='ppl-auto-enrich'>";
                        // if(sizeof($itemAutoPpl) > 0) {
                        //     foreach($itemAutoPpl as $person) {
                        //         $wikiIdArr = explode('/', $person['WikiData']);
                        //         $wikiId = array_pop($wikiIdArr);
                        //         $content .= "<div class='enrich-view'>";
                        //             $content .= "<p>";
                        //                 $content .= "<i class='fas fa-user'></i>";
                        //                 $content .= "<span class='enrich-label'>" . $person['Name'] . "</span>";
                        //                 $content .= "<span class='enrich-wiki'><a href='" . $person['WikiData'] . "' target='_blank'> Wikidata ID: " . $wikiId . "</a></span>";
                        //             $content .= "</p>";
                        //         $content .= "</div>";
                        //     }
                        // }
                        $content .= "</div>";
                        $content .= "<div id='accept-ppl-enrich' style='display:none;'> SUBMIT </div>";
                    $content .= "</div>";
                $content .= "</div>";
                // Help tab
                $content .= "<div id='help-tab' class='tabcontent' style='display:none;'>";
                    //$content .= do_shortcode('[tutorial_item_slider]');
                    $content .= "<div id='rc-form'>";
                        $content .= "<h3><b> Grad Zagreb </b></h3>";
                            // Top adress and card registration number(just labels)
                            $content .= "<table class='rc-top-address'>";
                                $content .= "<tr>";
                                    $content .= "<td class='rc-first-col top-part'> &nbsp</td>";
                                    $content .= "<td class='rc-second-col'> Reg. broj: </td>";
                                    $content .= "<td class='rc-third-col'> Prezime i ime podnosioca prijave:</td>";
                                    $content .= "<td class='btn-col'>&nbsp</td>";
                                $content .= "</tr>";
                                $content .= "<tr>";

                                    $content .= "<td class='rc-first-col mid-part' style='position:relative;'>";
                                        $content .= "&nbsp;";
                                    $content .= "</td>";

                                    $content .= "<td class='rc-second-col'><input type='text' id='regnumb' style='border: none;border-bottom: 1px dotted #ccc;width: 60%;margin: 0 auto;'></td>";

                                    $content .= "<td class='rc-third-col' style='vertical-align: bottom;position:relative;padding-left:4%;'>";
                                        $content .= "<span style='width:50%;display:inline-block;'><input type='text' placeholder='Prezime' id='submitter-lname' style='border: none;border-bottom: 1px dotted #ccc;'></span>";
                                        $content .= "<span style='width:50%;display:inline-block;'><input type='text' placeholder='Ime' id='submitter-fname' style='border: none;border-bottom: 1px dotted #ccc;'></span>";
                                        $content .= "<i id='submitter-check' class='fas fa-check' style='display:none;'></i>";
                                        $content .= "<div id='submitter-spinner' class='spinner-container'>";
                                            $content .= "<div class='spinner'></div>";
                                        $content .= "</div>";
                                    $content .= "</td>";

                                    $content .= "<td class='btn-col'>";
                                        $content .= "<i id='save-submitter' class='fas fa-save'></i>";
                                        $content .= "<i id='delete-submitter' class='fas fa-trash-alt' style='display:none;'></i>";
                                    $content .= "</td>";

                                $content .= "</tr>";

                                $content .= "<tr>";
                                    $content .= "<td class='rc-first-col bot-part' style='vertical-align:top!important;position:relative;'>Ulica, trg ili ina oznaka: </td>";

                                    $content .= "<td class='rc-second-col'> &nbsp </td>";

                                    $content .= "<td class='rc-third-col'>Prezime i ime i stan kucevlasnika:</td>";

                                    $content .= "<td class='btn-col'>&nbsp</td>";
                                $content .= "</tr>";
                                $content .= "<tr>";
                                    $content .= "<td class='rc-first-col bot-part' style='vertical-align:top!important;position:relative;'> &nbsp";
                                        $content .= "<input type='text' placeholder='Ulica' id='m-address' style='border: none;border-bottom: 1px dotted #ccc;'>";
                                        $content .= "<div class='spinner-container'>";
                                           $content .= "<div class='spinner'></div>";
                                        $content .= "</div>";
                                        $content .= "<div id='m-address-res'></div>";
                                        $content .= "<i id='m-address-check' class='fas fa-check' style='display:none;'></i>";
                                    $content .= "</td>";

                                    $content .= "<td class='rc-second-col'> &nbsp </td>";

                                    $content .= "<td class='rc-third-col' style='vertical-align: bottom;position:relative;padding-left:4%;'>";
                                        $content .= "<span style='width:50%;display:inline-block;'><input type='text' placeholder='Prezime' id='landlord-lname' style='border: none;border-bottom: 1px dotted #ccc;'></span>";
                                        $content .= "<span style='width:50%;display:inline-block;'><input type='text' placeholder='Ime' id='landlord-fname' style='border: none;border-bottom: 1px dotted #ccc;'></span>";
                                        $content .= "<i id='landlord-name-check' class='fas fa-check' style='display:none;'></i>";
                                        $content .= "<div id='landlord-spinner' class='spinner-container'>";
                                            $content .= "<div class='spinner'></div>";
                                        $content .= "</div>";
                                    $content .= "</td>";

                                    $content .= "<td class='btn-col'>";
                                        $content .= "<i id='save-l-lord' class='fas fa-save'></i>";
                                        $content .= "<i id='delete-l-lord' class='fas fa-trash-alt' style='display:none;'></i>";
                                    $content .= "</td>";

                                $content .= "</tr>";
                                $content .= "<tr>";
                                    $content .= "<td class='rc-first-col' style='vertical-align:top!important;position:relative;'>";
                                        $content .= "<label for='kbr' style='position:absolute;left:10%;'>Kbr:</label>";
                                        $content .= "<input type='text' placeholder='Kucni Broj' id='kbr' style='border: none;border-bottom: 1px dotted #ccc;'>";
                                        $content .= "<i class='fas fa-check' id='kbr-check' style='display:none;'></i>";
                                        $content .= "<i class='fas fa-search' id='rc-place-one'></i>";
                                        $content .= "<div id='edit-subm-container' style='display:none;'>";
                                            $content .= "<i class='fas fa-trash-alt' id='del-subm'></i>";
                                          //  $content .= "<i class='fas fa-pen' id='edit-subm'></i>";
                                        $content .= "</div>";
                                    $content .= "</td>";

                                    $content .= "<td class='rc-second-col'> &nbsp </td>";

                                    $content .= "<td class='rc-third-col' style='position:relative;padding-left:4%;'>";
                                        $content .= "<input type='text' id='landlord-loc' placeholder='Stan' style='border: none;border-bottom: 1px dotted #ccc;'>";
                                        $content .= "<i class='fas fa-check' id='landlord-check' style='display:none;'></i>";
                                        $content .= "<div class='spinner-container'>";
                                            $content .= "<div class='spinner'></div>";
                                        $content .= "</div>";
                                        $content .= "<div id='landlord-loc-res'></div>";
                                    $content .= "</td>";
                                    $content .= "<td class='btn-col'>";
                                        $content .= "<i id='l-lord-add' class='fas fa-search'></i>";
                                        $content .= "<div id='edit-llord-container' style='display:none;'>";
                                            $content .= "<i class='fas fa-trash-alt' id='del-llord'></i>";
                                            // $content .= "<i class='fas fa-pen' id='edit-llord'></i>;
                                        $content .= "</div>";
                                    $content .= "</td>";
                                $content .= "</tr>";
                            $content .= "</table>";

                            $content .= "<div id='show-sub-loc'>";
                            // Saved locations (submitter/landlord) will be shown here
                            $content .= "</div>";
                            $content .= "<div id='show-land-loc'>";
                            // Saved persons (submitter/landlord) will be shown here
                            $content .= "</div>";
                            $content .= "<div style='clear:both;'></div>";
                            
                            //
                            $content .= "<h3 class='rc-title'> Potrosacka prijavnica <br> za kucanstva i samce - samice. </h3>";
                            // Saving spinner
                            // $content .= "<div id='rc-spinner-container' class='spinner-container'>";
                            //     $content .= "<div class='spinner'></div>";
                            // $content .= "</div>";
                            // Main form
                            $content .= "<div id='rc-person-list'>";
                            $content .= "<form id='rc-list-form'>";
                               // $content .= "<table id='rc-table'>";
                                    // Table head
                                    $content .= "<div class='rc-list-head'>";
                                        $content .= "<span class='start-span' title='Redni Broj'>Redni Broj</span>";
                                        $content .= "<span class='first-span'>Prezime i ime</span>";
                                        $content .= "<span class='second-span'>God. Rod.</span>";
                                        $content .= "<span class='third-span' title='Odnos prema podnosiocu prijave odn. Starjesini'>Odnos prema podnosiocu prijave odn. Starjesini</span>";
                                        $content .= "<span class='fourth-span'>Zanimanje</span>";
                                        $content .= "<span class='fifth-span'>Mjesto rada</span>";
                                        $content .= "<span class='sixth-span'>&nbsp</span>";
                                    $content .= "</div>";

                                   // $content .= "<div>";
                                        $content .= "<div id='show-list-ppl'>";
                                            // $content .= "<div><span>Ime</span><span>Prezime</span><span>1943.</span><span>odnos</span><span>zanimanje</span><span>mjesto rada</span></div>";
                                            // $content .= "<div><span>Ime</span><span>Prezime</span><span>1943.</span><span>odnos</span><span>zanimanje</span><span>mjesto rada</span></div>";
                                        $content .= "</div>";
                                   // $content .= "</div>";
                                    // Table inputs
                                    $content .= "<div class='rc-list-td' style='position:relative;'>";
                                        $content .= "<span id='redni-broj-start' class='start-span'> 1 </span>";
                                        $content .= "<span class='first-span'>";
                                            $content .= "<span class='left-half'><input type='text' id='lst-p-lname' placeholder=' Prezime' name='lname'></span>";
                                            $content .= "<span class='right-half'><input type='text' id='lst-p-fname' placeholder=' Ime' name='fname'></span>";
                                        $content .= "</span>";
                                        $content .= "<span class='second-span'><input type='text' name='bdate' id='rc-bdate'></span>";
                                        $content .= "<span class='third-span'><input type='text' id='desc-rel' name='relation'></span>";
                                        $content .= "<span class='fourth-span'><input type='text' id='desc-voc' name='vocation'></span>";
                                        $content .= "<span class='fifth-span'><input type='text' id='desc-wp' name='workplace'></span>";
                                        $content .= "<span class='sixth-span'>";
                                            $content .= "<i id='save-list-person' class='fas fa-plus'></i>";
                                            $content .= "<div id='listed-person-spinner' class='spinner-container'>";
                                                $content .= "<span class='spinner'></span>";
                                            $content .= "</div>";
                                        $content .= "</span>";
                                    $content .= "</div>";
                              //  $content .= "</table>";
                                $content .= "</form>";
                            $content .= "</div>";
                            // Add Prirast/Odpad table
                            $content .= "<div id='prirast-odpad'>";
                                $content .= "<div id='prirast-btn'>";
                                    $content .= "<i class='fas fa-plus'></i>";
                                    $content .= "<p> Prirast </p>";
                                $content .= "</div>";
                                // Prirast container 
                                $content .= "<div id='prirast-container' style='display:none;'>";
                                    $content .= "<p style='font-size:9px;font-weight:600;'>PRIRAST: (Ispunjava vlast) </p>";
                                    $content .= "<form id='prirast-list-form'>";
                                        $content .= "<div id='show-prirast-ppl'></div>";
                                        $content .= "<div class='rc-list-td' style='position:relative;'>";
                                            $content .= "<span id='prirast-redni-broj' class='start-span'> 1 </span>";
                                            $content .= "<span class='first-span'>";
                                                $content .= "<span class='left-half'><input type='text' id='prirast-lname' placeholder=' Prezime' name='plname'></span>";
                                                $content .= "<span class='right-half'><input type='text' id='prirast-fname' placeholder=' Ime' name='pfname'></span>";
                                            $content .= "</span>";
                                            $content .= "<span class='second-span'><input type='text' id='prirast-bdate' name='prirast-bdate'></span>";
                                            $content .= "<span class='third-span'><input type='text' id='prirast-rel' name='p-relation'></span>";
                                            $content .= "<span class='fourth-span'><input type='text' id='prirast-voc' name='p-vocation'></span>";
                                            $content .= "<span class='fifth-span'><input type='text' id='prirast-wp' name='p-workplace'></span>";
                                            $content .= "<span class='sixth-span'>";
                                                $content .= "<i id='save-prirast-person' class='fas fa-plus'></i>";
                                                $content .= "<div id='prirast-spinner' class='spinner-container'>";
                                                    $content .= "<span class='spinner'></span>";
                                                $content .= "</div>";
                                            $content .= "</span>";
                                        $content .= "</div>";
                                    $content .= "</form>";
                                $content .= "</div>";
                                
                                //
                                $content .= "<div id='odpad-btn'>";
                                    $content .= "<i class='fas fa-plus'></i>";
                                    $content .= "<p> Odpad </p>";
                                $content .= "</div>";
                                // Odpad container
                                $content .= "<div id='odpad-container' style='display:none;'>";
                                    $content .= "<p style='font-size:9px;font-weight:600;'>ODPAD: (Ispunjava vlast) </p>";
                                    $content .= "<form id='odpad-list-form'>";
                                        $content .= "<div id='show-odpad-ppl'></div>";
                                        $content .= "<div class='rc-list-td' style='position:relative;'>";
                                            $content .= "<span id='odpad-redni-broj' class='start-span'> 1 </span>";
                                            $content .= "<span class='first-span'>";
                                                $content .= "<span class='left-half'><input type='text' id='odpad-lname' placeholder=' Prezime' name='olname'></span>";
                                                $content .= "<span class='right-half'><input type='text' id='odpad-fname' placeholder=' Ime' name='ofname'></span>";
                                            $content .= "</span>";
                                            $content .= "<span class='second-span'><input type='text' id='odpad-bdate' name='odpad-bdate'></span>";
                                            $content .= "<span class='third-span'><input type='text' id='odpad-rel' name='o-relation'></span>";
                                            $content .= "<span class='fourth-span'><input type='text' id='odpad-voc' name='o-vocation'></span>";
                                            $content .= "<span class='fifth-span'><input type='text' id='odpad-wp' name='o-workplace'></span>";
                                            $content .= "<span class='sixth-span'>";
                                                $content .= "<i id='save-odpad-person' class='fas fa-plus'></i>";
                                                $content .= "<div id='odpad-spinner' class='spinner-container'>";
                                                    $content .= "<span class='spinner'></span>";
                                                $content .= "</div>";
                                            $content .= "</span>";
                                        $content .= "</div>";
                                    $content .= "</form>";
                                $content .= "</div>";
                                
                                //
                            $content .= "</div>";
                            // Shop address
                            $content .= "<div id='shop-place' style='position:relative;'>";
                                $content .= "<p> Zivezne namirnice nabavljat cu:</p>";
                                $content .= "<label for='shop-name' style='position:relative;'>";
                                    $content .= "U radnji: ";
                                    $content .= "<input type='text' name='shop-name' id='shop-name'>";
                                    $content .= "<i class='fas fa-check' id='shop-name-check' style='display:none;'></i>";
                                $content .= "</label>";
                                $content .= "<label for='shop-loc' style='position:relative;'>";
                                    $content .= "ulica: ";
                                    $content .= "<input type='text' name='shop-loc' id='shop-loc'>";
                                    $content .= "<i class='fas fa-check' id='shop-check' style='display:none;'></i>";
                                    $content .= "<i class='fas fa-search' id='shop-loc-btn'></i>";
                                    $content .= "<div id='edit-shop-container' style='display:none;'>";
                                        $content .= "<i class='fas fa-trash-alt' id='del-shop'></i>";
                                        // $content .= "<i class='fas fa-pen' id='edit-shop'></i>;
                                    $content .= "</div>";
                                    $content .= "<div class='spinner-container'>";
                                        $content .= "<div class='spinner'></div>";
                                    $content .= "</div>";
                                    $content .= "<div id='shop-loc-res'></div>";
                                $content .= "</label>";
                            $content .= "</div>";

                            $content .= "<div id='show-bot-loc' style='margin-top:35px;'>";
                            // shop place will be added here
                            $content .= "</div>";

                            // Document date
                            $content .= "<div id='rc-date'>";
                                $content .= "<span>Zagreb, </span><input type='text' id='rc-date-entry' placeholder='dd/mm/yyyy' class='datepicker-input-field'><i id='save-rc-date' class='fas fa-save'></i>";
                                // Saving spinner
                                $content .= "<i id='date-check' class='fas fa-check' style='display:none;'></i>";
                                $content .= "<div id='rc-date-spinner-container' class='spinner-container'>";
                                    $content .= "<div class='spinner'></div>";
                                $content .= "</div>";
                            $content .= "</div>";

                            // Submit button
                            $content .= "<div id='submit-form'>SUBMIT</div>";
                            // Returned places form mapbox api
                            // $content .= "<div id='rc-loc-results-container'>";
                            //     $content .= "<p> Address search results:</p>";
                            //     $content .= "<div id='rc-place-spinner-container' class='spinner-container'>";
                            //         $content .= "<div class='spinner'></div>";
                            //     $content .= "</div>";
                            //     $content .= "<div id='rc-loc-result'>";
                            //     $content .= "</div>";
                            // $content .= "</div>";
                            
                            // Saved places
                            // $content .= "<div id='show-saved-loc'>";
                            //     $content .= "<p> Saved places: </p>";
                            // $content .= "</div>";

                            $content .= "<div id='rc-item-id' style='display:none;'>" . $_GET['item'] . "</div>";
                            $content .= "<div id='rc-user-id' style='display:none;'>" . get_current_user_id() . "</div>";
                        $content .= "</div>";
                    $content .= "</div>";
                    // Automatic enrichment tab
                    $content .= "<div id='autoEnrichment-tab' class='tabcontent' style='display:none;'>";
                    // Content will be added here in switchItemPageView function
                    $content .= "</div>";
                $content .= "</div>";
            $content .= "</div>";
        $content .= "</div>";

        // JAVASCRIPT TODO put in a separate file
    $content .= "<script>
        var ready = (callback) => {
        if (document.readyState != \"loading\") callback();
            else document.addEventListener(\"DOMContentLoaded\", callback);
}
// Replacement for jQuery document.ready; It runs the code after DOM is completely loaded
ready(() => {
const collapseBtn = document.querySelector('#transcription-collapse-btn');
const trSection = document.querySelector('#viewer-n-transcription');
const trContainer = document.querySelector('.current-transcription');
// Transcription Collapse
if(collapseBtn) {
    collapseBtn.addEventListener('click', function() {
        if(trSection.classList.contains('collapsed')){
            trSection.classList.remove('collapsed');
            trContainer.style.height = 'unset';
            //  trSection.style.height = 'unset';
            // document.querySelector('.transcription-language').style.position = 'unset';
            document.querySelector('#transcription-container').style.height = 'unset';
            collapseBtn.textContent = 'Show Less';
} else {
    trSection.classList.add('collapsed');
    //   trSection.style.height = '600px';
    trContainer.style.height = 'calc(100% - 205px)';
    //   document.querySelector('.transcription-language').style.position = 'absolute';
    document.querySelector('#transcription-container').style.height = '600px';
    collapseBtn.textContent = 'Show More';
}
});
}
// Metadata collapse
const metaCollapseBtn = document.querySelector('#meta-collapse');
const metaSection = document.querySelector('#story-info');
metaCollapseBtn.addEventListener('click', function() {
    if(metaSection.classList.contains('collapsed')) {
        metaSection.style.height = 'unset';
        metaSection.classList.remove('collapsed');
        metaCollapseBtn.querySelector('#angle-i').classList.remove('fa-angle-down');
        metaCollapseBtn.querySelector('#angle-i').classList.add('fa-angle-up');
        metaSection.querySelector('#meta-cover i').classList.add('fa-angle-double-up');
        metaSection.querySelector('#meta-cover i').classList.remove('fa-angle-double-down');
} else {
    metaSection.style.height = '325px';
    metaSection.classList.add('collapsed');
    metaCollapseBtn.querySelector('#angle-i').classList.remove('fa-angle-up');
    metaCollapseBtn.querySelector('#angle-i').classList.add('fa-angle-down');
    metaSection.querySelector('#meta-cover i').classList.remove('fa-angle-double-up');
    metaSection.querySelector('#meta-cover i').classList.add('fa-angle-double-down');
}
});
metaSection.querySelector('#meta-cover').addEventListener('click', function() {
    metaCollapseBtn.click();
});
let descLangDel = document.querySelector('#del-desc-lang');
if(descLangDel) {
    descLangDel.addEventListener('click',function() {
        updateDataProperty('items', ". $itemData['ItemId'] .", 'DescriptionLanguage', 0);
        this.parentNode.style.display = 'none';
}
);
}
});
</script>";

    $content .= '<script>
        jQuery("#item-image-section").resizable_split({
        handleSelector: "#item-splitter",
            resizeHeight: false
});
</script>';

    //$content .= "</section>"; // End of main section

    return $content;
}

add_shortcode( 'ration_cards', '_TCT_ration_cards' );