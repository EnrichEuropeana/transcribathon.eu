<?php

require_once($_SERVER['DOCUMENT_ROOT'] . '/wp-load.php');

ApiRequest::loggedInOrExit();

$apiRequest = new ApiRequest(TP_API_V2_TOKEN, TP_API_V2_ENDPOINT);

$response = $apiRequest->handle();

echo $response;

class ApiRequest
{
	protected $apiToken = null;

	protected $apiEndppoint = null;

	public function __construct($apiToken, $apiEndpoint)
	{
		$this->apiToken = $apiToken;
		$this->apiEndppoint = $apiEndpoint;
	}

	public function handle()
	{
		$method = $_SERVER['REQUEST_METHOD'];
		$payload = file_get_contents('php://input');
		$path = $_SERVER['PATH_INFO'];
		$query = $_SERVER['QUERY_STRING'] ? '?' . $_SERVER['QUERY_STRING'] : '';

		$url = $this->apiEndppoint . $path . $query;
		$options = [
			'http' => [
				'header' => [
					'Content-type: application/json',
					'Authorization: Bearer ' . $this->apiToken
			],
				'method' => $method
			]
		];

		if ($payload) {
			$options['http']['content'] = $payload;
		}

		return $this->send($url, $options);
	}

	public static function send($url, $options)
	{
		$options['ssl'] = [
			'verify_peer' => $options['ssl']['verify_peer'] ?: false,
      'verify_peer_name' => $options['ssl']['verify_peer_name'] ?: false
		];

		$options['http']['ignore_errors'] = true;
		$options['http']['timeout'] = 60;

		$context = stream_context_create($options);
		$result = @file_get_contents($url, false, $context);

		return $result;
	}

	public static function loggedInOrExit()
	{
		if (!is_user_logged_in()) {
			http_response_code(403);
			echo '{"error":"We think it is not safe to do this right now."}';
			exit(1);
		}
	}

}

