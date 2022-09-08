<?php

require 'config.php';
require 'lib/HtrData.php';
require 'lib/TranskribusClient.php';

use FactsAndFiles\Transcribathon\TranskribusClient;

$itemId = ($_GET['itemId'] && $_GET['itemId'] !== 'null')
	? $_GET['itemId']
	: null;

$storyId = ($_GET['storyId'] && $_GET['storyId'] !== 'null')
	? $_GET['storyId']
	: null;

$htrId = $_GET['htrId'] ?? null;

$htrModel = $_GET['htrModel'] ?? null;

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

	$queryData = $HtrData->sendQuery($oldApiEndpoint, $queryOptions);

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

	$HtrModelData = new TranskribusClient($config);

	$htrModels = $HtrModelData->getAllHtrModels();

	echo $htrModels;

	exit(0);
}
