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

class DiagramController extends acymailingController{

	function listing(){
		if(!$this->isAllowed('statistics','manage')) return;
		acymailing_setVar( 'layout', 'listing'  );
		return parent::display();
	}

	function mailing(){
		if(!$this->isAllowed('statistics','manage')) return;
		acymailing_setVar( 'layout', 'mailing'  );
		return parent::display();
	}
}
