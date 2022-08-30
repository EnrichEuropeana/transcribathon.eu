<?php
/*
Widget Name: Statistic for Homepage
Description: Displays the current numbers for a project
Author: Piktoresk | Olaf Baldini
Author URI: http://www.piktoresk.de
*/


class TCT_Home_Stats_Widget extends SiteOrigin_Widget {

   
	function __construct() {
		
		parent::__construct(
			'tct-home-stats-widget',
			_x('Transcribathon - Statistic for Landingpage', 'tct-home-stats-widget (backend)','transcribathon'),
			array(
				'description' => _x('Displays the current statistics for a project landingpage', 'tct-home-stats-widget (backend)','transcribathon'),
				'panels_groups' => array('transcribathon'),
				'panels_icon' => 'tct-home-stats-icon',
				'help'        => ''
			),
			array(
				'icon' => 'dashicons-edit',
			),
			array(
				'tct-home-stats-headline' => array(
					'type' => 'text',
					'label' => _x('Headline above the statistics (optional)', 'tct-home-stats-widget (backend)','transcribathon'),
					'default' => 'Top Transcribers',
				)
			), 
			plugin_dir_path(__FILE__)
		);
	}
	
	
	
	function get_template_name($instance) {
        return 'tct-home-stats-widget-template';
    }
	function get_template_dir($instance) {
    	return '';
	}
	
}

siteorigin_widget_register('tct-home-stats-widget', __FILE__, 'TCT_Home_Stats_Widget');


?>