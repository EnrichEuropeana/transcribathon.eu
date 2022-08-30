<?php
/*
Shortcode: tutorial_slider
Description: Gets tutorial slides and builds
*/

// include required files
include($_SERVER["DOCUMENT_ROOT"].'/wp-load.php');


function _TCT_tutorial_slider( $atts ) {
    global $ultimatemember;
    if (isset($_GET['item']) && $_GET['item'] != "") {

        // Execude http request
        include dirname(__FILE__)."/../custom_scripts/send_api_request.php";
  
        //include theme directory for text hovering
        $theme_sets = get_theme_mods();
        
        // Build tutorial slider content
        $content = "";
        $content = "<style>
                         
                    </style>";

                   // $content .= do_shortcode('[ultimatemember form_id="40"]');
             
    

        //  JavaScript
        $content .= '<script>
                       
                    </script>';

        echo $content;
    }
}
add_shortcode( 'tutorial_slider', '_TCT_tutorial_slider' );
?>
