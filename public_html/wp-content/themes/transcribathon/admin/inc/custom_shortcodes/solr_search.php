<?php
/*
Shortcode: solr_test
*/


// include required files
include($_SERVER["DOCUMENT_ROOT"].'/wp-load.php');

function _TCT_solr_search( $atts ) {

    $view = $_GET['view'];
    /* Set up facet fields and labels */
    $q = '*:*';
    $page = '0';

    $filter = [];

    foreach($_GET as $par) {
        if(array_search($par, $_GET) == 'view') {
            $view = $par;
        }
        if(array_search($par, $_GET) == 'q' && $par != '') {
            $q = htmlspecialchars($par);
        }
        if(array_search($par, $_GET) == 'ps' && $par != '') {
            $page = (intval($par) - 1) *24;
        }
        // if(array_search($par, $_GET) == 'Languages' && $par != '') {
        //     $fieldName = array_search($par, $_GET);
        //     $filterQuery = $fieldName . '="' . $par . '"';
        //     array_push($filter, $filterQuery );
        // }
        if(array_search($par, $_GET) != 'q' && array_search($par, $_GET) != 'view' && array_search($par, $_GET) != 'ps' ) {
            $fieldName = array_search($par, $_GET);
            $par = str_replace('&&', '"&&"', $par);
            $filterQuery = $fieldName . ':"' . $par . '"';

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
               // 'qf' => 'text',
                'fq' => $filter,
                'sort' => $sort,
                'start' => $page,
                'rows' => '24',
                'facet' => 'on',
                'facet.field' => ['Languages', 'CompletionStatus', 'Categories'],
                ]
            ]
        );
        $context = stream_context_create($options);

	    $data = @file_get_contents($url, false, $context);
        $data = json_decode($data, true);

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
              //  'qf' => 'text',
                'fq' => $filter,
                'sort' => $sort,
                'start' => $page,
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

    $responseData = $data['response'];
    $facetFields = $data['facet_counts']['facet_fields'];
    // Build Page Layout
    $content = '';

    // Pagination
    $pagination = '';
    if(!empty($responseData['docs']) && $responseData['numFound'] > 24) {
        $totalPs = ceil($responseData['numFound'] / 24);
        $currPs = intval($_GET['ps']);
        $pagination .= "<div class='search-pgntn'>";
        if($currPs == null || $currPs <= 3) {
            $pagination .= "<label class='pag-lbl' title='first'> 1";
                $pagination .= "<input type='checkbox' class='pagi-ctrl' form='query-form' value='1' name='ps' onChange='this.form.submit()'>";
            $pagination .= "</label>";
            $pagination .= "<label class='pag-lbl' title='first'> 2";
                $pagination .= "<input type='checkbox' class='pagi-ctrl' form='query-form' value='2' name='ps' onChange='this.form.submit()'>";
            $pagination .= "</label>";
            $pagination .= "<label class='pag-lbl' title='first'> 3";
                $pagination .= "<input type='checkbox' class='pagi-ctrl' form='query-form' value='3' name='ps' onChange='this.form.submit()'>";
            $pagination .= "</label>";
            $pagination .= "<label class='pag-lbl' title='first'><i class=\"fas fa-chevron-right\"></i>";
                $pagination .= "<input type='checkbox' class='pagi-ctrl' form='query-form' value='4' name='ps' onChange='this.form.submit()'>";
            $pagination .= "</label>";
            $pagination .= "<label class='pag-lbl' title='first'><i class=\"fas fa-chevron-double-right\"></i>";
                $pagination .= "<input type='checkbox' class='pagi-ctrl' form='query-form' value='".strval($totalPs)."' name='ps' onChange='this.form.submit()'>";
            $pagination .= "</label>";
    
            $pagination .= "<style>.pag-lbl:nth-of-type(".$currPs."){color:#000;font-weight:500;}</style>";
    
        } else if($currPs > $totalPs - 3) {
            $pagination .= "<label class='pag-lbl' title='first'><i class=\"fas fa-chevron-double-left\"></i>";
                $pagination .= "<input type='checkbox' class='pagi-ctrl' form='query-form' value='1' name='ps' onChange='this.form.submit()'>";
            $pagination .= "</label>";
            $pagination .= "<label class='pag-lbl' title='first'><i class=\"fas fa-chevron-left\"></i>";
                $pagination .= "<input type='checkbox' class='pagi-ctrl' form='query-form' value='".strval($totalPs - 3)."' name='ps' onChange='this.form.submit()'>";
            $pagination .= "</label>";
            $pagination .= "<label class='pag-lbl' title='first'>" . strval($totalPs - 2);
                $pagination .= "<input type='checkbox' class='pagi-ctrl' form='query-form' value='".strval($totalPs - 2)."' name='ps' onChange='this.form.submit()'>";
            $pagination .= "</label>";
            $pagination .= "<label class='pag-lbl' title='first'>" . strval($totalPs - 1);
                $pagination .= "<input type='checkbox' class='pagi-ctrl' form='query-form' value='".strval($totalPs - 1)."' name='ps' onChange='this.form.submit()'>";
            $pagination .= "</label>";
            $pagination .= "<label class='pag-lbl' title='first'>" . strval($totalPs);
                $pagination .= "<input type='checkbox' class='pagi-ctrl' form='query-form' value='".strval($totalPs)."' name='ps' onChange='this.form.submit()'>";
            $pagination .= "</label>";
    
            $pagination .= "<style>.pag-lbl:nth-of-type(".(5 - $totalPs + $currPs)."){color:#000;font-weight:600;}</style>";
        } else {
            $pagination .= "<label class='pag-lbl' title='first'><i class=\"fas fa-chevron-double-left\"></i>";
                $pagination .= "<input type='checkbox' class='pagi-ctrl' form='query-form' value='1' name='ps' onChange='this.form.submit()'>";
            $pagination .= "</label>";
            $pagination .= "<label class='pag-lbl' title='first'>" . strval($currPs - 1);
                $pagination .= "<input type='checkbox' class='pagi-ctrl' form='query-form' value='".strval($currPs - 1)."' name='ps' onChange='this.form.submit()'>";
            $pagination .= "</label>";
            $pagination .= "<label class='pag-lbl curr' title='first'>" . strval($currPs);
                $pagination .= "<input type='checkbox' class='pagi-ctrl' form='query-form' value='".strval($currPs)."' name='ps' onChange='this.form.submit()'>";
            $pagination .= "</label>";
            $pagination .= "<label class='pag-lbl' title='first'>" . strval($currPs + 1);
                $pagination .= "<input type='checkbox' class='pagi-ctrl' form='query-form' value='".strval($currPs + 1)."' name='ps' onChange='this.form.submit()'>";
            $pagination .= "</label>";
            $pagination .= "<label class='pag-lbl' title='first'><i class=\"fas fa-chevron-double-right\"></i>";
                $pagination .= "<input type='checkbox' class='pagi-ctrl' form='query-form' value='".strval($totalPs)."' name='ps' onChange='this.form.submit()'>";
            $pagination .= "</label>";
        }
        $pagination .= "</div>";
    }
    // Input field and Banner
    $content .= '<section class="temp-back" style="min-width: 100vw;">';
        $content .= '<div class="facet-form-search">';
            $content .= "<form id='query-form' action='" . home_url() . "/documents/' method='GET'>";
                $content .= '<div><input class="search-field" type="text" placeholder="Add a search term" name="q" form="query-form" value="' . str_replace('\\', '', htmlspecialchars($_GET['q'])) . '"></div>';
                //$content .= '<div><input class="search-field" type="text" name="view" value="story" form="query-form" hidden></div>';
                $content .= '<div><button type="submit" form="query-form" class="theme-color-background document-search-button"><i class="far fa-search" style="font-size: 20px;"></i></button></div>';
                $content .= '<div class="map-search-page"><a href="' . home_url() . '/documents/map" target="_blank" form="" class="theme-color-background document-search-button"><i class="fal fa-globe-europe" style="font-size: 20px;"></i></a></div>';
                $content .= '<div style="clear:both;"></div>';
            //$content .= "</form>";
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

        $content .= $pagination;

        // right side
        $content .= "<div class='num-results'>";
            if($responseData['numFound'] > 24) {
                $content .= "<div>Showing <span class='num-found'>" . ($responseData['start'] + 1) . "</span> - <span class='num-found'>" . ($responseData['start'] + 24) ." </span> of <span class='num-found'> " . $responseData['numFound'] . "</span> results.</div>";
            } else {
                $content .= "<div>Showing <span class='num-found'>" . $responseData['numFound']." </span> of <span class='num-found'> " . $responseData['numFound'] . "</span> results.</span></div>";
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
                            if($facetFields['CompletionStatus'][$x+1] != 0) {
                                if($_GET['CompletionStatus'] == $facetFields['CompletionStatus'][$x] ) {
                                    $checked = 'checked';
                                }
                                $content .= "<label class='facet-data' title='" . $facetFields['CompletionStatus'][$x] . " (" . $facetFields['CompletionStatus'][$x+1] . ")" . "'>" . $facetFields['CompletionStatus'][$x] . " (" . $facetFields['CompletionStatus'][$x+1] . ")";
                                    $content .= "<input class='search-check' type='checkbox' form='query-form' name='CompletionStatus' value='" . $facetFields['CompletionStatus'][$x] . "' " . $checked . " onChange='this.form.submit()'>";
                                    $content .= "<span class='checkmark'></span>";
                                $content .= "</label>";
                                $checked = '';
                            }
                        }
                    $content .= "</div>";
                }
                if($facetFields['Categories'] != null) {
                    $content .= "<div class='facet-single'>";
                        $content .= "<div class='facet-h' onClick='this.parentElement.classList.toggle(\"uncollapse\");'>DOCUMENT TYPE <i class='fas fa-plus-circle mobi-f'></i></div>";
                        for($x = 0; $x < count($facetFields['Categories']); $x += 2) {
                            if($facetFields['Categories'][$x+1] != 0) {
                                $value = $facetFields['Categories'][$x];
                                $name = 'Categories';
                                if(strpos($_GET['Categories'], $value) !== false) {
                                    $checked = 'checked';
                                    $value = '';
                                    $name = '';
                                } else if ($_GET['Categories'] != '') {
                                    $value .= '&&' . $_GET['Categories'];
                                }
                                $content .= "<label class='facet-data' title='" . $facetFields['Categories'][$x] . " (" . $facetFields['Categories'][$x+1] . ")" . "'>" . $facetFields['Categories'][$x] . " (" . $facetFields['Categories'][$x+1] . ")";
                                    $content .= "<input class='search-check' type='checkbox' form='query-form' name='" . $name . "' value='" . $value . "' " . $checked . " onChange='this.form.submit()'>";
                                    $content .= "<span class='checkmark'></span>";
                                $content .= "</label>";
                                $checked = '';
                            }
                        }
                    $content .= "</div>";
                }
                if($facetFields['edmCountry'] != null) {
                    $content .= "<div class='facet-single'>";
                        $content .= "<div class='facet-h' onClick='this.parentElement.classList.toggle(\"uncollapse\");'>PROVIDING COUNTRY <i class='fas fa-plus-circle mobi-f'></i></div>";
                        for($x = 0; $x < count($facetFields['edmCountry']); $x += 2) {
                            if($facetFields['edmCountry'][$x+1] != 0) {
                                if($_GET['edmCountry'] == $facetFields['edmCountry'][$x] ) {
                                    $checked = 'checked';
                                }
                                $content .= "<label class='facet-data' title='" . $facetFields['edmCountry'][$x] . " (" . $facetFields['edmCountry'][$x+1] . ")" . "'>" . $facetFields['edmCountry'][$x] . " (" . $facetFields['edmCountry'][$x+1] . ")";
                                    $content .= "<input class='search-check' type='checkbox' form='query-form' name='edmCountry' value='" . $facetFields['edmCountry'][$x] . "' " . $checked . " onChange='this.form.submit()'>";
                                    $content .= "<span class='checkmark'></span>";
                                $content .= "</label>";
                                $checked = '';
                            }
                        }
                    $content .= "</div>";
                }
                if($facetFields['dcLanguage'] != null) {
                    $content .= "<div class='facet-single'>";
                        $content .= "<div class='facet-h' onClick='this.parentElement.classList.toggle(\"uncollapse\");'>LANGUAGE <i class='fas fa-plus-circle'></i></div>";
                        for($x = 0; $x < count($facetFields['dcLanguage']); $x += 2) {
                            if($facetFields['dcLanguage'][$x+1] != 0) {
                                if($_GET['dcLanguage'] == $facetFields['dcLanguage'][$x] ) {
                                    $checked = 'checked';
                                }
                                $facetLabel = '';
                                $facetCleanAr = [];
                                $facetLabelAr = explode(' || ', $facetFields['dcLanguage'][$x]);
                                foreach($facetLabelAr as $lblLang) {
                                    if(strlen($lblLang) < 4 && strlen($lblLang) > 1) {
                                        array_push($facetCleanAr, ucfirst($lblLang));
                                    }
                                }

                                $facetLabel = implode(' - ', $facetCleanAr);
                                $content .= "<label class='facet-data' title='" . $facetFields['dcLanguage'][$x] . " (" . $facetFields['dcLanguage'][$x+1] . ")" . "'>" . $facetLabel . " (" . $facetFields['dcLanguage'][$x+1] . ")";
                                    $content .= "<input class='search-check' type='checkbox' form='query-form' name='dcLanguage' value='" . $facetFields['dcLanguage'][$x] . "' " . $checked . " onChange='this.form.submit()'>";
                                    $content .= "<span class='checkmark'></span>";
                                $content .= "</label>";
                                $checked = '';
                            }
                        }
                    $content .= "</div>";
                }
                if($facetFields['Dataset'] != null) {
                    $content .= "<div class='facet-single'>";
                        $content .= "<div class='facet-h' onClick='this.parentElement.classList.toggle(\"uncollapse\");'>DATASET <i class='fas fa-plus-circle'></i></div>";
                        for($x = 0; $x < count($facetFields['Dataset']); $x += 2) {
                            if($facetFields['Dataset'][$x+1] != 0) {
                                if($_GET['Dataset'] == $facetFields['Dataset'][$x] ) {
                                    $checked = 'checked';
                                }
                                $content .= "<label class='facet-data' title='" . $facetFields['Dataset'][$x] . " (" . $facetFields['Dataset'][$x+1] . ")" . "'>" . $facetFields['Dataset'][$x] . " (" . $facetFields['Dataset'][$x+1] . ")";
                                    $content .= "<input class='search-check' type='checkbox' form='query-form' name='Dataset' value='" . $facetFields['Dataset'][$x] . "' " . $checked . " onChange='this.form.submit()'>";
                                    $content .= "<span class='checkmark'></span>";
                                $content .= "</label>";
                                $checked = '';
                            }
                        }
                    $content .= "</div>";
                }
                if($facetFields['edmProvider'] != null) {
                    $content .= "<div class='facet-single'>";
                        $content .= "<div class='facet-h' onClick='this.parentElement.classList.toggle(\"uncollapse\");'>PROVIDER <i class='fas fa-plus-circle'></i></div>";
                        for($x = 0; $x < count($facetFields['edmProvider']); $x += 2) {
                            if($facetFields['edmProvider'][$x+1] != 0) {
                                if($_GET['edmProvider'] == $facetFields['edmProvider'][$x] ) {
                                    $checked = 'checked';
                                }
                                $content .= "<label class='facet-data' title='" . $facetFields['edmProvider'][$x] . " (" . $facetFields['edmProvider'][$x+1] . ")" . "'>" . $facetFields['edmProvider'][$x] . " (" . $facetFields['edmProvider'][$x+1] . ")";
                                    $content .= "<input class='search-check' type='checkbox' form='query-form' name='edmProvider' value='" . $facetFields['edmProvider'][$x] . "' " . $checked . " onChange='this.form.submit()'>";
                                    $content .= "<span class='checkmark'></span>";
                                $content .= "</label>";
                                $checked = '';
                            }
                        }
                    $content .= "</div>";
                }
                if($facetFields['Languages'] != null) {
                    $content .= "<div class='facet-single'>";
                        $content .= "<div class='facet-h' onClick='this.parentElement.classList.toggle(\"uncollapse\");'>LANGUAGE <i class='fas fa-plus-circle'></i></div>";
                        for($x = 0; $x < count($facetFields['Languages']); $x += 2) {
                            if($facetFields['Languages'][$x+1] != 0) {
                                $value = $facetFields['Languages'][$x];
                                $name = 'Languages';
                                if(strpos($_GET['Languages'], $value) !== false) {
                                    $checked = 'checked';
                                    $value = '';
                                    $name = '';
                                } else if ($_GET['Languages'] != '') {
                                    $value .= '&&' . $_GET['Languages'];
                                }
                                $content .= "<label class='facet-data' title='" . $facetFields['Languages'][$x] . " (" . $facetFields['Languages'][$x+1] . ")" . "'>" . $facetFields['Languages'][$x] . " (" . $facetFields['Languages'][$x+1] . ")";
                                    $content .= "<input class='search-check' type='checkbox' form='query-form' name='" . $name . "' value='" . $value . "' " . $checked . " onChange='this.form.submit()'>";
                                    $content .= "<span class='theme-color-background checkmark'></span>";
                                    $content .= "<span class='checkmark'></span>";
                                $content .= "</label>";
                                $checked = '';
                            }
                        }
                    $content .= "</div>";
                }

            //$content .= "</form>";

        $content .= "</div>";

        // Right side, search result 'stickers'
        
            if($responseData['docs']) {
                $content .= "<div class='result-stickers'>";

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

                        $content .= "<div class='search-page-single-result'><a href='" . home_url() . "/documents/story/?story=" . $doc['StoryId'] . "'>";
                            $content .= "<div class='search-page-result-image'>";
                                $content .= "<img src='" . $imageLink . "' alt='result image' width='280' height='140'>";
                            $content .= "</div>";
                                $content .= $compStatus;
                            $content .= "<div style='clear:both;'></div>";
                            $content .= "<div class='single-title'><h2 class='theme-color'>" . $doc['dcTitle'] . "</h2></div>";
                        $content .= "</a></div>";
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

                        $content .= "<div class='search-page-single-result'><a href='" . home_url() . "/documents/story/item/?item=" . $doc['ItemId'] . "'>";
                            $content .= "<div class='search-page-result-image'>";
                                $content .= "<img src='" . $imageLink . "' alt='result image' width='280' height='140'>";
                            $content .= "</div>";
                                $content .= $compStatus;
                            $content .= "<div style='clear:both;'></div>";
                            $content .= "<div class='single-title'><h2 class='theme-color'>" . $doc['Title'] . "</h2></div>";
                        $content .= "</a></div>";
                    }
                }
                $content .= "<div class='bottom-pag'>";
                    $content .= $pagination;
                $content .= "</div>";
                $content .= "</div>";
                $content .= "<div style='clear:both;'></div>";


            } else {
                $content .= "<div class='result-stickers'>";
                   $content .= "<h2> We are sorry! We couldn't find any match for: '" . $q . "'.</h2>";
                $content .= "</div>";
            }



    $content .= "</section>";


    return $content;
}
add_shortcode( 'solr_search',  '_TCT_solr_search' );
?>
