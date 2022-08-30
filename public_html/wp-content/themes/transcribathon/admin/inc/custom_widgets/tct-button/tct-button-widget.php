<?php
/*
Widget Name: Button
Description: Displays a preformatted button
Author: Me
Author URI: http://www.example.de
*/ 


class TCT_Button_Widget extends SiteOrigin_Widget {

    function get_template_name($instance) {
        return 'tct-button-widget-template';
    }
	function get_template_dir($instance) {
    	return '';
	}
    function get_style_name($instance) {
        return 'tct-button-widget';
    }
	function __construct() {
		
		parent::__construct(
			'tct-button-widget',
			_x('Transcribathon - Button', 'button-widget (backend)','transcribathon'),
			array(
				'description' => _x('A big button', 'button-widget (backend)','transcribathon'),
				'panels_groups' => array('transcribathon'),
				'panels_icon' => 'tct-button-icon',
				'help'        => ''
			),
			array(
				'icon' => 'dashicons-edit',
			),
			array(
				'tct-button-label' => array(
					'type' => 'text',
					'label' => _x('Button-Label', 'button-widget (backend)','transcribathon'),
					'default' => '',
					'description' => _x('Please enter a label for this Button', 'button-widget (backend)','transcribathon'),
				),
				'tct-button-url' => array(
					'type' => 'link',
					'label' => _x('Please enter a URL or select existing content to link to', 'button-widget (backend)','transcribathon'),
					'default' => ''
				),
				'tct-button-linktarget' => array(
					'type' => 'checkbox',
					'label' => _x('Open link in a new window', 'button-widget (backend)','transcribathon'),
					'default' => false
				),
				'tct-button-color' => array(
					'type' => 'color',
					'label' => _x('Adjust the button-Color if needed', 'button-widget (backend)','transcribathon'),
					'default' => '#7f3978'
				),
				'tct-button-icon' => array(
					'type' => 'icon',
					'label' => _x('Select an icon', 'button-widget (backend)','transcribathon'),
				)
			), 
			plugin_dir_path(__FILE__)
		);
		
	}
	
}

siteorigin_widget_register('tct-button-widget', __FILE__, 'TCT_Button_Widget');
?>