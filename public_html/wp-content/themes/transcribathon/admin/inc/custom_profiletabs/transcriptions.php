<?php
/* 
Shortcode: transcription_tab
Description: Creates the transcription profile tab
*/
function _TCT_transcription_tab( $atts ) {  
    
$theme_sets = get_theme_mods();
        // Set request parameters for image data
        $requestData = array(
            'key' => 'testKey'
        );
        $url = TP_API_HOST."/tp-api/profileStatistics/".um_profile_id();
        $requestType = "GET";

        // Execude http request
        include dirname(__FILE__)."/../custom_scripts/send_api_request.php";

        // Save image data
        $profileStatistics = json_decode($result, true);
        $profileStatistics = $profileStatistics[0];

        $twStatsBubblesClasses = <<<TW1
            w-20
            md:w-40
            h-20
            md:h-40
            border-4
            border-solid
            rounded-full
            border-gray-400
            text-center
            flex
            flex-col
            justify-center
        TW1;

        $twStatsNumbersClasses = <<<TW2
            m-0
            text-sm
            md:text-xl
            font-bold
            text-gray-400
        TW2;

        $twStatsLabelsClasses = <<<TW3
            m-0
            text-xs
            md:text-sm
            text-gray-400
            uppercase
        TW3;

        $content = '';

        $content .= '<div class="container mx-auto max-w-[80%]">';
            // Statistics circles
            $content .= '<div
                class="
                    grid
                    grid-cols-1
                    sm:grid-cols-5
                    gap-y-2
                    md:gap-x-5
                    place-items-center
                ">';

                $content .= '<div class=" ' . $twStatsBubblesClasses . ' ">';
                    $content .= '<p class=" ' . $twStatsNumbersClasses . ' ">' . number_format_i18n($profileStatistics['Miles']) . '</p>';
                    $content .= '<p class=" ' . $twStatsLabelsClasses . ' ">' . _x('miles run', 'Transcription-Tab on Profile', 'transcribathon') . '</p>';
                $content .= '</div>';

                $content .= '<div class=" ' . $twStatsBubblesClasses . ' ">';
                    $content .= '<p class=" ' . $twStatsNumbersClasses . ' ">' . number_format_i18n($profileStatistics['TranscriptionCharacters']) . '</p>';
                    $content .= '<p class=" ' . $twStatsLabelsClasses . ' ">' . _x('characters', 'Transcription-tab on Profile', 'transcribathon') . '</p>';
                $content .= '</div>';

                $content .= '<div class=" ' . $twStatsBubblesClasses . ' ">';
                    $content .= '<p class=" ' . $twStatsNumbersClasses . ' ">' . number_format_i18n($profileStatistics['Locations']) . '</p>';
                    $content .= '<p class=" ' . $twStatsLabelsClasses . ' ">' . _x('locations', 'Transcription-tab on Profile', 'transcribathon') . '</p>';
                $content .= '</div>';

                $content .= '<div class=" ' . $twStatsBubblesClasses . ' ">';
                    $content .= '<p class=" ' . $twStatsNumbersClasses . ' ">' . number_format_i18n($profileStatistics['Enrichments']) . '</p>';
                    $content .= '<p class=" ' . $twStatsLabelsClasses . ' ">' . _x('enrichments', 'Transcirption-tab on Profile', 'transcribathon') . '</p>';
                $content .= '</div>';

                $content .= '<div class=" ' . $twStatsBubblesClasses . ' ">';
                    $content .= '<p class=" ' . $twStatsNumbersClasses . ' ">' . number_format_i18n($profileStatistics['DocumentCount']) . '</p>';
                    $content .= '<p class=" ' . $twStatsLabelsClasses . ' ">' . _x('documents', 'Transcription-tab on Profile', 'transcribathon') . '</p>';
                $content .= '</div>';
                
            $content .= '</div>';
        $content .= '</div>';

            // Tr Characters chart
        $content .= '<div class="container mx-auto max-w-[80%]">';
            $content .= '<div id="personal_chart"
                class="
                    inline-block
                    w-full
                    h-48
                    md:h-[350px]
                    lg:h-[430px]
                    xl:h-[550px]
                ">';
                $content .= '<script type="text/javascript">';
                    if(trim($amt[0][0] != '' && (int)$amt[0][0] > 0)) {
                        $content .= 'getTCTlinePersonalChart("days", "' . date('Y-m-') . '01", "' . date('Y-m-t') . '", "personal_chart", "' . um_profile_id() . '");';
                    } else {
                        $content .= 'getTCTlinePersonalChart("months", "' . date('Y-') . '01-01", "' . date('Y-m-t', strtotime(date('Y') . '-12-01')) . '", "personal_chart", "' . um_profile_id() . '");';
                    }
                $content .= '</script>';
            $content .= '</div>';
        $content .= '</div>';
        $content .= '<div class="min-h-[50px]"> &nbsp </div>';

    
        // Get Documents that user edited
        $url = TP_API_HOST . '/tp-api/transcriptionProfile/' . um_profile_id();

        $getJsonOptions = [
            'http' => [
                'header' => [ 
                    'Content-type: application/json',
                ],
                'method' => 'GET'
            ]
        ];

        $documents = sendQuery($url, $getJsonOptions, true);
        
        // Display the documents

        $content .= '<div class="container mx-auto max-w-[80%]">';
            $content .= '<h2 class="theme-color text-xl font-bold mb-4">';
                $content .= _x('My Contributions', 'Transcription-tab on Profile', 'transcribathon');
            $content .= '</h2>';

            $content .= '<div
                class="
                    grid
                    grid-cols-1
                    sm:grid-cols-2
                    md:grid-cols-4
                    gap-4
                    justify-items-center
                ">';
                $i = 0;
                $k = 0;
                
                if(!empty($documents)) {
                    foreach($documents as $document) {
                        if($i > 7) {
                            $content .= '<div
                                class="
                                    item-sticker
                                    border
                                    border-solid
                                    border-gray-400
                                    rounded-none
                                    max-w-xs
                                    mx-auto
                                    md:mx-0
                                " style="display:none;">'; // start of single document (after 8th)
                        } else {
                            $content .= '<div
                                class="
                                    item-sticker
                                    border
                                    border-solid
                                    border-gray-400
                                    rounded-none
                                    max-w-xs
                                    mx-auto
                                    md:mx-0
                                ">'; // start of single document (before 8th)
                        }
                            $content .= '<a href="' . get_europeana_url() . "/documents/story/item?item=" . $document['ItemId'] . '">';

                                $image = json_decode($document['ItemImageLink'], true);
                                $imageLink = createImageLinkFromData($image, array('size' => '320,180', 'page' => 'search'));
                                if($image['height'] == null) {
                                    $imageLink = str_replace('full', '50,50,1800,1100', $imageLink);
                                }

                                $content .= '<img class="border-b border-solid border-gray-400" src="' . $imageLink . '" loading="lazy" width="320" height="180" alt="edited-item-' . $i . '">';

                            $content .= '</a>';
                            // Title
                            $content .= '<h3 class="theme-color text-base font-bold my-3 px-2">' . $document['ItemTitle'] . '</h3>';

                            $content .= '<p class="text-xs m-0 px-2"> Last Edit: ' . date_i18n(get_option('date_format'), strtotime($document['Timestamp'])) . '</p>';

                            foreach($document['Scores'] as $score) {
                                if(!empty($score)) {
                                    $content .= '<p class="text-xs m-0 px-2">' . $score['ScoreType'] . ': ' . $score['Amount'] . '</p>';
                                }
                            }
                        
                        $content .= '</div>'; // end of single document
                        $i += 1;
                    }
                    
                }
            $content .= '</div>';

            if(!empty($documents) && count($documents) > 7) {
                $content .= '<div id="profile-more"
                    class="
                        text-center
                        my-6
                        mx-auto
                        cursor-pointer
                        w-12
                        text-xs
                        sm:w-32
                        sm:text-base
                        py-2
                        theme-color-background
                    "> Show More </div>';
            }
        $content .= '</div>';

        $content .= <<<EOT
            <script type="text/javascript">
                let showBtn = document.getElementById("profile-more");
                console.log(showBtn);
                let itemStickers = document.querySelectorAll(".item-sticker");

                showBtn.addEventListener("click", function() {
                    if(!showBtn.classList.contains("shown")) {
                        for(let itm of itemStickers) {
                            itm.style.display = "block";
                            showBtn.classList.add("shown");
                            showBtn.textContent = "Show Less";
                        }
                    } else {
                        let i = 0;
                        for(let itm of itemStickers) {
                            if(i < 8) {
                                i++;
                                continue;
                            } else {
                                itm.style.display = "none";
                                showBtn.classList.remove("shown");
                                showBtn.textContent = "Show More";
                                i++;
                            }
                        }
                    }
                });
            </script>
        EOT;

    return $content;


}
add_shortcode( 'transcription_tab', '_TCT_transcription_tab' );
?>