<?php
global $wpdb;
// $myid = uniqid(rand()).date('YmdHis');


if ( ! is_admin() ) {

    $storyId = $instance['tct-storyofmonth-storybunch'];
    $itemId = $instance['tct-storyofmonth-itemid'];
    $colNum = $instance['tct-storyofmonth-column'];


    if(isset($storyId) && trim($storyId) != ""){ 
        $requestType = "GET";
        $url = TP_API_HOST."/tp-api/storiesMinimal?storyId=".str_replace(' ', '', $storyId);
        include dirname(__FILE__)."/../../custom_scripts/send_api_request.php";
        $storyData = json_decode($result, true);
    }

    $storyTitle = !empty($instance['tct-storyofmonth-title']) ? $instance['tct-storyofmonth-title'] : $storyData[0]['dcTitle'];

    $imageLink = $instance['tct-storyofmonth-itemimage'] . '/50,50,1800,1100/300,200/0/default.jpg';

    if($instance['tct-storyofmonth-itemimage'] == '') {
        $image = json_decode($storyData[0]['PreviewImage'], true);
        
        $imageLink = createImageLinkFromData($image, array('size' => '300,200'));
        // don't load full image when dimensions are missing
        if($image['height'] == null) {
            $imageLink = str_replace('full', '50,50,1800,1100', $imageLink);
        }
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

    $compStatus = '<div class="relative h-4 px-0 mb-2.5">';
        $compStatus .= '<div
           class="
               bg-[#61e02f]
               absolute
               h-4
               z-40
            " style="width:' . $completed . '%;" title="Completed: ' . round($completed) . '% No. of Items: ' . $totalItems . '">&nbsp</div>';
        $compStatus .= '<div
            class="
                bg-[#ffc720]
                absolute
                h-4
                z-30
            " style="width:' . ($completed + $review) . '%;" title="Review: ' . round($review) . '% No. of Items: ' . $totalItems . '">&nbsp</div>';
        $compStatus .= '<div
            class="
                bg-[#fff700]
                absolute
                h-4
                z-20
            " style="width:' . ($completed + $review + $edit) . '%;" title="Edit: ' . round($edit) . '% No. of Items: ' . $totalItems . '">&nbsp</div>';
        $compStatus .= '<div
            class="
                bg-[#eeeeee]
                absolute
                h-4
                z-10" style="width:100%;" title="Not Started: ' . round($notStarted) . '% No. of Items: ' . $totalItems . '">&nbsp</div>';
    $compStatus .= '</div>';

                // if($instance['tct-storyofmonth-headline'] != ""){ echo "<h1>".str_replace("\n","<br />",$instance['tct-storyofmonth-headline'])."</h1>\n"; }
        // story of the month rework
        $content = '';

        $content .= '<div
            class="
                max-w-[300px]
                h-[450px]
                border
                border-solid
                border-gray-100
                bg-gray-100
                mb-10
            " id="doc-results_' . $storyId . '">';
            $content .= '<div class="img-holder relative">';
                $content .= '<a href="' . home_url() . '/documents/story/?story=' . $storyId . '">';
                    $content .= '<img src="' . $imageLink . '" alt="Story-' . $storyId . '" width="300" height="200">';
                $content .= '</a>';
                $content .= '<div
                    class="
                        theme-color
                        w-8
                        absolute
                        font-bold
                        text-center
                        text-sm
                        top-2.5
                        right-2.5
                        bg-gray-100
                    ">';
                    $content .= $instance['tct-storyofmonth-lng'];
                $content .= '</div>';
            $content .= '</div>';
            $content .= '<div class="status-holder relative">';
                $content .= $compStatus;
            $content .= '</div>';
            $content .= '<div
                class="
                    body-holder
                    px-4
                    pb-2.5
                    h-[225px]
                    overflow-hidden
                ">';
                $content .= '<div class="h-1/5">';
                    $content .= '<h2
                        class="
                            theme-color
                            text-base
                            font-bold
                            truncate
                            m-0
                        " title="' . $storyTitle . '">' . $storyTitle . '</h2>';
                    $content .= '<h4
                        class="
                            text-xs
                            text-gray-700
                            m-0
                        ">' . $instance['tct-storyofmonth-subline'] . '</h4>';
                $content .= '</div>';
                $content .= '<hr/>';
                $content .= '<p
                    class="
                        m-0
                        text-sm
                        h-4/6
                        line-clamp-[7]
                        mt-4
                    " title="'.$instance['tct-storyofmonth-description'].'">' . $instance['tct-storyofmonth-description'] . '</p>';
            $content .= '</div>';
        $content .= '</div>';

                
        echo $content;
    }
    


?>