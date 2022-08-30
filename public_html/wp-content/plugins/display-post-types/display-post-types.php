<?php
/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link https://vedathemes.com
 * @since 1.0.0
 * @package Display_Post_Types
 *
 * @wordpress-plugin
 * Plugin Name: Display Post Types
 * Description: Filter, sort and display post, page or any post type.
 * Version: 2.1.0
 * Author: vedathemes
 * Author URI: https://vedathemes.com
 * License: GPL-3.0+
 * License URI: http://www.gnu.org/licenses/gpl-3.0.txt
 * Text Domain: display-post-types
 * Domain Path: /lang
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

// Currently plugin version.
define( 'DISPLAY_POST_TYPES_VERSION', '2.1.0' );

// Define plugin constants.
define( 'DISPLAY_POST_TYPES_DIR', plugin_dir_path( __FILE__ ) );

// Define plugin constants.
define( 'DISPLAY_POST_TYPES_URL', plugin_dir_url( __FILE__ ) );

// Define plugin constants.
define( 'DISPLAY_POST_TYPES_BASENAME', plugin_basename( __FILE__ ) );

// Register PHP autoloader.
spl_autoload_register(
	function( $class ) {
		$namespace = 'Display_Post_Types\\';

		// Bail if the class is not in our namespace.
		if ( 0 !== strpos( $class, $namespace ) ) {
			return;
		}

		// Get classname without namespace.
		$carray = array_values( explode( '\\', $class ) );
		$clast  = count( $carray ) - 1;

		// Return if proper array is not available. (Just in case).
		if ( ! $clast ) {
			return;
		}

		// Prepend actual classname with 'class-' prefix.
		$carray[ $clast ] = 'class-' . $carray[ $clast ];
		$class            = implode( '\\', $carray );

		// Generate file path from classname.
		$path = strtolower(
			str_replace(
				array( $namespace, '_' ),
				array( '', '-' ),
				$class
			)
		);

		// Build full filepath.
		$file = DISPLAY_POST_TYPES_DIR . DIRECTORY_SEPARATOR . str_replace( '\\', DIRECTORY_SEPARATOR, $path ) . '.php';

		// If the file exists for the class name, load it.
		if ( file_exists( $file ) ) {
			include $file;
		}
	}
);

add_action(
	'plugins_loaded',
	function() {
		// Load plugin's text domain.
		load_plugin_textdomain( 'display-post-types', false, DISPLAY_POST_TYPES_DIR . 'lang/' );

		// Register Podcast player front-end hooks.
		Display_Post_Types\Frontend\Register::init();

		// Register Podcast player back-end hooks.
		Display_Post_Types\Backend\Register::init();
	},
	8
);
