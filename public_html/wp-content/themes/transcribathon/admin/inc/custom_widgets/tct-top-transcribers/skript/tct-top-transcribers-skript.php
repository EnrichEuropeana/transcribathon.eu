<?php 
include(str_repeat("../",(sizeof(explode("/",substr((string)getcwd(),strrpos((string)getcwd(),"/wp-content"),strlen((string)getcwd()))))-2))."../wp-load.php");
global $wpdb,$mysql_con;


//{'q':'gtttrs','myid':myid,'base':base,'limit':limit}, function(res) 

if(isset($_POST['q']) && $_POST['q'] === "gtttrs"):
	$content = "";
	$nav = "";
	if(!isset($_POST['base']) || trim($_POST['base']) == ""){$base=0;}else{$base = (int)$_POST['base'];}
	if(!isset($_POST['limit']) || trim($_POST['limit']) == ""){$limit=0;}else{$limit = (int)$_POST['limit'];}
	if(!isset($_POST['subject']) || trim($_POST['subject']) == ""){ $subject = 'individuals'; }else{ $subject = stripslashes(trim($_POST['subject'])); }
	if(!isset($_POST['shortnames']) || trim($_POST['shortnames']) == "" || trim($_POST['shortnames']) != "1"){ $showshortnames = 0; }else{ $showshortnames = 1; }

	if(isset($_POST['kind']) && trim($_POST['kind']) == "campaign"){
		if(isset($_POST['cp']) && trim($_POST['cp']) != ""){
			$kind = "campaign";
			$cp = (int)trim($_POST['cp']);
		}else{
			$kind = "all";
			$cp = "";
		}
	}else{
		$kind = "all";
		$cp = "";
	}
	$myid = $_POST['myid'];


		if($kind === "campaign"){
			/*
			if($subject === "teams"){  // team
				$alltops = $wpdb->get_results("SELECT COUNT(DISTINCT prg.teamid) AS total FROM ".$wpdb->prefix."campaign_transcriptionprogress prg JOIN ".$wpdb->prefix."teams t ON t.team_id=prg.teamid WHERE prg.campaignid='".$cp."'",ARRAY_A );
				if((int)$alltops[0]['total'] <= $base){
					$base = (floor(((int)$alltops[0]['total']-1) / $limit)) * $limit;
				}
				
				
				//$query = "SELECT prg.teamid,SUM(prg.amount) AS teamamnt,tm.post_title,tm.post_type,tm.post_name,(SELECT COUNT(DISTINCT utm.user_id) FROM ".$wpdb->prefix."user_teams utm WHERE utm.team_id=prg.teamid) AS members, ROUND(( SUM(prg.amount)/ (SELECT COUNT(DISTINCT membs.userid) FROM ".$wpdb->prefix."campaign_transcriptionprogress membs WHERE membs.teamid=prg.teamid)),2) AS relamount,(SELECT COUNT(*) FROM ".$wpdb->prefix."campaign_enrichements cenr WHERE cenr.e_type='location' AND cenr.e_action ='new' AND cenr.teamid=prg.teamid) AS locations,((SELECT((SELECT COUNT(*) FROM ".$wpdb->prefix."campaign_enrichements WHERE (e_type='keywords' OR e_type='language-tag' OR e_type = 'theatre-tag' OR e_type = 'additional-source'  OR e_type='overall-category' OR e_type='document-tag') AND teamid = prg.teamid) ))+(select count(DISTINCT docid) FROM ".$wpdb->prefix."campaign_enrichements yt WHERE yt.e_type = 'item-description' AND CHAR_LENGTH(yt.e_note) > 0 AND yt.teamid=prg.teamid)) AS enrichements FROM ".$wpdb->prefix."campaign_transcriptionprogress prg LEFT JOIN ".$wpdb->prefix."posts tm ON tm.ID = prg.teamid WHERE prg.campaignid='".$cp."' GROUP BY prg.teamid ORDER BY relamount DESC LIMIT ".$base.",".$limit;
			
				$query = "SELECT prg.teamid,SUM(prg.amount) AS teamamnt, tm.team_title as post_title, 'teams' as post_type, LOWER(REPLACE((SELECT post_title), ' ', '-')) as post_name, 
						(SELECT COUNT(DISTINCT utm.user_id) FROM ".$wpdb->prefix."user_teams utm WHERE utm.team_id=prg.teamid) AS members, 
						(SUM(prg.amount)/(SELECT members)) AS relamount, 
						(SELECT COUNT(*) FROM ".$wpdb->prefix."campaign_enrichements cenr WHERE cenr.e_type='location' AND cenr.e_action ='new' AND cenr.teamid=prg.teamid) AS locations, 
						(SELECT
							(SELECT COUNT(*) FROM ".$wpdb->prefix."campaign_enrichements
							  WHERE (e_type='keywords' OR e_type='language-tag' OR e_type = 'theatre-tag' OR e_type = 'additional-source'  OR e_type='overall-category' OR e_type='document-tag') AND teamid = prg.teamid)
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
						)) + (SELECT e_miles) + (SELECT l_miles) AS totalmiles, 
						((FLOOR(
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
						)) + (SELECT e_miles) + (SELECT l_miles)) / (SELECT members) AS relmiles 
						FROM ".$wpdb->prefix."campaign_transcriptionprogress prg 
							JOIN ".$wpdb->prefix."teams tm
							ON tm.team_id = prg.teamid 
						WHERE prg.campaignid=".$cp."
						GROUP BY prg.teamid 
						ORDER BY relmiles DESC 
						LIMIT ".$base.",".$limit;
			
				
				
				$topusrs = $wpdb->get_results($query,ARRAY_A);
			}else{ // Invdl
				$alltops = $wpdb->get_results("SELECT COUNT(DISTINCT prg.userid) AS total FROM ".$wpdb->prefix."campaign_transcriptionprogress prg JOIN ".$wpdb->prefix."users usr ON usr.ID=prg.userid WHERE prg.campaignid='".$cp."'",ARRAY_A );
				if((int)$alltops[0]['total'] <= $base){
					$base = (floor(((int)$alltops[0]['total']-1) / $limit)) * $limit;
				}
				$query = "SELECT prg.userid,prg.campaignid,
						(
						SELECT GROUP_CONCAT(tShort) AS shortname 
						FROM ".$wpdb->prefix."teams t 
						JOIN ".$wpdb->prefix."user_teams ut 
						ON t.team_id = ut.team_id WHERE ut.user_id = prg.userid
						) AS teams,
						SUM(prg.amount)AS useramnt,
						usr.display_name,
						(
						SELECT(SUM(mm.miles_account)+SUM(mm.miles_chars)+SUM(mm.miles_review)+SUM(mm.miles_complete)+SUM(mm.miles_sharing)+SUM(mm.miles_message)+SUM(miles_locations)+SUM(miles_enrichements))  
						FROM ".$wpdb->prefix."user_miles mm 
						WHERE mm.userid=prg.userid
						) AS umiles, 
						(
						SELECT SUM(ttl.amount) 
						FROM ".$wpdb->prefix."user_transcriptionprogress ttl 
						WHERE ttl.userid=prg.userid
						) AS totalchars,
						(
						SELECT COUNT(*) 
						FROM CSMcH_campaign_enrichements cenr 
						WHERE cenr.e_type='location' AND cenr.e_action ='new' AND cenr.userid=prg.userid
						) AS locations´
						FROM ".$wpdb->prefix."campaign_transcriptionprogress prg 
						LEFT JOIN CSMcH_users usr 
						ON usr.ID=prg.userid 
						WHERE prg.campaignid='".$cp."' 
						GROUP BY prg.userid 
						ORDER BY useramnt
						DESC
						LIMIT ".$base.",".$limit;
				//$query = "SELECT prg.userid,prg.campaignid,SUM(prg.amount)AS useramnt,usr.display_name,(SELECT(SUM(mm.miles_account)+SUM(mm.miles_chars)+SUM(mm.miles_review)+SUM(mm.miles_complete)+SUM(mm.miles_sharing)+SUM(mm.miles_message))  FROM ".$wpdb->prefix."user_miles mm WHERE mm.userid=prg.userid) AS umiles, (SELECT SUM(ttl.amount) FROM ".$wpdb->prefix."user_transcriptionprogress ttl WHERE ttl.userid=prg.userid) AS totalchars FROM ".$wpdb->prefix."campaign_transcriptionprogress prg LEFT JOIN CSMcH_users usr ON usr.ID=prg.userid WHERE prg.campaignid='".$cp."' GROUP BY prg.userid ORDER BY SUM(prg.amount) DESC LIMIT ".$base.",".$limit;
				$topusrs = $wpdb->get_results($query,ARRAY_A);
			}
			*/
			 
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
				/*
				$alltops = $wpdb->get_results("SELECT COUNT(DISTINCT prg.teamid) AS total FROM ".$wpdb->prefix."team_transcriptionprogress prg JOIN ".$wpdb->prefix."teams t ON t.team_id=prg.teamid",ARRAY_A );
				if((int)$alltops[0]['total'] <= $base){
					$base = (floor(((int)$alltops[0]['total']-1) / $limit)) * $limit;
				}
				$query = "SELECT prg.teamid,SUM(prg.amount) AS teamamnt, tm.team_title as post_title, 'teams' as post_type, LOWER(REPLACE((SELECT post_title), ' ', '-')) as post_name,
						(SELECT COUNT(DISTINCT utm.user_id) 
						FROM ".$wpdb->prefix."user_teams utm 
						WHERE utm.team_id=prg.teamid) AS members, 
						ROUND(( SUM(prg.amount)/(SELECT members)),2) AS relamount 
					FROM ".$wpdb->prefix."team_transcriptionprogress prg 
						JOIN ".$wpdb->prefix."teams tm ON tm.team_id = prg.teamid 
					GROUP BY prg.teamid 
					ORDER BY relamount DESC 
					LIMIT ".$base.",".$limit;
				$topusrs = $wpdb->get_results($query,ARRAY_A);
				*/
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

		if(sizeof($topusrs)>0){
			$content .= "<ul class=\"topusers\">\n";
			
			if($subject === "teams"){
				/*
				$i=((int)$base+1);	
				foreach($topusrs as $team){
					$content .= "<li class=\"p".$i."\">";
					$content .= "<div class=\"tct-user-banner\"></div>\n"; 
					if($kind === "campaign"){
						
						
						$miles = "<span class=\"milage\">".sprintf( esc_html( _n( '%s mile per member', '%s miles per member', (float)$team['relmiles'], 'transcribathon'  ) ), number_format_i18n($team['relmiles'],2))."</span>\n";
						$miles2 = "<span class=\"chars\">".sprintf( esc_html( _n( '%s mile in this campaign', '%s total miles in this campaign', (int)$team['totalmiles'], 'transcribathon'  ) ), number_format_i18n((int)$team['totalmiles']))."</span>\n";
						$chars = "<span class=\"chars\">".sprintf( esc_html( _n( '%s character', '%s characters', (int)$team['teamamnt'], 'transcribathon'  ) ), number_format_i18n((int)$team['teamamnt']))."</span>\n";
						$locs = "<span class=\"chars\">".sprintf( esc_html( _n( '%s location', '%s locations', (int)$team['locations'], 'transcribathon'  ) ), number_format_i18n((int)$team['locations']))."</span>\n";
						$enrs = "<span class=\"chars\">".sprintf( esc_html( _n( '%s enrichment', '%s enrichments', (int)$team['enrichements'], 'transcribathon'  ) ), number_format_i18n((int)$team['enrichements']))."</span>\n";
						$content .=  "<span class=\"rang\">".$i."</span><h2><a href=\"/".$team['post_type']."/".$team['post_name']."\">".$team['post_title']."</a></h2><p>".$miles." | ".$miles2." <br /><span class=\"chars\">"._x('Achievements in this campaign','top-list','transcribathon').":</span><br />".$chars." | ".$locs." | ".$enrs."</p></li>\n";
						
						
					}else{
						$miles = "<span class=\"milage\">".sprintf( esc_html( _n( '%s Character per member', '%s Characters per member', (float)$team['relamount'], 'transcribathon'  ) ), number_format_i18n((float)$team['relamount']))."</span>\n";
						$chars = "<span class=\"chars\">".sprintf( esc_html( _n( '%s Character in total', '%s Characters in total', (int)$team['teamamnt'], 'transcribathon'  ) ), number_format_i18n((int)$team['teamamnt']))."</span>\n";
						$content .= "<span class=\"rang\">".$i."</span><h2><a href=\"/".$team['post_type']."/".$team['post_name']."\">".$team['post_title']."</a></h2><p>".$miles." | ".$chars."</p></li>\n";
					}
					$i++;
				}
				*/
			}else{
				$i=((int)$base+1);	
				foreach($topusrs as $usr){
					$aut = get_user_by('ID',$usr['UserId']);
					// if($usr['UserId'] != 3341) {
					um_fetch_user( $usr['UserId']);
					$content .= "<li class=\"p".(int)$i."\" style=\"background: #eeeeee; border-top: 8px solid #0c7da7; border-radius: 4px;\">";
					// $content .= "<div class=\"tct-user-banner ".um_user('role')."\">".ucfirst(um_user('role'))."</div>\n"; 

					if($kind === "campaign"){
						// $miles = "<span class=\"chars\">".sprintf( esc_html( _n( '%s mile in this campaign', '%s total miles in this campaign', (int)$usr['Miles'], 'transcribathon'  ) ), number_format_i18n((int)$usr['Miles']))."</span>\n";
						// $chars = "<span class=\"chars\">".sprintf( esc_html( _n( '%s character', '%s characters', (int)$usr['TranscriptionCharacters'], 'transcribathon'  ) ), number_format_i18n((int)$usr['TranscriptionCharacters']))."</span>\n";
						// $locs = "<span class=\"chars\">".sprintf( esc_html( _n( '%s location', '%s locations', (int)$usr['Locations'], 'transcribathon'  ) ), number_format_i18n((int)$usr['Locations']))."</span>\n";
						// $enrs = "<span class=\"chars\">".sprintf( esc_html( _n( '%s enrichment', '%s enrichments', (int)$usr['Enrichments'], 'transcribathon'  ) ), number_format_i18n((int)$usr['Enrichments']))."</span>\n";
						// echo "<span class=\"rang\">".$i."</span><h2><a target=\"_blank\" href=\"".network_home_url()."profile/".$aut->user_nicename."/\">".um_user('display_name')."</a><span class=\"teammem\">".$temm."</span></h2><p>".$miles." | ".$chars."</p><br />".$chars." | ".$locs." | ".$enrs."</p></li>\n";
						
						if($showshortnames > 0 && trim($usr['teams']) != ""){$temm = " (".str_replace(",",", ",$usr['teams']).")";}else{ $temm = "";}
						$miles = "<span class=\"milage\">".sprintf( esc_html( _n( '%s Mile in this campaign', '%s Miles in this campaign', (int)$usr['Miles'], 'transcribathon'  ) ), number_format_i18n((int)$usr['Miles']))."</span>\n";
						$charsc = "<span class=\"chars\"><strong>".sprintf( esc_html( _n( '%s Character in this campaign', '%s Characters in this campaign', (int)$usr['TranscriptionCharacters'], 'transcribathon'  ) ), number_format_i18n((int)$usr['TranscriptionCharacters']))."</strong></span>\n";
						$chars = "<span class=\"chars\">".sprintf( esc_html( _n( '%s Enrichment', '%s Enrichments', (int)$usr['Enrichments'], 'transcribathon'  ) ), number_format_i18n((int)$usr['Enrichments']))."</span>\n";
						$content .= "<span class=\"rang\" style=\"background: #ffffff; border-radius: 7px; color: #0c7da7;\">".$i."</span><h2><a style=\"color: #0c7da7 !important;\" href=\"".network_home_url()."profile/".$aut->user_nicename."/\">".um_user('display_name')."</a><span class=\"teammem\">".$temm."</span></h2><p>".$miles."</p><p><p>".$charsc." | ".$chars."</p></p></li>\n";
						
					}else{
						if($showshortnames > 0 && trim($usr['teams']) != ""){$temm = " (".str_replace(",",", ",$usr['teams']).")";}else{ $temm = "";}
						$miles = "<span class=\"milage\">".sprintf( esc_html( _n( '%s Mile', '%s Miles', (int)$usr['Miles'], 'transcribathon'  ) ), number_format_i18n((int)$usr['Miles']))."</span>\n";
						$chars = "<span class=\"chars\">".sprintf( esc_html( _n( '%s Character', '%s Characters', (int)$usr['TranscriptionCharacters'], 'transcribathon'  ) ), number_format_i18n((int)$usr['TranscriptionCharacters']))."</span>\n";
						if ($aut != null) {
							$content .= "<span class=\"rang\" style=\"background: #ffffff; border-radius: 7px; color: #0c7da7;\">".$i."</span><h2><a style=\"color: #0c7da7 !important;\" href=\"".network_home_url()."profile/".$aut->user_nicename."/\">".um_user('display_name')."</a><span class=\"teammem\">".$temm."</span></h2><p>".$miles." | ".$chars."</p></li>\n";
						}
						else {
							$content .= "<span class=\"rang\" style=\"background: #ffffff; border-radius: 7px; color: #0c7da7;\">".$i."</span><h2><a style=\"color: #0c7da7 !important;\" href=\"Placeholder User\">Placeholder User</a><span class=\"teammem\">".$temm."</span></h2><p>".$miles." | ".$chars."</p></li>\n";
						}
					}
					$i++;
				// }
			}
			}
			$content .= "</ul>\n";
		}

	$showttmenu = 0;

	$menu = "<ul>\n";
	if($base>0){
		if(((int)$base-(int)$limit)<0){$newb = 0; }else{ $newb=((int)$base-(int)$limit); }
		$menu .= "<li class=\"ttprev\"><a href=\"\" onclick=\"getMoreTops('".$myid."','".$newb."','".$limit."','".$kind."','".$cp."','".$subject."','".$showshortnames."'); return false;\">"._x('Previous', 'Tutorial-Slider-Widget (frontentd)','transcribathon')."</a></li>\n";
		$showttmenu++;
	}
	if((int)$alltops > ($base+$limit)){
		$menu .= "<li class=\"ttnext\"><a href=\"\" onclick=\"getMoreTops('".$myid."','".((int)$base+(int)$limit)."','".$limit."','".$kind."','".$cp."','".$subject."','".$showshortnames."'); return false;\">"._x('Next', 'Top-Transcribers-Slider-Widget (frontentd)','transcribathon')."</a></li>\n";
		$showttmenu++;
	}
	$menu .= "<div style='text-align:center'><label>Go to Page <input type='text' name='page_input' id='page_input_".$subject."' style='width:40px'></label>
				<button id='goto' style='font-size:16px; font-weight:bold; padding:7px; margin-left:5px;' 
					onclick=\"getMoreTopsPage('".$myid."','".$limit."','".$kind."','".$cp."','".$subject."','".$showshortnames."'); return false;\">GO</button>
					</br><p style='color:red; display:none;' id='pageWarning_".$subject."'>Please enter a number</p></div>";
	
	if($showttmenu > 0){
		$nav .= $menu."</ul>\n".'<div id="top-transcribers-spinner" class="spinner" style="float:right; display:none;"></div>';
	}

	//Rueckgabe 
	$res = array();
	$res['stat'] = 'ok';
	$res['content'] = $content;
	$res['ttnav'] = $nav;
	header("Content-Type: text/json; charset=utf-8");
	echo trim(json_encode($res));
endif;	



if(isset($_POST['q']) && $_POST['q'] === "gtttmtrs"):
	$content = "";
	$nav = "";
	if(!isset($_POST['base']) || trim($_POST['base']) == ""){$base=0;}else{$base = (int)$_POST['base'];}
	if(!isset($_POST['limit']) || trim($_POST['limit']) == ""){$limit=0;}else{$limit = (int)$_POST['limit'];}
	$myid = $_POST['myid'];

	
	// Set request parameters for image data
	$url = TP_API_HOST."/tp-api/rankings/userCount";
	$requestType = "GET";

	// Execude http request
	include TCT_THEME_DIR_PATH."admin/inc/custom_scripts/send_api_request.php";

	// Save image data
	$alltops = json_decode($result, true);

	$query = "SELECT usr.display_name,ut.*,(SELECT SUM(tp.amount) FROM ".$wpdb->prefix."user_transcriptionprogress tp WHERE tp.userid=ut.user_id) AS useramnt,(SELECT(SUM(mm.miles_account)+SUM(mm.miles_chars)+SUM(mm.miles_review)+SUM(mm.miles_complete)+SUM(mm.miles_sharing)+SUM(mm.miles_message))  FROM ".$wpdb->prefix."user_miles mm WHERE mm.userid=usr.ID) AS umiles FROM ".$wpdb->prefix."user_teams ut LEFT JOIN ".$wpdb->prefix."users usr on usr.ID=ut.user_id WHERE ut.team_id='".$_POST['tid']."' ORDER BY useramnt DESC LIMIT ".$base.",".$limit;
	$topusrs = $wpdb->get_results($query,ARRAY_A);
		if(sizeof($topusrs)>0){
			$content .= "<ul class=\"topusers\">\n";
			$i=((int)$base+1);	
			foreach($topusrs as $usr){
				$aut = get_user_by('ID',$usr['UserId']);
				um_fetch_user( $usr['UserId']);
				$content .= "<li class=\"p".$i."\">";
				// $content .= "<div class=\"tct-user-banner ".um_user('role')."\">".ucfirst(um_user('role'))."</div>\n"; 
				$acs = $wpdb->get_results("SELECT ac.*,uc.campaign_title,CASE ac.placing WHEN '1' then uc.campaign_badge_1 WHEN '2' then uc.campaign_badge_2 WHEN '3' then uc.campaign_badge_3 END AS badge FROM ".$wpdb->prefix."user_achievments ac LEFT JOIN ".$wpdb->prefix."user_campaigns uc ON uc.id=ac.campaign WHERE ac.userid='".$usr['userid']."'",ARRAY_A);
				if(sizeof($acs)>0){
					$content .= "<div class=\"tct_tt-achievments\">\n"; 
					foreach($acs as $ac){
						$content .= "<div title=\"".$ac['campaign_title']."\"class=\"".$ac['badge']."\"></div>\n";
					}
					$content .= "</div>\n";
				}
				$miles = "<span class=\"milage\">".sprintf( esc_html( _n( '%s Mile', '%s Miles', (int)$usr['Miles'], 'transcribathon'  ) ), number_format_i18n((int)$usr['Miles']))."</span>\n";
				$chars = "<span class=\"chars\">".sprintf( esc_html( _n( '%s Character', '%s Characters', (int)$usr['TranscriptionCharacters'], 'transcribathon'  ) ), number_format_i18n((int)$usr['TranscriptionCharacters']))."</span>\n";
				if ($aut != null) {
					$content .= "<span class=\"rang\">".$i."</span><h2><a href=\"/user/".$aut->user_nicename."/\">".um_user('display_name')."</a><span class=\"teammem\">".$temm."</span></h2><p>".$miles." | ".$chars."</p></li>\n";
				}
				else {
					$content .= "<span class=\"rang\">".$i."</span><h2><a href=\"Placeholder User\">Placeholder User</a><span class=\"teammem\">".$temm."</span></h2><p>".$miles." | ".$chars."</p></li>\n";
				}
				$i++;
			}
		$content .= "</ul>\n";
	}else{
		// No members - no top-list!
	}
	$showttmenu = 0;

	$menu = "<ul>\n";
	if($base>0){
		if(((int)$base-(int)$limit)<0){$newb = 0; }else{ $newb=((int)$base-(int)$limit); }
		$menu .= "<li class=\"ttprev\" style='float:left'><a href=\"\" onclick=\"getMoreTeamTops('".$myid."','".$newb."','".$limit."','".$_POST['tid']."'); return false;\">"._x('Previous', 'Tutorial-Slider-Widget (frontentd)','transcribathon')."</a></li>\n";
		$showttmenu++;
	}
	if((int)$alltops > ($base+$limit)){
		$menu .= "<li class=\"ttnext\" style='float:right'><a href=\"\" onclick=\"getMoreTeamTops('".$myid."','".((int)$base+(int)$limit)."','".$limit."','".$_POST['tid']."'); return false;\">"._x('Next', 'Top-Transcribers-Slider-Widget (frontentd)','transcribathon')."</a></li>\n";
		$showttmenu++;
	}
	$menu .= "<div style='text-align:center'><label>Go to Page <input type='text' name='page_input' id='page_input_".$subject."' style='width:40px'></label>
				<button id='goto' style='font-size:16px; font-weight:bold; padding:7px; margin-left:5px;' 
					onclick=\"getMoreTopsPage('".$myid."','".$limit."','".$kind."','".$cp."','".$subject."','".$showshortnames."'); return false;\">GO</button>
					</br><p style='color:red; display:none;' id='pageWarning_".$subject."'>Please enter a number</p></div>";
	
	if($showttmenu > 0){
		$nav .= $menu."</ul>\n".'<div id="top-transcribers-spinner" class="spinner" style="float:right; display:none;"></div>';
	}

	//Rueckgabe 
	$res = array();
	$res['stat'] = 'ok';
	$res['content'] = $content;
	$res['ttnav'] = $nav;
	header("Content-Type: text/json; charset=utf-8");
	echo trim(json_encode($res));
endif;	



?>