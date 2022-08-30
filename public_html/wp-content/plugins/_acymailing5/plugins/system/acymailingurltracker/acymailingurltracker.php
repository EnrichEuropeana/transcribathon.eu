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

$plgSystemAcymailingurltracker = new plgSystemAcymailingurltracker();
add_action('wp', array($plgSystemAcymailingurltracker, 'onAfterInitialise'));

class plgSystemAcymailingurltracker
{
	function __construct(){
	}

	function onAfterInitialise(){
		$helperFile = rtrim(dirname(dirname(dirname(__DIR__))),DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.'back'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'helper.php';

		if(empty($_REQUEST['acm'])){
			if(!empty($_GET['auid'])) {
				if(!file_exists($helperFile) || !include_once($helperFile)) return;
				$urlClass = acymailing_get('class.url');
				$urlClass->saveCurrentUrlName($_GET['auid']);
				unset($_GET['auid']);
				unset($_REQUEST['auid']);
			}
			return;
		}

		if(!file_exists($helperFile) || !include_once($helperFile)) return;

		$acm = acymailing_getVar('cmd', 'acm', '');
		if(!preg_match('#^[0-9]+_[0-9]+$#',$acm)) return;


		$vals = explode('_',$acm);

		$urlClass = acymailing_get('class.url');
		$urlObject = $urlClass->getAddCurrentUrl();

		if(empty($urlObject->urlid)) return;

		$urlClickClass = acymailing_get('class.urlclick');
		$urlClickClass->addClick($urlObject->urlid,$vals[1],$vals[0]);

		if($urlObject->url == $urlObject->name){
			$urlid = $urlObject->urlid;
		}

		unset($_GET['acm']);
		unset($_REQUEST['acm']);

		$currentURL = $urlClass->getCurrentUrl();
		if(!empty($currentURL) && strpos($currentURL,'#') === false){
			$oldUrl = $currentURL;
			$currentURL = preg_replace('#(\?|&|\/)(acm)[=:-][^&\/]*#i','',$currentURL);
			if($oldUrl != $currentURL){
				if(!strpos($currentURL,'?') && strpos($currentURL,'&')){
					$firstpos = strpos($currentURL,'&');
					$currentURL = substr($currentURL,0,$firstpos).'?'.substr($currentURL,$firstpos+1);
				}

				if(!empty($urlid)) $currentURL .= (strpos($currentURL,'?') ? '&' : '?').'auid='.$urlid;
				acymailing_redirect($currentURL);
			}

		}
	}
}//endclass

