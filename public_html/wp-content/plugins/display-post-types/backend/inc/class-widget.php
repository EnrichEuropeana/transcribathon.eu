<?php
/**
 * Display post types widget.
 *
 * @package Display_Post_Types
 * @since 1.0.0
 */

namespace Display_Post_Types\Backend\Inc;

use Display_Post_Types\Helper\Getters as Get_Fn;
use Display_Post_Types\Frontend\Inc\Display;

/**
 * Display post types widget.
 *
 * @since 1.0.0
 *
 * @see WP_Widget
 */
class Widget extends \WP_Widget {

	/**
	 * Holds all registered post type objects.
	 *
	 * @since  1.0.0
	 * @access protected
	 * @var    array
	 */
	protected $post_types = array();

	/**
	 * Holds sort orderby options.
	 *
	 * @since  1.0.0
	 * @access protected
	 * @var    array
	 */
	protected $orderby = array();

	/**
	 * Holds image cropping options.
	 *
	 * @since  1.0.0
	 * @access protected
	 * @var    array
	 */
	protected $imagecrop = array();

	/**
	 * Holds image aspect ratio options.
	 *
	 * @since  1.0.0
	 * @access protected
	 * @var    array
	 */
	protected $aspectratio = array();

	/**
	 * Holds widget settings defaults, populated in constructor.
	 *
	 * @since  1.0.0
	 * @access protected
	 * @var array
	 */
	protected $defaults = array();

	/**
	 * Holds all display styles.
	 *
	 * @since  1.0.0
	 * @access protected
	 * @var array
	 */
	protected $styles = array();

	/**
	 * Holds all display contents.
	 *
	 * @since  1.0.0
	 * @access protected
	 * @var array
	 */
	protected $contents = array();

	/**
	 * Holds all display styles supported items.
	 *
	 * @since  1.0.0
	 * @access protected
	 * @var array
	 */
	protected $style_supported = array();

	/**
	 * Sets up a new Blank widget instance.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		// Set widget instance settings default values.
		$this->defaults          = Get_Fn::defaults();
		$this->defaults['title'] = '';

		// Set the options for orderby.
		$this->orderby = array(
			'date'          => esc_html__( 'Publish Date', 'display-post-types' ),
			'modified'      => esc_html__( 'Modified Date', 'display-post-types' ),
			'title'         => esc_html__( 'Title', 'display-post-types' ),
			'author'        => esc_html__( 'Author', 'display-post-types' ),
			'comment_count' => esc_html__( 'Comment Count', 'display-post-types' ),
			'rand'          => esc_html__( 'Random', 'display-post-types' ),
		);

		$this->imagecrop = array(
			'topleftcrop'      => esc_html__( 'Top Left Cropping', 'display-post-types' ),
			'topcentercrop'    => esc_html__( 'Top Center Cropping', 'display-post-types' ),
			'centercrop'       => esc_html__( 'Center Cropping', 'display-post-types' ),
			'bottomleftcrop'   => esc_html__( 'Bottom Left Cropping', 'display-post-types' ),
			'bottomcentercrop' => esc_html__( 'Bottom Center Cropping', 'display-post-types' ),
		);

		$this->aspectratio = array(
			''       => esc_html__( 'No Cropping', 'display-post-types' ),
			'land1'  => esc_html__( 'Landscape (4:3)', 'display-post-types' ),
			'land2'  => esc_html__( 'Landscape (3:2)', 'display-post-types' ),
			'port1'  => esc_html__( 'Portrait (3:4)', 'display-post-types' ),
			'port2'  => esc_html__( 'Portrait (2:3)', 'display-post-types' ),
			'wdscrn' => esc_html__( 'Widescreen (16:9)', 'display-post-types' ),
			'squr'   => esc_html__( 'Square (1:1)', 'display-post-types' ),
		);

		// Get list of all registered supported contents.
		$this->contents = array(
			'thumbnail' => esc_html__( 'Thumbnail', 'display-post-types' ),
			'title'     => esc_html__( 'Title', 'display-post-types' ),
			'meta'      => esc_html__( 'Meta Info 1', 'display-post-types' ),
			'category'  => esc_html__( 'Meta Info 2', 'display-post-types' ),
			'excerpt'   => esc_html__( 'Excerpt', 'display-post-types' ),
			'date'      => esc_html__( 'Date', 'display-post-types' ),
			'ago'       => esc_html__( 'Ago', 'display-post-types' ),
			'author'    => esc_html__( 'Author', 'display-post-types' ),
			'content'   => esc_html__( 'Content', 'display-post-types' ),
		);

		// Set the widget options.
		$widget_ops = array(
			'classname'                   => 'display_posts_types',
			'description'                 => esc_html__( 'Create a display post types widget.', 'display-post-types' ),
			'customize_selective_refresh' => true,
		);
		parent::__construct( 'dpt_display_post_types', esc_html__( 'Display Post Types', 'display-post-types' ), $widget_ops );
	}

	/**
	 * Outputs the content for the current widget instance.
	 *
	 * @since 2.8.0
	 *
	 * @param array $args     Display arguments including 'before_title', 'after_title',
	 *                        'before_widget', and 'after_widget'.
	 * @param array $instance Settings for the current widget instance.
	 */
	public function widget( $args, $instance ) {

		$args['widget_id'] = isset( $args['widget_id'] ) ? $args['widget_id'] : $this->id;

		// Merge with defaults.
		$instance = wp_parse_args( (array) $instance, $this->defaults );

		$title = ! empty( $instance['title'] ) ? $instance['title'] : '';

		/** This filter is documented in wp-includes/widgets/class-wp-widget-pages.php */
		$title = apply_filters( 'widget_title', $title, $instance, $this->id_base );

		echo $args['before_widget']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

		if ( $title ) {
			echo $args['before_title'] . $title . $args['after_title']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}

		Display::init( $instance );

		echo $args['after_widget']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 * Get array of all widget options.
	 *
	 * @param array $instance Array of settings for current widget instance.
	 *
	 * @since 1.0.0
	 */
	public function get_widget_options( $instance ) {
		$widget    = $this;
		$post_type = Get_Fn::post_types();
		$post_type = array_merge( array( '' => esc_html__( 'None', 'display-post-types' ) ), $post_type );

		return apply_filters(
			'dpt_widget_options',
			array(
				'default' => array(
					'title'       => esc_html__( 'General Options', 'display-post-types' ),
					'op_callback' => function() use ( $widget, $instance ) {
						return '' !== $instance['post_type'];
					},
					'items'       => array(
						'title'     => array(
							'setting' => 'title',
							'label'   => esc_html__( 'Title', 'display-post-types' ),
							'type'    => 'text',
						),
						'post_type' => array(
							'setting' => 'post_type',
							'label'   => esc_html__( 'Select a Post Type', 'display-post-types' ),
							'type'    => 'select',
							'choices' => $post_type,
						),
					),
				),
				'fetch'   => array(
					'title'       => esc_html__( 'Get items to be displayed', 'display-post-types' ),
					'op_callback' => function() use ( $widget, $instance ) {
						return '' !== $instance['post_type'];
					},
					'items'       => array(
						'pages'         => array(
							'setting'       => 'pages',
							'type'          => 'pages',
							'hide_callback' => function() use ( $widget, $instance ) {
								return 'page' !== $instance['post_type'];
							},
							'wrapper'       => array(
								'id'    => 'filter',
								'type'  => 'card',
								'label' => esc_html__( 'Filter Items', 'display-post-types' ),
								'class' => 'dpt-filter-section',
							),
						),
						'taxonomy'      => array(
							'setting'       => 'taxonomy',
							'type'          => 'taxonomy',
							'hide_callback' => function() use ( $widget, $instance ) {
								return 'page' === $instance['post_type'];
							},
							'wrapper'       => array(
								'id'    => 'filter',
								'type'  => 'card',
								'label' => esc_html__( 'Filter Items', 'display-post-types' ),
								'class' => 'dpt-filter-section',
							),
						),
						'terms'         => array(
							'setting'       => 'terms',
							'type'          => 'terms',
							'hide_callback' => function() use ( $widget, $instance ) {
								return 'page' === $instance['post_type'] || '' === $instance['taxonomy'];
							},
							'wrapper'       => array(
								'id'    => 'filter',
								'type'  => 'card',
								'label' => esc_html__( 'Filter Items', 'display-post-types' ),
								'class' => 'dpt-filter-section',
							),
						),
						'relation'      => array(
							'setting'       => 'relation',
							'label'         => esc_html__( 'Terms Relationship', 'display-post-types' ),
							'type'          => 'select',
							'choices'       => array(
								'IN'  => esc_html__( 'OR - Show posts belong to any of the terms selected above.', 'display-post-types' ),
								'AND' => esc_html__( 'AND - Show posts only if they belong to all of the selected terms.', 'display-post-types' ),
							),
							'hide_callback' => function() use ( $widget, $instance ) {
								return 'page' === $instance['post_type'] || '' === $instance['taxonomy'];
							},
							'wrapper'       => array(
								'id'    => 'filter',
								'type'  => 'card',
								'label' => esc_html__( 'Filter Items', 'display-post-types' ),
								'class' => 'dpt-filter-section',
							),
						),
						'post_ids'      => array(
							'setting'       => 'post_ids',
							'label'         => esc_html__( 'Get items by Post IDs', 'display-post-types' ),
							'type'          => 'text',
							'input_attrs'   => array(
								'placeholder' => esc_attr_x( 'Comma separated ids, i.e. 230,300', 'Placeholder text for post ids', 'display-post-types' ),
							),
							'hide_callback' => function() use ( $widget, $instance ) {
								return 'page' === $instance['post_type'];
							},
							'wrapper'       => array(
								'id'    => 'filter',
								'type'  => 'card',
								'label' => esc_html__( 'Filter Items', 'display-post-types' ),
								'class' => 'dpt-filter-section',
							),
						),
						'orderby'       => array(
							'setting'       => 'orderby',
							'label'         => esc_html__( 'Order By', 'display-post-types' ),
							'type'          => 'select',
							'choices'       => $this->orderby,
							'hide_callback' => function() use ( $widget, $instance ) {
								return 'page' === $instance['post_type'];
							},
							'wrapper'       => array(
								'id'    => 'sort',
								'type'  => 'card',
								'label' => esc_html__( 'Sort Items', 'display-post-types' ),
								'class' => 'dpt-sort-section',
							),
						),
						'order'         => array(
							'setting'       => 'order',
							'label'         => esc_html__( 'Sort Order', 'display-post-types' ),
							'type'          => 'select',
							'choices'       => array(
								'DESC' => esc_html__( 'Descending', 'display-post-types' ),
								'ASC'  => esc_html__( 'Ascending', 'display-post-types' ),
							),
							'hide_callback' => function() use ( $widget, $instance ) {
								return 'page' === $instance['post_type'];
							},
							'wrapper'       => array(
								'id'    => 'sort',
								'type'  => 'card',
								'label' => esc_html__( 'Sort Items', 'display-post-types' ),
								'class' => 'dpt-sort-section',
							),
						),
						'number'        => array(
							'setting'     => 'number',
							'label'       => esc_html__( 'Number of items to display', 'display-post-types' ),
							'type'        => 'number',
							'input_attrs' => array(
								'step' => 1,
								'min'  => 1,
								'size' => 3,
							),
							'wrapper'     => array(
								'id'    => 'display',
								'type'  => 'card',
								'label' => esc_html__( 'Display Settings', 'display-post-types' ),
								'class' => 'dpt-display-settings-section',
							),
						),
						'offset'        => array(
							'setting'       => 'offset',
							'label'         => esc_html__( 'Offset (number of posts to displace)', 'display-post-types' ),
							'type'          => 'number',
							'input_attrs'   => array(
								'step' => 1,
								'min'  => 0,
								'size' => 3,
							),
							'hide_callback' => function() use ( $widget, $instance ) {
								return 'page' === $instance['post_type'];
							},
							'wrapper'       => array(
								'id'    => 'display',
								'type'  => 'card',
								'label' => esc_html__( 'Display Settings', 'display-post-types' ),
								'class' => 'dpt-display-settings-section',
							),
						),
						'show_pgnation' => array(
							'setting'       => 'show_pgnation',
							'label'         => esc_html__( 'Show Pagination', 'display-post-types' ),
							'type'          => 'checkbox',
							'hide_callback' => function() use ( $widget, $instance ) {
								return $widget->is_style_support( $instance['styles'], 'slider' );
							},
							'wrapper'       => array(
								'id'    => 'display',
								'type'  => 'card',
								'label' => esc_html__( 'Display Settings', 'display-post-types' ),
								'class' => 'dpt-display-settings-section',
							),
						),
					),
				),
				'layout'  => array(
					'title'       => esc_html__( 'Setup Items Layout', 'display-post-types' ),
					'op_callback' => function() use ( $widget, $instance ) {
						return '' !== $instance['post_type'];
					},
					'items'       => array(
						'styles'   => array(
							'setting' => 'styles',
							'label'   => esc_html__( 'Display Style', 'display-post-types' ),
							'type'    => 'select',
							'choices' => $this->get_display_styles(),
						),
						'col_narr' => array(
							'setting'       => 'col_narr',
							'label'         => esc_html__( 'Number of grid columns', 'display-post-types' ),
							'type'          => 'number',
							'input_attrs'   => array(
								'step' => 1,
								'min'  => 1,
								'max'  => 8,
								'size' => 1,
							),
							'hide_callback' => function() use ( $widget, $instance ) {
								return ! $widget->is_style_support( $instance['styles'], 'multicol' );
							},
						),
						'h_gutter' => array(
							'setting'     => 'h_gutter',
							'label'       => esc_html__( 'Horizontal Gutter (in px)', 'display-post-types' ),
							'type'        => 'number',
							'input_attrs' => array(
								'step' => 1,
								'min'  => 0,
								'size' => 3,
							),
						),
						'v_gutter' => array(
							'setting'     => 'v_gutter',
							'label'       => esc_html__( 'Vertical Gutter (in px)', 'display-post-types' ),
							'type'        => 'number',
							'input_attrs' => array(
								'step' => 1,
								'min'  => 0,
								'size' => 3,
							),
						),
						'autotime' => array(
							'setting'       => 'autotime',
							'label'         => esc_html__( 'Auto slide timer (delay in ms)', 'display-post-types' ),
							'type'          => 'number',
							'input_attrs'   => array(
								'step' => 500,
								'min'  => 0,
								'max'  => 10000,
								'size' => 5,
							),
							'desc'          => esc_html__( 'Setting delay time to 0 will disable auto slide.', 'display-post-type' ),
							'hide_callback' => function() use ( $widget, $instance ) {
								return ! $widget->is_style_support( $instance['styles'], 'slider' );
							},
						),
					),
				),
				'style'   => array(
					'title'       => esc_html__( 'Manage Display Elements', 'display-post-types' ),
					'op_callback' => function() use ( $widget, $instance ) {
						return '' !== $instance['post_type'];
					},
					'items'       => array(
						'text_align'   => array(
							'setting' => 'text_align',
							'label'   => esc_html__( 'Text Align', 'display-post-types' ),
							'type'    => 'select',
							'choices' => array(
								''       => esc_html__( 'Left Align', 'display-post-types' ),
								'r-text' => esc_html__( 'Right Align', 'display-post-types' ),
								'c-text' => esc_html__( 'Center Align', 'display-post-types' ),
							),
							'wrapper' => array(
								'id'    => 'container',
								'type'  => 'toggle',
								'label' => esc_html__( 'Item Wrapper', 'display-post-types' ),
								'class' => 'dpt-wrapper-section',
							),
						),
						'br_radius'    => array(
							'setting'     => 'br_radius',
							'label'       => esc_html__( 'Border Radius (in px)', 'display-post-types' ),
							'type'        => 'number',
							'input_attrs' => array(
								'step' => 1,
								'min'  => 0,
								'size' => 3,
							),
							'wrapper'     => array(
								'id'    => 'container',
								'type'  => 'toggle',
								'label' => esc_html__( 'Item Wrapper', 'display-post-types' ),
								'class' => 'dpt-wrapper-section',
							),
						),
						'show_thumb'   => array(
							'setting'    => 'style_sup',
							'label'      => esc_html__( 'Show Thumbnail', 'display-post-types' ),
							'value'      => 'thumbnail',
							'is_checked' => function() use ( $instance ) {
								return in_array( 'thumbnail', $instance['style_sup'], true ) ? 'checked' : '';
							},
							'type'       => 'spcheckbox',
							'wrapper'    => array(
								'id'    => 'thumbnail',
								'type'  => 'toggle',
								'label' => esc_html__( 'Thumbnail', 'display-post-types' ),
								'class' => 'dpt-thumbnail-section',
							),
						),
						'pl_holder'    => array(
							'setting'       => 'pl_holder',
							'label'         => esc_html__( 'Thumbnail Placeholder.', 'display-post-types' ),
							'type'          => 'checkbox',
							'hide_callback' => function() use ( $widget, $instance ) {
								return ! in_array( 'thumbnail', $instance['style_sup'], true );
							},
							'wrapper'       => array(
								'id'    => 'thumbnail',
								'type'  => 'toggle',
								'label' => esc_html__( 'Thumbnail', 'display-post-types' ),
								'class' => 'dpt-thumbnail-section',
							),
						),
						'img_aspect'   => array(
							'setting'       => 'img_aspect',
							'label'         => esc_html__( 'Image Cropping', 'display-post-types' ),
							'type'          => 'select',
							'choices'       => $this->aspectratio,
							'hide_callback' => function() use ( $widget, $instance ) {
								return ! in_array( 'thumbnail', $instance['style_sup'], true );
							},
							'wrapper'       => array(
								'id'    => 'thumbnail',
								'type'  => 'toggle',
								'label' => esc_html__( 'Thumbnail', 'display-post-types' ),
								'class' => 'dpt-thumbnail-section',
							),
						),
						'image_crop'   => array(
							'setting'       => 'image_crop',
							'label'         => esc_html__( 'Image Cropping Position', 'display-post-types' ),
							'type'          => 'select',
							'choices'       => $this->imagecrop,
							'hide_callback' => function() use ( $widget, $instance ) {
								return '' === $instance['img_aspect'] || ! in_array( 'thumbnail', $instance['style_sup'], true );
							},
							'wrapper'       => array(
								'id'    => 'thumbnail',
								'type'  => 'toggle',
								'label' => esc_html__( 'Thumbnail', 'display-post-types' ),
								'class' => 'dpt-thumbnail-section',
							),
						),
						'img_align'    => array(
							'setting'       => 'img_align',
							'label'         => esc_html__( 'Image Alignment', 'display-post-types' ),
							'type'          => 'select',
							'choices'       => array(
								''      => esc_html__( 'Left Aligned', 'display-post-types' ),
								'right' => esc_html__( 'Right Aligned', 'display-post-types' ),
							),
							'hide_callback' => function() use ( $widget, $instance ) {
								return ! $widget->is_style_support( $instance['styles'], 'ialign' ) || ! in_array( 'thumbnail', $instance['style_sup'], true );
							},
							'wrapper'       => array(
								'id'    => 'thumbnail',
								'type'  => 'toggle',
								'label' => esc_html__( 'Thumbnail', 'display-post-types' ),
								'class' => 'dpt-thumbnail-section',
							),
						),
						'show_title'   => array(
							'setting'    => 'style_sup',
							'label'      => esc_html__( 'Show Title', 'display-post-types' ),
							'value'      => 'title',
							'is_checked' => function() use ( $instance ) {
								return in_array( 'title', $instance['style_sup'], true ) ? 'checked' : '';
							},
							'type'       => 'spcheckbox',
							'wrapper'    => array(
								'id'    => 'title',
								'type'  => 'toggle',
								'label' => esc_html__( 'Title', 'display-post-types' ),
								'class' => 'dpt-title-section',
							),
						),
						'show_excerpt' => array(
							'setting'    => 'style_sup',
							'label'      => esc_html__( 'Show Excerpt', 'display-post-types' ),
							'value'      => 'excerpt',
							'is_checked' => function() use ( $instance ) {
								return in_array( 'excerpt', $instance['style_sup'], true ) ? 'checked' : '';
							},
							'type'       => 'spcheckbox',
							'wrapper'       => array(
								'id'    => 'excerpt',
								'type'  => 'toggle',
								'label' => esc_html__( 'Excerpt', 'display-post-types' ),
								'class' => 'dpt-excerpt-section',
							),
						),
						'e_length'     => array(
							'setting'       => 'e_length',
							'label'         => esc_html__( 'Excerpt Length (in words)', 'display-post-types' ),
							'type'          => 'number',
							'input_attrs'   => array(
								'step' => 1,
								'min'  => 0,
								'size' => 3,
							),
							'hide_callback' => function() use ( $widget, $instance ) {
								return ! in_array( 'excerpt', $instance['style_sup'], true ) || ! $widget->is_style_support( $instance['styles'], 'excerpt' );
							},
							'wrapper'       => array(
								'id'    => 'excerpt',
								'type'  => 'toggle',
								'label' => esc_html__( 'Excerpt', 'display-post-types' ),
								'class' => 'dpt-excerpt-section',
							),
						),
						'e_teaser'     => array(
							'setting'       => 'e_teaser',
							'label'         => esc_html__( 'Excerpt Teaser Text', 'display-post-types' ),
							'type'          => 'text',
							'input_attrs'   => array(
								'placeholder' => esc_attr_x( 'i.e., Continue Reading, Read More', 'Placeholder text for excerpt teaser', 'display-post-types' ),
							),
							'hide_callback' => function() use ( $widget, $instance ) {
								return ! in_array( 'excerpt', $instance['style_sup'], true ) || ! $widget->is_style_support( $instance['styles'], 'excerpt' );
							},
							'wrapper'       => array(
								'id'    => 'excerpt',
								'type'  => 'toggle',
								'label' => esc_html__( 'Excerpt', 'display-post-types' ),
								'class' => 'dpt-excerpt-section',
							),
						),
						'show_meta1'   => array(
							'setting'    => 'style_sup',
							'label'      => esc_html__( 'Show Meta Info - 1', 'display-post-types' ),
							'value'      => 'meta',
							'is_checked' => function() use ( $instance ) {
								return in_array( 'meta', $instance['style_sup'], true ) ? 'checked' : '';
							},
							'type'       => 'spcheckbox',
							'wrapper'    => array(
								'id'    => 'meta',
								'type'  => 'toggle',
								'label' => esc_html__( 'Meta Info 1', 'display-post-types' ),
								'class' => 'dpt-meta1-section',
							),
						),
						'meta1'        => array(
							'setting'       => 'meta1',
							'label'         => esc_html__( 'Meta Info to be displayed', 'display-post-types' ),
							'type'          => 'text',
							'hide_callback' => function() use ( $widget, $instance ) {
								return ! in_array( 'meta', $instance['style_sup'], true );
							},
							'wrapper'       => array(
								'id'    => 'meta',
								'type'  => 'toggle',
								'label' => esc_html__( 'Meta Info 1', 'display-post-types' ),
								'class' => 'dpt-meta1-section',
							),
						),
						'show_meta2'   => array(
							'setting'    => 'style_sup',
							'label'      => esc_html__( 'Show Meta Info - 2', 'display-post-types' ),
							'value'      => 'category',
							'is_checked' => function() use ( $instance ) {
								return in_array( 'category', $instance['style_sup'], true ) ? 'checked' : '';
							},
							'type'       => 'spcheckbox',
							'wrapper'    => array(
								'id'    => 'category',
								'type'  => 'toggle',
								'label' => esc_html__( 'Meta Info 2', 'display-post-types' ),
								'class' => 'dpt-meta2-section',
							),
						),
						'meta2'        => array(
							'setting'       => 'meta2',
							'label'         => esc_html__( 'Meta Info to be displayed', 'display-post-types' ),
							'type'          => 'text',
							'hide_callback' => function() use ( $widget, $instance ) {
								return ! in_array( 'category', $instance['style_sup'], true );
							},
							'wrapper'       => array(
								'id'    => 'category',
								'type'  => 'toggle',
								'label' => esc_html__( 'Meta Info 2', 'display-post-types' ),
								'class' => 'dpt-meta2-section',
							),
						),
					),
				),
			),
			$widget,
			$instance
		);
	}

	/**
	 * Outputs the settings form for the widget.
	 *
	 * @since 1.0.0
	 *
	 * @param array $instance Current settings.
	 */
	public function form( $instance ) {
		// Merge with defaults.
		$instance = wp_parse_args( (array) $instance, $this->defaults );
		$options  = $this->get_widget_options( $instance );

		$default_markup = '';
		$options_markup = '';
		foreach ( $options as $option => $args ) {
			$items    = $args['items'];
			$showop   = isset( $args['op_callback'] ) && is_callable( $args['op_callback'] ) ? call_user_func( $args['op_callback'] ) : true;
			$markup   = array();
			$wrappers = array();
			foreach ( $items as $item => $attr ) {
				$dcall = isset( $attr['display_callback'] ) && is_callable( $attr['display_callback'] ) ? call_user_func( $attr['display_callback'] ) : true;
				if ( ! $dcall ) {
					continue;
				}

				$set   = $attr['setting'];
				$id    = esc_attr( $this->get_field_id( $set ) );
				$name  = esc_attr( $this->get_field_name( $set ) );
				$type  = $attr['type'];
				$label = isset( $attr['label'] ) ? $attr['label'] : '';
				$desc  = isset( $attr['desc'] ) ? $attr['desc'] : '';
				$iatt  = isset( $attr['input_attrs'] ) ? $attr['input_attrs'] : array();
				$hcal  = isset( $attr['hide_callback'] ) && is_callable( $attr['hide_callback'] ) ? call_user_func( $attr['hide_callback'] ) : false;
				$wrap  = isset( $attr['wrapper'] ) ? $attr['wrapper'] : false;

				if ( $wrap ) {
					$wrappers[ $wrap['id'] ] = $wrap;
				}

				$inputattr = '';
				foreach ( $iatt as $att => $val ) {
					$inputattr .= esc_html( $att ) . '="' . esc_attr( $val ) . '" ';
				}

				switch ( $type ) {
					case 'select':
						$optmar  = $this->label( $set, $label, false );
						$optmar .= $this->select( $set, $attr['choices'], $instance[ $set ], array(), false );
						break;
					case 'checkbox':
						$optmar  = sprintf( '<input name="%s" id="%s" type="checkbox" value="yes" %s />', $name, $id, checked( $instance[ $set ], 'yes', false ) );
						$optmar .= $this->label( $set, $label, false );
						break;
					case 'spcheckbox':
						$checked = isset( $attr['is_checked'] ) && is_callable( $attr['is_checked'] ) ? call_user_func( $attr['is_checked'] ) : '';
						$spid    = esc_attr( $this->get_field_id( $item ) );
						$optmar  = sprintf( '<input name="%1$s" id="%2$s" type="checkbox" value="%3$s" %4$s class="spcheckbox" />', $name . '[]', $spid, esc_attr( $attr['value'] ), $checked );
						$optmar .= $this->label( $spid, $label, false );
						break;
					case 'text':
						$optmar  = $this->label( $set, $label, false );
						$optmar .= sprintf( '<input class="widefat" name="%1$s" id="%2$s" type="text" value="%3$s" />', $name, $id, esc_attr( $instance[ $set ] ) );
						$optmar .= sprintf( '<div class="dpt-desc">%s</div>', $desc );
						break;
					case 'url':
						$optmar  = $this->label( $set, $label, false );
						$optmar .= sprintf( '<input class="widefat" name="%1$s" id="%2$s" type="url" value="%3$s" />', $name, $id, esc_attr( $instance[ $set ] ) );
						$optmar .= sprintf( '<div class="dpt-desc">%s</div>', $desc );
						break;
					case 'number':
						$optmar  = $this->label( $set, $label, false );
						$optmar .= sprintf( '<input class="widefat" name="%1$s" id="%2$s" type="number" value="%3$s" %4$s />', $name, $id, absint( $instance[ $set ] ), $inputattr );
						$optmar .= sprintf( '<div class="dpt-desc">%s</div>', $desc );
						break;
					case 'textarea':
						$optmar  = $this->label( $set, $label, false );
						$optmar .= sprintf( '<textarea class="widefat" name="%1$s" id="%2$s" %3$s >%4$s</textarea>', $name, $id, $inputattr, esc_attr( $instance[ $set ] ) );
						break;
					case 'color':
						$optmar  = $this->label( $set, $label, false );
						$optmar .= sprintf( '<input class="dpt-color-picker" name="%1$s" id="%2$s" type="text" value="%3$s" />', $name, $id, esc_attr( sanitize_hex_color( $instance[ $set ] ) ) );
						break;
					case 'taxonomy':
						$optmar = $this->taxonomies_select( $instance['post_type'], $instance['taxonomy'] );
						break;
					case 'terms':
						$optmar = $this->terms_checklist( $instance['taxonomy'], $instance['terms'] );
						break;
					case 'pages':
						$optmar = $this->pages_checklist( $instance['pages'] );
						break;
					case 'style_sup':
						$optmar = $this->supported_checklist( $instance['styles'], $instance['style_sup'] );
						break;
					default:
						$optmar = apply_filters( 'dpt_custom_option_field', false, $item, $attr, $this, $instance );
						break;
				}
				$style = $hcal ? 'style="display: none;"' : '';
				$op    = $optmar ? sprintf( '<div class="%1$s dpt-widget-option" %2$s>%3$s</div>', $set, $style, $optmar ) : '';
				if ( $op ) {
					if ( ! $wrap ) {
						$markup['general'][] = $op;
					} else {
						$markup[ $wrap['id'] ][] = $op;
					}
				}
			}
			$markup = $this->get_options_markup( $markup, $wrappers );
			if ( 'default' === $option ) {
				$default_markup = $markup;
			} else {
				$opstyle         = $showop ? '' : 'style="display: none;"';
				$section         = sprintf( '<a class="dpt-settings-toggle dpt-%1$s-toggle" %2$s>%3$s</a>', $option, $opstyle, $args['title'] );
				$section        .= sprintf( '<div class="dpt-settings-content dpt-%1$s-content">%2$s</div>', $option, $markup );
				$options_markup .= $section;
			}
		}
		printf( '%1$s<div class="dpt-options-wrapper">%2$s</div>', $default_markup, $options_markup ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 * Combine and properly format widget options markup.
	 *
	 * @param array $markup   Markup Array.
	 * @param array $wrappers Wrappers information.
	 */
	public function get_options_markup( $markup, $wrappers ) {
		$overall_markup = '';
		foreach ( $markup as $key => $m ) {
			$content = implode( '', $m );
			if ( 'general' === $key ) {
				$overall_markup .= $content;
			} elseif ( isset( $wrappers[ $key ] ) ) {
				$wrap  = $wrappers[ $key ];
				$label = $wrap['label'];
				$type  = $wrap['type'];
				$class = $wrap['class'];

				if ( 'toggle' === $type ) {
					$content = sprintf(
						'<a class="dpt-settings-toggle">%1$s</a><div class="dpt-settings-content %2$s">%3$s</div>',
						$label,
						$class,
						$content
					);
				}

				if ( 'card' === $type ) {
					$content = sprintf(
						'<div class="dpt-card"><div class="dpt-card-title">%1$s</div><div class="dpt-card-content %2$s">%3$s</div></div>',
						$label,
						$class,
						$content
					);
				}

				$overall_markup .= $content;
			}
		}
		return $overall_markup;
	}

	/**
	 * Handles updating the settings for the current widget instance.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param array $new_instance New settings for this instance as input by the user via
	 *                            WP_Widget::form().
	 * @param array $old_instance Old settings for this instance.
	 * @return array Updated settings to save.
	 */
	public function update( $new_instance, $old_instance ) {

		// Merge with defaults.
		$new_instance = wp_parse_args( (array) $new_instance, $this->defaults );

		$instance          = $old_instance;
		$instance['title'] = sanitize_text_field( $new_instance['title'] );

		$valid_post_types      = array_keys( Get_Fn::post_types() );
		$instance['post_type'] = in_array( $new_instance['post_type'], $valid_post_types, true ) ? $new_instance['post_type'] : '';

		if ( 'page' === $instance['post_type'] ) {
			// Get list of all pages.
			$pages       = get_pages( array( 'exclude' => get_option( 'page_for_posts' ) ) );
			$valid_pages = wp_list_pluck( $pages, 'ID' );

			$instance['pages']    = array_intersect( $new_instance['pages'], $valid_pages );
			$instance['taxonomy'] = array();
		} else {
			$instance['pages'] = array();
		}

		if ( $instance['post_type'] && 'page' !== $instance['post_type'] && $new_instance['post_ids'] ) {
			$post_ids             = array_map( 'absint', explode( ',', $new_instance['post_ids'] ) );
			$instance['post_ids'] = implode( ',', $post_ids );
		} else {
			$instance['post_ids'] = '';
		}

		if ( $instance['post_type'] && 'page' !== $instance['post_type'] && $new_instance['taxonomy'] ) {
			// Get list of all taxonomies for a post type.
			$taxonomies = get_object_taxonomies( $instance['post_type'], 'objects' );
			$taxonomies = wp_list_pluck( $taxonomies, 'label', 'name' );

			$instance['taxonomy'] = array_key_exists( $new_instance['taxonomy'], $taxonomies ) ? $new_instance['taxonomy'] : '';
		} else {
			$instance['taxonomy'] = '';
		}

		if ( $instance['taxonomy'] && $new_instance['terms'] ) {
			// Get list of all terms.
			$terms       = get_terms( array( 'taxonomy' => $instance['taxonomy'] ) );
			$terms       = wp_list_pluck( $terms, 'name', 'slug' );
			$valid_terms = array_keys( $terms );

			$instance['terms'] = array_intersect( $new_instance['terms'], $valid_terms );
		} else {
			$instance['terms'] = array();
		}

		$instance['relation'] = ( 'IN' === $new_instance['relation'] ) ? 'IN' : 'AND';
		$instance['number']   = absint( $new_instance['number'] );
		$instance['offset']   = absint( $new_instance['offset'] );
		$instance['orderby']  = ( array_key_exists( $new_instance['orderby'], $this->orderby ) ) ? $new_instance['orderby'] : 'date';

		$instance['order'] = ( 'DESC' === $new_instance['order'] ) ? 'DESC' : 'ASC';

		$instance['image_crop'] = ( array_key_exists( $new_instance['image_crop'], $this->imagecrop ) ) ? $new_instance['image_crop'] : 'centercrop';

		$instance['img_aspect'] = ( array_key_exists( $new_instance['img_aspect'], $this->aspectratio ) ) ? $new_instance['img_aspect'] : 'land1';

		$instance['img_align']  = ( 'right' === $new_instance['img_align'] ) ? 'right' : '';
		$instance['text_align'] = sanitize_text_field( $new_instance['text_align'] );

		$instance['e_length'] = absint( $new_instance['e_length'] );
		$instance['v_gutter'] = absint( $new_instance['v_gutter'] );
		$instance['h_gutter'] = absint( $new_instance['h_gutter'] );
		$instance['e_teaser'] = sanitize_text_field( $new_instance['e_teaser'] );

		$instance['br_radius'] = absint( $new_instance['br_radius'] );
		$instance['col_narr']  = absint( $new_instance['col_narr'] );
		$instance['autotime']  = absint( $new_instance['autotime'] );

		$valid_styles       = $this->get_display_styles();
		$instance['styles'] = array_key_exists( $new_instance['styles'], $valid_styles ) ? $new_instance['styles'] : '';

		if ( $instance['styles'] ) {
			$valid_style_sup       = array_keys( $this->contents );
			$instance['style_sup'] = array_intersect( $new_instance['style_sup'], $valid_style_sup );
		} else {
			$instance['style_sup'] = array();
		}

		$instance['meta1'] = sanitize_text_field( $new_instance['meta1'] );
		$instance['meta2'] = sanitize_text_field( $new_instance['meta2'] );

		$instance['pl_holder']     = 'yes' === $new_instance['pl_holder'] ? 'yes' : '';
		$instance['show_pgnation'] = 'yes' === $new_instance['show_pgnation'] ? 'yes' : '';

		return $instance;
	}

	/**
	 * Prints a checkbox list of all pages.
	 *
	 * @param array $selected_pages Checked pages.
	 */
	public function pages_checklist( $selected_pages ) {

		// Get list of all pages.
		$pages = get_pages( array( 'exclude' => get_option( 'page_for_posts' ) ) );
		$pages = wp_list_pluck( $pages, 'post_title', 'ID' );

		$markup  = $this->label( 'pages', esc_html__( 'Select Pages', 'display-post-types' ), false );
		$markup .= $this->mu_checkbox( 'pages', $pages, $selected_pages, array(), false );
		return $markup;
	}

	/**
	 * Prints a checkbox list of all terms for a taxonomy.
	 *
	 * @param str   $taxonomy       Selected Taxonomy.
	 * @param array $selected_terms Selected Terms.
	 */
	public function terms_checklist( $taxonomy, $selected_terms = array() ) {

		// Get list of all registered terms.
		$terms = get_terms();

		// Get 'checkbox' options as value => label.
		$options = wp_list_pluck( $terms, 'name', 'slug' );

		// Get HTML classes for checkbox options.
		$classes = wp_list_pluck( $terms, 'taxonomy', 'slug' );
		if ( $taxonomy ) {
			foreach ( $classes as $slug => $taxon ) {
				if ( $taxonomy !== $taxon ) {
					$classes[ $slug ] .= ' dpt-hidden';
				}
			}
		}

		// Terms Checkbox markup.
		$markup  = $this->label( 'terms', esc_html__( 'Select Terms', 'display-post-types' ), false );
		$markup .= $this->mu_checkbox( 'terms', $options, $selected_terms, $classes, false );
		return $markup;
	}

	/**
	 * Prints a checkbox list of all supported content for a style.
	 *
	 * @param str   $style    Selected Style.
	 * @param array $selected Selected content.
	 */
	public function supported_checklist( $style, $selected = array() ) {

		$classes = array();

		foreach ( $this->contents as $slug => $label ) {
			foreach ( $this->style_supported as $s => $supported ) {
				if ( in_array( $slug, $supported, true ) ) {
					$classes[ $slug ][] = $s;
				} elseif ( $s === $style ) {
					$classes[ $slug ][] = 'dpt-hidden';
				}
			}
			if ( 'category' === $slug ) {
				$classes[ $slug ][] = 'post-only';
			}
			$classes[ $slug ] = join( ' ', array_unique( $classes[ $slug ] ) );
		}

		// Terms Checkbox markup.
		$markup  = $this->label( 'style_sup', esc_html__( 'Items supported by display style', 'display-post-types' ), false );
		$markup .= $this->mu_checkbox( 'style_sup', $this->contents, $selected, $classes, false );
		return $markup;
	}

	/**
	 * Prints select list of all taxonomies for a post type.
	 *
	 * @param str   $post_type Selected post type.
	 * @param array $selected  Selected taxonomy in widget form.
	 */
	public function taxonomies_select( $post_type, $selected = array() ) {

		$options = Get_Fn::taxonomies();

		// Get HTML classes for select options.
		$taxonomies = get_taxonomies( array(), 'objects' );
		$classes    = wp_list_pluck( $taxonomies, 'object_type', 'name' );
		if ( $post_type && 'page' !== $post_type ) {
			foreach ( $classes as $name => $type ) {
				$type = (array) $type;
				if ( ! in_array( $post_type, $type, true ) ) {
					$type[]           = 'dpt-hidden';
					$classes[ $name ] = $type;
				}
			}
		}
		$classes[''] = 'always-visible';

		// Taxonomy Select markup.
		$markup  = $this->label( 'taxonomy', esc_html__( 'Get items by Taxonomy', 'display-post-types' ), false );
		$markup .= $this->select( 'taxonomy', $options, $selected, $classes, false );
		return $markup;
	}

	/**
	 * Markup for 'label' for widget input options.
	 *
	 * @param str  $for  Label for which ID.
	 * @param str  $text Label text.
	 * @param bool $echo Display or Return.
	 * @return void|string
	 */
	public function label( $for, $text, $echo = true ) {
		$label = sprintf( '<label for="%s">%s</label>', esc_attr( $this->get_field_id( $for ) ), esc_html( $text ) );
		if ( $echo ) {
			echo $label; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		} else {
			return $label;
		}
	}

	/**
	 * Markup for Select dropdown lists for widget options.
	 *
	 * @param str   $for      Select for which ID.
	 * @param array $options  Select options as 'value => label' pair.
	 * @param str   $selected selected option.
	 * @param array $classes  Options HTML classes.
	 * @param bool  $echo     Display or return.
	 * @return void|string
	 */
	public function select( $for, $options, $selected, $classes = array(), $echo = true ) {
		$select      = '';
		$final_class = '';
		foreach ( $options as $value => $label ) {
			if ( isset( $classes[ $value ] ) ) {
				$option_classes = (array) $classes[ $value ];
				$option_classes = array_map( 'esc_attr', $option_classes );
				$final_class    = 'class="' . join( ' ', $option_classes ) . '"';
			}
			$select .= sprintf( '<option value="%1$s" %2$s %3$s>%4$s</option>', esc_attr( $value ), $final_class, selected( $value, $selected, false ), esc_html( $label ) );
		}

		$select = sprintf(
			'<select id="%1$s" name="%2$s" class="dpt-%3$s widefat">%4$s</select>',
			esc_attr( $this->get_field_id( $for ) ),
			esc_attr( $this->get_field_name( $for ) ),
			esc_attr( str_replace( '_', '-', $for ) ),
			$select
		);

		if ( $echo ) {
			echo $select; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		} else {
			return $select;
		}
	}

	/**
	 * Markup for multiple checkbox for widget options.
	 *
	 * @param str   $for      Select for which ID.
	 * @param array $options  Select options as 'value => label' pair.
	 * @param str   $selected selected option.
	 * @param array $classes  Checkbox input HTML classes.
	 * @param bool  $echo     Display or return.
	 * @return void|string
	 */
	public function mu_checkbox( $for, $options, $selected, $classes = array(), $echo = true ) {

		$final_class = '';

		$mu_checkbox = '<div class="' . esc_attr( $for ) . '-checklist"><ul id="' . esc_attr( $this->get_field_id( $for ) ) . '">';

		$selected    = array_map( 'strval', $selected );
		$rev_options = $options;

		// Moving selected items on top of the array.
		foreach ( $options as $id => $label ) {
			if ( in_array( strval( $id ), $selected, true ) ) {
				$rev_options = array( $id => $label ) + $rev_options;
			}
		}

		foreach ( $rev_options as $id => $label ) {
			if ( isset( $classes[ $id ] ) ) {
				$final_class = ' class="' . esc_attr( $classes[ $id ] ) . '"';
			}
			$mu_checkbox .= "\n<li$final_class>" . '<label class="selectit"><input value="' . esc_attr( $id ) . '" type="checkbox" name="' . esc_attr( $this->get_field_name( $for ) ) . '[]"' . checked( in_array( strval( $id ), $selected, true ), true, false ) . ' /> ' . esc_html( $label ) . "</label></li>\n";
		}
		$mu_checkbox .= "</ul></div>\n";

		if ( $echo ) {
			echo $mu_checkbox; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		} else {
			return $mu_checkbox;
		}
	}

	/**
	 * Get display styles.
	 *
	 * @return array
	 */
	public function get_display_styles() {
		if ( ! empty( $this->styles ) ) {
			return $this->styles;
		}

		$styles = Get_Fn::styles();
		foreach ( $styles as $style => $args ) {
			$this->styles[ $style ]          = $args['label'];
			$this->style_supported[ $style ] = $args['support'];
		}

		return $this->styles;
	}

	/**
	 * Check if item is supported by the style.
	 *
	 * @param string $style Current display style.
	 * @param string $item  item to be checked for support.
	 * @return bool
	 */
	public function is_style_support( $style, $item ) {
		if ( ! $style ) {
			return false;
		}

		$sup_arr = $this->style_supported[ $style ];
		return in_array( $item, $sup_arr, true );
	}
}
