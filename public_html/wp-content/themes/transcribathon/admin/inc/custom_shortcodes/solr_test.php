<?php
/* 
Shortcode: solr_test
*/


// include required files
include($_SERVER["DOCUMENT_ROOT"].'/wp-load.php');

function _TCT_solr_test( $atts ) { 

    /* Domain name of the Solr server */
    define('SOLR_SERVER_HOSTNAME', network_home_url());

    /* HTTP Port to connection */
    define('SOLR_SERVER_PORT', (8983));

    /* HTTP connection timeout */
    /* This is maximum time in seconds allowed for the http data transfer operation. Default value is 30 seconds */
    define('SOLR_SERVER_TIMEOUT', 10);

    $options = array
(
    'hostname' => SOLR_SERVER_HOSTNAME,
    'port'     => SOLR_SERVER_PORT,
);
$client = new SolrClient($options);

$doc = new SolrInputDocument();

}
add_shortcode( 'solr_test',  '_TCT_solr_test' );
?>
