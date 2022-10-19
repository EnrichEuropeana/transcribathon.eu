<?php

require_once($_SERVER["DOCUMENT_ROOT"].'/wp-load.php');

$config = array(
	'transkribus' => array(
		'htrModelEndpoint' => HTR_MODEL_ENDPOINT,
		'endpoint'         => HTR_ENDPOINT,
		'user'             => HTR_USER,
		'pass'             => HTR_PASS,
		'clientId'         => HTR_CLIENT_ID,
		'urlAccessToken'   => HTR_TOKEN_URI,
		'scope'            => array('openid profile'),
		'grantType'        => 'password'
	),
	'transcribathon' => array(
		'endpoint' => TP_API_V2_ENDPOINT,
		'apiToken' => TP_API_V2_TOKEN
	),
	'verifySSL' => false
);

$oldApiEndpoint = TP_API_HOST . '/tp-api' ;

