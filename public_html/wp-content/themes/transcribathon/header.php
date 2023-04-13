<?php
/**
 * The Header for our theme.
 *
 * Displays all of the <head> section and everything up till <div id="main">
 *
 * @package vantage
 * @since vantage 1.0
 * @license GPL 2.0
 */
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>" />
	<meta http-equiv="X-UA-Compatible" content="IE=edge" />
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="description" content="Transcribing of historical documents">
	<link rel="profile" href="http://gmpg.org/xfn/11" />
	<link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>" />
	<link rel="shortcut icon" href="<?php echo get_stylesheet_directory_uri(); ?>/images/favicon.ico" />
	<link rel="apple-touch-icon-precomposed" href="<?php echo get_stylesheet_directory_uri(); ?>/images/apple-touch-icon-precomposed.png" />
	<link rel="apple-touch-icon-precomposed" sizes="72x72" href="<?php echo get_stylesheet_directory_uri(); ?>/images/apple-touch-icon-72x72-precomposed.png" />
	<link rel="apple-touch-icon-precomposed" sizes="114x114" href="<?php echo get_stylesheet_directory_uri(); ?>/images/apple-touch-icon-114x114-precomposed.png" />
	<link rel="apple-touch-icon-precomposed" sizes="144x144" href="<?php echo get_stylesheet_directory_uri(); ?>/images/apple-touch-icon-144x144-precomposed.png" />
	<!--<link rel="preload" href="<?php echo get_stylesheet_directory_uri(); ?>/fonts/dosis-v27-latin-regular.woff2" as="font" type="font/woff2" crossOrigin />-->
	<link rel="preload" href="<?php echo get_stylesheet_directory_uri(); ?>/fonts/dosis-v27-latin-700.woff2" as="font" type="font/woff2" crossOrigin />
	<link rel="preload" href="<?php echo get_stylesheet_directory_uri(); ?>/fonts/open-sans-v34-latin-regular.woff2" as="font" type="font/woff2" crossOrigin />
	<!--<link rel="preload" href="<?php echo get_stylesheet_directory_uri(); ?>/fonts/open-sans-v34-latin-700.woff2" as="font" type="font/woff2" crossOrigin />-->
	<link rel="preload" href="<?php echo get_stylesheet_directory_uri(); ?>/fonts/open-sans-v34-latin-italic.woff2" as="font" type="font/woff2" crossOrigin />
	<link rel="stylesheet" href="<?php echo get_stylesheet_directory_uri(); ?>/css/fonts.css" type="text/css" />
	<script>const TP_API_HOST = '<?php echo TP_API_HOST; ?>';</script>


	<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>

<?php do_action('vantage_before_page_wrapper') ?>

<div id="page-wrapper">

	<?php do_action( 'vantage_before_masthead' ); ?>

	<?php if( ! siteorigin_page_setting( 'hide_masthead', false ) ) : ?>

		<?php get_template_part( 'parts/masthead', apply_filters( 'vantage_masthead_type', siteorigin_setting( 'layout_masthead' ) ) ); ?>

	<?php endif; ?>

	<?php do_action( 'vantage_after_masthead' ); ?>

	<?php vantage_render_slider() ?>

	<?php do_action( 'vantage_before_main_container' ); ?>

	<div id="main" class="site-main">
		<div class="full-container">
			<?php do_action( 'vantage_main_top' ); ?>
