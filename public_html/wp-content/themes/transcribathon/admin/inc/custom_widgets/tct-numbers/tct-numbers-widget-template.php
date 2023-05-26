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
		    w-72
			mx-auto
			flex
			justify-around
		">';
	    $content .= '<div
		    class="
			    theme-color-background
			    inline-block
				w-2/5
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
				inline-block
				w-2/5
				text-left
			">';
		    $content .= '<h4 class="tracking-wide">' . str_replace('-', '<br>', $instance['tct-numbers-kind']) . '</h4>';
		$content .= '</div>';
		$content .= '<div style="clear:both;"></div>';
	$content .= '</div>';

	echo $content;
}

?>