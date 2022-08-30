<?php
/* 
Shortcode: teamsandruns_tab
Description: Creates the 'Teams & runs' tab of profile tab
*//* Team-Tab */

function _TCT_teamsandruns_tab( $atts ) {  
echo  "<style type='text/css'>

.tct_hd h1{text-transform:none; line-height:1.2; font-weight:500; letter-spacing:0.2; font-size:1.8rem !important; margin-bottom:0px !important; padding-bottom:0px !important;}
.tct_hd h3{text-transform:none; line-height:1.2; font-weight:300; letter-spacing:0.3; color:#333; font-size:1.5rem !important; margin-top:0px !important; padding-top:0px !important; margin-bottom:0px !important;}
.tct_hd h1+h3{margin-top:0px !important; padding-top:5px !important;}
.tct_hd{padding-bottom:20px !important;}

button.tct-vio-but[type=button],input.tct-vio-but[type=button],a.tct-vio-but{min-height:20px; border:none !important; font-size:0.9em; letter-spacing:0.5px;
	padding:5px 30px; font-weight:400; color:#fff !important; text-align:center; cursor:pointer;
	-ms-box-sizing: border-box; -moz-box-sizing: border-box; -webkit-box-sizing: border-box; box-sizing: border-box;
	-moz-border-radius:4px; -webkit-border-radius:4px; border-radius:4px;
	-webkit-transition: all 0.4s ease 0.1s; -moz-transition: all 0.4s ease 0.1s; -o-transition: all 0.4s ease 0.1s; transition: all 0.4s ease 0.1s;
	-webkit-text-shadow:none !important; -moz-text-shadow:none !important; text-shadow:none !important;
	margin-top:4px !important; display:inline-block !important;
}

</style>";
// build 'Teams & runs' tab of Profile page


$url = TP_API_HOST."/tp-api/teams?WP_UserId=".um_profile_id();
$requestType = "GET";

// Execude http request
include dirname(__FILE__)."/../custom_scripts/send_api_request.php";

// Save image data
$myteams = json_decode($result, true);

if(is_user_logged_in() &&  get_current_user_id() === um_profile_id()){
		// Only User
		
	echo "<div class=\"section group maingroup\">\n";
		echo "<div class=\"column span_1_of_3\">\n";	
				if(sizeof($myteams)>0){	
					echo "<div class=\"tct_hd\">\n";
					echo "<h1>"._x('Teams', 'Team-Tab on Profile', 'transcribathon'  )."</h1><h3>"._x('Your teams','Team-Tab on Profile','transcribathon')."</h3>\n";
					echo "</div>\n";
					// Team-Liste member
					echo "<div id=\"ismember_list\" >\n";
						echo "<p class=\"smallloading\"></p>\n";
					echo "</div>\n";
				}else{
					echo "<div class=\"tct_hd\">\n";
						echo "<h1>"._x('Teams', 'Team-Tab on Profile', 'transcribathon'  )."</h1><h3>"._x('Your teams','Team-Tab on Profile','transcribathon')."</h3>\n";
					echo "</div>\n";
					echo "<div id=\"ismember_list\" >";
						echo "<p>"._x('You are not yet a member of any team', 'Team-Tab on Profile', 'transcribathon'  )."</p>";
					echo "</div>\n";
                }	
		echo "</div>\n";
			echo "<div class=\"column span_1_of_3 alg_l\">\n";		
				echo "<div class=\"tct_hd\">\n";
				echo "<h1>"._x('Join a team', 'Team-Tab on Profile', 'transcribathon'  )."</h1><h3>"._x('Join a team by code','Team-Tab on Profile','transcribathon')."</h3>\n";
				echo "</div>\n";	
				echo "<p class=\"nopad\">"._x('If you received a code to join a team, please enter it here and click ‘join’', 'Team-Tab on Profile', 'transcribathon'  )."</p>\n";
				echo "<form id=\"tct-tmcd-frm\" autocomplete=\"off\">\n";
				echo "<label for=\"tct-mem-code\">"._x('Code', 'Team-Tab on Profile', 'transcribathon'  ).":</label>\n<input type=\"password\" placeholder=\""._x('enter team code', 'Team-Tab on Profile', 'transcribathon'  )."\" id=\"tct-mem-code\" value=\"\" autocomplete=\"off\" />";
				echo "<a class=\"theme-color-background tct-vio-but\" onclick=\"chkTmCd('".um_profile_id()."','".get_current_user_id()."'); return false;\">"._x('Join','Team-Tab on Profile','transcribathon')."</a>";
				echo "</form>\n";
		
				echo "<p>&nbsp;</p>\n";
		/*
				// Code-Anmeldung
				echo "<div class=\"tct_hd\">\n";
				echo "<h1>"._x('Open teams', 'Team-Tab on Profile', 'transcribathon'  )."</h1><h3>"._x('Join an open team','Team-Tab on Profile','transcribathon')."</h3>\n";
				echo "</div>\n";
				// Team-Liste open teams
				echo "<div id=\"openteams_messageholder\" ></div>\n";
				echo "<div id=\"openteams_list\" >\n";
					echo "<p class=\"smallloading\"></p>\n";
				echo "</div>\n";*/
		
				
				// TO COME
				
			echo "</div>\n";
			echo "<div class=\"column span_1_of_3\">\n";	
				// Create a team
				echo "<div class=\"tct_hd\">\n";
				echo "<h1>"._x('Create a team', 'Team-Tab on Profile', 'transcribathon'  )."</h1><h3>"._x('Open up a new team','Team-Tab on Profile','transcribathon')."</h3>\n";
				echo "</div>\n";
				echo "<p class=\"nopad\">"._x('Would you like to create a new team? Please click the button below and fill out the form.', 'Team-Tab on Profile', 'transcribathon'  )."</p>\n";
				echo "<div id=\"team-creation-feedback\"></div>\n";
				echo "<a class=\"theme-color-background tct-vio-but\" id=\"open-tm-crt-but\" data-rel-close=\"X\" data-rel-open=\""._x('Create a team','Team-Tab on Profile','transcribathon')."\" onclick=\"openTmCreator('".um_profile_id()."','".get_current_user_id()."',jQuery(this)); return false;\">"._x('Create a team','Team-Tab on Profile','transcribathon')."</a>";
				echo "<form id=\"tct-crtrtm-frm\" autocomplete=\"off\">\n";
					echo "<label for=\"qtmnm\">"._x('Team title', 'Team-Tab on Profile', 'transcribathon'  )."</label>\n";
					echo "<input type=\"text\" data-rel-missing=\""._x('Please enter a team title', 'Team-Tab on Profile', 'transcribathon'  )."\" id=\"qtmnm\" placeholder=\""._x('Please enter a title', 'Team-Tab on Profile', 'transcribathon'  )."\" onchange=\"chkTeamname(jQuery(this));\" value=\"\" autocomplete=\"off\" />\n";
					echo "<p>"._x('Please enter an abbreviated title (max. 6 chars). This might appear in some cases next of a member’s name', 'Team-Tab on Profile', 'transcribathon'  )."</p>";
					echo "<label for=\"qtmshnm\">"._x('Team title abbreviation', 'Team-Tab on Profile', 'transcribathon'  )."</label>\n";
					echo "<input type=\"text\" maxlength=\"6\" style=\"width:80px;\" data-rel-missing=\""._x('Please enter an abbreviation of your team title', 'Team-Tab on Profile', 'transcribathon'  )."\" id=\"qtmshnm\" placeholder=\""._x('Abbr.', 'Team-Tab on Profile', 'transcribathon'  )."\" onchange=\"checkAbbr(jQuery(this));\" value=\"\" autocomplete=\"off\" />\n";
					echo "<label for=\"qtsdes\">"._x('Short description', 'Team-Tab on Profile', 'transcribathon'  )."</label>\n";
					echo "<textarea id=\"qtsdes\" cols=\"1\" rows=\"1\" placeholder=\""._x('Enter a short description of this team (optional)', 'Team-Tab on Profile', 'transcribathon'  )."\"></textarea>\n";
					echo "<p>"._x('If you want to create a <strong>Private team</strong>, set an <strong>access code</strong> by clicking the refresh button next to the code field below. Other users can then only join the team once they know this code or when they are sent an e-mail invitation from the team administrator.<br /><br />To create an <strong>Open team</strong>, leave the code field blank.', 'Team-Tab on Profile', 'transcribathon'  )."</p>";
					echo "<table class=\"nopad\">\n<tbody>\n";
						echo "<tr>\n";
						echo "<td><input type=\"text\" id=\"qtmcd\" style=\"width:100%;\" placeholder=\""._x('the refresh button to generate an access code', 'Team-Tab on Profile', 'transcribathon'  )."\" value=\"\" readonly onchange=\"checkCode('qtmcd','"._x('Code too short. Please enter at least 8 characters','Team-Tab on Profile','transcribathon')."');\" /></td>\n";
						echo "<td style=\"width:50px;\"><a onclick=\"tct_generateCode('qtmcd'); return false;\" class=\"theme-color-background tct-vio-but codecreator-but\" id=\"qtmcd-but\">create/refresh code</a><p class=\"smallloading\" style=\"display:none;\" id=\"qtmcd-waiter\"></td>\n";
						echo "</tr>\n";		
					echo "</tbody>\n</table>\n";
				echo "<p>"._x('If you want to register this team for a Special Transcribathon event or run, enter the run code below. You can also do this later via ‘Edit team’.', 'Team-Tab on Profile', 'transcribathon'  )."</p>\n";
				echo "<label for=\"qcmpgncd\">"._x('Run code', 'Team-Tab on Profile', 'transcribathon'  )."</label>\n";
				echo "<input type=\"password\" id=\"qcmpgncd\" placeholder=\""._x('Run code', 'Team-Tab on Profile', 'transcribathon'  )."\" value=\"\" autocomplete=\"off\" />\n";
				echo "<a class=\"theme-color-background tct-vio-but\" id=\"svTmBut\" onclick=\"svTeam('".um_profile_id()."','".get_current_user_id()."'); return false;\">"._x('Create team','Team-Tab on Profile','transcribathon')."</a>";
				echo "</form>\n";
		
		
				echo "<p>&nbsp;</p>\n";
		/*
				// Runs
				echo "<div class=\"tct_hd\">\n";
				echo "<h1>"._x('Runs', 'Team-Tab on Profile', 'transcribathon'  )."</h1><h3>"._x('Your runs','Team-Tab on Profile','transcribathon')."</h3>\n";
				echo "<p>"._x('You participated actively in the following runs', 'Team-Tab on Profile', 'transcribathon'  )."</p>\n";
				echo "</div>\n";
				// Run-list
				echo "<div id=\"isparticipant_list\" >\n";
					echo "<p class=\"smallloading\"></p>\n";
				echo "</div>\n";
				*/
				echo "<script type=\"text/javascript\">getTeamTabContent('".um_profile_id()."','".get_current_user_id()."');</script>\n";
			echo "</div>\n";
		echo "</div>\n";

	}else{
	// Public
		
		echo "<div class=\"section group maingroup\">\n";
			echo "<div class=\"column span_1_of_3\">\n";	
				if(sizeof($myteams)>0){	
					if(substr(um_user('display_name'),strlen(um_user('display_name'))-1,1) == "s"){$sadd = "’";}else{$sadd = "’s";}
					echo "<div class=\"tct_hd\">\n";
					echo "<h1>"._x('Teams', 'Team-Tab on Profile', 'transcribathon'  )."</h1><h3>".sprintf( esc_html__('%s teams','transcribathon'),um_user('display_name').$sadd)."</h3>\n";
					echo "</div>\n";	
					
					// Team-Liste member
					echo "<div id=\"ismember_list\">\n";
						echo "<div id=\"ismember_list\" >\n";
							echo "<p class=\"smallloading\"></p>\n";
						echo "</div>\n";
					echo "</div>\n";

						
				}else{
					if(substr(um_user('display_name'),strlen(um_user('display_name'))-1,1) == "s"){$sadd = "’";}else{$sadd = "’s";}
					echo "<div class=\"tct_hd\">\n";
					echo "<h1>"._x('Teams', 'Team-Tab on Profile', 'transcribathon'  )."</h1><h3>".sprintf( esc_html__('%s teams','transcribathon'),um_user('display_name').$sadd)."</h3>\n";
					echo "</div>\n";	
					echo "<p>".sprintf( esc_html__('%s is not a member of any team yet','transcribathon'),um_user('display_name'))."</p>\n";
				}	
		
			echo "</div>\n";
			echo "<div class=\"column span_1_of_3 alg_l\">\n";		
				if(substr(um_user('display_name'),strlen(um_user('display_name'))-1,1) == "s"){$sadd = "’";}else{$sadd = "’s";}
				// Runs
				echo "<div class=\"tct_hd\">\n";
				echo "<h1>"._x('Runs', 'Team-Tab on Profile', 'transcribathon'  )."</h1><h3>".sprintf( esc_html__('%s runs','transcribathon'),um_user('display_name').$sadd)."</h3>\n";
				echo "</div>\n";
				// Run-list
				echo "<div id=\"isparticipant_list\" >\n";
					echo "<p class=\"smallloading\"></p>\n";
				echo "</div>\n";
				echo "<script type=\"text/javascript\">getTeamTabContent('".um_profile_id()."','".get_current_user_id()."');</script>\n";
				
				
			echo "</div>\n";
			echo "<div class=\"column span_1_of_3\">\n";				
				
				
		
			echo "</div>\n";
		echo "</div>\n";

	}		
		
}
    
       

        

add_shortcode( 'teamsandruns_tab', '_TCT_teamsandruns_tab' );
?>