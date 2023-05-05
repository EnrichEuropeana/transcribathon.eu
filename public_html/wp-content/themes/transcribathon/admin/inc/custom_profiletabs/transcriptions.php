<?php
/* 
Shortcode: transcription_tab
Description: Creates the transcription profile tab
*/
function _TCT_transcription_tab( $atts ) {  
    
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
    
        echo "<div class=\"column-rgs span_1_of_5 alg_c\">\n";	
           echo "<div class=\"number-ball alg_c\">\n";
                echo "<div class=\"number-ball-content\">\n";
                    echo "<p>".number_format_i18n($profileStatistics['Miles'])."</p>";
                    echo "<span>"._x('miles run', 'Transcription-Tab on Profile', 'transcribathon'  )."</span>";
                echo "</div>\n";
            echo "</div>\n";	
        echo "</div>\n";
        echo "<div class=\"column-rgs span_1_of_5 alg_c\">\n";				
            echo "<div class=\"number-ball\">\n";
                echo "<div class=\"number-ball-content\">\n";
                    echo "<p>".number_format_i18n($profileStatistics['TranscriptionCharacters'])."</p>";
                    echo "<span>"._x('characters', 'Transcription-Tab on Profile', 'transcribathon'  )."</span>";
                echo "</div>\n";
            echo "</div>\n";	
        echo "</div>\n";
        echo "<div class=\"column-rgs span_1_of_5 alg_c\">\n";				
            echo "<div class=\"number-ball\">\n";
                echo "<div class=\"number-ball-content\">\n";
                    echo "<p>".number_format_i18n($profileStatistics['Locations'])."</p>";
                    echo "<span>"._x('locations', 'Transcription-Tab on Profile', 'transcribathon'  )."</span>";
                echo "</div>\n";
            echo "</div>\n";	
        echo "</div>\n";	
        echo "<div class=\"column-rgs span_1_of_5 alg_c\">\n";	
            echo "<div class=\"number-ball\">\n";
                echo "<div class=\"number-ball-content\">\n";
                    echo "<p>".number_format_i18n($profileStatistics['Enrichments'])."</p>";
                    echo "<span>"._x('enrichments', 'Transcription-Tab on Profile', 'transcribathon'  )."</span>";
                echo "</div>\n";
            echo "</div>\n";	
        echo "</div>\n";	
        echo "<div class=\"column-rgs span_1_of_5\">\n";				
            echo "<div class=\"number-ball alg_c\">\n";
                echo "<div class=\"number-ball-content\">\n";
                    echo "<p>".number_format_i18n($profileStatistics['DocumentCount'])."</p>";
                    echo "<span>"._x('documents', 'Transcription-Tab on Profile', 'transcribathon'  )."</span>";
                echo "</div>\n";
            echo "</div>\n";
        echo "</div>\n";
    echo "</div>\n";


    echo "<p>&nbsp;</p>\n<div id=\"personal_chart\">\n";
        echo "<script type=\"text/javascript\">\n";
            if(trim($amt[0][0]) != "" && (int)$amt[0][0] > 0){
                echo "getTCTlinePersonalChart('days','".date('Y-m-')."01','".date('Y-m-t')."','personal_chart','".um_profile_id()."';\n";
            }else{
                echo "getTCTlinePersonalChart('months','".date('Y-')."01-01','".date('Y-m-t',strtotime(date('Y').'-12-01'))."','personal_chart','".um_profile_id()."');\n";
            }
        echo "</script>\n";
    echo "</div>\n";

    //$docs = $wpdb->get_results("SELECT crh.*,pst.post_title AS title,SUM(crh.amount) AS menge,MAX(crh.datum) as zeitpunkt FROM ".$wpdb->prefix."user_transcriptionprogress crh LEFT JOIN ".$wpdb->prefix."posts pst ON pst.ID = crh.docid WHERE crh.userid='".um_profile_id()."' GROUP BY crh.docid ORDER BY crh.datum DESC",ARRAY_A);
		
    /*Set request parameters*/
     $url = TP_API_HOST."/tp-api/transcriptionProfile/".um_profile_id();
    $requestType = "GET";

    // Execude http request
    include dirname(__FILE__)."/../custom_scripts/send_api_request.php";

    // Display data
    $documents = json_decode($result, true);
    
	echo "<h2 style=\"margin-left: 50px;\">"._x('My Contributions','Transcription-Tab on Profile', 'transcribathon'  )."</h2>\n";
		echo "<div class=\"doc-results profile\" style=\"padding: 0 50px;\">\n";
            echo "<div class=\"tableholder\">\n";
                echo "<div class=\"tablegrid\">\n";	
                    echo "<div class=\"section group sepgroup tab\">\n";
                        $i = 0;
                        $k = 0;
                        if ($documents != null) {
                            foreach ($documents as $document){
                                
                                if ($i > 3) {
                                    if($k >= 1) {
                                        echo "</div>\n<div class=\"section group sepgroup tab\" style=\"display:none;\">\n";
                                    } else {
                                        echo "</div>\n<div class=\"section group sepgroup tab\">\n";
                                    }
                                    $k += 1;
                                    $i = 0;
                                }
                                echo "<div class=\"column span_1_of_4 collection\">\n";
                                    //$thumb_url = wp_get_attachment_image_src( get_post_thumbnail_id( $doc['docid'] ),'post-thumbnail');
                                    //$c = get_post_custom($doc['docid']);
                                        echo "<a href=\"".str_replace("://", "://".$document['ProjectUrl'].".", home_url()."/documents/story/item?item=".$document['ItemId'])."\">";
                                        
                                            $image = json_decode($document['ItemImageLink'], true);

                                            
                                            $imageLink = createImageLinkFromData($image, array('size' => '280,140', 'page' => 'search'));
                                            
                                            if($image['height'] == null) {
                                                $imageLink = str_replace('full', '50,50,1800,1100', $imageLink);
                                            }

                                            echo  '<img src='.$imageLink.' loading="lazy" width="280" height="140" alt="edited-item">';
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

                            if(count($documents) > 0) {
                                echo "</div>";
                                echo "<div id='profile-more'> Show More </div>";
                            }
                        }
                    echo "</div>\n";	
                echo "</div>\n";
            echo "</div>\n";
		echo "</div>\n"; 
	
	echo "<script>
        let itemRows = document.querySelectorAll('.section.group.sepgroup.tab');
        let showButton = document.getElementById('profile-more');

        showButton.addEventListener('click',function(){
            if(!showButton.classList.contains('shown')) {
                for(let row of itemRows) {
                    row.style.display = 'table-row';
                    showButton.classList.add('shown');
                    showButton.textContent = 'Show Less';
                }
            } else {
                let i = 0;
                for(let row of itemRows) {
                    if(i < 2) {
                        i++;
                        continue;
                    }
                    row.style.display = 'none';
                    showButton.classList.remove('shown');
                    showButton.textContent = 'Show More';
                    i ++;
                }
            }
            
        });";
    echo "</script>";
    echo "<style>
        #profile-more {
            width: 100vw;
            text-align: center;
            color: #0a72cc;
        }
    ";
    echo "</style>";


}
add_shortcode( 'transcription_tab', '_TCT_transcription_tab' );
?>