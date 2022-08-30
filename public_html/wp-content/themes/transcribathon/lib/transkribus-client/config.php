<?php

$config = array(
	"transkribus" => array(
		'endpoint'       => 'https://transkribus.eu/processing/v1',
		'user'           => 'enrich.europeana.test@transkribus.eu',
		'pass'           => 'heeF5PheiChie3zahl3eicuf',
		'clientId'       => 'processing-api-client',
		'urlAccessToken' => 'https://account.readcoop.eu/auth/realms/readcoop/protocol/openid-connect/token',
		'scope'          => array('openid profile'),
		'grantType'      => 'password'
	),
	"transcribathon" => array(
		"endpoint" => 'http://api.transcribathon.local/v2',
		"apiToken" => 'HQadnqNt27Fx7xz5I92iJPAHxO7MuelorJzIMpFyP3MYaot4Mx246Eq49KRV'
	)
);

$oldApiEndpoint = 'http://transcribathon.local/tp-api';

