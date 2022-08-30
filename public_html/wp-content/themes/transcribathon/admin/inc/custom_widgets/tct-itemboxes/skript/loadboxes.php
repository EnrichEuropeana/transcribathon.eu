<?php include(str_repeat("../",(sizeof(explode("/",substr((string)getcwd(),strrpos((string)getcwd(),"/wp-content"),strlen((string)getcwd()))))-2))."../wp-load.php");
global $wpdb,$mysql_con;


//{'q':'gtttrs','myid':myid,'base':base,'limit':limit}, function(res) 

if(isset($_POST['q']) && $_POST['q'] === "gmbxs"):
	$content = "";
	$tct_doccols = (int)$_POST['cols'];
	$toshow = explode("|",trim($_POST['ids'])); 
	/*
	$my_args = array('post_type'=>'documents','numberposts'=>12,'paged'=>true,'order_by'=>'post_date','order'=>'DESC',
		'post__in' => $toshow,
	);
	$str = get_posts($my_args);*/
	$limit = 12; 
	$url = TP_API_HOST."/tp-api/itemMinimal?itemId=".trim(implode(",", $toshow));
	$requestType = "GET";

	// Execude http request
	include dirname(__FILE__)."/../../../custom_scripts/send_api_request.php";

	// Save image data
	$itemData = json_decode($result, true);
	
	$content .= "<div class=\"section group sepgroup tab\">\n";
	global $post;
	$j = 1;
	$i=0;
	foreach($itemData as $item){
		if($i<$tct_doccols) {
				$i++; 
		} else { 
			$i=1; $content .=  "</div>\n<div class=\"section group tab sepgroup\">\n"; 
		}
		
			$content .=   '<div class="col span_1_of_4 collection">';
				$content .=   '<div class="dcholder">';
				
					$image = json_decode($item['ImageLink'], true);

					if (substr($image['service']['@id'], 0, 4) == "http") {
						$gridImageLink = $image['service']['@id'];
					}
					else {
						$gridImageLink = "http://".$image['service']['@id'];
					}

					if ($image["width"] != null || $image["height"] != null) {
						if ($image["width"] <= $image["height"]) {
							$gridImageLink .= "/0,0,".$image["width"].",".$image["width"];
						}
						else {
							$gridImageLink .= "/0,0,".$image["height"].",".$image["height"];
						}
					}
					else {
						$gridImageLink .= "/full";
					}
					$gridImageLink .= "/500,500/0/default.jpg";

					$content .=   "<a class='grid-view-image' href='".home_url()."/documents/story/item/?story=".$item['StoryId']."&item=".$item['ItemId']."'>";
						$content .=   '<img src='.$gridImageLink.'>';
					$content .=   "</a>";

					// Get status data
					$url = TP_API_HOST."/tp-api/completionStatus";
					$requestType = "GET";

					include dirname(__FILE__)."/../../../custom_scripts/send_api_request.php";

					// Save status data
					$statusTypes = json_decode($result, true);

					$statusData = array();
					foreach ($statusTypes as $statusType) {
						$statusObject = new stdClass;
						$statusObject->Id = $statusType['CompletionStatusId'];
						$statusObject->Name = $statusType['Name'];
						$statusObject->ColorCode = $statusType['ColorCode'];
						$statusObject->ColorCodeGradient = $statusType['ColorCodeGradient'];
						$statusObject->Amount = 0;
						$statusObject->Percentage = 0;
						$statusData[$statusType['Id']] = $statusObject;
					}
					// $itemAmount = 0;
					// foreach($story['CompletionStatus'] as $status) {
					//     $itemAmount += $status['Amount'];
					// }
					$statusCompletionId = $item['CompletionStatusId'];
					
					$totalPercent = 0;

					// Make sure that percent total is 100
					foreach ($statusData as $status) {
						if ($status->Name == "Not Started") {
							if ($totalPercent != 100) {
								$status->Percentage += (100 - $totalPercent);
							}
						}
					}
					$progressData = array(
						$item['TranscriptionStatusId'],
						$item['DescriptionStatusId'],
						$item['LocationStatusId'],
						$item['TaggingStatusId'],
						//$itemData['AutomaticEnrichmentStatusName'],
					);
					$progressCount = array (
									'1' => 0,
									'2' => 0,
									'3' => 0,
									'4' => 0
								);
					foreach ($progressData as $status) {
						$progressCount[$status] += 1;
					}                        
					$content .=  '<div class="box-progress-bar item-status-chart">';
						$content .=  '<div class="item-status-info-box box-status-bar-info-box">';
							$content .=  '<ul class="item-status-info-box-list">';
								
								foreach ($statusTypes as $status) {
									$percentage = ($progressCount[$status['CompletionStatusId']] / sizeof($progressData)) * 100;
									$content .=  '<li>';
										$content .=  '<span class="status-info-box-color-indicator" style="background-color:'.$status['ColorCode'].';
														background-image: -webkit-gradient(linear, left top, left bottom,
														color-stop(0, '.$status['ColorCode'].'), color-stop(1, '.$status['ColorCodeGradient'].'));">';
									$content .=  '</span>';
										$content .=  '<span id="progress-bar-overlay-'.str_replace(' ', '-', $status['Name']).'-section" class="status-info-box-percentage" style="width: 20%;">';
											$content .=  $percentage.'%';
										$content .=  '</span>';
										$content .=  '<span class="status-info-box-text">';
											$content .=  $status['Name'];
										$content .=  '</span>';
									$content .=  '</li>';
								}                                                          

							$content .= '</ul>';
						$content .= '</div>';

						$CompletedBar = "";
						$ReviewBar = "";
						$EditBar = "";
						$NotStartedBar = "";
						// Add each status section to progress bar
						foreach ($statusTypes as $status) {
							$percentage = ($progressCount[$status['CompletionStatusId']] / sizeof($progressData)) * 100;

							switch ($status['Name']) {
								case "Completed":
									$CompletedBar .= '<div id="progress-bar-'.str_replace(' ', '-', $status['Name']).'-section" class="progress-bar progress-bar-section"
														style="width: '.$percentage.'%; background-color:'.$status->ColorCode.';
														">';
										$CompletedBar .= $percentage.'%';
									$CompletedBar .= '</div>';
									break;
								case "Review":
									$ReviewBar .= '<div id="progress-bar-'.str_replace(' ', '-', $status['Name']).'-section" class="progress-bar progress-bar-section"
														style="width: '.$percentage.'%; background-color:'.$status->ColorCode.'">';
										$ReviewBar .= $percentage.'%';
									$ReviewBar .= '</div>';
									break;
								case "Edit":
									$EditBar .= '<div id="progress-bar-'.str_replace(' ', '-', $status['Name']).'-section" class="progress-bar progress-bar-section"
														style="width: '.$percentage.'%; background-color:'.$status->ColorCode.'">';
										$EditBar .= $percentage.'%';
									$EditBar .= '</div>';
									break;
								case "Not Started":
									$NotStartedBar .= '<div id="progress-bar-'.str_replace(' ', '-', $status['Name']).'-section" class="progress-bar progress-bar-section"
														style="width: '.$percentage.'%; background-color:'.$status->ColorCode.'">';
										$NotStartedBar .= $percentage.'%';
									$NotStartedBar .= '</div>';
									break;
							}
						}
						
						if ($CompletedBar != "") {
							$content .= $CompletedBar;
						}
						if ($ReviewBar != "") {
							$content .= $ReviewBar;
						}
						if ($EditBar != "") {
							$content .= $EditBar;
						}
						if ($NotStartedBar != "") {
							$content .= $NotStartedBar;
						}
					$content .= '</div>';
					
				$content .=  '</div>';

				$content .=   '<div class="">';
					$content .=   '<h3 class="theme-color">';
						$content .=   "<a class='storybox-title' href='".home_url()."/documents/story/item/?story=".$item['StoryId']."&item=".$item['ItemId']."'>";
							$content .=   $item['Title'];
						$content .=   "</a>";
					$content .=   '</h3>';
					/*
					$content .=   '<div class="search-page-single-result-description">';
						$content .=   $story['dcDescription'];
					$content .=   '</div>';*/
					$content .=   '<span style="display: none">...</span>';
				$content .=   '</div>';
				
				$content .=   '<div style="clear:both"></div>';
			$content .=   '</div>';

		//include(locate_template('document.php'));
		//get_template_part(document);
		if ($j >= $limit) {
			break;
		}
		else if($tct_doccols === 4){
			if($i==2){ $content .=  "<span class=\"sep\"></span>\n";}
		}
		else if($tct_doccols === 3){
			if($i==2){ $content .=  "<span class=\"sep\"></span>\n";}
		}
		$j++;
	}
	wp_reset_postdata();
	$content .= "</div>\n";	
	

	//Rueckgabe 
	$res = array();
	$res['status'] = 'ok';
	$res['boxes'] = $content;
	header("Content-Type: text/json; charset=utf-8");
	echo trim(json_encode($res));
endif;	

?>