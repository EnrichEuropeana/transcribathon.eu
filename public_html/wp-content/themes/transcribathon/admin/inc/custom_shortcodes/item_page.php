<?php
/*
Shortcode: item_page
Description: Gets item data and builds the item page
*/

// include required files
include($_SERVER["DOCUMENT_ROOT"].'/wp-load.php');
// include($_SERVER["DOCUMENT_ROOT"].'/htr-import/vendor/autoload.php');

require_once(get_stylesheet_directory() . '/htr-client/lib/TranskribusClient.php');
require_once(get_stylesheet_directory() . '/htr-client/config.php');

use FactsAndFiles\Transcribathon\TranskribusClient;

date_default_timezone_set('Europe/Berlin');

function _TCT_item_page( $atts ) {
    global $ultimatemember, $config;
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
        // Get the next and previous item
        for($a = 0; $a < count($itemImages);$a++){
            if(array_search($_GET['item'], $itemImages[$a])){
                if($a != count($itemImages)){
                    $nextItem = $itemImages[$a+1]['ItemId'];
                }
                if($a != 0) {
                    $prevItem = $itemImages[$a-1]['ItemId'];
                }
            }
        }
        // Build Item page content
        $content = "";
        $content = "<style>
                            .transcription-toggle>a:hover {
                                color: #0a72cc !important;
                            }
                            #transcription-selected-languages.language-selected ul li {
                                background: #0a72cc ;
                                color: #ffffff;
                            }
                            .language-item-select{
                                background: #0a72cc ;
                                width: 15em;
                            }
                            .language-select-selected{
                                background: #0a72cc ;
                                width: 15em;
                            }
                    </style>";
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
        // Login modal
        $content .= '<div id="item-page-login-container">';
            $content .=   '<div id="item-page-login-popup">';
                $content .=   '<div class="item-page-login-popup-header theme-color-background">';
                    $content .=      '<span class="item-login-close">&times;</span>';
                $content .=  '</div>';
                $content .=  '<div class="item-page-login-popup-body">';
                    $login_post = get_posts( array(
                        'name'    => 'default-login',
                        'post_type'    => 'um_form',
                    ));
                    $content .= do_shortcode('[ultimatemember form_id="'.$login_post[0]->ID.'"]');
                $content .= '</div>';
                $content .= '<div class="item-page-login-popup-footer theme-color-background">';
                $content .= '</div>';
            $content .= '</div>';
        $content .= '</div>';
        // Large spinner
        $content .= '<div class="full-spinner-container">';
            $content .= '<div class="spinner-full"></div>';
        $content .= '</div>';
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
        // Editor tab
        $editorTab = "";
            $progressData = array(
                $itemData['TranscriptionStatusName'],
                $itemData['DescriptionStatusName'],
                $itemData['LocationStatusName'],
                $itemData['TaggingStatusName'],
                //$itemData['AutomaticEnrichmentStatusName'],
            );
            $progressCount = array (
                            'Not Started' => 0,
                            'Edit' => 0,
                            'Review' => 0,
                            'Completed' => 0
                        );
            foreach ($progressData as $status) {
                $progressCount[$status] += 1;
            }
            // Current transcription
            $editorTab .= "<div id='transcription-section' class='item-page-section'>";
                $editorTab .= "<div class='item-page-section-headline-container transcription-headline-header'>";
                    // Add start transcription to tab view
                    $editorTab .= "<div class='transcirption-view-head'>";
                        $editorTab .= "<div id='test-tr' class='theme-color item-page-section-headline'>";
                            $editorTab .= "TRANSCRIPTION";
                            // Change status & status indicator
                            $editorTab .= '<div id="transcription-status-changer" class="status-changer section-status-changer login-required" style="background-color:'.$itemData['TranscriptionStatusColorCode'].';">';
                            $editorTab .= '<span id="transcription-status-indicator" class="status-indicator"
                                                onclick="event.stopPropagation(); document.getElementById(\'transcription-status-dropdown\').classList.toggle(\'show\')">' . $itemData['TranscriptionStatusName'] . '</span>';
                            $editorTab .= '<div id="transcription-status-dropdown" class="sub-status status-dropdown-content">';

                                foreach ($statusTypes as $statusType) {
                                    if ($statusType['CompletionStatusId'] != 4 || current_user_can('administrator')) {
                                        if ($itemData['TranscriptionStatusId'] == $statusType['CompletionStatusId']) {
                                            $editorTab .= "<div class='status-dropdown-option status-dropdown-option-current'
                                                                onclick=\"changeStatus(".$_GET['item'].", null, '".$statusType['Name']."', 'TranscriptionStatusId', ".$statusType['CompletionStatusId'].", '".$statusType['ColorCode']."', ".sizeof($progressData).", this)\">";
                                            $editorTab .= "<i class='fal fa-circle' style='color: transparent;
                                                                background-image: -webkit-gradient(linear, left top, left bottom, color-stop(0, ".$statusType['ColorCode']."), color-stop(1, ".$statusType['ColorCodeGradient']."));'>
                                                            </i>".$statusType['Name']."</div>";
                                        } else {
                                            $editorTab .= "<div class='status-dropdown-option'
                                                                onclick=\"changeStatus(".$_GET['item'].", null, '".$statusType['Name']."', 'TranscriptionStatusId', ".$statusType['CompletionStatusId'].", '".$statusType['ColorCode']."', ".sizeof($progressData).", this)\">";
                                            $editorTab .= "<i class='fal fa-circle' style='color: transparent; background-image: -webkit-gradient(linear, left top, left bottom, color-stop(0, ".$statusType['ColorCode']."), color-stop(1, ".$statusType['ColorCodeGradient']."));'></i>".$statusType['Name']."</div>";
                                        }
                                    }
                                }
                            $editorTab .= '</div>';
                        $editorTab .= '</div>';
                        //
                        $editorTab .= "<div role='button' id='tr-view-start-transcription'>";
                            $editorTab .= "<i id='tr-view-btn-i' style=\"font-size:15px;margin-right:2px;\" class=\"fa fa-pencil\" aria-hidden=\"true\"></i>";
                        $editorTab .= "</div>";
                    $editorTab .= '</div>';
                            //
                        $editorTab .= "</div>";
                        $editorTab .= "<div id='lang-holder'></div>";
                    // $editorTab .= "</div>";
                    //$editorTab .= do_shortcode('[ultimatemember form_id="38"]');
                    //status-changer
                    $editorTab .= "<div id='transcription-view'>".$itemData['Transcriptions'][0]['Text']."</div>";

                   // $editorTab .= "<div class='item-page-section-headline-right-site'>";
                  //  $editorTab .= "</div>";

                $editorTab .= '</div>';
                $editorTab .= '<div style="clear: both;"></div>';

                $currentTranscription = null;
                $transcriptionList = [];
                if ($itemData["Transcriptions"] != null) {
                    $transcriptionData = $itemData["Transcriptions"];
                    foreach ($transcriptionData as $transcription) {
                        if ($transcription['CurrentVersion'] == "1") {
                            $currentTranscription = $transcription;
                        }
                        else {
                            array_push($transcriptionList, $transcription);
                        }
                    }
                }
                $editorTab .= '<div id="mce-wrapper-transcription" class="login-required">';
                    $editorTab .= '<div id="mytoolbar-transcription"></div>';
                    $editorTab .= '<div id="item-page-transcription-text" rows="4">';
                    if ($currentTranscription != null) {
                        $editorTab .= $currentTranscription['Text'];
                    }
                    $editorTab .= '</div>';
                $editorTab .= '</div>';

                    $editorTab .= "<div class='transcription-mini-metadata'>";
                        $editorTab .= '<div id="transcription-language-selector" class="language-selector-background language-selector login-required">';
                            $editorTab .= '<select>';
                                $editorTab .= '<option value="" disabled selected hidden>';
                                    $editorTab .= 'Language(s) of the Document';
                                $editorTab .= '</option>';
                                foreach ($languages as $language) {
                                    $editorTab .= '<option value="'.$language['LanguageId'].'">';
                                        $editorTab .= $language['Name']." (".$language['NameEnglish'].")";
                                    $editorTab .= '</option>';
                                }
                            $editorTab .= '</select>';
                        $editorTab .= '</div>';
                        $editorTab .= '<div id="transcription-selected-languages" class="language-selected">';
                            $editorTab .= '<ul>';
                                if ($transcriptionData[0]['Languages'] != null) {
                                    $transcriptionLanguages = $transcriptionData[0]['Languages'];
                                            foreach($transcriptionLanguages as $transcriptionLanguage) {
                                                $editorTab .= "<li class='theme-colored-data-box'>";
                                                    $editorTab .= $transcriptionLanguage['Name']." (".$transcriptionLanguage['NameEnglish'].")";
                                                    $editorTab .= '<script>
                                                                jQuery("#transcription-language-selector option[value=\''.$transcriptionLanguage['LanguageId'].'\'").prop("disabled", true)
                                                            </script>';
                                            $editorTab .= '<i class="far fa-times" onClick="removeTranscriptionLanguage('.$transcriptionLanguage['LanguageId'].', this)"></i>';
                                            $editorTab .= '</li>';
                                            }
                                }
                            $editorTab .= '</ul>';
                        $editorTab .= '</div>';
                        $editorTab .= '<div class="transcription-metadata-container">';
                            $editorTab .= "<button disabled class='item-page-save-button language-tooltip' id='transcription-update-button'
                                                    onClick='updateItemTranscription(".$itemData['ItemId'].", ".get_current_user_id()."
                                                            , \"".$statusTypes[1]['ColorCode']."\", ".sizeof($progressData).")'>";
                                $editorTab .= "SAVE"; // save transcription
                                $editorTab .= "<span class='language-tooltip-text'>Please select a language</span>";
                            $editorTab .= "</button>";

                        $editorTab .= '<div id="no-text-selector">';
                            $editorTab .= '<label class="square-checkbox-container login-required">';
                                $editorTab .= '<span>No Text</span>';
                                $noTextChecked = "";
                                if ($currentTranscription != null) {
                                    if ($currentTranscription['NoText'] == "1") {
                                        $noTextChecked = "checked";
                                    }
                                }
                                $editorTab .= '<input id="no-text-checkbox" type="checkbox" '.$noTextChecked.'>';
                                $editorTab .= '<span class="theme-color-background item-checkmark checkmark"></span>';
                            $editorTab .= '</label>';
                        $editorTab .= '</div>';
                        $editorTab .= '<div id="item-transcription-spinner-container" class="spinner-container spinner-container-right">';
                            $editorTab .= '<div class="spinner"></div>';
                        $editorTab .= "</div>";
                     $editorTab .= "<div style='clear:both'></div>";
                    $editorTab .= '</div>';
                    $editorTab .= "<div style='clear:both'></div>";
                $editorTab .= '</div>';
            $editorTab .= '</div>';
            // Description
            $editorTab .= '<div class="item-page-section" id="desc-part" hidden>';
                $editorTab .= '<div class="item-page-section-headline-container collapse-headline  item-page-section-collapse-headline">';
                    $editorTab .= '<div id="description-collapse-heading" class="theme-color item-page-section-headline">';
                        $editorTab .= "DESCRIPTION";
                    
                    // status changer
                    $editorTab .= '<div id="description-status-changer" class="status-changer section-status-changer login-required" style="background-color:'.$itemData['DescriptionStatusColorCode'].'">';
                    //if (current_user_can('administrator')) {
                        $editorTab .= '<span id="description-status-indicator" class="status-indicator"
                                            onclick="event.stopPropagation(); document.getElementById(\'description-status-dropdown\').classList.toggle(\'show\')">'. $itemData['DescriptionStatusName'] .'</span>';
                    /*}
                    else {
                        $editorTab .= '<i id="description-status-indicator" class="fal fa-circle status-indicator"
                                            style="color: '.$itemData['DescriptionStatusColorCode'].'; background-color:'.$itemData['DescriptionStatusColorCode'].';">
                                        </i>';
                    }*/
                    $editorTab .= '<div id="description-status-dropdown" class="sub-status status-dropdown-content">';
                        foreach ($statusTypes as $statusType) {
                            if ($statusType['CompletionStatusId'] != 4 || current_user_can('administrator')) {
                                if ($itemData['DescriptionStatusId'] == $statusType['CompletionStatusId']) {
                                    $editorTab .= "<div class='status-dropdown-option status-dropdown-option-current'
                                                        onclick=\"changeStatus(".$_GET['item'].", null, '".$statusType['Name']."', 'DescriptionStatusId', ".$statusType['CompletionStatusId'].", '".$statusType['ColorCode']."', ".sizeof($progressData).", this)\">";
                                    $editorTab .= "<i class='fal fa-circle' style='color: transparent; background-image: -webkit-gradient(linear, left top, left bottom, color-stop(0, ".$statusType['ColorCode']."), color-stop(1, ".$statusType['ColorCodeGradient']."));'></i>".$statusType['Name']."</div>";
                                } else {
                                    $editorTab .= "<div class='status-dropdown-option'
                                                        onclick=\"changeStatus(".$_GET['item'].", null, '".$statusType['Name']."', 'DescriptionStatusId', ".$statusType['CompletionStatusId'].", '".$statusType['ColorCode']."', ".sizeof($progressData).", this)\">";
                                    $editorTab .= "<i class='fal fa-circle' style='color: transparent; background-image: -webkit-gradient(linear, left top, left bottom, color-stop(0, ".$statusType['ColorCode']."), color-stop(1, ".$statusType['ColorCodeGradient']."));'></i>".$statusType['Name']."</div>";
                                }
                            }
                        }
                    $editorTab .= '</div>';
                $editorTab .= '</div>';
                $editorTab .= '</div>';
                    //
                $editorTab .= '</div>';
                $editorTab .= '<div style="clear: both;"></div>';
                    $editorTab .= "<div id=\"description-area\" class=\"description-save collapse show\">";
                        $editorTab .= "<div id=\"category-checkboxes\" class=\"login-required\">";
                            foreach ($categories as $category) {
                                $checked = "";
                                if ($itemData['Properties'] != null) {
                                    foreach ($itemData['Properties'] as $itemProperty) {
                                        if ($itemProperty['PropertyId'] == $category['PropertyId']) {
                                            $checked = "checked";
                                            break;
                                        }
                                    }
                                }
                                $editorTab .= '<label class="square-checkbox-container">';
                                    $editorTab .= $category['PropertyValue'];
                                    $editorTab .= '<input class="category-checkbox" id="type-'.$category['PropertyValue'].'-checkbox" type="checkbox" '.$checked.'
                                                        name="'.$category['PropertyValue'].'"value="'.$category['PropertyId'].'"
                                                        onClick="addItemProperty('.$_GET['item'].', '.get_current_user_id().', \'category\', \''.$statusTypes[1]['ColorCode'].'\', '.sizeof($progressData).', \''.$category['PropertyValue'].'\', this)" />';
                                    $editorTab .= '<span  class="theme-color-background item-checkmark checkmark"></span>';
                                $editorTab .= '</label>';
                            }
                            $editorTab .= '<div style="clear: both;"></div>';
                        $editorTab .= '</div>';

                        $editorTab .= '<textarea id="item-page-description-text" class="login-required" name="description" rows="4">';
                            if ($itemData['Description'] != null) {
                                $editorTab .= htmlspecialchars($itemData['Description'], ENT_QUOTES, 'UTF-8');
                            }
                        $editorTab .= '</textarea>';

                        $editorTab .= '<div id= "description-language-selector" class="language-selector-background language-selector login-required">';
                            $editorTab .= '<select>';
                                if ($itemData['DescriptionLanguage'] == null) {
                                    $editorTab .= '<option value="" disabled selected hidden>';
                                        $editorTab .= 'Language of the Description';
                                    $editorTab .= '</option>';
                                    foreach ($languages as $language) {
                                        $editorTab .= '<option value="'.$language['LanguageId'].'">';
                                            $editorTab .= $language['Name']." (".$language['NameEnglish'].")";
                                        $editorTab .= '</option>';
                                    }
                                }
                                else {
                                    foreach ($languages as $language) {
                                        if ($itemData['DescriptionLanguage'] == $language['LanguageId']) {
                                            $editorTab .= '<option value="'.$language['LanguageId'].'" selected>';
                                                $editorTab .= $language['Name'];
                                            $editorTab .= '</option>';
                                        }
                                        else {
                                            $editorTab .= '<option value="'.$language['LanguageId'].'">';
                                                $editorTab .= $language['Name'];
                                            $editorTab .= '</option>';
                                        }
                                    }
                                }
                            $editorTab .= '</select>';
                        $editorTab .= '</div>';
                        $editorTab .= '<div>';
                            $editorTab .= "<button disabled class='language-tooltip' id='description-update-button' style='float: right;'
                                                onClick='updateItemDescription(".$itemData['ItemId'].", ".get_current_user_id().", \"".$statusTypes[1]['ColorCode']."\", ".sizeof($progressData).")'>";
                                $editorTab .= "SAVE"; //save description
                                $editorTab .= "<span class='language-tooltip-text'>Please select a language</span>";
                            $editorTab .= "</button>";
                            $editorTab .= '<div id="item-description-spinner-container" class="spinner-container spinner-container-right">';
                                $editorTab .= '<div class="spinner"></div>';
                            $editorTab .= "</div>";
                            $editorTab .= "<div style='clear:both'></div>";
                        $editorTab .= '</div>';
                        $editorTab .= "<div style='clear:both'></div>";
                        $editorTab .= "<span id='description-update-message'></span>";
                    $editorTab .= '</div>';
                $editorTab .= '</div>';
            // Transcription History
            $editorTab .= '<div id="tr-history" class="item-page-section">';
                $editorTab .= '<div class="item-page-section-headline-container collapse-headline item-page-section-collapse-headline collapse-controller" data-toggle="collapse" href="#transcription-history"
                                            onClick="jQuery(this).find(\'.collapse-icon\').toggleClass(\'fa-caret-circle-down\')
                                            jQuery(this).find(\'.collapse-icon\').toggleClass(\'fa-caret-circle-up\')">';
                    $editorTab .= '<h4 id="transcription-history-collapse-heading" class="theme-color item-page-section-headline">';
                        $editorTab .= "TRANSCRIPTION HISTORY";
                    $editorTab .= '</h4>';
                    $editorTab .= '<i class="far fa-caret-circle-down collapse-icon theme-color" style="font-size: 17px; float:left; margin-right: 8px; margin-top: 9px;"></i>';
                $editorTab .= '</div>';
                $editorTab .= '<div style="clear: both;"></div>';
                $editorTab .= "<div id=\"transcription-history\" class=\"collapse\">";

		$user = get_userdata($currentTranscription['WP_UserId']);
                $editorTab .= '<div class="transcription-toggle" data-toggle="collapse" data-target="#transcription-0">';
                   $editorTab .='<i class="fas fa-calendar-day" style= "margin-right: 6px;"></i>';
                   $date = strtotime($currentTranscription["Timestamp"]);
                   $editorTab .= '<span class="day-n-time">';
                        $editorTab .= $currentTranscription["Timestamp"];
                   $editorTab .= '</span>';
                   $editorTab .= '<i class="fas fa-user-alt" style="margin: 0 6px;"></i>';
                   $editorTab .= '<span class="day-n-time">';
                        $editorTab .= '<a target=\"_blank\" href="'.network_home_url().'profile/'.$user->data->user_nicename.'">';
                            $editorTab .= $user->data->user_nicename;
                        $editorTab .= '</a>';
                    $editorTab .= '</span>';
                    $editorTab .= '<i class="fas fa-angle-down" style= "float:right;"></i>';
                $editorTab .= '</div>';
		$editorTab .= '<div id="transcription-0" class="collapse transcription-history-collapse-content">';
                    $editorTab .= '<p>';
                        $editorTab .= $currentTranscription['TextNoTags'];
                    $editorTab .= '</p>';
                $editorTab .= '</div>';

                $i = 1;
                foreach ($transcriptionList as $transcription) {
                    $user = get_userdata($transcription['WP_UserId']);
                    $editorTab .= '<div class="transcription-toggle" data-toggle="collapse" data-target="#transcription-'.$i.'">';
                        $editorTab .='<i class="fas fa-calendar-day" style= "margin-right: 6px;"></i>';
                        $date = strtotime($transcription["Timestamp"]);
                        $editorTab .= '<span class="day-n-time">';
                            $editorTab .= $transcription["Timestamp"];
                        $editorTab .= '</span>';
                        $editorTab .= '<i class="fas fa-user-alt" style="margin: 0 6px;"></i>';
                        $editorTab .= '<span class="day-n-time">';
                            $editorTab .= '<a target=\"_blank\" href="'.network_home_url().'profile/'.$user->data->user_nicename.'">';
                                $editorTab .= $user->data->user_nicename;
                            $editorTab .= '</a>';
                        $editorTab .= '</span>';
                        $editorTab .= '<i class="fas fa-angle-down" style= "float:right;"></i>';
                    $editorTab .= '</div>';

                    $editorTab .= '<div id="transcription-'.$i.'" class="collapse transcription-history-collapse-content">';
                        $editorTab .= '<p>';
                            $editorTab .= $transcription['TextNoTags'];
                        $editorTab .= '</p>';
                        $editorTab .= "<input class='transcription-comparison-button theme-color-background' type='button'
                                            onClick='compareTranscription(".htmlentities(json_encode($transcriptionList[$i]['TextNoTags']), ENT_QUOTES)."
                                                        , ".htmlentities(json_encode($currentTranscription['TextNoTags']), ENT_QUOTES).",".$i.")'
                                            value='Compare to current transcription'>";
                        $editorTab .= '<div id="transcription-comparison-output-'.$i.'" class="transcription-comparison-output"></div>';
                    $editorTab .= '</div>';
                    $i++;
                }
                $editorTab .= '</div>';
            $editorTab .= '</div>';
        // Image settings tab
        $imageSettingsTab = "";
            $imageSettingsTab .= "<p class='theme-color item-page-section-headline'>ADVANCED IMAGE SETTINGS</p>";
        // Info tab
        $infoTab = "";
        $infoTab .= '<div class="item-page-section additional-info-bottom">';
            $infoTab .= '<div id="info-collapse-headline-container" class="item-page-section-headline-container collapse-headline collapse-controller" data-toggle="collapse" href="#additional-information-area"
                onClick="">';
                $infoTab .= '<h4 id="info-collapse-heading" class="theme-color item-page-section-headline" title="Existing metadata to the item">';
                    $infoTab .= 'Additional Information';
                $infoTab .= '</h4>';
                $infoTab .= '<i class="fal fa-info-square theme-color" style="font-size: 17px; float:left;  margin-right: 8px; margin-top: 9.6px;"></i>';
            $infoTab .= '</div>';
            $infoTab .= '<div style="clear: both;"></div>';

            $infoTab .= '<div id="additional-information-area">';
                // $infoTab .= "<h4 class='theme-color item-page-section-headline'>";
                //     $infoTab .= "Title: ".$itemData['Title'];
                // $infoTab .= "</h4>";

                $fields = array();
                foreach ($fieldMappings as $fieldMapping) {
                    $fields[$fieldMapping['Name']] = $fieldMapping['DisplayName'];
                }
                foreach ($itemData as $key => $value) {
                    if (substr($key, 0, 5) == "Story") {
                        $key = substr($key, 5);
                        if ($fields[$key] != null && $fields[$key] != "") {
                            $infoTab .= "<p class='item-page-property'>";
                                $infoTab .= "<span class='item-page-property-key' style='font-weight:bold;'>";
                                    $infoTab .= $fields[$key].": ";
                                $infoTab .= "</span>";
                                $infoTab .= "<span class='item-page-property-value'>";
                                $valueList = explode(" || ", $value);
                                $valueList = array_unique($valueList);
                                $i = 0;
                                foreach ($valueList as $singleValue) {
                                    if ($singleValue != "") {
                                        if ($i == 0) {
                                            if (filter_var($singleValue, FILTER_VALIDATE_URL)) {
                                                $infoTab .= "<a target=\"_blank\" href=\"".$singleValue."\">".$singleValue."</a>";
                                            }
                                            else {
                                                $infoTab .= $singleValue;
                                            }
                                        }
                                        else {
                                            if (filter_var($singleValue, FILTER_VALIDATE_URL)) {
                                                $infoTab .= "</br>";
                                                $infoTab .= "<a target=\"_blank\" href=\"".$singleValue."\">".$singleValue."</a>";
                                            }
                                            else {
                                                $infoTab .= "</br>";
                                                $infoTab .= $singleValue;
                                            }
                                        }
                                    }
                                    $i += 1;
                                }
                                $infoTab .= "</span></br>";
                            $infoTab .= "</p>";
                        }
                    }
                }
                $location = "";
                if ($itemData['StoryPlaceName'] != null && $itemData['StoryPlaceName'] != "") {
                    $location .= $itemData['StoryPlaceName'];
                }
                if ($itemData['StoryPlaceLatitude'] != null && $itemData['StoryPlaceLatitude'] != "" && $itemData['StoryPlaceLongitude'] != null && $itemData['StoryPlaceLongitude'] != "") {
                    $location .= " (".$itemData['StoryPlaceLatitude'].", ".$itemData['StoryPlaceLongitude'].")";
                }
                if ($location != "") {
                    $infoTab .= "<p class='item-page-property'>";
                        $infoTab .= "<span class='item-page-property-key' style='font-weight:bold;'>";
                            $infoTab .= "Location: ";
                        $infoTab .= "</span>";
                        $infoTab .= "<span class='item-page-property-value'>";
                            $infoTab .= $location;
                        $infoTab .= "</span></br>";
                    $infoTab .= "</p>";
                }
            $infoTab .= "</div>";
        $infoTab .= "</div>";
        // Tagging tab  // Splitted to mapTab and taggingTab
        $mapTab = "";
        // Location section
	    $mapTab .= "<div id='full-view-map' style='height:inherit;'>";
            $mapTab .= "<i class='far fa-map map-placeholder'></i>";
	    $mapTab .= "</div>";
	    $mapTab .= "<script src='https://api.mapbox.com/mapbox-gl-js/plugins/mapbox-gl-geocoder/v4.4.1/mapbox-gl-geocoder.min.js'></script>
						<link rel='stylesheet' href='https://api.mapbox.com/mapbox-gl-js/plugins/mapbox-gl-geocoder/v4.4.1/mapbox-gl-geocoder.css' type='text/css' />";

       $mapTab .= "<div id='location-section' class='item-page-section' style='display:none;'>";
            $mapTab .= "<div id='location-hide' class='item-page-section-headline-container login-required'>";
                $mapTab .= "<i class='fal fa-map-marker-alt theme-color' style='padding-right: 3px; font-size: 17px; margin-right:8px;'></i>";
                $mapTab .= "<div id='location-position' class='theme-color item-page-section-headline collapse-headline' title='Click to add a location'>";
                    $mapTab .= "Locations";
                    $mapTab .= '<i class="fas fa-plus-circle" style="margin-left:5px; font-size:15px; position: absolute; top: 14.5px;"></i>';
             //   $mapTab .= "</div>";
                //status-changer
               // $mapTab .= "<div class='item-page-section-headline-right-site'>";
                    $mapTab .= '<div id="location-status-changer" class="status-changer section-status-changer login-required" style="background-color:'.$itemData['LocationStatusColorCode'].';">';
                        //if (current_user_can('administrator')) {
                            $mapTab .= '<span id="location-status-indicator" class="status-indicator"
                                                onclick="event.stopPropagation(); document.getElementById(\'location-status-dropdown\').classList.toggle(\'show\')">'.$itemData['LocationStatusName'].'</span>';
                        /*}
                        else {
                            $taggingTab .= '<i id="location-status-indicator" class="fal fa-circle status-indicator"
                                                style="color: '.$itemData['LocationStatusColorCode'].'; background-color:'.$itemData['LocationStatusColorCode'].';"></i>';
                        }*/
                        $mapTab .= '<div id="location-status-dropdown" class="sub-status status-dropdown-content">';
                            foreach ($statusTypes as $statusType) {
                                if ($statusType['CompletionStatusId'] != 4 || current_user_can('administrator')) {
                                    if ($itemData['LocationStatusId'] == $statusType['CompletionStatusId']) {
                                        $mapTab .= "<div class='status-dropdown-option status-dropdown-option-current'
                                                            onclick=\"changeStatus(".$_GET['item'].", null, '".$statusType['Name']."', 'LocationStatusId', ".$statusType['CompletionStatusId'].", '".$statusType['ColorCode']."', ".sizeof($progressData).", this)\">";
                                        $mapTab .= "<i class='fal fa-circle' style='color: transparent; background-image: -webkit-gradient(linear, left top, left bottom, color-stop(0, ".$statusType['ColorCode']."), color-stop(1, ".$statusType['ColorCodeGradient']."));'></i>".$statusType['Name']."</div>";
                                    } else {
                                        $mapTab .= "<div class='status-dropdown-option'
                                                            onclick=\"changeStatus(".$_GET['item'].", null, '".$statusType['Name']."', 'LocationStatusId', ".$statusType['CompletionStatusId'].", '".$statusType['ColorCode']."', ".sizeof($progressData).", this)\">";
                                        $mapTab .= "<i class='fal fa-circle' style='color: transparent;background-image: -webkit-gradient(linear, left top, left bottom, color-stop(0, ".$statusType['ColorCode']."), color-stop(1, ".$statusType['ColorCodeGradient']."));'></i>".$statusType['Name']."</div>";
                                    }
                                }
                            }
                        $mapTab .= '</div>';
                    $mapTab .= '</div>';
               // $mapTab .= '</div>';
            $mapTab .= "</div>";
            $mapTab .= '<div id="location-input-section" style="display:none;">';

                $mapTab .= '<div class="location-input-section-second">';
                    $mapTab .= '<div class="location-input-name-container location-input-container">';
                        $mapTab .= '<span class="required-field">*</span>';
                        $mapTab .= '<br/>';
                        //$taggingTab .= '<input type="text" name="" placeholder="">';
                    $mapTab .= '</div>';
                $mapTab .= '</div>';

                $mapTab .= '<div class="location-input-section-top">';
                    $mapTab .= '<div id="location-name-display" style="margin-right: 16px;" class="location-display location-name-container location-input-container">';
                        $mapTab .= '<label>Location Name:</label>';
                        $mapTab .=    '<span class="required-field">*</span>';
                        $mapTab .=    '<br/>';
                        $mapTab .=    '<input type="text" name="" placeholder="e.g.: Berlin">';
                    $mapTab .= '</div>';
                    $mapTab .= '<div class="location-display location-input-coordinates-container location-input-container">';
                        $mapTab .=    '<label>Coordinates: </label>';
                        $mapTab .=    '<span class="required-field">*</span>';
                        $mapTab .=    '<br/>';
                        $mapTab .=    '<input type="text" name="" placeholder="e.g.: 10.0123, 15.2345">';
                    $mapTab .= '</div>';
                    $mapTab .= '<div style="clear:both;"></div>';
                $mapTab .= '</div>';

                $mapTab .= '<div class="location-input-description-container location-input-container">';
                    $mapTab .= '<label>Description:<i class="fas fa-question-circle" style="font-size:16px; cursor:pointer; margin-left:4px;" title="Add more information to this location, e.g. the building name, or its significance to the item"></i></label><br/>';
                    $mapTab .= '<textarea rows= "2" style="resize:none;" class="gsearch-form" type="text" id="ldsc" placeholder="" name=""></textarea>';
                $mapTab .= '</div>';

                $mapTab .= '<div id="location-input-geonames-search-container" class="location-input-container location-search-container">';
                    $mapTab .= '<label>WikiData Reference:
                    <i class="fas fa-question-circle" style="font-size:16px; cursor:pointer; margin-left:4px;" title="Identify this location by searching its name or code on Wikidata"></i></label><br/>';
                    $mapTab .= '<input type="text" id="lgns" placeholder="" name="">';
                    //$taggingTab .= '<a id="geonames-search-button" href="">';
                        //$taggingTab .= '<i class="far fa-search"></i>';
                    //$taggingTab .= '</a>';
                $mapTab .= '</div>';

                $mapTab .= "<div class='form-buttons-right'>";
                    $mapTab .= "<button class='item-page-save-button edit-data-save-right theme-color-background'
                                        onClick='saveItemLocation(".$itemData['ItemId'].", ".get_current_user_id()."
                                                , \"".$statusTypes[1]['ColorCode']."\", ".sizeof($progressData).")'>";
                        $mapTab .= "SAVE";
                    $mapTab .= "</button>";
                    $mapTab .= '<div id="item-location-spinner-container" class="spinner-container spinner-container-right">';
                        $mapTab .= '<div class="spinner"></div>';
                    $mapTab .= "</div>";
                    $mapTab .= "<div style='clear:both;'></div>";
                $mapTab .= "</div>";
                $mapTab .= "<div style='clear:both;'></div>";
            $mapTab .=    "</div>";

           $mapTab .= '<div id="item-location-list" class="item-data-output-list">';
           $mapTab .= '<ul>';
               foreach ($itemData['Places'] as $place) {
                   if ($place['Comment'] != "NULL") {
                       $comment = $place['Comment'];
                   }
                   else {
                       $comment = "";
                   }
                    $mapTab .= '<li id="location-'.$place['PlaceId'].'">';
                        $mapTab .= '<div class="item-data-output-element-header collapse-controller" data-toggle="collapse" href="#location-data-output-'.$place['PlaceId'].'">';
                            $mapTab .= '<h6>';
                               $mapTab .= $place['Name'];
                            $mapTab .= '</h6>';
                            $mapTab .= '<i class="fas fa-angle-down" style= "float:right;"></i>';
                            $mapTab .= '<div style="clear:both;"></div>';
                        $mapTab .= '</div>';

                    $mapTab .= '<div id="location-data-output-'.$place['PlaceId'].'" class="collapse">';
                        $mapTab .= '<div id="location-data-output-display-'.$place['PlaceId'].'" class="location-data-output-content">';
                            $mapTab .= '<span>';
                                $mapTab .= 'Description: ';
                                $mapTab .= $comment;
                            $mapTab .= '</span></br>';
                            $mapTab .= '<span>';
                                $mapTab .= 'Wikidata: ';
                                $mapTab .= '<a href="http://www.wikidata.org/wiki/'.$place['WikidataId'].'" style="text-decoration: none;" target="_blank">'.$place['WikidataName'].', '.$place['WikidataId'].'</a>';
                        $mapTab .= '</span></br>';
                        $mapTab .= '<div style="display:flex;"><span style="width:86%;"></span>';
                            $mapTab .= '<span style="width:14%;">';
                            $mapTab .= '<i class="login-required edit-item-data-icon fas fa-pencil theme-color-hover login-required"
                                                 onClick="openLocationEdit('.$place['PlaceId'].')"></i>';
                            $mapTab .= '<i class="login-required edit-item-data-icon fas fa-trash-alt theme-color-hover login-required"
                                                 onClick="deleteItemData(\'places\', '.$place['PlaceId'].', '.$_GET['item'].', \'place\', '.get_current_user_id().')"></i>';
                        $mapTab .= '</span></div>';
                        $mapTab .= '</div>';

                        $mapTab .= '<div id="location-data-edit-'.$place['PlaceId'].'" class="location-data-edit-container">';
                            $mapTab .= '<div class="location-input-section-top">';
                                $mapTab .= '<div class="location-input-name-container location-input-container">';
                                    $mapTab .= '<label>Location Name:</label><br/>';
                                    $mapTab .= '<input type="text" value="'.$place['Name'].'" name="" placeholder="">';
                                $mapTab .= '</div>';
                                $mapTab .= '<div class="location-input-coordinates-container location-input-container">';
                                    $mapTab .=    '<label>Coordinates: </label>';
                                    $mapTab .=    '<span class="required-field">*</span>';
                                    $mapTab .=    '<br/>';
                                    $mapTab .=    '<input type="text" value="'.htmlspecialchars($place['Latitude'], ENT_QUOTES, 'UTF-8').','.htmlspecialchars($place['Longitude'], ENT_QUOTES, 'UTF-8').'" name="" placeholder="">';
                                $mapTab .= '</div>';
                                $mapTab .= "<div style='clear:both;'></div>";
                            $mapTab .= '</div>';

                            $mapTab .= '<div class="location-input-description-container location-input-container">';
                                $mapTab .= '<label>Description:<i class="fas fa-question-circle" style="font-size:16px; cursor:pointer; margin-left:4px;" title="Add more information to this location, e.g. the building name, or its significance to the item"></i></label><br/>';
                                $mapTab .= '<textarea rows= "2" style="resize:none;" class="gsearch-form" type="text" id="ldsc">'.htmlspecialchars($comment, ENT_QUOTES, 'UTF-8').'</textarea>';
                            $mapTab .= '</div>';

                            $mapTab .= '<div class="location-input-geonames-container location-input-container location-search-container">';
                                $mapTab .= '<label>WikiData:</label><br/>';
                                if ($place['WikidataName'] != "NULL" && $place['WikidataId'] != "NULL") {
                                    $mapTab .= '<input type="text" placeholder="" name="" value="'.htmlspecialchars($place['WikidataId'], ENT_QUOTES, 'UTF-8').'; '.htmlspecialchars($place['WikidataName'], ENT_QUOTES, 'UTF-8').'">';
                                }
                                else {
                                    $mapTab .= '<input type="text" placeholder="" name="">';
                                }
                                //$taggingTab .= '<a id="geonames-search-button" href="">';
                                //    $taggingTab .= '<i class="far fa-search"></i>';
                                //$taggingTab .= '</a>';
                            $mapTab .= '</div>';

                            $mapTab .= "<div class='form-buttons-right'>";
                                $mapTab .= "<button class='item-page-save-button theme-color-background edit-data-save-right'
                                                    onClick='editItemLocation(".$place['PlaceId'].", ".$_GET['item'].", ".get_current_user_id().")'>";
                                    $mapTab .= "SAVE";
                                $mapTab .= "</button>";

                                $mapTab .= "<button class='theme-color-background edit-data-cancel-right' onClick='openLocationEdit(".$place['PlaceId'].")'>";
                                    $mapTab .= "CANCEL";
                                $mapTab .= "</button>";
                                $mapTab .= '<div id="item-location-'.$place['PlaceId'].'-spinner-container" class="spinner-container spinner-container-right">';
                                    $mapTab .= '<div class="spinner"></div>';
                                $mapTab .= "</div>";
                                $mapTab .= "<div style='clear:both;'></div>";
                            $mapTab .= "</div>";
                            $mapTab .= "<div style='clear:both;'></div>";
                        $mapTab .=    "</div>";
                    $mapTab .=    "</div>";
                    $mapTab .= '</li>';
                    }
                $mapTab .= '</ul>';
            $mapTab .= '</div>';
        $mapTab .= '</div>';
        //   $taggingTab .= '<hr>';

            //Tagging section
            $taggingTab = "";
            $taggingTab .= "<div id='tagging-section' class='item-page-section' hidden>";
                $taggingTab .= "<div class='item-page-section-headline-container'>";
                    $taggingTab .= "<i class='fal fa-tag theme-color' style='font-size: 17px; margin-right:8px;'></i><div class='theme-color item-page-section-headline' style='display:inline-block;'>";
                        $taggingTab .= "Enrichments";
                    $taggingTab .= "</div>";
                        //status-changer
                    $taggingTab .= "<div class='item-page-section-headline-right-site' style='display:inline-block;'>";
                        $taggingTab .= '<div id="tagging-status-changer" class="status-changer section-status-changer login-required" style="background-color:'.$itemData['TaggingStatusColorCode'].';">';
                            //if (current_user_can('administrator')) {
                                $taggingTab .= '<span id="tagging-status-indicator" class="status-indicator"
                                                    onclick="event.stopPropagation(); document.getElementById(\'tagging-status-dropdown\').classList.toggle(\'show\')">'.$itemData['TaggingStatusName'].'</span>';
                            /*}
                            else {
                                $taggingTab .= '<i id="tagging-status-indicator" class="fal fa-circle status-indicator"
                                                    style="color: '.$itemData['TaggingStatusColorCode'].'; background-color:'.$itemData['TaggingStatusColorCode'].';"></i>';
                            }*/
                            $taggingTab .= '<div id="tagging-status-dropdown" class="sub-status status-dropdown-content">';
                                foreach ($statusTypes as $statusType) {
                                    if ($statusType['CompletionStatusId'] != 4 || current_user_can('administrator')) {
                                        if ($itemData['TaggingStatusId'] == $statusType['CompletionStatusId']) {
                                            $taggingTab .= "<div class='status-dropdown-option status-dropdown-option-current'
                                                                onclick=\"changeStatus(".$_GET['item'].", null, '".$statusType['Name']."', 'TaggingtatusId', ".$statusType['CompletionStatusId'].", '".$statusType['ColorCode']."', ".sizeof($progressData).", this)\">";
                                            $taggingTab .= "<i class='fal fa-circle' style='color: transparent; background-image: -webkit-gradient(linear, left top, left bottom, color-stop(0, ".$statusType['ColorCode']."), color-stop(1, ".$statusType['ColorCodeGradient']."));'></i>".$statusType['Name']."</div>";
                                        } else {
                                            $taggingTab .= "<div class='status-dropdown-option'
                                                                onclick=\"changeStatus(".$_GET['item'].", null, '".$statusType['Name']."', 'TaggingStatusId', ".$statusType['CompletionStatusId'].", '".$statusType['ColorCode']."', ".sizeof($progressData).", this)\">";
                                            $taggingTab .= "<i class='fal fa-circle' style='color: transparent;background-image: -webkit-gradient(linear, left top, left bottom, color-stop(0, ".$statusType['ColorCode']."), color-stop(1, ".$statusType['ColorCodeGradient']."));'></i>".$statusType['Name']."</div>";
                                        }
                                    }
                                }
                            $taggingTab .= '</div>';
                        $taggingTab .= '</div>';
                    $taggingTab .= '</div>';
                $taggingTab .= '</div>';

                $taggingTab .= '<div id="item-date-container">';
                    $taggingTab .= '<h6 class="theme-color item-data-input-headline login-required">';
                        $taggingTab .= 'Document date';
                    $taggingTab .= '</h6>';
                        $taggingTab .= '<div class="item-date-inner-container">';
                        $taggingTab .= '<label>';
                            $taggingTab .= 'Start Date';
                        $taggingTab .= '</label>';
                            if ($itemData['DateStartDisplay'] != null) {
                                $startTimestamp = strtotime($itemData['DateStart']);
                                $dateStart = date("d/m/Y", $startTimestamp);
                                $taggingTab .= '<div class="item-date-display-container">';
                                    $taggingTab .= '<span type="text" id="startdateDisplay" class="item-date-display">';
                                        $taggingTab .= $itemData['DateStartDisplay'];
                                    $taggingTab .= '</span>';
                                    $taggingTab .= '<i class="edit-item-date edit-item-data-icon fas fa-pencil theme-color-hover login-required"></i>';
                                $taggingTab .= '</div>';
                                $taggingTab .= '<div class="item-date-input-container" style="display:none">';
                                    $taggingTab .= '<input type="text" id="startdateentry" placeholder="dd/mm/yyyy" class="datepicker-input-field" value="'.$dateStart.'">';
                                $taggingTab .= '</div>';
                            }
                            else {
                                $taggingTab .= '<div class="item-date-display-container" style="display:none">';
                                    $taggingTab .= '<span type="text" id="startdateDisplay" class="item-date-display">';
                                    $taggingTab .= '</span>';
                                    $taggingTab .= '<i class="edit-item-date edit-item-data-icon fas fa-pencil theme-color-hover login-required"></i>';
                                $taggingTab .= '</div>';
                                $taggingTab .= '<div class="item-date-input-container">';
                                    $taggingTab .= '<input type="text" id="startdateentry" class="login-required datepicker-input-field" placeholder="dd/mm/yyyy">';
                                $taggingTab .= '</div>';
                            }
                        $taggingTab .= "</div>";
                        $taggingTab .= '<div class="item-date-inner-container">';
                            $taggingTab .= '<label>';
                                $taggingTab .= 'End Date';
                            $taggingTab .= '</label>';
                            if ($itemData['DateEndDisplay'] != null) {
                                $endTimestamp = strtotime($itemData['DateEnd']);
                                $dateEnd = date("d/m/Y", $endTimestamp);
                                $taggingTab .= '<div class="item-date-display-container">';
                                    $taggingTab .= '<span type="text" id="enddateDisplay" class="item-date-display">';
                                        $taggingTab .= $itemData['DateEndDisplay'];
                                    $taggingTab .= '</span>';
                                    $taggingTab .= '<i class="edit-item-date edit-item-data-icon fas fa-pencil theme-color-hover login-required"></i>';
                                $taggingTab .= '</div>';
                                $taggingTab .= '<div class="item-date-input-container" style="display:none">';
                                    $taggingTab .= '<input type="text" id="enddateentry" class="datepicker-input-field" placeholder="dd/mm/yyyy" value="'.$dateEnd.'">';
                                $taggingTab .= '</div>';
                            }
                            else {
                                $taggingTab .= '<div class="item-date-display-container" style="display:none">';
                                    $taggingTab .= '<span type="text" id="enddateDisplay" class="item-date-display">';
                                        $taggingTab .= $dateEnd;
                                    $taggingTab .= '</span>';
                                    $taggingTab .= '<i class="edit-item-date edit-item-data-icon fas fa-pencil theme-color-hover login-required"></i>';
                                $taggingTab .= '</div>';
                                $taggingTab .= '<div class="item-date-input-container">';
                                    $taggingTab .= '<input type="text" id="enddateentry" class="login-required datepicker-input-field" placeholder="dd/mm/yyyy">';
                                $taggingTab .= '</div>';
                            }
                        $taggingTab .= "</div>";
                        $taggingTab .= "<button class='item-page-save-button theme-color-background login-required' id='item-date-save-button'
                                            onClick='saveItemDate(".$itemData['ItemId'].", ".get_current_user_id()."
                                            , \"".$statusTypes[1]['ColorCode']."\", ".sizeof($progressData).")'>";
                            $taggingTab .= "SAVE DATE";
                        $taggingTab .= "</button>";
                        $taggingTab .= '<div id="item-date-spinner-container" class="spinner-container spinner-container-right">';
                            $taggingTab .= '<div class="spinner"></div>';
                        $taggingTab .= "</div>";
                        $taggingTab .= '<div style="clear:both;"></div>';
                $taggingTab .= '</div>';
             //   $taggingTab .= '<hr>';
                //add person metadata area
                $taggingTab .= '<div class="item-page-person-container">';
                    //add person collapse heading
                    $taggingTab .= '<div id="item-page-person-headline" class="collapse-headline collapse-controller theme-color" data-toggle="collapse" href="#person-input-container"
                                        onClick="
                                            jQuery(this).find(\'.collapse-icon\').toggleClass(\'fa-caret-circle-up\')
                                            jQuery(this).find(\'.collapse-icon\').toggleClass(\'fa-caret-circle-down\')">';
                        $taggingTab .= '<h6 class="theme-color item-data-input-headline login-required" title="Click to tag a person">';
                            $taggingTab .= 'People';
                            $taggingTab .= '<i class="fas fa-plus-circle"></i>';
                        $taggingTab .= '</h6>';
                    $taggingTab .= '</div>';
                    // add person form area
                    $taggingTab .= '<div class="collapse person-item-data-container" id="person-input-container">';
                        $taggingTab .= '<div class="person-input-names-container">';
                            $taggingTab .= '<input type="text" id="person-firstName-input" class="input-response person-input-field" name="" placeholder="First Name">';
                            $taggingTab .= '<input type="text" id="person-lastName-input" class="input-response person-input-field" name="" placeholder="Last Name">';
                        $taggingTab .= '</div>';

                        $taggingTab .= '<div class="person-description-input">';
                            $taggingTab .= '<label>Description:<i class="fas fa-question-circle" style="font-size:16px; cursor:pointer; margin-left:4px;" title="Add more information to this person, e.g. their profession, or their significance to the item"></i></label><br/>';
                            $taggingTab .= '<input id="person-description-input-field" type="text" class="input-response person-input-field">';
                        $taggingTab .= '</div>';

                        $taggingTab .= '<div class="person-location-birth-inputs">';
                            $taggingTab .= '<input type="text" id="person-birthPlace-input"   class="input-response person-input-field" name="" placeholder="Birth Location">';
                            $taggingTab .= '<span class="input-response"><input type="text" id="person-birthDate-input" class="date-input-response person-input-field datepicker-input-field" name="" placeholder="Birth: dd/mm/yyyy"></span>';
                        $taggingTab .= '</div>';

                        $taggingTab .= '<div class="person-location-death-inputs">';
                            $taggingTab .= '<input type="text" id="person-deathPlace-input" class="input-response person-input-field" name="" placeholder="Death Location">';
                            $taggingTab .= '<span class="input-response"><input type="text" id="person-deathDate-input" class="date-input-response person-input-field datepicker-input-field" name="" placeholder="Death: dd/mm/yyyy"></span>';
                        $taggingTab .= '</div>';

                        $taggingTab .= '<div class="form-buttons-right">';
                            $taggingTab .= "<button id='save-personinfo-button' class='theme-color-background edit-data-save-right' id='person-save-button'
                                                onClick='savePerson(".$itemData['ItemId'].", ".get_current_user_id()."
                                                        , \"".$statusTypes[1]['ColorCode']."\", ".sizeof($progressData).")'>";
                                $taggingTab .= "SAVE";
                            $taggingTab .= "</button>";
                            $taggingTab .= '<div id="item-person-spinner-container" class="spinner-container spinner-container-left">';
                                $taggingTab .= '<div class="spinner"></div>';
                            $taggingTab .= "</div>";
                            $taggingTab .= '<div style="clear:both;"></div>';
                        $taggingTab .= '</div>';
                        $taggingTab .= '<div style="clear:both;"></div>';
                    $taggingTab .= '</div>';

                    $taggingTab .= '<div id="item-person-list" class="item-data-output-list">';
                    $taggingTab .= '<ul>';
                        foreach ($itemData['Persons'] as $person) {
                            if ($person['FirstName'] != "NULL") {
                                $firstName = $person['FirstName'];
                            }
                            else {
                                $firstName = "";
                            }
                            if ($person['LastName'] != "NULL") {
                                $lastName = $person['LastName'];
                            }
                            else {
                                $lastName = "";
                            }
                            if ($person['BirthPlace'] != "NULL") {
                                $birthPlace = $person['BirthPlace'];
                            }
                            else {
                                $birthPlace = "";
                            }
                            if ($person['BirthDate'] != "NULL") {
                                $birthTimestamp = strtotime($person['BirthDate']);
                                $birthDate = date("d/m/Y", $birthTimestamp);
                            }
                            else {
                                $birthDate = "";
                            }
                            if ($person['DeathPlace'] != "NULL") {
                                $deathPlace = $person['DeathPlace'];
                            }
                            else {
                                $deathPlace = "";
                            }
                            if ($person['DeathDate'] != "NULL") {
                                $deathTimestamp = strtotime($person['DeathDate']);
                                $deathDate = date("d/m/Y", $deathTimestamp);
                            }
                            else {
                                $deathDate = "";
                            }
                            if ($person['Description'] != "NULL") {
                                $description = $person['Description'];
                            }
                            else {
                                $description = "";
                            }
                            $personHeadline = "";
                               $personHeadline = '<span class="item-name-header">';
                                $personHeadline .= $firstName . ' ' . $lastName . ' ';
                               $personHeadline .= '</span>';
                                if ($birthDate != "") {
                                    if ($deathDate != "") {
                                        $personHeadline .= '<span class="item-name-header">(' . $birthDate . ' - ' . $deathDate . ')</span>';
                                    }
                                    else {
                                        $personHeadline .= '<span class="item-name-header">(Birth: ' . $birthDate . ')</span>';
                                    }
                                }
                                else {
                                    if ($deathDate != "") {
                                        $personHeadline .= '<span class="item-name-header">(Death: ' . $deathDate . ')</span>';
                                    }
                                    else {
                                        if ($description != "") {
                                            $personHeadline .= "<span class='person-output-description-headline'>".$description."</span>";
                                        }
                                    }
                                }
                            $taggingTab .= '<li id="person-'.$person['PersonId'].'">';
                                $taggingTab .= '<div class="item-data-output-element-header collapse-controller" data-toggle="collapse" href="#person-data-output-'.$person['PersonId'].'">';
                                    $taggingTab .= '<h6 class="person-data-ouput-headline">';
                                        $taggingTab .= '<div class="item-name-header person-dots">';
                                            $taggingTab .= $personHeadline;
                                        $taggingTab .= '</div>';
                                    $taggingTab .= '</h6>';
                                    //$taggingTab .= '<div class="person-dots" style="width=10px; white-space: nowrap; text-overflow:ellipsis;"></span>';
                                    $taggingTab .= '<i class="fas fa-angle-down" style= "float:right;"></i>';
                                    $taggingTab .= '<div style="clear:both;"></div>';
                                $taggingTab .= '</div>';

                                $taggingTab .= '<div id="person-data-output-'.$person['PersonId'].'" class="collapse">';
                                    $taggingTab .= '<div id="person-data-output-display-'.$person['PersonId'].'" class="person-data-output-content">';
                                        $taggingTab .= '<div>';
                                            $taggingTab .= '<table border="0">';
                                                $taggingTab .= '<tr>';
                                                    $taggingTab .= '<th></th>';
                                                    $taggingTab .= '<th>Birth</th>';
                                                    $taggingTab .= '<th>Death</th>';
                                                $taggingTab .= '</tr>';
                                                $taggingTab .= '<tr>';
                                                    $taggingTab .= '<th>Date</th>';
                                                    $taggingTab .= '<td>';
                                                    $taggingTab .= $birthDate;
                                                    $taggingTab .= '</td>';
                                                    $taggingTab .= '<td>';
                                                    $taggingTab .= $deathDate;
                                                    $taggingTab .= '</td>';
                                                $taggingTab .= '</tr>';
                                                $taggingTab .= '<tr>';
                                                    $taggingTab .= '<th>Location</th>';
                                                    $taggingTab .= '<td>';
                                                    $taggingTab .= $birthPlace;
                                                    $taggingTab .= '</td>';
                                                    $taggingTab .= '<td>';
                                                    $taggingTab .= $deathPlace;
                                                    $taggingTab .= '</td>';
                                                $taggingTab .= '</tr>';
                                            $taggingTab .= '</table>';

                                        $taggingTab .= '</div>';
                                        $taggingTab .= '<div class="person-data-output-button">';
                                                $taggingTab .= '<span>';
                                                    $taggingTab .= 'Description: ';
                                                    $taggingTab .= $description;
                                                $taggingTab .= '</span>';
                                                $taggingTab .= '<i class="login-required edit-item-data-icon fas fa-pencil theme-color-hover"
                                                                    onClick="openPersonEdit('.$person['PersonId'].')"></i>';
                                                $taggingTab .= '<i class="login-required edit-item-data-icon fas fa-trash-alt theme-color-hover"
                                                                    onClick="deleteItemData(\'persons\', '.$person['PersonId'].', '.$_GET['item'].', \'person\', '.get_current_user_id().')"></i>';
                                        $taggingTab .= '</div>';
                                        $taggingTab .= '<div style="clear:both;"></div>';
                                    $taggingTab .= '</div>';

                                    $taggingTab .= '<div class="person-data-edit-container person-item-data-container" id="person-data-edit-'.$person['PersonId'].'">';
                                        $taggingTab .= '<div class="person-input-names-container">';
                                            if ($firstName != "") {
                                                $taggingTab .= '<input type="text" id="person-'.$person['PersonId'].'-firstName-edit" class="input-response person-input-field person-re-edit" placeholder="First Name" value="'.$firstName.'">';
                                            }
                                            else {
                                                $taggingTab .= '<input type="text" id="person-'.$person['PersonId'].'-firstName-edit" class="input-response person-input-field person-re-edit" placeholder="First Name">';
                                            }

                                            if ($lastName != "") {
                                                $taggingTab .= '<input type="text" id="person-'.$person['PersonId'].'-lastName-edit" class="input-response person-input-field person-re-edit" value="'.$lastName.'" placeholder="Last Name">';
                                            }
                                            else {
                                                $taggingTab .= '<input type="text" id="person-'.$person['PersonId'].'-lastName-edit" class="input-response person-input-field person-re-edit" placeholder="Last Name">';
                                            }
                                        $taggingTab .= '</div>';

                                        $taggingTab .= '<div class="person-description-input">';
                                            $taggingTab .= '<label>Description:<i class="fas fa-question-circle" style="font-size:16px; cursor:pointer; margin-left:4px;" title="Add more information to this person, e.g. their profession, or their significance to the item"></i></label><br/>';
                                            $taggingTab .= '<input type="text" id="person-'.$person['PersonId'].'-description-edit" class="input-response person-edit-field" value="'.$description.'">';
                                        $taggingTab .= '</div>';

                                        $taggingTab .= '<div class="person-location-birth-inputs">';
                                            if ($birthPlace != "") {
                                                $taggingTab .= '<input type="text" id="person-'.$person['PersonId'].'-birthPlace-edit"   class="input-response person-input-field person-re-edit" value="'.$birthPlace.'"  placeholder="Birth Location">';
                                            }
                                            else {
                                                $taggingTab .= '<input type="text" id="person-'.$person['PersonId'].'-birthPlace-edit"   class="input-response person-input-field person-re-edit" placeholder="Birth Location">';
                                            }

                                            if ($birthDate != "") {
                                                $taggingTab .= '<span class="input-response"><input type="text" id="person-'.$person['PersonId'].'-birthDate-edit" class="date-input-response person-input-field datepicker-input-field person-re-edit" value="'.$birthDate.'" placeholder="Birth: dd/mm/yyyy"></span>';
                                            }
                                            else {
                                                $taggingTab .= '<span class="input-response"><input type="text" id="person-'.$person['PersonId'].'-birthDate-edit" class="date-input-response person-input-field datepicker-input-field person-re-edit" placeholder="Birth: dd/mm/yyyy"></span>';
                                            }
                                        $taggingTab .= '</div>';

                                        $taggingTab .= '<div class="person-location-death-inputs">';
                                            if ($deathPlace != "") {
                                                $taggingTab .= '<input type="text" id="person-'.$person['PersonId'].'-deathPlace-edit"   class="input-response person-input-field person-re-edit" value="'.$deathPlace.'" placeholder="Death Location">';
                                            }
                                            else {
                                                $taggingTab .= '<input type="text" id="person-'.$person['PersonId'].'-deathPlace-edit"   class="input-response person-input-field person-re-edit" placeholder="Death Location">';
                                            }

                                            if ($deathDate != "") {
                                                $taggingTab .= '<span class="input-response"><input type="text" id="person-'.$person['PersonId'].'-deathDate-edit" class="date-input-response person-input-field datepicker-input-field person-re-edit" value="'.$deathDate.'" placeholder="Death: dd/mm/yyyy"></span>';
                                            }
                                            else {
                                                $taggingTab .= '<span class="input-response"><input type="text" id="person-'.$person['PersonId'].'-deathDate-edit" class="date-input-response person-input-field datepicker-input-field person-re-edit" placeholder="Death: dd/mm/yyyy"></span>';
                                            }
                                        $taggingTab .= '</div>';

                                        $taggingTab .= '<div class="form-buttons-right">';
                                            $taggingTab .= "<button class='edit-data-save-right theme-color-background'
                                                                    onClick='editPerson(".$person['PersonId'].", ".$_GET['item'].", ".get_current_user_id().")'>";
                                                $taggingTab .= "SAVE";
                                            $taggingTab .= "</button>";

                                            $taggingTab .= "<button class='theme-color-background edit-data-cancel-right' onClick='openPersonEdit(".$person['PersonId'].")'>";
                                                $taggingTab .= "CANCEL";
                                            $taggingTab .= "</button>";

                                            $taggingTab .= '<div id="item-person-'.$person['PersonId'].'-spinner-container" class="spinner-container spinner-container-left">';
                                                $taggingTab .= '<div class="spinner"></div>';
                                            $taggingTab .= "</div>";
                                            $taggingTab .= '<div style="clear:both;"></div>';
                                        $taggingTab .= '</div>';
                                        $taggingTab .= '<div style="clear:both;"></div>';
                                    $taggingTab .= '</div>';
                                $taggingTab .= '</div>';

                            $taggingTab .= '</li>';
                        }
                    $taggingTab .= '</ul>';
                $taggingTab .= '</div>';
            $taggingTab .= '</div>';
             //   $taggingTab .= '<hr>';
                //key word metadata area
                $taggingTab .= '<div id="item-page-keyword-container">';
                $taggingTab .= '<div id="item-page-person-headline" class="collapse-headline collapse-controller" data-toggle="collapse" href="#keyword-input-container">';
                $taggingTab .= '<h6 class="theme-color item-data-input-headline login-required" title="Click to add keywords">';
                        $taggingTab .= 'Keywords';
                        $taggingTab .= '<i class="fas fa-plus-circle"></i>';
                    $taggingTab .= '</h6>';
                $taggingTab .= '</div>';
                $taggingTab .= '<div id="keyword-input-container" class="collapse">';
                    $taggingTab .= '<input type="text" id="keyword-input" name="" placeholder="">';
                    $taggingTab .= "<button id='keyword-save-button' type='submit' class='theme-color-background'
                                        onClick='saveKeyword(".htmlspecialchars($itemData['ItemId'], ENT_QUOTES, 'UTF-8').", ".get_current_user_id()."
                                        , \"".$statusTypes[1]['ColorCode']."\", ".sizeof($progressData).")'>";
                        $taggingTab .= 'SAVE';
                    $taggingTab .= '</button>';
                    $taggingTab .= '<div id="item-keyword-spinner-container" class="spinner-container spinner-container-left">';
                        $taggingTab .= '<div class="spinner"></div>';
                    $taggingTab .= "</div>";
                    $taggingTab .= '<div style="clear: both;"></div>';
                $taggingTab .= '</div>';

                $taggingTab .= '<div id="item-keyword-list" class="item-data-output-listt">';
                $taggingTab .= '<ul>';
                    foreach ($itemData['Properties'] as $property) {
                        if ($property['PropertyType'] == "Keyword") {
                            $taggingTab .= '<li id="add-item-keyword" class="theme-color-background">';
                                        $taggingTab .= $property['PropertyValue'];
                                    $taggingTab .= '<i class="login-required delete-item-datas far fa-times"
                                                        onClick="deleteItemData(\'properties\', '.$property['PropertyId'].', '.$_GET['item'].', \'keyword\', '.get_current_user_id().')"></i>';
                                  $taggingTab .= '</li>';
                              }
                          }
                      $taggingTab .= '</ul>';
                  $taggingTab .= '</div>';
              $taggingTab .= '</div>';
        //      $taggingTab .= '<hr>';
                //other sources metadata area
                $taggingTab .= '<div id="item-page-link-container">';
                    //add source link collapse heading
                    $taggingTab .= '<div class= "collapse-headline collapse-controller" data-toggle="collapse" href="#link-input-container">';
                    $taggingTab .= '<h6 class="theme-color item-data-input-headline login-required" title="Click to add a link">';
                            $taggingTab .= 'Other Sources';
                            $taggingTab .= '<i class="fas fa-plus-circle"></i>';
                        $taggingTab .= '</h6>';
                    $taggingTab .= '</div>';
                    // add source link form area
                    $taggingTab .= '<div id="link-input-container" class="collapse">';
                            $taggingTab .= '<div>';
                                $taggingTab .= "<span>Link:</span><br/>";
                            $taggingTab .= '</div>';

                            $taggingTab .= '<div class="link-url-input">';
                                $taggingTab .= '<input type="url" name="" placeholder="Enter URL here">';
                            $taggingTab .= '</div>';

                            $taggingTab .= '<div class="link-description-input">';
                                $taggingTab .= '<label>Additional description:</label><br/>';
                                $taggingTab .= '<textarea rows= "3" type="text" placeholder="" name=""></textarea>';
                            $taggingTab .= '</div>';
                            $taggingTab .= "<div class='form-buttons-right'>";
                                $taggingTab .= "<button type='submit' class='theme-color-background edit-data-save-right' id='link-save-button'
                                                    onClick='saveLink(".$itemData['ItemId'].", ".get_current_user_id()."
                                                    , \"".$statusTypes[1]['ColorCode']."\", ".sizeof($progressData).")'>";
                                    $taggingTab .= "SAVE";
                                $taggingTab .= "</button>";
                                $taggingTab .= '<div id="item-link-spinner-container" class="spinner-container spinner-container-left">';
                                    $taggingTab .= '<div class="spinner"></div>';
                                $taggingTab .= "</div>";
                                $taggingTab .= '<div style="clear:both;"></div>';
                            $taggingTab .=    "</div>";
                            $taggingTab .= '<div style="clear:both;"></div>';
                    $taggingTab .=    "</div>";
                $taggingTab .= '<div id="item-link-list" class="item-data-output-list">';
                    $taggingTab .= '<ul>';
                        foreach ($itemData['Properties'] as $property) {
                            if ($property['PropertyDescription'] != "NULL") {
                                $description = $property['PropertyDescription'];
                            }
                            else {
                                $description = "";
                            }
                            if ($property['PropertyType'] == "Link") {
                                $taggingTab .= '<li id="link-'.$property['PropertyId'].'">';
                                    $taggingTab .= '<div id="link-data-output-'.$property['PropertyId'].'" class="">';
                                        $taggingTab .= '<div id="link-data-output-display-'.$property['PropertyId'].'" class="link-data-output-content">';
                                            $taggingTab .= '<div class="item-data-output-element-header">';
                                                $taggingTab .= '<a href="'.$property['PropertyValue'].'" target="_blank">';
                                                        $taggingTab .= $property['PropertyValue'];
                                                $taggingTab .= '</a>';
                                                $taggingTab .= '<i class="edit-item-data-icon fas fa-pencil theme-color-hover login-required"
                                                                onClick="openLinksourceEdit('.$property['PropertyId'].')"></i>';
                                                $taggingTab .= '<i class="edit-item-data-icon delete-item-data fas fa-trash-alt theme-color-hover login-required"
                                                                onClick="deleteItemData(\'Properties\', '.$property['PropertyId'].', '.$_GET['item'].', \'link\', '.get_current_user_id().')"></i>';
                                                $taggingTab .= '<div style="clear:both;"></div>';
                                            $taggingTab .= '</div>';
                                            $taggingTab .= '<div>';
                                                $taggingTab .= '<span>';
                                                    $taggingTab .= 'Description: ';
                                                    $taggingTab .= $description;
                                                $taggingTab .= '</span>';
                                            $taggingTab .= '</div>';
                                        $taggingTab .= '</div>';

                                        $taggingTab .= '<div class="link-data-edit-container" id="link-data-edit-'.$property['PropertyId'].'">';
                                            $taggingTab .= '<div>';
                                                $taggingTab .= "<span>Link:</span><br/>";
                                            $taggingTab .= '</div>';

                                            $taggingTab .= '<div id="link-'.$property['PropertyId'].'-url-input" class="link-url-input">';
                                                $taggingTab .= '<input type="url" value="'.htmlspecialchars($property['PropertyValue'], ENT_QUOTES, 'UTF-8').'" placeholder="Enter URL here">';
                                            $taggingTab .= '</div>';

                                            $taggingTab .= '<div id="link-'.$property['PropertyId'].'-description-input" class="link-description-input">';
                                                $taggingTab .= '<label>Additional description:</label><br/>';
                                                $taggingTab .= '<textarea rows= "3" type="text" placeholder="" name="">'.htmlspecialchars($description, ENT_QUOTES, 'UTF-8').'</textarea>';
                                            $taggingTab .= '</div>';
                                            $taggingTab .= "<div class='form-buttons-right'>";
                                                $taggingTab .= "<button class='theme-color-background edit-data-save-right'
                                                                        onClick='editLink(".$property['PropertyId'].", ".$_GET['item'].", ".get_current_user_id().")'>";
                                                    $taggingTab .= "SAVE";
                                                $taggingTab .= "</button>";

                                                $taggingTab .= "<button class='theme-color-background edit-data-cancel-right' onClick='openLinksourceEdit(".$property['PropertyId'].")'>";
                                                    $taggingTab .= "CANCEL";
                                                $taggingTab .= "</button>";

                                                $taggingTab .= '<div id="item-link-'.$property['PropertyId'].'-spinner-container" class="spinner-container spinner-container-left">';
                                                    $taggingTab .= '<div class="spinner"></div>';
                                                $taggingTab .= "</div>";
                                                $taggingTab .= '<div style="clear:both;"></div>';
                                            $taggingTab .= '</div>';
                                            $taggingTab .= '<div style="clear:both;"></div>';
                                        $taggingTab .= '</div>';
                                    $taggingTab .= '</div>';
                                $taggingTab .= '</li>';
                            }
                        }
                    $taggingTab .= '</ul>';
                $taggingTab .= '</div>';
            $taggingTab .= '</div>';
        $taggingTab .= '</div>';
    //    $taggingTab .= '<hr>';
        // Comment section
        $commentSection = "";
        $commentSection .= '<div class="item-page-section">';
            $commentSection .= '<div class="item-page-section-headline-container collapse-headline item-page-section-collapse-headline collapse-controller" data-toggle="collapse" href="#comments"
                    onClick="jQuery(this).find(\'.collapse-icon\').toggleClass(\'fa-caret-circle-down\')
                        jQuery(this).find(\'.collapse-icon\').toggleClass(\'fa-caret-circle-up\')"
                        >';
                $commentSection .= '<h4 id="comments-collapse-heading" class="theme-color item-page-section-headline">';
                    $commentSection .= "NOTES AND QUESTIONS";
                $commentSection .= '</h4>';
                $commentSection .= '<i class="far fa-caret-circle-down collapse-icon theme-color" style="font-size: 17px; float:left; margin-right: 8px; margin-top: 9px;"></i>';
            $commentSection .= '</div>';
            $commentSection .= '<div style="clear: both;"></div>';
            $commentSection .= "<div id=\"comments\" class=\"comments-area collapse\">";
                $commentSection .= "<div id=\"respond\" class=\"comment-respond\">";
                    $commentSection .= "<h3 id=\"reply-title\" class=\"comment-reply-title\">";
                        $commentSection .= "Leave a Note or Question about the Item";
                        $commentSection .= "<small><a rel=\"nofollow\" id=\"cancel-comment-reply-link\" href=\"/en/documents/id-19044/item-223349/#respond\" style=\"display:none;\">";
                            $commentSection .= "Cancel reply";
                        $commentSection .= "</a></small>";
                    $commentSection .= "</h3>";
                    $commentSection .= "<form action=\"https://transcribathon.com/wp-comments-post.php\" method=\"post\" id=\"commentform\" class=\"comment-form\">";
                        $commentSection .= "<p class=\"logged-in-as\">";
                            $commentSection .= "<a href=\"https://transcribathon.com/wp-admin/profile.php\" aria-label=\"Logged in as ".wp_get_current_user()->display_name.". Edit your profile.\">";
                                $commentSection .= "Logged in as ".wp_get_current_user()->display_name."";
                            $commentSection .= "</a>.";
                            $commentSection .= "<a href=\"".wp_logout_url(home_url())."\">";
                                $commentSection .= "Log out?";
                            $commentSection .= "</a>";
                        $commentSection .= "</p>";
                        $commentSection .= "<textarea id=\"comment\" class=\"notes-questions item-page-textarea-input login-required\" rows=\"3\" name=\"comment\" aria-required=\"true\">";
                        $commentSection .= "</textarea>";
                        $commentSection .= "<input name=\"wpml_language_code\" type=\"hidden\" value=\"en\" />";
                        $commentSection .= "<p class=\"form-submit\">";
                            $commentSection .= "<input name=\"submit\" type=\"submit\" id=\"submit\" class=\"submit notes-questions-submit theme-color-background\" value=\"SAVE\" />";
                            $commentSection .= "<input type='hidden' name='comment_post_ID' value='296152' id='comment_post_ID' />";
                            $commentSection .= "<input type='hidden' name='comment_parent' id='comment_parent' value='0' />";
                        $commentSection .= "</p>";
                        $commentSection .= "<input type=\"hidden\" id=\"_wp_unfiltered_html_comment_disabled\" name=\"_wp_unfiltered_html_comment_disabled\" value=\"1f491b0ac2\" />";
                        $commentSection .= "<script>
                                                (function() {
                                                    if(window===window.parent){
                                                        document.getElementById('_wp_unfiltered_html_comment_disabled').name='_wp_unfiltered_html_comment';
                                                    }
                                                }) ();
                                            </script>";
                    $commentSection .= "</form>";
                $commentSection .= "</div><!-- #respond -->";
            $commentSection .= "</div><!-- #comments .comments-area -->";
        $commentSection .= '</div>';
/* ---------------------------------------------- Old Image Slider --------------------------------- */
        $numbPhotos = count($itemImages);

        $content .= "<section id='img-slider'>";
        $content .= "<div id='slider-container'>";
            $content .= "<button class='prev-slide' type='button'><i class=\"fas fa-chevron-left\"></i></button>";
            $content .= "<button class='next-slide' type='button'><i class=\"fas fa-chevron-right\"></i></button>";

            $content .= "<div id='inner-slider'>";
                for($x = 0; $x < $numbPhotos; $x++) {
                    $sliderImg = json_decode($itemImages[$x]['ImageLink'], true);
                    $dimensions = 0;
                    if($sliderImg["height"] || $sliderImg["width"]) {
                        if($sliderImg["width"] <= $sliderImg["height"]) {
                            $dimensions = '/0,0,'.$sliderImg["width"].','.$sliderImg["width"];
                        } else {
                            $dimensions = '/0,0,'.$sliderImg["height"].','.$sliderImg["height"];
                        }
                    } else {
                        $dimensions = 'full';
                    }
                    if(substr($sliderImg['service']['@id'],0,4) == 'rhus'){
                       $sliderImgLink ='http://'. str_replace(' ','_',$sliderImg['service']["@id"]) . $dimensions.'/200,200/0/default.jpg';
                    } else {
                        $sliderImgLink = str_replace(' ','_',$sliderImg['service']["@id"]) . $dimensions.'/200,200/0/default.jpg';
                    }
                    $content .= "<div class='slide-sticker' data-value='". ($x+1) ."'>";
                        $content .= "<div class='slide-img-wrap'>";
                            $content .= "<a href='".home_url()."/documents/story/item/?story=".$itemData['StoryId']."&item=".$itemImages[$x]['ItemId']."'><img src=".$sliderImgLink." class='slider-image' alt='slider-image-".($x+1)."' width='200' height='200' loading='lazy'></a>";
                            $content .= "<div class='image-completion-status' style='bottom:20px;border-color:".$itemImages[$x]['CompletionStatusColorCode']."'></div>";
                        $content .= "</div>";
                        $content .= "<div class='slide-number-wrap'>".($x+1)."</div>";
                    $content .= "</div>";
                }
            $content .= "</div>";
            $content .= "</div>";

            $content .= "<div id='controls-div'>";
                $content .= "<button class='prev-set' type='button'><i class=\"fas fa-chevron-double-left\"></i></button>";
                // $content .= "<div id='dot-indicators'>";
                // // placeholder for dot indicators
                // $content .= "</div>";
                $content .= "<div class='num-indicators'>";
                    $content .= "<span id='left-num'>1</span> - <span id='right-num'></span> of ";
                    $content .="<span>". $numbPhotos ."</span>";
                $content .="</div>";
                $content .= "<button class='next-set' type='button'><i class=\"fas fa-chevron-double-right\"></i></button>";
                //// To be discussed if we keep dots or numbers /////
            $content .= "</div>";

            $content .= "<div class='back-to-story'><a href='".home_url()."/documents/story?story=".$itemData['StoryId']."'><i class=\"fas fa-arrow-left\" style='margin-right:7.5px;'></i> Back to the Story</a></div>";

        $content .= "</section>";
/* -------------------------------------------- End of Old Image slider -----------------------  */
    // Image viewer
    $imageViewer = "";
            $imageViewer .= '<div id="openseadragon">';
                $imageViewer .= '<input type="hidden" id="image-data-holder" value=\''.$itemData['ImageLink'].'\'>';
                // Next/Previous Item Buttons
                // if($prevItem) {
                //     $imageViewer .= '<div id="previous-item" title="Previous Item" class="theme-color theme-color-hover"><a href="'.home_url()."/documents/story/item/?story=". $itemData['StoryId']."&item=". $prevItem .'" class="theme-color-hover"><i class="fas fa-step-backward"></i></a></div>';
                // }
                // if($nextItem) {
                //     $imageViewer .= '<div id="next-item" title="Next Item" class="theme-color theme-color-hover"><a href="' . home_url()."/documents/story/item/?story=".$itemData['StoryId']."&item=" . $nextItem . '" class="theme-color-hover"><i class="fas fa-step-forward"></i></a></div>';
                // }
                //viewer buttons out of fullscreen
                $imageViewer .= '<div class="buttons" id="buttons">';
                    $imageViewer .= '<div id="zoom-in" class="theme-color theme-color-hover"><i class="far fa-plus"></i></div>';
                    $imageViewer .= '<div id="zoom-out" class="theme-color theme-color-hover"><i class="far fa-minus"></i></div>';
                    $imageViewer .= '<div id="home" title="View full image" class="theme-color theme-color-hover"><i class="far fa-home"></i></div>';
                    $imageViewer .= '<div id="full-width" title="Fit image width to frame" class="theme-color theme-color-hover"><i class="far fa-arrows-alt-h"></i></div>';
                    $imageViewer .= '<div id="rotate-right" class="theme-color theme-color-hover"><i class="far fa-redo"></i></div>';
                    $imageViewer .= '<div id="rotate-left" class="theme-color theme-color-hover"><i class="far fa-undo"></i></div>';
                    $imageViewer .= '<div id="filterButton" title="Edit image" class="theme-color theme-color-hover"><i class="far fa-sliders-h"></i></div>';
                    $imageViewer .= '<div id="full-page" title="Full screen" class="theme-color theme-color-hover"><i class="far fa-expand-arrows-alt"></i></div>';
                $imageViewer .= '</div>';
                $imageViewer .= '<div class="buttons new-grid-button" id="buttons">';
                    if($isLoggedIn) {
                        if ($locked) {
                            $imageViewer .= '<div id="transcribeLock" class="theme-color theme-color-hover"><i class="far fa-lock"></i></div>';
                        }
                        else {
                            $imageViewer .= '<div id="transcribe" title="Enrich item" class="theme-color theme-color-hover"><i class="far fa-pen"></i></div>';
                        }
                    } else {
                        $imageViewer .= '<div id="transcribe-locked" class="theme-color theme-color-hover"><i class="far fa-pen" id="lock-login"></i></div>';
                    }
                    //$imageViewer .= '<div id="transcribe locked"><i class="far fa-lock" id="lock-login"></i></div>';
                $imageViewer .= '</div>';
            $imageViewer .= '</div>';
        $content .= "<div id='full-view-container'>";

            //$content .= '<div class="item-navigation-area">';
                // $content .= '<ul class="item-navigation-content-container left" style="">';
                //     $content .= '<li><a href="'.home_url().'/documents" style="text-decoration:none;">Stories</a></li>';
                //     $content .= '<li><i class="fal fa-angle-right"></i></li>';
                //     $content .= '<li><span style="text-decoration:none;">';
                //         $content .= '<a href="'.home_url().'/documents/story?story='.$itemData['StoryId'].'">';
                //             $content .= $itemData['Title'];
                //         $content .= '</a>';
                //     $content .= '</span></li>';
                //     /*$content .= '<li><i class="fal fa-angle-right"></i></li>';
                //     $content .= '<li><span>item number</span></li>';*/
                // $content .= '</ul>';
                // $content .= '<ul class="item-navigation-content-container right" style="">';
                //     $content .= '<div class="item-navigation-prev">';
                //         if ($prevItem != null) {
                //             $content .= '<li><a title="first" href="'.home_url().'/documents/story/item/?story='.$itemData['StoryId'].'&item='.$firstItem.'"><i class="fal fa-angle-double-left"></i></a></li>';
                //             $content .=  '<li class="rgt"><a title="previous" href="'.home_url().'/documents/story/item/?story='.$itemData['StoryId'].'&item='.$prevItem.'"><i class="fal fa-angle-left"></i></a></li>';
                //         }
                //     $content .= '</div>';
                //     $content .=  '<li class="rgt">';
                //         $content .= '<a title="Story:'.$itemData['StorydcTitle'].'" href="'.home_url().'/documents/story?story='.$itemData['StoryId'].'">';
                //         $content .= '<i class="fal fa-book"></i></a>';
                //     $content .= '</li>';
                //     $content .= '<div class="item-navigation-next">';
                //         if ($nextItem != null) {
                //             $content .= '<li class="rgt"><a title="next" href="'.home_url().'/documents/story/item/?story='.$itemData['StoryId'].'&item='.$nextItem.'"><i class="fal fa-angle-right"></i></a></li>';
                //             $content .= '<li class="rgt"><a title="last" href="'.home_url().'/documents/story/item/?story='.$itemData['StoryId'].'&item='.$lastItem.'"><i class="fal fa-angle-double-right"></i></a></li>';
                //         }
                //     $content .= '</div>';
                // $content .= '</ul>';
            //$content .= '</div>';

            // Start of Page building
            if($htrData){
                // $content .= "<div class='title-n-btn'>";
                //     $content .= "<div id='startTranscription'><b>🖉  Start Transcription</b></div>";
                //     $content .= "<a id='htrButton' type='button' href='https://europeana.transcribathon.local/story/item/item_page_htr/?story=".$_GET['story']."&item=".$_GET['item']."'><i class='fa fa-laptop' aria-hidden='true'></i><b> HTR TRANSCRIPTION</b></a>";
                // $content .= "</div>";
                $content .= "<div style='position:relative;width:80%;margin:20px auto;'>";
                    $content .= "<h4 id='item-header'><b>".$itemData['Title']."</b></h4>";
                $content .= "</div>";
                $content .= "<div style='clear:both;'></div>";

                $content .= "<div class='primary-full-width'>";
                // left side
                    $content .= "<div id='full-view-l'>";
                        $content .= $imageViewer;
                        $content .= "<div style='clear:both;'></div>";

                        $content .= "<div id='full-view-editor' hidden>";
                            $content .= $editorTab;
                        $content .= "</div>";
                        if($currentTranscription['Text'] != Null){
                            $content .= "<script>document.querySelector('#no-text-selector').style.display='none';</script>";
                        }
                        $content .= "<div id='user-transcription' style='position:relative;margin-top:30px;'>";
                            $content .= "<div id='startTranscription' style='display:flex;flex-direction:row;justify-content:space-between;padding:1px;' title='click to open editor'>";
                                $content .= "<span><h5 style='color:#0a72cc;'><i style=\"font-size: 20px;margin-bottom:5px;\" class=\"fa fa-pencil\" aria-hidden=\"true\"></i>&nbsp TRANSCRIPTION</h5></span>";
                                    if($itemData['TranscriptionStatusColorCode'] == '#61e02f'){
                                        $content .= "<span style='top:50%;transform:translate(0,8px);'><p class='completed'>COMPLETED</p>";
                                    }
                                    elseif($itemData['TranscriptionStatusColorCode'] == '#fff700') {
                                        $content .= "<span style='top:50%;transform:translate(0,8px);'><p class='edited'>EDITED</p>";
                                    }
                                    elseif($itemData['TranscriptionStatusColorCode'] == '#ffc720') {
                                        $content .= "<span style='top:50%;transform:translate(0,8px);'><p class='reviewed'>REVIEWED</p>";
                                    } else {
                                        $content .= "<span style='top:50%;transform:translate(0,8px);'><p class='not-started'>NOT STARTED</p>";
                                    }
                                    $content .= "<i style=\"font-size: 20px;margin-bottom:5px;\" class=\"fa fa-pencil\" aria-hidden=\"true\"></i></span>";
                                $content .= "</div>";
                                $content .= "<div class='htr-trans-toggle'>";
                                $content .= "<div class='togglePara' style='max-height:200px;'>";
                                    $content .= "<p>". $currentTranscription['Text'] ."</p>";
                                $content .= "</div>";
                                if(strlen($currentTranscription['Text']) > 200){
                                    $content .= "<p id='no-htr-toggle' class='descMore'>Show More</p>";
                                }
                                $content .= "</div>";
                                if($currentTranscription['Languages']){
                                    $content .= "<h6 class='enrich-headers'>Language(s) of Transcription</h6>";
                                    $content .= "<div class='accordion'>";
                                    foreach($currentTranscription['Languages'] as $trLang) {
                                        $content .= "<div class='card-header' style='position:relative;left:-5px;'>" . $trLang['Name'] . "</div>";
                                    }
                                        // $content .= "<div class='card-header' style='position:relative;left:-5px;'>".$trLanguage."</div>";
                                    $content .= "</div>";
                                }
                            $content .= "</div>";
                    $content .= "</div>";
                    // right side
                    $content .= "<div id='full-view-r'>";
                        $content .= "<div id='full-view-tagging' class='htr-map' style='height:500px;'>";
                            $content .= $mapTab;
                         //   $content .= $taggingTab;
                        $content .= "</div>";
                    //$content .= "<hr>";
                        $content .= "<div id='full-view-info' style='display:none;'>";
                            $content .= $infoTab;
                        $content .= "</div>";
                    //$content .= "<hr>";
                        $content .= "<div style='clear:both;'></div>";
                        //HTR Transcription
                        $htrTranscription = get_text_from_pagexml($htrData, '<br />');
                        $content .= "<a style='text-decoration:none;' href='http://europeana.transcribathon.eu.local/documents/story/item/item_page_htr/?story=".$_GET['story']."&item=".$_GET['item']."'><div id='htrButton' style='display:flex;flex-direction:row;justify-content:space-between;margin-top:30px;' title='click to open HTR viewer'>";
                            $content .= "<span><h5 style='color:#0a72cc;'><i style=\"font-size: 20px;margin-bottom:5px;\" class=\"fa fa-pencil\" aria-hidden=\"true\"></i>&nbsp HTR TRANSCRIPTION</h5></span>";
                            if(strlen($htrTranscription) > 10){
                                $content .= "<span style='top:50%;transform:translate(-1.5px,8px);'><p class='edited'>EDITED</p></span>";
                            } else {
                                $content .= "<span style='top:50%;transform:translate(-1.5px,8px);'><p class='not-started'>NOT STARTED</p></span>";
                            }
                        $content .= "</div></a>";
                        $content .= "<div class='htr-trans-toggle'>";
                        $content .= "<div class='togglePara' style='max-height:200px;'>";
                            $content .= "<p>" . str_replace("<br /><br />", "<br />", $htrTranscription) . "</p>";
                        $content .= "</div>";
                        if(strlen($htrTranscription)> 200){
                            $content .= "<p class='descMore' role='button'>Show More</p>";
                        }
                        $content .= "</div>";

                        $content .= '<div id="full-view-autoEnrichment" >';
                            $content .= $autoEnrichmentTab;
                        $content .= '</div>';
                        //Enrichments
                        $content .= "<div style='display:flex;flex-direction:row;justify-content:space-between;'>";
                            $content .= "<span><h5 style='color:#0a72cc;'><i style=\"font-size: 20px;margin-bottom:5px;\" class=\"fa fa-book\" aria-hidden=\"true\"></i>&nbsp ENRICHMENTS</h5></span>";
                            if($itemData['TaggingStatusColorCode'] == '#61e02f'){
                                $content .= "<span style='top:50%;transform:translate(0,8px);'><p class='completed'>COMPLETED</p></span>";
                            }
                            elseif($itemData['TaggingStatusColorCode'] == '#fff700') {
                                $content .= "<span style='top:50%;transform:translate(0,8px);'><p class='edited'>EDIT</p></span>";
                            }
                            elseif($itemData['TaggingStatusColorCode'] == '#ffc720') {
                                $content .= "<span style='top:50%;transform:translate(0,8px);'><p class='reviewed'>REVIEW</p></span>";
                            } else {
                                $content .= "<span style='top:50%;transform:translate(0,8px);'><p class='not-started'>NOT STARTED</p></span>";
                            }
                        $content .= "</div>";
                        // description
                        if($itemData['description']) {
                            $content .= "<h6 class='enrich-headers'>Description</h6>";
                                $content .= "<p>".$itemData['Description']."</p>";
                            $content .= "<h6 class='enrich-headers'>Language(s) of Description</h6>";
                            $content .= "<div calss='accordion'>";
                            foreach($languages as $language){
                                if($itemData['DescriptionLanguage'] == $language['LanguageId']){
                                    $content .= "<div class='card-header'>".$language['Name']."</div>";
                                }
                            }
                            $content .= "</div>";
                        }
                        // document date
                        if($dateStart || $dateEnd) {
                            $content .= "<h6 class='enrich-headers'>Document Date</h6>";
                            $content .= "<div class='accordion'>";
                                $content .= "<div class='card'>";
                                    $content .= "<div class='card-header'>";
                                        $content .= "<span style='float:left;'>Start Date:</span>";
                                        $content .= "<span style='float:right;margin-right:50%;'>End Date:</span>";
                                    $content .= "</div>";
                                $content .= "</div>";
                                $content .= "<div class='card'>";
                                    $content .= "<div class='card-header'>";
                                        $content .= "<span style='float:left;'>".$dateStart."</span>";
                                        $content .= "<span style='float:right,margin-right:50%;'>".$dateEnd."</span>";
                                    $content .= "</div>";
                                $content .= "</div>";
                            $content .= "</div>";
                        }
                        // people
                        if($itemData['Persons']) {
                            $content .= "<h6 class='enrich-headers'>People</h6>";
                            $content .= "<div class='accordion' id='personAccord'>";
                            foreach($itemData['Persons'] as $persona) {
                                $personBDate = strtotime($persona['BirthDate']);
                                $pBirthDate = date("d/m/Y", $personBDate);
                                $personDDate = strtotime($persona['DeathDate']);
                                $pDeathDate = date("d/m/Y", $personDDate);

                                $content .= "<div class='card'>";
                                    $content .= "<div role='button' data-toggle='collapse' data-target='#collapse-".$persona['PersonId']."' aria-expanded='true' aria-controls='collapse-".$persona['PersonId']."' class='card-header' id='header-".$persona['PersonId']."'>";
                                        $content .= "<span>";
                                        $content .=  $persona['FirstName']." ".$persona['LastName'];
                                        if ($persona['BirthDate'] && $persona['DeathDate']) {
                                            $content .= " (".$pBirthDate. " - " .$pDeathDate." )";
                                        } elseif ($persona['BirthDate']) {
                                            $content .= " (Birth: ".$pBirthDate." )";
                                        } elseif ($persona['DeathDate']) {
                                            $content .= " (Death: ".$pDeathDate." )";
                                        }
                                        $content .= "</span>";
                                        $content .= "<span><i style='color:#0a72cc;float:right;' class='fas fa-angle-down'></i></span>";
                                    $content .= "</div>";
                                    $content .= "<div class='collapse' id='collapse-".$persona['PersonId']."' aria-labelledby='header-".$persona['PersonId']."' data-parent='#personAccord'>";
                                        $content .= "<div class='card-body'>";
                                            $content .= "<table border=0>";
                                                $content .= "<tr>";
                                                    $content .= "<th></th>";
                                                    $content .= "<th>Birth</th>";
                                                    $content .= "<th>Death</th>";
                                                $content .= "</tr>";
                                                $content .= "<tr>";
                                                    $content .= "<th>Date</th>";
                                                    $content .= "<td>".$pBirthDate."</td>";
                                                    $content .= "<td>".$pDeathDate."</td>";
                                                $content .= "</tr>";
                                                $content .= "<tr>";
                                                    $content .= "<th>Place</th>";
                                                    $content .= "<td>".$persona['BirthPlace']."</td>";
                                                    $content .= "<td>".$persona['DeathPlace']."</td>";
                                                $content .= "</tr>";
                                                if($persona['Description'] && $persona['Description'] != 'NULL') {
                                                    $content .= "<tr>";
                                                        $content .= "<th>Description</th>";
                                                        $content .= "<td colspan='2'>" . $persona['Description'] . "</td>";
                                                    $content .= "</tr>";
                                                }
                                             //   $content .= "<tr><td>" . $persona['Description'] . "</td></tr>";
                                            $content .= "</table>";
                                        $content .= "</div>";
                                    $content .= "</div>";
                                $content .= "</div>";

                            }
                            $content .= "</div>";
                        }
                        if($itemData['Properties']) {
                            // key words
                            $content .= "<h6 class='enrich-headers'>Keywords</h6>";
                            $content .= "<div class='keyword-container js-check'>";
                            foreach($itemData['Properties'] as $properti){
                                if($properti['PropertyType'] == "Keyword"){
                                    $content .= "<div class='keyword-single'>".$properti['PropertyValue']."</div>";
                                }
                            }
                            $content .= "</div>";
                            // Category
                            $content .= "<h6 class='enrich-headers'>Category</h6>";
                            $content .= "<div class='accordion js-check'>";
                            foreach($itemData['Properties'] as $property) {
                                if($property['PropertyType'] == "Category") {
                                    $content .= "<div class='card-header'>" . $property['PropertyValue'] . "</div>";
                                }
                            }
                            $content .= "</div>";
                            // other sources
                            $content .= "<h6 class='enrich-headers'>Other Sources</h6>";
                            $content .= "<div class='accordion js-check'>";
                            foreach($itemData['Properties'] as $property){
                                if($property['PropertyType'] == 'Link' ) {
                                    $content .= "<div class='card'>";
                                        $content .= "<div class='card-header'><a href='".$property['PropertyValue']."'>".$property['PropertyValue']."</a></div>";
                                        $content .= "<div class='card-header'>Description: ".$property['PropertyDescription']."</div>";
                                    $content .= "</div>";
                                }
                            }
                            $content .= "</div>";
                        }
                        // Location
                        if($itemData['Places']) {
                            $content .= "<h6 class='enrich-headers'>Location</h6>";
                            $content .= "<div class='accordion'>";
                            foreach($itemData['Places'] as $platz){
                                $content .= "<div class='card-header'>".$platz['Name']." (".$platz['Latitude'].", ".$platz['Longitude'].")</div>";
                            }
                            $content .= "</div>";
                        }
                        $content .= "</div>";
                    $content .= "</div>";
                $content .= "</div>";
                // Metadata
                $content .= "<div style='clear:both;'></div>";
                $content .= "<div id='info-section' style='width:90%;margin:0 auto;'>";
                    $content .= "<div id='item-meta-collapse' class='add-info enrich-header' style='width:100%;background-color:#f8f8f8;' role='button' data-toggle='collapse' href='#infoCollapse' aria-expanded='false' aria-controls='infoCollapse'>";
                        $content .= "<p style='color:#0a72cc;'><span><b><i style='margin-right:5px;' class=\"fa fa-info-circle\" aria-hidden=\"true\"></i>METADATA</b></span><span style='float:right;'><i style='font-size:25px;margin-right:10px;' class='fas fa-angle-down'></i></span></p>";
                    $content .= "</div>";
                    $content .= "<div class='dl-enrichments' style='height: 300px;'>";
                    //Contributor
                    if($itemData['StorydcContributor']) {
                        $content .= "<div class='meta-h meta-sticker'>";
                            $content .= "<p class='mb-1'>Contributor</p>";
                            $content .= "<p class='meta-p'>" . str_replace(' || ', '</br>', $itemData['StorydcContributor']) . "</p>";
                        $content .= "</div>";
                    }
                    //Creator
                    if($itemData['StorydcCreator']) {
                        $content .= "<div class='meta-h meta-sticker'>";
                            $content .= "<p class='mb-1'>Creator</p>";
                            $content .= "<p class='meta-p'>" . str_replace(' || ', '</br>', $itemData['StorydcCreator']) . "</p>";
                        $content .= "</div>";
                    }
                    // Story Source
                    if($itemData['StorydcSource']) {
                        $content .= "<div class='meta-h meta-sticker'>";
                            $content .= "<p class='mb-1'>Story Source</p>";
                            $source = array_unique(explode(' || ', $itemData['StorydcSource']));
                            $content .= "<p class='meta-p'>" . implode('</br>', $source) . "</p>";
                        $content .= "</div>";
                    }
                    //Identifier
                    if($itemData['StoryExternalRecordId']) {
                        $content .= "<div class='meta-h meta-sticker'>";
                            $content .= "<p class='mb-1'>Identifier</p>";
                            if(substr($itemData['StoryExternalRecordId'], 0, 4) == 'http'){
                                $content .= "<p class='meta-p'><a target='_blank' href='".$itemData['StoryExternalRecordId']."'>" . $itemData['StoryExternalRecordId'] . "</a></p>";
                            } else {
                                $content .= "<p class='meta-p'>" . $itemData['StoryExternalRecordId'] . "</p>";
                            }
                        $content .= "</div>";
                    }
                    //Document Language
                    if($itemData['StorydcLanguage']) {
                        $content .= "<div class='meta-h meta-sticker'>";
                            $content .= "<p class='mb-1'>Document Language</p>";
                            $dcLanguage = array_unique(explode(' || ', $itemData['StorydcLanguage']));
                            $content .= "<p class='meta-p'>" . implode('</br>', $dcLanguage) . "</p>";
                        $content .= "</div>";
                    }
                    // Provider Language
                    if($itemData['StoryedmLanguage']) {
                        $content .= "<div class='meta-h meta-sticker'>";
                            $content .= "<p class='mb-1'>Provider Language</p>";
                            $content .= "<p class='meta-p'>".$itemData['StoryedmLanguage']."</p>";
                        $content .= "</div>";
                    }
                    // Publisher
                    if($itemData['StoryedmProvider']) {
                        $content .= "<div class='meta-h meta-sticker'>";
                            $content .= "<p class='mb-1'>Publisher</p>";
                            if(substr($itemData['StoryedmProvider'], 0, 4) == 'http'){
                                $content .= "<p class='meta-p'><a target='_blank' href='".$itemData['StoryedmProvider']."'>" . $itemData['StoryedmProvider'] . "</a></p>";
                            } else {
                                $content .= "<p class='meta-p'>" . $itemData['StoryedmProvider'] . "</p>";
                            }
                        $content .= "</div>";
                    }
                    // Rights
                    if($itemData['StoryedmRights']) {
                        $content .= "<div class='meta-h meta-sticker'>";
                            $content .= "<p class='mb-1'>Rights</p>";
                            $edmRights = array_unique(explode(' || ', $itemData['StoryedmRights']));
                            foreach($edmRights as $right) {
                                if(substr($right, 0, 4) == 'http'){
                                    $content .= "<p class='meta-p'><a target='_blank' href='".$right."'>" . $right . "</a></p>";
                                } else {
                                    $content .= "<p class='meta-p'>" . $right . "</p>";
                                }
                            }
                        $content .= "</div>";
                    }
                    // Image Rights
                    if($itemData['StorydcRights']) {
                        $content .= "<div class='meta-h meta-sticker'>";
                            $content .= "<p class='mb-1'>Image Rights</p>";
                            $imgRights = array_unique(explode(' || ', $itemData['StorydcRights']));
                            foreach($imgRights as $iRight) {
                                if(substr($iRight, 0, 4) == 'http'){
                                    $content .= "<p class='meta-p'><a target='_blank' href='".$iRight."'>" . $iRight . "</a></p>";
                                } else {
                                    $content .= "<p class='meta-p'>" . $iRight . "</p>";
                                }
                            }
                        $content .= "</div>";
                    }
                    // Type
                    if($itemData['StorydcType']) {
                        $content .= "<div class='meta-h meta-sticker'>";
                            $content .= "<p class='mb-1'>Type</p>";
                            $content .= "<p class='meta-p'>" . str_replace(' || ', '</br>', $itemData['StorydcType']) . "</p>";
                        $content .= "</div>";
                    }
                    // Medium
                    if($itemData['StorydctermsMedium']) {
                        $content .= "<div class='meta-h meta-sticker'>";
                            $content .= "<p class='mb-1'>Medium</p>";
                            $content .= "<p class='meta-p'>" . str_replace(' || ', '</br>', $itemData['StorydctermsMedium']) . "</p>";
                        $content .= "</div>";
                    }
                    // Creation Start
                    if($itemData['StoryedmBegin']) {
                        $content .= "<div class='meta-h meta-sticker'>";
                            $content .= "<p class='mb-1'>Creation Start</p>";
                            $content .= "<p class='meta-p'>" . str_replace(' || ', "</br>", $itemData['StoryedmBegin']) . "</p>";
                        $content .= "</div>";
                    }
                    // Creation End
                    if($itemData['StoryedmEnd']) {
                        $content .= "<div class='meta-h meta-sticker'>";
                            $content .= "<p class='mb-1'>Creation End</p>";
                            $content .= "<p class='meta-p'>" . str_replace(' || ', "</br>", $itemData['StoryedmEnd']) . "</p>";
                        $content .= "</div>";
                    }
                    // Providing Country
                    if($itemData['StoryedmCountry']) {
                        $content .= "<div class='meta-h meta-sticker'>";
                            $content .= "<p class='mb-1'>Providing Country</p>";
                            $content .= "<p class='meta-p'>".$itemData['StoryedmCountry']."</p>";
                        $content .= "</div>";
                    }
                    // Institution
                    if($itemData['StoryedmDataProvider']) {
                        $content .= "<div class='meta-h meta-sticker'>";
                            $content .= "<p class='mb-1'>Institution</p>";
                            $content .= "<p class='meta-p'>".$itemData['StoryedmDataProvider']."</p>";
                        $content .= "</div>";
                    }
                    // Dataset
                    if($itemData['StoryedmDatasetName']) {
                        $content .= "<div class='meta-h meta-sticker'>";
                            $content .= "<p class='mb-1'>Dataset</p>";
                            $content .= "<p class='meta-p'>".$itemData['StoryedmDatasetName']."</p>";
                        $content .= "</div>";
                    }
                    // Source Url
                    if($itemData['StoryedmIsShownAt']) {
                        $content .= "<div class='meta-h meta-sticker'>";
                            $content .= "<p class='mb-1'>Source Url</p>";
                            if(substr($itemData['StoryedmIsShownAt'], 0, 4) == 'http'){
                                $content .= "<p class='meta-p'><a target='_blank' href='".$itemData['StoryedmIsShownAt']."'>" . $itemData['StoryedmIsShownAt'] . "</a></p>";
                            } else {
                                $content .= "<p class='meta-p'>" . $itemData['StoryedmIsShownAt'] . "</p>";
                            }
                        $content .= "</div>";
                    }
                    // Story Title
                    $content .= "<div class='meta-h meta-sticker'>";
                        $content .= "<p class='mb-1'>Story Title</p>";
                        $content .= "<p class='meta-p'>". str_replace(' || ', "</br>", $itemData['StorydcTitle']) . "</p>";
                    $content .= "</div>";
                    // Story Landing Page
                    if($itemData['StoryedmLandingPage']) {
                        $content .= "<div class='meta-h meta-sticker'>";
                            $content .= "<p class='mb-1'>Story Landing Page</p>";
                            if(substr($itemData['StoryedmLandingPage'], 0, 4) == 'http'){
                                $content .= "<p class='meta-p'><a target='_blank' href='".$itemData['StoryedmLandingPage']."'>" . $itemData['StoryedmLandingPage'] . "</a></p>";
                            } else {
                                $content .= "<p class='meta-p'>" . $itemData['StoryedmLandingPage'] . "</p>";
                            }
                        $content .= "</div>";
                    }
                    // Parent Story
                    if($itemData['StoryParentStory']) {
                        $content .= "<div class='meta-h meta-sticker'>";
                            $content .= "<p class='mb-1'>Parent Story</p>";
                            if(substr($itemData['StoryParentStory'], 0, 4) == 'http'){
                                $content .= "<p class='meta-p'><a target='_blank' href='".$itemData['StoryParentStory']."'>" . $itemData['StoryParentStory'] . "</a></p>";
                            } else {
                                $content .= "<p class='meta-p'>" . $itemData['StoryParentStory'] . "</p>";
                            }
                        $content .= "</div>";
                    }
                $content .= "</div>";
                $content .= "<div style='clear:both'></div>";

                $content .= "</div>";
            // Else means that there is no Htr data available for the choosen Story
            } else {
                // Item page without htr transcription
                $content .= "<div class='title-n-btn'>";
                    $content .= "<h4 id='item-header'><b>".$itemData['Title']."</b></h4>";
                  //  $content .= "<div id='startTranscription' class='start-transcription'><b>🖉  Start Transcription</b></div>";
                $content .= "</div>";

                $content .= "<div class='primary-full-width'>";

                $content .= "<div id='full-view-l'>";
                    $content .= $imageViewer;

                $content .= "<div style='clear:both;'></div>";

                $content .= "<div id='full-view-editor' hidden>";
                    $content .= $editorTab;
                    if($currentTranscription['Text'] != Null){
                        $content .= "<script>document.querySelector('#no-text-selector').style.display='none';</script>";
                    }
                $content .= "</div>";
                //Download Enrichments Button/Div
                    $content .= '<a class="dl-enrichments" style="display:flex;flex-direction:row;justify-content:space-evenly;background-color:#f8f8f8;color:#0a72cc;cursor:pointer;" type="button" target="_blank" href="' . get_main_url() . '/htr-import/example/form-example.php?itemId=' . $_GET['item']  . '">';
                    $content .= "<span><h5 style='color:#0a72cc;'>Test Transkribus HTR Transcription</h5></span>";
                    $content .= "<span><i style='position:relative;top:50%;transform:translateY(-50%);font-size:20px;' class='fa fa-download' aria-hidden='true'></i></span>";
                $content .= "</a>";
                //Enrichments
                $content .= "<div class='dl-enrichments'>";
                $content .= "<div id='startEnrichment' class='enrich-header' style='display:flex;flex-direction:row;justify-content:space-between;'>";
                $content .= "<div style='display:inline-block;'><h5 style='color:#0a72cc;'><i style=\"font-size: 20px;margin-bottom:5px;\" class=\"fa fa-book\" aria-hidden=\"true\"></i> ENRICHMENTS</h5></div>";
                if($itemData['TaggingStatusColorCode'] == '#61e02f'){
                    $content .= "<div style='display:inline-block;'><span style='display:inline-block;font-weight:500!important;' class='completed'>COMPLETED</span>";
                }
                elseif($itemData['TaggingStatusColorCode'] == '#fff700') {
                    $content .= "<div style='display:inline-block;'><span style='display:inline-block;font-weight:500!important;' class='edited'>EDIT</span>";
                }
                elseif($itemData['TaggingStatusColorCode'] == '#ffc720') {
                    $content .= "<div style='display:inline-block;'><span style='display:inline-block;font-weight:500!important;' class='reviewed'>REVIEW</span>";
                } else {
                    $content .= "<div style='display:inline-block;'><span style='display:inline-block;font-weight:500!important;' class='not-started'>NOT STARTED</span>";
                }
                $content .= "<i class=\"fa fa-pencil right-i\" aria-hidden=\"true\"></i></div>";
                $content .= "</div>";
                // document date
                if($dateStart || $dateEnd) {
                    $content .= "<h6 class='enrich-headers'>Document Date</h6>";
                    $content .= "<div class='doc-date-container'>";
                        $content .= "<div class='date-top'>";
                                $content .= "<div style='float:left;display:inline-block;'>Start Date:</div>";
                                $content .= "<div style='float:right;margin-right:50%;display:inline-block;'>End Date:</div>";
                        $content .= "</div>";
                        $content .= "<div style='clear:both;'></div>";
                        $content .= "<div class='date-bottom'>";
                                $content .= "<div style='float:left;display:inline-block'>".$dateStart."</div>";
                                $content .= "<div style='float:right;margin-right:48%;display:inline-block;'>".$dateEnd."</div>";
                        $content .= "</div>";
                    $content .= "</div>";
                    $content .= "<div style='clear:both;'></div>";
                }
                // people
                if($itemData['Persons']) {
                    $content .= "<h6 class='enrich-headers'>People</h6>";
                    $content .= "<div class='person-container' id='personAccord'>";
                    foreach($itemData['Persons'] as $persona) {
                        $personBDate = strtotime($persona['BirthDate']);
                        $pBirthDate = date("d/m/Y", $personBDate);
                        $personDDate = strtotime($persona['DeathDate']);
                        $pDeathDate = date("d/m/Y", $personDDate);

                        $content .= "<div class='single-person'>";
                            $content .= "<div class='person-info'>";
                                $content .= "<span style='font-weight:400;'>" . $persona['FirstName'] . ' ' . $persona['LastName'] . "</span>";
                                if($personBDate != Null && $personDDate != Null) {
                                    $content .= " (" . $pBirthDate;
                                    if($persona['BirthPlace'] != Null) {
                                        $content .= ', ' . $persona['BirthPlace'];
                                    } 
                                    $content .= " - " . $pDeathDate;
                                    if($persona['DeathPlace'] != Null) {
                                        $content .= ', ' . $persona['DeathPlace'];
                                    }
                                    $content .= ")";
                                } elseif ($personBDate != Null) {
                                    $content .= " (Birth: " . $pBirthDate . ")";
                                } elseif ($personDDate != Null) {
                                    $content .= " (Death: " . $pDeathDate . ")";
                                }
                            $content .= "</div>";
                            if($persona['Description'] != Null && $persona['Description'] != 'NULL') {
                                $content .= "<div class='person-description' style='display:none;'><span style='font-weight:400;'><b>Description</b>: </span>" . $persona['Description'] . "</span></div>";
                            }
                            
                        $content .= "</div>";
                    }
                    $content .= "</div>";
                }
                // key words
                // js-check class is used to check if the property field is empty, so we can hide the header if it is empty
                if($itemData['Properties']) {
                    // Category
                    $content .= "<h6 class='enrich-headers'>Type of Media</h6>";
                    $content .= "<div class='keyword-container js-check'>";
                    foreach($itemData['Properties'] as $property) {
                        if($property['PropertyType'] == "Category") {
                            $content .= "<div class='keyword-single'>" . $property['PropertyValue'] . "</div>";
                        }
                    }
                    $content .= "</div>";
                    $content .= "<h6 class='enrich-headers'>Keywords</h6>";
                    $content .= "<div class='keyword-container js-check'>";
                    foreach($itemData['Properties'] as $properti){
                        if($properti['PropertyType'] == "Keyword"){
                            $content .= "<div class='keyword-single'>".$properti['PropertyValue']."</div>";
                        }
                    }
                    $content .= "</div>";
                    // other sources
                    $content .= "<h6 class='enrich-headers'>Other Sources</h6>";
                    $content .= "<div class='link-container js-check'>";
                    foreach($itemData['Properties'] as $property){
                        if($property['PropertyType'] == 'Link' ) {
                            
                            $content .= "<div class='link-single' title=".$property['PropertyType']."><a href='".$property['PropertyValue']."' style='color:#fff;'>".$property['PropertyValue']."</a>";
                            $content .= "<p class='link-description' style='display:none;'>" . $property['PropertyDescription'] . "</p>";
                            $content .= "</div>";
                        }
                    }
                    $content .= "</div>";
                }
                $content .= "</div>";
                //Metadata
                $content .= "<div id='item-meta-collapse' class='dl-enrichments' style='background-color:#f8f8f8;'>";
                    $content .= "<div class='add-info enrich-header' style='color:#0a72cc;font-size:1.2em;cursor:pointer;' role='button' aria-expanded='false'>";
                        $content .= "<span><i style='margin-right:14px;' class=\"fa fa-info-circle\" aria-hidden=\"true\"></i>METADATA</span><span style='float:right;padding-right:10px;'><i style='font-size:25px;margin-right:10px;' class='fas fa-angle-down'></i></span>";
                    $content .= "</div>";
                $content .= "</div>";

                $content .= "<div class='dl-enrichments' style='height: 300px;overflow:hidden;padding: 0 50px;'>";
                    //Contributor
                    if($itemData['StorydcContributor']) {
                        $content .= "<div class='single-meta'>";
                            $content .= "<span class='mb-1'>Contributor</span>";
                            $content .= "<span class='meta-p'>" . str_replace(' || ', '</br>', $itemData['StorydcContributor']) . "</span>";
                        $content .= "</div>";
                    }
                    //Creator
                    if($itemData['StorydcCreator']) {
                        $content .= "<div class='single-meta'>";
                            $content .= "<span class='mb-1'>Creator</span>";
                            $content .= "<span class='meta-p'>" . str_replace(' || ', '</br>', $itemData['StorydcCreator']) . "</span>";
                        $content .= "</div>";
                    }
                    // Story Source
                    if($itemData['StorydcSource']) {
                        $content .= "<div class='single-meta'>";
                            $content .= "<span class='mb-1'>Story Source</span>";
                            $source = array_unique(explode(' || ', $itemData['StorydcSource']));
                            $content .= "<span class='meta-p'>" . implode('</br>', $source) . "</span>";
                        $content .= "</div>";
                    }
                    //Identifier
                    if($itemData['StoryExternalRecordId']) {
                        $content .= "<div class='single-meta'>";
                            $content .= "<span class='mb-1'>Identifier</span>";
                            if(substr($itemData['StoryExternalRecordId'], 0, 4) == 'http'){
                                $content .= "<span class='meta-p'><a target='_blank' href='".$itemData['StoryExternalRecordId']."'>" . $itemData['StoryExternalRecordId'] . "</a></span>";
                            } else {
                                $content .= "<span class='meta-p'>" . $itemData['StoryExternalRecordId'] . "</span>";
                            }
                        $content .= "</div>";
                    }
                    //Document Language
                    if($itemData['StorydcLanguage']) {
                        $content .= "<div class='single-meta'>";
                            $content .= "<span class='mb-1'>Document Language</span>";
                            $dcLanguage = array_unique(explode(' || ', $itemData['StorydcLanguage']));
                            $content .= "<span class='meta-p'>" . implode(' / ', $dcLanguage) . "</span>";
                        $content .= "</div>";
                    }
                    // Provider Language
                    if($itemData['StoryedmLanguage']) {
                        $content .= "<div class='single-meta'>";
                            $content .= "<span class='mb-1'>Provider Language</span>";
                            $content .= "<span class='meta-p'>".$itemData['StoryedmLanguage']."</span>";
                        $content .= "</div>";
                    }
                    // Publisher
                    if($itemData['StoryedmProvider']) {
                        $content .= "<div class='single-meta'>";
                            $content .= "<span class='mb-1'>Publisher</span>";
                            if(substr($itemData['StoryedmProvider'], 0, 4) == 'http'){
                                $content .= "<span class='meta-p'><a target='_blank' href='".$itemData['StoryedmProvider']."'>" . $itemData['StoryedmProvider'] . "</a></span>";
                            } else {
                                $content .= "<span class='meta-p'>" . $itemData['StoryedmProvider'] . "</span>";
                            }
                        $content .= "</div>";
                    }
                    // Rights
                    if($itemData['StoryedmRights']) {
                        $content .= "<div class='single-meta'>";
                            $content .= "<span class='mb-1'>Rights</span>";
                            $edmRights = array_unique(explode(' || ', $itemData['StoryedmRights']));
                            foreach($edmRights as $right) {
                                if(substr($right, 0, 4) == 'http'){
                                    $content .= "<span class='meta-p'><a target='_blank' href='".$right."'>" . $right . "</a></span>";
                                } else {
                                    $content .= "<span class='meta-p'>" . $right . "</span>";
                                }
                            }
                        $content .= "</div>";
                    }
                    // Image Rights
                    if($itemData['StorydcRights']) {
                        $content .= "<div class='single-meta'>";
                            $content .= "<span class='mb-1'>Image Rights</span>";
                            $imgRights = array_unique(explode(' || ', $itemData['StorydcRights']));
                            foreach($imgRights as $iRight) {
                                if(substr($iRight, 0, 4) == 'http'){
                                    $content .= "<span class='meta-p'><a target='_blank' href='".$iRight."'>" . $iRight . "</a></span>";
                                } else {
                                    $content .= "<span class='meta-p'>" . $iRight . "</span>";
                                }
                            }
                        $content .= "</div>";
                    }
                    // Type
                    if($itemData['StorydcType']) {
                        $content .= "<div class='single-meta'>";
                            $content .= "<span class='mb-1'>Type</span>";
                            $content .= "<span class='meta-p'>" . str_replace(' || ', ' / ', $itemData['StorydcType']) . "</span>";
                        $content .= "</div>";
                    }
                    // Medium
                    if($itemData['StorydctermsMedium']) {
                        $content .= "<div class='single-meta'>";
                            $content .= "<span class='mb-1'>Medium</span>";
                            $content .= "<span class='meta-p'>" . str_replace(' || ', ' / ', $itemData['StorydctermsMedium']) . "</span>";
                        $content .= "</div>";
                    }
                    // Creation Start
                    if($itemData['StoryedmBegin']) {
                        $content .= "<div class='single-meta'>";
                            $content .= "<span class='mb-1'>Creation Start</span>";
                            $content .= "<span class='meta-p'>" . str_replace(' || ', " / ", $itemData['StoryedmBegin']) . "</span>";
                        $content .= "</div>";
                    }
                    // Creation End
                    if($itemData['StoryedmEnd']) {
                        $content .= "<div class='single-meta'>";
                            $content .= "<span class='mb-1'>Creation End</span>";
                            $content .= "<span class='meta-p'>" . str_replace(' || ', " / ", $itemData['StoryedmEnd']) . "</span>";
                        $content .= "</div>";
                    }
                    // Providing Country
                    if($itemData['StoryedmCountry']) {
                        $content .= "<div class='single-meta'>";
                            $content .= "<span class='mb-1'>Providing Country</span>";
                            $content .= "<span class='meta-p'>".$itemData['StoryedmCountry']."</span>";
                        $content .= "</div>";
                    }
                    // Institution
                    if($itemData['StoryedmDataProvider']) {
                        $content .= "<div class='single-meta'>";
                            $content .= "<span class='mb-1'>Institution</span>";
                            $content .= "<span class='meta-p'>".$itemData['StoryedmDataProvider']."</span>";
                        $content .= "</div>";
                    }
                    // Dataset
                    if($itemData['StoryedmDatasetName']) {
                        $content .= "<div class='single-meta'>";
                            $content .= "<span class='mb-1'>Dataset</span>";
                            $content .= "<span class='meta-p'>".$itemData['StoryedmDatasetName']."</span>";
                        $content .= "</div>";
                    }
                    // Source Url
                    if($itemData['StoryedmIsShownAt']) {
                        $content .= "<div class='single-meta'>";
                            $content .= "<span class='mb-1'>Source Url</span>";
                            if(substr($itemData['StoryedmIsShownAt'], 0, 4) == 'http'){
                                $content .= "<a class='meta-p' target='_blank' href='".$itemData['StoryedmIsShownAt']."'>" . $itemData['StoryedmIsShownAt'] . "</a>";
                            } else {
                                $content .= "<span class='meta-p'>" . $itemData['StoryedmIsShownAt'] . "</span>";
                            }
                        $content .= "</div>";
                    }
                    // Story Title
                    $content .= "<div class='single-meta'>";
                        $content .= "<span class='mb-1'>Story Title</span>";
                        $content .= "<span class='meta-p'>". str_replace(' || ', "</br>", $itemData['StorydcTitle']) . "</span>";
                    $content .= "</div>";
                    // Story Landing Page
                    if($itemData['StoryedmLandingPage']) {
                        $content .= "<div class='single-meta'>";
                            $content .= "<span class='mb-1'>Landing Page</span>";
                            if(substr($itemData['StoryedmLandingPage'], 0, 4) == 'http'){
                                $content .= "<span class='meta-p'><a target='_blank' href='".$itemData['StoryedmLandingPage']."'>" . substr($itemData['StoryedmLandingPage'], 0, 25) . "</a></span>";
                            } else {
                                $content .= "<span class='meta-p'>" . $itemData['StoryedmLandingPage'] . "</span>";
                            }
                        $content .= "</div>";
                    }
                   // var_dump($storyData);
                    //var_dump($itemData);
                    // Parent Story
                    if($itemData['StoryParentStory']) {
                        $content .= "<div class='single-meta'>";
                            $content .= "<p class='mb-1'>Parent Story</p>";
                            if(substr($itemData['StoryParentStory'], 0, 4) == 'http'){
                                $content .= "<p class='meta-p'><a target='_blank' href='".$itemData['StoryParentStory']."'>" . $itemData['StoryParentStory'] . "</a></p>";
                            } else {
                                $content .= "<p class='meta-p'>" . $itemData['StoryParentStory'] . "</p>";
                            }
                        $content .= "</div>";
                    }
                $content .= "</div>";
                // Metadata uncollapsed cover
                $content .= "<div class='cover-up'><i class=\"far fa-chevron-double-down\"></i></div>";
            $content .= "</div>";

            // right side
            $content .= "<div id='full-view-r' >";

                $content .= "<div id='transcription-container' style='min-height:575px;'>";
                    $content .= "<div id='startTranscription' style='display:flex;flex-direction:row;justify-content:space-between;' title='click to open editor'>";
                    $content .= "<div style='display:inline-block;'><h5 style='color:#0a72cc;'><i style=\"font-size: 20px;margin-bottom:5px;\" class=\"fa fa-quote-right\" aria-hidden=\"true\"></i> TRANSCRIPTION</h5></div>";
                        if($itemData['TranscriptionStatusColorCode'] == '#61e02f'){
                            $content .= "<div style='display:inline-block;'><span class='completed'>COMPLETED</span>";
                        }
                        elseif($itemData['TranscriptionStatusColorCode'] == '#fff700') {
                            $content .= "<div style='display:inline-block;'><span class='edited'>EDIT</span>";
                        }
                        elseif($itemData['TranscriptionStatusColorCode'] == '#ffc720') {
                            $content .= "<div style='display:inline-block;'><span class='reviewed'>REVIEW</span>";
                        } else {
                            $content .= "<div style='display:inline-block;'><span class='not-started'>NOT STARTED</span>";
                        }
                        $content .= "<i class=\"fa fa-pencil right-i\" aria-hidden=\"true\"></i></div>";
                    $content .= "</div>";
                    if(!str_contains(strtolower($currentTranscription['Text']),'<script>')) {
                        $formattedTranscription = htmlspecialchars_decode($currentTranscription['Text']);
                    }
                    $trLanguage = $currentTranscription['Languages'][0]['Name'];
                    if(strlen($formattedTranscription) < 700) {

                        $content .= "<div style='padding-left:50px;padding-right:20px;padding-top:20px;'>";
                            $content .= $formattedTranscription;
                        $content .= "</div>";

                        if($trLanguage){
                            $content .= "<h6 class='enrich-headers'>Language(s) of Transcription</h6>";
                            $content .= "<div class='language-container'>";
                            foreach($currentTranscription['Languages'] as $trLang) {
                                $content .= "<div class='language-single'>" . $trLang['Name'] . "</div>";
                            }
                                // $content .= "<div class='card-header' style='position:relative;left:-5px;'>".$trLanguage."</div>";
                            $content .= "</div>";
                        }
                        $content .= "</div>";
                    } else {
                    $content .= "<div class='trans-toggle'>";
                        $content .= "<div id='itemTranscription' class='togglePara' style='padding-left:50px;padding-right:20px;height:401px;padding-top:20px;'>".$formattedTranscription."</div>";
                        $content .= "<div id='itemBtn' class=''>Show More</div>";
                        if($trLanguage){
                            $content .= "<h6 class='enrich-headers'>Language(s) of Transcription</h6>";
                            $content .= "<div class='language-container'>";
                            foreach($currentTranscription['Languages'] as $trLang) {
                                $content .= "<div class='language-single'>" . $trLang['Name'] . "</div>";
                            }
                                // $content .= "<div class='card-header' style='position:relative;left:-5px;'>".$trLanguage."</div>";
                            $content .= "</div>";
                        }
                    $content .= "</div>";
                    $content .= "</div>";
                    }
                    // description
                    $content .= "<div id='startDescription' class='enrich-header' style='display:flex;flex-direction:row;justify-content:space-between;margin-top:20px;'>";
                        $content .= "<div style='display:inline-block;'><h5 style='color:#0a72cc;'><i style=\"font-size: 20px;margin-bottom:5px;\" class=\"fa fa-book\" aria-hidden=\"true\"></i> DESCRIPTION</h5></div>";
                        if($itemData['DescriptionStatusColorCode'] == '#61e02f'){
                            $content .= "<div style='display:inline-block;'><span style='display:inline-block;font-weight:500!important;' class='completed'>COMPLETED</span>";
                        }
                        elseif($itemData['DescriptionStatusColorCode'] == '#fff700') {
                            $content .= "<div style='display:inline-block;'><span style='display:inline-block;font-weight:500!important;' class='edited'>EDIT</span>";
                        }
                        elseif($itemData['DescriptionStatusColorCode'] == '#ffc720') {
                            $content .= "<div style='display:inline-block;'><span style='display:inline-block;font-weight:500!important;' class='reviewed'>REVIEW</span>";
                        } else {
                            $content .= "<div style='display:inline-block;'><span style='display:inline-block;font-weight:500!important;' class='not-started'>NOT STARTED</span>";
                        }
                        $content .= "<i class=\"fa fa-pencil right-i\" aria-hidden=\"true\"></i></div>";
                    $content .= "</div>";

                    $content .= "<p style='padding-left:50px;padding-right:20px;'>".$itemData['Description']."</p>";
                    $dcLang = array();
                    foreach($languages as $language){
                        if($itemData['DescriptionLanguage'] == $language['LanguageId']){
                            array_push($dcLang, $language['Name']);
                        }
                    }
                    if(count($dcLang) > 0){
                        $content .= "<h6 class='enrich-headers'>Language(s) of Description</h6>";
                        $content .= "<div class='language-container'>";
                            foreach($dcLang as $lang){
                                $content .= "<div class='language-single'>".$lang."</div>";
                            }
                        $content .= "</div>";
                    }
                    // Location
                    $content .= "<div id='startLocation' class='enrich-header' style='display:flex;flex-direction:row;justify-content:space-between;margin:20px 0;'>";
                    $content .= "<div style='display:inline-block;'><h5 style='color:#0a72cc;'><img src='".home_url()."/wp-content/uploads/location-icon.svg'> LOCATION</h5></div>";
                    if($itemData['LocationStatusColorCode'] == '#61e02f'){
                        $content .= "<div style='display:inline-block;'><span style='display:inline-block;font-weight:500!important;' class='completed'>COMPLETED</span>";
                    }
                    elseif($itemData['LocationStatusColorCode'] == '#fff700') {
                        $content .= "<div style='display:inline-block;'><span style='display:inline-block;font-weight:500!important;' class='edited'>EDIT</span>";
                    }
                    elseif($itemData['LocationStatusColorCode'] == '#ffc720') {
                        $content .= "<div style='display:inline-block;'><span style='display:inline-block;font-weight:500!important;' class='reviewed'>REVIEW</span>";
                    } else {
                        $content .= "<div style='display:inline-block;'><span style='display:inline-block;font-weight:500!important;' class='not-started'>NOT STARTED</span>";
                    }
                    $content .= "<i class=\"fa fa-pencil right-i\" aria-hidden=\"true\"></i></div>";
                    $content .= "</div>";

                    $content .= "<div id='full-view-tagging' class='no-htr-map' style='height:300px;position:relative;margin-bottom:20px;'>";
                       // $content .= $taggingTab;
                       $content .= $mapTab;
                    $content .= "</div>";

                    $content .= "<div class='location-output' style='position:relative;margin-top:20px;'>";
                    foreach($itemData['Places'] as $platz){
                        $content .= "<div class='location-single'>".$platz['Name']." (".$platz['Latitude'].", ".$platz['Longitude'].")</div>";
                    }
                    $content .= "</div>";

                        $content .= "</div>";
                    $content .= "</div>";
                $content .= "</div>";

                $content .= "<div id='full-view-info' style='display:none;'>";
                    $content .= $infoTab;
                $content .= "</div>";
            }
            $content .= "</div>";
        // Splitscreen container
        $content .= "<div id='image-view-container' class='panel-container-horizontal' style='display:none'>";
            // Image section
            $content .= "<div id='item-image-section' class='panel-left'>";
                $content .= '<div id="openseadragonFS">';
                    // Save All at once button
                    $content .= "<div id='save-all-btn'>Save All</div>";
                    $content .= "<div id='save-all-spinner'>Saving...</div>";
                    // Temporary Js
                //    $content .= "<script>
                //         document.querySelector('#save-all-btn').addEventListener('click', () => {
                //             callAll();
                //         }, true);

                //         function callAll() {
                //             document.querySelector('#save-all-spinner').style.display = 'block';
                //             console.log('calling');
                //             updateItemTranscription(".$itemData['ItemId'].", ".get_current_user_id().", \"".$statusTypes[1]['ColorCode']."\", ".sizeof($progressData).");
                //             setTimeout(()=>{saveItemLocation(".$itemData['ItemId'].", ".get_current_user_id().", \"".$statusTypes[1]['ColorCode']."\", ".sizeof($progressData).")},400);
                //             setTimeout(()=>{saveItemDate(".$itemData['ItemId'].", ".get_current_user_id().", \"".$statusTypes[1]['ColorCode']."\", ".sizeof($progressData).")},600);
                //             setTimeout(()=>{savePerson(".$itemData['ItemId'].", ".get_current_user_id().", \"".$statusTypes[1]['ColorCode']."\", ".sizeof($progressData).")},800);
                //             setTimeout(()=>{updateItemDescription(".$itemData['ItemId'].", ".get_current_user_id().", \"".$statusTypes[1]['ColorCode']."\", ".sizeof($progressData).")}, 1300);
                //             setTimeout(()=>{saveKeyword(".htmlspecialchars($itemData['ItemId'], ENT_QUOTES, 'UTF-8').", ".get_current_user_id().", \"".$statusTypes[1]['ColorCode']."\", ".sizeof($progressData).")},1700);
                //             setTimeout(()=>{saveLink(".$itemData['ItemId'].", ".get_current_user_id().", \"".$statusTypes[1]['ColorCode']."\", ".sizeof($progressData).")},1900);
                //             setTimeout(()=>{document.querySelector('#save-all-spinner').style.display = 'none';},2000);
                //        }

                //    </script>";


                    // Temporary css
                    $content .= "<style>
                        #save-all-btn {
                            position: absolute;
                            height: 25px;
                            width: 80px;
                            background: #0a72cc;
                            top: 1%;
                            right: 5%;
                            z-index: 9999;
                            color: white;
                            text-align: center;
                            cursor: pointer;
                        }
                        #save-all-spinner {
                            display: none;
                            position: absolute;
                            height: 150px;
                            width: 250px;
                            background: grey;
                            border: 5px solid #000;
                            text-align: center;
                            padding-top: 50px;
                            font-size: 20px;
                            top: 50%;
                            left: 50%;
                            z-index: 9999;
                        }
                    </style>";
                    // viewer buttons at fullscreen
                    $content .= '<div class="buttons" id="buttonsFS">';
                        $content .= '<div id="zoom-inFS" class="theme-color theme-color-hover"><i class="far fa-plus"></i></div>';
                        $content .= '<div id="zoom-outFS" class="theme-color theme-color-hover"><i class="far fa-minus"></i></div>';
                        $content .= '<div id="homeFS" title="View full image" class="theme-color theme-color-hover"><i class="far fa-home"></i></div>';
                        $content .= '<div id="full-widthFS" title="Fit image width to frame" class="theme-color theme-color-hover"><i class="far fa-arrows-alt-h"></i></div>';
                        $content .= '<div id="rotate-rightFS" class="theme-color theme-color-hover"><i class="far fa-redo"></i></div>';
                        $content .= '<div id="rotate-leftFS" class="theme-color theme-color-hover"><i class="far fa-undo"></i></div>';
                        $content .= '<div id="filterButtonFS" title="Edit image" class="theme-color theme-color-hover"><i class="far fa-sliders-h"></i></div>';
                        $content .= '<div id="full-pageFS" title="Exit full screen" class="theme-color theme-color-hover"><i class="far fa-expand-arrows-alt"></i></div>';
                    $content .= '</div>';
                    $content .= '<div class="buttons new-grid-button" id="buttonsFS">';
                        if($isLoggedIn) {
                            if ($locked) {
                                $content .= '<div id="transcribeLockFS" class="theme-color theme-color-hover"><i class="far fa-lock"></i></div>';
                            }
                            else {
                                $content .= '<div id="transcribeFS"  title="Enrich item" class="theme-color theme-color-hover"><i class="far fa-pen"></i></div>';
                            }
                        } else {
                            $content .= '<div id="transcribe-lockedFS" class="theme-color theme-color-hover"><i class="far fa-pen" id="lock-loginFS"></i></div>';
                        }
                        //$imageViewer .= '<div id="transcribe locked"><i class="far fa-lock" id="lock-login"></i></div>';
                    $content .= '</div>';
                    // Next/Previous Item full screen
                    if($prevItem) {
                        $content .= '<div id="previous-itemFS" title="Previous Item" style="text-align:center;"><a href="'.home_url()."/documents/story/item/?story=". $itemData['StoryId']."&item=". $prevItem .'&fs=true" ><i class="fas fa-step-backward"></i></a></div>';
                    }
                    if($nextItem) {
                        $content .= '<div id="next-itemFS" title="Next Item" style="text-align:center;"><a href="' . home_url()."/documents/story/item/?story=".$itemData['StoryId']."&item=" . $nextItem . '&fs=true" ><i class="fas fa-step-forward"></i></a></div>';
                    }
                $content .= '</div>';
            $content .= "</div>";
            // Resize slider
            $content .= '<div id="item-splitter" class="splitter-vertical"></div>';
            // Info/Transcription section
            $content .= "<div id='item-data-section' class='panel-right'>";
                $content .= "<div id='item-data-header'>";
                    // Tab menu
                    $content .= '<ul id="item-tab-list" class="tab-list">';
                        $content .= "<li>";
                            $content .= "<div id='tr-tab' class='theme-color tablinks active' title='Transcription and Description'
                                            onclick='switchItemTab(event, \"editor-tab\")'>";
                                $content .= '<i class="fa fa-quote-right tab-i"></i>';
                                $content .= "<p class='tab-h'><i class='tab-status fal fa-circle' style='color:".$itemData['TranscriptionStatusColorCode'].";background-color:".$itemData['TranscriptionStatusColorCode'].";'></i>";
                                $content .= "<span ><b> TRANSCRIPTION</b></span></p>";
                            $content .= "</div>";
                        $content .= "</li>";

                        $content .= "<li>";
                            $content .= "<div id='desc-tab' class='theme-color tablinks' title='Description' onclick='switchItemTab(event, \"editor-tab\");'>";
                                $content .= "<i class='fa fa-book tab-i'></i>";
                                $content .= "<p class='tab-h'><i class='tab-status fal fa-circle' style='color:".$itemData['DescriptionStatusColorCode'].";background-color:".$itemData['DescriptionStatusColorCode'].";'></i>";
                                $content .= "<span><b> DESCRIPTION</b></span></p>";
                            $content .= "</div>";
                        $content .= "</li>";

                        $content .= "<li>";
                            $content .= "<div id='loc-tab' class='theme-color tablinks' title='Locations and Tagging'
                                            onclick='switchItemTab(event, \"tagging-tab\");map.resize()'>";
                                    $content .= '<i class="fa fa-map-marker tab-i" style="background-color:#fff;"></i>';
                                    $content .= "<p class='tab-h'><i class='tab-status fal fa-circle' style='color:".$itemData['LocationStatusColorCode'].";background-color:".$itemData['LocationStatusColorCode'].";'></i>";
                                    $content .= "<span><b> LOCATION</b></span></p>";
                            $content .= "</div>";
                        $content .= "</li>";

                        $content .= "<li>";
                            $content .= "<div id='tagi-tab' class='theme-color tablinks' title='Tagging' onclick='switchItemTab(event, \"tag-tab\");'>";
                                $content .= "<i class='fa fa-tag tab-i' aria-hidden='true'></i>";
                                $content .= "<p class='tab-h'><i class='tab-status fal fa-circle' style='color:".$itemData['TaggingStatusColorCode'].";background-color:".$itemData['TaggingStatusColorCode'].";'></i>";
                                $content .= "<span><b> ENRICHMENTS</b></span></p>";
                            $content .= "</div>";
                        $content .= "</li>";

                        $content .= "<li>";
                            $content .= "<div class='theme-color tablinks' title='More Information'
                                            onclick='switchItemTab(event, \"info-tab\")'>";
                                $content .= '<i class="fa fa-info-circle tab-i"></i>';
                                $content .= "<p class='tab-h it'><b> INFO</b></p>";
                            $content .= "</div>";
                        $content .= "</li>";

                        $content .= "<li>";
                            $content .= "<div class='theme-color tablinks' title='Tutorial'
                                            onclick='switchItemTab(event, \"help-tab\")'>";
                                $content .= '<i class="fa fa-question-circle tab-i"></i>';
                                $content .= "<p class='tab-h it'><b> TUTORIAL</b></p>";
                            $content .= "</div>";
                        $content .= "</li>";
                    $content .= '</ul>';
                    // View switcher
                    $content .= '<div class="view-switcher" id="switcher-casephase">';
                        $content .= '<ul id="item-switch-list" class="switch-list" style="position:fixed;z-index:10;top:0;right:0;">';

                            $content .= "<li>";
                                $content .= '<i id="popout" class="far fa-window-restore fa-rotate-180 view-switcher-icons"
                            onclick="switchItemView(event, \'popout\')"></i>';
                            $content .= "</li>";

                            $content .= "<li>";
                                $content .= '<i id="vertical-split" class="far fa-window-maximize fa-rotate-180 view-switcher-icons"
                            onclick="switchItemView(event, \'vertical\')"></i>';
                            $content .= "</li>";

                            $content .= "<li>";
                                $content .= '<i id="horizontal-split" class="far fa-window-maximize fa-rotate-90 view-switcher-icons active theme-color" style="font-size:12px;"
                            onclick="switchItemView(event, \'horizontal\')"></i>';
                            $content .= "</li>";

                            $content .= "<li>";
                                $content .= '<i id="horizontal-split" class="fas fa-window-minimize view-switcher-icons"
                            onclick="switchItemView(event, \'closewindow\')"></i>';
                            $content .= "</li>";

                            $content .= "<li>";
                                $content .= '<i id="close-window-view" class="fas fa-times view-switcher-icons theme-color" onClick="switchItemPageView()"></i>';
                            $content .= "</li>";

                        $content .= '</ul>';
                    $content .= '</div>';
                $content .= "</div>";
                // Tab content
                $content .= "<div id='item-data-content' class='panel-right-tab-menu'>";
                    // Editor tab
                    $content .= "<div id='editor-tab' class='tabcontent'>";
                        // Content will be added here in switchItemPageView function
                    $content .= "</div>";
                    // Image settings tab
                    $content .= "<div id='settings-tab' class='tabcontent' style='display:none;'>";
                        $content .= $imageSettingsTab;
                    $content .= "</div>";
                    // Info tab
                    $content .= "<div id='info-tab' class='tabcontent' style='display:none;'>";
                        $content .= "<p class='theme-color item-page-section-headline'>Additional Information</p>";
                        // Content will be added here in switchItemPageView function
                    $content .= "</div>";
                    // Location tab
                    $content .= "<div id='tagging-tab' class='tabcontent' style='display:none;'>";
                        // Content will be added here in switchItemPageView function
                    $content .= "</div>";
                    // Tag tab
                    $content .= "<div id='tag-tab' class='tabcontent' style='display:none'>";
                        $content .= $taggingTab;
                    $content .= "</div>";
                    // Description tab
                    $content .= "<div id='description-tab' class='tabcontent' style='display:none;'>";
                    $content .= "</div>";
                    // Help tab
                    $content .= "<div id='help-tab' class='tabcontent' style='display:none;'>";
                        $content .= do_shortcode('[tutorial_item_slider]');
                    $content .= "</div>";
                    // Automatic enrichment tab
                    $content .= "<div id='autoEnrichment-tab' class='tabcontent' style='display:none;'>";
                        // Content will be added here in switchItemPageView function
                    $content .= "</div>";
                $content .= "</div>";
            $content .= '</div>';
        // Split screen JavaScript
        $content .= '<script>
                        jQuery("#item-image-section").resizable_split({
                            handleSelector: "#item-splitter",
                            resizeHeight: false
                        });
                        window.onscroll = function() {scrolluFunction()};
                            function scrolluFunction() {
                                if (document.body.scrollTop > 0 || document.documentElement.scrollTop > 0) {
                                    document.getElementById("_transcribathon_partnerlogo").style.height = "56px";
                                    document.getElementById("_transcribathon_partnerlogo").style.width = "56px";
                                    document.getElementById("_transcribathon_partnerlogo").style.marginLeft = "33px";
                                }
                                else {
                                    document.getElementById("_transcribathon_partnerlogo").style.height = "56px";
                                    document.getElementById("_transcribathon_partnerlogo").style.width = "56px";
                                    document.getElementById("_transcribathon_partnerlogo").style.marginLeft = "33px";
                                }
                            }
                    </script>';
        $content .= "</div>
                </div>";

    echo $content;
    }
}
add_shortcode( 'item_page', '_TCT_item_page' );
?>
