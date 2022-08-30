<?php
/*
Widget Name: Top Transcribers
Description: Displays the current top transcribers
Author: Piktoresk | Olaf Baldini
Author URI: http://www.piktoresk.de
*/


class TCT_Top_Transcribers_Widget extends SiteOrigin_Widget {

   
	function __construct() {
		
		parent::__construct(
			'tct-top-transcribers-widget',
			_x('Transcribathon - Top Transcribers', 'top-transcribers-widget (backend)','transcribathon'),
			array(
				'description' => _x('Displays the current top ten transcribers', 'top-transcribers-widget (backend)','transcribathon'),
				'panels_groups' => array('transcribathon'),
				'panels_icon' => 'tct-top-transcribers-icon',
				'help'        => ''
			),
			/*array(
				'icon' => 'dashicons-edit',
			),
			array(
				'tct-top-transcribers-headline' => array(
					'type' => 'text',
					'label' => _x('Headline above the list', 'top-transcribers-widget (backend)','transcribathon'),
					'default' => 'Top Transcribers',
				)
			), */
			plugin_dir_path(__FILE__)
		);
	}
	
	function modify_form( $instance) {
		global $wpdb;
		$ex_campgns = array();

		// Set request parameters
		$url = TP_API_HOST."/tp-api/campaigns?Public=1";
		$requestType = "GET";

		// Execude http request
		include TCT_THEME_DIR_PATH."admin/inc/custom_scripts/send_api_request.php";
		// Save data
		$cmp = json_decode($result, true);

		if(sizeof($cmp)>0){
			foreach($cmp as $cp){
				$ex_campgns[$cp['CampaignId']] = esc_html($cp['Name']); 
			}
		}else{
			$ex_campgns[''] = esc_html(_x('There are no campaigns', 'top-transcribers-widget (backend)','transcribathon')); 
		}
		$ex_tm_campgns = array();

		// Set request parameters
		$url = TP_API_HOST."/tp-api/campaigns?Public=0";
		$requestType = "GET";

		// Execude http request
		include TCT_THEME_DIR_PATH."admin/inc/custom_scripts/send_api_request.php";

		// Save data
		$cmp = json_decode($result, true);

		if(sizeof($cmp)>0){
			foreach($cmp as $cp){
				$ex_tm_campgns[$cp['CampaignId']] = esc_html($cp['Name']); 
			}
		}else{
			$ex_tm_campgns[''] = esc_html(_x('There are no team-based campaigns', 'top-transcribers-widget (backend)','transcribathon')); 
		}
	
		// General options
		$instance['tct-top-transcribers-headline'] = array(
			'type' => 'text',
			'label' => _x('Headline above the list', 'top-transcribers-widget (backend)','transcribathon'),
			'default' => 'Top Transcribers',
		);
		$instance['tct-top-transcribers-amount'] = array(
			'type' => 'slider',
			'label' => _x('Amount of users to show at one time', 'top-transcribers-widget (backend)','transcribathon'),
			'default' => 10,
			'min' => 1,
			'max' => 50,
			'integer' => true
		);
		$instance['tct-top-transcribers-nothingtoshow'] = array(
			'type' => 'text',
			'label' => _x('Text to be displayed as long as there is no data', 'top-transcribers-widget (backend)','transcribathon'),
			'default' => 'No data yet...',
			'optional' => true,
		);
		// Select Team/Individuals
		$instance['tct-top-transcribers-subject'] = array(
			'type' => 'select',
			'label' => _x('Show:', 'top-transcribers-widget (backend)','transcribathon'),
			'state_emitter' => array(
				'callback' => 'select',
				'args' => array( 'tct_toplist_subject' )
			),
			'options' => array(
				'individuals' => _x('Individuals', 'top-transcribers-widget (backend)','transcribathon'),
				'teams' => _x('Teams', 'top-transcribers-widget (backend)','transcribathon'),
			)
		);
		// INDIVIDUALS
		$instance['tct-top-transcribers-settings-individuals'] = array(
        	'type' => 'section',
			'label' => _x('Settings for individuals', 'top-transcribers-widget (backend)','transcribathon'),
        	'hide' => false,
			'fields' => array(
				'tct-top-transcribers-kind' => array(
					'type' => 'select',
					'label' => _x('Show top transcribers from:', 'top-transcribers-widget (backend)','transcribathon'),
					'state_emitter' => array(
						'callback' => 'select',
						'args' => array( 'tct_toplist_referal' )
					),
					'options' => array(
						'all' => _x('everything', 'top-transcribers-widget (backend)','transcribathon'),
						'campaign' => _x('one special campaign', 'top-transcribers-widget (backend)','transcribathon'),
					),
				),
				'tct-top-transcribers-campaign' => array(
					'type' => 'select',
					'label' => _x('Only show top-transcribers of the campaign:', 'storyboxes-widget (backend)','transcribathon'),
					'multiple' => false,
					'options' => $ex_campgns,
					'optional' => false,
					'state_handler' => array(
						'tct_toplist_referal[all]' => array('hide'),
						'_else[tct_toplist_referal]' => array('show'),
					),
				),
				'tct-top-transcribers-showteams' => array(
						'type' => 'checkbox',
						'label' => _x('Show shortnames of teams the users are member of (if available)', 'storyboxes-widget (backend)','transcribathon'),
						'default' => false
				),
        	),
			'state_handler' => array(
				'tct_toplist_subject[teams]' => array('hide'),
				'_else[tct_toplist_subject]' => array('show'),
			),
    	);
		// TEAMS
		$instance['tct-top-transcribers-settings-teams'] = array(
        	'type' => 'section',
			'label' => _x('Settings for teams', 'top-transcribers-widget (backend)','transcribathon'),
        	'hide' => false,
			'fields' => array(
				'tct-top-transcribers-kind' => array(
					'type' => 'select',
					'label' => _x('Show top teams from:', 'top-transcribers-widget (backend)','transcribathon'),
					'state_emitter' => array(
						'callback' => 'select',
						'args' => array( 'tct_toplist_referal_team' )
					),
					'options' => array(
						'all' => _x('everything', 'top-transcribers-widget (backend)','transcribathon'),
						'campaign' => _x('one special campaign', 'top-transcribers-widget (backend)','transcribathon'),
					)
				),
				'tct-top-transcribers-campaign' => array(
					'type' => 'select',
					'label' => _x('Only show top-teams of the campaign:', 'storyboxes-widget (backend)','transcribathon'),
					'multiple' => false,
					'options' => $ex_tm_campgns,
					'optional' => false,
					'state_handler' => array(
						'tct_toplist_referal_team[all]' => array('hide'),
						'_else[tct_toplist_referal_team]' => array('show'),
					),
				)
        	),
			'state_handler' => array(
				'tct_toplist_subject[individuals]' => array('hide'),
				'_else[tct_toplist_subject]' => array('show'),
			),
    	);
		
		
		return $instance;
	}
	
	
	
	function get_template_name($instance) {
        return 'tct-top-transcribers-widget-template';
    }
	function get_template_dir($instance) {
    	return '';
	}
    function get_style_name($instance) {
        return 'tct-top-transcribers-widget';
    }
	
}

siteorigin_widget_register('tct-top-transcribers-widget', __FILE__, 'TCT_Top_Transcribers_Widget');












?>