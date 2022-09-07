<?php

$config = array(
	"transkribus" => array(
		'htrModelEndpoint' => getenv('HTR_MODEL_ENDPOINT'),
		'endpoint'         => getenv('HTR_ENDPOINT'),
		'user'             => getenv('HTR_USER'),
		'pass'             => getenv('HTR_PASS'),
		'clientId'         => getenv('HTR_CLIENT_ID'),
		'urlAccessToken'   => getenv('HTR_TOKEN_URI'),
		'scope'            => array('openid profile'),
		'grantType'        => 'password'
	),
	"transcribathon" => array(
		"endpoint" => getenv('TP_API_V2_ENDPOINT'),
		"apiToken" => getenv('TP_API_V2_TOKEN')
	)
);

$oldApiEndpoint = getenv('OLD_API_ENDPOINT');
