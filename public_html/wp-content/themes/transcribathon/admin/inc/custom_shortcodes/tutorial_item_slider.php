<?php
/*
Shortcode: tutorial_item_slider
Description: Gets tutorial slides and builds
*/

// include required files
include($_SERVER["DOCUMENT_ROOT"].'/wp-load.php');

function _TCT_tutorial_item_slider( $atts ) {
    global $ultimatemember;
    if (isset($_GET['item']) && $_GET['item'] != "") {

  
        //include theme directory for text hovering
        $theme_sets = get_theme_mods();
        
        // Build tutorial slider content
        $content = "";
        $content = "<style>
                        .tutorial-prev {
                            position: relative;
                            float: left;
                            bottom: 0;
                            padding: 5px;
                            background-color: #0a72cc;
                            color: #fff;
                            width: 80px;
                        }
                        .tutorial-next {
                            position: relative;
                            float: right;
                            bottom: 0;
                            padding: 5px;
                            background-color: #0a72cc;
                            color: #fff;
                            width: 80px;
                        }
                        #tutorial-carousel button:focus {
                            background: #0a72cc!important;
                        }
                    </style>";
                           
        // Get all tutorial posts
        $args = array( 
            'posts_per_page'   => 50,
            'post_type'		=> 'tutorial', // or 'post', 'page'
            'meta_key' => 'tct_tutorial_order',
            'orderby'  => 'meta_value_num',
            'order'			=> 'ASC'
            );

        $tutorialPosts = get_posts($args);
        global $_wp_additional_image_sizes;
        // New Bootstrap Carousel to replace slick slider//
        $content .= '<div id="tutorial-help-item-page" class="tutorial-right tutorial-window-slider">';
        $skipSlideList = ["Register","Enrich"];
        $content .= "<div id='tutorial-carousel' class='carousel slide' data-ride='carousel' data-interval='false' style='height:100%;'>";
            $content .= "<div class='carousel-inner' style='height:90%;overflow-y: scroll;'>";
            $slideTracker = 0;
            foreach ($tutorialPosts as $tutorialPost) {
                if (in_array($tutorialPost->post_title, $skipSlideList)) {
                    continue;
                }
                if(get_post_meta($tutorialPost->ID, "_thumbnail_id")[0] != null) {
                    if($slideTracker == 0){
                        $content .= "<div class='carousel-item active'>";
                    } else {
                        $content .= "<div class='carousel-item'>";
                    }
                        $content .= "<div class='tutorial-image-area'>";
                            $content .= "<img src='".wp_get_attachment_image_src(get_post_meta($tutorialPost->ID, '_thumbnail_id')[0],
                            array($_wp_additional_image_sizes['tutorial-image']['width'],$_wp_additional_image_sizes['tutorial-image']['height']))[0]."' alt='tutorial slide' loading='lazy'/>";
                        $content .= "</div>";
                        $content .= "<div class='tutorial-text-area'>";
                            $content .= "<h2 class='theme-color tutorial-headline'>".$tutorialPost->post_title."</h2>";
                            $content .= $tutorialPost->post_content;
                        $content .= "</div>";
                    $content .= "</div>"; // carousel-item closing
                } else {
                    if($slideTracker == 0){
                        $content .= "<div class='carousel-item active'>";
                    } else {
                        $content .= "<div class='carousel-item'>";
                    }
                        $content .= "<div class='tutorial-text-area'>";
                            $content .= "<h2 class='theme-color tutorial-headline'>".$tutorialPost->post_title."</h2>";
                            $content .= $tutorialPost->post_content;
                        $content .= "</div>";
                    $content .= "</div>"; // carousel-item closing
                }
                $slideTracker += 1;
            }
            $content .= "</div>"; // Carousel inner closing
            $content .= "<div style='height:10%;'>";
            $content .= "<button id='tut-prev' class='tutorial-prev' type='button' data-target='#tutorial-carousel' data-slide='prev'>";
                $content .= "<span class='' aria-hidden='true'><< Previous</span>";
                $content .= "<span class='sr-only'>Prevoius</span>";
            $content .= "</button>";
            $content .= "<button id='tut-next' class='tutorial-next' type='button' data-target='#tutorial-carousel' data-slide='next'>";
                $content .= "<span class='' aria-hidden='true'>Next >></span>";
                $content .= "<span class='sr-only'>Next</span>";
            $content .= "</button>";
            $content .= "</div>";
        $content .= "</div>"; // Carousel closing

        /* -------------------------------------------------------------------------------------- */
        // $content .= '<div id="tutorial-help-item-page" class="tutorial-right tutorial-window-slider">';
        //     $skipSlideList = ["Register","Enrich"];
        //     foreach ($tutorialPosts as $tutorialPost) {
        //         if (in_array($tutorialPost->post_title, $skipSlideList)) {
        //             continue;
        //         }
        //         $content .= "<div class='testing active slick-slide'>";
        //             if (get_post_meta($tutorialPost->ID, "_thumbnail_id")[0] != null) {
        //                 $content .= "<div class='tutorial-image-area'>";
        //                     $content .= '<img data-lazy="'.wp_get_attachment_image_src(get_post_meta($tutorialPost->ID, "_thumbnail_id")[0], 
        //                     array($_wp_additional_image_sizes['tutorial-image']['width'],$_wp_additional_image_sizes['tutorial-image']['height']))[0].'" alt=""/>';
        //                 $content .= "</div>";
        //                 $content .= "<div class='tutorial-text-area'>";
        //                     $content .= "<h2 class='theme-color tutorial-headline'>".$tutorialPost->post_title."</h2>";
        //                     $content .= $tutorialPost->post_content;
        //                 $content .= "</div>";
        //             }
        //             else {
        //                 $content .= "<div class='tutorial-text-area' style='height: 100%'>";
        //                     $content .= "<h2 class='theme-color tutorial-headline'>".$tutorialPost->post_title."</h2>";
        //                     $content .= $tutorialPost->post_content;
        //                 $content .= "</div>";
        //             }
        //             $content .= '<div style="clear:both;"></div>';
        //         $content .= "</div>";
        //     }
        $content .= '</div>';

        //  JavaScript
        // $content .= "<script> 
        //                 jQuery(document).ready(function(){
        //                     jQuery('#tutorial-help-item-page').slick({
        //                         slidesToShow: 1,
        //                         slidesToScroll: 1,
        //                         dots: true,
        //                         infinite: false
        //                     });
        //                     jQuery('a[data-slide]').click(function(e) {
        //                     e.preventDefault();
        //                     var slideno = jQuery(this).data('slide');
        //                         jQuery('a[data-slide]').removeClass('theme-color-active');
        //                         jQuery(this).addClass('theme-color-active');
        //                     jQuery('#tutorial-help-item-page').slick('slickGoTo', slideno - 1);
        //                     });
        //                 });
        //             </script>";

        return $content;
    }
}
add_shortcode( 'tutorial_item_slider', '_TCT_tutorial_item_slider' );
?>
