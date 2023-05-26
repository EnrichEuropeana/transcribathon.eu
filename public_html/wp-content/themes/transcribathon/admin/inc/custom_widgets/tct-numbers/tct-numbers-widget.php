<?php
/*
Widget Name: Numbers-Widget
Description: Displays some statistical numbers
Author: Piktoresk | Olaf Baldini
Author URI: http://www.piktoresk.de
*/


class TCT_Numbers_Widget extends SiteOrigin_Widget {

    function get_template_name($instance) {
        return 'tct-numbers-widget-template';
    }
	function get_template_dir($instance) {
    	return '';
	}
    function get_style_name($instance) {
        return 'tct-numbers-widget';
    }
	function __construct() {

		$getAutoJsonOptions = [
			'http' => [
				'header' => [
					 'Content-type: application/json',
					],
				'method' => 'GET'
			]
		];
	
		$data = sendQuery(TP_API_HOST . '/tp-api/campaigns', $getAutoJsonOptions, true);

		$campaigns = array();
		$campaigns[''] = null;
		foreach($data as $campaign) {
			$campaigns[$campaign['CampaignId']] = $campaign['Name'];
		}
	
		$data = sendQuery(TP_API_HOST . '/tp-api/datasets', $getAutoJsonOptions, true);
		$datasets = array();
		$datasets[''] = null;
		foreach($data as $dataset) {
			$datasets[$dataset['DatasetId']] = $dataset['Name'];
		}

		parent::__construct(
			'tct-numbers-widget',
			_x('Transcribathon - Numbers', 'numbers-widget (backend)','transcribathon'),
			array(
				'description' => _x('Display certain statistical numbers concerning the current transcribe-status', 'numbers-widget (backend)','transcribathon'),
				'panels_groups' => array('transcribathon'),
				'panels_icon' => 'tct-numbers-icon',
				'help'        => ''
			),
			array(
				'icon' => 'dashicons-edit',
			),
			array(
				'tct-numbers-kind' => array(
					'type' => 'select',
					'label' => __( 'Choose the number you would like to be displayed', 'widget-form-fields-text-domain' ),
					'default' => 'uploaded-items',
					'options' => array(
						'uploaded-items' => _x('Documents uploaded','numbers-widget: number of uploaded items (back- and frontend)','transcribathon'),
						'started-items' => _x('Started documents','numbers-widget: number of started items (back- and frontend)','transcribathon'),
						'total-characters' => _x('Total Characters','numbers-widget items (back- and frontend)','transcribathon'),
					)
				),
				'tct-numbers-campaign' => array(
					'type' => 'select',
					'label' => 'Choose a campaign or dataset',
					'options' => $campaigns,
					'optional' => true,
				),
				'tct-numbers-dataset' => array(
					'type' => 'select',
					'label' => '',
					'options' => $datasets,
					'optional' => true,
				)
			),
			plugin_dir_path(__FILE__)
		);

	}

}

siteorigin_widget_register('tct-numbers-widget', __FILE__, 'TCT_Numbers_Widget');












?>
