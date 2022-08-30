<?php
global $wpdb;
$myid = uniqid(rand()).date('YmdHis');
$base = 0;

if(isset($instance)){
	if(isset($instance['tct-tutorial-slider-box']) && is_array($instance['tct-tutorial-slider-box']) && sizeof($instance['tct-tutorial-slider-box'])>0){
		if(isset($instance['tct-main-headline']) && trim($instance['tct-main-headline']) != ""){
			echo "<h1>".$instance['tct-main-headline']."</h1>\n";
		}
		echo "<ul class=\"tct-tutorial-slider\" id=\"tct-tutorial-slider-".$rootid."\">\n";
			$i=0;
			foreach($instance['tct-tutorial-slider-box'] as $step){
				if($i<1){ $cls = "class=\"open\""; }else{ $cls = ""; }
				echo "<li id=\"tct-tutorial-slider-".$rootid."-".$i."\" ".$cls.">\n";
					//headline
					if(isset($step['tct-step-headline']) && trim($step['tct-step-headline']) != ""){
						echo "<h3>".$step['tct-step-headline']."</h3>\n";	
						$alt = $step['tct-step-headline'];
					}else{
						echo "<h3>&nbsp;</h3>\n";
						$alt = "";
					}
					// Image
					if(isset( $step['tct-step-image']) && trim($step['tct-step-image']) != ""){ 
						//$img = wp_get_attachment_image_src($step['tct-step-image'],'full');
						//$image = "<img id=\"img-".$rootid."-".$i."\" src=\"".$img[0]."?".date('YmdHis')."\" alt=\"".$alt."\" />";
						$image = wp_get_attachment_image($step['tct-step-image'] ,'full','',array( "id" => "img-".$rootid."-".$i,"alt" => $alt));
					}else{
						$image = "";
					}
					echo "\n<div class=\"tct-tsl-imagebox\" id=\"tct-tutorial-slider-img-".$rootid."-".$i."\">".$image."</div>\n";
					// Text
					if(isset( $step['tct-step-text']) && trim($step['tct-step-text']) != ""){ 
						echo "<p>".str_replace("\n","<br />",$step['tct-step-text'])."</p>\n";
					}
					// navigation
					if(sizeof($instance['tct-tutorial-slider-box'])>1){
						echo "<div class=\"tct-tutorial-slider-navbar\">\n";
						//prev
						if($i>0){
							echo "<a href=\"\" onclick=\"tctTutorialStep('".($i-1)."','".$rootid."'); return false;\" class=\"prevbut\">"._x('Previous', 'Tutorial-Slider-Widget (frontentd)','transcribathon')."</a>\n";
						}else{
							echo "<span class=\"prevbut\">"._x('Previous', 'Tutorial-Slider-Widget (frontentd)','transcribathon')."</span>\n";
						}
						//next
						if(sizeof($instance['tct-tutorial-slider-box'])>($i+1)){
							echo "<a href=\"\" onclick=\"tctTutorialStep('".($i+1)."','".$rootid."'); return false;\" class=\"nextbut\">"._x('Next', 'Tutorial-Slider-Widget (frontentd)','transcribathon')."</a>\n";
						}else{
							echo "<span class=\"nextbut\">"._x('Next', 'Tutorial-Slider-Widget (frontentd)','transcribathon')."</span>\n";
						}
						echo "</div>\n";
					}
				echo "</li>\n";
				$i++;
			}
		echo "</ul>\n";
	}
}

?>