<?php
/**
 * The premium page for the Easy Accordion Free
 *
 * @package Easy Accordion Free
 * @subpackage easy-accordion-free/admin
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}  // if direct access.

/**
 * The premium class for the Easy Accordion Free
 */
class Easy_Accordion_Premium {

	/**
	 * Single instance of the class
	 *
	 * @var null
	 * @since 2.0
	 */
	protected static $_instance = null;

	/**
	 * Main EASY_ACCORDION_PRO_HELP Instance
	 *
	 * @since 2.0
	 * @static
	 * @see sp_eap_premium()
	 * @return self Main instance
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	/**
	 * Add admin menu.
	 *
	 * @return void
	 */
	public function premium_admin_menu() {
		$landing_page = 'https://shapedplugin.com/easy-accordion/pricing/?ref=1';
		add_submenu_page(
			'edit.php?post_type=sp_easy_accordion',
			__( 'Easy Accordion Premium', 'easy-accordion-free' ),
			'<span class="sp-go-pro-icon"></span>Go Pro',
			'manage_options',
			$landing_page
		);
	}
}
