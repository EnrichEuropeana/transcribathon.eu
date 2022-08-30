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

echo "<div style=\"text-align: center\" class=\"home-stats-content section group\">\n";
	echo "<div class=\"column-rgs span_1_of_3\">\n";
		echo "<div class=\"stat-number\">\n";
			echo "<h3 style=\"\" class=\"theme-color\">".strrev(chunk_split(strrev($items) , 3 , ' '))."  <span>Documents</span></h3>\n";
		echo "</div>\n"; 
	echo "</div>\n";
	echo "<div class=\"column-rgs span_1_of_3\">\n";
		echo "<div class=\"stat-number\">\n";
			echo "<h3 style=\"\" class=\"theme-color\"> ".strrev(chunk_split(strrev($characters) , 3 , ' '))." <span>Characters</span></h3>\n";
		echo "</div>\n";
	echo "</div>\n";
	echo "<div class=\"column-rgs span_1_of_3\">\n";
		echo "<div class=\"stat-number\">\n";
			echo "<h3 style=\"\" class=\"theme-color\">".strrev(chunk_split(strrev($enrichments) , 3 , ' '))."  <span>Enrichments</span></h3>\n";
		echo "</div>\n";
	echo "</div>\n";
echo "</div>\n";

?>
