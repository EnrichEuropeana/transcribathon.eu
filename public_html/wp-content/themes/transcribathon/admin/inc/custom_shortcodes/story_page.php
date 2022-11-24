<?php

// include required files
include($_SERVER["DOCUMENT_ROOT"].'/wp-load.php');

// get Document data from API
function _TCT_get_document_data( $atts ) {
    //include theme directory for text hovering
    $theme_sets = get_theme_mods();

    // Build Story page content
    $content = "";

    if (isset($_GET['story']) && $_GET['story'] != "") {
        // get Story Id from url parameter
        $storyId = $_GET['story'];

        // Set request parameters
        $url = TP_API_HOST."/tp-api/stories/".$storyId;
        $requestType = "GET";

        // Execude request
        include dirname(__FILE__)."/../custom_scripts/send_api_request.php";

        // Display data
        $storyData = json_decode($result, true);
        //dd($storyData);
        $storyData = $storyData[0];
    }

    $imgDescription = json_decode($storyData['Items'][rand(0,(count($storyData['Items'])-1))]['ImageLink'], true);
    $imgDescriptionLink = createImageLinkFromData($imgDescription, array('region' => 'full'));
    $descrLink = json_decode($storyData['Items'][0]['ItemId'], true);

    /////////////////////////
    $numbPhotos = count($storyData['Items']);
    // $numbSlides = floor($numbPhotos / 9);
    // $restPhotos = $numbPhotos - ($numbSlides * 9);
    $content .= "<section id='img-slider'>";
    $content .= "<div id='slider-container'>";
        $content .= "<button class='prev-slide' type='button'><i class=\"fas fa-chevron-left\"></i></button>";
        $content .= "<button class='next-slide' type='button'><i class=\"fas fa-chevron-right\"></i></button>";

        $content .= "<div id='inner-slider'>";
            for($x = 0; $x < $numbPhotos; $x++) {
                $sliderImg = json_decode($storyData['Items'][$x]['ImageLink'], true);

                $sliderImgLink = createImageLinkFromData($sliderImg, array('size' => '200,200'));

                $content .= "<div class='slide-sticker' data-value='". ($x+1) ."'>";
                    $content .= "<div class='slide-img-wrap'>";
                        $content .= "<a href='".home_url()."/documents/story/item/?story=".$storyData['StoryId']."&item=".$storyData['Items'][$x]['ItemId']."'><img src=".$sliderImgLink." class='slider-image' alt='slider-image' width='200' height='200' loading='lazy'></a>";
                        $content .= "<div class='image-completion-status' style='bottom:20px;border-color:".$storyData['Items'][$x]['CompletionStatusColorCode']."'></div>";
                    $content .= "</div>";
                    $content .= "<div class='slide-number-wrap'>".($x+1)."</div>";
                $content .= "</div>";
            }

        $content .= "</div>";
    $content .= "</div>";

        $content .= "<div id='controls-div'>";

            $content .= "<button class='prev-set' type='button' style='display:none;'><i class=\"fas fa-chevron-double-left\"></i></button>";
            $content .= "<div id='dot-indicators'>";
            // placeholder for dot indicators
            $content .= "</div>";
            $content .= "<div class='num-indicators'>";
                $content .= "<span id='left-num'>1</span> - <span id='right-num'></span> of ";
                $content .="<span>". $numbPhotos ."</span>";
            $content .="</div>";
            $content .= "<button class='next-set' type='button' style='display:none;'><i class=\"fas fa-chevron-double-right\"></i></button>";
            //// To be discussed if we keep dots or numbers /////
        $content .= "</div>";

    $content .= "</section>";

        /* New- Start Transcription button */
        $content .= "<a class='start-transcription' type='button' href='".get_europeana_url()."/documents/story/item?story=".$storyData['StoryId']."&item=".$descrLink."' style='font-family:\"Dosis\";margin-top:6px;'><b>🖉  Start Transcription</b></a>";

        $content .= "<div id='total-storypg' class='storypg-container'>";
            $content .= "<div class='main-storypg'>";

                // added image to description
            $content .= "<section>";
                $content .= "<div class='storypg-info'>";

                    $content .= "<div class='story-description-left'>";
                    //    $content .= "<div id='desc-img-wrap'>";
                    $content .= "<a href='".home_url()."/documents/story/item?story=".$storyData['StoryId']."&item=".$descrLink."'><img class=\"description-img\" src='".$imgDescriptionLink."' alt=\"story-img\"></a>";
                    unset($imgDescriptionLink);
                    //    $content .= "</div>";

                    $content .= "</div>"; //first column closing
                    $content .= "<div class='story-description-right'>";
                        $content .= "<div id='desc-text-wrap'>";
                        $storyTitle = array_unique(explode(" || ", $storyData['dcTitle']));
                        foreach ($storyTitle as $singleTitle) {
                            $content .= "<h1 class='storypg-title'>";
                            $content .= $singleTitle;
                            $content .= "</h1>";
                        }
                        $storyDescription = array_unique(explode(" || ", $storyData['dcDescription']));

                        //$descriptionStr = explode(". ",$storyDescription[0],2);
                        $storyText = '';
                        $storyKeyWords = array();
                        foreach($storyDescription as $description) {
                            if(strlen($description) > 20){
                                $storyText .= $description;
                            } else {
                                array_push($storyKeyWords, $description);
                            }
                        }

                        if((strlen($storyText) > 0) && (strlen($storyText) < 570) ) {

                            $content .= "<p>".$storyText."</p>";

                            foreach($storyKeyWords as $keyWords) {
                                $content .= "<p>".$keyWords."</p>";
                            }

                                $content .= "<div id='progress-wrap'>";
                                $content .= "<h5 class='progress-h'><i class=\"fa fa-flag-checkered\" aria-hidden=\"true\"></i>  PROGRESS</h5>";
                                    $content .= "<div class='progress-div'>";
                                        $content .= "<p class='progress-p'><span class='table-l'>START DATE</span><span class='tabler-lm'>&nbsp;12/05/22</span><span class='table-rm'>&nbsp;TRANSCRIBERS</span><span class='table-r'>49</span></p>";
                                        $content .= "<p class='progress-p'><span class='table-l'>CHARACTERS</span><span class='tabler-lm'>&nbsp;2.132.122</span><span class='table-rm'>&nbsp;ENRICHMENTS</span><span class='table-r'>89</span></p>";
                                $content .= "</div>";
                                $content .= "</div>";

                        } elseif (strlen($storyText) > 420) {
                            $content .= "<div class='desc-toggle' role='button'>";
                            $content .= "<div id='storyDescription' class='togglePara' style='max-height: 202px;'>";
                            $content .= $storyText;
                            //$content .= "<span class='desc-span' style='display:none;'>".substr($storyText, 399)."</span>";

                            foreach($storyKeyWords as $keyWord) {
                                $content .= "<p>".$keyWord."</p>";
                            }
                            $content .= "</div>";
                            $content .= "<p class='descMore' style='text-align:center;cursor:pointer;'>Show More</p>";
                            $content .= "</div>";

                            $content .= "<div id='progress-wrap'>";
                            $content .= "<h5 class='progress-h'><i class=\"fa fa-flag-checkered\" aria-hidden=\"true\"></i>  PROGRESS</h5>";
                                $content .= "<div class='progress-div'>";
                                    $content .= "<p class='progress-p'><span class='table-l'>START DATE</span><span class='tabler-lm'>&nbsp;12/05/22</span><span class='table-rm'>&nbsp;TRANSCRIBERS</span><span class='table-r'>49</span></p>";
                                    $content .= "<p class='progress-p'><span class='table-l'>CHARACTERS</span><span class='tabler-lm'>&nbsp;2.132.122</span><span class='table-rm'>&nbsp;ENRICHMENTS</span><span class='table-r'>89</span></p>";
                            $content .= "</div>";
                            $content .= "</div>";

                        }else{
                            $content .= "<div id='progress-wrap'>";
                            $content .= "<h5 class='progress-h'><i class=\"fa fa-flag-checkered\" aria-hidden=\"true\"></i>  PROGRESS</h5>";
                                $content .= "<div class='progress-div'>";
                                    $content .= "<p class='progress-p'><span class='table-l'>START DATE</span><span class='tabler-lm'>&nbsp;12/05/22</span><span class='table-rm'>&nbsp;TRANSCRIBERS</span><span class='table-r'>49</span></p>";
                                    $content .= "<p class='progress-p'><span class='table-l'>CHARACTERS</span><span class='tabler-lm'>&nbsp;2.132.122</span><span class='table-rm'>&nbsp;ENRICHMENTS</span><span class='table-r'>89</span></p>";
                            $content .= "</div>";
                            $content .= "</div>";
                        }
                    unset($storyKeyWords);
                    $content .= "</div>";
                    $content .= "</div>"; //second column closing
                    $content .= "</div>"; //row closing
            $content .= "</section>";
            $content .= "<div style='clear:both;'></div>";

            // Htr Import Link
            $content .= '<div style="width:49%;float:left;"><a class="dl-enrichments" style="display:flex;flex-direction:row;justify-content:space-evenly;color:#0a72cc;cursor:pointer;margin-bottom:10px;margin-top:10px;" type="button" href="' . get_europeana_url() . '/import-htr-transcription/?storyId=' . $_GET['story']  . '">';
                $content .= "<span><h5 style='color:#0a72cc;'>Run Transkribus automatic text recognition (HTR) </h5></span>";
                $content .= "<span><i style='position:relative;top:50%;transform:translateY(-50%);font-size:20px;' class='fas fa-desktop' aria-hidden='true'></i></span>";
            $content .= "</a></div>";
            $content .= "<div style='clear:both;'></div>";
            //Status Chart
            $content .= "<div class='storypg-chart'>";

                // Set request parameters for status data
                $url = TP_API_HOST ."/tp-api/completionStatus";
                $requestType = "GET";

                // Execude http request
                include dirname(__FILE__)."/../custom_scripts/send_api_request.php";

                // Save status data
                $statusTypes = json_decode($result, true);

                $statusCount = array(
                                   "Not Started" => 0,
                                   "Edit" => 0,
                                   "Review" => 0,
                                   "Completed" => 0
                               );
                $itemCount = 0;

                foreach ($storyData['Items'] as $item) {
                    switch ($item['CompletionStatusName']){
                        case 'Not Started':
                            $statusCount['Not Started'] += 1;
                            break;
                        case 'Edit':
                            $statusCount['Edit'] += 1;
                            break;
                        case 'Review':
                            $statusCount['Review'] += 1;
                            break;
                        case 'Completed':
                            $statusCount['Completed'] += 1;
                            break;
                    }

                    $itemCount += 1;
                }
                $completedStatus = ($statusCount['Completed'] / $itemCount) * 100;
                $reviewedStatus = ($statusCount['Review'] / $itemCount) * 100;
                $editedStatus = ($statusCount['Edit'] / $itemCount) * 100;
                $notStartedStatus = ($statusCount['Not Started'] / $itemCount) * 100;


                // new "chart"
            $content .= "<section class='chart-section'>";

                $content .= "<div class='bar-chart'>";
                    $content .= "<div class='story-status' style='width:".$completedStatus."%;background-color:#61e02f;z-index:4' title='Completed: ".round($completedStatus)."%'>&nbsp</div>";
                    $content .= "<div class='story-status' style='width:".($reviewedStatus+$completedStatus)."%;background-color:#ffc720;z-index:3;' title='Reviewed: ".round($reviewedStatus)."%'>&nbsp</div>";
                    $content .= "<div class='story-status' style='width:".($editedStatus+$reviewedStatus+$completedStatus)."%;background-color:#fff700;z-index:2;' title='Edited: ".round($editedStatus)."%'>&nbsp</div>";
                    $content .= "<div class='story-status' style='width:100%;background-color:#eeeeee;z-index:1' title='Not Started: ".round($notStartedStatus)."%'>&nbsp</div>";
                $content .= "</div>";
            $content .= "</section>";


            $content .= "</div>";

            unset($statusCount);

            // Get the Story Contributor and People in Story
            $storyContributors = [];
            $storyPersons = [];
            $personsInStory = explode(' || ', $storyData['edmAgent']);
            $contributorCode = explode(' || ', $storyData['dcContributor']);
            foreach($personsInStory as $person) {
                $temp = explode(' | ', $person);
                if($temp[sizeof($temp)-1] == $contributorCode[0]){
                    array_pop($temp);
                    foreach($temp as $tp) {
                        array_push($storyContributors, $tp);
                    }
                } else {
                    array_pop($temp);
                    foreach($temp as $tp) {
                        array_push($storyPersons, $tp);
                    }
                }
            }

            // Short Info Data under the status bar
            $content .= "<div class='story-info'>";
                if(count($storyContributors) > 0) {
                    $content .= "<div style='padding:2%;'><span class='story-info-s'>CONTRIBUTOR</span></br>" . implode('</br>', $storyContributors) . "</div>";
                } else {
                    $content .= "<div style='padding:2%;'><span class='story-info-s'>CREATOR</span></br>" . implode('</br>', $storyPersons) . "</div>";
                }
                $content .= "<div style='padding:2%;'><span class='story-info-s'>DATE</span></br>".substr($storyData['edmBegin'],0,4)."-".substr($storyData['edmEnd'],0,4)."</div>";
                $storyLang = explode(" || ", $storyData['dcLanguage']);
                $content .= "<div style='padding:2%;'><span class='story-info-s'>LANGUAGE</span></br>".$storyLang[1]."</div>";
                $content .= "<div style='padding:2%;'><span class='story-info-s'>ITEMS</span></br>".count($storyData['Items'])."</div>";
                $content .= "<div style='padding:2%;'><span class='story-info-s'>INSTITUTION</span></br>".$storyData['edmDataProvider']."</div>";
            $content .= "</div>";
            unset($storyLang);

                $content .= "<div style='clear:both;'></div>";
                // story map
	            $content .= "<div id='storyMap'></div>";
	            $content .= "<script src='https://api.mapbox.com/mapbox-gl-js/plugins/mapbox-gl-geocoder/v4.4.1/mapbox-gl-geocoder.min.js'></script>
		        				<link rel='stylesheet' href='https://api.mapbox.com/mapbox-gl-js/plugins/mapbox-gl-geocoder/v4.4.1/mapbox-gl-geocoder.css' type='text/css' />";
	            $content .= "    <script>
		        					jQuery(document).ready(function() {
		        				        var url_string = window.location.href;
		        					var url = new URL(url_string);
		        					var storyId = url.searchParams.get('story');
		        					var coordinates = jQuery('.location-input-coordinates-container.location-input-container > input ')[0];

		        					    mapboxgl.accessToken = 'pk.eyJ1IjoiZmFuZGYiLCJhIjoiY2pucHoybmF6MG5uMDN4cGY5dnk4aW80NSJ9.U8roKG6-JV49VZw5ji6YiQ';
		        					    var map = new mapboxgl.Map({
		        					      container: 'storyMap',
		        					      style: 'mapbox://styles/fandf/ck4birror0dyh1dlmd25uhp6y',
		        					      center: [13, 46],
		        					      zoom: 2.8
		        					    });
		        						var bounds = new mapboxgl.LngLatBounds();
		        					map.addControl(new mapboxgl.NavigationControl());

                                    fetch(
                                        '". get_home_url() . "/wp-content/themes/transcribathon/admin/inc/custom_scripts/send_ajax_api_request.php',
                                        {
                                            method: 'POST',
                                            body: JSON.stringify({
                                                type: 'GET',
                                                url: TP_API_HOST + '/tp-api/stories/' + storyId
                                            })
                                        }
                                    )
                                    .then(function(response) {
console.log(response);
                                        return response.json();
                                    })
                                    .then(function(places) {
                                        console.log(places);
                                        if(places.length > 0) {
                                            places[0].Items.forEach(function(marker) {
                                                marker.Places.forEach(function(place) {
                                                    var el = document.createElement('div');
                                                    el.className = 'marker savedMarker';
                                                    var popup = new mapboxgl.Popup({offset: 25, closeButton: false})
                                                    .setHTML('<div class=\"popupWrapper\"><div class=\"name\">' + (place.Name || \"\") + '</div><div class=\"comment\">' + (place.Comment || \"\") + '</div>' + '<a class=\"item-link\" href=\"' + home_url + '/documents/story/item/?story=' + places[0].StoryId + '&item=' + marker.ItemId + '\">' + marker.Title + '</a></div></div>');
                                                    bounds.extend([place.Longitude, place.Latitude]);
                                                    new mapboxgl.Marker({element: el, anchor: 'bottom'})
                                                    .setLngLat([place.Longitude, place.Latitude])
                                                    .setPopup(popup)
                                                    .addTo(map);
                                                });
                                            });
                                            // add story location to the map

                                            if (places[0].PlaceLongitude != 0 || places[0].PlaceLongitude != 0) {
                                                var el = document.createElement('div');
                                                el.className = 'marker savedMarker storyMarker';
                                                var popup = new mapboxgl.Popup({offset: 25, closeButton: false})
                                                .setHTML('<div class=\"popupWrapper\"><div class=\"story-location-header\">Story Location</div><div class=\"title\">' + places[0].dcTitle + '</div><div class=\"name\">' + places[0].PlaceName + '</div></div>');
                                                bounds.extend([places[0].PlaceLongitude, places[0].PlaceLatitude]);

                                                new mapboxgl.Marker({element: el, anchor: 'bottom'})
                                                .setLngLat([places[0].PlaceLongitude, places[0].PlaceLatitude])
                                                .setPopup(popup)
                                                .addTo(map);

                                                map.fitBounds(bounds, {padding: {top: 50, bottom:20, left: 20, right: 20}});
                                            }
                                        }
                                    });

                                });
                                    </script>";

                // metadata
                $content .= "<section class='meta-section'>";
                    $content .= "<p id='meta-collapse-btn' class='metadata-h' role='button' style='cursor:pointer;'><span><b><i style='margin-right:5px;' class=\"fa fa-info-circle\" aria-hidden=\"true\"></i>METADATA</b></span><span><i style='font-size:25px;margin-right:10px;' class='fas fa-angle-down'></i></span></p>";

                    $content .= "<div class='metadata-container js-container' style='height:110px;'>";

                        // Contributor
                        if(sizeof($storyContributors) > 0) {
                            $content .= "<div class='meta-sticker'>";
                                $content .= "<span class='mb-1'>Contributor</span>";
                                foreach($storyContributors as $contributor) {
                                    $content .= "<span class='meta-p'>". $contributor ."</span>";
                                }
                            $content .= "</div>";
                        }

                        // People
                        if($storyPersons) {
                            $content .= "<div class='meta-sticker'>";
                                $content .= "<span class='mb-1'>People</span>";
                                foreach($storyPersons as $person) {
                                    $content .= "<span class='meta-p'>". $person ."</span>";
                                }
                            $content .= "</div>";
                        }
                        // Date
                        if($storyData['dcDate']) {
                            $content .= "<div class='meta-sticker'>";
                                $content .= "<span class='mb-1'>Date</span>";
                                $storyDates = array_unique(explode(' || ', $storyData['dcDate']));
                                foreach($storyDates as $date){
                                    if(substr($date, 0, 4) == 'http'){
                                        // $content .= "<p class='meta-p'><a target='_blank' href='".$date."'>" . $date . "</a></p>";
                                        continue;
                                    } else {
                                        $content .= "<span class='meta-p'>" . $date . "</span>";
                                    }
                                }
                            $content .= "</div>";
                        }
                        // Creator
                        if($storyData['dcCreator']){
                            $content .= "<div class='meta-sticker'>";
                                $content .= "<span class='mb-1'>Creator</span>";
                                $creator = str_replace(' || ', '</br>', $storyData['dcCreator']);
                                    $content .= "<span class='meta-p'>". $creator . "</span>";
                            $content .= "</div>";
                        }

                        // Institution
                        if($storyData['edmDataProvider']) {
                            $content .= "<div class='meta-sticker'>";
                                $content .= "<span class='mb-1'>Institution</span>";
                                $institutions = str_replace(' || ', '</br>', $storyData['edmDataProvider']);
                                    $content .= "<span class='meta-p'>". $institutions . "</span>";
                            $content .= "</div>";
                            unset($institutions);
                        }
                        // Identifier
                        if($storyData['dcIdentifier']) {
                            $content .= "<div class='meta-sticker'>";
                                $content .= "<span class='mb-1'>Identifier</span>";
                                $itemIdentifiers = explode(' || ', $storyData['dcIdentifier']);
                                foreach($itemIdentifiers as $identifier) {
                                    if(substr($identifier, 0, 4) == 'http'){
                                        $content .= "<span class='meta-p'><a target='_blank' href='".$identifier."'>" . $identifier . "</a></span>";
                                    } else {
                                        $content .= "<span class='meta-p'>" . $identifier . "</span>";
                                    }
                                }
                            $content .= "</div>";
                            unset($itemIdentifiers);
                        }

                        // Document Language
                        if($storyData['dcLanguage']) {
                            $content .= "<div class='meta-sticker'>";
                                $content .= "<span class='mb-1'>Document Language</span>";
                                $languages = str_replace(' || ', '</br>', $storyData['dcLanguage']);
                                    $content .= "<span class='meta-p'>". $languages ."</span>";
                            $content .= "</div>";
                            unset($languages);
                        }
                        // Location
                        if($storyData['PlaceName']) {
                            $content .= "<div class='meta-sticker'>";
                                $content .= "<span class='mb-1'>Location</span>";
                                $itemLocations = str_replace(' || ', '</br>', $storyData['PlaceName']);
                                    $content .= "<span class='meta-p'>".$itemLocations."</span>";
                            $content .= "</div>";
                        }

                        // Creation Start
                        if($storyData['edmBegin']) {
                            $content .= "<div class='meta-sticker'>";
                                $content .= "<span class='mb-1'>Creation Start</span>";
                                $creationStarts = str_replace(' || ', '</br>', $storyData['edmBegin']);
                                    $content .= "<span class='meta-p'>". $creationStarts ."</span>";;
                            $content .= "</div>";
                            unset($creationStarts);
                        }
                        // Creation End
                        if($storyData['edmEnd']) {
                            $content .= "<div class='meta-sticker'>";
                                $content .= "<span class='mb-1'>Creation End</span>";
                                $creationEnds = str_replace(' || ', '</br>', $storyData['edmEnd']);
                                    $content .= "<span class='meta-p'>" . $creationEnds ."</span>";
                            $content .= "</div>";
                            unset($creationEnds);
                        }
                        // Source
                        if($storyData['dcSource']) {
                            $content .= "<div class='meta-sticker'>";
                                $content .= "<span class='mb-1'>Source</span>";
                                $itemProvenances = array_unique(explode(' || ', $storyData['dcSource']));
                                    $content .= "<span class='meta-p'>". implode('</br>', $itemProvenances) ."</span>";
                            $content .= "</div>";
                            unset($itemProvenances);
                        }
                        // dctermsProvenance
                        if($storyData['dctermsProvenance']) {
                            $content .= "<div class='meta-sticker'>";
                                $content .= "<span class='mb-1'>Provenance</span>";
                                $provenance = array_unique(explode(' || ', $storyData['dctermsProvenance']));
                                    $content .= "<span class='meta-p'>". implode('</br>' , $provenance) ."</span>";
                            $content .= "</div>";
                        }
                        // Type
                        if($storyData['dcType']) {
                            $content .= "<div class='meta-sticker'>";
                                $content .= "<span class='mb-1'>Type</span>";
                                $itemTypes = explode(' || ', $storyData['dcType']);
                                foreach($itemTypes as $type) {
                                    if(substr($type, 0, 4) == 'http'){
                                        $content .= "<span class='meta-p'><a target='_blank' href='".$type."'>" . $type . "</a></span>";
                                    } else {
                                        $content .= "<span class='meta-p'>" . $type . "</span>";
                                    }
                                }
                            $content .= "</div>";
                        }
                        // Provider Rights
                        if($storyData['dcRights']) {
                            $content .= "<div class='meta-sticker'>";
                                $content .= "<span class='mb-1'>Provider Rights</span>";
                                $dcRights = array_unique(explode(' || ', $storyData['dcRights']));
                                foreach($dcRights as $dcRight){
                                    if(substr($dcRight, 0, 4) == 'http'){
                                        $content .= "<span class='meta-p'><a target='_blank' href='".$dcRight."'>" . $dcRight . "</a></span>";
                                    } else {
                                        $content .= "<span class='meta-p'>" . $dcRight . "</span>";
                                    }
                                }
                            $content .= "</div>";
                        }
                        // edmProvider
                        if($storyData['edmProvider']) {
                            $content .= "<div class='meta-sticker'>";
                                $content .= "<span class='mb-1'>Provider</span>";
                                if(substr($storyData['edmProvider'], 0, 4) == 'http'){
                                    $content .= "<span class='meta-p'><a target='_blank' href='".$storyData['edmProvider']."'>" . $storyData['edmProvider'] . "</a></span>";
                                } else {
                                    $content .= "<span class='meta-p'>" . $storyData['edmProvider'] . "</span>";
                                }
                            $content .= "</div>";
                        }
                        // Providing Country
                        if($storyData['edmCountry']) {
                            $content .= "<div class='meta-sticker'>";
                                $content .= "<span class='mb-1'>Providing Country</span>";
                                    $content .= "<span class='meta-p'>".$storyData['edmCountry']."</span>";
                            $content .= "</div>";
                        }
                        // Provider Language
                        if($storyData['edmLanguage']) {
                            $content .= "<div class='meta-sticker'>";
                                $content .= "<span class='mb-1'>Provider Language</span>";
                                    $content .= "<span class='meta-p'>".$storyData['edmLanguage']."</span>";
                            $content .= "</div>";
                        }
                        // Dataset
                        if($storyData['edmDatasetName']) {
                            $content .= "<div class='meta-sticker'>";
                                $content .= "<span class='mb-1'>Dataset</span>";
                                    $content .= "<span class='meta-p'>".$storyData['edmDatasetName']."</span>";
                            $content .= "</div>";
                        }

                        // Publisher
                        if($storyData['dcPublisher']) {
                            $content .= "<div class='meta-sticker'>";
                                $content .= "<span class='mb-1'>Publisher</span>";
                                    $content .= "<span class='meta-p'>" . str_replace(' || ', '</br>', $storyData['dcPublisher']) . "</span>";
                            $content .= "</div>";
                        }

                        // dcCoverage
                        if($storyData['dcCoverage']) {
                            $content .= "<div class='meta-sticker'>";
                                $content .= "<span class='mb-1'>Coverage</span>";
                                    $content .= "<span class='meta-p'>" . str_replace(' || ', '</br>', $storyData['dcCoverage']) . "</span>";
                            $content .= "</div>";
                        }

                        // URL
                        if($storyData['edmLandnigPage']) {
                            $content .= "<div class='meta-sticker'>";
                                $content .= "<span class='mb-1'><span>Url</span>";
                                if(substr($storyData['edmLandingPage'], 0, 4) == 'http'){
                                    $content .= "<span class='meta-p'><a target='_blank' href='".$storyData['edmLandingPage']."'>" . $storyData['edmLandingPage'] . "</a></span>";
                                } else {
                                    $content .= "<span class='meta-p'>" . $storyData['edmLandingPage'] . "</span>";
                                }
                            $content .= "</div>";
                        }

                        // edmIsShownAt
                        if($storyData['edmIsShownAt']) {
                            $content .= "<div class='meta-sticker'>";
                                $content .= "<span class='mb-1'>>Shown At</span>";
                                if(substr($storyData['edmIsShownAt'], 0, 4) == 'http'){
                                    $content .= "<span class='meta-p'><a target='_blank' href='".$storyData['edmIsShownAt']."'>" . $storyData['edmIsShownAt'] . "</a></span>";
                                } else {
                                    $content .= "<span class='meta-p'>" . $storyData['edmIsShownAt'] . "</span>";
                                }
                            $content .= "</div>";
                        }

                        // Relation
                        if($storyData['dcRelation']) {
                            $content .= "<div class='meta-sticker'>";
                                $content .= "<span class='mb-1'>Relation</span>";
                                    $content .= "<span class='meta-p'>" . str_replace(' || ', '</br>', $storyData['dcRelation']) . "</span>";
                            $content .= "</div>";
                        }
                        // Rights
                        if($storyData['edmRights']) {
                            $content .= "<div class='meta-sticker'>";
                                $content .= "<span class='mb-1'>Rights</span>";
                                $edmRights = array_unique(explode(' || ', $storyData['edmRights']));
                                foreach($edmRights as $edmRight){
                                    if(substr($edmRight, 0, 4) == 'http'){
                                        $content .= "<span class='meta-p'><a target='_blank' href='".$edmRight."'>" . $edmRight . "</a></span>";
                                    } else {
                                        $content .= "<span class='meta-p'>" . $edmRight . "</span>";
                                    }
                                }
                            $content .= "</div>";
                        }

                        // Year
                        if($storyData['edmYear']) {
                            $content .= "<div class='meta-sticker'>";
                                $content .= "<span class='mb-1'>Year</span>";
                                    $content .= "<span class='meta-p'>" . str_replace(' || ', '</br>', $storyData['edmYear']) ."</span>";
                            $content .= "</div>";
                        }

                        // // Agent
                        // if($storyData['edmAgent']) {
                        //     $content .= "<div class='meta-sticker'>";
                        //         $content .= "<p class='mb-1'><b>Agent</b></p>";
                        //         $content .= "<p class='meta-p'>".$storyData['edmAgent']."</p>";
                        //     $content .= "</div>";
                        // }

                        //  dctermsMedium
                        if($storyData['dctermsMedium']) {
                            $content .= "<div class='meta-sticker'>";
                                $content .= "<span class='mb-1'>Medium</span>";
                                    $content .= "<span class='meta-p'>" . str_replace(' || ', '</br>', $storyData['dctermsMedium']) . "</span>";
                            $content .= "</div>";
                        }

                        $content .= "<div id='meta-show-more'><i class=\"fas fa-chevron-double-down\"></i></div>";



                    $content .= "</div>";


                $content .= "</section>";

            $content .= "</div>"; // end of story details
        $content .= "</div>";
    $content .= "</div>";
$content .= "</div>";


    return $content;
}

add_shortcode( 'get_document_data', '_TCT_get_document_data' );
?>
