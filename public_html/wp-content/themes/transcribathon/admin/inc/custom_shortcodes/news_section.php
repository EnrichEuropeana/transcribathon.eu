<?php
/* 
Shortcode: news_section
Description: Gets news information and builds the news section for front page
*/


// include required files
include($_SERVER["DOCUMENT_ROOT"].'/wp-load.php');

function _TCT_news_section( $atts ) {  

// Build Front page content
$content = "";

// Top area content
$content .= "<section id='frontpage-top'>";
    // Text at the top
    $content .= "<div class='frontpage-top-text'>";
        $content .= "<h1 class='theme-color frontpage-top-headline'>WELCOME TO ENRICH EUROPEANA</h1>";
        $content .= "<div class='frontpage-top-subtext'>";
            $content .= "<p class='theme-color'>Lorem Ipsum is simply dummy text of the printing and typesetting Lorem Ipsum is simply dummy text of the printing and typesetting</p>";
            $content .= "<p class='theme-color'>Lorem Ipsum is simply dummy text of the printing and typesetting</p>";
        $content .= "</div>";
    $content .= "</div>";

    // Links at the top
    $content .= "<div id='frontpage-top-links'>";
        $content .= "<ul id='frontpage-top-link-list'>";
            $content .= "<li>";
            /*$content .= "<a href='' class='frontpage-top-link'>";
            $content .= "<div class='frontpage-top-link-div theme-color-hover' style='height: 100%;'>";*/
            $content .= "<h5 class='theme-color'><i style='font-style:normal' class='far fa-pen' style='font-size: 10px;'></i> TRANSCRIBE NOW</h5>";
            $content .= "</li>";

            $content .= "<li>";
            $content .= "<h5 class='theme-color'><i style='font-style:normal' class='fas fa-search' style='font-size: 10px;'></i> SEARCH DOCUMENTS</h5>";
            $content .= "</li>";

            $content .= "<li>";
            $content .= "<h5 class='theme-color'><i style='font-style:normal' class='far fa-question-circle' style='font-size: 10px;'></i> HOW TO TRANSCRIBE</h5>";
            $content .= "</li>";
        $content .= "</ul>";
    $content .= "</div>";
$content .= "</section>";

$content .= "<script>
                function test (){
                    var articleAmount = 8;
                    for (var i = 1; i <= articleAmount; i++) {
                        var articleText = document.getElementById('article-' + i + '-text');
                        
                        if (articleText != null) {
                            if (articleText.scrollHeight > articleText.clientHeight){
                                jQuery('#article-' + i + '-dots').css('visibility', 'visible')
                            }
                        }
                    }
                };
                window.onload = test;
            </script>";

    // Get all news posts
    $args = array( 
        'post_type'		=> 'news', // or 'post', 'page'
        );

    $newsPosts = get_posts($args);
    global $_wp_additional_image_sizes;

    $content .= "<section class='news-container'>";
            $content .= "<div class='news-slider'>";
                foreach ($newsPosts as $newsPost) {
                    $content .= "<div class='news-article'>";
                        $content .= "<div class='news-image'>";
                            $content .= '<img data-lazy="'.wp_get_attachment_image_src(get_post_meta($newsPost->ID, "_thumbnail_id")[0], 
                                            array($_wp_additional_image_sizes['news-image']['width'],$_wp_additional_image_sizes['news-image']['height']))[0].'" alt=""/>';
                        $content .= "</div>";
                        $content .= "<div class='news-text'>";
                            $content .= "<h2 class='theme-color news-headline'>".$newsPost->title."</h2>";
                            $content .= "<p class='theme-color news-subline'>".get_post_meta($newsPost->ID, "tct_news_subheadline")[0]."</p>";
                            $content .= "<p id='news-article-1-text' class='news-description'>".get_post_meta($newsPost->ID, "tct_news_excerpt")[0]."</p>";
                        $content .= "</div>";
                        $content .= "<a href=''>READ MORE</a>";
                    $content .= "</div>";
                }
                $content .= "<div class='news-article'>";
                    $content .= "<div class='news-image'>";
                        $content .= '<img data-lazy="https://transcribathon.com/wp-content/uploads/PB281692c-2-436x272.jpg" alt=""/>';
                    $content .= "</div>";
                    $content .= "<div class='news-text'>";
                        $content .= "<h2 class='theme-color news-headline'>JOIN THE RACE</h2>";
                        $content .= "<p class='theme-color news-subline'>Lorem Ipsum is simply dummy text of the printing and typesetting </p>";
                        $content .= "<p id='news-article-1-text' class='news-description'>Lorem Ipsum is simply dummy text of the printing and typesetting industrLorem Ipsum is simply dummy text of the printing and typesetting industrLorem Ipsum is simply dummy text of the printing and typesetting industry.</p>";
                        $content .= '<p id="news-article-1-dots" class="dots" style="visibility:hidden">...</p>';
                    $content .= "</div>";
                        $content .= "<a href=''>WATCH VIDEO</a>";
                $content .= "</div>";
                
                $content .= "<div class='news-article'>";
                    $content .= "<div class='news-image'>";
                        $content .= '<img data-lazy="https://transcribathon.com/wp-content/uploads/PB281692c-2-436x272.jpg" alt=""/>';
                    $content .= "</div>";
                    $content .= "<div class='news-text'>";
                        $content .= "<h2 class='theme-color news-headline'>COOKBOOKS THROUGH THE CENTURIES</h2>";
                        $content .= "<p class='theme-color news-subline'>Lorem Ipsum is simply dummy text of the printing and typesetting </p>";
                        $content .= "<p>Lorem Ipsum is simply dummy text of the printing and typesetting industry.</p>";
                        $content .= "<p id='news-article-2-text' class='news-description'>Lorem Ipsum is simply dummy text of the printing and typesetting industrLorem Ipsum is simply dummy text of the printing and typesetting industrLorem Ipsum is simply dummy text of the printing and typesetting industry.</p>";
                        $content .= '<p id="news-article-2-dots" class="dots" style="visibility:hidden">...</p>';
                    $content .= "</div>";
                    $content .= "<a href=''>READ MORE</a>";
                $content .= "</div>";
                
                $content .= "<div class='news-article'>";
                    $content .= "<div class='news-image'>";
                        $content .= '<img data-lazy="https://transcribathon.com/wp-content/uploads/PB281692c-2-436x272.jpg" alt=""/>';
                    $content .= "</div>";
                    $content .= "<div class='news-text'>";
                        $content .= "<h2 class='theme-color news-headline'>EUROPEAN TRANSCRIBATHON CHAMPIONSHIP IN BRUSSELS!</h2>";
                        $content .= "<p class='theme-color news-subline'>Lorem Ipsum is simply dummy text of the printing and typesetting </p>";
                        $content .= "<p id='news-article-3-text' class='news-description'>Lorem Ipsum is simply dummy text of the printing and typesetting industrLorem Ipsum is simply dummy text of the printing and typesetting industrLorem Ipsum is simply dummy text of the printing and typesetting industry.</p>";
                        $content .= '<p id="news-article-3-dots" class="dots" style="visibility:hidden">...</p>';
                    $content .= "</div>";
                        $content .= "<a href=''>READ MORE</a>";
                $content .= "</div>";

                $content .= "<div class='news-article'>";
                    $content .= "<div class='news-image'>";
                        $content .= '<img data-lazy="https://transcribathon.com/wp-content/uploads/PB281692c-2-436x272.jpg" alt=""/>';
                    $content .= "</div>";
                    $content .= "<div class='news-text'>";
                        $content .= "<h2 class='theme-color news-headline'>LEVEL 4!</h2>";
                        $content .= "<p class='theme-color news-subline'>Lorem Ipsum is simply dummy text of the printing and typesetting </p>";
                        $content .= "<p id='news-article-4-text' class='news-description'>Lorem Ipsum is simply dummy text of the printing and typesetting industrLorem Ipsum is simply dummy text of the printing and typesetting industrLorem Ipsum is simply dummy text of the printing and typesetting industry.</p>";
                        $content .= '<p id="news-article-4-dots" class="dots" style="visibility:hidden">...</p>';
                    $content .= "</div>";
                        $content .= "<a href=''>READ MORE</a>";
                $content .= "</div>";

                $content .= "<div class='news-article'>";
                    $content .= "<div class='news-image'>";
                        $content .= '<img data-lazy="https://transcribathon.com/wp-content/uploads/PB281692c-2-436x272.jpg" alt=""/>';
                    $content .= "</div>";
                    $content .= "<div class='news-text'>";
                        $content .= "<h2 class='theme-color news-headline'>LEVEL 5!</h2>";
                        $content .= "<p class='theme-color news-subline'>Lorem Ipsum is simply dummy text of the printing and typesetting </p>";
                        $content .= "<p id='news-article-5-text' class='news-description'>Lorem Ipsum is simply dummy text of the printing and typesetting industrLorem Ipsum is simply dummy text of the printing and typesetting industrLorem Ipsum is simply dummy text of the printing and typesetting industry.</p>";
                        $content .= '<p id="news-article-5-dots" class="dots" style="visibility:hidden">...</p>';
                    $content .= "</div>";
                        $content .= "<a href=''>READ MORE</a>";
                $content .= "</div>";

                $content .= "<div class='news-article'>";
                    $content .= "<div class='news-image'>";
                        $content .= '<img data-lazy="https://transcribathon.com/wp-content/uploads/PB281692c-2-436x272.jpg" alt=""/>';
                    $content .= "</div>";
                    $content .= "<div class='news-text'>";
                        $content .= "<h2 class='theme-color news-headline'>LEVEL 6!</h2>";
                        $content .= "<p class='theme-color news-subline'>Lorem Ipsum is simply dummy text of the printing and typesetting </p>";
                        $content .= "<p id='news-article-6-text' class='news-description'>Lorem Ipsum is simply dummy text of the printing and typesetting industrLorem Ipsum is simply dummy text of the printing and typesetting industrLorem Ipsum is simply dummy text of the printing and typesetting industry.</p>";
                        $content .= '<p id="news-article-6-dots" class="dots" style="visibility:hidden">...</p>';
                    $content .= "</div>";
                        $content .= "<a href=''>READ MORE</a>";
                $content .= "</div>";

                $content .= "<div class='news-article'>";
                    $content .= "<div class='news-image'>";
                        $content .= '<img data-lazy="https://transcribathon.com/wp-content/uploads/PB281692c-2-436x272.jpg" alt=""/>';
                    $content .= "</div>";
                    $content .= "<div class='news-text'>";
                        $content .= "<h2 class='theme-color news-headline'>LEVEL 7!</h2>";
                        $content .= "<p class='theme-color news-subline'>Lorem Ipsum is simply dummy text of the printing and typesetting </p>";
                        $content .= "<p id='news-article-7-text' class='news-description'>Lorem Ipsum is simply dummy text of the printing and typesetting industrLorem Ipsum is simply dummy text of the printing and typesetting industrLorem Ipsum is simply dummy text of the printing and typesetting industry.</p>";
                        $content .= '<p id="news-article-7-dots" class="dots" style="visibility:hidden">...</p>';
                    $content .= "</div>";
                        $content .= "<a href=''>READ MORE</a>";
                $content .= "</div>";

                $content .= "<div class='news-article'>";
                    $content .= "<div class='news-image'>";
                        $content .= '<img data-lazy="https://transcribathon.com/wp-content/uploads/PB281692c-2-436x272.jpg" alt=""/>';
                    $content .= "</div>";
                    $content .= "<div class='news-text'>";
                        $content .= "<h2 class='theme-color news-headline'>LEVEL 8!</h2>";
                        $content .= "<p class='theme-color news-subline'>Lorem Ipsum is simply dummy text of the printing and typesetting </p>";
                        $content .= "<p id='news-article-8-text' class='news-description'>Lorem Ipsum is simply dummy text of the printing and typesetting industrLorem Ipsum is simply dummy text of the printing and typesetting industrLorem Ipsum is simply dummy text of the printing and typesetting industry.</p>";
                        $content .= '<p id="news-article-8-dots" class="dots" style="visibility:hidden">...</p>';
                    $content .= "</div>";
                        $content .= "<a href=''>READ MORE</a>";
                $content .= "</div>";
            $content .= "</div>";
            $content .= "<div class='news-navigation'><button class='theme-color slick-prev' id='prev-news' style='float:left;'>Previous</button><button class='theme-color slick-next' id='next-news' style='float:right;'>Next</button>";

        // Image slider JavaScript
        $content .= "<script>
                        jQuery(document).ready(function(){
                            jQuery('.news-slider').slick({
                                draggable:false,
                                swipe:false,
                                infinite:false,
                                speed: 300,
                                slidesToShow: 3,
                                slidesToScroll: 3,
                                lazyLoad: 'ondemand',
                                prevArrow: jQuery('#prev-news'),
                                nextArrow: jQuery('#next-news'),
                                responsive: [
                                    {
                                        breakpoint: 800,
                                        settings: {
                                        slidesToShow: 2,
                                        slidesToScroll: 2
                                        }
                                    },
                                    {
                                        breakpoint: 600,
                                        settings: {
                                        slidesToShow: 1,
                                        slidesToScroll: 1
                                        }
                                    },
                                ]
                            });
                        });
                </script>";
    $content .= "</section>";


$content .= "<section id='frontpage-bottom'>";
    $content .= "<div>";
        $content .= "<ul id='frontpage-bottom-link-list'>";
            $content .= "<li>";
                $content .= "<a href='' class='frontpage-bottom-link'>";
                $content .= "<div class='frontpage-bottom-link-div theme-color-hover' style='height: 100%;'>";
                    $content .= "<i class='far fa-users frontpage-bottom-icon'></i>";    
                    $content .= "<h5 class='theme-color theme-hover-child'>MEMBERS</h5>";
                $content .= "</div>";
                $content .= "</a>";
            $content .= "</li>";

            $content .= "<li>";
                $content .= "<a href='' class='frontpage-bottom-link'>";
                $content .= "<div class='frontpage-bottom-link-div theme-color-hover' style='height: 100%;'>";
                    $content .= "<i class='far fa-map-marked-alt frontpage-bottom-icon'></i>";
                    $content .= "<h5 class='theme-color theme-hover-child'>DOCUMENT MAP</h5>";
                $content .= "</div>";
            $content .= "</a>";
            $content .= "</li>";

            $content .= "<li>";
                $content .= "<a href='' class='frontpage-bottom-link'>";
                $content .= "<div class='frontpage-bottom-link-div theme-color-hover' style='height: 100%;'>";
                    $content .= "<i class='fal fa-chart-pie frontpage-bottom-icon'></i>";    
                    $content .= "<h5 class='theme-color theme-hover-child'>PROGRESS</h5>";
                $content .= "</div>";
            $content .= "</a>";
            $content .= "</li>";
                    
        $content .= "</ul>";
    $content .= "</div>";

$content .= "</section>";

echo $content;
}
add_shortcode( 'news_section',  '_TCT_news_section' );
?>
