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

acymailing_get('controller.newsletter');
class FollowupController extends NewsletterController{
	var $aclCat = 'campaign';
	var $copySendDate = true;

	function listing(){
		acymailing_redirect(acymailing_completeLink('campaign',false,true));
	}

	 function copy(){
		$followupid = array();
		$followupid[] = acymailing_getVar('none', 'followupid');
		if(empty($followupid)) return $this->listing();

		$this->copySendDate = true;
	 	acymailing_setVar('cid', $followupid);
		parent::copy();

		return $this->listing();

	 }
}
