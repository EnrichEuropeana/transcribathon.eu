<?php
/**
 * The Template for displaying all single posts.
 *
 * @package vantage
 * @since vantage 1.0
 * @license GPL 2.0
 */

get_header(); 

?>
<div id="primary" class="content-area">
	<div id="content" class="site-content" role="main">

	<?php while ( have_posts() ) : the_post(); ?>

		<?php
		$p_members = $wpdb->get_results("SELECT count(id) as members,(SELECT owm.display_name FROM ".$wpdb->prefix."user_teams ow LEFT JOIN ".$wpdb->prefix."users owm ON owm.ID=ow.user_id WHERE ow.team_id='".$post->ID."' AND ow.user_role='owner') AS ownerfname,(SELECT ous.user_nicename FROM ".$wpdb->prefix."user_teams ow LEFT JOIN ".$wpdb->prefix."users ous ON ous.ID=ow.user_id WHERE ow.team_id='".$post->ID."' AND ow.user_role='owner') AS ownerusername FROM ".$wpdb->prefix."user_teams WHERE team_id='".$post->ID."'",ARRAY_A);
		$custom = get_post_custom($post->ID);
		
		echo "<div class=\"entry-content\">\n";
		echo "<div class=\"so-widget-tct-headline-widget widget_headline-widget\" style=\"text-align:left;\">\n";
			echo "<h1 class=\"no-top-pad\">".$post->post_title."</h1>\n";
			echo "<h3>";
			if(trim($p_members[0]['ownerfname']) != ""){
				echo sprintf(_x('Founded on %1$s by %2$s', 'Team View', 'transcribathon'),date_i18n( get_option( 'date_format' ), strtotime($post->post_date ) ),"<a href=\"/".ICL_LANGUAGE_CODE."/user/".$p_members[0]['ownerusername']."\">".$p_members[0]['ownerfname']."</a>")." ";
			}else{
				echo sprintf(_x('Founded on %s', 'Team View', 'transcribathon'),date_i18n( get_option( 'date_format' ), strtotime($post->post_date ) ))." ";
			}
			echo "</h3>\n";	
		if($post->post_content != ""){
			echo "<p>".$post->post_content."</p>";
		}
		
		echo "<hr />\n";
		echo "</div>\n";
		
		echo "<div class=\"section group maingroup\">\n";
			echo "<div class=\"col span_1_of_3\">\n";	
				echo "<div class=\"number-ball alg_c\">\n";
					echo "<div class=\"number-ball-content\">\n";
						echo "<p>".number_format_i18n((int)$p_members[0]['members'])."</p>";
						echo "<span>"._n( 'member', 'members', (int)$p_members[0]['members'], 'transcribathon'  )."</span>";
					echo "</div>\n";
				echo "</div>\n";	
			echo "</div>\n";
			echo "<div class=\"col span_1_of_3 alg_c\">\n";				
				$p_chars = $wpdb->get_results("SELECT sum(amount) FROM ".$wpdb->prefix."team_transcriptionprogress WHERE teamid='".$post->ID."'",ARRAY_N );
				echo "<div class=\"number-ball\">\n";
					echo "<div class=\"number-ball-content\">\n";
						echo "<p>".number_format_i18n((int)$p_chars[0][0])."</p>";
						echo "<span>"._x('characters', 'Transcription-Tab on Profile', 'transcribathon'  )."</span>";
					echo "</div>\n";
				echo "</div>\n";	
			echo "</div>\n";
			echo "<div class=\"col span_1_of_3\">\n";				
				$docs = $wpdb->get_results("SELECT count(DISTINCT docid) FROM ".$wpdb->prefix."team_transcriptionprogress WHERE teamid='".$post->ID."'",ARRAY_N);
				echo "<div class=\"number-ball alg_c\">\n";
					echo "<div class=\"number-ball-content\">\n";
						echo "<p>".number_format_i18n((int)$docs[0][0])."</p>";
						echo "<span>"._x('documents', 'Transcription-Tab on Profile', 'transcribathon'  )."</span>";
					echo "</div>\n";
				echo "</div>\n";
			echo "</div>\n";
		echo "</div>\n";
		
		
		echo "<p>&nbsp;</p>\n<div id=\"personal_chart\">\n";
			echo "<script type=\"text/javascript\">\n";
				$amt = $wpdb->get_results("SELECT SUM(amount) FROM ".$wpdb->prefix."team_transcriptionprogress WHERE teamid='".$post->ID."' and datum >= '".date('Y-m-')."01' AND datum <= '".date('Y-m-t')."'",ARRAY_N);
				if(trim($amt[0][0]) != "" && (int)$amt[0][0] > 0){
					echo "getTCTlineTeamChart('days','".date('Y-m-')."01','".date('Y-m-t')."','personal_chart','".$post->ID."');\n";
				}else{
					echo "getTCTlineTeamChart('months','".date('Y-')."01-01','".date('Y-m-t',strtotime(date('Y').'-12-01'))."','personal_chart','".$post->ID."');\n";
				}
			echo "</script>\n";
		echo "</div>\n";
		
		
		echo "<hr />";
		
		
		echo "<div class=\"section group maingroup\">\n";
		
			$base = 0; $limit=10; $myid = "team-".$post->ID;
		
			echo "<div class=\"col span_1_of_2\">\n";
		
			echo "<div class=\"entry-content\">\n";
			echo "<div class=\"so-widget-tct-headline-widget widget_headline-widget\" style=\"text-align:left;\">\n";
				echo "<h1 class=\"no-top-pad\">"._x('Team members', 'Team View', 'transcribathon')."</h1>\n";
				echo "<h3>"._x('in order of their contributions', 'Team View', 'transcribathon')."</h3>\n";	
			echo "</div>\n";
		
		
		
		
			$alltops = $wpdb->get_results("SELECT COUNT(DISTINCT user_id) AS total FROM ".$wpdb->prefix."user_teams WHERE team_id='".$post->ID."'",ARRAY_A);
			$query = "SELECT usr.display_name,ut.*,(SELECT SUM(tp.amount) FROM ".$wpdb->prefix."user_transcriptionprogress tp WHERE tp.userid=ut.user_id) AS useramnt,(SELECT(SUM(mm.miles_account)+SUM(mm.miles_chars)+SUM(mm.miles_review)+SUM(mm.miles_complete)+SUM(mm.miles_sharing)+SUM(mm.miles_message)+SUM(mm.miles_locations)+SUM(mm.miles_enrichements))  FROM ".$wpdb->prefix."user_miles mm WHERE mm.userid=usr.ID) AS umiles FROM ".$wpdb->prefix."user_teams ut LEFT JOIN ".$wpdb->prefix."users usr on usr.ID=ut.user_id WHERE ut.team_id='".$post->ID."' ORDER BY useramnt DESC LIMIT ".$base.",".$limit;
			$topusrs = $wpdb->get_results($query,ARRAY_A);
		
		
			echo "<div id=\"tu_list_".$myid."\">\n";
			if(sizeof($topusrs)>0){
				echo "<ul class=\"topusers\">\n";
				$i=1;	
				foreach($topusrs as $usr){
					$aut = get_user_by('ID',$usr['user_id']);
					um_fetch_user( $usr['user_id']);
					echo "<li class=\"p".$i."\">";
					echo "<div class=\"tct-user-banner ".um_user('role')."\">".ucfirst(um_user('role'))."</div>\n"; 
					$acs = $wpdb->get_results("SELECT ac.*,uc.campaign_title,CASE ac.placing WHEN '1' then uc.campaign_badge_1 WHEN '2' then uc.campaign_badge_2 WHEN '3' then uc.campaign_badge_3 END AS badge FROM ".$wpdb->prefix."user_achievments ac LEFT JOIN ".$wpdb->prefix."user_campaigns uc ON uc.id=ac.campaign WHERE ac.userid='".$usr['userid']."'",ARRAY_A);
					if(sizeof($acs)>0){
						echo "<div class=\"tct_tt-achievments\">\n"; 
						foreach($acs as $ac){
							echo "<div title=\"".$ac['campaign_title']."\"class=\"".$ac['badge']."\"></div>\n";
						}
						echo "</div>\n";
					}
					$miles = "<span class=\"milage\">".sprintf( esc_html( _n( '%s Mile', '%s Miles', (int)$usr['umiles'], 'transcribathon'  ) ), number_format_i18n((int)$usr['umiles']))."</span>\n";
					$chars = "<span class=\"chars\">".sprintf( esc_html( _n( '%s Character', '%s Characters', (int)$usr['useramnt'], 'transcribathon'  ) ), number_format_i18n((int)$usr['useramnt']))."</span>\n";
					echo "<span class=\"rang\">".$i."</span><h2><a href=\"/".ICL_LANGUAGE_CODE."/user/".$aut->user_nicename."/\">".um_user('display_name')."</a><span class=\"teammem\"></span></h2><p>".$miles." | ".$chars."</p></li>\n";
					$i++;
				}
			echo "</ul>\n";
		}else{
			// No members - no top-list!
		}
	echo "</div>\n"; // #tu_list...
	$showttmenu = 0;
	echo "<div id=\"ttnav_".$myid."\" class=\"ttnav\">\n";
	$menu = "<ul>\n";
	if($base>0){
		if(((int)$base-(int)$limit)<0){$newb = 0; }else{ $newb=((int)$base-(int)$limit); }
		$menu .= "<li class=\"ttprev\"><a href=\"\" onclick=\"getMoreTeamTops('".$myid."','".$newb."','".$limit."','".$post->ID."'); return false;\">"._x('Previous', 'Top-Transcribers-Slider-Widget (frontentd)','transcribathon')."</a></li>\n";
		$showttmenu++;
	}else{
		$prev = "";
	}
	if((int)$alltops[0]['total'] > ($base+$limit)){
		$menu .= "<li class=\"ttnext\"><a href=\"\" onclick=\"getMoreTeamTops('".$myid."','".((int)$base+(int)$limit)."','".$limit."','".$post->ID."'); return false;\">"._x('Next', 'Top-Transcribers-Slider-Widget (frontentd)','transcribathon')."</a></li>\n";
		$showttmenu++;
	}else{
		$next = "";
	}
	if($showttmenu > 0){
		echo $menu."</ul>\n";
	}
	echo "</div>\n"; // #ttnav_...
		
		
		
				
			echo "</div>\n"; // .col.span_1_of_2
			echo "<div class=\"col span_1_of_2\">\n";			
		
		
		
			echo "</div>\n"; // .col.span_1_of_2
		echo "</div>\n"; // .section.group
				
		
		
		
		echo "</div>\n";

		
		echo "<p>&nbsp;<br />&nbsp;<br />&nbsp;<br />&nbsp;</p>";
		echo "</div>\n";

 endwhile; // end of the loop. ?>

	
    
    
    
    </div><!-- #content .site-content -->
    
    
</div><!-- #primary .content-area -->

<?php get_sidebar(); ?>

<?php get_footer(); ?>