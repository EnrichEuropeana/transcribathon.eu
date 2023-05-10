<?php
/*
Shortcode: datasets_admin_page
Description: Creates the content for datasets admin page
*/
function _TCT_datasets_admin_page( $atts ) {

    global $wp;

    /* jQuery UI CSS*/
    wp_enqueue_style( 'jQuery-UI', CHILD_TEMPLATE_DIR . '/css/all.min.css');
    /* jQuery UI JS*/
    wp_enqueue_script( 'jQuery-UI', CHILD_TEMPLATE_DIR . '/js/jquery-ui.min.js');
    /* Bootstrap CSS */
    wp_enqueue_style( 'bootstrap', CHILD_TEMPLATE_DIR . '/css/bootstrap.min.css');
    /* Bootstrap JS */
    wp_enqueue_script('bootstrap', CHILD_TEMPLATE_DIR . '/js/bootstrap.min.js');
    // Set Post content

    $requestData = array(
        'key' => 'testKey'
    );
    $url = TP_API_HOST."/tp-api/datasets";
    $requestType = "GET";

    include dirname(__FILE__) . '/../custom_scripts/send_api_request.php';

    $datasets = json_decode($result, true);

    $content = "";

    $content .= '<link rel="stylesheet" type="text/css" href="'.CHILD_TEMPLATE_DIR.'/css/jquery-ui.css">';

    $content .= "<style>";
        $content .= ".admin-datasets-list {
                        list-style: none;
                        margin: 0;
                    }";
        $content .= ".admin-datasets-list li {
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

                    .admin-dataset-view-info {
                        float: left;
                        width: 60%;
                        padding-right: 20px;
                    }
                    .admin-dataset-view-teams {
                        float: left;
                        width: 35%;
                    }
                    ';
    $content .= "</style>";

    $content .= "<script>";
    $content .= "
                    function editDataset(datasetId) {
                        // Prepare data and send API request
                        data = {
                        }
                        jQuery('#dataset-' + datasetId + '-spinner-container').css('display', 'block');
                        data['Name'] = jQuery('#admin-dataset-' + datasetId + '-name').val();
                        data['ProjectId'] = jQuery('#admin-dataset-' + datasetId + '-project').val();

                        var dataString= JSON.stringify(data);

                        jQuery.post('".home_url( null, 'https' )."/wp-content/themes/transcribathon/admin/inc/custom_scripts/send_ajax_api_request.php', {
                            'type': 'POST',
                            'url': '".TP_API_HOST."/tp-api/datasets/' + datasetId,
                            'data': data
                        },
                        // Check success and create confirmation message
                        function(response) {
                            console.log(response);
                            jQuery('#dataset-' + datasetId + '-spinner-container').css('display', 'none')
                        });
                    }

                    function removeDataset(datasetId) {
                        jQuery('#dataset-' + datasetId + '-spinner-container').css('display', 'block');

                        jQuery.post('".home_url( null, 'https' )."/wp-content/themes/transcribathon/admin/inc/custom_scripts/send_ajax_api_request.php', {
                            'type': 'DELETE',
                            'url': '".TP_API_HOST."/tp-api/datasets/' + datasetId
                        },
                        // Check success and create confirmation message
                        function(response) {
                            console.log(response);
                            jQuery('#dataset-' + datasetId + '-spinner-container').css('display', 'none')
                        });
                    }

                    function addDataset() {
                        // Prepare data and send API request
                        data = {
                        }
                        jQuery('#dataset-spinner-container').css('display', 'block');
                        data['Name'] = jQuery('#admin-dataset-name').val();
                        data['ProjectId'] = jQuery('#admin-dataset-project').val();

                        var dataString= JSON.stringify(data);

                        jQuery.post('".home_url( null, 'https' )."/wp-content/themes/transcribathon/admin/inc/custom_scripts/send_ajax_api_request.php', {
                            'type': 'POST',
                            'url': '".TP_API_HOST."/tp-api/datasets',
                            'data': data
                        },
                        // Check success and create confirmation message
                        function(response) {
                            console.log(response);
                            jQuery('#dataset-spinner-container').css('display', 'none')
                        });
                    }


                    ";
    $content .= "</script>";

    $content .= "<h2>DATASETS</h2>";

    $content .= "<button class='collapse-controller' data-toggle='collapse' href='#admin-dataset-add'>";
        $content .= "ADD";
    $content .= "</button>";

    $content .= "<div id='admin-dataset-add' class='admin-dataset-edit collapse'>";
        $content .= "<h6>Name: </h6>";
        $content .= "<input id='admin-dataset-name'>";

        $content .= "<select id='admin-dataset-project'>";

            $requestData = array(
                'key' => 'testKey'
            );
            $url = TP_API_HOST."/tp-api/projects";
            $requestType = "GET";

            include dirname(__FILE__) . '/../custom_scripts/send_api_request.php';

            $projects = json_decode($result, true);

            foreach ($projects as $project) {
                $content .= '<option value="'.$project['ProjectId'].'">';
                    $content .= $project['Name'];
                $content .= '</option>';
            }
        $content .= "</select>";

        $content .= "</br>";

        $content .= "<button onClick='addDataset()' style='float: left; margin-top: 10px;'>";
            $content .= "SAVE";
        $content .= "</button>";
        $content .= '<div id="dataset-spinner-container" class="spinner-container spinner-container-left">';
            $content .= '<div class="spinnerAdmin"></div>';
        $content .= "</div>";
        $content .= "<div style='clear:both'></div>";
    $content .= "</div>";

    $content .= "<hr>";

    $content .= "<ul class='admin-datasets-list'>";

        foreach ($datasets as $dataset){
            $content .= "<li>";
                $content .= "<div class='admin-dataset-view-info'>";
                    $content .= "<div class='admin-dataset-view'>";
                            $content .= "<h5>".$dataset['Name']."</h5>";
                            $content .= "<p>";
                                $content .= "<span style='font-weight: bold'>Project: </span>".$dataset['ProjectName'];
                            $content .= "</p>";
                        $content .= "</div>";

                    $content .= "<hr>";

                    $content .= "<button class='collapse-controller' data-toggle='collapse' href='#admin-dataset-".$dataset['DatasetId']."-edit'>";
                        $content .= "EDIT";
                    $content .= "</button>";

                    $content .= "<div id='admin-dataset-".$dataset['DatasetId']."-edit' class='admin-dataset-edit collapse'>";
                        $content .= "<h6>Name: </h6>";
                        $content .= "<input id='admin-dataset-".$dataset['DatasetId']."-name' value='".$dataset['Name']."'>";

                        $content .= "<select id='admin-dataset-".$dataset['DatasetId']."-project'>";
                            foreach ($projects as $project) {
                                if ($project['Name'] == $dataset['ProjectName']) {
                                    $content .= '<option selected value="'.$project['ProjectId'].'">';
                                        $content .= $project['Name'];
                                    $content .= '</option>';
                                }
                                else {
                                    $content .= '<option value="'.$project['ProjectId'].'">';
                                        $content .= $project['Name'];
                                    $content .= '</option>';
                                }
                            }
                        $content .= "</select>";

                        $content .= "</br>";

                        $content .= "<button onClick='editDataset(".$dataset['DatasetId'].")' style='float: left; margin-top: 10px;'>";
                            $content .= "SAVE";
                        $content .= "</button>";
                        $content .= "<button onClick='removeDataset(".$dataset['DatasetId'].")' style='float: right; margin-top: 10px;'>";
                            $content .= "REMOVE";
                        $content .= "</button>";

                        $content .= '<div id="dataset-'.$dataset['DatasetId'].'-spinner-container" class="spinner-container spinner-container-left">';
                            $content .= '<div class="spinnerAdmin"></div>';
                        $content .= "</div>";
                        $content .= "<div style='clear:both'></div>";
                    $content .= "</div>";
                $content .= "</div>";

                $content .= "<div style='clear: both;'></div>";
            $content .= "</li>";
        }
    $content .= "</ul>";

    echo $content;
}
