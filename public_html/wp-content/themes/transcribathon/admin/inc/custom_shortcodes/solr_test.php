<?php
/* 
Shortcode: solr_test
*/


// include required files
include($_SERVER["DOCUMENT_ROOT"].'/wp-load.php');

function _TCT_solr_test( $atts ) { 

    $view = $_GET['view'];
    /* Set up facet fields and labels */

    if($view == 'items') {

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
            ['params'=>[
                'q'=>'*:*',
                'facet' => 'on',
                'facet.field' => ['Languages', 'CompletionStatus', 'Categories'],
                ]
            ]
        );
        $context = stream_context_create($options);
    
	    $data = @file_get_contents($url, false, $context);
        $data = json_decode($data);
    } else {

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
                'q'=>'*:*',
                'facet' => 'on',
                'facet.field' => ['CompletionStatus', 'Categories', 'edmCountry', 'Dataset', 'dcLanguage'],
                ]
            ]
        );
        $context = stream_context_create($options);
        
        $data = @file_get_contents($url, false, $context);
        $data = json_decode($data, true);
    }
    //dd($storyFacets);
    
    
    ////
dd($data);
    //dd($result);

    // Build Page Layout
    $content = '';
    
    // Input field and Banner
    $content .= '<section class="temp-back">';
        $content .= '<div class="facet-form-search">';
            $content .= "<form id='query-form' action='" . home_url() . "/solr-test/' method='GET'>";
                $content .= '<div><input class="search-field" type="text" placeholder="Add a search term" name="q" form="query-form" value="' . str_replace('\\', '', htmlspecialchars($_GET['q'])) . '"></div>';
                $content .= '<div><input class="search-field" type="text" name="view" value="story" form="query-form" hidden></div>';
                $content .= '<div><button type="submit" form="query-form" class="theme-color-background document-search-button"><i class="far fa-search" style="font-size: 20px;"></i></button></div>';
                $content .= '<div class="map-search-page"><a href="dev/map" target="_blank" form="" class="theme-color-background document-search-button"><i class="fal fa-globe-europe" style="font-size: 20px;"></i></a></div>';
                $content .= '<div style="clear:both;"></div>';
            $content .= "</form>";
        $content .= '</div>';
    $content .= '</section>';
    // NAvigation section (switch between item/story, pagination, and numbers of results)
    $content .= "<section class='search-navigation'>";
    $content .= "</section>";
    // 'Body' of the page / Left Facet Fields Right Results
    $content .= "<section class='search-result'>";
        // Left side, facets
        $content .= "<div class='facet-menu'>";
            $content .= "PLACEHOLDER";
        $content .= "</div>";

        // Right side, search result 'stickers'
        $content .= "<div class='result-stickers'>";
            $content .= "PLACEHOLDER";
        $content .= "</div>";
    $content .= "</section>";


    /// TODO MOVE STYLES TO SEPARATE FILE
    $content .= "<style>

        .search-navigation {
            height: 50px;
            background: cyan;
        }
        .search-result {
            padding: 0 2vw;
        }
        .facet-menu {
            float: left;
            width: 30vw;
            height: 70vh;
            background: grey;
        }
        .result-stickers {
            float: right;
            width: 65vw;
            height: 70vh;
            background: lightgray;
        }
    
    </style>";
    

    return $content;
}
add_shortcode( 'solr_test',  '_TCT_solr_test' );
?>
