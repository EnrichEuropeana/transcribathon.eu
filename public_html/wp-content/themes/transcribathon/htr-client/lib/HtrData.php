<?php

use FactsAndFiles\Transcribathon\TranskribusClient;

class HtrData
{
	protected $transkribusClient = null;

	protected $amount = 0;

	protected $currentSuccess = 0;

	protected $currentErrors = 0;

	protected $errorMessages = array();

	public function __construct($config)
	{
		$this->transkribusClient = new TranskribusClient($config);
	}

	public function getAmount()
	{
		return $this->amount;
	}

	public function getCurrentSucces()
	{
		return $this->currentSuccess;
	}

	public function getCurrentErrors()
	{
		return $this->currentErrors;
	}

	public function getErrorMessages()
	{
		return $this->errorMessages;
	}

	public static function sendQuery($url, $options, $verifySSL = true)
	{
		if (!$verifySSL) {
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
			/* return error_get_last(); */
			return false;
		}

		return $result;
	}

	public function sendStoryData($items, $htrId)
	{
		$this->amount = count($items);
		$this->currentSuccess = 0;
		$this->currentErrors = 0;
		$this->errorMessages = array();

		array_walk($items, array($this, 'handleItem'), $htrId);

		$result = array(
			'amount' => $this->getAmount(),
			'errors' => $this->getCurrentErrors(),
			'success' => $this->getCurrentSucces(),
			'errorMessages' => $this->getErrorMessages()
		);

		$result = json_encode($result);

		return $result;
	}

	protected function handleItem($item, $index, $htrId)
	{
		$itemId = intval($item['ItemId']);
		$htrId = intval($htrId);

		// check item existence in db here
		$itemResultJson = $this->transkribusClient->getDataFromTranscribathon(
			null,
			array(
				'itemId' => $itemId,
				'htrId' => $htrId,
				'orderBy' => 'updated_at',
				'orderDir' => 'desc'
			)
		);
		$storedHtr = json_decode($itemResultJson, true);
		$isItemInHtrDb = !empty($storedHtr['data'][0]['id']) ? true : false;
		$isDifferentHtrModel = $htrId !== $storedHtr['data'][0]['htr_id'];

		// add new transcription if is not in DB or the HTR model is another
		if (!$isItemInHtrDb || $isDifferentHtrModel) {

			if (!array_key_exists('ImageLink', $item)) {
				$this->currentErrors += 1;
				$this->errorMessages[$this->currentErrors] = 'No ImageLink available for this item.';
				return;
			}

			$imageUrl = $this->buildImageUrlFromItem($item);

			$this->insertItem($itemId, $imageUrl, $htrId);

		} else {

			$storedHtrData = $storedHtr['data'][0];
			$processId = intval($storedHtrData['process_id']);
			$id = intval($storedHtrData['id']);

			// can be updated
			if (in_array($storedHtrData['htr_status'], array('CREATED', 'WAITING', 'RUNNING'))) {

				$transkribusData = $this->transkribusClient->getJSONDatafromTranskribus($processId);

				if (!$transkribusData) {
					$this->currentErrors += 1;
					$this->errorMessages[$this->currentErrors] = 'Could not get data from Transkribus.';
					return;
				}

				$transkribusDataArray = json_decode($transkribusData, true);

				if ($transkribusDataArray['status'] === 'FAILED') {
					$this->currentErrors += 1;
					$this->errorMessages[$this->currentErrors] = 'Transkribus processing failed.';
					return;
				}

				if ($transkribusDataArray['status'] === 'FINISHED') {
					$transkribusXmlData = $this->transkribusClient->getPageXMLfromTranskribus($processId);

					if (!$transkribusXmlData) {
						$this->currentErrors += 1;
						$this->errorMessages[$this->currentErrors] = 'Could not get PAGE XML data from Transkribus.';
						return;
					}

					// save the data
					$updateData = array(
						'htr_status'         => 'FINISHED',
						'transcription_data' => $transkribusXmlData
					);

					$updateQuery = $this->updateItem($id, $updateData);
				}

			} else { // nothing is processed, update success

				$this->currentSuccess += 1;

			}

		}
	}

	protected function updateItem($id, $data)
	{
		$updateEntry = $this->transkribusClient->updateDataToTranscribathon($id, $data);

		if (!$updateEntry) {
			$this->currentErrors += 1;
			$this->errorMessages[$this->currentErrors] = $this->transkribusClient->getLastError();
		} else {
			$this->currentSuccess += 1;
		}
	}

	protected function insertItem($itemId, $imageUrl, $htrId)
	{

		$createEntry = $this->transkribusClient->submitDataToTranskribus($itemId, $imageUrl, $htrId);

		if (!$createEntry) {
			$this->currentErrors += 1;
			$this->errorMessages[$this->currentErrors] = $this->transkribusClient->getLastError();
		}
	}

	public function buildImageUrlFromItem($item)
	{
		$imageUrl = json_decode($item['ImageLink'], true)['@id'];
		$imageUrl = str_replace('https://', '', $imageUrl);
		$imageUrl = 'https://' . $imageUrl;

		return $imageUrl;
	}
}
