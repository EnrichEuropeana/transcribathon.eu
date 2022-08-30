<?php
global $wpdb;
$myid = uniqid(rand()).date('YmdHis');
$base = 0;

$rootid = uniqid(rand()).date('Ymdhis');

$t = get_term_by('slug', strtolower(ICL_LANGUAGE_NAME), 'tct_languages');
if(isset($t) && is_object($t) && !empty($t)){
	$url_slug = "?l=".$t->term_id;
}else{
	$url_slug = "";	
}
			
// Translation for special keywords
$btrans = array("%ICL_LANGUAGE_NAME%"=>ICL_LANGUAGE_NAME,"\n"=>"<br />","%ICL_LANGUAGE_URLSLUG%"=>$url_slug,"%ICL_LANGUAGE_SHORT%"=>ICL_LANGUAGE_CODE);

if(isset($instance)){
	if(isset($instance['tct-boxes-box']) && is_array($instance['tct-boxes-box']) && sizeof($instance['tct-boxes-box'])>0){
		echo "<ul class=\"tct-featureboxes\">\n";
		foreach($instance['tct-boxes-box'] as $box){
			echo "<li class=\"list-item\">\n";
				// Image preparation
				if(isset( $box['tct-box-image']) && trim($box['tct-box-image']) != ""){ 
					$img = wp_get_attachment_image_src($box['tct-box-image'],'vantage-grid-loop');
					$image = " style=\"background-image: url(".$img[0].");\"";
				}else{
					$image = "";
				}
				echo "<div class=\"promo-block promo-block-image contrast\"".$image."> \n";
					// Link preparation
					if(isset($box['tct-box-url']) && trim($box['tct-box-url']) != ""){ 
						if(isset($box['tct-box-linktarget']) && trim($box['tct-box-linktarget']) != ""){ $target = " target=\"_blank\""; }else{ $target = ""; }
						$burl = strtr($box['tct-box-url'],$btrans);
						if(preg_match('/post:/i',$burl)){ $url = "/?p=".str_replace('post:','',str_replace(' ','',$burl)); }else{ $url = $burl; }
						$urlstart = "<div href=\"".$url."\"".$target." class=\"bmainlink\">\n";
						$urlend = "</div>\n";
					}else{
						$urlstart = "";	
						$urlend = "";
					}
					// Link - start
					echo $urlstart;
					// Text in the middle
					if(isset($box['tct-box-text']) && trim($box['tct-box-text']) != ""){
						echo "<div class=\"inner\">\n";
							echo "<div class=\"title\">".strtr($box['tct-box-text'],$btrans)."</div>\n";
						echo "</div>\n"; 
					}
					// Link - end
					echo $urlend;
					// Claim, top left corner
					if(isset($box['tct-box-headline']) && trim($box['tct-box-headline']) != ""){ echo "<div class=\"category-flag\">".$box['tct-box-headline']."</div>\n"; }
				echo "</div>\n";
			echo "</li>\n";
		}
		echo "</ul>\n"; 
		echo "<p class=\"clr\"></p>\n";
	}
}

?>