<?php
/*
Widget Name: Line Docstrt
Description: Displays a line docstrt
Author: Me
Author URI: http://example.com
*/

class _TCT_Progress_Line_Docstrt_Widget extends SiteOrigin_Widget {

	function __construct() {

		parent::__construct(
			'tct-progress-line-docstrt-widget',
			_x('Transcribathon - Line Docstrt', 'tct-progress-line-docstrt-widget (backend)','transcribathon'),
			array(
				'description' => _x('Displays a line docstrt graph', 'tct-progress-line-docstrt-widget (backend)','transcribathon'),
				'panels_groups' => array('transcribathon'),
				'panels_icon' => 'tct-top-transcribers-icon',
				'help'        => ''
			),
			array(
				'icon' => 'dashicons-edit',
			),
			$form_options = array(
				'tct-progress-line-docstrt-headline' => array(
					'type' => 'select',
					'label' => _x('Headline above the list', 'tct-progress-line-docstrt-widget (backend)','transcribathon'),
					//'default' => 'Top Transcribers',
					'options' => array(
						'Transcribers' => __( 'Transcribers', 'tct-progress-line-docstrt-widget (backend)','transcribathon'),
						'Stories completed' => __( 'Stories completed', 'tct-progress-line-docstrt-widget (backend)','transcribathon'),
						'Documents started' => __( 'Documents started', 'tct-progress-line-docstrt-widget (backend)','transcribathon'),
						'Documents completed' => __( 'Documents completed', 'tct-progress-line-docstrt-widget (backend)','transcribathon'),
						'Transcribed characters' => __( 'Transcribed characters', 'tct-progress-line-docstrt-widget (backend)','transcribathon'),

					)
				)
			,
			'image' => array(
					'type' => 'media',
					'library' => 'image',
					'label' => 'chart ID',
				
				) 
			
			),
			plugin_dir_path(__FILE__)
		);
	}



	function get_template_name($instance) {
	return 'tct-progress-line-docstrt-widget-template';
	}
	function get_template_dir($instance) {
	return '';
	}
    function get_style_name($instance) {
        return 'tct-progress-line-docstrt-widget';
    }

}

siteorigin_widget_register('tct-progress-line-docstrt-widget', __FILE__, '_TCT_Progress_Line_Docstrt_Widget');

?>