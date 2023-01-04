<?php

session_id() or session_start();
include_once($_SERVER['DOCUMENT_ROOT'] . '/wp-load.php');
header("content-type: text/css");
$theme_sets = get_theme_mods();

// Navigation-Color
echo "nav[role=navigation] ul#menu-main-menu li a{color:".$theme_sets['vantage_general_link_color'].";}\n";
echo "nav[role=navigation] ul#menu-main-menu li a:hover{background-color:".$theme_sets['vantage_general_link_hover_color']."; color:#fff;}\n";
echo "h1{color:".$theme_sets['vantage_general_link_color']." !important;}\n";
echo ".theme-color-hover:not(.active):hover{
    color: #fff !important;
    background-color: ".$theme_sets['vantage_general_link_hover_color']." !important;
}";
echo ".theme-color-hover:hover .theme-hover-child{
    color: #fff !important;
    background-color: ".$theme_sets['vantage_general_link_hover_color']." !important;
}";

echo ".theme-color{
    color: ".$theme_sets['vantage_general_link_color']." !important;
}";

echo ".widget_headline-widget h3{
    color: ".$theme_sets['vantage_general_link_color']." !important;
}";

echo ".theme-color-background{
    color: #fff !important;
    background-color: ".$theme_sets['vantage_general_link_hover_color']." !important;
    background-image: none !important;
}";

echo "div.um-profile-nav div.um-profile-nav-item a:hover{
    border-color: ".$theme_sets['vantage_general_link_hover_color']." !important;
}";
echo ".theme-color-border{
    border-color: ".$theme_sets['vantage_general_link_hover_color']." !important;
}";
echo "div.um-profile-nav div.um-profile-nav-item.active a{
    border-color: ".$theme_sets['vantage_general_link_hover_color']." !important;
}";
echo ".um-button{
    color: #fff !important;
    background-color: ".$theme_sets['vantage_general_link_hover_color']." !important;
    background-image: none !important;
}";

// Project-Navigation
$sites = get_sites(array('site__not_in'=>array('1'),'deleted'=>0));
foreach($sites as $s){
    switch_to_blog($s->blog_id);
    $tmp = get_theme_mod('vantage_general_link_hover_color');
    echo "ul#_transcribathon_topmenu li ul li.top_nav_point-".$s->blog_id." a:hover{background-color:".$tmp." ;}\n";
}


?>
