<?php
/*
Widget Name: Tutorial slider widget
Description: Displays the tutorial slider
Author: Piktoresk | Olaf Baldini
Author URI: http://www.piktoresk.de
*/


class TCT_Tutorial_Slider_Widget extends SiteOrigin_Widget {

	function get_template_name($instance) {
        return 'tct-tutorial-slider-widget-template';
    }
	function get_template_dir($instance) {
    	return '';
	}
    function get_style_name($instance) {
        return 'tct-tutorial-slider-widget';
    }
	function __construct() {
		parent::__construct(
			'tct-tutorial-slider-widget',
			_x('Transcribathon - Tutorial Slider', 'Tutorial-Slider-Widget (backend)','transcribathon'),
			array(
				'description' => _x('Displays a kind of slider to click through several steps of a tutorial', 'Tutorial-Slider-Widget (backend)','transcribathon'),
				'panels_groups' => array('transcribathon'),
				'panels_icon' => 'tct-tutorial-icon',
				'help'        => ''
			),
			array(
				'icon' => 'dashicons-edit',
			),
			array(
				'tct-main-headline' => array(
					'type' => 'text',
					'label' => _x('Title of the whole tutorial-slider (optional)', 'Tutorial-Slider-Widget (backend)','transcribathon'),
				),
				 'tct-tutorial-slider-box' => array(
						'type' => 'repeater',
						'label' => _x('Steps ', 'Tutorial-Slider-Widget (backend)','transcribathon'),
						'description' => _x('Create as many steps as you would like to be clicked through. You cand drag & drop them to adjust the order', 'Tutorial-Slider-Widget (backend)','transcribathon'),
						'item_name'  => __( 'Step', 'siteorigin-widgets' ),
						'item_label' => array(
							'selector'     => "[id*='tct-step-headline']",
							'update_event' => 'change',
							'value_method' => 'val'
						),
						'fields' => array(
							'tct-step-headline' => array(
								'type' => 'text',
								'label' => _x('The title of this step (eg. Step 01)', 'Tutorial-Slider-Widget (backend)','transcribathon'),
							),
							'tct-step-image' => array(
								'type' => 'media',
								'label' => _x('The image to be displayed', 'Tutorial-Slider-Widget (backend)','transcribathon'),
								'description' =>  _x('An animated gif or a jpeg', 'Tutorial-Slider-Widget (backend)','transcribathon'),
								'choose' => _x('Choose an image', 'Tutorial-Slider-Widget (backend)','transcribathon'),
								'update' => __( 'Set image', 'Tutorial-Slider-Widget (backend)','transcribathon'),
								'library' => 'image',
								'fallback' => true
							),
							'tct-step-text' => array(
								'type' => 'textarea',
								'label' => _x('A short(!) description underneath the image', 'Tutorial-Slider-Widget (backend)','transcribathon'),
								'default' => '',
								'rows' => 2
							),
							'tct-step-linktext' => array(
								'type' => 'text',
								'label' => _x('Label of an optional button underneath the description (if blank, no button will appear)', 'Tutorial-Slider-Widget (backend)','transcribathon'),
							),
							'tct-step-url' => array(
								'type' => 'link',
								'label' => _x('Please enter a URL or select existing content to link to', 'Tutorial-Slider-Widget (backend)','transcribathon'),
								'description' =>  _x('Only needed if the button-label is not empty', 'Tutorial-Slider-Widget (backend)','transcribathon'),
								'default' => ''
							),
							'tct-box-linktarget' => array(
								'type' => 'checkbox',
								'label' => _x('Open link in a new window', 'Tutorial-Slider-Widget (backend)','transcribathon'),
								'default' => false
							),
							
						)
					)
			), 
			//The $base_folder path string.
			plugin_dir_path(__FILE__)
		);
		
		
	}
	
}

siteorigin_widget_register('tct-tutorial-slider-widget', __FILE__, 'TCT_Tutorial_Slider_Widget');

function tct_tutorial_slider_widget_enqueue_scripts(){
	wp_enqueue_script('tct-tutorial-slider-widget', CHILD_TEMPLATE_DIR .'/admin/inc/custom_widgets/tct-tutorial-slider/js/tct-tutorial-slider-widget.js', array('jquery'), SITEORIGIN_PANELS_VERSION);
 }
 if(!is_admin()){
	 add_action('init', 'tct_tutorial_slider_widget_enqueue_scripts');
 }
 add_action( 'admin_print_scripts-widgets.php', 'tct_tutorial_slider_widget_enqueue_scripts' );
 add_action('siteorigin_panel_enqueue_admin_scripts', 'tct_tutorial_slider_widget_enqueue_scripts');
?>