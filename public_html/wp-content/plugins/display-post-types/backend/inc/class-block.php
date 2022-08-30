<?php
/**
 * Display post types block.
 *
 * @package Display_Post_Types
 * @since 1.0.0
 */

namespace Display_Post_Types\Backend\Inc;

use Display_Post_Types\Frontend\Inc\Display;

/**
 * The back-end specific functionality of the plugin.
 *
 * @since 1.0.0
 */
class Block {

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
		$inst = self::get_instance();
		add_action( 'init', array( $inst, 'register_block' ) );
		add_action( 'rest_api_init', array( $inst, 'register_routes' ) );
		add_action( 'enqueue_block_editor_assets', array( $inst, 'block_assets' ) );
	}

	/**
	 * Register editor blocks.
	 *
	 * @since 1.0.0
	 */
	public function register_block() {
		// Check if the register function exists.
		if ( ! function_exists( 'register_block_type' ) ) {
			return;
		}

		register_block_type(
			'dpt/display-post-types',
			array(
				'render_callback' => array( $this, 'render_dpt' ),
				'attributes'      => array(
					'postType'     => array(
						'type'    => 'string',
						'default' => 'post',
					),
					'taxonomy'     => array(
						'type'    => 'string',
						'default' => '',
					),
					'terms'        => array(
						'type'    => 'array',
						'items'   => array(
							'type' => 'string',
						),
						'default' => array(),
					),
					'relation'     => array(
						'type'    => 'string',
						'default' => 'IN',
					),
					'postIds'      => array(
						'type'    => 'string',
						'default' => '',
					),
					'pages'        => array(
						'type'    => 'array',
						'items'   => array(
							'type' => 'string',
						),
						'default' => array(),
					),
					'number'       => array(
						'type'    => 'number',
						'default' => 5,
					),
					'orderBy'      => array(
						'type'    => 'string',
						'default' => 'date',
					),
					'order'        => array(
						'type'    => 'string',
						'default' => 'DESC',
					),
					'styles'       => array(
						'type'    => 'string',
						'default' => 'dpt-grid1',
					),
					'styleSup'     => array(
						'type'    => 'array',
						'items'   => array(
							'type' => 'string',
						),
						'default' => array( 'thumbnail', 'title' ),
					),
					'imageCrop'    => array(
						'type'    => 'string',
						'default' => 'centercrop',
					),
					'imgAspect'    => array(
						'type'    => 'string',
						'default' => '',
					),
					'imgAlign'     => array(
						'type'    => 'string',
						'default' => '',
					),
					'brRadius'     => array(
						'type'    => 'number',
						'default' => 5,
					),
					'colNarr'      => array(
						'type'    => 'number',
						'default' => 3,
					),
					'autoTime'     => array(
						'type'    => 'number',
						'default' => 0,
					),
					'plHolder'     => array(
						'type'    => 'bool',
						'default' => true,
					),
					'showPgnation' => array(
						'type'    => 'bool',
						'default' => false,
					),
					'textAlign'    => array(
						'type'    => 'string',
						'default' => '',
					),
					'vGutter'      => array(
						'type'    => 'number',
						'default' => 20,
					),
					'hGutter'      => array(
						'type'    => 'number',
						'default' => 20,
					),
					'eLength'      => array(
						'type'    => 'number',
						'default' => 20,
					),
					'eTeaser'      => array(
						'type'    => 'string',
						'default' => '',
					),
					'className'    => array(
						'type' => 'string',
					),
					'offset'       => array(
						'type'    => 'number',
						'default' => 0,
					),
					'meta1'        => array(
						'type'    => 'string',
						'default' => '[author] &middot; [date]',
					),
					'meta2'        => array(
						'type'    => 'string',
						'default' => '[category]',
					),
				),
			)
		);
	}

	/**
	 * Render editor block for display posts.
	 *
	 * @since 1.0.0
	 *
	 * @param array $atts Display attributes.
	 */
	public function render_dpt( $atts ) {
		$classes = isset( $atts['className'] ) ? $atts['className'] : '';
		ob_start();
		Display::init(
			array(
				'post_type'     => $atts['postType'],
				'taxonomy'      => $atts['taxonomy'],
				'terms'         => $atts['terms'],
				'relation'      => $atts['relation'],
				'post_ids'      => $atts['postIds'],
				'pages'         => $atts['pages'],
				'number'        => $atts['number'],
				'orderby'       => $atts['orderBy'],
				'order'         => $atts['order'],
				'styles'        => $atts['styles'],
				'style_sup'     => $atts['styleSup'],
				'image_crop'    => $atts['imageCrop'],
				'img_aspect'    => $atts['imgAspect'],
				'img_align'     => $atts['imgAlign'],
				'br_radius'     => $atts['brRadius'],
				'col_narr'      => $atts['colNarr'],
				'autotime'      => $atts['autoTime'],
				'text_align'    => $atts['textAlign'],
				'v_gutter'      => $atts['vGutter'],
				'h_gutter'      => $atts['hGutter'],
				'e_length'      => $atts['eLength'],
				'e_teaser'      => $atts['eTeaser'],
				'meta1'         => $atts['meta1'],
				'meta2'         => $atts['meta2'],
				'classes'       => $classes,
				'offset'        => $atts['offset'],
				'pl_holder'     => ( 'false' === $atts['plHolder'] || ! $atts['plHolder'] ) ? '' : 'yes',
				'show_pgnation' => ( 'false' === $atts['showPgnation'] || ! $atts['showPgnation'] ) ? '' : 'yes',
			)
		);
		$content = ob_get_clean();
		return $content;
	}

	/**
	 * Create REST API endpoints to get all pages list.
	 *
	 * @since 1.0.0
	 */
	public function register_routes() {

		register_rest_route(
			'dpt/v1',
			'posttypes',
			array(
				'methods'             => 'GET',
				'callback'            => array( 'Display_Post_Types\Helper\Getters', 'post_types' ),
				'permission_callback' => function () {
					return current_user_can( 'edit_posts' );
				},
			)
		);

		register_rest_route(
			'dpt/v1',
			'pagelist',
			array(
				'methods'             => 'GET',
				'callback'            => array( 'Display_Post_Types\Helper\Getters', 'pagelist' ),
				'permission_callback' => function () {
					return current_user_can( 'edit_posts' );
				},
			)
		);

		register_rest_route(
			'dpt/v1',
			'stylelist',
			array(
				'methods'             => 'GET',
				'callback'            => array( 'Display_Post_Types\Helper\Getters', 'styles' ),
				'permission_callback' => function () {
					return current_user_can( 'edit_posts' );
				},
			)
		);

		register_rest_route(
			'dpt/v1',
			'/taxonomies/(?P<post_type>[\w-]+)',
			array(
				'methods'             => 'GET',
				'callback'            => array( 'Display_Post_Types\Helper\Getters', 'object_taxonomies' ),
				'permission_callback' => function () {
					return current_user_can( 'edit_posts' );
				},
				'args'                => array(
					'post_type' => array(
						'description' => esc_html__( 'Post Type', 'display-post-types' ),
						'type'        => 'string',
					),
				),
			)
		);

		register_rest_route(
			'dpt/v1',
			'/terms/(?P<taxonomy>[\w-]+)',
			array(
				'methods'             => 'GET',
				'callback'            => array( 'Display_Post_Types\Helper\Getters', 'terms' ),
				'permission_callback' => function () {
					return current_user_can( 'edit_posts' );
				},
				'args'                => array(
					'taxonomy' => array(
						'description' => esc_html__( 'Taxonomy', 'display-post-types' ),
						'type'        => 'string',
					),
				),
			)
		);
	}

	/**
	 * Register block assets.
	 *
	 * @since    1.0.0
	 */
	public function block_assets() {
		wp_enqueue_script(
			'dpt-flickity',
			plugins_url( '/frontend/js/flickity.pkgd.min.js', dirname( dirname( __FILE__ ) ) ),
			array(),
			DISPLAY_POST_TYPES_VERSION,
			true
		);

		wp_enqueue_script(
			'dpt-bricklayer',
			plugins_url( '/frontend/js/bricklayer.build.js', dirname( dirname( __FILE__ ) ) ),
			array(),
			DISPLAY_POST_TYPES_VERSION,
			true
		);

		wp_enqueue_script(
			'dpt-scripts',
			plugins_url( '/frontend/js/scripts.build.js', dirname( dirname( __FILE__ ) ) ),
			array( 'dpt-bricklayer', 'dpt-flickity' ),
			DISPLAY_POST_TYPES_VERSION,
			true
		);

		wp_enqueue_script(
			'dpt-blocks-js',
			plugins_url( '/js/blocks.build.js', dirname( __FILE__ ) ),
			array( 'wp-blocks', 'wp-i18n', 'wp-element', 'wp-components', 'wp-editor', 'wp-api-fetch', 'wp-compose' ),
			DISPLAY_POST_TYPES_VERSION,
			true
		);

		wp_enqueue_style(
			'dpt-blocks-css',
			plugins_url( '/css/blocks.css', dirname( __FILE__ ) ),
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

Block::init();
