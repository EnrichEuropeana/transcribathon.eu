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
        
        if(!empty($teamData)) {

            // Bubbles css
            $twStatsBubblesClasses = <<<TW1
                w-20
                md:w-26
                lg:w-40
                h-20
                md:h-26
                lg:h-40
                border-4
                border-solid
                rounded-full
                border-gray-400
                text-center
                flex
                flex-col
                justify-center
            TW1;

            $twStatsNumbersClasses = <<<TW2
                m-0
                text-sm
                md:text-base
                lg:text-xl
                font-bold
                text-gray-400
            TW2;

            $twStatsLabelsClasses = <<<TW3
                m-0
                text-[11px]
                lg:text-sm
                text-gray-400
                uppercase
            TW3;

            $content .= '<div class="container mx-auto max-w-[80%]">';
                $content .= '<h2 class="theme-color text-xl font-bold mb-8">' . $teamData[0]['Name'] . ' (' . $teamData[0]['ShortName'] . ')</h2>';

                if(!empty($teamData[0]['Description'])) {
                    $content .= '<h3 class="theme-color text-base font-bold mb-4"> Description: </h3>';
                    $content .= '<p class="my-0 text-sm">' . $teamData[0]['Description'] . '</p>';
                }
                $content .= '<hr class=" bg-gray-500 my-6 ">';

                // Team statistics bubbles
            
                $content .= '<div
                    class="
                        grid
                        grid-cols-1
                        sm:grid-cols-3
                        gap-y-2
                        md:gap-x-5
                        place-items-center
                        my-6
                    ">';

                    $content .= '<div class=" ' . $twStatsBubblesClasses . ' ">';
                        $content .= '<p class=" ' . $twStatsNumbersClasses . ' ">' . number_format_i18n(count($teamData[0]['Users'])) . '</p>';
                        $content .= '<p class=" ' . $twStatsLabelsClasses . ' "> Members </p>';
                    $content .= '</div>';

                    $content .= '<div class=" ' . $twStatsBubblesClasses . ' ">';
                        $content .= '<p class=" ' . $twStatsNumbersClasses . ' ">' . number_format_i18n('11001') . '</p>';
                        $content .= '<p class=" ' . $twStatsLabelsClasses . ' "> Characters </p>';
                    $content .= '</div>';

                    $content .= '<div class=" ' . $twStatsBubblesClasses . ' ">';
                        $content .= '<p class=" ' . $twStatsNumbersClasses . ' ">' . number_format_i18n('512') . '</p>';
                        $content .= '<p class=" ' . $twStatsLabelsClasses . ' "> Documents </p>';
                    $content .= '</div>';
                
                $content .= '</div>';

                

                $content .= '<div class="lg:flex">';
                    $content .= '<div
                        class="
                            flex-initial
                            w-full
                            lg:w-1/2
                        ">';
                        $content .= '<h3 class="theme-color text-base font-bold my-8"> Members </h3>';
                        foreach($teamData[0]['Users'] as $member) {
                            $content .= '<p class="text-sm my-0">' . $member['UserId'] . '</p>';
                        }
                    $content .= '</div>';
    
                    $content .= '<div
                        class="
                            flex-initial
                            w-full
                            lg:w-1/2
                        ">';
                        $content .= '<h3 class="theme-color text-base font-bold my-8"> Campaigns </h3>'; 
                        foreach($teamData[0]['Campaigns'] as $run) {
                            $content .= '<p class="text-sm my-1"><a href="' . get_europeana_url() . '/runs/' . str_replace(' ', '-',$run['Name']) . '" target="_blank">' . $run['Name'] . '</a></p>';
                        }
                    $content .= '</div>';
                    $content .= '<div style="clear:both;"></div>';
                $content .= '</div>';
            $content .= '</div>';
        } else {
            $content .= '<div class="h-60 p-8">';
                $content .= '<h2
                    class="
                        theme-color
                        text-xl
                        font-bold
                        text-center
                    "> We are sorry, but we couldn\'t find ' . $_GET['team'] . ' team!</h2>';
            $content .= '</div>';
        }
    }

    return $content;

}
add_shortcode( 'get_team',  '_TCT_get_team' );
?>
