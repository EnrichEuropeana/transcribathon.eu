<?php

/* 
Shortcode: tct_new_search
Description: New search Page, based on Solr Dismax Parser 
*/

// include required files
include($_SERVER["DOCUMENT_ROOT"].'/wp-load.php');


function _TCT_new_search( $atts ) {

    // Users query string(containing all search terms)
    $queryString = $_POST['qt'];

    // If the Query string is empty(e.g. first load of page) get all documents(24x to be more specific)
    if(strlen($queryString) < 2) {

        $url = TP_SOLR . '/solr/Items/select?facet.field=StoryId&facet=on&q=*%3A*&rows=24&start=0&sort=StoryId%20desc';
        $requestType = "GET";

        include dirname(__FILE__)."/../custom_scripts/send_api_request.php";

        $solrData = json_decode($result, true);

        $resultItems = $solrData['response']['docs'];
        
    } else {
        /*
        Theoretically it should work with str_replace(replacing spaces with *) and without foreach loop,
        but with short testing it din't work properly.
        */
        $queryString = explode(' ', $queryString);
        $queryPhrase = '';
        foreach($queryString as $query){
            $queryPhrase .= '*' . $query . '* '; 
        }
        htmlspecialchars($queryPhrase);
        $url = TP_SOLR . '/solr/Items/select?defType=edismax&facet.field=StoryId&facet=on&hl.fl=*&hl=on&mm=2&q.alt=*%3A*&q='.urlencode($queryPhrase).'&qf=text%20StoryId%20ItemId%20Languages%20CompletionStatus&rows=24&start=0&sort=StoryId%20desc';

        $requestType = "GET";
        include dirname(__FILE__)."/../custom_scripts/send_api_request.php";

        $solrData = json_decode($result, true);

        $resultItems = $solrData['response']['docs'];
    }
    
   // var_dump($solrData['facet_counts']['facet_fields']['StoryId']);
   // var_dump($resultItems);

    // Get ID of top 10 Stories
    $allStoryIds = $solrData['facet_counts']['facet_fields']['StoryId'];
    if($allStoryIds != Null) {
        $topStoriesId = '';
        for($i = 0; $i < 20; $i += 2) {
            // To store number of hits, we declare topStoriesId as an array and run following code
            // $topStoriesId[$allStoryIds[$i]] = $allStoryIds[$i + 1];
    
            // To get only Story ID
            if($allStoryIds[$i+1] > 0) {
                $topStoriesId .= $allStoryIds[$i] . ' ';
            }
        }
        var_dump(urlencode($topStoriesId));
        $url = TP_SOLR . '/solr/Stories/select?df=StoryId&q=' . urlencode($topStoriesId);
        $requestType = "GET";

        include dirname(__FILE__)."/../custom_scripts/send_api_request.php";

        $solrData = json_decode($result, true);

        $resultStories = $solrData['response']['docs'];
    }
  


    $content = "";
    // Styling for the page, needs to be moved to css at the end
    $content .= "<style>
        #primary-full-width {
            padding: unset;
        }
        #formContainer {
            position: relative;
            width: 400px;
            margin: 0 auto;
            top: 50%;
            transform: translateY(-50%);
        }
        #searchForm {
            display: inline-block;
            width: 100%;
            height: 100px;
        }
        #searchForm label {
            font-size: 15px;
            text-transform: uppercase;
            font-weight: 500;
        }
        .mapLink {
            height: 50px;
            display: inline-block;
            position: relative;
            bottom: 10px;
        }
        #searchTerm {
            width: 80%;
        }
        #searchContainer {
            background: url(\"https://europeana.transcribathon.eu.local/wp-content/uploads/sites/11/2019/03/Greek_school_report-2100x600.jpg\");
            height: 300px;
        }
        #itemResultContainer, #storyResultContainer {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-evenly;
        }
        .result-sticker {
            width: 300px;
            height: 300px;
            margin: 10px 10px;
            border: 1px solid #e6ebf2;
            padding: 5px;
            position: relative;
        }
        .result-sticker img {
            height: 60%!important;
        }
        .sticker-title {
            font-size: 20px;
            text-align: center;
            font-weight: 400;
        }
        .sticker-category {
            position: absolute;
            font-size: 10px;
            bottom: 0;
            margin: unset!important;
        }
        .sticker-language {
            font-size: 10px;
            position: absolute;
            bottom: 12px;
            margin: unset!important;
        }
        .sticker-status {
            display: inline-block;
            max-height: 10px;
        }
    </style>";


    // Top of the page containing user input used to build Solr query
    $content .= "<section id='searchContainer'>";
        $content .= "<div id='formContainer'>";
            $content .= "<form id='searchForm' action='#' method='post'>";
                $content .= "<input type='text' id='searchTerm' name='qt'>";
                $content .= "<input type='submit' value='🔎︎' form='searchForm' class='theme-color-background'>";
                $content .= "<div class='theme-color-background mapLink'><a href='/map' target='_blank' class='theme-color-background'><i class='fal fa-globe-europe' style='font-size: 20px;'></i></a></div>";    
            
                // Choose between Stories and Items
                $content .= "<input type='checkbox' id='story-radio' value='story'>";
                $content .= "<label for='story-radio'>Stories</label>";
                $content .= "<input type='checkbox' id='item-radio' value='item' style='margin-left:5px;'>";
                $content .= "<label for='item-radio'>Items</label>";
                // Give users some options for the query
                $content .= "</br>";
                $content .= "<p>Refine Your Search <i class=\"fas fa-angle-down\"></i></p>";
                $content .= "<div style='background-color:#fff;'>";
                    $content .= "<div>";
                        $content .= "<input type='checkbox' id='docId' name='docId'>";
                        $content .= "<label for='docId'>ID</label>";
                    $content .= "</div>";
                    $content .= "<div>";
                        $content .= "<input type='checkbox' id='docLanguage' name='docLanguage'>";
                        $content .= "<label for='docLanguage'>Language</label>";
                    $content .= "</div>";
                    $content .= "<div>";
                        $content .= "<input type='checkbox' id='edmCountry' name='edmCountry'>";
                        $content .= "<label for='edmCountry'>Country</label>";
                    $content .= "</div>";
                    $content .= "<div>";
                        $content .= "<input type='checkbox' id='edmProvider' name='edmProvider'>";
                        $content .= "<label for='edmProvider'>Provider</label>";
                    $content .= "</div>";
                    $content .= "<div>";
                        $content .= "<input type='checkbox' id='Categories' name='Categories'>";
                        $content .= "<label for='Categories'>Category</label>";
                    $content .= "</div>";
                $content .= "</div>";

            $content .= "</form>";   
        $content .= "</div>";
    $content .= "</section>"; // End of top section 

    // Bottom section, container with Item results
    $content .= "<section id='itemResultContainer'>";
        
        // Display results on the page
        foreach($resultItems as $item){

            // Get Image link
            $imgLink = json_decode($item['PreviewImageLink'], true);
            //var_dump($imgLink);
            if(substr($imgLink['service']['@id'], 0, 4) == "http") {
                $imgLink = $imgLink['service']['@id'] . '/0,0,'.$imgLink['height'].','.($imgLink['height']/2).'/350,350/0/default.jpg';
            } else {
                $imgLink = 'http://' . $imgLink['service']['@id'] . '/0,0,'.$imgLink['height'].','.($imgLink['height']/2).'/350,350/0/default.jpg';
            }
            // Clean the title
            $itemTitle = explode(' || ', $item['Title']);
            $itemTitle = $itemTitle[0];

            // Get the Languages
            $itemLanguage = 'Language: ';
           // $itemLanguages = array_unique(explode(' || ', $item['Languages']));
            if($item['Languages'] != Null){
                foreach($item['Languages'] as $language){
                    $itemLanguage .= ' ' . $language . ' ';
                }
            }

            // Prepare Category of the document
            $itemCategory = "Category: ";
            if($item['Categories'] != Null || $item['Categories'] != ''){
                foreach($item['Categories'] as $category){
                    $itemCategory .= ' \'' . $category . '\'';
                }
            }

            
            // Get status percentages
            $completeNr = 0;
            $reviewNr = 0;
            $editNr = 0;
            $notStartNr = 0;

            switch($item['DescriptionStatus']) {
                case 'Edit':
                    $editNr += 25;
                    break;
                case 'Not Started':
                    $notStartNr += 25;
                    break;
                case 'Review':
                    $reviewNr += 25;
                    break;
                case 'Completed':
                    $completeNr += 25;
                    break;
            }

            switch($item['TaggingStatus']) {
                case 'Edit':
                    $editNr += 25;
                    break;
                case 'Not Started':
                    $notStartNr += 25;
                    break;
                case 'Review':
                    $reviewNr += 25;
                    break;
                case 'Completed':
                    $completeNr += 25;
                    break;
            }

            switch($item['LocationStatus']) {
                case 'Edit':
                    $editNr += 25;
                    break;
                case 'Not Started':
                    $notStartNr += 25;
                    break;
                case 'Review':
                    $reviewNr += 25;
                    break;
                case 'Completed':
                    $completeNr += 25;
                    break;
            }

            switch($item['TranscriptionStatus']) {
                case 'Edit':
                    $editNr += 25;
                    break;
                case 'Not Started':
                    $notStartNr += 25;
                    break;
                case 'Review':
                    $reviewNr += 25;
                    break;
                case 'Completed':
                    $completeNr += 25;
                    break;
            }

            $content .= "<div class='result-sticker'>";
                $content .= "<img src='". $imgLink ."' alt='result image'>";
                // Status chart
                $content .= "<div style='width:100%;height:7px;margin-bottom:5px;display:block;'>";
                    // Completed
                    $content .= "<div class='sticker-status' style='width:".$completeNr."%;background-color:#61e02f;' title='Completed: ".$completeNr."%'>&nbsp</div>";
                    // Review
                    $content .= "<div class='sticker-status' style='width:".$reviewNr."%;background-color:#ffc720;' title='Reviewed: ".$reviewNr."%'>&nbsp</div>";
                    // Edit
                    $content .= "<div class='sticker-status' style='width:".$editNr."%;background-color:#fff700;' title='Edited: ".$editNr."%'>&nbsp;</div>";
                    // Not Started
                    $content .= "<div class='sticker-status' style='width:".$notStartNr."%;background-color:#eeeeee;' title='Not Started: ".$notStartNr."%'>&nbsp;</div>";
                $content .= "</div>";
                $content .= "<p class='sticker-title'>". $itemTitle ."</p>";
                $content .= "<p class='sticker-language'>". $itemLanguage ."</p>";
                $content .= "<p class='sticker-category'>". $itemCategory . "</p>";
            $content .= "</div>";
        }
        // Clean the memory
        $imgLink = Null;
        $itemTitle = Null;
        $itemCategory = Null;
        $itemLanguages = Null;

        
        
    $content .= "</section>"; // End of Results
    //var_dump($queryString);

    // Container with top 10 Stories
    $content .= "<section id='storyResultContainer'>";
        $content .= "<div style='width:100%;text-align:center;margin-top:50px;'><h4>Stories with most Items</h4></div>";

    if($resultStories != Null){
        foreach($resultStories as $story) {

            // Get percentages
            $completedNr = $story['CompletedAmount'];
            $reviewNr = $story['ReviewAmount'];
            $editNr = $story['EditAmount'];
            $notStartedNr = $story['NotStartedAmount'];
            $totalNr = $completedNr + $reviewNr + $editNr + $notStartedNr;
            $completedNr = ($completedNr / $totalNr) * 100;
            $reviewNr = ($reviewNr / $totalNr) * 100;
            $editNr = ($editNr / $totalNr) * 100;
            $notStartedNr = ($notStartedNr / $totalNr) * 100;


            // Get Image Link
            $imgLink = json_decode($story['PreviewImageLink'], true);
            //var_dump($imgLink);
            if(substr($imgLink['service']['@id'], 0, 4) == "http") {
                $imgLink = $imgLink['service']['@id'] . '/0,0,'.$imgLink['height'].','.($imgLink['height']/2).'/350,350/0/default.jpg';
            } else {
                $imgLink = 'http://' . $imgLink['service']['@id'] . '/0,0,'.$imgLink['height'].','.($imgLink['height']/2).'/350,350/0/default.jpg';
            }

            // Clean the story title( on some stories it's twice there)
            $storyTitle = explode(' || ', $story['dcTitle']);
            $storyTitle = $storyTitle[0];


            $content .= "<div class='result-sticker'>";
                $content .= "<img src='". $imgLink ."' alt='result image'>";
                // Status chart
                $content .= "<div style='width:100%;height:7px;margin-bottom:5px;display:block;'>";
                    // Completed
                    $content .= "<div class='sticker-status' style='width:".$completedNr."%;background-color:#61e02f;' title='Completed: ".$story['CompletedAmount']."'>&nbsp</div>";
                    // Review
                    $content .= "<div class='sticker-status' style='width:".$reviewNr."%;background-color:#ffc720;' title='Reviewed: ".$story['ReviewAmount']."'>&nbsp</div>";
                    // Edit
                    $content .= "<div class='sticker-status' style='width:".$editNr."%;background-color:#fff700;' title='Edited: ".$story['EditAmount']."'>&nbsp;</div>";
                    // Not Started
                    $content .= "<div class='sticker-status' style='width:".$notStartedNr."%;background-color:#eeeeee;' title='Not Started: ".$story['NotStartedAmount']."'>&nbsp;</div>";
                $content .= "</div>";
                $content .= "<p class='sticker-title'>". $storyTitle ."</p>";
                // $content .= "<p class='sticker-language'>". $itemLanguage ."</p>";
                // $content .= "<p class='sticker-category'>". $itemCategory . "</p>";
            $content .= "</div>";



        }
    }
    $content .= "</section>";


    // Js needed for the page, to be placed in js file later
    


    return $content;
}

add_shortcode( 'tct_new_search', '_TCT_new_search' );