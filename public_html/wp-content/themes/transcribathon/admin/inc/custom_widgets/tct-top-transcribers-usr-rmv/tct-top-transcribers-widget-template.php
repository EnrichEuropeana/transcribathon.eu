<?php
global $wpdb;
$myid = uniqid(rand()).date('YmdHis');
$base = 0;
//include theme directory for text hovering
$theme_sets = get_theme_mods();


// Limit
if(isset($instance['tct-top-transcribers-amount']) && trim($instance['tct-top-transcribers-amount']) != "" && (int)$instance['tct-top-transcribers-amount'] > 0){ $limit = (int)$instance['tct-top-transcribers-amount'];}else{$limit = 10;}
// Subject && Settings
if(isset($instance['tct-top-transcribers-subject']) && trim($instance['tct-top-transcribers-subject']) == "teams"){ 
	// TEAMS
	$subject = "teams"; 
	if(isset($instance['tct-top-transcribers-settings-teams']['tct-top-transcribers-kind']) && trim($instance['tct-top-transcribers-settings-teams']['tct-top-transcribers-kind']) == "campaign"){ if(isset($instance['tct-top-transcribers-settings-teams']['tct-top-transcribers-campaign']) && trim($instance['tct-top-transcribers-settings-teams']['tct-top-transcribers-campaign']) != ""){ $kind = "campaign"; $cp = (int)trim($instance['tct-top-transcribers-settings-teams']['tct-top-transcribers-campaign']); }else{ $kind = "all"; $cp = ""; } }else{ $kind = "all"; $cp = ""; }
}else{
	// INDIVIDUALS
	$subject = "individuals"; 
	if(isset($instance['tct-top-transcribers-settings-individuals']['tct-top-transcribers-kind']) && trim($instance['tct-top-transcribers-settings-individuals']['tct-top-transcribers-kind']) == "campaign"){ if(isset($instance['tct-top-transcribers-settings-individuals']['tct-top-transcribers-campaign']) && trim($instance['tct-top-transcribers-settings-individuals']['tct-top-transcribers-campaign']) != ""){ $kind = "campaign"; $cp = (int)trim($instance['tct-top-transcribers-settings-individuals']['tct-top-transcribers-campaign']); }else{ $kind = "all"; $cp = ""; } }else{ $kind = "all"; $cp = ""; }
}



if($instance['tct-top-transcribers-headline'] != ""){ echo "<h1>".str_replace("\n","<br />",$instance['tct-top-transcribers-headline'])."</h1>\n"; }
	// Build Item page content
	$content = "";
	$content = "<style>
				ul.topusers li.p1 span.rang{
					background: ".$theme_sets['vantage_general_link_hover_color'].";
				}
				ul.topusers li.p2 span.rang{
					background: rgba(9, 97, 129, 0.8);
				}
				ul.topusers li.p3 span.rang{
					background: rgba(9, 97, 129, 0.6);
				}
				ul.topusers li.p4 span.rang{
					background: rgba(9, 97, 129, 0.4);
				}
				ul.topusers li.p1{
					background: rgba(9, 97, 129, 0.6);
				}
				ul.topusers li.p2{
					background: rgba(9, 97, 129, 0.45);
				}
				ul.topusers li.p3{
					background: rgba(9, 97, 129, 0.3);
				}
				ul.topusers li.p4{
					background: rgba(9, 97, 129, 0.15);
				}
				</style>";
	if($kind === "campaign"){
		if($subject === "teams"){ // team
			// Set request parameters for image data
			$url = TP_API_HOST."/tp-api/rankings/teamCount?campaign=".$cp;
			$requestType = "GET";

			// Execude http request
			include TCT_THEME_DIR_PATH."admin/inc/custom_scripts/send_api_request.php";

			// Save image data
			$alltops = json_decode($result, true);
			if((int)$alltops <= $base){
				$base = (floor(((int)$alltops-1) / $limit)) * $limit;
			}
			

			// Set request parameters for image data
			$url = TP_API_HOST."/tp-api/rankings/teams?offset=".$base."&limit=".$limit."&campaign=".$cp;
			$requestType = "GET";

			// Execude http request
			include TCT_THEME_DIR_PATH."admin/inc/custom_scripts/send_api_request.php";

			// Save image data
			$topusrs = json_decode($result, true);
			
		}else{ // Invdl
			// Set request parameters for image data
			$url = TP_API_HOST."/tp-api/rankings/userCount?campaign=".$cp;
			$requestType = "GET";

			// Execude http request
			include TCT_THEME_DIR_PATH."admin/inc/custom_scripts/send_api_request.php";

			// Save image data
			$alltops = json_decode($result, true);
			if((int)$alltops <= $base){
				$base = (floor(((int)$alltops-1) / $limit)) * $limit;
			}
			

			// Set request parameters for image data
			$url = TP_API_HOST."/tp-api/rankings?offset=".$base."&limit=".$limit."&campaign=".$cp;
			$requestType = "GET";

			// Execude http request
			include TCT_THEME_DIR_PATH."admin/inc/custom_scripts/send_api_request.php";

			// Save image data
			$topusrs = json_decode($result, true);
		}
	}else{
		if($subject === "teams"){  // team
			// Set request parameters for image data
			$url = TP_API_HOST."/tp-api/rankings/teamCount";
			$requestType = "GET";

			// Execude http request
			include TCT_THEME_DIR_PATH."admin/inc/custom_scripts/send_api_request.php";

			// Save image data
			$alltops = json_decode($result, true);
			if((int)$alltops <= $base){
				$base = (floor(((int)$alltops-1) / $limit)) * $limit;
			}
			

			// Set request parameters for image data
			$url = TP_API_HOST."/tp-api/rankings/teams?offset=".$base."&limit=".$limit;
			$requestType = "GET";

			// Execude http request
			include TCT_THEME_DIR_PATH."admin/inc/custom_scripts/send_api_request.php";

			// Save image data
			$topusrs = json_decode($result, true);
		}else{ // Invdl
			
			// Set request parameters for image data
			$url = TP_API_HOST."/tp-api/rankings/userCount";
			$requestType = "GET";

			// Execude http request
			include TCT_THEME_DIR_PATH."admin/inc/custom_scripts/send_api_request.php";

			// Save image data
			$alltops = json_decode($result, true);
			if((int)$alltops <= $base){
				$base = (floor(((int)$alltops-1) / $limit)) * $limit;
			}
			

			// Set request parameters for image data
			$url = TP_API_HOST."/tp-api/rankings?offset=".$base."&limit=".$limit;
			$requestType = "GET";

			// Execude http request
			include TCT_THEME_DIR_PATH."admin/inc/custom_scripts/send_api_request.php";

			// Save image data
			$topusrs = json_decode($result, true);
		}
	}
	
	echo "<div id=\"tu_list_".$myid."\">\n";
		if(sizeof($topusrs)>0){
			echo "<ul class=\"topusers\">\n";
			if($subject === "teams"){
				$i=1;	
				foreach($topusrs as $team){
					echo "<li class=\"p".$i."\">";
					echo "<div class=\"tct-user-banner\"></div>\n"; 
					if($kind === "campaign"){
						// $miles = "<span class=\"milage\">".sprintf( esc_html( _n( '%s mile per member', '%s miles per member', (int)$team['MilesPerPerson'], 'transcribathon'  ) ), number_format_i18n((int)$team['MilesPerPerson'],2))."</span>\n";
						// $miles2 = "<span class=\"chars\">".sprintf( esc_html( _n( '%s mile in this campaign', '%s total miles in this campaign', (int)$team['Miles'], 'transcribathon'  ) ), number_format_i18n((int)$team['Miles']))."</span>\n";
						// $chars = "<span class=\"chars\">".sprintf( esc_html( _n( '%s character', '%s characters', (int)$team['TranscriptionCharacters'], 'transcribathon'  ) ), number_format_i18n((int)$team['TranscriptionCharacters']))."</span>\n";
						// $locs = "<span class=\"chars\">".sprintf( esc_html( _n( '%s location', '%s locations', (int)$team['Locations'], 'transcribathon'  ) ), number_format_i18n((int)$team['Locations']))."</span>\n";
						// $enrs = "<span class=\"chars\">".sprintf( esc_html( _n( '%s enrichment', '%s enrichments', (int)$team['Enrichments'], 'transcribathon'  ) ), number_format_i18n((int)$team['Enrichments']))."</span>\n";
						// echo "<span class=\"rang\">".$i."</span><h2>".$team['TeamName']."</h2><p>".$miles." | ".$miles2." <br /><span class=\"chars\">"._x('Achievements in this campaign','top-list','transcribathon').":</span><br />".$chars." | ".$locs." | ".$enrs."</p></li>\n";
						
						
					}else{
						$miles = "<span class=\"milage\">".sprintf( esc_html( _n( '%s Character per member', '%s Characters per member', (float)$team['Miles'], 'transcribathon'  ) ), number_format_i18n((float)$team['Miles']))."</span>\n";
						$chars = "<span class=\"chars\">".sprintf( esc_html( _n( '%s Character in total', '%s Characters in total', (int)$team['TranscriptionCharacters'], 'transcribathon'  ) ), number_format_i18n((int)$team['TranscriptionCharacters']))."</span>\n";
						echo "<span class=\"rang\">".$i."</span><h2>".$team['TeamName']."</h2><p>".$miles." | ".$chars."</p></li>\n";
					}
					$i++;
				}
			}else{
				$i=1;	
				foreach($topusrs as $usr){
					$aut = get_user_by('ID',$usr['UserId']);
					if($usr['UserId'] != 3341) {
					um_fetch_user( $usr['UserId']);
					echo "<li class=\"p".$i."\" style=\"background: #eeeeee; border-top: 8px solid #0c7da7; border-radius: 4px;\">";
					// echo "<div class=\"tct-user-banner ".um_user('role')."\">".ucfirst(um_user('role'))."</div>\n"; 
					/*
					$acs = $wpdb->get_results("SELECT ac.*,uc.campaign_title,CASE ac.placing WHEN '1' then uc.campaign_badge_1 WHEN '2' then uc.campaign_badge_2 WHEN '3' then uc.campaign_badge_3 END AS badge FROM ".$wpdb->prefix."user_achievments ac LEFT JOIN ".$wpdb->prefix."user_campaigns uc ON uc.id=ac.campaign WHERE ac.userid='".$usr['userid']."'",ARRAY_A);
					if(sizeof($acs)>0){
						echo "<div class=\"tct_tt-achievments\">\n"; 
						foreach($acs as $ac){
							echo "<div title=\"".$ac['campaign_title']."\"class=\"".$ac['badge']."\"></div>\n";
						}
						echo "</div>\n";
					}
					*/
					if($kind === "campaign"){
						// $miles = "<span class=\"chars\">".sprintf( esc_html( _n( '%s mile in this campaign', '%s total miles in this campaign', (int)$usr['Miles'], 'transcribathon'  ) ), number_format_i18n((int)$usr['Miles']))."</span>\n";
						// $chars = "<span class=\"chars\">".sprintf( esc_html( _n( '%s character', '%s characters', (int)$usr['TranscriptionCharacters'], 'transcribathon'  ) ), number_format_i18n((int)$usr['TranscriptionCharacters']))."</span>\n";
						// $locs = "<span class=\"chars\">".sprintf( esc_html( _n( '%s location', '%s locations', (int)$usr['Locations'], 'transcribathon'  ) ), number_format_i18n((int)$usr['Locations']))."</span>\n";
						// $enrs = "<span class=\"chars\">".sprintf( esc_html( _n( '%s enrichment', '%s enrichments', (int)$usr['Enrichments'], 'transcribathon'  ) ), number_format_i18n((int)$usr['Enrichments']))."</span>\n";
						// echo "<span class=\"rang\">".$i."</span><h2><a target=\"_blank\" href=\"".network_home_url()."profile/".$aut->user_nicename."/\">".um_user('display_name')."</a><span class=\"teammem\">".$temm."</span></h2><p>".$miles." | ".$chars."</p><br />".$chars." | ".$locs." | ".$enrs."</p></li>\n";
						
						
				// if(isset($instance['tct-top-transcribers-settings-individuals']['tct-top-transcribers-showteams']) && trim($instance['tct-top-transcribers-settings-individuals']['tct-top-transcribers-showteams']) == "1" && trim($usr['teams']) != ""){$temm = " (".str_replace(",",", ",$usr['teams']).")";}else{ $temm = "";}
				$charsc = "<span class=\"chars\"><strong>".sprintf( esc_html( _n( '%s Character in this campaign', '%s Characters in this campaign', (int)$usr['TranscriptionCharacters'], 'transcribathon'  ) ), number_format_i18n((int)$usr['TranscriptionCharacters']))."</strong></span>\n";
				$miles = "<span class=\"milage\">".sprintf( esc_html( _n( '%s Mile in this campaign', '%s Miles in this campaign', (int)$usr['Miles'], 'transcribathon'  ) ), number_format_i18n((int)$usr['Miles']))."</span>\n";
				$chars = "<span class=\"chars\">".sprintf( esc_html( _n( '%s Enrichment', '%s Enrichments', (int)$usr['Enrichments'], 'transcribathon'  ) ), number_format_i18n((int)$usr['Enrichments']))."</span>\n";
				echo "<span class=\"rang\" style=\"background: #ffffff; border-radius: 7px; color: #0c7da7;\">".$i."</span><h2><a style=\"color: #0c7da7 !important;\" href=\"".network_home_url()."profile/".$aut->user_nicename."/\">".um_user('display_name')."</a><span class=\"teammem\">".$temm."</span></h2><p>".$miles."</p><p><p>".$charsc." | ".$chars."</p></p></li>\n";
				// if($showshortnames > 0 && trim($usr['teams']) != ""){$temm = " (".str_replace(",",", ",$usr['teams']).")";}else{ $temm = "";}
				// $miles = "<span class=\"milage\">".sprintf( esc_html( _n( '%s Mile', '%s Miles', (int)$usr['Miles'], 'transcribathon'  ) ), number_format_i18n((int)$usr['Miles']))."</span>\n";
				// $charsc = "<span class=\"chars\"><strong>".sprintf( esc_html( _n( '%s Character in this campaign', '%s Characters in this campaign', (int)$usr['TranscriptionCharacters'], 'transcribathon'  ) ), number_format_i18n((int)$usr['TranscriptionCharacters']))."</strong></span>\n";
				// $chars = "<span class=\"chars\">".sprintf( esc_html( _n( '%s Character', '%s Characters', (int)$usr['totalchars'], 'transcribathon'  ) ), number_format_i18n((int)$usr['totalchars']))."</span>\n";
				// $content .= "<span class=\"rang\">".$i."</span><h2><a href=\"".network_home_url()."profile/".$aut->user_nicename."/\">".um_user('display_name')."</a><span class=\"teammem\">".$temm."</span></h2><p>".$charsc."</p><p><p>".$miles." | ".$chars."</p></p></li>\n";
						
					}else{
						if(isset($instance['tct-top-transcribers-settings-individuals']['tct-top-transcribers-showteams']) && trim($instance['tct-top-transcribers-settings-individuals']['tct-top-transcribers-showteams']) == "1" && trim($usr['teams']) != ""){$temm = " (".str_replace(",",", ",$usr['teams']).")";}else{ $temm = "";}
						$miles = "<span class=\"milage\">".sprintf( esc_html( _n( '%s Mile', '%s Miles', (int)$usr['Miles'], 'transcribathon'  ) ), number_format_i18n((int)$usr['Miles']))."</span>\n";
						$chars = "<span class=\"chars\">".sprintf( esc_html( _n( '%s Character', '%s Characters', (int)$usr['TranscriptionCharacters'], 'transcribathon'  ) ), number_format_i18n((int)$usr['TranscriptionCharacters']))."</span>\n";
						if ($aut != null) {
							echo "<span class=\"rang\" style=\"background: #ffffff; border-radius: 7px; color: #0c7da7;\">".$i."</span><h2><a target=\"_blank\" href=\"".network_home_url()."profile/".$aut->user_nicename."/\">".um_user('display_name')."</a><span class=\"teammem\">".$temm."</span></h2><p>".$miles." | ".$chars."</p></li>\n";
						}
						else {
							echo "<span class=\"rang\" style=\"background: #ffffff; border-radius: 7px; color: #0c7da7;\">".$i."</span><h2><a target=\"_blank\" href=\"Placeholder User\">Placeholder User</a><span class=\"teammem\">".$temm."</span></h2><p>".$miles." | ".$chars."</p></li>\n";
						} 
					}
					$i++;
				}
				}
			}
			echo "</ul>\n";
		
	echo "</div>\n"; // #tu_list...
	$showttmenu = 0;
	echo "<div id=\"ttnav_".$myid."\" class=\"ttnav ".$subject."\" style='height:50px'>\n";
	$menu = "<ul>\n";
	if($base>0){
		if(((int)$base-(int)$limit)<0){$newb = 0; }else{ $newb=((int)$base-(int)$limit); }
		$menu .= "<li class=\"ttprev\" style='float:left'><a href=\"\" onclick=\"getMoreTops('".$myid."','".$newb."','".$limit."','".$kind."','".$cp."','".$subject."','".(int)trim($instance['tct-top-transcribers-settings-individuals']['tct-top-transcribers-showteams'])."'); return false;\">"._x('Previous', 'Top-Transcribers-Slider-Widget (frontentd)','transcribathon')."</a></li>\n";
		$showttmenu++;
	}else{
		$prev = "";
	}
	
	if((int)$alltops > ($base+$limit)){
		$menu .= "<li class=\"ttnext\" style='float:right'><a href=\"\" onclick=\"getMoreTops('".$myid."','".((int)$base+(int)$limit)."','".$limit."','".$kind."','".$cp."','".$subject."','".(int)trim($instance['tct-top-transcribers-settings-individuals']['tct-top-transcribers-showteams'])."'); return false;\">"._x('Next', 'Top-Transcribers-Slider-Widget (frontentd)','transcribathon')."</a></li>\n";
		$showttmenu++;
	}else{
		$next = "";
	}
	
	$menu .= "<div style='text-align:center'><label>Go to Page <input type='text' name='page_input' id='page_input_".$subject."' style='width:40px'></label>
				<button id='goto_".$subject."' style='font-size:16px; font-weight:bold; padding:7px; margin-left:5px;' 
					onclick=\"getMoreTopsPage('".$myid."','".$limit."','".$kind."','".$cp."','".$subject."','".(int)trim($instance['tct-top-transcribers-settings-individuals']['tct-top-transcribers-showteams'])."'); return false;\">GO</button>
					</br><p style='color:red; display:none;' id='pageWarning_".$subject."'>Please enter a number</p></div>";

	if($showttmenu > 0){
		echo $menu."</ul>\n";
	}
	//Create loading wheel
	echo '<div id="top-transcribers-spinner" class="spinner" style="float:right; display:none;"></div>';
	echo "</div>\n"; // #ttnav_...
	}else{
		echo "<p>".$instance['tct-top-transcribers-nothingtoshow']."</p>";
		
	}
	//Show own rank below ranking
	/*
	if($subject != "teams"){
		if($kind === "campaign"){
			$query = "SELECT prg.userid,prg.campaignid,
						(
						SELECT GROUP_CONCAT(tShort) AS shortname 
						FROM ".$wpdb->prefix."teams t 
						JOIN ".$wpdb->prefix."user_teams ut 
						ON t.team_id = ut.team_id WHERE ut.user_id = prg.userid
						) AS teams,
						SUM(prg.amount)AS useramnt,
						usr.display_name,
						(SELECT
							(SELECT COUNT(*) FROM ".$wpdb->prefix."campaign_enrichements
							  WHERE (e_type='keywords' OR e_type='language-tag' OR e_type = 'theatre-tag' OR e_type = 'additional-source'  OR e_type='overall-category' OR e_type='document-tag') AND userid = prg.userid)
							+ (SELECT COUNT(DISTINCT docid) FROM ".$wpdb->prefix."campaign_enrichements yt WHERE yt.e_type = 'item-description' AND yt.e_note is not null AND yt.teamid=prg.teamid)
						) AS enrichements, 
						FLOOR(
							(CASE 
								WHEN SUM(prg.amount) >=11000 THEN (20 + (((SUM(prg.amount)-10000)/1000)*7))
								WHEN SUM(prg.amount) >=10000 THEN 20
								WHEN SUM(prg.amount) >=7500 THEN 15
								WHEN SUM(prg.amount) >=5000 THEN 10
								WHEN SUM(prg.amount) >=1000 THEN 5
								WHEN SUM(prg.amount) >=500 THEN 3 
								WHEN SUM(prg.amount) >=200 THEN 1 
								ELSE 0
							 END)
						) AS c_miles, 
						FLOOR((SELECT enrichements)/20) AS e_miles, 
						FLOOR(
							(SELECT COUNT(*) FROM ".$wpdb->prefix."campaign_enrichements cenr WHERE cenr.e_type='location' AND cenr.e_action ='new' AND cenr.teamid=prg.teamid)/10
						) AS l_miles, 
						(FLOOR(
							(CASE 
								WHEN SUM(prg.amount) >=11000 THEN (20 + (((SUM(prg.amount)-10000)/1000)*7))
								WHEN SUM(prg.amount) >=10000 THEN 20
								WHEN SUM(prg.amount) >=7500 THEN 15
								WHEN SUM(prg.amount) >=5000 THEN 10
								WHEN SUM(prg.amount) >=1000 THEN 5
								WHEN SUM(prg.amount) >=500 THEN 3 
								WHEN SUM(prg.amount) >=200 THEN 1 
								ELSE 0
							 END)
						)) + (SELECT e_miles) + (SELECT l_miles) AS umiles, 
						(
						SELECT SUM(ttl.amount) 
						FROM ".$wpdb->prefix."user_transcriptionprogress ttl 
						WHERE ttl.userid=prg.userid
						) AS totalchars,
						(
						SELECT COUNT(*) 
						FROM CSMcH_campaign_enrichements cenr 
						WHERE cenr.e_type='location' AND cenr.e_action ='new' AND cenr.userid=prg.userid
						) AS locations
						FROM ".$wpdb->prefix."campaign_transcriptionprogress prg 
						LEFT JOIN CSMcH_users usr 
						ON usr.ID=prg.userid 
						WHERE prg.campaignid='".$cp."'
						GROUP BY prg.userid 
						ORDER BY umiles DESC, useramnt DESC";
			$topusrs = $wpdb->get_results($query,ARRAY_A);
		}
		else {
			$query = "SELECT prg.userid,
						(
						SELECT GROUP_CONCAT(tShort) AS shortname 
						FROM ".$wpdb->prefix."teams t 
						JOIN ".$wpdb->prefix."user_teams ut 
						ON t.team_id = ut.team_id WHERE ut.user_id = prg.userid
						) AS teams,SUM(prg.amount)AS useramnt,
						usr.display_name,
						(
						SELECT(SUM(mm.miles_account)+SUM(mm.miles_chars)+SUM(mm.miles_review)+SUM(mm.miles_complete)+SUM(mm.miles_sharing)+SUM(mm.miles_message)+SUM(miles_locations)+SUM(miles_enrichements))  
						FROM ".$wpdb->prefix."user_miles mm 
						WHERE mm.userid=prg.userid
						) AS umiles 
						FROM ".$wpdb->prefix."user_transcriptionprogress prg 
						JOIN ".$wpdb->prefix."users usr 
						ON usr.ID=prg.userid 
						GROUP BY prg.userid 
						ORDER BY useramnt DESC";
			$topusrs = $wpdb->get_results($query,ARRAY_A);
		}
		$i = 1;
		foreach($topusrs as $usr){
			if($usr['userid'] == get_current_user_id()){
				echo "<h4>Your rank:</h4>";
				echo "<ul class=\"topusers\">\n";
				$aut = get_user_by('ID',$usr['userid']);
				um_fetch_user( $usr['userid']);
				echo "<li class=\"userRank\">";
				echo "<div class=\"tct-user-banner ".um_user('role')."\">".ucfirst(um_user('role'))."</div>\n"; 
				$acs = $wpdb->get_results("SELECT ac.*,uc.campaign_title,CASE ac.placing WHEN '1' then uc.campaign_badge_1 WHEN '2' then uc.campaign_badge_2 WHEN '3' then uc.campaign_badge_3 END AS badge FROM ".$wpdb->prefix."user_achievments ac LEFT JOIN ".$wpdb->prefix."user_campaigns uc ON uc.id=ac.campaign WHERE ac.userid='".$usr['userid']."'",ARRAY_A);
				if(sizeof($acs)>0){
					echo "<div class=\"tct_tt-achievments\">\n"; 
					foreach($acs as $ac){
						echo "<div title=\"".$ac['campaign_title']."\"class=\"".$ac['badge']."\"></div>\n";
					}
					echo "</div>\n";
				}
				if($kind === "campaign"){
					if(isset($instance['tct-top-transcribers-settings-individuals']['tct-top-transcribers-showteams']) && trim($instance['tct-top-transcribers-settings-individuals']['tct-top-transcribers-showteams']) == "1" && trim($usr['teams']) != ""){$temm = " (".str_replace(",",", ",$usr['teams']).")";}else{ $temm = "";}
					$miles = "<span class=\"milage\">".sprintf( esc_html( _n( '%s Mile', '%s Miles', (int)$usr['umiles'], 'transcribathon'  ) ), number_format_i18n((int)$usr['umiles']))."</span>\n";
					$charsc = "<span class=\"chars\"><strong>".sprintf( esc_html( _n( '%s Character in this campaign', '%s Characters in this campaign', (int)$usr['useramnt'], 'transcribathon'  ) ), number_format_i18n((int)$usr['useramnt']))."</strong></span>\n";
					$chars = "<span class=\"chars\">".sprintf( esc_html( _n( '%s Character', '%s Characters', (int)$usr['totalchars'], 'transcribathon'  ) ), number_format_i18n((int)$usr['totalchars']))."</span>\n";
					echo "<span class=\"rang\">".$i."</span><h2><a href=\"/".ICL_LANGUAGE_CODE."/user/".$aut->user_nicename."/\">".um_user('display_name')."</a><span class=\"teammem\">".$temm."</span></h2><p>".$charsc."</p><p><p>".$miles." | ".$chars."</p></p></li>\n";
				}else{
					if(isset($instance['tct-top-transcribers-settings-individuals']['tct-top-transcribers-showteams']) && trim($instance['tct-top-transcribers-settings-individuals']['tct-top-transcribers-showteams']) == "1" && trim($usr['teams']) != ""){$temm = " (".str_replace(",",", ",$usr['teams']).")";}else{ $temm = "";}
					$miles = "<span class=\"milage\">".sprintf( esc_html( _n( '%s Mile', '%s Miles', (int)$usr['umiles'], 'transcribathon'  ) ), number_format_i18n((int)$usr['umiles']))."</span>\n";
					$chars = "<span class=\"chars\">".sprintf( esc_html( _n( '%s Character', '%s Characters', (int)$usr['useramnt'], 'transcribathon'  ) ), number_format_i18n((int)$usr['useramnt']))."</span>\n";
					echo "<span class=\"rang\">".$i."</span><h2><a href=\"/".ICL_LANGUAGE_CODE."/user/".$aut->user_nicename."/\">".um_user('display_name')."</a><span class=\"teammem\">".$temm."</span></h2><p>".$miles." | ".$chars."</p></li>\n";
				}
				echo "</ul>\n";
				break;
			}
			$i++;
		}
	}
	*/
	echo $content;
?>