<?php
/**
 * The front end specific functionality of the plugin.
 *
 * @package Display_Post_Types
 * @since 2.0.0
 */

namespace Display_Post_Types\Frontend\Inc;

use Display_Post_Types\Frontend\Inc\Instance_Counter;

/**
 * The front-end specific functionality of the plugin.
 *
 * @since 2.0.0
 */
class Loader {

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
	 * @since  2.0.0
	 */
	public function __construct() {}

	/**
	 * Check if DPT display class is instantiated.
	 *
	 * @since 2.0.0
	 */
	public function has_dpt() {
		// Always load scripts on customizer preview screen.
		if ( is_customize_preview() ) {
			return true;
		}

		$dpt = Instance_Counter::get_instance();
		return $dpt->has_dpt();
	}

	/**
	 * Check if DPT instance has slider style.
	 *
	 * @since 2.0.0
	 */
	public function has_slider() {
		// Always load scripts on customizer preview screen.
		if ( is_customize_preview() ) {
			return true;
		}

		$dpt = Instance_Counter::get_instance();
		return $dpt->has_slider();
	}

	/**
	 * Check if DPT instance has Masonry style.
	 *
	 * @since 2.0.0
	 */
	public function has_mason() {
		// Always load scripts on customizer preview screen.
		if ( is_customize_preview() ) {
			return true;
		}

		$dpt = Instance_Counter::get_instance();
		return $dpt->has_mason();
	}

	/**
	 * Register the frontend scripts.
	 *
	 * @since    2.0.0
	 */
	public function enqueue_scripts() {

		// Enqueue scripts and styles only if dpt is available on the page.
		if ( $this->has_dpt() ) {
			$this->enqueue_front_scripts();
		}
	}

	/**
	 * Register the frontend scripts and styles.
	 *
	 * @since    2.0.0
	 */
	public function enqueue_front_scripts() {
		$deps = array();

		// Load slider script only if there is at least one instance of DPT slider.
		if ( $this->has_slider() ) {
			$deps[] = 'dpt-flickity';
			wp_enqueue_script(
				'dpt-flickity',
				DISPLAY_POST_TYPES_URL . 'frontend/js/flickity.pkgd.min.js',
				array(),
				DISPLAY_POST_TYPES_VERSION,
				true
			);
		}

		// Load Mason script only if there is at least one instance of DPT masonry layout.
		if ( $this->has_mason() ) {
			$deps[] = 'dpt-bricklayer';
			wp_enqueue_script(
				'dpt-bricklayer',
				DISPLAY_POST_TYPES_URL . 'frontend/js/bricklayer.build.js',
				array(),
				DISPLAY_POST_TYPES_VERSION,
				true
			);
		}

		wp_enqueue_script(
			'dpt-scripts',
			DISPLAY_POST_TYPES_URL . 'frontend/js/scripts.build.js',
			$deps,
			DISPLAY_POST_TYPES_VERSION,
			true
		);

		wp_enqueue_style(
			'dpt-style',
			DISPLAY_POST_TYPES_URL . 'frontend/css/style.css',
			array(),
			DISPLAY_POST_TYPES_VERSION,
			'all'
		);
	}

	/**
	 * Register the frontend scripts and styles.
	 *
	 * @since    2.0.0
	 */
	public function enqueue_front() {
		wp_enqueue_script(
			'dpt-bricklayer',
			DISPLAY_POST_TYPES_URL . 'frontend/js/bricklayer.build.js',
			array(),
			DISPLAY_POST_TYPES_VERSION,
			true
		);

		wp_enqueue_script(
			'dpt-flickity',
			DISPLAY_POST_TYPES_URL . 'frontend/js/flickity.pkgd.min.js',
			array(),
			DISPLAY_POST_TYPES_VERSION,
			true
		);

		wp_enqueue_script(
			'dpt-scripts',
			DISPLAY_POST_TYPES_URL . 'frontend/js/scripts.build.js',
			array( 'dpt-bricklayer', 'dpt-flickity' ),
			DISPLAY_POST_TYPES_VERSION,
			true
		);

		wp_enqueue_style(
			'dpt-style',
			DISPLAY_POST_TYPES_URL . 'frontend/css/style.css',
			array(),
			DISPLAY_POST_TYPES_VERSION,
			'all'
		);
	}

	/**
	 * Returns the instance of this class.
	 *
	 * @since  2.0.0
	 *
	 * @return object Instance of this class.
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}
}
