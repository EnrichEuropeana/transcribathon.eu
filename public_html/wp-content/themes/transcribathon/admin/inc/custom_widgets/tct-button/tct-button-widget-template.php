<?php
global $wpdb;
$myid = uniqid(rand()).date('YmdHis');
$base = 0;

if($instance['id'] != ""){ echo "<p><a id=\"".$instance['id']."\" class=\"jumper\"></a></p>\n"; }
$icon_styles = array();
$icon_styles[] = 'font-size: 2.4rem';
$icon_styles[] = 'color: #fff';
echo "<!-- DEBUG: ".$instance['tct-button-url']."-->\n";
echo "\n<div class=\"tct-widget-button\">\n<div>\n";
echo "<div class=\"tct-button-base\">\n";
if(isset($instance['tct-button-color']) && trim($instance['tct-button-color']) != ""){ $col = $instance['tct-button-color']; }else{ $col = "#444"; }
if(preg_match('/post:/i',$instance['tct-button-url'])){ $url = "/?p=".str_replace('post:','',str_replace(' ','',$instance['tct-button-url'])); }else{ $url = $instance['tct-button-url']; }

if(preg_match('/%TRANSREQUEST%/i',$instance['tct-button-url'])){
	reset($_GET); $obj = (int)esc_html(stripslashes(key($_GET)));
	echo "<a class=\"tct-button-hover\" style=\"background:".$col."; border: 1px solid ".$col.";\" href=\"\" onclick=\"openOverlay('trnsreqst-".$obj."','".ICL_LANGUAGE_CODE."'); return false;\">\n";
}else{
	if(isset($instance['tct-button-linktarget']) && trim($instance['tct-button-linktarget']) != "" && $instance['tct-button-linktarget'] != false){ $tg = " target=\"_blank\""; }else{ $tg = ""; }
	echo "<a class=\"tct-button-hover\" style=\"background:".$col."; border: 1px solid ".$col.";\" href=\"".$url."\"".$tg.">\n";
}

echo "<span>\n";
if(isset($instance['tct-button-icon']) && trim($instance['tct-button-icon']) != ""){
	echo siteorigin_widget_get_icon( $instance['tct-button-icon'], $icon_styles ); 
}
if(isset($instance['tct-button-label']) && trim($instance['tct-button-label']) != ""){					
	echo $instance['tct-button-label'];
}
echo "</span>\n";
echo "</a>\n";
//echo "<pre>".print_r($_GET,true)."</pre>\n";
echo "</div>\n";
echo "</div>\n</div>\n";
?>