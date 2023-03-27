<?php

require_once($_SERVER['DOCUMENT_ROOT'] . '/wp-load.php');
require_once('config.php');
require_once('lib/HtrData.php');
require_once('lib/TranskribusClient.php');

use FactsAndFiles\Transcribathon\TranskribusClient;

if (!is_user_logged_in()) {
	echo '{"error":"We think it is not safe to do this right now."}';
	exit(1);
}

$TranskribusClient = new TranskribusClient($config);

$itemId = ($_GET['itemId'] && $_GET['itemId'] !== 'null')
	? $_GET['itemId']
	: null;

$storyId = ($_GET['storyId'] && $_GET['storyId'] !== 'null')
	? $_GET['storyId']
	: null;

$htrModelId = $_GET['htrModelId'] ?? null;

$languageId = $_GET['languageId'] ?? null;

$htrModel = $_GET['htrModel'] ?? null;

$languages = $_GET['languages'] ?? null;

$htrUserData = file_get_contents('php://input');

if ($storyId && $itemId) {
	echo '{"error":"Please, choose a storyID or an itemID."}';
	exit(1);
}

if (($storyId || $itemId) && $htrModelId) {

	$path = $storyId
		? '/items?limit=1000&StoryId=' . $storyId
		: '/items/' . $itemId;

	$HtrData = new HtrData($config);

	$apiV2Endpoint = $config['transcribathon']['endpoint'] . $path;
	$queryOptions = array(
		'http' => array(
			'ignore_errors' => true,
			'header' => array(
				'Content-type: application/json',
				'Authorization: Bearer ' . $config['transcribathon']['apiToken']
			),
			'method' => 'GET'
		)
	);

	$queryData = $HtrData::sendQuery($apiV2Endpoint, $queryOptions);

	if (!$queryData) {
		echo '{"error":"An error occurred while getting the item data."}';
		exit(1);
	}

	$queryDataArray = json_decode($queryData, true);

	$itemsData = $storyId
		? $queryDataArray['data']
		: [$queryDataArray['data']];

	$sendedData = $HtrData->sendStoryData($itemsData, $htrModelId, $languageId);

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
	$htrUserTranscription = $TranskribusClient->postToTranscribathon($htrUserDataArray);

	if (!$htrUserTranscription) {
		echo '{ "error": ' . $TranskribusClient->getLastError() . '}';
		exit(1);
	}

	echo $htrUserTranscription;

	exit(0);

}

if ($languages) {

	$languageEndpoint = $config['transcribathon']['endpoint'] . '/languages?orderBy=LanguageId&orderDir=asc';
	$languageQueryOptions = array(
		'http' => array(
			'ignore_errors' => true,
			'header' => array(
				'Content-type: application/json',
				'Authorization: Bearer ' . $config['transcribathon']['apiToken']
			),
			'method' => 'GET'
		)
	);

	$languageQueryData = HtrData::sendQuery($languageEndpoint, $languageQueryOptions);

	echo $languageQueryData;

	exit(0);

}
