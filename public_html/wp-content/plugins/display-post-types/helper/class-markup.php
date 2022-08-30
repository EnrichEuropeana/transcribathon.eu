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
class Markup {

	/**
	 * Constructor method.
	 *
	 * @since  1.0.0
	 */
	public function __construct() {}

	/**
	 * Outputs a HTML element.
	 *
	 * @since  1.0.0
	 *
	 * @param string   $class     Markup HTML class(es).
	 * @param callable $callbacks Callback functions to echo content inside the wrapper.
	 * @param string   $open      Markup wrapper opening div.
	 * @param string   $close     Markup wrapper closing div.
	 * @return void
	 */
	public static function markup( $class = '', $callbacks = array(), $open = '<div%s>', $close = '</div>' ) {
		if ( ! $class ) {
			return;
		}

		if ( is_array( $class ) ) {
			// First HTML class will become context for the element.
			$context = array_shift( $class );
			// Remaining classes will simply be added to the element.
			$classes = join( ' ', array_map( 'esc_attr', $class ) );
		} else {
			$context = $class;
			$classes = '';
		}

		$hook = str_replace( '-', '_', $context );

		/**
		 * Filter array of all supplied callable functions for this context.
		 *
		 * @since 1.0.0
		 *
		 * @param arrray $callbacks Array of callback functions (may be with args).
		 */
		$callbacks = apply_filters( "dpt_markup_{$hook}", $callbacks );

		// Return if there are no display functions.
		if ( empty( $callbacks ) ) {
			return;
		}

		printf( $open, self::get_attr( $context, array( 'class' => $classes ) ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

		foreach ( $callbacks as $callback ) {
			$callback = (array) $callback;
			$function = array_shift( $callback );

			// Display output of a function which returns the markup.
			if ( 'echo' === $function ) {
				$function = array_shift( $callback );

				if ( is_callable( $function ) ) {
					echo call_user_func_array( $function, $callback ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				}
			} else {
				if ( is_callable( $function ) ) {
					call_user_func_array( $function, $callback );
				}
			}
		}

		echo $close; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 * Outputs an HTML element's attributes.
	 *
	 * The purposes of this is to provide a way to hook into the attributes for specific
	 * HTML elements and create new or modify existing attributes, without modifying actual
	 * markup templates.
	 *
	 * @since  1.0.0
	 *
	 * @param  str   $slug The slug/ID of the element (e.g., 'sidebar').
	 * @param  array $attr Array of attributes to pass in (overwrites filters).
	 */
	public static function attr( $slug, $attr = array() ) {
		echo self::get_attr( $slug, $attr ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 * Gets an HTML element's attributes.
	 *
	 * This code is inspired (but totally modified) from Stargazer WordPress Theme,
	 * Copyright 2013 – 2018 Justin Tadlock. Stargazer is distributed
	 * under the terms of the GNU GPL.
	 *
	 * @since  1.0.0
	 *
	 * @param  str   $slug The slug/ID of the element (e.g., 'sidebar').
	 * @param  array $attr Array of attributes to pass in (overwrites filters).
	 * @return string
	 */
	public static function get_attr( $slug, $attr = array() ) {
		if ( ! $slug ) {
			return '';
		}

		$out = '';

		if ( false !== $attr ) {
			if ( isset( $attr['class'] ) ) {
				$attr['class'] .= ' ' . $slug;
			} else {
				$attr['class'] = $slug;
			}
		}

		$hook = str_replace( '-', '_', $slug );

		/**
		 * Filter element's attributes.
		 *
		 * @since 1.0.0
		 */
		$attr = apply_filters( "dpt_get_attr_{$hook}", $attr, $slug );

		if ( $attr ) {
			foreach ( $attr as $name => $value ) {
				$out .= sprintf( ' %s="%s"', esc_html( $name ), esc_attr( $value ) );
			}
		}

		return $out;
	}
}
