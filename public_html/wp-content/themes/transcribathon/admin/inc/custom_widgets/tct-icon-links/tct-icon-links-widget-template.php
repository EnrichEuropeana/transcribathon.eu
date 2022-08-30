<?php
global $wpdb;
$myid = uniqid(rand()).date('YmdHis');
$base = 0;


$content = "";

$content .= "<section id='frontpage-bottom'>";
    $content .= "<div>";
        $content .= "<ul id='frontpage-bottom-link-list'>";
            $content .= "<li>";
                $content .= "<a href='".network_home_url()."members' class='frontpage-bottom-link'>";
                $content .= "<div class='frontpage-bottom-link-div theme-color-hover' style='height: 100%;'>";
                    $content .= "<i class='far fa-users frontpage-bottom-icon'></i>";    
                    $content .= "<h5 class='theme-color theme-hover-child'>MEMBERS</h5>";
                $content .= "</div>";
                $content .= "</a>";
            $content .= "</li>";

            $content .= "<li>";
                $content .= "<a href='".home_url()."/map' class='frontpage-bottom-link'>";
                $content .= "<div class='frontpage-bottom-link-div theme-color-hover' style='height: 100%;'>";
                    $content .= "<i class='far fa-map-marked-alt frontpage-bottom-icon'></i>";
                    $content .= "<h5 class='theme-color theme-hover-child'>DOCUMENT MAP</h5>";
                $content .= "</div>";
            $content .= "</a>";
            $content .= "</li>";

            $content .= "<li>";
                $content .= '<a href="'.home_url().'/progress" class="frontpage-bottom-link">';
                $content .= "<div class='frontpage-bottom-link-div theme-color-hover' style='height: 100%;'>";
                    $content .= "<i class='fal fa-chart-pie frontpage-bottom-icon'></i>";    
                    $content .= "<h5 class='theme-color theme-hover-child'>PROGRESS</h5>";
                $content .= "</div>";
            $content .= "</a>";
            $content .= "</li>";
                    
        $content .= "</ul>";
    $content .= "</div>";

$content .= "</section>";

echo $content;
?>