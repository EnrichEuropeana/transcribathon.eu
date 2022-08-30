<?php 
if(isset($instance['tct-numbers-kind']) && trim($instance['tct-numbers-kind']) != ""){
	echo "<div class=\"tct-number\">\n";	
		$requestType = "GET";
		echo "<div class='numbers-widget-container'>";
			switch($instance['tct-numbers-kind']){
				case "uploaded-items":
					$url = TP_API_HOST."/tp-api/statistics/items";
					include dirname(__FILE__)."/../../custom_scripts/send_api_request.php";
					$data = json_decode($result, true);
					echo "<span class='numbers-widget-number'>".$data."</span>\n";
					echo "</br>";
					echo "<span class='numbers-widget-text'>"._x('Documents uploaded','numbers-widget: number of uploaded items (back- and frontend)','transcribathon')."</span>\n";
				break;
				case "started-items":
					$url = TP_API_HOST."/tp-api/statistics/itemsStarted";
					if ($instance['tct-numbers-campaign'] != null) {
						$url .= "?campaign=".$instance['tct-numbers-campaign'];
					}
					else if ($instance['tct-numbers-dataset'] != null) {
						$url .= "?dataset=".$instance['tct-numbers-dataset'];
					}
					include dirname(__FILE__)."/../../custom_scripts/send_api_request.php";
					$data = json_decode($result, true);
					echo "<span class='numbers-widget-number'>".$data."</span>\n";
					echo "</br>";
					echo "<span class='numbers-widget-text'>"._x('Documents started','numbers-widget: number of started items (back- and frontend)','transcribathon')."</span>\n";
				break;
				case "total-characters":
					$url = TP_API_HOST."/tp-api/statistics/characters";
					if ($instance['tct-numbers-campaign'] != null) {
						$url .= "/campaign/".$instance['tct-numbers-campaign'];
					}
					else if ($instance['tct-numbers-dataset'] != null) {
						$url .= "?dataset=".$instance['tct-numbers-dataset'];
					}
					include dirname(__FILE__)."/../../custom_scripts/send_api_request.php";
					$data = json_decode($result, true);
					// if($data != null){
					echo "<span class='numbers-widget-number'>".strrev(chunk_split(strrev($data) , 3 , ' '))."</span>\n";
					//echo "<span class='numbers-widget-number'>0</span>\n";
					// }else{
					// 	echo "<span class='numbers-widget-number'>0</span>\n";
					// }	
				echo "</br>";
					echo "<span class='numbers-widget-text'>"._x('Total Characters','numbers-widget: number of started items (back- and frontend)','transcribathon')."</span>\n";
				break;
			}
		echo "</div>";
	echo "</div>\n";
}

?>