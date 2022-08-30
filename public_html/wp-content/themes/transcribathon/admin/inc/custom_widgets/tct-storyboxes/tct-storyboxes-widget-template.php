<?php
global $wpdb;
$myid = uniqid(rand()).date('YmdHis');
$base = 0;

if ( ! is_admin() ) {

   
                if($instance['tct-storyboxes-headline'] != ""){ echo "<h1>".str_replace("\n","<br />",$instance['tct-storyboxes-headline'])."</h1>\n"; }
                $stories = array();
                $docs = array();

                $storyIds = $instance['tct-storyboxes-storybunch'];
                if(isset($storyIds) && trim($storyIds) != ""){
                    $limit = 12; 
                    $requestType = "GET";
                    $url = TP_API_HOST."/tp-api/storiesMinimal?storyId=".str_replace(' ', '', $storyIds);
                    include dirname(__FILE__)."/../../custom_scripts/send_api_request.php";
                    $storyData = json_decode($result, true);
                    
                }else if(isset($instance['tct-storyboxes-datasets']) && is_array($instance['tct-storyboxes-datasets']) 
                            && sizeof($instance['tct-storyboxes-datasets'])>0 || isset($instance['tct-storyboxes-datasets']) && trim($instance['tct-storyboxes-datasets']) != "" ){
                    if(!is_array($instance['tct-storyboxes-datasets'])){
                        $url = TP_API_HOST."/tp-api/storiesMinimal?DatasetId=".(int)trim($instance['tct-storyboxes-datasets']);
                    }else{
                        $url = TP_API_HOST."/tp-api/storiesMinimal?DatasetId=".implode(",",$instance['tct-storyboxes-datasets']);
                    }
                    
                    if(isset($instance['tct-storyboxes-languages']) && is_array($instance['tct-storyboxes-languages']) 
                            && sizeof($instance['tct-storyboxes-languages'])>0 || isset($instance['tct-storyboxes-languages']) && trim($instance['tct-storyboxes-languages']) != "" ){
                        if(!is_array($instance['tct-storyboxes-languages'])){
                            $url .= "&StorydcLanguage=".trim(urlencode($instance['tct-storyboxes-languages']));
                        }else{
                            $url .= "&StorydcLanguage=".implode(",",$instance['tct-storyboxes-languages']);
                        }
                    }
    
                    $url .= "&AndOr=".$instance['tct-storyboxes-AndOr'];
                    $requestType = "GET";
                    include dirname(__FILE__)."/../../custom_scripts/send_api_request.php";
                    $storyData = json_decode($result, true);
                }else if(isset($instance['tct-storyboxes-languages']) && is_array($instance['tct-storyboxes-languages']) 
                        && sizeof($instance['tct-storyboxes-languages'])>0 || isset($instance['tct-storyboxes-languages']) && trim($instance['tct-storyboxes-languages']) != "" ){
                    if(!is_array($instance['tct-storyboxes-languages'])){
                        $url .= TP_API_HOST."/tp-api/storiesMinimal?StorydcLanguage=".trim(urlencode($instance['tct-storyboxes-languages']));
                    }else{
                        $url .= TP_API_HOST."/tp-api/storiesMinimal?StorydcLanguage=".implode(",",$instance['tct-storyboxes-languages']);
                    }

                    $url .= "&AndOr=".$instance['tct-storyboxes-AndOr'];
                    $requestType = "GET";
                    include dirname(__FILE__)."/../../custom_scripts/send_api_request.php";
                    $storyData = json_decode($result, true);
                }
                if(isset($instance['tct-storyboxes-cols']) && trim($instance['tct-storyboxes-cols']) != ""){ 
                    $tct_doccols = (int)$instance['tct-storyboxes-cols'];
                }else{
                    $tct_doccols = 4;
                }
                
                if(is_array($storyData) && sizeof($storyData) > 0){
                    
                    
                    
                    
                    $storyDataRev = array_reverse($storyData);

                    $limit = 12;
                    $stand = 0;
                    
                    $storyIdList = array();
                    foreach($storyData as $story) {
                        array_push($storyIdList, $story['StoryId']);
                    }
                    
                    $portions = array_chunk($storyIdList, $limit);
                    echo "<div id=\"tct_storyboxidholder_".$myid."\" style=\"display:none;\">\n";
                    $i=0;
                    foreach($portions as $p){
                        echo "<div class=\"tct_sry_".$i."\">".implode(',',$p)."</div>\n";
                        $i++;
                    }
                    echo "</div>\n";

                    echo "<div id=\"doc-results_".$myid."\">\n";
                        echo "<div class=\"tableholder\">\n";
                            echo "<div class=\"tablegrid\">\n";
                                echo "<div class=\"section group sepgroup tab\">\n";
                                    $j = 1;
                                    $i=0;
                                    foreach($storyData as $story){
                                        if($i<$tct_doccols) {
                                             $i++; 
                                        } else { 
                                            $i=1; echo "</div>\n<div class=\"section group tab sepgroup\">\n"; 
                                        }
                                        
                                            echo  '<div class="col span_1_of_4 collection" style="padding: 8px;">';
                                                echo  '<div class="dcholder">';
                                                
                                                    $image = json_decode($story['PreviewImage'], true);

                                                    if (substr($image['service']['@id'], 0, 4) == "http") {
                                                        $gridImageLink = $image['service']['@id'];
                                                    }
                                                    else {
                                                        $gridImageLink = "http://".$image['service']['@id'];
                                                    }

                                                    if ($image["width"] != null || $image["height"] != null) {
                                                        if ($image["width"] <= ($image["height"] * 2)) {
                                                            $gridImageLink .= "/0,0,".$image["width"].",".round($image["width"] / 2);
                                                        }
                                                        else {
                                                            $gridImageLink .= "/".round(($image["width"] - $image["height"]) / 2).",0,".($image["height"] * 2).",".$image["height"];
                                                        }
                                                    }
                                                    else {
                                                        $gridImageLink .= "/full";
                                                    }
                                                    $gridImageLink .= "/280,140/0/default.jpg";

                                                    echo  "<a class='grid-view-image' href='".home_url()."/documents/story/?story=".$story['StoryId']."'>";
                                                        echo  '<img src='.$gridImageLink.'>';
                                                    echo  "</a>";

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
                                                    echo '</div>';
                                                echo '</div>';

                                                echo  '<div class="">';
                                                    echo  '<h3 class="theme-color">';
                                                        echo  "<a class='storybox-title' href='".home_url()."/documents/story/?story=".$story['StoryId']."'>";
                                                            echo  $story['dcTitle'];
                                                        echo  "</a>";
                                                    echo  '</h3>';
                                                    /*
                                                    echo  '<div class="search-page-single-result-description">';
                                                        echo  $story['dcDescription'];
                                                    echo  '</div>';*/
                                                    echo  '<span style="display: none">...</span>';
                                                echo  '</div>';
                                                
                                                echo  '<div style="clear:both"></div>';
                                            echo  '</div>';

                                        //include(locate_template('document.php'));
                                        //get_template_part(document);
                                        if ($j >= $limit) {
                                            break;
                                        }
                                        else if($tct_doccols === 4){
                                            if($i==2){ echo "<span class=\"sep\"></span>\n";}
                                        }
                                        else if($tct_doccols === 3){
                                            if($i==2){ echo "<span class=\"sep\"></span>\n";}
                                        }
                                        $j++;
                                    }
                                    wp_reset_postdata();
                                echo "</div>\n";	
                            echo "</div>\n";
                        echo "</div>\n";
                    if(sizeof($portions) > 1){
                        echo "<a href=\"\" class=\"tct-vio-but load-more-storyboxes theme-color-background\" id=\"tct_storyboxmore_".$myid."\" onclick=\"tct_storybox_getNextTwelve('".$myid."','".((int)$stand+1)."','".$tct_doccols."'); return false;\">"._x('Load more stories','Story-Box Widget','transcribathon')."</a>\n";
                    }
                    echo "</div>\n";
                    }
                    echo "<p style=\"display:block; clear:both;\"></p>\n";
                
        
    }
    


?>