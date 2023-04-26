<?php

/**
 * database
 */
define('DB_NAME', getenv('DB_NAME'));
define('DB_USER', getenv('DB_USER'));
define('DB_PASSWORD', getenv('DB_PASSWORD'));
define('DB_HOST', getenv('DB_HOST'));
define('DB_CHARSET', 'utf8');
define('DB_COLLATE', '');
$table_prefix = getenv('DB_PREFIX');

/**
 * keys/salts
 */
define('AUTH_KEY',         getenv('AUTH_KEY'));
define('SECURE_AUTH_KEY',  getenv('SECURE_AUTH_KEY'));
define('LOGGED_IN_KEY',    getenv('LOGGED_IN_KEY'));
define('NONCE_KEY',        getenv('NONCE_KEY'));
define('AUTH_SALT',        getenv('AUTH_SALT'));
define('SECURE_AUTH_SALT', getenv('SECURE_AUTH_SALT'));
define('LOGGED_IN_SALT',   getenv('LOGGED_IN_SALT'));
define('NONCE_SALT',       getenv('NONCE_SALT'));

/**
 * site setup
 */
define('WP_ALLOW_MULTISITE', true);
define('MULTISITE', true);
define('SUBDOMAIN_INSTALL', false);
define('DOMAIN_CURRENT_SITE', getenv('DOMAIN_CURRENT_SITE'));
define('PATH_CURRENT_SITE', '/');
define('SITE_ID_CURRENT_SITE', 1);
define('BLOG_ID_CURRENT_SITE', 1);
define('COOKIE_DOMAIN', '.'.getenv('DOMAIN_CURRENT_SITE'));
define('COOKIEPATH', '/');
define('COOKIEHASH', md5(getenv('DOMAIN_CURRENT_SITE')));

/**
 * debug
 */
define('WP_DEBUG', (bool) getenv('WP_DEBUG'));

/**
 * file access method
 */
define('FS_METHOD', 'direct');

/**
 * autoupdate
 */
define('WP_AUTO_UPDATE_CORE', false);

/**
 * Solr
 */
define('TP_SOLR', getenv('TP_SOLR'));

/**
 * define TP-API V1
 */
define('TP_API_HOST', getenv('TP_API_HOST'));
define('TP_API_TOKEN', getenv('TP_API_TOKEN'));

/**
 * define auth for Transkribus
 */
define('HTR_TOKEN_URI', getenv('HTR_TOKEN_URI'));
define('HTR_USER', getenv('HTR_USER'));
define('HTR_PASS', getenv('HTR_PASS'));
define('HTR_CLIENT_ID', getenv('HTR_CLIENT_ID'));
define('HTR_ENDPOINT', getenv('HTR_ENDPOINT'));
define('HTR_MODEL_ENDPOINT', getenv('HTR_MODEL_ENDPOINT'));

/**
 * define endpoint and token for TP API V2
 */
define('TP_API_V2_TOKEN', getenv('TP_API_V2_TOKEN'));
define('TP_API_V2_ENDPOINT', getenv('TP_API_V2_ENDPOINT'));

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');
