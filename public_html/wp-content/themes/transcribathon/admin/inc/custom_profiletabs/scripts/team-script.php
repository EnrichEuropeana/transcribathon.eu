<?php include(str_repeat("../",(sizeof(explode("/",substr((string)getcwd(),strrpos((string)getcwd(),"/wp-content"),strlen((string)getcwd()))))-2))."../wp-load.php");

global $wpdb,$mysql_con;

function getMyTeams($uid='',$cuid=''){
	global $wpdb;
	$content = "";
	$url = TP_API_HOST."/tp-api/teams?WP_UserId=".get_current_user_id();
	$requestType = "GET";

	// Execude http request
	include dirname(__FILE__)."/../../custom_scripts/send_api_request.php";

	// Save image data
	$myteams = json_decode($result, true);
	if(sizeof($myteams)>0){
		// Team-Liste member
		$content .= "<table class=\"team-tab is-member\">\n";
			$content .= "<tbody>\n";
			foreach($myteams as $tm){
				$content .= "<tr>\n";
				$content .= "<td class=\"tct_tmname\"><i class='far fa-users'></i>".$tm['Name']."</td>\n";
				if($uid === $cuid){
					if($tm['user_role'] == "owner"){
						$add_edit = "<li><a title=\""._x('Edit team / Invite users','Team-Tab on Profile','transcribathon')."\" href=\"\" onclick=\"openOverlay('edit-tm-".$tm['ID']."-".$uid."-".$cuid."','".ICL_LANGUAGE_CODE."'); return false;\" class=\"ed-tm\">"._x('Edit team / Invite users','Team-Tab on Profile','transcribathon')."</a></li>\n";
					}else{
						$add_edit = "";
					}
					$content .= "<td class=\"tct_tmtools\"><ul class=\"tls\">\n".$add_edit."<li><a title=\""._x('Go to team page','Team-Tab on Profile','transcribathon')."\" href=\"".$tm['Name']."/\" class=\"vw-tm\"><i class='far fa-eye'></i>"._x('Go to team page','Team-Tab on Profile','transcribathon')."</a></li>\n<li><a title=\""._x('Leave this team','Team-Tab on Profile','transcribathon')."\" href=\"\" onclick=\"exitTm('".$uid."','".$uid."','".$tm['ID']."','"._x("Are you sure, you want to leave this team?\\nIf so, please click ‘OK’.",'Team-Tab on Profile','transcribathon')."'); return false;\" class=\"ext-tm\"><i class='fas fa-sign-in'></i>"._x('Leave this team','Team-Tab on Profile','transcribathon')."</a></li>\n</ul>\n</td>\n";
				}else{
					$content .= "<td class=\"tct_tmtools\"><ul class=\"tls\">\n<li><a title=\""._x('Go to team page','Team-Tab on Profile','transcribathon')."\" href=\"".$tm['Name']."/\" class=\"vw-tm\"><i class='far fa-eye'></i>"._x('Go to team page','Team-Tab on Profile','transcribathon')."</a></li>\n</ul>\n</td>\n";
				}
				$content .= "</tr>\n";
			}
			$content .= "</tbody>\n";
		$content .= "</table>\n";
	}else{
		$content .= "<p>"._x('You are not yet a member of any team', 'Team-Tab on Profile', 'transcribathon'  )."</p>\n";
	}
	return $content;
}


function getMyTeamsAsString($uid=''){
	global $wpdb;
	$content = "";
	$query = "SELECT tms.*,pm1.meta_value AS campaign,ut.join_date,ut.user_role FROM ".$wpdb->prefix."posts tms LEFT JOIN ".$wpdb->prefix."user_teams ut ON ut.team_id=tms.ID LEFT JOIN ".$wpdb->prefix."postmeta pm1 ON pm1.post_id=tms.id WHERE tms.post_type='teams' AND ut.user_id='".$uid."' AND pm1.meta_key='tct_team_campaign' ORDER BY tms.post_title ASC";
	$myteams = $wpdb->get_results($query,ARRAY_A);
	if(sizeof($myteams)>0){
		// Team-Liste member
			$t= "";
			foreach($myteams as $tm){
				$content .= $t."<span class=\"tct_tmname\">".$tm['post_title']."</span>\n";
				$t = ", ";
			}
	}else{
		$content .= "no";
	}
	return $content;
}



function getOpenTeams($uid='',$cuid=''){
	global $wpdb;
	$content = "";
	$query = "SELECT tm.* FROM ".$wpdb->prefix."posts tm LEFT JOIN ".$wpdb->prefix."postmeta pm1 ON pm1.post_id=tm.ID LEFT JOIN ".$wpdb->prefix."user_teams ut ON ut.team_id=tm.ID WHERE tm.post_type='teams' AND ((pm1.meta_value IS NULL AND pm1.meta_key='tct_team_code') || pm1.meta_value='' AND pm1.meta_key='tct_team_code') AND tm.ID NOT IN (SELECT team_id FROM ".$wpdb->prefix."user_teams WHERE user_id='".$uid."')";
	//$query = "SELECT tm.* FROM ".$wpdb->prefix."posts tm LEFT JOIN ".$wpdb->prefix."postmeta pm1 ON pm1.post_id=tm.ID LEFT JOIN ".$wpdb->prefix."user_teams ut ON ut.team_id=tm.ID WHERE tm.post_type='teams' AND ((pm1.meta_value IS NULL AND pm1.meta_key='tct_team_code') || pm1.meta_value='' AND pm1.meta_key='tct_team_code')";
	$oteams = $wpdb->get_results($query,ARRAY_A);
	if(sizeof($oteams)>0){
		// Team-Liste member
		$content .= "<table class=\"team-tab\">\n";
			$content .= "<tbody>\n";
			foreach($oteams as $tm){
				$content .= "<tr>\n";
				$content .= "<td class=\"tct_tmname\"><i></i>".$tm['post_title']."</td>\n";
				$content .= "<td class=\"tct_tmtools\"><ul class=\"tls\">\n<li><a title=\""._x('Go to team page','Team-Tab on Profile','transcribathon')."\" href=\"/".ICL_LANGUAGE_CODE."/".$tm['post_name']."/\" class=\"vw-tm\">"._x('Go to team page','Team-Tab on Profile','transcribathon')."</a></li>\n<li><a title=\""._x('Join this team','Team-Tab on Profile','transcribathon')."\" href=\"\" onclick=\"joinTeam('".$uid."','".$cuid."','".$tm['ID']."'); return false;\" class=\"jn-tm\">"._x('Join this team','Team-Tab on Profile','transcribathon')."</a></li>\n</ul>\n</td>\n";
				$content .= "</tr>\n";
			}
			$content .= "</tbody>\n";
		$content .= "</table>\n";
	}else{
		$content .= "<p>"._x('There are no open teams you could join in the moment', 'Team-Tab on Profile', 'transcribathon'  )."</p>\n";
	}
	return $content;
}



function getMyCampaigns($uid=''){
	global $wpdb;
	$content = "";
	$worked_on = array();
	// All campaigns the user actually worked for
	$query1 = "SELECT DISTINCT cpr.campaignid FROM ".$wpdb->prefix."campaign_transcriptionprogress cpr LEFT JOIN ".$wpdb->prefix."user_campaigns uc ON uc.id=cpr.campaignid WHERE cpr.userid='".$uid."'";
	$easycampaigns = $wpdb->get_results($query1,ARRAY_A);
	foreach($easycampaigns as $cp){
		if(!in_array($cp['campaignid'],$worked_on)){
			array_push($worked_on,$cp['campaignid']);
		}
	}
	$query = "SELECT cps.* FROM ".$wpdb->prefix."user_campaigns cps WHERE id IN ('".implode("','",$worked_on)."') ";
	$mycampaigns = $wpdb->get_results($query,ARRAY_A);

	if(sizeof($mycampaigns)>0){
		// Campaign-Liste member
		$content .= "<table class=\"campaign-tab\">\n";
			$content .= "<tbody>\n";
			foreach($mycampaigns as $cp){
				$content .= "<tr>\n";
				$content .= "<td class=\"tct_cpname\"><i></i>".$cp['campaign_title']."</td>\n";
				if(trim($cp['campaign_page_id']) != ""){
					$content .= "<td class=\"tct_cptools\"><ul class=\"tls\">\n<li><a title=\""._x('Go to run page','Team-Tab on Profile','transcribathon')."\" href=\"/".ICL_LANGUAGE_CODE."/?p=".$cp['campaign_page_id']."/\" class=\"vw-cp\">"._x('Go to run page','Team-Tab on Profile','transcribathon')."</a></li>\n</ul>\n</td>\n";
				}else{
					$content .= "<td class=\"tct_cptools\"><ul class=\"tls\">\n<li>&nbsp;</li>\n</ul>\n</td>\n";
				}
				$content .= "</tr>\n";
			}
			$content .= "</tbody>\n";
		$content .= "</table>\n";
	}else{
		$content .= "<p>"._x('You have not yet participated in any run', 'Team-Tab on Profile', 'transcribathon'  )."</p>\n";
	}
	return $content;
}


function getChallenges($tid=''){
	global $wpdb;
	$content = "";
	$c = get_post_custom((int)$tid);
	$worked_on = explode(',',$c['tct_team_campaign'][0]);
	$query = "SELECT cps.*,(SELECT COUNT(prg.id) FROM ".$wpdb->prefix."campaign_transcriptionprogress prg WHERE prg.campaignid=cps.id AND teamid='".(int)$tid."') AS worked FROM ".$wpdb->prefix."user_campaigns cps WHERE cps.id IN ('".implode("','",$worked_on)."') ";
	$mycampaigns = $wpdb->get_results($query,ARRAY_A);

	if(sizeof($mycampaigns)>0){
		// Campaign-Liste member
		$content .= "<table class=\"campaign-tab\">\n";
			$content .= "<tbody>\n";
			foreach($mycampaigns as $cp){
				$content .= "<tr>\n";
				$content .= "<td class=\"tct_cpname\"><i></i>".$cp['campaign_title']."</td>\n";
				$content .= "<td class=\"tct_cptools\"><ul class=\"tls\">\n";
				if(trim($cp['campaign_page_id']) != ""){
					$content .= "<li><a title=\""._x('Go to run page','Team-Tab on Profile','transcribathon')."\" href=\"/".ICL_LANGUAGE_CODE."/?p=".$cp['campaign_page_id']."/\" class=\"vw-cp\">"._x('Go to run page','Team-Tab on Profile','transcribathon')."</a></li>\n";
				}
				if((int)$cp['worked'] < 1){
					$content .= "<li><a title=\""._x('Leave run','Team-Tab on Profile','transcribathon')."\" href=\"\" onclick=\"leaveCampaign('".$cp['id']."','".(int)$tid."','"._x("Are you sure, you want this team \\nnot to be assigned to this run?\\nIf so, please click ‘OK’.",'Team-Tab on Profile','transcribathon')."'); return false;\" class=\"ext-cp\">"._x('Leave run','Team-Tab on Profile','transcribathon')."</a></li>\n";
				}
				$content .= "</ul>\n</td>\n";
				$content .= "</tr>\n";
			}
			$content .= "</tbody>\n";
		$content .= "</table>\n<p>&nbsp;</p>";
	}else{
		$content .= "<p><em>"._x('This team is not yet assigned to any challenges/runs', 'Team-Tab on Profile', 'transcribathon'  )."</em></p>\n";
	}
	return $content;
}


// Get my teams as string
if(isset($_POST['q']) && $_POST['q'] === "gt-my-string-teams"):
	$list = getMyTeamsAsString((int)$_POST['uid']);
	if($list != "no"){$list = "<strong>Teams: </strong>".$list;}
	//Rueckgabe
	$res = array();
	$res['status'] = 'ok';
	$res['list'] = $list;
	header("Content-Type: text/json; charset=utf-8");
	echo trim(json_encode($res));
endif;


// Get all assigned campaigns
if(isset($_POST['q']) && $_POST['q'] === "gt-tm-challenges"):
	$campaignlist = getChallenges((int)$_POST['tid']);
	//Rueckgabe
	$res = array();
	$res['status'] = 'ok';
	$res['campaignlist'] = $campaignlist;
	header("Content-Type: text/json; charset=utf-8");
	echo trim(json_encode($res));
endif;


// Join an open team
if(isset($_POST['q']) && $_POST['q'] === "join-team"):
	$success = 0;
	if(isset($_POST['pid']) && trim($_POST['pid']) != "" && isset($_POST['cuid']) && trim($_POST['cuid']) != "" && trim($_POST['cuid']) === trim($_POST['pid']) && isset($_POST['tid']) && trim($_POST['tid']) != "" ){
		$query = "SELECT mytm.ID AS newteamid,mytm.post_title AS teamtitle FROM ".$wpdb->prefix."posts mytm WHERE mytm.ID='".(int)$_POST['tid']."' AND mytm.post_type='teams'";
		$ex = $wpdb->get_results($query,ARRAY_A);
		$wpdb->query("INSERT INTO ".$wpdb->prefix."user_teams set team_id='".(int)$_POST['tid']."',user_id='".(int)$_POST['cuid']."',user_role='participant',join_date='".date('Y-m-d H:i:s')."'");
		$message = "<div class=\"message success\"><p>".sprintf(_x('Welcome! you are now member of the team ‘%s’','Team-Tab on Profile','transcribathon'),$ex[0]['teamtitle'])."</p></div>\n";
		$teamlist = getMyTeams($_POST['pid'],$_POST['cuid']);
		$openteams = getOpenTeams($_POST['pid'],$_POST['cuid']);
		$success++;
	}else{
		$message = "<div class=\"message error\"><p>"._x('It appears that you are working in someone else´s profile', 'Team-Tab on Profile', 'transcribathon'  )."</p></div>\n";
		$success = 0;
		$teamlist = "no";
		$openteams = "no";
	}
	//Rueckgabe
	$res = array();
	$res['status'] = 'ok';
	$res['message'] =$message;
	$res['success'] =$success;
	$res['teamlist'] = $teamlist;
	$res['openteams'] = $openteams;
	header("Content-Type: text/json; charset=utf-8");
	echo trim(json_encode($res));
endif;

// Create a code
if(isset($_POST['q']) && $_POST['q'] === "get-code"):
	$pw = tct_generatePassword(12,3,2);
	//Rueckgabe
	$res = array();
	$res['status'] = 'ok';
	$res['content'] =$pw;
	header("Content-Type: text/json; charset=utf-8");
	echo trim(json_encode($res));
endif;


// Create new Team
if(isset($_POST['q']) && $_POST['q'] === "crt-nw-tm"):
	$success = 0;
	if(isset($_POST['pid']) && trim($_POST['pid']) != "" && isset($_POST['cuid']) && trim($_POST['cuid']) != "" && trim($_POST['cuid']) === trim($_POST['pid'])){

		$meta = array();
		if(isset($_POST['ccd']) && trim($_POST['ccd']) != ""){
			$ex = $wpdb->get_results("SELECT id,(SELECT CASE WHEN ende >= NOW() THEN 'ok' ELSE 'no' END) AS ok FROM ".$wpdb->prefix."user_campaigns WHERE campaign_code='".esc_html(trim(stripslashes($_POST['ccd'])))."' AND campaign_type='team_time'",ARRAY_A);
			if(isset($ex[0]['ok']) && $ex[0]['ok'] == "ok"){
				$meta['tct_team_campaign'] = $ex[0]['id'];
			}else{
				$meta['tct_team_campaign'] = NULL;
			}
		}else{
			$meta['tct_team_campaign'] = NULL;
		}
		if(isset($_POST['tcd']) && trim($_POST['tcd']) != ""){
			$meta['tct_team_code'] = esc_html(trim(stripslashes($_POST['tcd'])));
		}else{
			$meta['tct_team_code'] = NULL;
		}
		if(isset($_POST['qtshtl']) && trim($_POST['qtshtl']) != ""){
			$meta['tct_team_shortname'] = esc_html(trim(stripslashes($_POST['qtshtl'])));
		}else{
			$meta['tct_team_shortname'] = NULL;
		}

		$my_team = array(
			  'post_title'    => wp_strip_all_tags(esc_html(trim(stripslashes($_POST['qttl'])))),
			  'post_content'  => esc_html(stripslashes($_POST['tdes'])),
			  'post_status'   => 'publish',
			  'comment_status' => 'open',
			  'post_type' => 'teams',
			  'post_author'   => get_current_user_id(),
			  'meta_input' => $meta,
		);


		$url = TP_API_HOST."/tp-api/teams?UserId=".$_POST['cuid'];
		$requestType = "POST";
		$requestData = array(
			'Name' => wp_strip_all_tags(esc_html(trim(stripslashes($_POST['qttl'])))),
			'ShortName' => esc_html(trim(stripslashes($_POST['qtshtl']))),
			'Code' => esc_html(trim(stripslashes($_POST['tcd']))),
			'Description' => esc_html(trim(stripslashes($_POST['tdes'])))
		);

		// Execude http request
		include dirname(__FILE__)."/../../custom_scripts/send_api_request.php";

		$message = "<p class=\"message success\">"._x('you successfully created a new team', 'Team-Tab on Profile', 'transcribathon'  )."</p>\n";
		$success = 1;

		$teamlist = getMyTeams($_POST['pid'],$_POST['cuid']);
		$campaignlist = getMyCampaigns($_POST['pid']);

	}else{
		$message = "<p class=\"message error\">"._x('It appears that you are working in someone else´s profile', 'Team-Tab on Profile', 'transcribathon'  )."</p>\n";
		$success = 0;
		$teamlist = "no";
		$campaignlist = "no";
	}
	//Rueckgabe
	$res = array();
	$res['status'] = 'ok';
	$res['message'] =$message;
	$res['success'] =$success;
	$res['teamlist'] = $teamlist;
	$res['campaignlist'] = $campaignlist;
	header("Content-Type: text/json; charset=utf-8");
	echo trim(json_encode($res));
endif;


// Delete Team
if(isset($_POST['q']) && $_POST['q'] === "rem-tmfg"):
	$message = "no";
	if(isset($_POST['pid']) && trim($_POST['pid']) != "" && isset($_POST['cuid']) && trim($_POST['cuid']) != "" && trim($_POST['cuid']) === trim($_POST['pid'])){
		wp_delete_post((int)trim($_POST['tid']), true);
		$wpdb->query("DELETE FROM ".$wpdb->prefix."user_teams WHERE team_id='".(int)trim($_POST['tid'])."'");
		$teamlist = getMyTeams($_POST['pid'],$_POST['cuid']);
		$campaignlist = getMyCampaigns($_POST['pid']);
	}else{
		$message = _x('Sorry: could not delete team. Please try again later', 'Team-Tab on Profile', 'transcribathon'  );
	}
	//Rueckgabe
	$res = array();
	$res['status'] = 'ok';
	$res['message'] =$message;
	$res['teamlist'] = $teamlist;
	$res['campaignlist'] = $campaignlist;
	header("Content-Type: text/json; charset=utf-8");
	echo trim(json_encode($res));
endif;


// Leave Campaign
if(isset($_POST['q']) && $_POST['q'] === "leav-tm-challenge"):
	$c = get_post_custom((int)$_POST['tid']);
	$mycmps = explode(',',$c['tct_team_campaign'][0]);
	$rest = array();
	foreach($mycmps as $cp){
		if($cp != (int)$_POST['cpid']){
			array_push($rest,$cp);
		}
	}
	$rest = array_filter($rest);
	update_post_meta((int)$_POST['tid'], 'tct_team_campaign',implode(',',$rest));

	$campaignlist = getChallenges((int)$_POST['tid']);
	//Rueckgabe
	$res = array();
	$res['status'] = 'ok';
	$res['allright'] = $allright;
	$res['message'] = $message;
	$res['campaignlist'] = $campaignlist;
	header("Content-Type: text/json; charset=utf-8");
	echo trim(json_encode($res));
endif;

// Check campaign-code and join if possible (on team administration per owner)
if(isset($_POST['q']) && $_POST['q'] === "chknjn-cpgn"):
	$allright = "no";
	$message = "no";
	if(isset($_POST['pid']) && trim($_POST['pid']) != "" && (int)$_POST['pid'] > 0){
		$query = "SELECT mcp.id,mcp.campaign_title,(SELECT CASE WHEN ende >= NOW() THEN 'ok' ELSE 'no' END) AS date_ok,(SELECT CASE WHEN campaign_type != 'team_time' THEN 'ok' ELSE (SELECT COUNT(pm1.post_id) FROM ".$wpdb->prefix."postmeta pm1 WHERE pm1.meta_key='tct_team_campaign' AND find_in_set(mcp.id,pm1.meta_value) AND pm1.post_id IN (SELECT ut.team_id FROM ".$wpdb->prefix."user_teams ut WHERE ut.user_id IN(SELECT mut.user_id FROM ".$wpdb->prefix."user_teams mut WHERE mut.team_id='".(int)$_POST['tid']."'))) END) AS not_allowed FROM ".$wpdb->prefix."user_campaigns mcp WHERE mcp.campaign_code='".esc_html(trim(stripslashes($_POST['cd'])))."' AND mcp.campaign_type='team_time'";
		$ex = $wpdb->get_results($query,ARRAY_A);

		if(isset($ex[0]['date_ok']) && $ex[0]['date_ok'] == "ok"){
			// is the user allready in a competeing campaign?
			if(isset($ex[0]['not_allowed']) && (int)$ex[0]['not_allowed'] >0){
				$allright = 'no';
				$message = "<div class=\"message error\"><p>".sprintf(_x("This team can not participate in this run (‘%s’), because one of the team members already participates as a member of another team. <em>(and so he/her would be competeing against him/herself)</em>", 'Team-Tab on Profile', 'transcribathon' ),$ex[0]['campaign_title'])."</p></div>";
			}else{
				$c = get_post_custom((int)$_POST['tid']);
				$mycmps = explode(',',$c['tct_team_campaign'][0]);
				if(!in_array((int)$ex[0]['id'],$mycmps)){
					$allright = 'ok';
					array_push($mycmps,(int)$ex[0]['id']);
					$mycmps = array_filter($mycmps);
					update_post_meta((int)$_POST['tid'], 'tct_team_campaign',implode(',',$mycmps));
					$message = "<div class=\"message success\"><p>".sprintf(_x('Congratulations! This team now participates in the run ‘%s’ ', 'team-invitation-widget (backend)','transcribathon'),$ex[0]['campaign_title'])."</p></div>";
				}else{
					$message = "<div class=\"message error\"><p>"._x("This team is already participating in this run. No need to assign twice :)", 'Team-Tab on Profile', 'transcribathon' )."</p></div>";
				}
			}
		}else if(isset($ex[0]['date_ok']) && $ex[0]['date_ok'] == "no"){
			$allright = 'no';
			$message = "<div class=\"message error\"><p>"._x("The run you are trying to assign this team to\nis already finished - please enter \nanother run-code or leave it blank", 'Team-Tab on Profile', 'transcribathon' )."</p></div>";
		}else{
			$allright = 'no';
			$message = "<div class=\"message error\"><p>"._x("There is no run with this run-code to be found.\nPlease check.", 'Team-Tab on Profile', 'transcribathon' )."</p></div>";
		}
	}else{
		$allright = 'no';
		$message = "<div class=\"message error\"><p>"._x("Something went wrong - please try again.", 'Team-Tab on Profile', 'transcribathon' )."</p></div>";
	}
	$campaignlist = getChallenges((int)$_POST['tid']);
	//Rueckgabe
	$res = array();
	$res['status'] = 'ok';
	$res['allright'] = $allright;
	$res['message'] = $message;
	$res['campaignlist'] = $campaignlist;
	header("Content-Type: text/json; charset=utf-8");
	echo trim(json_encode($res));
endif;


// Check for code-protected team-campaign (on team -creation)
if(isset($_POST['q']) && $_POST['q'] === "check-tmcpgn-cd"):
	$allright = "no";
	$message = "no";
	if(isset($_POST['pid']) && trim($_POST['pid']) != "" && (int)$_POST['pid'] > 0){
		$ex = $wpdb->get_results("SELECT mcp.id,(SELECT CASE WHEN ende >= NOW() THEN 'ok' ELSE 'no' END) AS date_ok,(SELECT CASE WHEN campaign_type != 'team_time' THEN 'ok' ELSE (SELECT COUNT(pm1.post_id) FROM ".$wpdb->prefix."postmeta pm1 WHERE pm1.meta_key='tct_team_campaign' AND find_in_set(mcp.id,pm1.meta_value) AND pm1.post_id IN (SELECT ut.team_id FROM ".$wpdb->prefix."user_teams ut WHERE ut.user_id='".(int)$_POST['pid']."')) END) AS not_allowed FROM ".$wpdb->prefix."user_campaigns mcp WHERE mcp.campaign_code='".esc_html(trim(stripslashes($_POST['cd'])))."' AND mcp.campaign_type='team_time'",ARRAY_A);
		if(isset($ex[0]['date_ok']) && $ex[0]['date_ok'] == "ok"){
			// is the user allready in a competeing campaign?
			if(isset($ex[0]['not_allowed']) && (int)$ex[0]['not_allowed'] >0){
				$allright = 'no';
				$message = _x("Sorry, you can not assign this new team to this run, \nbecause you are already member of a team in this run \n(and this would make you your own competitor)  \n\nplease enter another run-code or leave it blank", 'Team-Tab on Profile', 'transcribathon' );
			}else{
				$allright = 'ok';
				$message = '';
			}
		}else if(isset($ex[0]['date_ok']) && $ex[0]['date_ok'] == "no"){
			$allright = 'no';
			$message = _x("The run you are trying to assign this team to\nis already finished - please enter \nanother run-code or leave it blank", 'Team-Tab on Profile', 'transcribathon' );
		}else{
			$allright = 'no';
			$message = _x("There is no run with this run-code to be found.\nPlease check.", 'Team-Tab on Profile', 'transcribathon' );
		}
	}else{
		$allright = 'no';
		$message = _x("Something went wrong - please try again.", 'Team-Tab on Profile', 'transcribathon' );
	}
	//Rueckgabe
	$res = array();
	$res['status'] = 'ok';
	$res['allright'] = $allright;
	$res['message'] = $message;
	header("Content-Type: text/json; charset=utf-8");
	echo trim(json_encode($res));
endif;


// Leave team
if(isset($_POST['q']) && $_POST['q'] === "pls-ex-it-tm"):
	$content = "";
	$success = "no";
	//{'q':'pls-ex-it-tm','pid':pID,'cuID':cuID,'tID':tid}
	if(is_user_logged_in() &&  isset($_POST['pid']) && trim($_POST['pid']) == get_current_user_id() && isset($_POST['cuid']) && trim($_POST['cuid']) == get_current_user_id() && isset($_POST['tid']) && trim($_POST['tid']) != ""){
		$query = "DELETE FROM ".$wpdb->prefix."user_teams WHERE team_id='".(int)trim(stripslashes($_POST['tid']))."' AND user_id='".get_current_user_id()."'";
		$wpdb->query($query);
		$success = "yes";
	}
	$query = "SELECT tms.*,pm1.meta_value AS campaign,ut.join_date,ut.user_role FROM ".$wpdb->prefix."posts tms LEFT JOIN ".$wpdb->prefix."user_teams ut ON ut.team_id=tms.ID LEFT JOIN ".$wpdb->prefix."postmeta pm1 ON pm1.post_id=tms.id WHERE tms.post_type='teams' AND ut.user_id='".get_current_user_id()."' AND pm1.meta_key='tct_team_campaign' ORDER BY tms.post_title ASC";
	$myteams = $wpdb->get_results($query,ARRAY_A);

	$refresh_caps = getMyCampaigns(get_current_user_id());
	$content .= getMyTeams($_POST['pid'],$_POST['cuid']);
	$openteams = getOpenTeams($_POST['pid'],$_POST['cuid']);
	//Rueckgabe
	$res = array();
	$res['status'] = 'ok';
	$res['content'] = $content;
	$res['success'] = $success;
	$res['refresh_caps'] = $refresh_caps;
	$res['openteams'] = $openteams;
	header("Content-Type: text/json; charset=utf-8");
	echo trim(json_encode($res));
endif;


// Check Code and join if possible
if(isset($_POST['q']) && $_POST['q'] === "chk-tm-cd"):
	$content = "";
	$message = "";
	$success = "no";
	$refresh = "no";
	$refresh_caps = "no";
	if(is_user_logged_in() &&  isset($_POST['pid']) && trim($_POST['pid']) == get_current_user_id() && isset($_POST['cuid']) && trim($_POST['cuid']) == get_current_user_id() && isset($_POST['tidc']) && trim($_POST['tidc']) != ""){

		$url = TP_API_HOST."/tp-api/teams?Code=".$_POST['tidc'];
		$requestType = "GET";

		// Execude http request
		include dirname(__FILE__)."/../../custom_scripts/send_api_request.php";

		$ex = json_decode($result, true);

		if(sizeof($ex)>0 && isset($ex[0]['TeamId']) && $ex[0]['TeamId'] != ""){
			$alreadyMember = false;
			foreach ($ex[0]['Users'] as $user) {
				if ($user == get_current_user_id()) {
					$alreadyMember = true;
				}
			}
			if ($alreadyMember == false){
				//if($ex[0]['exclusive'] != "yes"){
					$add = 0;
					/*
					if(trim($ex[0]['newcampaigns']) == ""){
						$add = 1;
					}else{
						$gefahr = array_intersect(array_unique(explode(",",$ex[0]['mycampaigns'])),array_unique(explode(",",$ex[0]['newcampaigns'])));
						$query = "SELECT COUNT(id) FROM ".$wpdb->prefix."user_campaigns WHERE id IN ('".implode("','",$gefahr)."') AND campaign_type = 'team_time'";
						$avoid = $wpdb->get_results($query,ARRAY_N);
						$avoid = array_column($avoid,0);
						if((int)$avoid[0] <1 ){
							$add = 1;
						}else{
							$message = "<div class=\"message error\">"._x('Sorry, you are already member of a competeing team.','Team-Tab on Profile','transcribathon')."</div>\n";
						}
					}*/
					$add = 1;
					if($add>0){
						$url = TP_API_HOST."/tp-api/teamUsers";
						$requestType = "POST";
						$requestData = array(
							'UserId' => $_POST['cuid'],
							'TeamId' => $ex[0]['TeamId']
						);
						// Execude http request
						include dirname(__FILE__)."/../../custom_scripts/send_api_request.php";

						$message = "<div class=\"message success\">"._x('Welcome! you are now member of the team ‘'.$ex[0]['Name'].'’','Team-Tab on Profile','transcribathon')."</div>\n";

						$refresh = getMyTeams($_POST['pid'],$_POST['cuid']);
						$refresh_caps = getMyCampaigns(get_current_user_id());
					}
				//}else{
					// Exclusive
				//	$message = "<div class=\"message error\">"._x('Sorry, you can not join ‘'.$ex[0]['teamtitle'].'’ &ndash; you need an administrator to make you a member.','Team-Tab on Profile','transcribathon')."</div>\n";
				//}
			}else{
				// Already a team-member of this team
				$message = "<div class=\"message error\">"._x('Ups! You are already a member of the team ‘'.$ex[0]['teamtitle'].'’ - no need for a second membership :)','Team-Tab on Profile','transcribathon')."</div>\n";
			}
		}else{
			// Wrong Code
			$message = "<div class=\"message error\">"._x('Sorry, the code you entered seems to be wrong.','Team-Tab on Profile','transcribathon')."</div>\n";
		}
		$success = "yes";
	}else{
		// Not logged in OR logged in as different user OR missing Team-Code
		$message = "<div class=\"message error\">"._x('Sorry, it seems this did not work. Please check your code and try again or contact team@transcribathon.eu','Team-Tab on Profile','transcribathon')."</div>\n";
		$success = "yes";
	}
	//$content .= $debug;
	$content .= $message;
	$content .= "<label for=\"tct-mem-code\">"._x('Code', 'Team-Tab on Profile', 'transcribathon'  ).":</label>\n<input type=\"password\" placeholder=\""._x('enter team-code', 'Team-Tab on Profile', 'transcribathon'  )."\" id=\"tct-mem-code\" value=\"\" autocomplete=\"off\" />";
	$content .=  "<a class=\"tct-vio-but\" onclick=\"chkTmCd('".trim($_POST['pid'])."','".get_current_user_id()."'); return false;\">"._x('Join','Team-Tab on Profile','transcribathon')."</a>";
	//Rueckgabe
	$res = array();
	$res['status'] = 'ok';
	$res['content'] = $content;
	$res['success'] = $success;
	$res['refresh'] = $refresh;
	$res['refresh_caps'] = $refresh_caps;
	header("Content-Type: text/json; charset=utf-8");
	echo trim(json_encode($res));
endif;


// Accept team-Invitation
if(isset($_POST['q']) && $_POST['q'] === "acpt-ti"):
	$message = "";
	if(isset($_POST['tid']) && (int)$_POST['tid'] > 0 && isset($_POST['cuid']) && (int)$_POST['cuid'] > 0 && isset($_POST['iuid']) && (int)$_POST['iuid'] > 0 && $_POST['cuid'] == get_current_user_id() && (int)$_POST['iuid'] == $_POST['cuid']){
		$invitation = $wpdb->get_results("SELECT ti.*,usr.display_name,tm.post_title AS teamtitle,sndr.display_name AS sender FROM ".$wpdb->prefix."team_invitations ti LEFT JOIN ".$wpdb->prefix."users usr on usr.ID=ti.userid  LEFT JOIN ".$wpdb->prefix."users sndr on sndr.ID=ti.senderid LEFT JOIN ".$wpdb->prefix."posts tm on tm.ID=ti.teamid WHERE ti.id='".(int)$_POST['tid']."' AND tm.post_type='teams'",ARRAY_A);
		if(sizeof($invitation)>0 && is_array($invitation[0])){
			if($invitation[0]['confirmed'] == ""){
				$success = 0;
				// Now check, if this user is allowed to join the team, he was invited to
				$query = "SELECT mytm.ID AS newteamid,(SELECT lck.meta_value FROM ".$wpdb->prefix."postmeta lck WHERE lck.post_id=mytm.ID AND lck.meta_key='tct_team_is_exclusive') AS exclusive, mytm.post_title AS teamtitle,(SELECT COUNT(id) FROM ".$wpdb->prefix."user_teams ut WHERE ut.team_id=mytm.ID AND ut.user_id='".get_current_user_id()."') AS already_member,(SELECT GROUP_CONCAT(pm1.meta_value) FROM ".$wpdb->prefix."postmeta pm1 WHERE pm1.meta_key='tct_team_campaign' AND pm1.post_id IN (SELECT otms.team_id FROM ".$wpdb->prefix."user_teams otms WHERE otms.user_id='".get_current_user_id()."')) AS mycampaigns,(SELECT pm2.meta_value FROM ".$wpdb->prefix."postmeta pm2 WHERE pm2.post_id=mytm.ID AND pm2.meta_key='tct_team_campaign') AS newcampaigns FROM ".$wpdb->prefix."posts mytm WHERE mytm.ID='".(int)$invitation[0]['teamid']."' AND mytm.post_type='teams'";

				$ex = $wpdb->get_results($query,ARRAY_A);
				if(sizeof($ex)>0 && isset($ex[0]['newteamid']) && $ex[0]['newteamid'] != ""){
					if($ex[0]['already_member'] < 1){
						if($ex[0]['exclusive'] != "yes"){
							$add = 0;
							if(trim($ex[0]['newcampaigns']) == ""){
								$add = 1;
							}else{
								$gefahr = array_intersect(array_unique(explode(",",$ex[0]['mycampaigns'])),array_unique(explode(",",$ex[0]['newcampaigns'])));
								$query = "SELECT COUNT(id) FROM ".$wpdb->prefix."user_campaigns WHERE id IN ('".implode("','",$gefahr)."') AND campaign_type = 'team_time'";
								$avoid = $wpdb->get_results($query,ARRAY_N);
								$avoid = array_column($avoid,0);
								if((int)$avoid[0] <1 ){
									$add = 1;
								}else{
									$message = "<div class=\"message error\">"._x('Sorry, you are already member of a competeing team - that means, that the team, you are trying to join, is participating an a challenge/run, in which you are already participating as a member of another team.','Team-Tab on Profile','transcribathon')."</div>\n";
								}
							}
							if($add>0){
								$message = "<div class=\"message success\"><p>".sprintf(_x('Welcome! you are now member of the team ‘%s’','Team-Tab on Profile','transcribathon'),$ex[0]['teamtitle'])."</p><p>".sprintf(_x('To check out the team, go to: %s','Team-Tab on Profile','transcribathon'),'<a href="?p='.$ex[0]['newteamid'].'">Team-page</a>')."</p></div>\n";
								$query = "INSERT INTO ".$wpdb->prefix."user_teams set team_id='".$ex[0]['newteamid']."', user_id='".get_current_user_id()."',user_role='participant',join_date='".date('Y-m-d H:i:s')."'";
								$wpdb->query($query);
								$refresh = getMyTeams($_POST['pid'],$_POST['cuid']);
								$refresh_caps = getMyCampaigns(get_current_user_id());
								$success = 1;
							}
						}else{
							// Exclusive
							$message = "<div class=\"message error\">"._x('Sorry, you can not join ‘'.$ex[0]['teamtitle'].'’ &ndash; you need an administrator to make you a member.','Team-Tab on Profile','transcribathon')."</div>\n";
						}
					}else{
						// Already a team-member of this team
						$message = "<div class=\"message error\">"._x('Ups! You are already a member of the team ‘'.$ex[0]['teamtitle'].'’ - no need for a second membership :)','Team-Tab on Profile','transcribathon')."</div>\n";
					}
				}else{
					// Wrong Code
					$message = "<div class=\"message error\">".sprintf(_x('It seems that this invitation is expired. Sorry! You could contact %s and ask him to invite you again.','Team-Tab on Profile','transcribathon'),$invitation[0]['sender'])."</div>\n";
				}
				// set invitation as confirmed
				$wpdb->query("UPDATE ".$wpdb->prefix."team_invitations set confirmed='".date('Y-m-d H:i:s')."',success='".$success."' WHERE id='".(int)$_POST['tid']."'");
			}else{
				$message .= "<p class=\"message error\">"._x('It seems, like you already have accepted this invitation. No need to accept it twice :)', 'team-invitation-widget (backend)','transcribathon')."</p>";
			}
		}else{
			$message .= "<p class=\"message error\">"._x('Somehow your invitation got lost on the way. Sorry, please reload the page and try again.', 'team-invitation-widget (backend)','transcribathon')."</p>";
		}
	}else{
		$message .= "<p class=\"message error\">"._x('Somehow your invitation got chewed up by our security-system. Sorry, please reload the page and try again.', 'team-invitation-widget (backend)','transcribathon')."</p>";
	}
	//Rueckgabe
	$res = array();
	$res['status'] = 'ok';
	$res['message'] = $message;
	header("Content-Type: text/json; charset=utf-8");
	echo trim(json_encode($res));
endif;


// Initial Data
if(isset($_POST['q']) && $_POST['q'] === "init-teamtab"):
	$teamlist = "";
	$campaignlist = "";
	$teamlist = getMyTeams($_POST['pid'],$_POST['cuid']);
	$campaignlist = getMyCampaigns($_POST['pid']);
	$openteams = getOpenTeams($_POST['pid'],$_POST['cuid']);
	//Rueckgabe
	$res = array();
	$res['status'] = 'ok';
	$res['teamlist'] = $teamlist;
	$res['campaignlist'] = $campaignlist;
	$res['openteams'] = $openteams;
	header("Content-Type: text/json; charset=utf-8");
	echo trim(json_encode($res));
endif;


// Invite Users
if(isset($_POST['q']) && $_POST['q'] === "inv-tmmembs"):
	$debug = "";
	if(isset($_POST['cuid']) && (int)$_POST['cuid'] > 0 && isset($_POST['pid']) && (int)$_POST['pid'] > 0 && $_POST['cuid'] == get_current_user_id() && (int)$_POST['pid'] == $_POST['cuid']){
		if(isset($_POST['usrs']) && is_array($_POST['usrs']) && sizeof($_POST['usrs'])>0){
			$abs =  $wpdb->get_results("SELECT us.display_name,us.user_email,umt.meta_value AS first_name,umt2.meta_value AS last_name FROM ".$wpdb->prefix."users us LEFT JOIN ".$wpdb->prefix."usermeta umt ON umt.user_id=us.ID LEFT JOIN ".$wpdb->prefix."usermeta umt2 ON umt2.user_id=us.ID WHERE us.ID = '".(int)$_POST['pid']."' AND umt.meta_key='first_name' AND umt2.meta_key='last_name'",ARRAY_A);
			$tm = $wpdb->get_results("SELECT post_title FROM ".$wpdb->prefix."posts WHERE post_type='teams' AND ID='".(int)$_POST['tid']."'",ARRAY_A);

			$reps = $wpdb->get_results("SELECT us.ID,us.display_name,us.user_email,umt.meta_value AS first_name FROM ".$wpdb->prefix."users us LEFT JOIN ".$wpdb->prefix."usermeta umt ON umt.user_id=us.ID WHERE us.ID IN ('".implode("','",$_POST['usrs'])."') AND umt.meta_key='first_name'",ARRAY_A);

			$invquery = "INSERT INTO ".$wpdb->prefix."team_invitations (`code`, `teamid`, `userid`, `senderid`, `senddate`) VALUES ";

			$tr = "\n";
			foreach($reps as $rp){
				$linkcode = date('HisYmd').TCT_INVITATION_PREFIX.uniqid(rand());

				$invquery .= $tr."('".$linkcode."',".(int)$_POST['tid'].",".$rp['ID'].",".(int)$_POST['pid'].",'".date('Y-m-d H:i:s')."')";
				$tr =",\n";

				$to = array($rp['user_email']); // !!!! ANPASSEN!
				$headers[] = 'Content-Type: text/html; charset=UTF-8';
				$headers[] = 'From: Transcribathon<team@transcribathon.eu>' . "\r\n";
				$subj = 'You received a team invitation on transcribathon.eu';
				$message = "<html><body style=\"background: #f2f2f2;-webkit-font-smoothing: antialiased;-moz-osx-font-smoothing: grayscale;\">
				<div style=\"max-width: 560px; text-align:center; padding: 0px; margin:40px auto 0px auto;font-family: Open Sans,Helvetica,Arial;font-size: 26px; color: #666;\"><img src=\"https://transcribathon.com/wp-content/themes/transcribathon/images/logo-transparent-mail.gif\" alt=\"transcribathon.eu\" /></div>
				<div style=\"max-width: 560px;padding: 15px;background: #ffffff; border-top-left-radius: 5px; border-top-right-radius: 5px; margin:20px auto 0px auto;font-family: Open Sans,Helvetica,Arial;font-size: 15px;color: #666;\">
				<div style=\"padding: 0 30px 30px 30px;\">
				<div style=\"padding:20px 20px 0px 20px; font-size:24px; \">
					Dear ".$rp['first_name'].", you got invited!
					</div>
					<div style=\"padding:20px;\">
					You are receiving this letter because <strong>".$abs[0]['first_name']." ".$abs[0]['last_name']."</strong> wants to invite you to join his team <a href=\"https://transcribathon.com/?p=".(int)$_POST['tid']."\" style=\"color: #7f3978; font-weight:600; text-decoration: none;\">".$tm[0]['post_title']."</a>.
					<br /><br />
					In order to accept this invitation, please click the link below:
					<br /><br />
					<center><a href=\"https://transcribathon.com/en/accept-team-invitation/?".$linkcode."\" style=\" display:inline-block; position:relative; margin-top:20px; margin-bottom:20px; background: #7f3978; color:#fff; font-weight:600; text-decoration: none; text-align:center; padding:10px 50px;\">Accept invitation</a></center>
					<br /><br />
					If you have troubles with the button above, you can also try to copy this link into the address-bar of your browser:<br /><em style=\"font-size:11px;\">https://transcribathon.com/en/accept-team-invitation/?".$linkcode."</em>
					<br />
					</div>
				</div>
				</div>
				<div style=\"max-width: 560px;padding: 20px; background: #E7E7E7; border-top:5px solid #293745; border-bottom-left-radius: 5px; border-bottom-right-radius: 5px; margin:0px auto 40px auto;font-family: Open Sans,Helvetica,Arial;font-size: 13px;color: #000;\">
				<div style=\"color: #000; padding: 20px 30px\">
					<div style=\"padding:0px 20px;\">
						<strong>Team Transcribathon</strong>
						<br />
						Europeana - Facts &amp; Files
						<br />
						<a href=\"mailto:team@transcribathon.eu\" style=\"color: #444; text-decoration: none;\">team@transcribathon.eu</a>
						<br /><br />
						<a href=\"http://www.transcribathon.eu\" style=\"color: #444; text-decoration: none;\">www.transcribathon.eu</a>
						<br />
						<a href=\"http://www.europeana1914-1918.eu\" style=\"color: #444; text-decoration: none;\">www.europeana1914-1918.eu</a>
						<br />
						<a href=\"https://www.facebook.com/europeana1914-1918\" style=\"color: #444; text-decoration: none;\">facebook.com/europeana1914-1918</a>
						<br />
						<a href=\"https://twitter.com/europeana1914\" style=\"color: #444; text-decoration: none;\">twitter.com/europeana1914</a>
						<br />
						<a href=\"http://www.factsandfiles.com\" style=\"color: #444; text-decoration: none;\">www.factsandfiles.com</a>
						<br />
					</div>
				</div>
				</div>
				</body>
				</html>";

				wp_mail( $to, $subj, $message, $headers);
			}
			$invquery .= ";";
			$wpdb->query($invquery);
		}
		$message = "<div class=\"message success\"><p>"._x('Successfully sent invitations!', 'team-invitation-widget (backend)','transcribathon')."</p></div>";

	}else{
		$message = "<div class=\"message error\"><p>"._x('Sorry - that did not work. Please try again.', 'team-invitation-widget (backend)','transcribathon')."</p></div>";
	}
	//Rueckgabe
	$res = array();
	$res['status'] = 'ok';
	$res['message'] = $message;
	header("Content-Type: text/json; charset=utf-8");
	echo trim(json_encode($res));
endif;


// Check, if team-name is already taken
if(isset($_POST['q']) && $_POST['q'] === "chk-teamname"):
	$usable = "no";
	$message = _x("Sorry - this team title is already in use. \nPlease choose another one.", 'team-invitation-widget (backend)','transcribathon');
	if(isset($_POST['myself']) && $_POST['myself'] != "new"){
		$ex = $wpdb->get_results("SELECT COUNT(ID) FROM ".$wpdb->prefix."posts WHERE post_type='teams' AND post_title='".esc_html(stripslashes(trim($_POST['title'])))."' AND ID !='".(int)$_POST['myself']."'",ARRAY_N);
	}else{
		$ex = $wpdb->get_results("SELECT COUNT(ID) FROM ".$wpdb->prefix."posts WHERE post_type='teams' AND post_title='".esc_html(stripslashes(trim($_POST['title'])))."'",ARRAY_N);
	}
	$ex = array_column($ex,0);
	if((int)$ex[0] < 1){
		$usable = "ok";
	}
	//Rueckgabe
	$res = array();
	$res['status'] = 'ok';
	$res['message'] = $message;
	$res['usable'] = $usable;
	header("Content-Type: text/json; charset=utf-8");
	echo trim(json_encode($res));
endif;


// Check, if team-name abbreviation is already taken
if(isset($_POST['q']) && $_POST['q'] === "chk-teamshortname"):
	$usable = "no";
	$message = _x("Sorry - this abbreviation is already in use. \nPlease choose another one.", 'team-invitation-widget (backend)','transcribathon');
	if(isset($_POST['myself']) && $_POST['myself'] != "new"){
		$ex = $wpdb->get_results("SELECT COUNT(tm.ID) FROM ".$wpdb->prefix."posts tm LEFT JOIN ".$wpdb->prefix."postmeta pm1 ON pm1.post_id=tm.ID WHERE tm.post_type='teams' AND pm1.meta_value='".esc_html(stripslashes(trim($_POST['title'])))."' AND pm1.meta_key='tct_team_shortname' AND tm.ID !='".(int)$_POST['myself']."'",ARRAY_N);
	}else{
		$ex = $wpdb->get_results("SELECT COUNT(tm.ID) FROM ".$wpdb->prefix."posts tm LEFT JOIN ".$wpdb->prefix."postmeta pm1 ON pm1.post_id=tm.ID WHERE tm.post_type='teams' AND pm1.meta_value='".esc_html(stripslashes(trim($_POST['title'])))."' AND pm1.meta_key='tct_team_shortname'",ARRAY_N);
	}
	$ex = array_column($ex,0);
	if((int)$ex[0] < 1){
		$usable = "ok";
	}
	//Rueckgabe
	$res = array();
	$res['status'] = 'ok';
	$res['message'] = $message;
	$res['usable'] = $usable;
	header("Content-Type: text/json; charset=utf-8");
	echo trim(json_encode($res));
endif;


// Save Team-Settings (from existing team)
if(isset($_POST['q']) && $_POST['q'] === "sv-tmmsts"):
	$debug = "";
	$title = "no";
	if(isset($_POST['tid']) && (int)$_POST['tid'] > 0 && isset($_POST['cuid']) && (int)$_POST['cuid'] > 0 && isset($_POST['pid']) && (int)$_POST['pid'] > 0 && $_POST['pid'] == get_current_user_id() && (int)$_POST['pid'] == $_POST['cuid']){
		$tm = $wpdb->get_results("SELECT post_title FROM ".$wpdb->prefix."posts WHERE post_type='teams' AND ID='".(int)$_POST['tid']."'",ARRAY_A);
		if(sizeof($tm)>0 && is_array($tm[0]) && $tm[0]['post_title'] != ""){
			$wpdb->query("UPDATE ".$wpdb->prefix."posts set post_title='".esc_html(stripslashes($_POST['title']))."',post_content='".esc_html(stripslashes($_POST['des']))."' WHERE id='".(int)$_POST['tid']."'");
			update_post_meta((int)$_POST['tid'], 'tct_team_shortname',esc_html(stripslashes($_POST['shtitle'])));
			$message = "<div class=\"message success\"><p>"._x('Team data has been updated successfully', 'team-invitation-widget (backend)','transcribathon')."</p></div>";
			$title = esc_html(stripslashes($_POST['title']));
		}else{
			// team does not exist
			$message = "<div class=\"message error\"><p>"._x('Sorry - it appears, that this team does not exist (anymore)', 'team-invitation-widget (backend)','transcribathon')."</p></div>";
		}
	}else{
		// Not enough or the wrong data
		$message = "<div class=\"message error\"><p>"._x('Sorry - that did not work. Please try again.', 'team-invitation-widget (backend)','transcribathon')."</p></div>";
	}
	//Rueckgabe
	$res = array();
	$res['status'] = 'ok';
	$res['message'] = $message;
	$res['title'] = $title;
	header("Content-Type: text/json; charset=utf-8");
	echo trim(json_encode($res));
endif;


?>
