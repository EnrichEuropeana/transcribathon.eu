<?php
/*
Widget Name: News Container
Description: Displays the news container
Author: Me
Author URI: http://example.com
*/

class _TCT_News_Container_Widget extends SiteOrigin_Widget {

	function __construct() {
		
		parent::__construct(
			'tct-news-container-widget',
			_x('Transcribathon - Container of Trending News', 'tct-news-container-widget (backend)','transcribathon'),
			array(
				'description' => _x('Displays the current trending news in a container', 'tct-news-container-widget (backend)','transcribathon'),
				'panels_groups' => array('transcribathon'),
				'panels_icon' => 'tct-news-container-icon',
				'help'        => ''
			),
			array(
				'icon' => 'dashicons-edit',
			),
			array(
				'tct-news-container-headline' => array(
					'type' => 'text',
					'label' => _x('Headline (optional)', 'tct-news-container-widget (backend)','transcribathon'),
					'default' => 'Top Transcribers',
				)
			), 
			plugin_dir_path(__FILE__)
		);
	}
	
	
	function get_template_name($instance) {
	return 'tct-news-container-widget-template';
	}
	function get_template_dir($instance) {
	return '';
	}
    function get_style_name($instance) {
        return 'tct-news-container-widget';
    }

}

siteorigin_widget_register('tct-news-container-widget', __FILE__, '_TCT_News_Container_Widget');


?>