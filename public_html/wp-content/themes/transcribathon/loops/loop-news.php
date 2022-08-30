<?php
/**
 * Loop Name: Blog
 */
?>
<?php if ( have_posts() ) : ?>

	<?php /* Start the Loop */ ?>
    <?php vantage_content_nav( 'nav-below' ); ?>
    <?php 
		// Doc-Uebersicht
		if('news' === get_post_type($post->ID)){ 
			//echo "<ul class=\"tct-featureboxes smallfont\">\n"; 
			echo "<div id=\"doc-results\">\n";
			echo "<div class=\"tableholder\">\n";
				echo "<div class=\"tablegrid\">\n";
					echo "<div class=\"section group sepgroup tab\">\n";
						$i=0;
						while ( have_posts() ) : the_post(); 
							//if(isset($_GET['st']) && $_GET['st'] != ""){
								if($i<4){ $i++; }else{ $i=1; echo "</div>\n<div class=\"section group tab sepgroup\">\n"; }
								get_template_part( 'newsmeldung', get_post_format() );
								if($i==2){ echo "<span class=\"sep\"></span>\n";}
							//}
						endwhile;
						if($i<4){
							for($x=$i; $x<4; $x++){
								echo "<div class=\"col span_1_of_4 newsarchiv empty\"></div>\n"; 
							}
							
						}
						
						
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
	<?php vantage_content_nav( 'nav-below' ); ?>

<?php else : ?>

	<?php 
		//if('documents' === get_post_type($post->ID)){ 
			echo "<p>"._x('No news in the moment','News: no results headline','transcribathon')."</p>\n";
		/* }else{
			echo $post->ID;
			get_template_part( 'no-results', 'index' ); 
		}
 */		?>
<?php endif; ?>