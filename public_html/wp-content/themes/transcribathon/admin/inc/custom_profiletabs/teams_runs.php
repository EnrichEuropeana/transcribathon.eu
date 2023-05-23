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

	// Wip Disclaimer
	$content .= '<h1
	    class="
		    my-6
		    mx-auto
			w-1/3
		    border
			border-solid
			border-red-900
			text-base
			text-black
			p-4
		"> Hello there! </br>
	    At the moment, this page is still under construction, and some features are not fuly functional. Our team is working to provide seamless and enjoyable experience once the page is complete. </h1>';

	if(is_user_logged_in() && get_current_user_id() === um_profile_id()) {

        $projectUrl = get_europeana_url();
		$content .= '<div class="container mx-auto md:flex px-2 max-w-[80%]">';

		    // Teams that user is part of
		    $content .= '<div class="flex-initial w-full md:w-2/3">';
			    $content .= '<h2 class="theme-color text-2xl font-bold"> Teams </h2>';
				if(!empty($myTeams)) {
					$content .= '<p class="text-sm"> Your Teams: </p>';
					foreach($myTeams as $team) {

						$content .= '<div 
						    class="
						        relative
								mx-auto
								md:mx-0
						        mb-2
						        w-3/4
								p-2
								border-solid
								border-2
								border-gray-200
								rounded-none
						    ">';
						    $content .= '<h3 class="theme-color text-xl"><a href="' . home_url() . '/team?team=' . $team['Name'] . '" target="_blank">' . $team['Name'] . ' ( ' . $team['ShortName'] . ') ' . '</a></h3>';
							$content .= '<p class="team-desc text-sm">' . $team['Description'] . '</p>';
							// Add all team members
							$content .= '<div class="float-left w-1/3">';
							    $content .= '<h4 class="theme-color text-lg"> Members </h4>';
								$content .= '<p class="text-sm"> TestUser </p>';
							$content .= '</div>';
							// Add all campaigns where team have participated
							$content .= '<div class="float-right w-2/3">';
							    $content .= '<h4 class="theme-color text-lg"> Campaigns: </h4>';
							    $content .= '<p class="campaign-single text-sm"><i class="fas fa-running"></i><a href="'. $projectUrl .'/runs/dublin-run/" target="_blank"> Test Run </a></p>';
							$content .= '</div>';
							$content .= '<div class="clear-both"></div>';
							$content .= '<div 
							    class="
								    team-controls
									absolute
									right-2
									bottom-2
							">';
							    $content .= '<a href="' . home_url() . '/team/?team=' . $team['Name'] . '" target="_blank"<i class="fas fa-eye mr-2" title="View team page"></i></a>';
								$content .= '<i class="fas fa-user-slash" title="Leave Team"></i>';
							$content .= '</div>';
						$content .= '</div>';

					}
				}
			$content .= '</div>';

			$content .= '<div class="flex-initial w-11/12 md:w-1/3">';
			    // Join a team
                $content .= '<div class="whitespace-nowrap">';
			        $content .= '<h3 class="theme-color text-xl font-bold"> Join a team </h3>';
					$content .= '<p class="text-sm"> If you received a code to join a team, please enter it here and click \'Join\'</p>';
					$content .= '<div class="h-8">';
					    $content .= '<input type="text" id="join-team-code" placeholder="Enter team code" 
						    class="
						        w-4/5
						    	h-full
						    	rounded-none
								box-border
								align-bottom
								p-0.5
								leading-loose
						">';
					    $content .= '<button id="join-team-btn" 
						    class="
							    theme-color-background
								w-1/5
								h-full
								rounded-none
								border-red-800
								box-border
								align-bottom
								pb-px
								leading-loose
								text-sm
							">
						    Join
						</button>';
					$content .= '</div>';
			    $content .= '</div>';
    
			    // Create a team
			    $content .= '<div class="create-team-box" style="margin-top:25px;">';
			        $content .= '<h3 class="theme-color text-xl font-bold"> Create a team </h3>';
					$content .= '<p class="text-sm mb-8"> If you want to create a new team, please fill the form: </p>';
					$content .= '<form id="team-form">';
					    // Team Name
					    $content .= '<label for="team-title" class="text-sm"> Team Name: </label></br>';
						$content .= '<input type="text" id="team-title" name="team-title" placeholder="Please enter a name" 
						    class="
							    w-full
								h-8
								box-border
								p-0.5
								leading-loose
								rounded-none
								mb-6
							">
						</br>';

						// Short Name
						$content .= '<p class="text-sm"> Please enter an abbreviated name (max 6 characters), this might appear in some cases next of a member\'s name. </p>';
						$content .= '<label for="team-shortname" class="text-sm" class="w-2/6"> Team name abbreviation: </label>';
						$content .= '<input type="text" id="team-shortname" name="team-shortname" placeholder="Abbr." class="w-4/6 rounded-none border-box mb-8"></br>';

						// Description
						$content .= '<label for="team-description" class="text-sm clear-both"> Short description of a team: </label></br>';
						$content .= '<textarea id="team-description" name="team-description" placeholder="Enter short description (optional)" rows="3" 
						    class="
								w-full
								box-border
								rounded-none
								px-0.5
								mb-6
							">
						</textarea></br>';

						// Access code
						$content .= '<label for="access-code" class="text-sm"> 
						    Generate the access code. Using this access code, other people can join your team:
						</label></br>';
						$content .= '<input type="text" id="access-code" name="access-code" style="display:none;">';
						$content .= '<div id="generate-code" 
						    class="
							    theme-color-background
								text-sm
								text-center
								leading-loose
								h-8
								mb-6
							"> 
						    Click here to generate access code 
						</div>';

						// Runs code
						$content .= '<label for="run-code" class="text-sm"> 
						    If you want to register this team for Transcribathon event or run, enter the run code below.
						    You can also do that later via \'Edit Team\'.
						</label></br>';
						$content .= '<input type="text" id="run-code" name="run-code" placeholder="Run code"
						    class="
							    w-full
								h-8
								text-sm
								rounded-none
								leading-loose
								box-border
								mb-6
							">
						</br>';

						// 'Submit' button
						$content .= '<div id="create-team-btn" 
						    class="
							theme-color-background
							text-sm
							text-center
							leading-loose
							box-border
							h-8
							"> 
						    Create Team 
						</div>';

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
			// Create new team
			const createTeamBtn = document.querySelector("#create-team-btn");
			createTeamBtn.addEventListener("click", function() {
				createNewTeam(' . $userId . ');
			});
			// Join existing team
			const joinTeamBtn = document.querySelector("#join-team-btn");
			joinTeamBtn.addEventListener("click", function() {
				chkTmCd("' . um_profile_id() . '", "' . get_current_user_id() . '");
			});
		
		    </script>';


	} else {
		echo 'Go Away';
	}


    return $content;

}
    
        

add_shortcode( 'teamsandruns_tab', '_TCT_teamsandruns_tab' );
?>