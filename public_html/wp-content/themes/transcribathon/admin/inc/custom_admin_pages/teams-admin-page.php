<?php
/* 
Shortcode: teams_admin_page
Description: Creates the content for teams admin page
*/
function _TCT_teams_admin_page( $atts ) {  

    global $wp;
    // Set Post content
    $requestData = array(
        'key' => 'testKey'
    );
    $url = TP_API_HOST."/tp-api/teams";
    $requestType = "GET";

    include dirname(__FILE__) . '/../custom_scripts/send_api_request.php';

    /* jQuery UI CSS*/
    wp_enqueue_style( 'jQuery-UI', CHILD_TEMPLATE_DIR . '/css/jquery-ui.min.css');
    /* jQuery UI JS*/
    wp_register_script( 'jQuery-UI', CHILD_TEMPLATE_DIR . '/js/jquery-ui.min.js');
    /* Bootstrap CSS */
    wp_enqueue_style( 'bootstrap', CHILD_TEMPLATE_DIR . '/css/bootstrap.min.css');
    /* Bootstrap JS */
    wp_enqueue_script('bootstrap', CHILD_TEMPLATE_DIR . '/js/bootstrap.min.js');

    $teams = json_decode($result, true);
    $content = "";

    $content .= "<style>";
        $content .= ".admin-teams-list {
                        list-style: none;
                        margin: 0;
                    }";
        $content .= ".admin-teams-list li {
                        border: 2px #c4c4c4 solid;
                        border-radius: 5px;
                        padding: 0 10px;
                        margin-right: 100px;
                        margin-bottom: 20px;
                    }";
        $content .= '.spinnerAdmin {
                        height: 20px;  
                        position: relative;
                        opacity: 1;
                        transition: opacity linear 0.1s; 
                    }';
        $content .= '.spinnerAdmin::before {
                        border: solid 3px #eee;
                        border-radius: 50%;
                        content: "";
                        height: 20px;
                        left: 50%;
                        position: absolute;
                        top: 50%;
                        transform: translate3d(-50%, -50%, 0);
                        width: 20px;
                        animation: 2s linear infinite spinnerAdmin;
                        border: solid 3px #eee;
                        border-bottom-color: rgb(152, 152, 152);
                        border-radius: 50%;
                        content: "";
                        height: 20px;
                        left: 50%;
                        opacity: inherit;
                        position: absolute;
                        top: 50%;
                        transform: translate3d(-50%, -50%, 0);
                        transform-origin: center;
                        width: 20px;
                        will-change: transform;
                    }
                    @keyframes spinnerAdmin {
                        0% {
                            transform: translate3d(-50%, -50%, 0) rotate(0deg);
                        }
                        100% {
                             transform: translate3d(-50%, -50%, 0) rotate(360deg);
                        }
                    }
                    .spinner-container {
                        display: none;
                        padding: 10px;
                        width: 40px;
                    }
                    .spinner-container-left {
                        float: left;
                    }
                    .spinner-container-right {
                        float: right;
                    }

                    .admin-team-view-info {
                        float: left;
                        width: 60%;
                        padding-right: 20px;
                    }
                    .admin-team-view-member {
                        float: left;
                        width: 35%;
                    }
                    ';
    $content .= "</style>";

    $content .= "<script>";
    $content .= "   function generateTeamCode() {
                        var result           = '';
                        var characters       = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
                        var charactersLength = characters.length;
                        for ( var i = 0; i < 10; i++ ) {
                            result += characters.charAt(Math.floor(Math.random() * charactersLength));
                        }
                        return result;
                    }
                    
                    function editTeam(teamId) {
                        jQuery('#team-' + teamId + '-spinner-container').css('display', 'block')
                        name = jQuery('#admin-team-' + teamId + '-name').val();
                        shortName = jQuery('#admin-team-' + teamId + '-shortName').val();
                        description = jQuery('#admin-team-' + teamId + '-description').val();
                        code = jQuery('#admin-team-' + teamId + '-code').val();
                        
                        // Prepare data and send API request
                        data = {
                            Name: name,
                            ShortName: shortName,
                            Description: description,
                            Code: code
                        }
                        var dataString= JSON.stringify(data);
                        
                        jQuery.post('".home_url( null, 'https' )."/wp-content/themes/transcribathon/admin/inc/custom_scripts/send_ajax_api_request.php', {
                            'type': 'POST',
                            'url': '".TP_API_HOST."/tp-api/teams/' + teamId,
                            'data': data
                        },
                        // Check success and create confirmation message
                        function(response) {
                            console.log(response);
                            jQuery('#team-' + teamId + '-spinner-container').css('display', 'none')
                        });
                    }
                    
                    function addTeam() {
                        jQuery('#team-spinner-container').css('display', 'block')
                        name = jQuery('#admin-team-name').val();
                        shortName = jQuery('#admin-team-shortName').val();
                        description = jQuery('#admin-team-description').val();
                        code = jQuery('#admin-team-code').val();
                        
                        // Prepare data and send API request
                        data = {
                            Name: name,
                            ShortName: shortName,
                            Description: description,
                            Code: code
                        }
                        var dataString= JSON.stringify(data);
                        
                        jQuery.post('".home_url( null, 'https' )."/wp-content/themes/transcribathon/admin/inc/custom_scripts/send_ajax_api_request.php', {
                            'type': 'POST',
                            'url': '".TP_API_HOST."/tp-api/teams',
                            'data': data
                        },
                        // Check success and create confirmation message
                        function(response) {
                            console.log(response);
                            jQuery('#team-spinner-container').css('display', 'none')
                        });
                    }
                    
                    function removeTeamUser(userId, teamId) {                        
                        jQuery.post('".home_url( null, 'https' )."/wp-content/themes/transcribathon/admin/inc/custom_scripts/send_ajax_api_request.php', {
                            'type': 'DELETE',
                            'url': '".TP_API_HOST."/tp-api/teamUsers/' + teamId + '/' + userId,
                        },
                        // Check success and create confirmation message
                        function(response) {
                        });
                    }
                    
                    function addTeamUser(userId, teamId) {
                        data = {
                            UserId: userId,
                            TeamId: teamId
                        }
                        var dataString= JSON.stringify(data);
                        
                        jQuery.post('".home_url( null, 'https' )."/wp-content/themes/transcribathon/admin/inc/custom_scripts/send_ajax_api_request.php', {
                            'type': 'POST',
                            'url': '".TP_API_HOST."/tp-api/teamUsers',
                            'data': data
                        },
                        // Check success and create confirmation message
                        function(response) {
                        });
                    }
                    ";
    $content .= "</script>";

    $content .= "<h2>TEAMS</h2>";

    $content .= "<button class='collapse-controller' data-toggle='collapse' href='#admin-team-add'>";
        $content .= "ADD";
    $content .= "</button>";

    $content .= "<div id='admin-team-add' class='admin-team-edit collapse'>";
        $content .= "<h6>Name: </h6>";
        $content .= "<input id='admin-team-name'>";
        $content .= "<h6>Short Name: </h6>";
        $content .= "<input id='admin-team-shortName'>";
        $content .= "<p>";
            $content .= "<h6>Description: </h6>";
            $content .= "<textarea id='admin-team-description' style='width: 80%'></textarea>";
        $content .= "</p>";
        $content .= "<h6>Code: </h6>";
        $content .= "<input id='admin-team-code'>";
        $content .= "</br>";

        $content .= "<button onClick='addTeam()' style='float: left; margin-top: 10px;'>";
            $content .= "SAVE";
        $content .= "</button>";
        $content .= '<div id="team-spinner-container" class="spinner-container spinner-container-left">';
            $content .= '<div class="spinnerAdmin"></div>';
        $content .= "</div>";
        $content .= "<div style='clear:both'></div>";
    $content .= "</div>";

    $content .= "<hr>";

    $content .= "<ul class='admin-teams-list'>";
        foreach ($teams as $team){
            $content .= "<li>";
                $content .= "<div class='admin-team-view-info'>";
                    $content .= "<div class='admin-team-view'>";
                            $content .= "<h5>".$team['Name']." (".$team['ShortName'].")</h5>";
                            $content .= "<p>";
                                $content .= "<span style='font-weight: bold'>Description: </span>";
                                $content .= $team['Description'];
                            $content .= "</p>";
                            $content .= "<p>";
                                $content .= "<span style='font-weight: bold'>Code: </span>";
                                $content .= $team['Code'];
                            $content .= "</p>";
                        $content .= "</div>";

                    $content .= "<hr>";
                    
                    $content .= "<button class='collapse-controller' data-toggle='collapse' href='#admin-team-".$team['TeamId']."-edit'>";
                        $content .= "EDIT";
                    $content .= "</button>";

                    $content .= "<div id='admin-team-".$team['TeamId']."-edit' class='admin-team-edit collapse'>";
                        $content .= "<h6>Name: </h6>";
                        $content .= "<input id='admin-team-".$team['TeamId']."-name' value='".$team['Name']."'>";
                        $content .= "<h6>Short Name: </h6>";
                        $content .= "<input id='admin-team-".$team['TeamId']."-shortName' value='".$team['ShortName']."'>";
                        $content .= "<p>";
                            $content .= "<h6>Description: </h6>";
                            $content .= "<textarea id='admin-team-".$team['TeamId']."-description' style='width: 80%'>".$team['Description']."</textarea>";
                        $content .= "</p>";
                        $content .= "<h6>Code: </h6>";
                        $content .= "<input id='admin-team-".$team['TeamId']."-code' value='".$team['Code']."'>";
                        $content .= "</br>";

                        $content .= "<button onClick='editTeam(".$team['TeamId'].")' style='float: left; margin-top: 10px;'>";
                            $content .= "SAVE";
                        $content .= "</button>";
                        $content .= '<div id="team-'.$team['TeamId'].'-spinner-container" class="spinner-container spinner-container-left">';
                            $content .= '<div class="spinnerAdmin"></div>';
                        $content .= "</div>";
                        $content .= "<div style='clear:both'></div>";
                    $content .= "</div>";
                $content .= "</div>";

                $content .= "<div class='admin-team-view-member'>";
                    $content .= "<h5>Member:</h5>";
                    $content .= "<ul>";
                        $members = array();
                        foreach ($team['Users'] as $member) {
                            $content .= "<li>";
                                $content .= get_userdata($member['WP_UserId'])->user_nicename;
                                $content .= "<button onClick='removeTeamUser(".$member['WP_UserId'].", ".$team['TeamId'].")' style='float: right;'>REMOVE</button>";
                                array_push($members, $member['WP_UserId']);
                            $content .= "</li>";
                        }
                    $content .= "</ul>";
                    $content .= "<button class='collapse-controller' data-toggle='collapse' href='#team-".$team['TeamId']."-user-list'>ADD</button>";

                    $content .= "<div id='team-".$team['TeamId']."-user-list' class='collapse'>";
                        $content .= "<ul>";
                            $users = get_users('blog_id=1');
                            foreach ($users as $user) {
                                if (!in_array($user->data->ID, $members)) {
                                    $content .= "<li>";
                                        $content .= $user->data->user_nicename;
                                        $content .= "<button onClick='addTeamUser(".$user->data->ID.", ".$team['TeamId'].")' style='float: right;'>+</button>";
                                    $content .= "</li>";
                                }
                            }
                        $content .= "</ul>";
                    $content .= "</div>";
                $content .= "</div>";

                $content .= "<div style='clear: both;'></div>";
            $content .= "</li>";
        }
    $content .= "</ul>";

    echo $content;

   
}

add_action( 'admin_menu', 'teams_menu' );

function teams_menu() {
	add_menu_page( 
        'Teams', 
        'Teams', 
        'manage_options', 
        'teams-admin-page', 
        '_TCT_teams_admin_page', 
        'dashicons-groups', 
        3  
    );
}
?>