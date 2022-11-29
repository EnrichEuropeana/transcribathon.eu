<?php

/*
Shortcode: item_page_htr
Description: Gets item data and builds the item page with htr editor
*/

// include required files
include($_SERVER["DOCUMENT_ROOT"].'/wp-load.php');


require_once(get_stylesheet_directory() . '/htr-client/lib/TranskribusClient.php');
require_once(get_stylesheet_directory() . '/htr-client/config.php');

use FactsAndFiles\Transcribathon\TranskribusClient;

date_default_timezone_set('Europe/Berlin');

function _TCT_compare_transcriptions( $atts) {
    global $ultimatemember, $config;
    if (isset($_GET['item']) && $_GET['item'] != "") {

	      $alpineJs = get_stylesheet_directory_uri(). '/js/alpinejs.3.10.4.min.js';

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
        $itemId = intval($_GET['item']);
        $htrDataJson = $transkribusClient->getDataFromTranscribathon(
            null,
            array(
                'ItemId' => $itemId,
		            'orderBy' => 'LastUpdated',
		            'orderDir' => 'desc'
            )
        );
        // extract the data itself
        $htrDataArray = json_decode($htrDataJson, true);
        $htrData = $htrDataArray['data'][0]['TranscriptionData'];

    // Build required components for the page
    $content = "";

    $content .= '<script>
    // window.onclick = function(event) {
    //     if (event.target.id != "transcription-status-indicator") {
    //         var dropdown = document.getElementById("transcription-status-dropdown");
    //         if (dropdown.classList.contains("show")) {
    //             dropdown.classList.remove("show");
    //         }
    //     }
    //     if (event.target.id != "description-status-indicator") {
    //         var dropdown = document.getElementById("description-status-dropdown");
    //         if (dropdown.classList.contains("show")) {
    //             dropdown.classList.remove("show");
    //         }
    //     }
    //     if (event.target.id != "location-status-indicator") {
    //         var dropdown = document.getElementById("location-status-dropdown");
    //         if (dropdown.classList.contains("show")) {
    //             dropdown.classList.remove("show");
    //         }
    //     }
    //     if (event.target.id != "tagging-status-indicator") {
    //         var dropdown = document.getElementById("tagging-status-dropdown");
    //         if (dropdown.classList.contains("show")) {
    //             dropdown.classList.remove("show");
    //         }
    //     }
    //}
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


    // Transcription History
    $trHistory = "";
    $trHistory .= "<div class='tr-history-section'>";
        $trHistory .= "<div class='item-page-section-headline-container collapse-headline item-page-section-collapse-headline collapse-controller' data-toggle='collapse' href='#transcription-history'
                        onClick='jQuery(this).find(\"collapse-icon\").toggleClass(\"fa-caret-circle-down\")
                        jQuery(this).find(\"collapse-icon\").toggleClass(\"fa-caret-circle-up\")'>";
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

        $trHistory .= "</div>";
    $trHistory .= "</div>";
    // Editor Tab
    $editorTab = "";
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
                                            onclick='changeStatus(" . $_GET['item'] . ", null, \"" . $statusTyp['Name'] . "\", \"TranscriptionStatusId\", " . $statusTyp['CompletionStatusId'] . ", \"" . $statusTyp['ColorCode'] . "\", " . sizeof($progressData) . ", this)'>";
                                    $editorTab .= "<i class='fal fa-circle' style='color:transparent;background-image: -webkit-gradient(linear, left top, left bottom, color-stop(0, " . $statusTyp['ColorCode'] . "), color-stop(1, " . $statusTyp['ColorCodeGradient'] . "));'></i>";
                                    $editorTab .= $statusTyp['Name'];
                                $editorTab .= "</div>";
                            } else {
                                $editorTab .= "<div class='status-dropdown-option'
                                            onclick='changeStatus(" . $_GET['item'] . ", null, \"" . $statusTyp['Name'] . "\", \"TranscriptionStatusId\", " . $statusTyp['CompletionStatusId'] . ", \"" . $statusTyp['ColorCode'] . "\", " . sizeof($progressData) . ", this)'>";
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
            $editorTab .= "<div id='switch-tr-view' style='float:right;'><i class='fa fa-pencil' style='font-size:20px;color:#0a72cc;cursor:pointer;margin-top:5px;'></i></div>";
        $editorTab .= "</div>"; // End of header
        $editorTab .= "<div style='clear:both;'></div>";

        // Editor and Language Selector
        $editorTab .= "<div id='transcription-edit-container' style='display:none;'>";
            // MCE Editor
            $editorTab .= "<div id='mce-wrapper-transcription' class='login-required'>";
                $editorTab .= "<div id='mytoolbar-transcription' style='max-width:500px;'></div>";
                $editorTab .= "<div id='item-page-transcription-text' rows='4'>";
                    if($currentTranscription != null) {
                        $editorTab .= $currentTranscription['Text'];
                    }
                $editorTab .= "</div>";
            $editorTab .= "</div>";

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
                    $editorTab .= "<button disabled class='item-page-save-button language-tooltip' id='transcription-update-button'
                                    onClick='updateItemTranscription(" . $itemData['ItemId'] . ", " . get_current_user_id() . ", \"" . $statusTypes[1]['ColorCode'] . "\", " . sizeof($progressData) . ")'>";
                        $editorTab .= "SAVE"; // Save transcription
                        $editorTab .= "<span class='language-tooltip-text'>Please select a language</span>";
                    $editorTab .= "</button>";

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


                    $editorTab .= "<div id='item-transcription-spinner-container' class='spinner-container spinner-container-right'>";
                        $editorTab .= "<div class='spinner'></div>";
                    $editorTab .= "</div>";

                    $editorTab .= "<div style='clear:both;'></div>";
                $editorTab .= "</div>";
                $editorTab .= "<div style='clear:both;'></div>";
            $editorTab .= "</div>";
        $editorTab .= "</div>"; // End of 'editable' section

        $editorTab .= "<div id='transcription-view-container' style='display:block;'>";
            $editorTab .= "<div id='current-tr-view'>";
                $editorTab .= $currentTranscription['Text'];
            $editorTab .= "</div>";
            $editorTab .= $trHistory;
        $editorTab .= "</div>";

    $editorTab .= "</div>"; // End of Transcription-section


    // Image Slider
    $numOfPhotos = count($itemImages);
    // Get the image of the Current Item
    $startingSlide = array_search($_GET['item'], array_column($itemImages, 'ItemId'));

    $imageSlider = "";
    $imageSlider .= "<div id='img-slider'>";
        // Hidden span to get the current Image
        $imageSlider .= "<span id='slide-start' style='display:none;'>" . $startingSlide . "</span>";

        $imageSlider .= "<div id='slider-container'>";
            // Buttons to go through images
            $imageSlider .= "<button class='prev-slide' type='button'><i class='fas fa-chevron-left'></i></button>";
            $imageSlider .= "<button class='next-slide' type='button'><i class='fas fa-chevron-right'></i></button>";
            // Image container
            $imageSlider .= "<div id='inner-slider'>";
                for($x = 0; $x < $numOfPhotos; $x++) {
                    $sliderImg = json_decode($itemImages[$x]['ImageLink'], true);
                    $dimensions = 0;
                    if($sliderImg['height'] || $sliderImg['width']) {
                        if($sliderImg['width'] <= $sliderImg['height']) {
                            $dimensions = '/0,0,'.$sliderImg["width"].','.$sliderImg["width"];
                        } else {
                            $dimensions = '/0,0,'.$sliderImg["height"].','.$sliderImg["height"];
                        }
                    } else {
                        $dimensions = "/full";
                    }

                    if(substr($sliderImg['service']['@id'],0,4) == 'rhus'){
                        $sliderImgLink ='http://'. str_replace(' ','_',$sliderImg['service']["@id"]) . $dimensions.'/200,200/0/default.jpg';
                    } else {
                        $sliderImgLink = str_replace(' ','_',$sliderImg['service']["@id"]) . $dimensions.'/200,200/0/default.jpg';
                    }

                    $imageSlider .= "<div class='slide-sticker' data-value='" . ($x + 1) . "'>";
                    if($x == $startingSlide) {
                        $imageSlider .= "<div class='slide-img-wrap active'>";
                    } else {
                        $imageSlider .= "<div class='slide-img-wrap'>";
                    }
                            $imageSlider .= "<a href='" . home_url() . "/documents/compare-transcriptions/?story=" . $itemData['StoryId'] . "&item=" . $itemImages[$x]['ItemId'] . "'>";
                                $imageSlider .= "<img src='" . $sliderImgLink . "' class='slider-image' alt='slider-img-" . ($x + 1) . "' width='200' height='200' loading='lazy'>";
                            $imageSlider .= "</a>";
                            $imageSlider .= "<div class='image-completion-status' style='bottom:20px;border-color:" . $itemImages[$x]['CompletionStatusColorCode'] . ";'></div>";
                        $imageSlider .= "</div>";
                        $imageSlider .= "<div class='slide-number-wrap'>" . ($x + 1) . "</div>";
                    $imageSlider .= "</div>";
                }


            $imageSlider .= "</div>";
        $imageSlider .= "</div>";

        // Slider dots and numbers
        $imageSlider .= "<div id='dot-indicators'></div>";

        $imageSlider .= "<div id='controls-div'>";
            $imageSlider .= "<div class='num-indicators' style='display:none;'>";
                $imageSlider .= "<span id='left-num'>1</span> - <span id='right-num'></span> of <span>" . $numOfPhotos . "</span>";
            $imageSlider .= "</div>";
        $imageSlider .= "</div>";

        //$imageSlider .= "<div class='back-to-story'><a href='" . home_url() . "/documents/story/?story=" . $itemData['StoryId'] . "'><i class='fas fa-arrow-left' style='margin-right:7.5px;'></i> Back to the Story </a></div>";
    $imageSlider .= "</div>"; // End of Image Slider


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
    $content .= "<section id='image-slider-section' style='padding:0;'>";
        $content .= $imageSlider;
    $content .= "</section>";

        // Title
    $content .= "<section id='title-n-progress'>";
    $content .= "<div class='back-to-story'><a href='" . home_url() . "/documents/story/?story=" . $itemData['StoryId'] . "'><i class='fas fa-arrow-left' style='margin-right:7.5px;'></i> Back to the Story </a></div>";
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
            $content .= "<div id='full-view-l'>";
                $content .= "<div id='htr-container' style='height:600px;'>";
                    $content .= "<div id='startHtrTranscription' style='display:flex;flex-direction:row;justify-content:space-between;cursor:pointer;padding-left:0;padding-right:0;' title='click to open editor'>";
                        $content .= "<div style='display:inline-block;'><h5 style='color:#0a72cc;'><i style=\"font-size: 20px;margin-bottom:5px;\" class=\"fa fa-quote-right\" aria-hidden=\"true\"></i> HTR TRANSCRIPTION</h5></div>";
                        $content .= "<div>";
                            $content .= "<div id='htr-status' class='status-display' style='background-color:#fff700;'>";
                                $content .= "<span class='status-indicator-view' style='bottom:0px;'>EDIT</span>";
                            $content .= "</div>";
                            $content .= "<i class=\"fa fa-pencil right-i\" aria-hidden=\"true\"></i>";
                        $content .= "</div>";
                    $content .= "</div>";
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

            $content .= "<div id='full-view-r'>";
            //var_dump($itemData);
                // Transcription
                $content .= "<div id='transcription-container' style='height:600px;'>";
                    $content .= "<div id='startTranscription' style='display:flex;flex-direction:row;justify-content:space-between;cursor:pointer;padding-left:0;padding-right:0;' title='click to open editor'>";
                        $content .= "<div style='display:inline-block;'><h5 style='color:#0a72cc;'><i style=\"font-size: 20px;margin-bottom:5px;\" class=\"fa fa-quote-right\" aria-hidden=\"true\"></i> TRANSCRIPTION</h5></div>";
                        $content .= "<div>";
                            $content .= "<div class='status-display' style='background-color:".$itemData['TranscriptionStatusColorCode']."'>";
                                $content .= "<span class='status-indicator-view' style='bottom:0px;'>" . $itemData['TranscriptionStatusName'] . "</span>";
                            $content .= "</div>";
                            $content .= "<i class=\"fa fa-pencil right-i\" aria-hidden=\"true\"></i>";
                        $content .= "</div>";
                    $content .= "</div>";
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

                            // $content .= "<div class='transcription-language'>";
                            //     $content .= "<h6 class='enrich-headers'> Language(s) of Transcription </h6>";
                            //     $content .= "<div style='padding-left:24px;'>";
                            //     foreach($currentTranscription['Languages'] as $language) {
                            //         $content .= "<div class='language-single'>" . $language['Name'] . "</div>";
                            //     }
                            // $content .= "</div>";
                        } else if(strlen($formattedTranscription) != 0) {
                            $content .= "<div class='current-transcription' style='padding-left:24px;'>";
                                $content .= $formattedTranscription;
                            $content .= "</div>";
                          //  $content .= "<div id='transcription-collapse-btn'> Show More </div>";

                            // $content .= "<div class='transcription-language'>";
                            //     $content .= "<h6 class='enrich-headers'> Language(s) of Transcription </h6>";
                            //     $content .= "<div style='padding-left:24px;'>";
                            //     foreach($currentTranscription['Languages'] as $language) {
                            //         $content .= "<div class='language-single'>" . $language['Name'] . "</div>";
                            //     }
                            // $content .= "</div>";
                        } else {
                            $content .= "<div id='no-text-placeholder'>";
                                $content .= "<p style='position:relative;top:40%;'><img src='".home_url()."/wp-content/themes/transcribathon/images/pen_in_circle.svg'><span>START TRANSCRIPTION</span></p>";
                            $content .= "</div></div>";
                        }
                    }
                $content .= "</div>";

            $content .= "</div>"; // end of transcription
        $content .= "</div>";
    $content .= "</section>";
    $content .= "<div style='clear:both;'></div>";

    if(strlen($formattedTranscription) > 700) {
        $content .= "<div id='transcription-collapse-btn' style='font-size:17px;font-weight:600;height:35px;width:150px;margin: 50px auto;background-color:#0a72cc;color:#fff;padding-top:7px;'> Show More </div>";
    }


    // $content .= "<section id='location-n-enrichments' style='background-color:#f8f8f8;'>";
                // Location and Enrichments placeholder
    //     $content .= "</section>";
        $content .= "<div style='clear:both;'></div>";

        // $content .= "<section id='story-info' class='collapsed' style='height:325px;'>";
               // Metadata placeholder
        // $content .= "</section>";

        $content .= "<div id='image-view-container' class='panel-container-horizontal' style='display:none;overflow:hidden;'>";
            // Image Section
            $content .= "<div id='item-image-section' class='panel-left'>";

            $content .= "</div>";
            // Splitter
            $content .= "<div id='item-splitter' class='splitter-vertical'></div>";
            // Data Section
            $content .= "<div id='item-data-section' class='panel-right'>";
                $content .= "<div id='item-data-header'>";
                    $content .= "<div class='back-to-story'><a href='".home_url()."/documents/story?story=".$itemData['StoryId']."'><i class=\"fas fa-arrow-left\" style='margin-right:7.5px;'></i> Back to the Story</a></div>";

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
                                $content .= '<div class="switch-i"><i id="horizontal-split" class="fas fa-window-minimize view-switcher-icons" style="position:relative;bottom:3px;"
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
                            $content .= "<div id='tr-tab' class='theme-color tablinks active' title='Transcription and Description'
                                            onclick='switchItemTab(event, \"editor-tab\")'>";
                                $content .= '<i class="fa fa-quote-right tab-i"></i>';
                                $content .= "<p class='tab-h'><i class='tab-status fal fa-circle' style='color:".$itemData['TranscriptionStatusColorCode'].";background-color:".$itemData['TranscriptionStatusColorCode'].";'></i>";
                                $content .= "<span ><b> TRANSCRIPTION</b></span></p>";
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
                    $content .= "</div>";

                $content .= "</div>";


            $content .= "</div>";

        $content .= "</div>";



        $content .= "</div>"; // end of full-view-container
    $content .= "<style>
    #primary-full-width { padding: unset!important;}
    .highlight { color: #0a72cc;outline: 0.8px solid;}
    metadata {
        display: none;
    }
    </style>";

    //$content .= "</section>"; // End of main section

    $content .= "<script src='{$alpineJs}'></script>";

    return $content;
    }
}

add_shortcode( 'compare_transcriptions', '_TCT_compare_transcriptions' );

?>
