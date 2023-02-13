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

		$itemResultJson = $this->transkribusClient->getDataFromTranscribathon(
			null,
			array(
				'ItemId' => $itemId,
				'HtrId' => $htrId,
				'orderBy' => 'LastUpdated',
				'orderDir' => 'desc'
			)
		);
		$storedHtr = json_decode($itemResultJson, true);
		$isItemWithHtrIdInHtrDb = !empty($storedHtr['data'][0]['HtrDataId']) ? true : false;

		// check item existence in db here by itemId and $htrId
		if (!$isItemWithHtrIdInHtrDb) {

			if (!array_key_exists('ImageLink', $item)) {
				$this->currentErrors += 1;
				$this->errorMessages[$this->currentErrors] = 'No ImageLink available for this item.';
				return;
			}

			$imageUrl = $this->buildImageUrlFromItem($item);

			$this->insertItem($itemId, $imageUrl, $htrId);

		} else {

			$storedHtrData = $storedHtr['data'][0];
			$processId = intval($storedHtrData['ProcessId']);
			$id = intval($storedHtrData['HtrDataId']);

			// can be updated
			if (in_array($storedHtrData['HtrStatus'], array('CREATED', 'WAITING', 'RUNNING'))) {

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

					$transcritpionText = $this->extractPlainTextFromPageXml($transkribusXmlData);

					// save the data
					$updateData = array(
						'HtrStatus'         => 'FINISHED',
						'TranscriptionData' => $transkribusXmlData,
						'TranscriptionText' => $transcritpionText
					);

					$updateQuery = $this->updateItem($id, $updateData);
				}

			} else { // nothing is processed, update success

				$this->currentSuccess += 1;

			}

		}
	}

	public static function extractPlainTextFromPageXml ($xmlString, $break = "\n")
	{
    $text = '';
    $xmlString = str_replace('xmlns=', 'ns=', $xmlString);

    if ($xmlString) {
    	$xml = new SimpleXMLElement($xmlString);
    	$textRegions = $xml->xpath('//Page/TextRegion');

    	foreach ($textRegions as $textRegion) {
        $textLines = $textRegion->xpath('TextLine');
        foreach ($textLines as $textLine) {
          $textElement = $textLine->xpath(('TextEquiv/Unicode'));
          $text .= $textElement[0] . $break;
        }
        $text .= $break;
    	}
    }

    return $text;
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
