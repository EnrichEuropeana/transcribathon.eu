<?php
global $wpdb;
$myid = uniqid(rand()).date('YmdHis');
$base = 0;

//$cols = wp_kses_post($instance['amount']);
$id = "hpm-".uniqid(rand()).date('Ymdhis');

if(isset($instance['tct-colcontent-content']) && trim($instance['tct-colcontent-content']) != ""): 
	echo "<div class=\"colcontent\">";
	echo $instance['tct-colcontent-content'];
	echo "</div>\n"; // .colcontent
endif;

?>