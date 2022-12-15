<?php
$theme_sets = get_theme_mods();

function reverseQueryParameterUrlEncode($string) {
    $entities = array('%5C', '%22');
    $replacements = array('', '"');
    return str_replace($entities, $replacements, urlencode($string));
}

/* Set up facet fields and labels */

$storyFacetFields = [
    [
        "fieldName" => "CompletionStatus",
        "fieldLabel" => "COMPLETION STATUS"],
    [
        "fieldName" => "edmLanguage",
        "fieldLabel" => "LANGUAGE"],
    [
        "fieldName" => "dcLanguage",
        "fieldLabel" => "DOCUMENT LANGUAGE"],
    [
        "fieldName" => "Categories",
        "fieldLabel" => "DOCUMENT TYPE"],
    [
        "fieldName" => "edmCountry",
        "fieldLabel" => "PROVIDING COUNTRY"],
    [
        "fieldName" => "Dataset",
        "fieldLabel" => "DATASET"],
    [
        "fieldName" => "edmProvider",
        "fieldLabel" => "AGGREGATOR"]
];

$itemFacetFields = [
    [
        "fieldName" => "CompletionStatus",
        "fieldLabel" => "COMPLETION STATUS"],
    [
        "fieldName" => "Categories",
        "fieldLabel" => "DOCUMENT TYPE"],
    [
        "fieldName" => "Languages",
        "fieldLabel" => "LANGUAGES"]
];


$itemPage = $_GET['pi'];
$storyPage = $_GET['ps'];

 // #### Story Solr request start ####

 // Build query from url parameters
$url = TP_SOLR . '/solr/Stories/select?facet=on';
$qsRaw = htmlspecialchars(!empty($_GET['qs']) ? reverseQueryParameterUrlEncode($_GET['qs']) : '*:*');
 //echo $qsRaw;
if(!strpos($qsRaw, '+OR+') && !strpos($qsRaw, '+%7C%7C+')){
    $qs = str_replace('+', '+AND+', $qsRaw);
} else {
    $qs = $qsRaw;
}

$url .= '&q=' . $qs . '+OR+';
$url .= 'StoryId:("' . $qs . '")';

foreach ($storyFacetFields as $storyFacetField) {
    $url .= '&facet.field='.urlencode($storyFacetField['fieldName']);
}

 $url .= '&fq=';

 $url .= "(ProjectId:\"".get_current_blog_id()."\")";
 $first = true;
 for ($j = 0; $j < sizeof($storyFacetFields); $j++) {
    for ($i = 0; $i < sizeof($_GET[$storyFacetFields[$j]['fieldName']]); $i++) {
        if ($first == true) {
            $url .= "+AND+";
            $first = false;
        }
        if ($i == 0) {
            $url .= "(";
        }
        $url .= urlencode($storyFacetFields[$j]['fieldName']).':"'.str_replace(" ", "+", urlencode($_GET[$storyFacetFields[$j]['fieldName']][$i])).'"';
        if (($i + 1) < sizeof($_GET[$storyFacetFields[$j]['fieldName']])) {
            $url .= "+OR+";
        }
        else {
            $url .= ")";
            if (($j + 1) < sizeof($storyFacetFields)) {
                for ($k = ($j + 1); $k < sizeof($storyFacetFields); $k++) {
                    if (sizeof($_GET[$storyFacetFields[$k]['fieldName']]) > 0) {
                        $url .= "+AND+";
                        break;
                    }
                }
            }
        }
    }
 }
 if ($storyPage != null && is_numeric($storyPage) && $storyPage != 0){
    $url .= "&rows=24&start=".(($storyPage - 1) * 24);
 }
 else {
    $url .= "&rows=24&start=0";
 }
 $url .= "&sort=StoryId%20desc";
 /* echo($url); */

 $requestType = "GET";

 include get_stylesheet_directory() . '/admin/inc/custom_scripts/send_api_request.php';

 $solrStoryData = json_decode($result, true);

 $storyCount = $solrStoryData['response']['numFound'];

 if ($storyPage != null && is_numeric($storyPage) && (($storyPage - 1) * 24) < $storyCount && $storyPage != 0){
     $storyStart = (($storyPage - 1) * 24) + 1;
     $storyEnd = $storyPage * 24;
 }
 else {
     $storyPage = 1;
     $storyStart = 1;
     $storyEnd = 24;
 }

 // #### Story Solr request end ####


 // #### Item Solr request start ####

 // Build query from url parameters
 $url = TP_SOLR . '/solr/Items/select?facet=on';

 $qiRaw = htmlspecialchars(!empty($_GET['qi']) ? reverseQueryParameterUrlEncode($_GET['qi']) : '*:*');
 if(!strpos($qiRaw, '+OR+') && !strpos($qiRaw, '+%7C%7C+')){
    $qi = str_replace('+', '+AND+', $qiRaw);
} else {
    $qi = $qiRaw;
}
 $url .= '&q=' . $qi;
 $url .= '+OR+';
 $url .= 'ItemId:("' . $qi . '")';


 foreach ($itemFacetFields as $itemFacetField) {
    $url .= '&facet.field='.$itemFacetField['fieldName'];
 }

 $url .= '&fq=';

 for ($j = 0; $j < sizeof($itemFacetFields); $j++) {
    for ($i = 0; $i < sizeof($_GET[$itemFacetFields[$j]['fieldName']]); $i++) {
        if ($i == 0) {
            $url .= "(";
        }
        $url .= urlencode($itemFacetFields[$j]['fieldName']).':"'.str_replace(" ", "+", urlencode($_GET[$itemFacetFields[$j]['fieldName']][$i])).'"';
        if ($i + 1 < sizeof($_GET[$itemFacetFields[$j]['fieldName']])) {
            $url .= "+OR+";
        }
        else {
            $url .= ")";
            if (($j + 1) < sizeof($itemFacetFields) && sizeof($_GET[$itemFacetFields[$j+1]['fieldName']]) > 0) {
                for ($k = ($j + 1); $k < sizeof($itemFacetFields); $k++) {
                    if (sizeof($_GET[$itemFacetFields[$k]['fieldName']]) > 0) {
                        $url .= "+AND+";
                        break;
                    }
                }
            }
        }
    }
 }

 if ($itemPage != null && is_numeric($itemPage) && $itemPage != 0){
    $url .= "&rows=24&start=".(($itemPage - 1) * 24);
 }
 else {
    $url .= "&rows=24&start=0";
 }
 $url .= "&sort=Timestamp%20desc";
 $requestType = "GET";

 include get_stylesheet_directory() . '/admin/inc/custom_scripts/send_api_request.php';

 $solrItemData = json_decode($result, true);

 $itemCount = $solrItemData['response']['numFound'];

 if ($itemPage != null && is_numeric($itemPage) && (($itemPage - 1) * 24) < $itemCount && $itemPage != 0){
     $itemStart = (($itemPage - 1) * 24) + 1;
     $itemEnd = $itemPage * 24;
 }
 else {
     $itemPage = 1;
     $itemStart = 1;
     $itemEnd = 24;
 }

 // #### Item Solr request end ####



// Get status data
$url = TP_API_HOST . "/tp-api/completionStatus";
$requestType = "GET";

include dirname(__FILE__)."/../../custom_scripts/send_api_request.php";

// Save status data
$statusTypes = json_decode($result, true);

 $content = "";

 $content .= "<style>
                 .search-bar input::placeholder {
                     color: ".$theme_sets['vantage_general_link_color'].";
                 }
             </style>";

// Show grid view as default
$view = "grid";

if (isset($_GET['view']) && $_GET['view'] != "") {
    $view = $_GET['view'];
}

$content .= '<script>
                jQuery ( document ).ready(function() {';
                    if ($view == "list") {
                        $content .= 'jQuery(".search-results-list-radio").click()';
                    }
    $content .= '});
            </script>';



$itemTabContent = "";
$storyTabContent = "";

$clearButton = "";
if (isset($_GET['qs']) || isset($_GET['qi']))  {
    $clearButton .= "<a style='text-decoration:none; outline:none;' href='/documents'>clear filters</a>";
}


    // #### Header Search Start ####

    $searchContent = '<section class="temp-back story-search-f" style="height: 200px;">';

        $searchContent .= "<div class='search-form-style'>";
        //    $searchContent .= "<form id='story-search-form' action='".home_url()."/documents/' method='get'>";
                $searchContent .= "<div style='width:400px;'>";
                    $searchContent .= "<input type='text' id='storySearchV' name='qs' form='story-facet-form' value='".htmlspecialchars($_GET['qs'])."' placeholder='Add a search term' class='search-text'>";
                    $searchContent .= "<input type='text' id='itemSearchV' name='qi' form='story-facet-form' value='".htmlspecialchars($_GET['qs'])."' hidden>";
                    $searchContent .= "<input type='submit' form='story-facet-form' value='🔎︎' class='search-submitV theme-color-background' style='position:relative;height:49px;bottom:1.5px;'>";
                    $searchContent .= "<a href='/map' target='_blank' class='theme-color-background'><i class='fal fa-globe-europe' style='font-size: 20px;'></i></a>";
                $searchContent .= "</div>";
        //    $searchContent .= "</form>";

        $searchContent .= "</div>";
    $searchContent .= '</section>';


    //$content .= $searchContent;

    // #### Header Search End ####

        // $content .= "<div class='primary-full-width'>";
        //     $content .= '<div class="complete-search-content">';

        // $itemTabContent .= "<div class='primary-full-width'>";
        //     $itemTabContent .= '<div class="complete-search-content">';

            // #### Facets Start ####
            $searchContent .= '<div class="search-page-mobile-facets">';
                $searchContent .= '<i class="fas fa-bars"></i>';
            $searchContent .= '</div>';
            $searchContent .= '<div id="story-search-container" class="search-content-left">';
                $searchContent .= '<div class="left-buttons">';
                    $searchContent .= '<p class="theme-color">REFINE YOUR SEARCH</p>';

                    // Item/Story switcher
                    $searchContent .= '<div class="search-page-tab-container">';
                        $searchContent .= '<ul class="content-view-bar" style="padding: 6px;">';
                            $searchContent .= '<li>';
                                $searchContent .= '<button class="search-page-tab-button left search-page-story-tab-button theme-color-background">';
                                    $searchContent .= 'STORIES';
                                $searchContent .= '</button>';
                            $searchContent .= '</li>';
                            $searchContent .= '<li>';
                                $searchContent .= '<button class="search-page-tab-button right search-page-item-tab-button">';
                                    $searchContent .= 'ITEMS';
                                $searchContent .= '</button>';
                            $searchContent .= '</li>';
                        $searchContent .= '</ul>';
                    $searchContent .= '</div>';
                $searchContent .= '</div>';

                    $searchContent .= '<div class="result-viewtype">';
                    $searchContent .= "<div class='story-results'>";
                    if($storyCount >= $storyEnd){
                        $searchContent .= $storyStart.' - '.$storyEnd.' of '.$storyCount.' results';
                    } else {
                        $searchContent .= $storyCount.' of '.$storyCount.' results';
                    }
                    $searchContent .= "</div>";
                    $searchContent .= "<div class='item-results' style='display: none;'>";
                    if($itemCount >= $itemEnd) {
                        $searchContent .= $itemStart.' - '.$itemEnd.' of '.$itemCount.' results';
                    } else {
                        $searchContent .= $itemCount.' of '.$itemCount.' results';
                    }
                    $searchContent .= "</div>";

                    $searchContent .= '<ul class="content-view-bar" style="float: right;">';
                        $searchContent .= '<li class="search-results-grid-radio search-results-radio left">';
                            $searchContent .= '<input id="story-grid-button" type="radio" name="view" form="story-facet-form" value="grid" checked>';
                                $searchContent .= '<label for="story-grid-button" class="theme-color-background">';
                                    $searchContent .= '<i class="far fa-th-large" style="font-size: 12px; padding-right: 6px;"></i>';
                                    $searchContent .= 'Grid';
                                $searchContent .= '</label>';
                            $searchContent .= '</input>';
                        $searchContent .= '</li>';
                        $searchContent .= '<li class="search-results-list-radio search-results-radio right">';
                            $searchContent .= '<input id="story-list-button" type="radio" name="view" form="story-facet-form" value="list">';
                                $searchContent .= '<label for="story-list-button">';
                                    $searchContent .= '<i class="far fa-th-list theme-color" style="font-size: 12px; padding-right: 6px;"></i>';
                                    $searchContent .= 'List';
                                $searchContent .= '</label>';
                            $searchContent .= '</input>';
                        $searchContent .= '</li>';
                    $searchContent .= '</ul>';

                $searchContent .= '</div>';
                $searchContent .= '<div class="search-content-results-headline search-headline">';

                $searchContent .= '</div>';


            $searchContent .= "</div>";

                // Facet form
                $storyFacetContent = '<form id="story-facet-form">';
                    $storyFacetContent .= $clearButton;
                    foreach ($storyFacetFields as $storyFacetField) {
                        $facetData = $solrStoryData['facet_counts']['facet_fields'][$storyFacetField['fieldName']];

                        $isEmpty = true;
                        for ($i = 0; $i < sizeof($facetData); $i = $i + 2) {
                            if ($facetData[$i + 1] != 0) {
                                $isEmpty = false;
                            }
                        }

                        if ($isEmpty != true) {
                            $storyFacetContent .= '<div class="search-panel-default collapse-controller">';
                                $storyFacetContent .= '<div class="search-panel-heading collapse-headline clickable" data-toggle="collapse" href="#story-'.$storyFacetField['fieldName'].'-area"
                                                    onClick="jQuery(this).find(\'.collapse-icon\').toggleClass(\'fa-caret-circle-up\')
                                                            jQuery(this).find(\'.collapse-icon\').toggleClass(\'fa-caret-circle-down\')">';
                                    $storyFacetContent .= '<h4 class="left-panel-dropdown-title">';
                                        $storyFacetContent .= '<li style="font-size:14px;">'.$storyFacetField['fieldLabel'].'</li>';
                                    $storyFacetContent .= '</h4>';
                                    $storyFacetContent .= '<i class="far fa-caret-circle-up collapse-icon theme-color" style="font-size: 17px; float:right; margin-top:17.4px;"></i>';
                                $storyFacetContent .= '</div>';

                                $storyFacetContent .= "<div id='story-".$storyFacetField['fieldName']."-area' class=\"facet-search-subsection collapse show\">";
                                    $rowCount = 0;
                                    for ($i = 0; $i < sizeof($facetData); $i = $i + 2) {
                                        if ($facetData[$i+1] != 0) {
                                            if ($rowCount == 5) {
                                                $storyFacetContent .= '<div class="show-more theme-color"
                                                                            data-toggle="collapse" href="#story-'.$storyFacetField['fieldName'].'-hidden-area">';
                                                    $storyFacetContent .= 'Show More';
                                                $storyFacetContent .= '</div>';

                                                $storyFacetContent .= "<div id='story-".$storyFacetField['fieldName']."-hidden-area'
                                                                        class=\"facet-search-subsection collapse\">";
                                            }
                                            $storyFacetContent .= '<label class="search-container theme-color">';
                                                $storyFacetContent .= $facetData[$i].' ('.$facetData[$i+1].')';
                                                $checked = "";
                                                if (isset($_GET[$storyFacetField['fieldName']]) && in_array($facetData[$i], $_GET[$storyFacetField['fieldName']])) {
                                                    $checked = "checked";
                                                }
                                                $storyFacetContent .= '<input type="checkbox" name="'.$storyFacetField['fieldName'].'[]" value="'.$facetData[$i].'"
                                                                '.$checked.' onChange="this.form.submit()">
                                                                <span class="theme-color-background checkmark"></span>';
                                            $storyFacetContent .= '</label>';
                                            $rowCount += 1;
                                        }
                                    }
                                    if ($rowCount > 5) {
                                            $storyFacetContent .= '<div class="show-less theme-color" data-toggle="collapse" href="#story-'.$storyFacetField['fieldName'].'-hidden-area">';
                                                $storyFacetContent .= 'Show Less';
                                            $storyFacetContent .= '</div>';
                                        $storyFacetContent .= '</div>';
                                    }
                                $storyFacetContent .= '</div>';
                            $storyFacetContent .= '</div>';
                        }
                    }
                $storyFacetContent .= '</form>';
            //$storyFacetContent .= '</div>';

                // Facet form
                $itemFacetContent = '<form id="item-facet-form">';
                    $itemFacetContent .= $clearButton;
                    foreach ($itemFacetFields as $itemFacetField) {
                        $facetData = $solrItemData['facet_counts']['facet_fields'][$itemFacetField['fieldName']];
                        if (sizeof($facetData) > 0) {
                            $itemFacetContent .= '<div class="search-panel-default collapse-controller">';
                                $itemFacetContent .= '<div class="search-panel-heading collapse-headline clickable" data-toggle="collapse" href="#item-'.$itemFacetField['fieldName'].'-area"
                                                    onClick="jQuery(this).find(\'.collapse-icon\').toggleClass(\'fa-caret-circle-up\')
                                                            jQuery(this).find(\'.collapse-icon\').toggleClass(\'fa-caret-circle-down\')">';
                                    $itemFacetContent .= '<h4 class="left-panel-dropdown-title">';
                                        $itemFacetContent .= '<li style="font-size:14px;">'.$itemFacetField['fieldLabel'].'</li>';
                                    $itemFacetContent .= '</h4>';
                                    $itemFacetContent .= '<i class="far fa-caret-circle-up collapse-icon theme-color" style="font-size: 17px; float:right; margin-top:17.4px;"></i>';
                                $itemFacetContent .= '</div>';

                                $itemFacetContent .= "<div id='item-".$itemFacetField['fieldName']."-area' class=\"facet-search-subsection collapse show\">";
                                $rowCount = 0;
                                    for ($i = 0; $i < sizeof($facetData); $i = $i + 2) {
                                        if ($facetData[$i+1] != 0) {
                                            if ($rowCount == 5) {
                                                $itemFacetContent .= '<div class="show-more theme-color"
                                                                            data-toggle="collapse" href="#item-'.$itemFacetField['fieldName'].'-hidden-area">';
                                                    $itemFacetContent .= 'Show More';
                                                $itemFacetContent .= '</div>';

                                                $itemFacetContent .= "<div id='item-".$itemFacetField['fieldName']."-hidden-area'
                                                                        class=\"facet-search-subsection collapse\">";
                                            }
                                            $itemFacetContent .= '<label class="search-container theme-color">';
                                                $itemFacetContent .= $facetData[$i].' ('.$facetData[$i+1].')';
                                                $checked = "";
                                                if (isset($_GET[$itemFacetField['fieldName']]) && in_array($facetData[$i], $_GET[$itemFacetField['fieldName']])) {
                                                    $checked = "checked";
                                                }
                                                $itemFacetContent .= '<input type="checkbox" class name="'.$itemFacetField['fieldName'].'[]" form="story-facet-form" value="'.$facetData[$i].'"
                                                                '.$checked.' onChange="this.form.submit()">
                                                                <span class="theme-color-background checkmark"></span>';

                                            $itemFacetContent .= '</label>';
                                            $rowCount += 1;
                                        }
                                    }
                                    if ($rowCount > 5) {
                                            $itemFacetContent .= '<div class="show-less theme-color" data-toggle="collapse" href="#item-'.$itemFacetField['fieldName'].'-hidden-area">';
                                                $itemFacetContent .= 'Show Less';
                                            $itemFacetContent .= '</div>';
                                        $itemFacetContent .= '</div>';
                                    }
                                $itemFacetContent .= '</div>';
                            $itemFacetContent .= '</div>';
                        }
                    }

                $itemFacetContent .= '</form>';
            $itemFacetContent .= '</div>';

            // #### Facets End ####


            // #### Results Start ####

            $searchContent .= '<div class="search-content-right">';
                $searchContent .= '<div class="search-content-right-header">';

                    // List/Grid switcher
                    $searchContent .= '<div class="search-content-results-headline search-content-results-view search-division-detail">';

                    $searchContent .= '</div>';


                $searchContent .= '</div>';
            $searchContent .= '</div>';
////////////////////////////////////////////////////////
                // Search result pagination
                $pagination = "";
                $pagination .= '<div class="search-page-pagination">';
                    // Left arrows
                    if ($storyPage > 1) {
                        $pagination .= '<button type="submit" form="story-facet-from" name="ps" value="1" class="theme-color-hover pagBtn" style="outline:none;">';
                            $pagination .= '&laquo;';
                        $pagination .= '</button>';
                    }

                    // Previous page arrow
                        if ($storyPage != null && is_numeric($storyPage) && $storyPage > 1) {
                            $pagination .= '<button type="submit" form="story-facet-form" name="ps" value="'.($storyPage - 1).'" class="theme-color-hover pagBtn" style="outline:none;">';
                                $pagination .= '&lsaquo;';
                            $pagination .= '</button>';
                        }

                    // Previous page number
                        if ($storyPage != null && is_numeric($storyPage) && $storyPage > 1) {
                            $pagination .= '<button type="submit" form="story-facet-form" name="ps" value="'.($storyPage - 1).'" class="theme-color-hover pagBtn" style="outline:none;">';
                                $pagination .= ($storyPage - 1);
                            $pagination .= '</button>';
                        }

                    // Current page
                        $pagination .= '<button type="submit" form="story-facet-form" name="ps" value="'.$storyPage.'" class="theme-color-background pagBtn" style="outline:none;">';
                            $pagination .= $storyPage;
                        $pagination .= '</button>';

                    // 3 next pages
                    for ($i = 1; $i <= 3; $i++) {
                        if (((($storyPage + $i) - 1) * 24) < $storyCount) {
                            $pagination .= '<button type="submit" form="story-facet-form" name="ps" value="'.($storyPage + $i).'" class="theme-color-hover pagBtn" style="outline:none;">';
                                $pagination .= ($storyPage + $i);
                            $pagination .= '</button>';
                        }
                    }

                    // Next page arrow
                    if ($storyPage < ceil($storyCount / 24)) {
                        $pagination .= '<button type="submit" form="story-facet-form" name="ps" value="'.($storyPage + 1).'" class="theme-color-hover pagBtn" style="outline:none;">';
                            $pagination .= '&rsaquo;';
                        $pagination .= '</button>';
                    }

                    // Right arrows
                    if ($storyPage < ceil($storyCount / 24)) {
                        $pagination .= '<button type="submit" form="story-facet-form" name="ps" value="'.ceil($storyCount / 24).'" class="theme-color-hover pagBtn" style="outline:none;">';
                            $pagination .= '&raquo;';
                        $pagination .= '</button>';
                    }
                    $pagination .= '<div style="clear:both;"></div>';
                $pagination .= '</div>';

                // Pagination on top of search results
                $storyTabContent .= $pagination;
///////////////////////////////////////////////////////////////////////7
                // Search results
                $storyTabContent .= '<div class="search-content-right-items">';
                    $storyIdList = array();
                    for ($i = 0; $i < sizeof($solrStoryData['response']['docs']); $i++) {
                        array_push($storyIdList, $solrStoryData['response']['docs'][$i]['StoryId']);
                    }

                    // Get additional story data
                    $url = TP_API_HOST."/tp-api/storiesMinimal?storyId=";
                    $first = true;
                    foreach($storyIdList as $storyId) {
                        if ($first == true) {
                            $first = false;
                        }
                        else {
                            $url .= ",";
                        }
                        $url .= $storyId;
                    }
                    $requestType = "GET";

                    // Execude http request
                    include dirname(__FILE__)."/../../custom_scripts/send_api_request.php";

                    // Save story data
                    $storyData = json_decode($result, true);


                    for ($i = 0; $i < sizeof($solrStoryData['response']['docs']); $i++) {
                        $storyTabContent .= '<div class="search-page-single-result maingridview">';

                        // Single story image
                        $storyTabContent .= '<div class="search-page-single-result-image">';
                        $image = json_decode($solrStoryData['response']['docs'][$i]['PreviewImageLink'], true);

                        $gridImageLink = createImageLinkFromData($image, array('size' => '280,140', 'page' => 'search'));

                                // $storyTabContent .= "<a class='list-view-image' style='display:none' href='".home_url( $wp->request )."/story?story=".$solrStoryData['response']['docs'][$i]['StoryId']."'>";
                                 //   $storyTabContent .= '<img src='.$listImageLink.'>';
                                // $storyTabContent .= "</a>";
                                $storyTabContent .= "<a class='grid-view-image storyImg' href='".home_url( $wp->request )."/story?story=".$solrStoryData['response']['docs'][$i]['StoryId']."'>";
                                    $storyTabContent .= '<img src='.$gridImageLink.'>';
                                $storyTabContent .= "</a>";

                                // Progress bar

                                $statusData = array();
                                foreach ($statusTypes as $statusType) {
                                    $statusObject = new stdClass;
                                    $statusObject->Name = $statusType['Name'];
                                    $statusObject->ColorCode = $statusType['ColorCode'];
                                    $statusObject->ColorCodeGradient = $statusType['ColorCodeGradient'];
                                    $statusObject->Amount = 0;
                                    $statusObject->Percentage = 0;
                                    $statusData[$statusType['Name']] = $statusObject;
                                }
                                $itemAmount = 0;
                                $itemAmount += $solrStoryData['response']['docs'][$i]['NotStartedAmount'];
                                $itemAmount += $solrStoryData['response']['docs'][$i]['EditAmount'];
                                $itemAmount += $solrStoryData['response']['docs'][$i]['ReviewAmount'];
                                $itemAmount += $solrStoryData['response']['docs'][$i]['CompletedAmount'];

                                $totalPercent = 0;

                                // Create status objects for each status
                                foreach($statusTypes as $status) {
                                    $statusObject = new stdClass;
                                    $statusObject->Name = $status['Name'];
                                    $statusObject->ColorCode = $status['ColorCode'];
                                    $statusObject->ColorCodeGradient = $status['ColorCodeGradient'];
				    switch ($status['Name']) {
					case "Not Started":
						$statusObject->Amount = $solrStoryData['response']['docs'][$i]['NotStartedAmount'];
						$statusObject->Percentage = (round($solrStoryData['response']['docs'][$i]['NotStartedAmount'] / $itemAmount, 2) * 100);
						break;
					case "Edit":
						$statusObject->Amount = $solrStoryData['response']['docs'][$i]['EditAmount'];
						$statusObject->Percentage = (round($solrStoryData['response']['docs'][$i]['EditAmount'] / $itemAmount, 2) * 100);
						break;
					case "Review":
						$statusObject->Amount = $solrStoryData['response']['docs'][$i]['ReviewAmount'];
						$statusObject->Percentage = (round($solrStoryData['response']['docs'][$i]['ReviewAmount'] / $itemAmount, 2) * 100);
						break;
					case "Completed":
						$statusObject->Amount = $solrStoryData['response']['docs'][$i]['CompletedAmount'];
                                    		$statusObject->Percentage = (round($solrStoryData['response']['docs'][$i]['CompletedAmount'] / $itemAmount, 2) * 100);
						break;
				    }

                                    $statusData[$status['Name']] = $statusObject;
                                    $totalPercent += $statusObject->Percentage;
                                }

                                // Make sure that percent total is 100
                                foreach ($statusData as $status) {
                                    if ($status->Name == "Not Started") {
                                        if ($totalPercent != 100) {
                                            $status->Amount += (100 - $totalPercent);
                                        }
                                    }
                                }
                                $storyTabContent .= '<div class="box-progress-bar item-status-chart">';

                                    // Status hover info box
                                    $storyTabContent .= '<div class="item-status-info-box box-status-bar-info-box">';
                                        $storyTabContent .= '<ul class="item-status-info-box-list">';
                                            foreach ($statusData as $status) {
                                                $percentage = $status->Percentage;
                                                $storyTabContent .= '<li>';
                                                    $storyTabContent .= '<span class="status-info-box-color-indicator" style="background-color:'.$status->ColorCode.';
                                                                    background-image: -webkit-gradient(linear, left top, left bottom,
                                                                    color-stop(0, '.$status->ColorCode.'), color-stop(1, '.$status->ColorCodeGradient.'));">';
                                                    $storyTabContent .= '</span>';
                                                    $storyTabContent .= '<span id="progress-bar-overlay-'.str_replace(' ', '-', $status->Name).'-section" class="status-info-box-percentage">';
                                                        $storyTabContent .= $percentage.'% | '.$status->Amount;
                                                    $storyTabContent .= '</span>';
                                                    $storyTabContent .= '<span class="status-info-box-text">';
                                                        $storyTabContent .= $status->Name;
                                                    $storyTabContent .= '</span>';
                                                $storyTabContent .= '</li>';
                                            }
                                        $storyTabContent .= '</ul>';
                                    $storyTabContent .= '</div>';

                                    $CompletedBar = "";
                                    $ReviewBar = "";
                                    $EditBar = "";
                                    $NotStartedBar = "";

                                    // Add each status section to progress bar
                                    foreach ($statusData as $status) {
                                        $percentage = $status->Percentage;

                                        switch ($status->Name) {
                                            case "Completed":
                                                $CompletedBar .= '<div id="progress-bar-'.str_replace(' ', '-', $status->Name).'-section" class="progress-bar progress-bar-section"
                                                                    style="width: '.$percentage.'%; background-color:'.$status->ColorCode.';
                                                                    ">';
                                                    $CompletedBar .= $percentage.'%';
                                                $CompletedBar .= '</div>';
                                                break;
                                            case "Review":
                                                $ReviewBar .= '<div id="progress-bar-'.str_replace(' ', '-', $status->Name).'-section" class="progress-bar progress-bar-section"
                                                                    style="width: '.$percentage.'%; background-color:'.$status->ColorCode.'">';
                                                    $ReviewBar .= $percentage.'%';
                                                $ReviewBar .= '</div>';
                                                break;
                                            case "Edit":
                                                $EditBar .= '<div id="progress-bar-'.str_replace(' ', '-', $status->Name).'-section" class="progress-bar progress-bar-section"
                                                                    style="width: '.$percentage.'%; background-color:'.$status->ColorCode.'">';
                                                    $EditBar .= $percentage.'%';
                                                $EditBar .= '</div>';
                                                break;
                                            case "Not Started":
                                                $NotStartedBar .= '<div id="progress-bar-'.str_replace(' ', '-', $status->Name).'-section" class="progress-bar progress-bar-section"
                                                                    style="width: '.$percentage.'%; background-color:'.$status->ColorCode.'">';
                                                    $NotStartedBar .= $percentage.'%';
                                                $NotStartedBar .= '</div>';
                                                break;
                                        }
                                    }
                                    if ($CompletedBar != "") {
                                        $storyTabContent .= $CompletedBar;
                                    }
                                    if ($ReviewBar != "") {
                                        $storyTabContent .= $ReviewBar;
                                    }
                                    if ($EditBar != "") {
                                        $storyTabContent .= $EditBar;
                                    }
                                    if ($NotStartedBar != "") {
                                        $storyTabContent .= $NotStartedBar;
                                    }
                                $storyTabContent .= '</div>';
                            $storyTabContent .= '</div>';

                            // Single story info
                            $storyTabContent .= '<div class="search-page-single-result-info">';
                                $storyTabContent .= '<h2 class="theme-color">';
                                    $storyTabContent .= "<a href='".home_url( $wp->request )."/story?story=".$solrStoryData['response']['docs'][$i]['StoryId']."'>";
                                        $storyTabContent .= $solrStoryData['response']['docs'][$i]['dcTitle'];
                                    $storyTabContent .= "</a>";
                                $storyTabContent .= '</h2>';
                                $storyTabContent .= '<span style="display: none">...</span>';
                            $storyTabContent .= '</div>';

                            $storyTabContent .= '<div style="clear:both"></div>';
                        $storyTabContent .= '</div>';
                    }
                $storyTabContent .= '</div>';

                // Pagination below search results
                $storyTabContent .= $pagination;

            //$storyTabContent .= '</div>';



            // $itemTabContent .= '<div class="search-content-right">';
            //     $itemTabContent .= '<div class="search-content-right-header">';

                // Search result pagination
                $pagination = "";
                $pagination .= '<div class="search-page-pagination">';
                    // Left arrows
                    if ($itemPage > 1) {
                        $pagination .= '<button type="submit" form="story-facet-form" name="pi" value="1" class="theme-color-hover" style="outline:none;">';
                            $pagination .= '&laquo;';
                        $pagination .= '</button>';
                    }

                    // Previous page
                        if ($itemPage != null && is_numeric($itemPage) && $itemPage > 1) {
                            $pagination .= '<button type="submit" form="story-facet-form" name="pi" value="'.($itemPage - 1).'" class="theme-color-hover" style="outline:none;">';
                                $pagination .= ($itemPage - 1);
                            $pagination .= '</button>';
                        }

                    // Current page
                        $pagination .= '<button type="submit" form="story-facet-form" name="pi" value="'.$itemPage.'" class="theme-color-background" style="outline:none;">';
                            $pagination .= $itemPage;
                        $pagination .= '</button>';

                    // 3 next pages
                    for ($i = 1; $i <= 3; $i++) {
                        if (((($itemPage + $i) - 1) * 24) < $itemCount) {
                            $pagination .= '<button type="submit" form="story-facet-form" name="pi" value="'.($itemPage + $i).'" class="theme-color-hover" style="outline:none;">';
                                $pagination .= ($itemPage + $i);
                            $pagination .= '</button>';
                        }
                    }

                        // Right arrows
                    if ($itemPage < ceil($itemCount / 24)) {
                        $pagination .= '<button type="submit" form="story-facet-form" name="pi" value="'.ceil($itemCount / 24).'" class="theme-color-hover" style="outline:none;">';
                            $pagination .= '&raquo;';
                        $pagination .= '</button>';
                    }
                    $pagination .= '<div style="clear:both;"></div>';
                $pagination .= '</div>';

                // Pagination on top of search results
                $itemTabContent .= $pagination;

                // Search results
                $itemTabContent .= '<div class="search-content-right-items">';
                    foreach ($solrItemData['response']['docs'] as $item) {
                        $itemTabContent .= '<div class="search-page-single-result maingridview">';

                            // Single item image
                            $itemTabContent .= '<div class="search-page-single-result-image">';

                                $image = json_decode($item['PreviewImageLink'], true);

                                $gridImageLink = createImageLinkFromData($image, array('size' => '280,140', 'page' => 'search'));

                                $itemTabContent .= "<a class='list-view-image' style='display:none' href='".home_url( $wp->request )."/item?item=".$item['ItemId']."'>";
                                    //$itemTabContent .= '<img src='.$listImageLink.'>';
                                $itemTabContent .= "</a>";
                                $itemTabContent .= "<a class='grid-view-image' href='".home_url( $wp->request )."/item?item=".$item['ItemId']."'>";
                                    $itemTabContent .= '<img src='.$gridImageLink.'>';
                                $itemTabContent .= "</a>";



                                // Progress bar
                                $progressData = array(
                                    $item['TranscriptionStatus'],
                                    $item['DescriptionStatus'],
                                    $item['LocationStatus'],
                                    $item['TaggingStatus'],
                                );
                                $progressCount = array (
                                                'Not Started' => 0,
                                                'Edit' => 0,
                                                'Review' => 0,
                                                'Completed' => 0
                                            );
                                // Save each status occurence
                                foreach ($progressData as $status) {
                                    $progressCount[$status] += 1;
                                }
                                $itemTabContent .= '<div class="box-progress-bar item-status-chart">';

                                    // Status hover info box
                                    $itemTabContent .= '<div class="item-status-info-box box-status-bar-info-box">';
                                        $itemTabContent .= '<ul class="item-status-info-box-list">';
                                            foreach ($statusTypes as $status) {
                                                $percentage = ($progressCount[$status['Name']] / sizeof($progressData)) * 100;
                                                $itemTabContent .= '<li>';
                                                    $itemTabContent .= '<span class="status-info-box-color-indicator" style="background-color:'.$status['ColorCode'].';
                                                                    background-image: -webkit-gradient(linear, left top, left bottom,
                                                                    color-stop(0, '.$status['ColorCode'].'), color-stop(1, '.$status['ColorCodeGradient'].'));">';
                                                    $itemTabContent .= '</span>';
                                                    $itemTabContent .= '<span id="progress-bar-overlay-'.str_replace(' ', '-', $status['Name']).'-section" class="status-info-box-percentage" style="width: 20%;">';
                                                        $itemTabContent .= $percentage.'%';
                                                    $itemTabContent .= '</span>';
                                                    $itemTabContent .= '<span class="status-info-box-text">';
                                                        $itemTabContent .= $status['Name'];
                                                    $itemTabContent .= '</span>';
                                                $itemTabContent .= '</li>';
                                            }
                                        $itemTabContent .= '</ul>';
                                    $itemTabContent .= '</div>';

                                    $CompletedBar = "";
                                    $ReviewBar = "";
                                    $EditBar = "";
                                    $NotStartedBar = "";

                                    // Add each status section to progress bar
                                    foreach ($statusTypes as $status) {
                                        $percentage = ($progressCount[$status['Name']] / sizeof($progressData)) * 100;

                                        switch ($status['Name']) {
                                            case "Completed":
                                                $CompletedBar .= '<div id="progress-bar-'.str_replace(' ', '-', $status['Name']).'-section" class="progress-bar progress-bar-section"
                                                                    style="width: '.$percentage.'%; background-color:'.$status->ColorCode.';
                                                                    ">';
                                                    $CompletedBar .= $percentage.'%';
                                                $CompletedBar .= '</div>';
                                                break;
                                            case "Review":
                                                $ReviewBar .= '<div id="progress-bar-'.str_replace(' ', '-', $status['Name']).'-section" class="progress-bar progress-bar-section"
                                                                    style="width: '.$percentage.'%; background-color:'.$status->ColorCode.'">';
                                                    $ReviewBar .= $percentage.'%';
                                                $ReviewBar .= '</div>';
                                                break;
                                            case "Edit":
                                                $EditBar .= '<div id="progress-bar-'.str_replace(' ', '-', $status['Name']).'-section" class="progress-bar progress-bar-section"
                                                                    style="width: '.$percentage.'%; background-color:'.$status->ColorCode.'">';
                                                    $EditBar .= $percentage.'%';
                                                $EditBar .= '</div>';
                                                break;
                                            case "Not Started":
                                                $NotStartedBar .= '<div id="progress-bar-'.str_replace(' ', '-', $status['Name']).'-section" class="progress-bar progress-bar-section"
                                                                    style="width: '.$percentage.'%; background-color:'.$status->ColorCode.'">';
                                                    $NotStartedBar .= $percentage.'%';
                                                $NotStartedBar .= '</div>';
                                                break;
                                        }
                                    }
                                    if ($CompletedBar != "") {
                                        $itemTabContent .= $CompletedBar;
                                    }
                                    if ($ReviewBar != "") {
                                        $itemTabContent .= $ReviewBar;
                                    }
                                    if ($EditBar != "") {
                                        $itemTabContent .= $EditBar;
                                    }
                                    if ($NotStartedBar != "") {
                                        $itemTabContent .= $NotStartedBar;
                                    }
                                $itemTabContent .= '</div>';
                            $itemTabContent .= '</div>';

                            // Single item info
                            $itemTabContent .= '<div class="search-page-single-result-info">';
                                $itemTabContent .= '<h2 class="theme-color">';
                                    $itemTabContent .= "<a href='".home_url( $wp->request )."/story/item?item=".$item['ItemId']."'>";
                                        $itemTabContent .= $item['Title'];
                                    $itemTabContent .= "</a>";
                                $itemTabContent .= '</h2>';
                                $itemTabContent .= '<span style="display: none">...</span>';
                            $itemTabContent .= '</div>';

                            $itemTabContent .= '<div style="clear:both"></div>';
                        $itemTabContent .= '</div>';
                    }
                $itemTabContent .= '</div>';

                // Pagination below search results
                $itemTabContent .= $pagination;

            $itemTabContent .= '</div>';


            // #### Results End ####


        $itemTabContent .= '</div>';
    $itemTabContent .= "</div>";

    //     $storyTabContent .= '</div>';
    // $storyTabContent .= "</div>";

    // Build page content
    $content .= $searchContent;
    $content .= "<div style='clear:both;'></div>";
    $content .= "<div class='primary-full-width' style='padding:0 50px 50px 50px!important;'>";
        $content .= "<div class='complete-search-content'>";
        $content .= "<div class='story-facet-content'>";
            $content .= $storyFacetContent;
        $content .= "</div>";
        $content .= "<div class='item-facet-content' style='display: none;'>";
            $content .= $itemFacetContent;
            //$content .= "</div>";
        if($_GET['pi'] != null) {
            $content .= '<div id="search-page-story-tab" style="display: none;">';
                $content .= $storyTabContent;
            $content .= '</div>';

            $content .= '<div id="search-page-item-tab">';
                $content .= $itemTabContent;
        } else {
        $content .= '<div id="search-page-story-tab">';
            $content .= $storyTabContent;
        $content .= '</div>';

        $content .= '<div id="search-page-item-tab" style="display: none;">';
            $content .= $itemTabContent;
       // $content .= '</div>';
        }
    $content .= '</div>';


echo $content;


?>
