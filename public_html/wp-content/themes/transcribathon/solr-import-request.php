<?php

/**
 * Triggers a Solr import with JavaScript.
 *
 * Requirement: User have to be logged in.
 *
 * Example: Delta import for Items core:
 * <code>
 *
 * const solrImportWrapper = 'https://transcribathon.eu/wp-content/themes/transcribathon/solr-import-request.php';
 * const solrApiCommand = '/solr/Items/dataimport?command=delta-import&commit=true';
 *
 * fetch (solrImportWrapper + solrApiCommand);
 *
 * </code>
 *
 */

require_once($_SERVER['DOCUMENT_ROOT'] . '/wp-load.php');
require_once(get_stylesheet_directory() . '/admin/inc/custom_libs/ApiRequest.php');

use FactsAndFiles\Transcribathon\ApiRequest;

if (!is_user_logged_in()) {
	http_response_code(403);
	echo '{"error":"We think it is not safe to do this right now."}';
	exit(1);
}

$path = $_SERVER['PATH_INFO'];
$query = $_SERVER['QUERY_STRING'] ? '?' . $_SERVER['QUERY_STRING'] : '';

if (empty($path)) {
	echo '{"error":"We think it is not safe to do this right now."}';
	exit(1);
}

$url = TP_SOLR . $path . $query;

echo(ApiRequest::sendRaw($url, []));
