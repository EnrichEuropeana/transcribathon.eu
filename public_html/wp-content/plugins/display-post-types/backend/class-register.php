<?php
/**
 * The back-end specific functionality of the plugin.
 *
 * @package Display_Post_Types
 * @since 1.0.0
 */

namespace Display_Post_Types\Backend;

use Display_Post_Types\Backend\Inc\Block;
use Display_Post_Types\Backend\Inc\Shortcode;

/**
 * The back-end specific functionality of the plugin.
 *
 * @since 1.0.0
 */
class Register {

	/**
	 * Holds the instance of this class.
	 *
	 * @since  1.0.0
	 * @access protected
	 * @var    object
	 */
	protected static $instance = null;

	/**
	 * Constructor method.
	 *
	 * @since  1.0.0
	 */
	public function __construct() {}

	/**
	 * Register hooked functions.
	 *
	 * @since 1.0.0
	 */
	public static function init() {
		$inst      = self::get_instance();
		$block     = Block::init();
		$shortcode = Shortcode::init();
		add_action( 'widgets_init', array( $inst, 'register_custom_widget' ) );
		add_action( 'admin_enqueue_scripts', array( $inst, 'enqueue_admin_widgets' ) );

		if (
			in_array(
				'elementor/elementor.php',
				apply_filters( 'active_plugins', get_option( 'active_plugins' ) ),
				true
			)
		) {
			add_action(
				'elementor/editor/before_enqueue_scripts',
				array( $inst, 'enqueue_admin' )
			);
		}
	}

	/**
	 * Register the custom Widget.
	 *
	 * @since 1.0.0
	 */
	public function register_custom_widget() {
		require_once DISPLAY_POST_TYPES_DIR . '/backend/inc/class-widget.php';
		register_widget( 'Display_Post_Types\Backend\Inc\Widget' );
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_admin_widgets() {
		$screen = get_current_screen();
		if ( in_array( $screen->id, array( 'widgets', 'customize' ), true ) ) {
			$this->enqueue_admin();
		}
	}

	/**
	 * Register the Scripts and styles for the admin area.
	 *
	 * @since    1.8.0
	 */
	public function enqueue_admin() {
		wp_enqueue_script(
			'dpt_widget_admin_js',
			plugin_dir_url( __FILE__ ) . 'js/widgets.build.js',
			array( 'jquery' ),
			DISPLAY_POST_TYPES_VERSION,
			true
		);

		wp_enqueue_style(
			'dpt_widget_admin_style',
			plugin_dir_url( __FILE__ ) . 'css/widgets.css',
			array(),
			DISPLAY_POST_TYPES_VERSION,
			'all'
		);
	}

	/**
	 * Returns the instance of this class.
	 *
	 * @since  1.0.0
	 *
	 * @return object Instance of this class.
	 */
	public static function get_instance() {

		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}
}
