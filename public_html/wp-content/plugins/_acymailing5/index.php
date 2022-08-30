<?php
/*
Plugin Name: AcyMailing 5
Description: Manage your contact lists and send newsletters from your site.
Author: Acyba
Author URI: https://www.acyba.com
License: GPLv3
Version: 5.10.12
Text Domain: acymailing5
Domain Path: /language
*/

defined('ABSPATH') || die('Restricted Access');

// Load defines
if(!defined('DS')) define('DS', DIRECTORY_SEPARATOR);

include_once(__DIR__.DS.'install.wordpress.php');
include_once(__DIR__.DS.'widgets'.DS.'subform'.DS.'module.php');
include_once(__DIR__.DS.'plugins'.DS.'system'.DS.'acymailingurltracker'.DS.'acymailingurltracker.php');

class acymailingInit
{
	function __construct(){
		// Install Acy DB and sample data on first activation (not installation because of FTP install)
		register_activation_hook(__FILE__, array($this, 'install'));

		// Enable ajax calls
		add_action('wp_ajax_acymailing5_router', array($this, 'router'));
		add_action('wp_ajax_acymailing5_frontrouter', array($this, 'frontRouter'));
		add_action('wp_ajax_nopriv_acymailing5_frontrouter', array($this, 'frontRouter'));

		// Make sure we can redirect if needed after some checks (find better if possible)
		$pages = array('dashboard', 'list', 'subscriber', 'newsletter', 'stats', 'cpanel');
		foreach($pages as $page){
			add_action('load-acymailing5_page_acymailing5_'.$page, array($this, 'waitHeaders'));
            add_action('load-acymailing_page_acymailing5_'.$page, array($this, 'waitHeaders'));
		}

		add_action('widgets_init', array($this, 'loadWidgets'));

		// Load system plugins
		$plugin = plugin_basename( __FILE__ );
		add_filter('plugin_action_links_'.$plugin, array($this, 'addPluginLinks'));

		if(defined('WP_ADMIN') && WP_ADMIN){
			// Add AcyMailing menu in the back-end's left menu of WordPress
			add_action('admin_menu', array($this, 'addMenus'), 99);
		}else{
			// No system messages on front in WP, DIY
			add_action('wp', array($this, 'frontMessages'));
			add_action('wp_footer', array($this, 'messagingSystem'));
		}

		// Hooks to create/update an Acy user when a WP user is created/updated
		add_action('user_register', array($this, 'synchUsers'), 10, 1);
		add_action('profile_update', array($this, 'synchUsers'), 10, 2);

        // Update system
        add_filter('pre_set_site_transient_update_plugins', array($this, 'checkUpdates'));
	}

    public function checkUpdates($transient)
    {
		if(isset($transient->response["acymailing5/index.php"])) unset($transient->response["acymailing5/index.php"]);
        return $transient;
    }

	function synchUsers($userId, $oldUser = null){
		if(empty($userId)) return;

		$isnew = empty($oldUser);
		$cmsUser = get_user_by('id', $userId);
		if(empty($cmsUser->user_email)) return true;

		require_once(rtrim(__DIR__,DS).DS.'back'.DS.'helpers'.DS.'helper.php');

		// Avoid any problem... lets make sure the e-mail address is valid anyway
		$userHelper = acymailing_get('helper.user');
		if(!$userHelper->validEmail($cmsUser->user_email)) return true;

		if(!acymailing_getVar('cmd', 'acy_source')) acymailing_setVar('acy_source', 'wordpress');

		$config = acymailing_config();
		$requireConfirmation = $config->get('require_confirmation', false);

		$subscriberClass = acymailing_get('class.subscriber');

		// Step 1 : create/update the Acy user
		$acyUser = new stdClass();
		$acyUser->email = trim(strip_tags($cmsUser->user_email));
		if(!empty($cmsUser->display_name)) $acyUser->name = trim(strip_tags($cmsUser->display_name));
		if(empty($acyUser->name) && !empty($cmsUser->user_nicename)) $acyUser->name = trim(strip_tags($cmsUser->user_nicename));
		$acyUser->enabled = 1;
		$acyUser->userid = $cmsUser->ID;

		if(!acymailing_isAdmin()) $subscriberClass->geolocRight = true;

		if(!$isnew && !empty($oldUser->user_email) && $cmsUser->user_email != $oldUser->user_email){
			// The email address has been updated, load the existing Acy user instead of creating a new one
			$acyUser->subid = $subscriberClass->subid($oldUser->user_email);
		}
		// Just in case of this is an existing user but the e-mail address has been modified by something else...
		if(empty($acyUser->subid)){
			if(empty($acyUser->userid)){
				$acyUser->subid = null;
			}else{
				$acyUser->subid = $subscriberClass->subid($acyUser->userid);
			}
		}

		// We may update the user with a new e-mail address... lets make sure we can do that and we won't have a conflict with an existing address
		// Thats only for an update and only if that e-mail address exists for another user...
		if(!empty($acyUser->subid)){
			$currentSubid = $subscriberClass->subid($acyUser->email);
			if(!empty($currentSubid) && $acyUser->subid != $currentSubid){
				// The user already exists and has a different subid... that will block the update.
				// We will delete that user.
				$subscriberClass->delete($currentSubid);
			}
		}

		$isnew = (bool)($isnew || empty($acyUser->subid));

		if(empty($acyUser->subid)) $acyUser->confirmed = $requireConfirmation ? 0 : 1;

		$subscriberClass->checkVisitor = false;
		$subscriberClass->sendConf = ($requireConfirmation && empty($acyUser->confirmed));
		$subscriberClass->triggerFilterBE = true;

		$subid = $subscriberClass->save($acyUser);
		$acyUser = $subscriberClass->get($subid);



		// Step 2 : subscribe the user to some lists depending on the pref or on the variables...
		// Only if it's a new user!
		$listsToSubscribe = ($isnew) ? $config->get('autosub', 'None') : 'None';
		$currentSubscription = $subscriberClass->getSubscriptionStatus($subid);

		$listsClass = acymailing_get('class.list');
		$allLists = $listsClass->getLists('listid');
		if(acymailing_level(1)){
			$allLists = $listsClass->onlyCurrentLanguage($allLists);
		}

		// Add subscriptions on "wait confirmation" or "subscribed"...
		$statusAdd = (empty($acyUser->confirmed) && $requireConfirmation) ? 2 : 1;

		$addlists = array();
		if(strpos($listsToSubscribe, ',') || is_numeric($listsToSubscribe)){
			$listsArrayParam = explode(',', $listsToSubscribe);
			foreach($allLists as $oneList){
				if(in_array($oneList->listid, $listsArrayParam) && (!isset($currentSubscription[$oneList->listid]) || $currentSubscription[$oneList->listid]->status == -1)){
					$addlists[$statusAdd][$oneList->listid] = $oneList->listid;
				}
			}
		}elseif(strtolower($listsToSubscribe) == 'all'){
			foreach($allLists as $oneList){
				if($oneList->published && (!isset($currentSubscription[$oneList->listid]) || $currentSubscription[$oneList->listid]->status == -1)){
					$addlists[$statusAdd][$oneList->listid] = $oneList->listid;
				}
			}
		}

		$listsubClass = acymailing_get('class.listsub');

		if(!empty($addlists)){
			$userSubscriptions = $listsubClass->getSubscription($subid);
			$listsubClass->gid = $cmsUser->roles[0];
			// If unsubscribed from some lists and wants to subscribe again, don't try the "addSubscription" but update it...
			$listsToUpdate = array_intersect(array_keys($userSubscriptions), $addlists[$statusAdd]);
			$updateLists = array();

			if(!empty($listsToUpdate)){
				foreach($listsToUpdate as $key => $oneListToUpdate){
					// If the user already unsubscribed, don't re-subscribe him
					if($userSubscriptions[$oneListToUpdate]->status == -1) continue;
					$updateLists[] = $oneListToUpdate;
				}

				// Update the subscription
				if(!empty($updateLists)) $listsubClass->updateSubscription($subid, array($statusAdd => $updateLists));
				// Remove the updated lists from the lists to add
				$addlists[$statusAdd] = array_diff($addlists[$statusAdd], $listsToUpdate);
			}

			// Add the remaining subscriptions
			if(!empty($addlists[$statusAdd])) $listsubClass->addSubscription($subid, $addlists);
		}

		return true;
	}

	function frontMessages(){
		$sessionID = session_id();
		if(empty($sessionID)) @session_start();
		$output = '';

		$types = array('success', 'info', 'warning', 'error', 'notice', 'message');
		foreach($types as $type){
			if(empty($_SESSION['acymessage'.$type])) continue;

			$messages = $_SESSION['acymessage'.$type];
			if(!is_array($messages)) $messages = array($messages);

			$output .= '<div id="acymailing_messages_'.$type.'" class="alert alert-'.$type.' alert-block"><ul><li>'.implode('</li><li>', $messages).'</li></ul></div>';

			unset($_SESSION['acymessage'.$type]);
		}

		$_SESSION['acymessages'] = $output;
	}

	function messagingSystem(){
		$sessionID = session_id();
		if(empty($sessionID)) @session_start();

		if(empty($_SESSION['acymessages'])) return;

		echo '<div id="acymailingpopupshadow" onclick="acymailing.closeBox();"></div>
				<div id="acymailingpopup" onclick="acymailing.closeBox();">
					'.$_SESSION['acymessages'].'
				</div>';

		$script = '
				container = document.getElementById("acymailingpopup");
				container.style.width = "450px";
				container.style.left = ((window.innerWidth - 450) / 2)+"px";
				container.style.top = ((window.innerHeight - 200) / 2)+"px";';
        echo '<script type="text/javascript">'.$script.'</script>';

		$thisPlugin = plugin_basename(__DIR__);
		$mediaURL = plugins_url().'/'.$thisPlugin.'/media/';
		$mediaPath = rtrim(ABSPATH, DS.'/').DS.str_replace('/', DS, str_replace(ABSPATH, '', WP_PLUGIN_DIR)).DS.$thisPlugin.DS.'media'.DS;

		echo '<script type="text/javascript" src="'.$mediaURL.'js/acymailing.js?v='.filemtime($mediaPath.'js'.DS.'acymailing.js').'"></script>';
		echo '<link rel="stylesheet" href="'.$mediaURL.'css/acypopup.css?v='.filemtime($mediaPath.'css'.DS.'acypopup.css').'" type="text/css">';
		echo '<link rel="stylesheet" href="'.$mediaURL.'css/acyicon.css?v='.filemtime($mediaPath.'css'.DS.'acyicon.css').'" type="text/css">';
		echo '<link rel="stylesheet" href="'.$mediaURL.'css/acymessages.css?v='.filemtime($mediaPath.'css'.DS.'acymessages.css').'" type="text/css">';
	}

	function frontRouter(){
		$this->router('_front');
	}

	function router($suffix = ''){
		if(empty($suffix)) auth_redirect();

		require_once(rtrim(__DIR__,DS).DS.'back'.DS.'helpers'.DS.'helper.php');

		// Get controller. If not found, take it from the page
		$ctrl = acymailing_getVar('cmd', 'ctrl', '');
		if(empty($ctrl)){
			$ctrl = str_replace(ACYMAILING_COMPONENT.'_', '', acymailing_getVar('cmd', 'page', ''));

			if(empty($ctrl)){
				echo 'Page not found';
				return;
			}

			acymailing_setVar('ctrl', $ctrl);
		}

		if(!file_exists(constant('ACYMAILING_CONTROLLER'.strtoupper($suffix)).$ctrl.'.php')){
			echo 'Controller not found';
			return;
		}

		// Install language files if needed
		$config = acymailing_config();
		$installLanguages = $config->get('installlang', '');
		if(!empty($installLanguages)){
			$newConfig = new stdClass();
			$newConfig->installlang = '';
			$config->save($newConfig);

			acymailing_setVar('languages', $installLanguages);
			$updateHelper = acymailing_get('controller.file');
			$updateHelper->installLanguages(false);
			global $acymailingLanguages;
			$acymailingLanguages = array();
		}

		$controller = acymailing_get('controller'.$suffix.'.'.$ctrl);
		$controller->suffix = $suffix;

		$task = acymailing_getVar('cmd', 'task', '');
		$method = '';

		// There could be aliases for some tasks.. uh
		if(!empty($task)){
			if(method_exists($controller, $task)){
				$method = $task;
			}elseif(!empty($controller->aliases[$task])){
				$controller->alias = $task;
				$method = $controller->aliases[$task];
			}
		}

		if(($method !== 'spamtest' && acymailing_getVar('cmd', 'action', '') != ACYMAILING_COMPONENT.'_router') && (!defined('DOING_AJAX') || !DOING_AJAX)) $this->writeScripts();

		// Call the right page
		if(empty($method)) $method = acymailing_getVar('cmd', 'defaulttask', empty($controller->defaulttask) ? 'listing' : $controller->defaulttask);
		$controller->$method();

		if($method === 'spamtest' || acymailing_getVar('cmd', 'action', '') == ACYMAILING_COMPONENT.'_router' || (defined('DOING_AJAX') && DOING_AJAX)) $this->writeScripts();
	}

	function writeScripts(){
		$config = acymailing_config();

		acymailing_addScript(false, ACYMAILING_JS.'acymailing.js?v='.filemtime(ACYMAILING_MEDIA.'js'.DS.'acymailing.js'));
		acymailing_addStyle(false, ACYMAILING_CSS.'wordpress.css?v='.filemtime(ACYMAILING_MEDIA.'css'.DS.'wordpress.css'));
		acymailing_addStyle(false, ACYMAILING_CSS.'acyicon.css?v='.filemtime(ACYMAILING_MEDIA.'css'.DS.'acyicon.css'));
		acymailing_addStyle(false, ACYMAILING_CSS.'acymenu.css?v='.filemtime(ACYMAILING_MEDIA.'css'.DS.'acymenu.css'));

		if(acymailing_isAdmin()){
			acymailing_addStyle(false, ACYMAILING_CSS.'backend_default.css?v='.filemtime(ACYMAILING_MEDIA.'css'.DS.'backend_default.css'));
			$cssBackend = $config->get('css_backend');
			if($cssBackend == 'custom' && file_exists(ACYMAILING_MEDIA.'css'.DS.'custom_backend.css')){
				acymailing_addStyle(false, ACYMAILING_CSS.'custom_backend.css?v='.filemtime(ACYMAILING_MEDIA.'css'.DS.'custom_backend.css'));
			}
		}else{
			$cssFrontend = $config->get('css_frontend', 'default');
			if(!empty($cssFrontend)){
				acymailing_addStyle(false, ACYMAILING_CSS.'component_'.$cssFrontend.'.css?v='.filemtime(ACYMAILING_MEDIA.'css'.DS.'component_'.$cssFrontend.'.css'));
			}
		}

		if(empty($_REQUEST['noheader'])) return;

		acymailing_addScript(false, ACYMAILING_JS.'jquery/jquery-1.9.1.min.js?v='.filemtime(ACYMAILING_MEDIA.'js'.DS.'jquery'.DS.'jquery-1.9.1.min.js'));
		acymailing_addScript(false, ACYMAILING_JS.'jquery/jquery-ui.min.js?v='.filemtime(ACYMAILING_MEDIA.'js'.DS.'jquery'.DS.'jquery-ui.min.js'));
		acymailing_addStyle(false, ACYMAILING_CSS.'wponlyplugin.css?v='.filemtime(ACYMAILING_MEDIA.'css'.DS.'wponlyplugin.css'));
		acymailing_addStyle(false, get_option('siteurl').'/wp-admin/load-styles.php?c=1&dir=ltr&load%5B%5D=dashicons,admin-bar,common,forms,admin-menu,dashboard,list-tables,edit,revisions,media,themes,about,nav-menus,wp-pointer,widgets&load%5B%5D=,site-icon,l10n,buttons,wp-auth-check&ver=4.8.1');
	}

	// Add AcyMailing menu to WP left menu and define controllers
	function addMenus(){
		require_once(rtrim(__DIR__,DS).DS.'back'.DS.'helpers'.DS.'helper.php');

		$config = acymailing_config();
		$allowedGroups = explode(',', $config->get('wp_access', 'administrator'));
		$userGroups = acymailing_getGroupsByUser();

		$allowed = false;
    	foreach($userGroups as $oneGroup){
			if($oneGroup == 'administrator' || in_array($oneGroup, $allowedGroups)){
				$allowed = true;
				break;
			}
		}
		if(!$allowed) return;

		// Everyone in WordPress can read, the real test is made above
		$capability = 'read';

		add_menu_page(
			acymailing_translation('ACY_CPANEL'),
			'AcyMailing',
			$capability,
			ACYMAILING_COMPONENT.'_dashboard',
			array($this, 'router'),
			plugins_url().'/'.ACYMAILING_COMPONENT.'/media/images/icons/icon-16-acymailing.png',
			42
		);

		if(acymailing_isAllowed($config->get('acl_lists_manage', 'all'))) {
			add_submenu_page(
				ACYMAILING_COMPONENT.'_dashboard',
				acymailing_translation('ACY_DASHBOARD_LISTS'),
				acymailing_translation('ACY_DASHBOARD_LISTS'),
				$capability,
				ACYMAILING_COMPONENT.'_list',
				array($this, 'router')
			);
		}

		if(acymailing_isAllowed($config->get('acl_subscriber_manage', 'all'))) {
			add_submenu_page(
				ACYMAILING_COMPONENT.'_dashboard',
				acymailing_translation('ACY_DASHBOARD_USERS'),
				acymailing_translation('ACY_DASHBOARD_USERS'),
				$capability,
				ACYMAILING_COMPONENT.'_subscriber',
				array($this, 'router')
			);
		}

		if(acymailing_isAllowed($config->get('acl_newsletters_manage', 'all'))) {
			add_submenu_page(
				ACYMAILING_COMPONENT.'_dashboard',
				acymailing_translation('ACY_DASHBOARD_NEWSLETTERS'),
				acymailing_translation('ACY_DASHBOARD_NEWSLETTERS'),
				$capability,
				ACYMAILING_COMPONENT.'_newsletter',
				array($this, 'router')
			);
		}

		if(acymailing_isAllowed($config->get('acl_statistics_manage', 'all'))) {
			add_submenu_page(
				ACYMAILING_COMPONENT.'_dashboard',
				acymailing_translation('STATISTICS'),
				acymailing_translation('STATISTICS'),
				$capability,
				ACYMAILING_COMPONENT.'_stats',
				array($this, 'router')
			);
		}

		if(acymailing_isAllowed($config->get('acl_configuration_manage', 'all'))) {
			add_submenu_page(
				ACYMAILING_COMPONENT.'_dashboard',
				acymailing_translation('ACY_CONFIGURATION'),
				acymailing_translation('ACY_CONFIGURATION'),
				'manage_options',
				ACYMAILING_COMPONENT.'_cpanel',
				array($this, 'router')
			);
		}

		// Declare invisible menus
		$controllers = array('action', 'autonews', 'bounces', 'campaign', 'chooselist', 'data', 'diagram', 'editor', 'email', 'fields', 'file', 'filter', 'followup', 'notification', 'queue', 'send', 'simplemail', 'statsurl', 'tag', 'template', 'toggle', 'update');
		foreach($controllers as $oneCtrl){
			add_submenu_page(
				null,
				$oneCtrl,
				$oneCtrl,
				$capability,
				ACYMAILING_COMPONENT.'_'.$oneCtrl,
				array($this, 'router')
			);
		}

		add_submenu_page(
			null,
			'front',
			'front',
			$capability,
			ACYMAILING_COMPONENT.'_front',
			array($this, 'frontRouter')
		);

		global $submenu;
		if(isset($submenu[ACYMAILING_COMPONENT.'_dashboard'])) $submenu[ACYMAILING_COMPONENT.'_dashboard'][0][0] = acymailing_translation('ACY_CPANEL');
	}

	function waitHeaders(){
		ob_start();
	}

	function loadWidgets(){
		register_widget('acymailing_subform_widget');
	}

	// Add links on the plugins listing
	function addPluginLinks($links){
		$settings_link = '<a href="admin.php?page='.ACYMAILING_COMPONENT.'_cpanel">'.__('Settings').'</a>';
		$links = array_merge(array($settings_link), $links);
		return $links;
	}

	// Install DB and sample data
	function install(){

		$file_name = rtrim(__DIR__,DS).DS.'back'.DS.'tables.sql';
		$handle = fopen($file_name, 'r');
		$queries = fread($handle, filesize($file_name));
		fclose($handle);

		require_once(rtrim(__DIR__, DS).DS.'back'.DS.'helpers'.DS.'helper.php');

		if(is_multisite()){
			$currentBlog = get_current_blog_id();
			$sites = function_exists('get_sites') ? get_sites() : wp_get_sites();
			
			foreach($sites as $site){
				if(is_object($site)) $site = get_object_vars($site);
				switch_to_blog($site['blog_id']);
				$this->sampledata($queries);
			}

			switch_to_blog($currentBlog);
		}else{
			$this->sampledata($queries);
		}
	}

	function sampledata($queries){
		global $wpdb;
		$wptables = acymailing_getTableList();
		$prefix = acymailing_getPrefix();

		$acytables = str_replace('#__', $prefix, $queries);
		$tables = explode('CREATE TABLE IF NOT EXISTS', $acytables);

		foreach($tables as $oneTable) {
			$oneTable = trim($oneTable);
			if(empty($oneTable)) continue;
			$wpdb->query('CREATE TABLE IF NOT EXISTS'.$oneTable);
		}

		if(in_array($prefix.ACYMAILING_COMPONENT.'_config', $wptables)) return;

		$installClass = new acymailingInstall();
		$installClass->addPref();
		$installClass->updatePref();
		$installClass->updateSQL();

		// Reload conf
    	acymailing_config(true);

		$updateHelper = acymailing_get('helper.update');
		$updateHelper->installLanguages(false);
		$updateHelper->initList();
		$updateHelper->installTemplates();
		$updateHelper->installNotifications();
		$updateHelper->installFields();
		$updateHelper->installBounceRules();
	}
}

new acymailingInit();
