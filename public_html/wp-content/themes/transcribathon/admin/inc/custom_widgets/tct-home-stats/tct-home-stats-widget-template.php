<?php
global $wpdb;
$myid = uniqid(rand()).date('YmdHis');
$base = 0;




// Total characters
$url = TP_API_HOST."/tp-api/statistics/characters";
$requestType = "GET";
include TCT_THEME_DIR_PATH."admin/inc/custom_scripts/send_api_request.php";
$characters = json_decode($result, true);

// Total enrichments
$url = TP_API_HOST."/tp-api/statistics/enrichments";
$requestType = "GET";
include TCT_THEME_DIR_PATH."admin/inc/custom_scripts/send_api_request.php";
$enrichments = json_decode($result, true);

// Total items
$url = TP_API_HOST."/tp-api/statistics/items";
$requestType = "GET";
include TCT_THEME_DIR_PATH."admin/inc/custom_scripts/send_api_request.php";
$items = json_decode($result, true);

// Build Statistics layout
$content = '';
$content .= '<div
    class="
	    sm:flex
		sm:flex-row
		border-y
		border-gray-300
		justify-center
		p-2
	">';

    $content .= '<div class="flex-initial basis-1/4">';
	    $content .= '<h3
		    class="
			    theme-color
				text-xl
				sm:text-2xl
				md:text-3xl
				lg:text-4xl
				font-bold
			">' . number_format(intval($items), 0, '.', ' ') . '</h3>';
		$content .= '<p
		    class="
			    theme-color
				text-sm
				sm:text-base
				md:text-lg
				lg:text-xl
				font-bold
				mt-0
			"> Documents </p>';
	$content .= '</div>';

	$content .= '<div class="flex-initial basis-1/4">';
	    $content .= '<h3
		    class="
			    theme-color
				text-xl
				sm:text-2xl
				md:text-3xl
				lg:text-4xl
				font-bold
			">' . number_format(intval($characters), 0, '.', ' ') . '</h3>';
		$content .= '<p
		    class="
			    theme-color
				text-sm
				sm:text-base
				md:text-lg
				lg:text-xl
				font-bold
				mt-0
			"> Characters </p>';
	$content .= '</div>';

	$content .= '<div class="flex-initial basis-1/4">';
	    $content .= '<h3
		    class="
			    theme-color
				text-xl
				sm:text-2xl
				md:text-3xl
				lg:text-4xl
				font-bold
			">' . number_format(intval($enrichments), 0, '.', ' ') . '</h3>';
		$content .= '<p
		    class="
			    theme-color
				text-sm
				sm:text-base
				md:text-lg
				lg:text-xl
				font-bold
				mt-0
			"> Enrichments </p>';
	$content .= '</div>';

$content .= '</div>';

echo $content;

?>
