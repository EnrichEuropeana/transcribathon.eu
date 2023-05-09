<?php
/* 
Shortcode: teamsandruns_tab
Description: Creates the 'Teams & runs' tab of profile tab
*//* Team-Tab */

function _TCT_teamsandruns_tab( $atts ) {

    // build 'Teams & runs' tab of Profile page
    // Keep it temp here, needs to be replaced after changes to user endpoint
    $url = TP_API_HOST."/tp-api/teams?WP_UserId=".um_profile_id();
    $requestType = "GET";
    
    // Execude http request
    include dirname(__FILE__)."/../custom_scripts/send_api_request.php";
    
    // Save image data
    $myTeams = json_decode($result, true);
    
     //var_dump($myTeams);
    $getJsonOptions = [
    	'http' => [
    		'header' => [ 
    			'Content-type: application/json',
    			'Authorization: Bearer ' . TP_API_V2_TOKEN
    		],
    		'method' => 'GET'
    	]
    ];
    
    $userInfo = sendQuery(TP_API_V2_ENDPOINT . '/users?WP_UserId=' . get_current_user_id(), $getJsonOptions, true);
	$userId = $userInfo["data"][0]["UserId"];
	

	$content = '';
	$content .= 
	    '<style type="text/css">
		    .tct_hd h1 {
				text-transfrom: none;
				line-height: 1.2;
				font-weight: regular;
				letter-spacing: 0.2;
				font-size: 1.8rem!important;
				margin-bottom: 0px!important;
				padding-bottom: 0px!important;
			}
			.tct_hd h3 {
				text-transfrom: none;
				line-height: 1.2;
				font-weight: regular;
				letter-spacing: 0.3;
				color: #333;
				font-size: 1.5rem!important;
				margin-top: 0px!important;
				padding-top: 0px!important;
				margin-bottom: 0px!important;
			}
			.tct_hd h1+h3 {
				margin-top: 0px!important;
				padding-top: 5px!important;
			}
			.tct_hd {
				padding-bottom: 20px!important;
			}
			button.tct-vio-but[type=button],input.tct-vio-but[type=button],a.tct-vio-but {
				min-height: 20px;
				border: none!important;
				font-size: 0.9em;
				letter-spacing: 0.5px;
				padding: 5px 30px;
				font-weight: regular;
				color: #fff!important;
				text-align: center;
				cursor: pointer;
				margin-top: 4px!important;
				display: inline-block!important;
			}
			.team-left {
				width: 60%;
			}
			.team-right {
				width: 39%;
				padding: 0 50px;
			}
			.team-left h3, .team-right h3 {
				font-weight: 700;
				letter-spacing: 0.025em;
				text-align: center;
				margin-bottom: 20px;
			}
			.teams-top {
				display: flex;
				justify-content: space-around;
				padding: 10px 25px;
				white-space: nowrap;
			}
			.teams-top label, .teams-top p {
				white-space: initial!important;
			}
			.teams-top label {
				margin-bottom: 2px;
				margin-top: 10px;
			}
			#team-form input, #team-form textarea {
				width: 100%;
				border-radius: 0!important;
				margin-bottom: 10px;
			}
			.single-team {
				position: relative;
				margin: 10px 0;
				padding: 10px;
				border: 1px solid #D3D3D3;
			}
			.team-desc {
				margin-top: 5px!important;
				margin-bottom: 5px!important;
				font-size: 14px;
				font-family: var(--p-font-family)!important;
				line-height: normal!important;
			}
			.campaign-single {
				font-size: 13px!important;
				margin-top: 5px!important;
				margin-bottom: 5px!important;
				margin-left: 10px!important;
			}
			.team-controls {
				position: absolute;
				right: 0;
				bottom: 0;
			}
			.team-controls i {
				font-size: 13px;
				margin: 5px!important;
				cursor: pointer;
			}
			.team-controls a {
				color: #000!important;
			}
			#join-team-btn {
				padding: 5px!important;
				border: none!important;
				border-radius: 0!important;
				font-weight: normal!important;
                height: 25px;
				width: 30px;

			}
			#join-team-code {
				height: 35px;
				width: calc( 100% - 30px);
				border-radius: 0px!important;
			}
			.join-team-box, .create-team-box {
				width: 80%;
				margin: 0 auto;
				cursor: pointer;
			}
			#generate-code, #create-team-btn {
				width: 80%;
				margin: 5px auto 10px;
				text-align: center;
			}
		</style>';

	if(is_user_logged_in() && get_current_user_id() === um_profile_id()) {

        $projectUrl = get_europeana_url();
		$content .= '<div class="teams-top">';

		    // Teams that user is part of
		    $content .= '<div class="team-left">';
			    $content .= '<h3 class="theme-color"> Teams </h3>';
				if(!empty($myTeams)) {
					$content .= '<p> Your Teams: </p>';
					foreach($myTeams as $team) {

						$content .= '<div class="single-team">';
						    $content .= '<h4 class="theme-color">' . $team['Name'] . ' ( ' . $team['ShortName'] . ') ' . '</h4>';
							$content .= '<p class="team-desc">' . $team['Description'] . '</p>';
							// Add all campaigns where team have participated
							$content .= '<h6 class="theme-color"> Campaigns: </h6>';
							$content .= '<p class="campaign-single"><i class="fas fa-running"></i><a href="'. $projectUrl .'/runs/dublin-run/" target="_blank"> Test Run </a></p>';
							$content .= '<div class="team-controls">';
							    $content .= '<a href="' . home_url() . '/teams/' . $team['Name'] . '" target="_blank"<i class="fas fa-eye" title="View team page"></i></a>';
								$content .= '<i class="fas fa-user-slash" title="Leave Team"></i>';
							$content .= '</div>';
						$content .= '</div>';

					}
				}
			$content .= '</div>';

			$content .= '<div class="team-right">';
			    // Join a team
                $content .= '<div class="join-team-box">';
			        $content .= '<h3 class="theme-color"> Join a team </h3>';
					$content .= '<p> If you received a code to join a team, please enter it here and click \'Join\'</p>';
					$content .= '<input type="text" id="join-team-code" placeholder="Enter team code">';
					$content .= '<button id="join-team-btn" class="theme-color-background"> Join </button>';
			    $content .= '</div>';
    
			    // Create a team
			    $content .= '<div class="create-team-box" style="margin-top:25px;">';
			        $content .= '<h3 class="theme-color"> Create a team </h3>';
					$content .= '<p> If you want to create a new team, please fill the form: </p>';
					$content .= '<form id="team-form">';
					    // Team Name
					    $content .= '<label for="team-title"> Team Name: </label></br>';
						$content .= '<input type="text" id="team-title" name="team-title" placeholder="Please enter a name"></br>';

						// Short Name
						$content .= '<p> Please enter an abbreviated name (max 6 characters), this might appear in some cases next of a member\'s name. </p>';
						$content .= '<label for="team-shortname"> Team name abbreviation: </label></br>';
						$content .= '<input type="text" id="team-shortname" name="team-shortname" placeholder="Abbr."></br>';

						// Description
						$content .= '<label for="team-description"> Short description of a team: </label></br>';
						$content .= '<textarea id="team-description" name="team-description" placeholder="Enter short description (optional)" rows="3"></textarea></br>';

						// Access code
						$content .= '<label for="access-code"> Generate the access code. Using this access code, other people can join you team:</label></br>';
						$content .= '<input type="text" id="access-code" name="access-code" style="display:none;">';
						$content .= '<div id="generate-code" class="theme-color-background"> Click here to generate access code </div>';

						// Runs code
						$content .= '<label for="run-code"> If you want to register this team for Transcribathon event or run, enter the run code below.
						    You can also do that later via \'Edit Team\'.</label></br>';
						$content .= '<input type="text" id="run-code" name="run-code" placeholder="Run code"></br>';

						// 'Submit' button
						$content .= '<div id="create-team-btn" class="theme-color-background"> Create Team </div>';

					$content .= '</form>';
			    $content .= '</div>';
			$content .= '</div>';

		$content .= '</div>';

		// Add eventlisteners to buttons
		$content .= 
		    '<script>
			// generate code
			const genCodeBtn = document.querySelector("#generate-code");
            genCodeBtn.addEventListener("click", function() {
				if(document.querySelector("#team-title").value == "") {
					window.alert("Please enter the team name first!");
				} else {
				    let code = generateTeamCode();
    
				    document.querySelector("#access-code").value = code;
				    document.querySelector("#access-code").style.display = "block";
				    genCodeBtn.style.display = "none";
				}
			});

			const createTeamBtn = document.querySelector("#create-team-btn");
			createTeamBtn.addEventListener("click", function() {
				createNewTeam(' . $userId . ');
			});
		
		    </script>';


	} else {
		echo 'Go Away';
	}


    return $content;

}
    
        

add_shortcode( 'teamsandruns_tab', '_TCT_teamsandruns_tab' );
?>