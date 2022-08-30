<?php
/* 
Shortcode: get_word
Description: Download Transccriptions as Word Document
*/

// include required files
include($_SERVER["DOCUMENT_ROOT"].'/wp-load.php');

// get Document data from API
function _TCT_get_document_word( $atts ) {   
    //include theme directory for text hovering
    $theme_sets = get_theme_mods();

    // Build Story page content
    $content = "";
    $content = "<style>
                  
        @page {
            size: A4 portrait;
            margin-top: 1.2cm;
            margin-bottom: 1.2cm;
            margin-left: 1.2cm;
            margin-right: 1.2cm;
            @bottom-center {
                content: counter(page);
            }
        }

        @media print {
          button {
            display: none
          }
          .print {
            display: block
          }

        }
    

            </style>"; //background: ".$theme_sets['vantage_general_link_color']." !important;// removed from hover 2 lines above //
    if (isset($_GET['story']) && $_GET['story'] != "") {
        // get Story Id from url parameter
        $storyId = $_GET['story'];

        // Set request parameters
        $url = TP_API_HOST."/tp-api/stories/".$storyId;
        $requestType = "GET";
    
        // Execude request
        include dirname(__FILE__)."/../custom_scripts/send_api_request.php";

        // Display data
        $storyData = json_decode($result, true);
        $storyData = $storyData[0];
        //print_r($storyData['Items'][1]);



/////// enrichment test
        $itemIds = array();
        foreach($storyData['Items'] as $item){
            array_push($itemIds, $item['ItemId']);
        }
        $myJson = array();
        $itemItems = array();
        foreach($itemIds as $Id){
            $requestData = array(
                'key' => 'testKey'
            );
        $url = TP_API_HOST.'/tp-api/items/'.$Id;
        $requestType = "GET";

        include dirname(__FILE__)."/../custom_scripts/send_api_request.php";
    
        $itemsData = json_decode($result, true);
        array_push($myJson, $itemsData);
        }
        $myDoc = '';
        $jsonExample = array();

        //$jsonPlatz = '';
        foreach($myJson as $json){
        
            //pdf converter

            $myDoc .= "<h3 style='text-align:center;'>".$json['Title']."</h3>";
            $myDoc .= "</br>";
            $myDoc .= "<p style='display:block;width:65%;margin: 0 auto;'>https://europeana.transcribathon.eu/documents/story/item/?story=".$json['StoryId']."&item=".$json['ItemId']."</p>";
            $myDoc .= "</br>";
            $myDoc .= "<div style='display:block;width:65%;margin: 0 auto;'><p>".$json['StoryEdmProvider']."</p></div>";
            $myDoc .= "<div style='display:block;width:65%;margin: 0 auto;'>".$json['Transcriptions'][0]['Text']."</div>";
            $myDoc .= "</br>";




            //json converter
            $jsonPlaces = array();
            $newItem = array();
            $newItem['Id'] = $json['ItemId'];
            $newItem['Title'] = $json['Title'];
            $newItem['StoryId'] = $json['StoryId'];
            $newItem['Transcription'] = $json['Transcriptions'][0]['Text'];
            $newItem['TranscriptionNoTags'] = $json['Transcriptions'][0]['TextNoTags'];
            $newItem['Language'] = $json['Transcriptions'][0]['Languages'][0]['Name'];
       
            foreach($json['Places'] as $place){
        //var_dump($place['Name']);
        
                array_push($jsonPlaces, $place['Name']);
            }
            $jsonPlaces = array_unique($jsonPlaces);
            $newItem['Places'] = $jsonPlaces;
            $jsonPerons = [];
            foreach($json['Persons'] as $person){
                $newPerson = array(
                    'FirstName' => $person['FirstName'],
                    'LastName' => $person['LastName'],
                    'BirthDate' => $person['BirthDate'],
                    'BirthPlace' => $person['BirthPlace'],
                    'DeathDate' => $person['DeathDate'],
                    'DeathPlace' => $person['DeathPlace'],
                    'Description' => $person['Description']
                );
                array_push($jsonPerons, $newPerson);
            }
            $newItem['Person'] = $jsonPerons;
    
            array_push($jsonExample, $newItem);
    
        }
$storyTitleA = explode(' || ', $json['StorydcTitle']);
//file_put_contents('myfile.json', json_encode($jsonExample));

$content .= "<div  style='text-align:center;height:300px;'>";
$content .= "<p>Europeana Transcribe</p>";
$content .= "<h2>Transcribathon.eu</h2>";
$content .= "</div>";
$content .= "<div style='display:block;margin: 0 auto;width:65%;'>";
    $content .= "<p><b>Story Provider:</b> ".$json['StoryedmProvider']."</p>";
    $content .= "<p><b>Story Data Provider:</b> ".$json['StoryedmDataProvider']."</p>";
    $content .= "<p><b>Story Creator:</b> ".$json['StorydcCreator']."</p>";
    $content .= "<p><b>Type:</b> ".$json['StorydcType']."</p>";
    $content .= "<p><b>Story Link</b>: https://europeana.transcribathon.eu/documents/story/?story=".$json['StoryId']."</p>";
    $content .= "</br>";
    $content .= "<h3>".$storyTitleA[0]."</h3>";
    $content .= "</br>";
    $content .= "<h4>Description</h4>";
    $content .= "</br>";
    $content .= "<p>".$json['StorydcDescription']."</p>";
$content .= "</div>";
$content .= "</br>";
$content .= "</br>";
$content .= "</br>";
$content .= "</br>";
$content .= "</br>";
$content .= "</br>";
$content .= "</br>";
$content .= "</br>";
$content .= "</br>";
$content .= "</br>";
$content .= "</br>";
$content .= "</br>";
$content .= "</br>";
$content .= "</br>";
$content .= "</br>";
$content .= "</br>";
$content .= "</br>";
$content .= "</br>";
// $content .= "</br>";
// $content .= "</br>";
// $content .= "</br>";
// $content .= "</br>";


$content .= "<div class='print' style='display:block;margin:0 auto;'>". $myDoc . "</div>";

//var_dump($itemsData);


///////////////////////
// print "<html xmlns:v=\"urn:schemas-microsoft-com:vml\"";
// print "xmlns:o=\"urn:schemas-microsoft-com:office:office\"";
// print "xmlns:w=\"urn:schemas-microsoft-com:office:word\"";
// print "xmlns=\"http://www.w3.org/TR/REC-html40\">";
// print "<xml>
//  <w:WordDocument>
//   <w:View>Print</w:View>

//   <w:DrawingGridHorizontalSpacing>9.35 pt</w:DrawingGridHorizontalSpacing>
//   <w:DrawingGridVerticalSpacing>9.35 pt</w:DrawingGridVerticalSpacing>
//  </w:WordDocument>
// </xml>
// ";
// print $myDoc;
// header("Content-Type: text/html; charset=UTF-8");
// header( 'Content-Type: application/msword' );
// header("Content-disposition: attachment; filename=transcription.doc");
//$content .= $myDoc;
$content .= "<button type='button' onclick='window.print();'>Click</button>";


    }
    return $content;
}
add_shortcode( 'get_document_word', '_TCT_get_document_word' );
?>