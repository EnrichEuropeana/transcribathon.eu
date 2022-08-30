<?php
/*
Widget Name: Transcribathon - Menu-List
Description: Displays A list of links to be used as a submenu
Author: Me
Author URI: http://example.com
*/

class _TCT_Menulist_Widget extends SiteOrigin_Widget {

	function get_template_name($instance) {
        return 'tct-menulist-widget-template';
    }
	function get_template_dir($instance) {
    	return '';
	}
    function get_style_name($instance) {
        return 'tct-menulist-widget';
    }
	function __construct() {
		parent::__construct(
			'tct-menulist-widget',
			_x('Transcribathon -  Menu-List', 'Menu-List-Widget (backend)','transcribathon'),
			array(
				'description' => _x('Displays A list of links to be used as a submenu', 'Menu-List-Widget (backend)','transcribathon'),
				'panels_groups' => array('transcribathon'),
				'panels_icon' => 'tct-menu-icon',
				'help'        => ''
			),
			array(
				'icon' => 'dashicons-edit',
			),
			array(
				
				 'tct-menulist-box' => array(
						'type' => 'repeater',
						'label' => _x('List items ', 'Menu-List-Widget (backend)','transcribathon'),
						'description' => _x('Create list items wich open a link-List when you click on them', 'Menu-List-Widget (backend)','transcribathon'),
						'item_name'  => __( 'List items', 'siteorigin-widgets' ),
						'item_label' => array(
							'selector'     => "[id*='tct-menulist-title']",
							'update_event' => 'change',
							'value_method' => 'val'
						),
						'fields' => array(
							'tct-menulist-title' => array(
								'type' => 'text',
								'label' => _x('The title of this list item', 'Menu-List-Widget (backend)','transcribathon'),
							),
							
							'tct-menulist-linkitems' => array(
								'type' => 'repeater',
								'label' => _x('Links', 'Menu-List-Widget (backend)','transcribathon'),
								'description' => _x('A set of links to be displayed wehn you click the list item title', 'Menu-List-Widget (backend)','transcribathon'),
								'item_name'  => __( 'Link', 'siteorigin-widgets' ),
								'item_label' => array(
									'selector'     => "[id*='tct-menulist-link-title']",
									'update_event' => 'change',
									'value_method' => 'val'
								),
								'fields' => array(
									'tct-menulist-link-title' => array(
										'type' => 'text',
										'label' => _x('The text to click on', 'Menu-List-Widget (backend)','transcribathon'),
									),
									'tct-menulist-link-url' => array(
										'type' => 'link',
										'label' => _x('Please enter a URL or select existing content to link to', 'Menu-List-Widget (backend)','transcribathon'),
										'default' => ''
									),
									'tct-menulist-link-target' => array(
										'type' => 'checkbox',
										'label' => _x('Open link in a new window', 'Menu-List-Widget (backend)','transcribathon'),
										'default' => false
									),
								)
							)
						)
					)
								
			
			), 
			//The $base_folder path string.
			plugin_dir_path(__FILE__)
		);
		
		
	}
	
}

siteorigin_widget_register('tct-menulist-widget', __FILE__, '_TCT_Menulist_Widget');

function tct_menulist_widget_enqueue_scripts(){
	wp_enqueue_script('tct-menulist-widget', CHILD_TEMPLATE_DIR .'/admin/inc/custom_widgets/tct-menulist/js/tct-menulist-widget.js', array('jquery'), SITEORIGIN_PANELS_VERSION);
 }
 if(!is_admin()){
	 add_action('init', 'tct_menulist_widget_enqueue_scripts');
 }
 add_action( 'admin_print_scripts-widgets.php', 'tct_menulist_widget_enqueue_scripts' );
 add_action('siteorigin_panel_enqueue_admin_scripts', 'tct_menulist_widget_enqueue_scripts');
?>