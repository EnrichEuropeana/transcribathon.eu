<?php
global $wpdb;
$myid = uniqid(rand()).date('YmdHis');
$base = 0;

if($instance['id'] != ""){ echo "<p><a id=\"".$instance['id']."\" class=\"jumper\"></a></p>\n"; }
echo "<hr />"; 

?>