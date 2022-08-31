<?php
/**
 * The template used for displaying page content in page.php
 *
 * @package vantage
 * @since vantage 1.0
 * @license GPL 2.0
 */
?>

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

	<div class="entry-main">
		<!-- page header, including meta-slider -->
		<?php do_action('vantage_entry_main_top') ?>

		<!-- bubbles -->
		<div class="entry-content bubbles">
			<!-- <hr class="hr-theme-bg theme-color-background"> -->
			<a class="bubble search theme-color-background" href="log-in"><i class="fal fa-user-circle"></i><span class="rwd-mobile-display">Search</span></a>
			<a class="bubble big transcribe-now theme-color-background" href="<?php echo get_europeana_url(); ?>/documents"><i class="far fa-pen-nib"></i><br />&nbsp;<br />&nbsp;<br />Transcribe <br />now</a>
			<a class="bubble help theme-color-background tutorial-model" href="http://europeana.transcribathon.eu.local/tutorial-english/" id="tutorial-mode"><i class="fal fa-question-circle"></i><span class="rwd-mobile-display">How to start</span></a>
		</div>

		<script>

		</script>
		<div class="entry-content">
			<?php the_content(); ?>
			<?php wp_link_pages( array( 'before' => '<div class="page-links">' . __( 'Pages:', 'vantage' ), 'after' => '</div>' ) ); ?>
		</div><!-- .entry-content -->

		<?php do_action('vantage_entry_main_bottom') ?>

	</div>

</article><!-- #post-<?php the_ID(); ?> -->
