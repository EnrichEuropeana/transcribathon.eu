<?php

namespace FactsAndFiles\Transcribathon;

class ApiRequest
{
	protected $apiToken = null;

	protected $apiHost = null;

	public function __construct(string $apiToken, string $apiHost)
	{
		$this->apiToken = $apiToken;
		$this->apiHost = $apiHost;
	}

	public function get(string $endpoint) : array
	{
		$response = $this->send($endpoint, 'GET', null);
		return $this->toArray($response);
	}

	public function post(string $endpint, array $payload) : array
	{
		$response = $this->send($handle, 'POST', $payload);
		return $this->toArray($response);
	}

	public function put(striing $endpint, array $payload) : array
	{
		$response = $this->send($handle, 'PUT', $payload);
		return $this->toArray($response);
	}

	public function delete(string $endpint) : array
	{
		$response = $this->send($handle, 'DELETE');
		return $this->toArray($response);
	}

	public function send(string $endpoint, string $method = 'GET', mixed $payload = null) : string
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
			$options['http']['content'] = json_encode($payload);
		}

		return $this->sendRaw($url, $options);
	}

	public static function toArray(string $jsonString): array
	{
			return json_decode($jsonString, true);
	}

	public static function sendRaw(string $url, array $options) : string
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

