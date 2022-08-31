<?php

require __DIR__ . '/../vendor/autoload.php';

use FactsAndFiles\Transcribathon\TranskribusClient;

error_reporting(E_ALL);

$config = array(
	"transkribus" => array(
		'endpoint'       => getenv('HTR_ENDPOINT'),
		'user'           => getenv('HTR_USER'),
		'pass'           => getenv('HTR_PASS'),
		'clientId'       => getenv('HTR_CLIENT_ID'),
		'urlAccessToken' => getenv('HTR_TOKEN_URI'),
		'scope'          => array('openid profile'),
		'grantType'      => 'password'
	),
	"transcribathon" => array(
		"endpoint" => getenv('TP_API_ENDPOINT'),
		"apiToken" => getenv('TP_API_TOKEN')
	)
);

$testProcessId = 3037920;

// create new client and inject configuration
$transkribusClient = new TranskribusClient($config);

// get status and result as JSON from Transkribus
/* !d($transkribusClient->getJSONDatafromTranskribus($testProcessId)); */

// get PageXML from Transkribus
/* !d($transkribusClient->getPageXMLfromTranskribus(2959244)); */

// post a Image to Transkribus
$testItemId = 39330663;
$testImageUrl = 'https://rhus-209.man.poznan.pl/fcgi-bin/iipsrv.fcgi?IIIF=11/5//2020601/https___1914_1918_europeana_eu_contributions_21865/21865.260648.original.tif/full/full/0/default.jpg';
$testHtrId = 2125;
/* !d($transkribusClient->submitDataToTranskribus($testItemId, $testImageUrl, $testHtrId)); */

// get an item from TP HTR database by HTR process ID
/* !d($transkribusClient->getDataFromTranscribathon(null, $testProcessId)); */

// get an item from TP HTR database by item id
/* !d($transkribusClient->getDataFromTranscribathon($testItemId)); */

// return just the fulltext textequiv
$result = $transkribusClient->getDataFromTranscribathon($testItemId);
$resultArray = json_decode($result, true);
$xmlString = $resultArray['data']['data'];
$xmlString = str_replace('xmlns=', 'ns=', $xmlString); // replace namespace
$xml = new SimpleXMLElement($xmlString);

$break = '<br />';
$text = '';
$textRegions = $xml->xpath('//Page/TextRegion');

foreach ($textRegions as $textRegion) {

	$textLines = $textRegion->xpath('TextLine');

	foreach ($textLines as $textLine) {

		$textElement = $textLine->xpath(('TextEquiv/Unicode'));

		$text .= $textElement[0] . $break;

	}

	$text .= $break;

}

echo $text;

/* $result = $xml->xpath('//TextLine//Unicode'); */
/* $text = implode('<br />', $result); */
/* echo $text; */

// get all items form TP HTR database
/* !d($transkribusClient->getDataFromTranscribathon()); */

/* !d($transkribusClient->updateDataToTranscribathon($testItemId, array('data_type' => 'json'))); */

// if errors show some
echo $transkribusClient->getLastError();
