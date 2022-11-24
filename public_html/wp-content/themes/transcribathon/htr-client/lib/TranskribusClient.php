<?php

namespace FactsAndFiles\Transcribathon;

class TranskribusClient
{
	protected $error = null;

	protected $transkribusHtrModelEndpoint = null;

	protected $transkribusEndpoint = null;

	protected $transkribusAccess = array();

	protected $transkribusUrlAccessToken = null;

	protected $transkribusAuthData = array();

	protected $transcribathonEndpoint = null;

	protected $transcribathonToken = null;

	protected $verifySSL = true;

	/*
 	 * __construct
 	 *
 	 * Class constructor
 	 *
 	 * @param array $config injected configuration
 	 */
	public function __construct($config)
	{
		$transkribusConfig = $config['transkribus'];
		$transcribathonConfig = $config['transcribathon'];

		$this->verifySSL = $config['verifySSL'];

		$this->transcribathonToken = $transcribathonConfig['apiToken'];
		$this->transcribathonEndpoint = $transcribathonConfig['endpoint'];

		$this->transkribusAuthData = array(
			'scope'      => $transkribusConfig['scope'],
			'grant_type' => $transkribusConfig['grantType'],
			'username'   => $transkribusConfig['user'],
			'password'   => $transkribusConfig['pass'],
			'client_id'  => $transkribusConfig['clientId']
		);

		$this->transkribusUrlAccessToken = $transkribusConfig['urlAccessToken'];
		$this->transkribusEndpoint = $transkribusConfig['endpoint'];
		$this->transkribusHtrModelEndpoint = $transkribusConfig['htrModelEndpoint'];

		$access = $this->getTranskribusAccessToken();

		if ($access) {
			$this->transkribusAccess['token'] = $access['access_token'];
			$this->transkribusAccess['token_expired'] = time() + $access['expires_in'];
		}
	}

	/*
 	 * get all HTR models
 	 *
 	 * @return mixed JSON string on success otherwise false
 	 */
	public function getAllHtrModels()
	{
		$queryOptions = array();

		$query = $this->queryTranskribus($queryOptions, $this->transkribusHtrModelEndpoint);

		if (!$query) {
			return false;
		}

		return $query;
	}

	/*
 	 * submitDataToTranskribus
 	 *
 	 * send an imageUrl to be procssed by handwriting recognition
 	 *
 	 * @param  integer $itemId   Item from the Transcribathon DB
 	 * @param  string  $imageUrl Url of the image
 	 * @param  integer $htrId    HTR model ID
 	 * @return mixed             string response on success otherwise false
 	 */
	public function submitDataToTranskribus($itemId, $imageUrl, $htrId)
	{
		$payload = array(
			"config" => array(
				"textRecognition" => array(
					'htrId' => $htrId
				)
			),
			"image" => array(
				"imageUrl" => $imageUrl
			)
		);

		$payload = json_encode($payload);

		$queryOptions = array(
			'method' => 'POST',
			'body'   => $payload
		);

		$result = $this->queryTranskribus($queryOptions);
		if (!$result) {
			return false;
		}

		$resultArray = json_decode($result, true);
		$processId   = $resultArray['processId'];
		$status      = $resultArray['status'];

		$postData = array(
			'ItemId' => $itemId,
			'HtrId'  => $htrId,
			'ProcessId' => $processId,
			'HtrStatus' => $status
		);

		$tpResult = $this->postToTranscribathon($postData);

		if (!$tpResult) {
			return false;
		}

		return $result;
	}

	/*
 	 * getJSONDatafromTranskribus
 	 *
 	 * get JSON data from Transkribus
 	 *
 	 * @param  integer $processId  ID of the Transkribus process
 	 * @return mixed               string JSON response on success otherwise false
 	 */
	public function getJSONDatafromTranskribus($processId)
	{
		$queryOptions = array(
			'processId' => $processId
		);

		$query = $this->queryTranskribus($queryOptions);

		return $query;
	}

	/*
 	 * getPageXMLfromTranskribus
 	 *
 	 * get PAGE XML data from Transkribus
 	 *
 	 * @param  integer $processId  ID of the Transkribus process
 	 * @return mixed               string PAGE XML response on success otherwise false
 	 */
	public function getPageXMLfromTranskribus($processId)
	{
		$queryOptions = array(
			'processId' => $processId,
			'accept'    => 'xml',
			'what'      => 'page'
		);

		$query = $this->queryTranskribus($queryOptions);

		return $query;
	}

	/*
 	 * getDataFromTranscribathon
 	 *
 	 * get data from HTR Transcribathon data
 	 *
 	 * @param  integer $HtrDataId  ID og the HTR entry
 	 * @param  array   $what       nature parameters of endoint call
 	 * @return mixed               string JSON response on success otherwise false
 	 */
	public function getDataFromTranscribathon($HtrDataId = null, $what = array())
	{
		$queryOptions = array(
			'method'    => 'GET',
			'HtrDataId' => $HtrDataId,
			'what'      => $what
		);

		$result = $this->queryTranscribathon($queryOptions);

		return $result;
	}

	/*
 	 * updateDataToTranscribathon
 	 *
 	 * update item data to transcribathon db via TP API
 	 *
 	 * $data example:
 	 *
 	 * array(
 	 * 	"HtrStatus" => $status,
 	 * 	"TranscriptionData" => $data
 	 * );
 	 *
 	 * @param  integer $HtrDataId ID of the entry to be updated
 	 * @param  array   $data      $data array with data to be updated
 	 * @result mixed              response string on success otherwise false
 	 */
	public function updateDataToTranscribathon($HtrDataId, $data)
	{
		if (!$HtrDataId || !$data) {
			return false;
		}

		$payload = json_encode($data);

		$queryOptions = array(
			'method'    => 'PUT',
			'HtrDataId' => $HtrDataId,
			'body'      => $payload
		);

		$result = $this->queryTranscribathon($queryOptions);

		return $result;
	}

	/*
 	 * postToTranscribathon
 	 *
 	 * save the query data to transcribathon db via TP API
 	 *
 	 * $data example for data from Transkribus:
 	 *
 	 * array(
 	 *	"ItemId"    => 1111,
 	 *	"HtrId"     => 2025,
 	 *	"ProcessId" => 3333,
 	 *	"HtrStatus" => 'CREATED'
 	 * );
 	 *
 	 * $data example for data from Transcribathon user:
 	 *
 	 * array(
 	 *	"ItemId"                 => 1111,
 	 *	"UserId"                 => 2222,
 	 * 	"TranscriptionData"      => '<xml />'
 	 * );
 	 *
 	 * @param  array $body array with entries for the payload
 	 * @result mixed       response string on success otherwise false
 	 */
	public function postToTranscribathon($data = array())

	{
		if (empty($data['ItemId'])) {
			$this->error = 'No item ID in posted data';
			return false;
		}

		$payload = json_encode($data);

		$queryOptions = array(
			'method' => 'POST',
			'body'   => $payload
		);

		$result = $this->queryTranscribathon($queryOptions);

		return $result;
	}

	/*
 	 * queryTranscribathon
 	 *
 	 * query the Transcribathon API
 	 *
 	 * @param  array $queryOptions array with data for building the query
 	 * @return mixed               response string on success (statusCode <= 299) otherwise false
 	 */
	protected function queryTranscribathon($queryOptions)
	{
		if (is_array($queryOptions)) {
			$queryOptions = array(
				'method'    => $queryOptions['method']    ?? 'GET',
				'HtrDataId' => $queryOptions['HtrDataId'] ?? null,
				'what'      => $queryOptions['what']      ?? array(),
				'body'      => $queryOptions['body']      ?? null
			);
		} else {
			return false;
		}

		extract($queryOptions);

		$options = array(
			'http' => array(
				'header' => array(
					'Content-type: application/json',
					'Authorization: Bearer ' . $this->transcribathonToken
				),
				'method' => $method
			)
		);

		if ($body) {
			$options['http']['content'] = $body;
		}

		$idSep = $what ? '=' : '/';
		$whatPath = count($what) > 0 ? '?' . http_build_query($what): '';
		$idPath = $HtrDataId ? $idSep . $HtrDataId : '';
		$url = $this->transcribathonEndpoint . '/htrdata' . $whatPath . $idPath;

		$result = $this->sendQuery($url, $options);

		return $result;
	}

	/*
 	 * sendQuery
 	 *
 	 * send a API request
 	 *
 	 * @param  string $url     API endpoint
 	 * @param  array  $options options array for context ceating
 	 * @return mixed           repsonse string on success, otherwise false
 	 */
	protected function sendQuery($url, $options)
	{
		if (!$this->verifySSL) {
			$options['ssl'] = array(
				'verify_peer' => false,
        'verify_peer_name' => false,
			);
		}

		$options['http']['ignore_errors'] = true;
		$options['http']['timeout'] = 60;

		$context = stream_context_create($options);
		$result = @file_get_contents($url, false, $context);

		$responseHeader = $http_response_header ?? array('HTTP/1.1 400 Bad request');

		$status = explode(' ', $responseHeader[0])[1];

		if ($status > 299) {
			$this->error = $result ?: error_get_last()['message'];
			return false;
		}

		return $result;
	}

	/*
 	 * queryTranskribus
 	 *
 	 * query the Transkribus API
 	 *
 	 * @param  array $queryOptions array with data for building the query
 	 * @return mixed               response string on success (statusCode <= 299) otherwise false
 	 */
	protected function queryTranskribus($queryOptions, $endpoint = null)
	{
		if (is_array($queryOptions)) {
			$queryOptions = array(
				'method'    => $queryOptions['method']    ?? 'GET',
				'processId' => $queryOptions['processId'] ?? null,
				'what'      => $queryOptions['what']      ?? null,
				'body'      => $queryOptions['body']      ?? null,
				'accept'    => $queryOptions['accept']    ?? 'json'
			);
		} else {
			return false;
		}

		extract($queryOptions);

		if (!$this->handleTranskribusAccess()) {
			return false;
		}

		$accessToken = $this->transkribusAccess['token'];

		$options = array(
			'http' => array(
				'header' => array(
					'Content-type: application/' .$accept,
					'Authorization: Bearer ' . $accessToken
				),
				'method' => $method
			)
		);

		if ($body) {
			$options['http']['content'] = $body;
		}

		// use transcritpion endoint as default
		if (!$endpoint) {
			$path = $what ? '/' . $what : '';
			$id = $processId ? '/' . $processId : '';
			$url = $this->transkribusEndpoint . '/processes' . $id . $path;
			// otherwise use argument endoint
		} else {
			$url = $endpoint;
		}

		$result = $this->sendQuery($url, $options);

		return $result;
	}

	/*
 	 * getLastError
 	 *
 	 * Getter to get the last occured error if one
 	 *
 	 * @return string
 	 */
	public function getLastError()
	{
		$error = $this->error ?? '';
		return $error;
	}

	/*
 	 * handleTranskribusAccess
 	 *
 	 * Get a new token when old one is expired.
 	 *
 	 * return bool true on success otherwise false
 	 */
	protected function handleTranskribusAccess()
	{
		$now = time();
		$tokenExpired = $this->transkribusAccess['token_expired'];

		if ($now >= $tokenExpired) {

			// old token is expired so we get a new one
			$access = $this->getTranskribusAccessToken();

			if (!$access) {
				return false;
			}

			// and save the new one and its expire time
			$this->transkribusAccess['token'] = $access['access_token'];
			$this->transkribusAccess['token_expired'] = time() + $access['expires_in'];
		}

		return true;
	}

	/*
 	 * getTranskribusAccessToken
 	 *
 	 * Get the access token from Transkribus
 	 *
 	 * @return mixed
 	 */
	protected function getTranskribusAccessToken()
	{
		$options = array(
			'http' => array(
				'header'  => array(
					'Content-type: application/x-www-form-urlencoded'
				),
				'method'  => 'POST',
				'content' => http_build_query($this->transkribusAuthData)
			)
		);

		$url = $this->transkribusUrlAccessToken;

		$result = $this->sendQuery($url, $options);

		if ($result) {
			$result = json_decode($result, true);
		}

		return $result;
	}
}
