<?php
/*
Widget Name: Search Documents
Description: Search the documents page
Author: Me
Author URI: http://example.com
*/

class _TCT_Search_Documents_Widget extends SiteOrigin_Widget {

	function __construct() {
		
		parent::__construct(
			'tct-search-documents-widget',
			_x('Transcribathon - Search Documents', 'tct-search-documents-widget (backend)','transcribathon'),
			array(
				'description' => _x('Search stories', 'tct-search-documents-widget (backend)','transcribathon'),
				'panels_groups' => array('transcribathon'),
				'panels_icon' => 'tct-search-icon',
				'help'        => ''
			),
			array(
				'icon' => 'dashicons-edit',
			),
			array(
				'tct-search-documents-headline' => array(
					'type' => 'text',
					'label' => _x('Headline (optional)', 'tct-search-documents-widget (backend)','transcribathon'),
					'default' => 'not yet',
				)
			), 
			plugin_dir_path(__FILE__)
		);
	}
	
	
	function get_template_name($instance) {
	return 'tct-search-documents-widget-template';
	}
	function get_template_dir($instance) {
	return '';
	}
    function get_style_name($instance) {
        return 'tct-search-documents-widget';
    }

}

siteorigin_widget_register('tct-search-documents-widget', __FILE__, '_TCT_Search_Documents_Widget');


?>