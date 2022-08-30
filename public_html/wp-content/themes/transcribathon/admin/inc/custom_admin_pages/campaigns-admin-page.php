<?php
/* 
Shortcode: campaigns_admin_page
Description: Creates the content for campaigns admin page
*/
function _TCT_campaigns_admin_page( $atts ) {  

    global $wp;
    // Set Post content
    $requestData = array(
        'key' => 'testKey'
    );
    $url = TP_API_HOST."/tp-api/campaigns";
    $requestType = "GET";

    include dirname(__FILE__) . '/../custom_scripts/send_api_request.php';

    /* jQuery UI CSS*/
    wp_enqueue_style( 'jQuery-UI', CHILD_TEMPLATE_DIR . '/css/all.min.css');
    /* jQuery UI JS*/
    wp_enqueue_script( 'jQuery-UI', CHILD_TEMPLATE_DIR . '/js/jquery-ui.min.js');
    /* Bootstrap CSS */
    wp_enqueue_style( 'bootstrap', CHILD_TEMPLATE_DIR . '/css/bootstrap.min.css');
    /* Bootstrap JS */
    wp_enqueue_script('bootstrap', CHILD_TEMPLATE_DIR . '/js/bootstrap.min.js');

    $campaigns = json_decode($result, true);
    $content = "";

    $content .= '<link rel="stylesheet" type="text/css" href="'.CHILD_TEMPLATE_DIR.'/css/jquery-ui.css">';

    $content .= "<style>";
        $content .= ".admin-campaigns-list {
                        list-style: none;
                        margin: 0;
                    }";
        $content .= ".admin-campaigns-list li {
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

                    .admin-campaign-view-info {
                        float: left;
                        width: 60%;
                        padding-right: 20px;
                    }
                    .admin-campaign-view-teams {
                        float: left;
                        width: 35%;
                    }
                    ';
    $content .= "</style>";

    $content .= "<script>";
    $content .= "   
                    jQuery ( document ).ready(function() {
                        jQuery( '.datepicker-input-field' ).datepicker({
                            dateFormat: 'dd/mm/yy',
                            changeMonth: true,
                            changeYear: true,
                            yearRange: '2019:2022',
                            showOn: 'button',
                            buttonText: '<i class=\'far fa-calendar-edit datepick-calendar-size\'></i>'
                        });
                    })

                    function generateCampaignCode() {
                        var result           = '';
                        var characters       = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
                        var charactersLength = characters.length;
                        for ( var i = 0; i < 10; i++ ) {
                            result += characters.charAt(Math.floor(Math.random() * charactersLength));
                        }
                        return result;
                    }
                    
                    function editCampaign(campaignId) {
                        // Prepare data and send API request
                        data = {
                        }
                        jQuery('#campaign-' + campaignId + '-spinner-container').css('display', 'block');
                        data['Name'] = jQuery('#admin-campaign-' + campaignId + '-name').val();
                        startDate = jQuery('#admin-campaign-' + campaignId + '-startDate').val().split('/');
                        if (!isNaN(startDate[2]) && !isNaN(startDate[1]) && !isNaN(startDate[0])) {
                          data['Start'] = startDate[2] + '-' + startDate[1] + '-' + startDate[0];
                          data['Start'] += ' ' + jQuery('#admin-campaign-' + campaignId + '-startTime').val();
                        }
                        endDate = jQuery('#admin-campaign-' + campaignId + '-endDate').val().split('/');
                        if (!isNaN(endDate[2]) && !isNaN(endDate[1]) && !isNaN(endDate[0])) {
                          data['End'] = endDate[2] + '-' + endDate[1] + '-' + endDate[0];
                          data['End'] += ' '  + jQuery('#admin-campaign-' + campaignId + '-endTime').val();
                        }
                        if (jQuery('#admin-campaign-' + campaignId + '-dataset').val() != 'null') {
                            data['DatasetId'] = jQuery('#admin-campaign-' + campaignId + '-dataset').val();
                        }
                        data['Public'] = 0;
                        if (jQuery('#admin-campaign-' + campaignId + '-public').prop('checked')) {
                            data['Public'] = 1
                        }
                        
                        var dataString= JSON.stringify(data);
                        
                        jQuery.post('".home_url( null, 'https' )."/wp-content/themes/transcribathon/admin/inc/custom_scripts/send_ajax_api_request.php', {
                            'type': 'POST',
                            'url': '".TP_API_HOST."/tp-api/campaigns/' + campaignId,
                            'data': data
                        },
                        // Check success and create confirmation message
                        function(response) {
                            console.log(response);
                            jQuery('#campaign-' + campaignId + '-spinner-container').css('display', 'none')
                        });
                    }
                    
                    function removeCampaign(campaignId) {     
                        jQuery('#campaign-' + campaignId + '-spinner-container').css('display', 'block');
                                           
                        jQuery.post('".home_url( null, 'https' )."/wp-content/themes/transcribathon/admin/inc/custom_scripts/send_ajax_api_request.php', {
                            'type': 'DELETE',
                            'url': '".TP_API_HOST."/tp-api/campaigns/' + campaignId
                        },
                        // Check success and create confirmation message
                        function(response) {
                            console.log(response);
                            jQuery('#campaign-' + campaignId + '-spinner-container').css('display', 'none')
                        });
                    }
                    
                    function addCampaign() {
                        // Prepare data and send API request
                        data = {
                        }
                        jQuery('#campaign-spinner-container').css('display', 'block');
                        data['Name'] = jQuery('#admin-campaign-name').val();
                        startDate = jQuery('#admin-campaign-startDate').val().split('/');
                        if (!isNaN(startDate[2]) && !isNaN(startDate[1]) && !isNaN(startDate[0])) {
                          data['Start'] = startDate[2] + '-' + startDate[1] + '-' + startDate[0];
                          data['Start'] += ' ' + jQuery('#admin-campaign-startTime').val();
                        }
                        endDate = jQuery('#admin-campaign-endDate').val().split('/');
                        if (!isNaN(endDate[2]) && !isNaN(endDate[1]) && !isNaN(endDate[0])) {
                          data['End'] = endDate[2] + '-' + endDate[1] + '-' + endDate[0];
                          data['End'] += ' '  + jQuery('#admin-campaign-endTime').val();
                        }
                        if (jQuery('#admin-campaign-dataset').val() != null && jQuery('#admin-campaign-dataset').val() != 'null') {
                            data['DatasetId'] = jQuery('#admin-campaign-dataset').val();
                        }
                        data['Public'] = 0;
                        if (jQuery('#admin-campaign-public').prop('checked')) {
                            data['Public'] = 1
                        }
                        
                        var dataString= JSON.stringify(data);
                        
                        jQuery.post('".home_url( null, 'https' )."/wp-content/themes/transcribathon/admin/inc/custom_scripts/send_ajax_api_request.php', {
                            'type': 'POST',
                            'url': '".TP_API_HOST."/tp-api/campaigns',
                            'data': data
                        },
                        // Check success and create confirmation message
                        function(response) {
                            console.log(response);
                            jQuery('#campaign-spinner-container').css('display', 'none')
                        });
                    }
                    
                    function removeCampaignTeam(teamId, campaignId) {                        
                        jQuery.post('".home_url( null, 'https' )."/wp-content/themes/transcribathon/admin/inc/custom_scripts/send_ajax_api_request.php', {
                            'type': 'DELETE',
                            'url': '".TP_API_HOST."/tp-api/teamCampaigns/' + campaignId + '/' + teamId,
                        },
                        // Check success and create confirmation message
                        function(response) {
                        });
                    }
                    
                    function addCampaignTeam(teamId, campaignId) {
                        data = {
                            TeamId: teamId,
                            CampaignId: campaignId
                        }
                        var dataString= JSON.stringify(data);
                        
                        jQuery.post('".home_url( null, 'https' )."/wp-content/themes/transcribathon/admin/inc/custom_scripts/send_ajax_api_request.php', {
                            'type': 'POST',
                            'url': '".TP_API_HOST."/tp-api/teamCampaigns',
                            'data': data
                        },
                        // Check success and create confirmation message
                        function(response) {
                        });
                    }
                    
                    function changeDataset(campaignId) {
                        // Prepare data and send API request
                        data = {
                        }
                        jQuery('#campaign-' + campaignId + '-spinner-container').css('display', 'block');
                        data['DatasetId'] = jQuery('#admin-campaign-' + campaignId + '-dataset').val();
                        var dataString= JSON.stringify(data);
                        
                        jQuery.post('".home_url( null, 'https' )."/wp-content/themes/transcribathon/admin/inc/custom_scripts/send_ajax_api_request.php', {
                            'type': 'POST',
                            'url': '".TP_API_HOST."/tp-api/campaigns/' + campaignId,
                            'data': data
                        },
                        // Check success and create confirmation message
                        function(response) {
                            console.log(response);
                            jQuery('#campaign-' + campaignId + '-spinner-container').css('display', 'none');
                        });
                    }
                    
                    ";
    $content .= "</script>";

    $content .= "<h2>CAMPAIGNS</h2>";

    $content .= "<button class='collapse-controller' data-toggle='collapse' href='#admin-campaign-add'>";
        $content .= "ADD";
    $content .= "</button>";

    $content .= "<div id='admin-campaign-add' class='admin-campaign-edit collapse'>";
        $content .= "<h6>Name: </h6>";
        $content .= "<input id='admin-campaign-name'>";

        $content .= "<p>";
            $content .= "<h6>Start: </h6>";
            $content .= "<input id='admin-campaign-startDate' class='datepicker-input-field' style='width: 150px;'>";
            $content .= "<input id='admin-campaign-startTime' style='width: 200px; margin-left: 20px;' placeholder='Start time: hh/mm/ss'>";
        $content .= "</p>";
        $content .= "<p>";
            $content .= "<h6>End: </h6>";
            $content .= "<input id='admin-campaign-endDate' class='datepicker-input-field' style='width: 150px;'>";
            $content .= "<input id='admin-campaign-endTime' style='width: 200px; margin-left: 20px;' placeholder='End time: hh/mm/ss'>";
        $content .= "</p>";

        $content .= "<h6>Dataset: </h6>";
        $content .= "<select id='admin-campaign-dataset'>";
            $requestData = array(
                'key' => 'testKey'
            );
            $url = TP_API_HOST."/tp-api/datasets";
            $requestType = "GET";
        
            include dirname(__FILE__) . '/../custom_scripts/send_api_request.php';
        
            $datasets = json_decode($result, true);

            $content .= '<option selected value=null>';
                $content .= "No dataset";
            $content .= '</option>';
            foreach ($datasets as $dataset) {
                $content .= '<option value="'.$dataset['DatasetId'].'">';
                    $content .= $dataset['Name'];
                $content .= '</option>';
            }
        $content .= "</select>";

        $content .= "</br>";

        $content .= "<span>Public: </span><input type='checkbox' id='admin-campaign-public'>";
        $content .= "</br>";

        $content .= "<button onClick='addCampaign()' style='float: left; margin-top: 10px;'>";
            $content .= "SAVE";
        $content .= "</button>";
        $content .= '<div id="campaign-spinner-container" class="spinner-container spinner-container-left">';
            $content .= '<div class="spinnerAdmin"></div>';
        $content .= "</div>";
        $content .= "<div style='clear:both'></div>";
    $content .= "</div>";

    $content .= "<hr>";

    $content .= "<ul class='admin-campaigns-list'>";
        foreach ($campaigns as $campaign){
            $content .= "<li>";
                $content .= "<div class='admin-campaign-view-info'>";
                    $content .= "<div class='admin-campaign-view'>";
                            $content .= "<h5>".$campaign['Name']."</h5>";
                            $content .= "<p>";
                                $content .= "<span style='font-weight: bold'>Start: </span>";
                                $startTimestamp = strtotime($campaign['Start']);
                                $dateStart = date("d/m/Y", $startTimestamp);
                                $timeStart = date("H:i:s", $startTimestamp);
                                $content .= $dateStart." ".$timeStart;
                            $content .= "</p>";
                            $content .= "<p>";
                                $content .= "<span style='font-weight: bold'>End: </span>";
                                $endTimestamp = strtotime($campaign['End']);
                                $dateEnd = date("d/m/Y", $endTimestamp);
                                $timeEnd = date("H:i:s", $endTimestamp);
                                $content .= $dateEnd." ".$timeEnd;
                            $content .= "</p>";
                            $content .= "<p>";
                                $content .= "<span style='font-weight: bold'>Public: </span>";
                                if ($campaign['Public'] == 1) {
                                    $content .= "yes";
                                }
                                else {
                                    $content .= "no";
                                }
                            $content .= "</p>";
                        $content .= "</div>";

                    $content .= "<hr>";
                    
                    $content .= "<button class='collapse-controller' data-toggle='collapse' href='#admin-campaign-".$campaign['CampaignId']."-edit'>";
                        $content .= "EDIT";
                    $content .= "</button>";

                    $content .= "<div id='admin-campaign-".$campaign['CampaignId']."-edit' class='admin-campaign-edit collapse'>";
                        $content .= "<h6>Name: </h6>";
                        $content .= "<input id='admin-campaign-".$campaign['CampaignId']."-name' value='".$campaign['Name']."'>";
                        $content .= "<p>";
                            $content .= "<h6>Start: </h6>";
                            $content .= "<input id='admin-campaign-".$campaign['CampaignId']."-startDate' class='datepicker-input-field' style='width: 150px;' value='".$dateStart."'>";
                            $content .= "<input id='admin-campaign-".$campaign['CampaignId']."-startTime' style='width: 150px; margin-left: 20px;' value='".$timeStart."'>";
                        $content .= "</p>";
                        $content .= "<p>";
                            $content .= "<h6>End: </h6>";
                            $content .= "<input id='admin-campaign-".$campaign['CampaignId']."-endDate' class='datepicker-input-field' style='width: 150px;' value='".$dateEnd."'>";
                            $content .= "<input id='admin-campaign-".$campaign['CampaignId']."-endTime' style='width: 150px; margin-left: 20px;' value='".$timeEnd."'>";
                        $content .= "</p>";

                        $content .= "<h6>Dataset: </h6>";
                        $content .= "<select id='admin-campaign-".$campaign['CampaignId']."-dataset'>";
                            $requestData = array(
                                'key' => 'testKey'
                            );
                            $url = TP_API_HOST."/tp-api/datasets";
                            $requestType = "GET";
                        
                            include dirname(__FILE__) . '/../custom_scripts/send_api_request.php';
                        
                            $datasets = json_decode($result, true);
                
                            if ($campaign['DatasetName'] == null) {
                                $content .= '<option selected value=null>';
                                    $content .= "No dataset";
                                $content .= '</option>';
                            }
                            else {
                                $content .= '<option value=null>';
                                    $content .= "No dataset";
                                $content .= '</option>';
                            }
                            foreach ($datasets as $dataset) {
                                if ($campaign['DatasetName'] == $dataset['Name']) {
                                    $content .= '<option selected value="'.$dataset['DatasetId'].'">';
                                        $content .= $dataset['Name'];
                                    $content .= '</option>';
                                }
                                else {
                                    $content .= '<option value="'.$dataset['DatasetId'].'">';
                                        $content .= $dataset['Name'];
                                    $content .= '</option>';
                                }
                            }
                        $content .= "</select>";

                        $content .= "</br>";

                        $checked = "";
                        if ($campaign['Public'] == 1) {
                            $checked = "checked";
                        }
                        $content .= "<span>Public: </span><input type='checkbox' id='admin-campaign-".$campaign['CampaignId']."-public' ".$checked.">";
                                
                        $content .= "</br>";

                        $content .= "<button onClick='editCampaign(".$campaign['CampaignId'].")' style='float: left; margin-top: 10px;'>";
                            $content .= "SAVE";
                        $content .= "</button>";
                        $content .= "<button onClick='removeCampaign(".$campaign['CampaignId'].")' style='float: right; margin-top: 10px;'>";
                            $content .= "REMOVE";
                        $content .= "</button>";

                        $content .= '<div id="campaign-'.$campaign['CampaignId'].'-spinner-container" class="spinner-container spinner-container-left">';
                            $content .= '<div class="spinnerAdmin"></div>';
                        $content .= "</div>";
                        $content .= "<div style='clear:both'></div>";
                    $content .= "</div>";
                $content .= "</div>";

                $content .= "<div class='admin-campaign-view-teams'>";
                    $content .= "<h5>Teams:</h5>";
                    $content .= "<ul>";
                        $teams = array();
                        foreach ($campaign['Teams'] as $team) {
                            $content .= "<li>";
                                $content .= $team['Name'];
                                $content .= "<button onClick='removeCampaignTeam(".$team['TeamId'].", ".$campaign['CampaignId'].")' style='float: right;'>REMOVE</button>";
                                array_push($teams, $team['TeamId']);
                            $content .= "</li>";
                        }
                    $content .= "</ul>";
                    $content .= "<button class='collapse-controller' data-toggle='collapse' href='#campaign-".$campaign['CampaignId']."-team-list'>ADD</button>";

                    $content .= "<div id='campaign-".$campaign['CampaignId']."-team-list' class='collapse'>";
                        $content .= "<ul>";
                            // Set request parameters for story data
                            $url = TP_API_HOST."/tp-api/teams";
                            $requestType = "GET";
                    
                            // Execude http request
                            include dirname(__FILE__)."/../custom_scripts/send_api_request.php";
                    
                            // Save story data
                            $teamData = json_decode($result, true);

                            foreach ($teamData as $team) {
                                if (!in_array($team['TeamId'], $teams)) {
                                    $content .= "<li>";
                                        $content .= $team['Name'];
                                        $content .= "<button onClick='addCampaignTeam(".$team['TeamId'].", ".$campaign['CampaignId'].")' style='float: right;'>+</button>";
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

add_action( 'admin_menu', 'campaigns_menu' );

function campaigns_menu() {
	add_menu_page( 
        'Campaigns', 
        'Campaigns', 
        'manage_options', 
        'campaigns-admin-page', 
        '_TCT_campaigns_admin_page', 
        'dashicons-admin-site', 
        3  
    );
}
?>