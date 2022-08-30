<?php
/*
Widget Name: Bar-Chart
*/


class TCT_Barchart_Widget extends SiteOrigin_Widget {

    function get_template_name($instance) {
        return 'tct-barchart-widget-template';
    }
	function get_template_dir($instance) {
    	return '';
	}
    function get_style_name($instance) {
        return 'tct-barchart-widget';
    }


	function __construct() {
		global $wpdb;
		$ex_campgns = array();
		$typearray = array('team_time'=>'(T/T)','time_code'=>'(T/C)','time'=>'(T O)');
		// Set request parameters
		$url = TP_API_HOST."/tp-api/campaigns";
		$requestType = "GET";

		// Execude http request
		include TCT_THEME_DIR_PATH."admin/inc/custom_scripts/send_api_request.php";

		// Save data
		$cmp = json_decode($result, true);

		// Set request parameters
		$url = TP_API_HOST."/tp-api/teams";
		$requestType = "GET";

		// Execude http request
		include TCT_THEME_DIR_PATH."admin/inc/custom_scripts/send_api_request.php";

		// Save data
		$teams = json_decode($result, true);
		$teamlist = array();
		$i = 0;
		foreach($teams as $team){
			$teamlist[$team['TeamId']] = esc_html($team['Name']);
			$i++;
		}

		if(sizeof($cmp)>0){
			foreach($cmp as $cp){
				$ex_campgns[$cp['CampaignId']] = $cp['Name'];
			}
		}else{
			$ex_campgns[''] = esc_html(_x('There are no campaigns', 'top-transcribers-widget (backend)','transcribathon'));
		}


		parent::__construct(
			'tct-barchart-widget',
			_x('Transcribathon - Bar-Chart', 'bardiagramm-widget (backend)','transcribathon'),
			array(
				'description' => _x('Displays a simple bar chart', 'bardiagramm-widget (backend)','transcribathon'),
				'panels_groups' => array('transcribathon'),
				'panels_icon' => 'tct-barchart-icon',
				'help'        => ''
			),
			array(
				'icon' => 'dashicons-edit',
			),
			array(
				'tct-barchart-label' => array(
					'type' => 'text',
					'label' => _x('Label', 'bardiagramm-widget (backend)','transcribathon'),
					'default' => '',
					'description' => _x('What is this chart displaying?','bardiagramm-widget (backend)','transcribathon')
				),
				'tct-barchart-base-value' => array(
					'type' => 'number',
					'label' => _x('Base value (where the chart should start counting)','bardiagramm-widget (backend)','transcribathon'),
					'description' => _x('You might want to enter a higher number as base in order to make changes more visible','bardiagramm-widget (backend)','transcribathon'),
					'default' => '0'
				),
				'tct-barchart-areaheight' => array(
					'type' => 'number',
					'label' => _x('Height of the chart (Number in pixels)','bardiagramm-widget (backend)','transcribathon'),
					'description' => _x('Please adjust the height of this chart if needed','bardiagramm-widget (backend)','transcribathon'),
					'default' => '250'
				),
				'tct-barchart-barwidth' => array(
					'type' => 'number',
					'label' => _x('Width of the bars (Number in pixels)','bardiagramm-widget (backend)','transcribathon'),
					'description' => _x('Please adjust the width of the bar(s) if needed','bardiagramm-widget (backend)','transcribathon'),
					'default' => '25'
				),
				'tct-barchart-steps' => array(
					'type' => 'text',
					'label' => _x('Steps', 'bardiagramm-widget (backend)','transcribathon'),
					'default' => '',
					'description' => _x('Steps to be displayed (Numbers seperated by commas eg.: 500,1000,1500)','bardiagramm-widget (backend)','transcribathon')
				),
				'tct-barchart-repeater' => array(
					'type' => 'repeater',
					'label' => _x('Comparable values (bars)','bardiagramm-widget (backend)','transcribathon'),
					'item_name'  => _x('Bar','bardiagramm-widget (backend)','transcribathon'),
					'item_label' => array(
						'selector'     => "[id*='tct-barchart-bar-label']",
						'update_event' => 'change',
						'value_method' => 'val'
					),
					'fields' => array(
						'tct-barchart-bar-label' => array(
							'type' => 'text',
							'label' => _x('Bar-label','bardiagramm-widget (backend)','transcribathon')
						),
						'tct-barchart-bar-value' => array(
							'type' => 'text',
							'label' => _x('Fixed Value (number)','bardiagramm-widget (backend)','transcribathon'),
							'description' => _x('enter a fixed number or select a variable number below (select a variable number below will cause the skript to ignore any number in this field)','bardiagramm-widget (backend)','transcribathon')
						),
						'tct-barchart-bar-color' => array(
								'type' => 'color',
								'label' => _x('Color of the bar','bardiagramm-widget (backend)','transcribathon'),
								'default' => '#cccccc'
						),
						'tct-barchart-variable' => array(
								'type' => 'select',
								'label' => _x( 'Variable Value (generated on each page visit)','bardiagramm-widget (backend)','transcribathon'),
								'default' => 'fixed',
								'options' => array(
									'fixed' => _x('Fixed Value (number)','bardiagramm-widget (backend)','transcribathon'),
									'uploaded-stories' => _x('Stories uploaded','numbers-widget: number of uploaded stories (back- and frontend)','transcribathon'),
									'uploaded-items' => _x('Documents uploaded','numbers-widget: number of uploaded items (back- and frontend)','transcribathon'),
									'processed-items' => _x('Documents in work','numbers-widget: number of items on which has been worked on yet (back- and frontend)','transcribathon'),
									'started-stories' => _x('Started stories','numbers-widget: number of started stories (back- and frontend)','transcribathon'),
									'completed-stories' => _x('Completed stories','numbers-widget: number of completed stories (back- and frontend)','transcribathon'),
									'started-items' => _x('Started documents','numbers-widget: number of started items (back- and frontend)','transcribathon'),
									'review-items' => _x('Documents in review','numbers-widget: number of items in review (back- and frontend)','transcribathon'),
									'completed-items' => _x('Completed documents','numbers-widget: number of completed items (back- and frontend)','transcribathon'),
									'users-registered' => _x('Registered users','numbers-widget: number of registered users (back- and frontend)','transcribathon'),
									'users-recruit' => _x('Recruits','numbers-widget: number of users with the role "recruit" (back- and frontend)','transcribathon'),
									'users-runner' => _x('Runners','numbers-widget: number of users with the role "runner" (back- and frontend)','transcribathon'),
									'users-champion' => _x('Champions','numbers-widget: number of users with the role "champion" (back- and frontend)','transcribathon'),
									'users-mentor' => _x('Mentors','numbers-widget: number of users with the role "mentor" (back- and frontend)','transcribathon'),
									'campaign-related' => _x('Campaign related values','numbers-widget: number of users with the role "mentor" (back- and frontend)','transcribathon'),
								),
								'state_emitter' => array(
									'callback' => 'select',
									'args' => array( 'tct_barchart_valuetype_{$repeater}' )
								),
							),

							//
							'tct-campaign-related-values' => array(
								'type' => 'section',
								'label' => _x('Settings for campaign-related values','numbers-widget: number of users with the role "mentor" (back- and frontend)','transcribathon'),
								'hide' => false,
								'fields' => array(
									'tct-barchart-campaign' => array(
										'type' => 'select',
										'label' => _x('Campaign:', 'storyboxes-widget (backend)','transcribathon'),
										'multiple' => false,
										'options' => $ex_campgns,
										'optional' => false,
										'description' => _x('(T/T) = team/time-based, (T/C) = team/code-based, (T O) = only time-based','numbers-widget: number of users with the role "mentor" (back- and frontend)','transcribathon'),
									),
									'tct-barchart-campaign-value' => array(
										'type' => 'select',
										'label' => _x('Value to track:', 'storyboxes-widget (backend)','transcribathon'),
										'multiple' => false,
										'options' => array(
											'total-characters' => _x('Characters transcribed','bardiagramm-widget (backend)','transcribathon'),
											'total-characters-day' => _x('Characters transcribed by day','bardiagramm-widget (backend)','transcribathon'),
											'documents-existing' => _x('Existing documents*','bardiagramm-widget (backend)','transcribathon'),
											'documents-started' => _x('Started documents','bardiagramm-widget (backend)','transcribathon'),
											'documents-started-day' => _x('Started documents by day','bardiagramm-widget (backend)','transcribathon'),
											'participating-individuals' => _x('Participating individuals','bardiagramm-widget (backend)','transcribathon'),
											'participating-teams' => _x('Participating teams','bardiagramm-widget (backend)','transcribathon'),
											'total-characters-team' => _x('Characters transcribed by team','bardiagramm-widget (backend)','transcribathon'),
											'documents-started-team' => _x('Started documents by team','bardiagramm-widget (backend)','transcribathon'),
										),
										'state_emitter' => array(
											'callback' => 'select',
											'args' => array( 'tct_barchart_campaign_valuetype_{$repeater}' )
										),
										'optional' => false,
										'description' => _x('*The value: Existing documents only works, if the campaign is restricted to certain user-tags, then it will show the number of documents containing at least one of these tags.','numbers-widget: number of users with the role "mentor" (back- and frontend)','transcribathon'),
									),
									'total-characters-team' => array(
										'type' => 'select',
										'label' => _x('Select a team','numbers-widget: number of users with the role "mentor" (back- and frontend)','transcribathon'),
										'multiple' => false,
										'options' => $teamlist,
										//'options' => array(
										//	'CY Open University' => _x('CY Open University','bardiagramm-widget (backend)','transcribathon'),
										//),
										'optional' => false,
										'state_handler' => array(
											'tct_barchart_campaign_valuetype_{$repeater}[total-characters-team]' => array('show'),
											'tct_barchart_campaign_valuetype_{$repeater}[documents-started-team]' => array('show'),
											'_else[tct_barchart_campaign_valuetype_{$repeater}]' => array('hide'),
										),
									),
									'total-characters-day' => array(
										'type' => 'section',
										'label' => _x('Select a date','numbers-widget: number of users with the role "mentor" (back- and frontend)','transcribathon'),
										'fields' => array(
											'tct-barchart-campaign-year' => array(
												'type' => 'select',
												'label' => _x('Year:', 'storyboxes-widget (backend)','transcribathon'),
												'multiple' => false,
												'options' => array(
													'2016'  => _x('2016','bardiagramm-widget (backend)','transcribathon'),
													'2017'  => _x('2017','bardiagramm-widget (backend)','transcribathon'),
													'2018'  => _x('2018','bardiagramm-widget (backend)','transcribathon'),
													'2019'  => _x('2019','bardiagramm-widget (backend)','transcribathon'),
													'2020'  => _x('2020','bardiagramm-widget (backend)','transcribathon'),
													'2021'  => _x('2021','bardiagramm-widget (backend)','transcribathon'),
													'2022'  => _x('2022','bardiagramm-widget (backend)','transcribathon'),
													'2023'  => _x('2023','bardiagramm-widget (backend)','transcribathon'),
													'2024'  => _x('2024','bardiagramm-widget (backend)','transcribathon'),
													'2025'  => _x('2025','bardiagramm-widget (backend)','transcribathon')
												),
												'optional' => false,
											),
											'tct-barchart-campaign-month' => array(
												'type' => 'select',
												'label' => _x('Month:', 'storyboxes-widget (backend)','transcribathon'),
												'multiple' => false,
												'options' => array(
													'01'  => _x('01','bardiagramm-widget (backend)','transcribathon'),
													'02'  => _x('02','bardiagramm-widget (backend)','transcribathon'),
													'03'  => _x('03','bardiagramm-widget (backend)','transcribathon'),
													'04'  => _x('04','bardiagramm-widget (backend)','transcribathon'),
													'05'  => _x('05','bardiagramm-widget (backend)','transcribathon'),
													'06'  => _x('06','bardiagramm-widget (backend)','transcribathon'),
													'07'  => _x('07','bardiagramm-widget (backend)','transcribathon'),
													'08'  => _x('08','bardiagramm-widget (backend)','transcribathon'),
													'09'  => _x('09','bardiagramm-widget (backend)','transcribathon'),
													'10'  => _x('10','bardiagramm-widget (backend)','transcribathon'),
													'11'  => _x('11','bardiagramm-widget (backend)','transcribathon'),
													'12'  => _x('12','bardiagramm-widget (backend)','transcribathon')
												),
												'optional' => false,
											),
											'tct-barchart-campaign-day' => array(
												'type' => 'select',
												'label' => _x('Day:', 'storyboxes-widget (backend)','transcribathon'),
												'multiple' => false,
												'options' => array(
													'1'  => _x('1','bardiagramm-widget (backend)','transcribathon'),
													'2'  => _x('2','bardiagramm-widget (backend)','transcribathon'),
													'3'  => _x('3','bardiagramm-widget (backend)','transcribathon'),
													'4'  => _x('4','bardiagramm-widget (backend)','transcribathon'),
													'5'  => _x('5','bardiagramm-widget (backend)','transcribathon'),
													'6'  => _x('6','bardiagramm-widget (backend)','transcribathon'),
													'7'  => _x('7','bardiagramm-widget (backend)','transcribathon'),
													'8'  => _x('8','bardiagramm-widget (backend)','transcribathon'),
													'9'  => _x('9','bardiagramm-widget (backend)','transcribathon'),
													'10'  => _x('10','bardiagramm-widget (backend)','transcribathon'),
													'11'  => _x('11','bardiagramm-widget (backend)','transcribathon'),
													'12'  => _x('12','bardiagramm-widget (backend)','transcribathon'),
													'13'  => _x('13','bardiagramm-widget (backend)','transcribathon'),
													'14'  => _x('14','bardiagramm-widget (backend)','transcribathon'),
													'15'  => _x('15','bardiagramm-widget (backend)','transcribathon'),
													'16'  => _x('16','bardiagramm-widget (backend)','transcribathon'),
													'17'  => _x('17','bardiagramm-widget (backend)','transcribathon'),
													'18'  => _x('18','bardiagramm-widget (backend)','transcribathon'),
													'19'  => _x('19','bardiagramm-widget (backend)','transcribathon'),
													'20'  => _x('20','bardiagramm-widget (backend)','transcribathon'),
													'21'  => _x('21','bardiagramm-widget (backend)','transcribathon'),
													'22'  => _x('22','bardiagramm-widget (backend)','transcribathon'),
													'23'  => _x('23','bardiagramm-widget (backend)','transcribathon'),
													'24'  => _x('24','bardiagramm-widget (backend)','transcribathon'),
													'25'  => _x('25','bardiagramm-widget (backend)','transcribathon'),
													'26'  => _x('26','bardiagramm-widget (backend)','transcribathon'),
													'27'  => _x('27','bardiagramm-widget (backend)','transcribathon'),
													'28'  => _x('28','bardiagramm-widget (backend)','transcribathon'),
													'29'  => _x('29','bardiagramm-widget (backend)','transcribathon'),
													'30'  => _x('30','bardiagramm-widget (backend)','transcribathon'),
													'31'  => _x('31','bardiagramm-widget (backend)','transcribathon')
												),
												'optional' => false,
											),
										),
										'state_handler' => array(
											'tct_barchart_campaign_valuetype_{$repeater}[total-characters-day]' => array('show'),
											'tct_barchart_campaign_valuetype_{$repeater}[documents-started-day]' => array('show'),
											'_else[tct_barchart_campaign_valuetype_{$repeater}]' => array('hide'),
										),
									),
									/*'grouped_checkbox' => array(
										'type' => 'checkbox',
										'label' => __( 'A grouped checkbox', 'widget-form-fields-text-domain' )
									)*/
								),
									'state_handler' => array(
									'tct_barchart_valuetype_{$repeater}[campaign-related]' => array('show'),
									'_else[tct_barchart_valuetype_{$repeater}]' => array('hide'),
								),
							)

					)
				)
			),
			plugin_dir_path(__FILE__)
		);

	}

}

siteorigin_widget_register('tct-barchart-widget', __FILE__, 'TCT_Barchart_Widget');












?>
