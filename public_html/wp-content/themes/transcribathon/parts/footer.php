<?php
/**
 * Part Name: Default Footer
 */
?>
<footer id="colophon" class="site-footer" role="contentinfo">

	<?php if( ! siteorigin_page_setting( 'hide_footer_widgets', false ) ) : ?>
		<div id="footer-widgets" class="full-container">
			<?php dynamic_sidebar( 'sidebar-footer' ) ?>
		</div><!-- #footer-widgets -->
	<?php endif; ?>

	<?php $site_info_text = apply_filters('vantage_site_info', siteorigin_setting('general_site_info_text') ); if( !empty($site_info_text) ) : ?>
		<div id="site-info">
			<?php echo wp_kses_post($site_info_text) ?>
		</div><!-- #site-info -->
	<?php endif; ?>

	<?php echo apply_filters( 'vantage_footer_attribution', '<div id="theme-attribution">' . sprintf( __('A <a href="%s">SiteOrigin</a> Theme', 'vantage'), 'https://siteorigin.com') . '</div>' ) ?>
	
</footer><!-- #colophon .site-footer -->

<footer class="_tct_footer">
	<span class="main-footer-bottom-left">
		<a href=<?php echo network_home_url()."terms-of-use/" ?> target= "_blank">Terms of Use</a>
		<a href=<?php echo network_home_url()."legal-disclosure/" ?> target= "_blank">Legal Disclosure - Impressum</a>
	</span>
	<strong class="main-footer-bottom-right">Developed by Facts & Files <?php echo date('Y')?>; Version: <?php echo wp_get_theme()->Version;?></strong>

</footer>