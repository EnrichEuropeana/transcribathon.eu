<?php

namespace FACTSANDFILES\TP;

class ApiRequest
{
	protected $apiToken = null;

	protected $apiHost = null;

  public function __construct(string $apiToken, string $apiHost)
	{
		$this->apiToken = $apiToken;
		$this->apiHost = $apiHost;
	}

	public function get($endpoint)
	{
		$response = $this->send($endpoint, 'GET', null);
		return $this->toArray($response);
	}

	public function post($endpint, $payload)
	{
		$response = $this->send($handle, 'POST', $payload);
		return $this->toArray($response);
	}

	public function put($endpint, $payload)
	{
		$response = $this->send($handle, 'PUT', $payload);
		return $this->toArray($response);
	}

	public function delete($endpint)
	{
		$response = $this->send($handle, 'DELETE');
		return $this->toArray($response);
	}

	public function send($endpoint, $method = 'GET', $payload = null)
	{
		$url = $this->apiHost . $endpoint;
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

		return $this->sendRaw($url, $options);
	}

	public static function toArray($jsonString)
	{
			return json_decode($jsonString, true);
	}

  public static function sendRaw($url, $options)
	{
		$options['ssl'] = [
			'verify_peer' => $options['ssl']['verify_peer'] ?? false,
      'verify_peer_name' => $options['ssl']['verify_peer_name'] ?? false
		];

		$options['http']['ignore_errors'] = true;
		$options['http']['timeout'] = 60;

		$context = stream_context_create($options);
		$result = @file_get_contents($url, false, $context);

		return $result;
	}
}

