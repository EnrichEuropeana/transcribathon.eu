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

        // // Set request parameters
        // $url = TP_API_HOST."/tp-api/stories/".$storyId;
        // $requestType = "GET";

        // // Execude request
        // include dirname(__FILE__)."/../custom_scripts/send_api_request.php";

        // // Display data
        // $storyDataA = json_decode($result, true);
        // //dd($storyData);
        // $storyDataA = $storyDataA[0];
        // dd($storyDataA);
        $getJsonOptions = [
            'http' => [
                'header' => [ 
                    'Content-type: application/json',
                    'Authorization: Bearer ' . TP_API_V2_TOKEN
                ],
                'method' => 'GET'
            ]
        ];
    
        $storyDataSet = sendQuery(TP_API_V2_ENDPOINT . '/stories/' . $storyId, $getJsonOptions, true);
        if(!$storyDataSet['success']){
            echo '<div style="width:50vw;height:50vh;margin:50px auto;"><h2>We couldn\'t find any story with that ID</h2></div>';
            return;
        }
        $storyData = $storyDataSet['data'];

        // // Change Story Endpoint
        $allItemsSet = sendQuery(TP_API_V2_ENDPOINT . '/items?limit=500&page=1&orderBy=OrderIndex&orderDir=asc&StoryId=' . $storyId, $getJsonOptions, true);
        $allItems = $allItemsSet['data'];

       // dd($allItems);

    }




    $randomItem = rand(0,(count($allItems)-1));



    // Change path if it's ration card
    $itemPath = 'item';
    if(str_contains($storyData['Dc']['Title'], 'Potrošačka kartica')) {
        $itemPath = 'ration-cards';
    }

    /////////////////////////
    $numbPhotos = count($allItems);

    $statusCount = array(
        "Not Started" => 0,
        "Edit" => 0,
        "Review" => 0,
        "Completed" => 0
    );

    // $numbSlides = floor($numbPhotos / 9);
    // $restPhotos = $numbPhotos - ($numbSlides * 9);
    //dd($allItems);
    //// NEW IMAGE SLIDER
    $allImages = [];
    $compStatusCheck = 0;
    for($x = 0; $x < $numbPhotos; $x++) {

        if(($allItems[$x]['CompletionStatus']['StatusId'] == 2 || $allItems[$x]['CompletionStatus']['StatusId'] == 1) && $compStatusCheck < 1) {
            $randomItem = $x;
            $compStatusCheck = 1;
        }

        $sliderImg = json_decode($allItems[$x]['ImageLink'], true);
        $sliderImgLink = createImageLinkFromData($sliderImg, array('size' => '200,200'));

        if($sliderImg['height'] == null) {
            $sliderImgLink = str_replace('full', '50,50,1800,1100', $sliderImgLink);
        }

        $completionStatusColor = $allItems[$x]['CompletionStatus']['ColorCode'];

        $statusCount[$allItems[$x]['CompletionStatus']['Name']] += 1;

        array_push($allImages, ($sliderImgLink . ' || ' . $allItems[$x]['ItemId'] . ' || ' . $completionStatusColor));
    }
    $imgDescription = json_decode($allItems[$randomItem]['ImageLink'], true);
    $imgDescriptionLink = createImageLinkFromData($imgDescription, array('size' => 'full', 'region' => 'full'));
    $descrLink = json_decode($storyData['Items'][0]['ItemId'], true);
    //dd($allItems);

    $imageSlider = "";
    $imageSlider .= "<div id='slider-images' style='display:none;'>" . json_encode($allImages) . "</div>";
    $imageSlider .= "<div id='story-id' style='display:none;'>" . $storyData['StoryId'] . "</div>";
    $imageSlider .= "<div id='current-itm' style='display:none;'>" . $randomItem . "</div>";
    $imageSlider .= "<div id='img-slider'>";
        $imageSlider .= "<div id='slider-container'>";
            $imageSlider .= "<button class='prev-slide' type='button' aria-label='Previous'><i class='fas fa-chevron-left'></i></button>";
            $imageSlider .= "<button class='next-slide' type='button' aria-label='Next'><i class='fas fa-chevron-right'></i></button>";
            $imageSlider .= "<div id='inner-slider'></div>";
            $imageSlider .= "<div id='dot-indicators'></div>";
        $imageSlider .= "</div>";
    $imageSlider .= "</div>";

    ////
    $content .= "<section id='images-slider'>";
        $content .= $imageSlider;
    $content .= "</section>";

        /* New- Start Transcription button */

        $content .= "<a class='start-transcription' type='button' href='".get_europeana_url()."/documents/story/" . $itemPath . "/?item=".$allItems[$randomItem]['ItemId']."' style='font-family:\"Dosis\";margin-top:6px;'><b>🖉  Start Transcription</b></a>";

        $content .= "<div id='total-storypg' class='storypg-container'>";
            $content .= "<div class='main-storypg'>";
                // added image to description
            $content .= "<section>";
                $content .= "<div class='storypg-info'>";

                    $content .= "<div class='story-description-left'>";
                    //    $content .= "<div id='desc-img-wrap'>";
                    $content .= "<a href='".home_url()."/documents/story/" . $itemPath . "/?item=".$allItems[$randomItem]['ItemId']."'><img class=\"description-img\" src='".$imgDescriptionLink."' alt=\"story-img\"></a>";
                    unset($imgDescriptionLink);
                    //    $content .= "</div>";

                    $content .= "</div>"; //first column closing
                    $content .= "<div class='story-description-right'>";
                        $content .= "<div id='desc-text-wrap'>";
                        $storyTitle = array_unique(explode(" || ", $storyData['Dc']['Title']));
                        foreach ($storyTitle as $singleTitle) {
                            $content .= "<h1 class='storypg-title'>";
                            $content .= $singleTitle;
                            $content .= "</h1>";
                        }
                        $storyDescription = array_unique(explode(" || ", $storyData['Dc']['Description']));

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
                        // After endpoint change, some data missing

                        //Get start date of enrichments
                    //     $startDate = '';
                    //     $nrUserArr = [];
                    //     $timeStamp = [];
                    //    // var_dump($storyData['Items'][0]);
                    //     foreach($storyData['Items'] as $itm) {
                    //         $tmpDate = explode(' ',$itm['LockedTime']);
                    //         if($tmpDate[0] != ""){
                    //             array_push($timeStamp, $tmpDate[0]);
                    //         }
                    //         foreach($itm['Places'] as $pl){
                    //            // var_dump($pl['UserId']);
                    //             array_push($nrUserArr, $pl['UserId']);
                    //         }
                    //     }
                    //     $nrUsers = count(array_unique($nrUserArr));
                    //     if($timeStamp != null) {
                    //         $startDate = min($timeStamp);
                    //     } else {
                    //         $startDate = $storyData['Items'][0]['Timestamp'];
                    //     }

                        if((strlen($storyText) > 0) && (strlen($storyText) < 570) ) {

                            $content .= "<p>".$storyText."</p>";

                            foreach($storyKeyWords as $keyWords) {
                                $content .= "<p>".$keyWords."</p>";
                            }

                            // $content .= "<div id='progress-wrap'>";
                            //     $content .= "<h5 class='progress-h'><i class=\"fa fa-flag-checkered\" aria-hidden=\"true\"></i>  PROGRESS</h5>";
                            //     $content .= "<div class='progress-div'>";
                            //         $content .= "<p class='progress-p'><span class='table-l'>START DATE</span><span class='tabler-lm'>&nbsp;". $startDate ."</span><span class='table-rm'>&nbsp;TRANSCRIBERS</span><span class='table-r'>". $nrUsers ."</span></p>";
                            //     $content .= "</div>";
                            // $content .= "</div>";

                        } else if (strlen($storyText) > 420) {
                            $content .= "<div class='desc-toggle' role='button'>";
                            $content .= "<div id='storyDescription' class='togglePara' style='max-height: 275px;'>";
                            $content .= $storyText;
                            //$content .= "<span class='desc-span' style='display:none;'>".substr($storyText, 399)."</span>";

                            foreach($storyKeyWords as $keyWord) {
                                $content .= "<p>".$keyWord."</p>";
                            }
                            $content .= "</div>";
                            $content .= "<p class='descMore' style='text-align:center;cursor:pointer;'>Show More</p>";
                            $content .= "</div>";

                            // $content .= "<div id='progress-wrap'>";
                            //     $content .= "<h5 class='progress-h'><i class=\"fa fa-flag-checkered\" aria-hidden=\"true\"></i>  PROGRESS</h5>";
                            //     $content .= "<div class='progress-div'>";
                            //         $content .= "<p class='progress-p'><span class='table-l'>START DATE</span><span class='tabler-lm'>&nbsp;". $startDate ."</span><span class='table-rm'>&nbsp;TRANSCRIBERS</span><span class='table-r'>". $nrUsers ."</span></p>";
                            //     $content .= "</div>";
                            // $content .= "</div>";

                        } else if(!empty($storyKeyWords)) {
                            foreach($storyKeyWords as $keyWord) {
                                $content .= "<p>".$keyWord."</p>";
                            }
                        }
                        // else{
                        // else{
                        //     $content .= "<div id='progress-wrap'>";
                        //         $content .= "<h5 class='progress-h'><i class=\"fa fa-flag-checkered\" aria-hidden=\"true\"></i>  PROGRESS</h5>";
                        //         $content .= "<div class='progress-div'>";
                        //             $content .= "<p class='progress-p'><span class='table-l'>START DATE</span><span class='tabler-lm'>&nbsp;". $startDate ."</span><span class='table-rm'>&nbsp;TRANSCRIBERS</span><span class='table-r'>". $nrUsers ."</span></p>";
                        //         $content .= "</div>";
                        //     $content .= "</div>";
                        // }
                        unset($storyKeyWords);
                        $content .= "</div>";

                    $content .= "</div>"; //second column closing
                $content .= "</div>"; //row closing
            $content .= "</section>";
            $content .= "<div style='clear:both;'></div>";

            // Htr Import Link
            if(current_user_can('administrator')) {
                $content .= '<div style="width:49%;float:left;"><a class="dl-enrichments" style="display:flex;flex-direction:row;justify-content:space-evenly;color:#0a72cc;cursor:pointer;margin-top:10px!important;" type="button" href="' . get_main_url() . '/import-htr-transcription/?storyId=' . $_GET['story']  . '">';
                    $content .= "<span><h5 style='color:#0a72cc;'>Run Transkribus automatic text recognition (HTR) </h5></span>";
                    $content .= "<span><i style='position:relative;top:50%;transform:translateY(-50%);font-size:20px;' class='fas fa-desktop' aria-hidden='true'></i></span>";
                $content .= "</a></div>";
                $content .= "<div style='clear:both;'></div>";
            }
            //Status Chart
            $content .= "<div class='storypg-chart'>";

            $completedStatus = ($statusCount['Completed'] / $numbPhotos) * 100;
            $reviewedStatus = ($statusCount['Review'] / $numbPhotos) * 100;
            $editedStatus = ($statusCount['Edit'] / $numbPhotos) * 100;
            $notStartedStatus = ($statusCount['Not Started'] / $numbPhotos) * 100;


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
            $personsInStory = explode(' || ', $storyData['Edm']['Agent']);
            $contributorCode = explode(' || ', $storyData['Dc']['Contributor']);
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
                if(count($storyContributors) > 0 || $storyData['Dc']['Contributor']) {
                    if(count($storyContributors) > 0) {
                        $content .= "<div style='padding:2%;'><span class='story-info-s'>CONTRIBUTOR</span></br>" . implode('</br>', $storyContributors) . "</div>";
                    } else {
                        $content .= "<div style='padding:2%;'><span class='story-info-s'>CONTRIBUTOR</span></br>" . str_replace(' || ', '</br>', $storyData['Dc']['Contributor']) . "</div>";
                    }
                } else {
                    if(count($storyPersons) > 0) {
                        $content .= "<div style='padding:2%;'><span class='story-info-s'>CREATOR</span></br>" . implode('</br>', $storyPersons) . "</div>";
                    } else {
                        $content .= "<div style='padding:2%;'><span class='story-info-s'>CREATOR</span></br>" . str_replace(' || ','</br>', $storyData['Dc']['Creator']) . "</div>";
                    }
                }
                /// Get document dates
                $storyDate = '';
                $creationStarts = explode(' || ', $storyData['Edm']['Begin']);
                $creationEnds = explode(' || ', $storyData['Edm']['End']);
                if($storyData['Dc']['Date']) {
                    $storyDates = array_unique(explode(' || ', $storyData['Dc']['Date']));
                    $storyDateArr  = [];
                    foreach($storyDates as $date){
                        if(substr($date, 0, 4) == 'http' || substr($date, 0, 4) == 'file'){
                            // $content .= "<p class='meta-p'><a target='_blank' href='".$date."'>" . $date . "</a></p>";
                            continue;
                        } else if(!empty($date)) {
                            // $storyDate .= "<span>" . $date . "</span>";
                            array_push($storyDateArr, $date);
                        }
                    }
                    sort($storyDateArr);
                    $endDate = end($storyDateArr);
                    if(count($storyDateArr) > 1) {
                        $storyDate = $storyDateArr[0] . ' - ' . $endDate;
                    } else {
                        $storyDate = $storyDateArr[0];
                    }
                   // var_dump($storyDate);
                } else if(!empty($creationStarts)) {
                    sort($creationStarts);
                    $storyDate = $creationStarts[0];
                } else if(!empty($creationEnds)) {
                    sort($creationEnds);
                    $storyDate = $creationEnds[0];
                }

                $content .= "<div style='padding:2%;'><span class='story-info-s'>DATE</span></br>". $storyDate ."</div>";
                $storyLang = explode(" || ", $storyData['Dc']['Language']);
                $content .= "<div style='padding:2%;'><span class='story-info-s'>LANGUAGE</span></br>".$storyLang[1]."</div>";
                $content .= "<div style='padding:2%;'><span class='story-info-s'>ITEMS</span></br>".count($allItems)."</div>";
                $content .= "<div style='padding:2%;'><span class='story-info-s'>INSTITUTION</span></br>".$storyData['Edm']['DataProvider']."</div>";
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
                                         
                                        return response.json();
                                    })
                                    .then(function(places) {
                                        let placest = JSON.parse(places.content);
                                        // console.log(placest);
                                        if(placest.length > 0) {
                                            placest[0].Items.forEach(function(marker) {
                                                marker.Places.forEach(function(place) {
                                                    var el = document.createElement('div');
                                                    el.className = 'marker savedMarker';
                                                    var popup = new mapboxgl.Popup({offset: 25, closeButton: false})
                                                    .setHTML('<div class=\"popupWrapper\"><div class=\"name\">' + (place.Name || \"\") + '</div><div class=\"comment\">' + (place.Comment || \"\") + '</div>' + '<a class=\"item-link\" href=\"' + home_url + '/documents/story/" . $itemPath . "/?item=' + marker.ItemId + '\">' + marker.Title + '</a></div></div>');
                                                    bounds.extend([place.Longitude, place.Latitude]);
                                                    new mapboxgl.Marker({element: el, anchor: 'bottom'})
                                                    .setLngLat([place.Longitude, place.Latitude])
                                                    .setPopup(popup)
                                                    .addTo(map);
                                                });
                                            });
                                            // add story location to the map

                                            if (placest[0].PlaceLongitude != 0 || placest[0].PlaceLongitude != 0) {
                                                // console.log('story place');
                                                var el = document.createElement('div');
                                                el.className = 'marker savedMarker storyMarker';
                                                var popup = new mapboxgl.Popup({offset: 25, closeButton: false})
                                                .setHTML('<div class=\"popupWrapper\"><div class=\"story-location-header\">Story Location</div><div class=\"title\">' + placest[0].dcTitle + '</div><div class=\"name\">' + placest[0].PlaceName + '</div></div>');
                                                bounds.extend([placest[0].PlaceLongitude, placest[0].PlaceLatitude]);

                                                new mapboxgl.Marker({element: el, anchor: 'bottom'})
                                                .setLngLat([placest[0].PlaceLongitude, placest[0].PlaceLatitude])
                                                .setPopup(popup)
                                                .addTo(map);

                                                map.fitBounds(bounds, {padding: {top: 50, bottom:20, left: 20, right: 20}, maxZoom: 15});
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
                        } elseif ($storyData['Dc']['Contributor']) {
                            $content .= "<div class='meta-sticker'>";
                                $content .= "<span class='mb-1'>Contributor</span>";
                                $content .= "<span class='meta-p'>" . str_replace(' || ', '</br>', $storyData['Dc']['Contributor']) . "</span>";
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
                        if($storyData['Dc']['Date']) {
                            $content .= "<div class='meta-sticker'>";
                                $content .= "<span class='mb-1'>Date</span>";
                                $content .= "<span class='meta-p'>" . $storyDate . "</span>";
                            $content .= "</div>";
                        }
                        // Creator
                        if($storyData['Dc']['Creator']){
                            $content .= "<div class='meta-sticker'>";
                                $content .= "<span class='mb-1'>Creator</span>";
                                $creator = str_replace(' || ', '</br>', $storyData['Dc']['Creator']);
                                    $content .= "<span class='meta-p'>". $creator . "</span>";
                            $content .= "</div>";
                        }

                        // Institution
                        if($storyData['Edm']['DataProvider']) {
                            $content .= "<div class='meta-sticker'>";
                                $content .= "<span class='mb-1'>Institution</span>";
                                $institutions = str_replace(' || ', '</br>', $storyData['Edm']['DataProvider']);
                                    $content .= "<span class='meta-p'>". $institutions . "</span>";
                            $content .= "</div>";
                            unset($institutions);
                        }
                        // Identifier
                        if($storyData['Dc']['Identifier']) {
                            $content .= "<div class='meta-sticker'>";
                                $content .= "<span class='mb-1'>Identifier</span>";
                                $itemIdentifiers = explode(' || ', $storyData['Dc']['Identifier']);
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
                        // EUropeana Identifier
                        //Identifier
                        if($storyData['ExternalRecordId']) {
                            $content .= "<div class='meta-sticker'>";
                                $content .= "<span class='mb-1'>Europeana Identifier</span>";
                                if(substr($storyData['ExternalRecordId'], 0, 4) == 'http'){
                                    $content .= "<span class='meta-p'><a target='_blank' href='".$storyData['ExternalRecordId']."'>" . substr($storyData['ExternalRecordId'], 0, 45) . "</a></span>";
                                } else {
                                    $content .= "<span class='meta-p'>" . $storyData['ExternalRecordId'] . "</span>";
                                }
                            $content .= "</div>";
                        }

                        // Document Language
                        if($storyData['Dc']['Language']) {
                            $content .= "<div class='meta-sticker'>";
                                $content .= "<span class='mb-1'>Document Language</span>";
                                $languages = str_replace(' || ', '</br>', $storyData['Dc']['Language']);
                                    $content .= "<span class='meta-p'>". $languages ."</span>";
                            $content .= "</div>";
                            unset($languages);
                        }
                        // Location
                        if($storyData['Place']['Name']) {
                            $content .= "<div class='meta-sticker'>";
                                $content .= "<span class='mb-1'>Location</span>";
                                $itemLocations = str_replace(' || ', '</br>', $storyData['Place']['Name']);
                                    $content .= "<span class='meta-p'>".$itemLocations."</span>";
                            $content .= "</div>";
                        }

                        // Creation Start
                        if($storyData['Edm']['Begin']) {
                            $content .= "<div class='meta-sticker'>";
                                $content .= "<span class='mb-1'>Creation Start</span>";
                                    $content .= "<span class='meta-p'>". implode('<br>', $creationStarts) ."</span>";;
                            $content .= "</div>";
                        }
                        // Creation End
                        if($storyData['Edm']['End']) {
                            $content .= "<div class='meta-sticker'>";
                                $content .= "<span class='mb-1'>Creation End</span>";
                                    $content .= "<span class='meta-p'>" . implode('<br>', $creationEnds) ."</span>";
                            $content .= "</div>";
                        }
                        // Source
                        if($storyData['Dc']['Source']) {
                            $content .= "<div class='meta-sticker'>";
                                $content .= "<span class='mb-1'>Source</span>";
                                $itemProvenances = array_unique(explode(' || ', $storyData['Dc']['Source']));
                                    $content .= "<span class='meta-p'>". implode('</br>', $itemProvenances) ."</span>";
                            $content .= "</div>";
                            unset($itemProvenances);
                        }
                        // dctermsProvenance
                        if($storyData['Dcterms']['Provenance']) {
                            $content .= "<div class='meta-sticker'>";
                                $content .= "<span class='mb-1'>Provenance</span>";
                                $provenance = array_unique(explode(' || ', $storyData['Dcterms']['Provenance']));
                                    $content .= "<span class='meta-p'>". implode('</br>' , $provenance) ."</span>";
                            $content .= "</div>";
                        }
                        // Type
                        if($storyData['Dc']['Type']) {
                            $content .= "<div class='meta-sticker'>";
                                $content .= "<span class='mb-1'>Type</span>";
                                $itemTypes = explode(' || ', $storyData['Dc']['Type']);
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
                        if($storyData['Dc']['Rights']) {
                            $content .= "<div class='meta-sticker'>";
                                $content .= "<span class='mb-1'>Provider Rights</span>";
                                $dcRights = array_unique(explode(' || ', $storyData['Dc']['Rights']));
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
                        if($storyData['Edm']['Provider']) {
                            $content .= "<div class='meta-sticker'>";
                                $content .= "<span class='mb-1'>Provider</span>";
                                if(substr($storyData['edmProvider'], 0, 4) == 'http'){
                                    $content .= "<span class='meta-p'><a target='_blank' href='".$storyData['Edm']['Provider']."'>" . $storyData['Edm']['Provider'] . "</a></span>";
                                } else {
                                    $content .= "<span class='meta-p'>" . $storyData['Edm']['Provider'] . "</span>";
                                }
                            $content .= "</div>";
                        }
                        // Providing Country
                        if($storyData['Edm']['Country']) {
                            $content .= "<div class='meta-sticker'>";
                                $content .= "<span class='mb-1'>Providing Country</span>";
                                    $content .= "<span class='meta-p'>".$storyData['Edm']['Country']."</span>";
                            $content .= "</div>";
                        }
                        // Provider Language
                        if($storyData['Edm']['Language']) {
                            $content .= "<div class='meta-sticker'>";
                                $content .= "<span class='mb-1'>Provider Language</span>";
                                    $content .= "<span class='meta-p'>".$storyData['Edm']['Language']."</span>";
                            $content .= "</div>";
                        }
                        // Dataset
                        if($storyData['Edm']['DatasetName']) {
                            $content .= "<div class='meta-sticker'>";
                                $content .= "<span class='mb-1'>Dataset</span>";
                                    $content .= "<span class='meta-p'>".$storyData['Edm']['DatasetName']."</span>";
                            $content .= "</div>";
                        }

                        // Publisher
                        if($storyData['Dc']['Publisher']) {
                            $content .= "<div class='meta-sticker'>";
                                $content .= "<span class='mb-1'>Publisher</span>";
                                    $content .= "<span class='meta-p'>" . str_replace(' || ', '</br>', $storyData['Dc']['Publisher']) . "</span>";
                            $content .= "</div>";
                        }

                        // dcCoverage
                        if($storyData['Dc']['Coverage']) {
                            $content .= "<div class='meta-sticker'>";
                                $content .= "<span class='mb-1'>Coverage</span>";
                                    $content .= "<span class='meta-p'>" . str_replace(' || ', '</br>', $storyData['Dc']['Coverage']) . "</span>";
                            $content .= "</div>";
                        }

                        // URL
                        if($storyData['Edm']['LandnigPage']) {
                            $content .= "<div class='meta-sticker'>";
                                $content .= "<span class='mb-1'><span>Url</span>";
                                if(substr($storyData['Edm']['LandingPage'], 0, 4) == 'http'){
                                    $content .= "<span class='meta-p'><a target='_blank' href='".$storyData['Edm']['LandingPage']."'>" . $storyData['Edm']['LandingPage'] . "</a></span>";
                                } else {
                                    $content .= "<span class='meta-p'>" . $storyData['Edm']['LandingPage'] . "</span>";
                                }
                            $content .= "</div>";
                        }

                        // edmIsShownAt
                        if($storyData['Edm']['IsShownAt']) {
                            $content .= "<div class='meta-sticker'>";
                                $content .= "<span class='mb-1'>Shown At</span>";
                                if(substr($storyData['Edm']['IsShownAt'], 0, 4) == 'http'){
                                    $content .= "<span class='meta-p'><a target='_blank' href='".$storyData['Edm']['IsShownAt']."'>" . $storyData['Edm']['IsShownAt'] . "</a></span>";
                                } else {
                                    $content .= "<span class='meta-p'>" . $storyData['Edm']['IsShownAt'] . "</span>";
                                }
                            $content .= "</div>";
                        }

                        // Relation
                        if($storyData['Dc']['Relation']) {
                            $content .= "<div class='meta-sticker'>";
                                $content .= "<span class='mb-1'>Relation</span>";
                                    $content .= "<span class='meta-p'>" . str_replace(' || ', '</br>', $storyData['Dc']['Relation']) . "</span>";
                            $content .= "</div>";
                        }
                        // Rights
                        if($storyData['Edm']['Rights']) {
                            $content .= "<div class='meta-sticker'>";
                                $content .= "<span class='mb-1'>Rights</span>";
                                $edmRights = array_unique(explode(' || ', $storyData['Edm']['Rights']));
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
                        if($storyData['Edm']['Year']) {
                            $content .= "<div class='meta-sticker'>";
                                $content .= "<span class='mb-1'>Year</span>";
                                    $content .= "<span class='meta-p'>" . str_replace(' || ', '</br>', $storyData['Edm']['Year']) ."</span>";
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
                        if($storyData['Dcterms']['Medium']) {
                            $content .= "<div class='meta-sticker'>";
                                $content .= "<span class='mb-1'>Medium</span>";
                                    $content .= "<span class='meta-p'>" . str_replace(' || ', '</br>', $storyData['Dcterms']['Medium']) . "</span>";
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
