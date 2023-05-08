<?php
/* 
Shortcode: teamsandruns_tab
Description: Creates the 'Teams & runs' tab of profile tab
*//* Team-Tab */

function _TCT_teamsandruns_tab( $atts ) {

    // build 'Teams & runs' tab of Profile page
    // Keep it temp here, needs to be replaced after changes to user endpoint
    $url = TP_API_HOST."/tp-api/teams?WP_UserId=".um_profile_id();
    $requestType = "GET";
    
    // Execude http request
    include dirname(__FILE__)."/../custom_scripts/send_api_request.php";
    
    // Save image data
    $myteams = json_decode($result, true);
    
    // var_dump($myteams);
    $getJsonOptions = [
    	'http' => [
    		'header' => [ 
    			'Content-type: application/json',
    			'Authorization: Bearer ' . TP_API_V2_TOKEN
    		],
    		'method' => 'GET'
    	]
    ];
    
    $userInfo = sendQuery(TP_API_V2_ENDPOINT . '/users?WP_UserId=' . get_current_user_id(), $getJsonOptions, true);

	$content = '';
	$content .= 
	    '<style type="text/css">
		    .tct_hd h1 {
				text-transfrom: none;
				line-height: 1.2;
				font-weight: regular;
				letter-spacing: 0.2;
				font-size: 1.8rem!important;
				margin-bottom: 0px!important;
				padding-bottom: 0px!important;
			}
			.tct_hd h3 {
				text-transfrom: none;
				line-height: 1.2;
				font-weight: regular;
				letter-spacing: 0.3;
				color: #333;
				font-size: 1.5rem!important;
				margin-top: 0px!important;
				padding-top: 0px!important;
				margin-bottom: 0px!important;
			}
			.tct_hd h1+h3 {
				margin-top: 0px!important;
				padding-top: 5px!important;
			}
			.tct_hd {
				padding-bottom: 20px!important;
			}
			button.tct-vio-but[type=button],input.tct-vio-but[type=button],a.tct-vio-but {
				min-height: 20px;
				border: none!important;
				font-size: 0.9em;
				letter-spacing: 0.5px;
				padding: 5px 30px;
				font-weight: regular;
				color: #fff!important;
				text-align: center;
				cursor: pointer;
				margin-top: 4px!important;
				display: inline-block!important;
			}
		</style>';

	if(is_user_logged_in() && get_current_user_id() === um_profile_id()) {

		$content .= "<div class=>";
		$content .= "</div>";


	} else {
		echo 'Go Away';
	}




}
    
        

add_shortcode( 'teamsandruns_tab', '_TCT_teamsandruns_tab' );
?>