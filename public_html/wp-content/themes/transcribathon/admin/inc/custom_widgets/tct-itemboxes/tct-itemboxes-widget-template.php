<?php
global $wpdb;
$myid = uniqid(rand()).date('YmdHis');
$base = 0;

if ( ! is_admin() ) {

   
                if($instance['tct-storyboxes-headline'] != ""){ echo "<h1>".str_replace("\n","<br />",$instance['tct-storyboxes-headline'])."</h1>\n"; }
                $items = array();
                $docs = array();

                $itemIds = $instance['tct-storyboxes-storybunch'];
                if(isset($itemIds) && trim($itemIds) != ""){
                    $limit = 12; 
                    $requestType = "GET";
                    $url = TP_API_HOST."/tp-api/itemMinimal?itemId=".str_replace(' ', '', $itemIds);
                    include dirname(__FILE__)."/../../custom_scripts/send_api_request.php";
                    $itemData = json_decode($result, true);
                    
                }else if(isset($instance['tct-storyboxes-datasets']) && is_array($instance['tct-storyboxes-datasets']) 
                            && sizeof($instance['tct-storyboxes-datasets'])>0 || isset($instance['tct-storyboxes-datasets']) && trim($instance['tct-storyboxes-datasets']) != "" ){
                    if(!is_array($instance['tct-storyboxes-datasets'])){
                        $url = TP_API_HOST."/tp-api/itemMinimal?DatasetId=".(int)trim($instance['tct-storyboxes-datasets']);
                    }else{
                        $url = TP_API_HOST."/tp-api/itemMinimal?DatasetId=".implode(",",$instance['tct-storyboxes-datasets']);
                    }
                    
                    if(isset($instance['tct-storyboxes-languages']) && is_array($instance['tct-storyboxes-languages']) 
                            && sizeof($instance['tct-storyboxes-languages'])>0 || isset($instance['tct-storyboxes-languages']) && trim($instance['tct-storyboxes-languages']) != "" ){
                        if(!is_array($instance['tct-storyboxes-languages'])){
                            $url .= "&StorydcLanguage=".trim($instance['tct-storyboxes-languages']);
                        }else{
                            $url .= "&StorydcLanguage=".implode(",",$instance['tct-storyboxes-languages']);
                        }
                    }
    
                    // $url .= "&AndOr=".$instance['tct-storyboxes-AndOr'];
                    $requestType = "GET";
                    include dirname(__FILE__)."/../../custom_scripts/send_api_request.php";
                    $itemData = json_decode($result, true);
                }else if(isset($instance['tct-storyboxes-languages']) && is_array($instance['tct-storyboxes-languages']) 
                        && sizeof($instance['tct-storyboxes-languages'])>0 || isset($instance['tct-storyboxes-languages']) && trim($instance['tct-storyboxes-languages']) != "" ){
                    if(!is_array($instance['tct-storyboxes-languages'])){
                        // $url .= TP_API_HOST."/tp-api/storiesMinimal?StorydcLanguage=".trim($instance['tct-storyboxes-languages']);
                    }else{
                        // $url .= TP_API_HOST."/tp-api/storiesMinimal?StorydcLanguage=".implode(",",$instance['tct-storyboxes-languages']);
                    }

                    $url .= "&AndOr=".$instance['tct-storyboxes-AndOr'];
                    $requestType = "GET";
                    include dirname(__FILE__)."/../../custom_scripts/send_api_request.php";
                    $itemData = json_decode($result, true);
                }
                if(isset($instance['tct-storyboxes-cols']) && trim($instance['tct-storyboxes-cols']) != ""){ 
                    $tct_doccols = (int)$instance['tct-storyboxes-cols'];
                }else{
                    $tct_doccols = 4;
                }
                
                if(is_array($itemData) && sizeof($itemData) > 0){
                    
                    
                    
                    
                    
                    $limit = 12;
                    $stand = 0;
                    
                    $itemIdList = array();
                    foreach($itemData as $item) {
                        array_push($itemIdList, $item['ItemId']);
                    }
                    
                    $portions = array_chunk($itemIdList, $limit);
                    echo "<div id=\"tct_itemboxidholder_".$myid."\" style=\"display:none;\">\n";
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
                                    foreach($itemData as $item){
                                        if($i<$tct_doccols) {
                                             $i++; 
                                        } else { 
                                            $i=1; echo "</div>\n<div class=\"section group tab sepgroup\">\n"; 
                                        }
                                        
                                            echo  '<div class="col span_1_of_4 collection" style="padding: 8px;">';
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

                                                    echo  "<a class='grid-view-image' href='".home_url()."/documents/story/item/?story=".$item['StoryId']."&item=".$item['ItemId']."'>";
                                                        echo  '<img src='.$gridImageLink.'>';
                                                    echo  "</a>";

                                                    // Get status data
                                                    $url = TP_API_HOST."/tp-api/completionStatus";
                                                    $requestType = "GET";

                                                    include dirname(__FILE__)."/../../custom_scripts/send_api_request.php";

                                                    // Save status data
                                                    // $statusTypes = json_decode($result, true);
                                                    $statusTypes = json_decode($result, true);
                                                    // $statusTypes = $itemPageData['CompletionStatus'];

                                                    $statusData = array();
                                                    foreach ($statusTypes as $statusType) {
                                                        $statusObject = new stdClass;
                                                        $statusObject->Id = $statusType['CompletionStatusId'];
                                                        $statusObject->Name = $statusType['Name'];
                                                        $statusObject->ColorCode = $statusType['ColorCode'];
                                                        $statusObject->ColorCodeGradient = $statusType['ColorCodeGradient'];
                                                        $statusObject->Amount = 0;
                                                        $statusObject->Percentage = 0;
                                                        $statusData[$statusType['Id']] = $statusObject;
                                                    }
                                                    // $itemAmount = 0;
                                                    // foreach($story['CompletionStatus'] as $status) {
                                                    //     $itemAmount += $status['Amount'];
                                                    // }
                                                    $statusCompletionId = $item['CompletionStatusId'];
                                                    
                                                    $totalPercent = 0;

                                                    // Create status objects for each status
                                                    // foreach($story['CompletionStatus'] as $status) {
                                                    //     $statusObject = new stdClass;
                                                    //     $statusObject->Name = $status['Name'];
                                                    //     $statusObject->ColorCode = $status['ColorCode'];
                                                    //     $statusObject->ColorCodeGradient = $status['ColorCodeGradient'];
                                                    //     // $statusObject->Amount = $status['Amount'];
                                                    //     // $statusObject->Percentage = (round($status['Amount'] / $itemAmount, 2) * 100);

                                                    //     $statusData[$status['Name']] = $statusObject;
                                                    //     $totalPercent += $statusObject->Percentage;
                                                    // }

                                                    // Make sure that percent total is 100
                                                    foreach ($statusData as $status) {
                                                        if ($status->Name == "Not Started") {
                                                            if ($totalPercent != 100) {
                                                                $status->Percentage += (100 - $totalPercent);
                                                            }
                                                        }
                                                    }
                                                    $progressData = array(
                                                        $item['TranscriptionStatusId'],
                                                        $item['DescriptionStatusId'],
                                                        $item['LocationStatusId'],
                                                        $item['TaggingStatusId'],
                                                        //$itemData['AutomaticEnrichmentStatusName'],
                                                    );
                                                    $progressCount = array (
                                                                    '1' => 0,
                                                                    '2' => 0,
                                                                    '3' => 0,
                                                                    '4' => 0
                                                                );
                                                    foreach ($progressData as $status) {
                                                        $progressCount[$status] += 1;
                                                    }                        
                                                    echo '<div class="box-progress-bar item-status-chart">';
                                                        echo '<div class="item-status-info-box box-status-bar-info-box">';
                                                            echo '<ul class="item-status-info-box-list">';
                                                                
                                                                foreach ($statusTypes as $status) {
                                                                    $percentage = ($progressCount[$status['CompletionStatusId']] / sizeof($progressData)) * 100;
                                                                    echo '<li>';
                                                                        echo '<span class="status-info-box-color-indicator" style="background-color:'.$status['ColorCode'].';
                                                                                        background-image: -webkit-gradient(linear, left top, left bottom,
                                                                                        color-stop(0, '.$status['ColorCode'].'), color-stop(1, '.$status['ColorCodeGradient'].'));">';
                                                                            echo '</span>';
                                                                        echo '<span id="progress-bar-overlay-'.str_replace(' ', '-', $status['Name']).'-section" class="status-info-box-percentage" style="width: 20%;">';
                                                                        echo $percentage.'%';
                                                                            echo '</span>';
                                                                        echo '<span class="status-info-box-text">';
                                                                            echo $status['Name'];
                                                                        echo '</span>';
                                                                    echo '</li>';
                                                                }                                                          

                                                            echo '</ul>';
                                                        echo '</div>';

                                                        $CompletedBar = "";
                                                        $ReviewBar = "";
                                                        $EditBar = "";
                                                        $NotStartedBar = "";
                                                        // Add each status section to progress bar
                                                        foreach ($statusTypes as $status) {
                                                            $percentage = ($progressCount[$status['CompletionStatusId']] / sizeof($progressData)) * 100;

                                                            switch ($status['Name']) {
                                                                case "Completed":
                                                                    $CompletedBar .= '<div id="progress-bar-'.str_replace(' ', '-', $status['Name']).'-section" class="progress-bar progress-bar-section"
                                                                                        style="width: '.$percentage.'%; background-color:'.$status->ColorCode.';
                                                                                        ">';
                                                                        $CompletedBar .= $percentage.'%';
                                                                    $CompletedBar .= '</div>';
                                                                    break;
                                                                case "Review":
                                                                    $ReviewBar .= '<div id="progress-bar-'.str_replace(' ', '-', $status['Name']).'-section" class="progress-bar progress-bar-section"
                                                                                        style="width: '.$percentage.'%; background-color:'.$status->ColorCode.'">';
                                                                        $ReviewBar .= $percentage.'%';
                                                                    $ReviewBar .= '</div>';
                                                                    break;
                                                                case "Edit":
                                                                    $EditBar .= '<div id="progress-bar-'.str_replace(' ', '-', $status['Name']).'-section" class="progress-bar progress-bar-section"
                                                                                        style="width: '.$percentage.'%; background-color:'.$status->ColorCode.'">';
                                                                        $EditBar .= $percentage.'%';
                                                                    $EditBar .= '</div>';
                                                                    break;
                                                                case "Not Started":
                                                                    $NotStartedBar .= '<div id="progress-bar-'.str_replace(' ', '-', $status['Name']).'-section" class="progress-bar progress-bar-section"
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
                                                        echo  "<a class='storybox-title' href='".home_url()."/documents/story/item/?story=".$item['StoryId']."&item=".$item['ItemId']."'>";
                                                            echo  $item['Title']; 
                                                        echo  "</a>";
                                                    echo  '</h3>';
                                                    /*
                                                    echo  '<div class="search-page-single-result-description">';
                                                        echo  $item['dcDescription'];
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
                        echo "<a href=\"\" class=\"tct-vio-but load-more-storyboxes theme-color-background\" id=\"tct_itemboxmore_".$myid."\" onclick=\"tct_itembox_getNextTwelve('".$myid."','".((int)$stand+1)."','".$tct_doccols."'); return false;\">"._x('Load more items','Item-Box Widget','transcribathon')."</a>\n";
                    }
                    echo "</div>\n";
                    }
                    echo "<p style=\"display:block; clear:both;\"></p>\n";
                
        
    }
    


?>