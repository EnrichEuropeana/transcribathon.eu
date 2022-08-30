<?php
/*
Widget Name: Headline for Homepage
Description: Displays the headline for a project
Author: Me
Author URI: http://www.example.de
*/


class TCT_Headline_Widget extends SiteOrigin_Widget {

   
    function get_template_name($instance) {
        return 'tct-headline-widget-template';
    }
	function get_template_dir($instance) {
    	return '';
	}
	function __construct() {
		
		parent::__construct(
			'tct-headline-widget',
			_x('Transcribathon - Headline', 'headline-widget (backend)','transcribathon'),
			array(
				'description' => _x('Displays main- and sub-headline or one of those', 'headline-widget (backend)','transcribathon'),
				'panels_groups' => array('transcribathon'),
				'panels_icon' => 'tct-headline-icon',
				'help'        => ''
			),
			array(
				'icon' => 'dashicons-edit',
			),
			array(
				'h1' => array(
					'type' => 'textarea',
					'label' => _x('Headline', 'headline-widget (backend)','transcribathon'),
					'default' => '',
					'rows' => 1
				),
				'h3' => array(
					'type' => 'textarea',
					'label' => _x('Sub headline', 'headline-widget (backend)','transcribathon'),
					'default' => '',
					'rows' => 1
				)
			), 
			plugin_dir_path(__FILE__)
		);
		
	}
	
}

siteorigin_widget_register('tct-headline-widget', __FILE__, 'TCT_Headline_Widget');


?>