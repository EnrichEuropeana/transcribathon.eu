<?php

if ( class_exists( 'Meow_MGCL_Core' ) ) {
	function mfrh_admin_notices() {
		echo '<div class="error"><p>Thanks for installing Gallery Custom Links :) However, another version is still enabled. Please disable or uninstall it.</p></div>';
	}
	add_action( 'admin_notices', 'mfrh_admin_notices' );
	return;
}

spl_autoload_register(function ( $class ) {
  $necessary = true;
  $file = null;
  if ( strpos( $class, 'Meow_MGCL' ) !== false ) {
    $file = MGCL_PATH . '/classes/' . str_replace( 'meow_mgcl_', '', strtolower( $class ) ) . '.php';
  }
  else if ( strpos( $class, 'MeowCommon_' ) !== false ) {
    $file = MGCL_PATH . '/common/' . str_replace( 'meowcommon_', '', strtolower( $class ) ) . '.php';
  }
  else if ( strpos( $class, 'MeowCommonPro_' ) !== false ) {
    $necessary = false;
    $file = MGCL_PATH . '/common/premium/' . str_replace( 'meowcommonpro_', '', strtolower( $class ) ) . '.php';
  }
  else if ( strpos( $class, 'MeowPro_MGCL' ) !== false ) {
    $necessary = false;
    $file = MGCL_PATH . '/premium/' . str_replace( 'meowpro_mgcl_', '', strtolower( $class ) ) . '.php';
  }
  if ( $file ) {
    if ( !$necessary && !file_exists( $file ) ) {
      return;
    }
    require( $file );
  }
});

new Meow_MGCL_Core();

// Temporary

if ( get_transient( 'mgcl_new_plugin_notice' ) ) {

  // Delete notice
  if ( isset( $_POST['mgcl_new_plugin_notice'] ) || isset( $_GET['mgcl_new_plugin_notice'] ) ||
    file_exists( WP_PLUGIN_DIR . '/' . 'database-cleaner/database-cleaner.php' ) ) {
    delete_transient( 'mgcl_new_plugin_notice' );
    return;
  }

  // Show notice
  function mgcl_new_plugin_notice() {
    $url_repo = 'https://wordpress.org/plugins/database-cleaner/';
    $url_install = wp_nonce_url(
      add_query_arg( array( 'action' => 'install-plugin', 'plugin' => 'database-cleaner', 'mgcl_new_plugin_notice' => 'true' ),
      admin_url( 'update.php' ) ), 'install-plugin' . '_' . 'database-cleaner'
    );
    echo '<div class="notice notice-success">';
    echo '<h2>Clean your WordPress database with the new <a target="_blank" href="' . $url_repo . '">Database Cleaner</a>!</h2>';
    echo '<p>After analyzing the existing solutions to clean and optimize databases, the decision of building a fresh and ultra-performant plugin was taken by Meow Apps. It is now available on the official WordPress repository: <a target="_blank" href="' . $url_repo . '">Database Cleaner</a>. Please try it out as it might help you greatly! 💕</p>';
    echo '<div style="display: flex; justify-content: space-between; margin-top: 15px; margin-bottom: 15px;">
      <a href="' . $url_install . '" class="wp-core-ui button button-primary" style="margin-right: 10px;">
        Install Database Cleaner
      </a>
      <div>
        <form method="post" action="">
          <input type="hidden" name="mgcl_new_plugin_notice" value="true">
          <input type="submit" name="submit" id="submit" class="button" value="Got it! Hide this.">
        </form>
      </div>
    </div>
    ';
    echo '</div>';
  }
  add_action( 'admin_notices', 'mgcl_new_plugin_notice' );
}

add_action( 'upgrader_process_complete', function ( $upgrader_object, $options ) {
  if ( $options['action'] == 'update' && $options['type'] == 'plugin' && isset( $options['plugins'] ) ) {
    foreach( $options['plugins'] as $plugin ) {
      if ( $plugin == MGCL_BASENAME ) {
        set_transient( 'mgcl_new_plugin_notice', 'true', 12 * HOUR_IN_SECONDS );
        return;
      }
    }
  }
}, 10, 2 );

?>