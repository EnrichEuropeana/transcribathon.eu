<?php
/* 
Shortcode: _TCT_get_team
Description: Gets news information and builds the news section for front page
*/


// include required files
include($_SERVER["DOCUMENT_ROOT"].'/wp-load.php');

function _TCT_get_team( $atts ) { 
    

    $url = TP_API_HOST."/tp-api/teams?ShortName=".$_GET['team'];
    $requestType = "GET";

    // Execude http request
    include dirname(__FILE__)."/../custom_scripts/send_api_request.php";

    // Save image data
    $team = json_decode($result, true);

    // Build Front page content
    $content = "";
    if (sizeof($team) != 1) {
        echo "";
    }
    else {
        $team = $team[0];
        foreach ($team['Users'] as $user) {
            $userData = get_user_by( 'id', $user['WP_UserId'] );
            var_dump($userData->user_nicename);
        }
        $content .= "test";
    
        echo $content;
    }
}
add_shortcode( 'get_team',  '_TCT_get_team' );
?>
