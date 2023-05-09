<?php
/*
Shortcode: _TCT_get_team
Description: Shows all registered teams
*/


// include required files
include($_SERVER["DOCUMENT_ROOT"].'/wp-load.php');

function _TCT_get_team( $atts ) {
    
    $teamQuery = $_GET['team'];

    $getJsonOptions = [
    	'http' => [
    		'header' => [ 
    			'Content-type: application/json',
    			'Authorization: Bearer ' . TP_API_V2_TOKEN
    		],
    		'method' => 'GET'
    	]
    ];

    $content = '';
    // Some inline style 
    $content .= 
        '<style>
            .entry-content {
                padding: 0 50px;
            }
            .single-team {
				position: relative;
				margin: 10px 0;
				padding: 10px;
				border: 1px solid #D3D3D3;
                width: 30%;
			}
            .teams-container {
                display: flex;
                flex-wrap: wrap;
                justify-content: space-around;
            }
            .team-left {
                width: 30%;
                float: left;
            }
            .team-right {
                width: 65%;
                float: right;
            }
        </style>';
    
    if(empty($teamQuery)) {
        $allTeamsResponse = sendQuery(TP_API_V2_ENDPOINT . '/teams?orderBy=Name&orderDir=asc', $getJsonOptions, true);
        $allTeams = $allTeamsResponse['data'];
        //var_dump($allTeams);

        $content .= '<h2 class="theme-color"> Teams </h2>';
        $content .= '<div class="teams-container">';
            foreach($allTeams as $team) {
                $content .= '<div class="single-team">';
                    $content .= '<h4 class="theme-color"><a href="?team='.$team['Name'].'">' . $team['Name'] . ' (' . $team['ShortName'] . ') </a></h4>';
                    $content .= '<p>' . $team['Description'] . '</p>';
                    $content .= '<div class="team-left">';
                        $content .= '<h6 class="theme-color"> Members </h6>';
                        foreach($team['Users'] as $member) {
                            $content .= '<p>' . $member['UserId'] . '</p>';
                        }
                    $content .= '</div>';
                    $content .= '<div class="team-right">';
                        $content .= '<h6 class="theme-color"> Campaigns </h6>';
                        foreach($team['Campaigns'] as $run) {
                            $content .= '<p><a href="' . get_europeana_url() . '/runs/' . $run['Name'] . '" target="_blank">' . $run['Name'] . '</a></p>';
                        }
                    $content .= '</div>';
                    $content .= '<div style="clear:both;"></div>';
                $content .= '</diV>';
            }
        $content .= '</div>';


    } else {
        echo 'Hello';
    }

    return $content;

}
add_shortcode( 'get_team',  '_TCT_get_team' );
?>
