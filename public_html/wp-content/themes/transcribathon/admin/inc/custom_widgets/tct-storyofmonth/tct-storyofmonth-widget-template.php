<?php
global $wpdb;
// $myid = uniqid(rand()).date('YmdHis');


if ( ! is_admin() ) {

    $storyId = $instance['tct-storyofmonth-storybunch'];
    $itemId = $instance['tct-storyofmonth-itemid'];

    if(isset($storyId) && trim($storyId) != ""){ 
        $requestType = "GET";
        $url = TP_API_HOST."/tp-api/storiesMinimal?storyId=".str_replace(' ', '', $storyId);
        include dirname(__FILE__)."/../../custom_scripts/send_api_request.php";
        $storyData = json_decode($result, true);
        
    } 

    //var_dump($instance);

    $image = json_decode($storyData[0]['PreviewImage'], true);

    $imageLink = createImageLinkFromData($image, array('size' => '300,200'));
    // don't load full image when dimensions are missing
    if($image['height'] == null) {
        $imageLink = str_replace('full', '50,50,1800,1100', $imageLink);
    }

    $completionStatus = array(
        'Not Started' => 0,
        'Edit' => 0,
        'Review' => 0,
        'Completed' => 0
    );
    $totalItems = 0;

    foreach($storyData[0]['CompletionStatus'] as $status) {
        $completionStatus[$status['Name']] = $status['Amount'];
        $totalItems += $status['Amount'];
    }

    $completed = ($completionStatus['Completed'] / $totalItems) * 100;
    $review = ($completionStatus['Review'] / $totalItems) * 100;
    $edit = ($completionStatus['Edit'] / $totalItems) * 100;
    $notStarted = ($completionStatus['Not Started'] / $totalItems) * 100;

    $compStatus = "<div class='search-page-single-status' style='position:relative;height:15px;'>";
        $compStatus .= "<div class='search-status' style='height:15px;width:" . $completed . "%;background-color:#61e02f;z-index:4;position: absolute;' title='Completed: " . round($completed) . "% No. of Items: " . $totalItems . "'>&nbsp</div>";
        $compStatus .= "<div class='search-status' style='height:15px;width:" . ($completed + $review) . "%;background-color:#ffc720;z-index:3;position: absolute;' title='Review: " . round($review) . "% No. of Items: " . $totalItems . "'>&nbsp</div>";
        $compStatus .= "<div class='search-status' style='height:15px;width:" . ($completed + $review + $edit) . "%;background-color:#fff700;z-index:2;position: absolute;' title='Edit: " . round($edit) . "% No. of Items: " . $totalItems . "'>&nbsp</div>";
        $compStatus .= "<div class='search-status' style='height:15px;width:100%;background-color:#eeeeee;z-index:1;position: absolute;' title='Not Started: " . round($notStarted) . "% No. of Items: " . $totalItems . "'>&nbsp</div>";
    $compStatus .= "</div>";

                // if($instance['tct-storyofmonth-headline'] != ""){ echo "<h1>".str_replace("\n","<br />",$instance['tct-storyofmonth-headline'])."</h1>\n"; }
        // story of the month rework
        $content = '';

        $content .= '<div class="max-w-[300px] h-[450px] border border-solid border-gray-500" id="doc-results_' . $storyId . '">';
            $content .= '<div class="img-holder">';
                $content .= '<a href="' . home_url() . '/documents/story/?story=' . $storyId . '">';
                    $content .= '<img src="' . $imageLink . '" alt="Story-' . $storyId . '" width="300" height="200">';
                $content .= '</a>';
            $content .= '</div>';
            $content .= '<div class="status-lang-date relative">';
                $content .= '<div class="h-4 absolute w-full top-[-16px] px-2" style="background-color:rgba(0,0,0,0.5);"><span class="inline-block float-left text-xs text-white">' . $instance['tct-storyofmonth-lng'] . '</span><span class="inline-block float-right text-xs text-white">' . $instance['tct-storyofmonth-month'] . '</span></div>';
                $content .= $compStatus;
            $content .= '</div>';
            $content .= '<div class="body-holder p-2">';
                $content .= '<h2 class="theme-color text-base font-bold">' . $storyData[0]['dcTitle'] . '</h2>';
                $content .= '<h4 class="text-xs text-gray-700">' . $instance['tct-storyofmonth-subline'] . '</h4>';
                $content .= '<hr/>';
                $content .= '<p class="text-sm">' . $instance['tct-storyofmonth-description'] . '</p>';
            $content .= '</div>';
        $content .= '</div>';

                
        echo $content;
    }
    


?>