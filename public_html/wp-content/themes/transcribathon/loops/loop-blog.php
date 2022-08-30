<?php
/**
 * Loop Name: Blog
 */
?>
<?php if ( have_posts() ) : ?>

	<?php /* Start the Loop */ ?>
   
    <?
	
	if(!function_exists( 'getDocsNav')){
		function getDocsNav(){
			global $wp_query;
			$pagina = paginate_links( array(
				'base' => str_replace(999999999, '%#%', esc_url( get_pagenum_link( 999999999 ) ) ),
				'format' => '?paged=%#%',
				'show_all'	=> true,
				'type'	=> 'array',
				'prev_next' => false,
				'current' => max( 1, get_query_var('paged') ),
				'total' => $wp_query->max_num_pages
			));
			if(trim($_SERVER['QUERY_STRING']) != "" ){ $add="?".$_SERVER['QUERY_STRING']; }else{ $add = ""; }
			$ret = "<nav role=\"navigation\" class=\"site-navigation paging-navigation\">\n<h1 class=\"assistive-text\">Post navigation</h1>\n";
			$ret .= "<div class=\"pagination\">\n";
				if(sizeof($pagina)>0){
					$ret .= "<ul>\n";
					$current =  max( 1, get_query_var('paged') );
					// first
					if((int)$current > 1){
						$ret .= "<li><a class=\"first page-numbers\" href=\"/".ICL_LANGUAGE_CODE."/documents/page/1/".$add."\">«« First</a></li>\n";
					}else{
						$ret .= "<li><span class=\"first page-numbers\">«« First</span></li>\n";
					}
					// prev
					if((int)$current > 1){
						$ret .= "<li><a class=\"prev page-numbers\" href=\"/".ICL_LANGUAGE_CODE."/documents/page/".((int)$current-1)."/".$add."\">« Previous</a></li>\n";
					}else{
						$ret .= "<li><span class=\"prev page-numbers\">« Previous</span></li>\n";
					}
					$ret .= "<li>";
						$ret .= "<select onchange=\"document.location.href='/".ICL_LANGUAGE_CODE."/documents/page/'+jQuery(this).val()+'/".$add."';\">\n";
						for($i=1; $i<=sizeof($pagina); $i++){
							if($i == (int)$current){$sl = " selected=\"selected\""; }else{ $sl = ""; }
							$ret .= "<option value=\"".$i."\"".$sl.">".$i."</option>\n";
						}	
						$ret .= "</select>\n";
					$ret .= "</li>\n";
					// next
					if((int)$current < (int)sizeof($pagina)){
						$ret .= "<li><a class=\"next page-numbers\" href=\"/".ICL_LANGUAGE_CODE."/documents/page/".((int)$current+1)."/".$add."\">Next »</a></li>\n";
					}else{
						$ret .= "<li><span class=\"next page-numbers\">Next »</span></li>\n";
					}
					// last
					if((int)$current < (int)sizeof($pagina)){
						$ret .= "<li><a class=\"last page-numbers\" href=\"/".ICL_LANGUAGE_CODE."/documents/page/".sizeof($pagina)."/".$add."\">Last »»</a></li>\n";
					}else{
						$ret .= "<li><span class=\"last page-numbers\">Last »»</span></li>\n";
					}
					$ret .= "</ul>\n";
				}
			$ret .= "</div>\n";
			$ret .= "</nav>\n";
			
			
			return $ret;
			
		}
		
		
		
	}
 	//vantage_content_nav( 'nav-below' );
	echo getDocsNav();
	
	
		// Doc-Uebersicht
		if('documents' === get_post_type($post->ID)){ 
			//echo "<ul class=\"tct-featureboxes smallfont\">\n"; 
			echo "<div id=\"doc-results\">\n";
			echo "<div class=\"tableholder\">\n";
				echo "<div class=\"tablegrid\">\n";
					echo "<div class=\"section group sepgroup tab\">\n";
						$i=0;
						while ( have_posts() ) : the_post(); 
							//if(isset($_GET['st']) && $_GET['st'] != ""){
								if($i<4){ $i++; }else{ $i=1; echo "</div>\n<div class=\"section group tab sepgroup\">\n"; }
								get_template_part( 'document', get_post_format() );
								if($i==2){ echo "<span class=\"sep\"></span>\n";}
							//}
						endwhile;
					echo "</div>\n";	
				echo "</div>\n";
			echo "</div>\n";
			//echo "</ul>\n"; 
			echo "</div>\n";

			echo "<p style=\"display:block; clear:both;\"></p>\n";
		// Andere Posts
		}else{
			while ( have_posts() ) : the_post();
				get_template_part( 'content', get_post_format() );
			endwhile;
		}
	?>
	<?php echo getDocsNav(); //vantage_content_nav( 'nav-below' ); ?>

<?php else : ?>

	<?php 
		//if('documents' === get_post_type($post->ID)){ 
			echo "<p>"._x('No Stories found','Collection-Filter no results headline','transcribathon')."</p>\n";
		/* }else{
			echo $post->ID;
			get_template_part( 'no-results', 'index' ); 
		}
 */		?>
<?php endif; ?>