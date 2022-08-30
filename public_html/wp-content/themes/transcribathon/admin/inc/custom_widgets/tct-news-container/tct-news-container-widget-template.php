<?php
global $wpdb;
$myid = uniqid(rand()).date('YmdHis');
$base = 0;

// include required files
include($_SERVER["DOCUMENT_ROOT"].'/wp-load.php');


$content = "";

// Top area content

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
            $content .= "<h2 class='theme-color'>NEWS</h2>";
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
                        $content .= "<a target=”_blank” href='".$newsPost->guid."'>more</a>";
                    $content .= "</div>";
                }
                /*$content .= "<div class='news-article'>";
                    $content .= "<div class='news-image'>";
                        $content .= '<img src="/wp-content/uploads/admin-ajax.jpg" alt=""/>';
                    $content .= "</div>";
                    $content .= "<div class='news-text'>";
                        $content .= "<h2 class='theme-color news-headline'>Follow us on Social Media!</h2>";
                        $content .= "<p class='theme-color news-subline'>Keep updated with all the exciting new things coming to the Transcribathon platform.</p>";
                        $content .= "<p id='news-article-1-text' class='news-description'>Twitter @Transcribathon </br>
                        facebook.com/transcribathon</p>";
                        $content .= '<p id="news-article-1-dots" class="dots" style="visibility:hidden">...</p>';
                    $content .= "</div>";
                        $content .= "<a target=”_blank” href='https://twitter.com/transcribathon' style='text-decoration:none; outline:none;'>Read More</a>";
                $content .= "</div>";

                $content .= "<div class='news-article'>";
                    $content .= "<div class='news-image'>";
                        $content .= '<img src="/wp-content/uploads/3.jpg" alt=""/>';
                    $content .= "</div>";
                    $content .= "<div class='news-text'>";
                        $content .= "<h2 class='theme-color news-headline'>Europeana 1989</h2>";
                        $content .= "<p class='theme-color news-subline'>The Europeana 1989 Transcribathon Run lets you uncover personal stories from the fall of the Iron Curtain. WE MADE HISTORY!</p>";
                        $content .= "<p>Learn about the revolutionary events of the period by deciphering, transcribing and annotating their fascinating, personal stories.</p>";
                        $content .= "<p id='news-article-2-text' class='news-description'></p>";
                        $content .= '<p id="news-article-2-dots" class="dots" style="visibility:hidden">...</p>';
                    $content .= "</div>";
                    $content .= "<a target=”_blank” href='https://europeana.transcribathon.eu/runs/europeana1989/' style='text-decoration:none; outline:none;'>READ MORE</a>";
                $content .= "</div>";

                $content .= "<div class='news-article'>";
                    $content .= "<div class='news-image'>";
                        $content .= '<img src="/wp-content/uploads/transcribathonlong_black.jpg" alt=""/>';
                    $content .= "</div>";
                    $content .= "<div class='news-text'>";
                        $content .= "<h2 class='theme-color news-headline'>ENRICH EUROPEANA LAUNCH EVENT IN VIENNA</h2>";
                        $content .= "<p class='theme-color news-subline'>The new Beta version of Enrich Europeana will be launched with a Mini Transcribathon in Vienna</p>";
                        $content .= "<p id='news-article-3-text' class='news-description'>Join us on Tuesday 24 September 2019 from 2 pm to 6:30 pm at the Austrian National Library in Vienna.</p>";
                        $content .= '<p id="news-article-3-dots" class="dots" style="visibility:hidden">...</p>';
                    $content .= "</div>";
                        $content .= "<a target=”_blank” href='https://www.eventbrite.co.uk/e/enrich-europeana-launch-event-tickets-65909961469;' style='text-decoration:none; outline:none;'>READ MORE</a>";
                $content .= "</div>";

                $content .= "<div class='news-article'>";
                $content .= "<div class='news-image'>";
                    $content .= '<img src="/wp-content/uploads/admin-ajax.jpg" alt=""/>';
                $content .= "</div>";
                $content .= "<div class='news-text'>";
                    $content .= "<h2 class='theme-color news-headline'>Follow us on Social Media!</h2>";
                    $content .= "<p class='theme-color news-subline'>Keep updated with all the exciting new things coming to the Transcribathon platform.</p>";
                    $content .= "<p id='news-article-1-text' class='news-description'>Twitter @Transcribathon </br>
                    facebook.com/transcribathon</p>";
                    $content .= '<p id="news-article-1-dots" class="dots" style="visibility:hidden">...</p>';
                $content .= "</div>";
                    $content .= "<a target=”_blank” href='https://twitter.com/transcribathon' style='text-decoration:none; outline:none;'>Read More</a>";
            $content .= "</div>";

            $content .= "<div class='news-article'>";
                $content .= "<div class='news-image'>";
                    $content .= '<img src="/wp-content/uploads/3.jpg" alt=""/>';
                $content .= "</div>";
                $content .= "<div class='news-text'>";
                    $content .= "<h2 class='theme-color news-headline'>Europeana 1989</h2>";
                    $content .= "<p class='theme-color news-subline'>The Europeana 1989 Transcribathon Run lets you uncover personal stories from the fall of the Iron Curtain. WE MADE HISTORY!</p>";
                    $content .= "<p>Learn about the revolutionary events of the period by deciphering, transcribing and annotating their fascinating, personal stories.</p>";
                    $content .= "<p id='news-article-2-text' class='news-description'></p>";
                    $content .= '<p id="news-article-2-dots" class="dots" style="visibility:hidden">...</p>';
                $content .= "</div>";
                $content .= "<a target=”_blank” href='https://europeana.transcribathon.eu/runs/europeana1989/' style='text-decoration:none; outline:none;'>READ MORE</a>";
            $content .= "</div>";

            $content .= "<div class='news-article'>";
                $content .= "<div class='news-image'>";
                    $content .= '<img src="/wp-content/uploads/transcribathonlong_black.jpg" alt=""/>';
                $content .= "</div>";
                $content .= "<div class='news-text'>";
                    $content .= "<h2 class='theme-color news-headline'>ENRICH EUROPEANA LAUNCH EVENT IN VIENNA</h2>";
                    $content .= "<p class='theme-color news-subline'>The new Beta version of Enrich Europeana will be launched with a Mini Transcribathon in Vienna</p>";
                    $content .= "<p id='news-article-3-text' class='news-description'>Join us on Tuesday 24 September 2019 from 2 pm to 6:30 pm at the Austrian National Library in Vienna.</p>";
                    $content .= '<p id="news-article-3-dots" class="dots" style="visibility:hidden">...</p>';
                $content .= "</div>";
                    $content .= "<a target=”_blank” href='https://www.eventbrite.co.uk/e/enrich-europeana-launch-event-tickets-65909961469;' style='text-decoration:none; outline:none;'>READ MORE</a>";
            $content .= "</div>";
            $content .= "<div class='news-article'>";
            $content .= "<div class='news-image'>";
                $content .= '<img src="/wp-content/uploads/admin-ajax.jpg" alt=""/>';
            $content .= "</div>";
            $content .= "<div class='news-text'>";
                $content .= "<h2 class='theme-color news-headline'>Follow us on Social Media!</h2>";
                $content .= "<p class='theme-color news-subline'>Keep updated with all the exciting new things coming to the Transcribathon platform.</p>";
                $content .= "<p id='news-article-1-text' class='news-description'>Twitter @Transcribathon </br>
                facebook.com/transcribathon</p>";
                $content .= '<p id="news-article-1-dots" class="dots" style="visibility:hidden">...</p>';
            $content .= "</div>";
                $content .= "<a target=”_blank” href='https://twitter.com/transcribathon' style='text-decoration:none; outline:none;'>Read More</a>";
        $content .= "</div>";

        $content .= "<div class='news-article'>";
            $content .= "<div class='news-image'>";
                $content .= '<img src="/wp-content/uploads/3.jpg" alt=""/>';
            $content .= "</div>";
            $content .= "<div class='news-text'>";
                $content .= "<h2 class='theme-color news-headline'>Europeana 1989</h2>";
                $content .= "<p class='theme-color news-subline'>The Europeana 1989 Transcribathon Run lets you uncover personal stories from the fall of the Iron Curtain. WE MADE HISTORY!</p>";
                $content .= "<p>Learn about the revolutionary events of the period by deciphering, transcribing and annotating their fascinating, personal stories.</p>";
                $content .= "<p id='news-article-2-text' class='news-description'></p>";
                $content .= '<p id="news-article-2-dots" class="dots" style="visibility:hidden">...</p>';
            $content .= "</div>";
            $content .= "<a target=”_blank” href='https://europeana.transcribathon.eu/runs/europeana1989/' style='text-decoration:none; outline:none;'>READ MORE</a>";
        $content .= "</div>";

        $content .= "<div class='news-article'>";
            $content .= "<div class='news-image'>";
                $content .= '<img src="/wp-content/uploads/transcribathonlong_black.jpg" alt=""/>';
            $content .= "</div>";
            $content .= "<div class='news-text'>";
                $content .= "<h2 class='theme-color news-headline'>ENRICH EUROPEANA LAUNCH EVENT IN VIENNA</h2>";
                $content .= "<p class='theme-color news-subline'>The new Beta version of Enrich Europeana will be launched with a Mini Transcribathon in Vienna</p>";
                $content .= "<p id='news-article-3-text' class='news-description'>Join us on Tuesday 24 September 2019 from 2 pm to 6:30 pm at the Austrian National Library in Vienna.</p>";
                $content .= '<p id="news-article-3-dots" class="dots" style="visibility:hidden">...</p>';
            $content .= "</div>";
                $content .= "<a target=”_blank” href='https://www.eventbrite.co.uk/e/enrich-europeana-launch-event-tickets-65909961469;' style='text-decoration:none; outline:none;'>READ MORE</a>";
        $content .= "</div>";*/

            $content .= "</div>";
            $content .= "<div class='news-navigation'>
            <button class='theme-color slick-prev' id='prev-news' style='float:left;'>Previous</button>
            <button class='theme-color slick-next' id='next-news' style='float:right;'>Next</button></div>";

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

echo $content;


?>
