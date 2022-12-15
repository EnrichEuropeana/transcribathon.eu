<?php
/*
Shortcode: item_page_test
Description: Gets item data and builds the item page
*/

// include required files
include($_SERVER["DOCUMENT_ROOT"].'/wp-load.php');



function _TCT_item_page_test( $atts ) {
    if (isset($_GET['item']) && $_GET['item'] != "") {
        // Set request parameters for image data
        $requestData = array(
            'key' => 'testKey'
        );
        $url = TP_API_HOST."/tp-api/items/?ItemId=".$_GET['item'];
        $requestType = "GET";

        // Execude http request
        include dirname(__FILE__)."/../custom_scripts/send_api_request.php";

        // Save image data
        $imageData = json_decode($result, true);

        if ($itemData['StoryId'] != null) {
            // Set request parameters for story data
            $url = TP_API_HOST."/tp-api/stories/".$itemData['StoryId'];
            $requestType = "GET";

            // Execude http request
            include dirname(__FILE__)."/../custom_scripts/send_api_request.php";

            // Save story data
            $storyData = json_decode($result, true);
            $storyData = $storyData[0];
         }

        // Build Item page content
        $content = "";

        // Image viewer
        //$imageViewer = "";
        //    $imageViewer .= "<img src='".$imageData['ImageLink']."'>";
        // Editor tab
        $editorTab = "";
            $currentStatus = $imageData['CompletionStatusId'];
            $statusList = array(
                            1 => "Not Started",
                            2 => "Edit",
                            3 => "Review",
                            4 => "Complete"
                        );
            $editorTab .=     '<div class="dropdown">';
            $editorTab .= '<i class="fal fa-shield-check theme-color-background theme-color-hover tablinks dropbtn"
            onclick="document.getElementById(\'myDropdown\').classList.toggle(\'show\')"></i>';
                $editorTab .=     '<div id="myDropdown" class="submenu dropdown-content">';
                    foreach ($statusList as $statusId => $statusName) {
                        if ($currentStatus != $statusId) {
                            $editorTab .= "<a href=''><i class='far fa-circle icon-str'></i>".$statusName."</a>";
                        } else {
                            $editorTab .= "<a href=''><i class='far fa-circle icon-str'></i>".$statusName."</a>";
                        }
                    }
                $editorTab .=     '</div>';
            $editorTab .=     '</div>';

            // Transcription status switcher
            $currentStatus = $imageData['CompletionStatusId'];
            $editorTab .= "<div id='status-editor'>";
                $editorTab .= "<select id='status-selection'>";
                    $statusList = array(
                                    1 => "Not Started",
                                    2 => "Edit",
                                    3 => "Review",
                                    4 => "Complete"
                                );
                    foreach ($statusList as $statusId => $statusName) {
                        if ($currentStatus != $statusId) {
                            $editorTab .= "<option value=".$statusId.">".$statusName."</option>";
                        } else {
                            $editorTab .= "<option selected value=".$statusId.">".$statusName."</option>";
                        }
                    }
                $editorTab .= "</select>\n";
                $editorTab .= "<button id='status-update-button' onClick='updateItemStatus(".$imageData['ItemId'].")'>";
                    $editorTab .= "Change status";
                $editorTab .= "</button>";
                $editorTab .= "<span id='status-update-message'>";
                $editorTab .= "</span>";
            $editorTab .= "</div>";

            // Current transcription
            $editorTab .= "<h4 class='theme-color item-page-section-headline'>TRANSCRIPTION</h4>";

            $currentTranscription = "";
            $transcriptionList = [];
            foreach ($imageData["Transcriptions"] as $transcription) {
                if ($transcription['CurrentVersion'] == "1") {
                    $currentTranscription = $transcription['Text'];
                }
                else {
                    array_push($transcriptionList, $transcription);
                }
            }
            $editorTab .= '<p id="item-page-current-transcription">';
            $editorTab .= $currentTranscription;
            $editorTab .= '</p>';

            // Description
            $editorTab .= "<h5 class='theme-color item-page-section-headline'>";
                $editorTab .= "Description";
            $editorTab .= "</h5>";
            $editorTab .= do_shortcode('[ultimatemember form_id="38"]')."test";
            $editorTab .= '<textarea id="item-page-description-text" rows="4">';
                $editorTab .= $imageData['Description'];
            $editorTab .= '</textarea>';
            $editorTab .= "<button id='description-update-button' onClick='updateItemDescription(".$imageData['ItemId'].")'>";
                $editorTab .= "Save description";
            $editorTab .= "</button>";
            $editorTab .= "<span id='description-update-message'>";
            $editorTab .= "</span>";

            // Transcription history
            $editorTab .= '<h4 class="theme-color item-page-section-headline">TRANSCRIPTION HISTORY</h4></br>';
            $i = 0;
            foreach ($transcriptionList as $transcription) {
                $editorTab .= '<button type="button" class="trancription-toggle" data-toggle="collapse" data-target="#transcription-'.$i.'">';
                    $editorTab .= $transcription["Timestamp"];
                $editorTab .= '</button>';
                    $editorTab .= '<div id="transcription-'.$i.'" class="collapse transcription-history-collapse-content">';
                        $editorTab .= '<p id="item-page-current-transcription">';
                            $editorTab .= $transcription['Text'];
                        $editorTab .= '</p>';
                        $editorTab .= '<input class="transcription-comparison-button" type="button"
                                            onClick="compareTranscription(\''.$transcriptionList[$i]['Text'].'\', \''.$currentTranscription.'\','.$i.')"
                                            value="Compare to current transcription">';
                        $editorTab .= '<p id="transcription-comparison-output-'.$i.'" class="transcription-comparison-output">';
                        $editorTab .= '</p>';
                    $editorTab .= '</div>';
                $i++;
            }


        // Image settings tab
        $imageSettingsTab = "";
            $imageSettingsTab .= "<p class='theme-color item-page-section-headline'>ADVANCED IMAGE SETTINGS</p>";

        // Info tab
        $infoTab = "";
            $infoTab .= "<h4 class='theme-color item-page-section-headline'>";
                $infoTab .= "Title: ".$imageData['Title'];
            $infoTab .= "</h4>";
            $infoTab .= "<p class='item-page-property-value'>";
                $infoTab .= $imageData['Description'];
            $infoTab .= "</p>";

            $infoTab .= "<h5 class='theme-color item-page-property-headline'>";
                $infoTab .= "People";
            $infoTab .= "</h5>";
            $infoTab .= "<p class='item-page-property'>";
                $infoTab .= "<span class='item-page-property-key'>";
                    $infoTab .= "Contributor: ";
                $infoTab .= "</span>";
                $infoTab .= "<span class='item-page-property-value'>";
                    $infoTab .= $imageData['Contributor'];
                $infoTab .= "</span>";
            $infoTab .= "</p>";
            $infoTab .= "<p class='item-page-property'>";
                $infoTab .= "<span class='item-page-property-key'>";
                    $infoTab .= "Subject: ";
                $infoTab .= "</span>";
                $infoTab .= "<span class='item-page-property-value'>";
                    $infoTab .= $imageData['StoryPlaceName'];
                $infoTab .= "</span>";
            $infoTab .= "</p>";

            $infoTab .= "<h5 class='theme-color item-page-property-headline'>";
                $infoTab .= "Classifications";
            $infoTab .= "</h5>";
            $infoTab .= "<p class='item-page-property'>";
                $infoTab .= "<span class='item-page-property-key'>";
                    $infoTab .= "Type: ";
                $infoTab .= "</span>";
                $infoTab .= "<span class='item-page-property-value'>";
                    $infoTab .= $imageData['Title'];
                $infoTab .= "</span>";
            $infoTab .= "</p>";
            $infoTab .= "<p class='item-page-property'>";
                $infoTab .= "<span class='item-page-property-key'>";
                    $infoTab .= "Subject: ";
                $infoTab .= "</span>";
                $infoTab .= "<span class='item-page-property-value'>";
                    $infoTab .= $imageData['StoryPlaceName'];
                $infoTab .= "</span>";
            $infoTab .= "</p>";

            $infoTab .= "<h5 class='theme-color item-page-property-headline'>";
                $infoTab .= "PROPERTIES";
            $infoTab .= "</h5>";
            $infoTab .= "<p class='item-page-property'>";
                $infoTab .= "<span class='item-page-property-key'>";
                    $infoTab .= "Language: ";
                $infoTab .= "</span>";
                $infoTab .= "<span class='item-page-property-value'>";
                    $infoTab .= $imageData['Languauges'][0];
                $infoTab .= "</span>";
            $infoTab .= "</p>";
            $infoTab .= "<p class='item-page-property'>";
                $infoTab .= "<span class='item-page-property-key'>";
                    $infoTab .= "Keyword: ";
                $infoTab .= "</span>";
                $infoTab .= "<span class='item-page-property-value'>";
                    $infoTab .= $imageData['SearchText'];
                $infoTab .= "</span></br>";
            $infoTab .= "</p>";
            $infoTab .= "<p class='item-page-property'>";
                $infoTab .= "<span class='item-page-property-key'>";
                    $infoTab .= "Link: ";
                $infoTab .= "</span>";
                $infoTab .= "<span class='item-page-property-value'>";
                    $infoTab .= $imageData['Link'];
                $infoTab .= "</span></br>";
            $infoTab .= "</p>";
            $infoTab .= "<p class='item-page-property'>";
                $infoTab .= "<span class='item-page-property-key'>";
                    $infoTab .= "Category: ";
                $infoTab .= "</span>";
                $infoTab .= "<span class='item-page-property-value'>";
                    $infoTab .= $imageData['Title'];
                $infoTab .= "</span></br>";
            $infoTab .= "</p>";

            // Just filler content for now, to make the size realistic
            $infoTab .= "<p class='theme-color item-page-property-headline'>Time</p>";
            $infoTab .= "<span class='item-page-property-key'>Creation date: </span>";
            $infoTab .= "<span class='item-page-property-value'>".$imageData['Timestamp']."</span></br>";

            $infoTab .= "<p class='theme-color item-page-property-headline'>Provenanace</p>";
            $infoTab .= "<span class='item-page-property-key'>Source: </span>";
            $infoTab .= "<span class='item-page-property-value'>".$imageData['Title']."</span></br>";
            $infoTab .= "<span class='item-page-property-key'>Provenance: </span>";
            $infoTab .= "<span class='item-page-property-value'>".$imageData['Title']."</span></br>";
            $infoTab .= "<span class='item-page-property-key'>Identifier: </span>";
            $infoTab .= "<span class='item-page-property-value'>".$imageData['Title']."</span></br>";
            $infoTab .= "<span class='item-page-property-key'>Institution: </span>";
            $infoTab .= "<span class='item-page-property-value'>".$imageData['Title']."</span></br>";
            $infoTab .= "<span class='item-page-property-key'>Provider: </span>";
            $infoTab .= "<span class='item-page-property-value'>".$imageData['Title']."</span></br>";
            $infoTab .= "<span class='item-page-property-key'>Providing country: </span>";
            $infoTab .= "<span class='item-page-property-value'>".$imageData['Title']."</span></br>";
            $infoTab .= "<span class='item-page-property-key'>First published in Europeana: </span>";
            $infoTab .= "<span class='item-page-property-value'>".$imageData['DateStart']."</span></br>";
            $infoTab .= "<span class='item-page-property-key'>Last updated in Europeana: </span>";
            $infoTab .= "<span class='item-page-property-value'>".$imageData['DateEnd']."</span></br>";

            $infoTab .= "<p class='theme-color item-page-property-headline'>References and relations</p>";
            $infoTab .= "<span class='item-page-property-key'>Location: </span>";
            $infoTab .= "<span class='item-page-property-value'>".$imageData['TranscriptionId']."</span></br>";

            $infoTab .= "<p class='theme-color item-page-property-headline'>Location</p>";
            $infoTab .= "<span class='item-page-property-key'>Dataset: </span>";
            $infoTab .= "<span class='item-page-property-value'>".$imageData['PlaceId']."</span></br>";

            $infoTab .= "<p class='theme-color item-page-property-headline'>Entities</p>";
            $infoTab .= "<span class='item-page-property-key'>Concept term: </span>";
            $infoTab .= "<span class='item-page-property-value'>".$imageData['ImageLink']."</span></br>";

        // Tagging tab
        $taggingTab = "";
            $taggingTab .= "<p>test... tags tab</p>";

        // Help tab
        $helpTab = "";
            $helpTab .= "<p>test... help tab</p>";

        // Automatic enrichment tab
        $autoEnrichmentTab = "";
            $autoEnrichmentTab .= "<p>test... automatic enrichment tab</p>";

        // Comment section
        $commentSection = "";
            $commentSection .= "<h2 class=\"theme-color-background comments-head\">";
                $commentSection .= "Notes and questions";
            $commentSection .= "</h2>";
            $commentSection .= "<div id=\"single-comments-wrapper\">";
                $commentSection .= "<div id=\"comments\" class=\"comments-area\">";
                    $commentSection .= "<div id=\"respond\" class=\"comment-respond\">";
                        $commentSection .= "<h3 id=\"reply-title\" class=\"comment-reply-title\">";
                            $commentSection .= "Leave a note or a question";
                            $commentSection .= "<small><a rel=\"nofollow\" id=\"cancel-comment-reply-link\" href=\"/en/documents/id-19044/item-223349/#respond\" style=\"display:none;\">";
                                $commentSection .= "Cancel reply";
                            $commentSection .= "</a></small>";
                        $commentSection .= "</h3>";
                        $commentSection .= "<form action=\"https://transcribathon.com/wp-comments-post.php\" method=\"post\" id=\"commentform\" class=\"comment-form\">";
                            $commentSection .= "<p class=\"logged-in-as\">";
                                $commentSection .= "<a href=\"https://transcribathon.com/wp-admin/profile.php\" aria-label=\"Logged in as ".wp_get_current_user()->display_name.". Edit your profile.\">";
                                    $commentSection .= "Logged in as ".wp_get_current_user()->display_name."";
                                $commentSection .= "</a>.";
                                $commentSection .= "<a href=\"".wp_logout_url(network_home_url())."\">";
                                    $commentSection .= "Log out?";
                                $commentSection .= "</a>";
                            $commentSection .= "</p>";
                            $commentSection .= "<textarea id=\"comment\" name=\"comment\" aria-required=\"true\">";
                            $commentSection .= "</textarea>";
                            $commentSection .= "<input name=\"wpml_language_code\" type=\"hidden\" value=\"en\" />";
                            $commentSection .= "<p class=\"form-submit\">";
                                $commentSection .= "<input name=\"submit\" type=\"submit\" id=\"submit\" class=\"submit\" value=\"Save note\" />";
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
            $commentSection .= "</div>";

        // View switcher button
        $content .= "<button id='item-page-switcher' onclick='switchItemPageView()'>switch</button>";

        // <<< FULL VIEW >>> //

        $content .= "<div id='full-view-container'>";
            // Top image slider
            $content .= "<div class='item-page-slider'>";

            foreach ($storyData['Items'] as $item) {
                $content .= "<div><img data-lazy='".$item['ImageLink']."'></div>";
            }
            $content .= "<div><img data-lazy='https://transcribathon.com/wp-content/uploads/document-images/21795.258363.full-150x150.jpg'></div>
            <div><img data-lazy='https://transcribathon.com/wp-content/uploads/document-images/21795.258364.full-150x150.jpg'></div>
            <div><img data-lazy='https://transcribathon.com/wp-content/uploads/document-images/21795.258365.full-150x150.jpg'></div>
            <div><img data-lazy='https://transcribathon.com/wp-content/uploads/document-images/21795.258366.full-150x150.jpg'></div>
            <div><img data-lazy='https://transcribathon.com/wp-content/uploads/document-images/21795.258367.full-150x150.jpg'></div>
            <div><img data-lazy='https://transcribathon.com/wp-content/uploads/document-images/21795.258368.full-150x150.jpg'></div>
            <div><img data-lazy='https://transcribathon.com/wp-content/uploads/document-images/21795.258369.full-150x150.jpg'></div>
            <div><img data-lazy='https://transcribathon.com/wp-content/uploads/document-images/21795.258370.full-150x150.jpg'></div>
            <div><img data-lazy='https://transcribathon.com/wp-content/uploads/document-images/21795.258371.full-150x150.jpg'></div>

            <div><img data-lazy='https://iiif.onb.ac.at/images/ANNO/fug15840321/z116567901_00000162/square/150,150/0/default.jpg'></div>
            <div><img data-lazy='https://iiif.onb.ac.at/images/ANNO/fug15840321/z116567901_00000162/square/150,150/0/default.jpg'></div>
            <div><img data-lazy='https://iiif.onb.ac.at/images/ANNO/fug15840321/z116567901_00000162/square/150,150/0/default.jpg'></div>
            <div><img data-lazy='https://iiif.onb.ac.at/images/ANNO/fug15840321/z116567901_00000162/square/150,150/0/default.jpg'></div>
            <div><img data-lazy='https://iiif.onb.ac.at/images/ANNO/fug15840321/z116567901_00000162/square/150,150/0/default.jpg'></div>
            <div><img data-lazy='https://iiif.onb.ac.at/images/ANNO/fug15840321/z116567901_00000162/square/150,150/0/default.jpg'></div>
            <div><img data-lazy='https://iiif.onb.ac.at/images/ANNO/fug15840321/z116567901_00000162/square/150,150/0/default.jpg'></div>
            <div><img data-lazy='https://iiif.onb.ac.at/images/ANNO/fug15840321/z116567901_00000162/square/150,150/0/default.jpg'></div>
            <div><img data-lazy='https://iiif.onb.ac.at/images/ANNO/fug15840321/z116567901_00000162/square/150,150/0/default.jpg'></div>
            <div><img data-lazy='https://iiif.onb.ac.at/images/ANNO/fug15840321/z116567901_00000162/square/150,150/0/default.jpg'></div>
            <div><img data-lazy='https://iiif.onb.ac.at/images/ANNO/fug15840321/z116567901_00000162/square/150,150/0/default.jpg'></div>
            <div><img data-lazy='https://iiif.onb.ac.at/images/ANNO/fug15840321/z116567901_00000162/square/150,150/0/default.jpg'></div>
            <div><img data-lazy='https://iiif.onb.ac.at/images/ANNO/fug15840321/z116567901_00000162/square/150,150/0/default.jpg'></div>
            <div><img data-lazy='https://iiif.onb.ac.at/images/ANNO/fug15840321/z116567901_00000162/square/150,150/0/default.jpg'></div>
            <div><img data-lazy='https://iiif.onb.ac.at/images/ANNO/fug15840321/z116567901_00000162/square/150,150/0/default.jpg'></div>
            <div><img data-lazy='https://iiif.onb.ac.at/images/ANNO/fug15840321/z116567901_00000162/square/150,150/0/default.jpg'></div>";
            $content .= "</div>";

// Image slider JavaScript
$infinite = "true";
/* var_dump(sizeof($storyData['Items'])); */
if (sizeof($storyData['Items']) > 100) {
    $infinite = "false";
}

$content .= "<script>
            jQuery(document).ready(function(){
                jQuery('.item-page-slider').slick({
                    dots: true,
                    infinite: ".$infinite.",
                    arrows: false,
                    speed: 300,
                    slidesToShow: 10,
                    slidesToScroll: 10,
                    lazyLoad: 'ondemand',
                    responsive: [
                        {
                            breakpoint: 1420,
                            settings: { slidesToShow: 9, slidesToScroll: 9, }
                        },
                        {
                            breakpoint: 1270,
                            settings: { slidesToShow: 8, slidesToScroll: 8, }
                        },
                        {
                            breakpoint: 1120,
                            settings: { slidesToShow: 7, slidesToScroll: 7, }
                        },
                        {
                            breakpoint: 970,
                            settings: { slidesToShow: 6, slidesToScroll: 6, }
                        },
                        {
                            breakpoint: 820,
                            settings: { slidesToShow: 5, slidesToScroll: 5, }
                        },
                        {
                            breakpoint: 670,
                            settings: { slidesToShow: 4, slidesToScroll: 4, }
                        },
                        {
                            breakpoint: 520,
                            settings: { slidesToShow: 3, slidesToScroll: 3, }
                        },
                    ]
                });
            });
        </script>";

            $content .= "<div id='full-view-left'>";
                $content .= $imageViewer;
                $content .= "<div id='full-view-editor'>";
                    $content .= $editorTab;
                $content .= "</div>";
            $content .= "</div>";
            $content .= "<div id='full-view-right'>";
                $content .= "<div id='full-view-tagging'>";
                    $content .= $taggingTab;
                $content .= "</div>";
                $content .= "<hr>";
                $content .= '<div class="panel panel-default">';
                    $content .= '<div class="panel-heading clickable" data-toggle="collapse" href="#info-collapsable">';
                        $content .= '<h4 id="info-collapse-heading" class="theme-color item-page-section-headline panel-title">';
                            $content .= 'DOCUMENT META DATA';
                        $content .= '</h4>';
                        $content .= '<i class="fa fa-angle-down" style="font-size: 20px; float:right;"></i>';
                    $content .= '</div>';
                    $content .= '<div id="info-collapsable" class="panel-body panel-collapse collapse">';
                        $content .= "<div id='full-view-info'>";
                            $content .= $infoTab;
                        $content .= "</div>";
                    $content .= "</div>";
                $content .= "</div>";
                $content .= "<hr>";
                $content .= "<div id='full-view-help'>";
                    $content .= $helpTab;
                $content .= "</div>";
                $content .= "<hr>";
                $content .= "<div id='full-view-autoEnrichment'>";
                    $content .= $autoEnrichmentTab;
                $content .= "</div>";
                $content .= "<hr>";
                $content .= "<div id='full-view-comment'>";
                    $content .= $commentSection;
                $content .= "</div>";
            $content .= "</div>";
        $content .= "</div>";


        // Splitscreen container
        $content .= "<div id='image-view-container' class='panel-container-horizontal' style='display:none'>";

            // Image section
            $content .= "<div id='item-image-section' class='panel-left'>
                            <img src='".$imageData['ImageLink']."'>
                        </div>";

            // Resize slider
            $content .= "<div id='item-splitter' class='splitter-vertical'>
                        </div>";

            // Info/Transcription section
            $content .= "<div id='item-data-section' class='panel-right'>";
                $content .= "<div id='item-data-header'>";
                    // Tab menu
                    $content .= '<ul id="item-tab-list" class="tab-list">';
                    $content .= "<li>";
                        $content .= '<i class="far fa-pencil theme-color theme-color-hover tablinks active"
                                    onclick="switchItemTab(event, \'editor-tab\')"></i>';
                    $content .= "</li>";

                    $content .= "<li>";
                        $content .= '<i class="far fa-globe theme-color theme-color-hover tablinks"
                                    onclick="switchItemTab(event, \'tagging-tab\')"></i>';
                    $content .= "</li>";

                    $content .= "<li>";
                        $content .= '<i class="far fa-info-circle theme-color theme-color-hover tablinks"
                                    onclick="switchItemTab(event, \'info-tab\')"></i>';
                    $content .= "</li>";

                    $content .= "<li>";
                        $content .= '<i id="item-tab-laptop" class="far fa-laptop theme-color theme-color-hover tablinks"
                                    onclick="switchItemTab(event, \'autoEnrichment-tab\')"></i>';
                    $content .= "</li>";

                    $content .= "<li>";
                        $content .= '<i class="far fa-sliders-h theme-color theme-color-hover tablinks"
                                    onclick="switchItemTab(event, \'settings-tab\')"></i>';
                    $content .= "</li>";

                    $content .= "<li>";
                        $content .= '<i class="far fa-question-circle theme-color-hover theme-color tablinks"
                                    onclick="switchItemTab(event, \'help-tab\')"></i>';
                    $content .= "</li>";

                    $content .= '</ul>';

                    // View switcher
                    $content .= '<div class="view-switcher">';
                        $content .= '<ul id="item-switch-list" class="switch-list">';

                            $content .= "<li>";
                                $content .= '<i id="horizontal-split" class="fas fa-window-maximize fa-rotate-270 theme-color-hover theme-color view-switcher-icons active"
                            onclick="switchItemView(event, \'horizontal\')"></i>';
                            $content .= "</li>";

                            $content .= "<li>";
                                $content .= '<i id="vertical-split" class="fas fa-window-maximize theme-color-hover theme-color view-switcher-icons"
                            onclick="switchItemView(event, \'vertical\')"></i>';
                            $content .= "</li>";

                            $content .= "<li>";
                                $content .= '<i id="popout" class="far fa-expand-arrows theme-color-hover theme-color view-switcher-icons"
                            onclick="switchItemView(event, \'popout\')"></i>';
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
                        $content .= "<p class='theme-color item-page-section-headline'>DOCUMENT META DATA</p>";
                        // Content will be added here in switchItemPageView function
                    $content .= "</div>";

                    // Tagging tab
                    $content .= "<div id='tagging-tab' class='tabcontent' style='display:none;'>";
                        // Content will be added here in switchItemPageView function
                    $content .= "</div>";

                    // Help tab
                    $content .= "<div id='help-tab' class='tabcontent' style='display:none;'>";
                        // Content will be added here in switchItemPageView function
                    $content .= "</div>";

                    // Automatic enrichment tab
                    $content .= "<div id='autoEnrichment-tab' class='tabcontent' style='display:none;'>";
                        // Content will be added here in switchItemPageView function
                    $content .= "</div>";

                $content .= "</div>";
            $content .= '</div>
                    </div>';

        // Split screen JavaScript
        $content .= '<script>
                        jQuery("#item-image-section").resizable({
                            handleSelector: "#item-splitter",
                            resizeHeight: false
                        });
                    </script>';

        $content .= "</div>
                </div>";
        echo $content;
    }
}
add_shortcode( 'item_page_test', '_TCT_item_page_test' );
?>
