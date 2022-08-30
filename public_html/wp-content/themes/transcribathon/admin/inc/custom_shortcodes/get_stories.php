<?php
/* 
Shortcode: get_stories
Description: Gets stories from the API and displays them
*/
function _TCT_get_stories( $atts ) {  

    /* Domain name of the Solr server */
    define('SOLR_SERVER_HOSTNAME', home_url());

    /* Whether or not to run in secure mode */
    define('SOLR_SECURE', true);

    /* HTTP Port to connection */
    define('SOLR_SERVER_PORT', ((SOLR_SECURE) ? 8443 : 8983));

    /* HTTP Basic Authentication Username */
    //define('SOLR_SERVER_USERNAME', 'admin');

    /* HTTP Basic Authentication password */
    //define('SOLR_SERVER_PASSWORD', 'changeit');

    /* HTTP connection timeout */
    /* This is maximum time in seconds allowed for the http data transfer operation. Default value is 30 seconds */
    define('SOLR_SERVER_TIMEOUT', 30);

    global $wp;
    // Set Post content
    $requestData = array(
        'key' => 'testKey'
    );
    $url = home_url() . '/tp-api/storiesMinimal';
    $requestType = "GET";

    include dirname(__FILE__) . '/../custom_scripts/send_api_request.php';

    $stories = json_decode($result, true);
    $content = "";
    foreach ($stories as $story){
        $content .= "<a href='".home_url( $wp->request )."/story?story=".$story['StoryId']."'>".$story['dcTitle']."</a></br>";
    }

    $options = array
    (
        'hostname' => SOLR_SERVER_HOSTNAME,
        'port'     => SOLR_SERVER_PORT,
    );
    
    
$content .= '<div class="refine">';
    $content .= '<h2>Refine your search</h2>';
$content .= '</div>';

$content .= '<div class="filter radiobuttons">';

        $content .= '<div class="filter-name" data-filter-name="COLLECTION">Collections';
            $content .= '<ul class="filter-mobile-summary">';
            $content .= '<li>All Items</li>';
            $content .= '</ul>';    
        $content .= '</div>';

            $content .= '<ul class="filter-list filter-wrap">';
                $content .= '<li>';
                $content .= '<a href="#" class="filter-item  is-checked "  aria-checked="true"  ><div class="filter-text">All Items</div></a>';
                $content .= '</li>';

                $content .= '<li>';
                $content .= '<a href="#" class="filter-item "   aria-checked="false" ><div class="filter-text">1914-1918</div></a>';
                $content .= '</li>';

                $content .= '<li>';
                $content .= '<a href="#" class="filter-item "   aria-checked="false" ><div class="filter-text">Archaeology</div></a>';
                $content .= '</li>';

                $content .= '<li>';
                $content .= '<a href="#" class="filter-item "   aria-checked="false" ><div class="filter-text">Art</div></a>';
                 $content .= '</li>';

                $content .= '<li>';
                $content .= '<a href="#" class="filter-item "   aria-checked="false" ><div class="filter-text">Fashion</div></a>';
                $content .= '</li>';

            $content .= '<ul>';

        $content .= '<a class="js-showhide filter-moreless" href="#" data-text-swap="Less Collections" aria-expanded="false">More<span class="is-vishidden"> Collections</span></a>';
$content .= '</div>';





/*    $content .= '<div class="search-page">';
        $content .= '<div class="search-page-left" style="background-color:#ddd;">';
            $content .= '<h2>refine searches</h2>';
            $content .= '<ul id="search-menu">';*/
////
/*$editorTab .= '<div class="panel panel-default">';
                $editorTab .= '<div class="panel-heading clickable" data-toggle="collapse" href="#description-area">';
                    $editorTab .= '<h4 id="description-collapse-heading" class="theme-color item-page-section-headline panel-title">';  
                        $editorTab .= '<li><a href="#">COLLECTIONS</a></li>';
                    $editorTab .= '</h4>';
                    $editorTab .= '<i class="fa fa-angle-down" style="font-size: 20px; float:right;"></i>';
                $editorTab .= '</div>';
                

                $editorTab .= "<div id=\"description-area\" class=\"transcription-history-area panel-body panel-collapse collapse\">";
                $editorTab .= '<label class="container">Letter<input id="type-letter-checkbox" type="checkbox" checked="checked" name="doctype" value="card"><span  class=" theme-color-background checkmark"></span></label>';
              $editorTab .= '<label class="container">Diary<input type="checkbox" name="doctype" value="card"><span class="theme-color-background checkmark"></span></label>';
                $editorTab .= '<label class="container">Post card<input type="checkbox"  name="doctype" value="card"><span class="theme-color-background checkmark"></span></label>';                        
                $editorTab .= '<label class="container">Picture<input type="checkbox"  name="doctype" value="card"><span class="theme-color-background checkmark"></span></label>';
                   
                $editorTab .= '</div>';
            $editorTab .= '</div>';*/


////
  /*              $content .= '<li><a href="#">STATUS</a></li>';
                $content .= '<li><a href="#">COUNTRY</a></li>';
                $content .= '<li><a href="#">LANGUAGES</a></li>';
                $content .= '<li><a href="#">KEY WORDS</a></li>';
                $content .= '<li><a href="#">MEDIA</a></li>';
            $content .= '</ul>';
        $content .= '</div>';  

        $content .= '<div class="search-page-right" style="background-color:#fff;">';
            $content .= '<h2>Page Content</h2>';
            $content .= '<p>Start to type for a spe "filter" the search options.</p>';
            $content .= '<p>Some text..</p>';
        $content .= '</div>';
    $content .= '</div>';*/

    echo $content;

   
}
add_shortcode( 'get_stories', '_TCT_get_stories' );
?>