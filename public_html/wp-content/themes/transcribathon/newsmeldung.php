<?php
/**
 * Displays 
 * 
 * @package vantage
 * @since vantage 1.0
 * @license GPL 2.0
 */

include(TCT_THEME_DIR_PATH.'admin/inc/custom_labels/labels-single-doc.php');


	$c = get_post_custom($post->ID);	
	
	// Get the Thumbnail
	if(has_post_thumbnail($post->ID)){$thumb_url = wp_get_attachment_image_src(get_post_thumbnail_id($post->ID),'transcribathon-uncropped-small', true);}else{$thumb_url = array();}
		
	echo "<div class=\"col span_1_of_4 newsarchiv\">\n"; 
		echo "<a style=\"text-decoration: none;\" href=\"".$post->guid."\">";
		echo "<div id=\"additional-news-border\"><div class=\"dcholder news-page-images\" style=\"background-image: url(".$thumb_url[0]."); \"><img src=\"".CHILD_TEMPLATE_DIR."/images/spaceholder.gif\" alt=\"\" /></div></div>\n";
		
		if(isset($c['tct_news_startdate'][0]) && trim($c['tct_news_startdate'][0]) != "" || isset($c['tct_news_enddate'][0]) && trim($c['tct_news_enddate'][0]) != ""){
			
				if(isset($c['tct_news_startdate'][0]) && trim($c['tct_news_startdate'][0]) != "" && isset($c['tct_news_enddate'][0]) && trim($c['tct_news_enddate'][0]) != ""){
					echo "<div class=\"newsdate\">\n";
						$start = strtotime($c['tct_news_startdate'][0]);
						$ende = strtotime($c['tct_news_enddate'][0]);
						echo "<div class=\"leftdate\">\n";
							echo "<h4 class='theme-color'>".date('d',$start)."</h4>\n";
							echo "<h5>".date_i18n('M',$start)."</h5>\n";
							echo "<h6>".date_i18n('Y',$start)."</h6>\n";
						echo "</div>\n";
						echo "<div class=\"rightdate\">\n";
							echo "<h4 class='theme-color'>".date('d',$ende)."</h4>\n";
							echo "<h5>".date('M',$ende)."</h5>\n";
							echo "<h6>".date('Y',$ende)."</h6>\n";
						echo "</div>\n";
						echo "<p class=\"clear\"></p>\n";
						echo "</div>\n";
				}else if(isset($c['tct_news_startdate'][0]) && trim($c['tct_news_startdate'][0]) != ""){
					echo "<div class=\"newsdate single\">\n";
						$start = strtotime($c['tct_news_startdate'][0]);
						echo "<div class=\"leftdate\">\n";
							echo "<h4 class='theme-color'>".date('d',$start)."</h4>\n";
							echo "<h5>".date_i18n('M',$start)."</h5>\n";
							echo "<h6>".date_i18n('Y',$start)."</h6>\n";
						echo "</div>\n";
						echo "<p class=\"clear\"></p>\n";
						echo "</div>\n";
				}else if(isset($c['tct_news_enddate'][0]) && trim($c['tct_news_enddate'][0]) != ""){
					echo "<div class=\"newsdate single\">\n";
						$start = strtotime($c['tct_news_enddate'][0]);
						echo "<div class=\"leftdate\">\n";
							echo "<h4 class='theme-color'>".date('d',$start)."</h4>\n";
							echo "<h5>".date_i18n('M',$start)."</h5>\n";
							echo "<h6>".date_i18n('Y',$start)."</h6>\n";
						echo "</div>\n";
						echo "<p class=\"clear\"></p>\n";
						echo "</div>\n";
				}
		}else{
			echo "<div class=\"nodate\"></div>\n";
		}
		
		echo "<h1>".$post->post_title."</h1>\n";
		/*echo "<span>".$post->post_title."</span>\n";*/
		if(isset($c['tct_news_subheadline'][0]) && trim($c['tct_news_subheadline'][0]) !== ""){
			echo "<h3>".$c['tct_news_subheadline'][0]."</h3>\n";	
		}
		if(isset($c['tct_news_excerpt'][0]) && trim($c['tct_news_excerpt'][0]) !== ""){
			echo "<p>".$c['tct_news_excerpt'][0]."</p>\n";	
		}
		echo "</a>\n";
		echo "<div class=\"bottombutton theme-color-background\">";
		echo "<a href=\"".$post->guid."\" class=\"more\">"._x('more','Read-More Button at news','transcribathon')."</a>";
		echo "</div>\n";
	echo "</div>\n";






?>