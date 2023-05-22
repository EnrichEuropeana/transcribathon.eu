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

    if(empty($teamQuery)) {
        $allTeamsResponse = sendQuery(TP_API_V2_ENDPOINT . '/teams?orderBy=Name&orderDir=asc', $getJsonOptions, true);
        $allTeams = $allTeamsResponse['data'];
        //var_dump($allTeams);

        $content .= '<div class="container mx-auto">';
            $content .= '<h2 class="theme-color text-xl font-bold mb-8"> Teams </h2>';
            $content .= '<div
                class="
                    grid
                    grid-cols-1
                    md:grid-cols-3
                    lg:grid-cols-4
                    gap-2
                    lg:gap-4
                ">';
            foreach($allTeams as $team) {
                $content .= '<div
                    class="
                        border
                        border-solid
                        rounded-none
                        border-gray-500
                        p-4
                    ">';
                    $content .= '<h3
                        class="
                            theme-color
                            text-base
                            font-bold
                            mb-4
                        "><a href="?team='. $team['Name'] .'">' . $team['Name'] . ' (' . $team['ShortName'] . ') </a></h3>';
                    $content .= '<p class="text-sm my-1 min-h-[50px]">' . $team['Description'] . '</p>';

                    $content .= '<h4 class="theme-color text-sm font-bold my-2"> Members: </h6>';
                    $content .= '<div class="flex flex-wrap justify-start mb-4 min-h-[30px]">';
                        foreach($team['Users'] as $member) {
                            $content .= '<p class="text-sm my-0 mr-2">' . $member['UserId'] . '</p>';
                        }
                    $content .= '</div>';
                    $content .= '<div class="">';
                        $content .= '<h4 class="theme-color text-sm font-bold my-2"> Campaigns: </h4>';
                        foreach($team['Campaigns'] as $run) {
                            $content .= '<p class="text-sm my-0"><a href="' . get_europeana_url() . '/runs/' . str_replace(' ', '-',$run['Name']) . '" target="_blank">' . $run['Name'] . '</a></p>';
                        }
                    $content .= '</div>';
                    $content .= '<div style="clear:both;"></div>';
                $content .= '</diV>';
            }
            $content .= '</div>';
        $content .= '</div>';

    } else {
        $teamResponse = sendQuery(TP_API_V2_ENDPOINT . '/teams?Name=' . urlencode($teamQuery), $getJsonOptions, true);
        $teamData = $teamResponse['data'];

        $content .= '<div class="container mx-auto">';
            $content .= '<h2 class="theme-color text-xl font-bold mb-8">' . $teamData[0]['Name'] . ' (' . $teamData[0]['ShortName'] . ')</h2>';

            if(!empty($teamData[0]['Description'])) {
                $content .= '<h3 class="theme-color text-base font-bold mb-4"> Description: </h3>';
                $content .= '<p class="my-0 text-sm">' . $teamData[0]['Description'] . '</p>';
            }

            $content .= '<div>';
                $content .= '<div class="">';
                    $content .= '<h3 class="theme-color text-base font-bold my-8"> Members </h3>';
                    foreach($teamData[0]['Users'] as $member) {
                        $content .= '<p class="text-sm my-0">' . $member['UserId'] . '</p>';
                    }
                $content .= '</div>';
    
                $content .= '<div class="">';
                    $content .= '<h3 class="theme-color text-base font-bold my-8"> Campaigns </h3>'; 
                    foreach($teamData[0]['Campaigns'] as $run) {
                        $content .= '<p class="text-sm my-1"><a href="' . get_europeana_url() . '/runs/' . str_replace(' ', '-',$run['Name']) . '" target="_blank">' . $run['Name'] . '</a></p>';
                    }
                $content .= '</div>';
                $content .= '<div style="clear:both;"></div>';
            $content .= '</div>';
        $content .= '</div>';
    }

    return $content;

}
add_shortcode( 'get_team',  '_TCT_get_team' );
?>
