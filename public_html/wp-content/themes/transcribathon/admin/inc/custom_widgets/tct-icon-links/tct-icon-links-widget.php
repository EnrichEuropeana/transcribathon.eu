<?php 
/*
Widget Name: icon bottom for frontpage
Description: Displays the icon bottom for a project
Author: Me
Author URI: http://www.example.de
*/

class TCT_Icon_Links_Widget extends SiteOrigin_Widget {

	function __construct() {
		
		parent::__construct(
			'tct-icon-links-widget',
			_x('Transcribathon - Icon links', 'tct-icon-links-widget (backend)','transcribathon'),
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
				'tct-icon-links-headline' => array(
					'type' => 'text',
					'label' => _x('Headline (optional)', 'tct-icon-links-widget (backend)','transcribathon'),
					'default' => '',
				)
            ), 
			plugin_dir_path(__FILE__)
		);
	}
	
	
	
	function get_template_name($instance) {
        return 'tct-icon-links-widget-template';
    }
	function get_template_dir($instance) {
    	return '';
	}
	function get_style_name($instance) {
        return 'tct-icon-links-widget';
    }
}





siteorigin_widget_register('tct-icon-links-widget', __FILE__, 'TCT_Icon_Links_Widget');

?>