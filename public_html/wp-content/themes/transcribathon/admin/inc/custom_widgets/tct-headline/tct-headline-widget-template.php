<?php
global $wpdb;
$myid = uniqid(rand()).date('YmdHis');
$base = 0;

if($instance['h1'] != ""){ echo "<h1>".str_replace("\n","<br />",$instance['h1'])."</h1>\n"; }
if($instance['h3'] != ""){ echo "<h3>".str_replace("\n","<br />",$instance['h3'])."</h3>\n"; }

?>