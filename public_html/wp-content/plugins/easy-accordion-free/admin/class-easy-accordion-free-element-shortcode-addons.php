<?php
/**
 * Elementor shortcode block.
 *
 * @since      2.1.6
 * @package     easy-accordion-free
 * @subpackage  easy-accordion-free/admin
 */

/**
 * Easy_Accordion_Free_Element_Shortcode_Addons
 */
class Easy_Accordion_Free_Element_Shortcode_Addons {
	/**
	 * Instance
	 *
	 * @since 2.1.6
	 *
	 * @access private
	 * @static
	 *
	 * @var Easy_Accordion_Free_Element_Shortcode_Addons The single instance of the class.
	 */
	private static $_instance = null;

	/**
	 * Instance
	 *
	 * Ensures only one instance of the class is loaded or can be loaded.
	 *
	 * @since 2.1.6
	 *
	 * @access public
	 * @static
	 *
	 * @return Elementor_Test_Extension An instance of the class.
	 */
	public static function instance() {

		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;

	}

	/**
	 * Constructor
	 *
	 * @since 2.1.6
	 *
	 * @access public
	 */
	public function __construct() {
		$this->on_plugins_loaded();
		add_action( 'wp_enqueue_scripts', array( $this, 'easy_accordion_free_addons_enqueue_scripts' ) );
		add_action( 'elementor/editor/before_enqueue_scripts', array( $this, 'easy_accordion_free_addons_icon' ) );
	}

	/**
	 * Elementor block icon.
	 *
	 * @since    2.1.6
	 * @return void
	 */
	public function easy_accordion_free_addons_icon() {
		wp_enqueue_style( 'easy_accordion_free_elementor_addons_icon', SP_EA_URL . 'admin/css/fontello.min.css', array(), SP_EA_VERSION, 'all' );
	}

	/**
	 * Register the JavaScript for the elementor block area.
	 *
	 * @since    2.1.6
	 */
	public function easy_accordion_free_addons_enqueue_scripts() {
		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in easy_accordion_free_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The easy_accordion_free_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		$prefix = defined( 'WP_DEBUG' ) && WP_DEBUG ? '' : '.min';
		wp_enqueue_script( 'sp-ea-accordion-js', esc_url( SP_EA_URL . 'public/assets/js/collapse' . $prefix . '.js' ), array( 'jquery' ), SP_EA_VERSION, false );
		wp_enqueue_script( 'sp-ea-accordion-config', esc_url( SP_EA_URL . 'public/assets/js/script.js' ), array( 'jquery', 'sp-ea-accordion-js' ), SP_EA_VERSION, true );
	}

	/**
	 * On Plugins Loaded
	 *
	 * Checks if Elementor has loaded, and performs some compatibility checks.
	 * If All checks pass, inits the plugin.
	 *
	 * Fired by `plugins_loaded` action hook.
	 *
	 * @since 2.1.6
	 *
	 * @access public
	 */
	public function on_plugins_loaded() {
		add_action( 'elementor/init', array( $this, 'init' ) );
	}

	/**
	 * Initialize the plugin
	 *
	 * Load the plugin only after Elementor (and other plugins) are loaded.
	 * Load the files required to run the plugin.
	 *
	 * Fired by `plugins_loaded` action hook.
	 *
	 * @since 2.1.6
	 *
	 * @access public
	 */
	public function init() {
		// Add Plugin actions.
		add_action( 'elementor/widgets/register', array( $this, 'init_widgets' ) );
	}

	/**
	 * Init Widgets
	 *
	 * Include widgets files and register them
	 *
	 * @since 2.1.6
	 *
	 * @access public
	 */
	public function init_widgets() {
		// Register widget.
		require_once SP_EA_PATH . 'admin/ElementAddons/Sp_Easy_Accordion_Shortcode_Widget.php';
		\Elementor\Plugin::instance()->widgets_manager->register( new Sp_Easy_Accordion_Shortcode_Widget() );

	}

}

Easy_Accordion_Free_Element_Shortcode_Addons::instance();
