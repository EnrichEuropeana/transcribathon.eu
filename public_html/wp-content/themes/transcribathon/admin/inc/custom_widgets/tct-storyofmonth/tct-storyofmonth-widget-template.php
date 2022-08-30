<?php
global $wpdb;
// $myid = uniqid(rand()).date('YmdHis');

if ( ! is_admin() ) {

   
                // if($instance['tct-storyofmonth-headline'] != ""){ echo "<h1>".str_replace("\n","<br />",$instance['tct-storyofmonth-headline'])."</h1>\n"; }

                
                    echo "<div id=\"doc-results_".$myid."\">\n";
                        echo "<div class=\"tableholder\">\n";
                            echo "<div class=\"tablegrid\">\n";
                                echo "<div class=\"section group sepgroup tab\">\n"; 
                                echo  '<div class="monthly_story col span_1_of_4 collection" style="padding: 8px;">';
                                
                                $itemIds = $instance['tct-storyofmonth-itemid'];

                                if(isset($itemIds) && trim($itemIds) != ""){ 
                                    $requestType = "GET";
                                    $url = TP_API_HOST."/tp-api/itemMinimal?itemId=".str_replace(' ', '', $itemIds);
                                    include dirname(__FILE__)."/../../custom_scripts/send_api_request.php";
                                    $itemData = json_decode($result, true);
                                    
                                }
                                foreach($itemData as $item){
                                echo  '<div class="dcholder">';
                                                
                                $image = json_decode($item['ImageLink'], true);

                                    if (substr($image['service']['@id'], 0, 4) == "http") {
                                        $gridImageLink = $image['service']['@id'];
                                    }
                                    else {
                                        $gridImageLink = "http://".$image['service']['@id'];
                                    }

                                    if ($image["width"] != null || $image["height"] != null) {
                                        if ($image["width"] <= $image["height"]) {
                                            $gridImageLink .= "/0,0,".$image["width"].",".$image["width"];
                                        }
                                        else {
                                            $gridImageLink .= "/0,0,".$image["height"].",".$image["height"];
                                        }
                                    }
                                    else {
                                        $gridImageLink .= "/full";
                                    }
                                    $gridImageLink .= "/500,500/0/default.jpg";

                                    echo  "<a class='grid-view-image' href='".home_url()."/documents/story/?story=".$item['StoryId']."'>";
                                        echo  '<img src='.$gridImageLink.'>';
                                    echo  "</a>";
                                    if($instance['tct-storyofmonth-lng'] != ""){ echo "<div class='story-lng' style=''><h1 class='theme-color'>".str_replace("\n","<br />",$instance['tct-storyofmonth-lng'])."</h1></div>\n"; }

                                echo '</div>';
                                }

//story
                                    $storyIds = $instance['tct-storyofmonth-storybunch'];

                                    if(isset($storyIds) && trim($storyIds) != ""){ 
                                        $requestType = "GET";
                                        $url = TP_API_HOST."/tp-api/storiesMinimal?storyId=".str_replace(' ', '', $storyIds);
                                        include dirname(__FILE__)."/../../custom_scripts/send_api_request.php";
                                        $storyData = json_decode($result, true);
                                        
                                    } 
                
                // if(is_array($storyData) && sizeof($storyData) > 0){
                       
                    // echo "<div id=\"doc-results_".$myid."\">\n";
                    //     echo "<div class=\"tableholder\">\n";
                    //         echo "<div class=\"tablegrid\">\n";
                    //             echo "<div class=\"section group sepgroup tab\">\n"; 
                    //             echo  '<div class="monthly_story col span_1_of_4 collection" style="position: relative; padding: 8px;">';
 
                                    foreach($storyData as $story){
                                       
                                    
                                    

                                    
                                                    // Get status data
                                                    $url = TP_API_HOST."/tp-api/completionStatus";
                                                    $requestType = "GET";

                                                    include dirname(__FILE__)."/../../custom_scripts/send_api_request.php";

                                                    // Save status data
                                                    $statusTypes = json_decode($result, true);

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
                                                    foreach($story['CompletionStatus'] as $status) {
                                                        $itemAmount += $status['Amount'];
                                                    }
                                                    
                                                    $totalPercent = 0;

                                                    // Create status objects for each status
                                                    foreach($story['CompletionStatus'] as $status) {
                                                        $statusObject = new stdClass;
                                                        $statusObject->Name = $status['Name'];
                                                        $statusObject->ColorCode = $status['ColorCode'];
                                                        $statusObject->ColorCodeGradient = $status['ColorCodeGradient'];
                                                        $statusObject->Amount = $status['Amount'];
                                                        $statusObject->Percentage = (round($status['Amount'] / $itemAmount, 2) * 100);

                                                        $statusData[$status['Name']] = $statusObject;
                                                        $totalPercent += $statusObject->Percentage;
                                                    }

                                                    // Make sure that percent total is 100
                                                    foreach ($statusData as $status) {
                                                        if ($status->Name == "Not Started") {
                                                            if ($totalPercent != 100) {
                                                                $status->Percentage += (100 - $totalPercent);
                                                            }
                                                        }
                                                    }
                                                                                                                
                                                    echo '<div class="box-progress-bar item-status-chart">';
                                                        echo '<div class="item-status-info-box box-status-bar-info-box">';
                                                            echo '<ul class="item-status-info-box-list">';
                                                                foreach ($statusData as $status) {
                                                                    $percentage = $status->Percentage;
                                                                    echo '<li>';
                                                                        echo '<span class="status-info-box-color-indicator" style="background-color:'.$status->ColorCode.';
                                                                                        background-image: -webkit-gradient(linear, left top, left bottom,
                                                                                        color-stop(0, '.$status->ColorCode.'), color-stop(1, '.$status->ColorCodeGradient.'));">';
                                                                        echo '</span>';
                                                                        echo '<span id="progress-bar-overlay-'.str_replace(' ', '-', $status->Name).'-section" class="status-info-box-percentage">';
                                                                            echo $percentage.'% | '.$status->Amount;
                                                                        echo '</span>';
                                                                        echo '<span class="status-info-box-text">';
                                                                            echo $status->Name;
                                                                        echo '</span>';
                                                                    echo '</li>';
                                                                }
                                                            echo '</ul>';
                                                        echo '</div>';

                                                        $CompletedBar = "";
                                                        $ReviewBar = "";
                                                        $EditBar = "";
                                                        $NotStartedBar = "";
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
                                                            echo $CompletedBar;
                                                        }
                                                        if ($ReviewBar != "") {
                                                            echo $ReviewBar;
                                                        }
                                                        if ($EditBar != "") {
                                                            echo $EditBar;
                                                        }
                                                        if ($NotStartedBar != "") {
                                                            echo $NotStartedBar;
                                                        }
                                                        if($instance['tct-storyofmonth-month'] != ""){ echo "<div class='storymonth story-date' style=''><h1 class='theme-color'>".str_replace("\n","<br />",$instance['tct-storyofmonth-month'])."</h1></div>\n"; }
                                                    echo '</div>';
                                                
                                                echo  '<div class="monthStoryContent">';
                                                    echo  '<h1 class="theme-color">';
                                                        // echo  "<a class='storybox-title' href='".home_url()."/documents/story/?story=".$story['StoryId']."'>";
                                                            echo  $story['dcTitle'];
                                                        // echo  "</a>";
                                                    echo  '</h1>';
                                    }
                                                    if($instance['tct-storyofmonth-subline'] != ""){ echo "<h3 class='storySubline'>".str_replace("\n","<br />",$instance['tct-storyofmonth-subline'])."</h3>\n"; }
                                                    if($instance['tct-storyofmonth-description'] != ""){ echo "<p class='storyDescrp'>".str_replace("\n","<br />",$instance['tct-storyofmonth-description'])."</p>\n"; }

                                                    echo  '<span style="display: none">...</span>';
                                                echo  '</div>';
                                                
                                                echo  '<div style="clear:both"></div>';
                                            echo  '</div>';

                                        //include(locate_template('document.php'));
                                        //get_template_part(document); 
                                    
                                    wp_reset_postdata();
                                echo "</div>\n";	
                            echo "</div>\n";
                        echo "</div>\n"; 
                    echo "</div>\n"; 
                    
                    echo "<p style=\"display:block; clear:both;\"></p>\n";
                
    }
    


?>