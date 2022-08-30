<?php
/*
Widget Name: Horizontal line widget
Description: Displays the horizontal line
Author: Piktoresk | Olaf Baldini
Author URI: http://www.piktoresk.de
*/


class TCT_Horizontal_line_Widget extends SiteOrigin_Widget {

	function get_template_name($instance) {
		return 'tct-horizontal-line-widget-template';
	}
	function get_template_dir($instance) {
		return '';
	}
	function get_style_name($instance) {
		return 'tct-horizontal-line-widget';
	}
	function __construct() {
		
		parent::__construct(
			'tct-horizontal-line-widget',
			_x('Transcribathon - Horizontal line', 'line-widget (backend)','transcribathon'),
			array(
				'description' => _x('Horizontal line - optional including an anchor', 'line-widget (backend)','transcribathon'),
				'panels_groups' => array('transcribathon'),
				'panels_icon' => 'tct-trenner-icon',
				'help'        => ''
			),
			array(
				'icon' => 'dashicons-edit',
			),
			array(
				'id' => array(
					'type' => 'text',
					'label' => _x('Anchor-ID', 'line-widget (backend)','transcribathon'),
					'default' => '',
					'description' => __('If you would like to add an anchor to this position, please enter an ID. the ID must not contain empty spaces or special characters (only a-z, 0-9 and underscores/minus-characters).', 'line-widget (backend)','transcribathon'),
				)
			), 
			plugin_dir_path(__FILE__)
		);
		
	}
	
}

siteorigin_widget_register('tct-horizontal-line-widget', __FILE__, 'TCT_Horizontal_line_Widget');


?>