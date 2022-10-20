<?php

require_once('config.php');
require_once('lib/HtrData.php');
require_once('lib/TranskribusClient.php');

use FactsAndFiles\Transcribathon\TranskribusClient;

$TranskribusClient = new TranskribusClient($config);

$itemId = ($_GET['itemId'] && $_GET['itemId'] !== 'null')
	? $_GET['itemId']
	: null;

$storyId = ($_GET['storyId'] && $_GET['storyId'] !== 'null')
	? $_GET['storyId']
	: null;

$htrId = $_GET['htrId'] ?? null;

$htrModel = $_GET['htrModel'] ?? null;

$htrUserData = file_get_contents('php://input');

if ($storyId && $itemId) {
	echo '{"error":"Please, choose a storyID or an itemID."}';
	exit(1);
}

if (($storyId || $itemId) && $htrId) {

	$path = $storyId
		? '/stories/' . $storyId
		: '/items/' . $itemId;

	$HtrData = new HtrData($config);

	// get itemData with Java API
	$oldApiEndpoint .= $path;
	$queryOptions = array(
		'http' => array(
			'ignore_errors' => true,
			'header' => array(
				'Content-type: application/json'
			),
			'method' => 'GET'
		)
	);

	$queryData = $HtrData::sendQuery($oldApiEndpoint, $queryOptions);

	if (!$queryData) {
		echo '{"error":"An error occurred when consuming the Java API."}';
		exit(1);
	}

	$queryDataArray = json_decode($queryData, true);

	$itemsData = $storyId ? $queryDataArray[0]['Items'] : array($queryDataArray);

	$sendedData = $HtrData->sendStoryData($itemsData, $htrId);

	echo $sendedData;

	exit(0);
}

if ($htrModel) {

	$htrModels = $TranskribusClient->getAllHtrModels();

	echo $htrModels;

	exit(0);
}

if ($htrUserData) {

	$htrUserDataArray = json_decode($htrUserData, true);
	$htrUserTranscritpion = $TranskribusClient->postToTranscribathon($htrUserDataArray);

	if (!$htrUserTranscritpion) {
		echo '{ "error": ' . $TranskribusClient->getLastError() . '}';
		exit(1);
	}

	echo $htrUserTranscritpion;

	exit(0);

}
