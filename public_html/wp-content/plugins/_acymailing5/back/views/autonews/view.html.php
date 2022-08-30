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

include(ACYMAILING_BACK.'views'.DS.'newsletter'.DS.'view.html.php');
class AutonewsViewAutonews extends NewsletterViewNewsletter
{
	var $type = 'autonews';
	var $ctrl = 'autonews';
	var $nameListing = 'AUTONEWSLETTERS';
	var $nameForm = 'AUTONEW';
	var $icon = 'autonewsletter';
	var $aclCat = 'autonewsletters';
	var $doc = 'autonewsletter';
}
