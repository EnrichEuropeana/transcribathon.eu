<?php
/*
Plugin Name: Gallery Custom Links
Plugin URI: https://meowapps.com
Description: Gallery Custom Links allows you to link images from galleries to a specified URL. Tested with WordPress Gallery, Gutenberg, the Meow Gallery and others.
Version: 2.1.3
Author: Jordy Meow
Author URI: https://meowapps.com
Text Domain: gallery-custom-links
Domain Path: /languages

Dual licensed under the MIT and GPL licenses:
http://www.opensource.org/licenses/mit-license.php
http://www.gnu.org/licenses/gpl.html
*/

if ( !defined( 'MGCL_VERSION' ) ) {
  define( 'MGCL_VERSION', '2.1.3' );
  define( 'MGCL_PREFIX', 'mgcl' );
  define( 'MGCL_DOMAIN', 'gallery-custom-links' );
  define( 'MGCL_ENTRY', __FILE__ );
  define( 'MGCL_PATH', dirname( __FILE__ ) );
  define( 'MGCL_URL', plugin_dir_url( __FILE__ ) );
  define( 'MGCL_BASENAME', plugin_basename( __FILE__ ) );
}

require_once( 'classes/init.php' );

// Temporary
// TODO: Let's delete the 'mgcl_hide_new_version' later

if ( is_admin() && !get_option( 'mgcl_hide_new_version' ) ) {

  // Delete notice
  if ( isset( $_POST['mgcl_hide_new_version'] ) || isset( $_GET['mgcl_hide_new_version'] ) ) {
    update_option( 'mgcl_hide_new_version', 1 );
    return;
  }

  // Show notice
  function mgcl_new_plugin_notice() {
    $url = 'https://meowapps.com/products/gallery-custom-links-pro/';
    echo '<div class="notice notice-success">';
    echo '<h2>Announcement for Gallery Custom Links 🎵</h2>';
    echo '<p>I want to make it better, easier to use, with a better support. In order to do this, <b>I am launching a Pro Version!</b><br />If you want to support Gallery Custom Links and/or take advantage of the currently very low price of the Pro Version, please check it out. Thanks a lot! 😊</p>';
    echo '<div style="display: flex; justify-content: space-between; margin-top: 15px; margin-bottom: 15px;">
      <a target="_blank" href="' . $url . '" class="wp-core-ui button button-primary" style="margin-right: 10px;">
        Check the new Pro Version
      </a>
      <div>
        <form method="post" action="">
          <input type="hidden" name="mgcl_hide_new_version" value="true">
          <input type="submit" name="submit" id="submit" class="button" value="Hide this!">
        </form>
      </div>
    </div>
    ';
    echo '</div>';
  }
  add_action( 'admin_notices', 'mgcl_new_plugin_notice' );
}

?>
