<?php
/* 
Shortcode: achievements_tab
Description: Creates the achievements profile tab
*/
function _TCT_achievements_tab( $atts ) { 
        // Set request parameters for image data
        $requestData = array(
            'key' => 'testKey'
        );
        $url = TP_API_HOST."/tp-api/achievements";
        $requestType = "GET";

        // Execude http request
        include dirname(__FILE__)."/../custom_scripts/send_api_request.php";

        // Save image data
        $achievements = json_decode($result, true);

        $url = TP_API_HOST."/tp-api/userAchievements";
        $requestType = "GET";

        // Execude http request
        include dirname(__FILE__)."/../custom_scripts/send_api_request.php";

        // Save image data
        $userAchievements = json_decode($result, true);
        
        echo "<div>";
            echo "<h1>Achievements</h1>";
    
            $achievementList = array();

            echo "<h5 class='completed-achievements-headline'>Completed</h5>";
            foreach ($achievements as $achievement) {
                $completed = false;
                foreach ($userAchievements as $userAchievement) {
                    if ($achievement['AchievementId'] == $userAchievement['AchievementId']) {
                        echo "<div class='completed-achievement achievement-row'>
                                <span class='achievement-name'>".$achievement['Name']."</span>
                                <span class='achievement-description'>".$achievement['Description']."</span>
                            </div>";
                        $completed = true;
                        break;
                    }
                }
                if ($completed == false) {
                    array_push($achievementList, $achievement);
                }
            }
            
            echo "</br>";

            echo "<h5 class='achievements-subheadline'>All Achievements</h5>";
            foreach ($achievementList as $achievement) {
                echo "<div class='achievement-row'>
                        <span class='achievement-name'>".$achievement['Name']."</span>
                        <span class='achievement-description'>".$achievement['Description']."</span>
                    </div>";
            }
        echo "</div>";

                


}
add_shortcode( 'achievements_tab', '_TCT_achievements_tab' );


?>