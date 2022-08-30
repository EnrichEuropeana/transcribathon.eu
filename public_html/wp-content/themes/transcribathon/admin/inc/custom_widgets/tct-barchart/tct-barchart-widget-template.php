<?php global $wpdb;
if(sizeof($instance['tct-barchart-repeater'])>0){
	if(trim($instance['tct-barchart-label']) != ""){
		echo "<h3 class=\"tct_barchar_head\">".$instance['tct-barchart-label']."</h3>\n";
	}
	if(trim($instance['tct-barchart-areaheight']) != "" && trim($instance['tct-barchart-areaheight']) != "250"){
		$charheight = " style=\"height:".trim($instance['tct-barchart-areaheight'])."px;\"";
		$mh = (int)$instance['tct-barchart-areaheight'];
	}else{
		$charheight = " style=\"height:250px;\"";
		$mh = 250;
	}
	if(trim($instance['tct-barchart-barwidth']) != "" && trim($instance['tct-barchart-barwidth']) != "25"){
		$bw = " width:".trim($instance['tct-barchart-barwidth'])."px;";
	}else{
		$bw = "";
	}
	
	$highest = 0;
	
	// Steps vorbereiten
	if(trim($instance['tct-barchart-steps']) != ""){
		$stps = explode(",",$instance['tct-barchart-steps']);
		$steps = array();
		$h = 0;
		for($i=0; $i<sizeof($stps); $i++){
			$myh = 0;
			if(trim($stps[$i]) != "0"){
				$myh = (int)$stps[$i];
				$steps[$h]['totalnum'] = $myh;
				$steps[$h]['displaynum'] = ($myh-(int)trim($instance['tct-barchart-base-value']));
				if(($myh-(int)trim($instance['tct-barchart-base-value'])) > $highest){
					$highest = (int)($myh-(int)trim($instance['tct-barchart-base-value']));
				}
				$h++;
			}
		}
	}
	echo "<div class=\"tct_barchart_area\"".$charheight.">\n";
		echo "<div class=\"tct_barchart_holder\">\n";
		// Get percentage
		$vals = array();
		
		
		for($i=0; $i<sizeof($instance['tct-barchart-repeater']); $i++){
			$myh = 0;
			$vals[$i] = array();
			if($instance['tct-barchart-repeater'][$i]['tct-barchart-variable'] == "" || $instance['tct-barchart-repeater'][$i]['tct-barchart-variable'] == "fixed"){
				$myh = (int)trim($instance['tct-barchart-repeater'][$i]['tct-barchart-bar-value']);
			}elseif($instance['tct-barchart-repeater'][$i]['tct-barchart-variable'] == "campaign-related"){
				//$myh = 1000;
				$cp = $instance['tct-barchart-repeater'][$i]['tct-campaign-related-values']['tct-barchart-campaign'];
				$myh = 0;
				switch($instance['tct-barchart-repeater'][$i]['tct-campaign-related-values']['tct-barchart-campaign-value']){					
					case 'total-characters':
						$chars = $wpdb->get_results("SELECT SUM(cp.amount) FROM ".$wpdb->prefix."campaign_transcriptionprogress cp WHERE campaignid='".(int)$cp."'",ARRAY_N);
						$chars = array_column($chars,0);
						$myh = $chars[0];
					break;
					case 'total-characters-day':			
						$dateData = $instance['tct-barchart-repeater'][$i]['tct-campaign-related-values']['total-characters-day'];
						$date = $dateData["tct-barchart-campaign-year"]."-".str_pad($dateData["tct-barchart-campaign-month"], 2, "0", STR_PAD_LEFT)."-".str_pad($dateData["tct-barchart-campaign-day"], 2, "0", STR_PAD_LEFT);
						// Set request parameters for image data
						$url = TP_API_HOST."/tp-api/statistics/characters?campaign=".(int)$cp."&dateEnd=".$date."";
						$requestType = "GET";
			
						// Execude http request
						include TCT_THEME_DIR_PATH."admin/inc/custom_scripts/send_api_request.php";
			
						// Save image data
						$myh = json_decode($result, true);
					break;
					case 'total-characters-team':
						$team = $instance['tct-barchart-repeater'][$i]['tct-campaign-related-values']['total-characters-team'];
						// Set request parameters for image data
						$url = TP_API_HOST."/tp-api/statistics/teamsCharacters?campaign=".$cp."&team=".$team;
						$requestType = "GET";
			
						// Execude http request
						include TCT_THEME_DIR_PATH."admin/inc/custom_scripts/send_api_request.php";
			
						// Save image data
						$myh = json_decode($result, true);
					break;
					case 'documents-existing':
						$doks = get_posts(array('post_type' => 'documents','numberposts' => -1,'tax_query' => array(array('taxonomy' => 'tct_usertags','field' => 'id', 'terms' => 217))));
						$myh += sizeof($doks);
					break;
					case 'documents-started':
						$doks = $wpdb->get_results("SELECT COUNT(DISTINCT cp.docid) FROM ".$wpdb->prefix."campaign_transcriptionprogress cp WHERE campaignid='".(int)$cp."'",ARRAY_N);
						$doks = array_column($doks,0);
						$myh = (int)$doks[0];
						
					break;
					case 'documents-started-day':
						$year = $instance['tct-barchart-repeater'][$i]['tct-campaign-related-values']['total-characters-day']['tct-barchart-campaign-year'];
						$month = $instance['tct-barchart-repeater'][$i]['tct-campaign-related-values']['total-characters-day']['tct-barchart-campaign-month'];
						$day = $instance['tct-barchart-repeater'][$i]['tct-campaign-related-values']['total-characters-day']['tct-barchart-campaign-day'];
						$doks = $wpdb->get_results("SELECT COUNT(DISTINCT cp.docid) FROM ".$wpdb->prefix."campaign_transcriptionprogress cp WHERE campaignid='".(int)$cp."' AND datum <= '".$year."-".$month."-".$day." 23:59:59'",ARRAY_N);
						$doks = array_column($doks,0);
						$myh = (int)$doks[0];
					break;
					case 'documents-started-team':
						$team = $instance['tct-barchart-repeater'][$i]['tct-campaign-related-values']['total-characters-team'];
						if ((int)$cp == 41){
							$doks = $wpdb->get_results("SELECT COUNT(DISTINCT cp.docid) FROM ".$wpdb->prefix."campaign_transcriptionprogress cp 
													JOIN ".$wpdb->prefix."teams t ON cp.teamid = t.team_id
													WHERE datum <= '2018-12-06 14:15:00' AND cp.campaignid='".(int)$cp."' AND t.team_title = '".$team."'",ARRAY_N);
							$doks = array_column($doks,0);
						}
						else {
							$doks = $wpdb->get_results("SELECT COUNT(DISTINCT cp.docid) FROM ".$wpdb->prefix."campaign_transcriptionprogress cp 
														JOIN ".$wpdb->prefix."teams t ON cp.teamid = t.team_id
														WHERE cp.campaignid='".(int)$cp."' AND t.team_title = '".$team."'",ARRAY_N);
							$doks = array_column($doks,0);
						}
						$myh = (int)$doks[0];
					break;
					case 'participating-individuals':
						$myh += 40;
					break;
					case 'participating-teams':
						$myh += 50;
						
					break;	
				}
				
				
				
				
			}else{
				$myh = (int)do_shortcode("[tct-numbers kind=\"".$instance['tct-barchart-repeater'][$i]['tct-barchart-variable']."\"]");
			}
			$vals[$i]['totalnum'] = $myh;
			$vals[$i]['displaynum'] = ($myh-(int)trim($instance['tct-barchart-base-value']));
			if((int)($myh-(int)trim($instance['tct-barchart-base-value'])) > $highest){
				$highest = (int)($myh-(int)trim($instance['tct-barchart-base-value']));
			}
			if(trim($instance['tct-barchart-repeater'][$i]['tct-barchart-bar-color']) != "" && trim($instance['tct-barchart-repeater'][$i]['tct-barchart-bar-color']) != "#cccccc"){
				$vals[$i]['color'] = trim($instance['tct-barchart-repeater'][$i]['tct-barchart-bar-color']);
			}else{
				$vals[$i]['color'] = "";
			}
			if(trim($instance['tct-barchart-repeater'][$i]['tct-barchart-bar-label']) != ""){
				$vals[$i]['label'] = trim($instance['tct-barchart-repeater'][$i]['tct-barchart-bar-label']);
			}else{
				$vals[$i]['label'] = "";
			}
		}
		$einpro = $highest/100;
		for($i=0; $i<sizeof($instance['tct-barchart-repeater']); $i++){
			if($vals[$i]['color'] != ""){
				$bc = " background-color:".$vals[$i]['color'].";";
			}else{
				$bc = "";
			}
			if((int)($vals[$i]['displaynum']/$einpro) <1){
				$bh = " height:1px;";
			}else{
				$bh = " height:".($vals[$i]['displaynum']/$einpro)."%;";
			}
			echo "<div class=\"tct_barchart_bar\" style=\"".$bw.$bh.$bc."\">\n";
				echo "<span>".(int)$vals[$i]['totalnum']."</span>\n";
			echo "</div>\n";

		}
		for($i=0; $i<sizeof($steps); $i++){
			$bh = " height:".($steps[$i]['displaynum']/$einpro)."%;";
			echo "<div class=\"tct_barchart_step\" style=\"".$bw.$bh."\">\n";
				echo "<span>".(int)$steps[$i]['totalnum']."</span>\n";
			echo "</div>\n";

		}
		
		echo "</div>\n"; // .tct_barchart_holder
	
	
	echo "</div>\n"; // .tct_barchart_area
	echo "<div class=\"tct_barchart_legend\">\n";
	
	echo "<table>\n";
	for($i=0; $i<sizeof($instance['tct-barchart-repeater']); $i++){
		echo "<tr>\n";
			if($vals[$i]['color'] != ""){
				$bc = " style=\"background-color:".$vals[$i]['color'].";\"";
			}else{
				$bc = "";
			}
			echo "<td><span class=\"colorbox\"".$bc."></span></td>\n";
			echo "<td>".$vals[$i]['label'].":</td>\n";
			echo "<td class=\"alg_r\">".(int)$vals[$i]['totalnum']."</td>\n";
		echo "</tr>\n";
	}
	echo "</table>\n";
	echo "</div>\n"; // .tct_barchart_legend
	


}





?>