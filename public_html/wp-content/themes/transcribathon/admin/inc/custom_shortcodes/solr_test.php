<?php
/* 
Shortcode: solr_test
*/


// include required files
include($_SERVER["DOCUMENT_ROOT"].'/wp-load.php');

function _TCT_solr_test( $atts ) { 

    $view = $_GET['view'];
    /* Set up facet fields and labels */
    $q = '*:*';
    $filter = [];

    foreach($_GET as $par) {
        if(array_search($par, $_GET) == 'view') {
            $view = $par;
        }
        if(array_search($par, $_GET) == 'q' && $par != '') {
            $q = $par;
        }
        if(array_search($par, $_GET) != 'q' && array_search($par, $_GET) != 'view') {
            $fieldName = array_search($par, $_GET);
            $filterQuery = $fieldName . ':' . $par;
            array_push($filter, $filterQuery );
        }
    }

    if($view == 'items') {
        $sort = 'Timestamp desc';
        if($_GET['q'] != '') {
            $sort = '';
        }

        /// Items Facet Field   
        $url = TP_SOLR . '/solr/Items/query';
        $options = [
            'http' => [
                'header' => [
                    'Content-type: application/json',
            ],
                'method' => 'GET'
            ]
        ];
        $options['http']['content'] = json_encode(
            ['params' =>[
                'q' => $q,
                'fq' => $filter,
                'sort' => $sort,
                'start' => '24',
                'rows' => '24',
                'facet' => 'on',
                'facet.field' => ['Languages', 'CompletionStatus', 'Categories'],
                ]
            ]
        );
        $context = stream_context_create($options);
    
	    $data = @file_get_contents($url, false, $context);
        $data = json_decode($data, true);
        //dd($data);
    } else {

        $sort = 'StoryId desc';
        if($_GET['q'] != '') {
            $sort = '';
        }
        // Story Facets
        $url = TP_SOLR . '/solr/Stories/query';
        $options = [
            'http' => [
                'header' => [
                    'Content-type: application/json',
            ],
                'method' => 'GET'
            ]
        ];
        $options['http']['content'] = json_encode(
            ['params'=>[
                'q'=> $q,
                'fq' => $filter,
                'sort' => $sort,
                'rows' => '24',
                'facet' => 'on',
                'facet.field' => ['CompletionStatus', 'Categories', 'edmCountry', 'Dataset', 'dcLanguage', 'edmProvider'],
                ]
            ]
        );
        $context = stream_context_create($options);
        
        $data = @file_get_contents($url, false, $context);
        $data = json_decode($data, true);
    }
    //dd($storyFacets);
    $responseData = $data['response'];
    $facetFields = $data['facet_counts']['facet_fields'];

    // GET name of url parameter
   // var_dump($_GET);

    ////
  //dd($facetFields);
    //dd($result);

    // Build Page Layout
    $content = '';
    
    // Input field and Banner
    $content .= '<section class="temp-back">';
        $content .= '<div class="facet-form-search">';
            $content .= "<form id='query-form' action='" . home_url() . "/solr-test/' method='GET'>";
                $content .= '<div><input class="search-field" type="text" placeholder="Add a search term" name="q" form="query-form" value="' . str_replace('\\', '', htmlspecialchars($_GET['q'])) . '"></div>';
                //$content .= '<div><input class="search-field" type="text" name="view" value="story" form="query-form" hidden></div>';
                $content .= '<div><button type="submit" form="query-form" class="theme-color-background document-search-button"><i class="far fa-search" style="font-size: 20px;"></i></button></div>';
                $content .= '<div class="map-search-page"><a href="' . home_url() . '/documents/map" target="_blank" form="" class="theme-color-background document-search-button"><i class="fal fa-globe-europe" style="font-size: 20px;"></i></a></div>';
                $content .= '<div style="clear:both;"></div>';
            $content .= "</form>";
        $content .= '</div>';
    $content .= '</section>';
    // NAvigation section (switch between item/story, pagination, and numbers of results)
    $content .= "<section class='search-navigation'>";
        // left side with buttons
        $content .= "<div class='str-itm-switch'>";
            $content .= "<div class='filter-h'>Refine your search</div>";
            // Get Story/Item view
            $stryCheck = '';
            $itmCheck = '';
            if($_GET['view'] == 'stories' || $_GET['view'] == null) {
                $stryCheck = 'checked';
                $content .= "<style>#stry-btn{background:#0a72cc!important;color:#fff!important;}</style>";
            } else {
                $itmCheck = 'checked';
                $content .= "<style>#itm-btn{background:#0a72cc!important;color:#fff!important;}</style>";
            }
            $content .= "<div id='stry-btn'>";
                $content .= "<label>Stories";
                    $content .= "<input type='radio' form='query-form' name='view' value='stories' style='opacity:0;position:absolute;' onChange='this.form.submit();' " . $stryCheck . ">";
                $content .= "</label>";
            $content .= "</div>";
            $content .= "<div id='itm-btn'>";
                $content .= "<label>Items";
                    $content .= "<input type='radio' form='query-form' name='view' value='items' style='opacity:0;position:absolute;' onChange='this.form.submit();' " . $itmCheck . ">";
                $content .= "</label>";
            $content .= "</div>";
        $content .= "</div>";
        // right side
        $content .= "<div class='num-results'><i class=\"fa-regular fa-line-columns\"></i>";
            if($responseData['numFound'] > 24) {
                $content .= "<div><span>" . ($responseData['start'] + 1) . "</span><span> - " . ($responseData['start'] + 24) ." of " . $responseData['numFound'] . "</div>";  
            }
        $content .= "</div>";
    $content .= "</section>";
    // 'Body' of the page / Left Facet Fields Right Results
    $content .= "<section class='search-result'>";
        // Left side, facets
        $content .= "<div class='facet-menu'>";
            //$content .= "<form id='facet-form'>";
                $checked = '';
                if($facetFields['CompletionStatus'] != null) {
                    $content .= "<div class='facet-single'>";
                        $content .= "<div class='facet-h'>COMPLETION STATUS</div>";
                        for($x = 0; $x < count($facetFields['CompletionStatus']); $x += 2) {
                            if($_GET['CompletionStatus'] != null && ($_GET['CompletionStatus'] == $facetFields['CompletionStatus'][$x] )) {
                                $checked = 'checked';
                            }
                            $content .= "<label class='facet-data' title='" . $facetFields['CompletionStatus'][$x] . "'>" . $facetFields['CompletionStatus'][$x] . " (" . $facetFields['CompletionStatus'][$x+1] . ")";
                                $content .= "<input type='checkbox' form='query-form' name='CompletionStatus' value='" . $facetFields['CompletionStatus'][$x] . "' " . $checked . " onChange='this.form.submit()'>";
                                $content .= "<span class='theme-color-background checkmark'></span>";
                            $content .= "</label>";
                            $checked = '';
                        }
                    $content .= "</div>";
                }
                if($facetFields['Categories'] != null) {
                    $content .= "<div class='facet-single'>";
                        $content .= "<div class='facet-h'>DOCUMENT TYPE</div>";
                        for($x = 0; $x < count($facetFields['Categories']); $x += 2) {
                            if($_GET['Categories'] != null && ($_GET['Categories'] == $facetFields['Categories'][$x] )) {
                                $checked = 'checked';
                            }
                            $content .= "<label class='facet-data' title='" . $facetFields['Categories'][$x] . "'>" . $facetFields['Categories'][$x] . " (" . $facetFields['Categories'][$x+1] . ")";
                                $content .= "<input type='checkbox' form='query-form' name='Categories' value='" . $facetFields['Categories'][$x] . "' " . $checked . " onChange='this.form.submit()'>";
                                $content .= "<span class='theme-color-background checkmark'></span>";
                            $content .= "</label>";
                            $checked = '';
                        }
                    $content .= "</div>";
                }
                if($facetFields['edmCountry'] != null) {
                    $content .= "<div class='facet-single'>";
                        $content .= "<div class='facet-h'>PROVIDING COUNTRY</div>";
                        for($x = 0; $x < count($facetFields['edmCountry']); $x += 2) {
                            if($_GET['edmCountry'] != null && ($_GET['edmCountry'] == $facetFields['edmCountry'][$x] )) {
                                $checked = 'checked';
                            }
                            $content .= "<label class='facet-data' title='" . $facetFields['edmCountry'][$x] . "'>" . $facetFields['edmCountry'][$x] . " (" . $facetFields['edmCountry'][$x+1] . ")";
                                $content .= "<input type='checkbox' form='query-form' name='edmCountry' value='" . $facetFields['edmCountry'][$x] . "' " . $checked . " onChange='this.form.submit()'>";
                                $content .= "<span class='theme-color-background checkmark'></span>";
                            $content .= "</label>";
                            $checked = '';
                        }
                    $content .= "</div>";
                }
                if($facetFields['Dataset'] != null) {
                    $content .= "<div class='facet-single'>";
                        $content .= "<div class='facet-h' onClick='this.parentElement.classList.toggle(\"uncollapse\");'>DATASET <i class='fas fa-plus-circle'></i></div>";
                        for($x = 0; $x < count($facetFields['Dataset']); $x += 2) {
                            if($_GET['Dataset'] != null && ($_GET['Dataset'] == $facetFields['Dataset'][$x] )) {
                                $checked = 'checked';
                            }
                            $content .= "<label class='facet-data' title='" . $facetFields['Dataset'][$x] . "'>" . $facetFields['Dataset'][$x] . " (" . $facetFields['Dataset'][$x+1] . ")";
                                $content .= "<input type='checkbox' form='query-form' name='Dataset' value='" . $facetFields['Dataset'][$x] . "' " . $checked . " onChange='this.form.submit()'>";
                                $content .= "<span class='theme-color-background checkmark'></span>";
                            $content .= "</label>";
                            $checked = '';
                        }
                    $content .= "</div>";
                }
                if($facetFields['edmProvider'] != null) {
                    $content .= "<div class='facet-single'>";
                        $content .= "<div class='facet-h' onClick='this.parentElement.classList.toggle(\"uncollapse\");'>PROVIDER <i class='fas fa-plus-circle'></i></div>";
                        for($x = 0; $x < count($facetFields['edmProvider']); $x += 2) {
                            if($_GET['edmProvider'] != null && ($_GET['edmProvider'] == $facetFields['edmProvider'][$x] )) {
                                $checked = 'checked';
                            }
                            $content .= "<label class='facet-data' title='" . $facetFields['edmProvider'][$x] . "'>" . $facetFields['edmProvider'][$x] . " (" . $facetFields['edmProvider'][$x+1] . ")";
                                $content .= "<input type='checkbox' form='query-form' name='edmProvider' value='" . $facetFields['edmProvider'][$x] . "' " . $checked . " onChange='this.form.submit()'>";
                                $content .= "<span class='theme-color-background checkmark'></span>";
                            $content .= "</label>";
                            $checked = '';
                        }
                    $content .= "</div>";
                }
                if($facetFields['dcLanguage'] != null) {
                    $content .= "<div class='facet-single'>";
                        $content .= "<div class='facet-h' onClick='this.parentElement.classList.toggle(\"uncollapse\");'>LANGUAGE <i class='fas fa-plus-circle'></i></div>";
                        for($x = 0; $x < count($facetFields['dcLanguage']); $x += 2) {
                            if($_GET['dcLanguage'] != null && ($_GET['dcLanguage'] == $facetFields['dcLanguage'][$x] )) {
                                $checked = 'checked';
                            }
                            $content .= "<label class='facet-data' title='" . $facetFields['dcLanguage'][$x] . "'>" . $facetFields['dcLanguage'][$x] . " (" . $facetFields['dcLanguage'][$x+1] . ")";
                                $content .= "<input type='checkbox' form='query-form' name='Language' value='" . $facetFields['dcLanguage'][$x] . "' " . $checked . " onChange='this.form.submit()'>";
                                $content .= "<span class='theme-color-background checkmark'></span>";
                            $content .= "</label>";
                            $checked = '';
                        }
                    $content .= "</div>";
                }
                if($facetFields['Languages'] != null) {
                    $content .= "<div class='facet-single'>";
                        $content .= "<div class='facet-h' onClick='this.parentElement.classList.toggle(\"uncollapse\");'>LANGUAGE <i class='fas fa-plus-circle'></i></div>";
                        for($x = 0; $x < count($facetFields['Languages']); $x += 2) {
                            if($_GET['Languages'] != null && ($_GET['Languages'] == $facetFields['Languages'][$x] )) {
                                $checked = 'checked';
                            }
                            $content .= "<label class='facet-data' title='" . $facetFields['Languages'][$x] . "'>" . $facetFields['Languages'][$x] . " (" . $facetFields['Languages'][$x+1] . ")";
                                $content .= "<input type='checkbox' form='query-form' name='Language' value='" . $facetFields['Languages'][$x] . "' " . $checked . " onChange='this.form.submit()'>";
                                $content .= "<span class='theme-color-background checkmark'></span>";
                            $content .= "</label>";
                            $checked = '';
                        }
                    $content .= "</div>";
                }
            //$content .= "</form>";
            
        $content .= "</div>";

        // Right side, search result 'stickers'
        $content .= "<div class='result-stickers'>";
            if(count($responseData['docs']) != 0) {
                foreach($responseData['docs'] as $doc) {
    
                    // Completion status
                    if($view != 'items') {
                        $total = $doc['EditAmount'] + $doc['CompletedAmount'] + $doc['NotStartedAmount'] + $doc['ReviewAmount'];
                        $completed = ($doc['CompletedAmount'] / $total) * 100; 
                        $review = ($doc['ReviewAmount'] / $total) * 100; 
                        $edit = ($doc['EditAmount'] / $total) * 100; 
                        $notStarted = ($doc['NotStartedAmount'] / $total) * 100; 
    
                        $compStatus = "<div class='search-page-single-status'>";
                            $compStatus .= "<div class='search-status' style='width:" . $completed . "%;background-color:#61e02f;z-index:4;' title='Completed:" . round($completed) . "%'>&nbsp</div>";
                            $compStatus .= "<div class='search-status' style='width:" . ($completed + $review) . "%;background-color:#ffc720;z-index:3;' title='Review:" . round($review) . "%'>&nbsp</div>";
                            $compStatus .= "<div class='search-status' style='width:" . ($completed + $review + $edit) . "%;background-color:#fff700;z-index:2;' title='Edit:" . round($edit) . "%'>&nbsp</div>";
                            $compStatus .= "<div class='search-status' style='width:100%;background-color:#eeeeee;z-index:1;' title='Not Started:" . round($notStarted) . "%'>&nbsp</div>";
                        $compStatus .= "</div>";
                        // Image
                        $image = json_decode($doc['PreviewImageLink'], true);
                        $imageLink = createImageLinkFromData($image, array('size' => '280,140', 'page' => 'search'));
        
                        $content .= "<div class='search-page-single-result'>";
                            $content .= "<div class='search-page-result-image'>";
                                $content .= "<img src='" . $imageLink . "' alt='result image' width='280' height='140'>";
                            $content .= "</div>";
                                $content .= $compStatus;
                            $content .= "<div style='clear:both;'></div>";
                            $content .= "<div class='single-title'><h2 class='theme-color'>" . $doc['dcTitle'] . "</h2></div>";
                        $content .= "</div>";
                    } else {

                        // Progress bar
                        $progressData = array(
                            $doc['TranscriptionStatus'],
                            $doc['DescriptionStatus'],
                            $doc['LocationStatus'],
                            $doc['TaggingStatus'],
                        );
                        $progressCount = array (
                                        'Not Started' => 0,
                                        'Edit' => 0,
                                        'Review' => 0,
                                        'Completed' => 0
                                    );
                        // Save each status occurence
                        foreach ($progressData as $status) {
                            $progressCount[$status] += 25;
                        }
                        

                        $compStatus = "<div class='search-page-single-status'>";
                            $compStatus .= "<div class='search-status' style='width:" . $progressCount['Completed'] . "%;background-color:#61e02f;z-index:4;' title='Completed:" . round($progressCount['Completed']) . "%'>&nbsp</div>";
                            $compStatus .= "<div class='search-status' style='width:" . ($progressCount['Completed'] + $progressCount['Review']) . "%;background-color:#ffc720;z-index:3;' title='Review:" . round($progressCount['Review']) . "%'>&nbsp</div>";
                            $compStatus .= "<div class='search-status' style='width:" . ($progressCount['Completed'] + $progressCount['Review'] + $progressCount['Edit']) . "%;background-color:#fff700;z-index:2;' title='Edit:" . round($progressCount['Edit']) . "%'>&nbsp</div>";
                            $compStatus .= "<div class='search-status' style='width:100%;background-color:#eeeeee;z-index:1;' title='Not Started:" . round($progressCount['Not Started']) . "%'>&nbsp</div>";
                        $compStatus .= "</div>";


                        $image = json_decode($doc['PreviewImageLink'], true);
                        $imageLink = createImageLinkFromData($image, array('size' => '280,140', 'page' => 'search'));
        
                        $content .= "<div class='search-page-single-result'>";
                            $content .= "<div class='search-page-result-image'>";
                                $content .= "<img src='" . $imageLink . "' alt='result image' width='280' height='140'>";
                            $content .= "</div>";
                                $content .= $compStatus;
                            $content .= "<div style='clear:both;'></div>";
                            $content .= "<div class='single-title'><h2 class='theme-color'>" . $doc['Title'] . "</h2></div>";
                        $content .= "</div>";
                    }
                }
            }
        $content .= "</div>";
        $content .= "<div style='clear:both;'></div>";
        
    $content .= "</section>";
    
    


    /// TODO MOVE STYLES TO SEPARATE FILE
    $content .= "<style>
        .search-navigation {
            height: 85px;
            background: #fff;
        }
        .facet-menu {
            float: left;
            width: 16%;
        }
        .result-stickers {
            float: right;
            width: 82%;
            background: #fff;
            display: flex;
            flex-wrap: wrap;
        }
        .str-itm-switch {
            width: 21vw;
            display: inline-block;
            text-transform: uppercase;
            padding-top: 15px;
            padding-left: 50px;
        }
        #stry-btn, #itm-btn {
            display: inline-block;
            border: 1px solid #dedede;
            background-color: #fff;
            padding: 6px 12px;
            outline: none;
            margin-top: 10px;
            text-align: center;
            cursor: pointer;
        }
        .str-itm-switch label {
            cursor: pointer;
        }
        #stry-btn {
            border-top-left-radius: 6px;
            border-bottom-left-radius: 6px;
            border-right: none;
        }
        #itm-btn {
            border-top-right-radius: 6px;
            border-bottom-right-radius: 6px;
            border-left: none;
        }
        .num-results {
            display: inline-block;
            float: right;
            padding-top: 15px;
            padding-right: 15px;
        }
        .search-page-single-status {
            width: 94%;
            margin: 0 auto;
            max-height: 40px;
            position: relative;
            margin-bottom: 25px;
        }
        .search-status {
            display: inline-block;
            min-height: 20px;
            max-height: 20px;
            position: absolute;
            top: 0;
            left: 0;
        }
        .search-page-single-result {
            display: inline-block;
            border: 1px solid #eeeeee;
            padding: 0;
            margin: 5px 10px 5px 0px;
            width: 300px;
            height: 220px;
        }
        .search-page-result-image {
            width: 94%;
            margin: 0 auto;
        }
        .single-title {
            width: 94%;
            margin: 0 auto;
        }
        .facet-single {
            margin-top: 25px;
            margin-left: 50px;
            width: 70%;
            max-height: 316px;
            overflow: hidden;
        }
        .facet-single.uncollapse {
            max-height: unset;
        }
        .facet-single i {
            color: #0a72cc;
        }
        .facet-h {
            display: list-item;
            color: #000;
            margin: 15px 0;
            font-weight: 500;
        }
        .facet-data {
            display: block;
            color: var(--main-color);
            cursor: pointer;
            margin-left: 0px!important;
            text-overflow: ellipsis;
            overflow: hidden;
            white-space: nowrap;
            position: relative;
            padding-left: 20px;
        }
        .facet-data input {
            margin-right: 5px;
            position: absolute;
            top: 6px;
            left: 0px;
        }
        .single-title h2 {
            font-size: 14px!important;
            font-family: var(--p-font-family);
        }
        
    
    </style>";

    // Javascript to separate
    $content .= "<script>
    
    
    </script>";
    

    return $content;
}
add_shortcode( 'solr_test',  '_TCT_solr_test' );
?>