<?php
/*
Widget Name: Line Chart
Description: Displays a line chart
Author: Me
Author URI: http://example.com
*/

class _TCT_Progress_Line_Chart_Widget extends SiteOrigin_Widget {

	function __construct() {

		parent::__construct(
			'tct-progress-line-chart-widget',
			_x('Transcribathon - Line Chart', 'tct-progress-line-chart-widget (backend)','transcribathon'),
			array(
				'description' => _x('Displays a line chart graph', 'tct-progress-line-chart-widget (backend)','transcribathon'),
				'panels_groups' => array('transcribathon'),
				'panels_icon' => 'tct-top-transcribers-icon',
				'help'        => ''
			),
			array(
				'icon' => 'dashicons-edit',
			),
			$form_options = array(
				'tct-progress-line-chart-headline' => array(
					'type' => 'select',
					'label' => _x('Headline above the list', 'tct-progress-line-chart-widget (backend)','transcribathon'),
					//'default' => 'Top Transcribers',
					'options' => array(
						'Transcribers' => __( 'Transcribers', 'tct-progress-line-chart-widget (backend)','transcribathon'),
						'Stories completed' => __( 'Stories completed', 'tct-progress-line-chart-widget (backend)','transcribathon'),
						'Documents started' => __( 'Documents started', 'tct-progress-line-chart-widget (backend)','transcribathon'),
						'Documents completed' => __( 'Documents completed', 'tct-progress-line-chart-widget (backend)','transcribathon'),
						'Transcribed characters' => __( 'Transcribed characters', 'tct-progress-line-chart-widget (backend)','transcribathon'),

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
	return 'tct-progress-line-chart-widget-template';
	}
	function get_template_dir($instance) {
	return '';
	}
    function get_style_name($instance) {
        return 'tct-progress-line-chart-widget';
    }

}

siteorigin_widget_register('tct-progress-line-chart-widget', __FILE__, '_TCT_Progress_Line_Chart_Widget');

?>