<?php
global $wpdb;

$content = '';

if($instance['h1'] != '') {
    $content .= '<h1
        class="
            theme-color
            text-xl
            uppercase
            font-bold
            tracking-wide
        ">' . str_replace("\n", "<br>", $instance['h1']) . '</h1>';
}

if($instance['h3'] != '') {
    $content .= '<h3
        class="
            theme-color
            text-base
            uppercase
            font-bold
            tracking-wide
        ">' . str_replace("\n", "<br>", $instance['h3']) . '</h3>';
}


echo $content;


?>