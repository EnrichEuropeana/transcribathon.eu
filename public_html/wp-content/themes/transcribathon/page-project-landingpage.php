<?php
/**
 * Template Name: Project Landingpage
 *
 * The template for project landingpages
 *
 * This is the template that contains the layout of a project landingpage.
 * Please note that this is the WordPress construct of pages and that
 * other "pages" on your WordPress site will use a different template.
 *
 * @package WordPress
 * @subpackage Transcribathon
 * @since Transcribathon 1.0
 */

get_header(); ?>

<div id="primary" class="content-area logo-status">
	<div id="content" class="site-content" role="main">
    

		<?php while ( have_posts() ) : the_post(); ?>

			<?php get_template_part( 'content', 'project-landingpage' ); ?>

			<?php if ( comments_open() || '0' != get_comments_number() ) : ?>
				<?php comments_template( '', true ); ?>
			<?php endif; ?>

		<?php endwhile; // end of the loop. ?>

	</div><!-- #content .site-content -->
</div><!-- #primary .content-area -->

        <script>
            // When the user scrolls down 60px from the top of the document, resize the navbar's padding 
            //and the logo's font size

            window.onscroll = function() {scrollFunction()};
                                
            function scrollFunction() {
                document.getElementById("_transcribathon_partnerlogo").style.height = "120px";
                document.getElementById("_transcribathon_partnerlogo").style.width = "120px";
                document.getElementById("_transcribathon_partnerlogo").style.marginLeft = "0px";
            }
        </script>
<?php get_footer(); ?>