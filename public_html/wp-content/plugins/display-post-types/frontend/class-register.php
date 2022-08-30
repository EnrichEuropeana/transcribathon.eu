<?php
/**
 * The front end specific functionality of the plugin.
 *
 * @package Display_Post_Types
 * @since 1.0.0
 */

namespace Display_Post_Types\Frontend;

use Display_Post_Types\Helper\Getters as Get_Fn;
use Display_Post_Types\Frontend\Inc\Loader;
use Display_Post_Types\Helper\Markup;

/**
 * The front-end specific functionality of the plugin.
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
		$inst = self::get_instance();
		add_filter( 'dpt_wrapper_classes', array( $inst, 'wrapper_classes' ), 10, 2 );
		add_filter( 'dpt_html_attributes', array( $inst, 'html_attr' ), 10, 2 );
		add_filter( 'dpt_entry_classes', array( $inst, 'entry_classes' ), 10, 2 );
		add_action( 'dpt_entry', array( $inst, 'entry' ) );
		add_action( 'dpt_before_wrapper', array( $inst, 'inline_css' ), 10, 2 );
		add_filter( 'body_class', array( $inst, 'add_body_classes' ) );

		// Loader Instance.
		$loader = Loader::get_instance();

		// Load front end scripts.
		self::load_scripts( $loader );

		// Elementor Support.
		self::elementor_support( $loader );
	}

	/**
	 * Load front-end specific scripts for DPT.
	 *
	 * @since 2.0.0
	 *
	 * @param object $instance DPT script loader instance.
	 */
	public static function load_scripts( $instance ) {
		add_action( 'wp_footer', array( $instance, 'enqueue_scripts' ) );
	}

	/**
	 * Scripts to support elementor functionality in preview screen.
	 *
	 * @since 2.0.0
	 *
	 * @param object $instance DPT script loader instance.
	 */
	public static function elementor_support( $instance ) {
		if (
			in_array(
				'elementor/elementor.php',
				apply_filters( 'active_plugins', get_option( 'active_plugins' ) ),
				true
			)
		) {
			add_action(
				'elementor/preview/enqueue_scripts',
				array( $instance, 'enqueue_front' )
			);
		}
	}

	/**
	 * Display widget content to front-end.
	 *
	 * @param array $args Widget display arguments.
	 */
	public function entry( $args ) {
		$display = $this->get_display_map( $args['styles'] );
		$display = $this->filter_display_map( $display, $args );
		$this->render_entry( $display, $args );
	}

	/**
	 * Display widget content to front-end.
	 *
	 * @param array $items content to be displayed.
	 * @param array $args Widget display arguments.
	 */
	public function filter_display_map( $items, $args ) {
		if ( ! $args['style_sup'] ) {
			return array();
		}
		foreach ( $items as $key => $item ) {
			if ( is_array( $item ) ) {
				$items[ $key ] = $this->filter_display_map( $item, $args );
			} else {
				$unset = true;
				if ( in_array( $item, $args['style_sup'], true ) ) {
					$unset = false;
				} elseif ( false !== strpos( $item, 'thumbnail' ) ) {
					if ( in_array( 'thumbnail', $args['style_sup'], true ) ) {
						$unset = false;
					}
				}
				if ( $unset ) {
					unset( $items[ $key ] );
				}
			}
		}

		return $items;
	}

	/**
	 * Display widget content to front-end.
	 *
	 * @param array $instance display args.
	 * @param int   $id Instance ID.
	 */
	public function inline_css( $instance, $id ) {
		$args  = $instance['args'];
		$style = '.display-post-types {opacity: 0;}';
		if ( isset( $args['br_radius'] ) && $args['br_radius'] ) {
			$style .= sprintf(
				'
				#dpt-wrapper-%1$s .dpt-featured-content,
				#dpt-wrapper-%1$s a.dpt-permalink,
				#dpt-wrapper-%1$s .dpt-featured-content:after,
				#dpt-wrapper-%1$s .dpt-thumbnail img {
					border-radius: %2$spx;
				}
				',
				absint( $id ),
				absint( $args['br_radius'] )
			);
		}
		if ( isset( $args['h_gutter'] ) && 20 !== $args['h_gutter'] ) {
			$hgutter = absint( $args['h_gutter'] );
			$hghalf  = $hgutter / 2;
			$hghalfn = -1 * $hghalf;
			$style  .= sprintf(
				'
				#dpt-wrapper-%1$s.multi-col {
					margin-left: %2$spx;
					margin-right: %2$spx;
					width: calc( 100%% + %4$spx );
					max-width: 100vw;
				}
				#dpt-wrapper-%1$s.multi-col .dpt-entry {
					padding-left: %3$spx;
					padding-right: %3$spx;
				}
				#dpt-wrapper-%1$s .flickity-prev-next-button.previous {
					left: %3$spx;
				}
				#dpt-wrapper-%1$s .flickity-prev-next-button.next {
					right: %3$spx;
				}
				',
				absint( $id ),
				$hghalfn,
				$hghalf,
				$hgutter
			);
		}

		if ( isset( $args['v_gutter'] ) && 20 !== $args['v_gutter'] ) {
			$vgutter = absint( $args['v_gutter'] );
			$vghalf  = $vgutter / 2;
			$vghalfn = -1 * $vghalf;
			$style  .= sprintf(
				'
				#dpt-wrapper-%1$s {
					margin-top: %2$spx !important;
					margin-bottom: %2$spx;
				}
				#dpt-wrapper-%1$s .dpt-entry {
					padding-top: %3$spx;
					padding-bottom: %3$spx;
				}
				.hentry #dpt-wrapper-%1$s {
					margin-bottom: %3$spx;
				}
				',
				absint( $id ),
				$vghalfn,
				$vghalf,
				$vgutter
			);
		}
		if ( $style ) {
			?>
			<style type="text/css">
			<?php echo wp_strip_all_tags( $style, true ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</style>
			<?php
		}
	}

	/**
	 * Add wrapper classes.
	 *
	 * @param str   $classes  Comma separated wrapper classes.
	 * @param array $args Settings for the current instance.
	 *
	 * @return array Array of supported display styles.
	 */
	public function wrapper_classes( $classes, $args ) {
		$classes[] = $args['image_crop'];
		$classes[] = $args['img_aspect'];
		$classes[] = $args['text_align'];

		if ( $this->is_style_support( $args['styles'], 'multicol' ) ) {
			$classes[] = $args['col_narr'] ? 'col-nr-' . absint( $args['col_narr'] ) : '';

			if ( 1 <= $args['col_narr'] ) {
				$classes[] = 'multi-col';
			}
		}

		$wrap = 'dpt-flex-wrap';
		if ( '' !== $args['img_aspect'] ) {
			$classes[] = 'dpt-cropped';
		} elseif ( $this->is_style_support( $args['styles'], 'multicol' ) && 1 !== $args['col_narr'] ) {
			$wrap = 'dpt-mason-wrap';
		}

		if ( $this->is_style_support( $args['styles'], 'slider' ) ) {
			$classes[] = 'dpt-slider';
			$wrap      = '';
			if ( ! $args['img_aspect'] ) {
				$wrap = 'dpt-mason-slider';
			}
		}
		$classes[] = $wrap;

		if ( $this->is_style_support( $args['styles'], 'ialign' ) ) {
			if ( 'right' === $args['img_align'] ) {
				$classes[] = 'right-al';
			}
		}

		return array_filter( $classes );
	}

	/**
	 * Add html attributes to DPT wrapper.
	 *
	 * @param array $attr HTML attributes associative array.
	 * @param array $args Settings for the current instance.
	 */
	public function html_attr( $attr, $args ) {
		if ( isset( $args['autotime'] ) && $args['autotime'] ) {
			$attr['data-autotime'] = $args['autotime'];
		}

		return $attr;
	}

	/**
	 * Add entry classes.
	 *
	 * @param str   $classes  Comma separated entry posts classes.
	 * @param array $args Settings for the current instance.
	 *
	 * @return array Array of supported display styles.
	 */
	public function entry_classes( $classes, $args ) {
		if ( has_post_thumbnail() ) {
			$classes[] = 'has-thumbnail';
		} else {
			$classes[] = 'no-thumbnail';
		}

		return array_filter( $classes );
	}

	/**
	 * Get args for displaying elements for specific dp style.
	 *
	 * @param str $style Style for this widget instance.
	 * @return array
	 */
	public function get_display_map( $style ) {

		switch ( $style ) {
			case 'dpt-list1':
				$d = array( 'thumbnail-medium', array( 'meta', 'title', 'excerpt', 'category' ) );
				break;
			case 'dpt-list2':
				$d = array( 'thumbnail-medium', array( 'category', 'title', 'meta' ) );
				break;
			case 'dpt-grid1':
				$d = array( 'thumbnail-medium', array( 'meta', 'title', 'excerpt', 'category' ) );
				break;
			case 'dpt-grid2':
				$d = array( 'thumbnail-medium', array( 'meta', 'title', 'category' ) );
				break;
			case 'dpt-slider1':
				$d = array( 'thumbnail-large', array( 'meta', 'title', 'category' ) );
				break;
			default:
				$d = array();
		}

		return apply_filters( 'dpt_style_args', $d, $style );
	}

	/**
	 * Display entry content to front-end.
	 *
	 * @param array $items Content display arguments.
	 * @param str   $args  Current display post style.
	 */
	public function render_entry( $items, $args ) {
		foreach ( $items as $item ) {
			if ( is_array( $item ) ) {
				Markup::markup( 'sub-entry', array( array( array( $this, 'render_entry' ), $item, $args ) ) );
			} else {
				switch ( $item ) {
					case 'title':
						$this->title();
						break;
					case 'date':
						$this->date();
						break;
					case 'ago':
						$this->ago();
						break;
					case 'author':
						$this->author();
						break;
					case 'content':
						$this->content();
						break;
					case 'excerpt':
						$this->excerpt( $args );
						break;
					case 'category':
						$this->meta2( $args );
						break;
					case 'meta':
						$this->meta( $args );
						break;
					case 'thumbnail-small':
						$this->featured( 'thumbnail', $args );
						break;
					case 'thumbnail-medium':
						$this->featured( 'dpt-medium', $args );
						break;
					case 'thumbnail-large':
						$this->featured( 'dpt-large', $args );
						break;
					case 'no-thumb':
						$this->featured( false, $args );
						break;
					default:
						do_action( 'display_dpt_item', $item, $args );
						break;
				}
			}
		}
	}

	/**
	 * Display post entry title.
	 *
	 * @since 1.0.0
	 */
	public function title() {
		if ( get_the_title() ) {
			the_title(
				sprintf(
					'<h3 class="dpt-title"><a class="dpt-title-link" href="%s" rel="bookmark">',
					esc_url( get_permalink() )
				),
				'</a></h3>'
			);
		}
	}

	/**
	 * Display post entry date.
	 *
	 * @since 1.0.0
	 */
	public function date() {

		printf(
			'<div class="dpt-date"><time datetime="%s">%s</time></div>',
			esc_attr( get_the_date( DATE_W3C ) ),
			esc_html( get_the_date() )
		);
	}

	/**
	 * Display human readable post entry date.
	 *
	 * @since 1.0.0
	 */
	public function ago() {

		$time = sprintf(
			/* translators: %s: human-readable time difference */
			esc_html_x( '%s ago', 'human-readable time difference', 'display-post-types' ),
			esc_html( human_time_diff( get_the_time( 'U' ), current_time( 'timestamp' ) ) )
		);

		printf( '<div class="dpt-date">%s</div>', $time ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 * Display post entry author.
	 *
	 * @since 1.0.0
	 */
	public function author() {

		printf(
			'<div class="dpt-author"><a href="%s"><span>%s</span></a></div>',
			esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ),
			esc_html( get_the_author_meta( 'display_name' ) )
		);
	}

	/**
	 * Display post entry author.
	 *
	 * @since 1.0.0
	 */
	public function permalink() {

		printf(
			'<a href="%s" class="dpt-permalink"><span class="screen-reader-text">%s</span></a>',
			esc_url( get_permalink() ),
			the_title( '', '', false ) // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		);
	}

	/**
	 * Display post featured content.
	 *
	 * @since 1.0.0
	 *
	 * @param str   $size Thumbanil Size.
	 * @param array $args Current instance settings.
	 */
	public function featured( $size, $args ) {
		$style = $args['styles'];

		if ( has_post_thumbnail() || ( isset( $args['pl_holder'] ) && $args['pl_holder'] ) ) {

			Markup::markup(
				'dpt-featured-content',
				array(
					array( array( $this, 'permalink' ) ),
					array( array( $this, 'thumbnail' ), $size, $args ),
				)
			);
		}
	}

	/**
	 * Display post entry thumbnail.
	 *
	 * @since 1.0.0
	 *
	 * @param string $size Thumbanil Size.
	 * @param array  $args Current instance settings.
	 */
	public function thumbnail( $size = 'thumbnail', $args = array() ) {
		if ( ! has_post_thumbnail() ) {
			return;
		}

		$class   = '';
		$id      = get_post_thumbnail_id();
		$imgmeta = wp_get_attachment_metadata( $id );
		if ( isset( $imgmeta['width'] ) && isset( $imgmeta['height'] ) ) {
			if ( $imgmeta['width'] > $imgmeta['height'] ) {
				$class = 'landscape';
			} else {
				$class = 'portrait';
			}
		}

		echo '<div class="dpt-thumbnail ' . esc_attr( $class ) . '">';
		if ( isset( $args['img_aspect'] ) && '' === $args['img_aspect'] ) {
			the_post_thumbnail( $size, array( 'loading' => 'auto' ) );
		} else {
			the_post_thumbnail( $size );
		}
		echo '</div>';
	}

	/**
	 * Display post content.
	 *
	 * @since 1.0.0
	 */
	public function content() {
		echo '<div class="dpt-content">';
		the_content();
		echo '</div>';
	}

	/**
	 * Display post content.
	 *
	 * @since 1.0.0
	 *
	 * @param str $args Current display post args.
	 */
	public function excerpt( $args ) {

		// Short circuit filter.
		$check = apply_filters( 'dpt_excerpt', false, $args );
		if ( false !== $check ) {
			return;
		}

		$is_excerpt = false;

		if ( has_excerpt() ) {
			$text       = get_the_excerpt();
			$is_excerpt = true;
		} else {
			$text = get_the_content( '' );
		}

		$text = wp_strip_all_tags( strip_shortcodes( $text ) );
		$text = str_replace( ']]>', ']]&gt;', $text );

		$length = $args['e_length'] ? absint( $args['e_length'] ) : 20;
		/**
		 * Filters the number of words in an excerpt.
		 *
		 * @since 1.0.0
		 *
		 * @param int $number The number of words.
		 */
		$excerpt_length = apply_filters( 'dpt_excerpt_length', $length, $args );

		// Generate excerpt teaser text and link.
		$exrpt_url   = esc_url( get_permalink() );
		$exrpt_text  = esc_html__( 'Continue Reading', 'display-post-types' );
		$exrpt_text  = $args['e_teaser'] ? esc_html( $args['e_teaser'] ) : $exrpt_text;
		$exrpt_title = get_the_title();

		if ( 0 === strlen( $exrpt_title ) ) {
			$screen_reader = '';
		} else {
			$screen_reader = sprintf( '<span class="screen-reader-text">%s</span>', $exrpt_title );
		}

		$excerpt_teaser = sprintf( '<p class="dpt-link-more"><a class="dpt-more-link" href="%1$s">%2$s %3$s</a></p>', $exrpt_url, $exrpt_text, $screen_reader );

		/**
		 * Filters the string in the "more" link displayed after a trimmed excerpt.
		 *
		 * @since 1.0.0
		 *
		 * @param string $more_string The string shown within the more link.
		 */
		$excerpt_more = apply_filters( 'dpt_excerpt_more', ' ' . $excerpt_teaser, $args );
		if ( $is_excerpt ) {
			$text = wp_trim_words( $text, $excerpt_length, '' );
			$text = $text ? $text . $excerpt_more : $text;
		} else {
			$text = wp_trim_words( $text, $excerpt_length, $excerpt_more );
		}

		printf( '<div class="dpt-excerpt">%s</div>', $text ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 * Display post categories.
	 *
	 * @since 1.0.0
	 */
	public function category() {
		echo '<div class="dpt-categories">';
		the_category( ', ' );
		echo '</div>';
	}

	/**
	 * Display post meta.
	 *
	 * @since 2.1.0
	 *
	 * @param array $args Settings for DPT display instance.
	 */
	public function meta( $args ) {
		$meta1   = isset( $args['meta1'] ) && $args['meta1'] ? $args['meta1'] : false;
		$content = $meta1 ? $this->get_converted_meta( $meta1 ) : false;
		echo '<div class="dpt-meta dpt-meta1">';
		if ( $content ) {
			echo $content;
		} else {
			$this->author();
			$this->date();
		}
		echo '</div>';
	}

	/**
	 * Display post meta.
	 *
	 * @since 2.1.0
	 *
	 * @param array $args Settings for DPT display instance.
	 */
	public function meta2( $args ) {
		$meta2   = isset( $args['meta2'] ) && $args['meta2'] ? $args['meta2'] : false;
		$content = $meta2 ? $this->get_converted_meta( $meta2 ) : false;
		echo '<div class="dpt-meta2">';
		if ( $content ) {
			echo $content;
		} else {
			$this->category();
		}
		echo '</div>';
	}

	/**
	 * Convert and return post meta symbols to relevant HTML.
	 *
	 * @since 2.1.0
	 *
	 * @param string $text Text to fetch post meta.
	 */
	public function get_converted_meta( $text ) {
		$text = esc_html( $text );
		if ( ! $text ) {
			return '';
		}

		if ( false !== strpos( $text, '[author]' ) ) {
			ob_start();
			$this->author();
			$content = ob_get_clean();
			$text    = str_replace( '[author]', $content, $text );
		}

		if ( false !== strpos( $text, '[date]' ) ) {
			ob_start();
			$this->date();
			$content = ob_get_clean();
			$text    = str_replace( '[date]', $content, $text );
		}

		if ( false !== strpos( $text, '[category]' ) ) {
			ob_start();
			$this->category();
			$content = ob_get_clean();
			$text    = str_replace( '[category]', $content, $text );
		}

		return $text;
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

		$all = Get_Fn::styles();
		if ( ! isset( $all[ $style ]['support'] ) || ! $all[ $style ]['support'] ) {
			return false;
		}
		$sup_arr = $all[ $style ]['support'];

		return in_array( $item, $sup_arr, true );
	}

	/**
	 * Extend the default WordPress body classes.
	 *
	 * @since 1.0.0
	 *
	 * @param array $classes Classes for the body element.
	 * @return array
	 */
	public function add_body_classes( $classes ) {
		$classes[] = 'dpt';
		return $classes;
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
