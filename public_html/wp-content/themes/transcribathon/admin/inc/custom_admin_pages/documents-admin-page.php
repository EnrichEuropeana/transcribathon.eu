<?php
/* 
Shortcode: documents_admin_page
Description: Creates the content for documents admin page
*/
function _TCT_documents_admin_page( $atts ) {  

    global $wp;
    // Set Post content
    $requestData = array(
        'key' => 'testKey'
    );
    $url = TP_API_HOST."/tp-api/storiesMinimal";
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

    $stories = json_decode($result, true);
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
                    function changeDataset(storyId) {
                        // Prepare data and send API request
                        data = {
                        }
                        jQuery('#story-' + storyId + '-spinner-container').css('display', 'block');
                        data['DatasetId'] = jQuery('#admin-story-' + storyId + '-dataset').val();
                        var dataString= JSON.stringify(data);
                        
                        jQuery.post('".home_url( null, 'https' )."/wp-content/themes/transcribathon/admin/inc/custom_scripts/send_ajax_api_request.php', {
                            'type': 'POST',
                            'url': '".TP_API_HOST."/tp-api/stories/' + storyId,
                            'data': data
                        },
                        // Check success and create confirmation message
                        function(response) {
                            console.log(response);
                            jQuery('#story-' + storyId + '-spinner-container').css('display', 'none');
                        });
                    }
                    
                    function searchStory() {
                        var storyId = jQuery('#search-story-id').val();
                        jQuery.post('".str_replace("http://", "https://", home_url())."' + '/wp-content/themes/transcribathon/admin/inc/custom_scripts/send_ajax_api_request.php', {
                            'type': 'GET',
                            'url': '".home_url()."' + '/tp-api/storiesMinimal/' + storyId
                        },
                        function(response) {
                            var response = JSON.parse(response);
                            console.log(response);
                            var image = JSON.parse(JSON.parse(response.content)[0]['PreviewImage']);
                            console.log(image);
                            if (image['service']['@id'].substring(0, 4) == 'http') {
                                var imageLink = image['service']['@id'];
                            }
                            else {
                                var imageLink = 'http://' + image['service']['@id'];
                            }
            
                            if (image['width'] != null || image['height'] != null) {
                                if (image['width'] <= image['height']) {
                                    imageLink += '/0,0,' + image['width'] + ',' + image['width'];
                                }
                                else {
                                    imageLink += '/0,0,' + image['height'] + ',' + image['height'];
                                }
                            }
                            else {
                                imageLink += '/full';
                            }
                            imageLink += '/100,100/0/default.jpg';
                            console.log(imageLink);
                            jQuery('.table').html(
                                '<thead>' +
                                    '<tr>' +
                                        '<th scope=\"col\">ID</th>' +
                                        '<th scope=\"col\">Title</th>' +
                                        '<th scope=\"col\">Dataset</th>' +
                                        '<th scope=\"col\">Thumbnail</th>' +
                                    '</tr>' +
                                '</thead>' +
                                '<tbody>' +
                                    '<tr>' +
                                        '<th scope=\"row\">' + JSON.parse(response.content)[0]['StoryId'] + '</th>' +
                                        '<td>' + JSON.parse(response.content)[0]['dcTitle'] + '</td>' +
                                        '<td>' + JSON.parse(response.content)[0]['dcTitle'] + '</td>' +
                                        '<td><img src=' + imageLink + '></td>' +
                                    '</tr>' +
                                '</tbody>'
                            )
                        })
                    }
                    ";
    $content .= "</script>";

    $content .= "<h2>Stories</h2>";

    $content .= "<label>ID: </label>";
    $content .= "<input type='text' id='search-story-id'/>";
    $content .= "<button onclick='searchStory()'>Search</button>";

    $content .= '<table class="table">
                    <thead>
                        <tr>
                            <th scope="col">ID</th>
                            <th scope="col">Title</th>
                            <th scope="col">Dataset</th>
                            <th scope="col">Thumbnail</th>
                        </tr>
                    </thead>
                    <tbody>';
                        $i = 0;
                        foreach ($stories as $story) {
                            $image = json_decode($story['PreviewImage'], true);
                            if (substr($image['service']['@id'], 0, 4) == "http") {
                                $imageLink = $image['service']['@id'];
                            }
                            else {
                                $imageLink = "http://".$image['service']['@id'];
                            }
            
                            if ($image["width"] != null || $image["height"] != null) {
                                if ($image["width"] <= $image["height"]) {
                                    $imageLink .= "/0,0,".$image["width"].",".$image["width"];
                                }
                                else {
                                    $imageLink .= "/0,0,".$image["height"].",".$image["height"];
                                }
                            }
                            else {
                                $imageLink .= "/full";
                            }
                            $imageLink .= "/100,100/0/default.jpg";

                            $content .= '
                                <tr>
                                    <th scope="row">'.$story['StoryId'].'</th>
                                    <td>'.$story['dcTitle'].'</td>
                                    <td>';
                            $content .= "<select id='admin-story-".$story['StoryId']."-dataset' onchange='changeDataset(".$story['StoryId'].")'>";
                                        $requestData = array(
                                            'key' => 'testKey'
                                        );
                                        $url = TP_API_HOST."/tp-api/datasets";
                                        $requestType = "GET";
                                    
                                        include dirname(__FILE__) . '/../custom_scripts/send_api_request.php';
                                    
                                        $datasets = json_decode($result, true);
                            
                                        if ($story['DatasetName'] == null) {
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
                                            if ($story['DatasetName'] == $dataset['Name']) {
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
                        $content .= '</td>
                                    <td><img src="'.$imageLink.'"></td>
                                    <div id="dataset-spinner-container" class="spinner-container spinner-container-left">
                                        <div class="spinnerAdmin"></div>
                                    </div>
                                </tr>';

                            $i++;
                            if ($i == 20) {
                                break;
                            }
                        }
                    '</tbody>
                </table>';

    echo $content;

   
}

add_action( 'admin_menu', 'documents_menu' );

function documents_menu() {
	add_menu_page( 
        'Documents', 
        'Documents', 
        'manage_options', 
        'documents-admin-page', 
        '_TCT_documents_admin_page', 
        'dashicons-admin-site', 
        3  
    );
}
?>