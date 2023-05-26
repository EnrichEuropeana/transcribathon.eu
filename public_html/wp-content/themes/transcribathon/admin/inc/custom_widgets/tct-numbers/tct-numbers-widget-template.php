<?php 
if(isset($instance['tct-numbers-kind']) && trim($instance['tct-numbers-kind']) != ""){

	// Get data from API 
    $getJsonOptions = [
        'http' => [
            'header' => [
                 'Content-type: application/json'
                ],
            'method' => 'GET'
        ]
    ];

	switch ($instance['tct-numbers-kind']) {
		case 'uploaded-items':
			$numbers = sendQuery(TP_API_HOST . '/tp-api/statistics/items', $getJsonOptions, true);
			break;
		
		case 'started-items':
			// Check if campaign filter is empty, if yes, then check if there is dataset filter
			$urlAppend = '';
			if(!empty($instance['tct-numbers-campaign'])) {
				$urlAppend = '?campaign=' . $instance['tct-numbers-campaign'];
			} else if (!empty($instance['tct-numbers-dataset'])) {
				$urlAppend = '?dataset=' . $instance['tct-numbers-dataset'];
			}

			$numbers = sendQuery(TP_API_HOST . '/tp-api/statistics/items' . $urlAppend, $getJsonOptions, true);
			break;

		case 'total-characters':
			$urlAppend = '';
			if(!empty($instance['tct-numbers-campaign'])) {
				$urlAppend = '/campaign/' . $instance['tct-numbers-campaign'];
			} else if (!empty($instance['tct-numbers-dataset'])) {
				$urlAppend = '?dataset=' . $instance['tct-numbers-dataset'];
			}

			$numbers = sendQuery(TP_API_HOST . '/tp-api/statistics/characters' . $urlAppend, $getJsonOptions, true);
			break;
	}

	$content = '';

	$content .= '<div
	    class="
		    w-60
			mx-auto
		">';
	    $content .= '<div
		    class="
			    theme-color-background
			    float-left
				rounded-full
				font-bold
				tracking-wider
				py-2
				px-4
			">';
	        $content .= number_format($numbers, 0, '.', ',');
		$content .= '</div>';
		$content .= '<div
		    class="
			    uppercase
				leading-5
				float-right
				text-left
			">';
		    $content .= str_replace('-', '<br>', $instance['tct-numbers-kind']);
		$content .= '</div>';
		$content .= '<div style="clear:both;"></div>';
	$content .= '</div>';

	echo $content;
}

?>