<?php
/* 
Shortcode: contributions_tab
Description: Creates the contributions profile tab
*/
function _TCT_contributions_tab( $atts ) {  
    
$theme_sets = get_theme_mods();
        // Set request parameters for image data
        $requestData = array(
            'key' => 'testKey'
        );
        $url = TP_API_HOST."/tp-api/profileStatistics/".um_profile_id();
        $requestType = "GET";

        // Execude http request
        include dirname(__FILE__)."/../custom_scripts/send_api_request.php";

        // Save image data
        $profileStatistics = json_decode($result, true);
        $profileStatistics = $profileStatistics[0];

    //$docs = $wpdb->get_results("SELECT crh.*,pst.post_title AS title,SUM(crh.amount) AS menge,MAX(crh.datum) as zeitpunkt FROM ".$wpdb->prefix."user_transcriptionprogress crh LEFT JOIN ".$wpdb->prefix."posts pst ON pst.ID = crh.docid WHERE crh.userid='".um_profile_id()."' GROUP BY crh.docid ORDER BY crh.datum DESC",ARRAY_A);
		
    /*Set request parameters*/
    $url = TP_API_HOST."/tp-api/transcriptionProfile/".um_profile_id();
    $requestType = "GET";

    // Execude http request
    include dirname(__FILE__)."/../custom_scripts/send_api_request.php";
    
    // Display data
    $documents = json_decode($result, true);
    
	echo "<h2>"._x('My Contributions','Contributions-Tab on Profile', 'transcribathon'  )."</h2>\n";
		echo "<div class=\"doc-results profile\">\n";
            echo "<div class=\"tableholder\">\n";
                echo "<div class=\"tablegrid\">\n";	
                    echo "<div class=\"section group sepgroup tab\">\n";
                        $i=0;
                        if ($documents != null) {
                            foreach ($documents as $document){
                                //var_dump($transcription);
                                if($i>3){ echo "</div>\n<div class=\"section group sepgroup tab\">\n"; $i=0; }
                                echo "<div class=\"column span_1_of_4 collection\">\n";
                                    //$thumb_url = wp_get_attachment_image_src( get_post_thumbnail_id( $doc['docid'] ),'post-thumbnail');
                                    //$c = get_post_custom($doc['docid']);
                                        echo "<a href=\"".str_replace("://", "://".$document['ProjectUrl'].".", home_url()."/documents/story/item?item=".$document['ItemId'])."\">";
                                        
                                            $image = json_decode($document['ItemImageLink'], true);

                                            $imageLink = createImageLinkFromData($image, array('size' => '280,140', 'page' => 'search'));
                                            
                                            if($image['height'] == null) {
                                                $imageLink = str_replace('full', '50,50,1800,1100', $imageLink);
                                            }

                                            // if (substr($image['service']['@id'], 0, 4) == "http") {
                                            //     $imageLink = $image['service']['@id'];
                                            // }
                                            // else {
                                            //     $imageLink = "http://".$image['service']['@id'];
                                            // }

                                            // if ($image["width"] != null || $image["height"] != null) {
                                            //     if ($image["width"] <= ($image["height"] * 2)) {
                                            //         $imageLink .= "/0,0,".$image["width"].",".($image["width"] / 2);
                                            //     }
                                            //     else {
                                            //         $imageLink .= "/".round(($image["width"] - $image["height"]) / 2).",0,".($image["height"] * 2).",".$image["height"];
                                            //     }
                                            // }
                                            // else {
                                            //     $imageLink .= "/full";
                                            // }
                                            // $imageLink .= "/280,140/0/default.jpg";

                                            echo  '<img src='.$imageLink.'>';
                                        echo  "</a>";

                                        echo "<h3 id= \"nopadmod\" class=\"nopad\">".$document['ItemTitle']."</h3>\n";
                                        echo "<p id= \"smalladinfo\" class=\"smallinfo\">";
                                        echo "Last time: ".date_i18n(get_option('date_format'),strtotime($document['Timestamp']))."<br />";

                                        $scoreOutput = "";
                                        foreach($document['Scores'] as $score) {
                                            switch ($score['ScoreType']) {
                                                case "Transcription":
                                                    if ($scoreOutput != "") {
                                                        $scoreOutput .= ", ";
                                                    }
                                                    $scoreOutput .= "Characters: ".$score['Amount'];
                                                    break;
                                                case "Location":
                                                    if ($scoreOutput != "") {
                                                        $scoreOutput .= ", ";
                                                    }
                                                    $scoreOutput .= "Locations: ".$score['Amount'];
                                                    break;
                                                case "Enrichment":
                                                    if ($scoreOutput != "") {
                                                        $scoreOutput .= ", ";
                                                    }
                                                    $scoreOutput .= "Enrichments: ".$score['Amount'];
                                                    break;
                                            }
                                        }
                                        echo $scoreOutput;
                                        echo "</p>\n";

                                    echo "<div class=\"docstate\" style=\"border-color: ".$document['CompletionColorCode']."  transparent transparent ".$document['CompletionColorCode']."\">
                                                ".$document['CompletionStatus']."
                                            </div>\n";
                                echo "</div>\n";
                                $i++;
                            }
                        }
                    echo "</div>\n";	
                echo "</div>\n";
            echo "</div>\n";
		echo "</div>\n";
	
	if(is_user_logged_in() &&  get_current_user_id() === 1){	}
			//$docs = $wpdb->get_results("SELECT *,SUM(amount) AS menge,MAX(datum) as zeitpunkt FROM ".$wpdb->prefix."user_transcriptionprogress WHERE userid='".um_profile_id()."' GROUP BY docid ORDER BY datum DESC",ARRAY_A);
			/*$amt = $wpdb->get_results("SELECT SUM(amount) FROM ".$wpdb->prefix."user_transcriptionprogress WHERE userid='".um_profile_id()."' and datum >= '".date('Y-m-')."01' AND datum <= '".date('Y-m-t')."'",ARRAY_N);
			echo "<pre>".print_r($amt,true)."</pre>";*/


}
//add_shortcode( 'contributions_tab', '_TCT_contributions_tab' );
add_shortcode( 'contributions_tab', '_TCT_contributions_tab' );

?>