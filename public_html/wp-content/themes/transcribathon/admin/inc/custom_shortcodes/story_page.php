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
        //var_dump($storyData);
        $storyData = $storyData[0];
    }

    $imgDescription = json_decode($storyData['Items'][rand(0,(count($storyData['Items'])-1))]['ImageLink'], true);
    if(substr($imgDescription['service']['@id'],0,4) == 'rhus'){
        $imgDescriptionLink ='http://'. str_replace(' ','_',$imgDescription['service']["@id"]) . '/full/full/0/default.jpg';
    } else {
        $imgDescriptionLink = str_replace(' ','_',$imgDescription['service']["@id"]) . '/full/full/0/default.jpg';
    }
    $descrLink = json_decode($storyData['Items'][0]['ItemId'], true);

    //////// Carousel Test
    $numbPhotos = count($storyData['Items']);
    $numbSlides = floor($numbPhotos/9);
    $restPhotos = $numbPhotos - ($numbSlides * 9);

    $content .= "<div id='carousel-image-slider' class='carousel slide' data-ride='carousel' data-interval='false'>";

        $content .= "<div class='carousel-inner'>";
        if($numbPhotos < 9){
            $content .= "<div class='carousel-item-active w-100' style='display:flex;flex-direction:row;justify-content:center;'>";

            for($x=0;$x<$numbPhotos;$x++) {
                $sliderImg = json_decode($storyData['Items'][$x]['ImageLink'], true);

                if(substr($sliderImg['service']['@id'],0,4) == 'rhus'){

                    $sliderImgLink ='http://'. str_replace(' ','_',$sliderImg['service']["@id"]) . '/0,0,'.$sliderImg["height"].','.$sliderImg["width"].'/150,150/0/default.jpg';
                } else {
                    $sliderImgLink = str_replace(' ','_',$sliderImg['service']["@id"]) . '/0,0,'.$sliderImg["height"].','.$sliderImg["width"].'/150,150/0/default.jpg';
                }
                    $content .= "<div>";
                        $content .= "<div class='slide-img-wrap'>";
                            $content .= "<a href='".home_url()."/documents/story/item/?story=".$storyData['StoryId']."&item=".$storyData['Items'][$x]['ItemId']."'><img src=".$sliderImgLink." class='slider-image' alt='slider-image' loading='lazy'></a>";
                            $content .= "<div class='image-completion-status' style='bottom:20px;border-color:".$storyData['Items'][$x]['CompletionStatusColorCode']."'></div>";
                        $content .= "</div>";
                        $content .= "<div class='slide-number-wrap'>".($x+1)."</div>";
                    $content .= "</div>";
            }
            unset($x);
            $content .= "</div>";

        } else {

            $content .= "<div class='carousel-item active'>";
                $content .= "<div style='display:flex;flex-direction:row;justify-content:space-evenly;'>";

                for($i=0;$i<9;$i++) {
                    $sliderImg = json_decode($storyData['Items'][$i]['ImageLink'], true);

                    if(substr($sliderImg['service']['@id'],0,4) == 'rhus'){
                        $sliderImgLink ='http://'. str_replace(' ','_',$sliderImg['service']["@id"]) . '/0,0,'.$sliderImg["height"].','.$sliderImg["width"].'/150,150/0/default.jpg';
                    } else {
                        $sliderImgLink = str_replace(' ','_',$sliderImg['service']["@id"]) . '/0,0,'.$sliderImg["height"].','.$sliderImg["width"].'/150,150/0/default.jpg';
                    }

                    $content .= "<div>";
                        $content .= "<div class='slide-img-wrap' style='margin: 0 5px;'>";
                            $content .= "<a href='".home_url()."/documents/story/item/?story=".$storyData['StoryId']."&item=".$storyData['Items'][$i]['ItemId']."'><img src=".$sliderImgLink." class='slider-image' alt='slider-image' loading='lazy'></a>";
                            $content .= "<div class='image-completion-status' style='bottom:20px;border-color:".$storyData['Items'][$i]['CompletionStatusColorCode']."'></div>";
                        $content .= "</div>";
                        $content .= "<div class='slide-number-wrap'>".($i+1)."</div>";
                    $content .= "</div>";

                }
                unset($i);
                $content .= "</div>";
            $content .= "</div>";

            for($y=1;$y<$numbSlides;$y++){

                $content .= "<div class='carousel-item'>";
                    $content .= "<div style='display:flex;flex-direction:row;justify-content:space-evenly;'>";

                    for($j=0;$j<9;$j++) {
                        $sliderImg = json_decode($storyData['Items'][$j+($y*9)]['ImageLink'], true);
                        if(substr($sliderImg['service']['@id'],0,4) == 'rhus'){
                            $sliderImgLink ='http://'. str_replace(' ','_',$sliderImg['service']["@id"]) . '/0,0,'.$sliderImg["height"].','.$sliderImg["width"].'/150,150/0/default.jpg';
                        } else {
                            $sliderImgLink = str_replace(' ','_',$sliderImg['service']["@id"]) . '/0,0,'.$sliderImg["height"].','.$sliderImg["width"].'/150,150/0/default.jpg';
                        }
                        $content .= "<div>";
                            $content .= "<div class='slide-img-wrap' style='margin: 0 5px;'>";
                                $content .= "<a href='".home_url()."/documents/story/item/?story=".$storyData['StoryId']."&item=".$storyData['Items'][$j+($y*9)]['ItemId']."'><img src=".$sliderImgLink." class='slider-image' alt='slider-image' loading='lazy'></a>";
                                $content .= "<div class='image-completion-status' style='bottom:20px;border-color:".$storyData['Items'][$j+($y*9)]['CompletionStatusColorCode']."'></div>";
                            $content .= "</div>";
                            $content .= "<div class='slide-number-wrap'>".($j+($y*9)+1)."</div>";
                        $content .= "</div>";
                    }
                    unset($j);
                    $content .= "</div>";
                $content .= "</div>";
            }
            unset($y);

            if($restPhotos > 0){
                $content .= "<div class='carousel-item'>";
                    $content .= "<div style='display:flex;flex-direction:row;justify-content:space-evenly;'>";

                    for($z=($numbPhotos-9);$z<$numbPhotos;$z++) {
                        $sliderImg = json_decode($storyData['Items'][$z]['ImageLink'], true);

                        if(substr($sliderImg['service']['@id'],0,4) == 'rhus'){
                            $sliderImgLink ='http://'. str_replace(' ','_',$sliderImg['service']["@id"]) . '/0,0,'.$sliderImg["height"].','.$sliderImg["width"].'/150,150/0/default.jpg';
                        } else {
                            $sliderImgLink = str_replace(' ','_',$sliderImg['service']["@id"]) . '/0,0,'.$sliderImg["height"].','.$sliderImg["width"].'/150,150/0/default.jpg';
                        }

                        $content .= "<div>";
                            $content .= "<div class='slide-img-wrap' style='margin: 0 5px;'>";
                                $content .= "<a href='".home_url()."/documents/story/item/?story=".$storyData['StoryId']."&item=".$storyData['Items'][$z]['ItemId']."'><img src=".$sliderImgLink." class='slider-image' alt='slider-image' loading='lazy'></a>";
                                $content .= "<div class='image-completion-status' style='bottom:20px;border-color:".$storyData['Items'][$z]['CompletionStatusColorCode']."'></div>";
                            $content .= "</div>";
                            $content .= "<div class='slide-number-wrap'>".($z+1)."</div>";
                        $content .= "</div>";
                    }
                    unset($z);
                    $content .= "</div>";
                $content .= "</div>";

                }
        }

        $content .= "</div>";
        if($numbPhotos > 9){
            $content .= "<button class='carousel-control-prev' type='button' data-target='#carousel-image-slider' data-slide='prev'>";
                $content .= "<span class='carousel-control-prev-icon' aria-hidden='true'></span>";
                $content .= "<span class='sr-only'>Prevoius</span>";
            $content .= "</button>";
            $content .= "<button class='carousel-control-next' type='button' data-target='#carousel-image-slider' data-slide='next'>";
                $content .= "<span class='carousel-control-next-icon' aria-hidden='true'></span>";
                $content .= "<span class='sr-only'>Next</span>";
            $content .= "</button>";
            $content .= "<div class='carousel-indicators'>";
                $content .= "<button type='button' data-target='#carousel-image-slider' data-slide-to='0' class='active' aria-current='true' aria-label='slide 1'></button>";

            for($n=0;$n<$numbSlides;$n++) {
                    $content .= "<button type='button' data-target='#carousel-image-slider' data-slide-to='".($n+1)."' aria-label='slide 2'></button>";
                }

            $content .= "</div>";
        }
    $content .= "</div>"; //end of opening div
    // Clean variables that we don't need anymore

    unset($numbPhotos, $numbSlides, $sliderImg, $sliderImgLink);



        $content .= '<div class="story-navigation-area">';
            $content .= '<ul class="story-navigation-content-container left" style="">';
                $content .= '<li><a href="'.home_url().'/documents/" style="text-decoration: none;">Stories</a></li>';
                $content .= '<li><i class="fal fa-angle-right"></i></li>';
                $content .= '<li>';
                $content .= $storyData['dcTitle'];
                $content .= '</li>';
            $content .= '</ul>';
        $content .= '</div>';

        /* New- Start Transcription button */
        $content .= "<a class='start-transcription' type='button' href='".get_europeana_url()."/documents/story/item?story=".$storyData['StoryId']."&item=".$descrLink."'><b>🖉  Start Transcription</b></a>";

        $content .= "<div id='total-storypg' class='storypg-container'>";
            $content .= "<div class='main-storypg'>";

                // added image to description
            $content .= "<section style='min-height:600px;max-height:600px;'>";
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
                            $content .= "<div id='storyDescription' class='togglePara' style='max-height: 200px;'>";
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
                echo $statusCount['Not-Started'];
                $completedStatus = ($statusCount['Completed'] / $itemCount) * 100;
                $reviewedStatus = ($statusCount['Review'] / $itemCount) * 100;
                $editedStatus = ($statusCount['Edit'] / $itemCount) * 100;
                $notStartedStatus = ($statusCount['Not Started'] / $itemCount) * 100;

                var_dump($editedStatus);
                // new "chart"
            $content .= "<section>";

                $content .= "<div style='margin-bottom:20px;'>";
                    $content .= "<span style='color:#0a72cc;' class='status-header'><b>Story Status</b></span>";
                    $content .= "<span class='dot' style='background:#61e02f;' title='Completed'></span>";
                    $content .= "<span class='dot' style='background:#ffc720;' title='Reviewed'></span>";
                    $content .= "<span class='dot' style='background:#fff700;' title='Edited'></span>";
                    $content .= "<span class='dot' style='background:#eeeeee;' title='Not Started'></span>";
                $content .= "</div>";

                $content .= "<div class='bar-chart'>";
                    $content .= "<div class='story-status' style='width:".$completedStatus."%;background-color:#61e02f;z-index:4'>&nbsp</div>";
                    $content .= "<div class='story-status' style='width:".($reviewedStatus+$completedStatus)."%;background-color:#ffc720;z-index:3;'>&nbsp</div>";
                    $content .= "<div class='story-status' style='width:".($editedStatus+$reviewedStatus+$completedStatus)."%;background-color:#fff700;z-index:2;'>&nbsp</div>";
                    $content .= "<div class='story-status' style='width:100%;background-color:#eeeeee;z-index:1'>&nbsp</div>";
                $content .= "</div>";
            $content .= "</section>";


            $content .= "</div>";

            unset($statusCount);

            // Short Info Data under the status bar
            $content .= "<div class='story-info'>";
                $content .= "<span style='width:20%;padding:2%;'><span class='story-info-s'>DATE</span></br>".substr($storyData['edmBegin'],0,4)."-".substr($storyData['edmEnd'],0,4)."</span>";
                $storyLang = explode(" || ", $storyData['dcLanguage']);
                $content .= "<span style='width:20%;padding:2%;'><span class='story-info-s'>LANGUAGE</span></br>".$storyLang[1]."</span>";
                $content .= "<span style='width:20%;padding:2%;'><span class='story-info-s'>ITEMS</span></br>".count($storyData['Items'])."</span>";
                $content .= "<span style='width:20%;padding:2%;'><span class='story-info-s'>PROVIDER</span></br>".$storyData['edmProvider']."</span>";
                $content .= "<span style='width:20%;padding:2%;'><span class='story-info-s'>DATASET</span></br>".$storyData['edmDatasetName']."</span>";
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

                                    fetch(TP_API_HOST + '/tp-api/stories/' + storyId)
                                    .then(function(response) {
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
                $content .= "<p class='metadata-h' role='button' data-toggle='collapse' href='#metaCollapse' aria-expanded='false' aria-controls='metaCollapse'><span><b><i style='margin-right:5px;' class=\"fa fa-info-circle\" aria-hidden=\"true\"></i>METADATA</b></span><span><i style='font-size:25px;margin-right:10px;' class='fas fa-angle-down'></i></span></p>";
                $content .= "<div class='container'>";
                    $content .= "<div class='row'>";
                        $content .= "<div class='col-md-3'>";
                            $content .= "<div class='d-flex justify-content-between'>";
                                $content .= "<p class='mb-1'><b>Contributor</b></p>";
                            $content .= "</div>";
                            $contributors = explode(' || ', $storyData['dcContributor']);
                            foreach($contributors as $contributor) {
                                $content .= "<p class='meta-p'>".$contributor."</p>";
                            }
                            unset($contributors);
                        $content .= "</div>";

                        $content .= "<div class='col-md-3'>";
                            $content .= "<div class='d-flex justify-content-between'>";
                                $content .= "<p class='mb-1'><b>Creator</b></p>";
                            $content .= "</div>";
                            $creators = array_unique(explode(' || ', $storyData['dcCreator']));
                            foreach($creators as $creator) {
                                $content .= "<p class='meta-p'>".$creator."</p>";
                            }
                        $content .= "</div>";
                        unset($creators);

                        $content .= "<div class='col-md-3'>";
                            $content .= "<div class='d-flex justify-content-between'>";
                                $content .= "<p class='mb-1'><b>Institution</b></p>";
                            $content .= "</div>";
                            $institutions = explode(' || ', $storyData['edmDataProvider']);
                            foreach($institutions as $institution) {
                                $content .= "<p class='meta-p'>".$institution."</p>";
                            }
                        $content .= "</div>";
                        unset($institutions);

                        $content .= "<div class='col-md-3'>";
                            $content .= "<div class='d-flex justify-content-between'>";
                                $content .= "<p class='mb-1'><b>Identifier</b></p>";
                            $content .= "</div>";
                            $itemIdentifiers = explode(' || ',$storyData['dcIdentifier']);
                            $content .= "<p class='meta-p'>".$itemIdentifiers[1]."</p>";
                        $content .= "</div>";
                        unset($itemIdentifiers);

                        $content .= "<div class='w-100'></div>";

                    $content .= "</div>";
                $content .= "</div>";

                $content .= "<div class='collapse' id='metaCollapse'>";
                $content .= "<div class='container'>";
                    $content .= "<div class='row'>";

                        $content .= "<div class='col-md-3'>";
                            $content .= "<div class='d-flex justify-content-between'>";
                                $content .= "<p class='mb-1'><b>Creation Start</b></p>";
                            $content .= "</div>";
                            $creationStarts = explode(' || ',$storyData['edmBegin']);
                            foreach($creationStarts as $creationStart) {
                                $content .= "<p class='meta-p'>".$creationStart."</p>";
                            }
                        $content .= "</div>";
                        unset($creationStarts);

                        $content .= "<div class='col-md-3'>";
                            $content .= "<div class='d-flex justify-content-between'>";
                                $content .= "<p class='mb-1'><b>Creation End</b></p>";
                            $content .= "</div>";
                            $creationEnds = explode(' || ',$storyData['edmEnd']);
                            foreach($creationEnds as $creationEnd) {
                                $content .= "<p class='meta-p'>".$creationEnd."</p>";
                            }
                        $content .= "</div>";
                        unset($creationEnds);

                        $content .= "<div class='col-md-3'>";
                            $content .= "<div class='d-flex justify-content-between'>";
                                $content .= "<p class='mb-1'><b>Provenance</b></p>";
                            $content .= "</div>";
                            $itemProvenances = explode(' || ', $storyData['dcSource']);
                            $content .= "<p class='meta-p'>".$itemProvenances[0]."</p>";
                        $content .= "</div>";
                        unset($itemProvenances);

                        $content .= "<div class='col-md-3'>";
                            $content .= "<div class='d-flex justify-content-between'>";
                                $content .= "<p class='mb-1'><b>Url</b></p>";
                            $content .= "</div>";
                            $content .= "<p class='meta-p'>".$storyData['edmLandingPage']."</p>";
                        $content .= "</div>";

                        $content .= "<div class='w-100'></div>";

                        $content .= "<div class='col-md-3'>";
                            $content .= "<div class='d-flex justify-content-between'>";
                                $content .= "<p class='mb-1'><b>Document Language</b></p>";
                            $content .= "</div>";
                            $languages = array_unique(explode(' || ', $storyData['dcLanguage']));
                            foreach($languages as $language){
                                $content .= "<p class='meta-p'>".$language."</p>";
                            }
                        $content .= "</div>";
                        unset($languages);

                        $content .= "<div class='col-md-3'>";
                            $content .= "<div class='d-flex justify-content-between'>";
                                $content .= "<p class='mb-1'><b>Country</b></p>";
                            $content .= "</div>";
                            $content .= "<p class='meta-p'>".$storyData['edmCountry']."</p>";
                        $content .= "</div>";

                        $content .= "<div class='col-md-3'>";
                            $content .= "<div class='d-flex justify-content-between'>";
                                $content .= "<p class='mb-1'><b>Provider Language</b></p>";
                            $content .= "</div>";
                            $content .= "<p class='meta-p'>".$storyData['edmLanguage']."</p>";
                        $content .= "</div>";

                        $content .= "<div class='col-md-3'>";
                            $content .= "<div class='d-flex justify-content-between'>";
                                $content .= "<p class='mb-1'><b>Provider</b></p>";
                            $content .= "</div>";
                            $content .= "<p class='meta-p'>".$storyData['edmProvider']."</p>";
                        $content .= "</div>";

                        $content .= "<div class='w-100'></div>";

                        $content .= "<div class='col-md-3'>";
                            $content .= "<div class='d-flex justify-content-between'>";
                                $content .= "<p class='mb-1'><b>Location</b></p>";
                            $content .= "</div>";
                            $itemLocations = explode(' || ',$storyData['PlaceName']);
                            foreach($itemLocations as $itemLocation) {
                                $content .= "<p class='meta-p'>".$itemLocation."</p>";
                            }
                        $content .= "</div>";

                        $content .= "<div class='col-md-3'>";
                            $content .= "<div class='d-flex justify-content-between'>";
                                $content .= "<p class='mb-1'><b>Type</b></p>";
                            $content .= "</div>";
                            $itemTypes = array_unique(explode(' || ',$storyData['dcType']));
                            foreach($itemTypes as $itemType){
                                $content .= "<p class='meta-p'>".$itemType."</p>";
                            }
                        $content .= "</div>";

                        $content .= "<div class='col-md-3'>";
                            $content .= "<div class='d-flex justify-content-between'>";
                                $content .= "<p class='mb-1'><b>Dataset</b></p>";
                            $content .= "</div>";
                            $content .= "<p class='meta-p'>".$storyData['edmDatasetName']."</p>";
                        $content .= "</div>";

                        $content .= "<div class='col-md-3'>";
                            $content .= "<div class='d-flex justify-content-between'>";
                                $content .= "<p class='mb-1'><b>Rights</b></p>";
                            $content .= "</div>";
                            $itemRights = array_unique(explode(' || ', $storyData['edmRights']));
                            foreach($itemRights as $itemRight) {
                                $content .= "<p class='meta-p'>".$itemRight."</p>";
                            }
                        $content .= "</div>";

                    $content .= "</div>";
                $content .= "</div>";
                $content .= "</div>";

            $content .= "</div>"; // end of story details
        $content .= "</div>";
    $content .= "</div>";
$content .= "</div>";


    return $content;
}

add_shortcode( 'get_document_data', '_TCT_get_document_data' );
?>
