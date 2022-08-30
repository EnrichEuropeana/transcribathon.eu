<?php
/**
 * The front end specific functionality of the plugin.
 *
 * @package Display_Post_Types
 * @since 1.0.0
 */

namespace Display_Post_Types\Helper;

/**
 * The front-end specific functionality of the plugin.
 *
 * @since 1.0.0
 */
class Getters {

	/**
	 * Constructor method.
	 *
	 * @since  1.0.0
	 */
	public function __construct() {}

	/**
	 * Get list of all registered post types.
	 *
	 * @return array
	 */
	public static function post_types() {

		// Default Post and Pages post types.
		$default = array(
			'post' => esc_html__( 'Posts', 'display-post-types' ),
			'page' => esc_html__( 'Pages', 'display-post-types' ),
		);

		// Get the registered post types.
		$post_types = get_post_types(
			array(
				'public'   => true,
				'_builtin' => false,
			),
			'objects'
		);
		$post_types = wp_list_pluck( $post_types, 'label', 'name' );
		$post_types = array_merge( $default, $post_types );

		return $post_types;
	}

	/**
	 * Get list of taxonomies.
	 *
	 * @return array
	 */
	public static function taxonomies() {

		// Default taxonomies.
		$default = array(
			''         => esc_html__( 'Ignore Taxonomy', 'display-post-types' ),
			'category' => esc_html__( 'Categories', 'display-post-types' ),
			'post_tag' => esc_html__( 'Tags', 'display-post-types' ),
		);

		// Get list of all registered taxonomies.
		$taxonomies = get_taxonomies(
			array(
				'public'   => true,
				'_builtin' => false,
			),
			'objects'
		);

		// Get 'select' options as value => label.
		$options = wp_list_pluck( $taxonomies, 'label', 'name' );
		$options = array_merge( $default, $options );

		return $options;
	}

	/**
	 * Get list of taxonomies.
	 *
	 * @param  WP_REST_Request $request Request data.
	 *
	 * @return array
	 */
	public static function object_taxonomies( $request ) {

		$taxs = array();
		if ( isset( $request['post_type'] ) ) {
			// Get list of all registered taxonomies.
			$taxs = get_object_taxonomies( sanitize_text_field( $request['post_type'] ), 'objects' );
		}

		if ( empty( $taxs ) ) {
			return $taxs;
		}

		// Get 'select' options as value => label.
		$taxonomies = wp_list_pluck( $taxs, 'label', 'name' );

		if ( isset( $taxonomies['post_format'] ) ) {
			unset( $taxonomies['post_format'] );
		}

		$taxonomies[''] = esc_html__( '- Ignore Taxonomy -', 'display-post-types' );

		return $taxonomies;
	}

	/**
	 * Get list of taxonomies.
	 *
	 * @return array
	 */
	public static function pagelist() {

		// Get list of all pages.
		$pages = get_pages( array( 'exclude' => get_option( 'page_for_posts' ) ) );
		$pages = wp_list_pluck( $pages, 'post_title', 'ID' );

		return $pages;
	}

	/**
	 * Get list of taxonomies.
	 *
	 * @param  WP_REST_Request $request Request data.
	 *
	 * @return array
	 */
	public static function terms( $request ) {

		$terms = array();
		if ( isset( $request['taxonomy'] ) ) {
			$terms = get_terms(
				array(
					'taxonomy'   => sanitize_text_field( $request['taxonomy'] ),
					'hide_empty' => true,
				)
			);
			if ( is_wp_error( $terms ) ) {
				$terms = array();
			}
		}

		if ( empty( $terms ) ) {
			return $terms;
		}

		// Get 'select' options as value => label.
		$termlist = wp_list_pluck( $terms, 'name', 'slug' );
		return $termlist;
	}

	/**
	 * Get options default values.
	 *
	 * @return array
	 */
	public static function defaults() {

		return array(
			'post_type'     => '',
			'taxonomy'      => '',
			'terms'         => array(),
			'relation'      => 'IN',
			'post_ids'      => '',
			'pages'         => array(),
			'number'        => 5,
			'orderby'       => 'date',
			'order'         => 'DESC',
			'styles'        => 'dpt-grid1',
			'style_sup'     => array( 'thumbnail', 'title' ),
			'image_crop'    => 'centercrop',
			'img_aspect'    => '',
			'img_align'     => '',
			'br_radius'     => 5,
			'col_narr'      => 3,
			'pl_holder'     => 'yes',
			'show_pgnation' => '',
			'text_align'    => '',
			'v_gutter'      => 20,
			'h_gutter'      => 20,
			'e_length'      => 20,
			'e_teaser'      => '',
			'classes'       => '',
			'offset'        => 0,
			'autotime'      => 0,
			'meta1'         => '[author] &middot; [date]',
			'meta2'         => '[category]',
		);
	}

	/**
	 * Register widget display styles.
	 *
	 * @return array Array of supported display styles.
	 */
	public static function styles() {
		return apply_filters(
			'dpt_styles',
			array(
				'dpt-list1'   => array(
					'label'   => esc_html__( 'List - Full', 'display-post-types' ),
					'support' => array( 'thumbnail', 'title', 'meta', 'category', 'excerpt', 'ialign' ),
				),
				'dpt-list2'   => array(
					'label'   => esc_html__( 'List - Mini', 'display-post-types' ),
					'support' => array( 'thumbnail', 'title', 'meta', 'category', 'ialign' ),
				),
				'dpt-grid1'   => array(
					'label'   => esc_html__( 'Grid - Normal', 'display-post-types' ),
					'support' => array( 'thumbnail', 'title', 'meta', 'category', 'excerpt', 'multicol' ),
				),
				'dpt-grid2'   => array(
					'label'   => esc_html__( 'Grid - Overlay', 'display-post-types' ),
					'support' => array( 'thumbnail', 'title', 'meta', 'category', 'multicol' ),
				),
				'dpt-slider1' => array(
					'label'   => esc_html__( 'Slider - Normal', 'display-post-types' ),
					'support' => array( 'thumbnail', 'title', 'meta', 'category', 'multicol', 'slider' ),
				),
			)
		);
	}
}
