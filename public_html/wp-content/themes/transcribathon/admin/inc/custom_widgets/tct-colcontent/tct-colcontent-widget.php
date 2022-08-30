<?php
/*
Widget Name: Multiple-Column Text
Description: Multiple-Column Text
Author: Me
Author URI: http://www.example.de
*/


class TCT_Colcontent_Widget extends SiteOrigin_Widget {
	function get_template_name($instance) {
        return 'tct-colcontent-widget-template';
    }
	function get_template_dir($instance) {
    	return '';
	}
    function get_style_name($instance) {
        return 'tct-colcontent-widget';
    }
	function __construct() {
		parent::__construct(
			'tct-colcontent-widget',
			_x('Transcribathon - Multi-Column Text', 'multi-column-widget (backend)','transcribathon'),
			array(
				'description' => _x('Displays the content equaly spread in several columns, depending on the width of the browser', 'multi-column-widget (backend)','transcribathon'),
				'panels_groups' => array('transcribathon'),
				'panels_icon' => 'tct-columns-icon',
				'help'        => ''
				
			),
			array(
				'icon' => 'dashicons-edit',
			),
			array(
				'tct-colcontent-content' => array(
					'type' => 'tinymce',
					'label' => __( 'Text:', 'widget-form-fields-text-domain' ),
					'description' => _x('The text entered here will be displayed in several columns', 'multi-column-widget (backend)','transcribathon'),
					'default' => '',
					'rows' => 10,
					'default_editor' => 'html',
					'button_filters' => array(
						'mce_buttons' => array( $this, 'filter_mce_buttons' ),
						'mce_buttons_2' => array( $this, 'filter_mce_buttons_2' ),
						'mce_buttons_3' => array( $this, 'filter_mce_buttons_3' ),
						'mce_buttons_4' => array( $this, 'filter_mce_buttons_5' ),
						'quicktags_settings' => array( $this, 'filter_quicktags_settings' ),
					),
				),
			), 
			//The $base_folder path string.
			plugin_dir_path(__FILE__)
		);
		
		
	}
	
}

siteorigin_widget_register('tct-colcontent-widget', __FILE__, 'TCT_Colcontent_Widget');

function tct_colcontent_widget_enqueue_scripts(){
   wp_enqueue_script('tct-colcontent-widget', CHILD_TEMPLATE_DIR .'/admin/inc/custom_widgets/tct-colcontent/js/tct-colcontent-widget.js', array('jquery'), SITEORIGIN_PANELS_VERSION);
}
if(!is_admin()){
	//add_action('init', 'tct_colcontent_widget_enqueue_scripts');
}
add_action( 'admin_print_scripts-widgets.php', 'tct_colcontent_widget_enqueue_scripts' );
//add_action('siteorigin_panel_enqueue_admin_scripts', 'tct_colcontent_widget_enqueue_scripts');
?>