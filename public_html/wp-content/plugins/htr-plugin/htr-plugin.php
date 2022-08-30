<?php
/**
 * Plugin Name: HTR Plugin
 * Plugin URI: https://github.com/Facts-and-Files/transcribathon.eu
 * Description: Shows the HTR Viewer and handles the related XML data
 * Version: 0.1.0
 * Author: Tommy Schmucker
 * Author URI: https://www.factsandfiles.com/
 */

if (!defined('ABSPATH')) {
	exit;
}

add_action('rest_api_init', function() {
	register_rest_route('htr-plugin', '/item/(?P<id>\d+)', array(
		'methods' => 'GET',
		'callback' => 'htr_show_xml',
	));
});

/* function htr_show_xml($request) */
/* { */
/* 	/1* var_dump($request['id']); *1/ */
/* 	$xmlItemArray = array('xml' => '<xml />'); */
/* 	$response = new WP_REST_Response($xmlItemArray); */
/* 	return $response; */
/* } */

/**
 * get_htr_json_data
 *
 * Get the HTR-JSON for specific item
 *
 * @param integer Item ID
 * @return string JSON string
 */
function get_htr_json_data($itemId = null)
{
	if (!$itemId === null) {
		return '';
	}

	$file = WP_CONTENT_DIR . '/htr-json-data/' . $itemId . '.json';

	if (!file_exists($file)) {
		return '';
	}

	$jsonString = file_get_contents($file);

	return $jsonString;
}

/**
 * show_htr_viewer
 *
 * Shows the HTR viewer/editor as Iframe
 *
 * @param string $version (see provided folders in plugin)
 * @return string returns iframe
 */
function show_htr_viewer($version = '2021-12-07')
{
	$versionFolder = plugins_url() . '/htr-plugin/assets/' . $version;
	$itemId = $_GET['item'] ? intval($_GET['item']) : null;

	$xmlJsonData = get_htr_json_data($itemId);
	$iiifUrl = 'here should be an IIIF Data JSON url';

	$htr_plugin  = <<<EOF
<iframe style="width: 100%; height: 100%" srcdoc='
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width,initial-scale=1">
	<title>Transkribus Text-Editor</title>
	<link rel="preload" as="style" href="{$versionFolder}/css/app.css">
	<link rel="preload" as="style" href="{$versionFolder}/css/chunk-vendors.css">
	<link rel="preload" as="style" href="{$versionFolder}/custom.css">
	<link rel="preload" as="script" href="{$versionFolder}/js/app.js">
	<link rel="preload" as="script" href="{$versionFolder}/js/chunk-vendors.js">
	<link rel="stylesheet" href="{$versionFolder}/css/app.css">
	<link rel="stylesheet" href="{$versionFolder}/css/chunk-vendors.css">
	<link rel="stylesheet" href="{$versionFolder}/custom.css">
</head>
<body>
<!--
	<div
		id="transkribusEditor"
		ref="editor"
		data-iiif-url="{$iiifUrl}"
		data-xml-json-string="{$xmlJsonData}"
	></div>
-->
	<div id="transkribusEditor" ref="editor" ></div>
</body>
<script src="{$versionFolder}/js/chunk-vendors.js"></script>
<script src="{$versionFolder}/js/app.js"></script>
<script>
	window.eventBus.$on('dataSaved', (data) => {
		console.log('saved HTR data');
		console.log(data);
	});
</script>
</html>
'></iframe>
EOF;

	return $htr_plugin;
}
