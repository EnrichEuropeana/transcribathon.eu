<?php
/**
 * @package	AcyMailing for WordPress
 * @version	5.10.12
 * @author	acyba.com
 * @copyright	(C) 2009-2020 ACYBA S.A.R.L. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

defined('ABSPATH') or die('Restricted access');
?><?php

class plgAcymailingExportmassaction
{
	function __construct(&$subject, $config){
	}

	function onAcyDisplayActions(&$type){
		$fields = acymailing_getColumns('#__acymailing_subscriber');

	 	$return = '<div id="action__num__export"><table><tr>';

		$i = 1;
		foreach($fields as $oneField => $value){
			$return .= '<td>' .
							'<input type="checkbox" name="action[__num__][export]['.$oneField.']" value="'.$oneField.'" id="action__num__export'.$oneField.'"/>' .
							'<label style="margin-left:5px" for="action__num__export'.$oneField.'">'.$oneField.'</label>' .
						'</td>';
			if($i%5 == 0) $return .= '</tr><tr>';
			$i++;
		}

		for($i;$i%5 != 0;$i++){
			$return .= '<td/>';
		}

	 	$return .= '</tr></table>' .
	 			'<label style="margin-left:5px" for="action__num__exportpathtolog">Log\'s path</label>' .
	 			'<input style="width:350px;" type="text" name="action[__num__][export][pathtolog]" value="'.ACYMAILING_MEDIA_FOLDER.'/logs/export_%Y_%m_%d.csv" id="action__num__exportpathtolog"/>' .
	 			'</div>';
	 	$type['export'] = acymailing_translation('ACY_EXPORT');

	 	return $return;
	}

	function onAcyProcessAction_export($cquery,$action,$num){
		if(!empty($action['pathtolog'])){
			$action['pathtolog'] = strftime($action['pathtolog']);
			$pathtolog = ABSPATH.$action['pathtolog'];
			preg_match('/^(.+\/)[^\/]+$/', $action['pathtolog'], $matches);
			if(!empty($matches[1]) && !file_exists(ABSPATH.DS.$matches[1])) acymailing_createDir(ABSPATH.DS.$matches[1]);
			unset($action['pathtolog']);
		}else{
			if(isset($action['pathtolog'])) unset($action['pathtolog']);
			$pathtolog = ACYMAILING_FOLDER.'media'.DS.'logs'.DS.'export_'.date('Y-m-d').'.csv';
			if(!file_exists(ACYMAILING_FOLDER.'media'.DS.'logs')) acymailing_createDir(ACYMAILING_FOLDER.'media'.DS.'logs'.DS);
		}

		if(empty($action)) return '[Action Export] Error : no fields selected';

		acymailing_increasePerf();

		$allFields = array();
		$allFieldsSub = array();
		foreach($action as $fieldName){
			$allFields[] = acymailing_secureField($fieldName);
			$allFieldsSub[] = acymailing_secureField('sub.'.$fieldName);
		}

		$fp = fopen($pathtolog, 'w');
		if($fp === false) return '[Action Export] Error : unable to create the file '.$pathtolog;
		$error = fwrite($fp, '"'.implode('";"', $allFields).'"'."\r\n");
		if($error === false) return '[Action Export] Error : unable to write in the file '.$pathtolog;

		$valDep = 0;
		$dateFields = array('created', 'confirmed_date', 'lastopen_date','lastsent_date', 'lastclick_date', 'userstats_opendate', 'userstats_senddate', 'urlclick_date', 'hist_date');
		do{
			$cquery->limit = '';
			$cquery->orderBy = 'sub.subid';
			$allData = acymailing_loadObjectList($cquery->getQuery(array_merge(array('sub.`subid`'),$allFieldsSub)).' LIMIT '.$valDep.', 500');
			$valDep += 500;
			if(empty($allData)) break;
			$dataUser = array();
			$subids = array();
			for($i=0,$a=count($allData) ; $i<$a ; $i++){
				$subids[] = (int) $allData[$i]->subid;
				if(!in_array('subid',$allFields)) unset($allData[$i]->subid);

				foreach($allData[$i] as $fieldName => $oneUser){
					$dataUser[$subids[$i]][$fieldName] = in_array($fieldName, $dateFields) ? acymailing_getDate($oneUser,'%Y-%m-%d %H:%M:%S') : $oneUser;
				}
			}

			foreach($subids as $subid){
				$error = fwrite($fp, '"'.implode('";"', $dataUser[$subid]).'"'."\r\n");
				if($error === false) return '[Action Export] Error : unable to write in the file '.$pathtolog;
			}
		}while(!empty($allData));
		fclose($fp);

		return '[Action Export] Successfully exported in: <a href="'.str_replace(ABSPATH, acymailing_rootURI(), $pathtolog).'">'.$pathtolog.'</a>';
	}
}//endclass

