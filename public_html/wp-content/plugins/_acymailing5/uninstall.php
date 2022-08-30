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

$file_name = rtrim(__DIR__,DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.'back'.DIRECTORY_SEPARATOR.'tables.sql';
$handle = fopen($file_name, 'r');
$queries = fread($handle, filesize($file_name));
fclose($handle);

if(is_multisite()){
	$currentSite = get_current_site();
	$sites = function_exists('get_sites') ? get_sites() : wp_get_sites();

	foreach($sites as $site){
		if(is_object($site)) $site = get_object_vars($site);
		switch_to_blog($site['blog_id']);
		acymailing_delete($queries);
	}

	switch_to_blog($currentSite->blog_id);
}else{
	acymailing_delete($queries);
}

function acymailing_delete($queries){
	global $wpdb;

	$acytables = str_replace('#__', $wpdb->prefix, $queries);
	preg_match_all('#CREATE TABLE IF NOT EXISTS `([^`]+)`#Uis', $acytables, $tables);

	foreach($tables[1] as $oneTable) {
		$wpdb->query('DROP TABLE `' . $oneTable . '`;');
	}
}
