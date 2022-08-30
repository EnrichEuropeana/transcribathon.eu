<?php
/*
Widget Name: Transcribathon - Boxes
Description: Displays Feature-Boxes
Author: Me
Author URI: http://www.example.de
*/


class TCT_Boxes_Widget extends SiteOrigin_Widget {
	function get_template_name($instance) {
        return 'tct-boxes-widget-template';
    }
	function get_template_dir($instance) {
    	return '';
	}
    function get_style_name($instance) {
        return 'tct-boxes-widget';
    }
	function __construct() {
		parent::__construct(
			'tct-boxes-widget',
			_x('Transcribathon - Feature-Boxes', 'Boxes-Widget (backend)','transcribathon'),
			array(
				'description' => _x('Displays Boxes to feature some content', 'Boxes-Widget (backend)','transcribathon'),
				'panels_groups' => array('transcribathon'),
				'panels_icon' => 'tct-boxes-icon',
				'help'        => ''
			),
			array(
				'icon' => 'dashicons-edit',
			),
			array(
				
				 'tct-boxes-box' => array(
						'type' => 'repeater',
						'label' => _x('Boxes ', 'Boxes-Widget (backend)','transcribathon'),
						'description' => _x('Create as many Boxes as you would like to show in one row. You cand drag & drop them to adjust the order', 'Boxes-Widget (backend)','transcribathon'),
						'item_name'  => __( 'Box', 'siteorigin-widgets' ),
						'item_label' => array(
							'selector'     => "[id*='tct-box-text']",
							'update_event' => 'change',
							'value_method' => 'val'
						),
						'fields' => array(
							'tct-box-headline' => array(
								'type' => 'text',
								'label' => _x('The text displayed in top left corner', 'Boxes-Widget (backend)','transcribathon'),
							),
							'tct-box-text' => array(
								'type' => 'textarea',
								'label' => _x('The text displayed in the middle over the image (Keep it short!)', 'Boxes-Widget (backend)','transcribathon'),
								'default' => 'Example',
								'rows' => 2
							),
							'tct-box-url' => array(
								'type' => 'link',
								'label' => _x('Please enter a URL or select existing content to link to', 'Boxes-Widget (backend)','transcribathon'),
								'default' => ''
							),
							'tct-box-linktarget' => array(
								'type' => 'checkbox',
								'label' => _x('Open link in a new window', 'Boxes-Widget (backend)','transcribathon'),
								'default' => false
							),
							'tct-box-image' => array(
								'type' => 'media',
								'label' => _x('Background-Image', 'Boxes-Widget (backend)','transcribathon'),
								'choose' => _x('Choose an image', 'Boxes-Widget (backend)','transcribathon'),
								'update' => __( 'Set image', 'Boxes-Widget (backend)','transcribathon'),
								'library' => 'image',
								'fallback' => true
							)
						)
					)
								
			
			
				
			), 
			//The $base_folder path string.
			plugin_dir_path(__FILE__)
		);
		
		
	}
	
}

siteorigin_widget_register('tct-boxes-widget', __FILE__, 'TCT_Boxes_Widget');

function tct_boxes_widget_enqueue_scripts(){
   wp_enqueue_script('tct-boxes-widget', CHILD_TEMPLATE_DIR .'/admin/inc/custom_widgets/tct-boxes/js/tct-boxes-widget.js', array('jquery'), SITEORIGIN_PANELS_VERSION);
}
if(!is_admin()){
	add_action('init', 'tct_boxes_widget_enqueue_scripts');
}
add_action( 'admin_print_scripts-widgets.php', 'tct_boxes_widget_enqueue_scripts' );
add_action('siteorigin_panel_enqueue_admin_scripts', 'tct_boxes_widget_enqueue_scripts');
?>