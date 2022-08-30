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

define('ACYMAILING_CMS', 'WordPress');
define('ACYMAILING_CMS_SIMPLE', 'wordpress');
define('ACYMAILING_COMPONENT', 'acymailing5');
define('ACYMAILING_DEFAULT_LANGUAGE', 'en-US');

define('ACYMAILING_BASE', '');
define('ACYMAILING_ROOT', rtrim(ABSPATH, DS.'/').DS);
define('ACYMAILING_FOLDER', WP_PLUGIN_DIR.DS.ACYMAILING_COMPONENT.DS);
define('ACYMAILING_FRONT', ACYMAILING_FOLDER.'front'.DS);
define('ACYMAILING_BACK', ACYMAILING_FOLDER.'back'.DS);
define('ACYMAILING_VIEW', ACYMAILING_BACK.'views'.DS);
define('ACYMAILING_VIEW_FRONT', ACYMAILING_FRONT.'views'.DS);
define('ACYMAILING_HELPER', ACYMAILING_BACK.'helpers'.DS);
define('ACYMAILING_CLASS', ACYMAILING_BACK.'classes'.DS);
define('ACYMAILING_TYPE', ACYMAILING_BACK.'types'.DS);
define('ACYMAILING_CONTROLLER', ACYMAILING_BACK.'controllers'.DS);
define('ACYMAILING_CONTROLLER_FRONT', ACYMAILING_FRONT.DS.'controllers'.DS);
define('ACYMAILING_MEDIA', ACYMAILING_FOLDER.'media'.DS);
define('ACYMAILING_TEMPLATE', ACYMAILING_MEDIA.'templates'.DS);
define('ACYMAILING_LANGUAGE', ACYMAILING_FOLDER.'language'.DS);
define('ACYMAILING_INC', ACYMAILING_FRONT.'inc'.DS);

define('ACYMAILING_MEDIA_URL', rtrim(plugins_url(), '/').'/'.ACYMAILING_COMPONENT.'/media/');
define('ACYMAILING_IMAGES', ACYMAILING_MEDIA_URL.'images/');
define('ACYMAILING_CSS', ACYMAILING_MEDIA_URL.'css/');
define('ACYMAILING_JS', ACYMAILING_MEDIA_URL.'js/');

define('ACYMAILING_MEDIA_FOLDER', str_replace(ABSPATH, '', WP_PLUGIN_DIR).'/'.ACYMAILING_COMPONENT.'/media');

define('ACYMAILING_CMSV', get_bloginfo( 'version' ));
define('ACYMAILING_J16', true);
define('ACYMAILING_J25', true);
define('ACYMAILING_J30', true);
define('ACYMAILING_J40', true);

define('ACY_ALLOWRAW', 2);
define('ACY_ALLOWHTML', 4);

include_once(rtrim(dirname(__DIR__),DS).DS.'compat'.DS.'punycode.php');

global $acymailingLanguages;

function acymailing_loadEditor(){
    include_once(rtrim(dirname(__DIR__), DS).DS.'compat'.DS.'wordpress.editor.php');
}

function acymailing_getTime($date){
    static $timeoffset = null;
    if($timeoffset === null){
        $timeoffset = acymailing_getCMSConfig('offset');

        if(!is_numeric($timeoffset)) {
            $timezone = new DateTimeZone($timeoffset);
            $timeoffset = $timezone->getOffset(new DateTime);
        }
    }

    return strtotime($date) - $timeoffset + date('Z');
}

function acymailing_fileGetContent($url, $timeout = 10){
    ob_start();
    $data = '';


    if(function_exists('file_get_contents')){
        if(!empty($timeout)){
            ini_set('default_socket_timeout', $timeout);
        }
        $streamContext = stream_context_create(array('ssl' => array('verify_peer' => false, 'verify_peer_name' => false)));
        $data = file_get_contents($url, false, $streamContext);
    }

    if(empty($data) && function_exists('curl_exec')){
        $conn = curl_init($url);
        curl_setopt($conn, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($conn, CURLOPT_FRESH_CONNECT, true);
        curl_setopt($conn, CURLOPT_RETURNTRANSFER, 1);
        if(!empty($timeout)){
            curl_setopt($conn, CURLOPT_TIMEOUT, $timeout);
            curl_setopt($conn, CURLOPT_CONNECTTIMEOUT, $timeout);
        }

        $data = curl_exec($conn);
        if($data === false) echo curl_error($conn);
        curl_close($conn);
    }

    if(empty($data) && function_exists('fopen') && function_exists('stream_get_contents')){
        $handle = fopen($url, "r");
        if(!empty($timeout)){
            stream_set_timeout($handle, $timeout);
        }
        $data = stream_get_contents($handle);
    }
    $warnings = ob_get_clean();

    if(acymailing_isDebug()) echo $warnings;

    return $data;
}

function acymailing_formToken(){
    return '<input type="hidden" name="_wpnonce" value="'.wp_create_nonce('acymailingnonce').'">';
}

function acymailing_checkToken(){
    $token = acymailing_getVar('cmd', '_wpnonce');
    if(!wp_verify_nonce($token, 'acymailingnonce')) die('Invalid Token');
}

function acymailing_getFormToken() {
    $token = acymailing_getVar('cmd', '_wpnonce', '');
    if(empty($token)) $token = wp_create_nonce('acymailingnonce');
    acymailing_setVar('_wpnonce', $token);

    return '_wpnonce='.$token;
}

function acymailing_setTitle($name, $picture = '', $link = ''){
    return;
}

function acymailing_translation($translation, $jsSafe = false, $interpretBackSlashes = true){
    global $acymailingLanguages;
    if(empty($acymailingLanguages['currentLanguage'])) acymailing_getLanguageTag();
    if(!isset($acymailingLanguages[$acymailingLanguages['currentLanguage']])) acymailing_loadLanguage();

    foreach($acymailingLanguages[$acymailingLanguages['currentLanguage']] as $fileContent){
        if(isset($fileContent[$translation])){
            $translation = $fileContent[$translation];
            break;
        }
    }

    if($jsSafe) $translation = addslashes($translation);
    elseif($interpretBackSlashes && strpos($translation, '\\') !== false) $translation = str_replace(array('\\\\', '\t', '\n'), array("\\", "\t", "\n"), $translation);

    return str_replace('Joomla', 'WordPress', $translation);
}

function acymailing_translation_sprintf(){
    $args = func_get_args();
    $args[0] = acymailing_translation($args[0]);
    $return = "return sprintf('".str_replace("'", "\\'", array_shift($args))."'";
    foreach($args as $oneArg){
        $return .= ",'".str_replace("'", "\\'", $oneArg)."'";
    }
    $return .= ');';
    return eval($return);
}

function acymailing_route($url, $xhtml = true, $ssl = null){
    return acymailing_baseURI().$url;
}

function acymailing_getVar($type, $name, $default = null, $hash = 'REQUEST', $mask = 0){
    $hash = strtoupper($hash);

    switch ($hash){
        case 'GET':
            $input = &$_GET;
            break;
        case 'POST':
            $input = &$_POST;
            break;
        case 'FILES':
            $input = &$_FILES;
            break;
        case 'COOKIE':
            $input = &$_COOKIE;
            break;
        case 'ENV':
            $input = &$_ENV;
            break;
        case 'SERVER':
            $input = &$_SERVER;
            break;
        default:
            $input = &$_REQUEST;
            break;
    }

    if(!isset($input[$name])) return $default;

    $result = $input[$name];
    if($type == 'array') $result = (array)$result;

    if(in_array($hash, array('POST', 'REQUEST', 'GET', 'COOKIE', ''))) $result = acymailing_stripslashes($result);
    
    return acymailing_cleanVar($result, $type, $mask);
}

function acymailing_stripslashes($element){
    if(is_array($element)){
        foreach($element as &$oneCell){
            $oneCell = acymailing_stripslashes($oneCell);
        }
    }elseif(is_string($element)){
        $element = stripslashes($element);
    }

    return $element;
}

function acymailing_cleanVar($var, $type, $mask){
    if(is_array($var)){
        foreach($var as &$val){
            $val = acymailing_cleanVar($val, $type, $mask);
        }

        return $var;
    }

    switch($type){
        case 'string':
            $var = (string)$var;
            break;
        case 'int':
            $var = (int)$var;
            break;
        case 'float':
            $var = (float)$var;
            break;
        case 'boolean':
            $var = (boolean)$var;
            break;
        case 'word':
            $var = preg_replace('#[^a-zA-Z_]#', '', $var);
            break;
        case 'cmd':
            $var = preg_replace('#[^a-zA-Z0-9_\.-]#', '', $var);
            $var = ltrim($var, '.');
            break;
        default:
            break;
    }

    if(!is_string($var)) return $var;

    $var = trim($var);

    if($mask & ACY_ALLOWRAW) return $var;

    if (!preg_match('//u', $var)){
        $var = htmlspecialchars_decode(htmlspecialchars($var, ENT_IGNORE, 'UTF-8'));
    }

    if(!($mask & ACY_ALLOWHTML)) $var = preg_replace('#<[a-zA-Z/]+[^>]*>#Uis', '', $var);

    return $var;
}

function acymailing_setVar($name, $value = null, $hash = 'REQUEST', $overwrite = true){
    $hash = strtoupper($hash);

    switch ($hash){
        case 'GET':
            $input = &$_GET;
            break;
        case 'POST':
            $input = &$_POST;
            break;
        case 'FILES':
            $input = &$_FILES;
            break;
        case 'COOKIE':
            $input = &$_COOKIE;
            break;
        case 'ENV':
            $input = &$_ENV;
            break;
        case 'SERVER':
            $input = &$_SERVER;
            break;
        default:
            $input = &$_REQUEST;
            break;
    }

    if(!isset($input[$name]) || $overwrite) $input[$name] = $value;
}

function acymailing_raiseError($level, $code, $msg, $info = null){
    acymailing_display($code.': '.$msg, 'error');
    wp_die();
}

function acymailing_getGroupsByUser($userid = null, $recursive = false){
    $currentUser = wp_get_current_user();
    return $currentUser->roles;
}

function acymailing_getGroups(){
    $groups = acymailing_loadResult('SELECT option_value FROM #__options WHERE option_name = "#__user_roles"');
    $groups = unserialize($groups);

    $usersPerGroup = acymailing_loadObjectList('SELECT meta_value, COUNT(meta_value) AS nbusers FROM #__usermeta WHERE meta_key = "#__capabilities" GROUP BY meta_value');

    $nbUsers = array();
    foreach($usersPerGroup as $oneGroup){
        $oneGroup->meta_value = unserialize($oneGroup->meta_value);
        $nbUsers[key($oneGroup->meta_value)] = $oneGroup->nbusers;
    }

    foreach($groups as $key => $group){
        $newGroup = new stdClass();
        $newGroup->id = $key;
        $newGroup->value = $key;
        $newGroup->parent_id = 0;
        $newGroup->text = $group['name'];
        $newGroup->nbusers = empty($nbUsers[$key]) ? 0 : $nbUsers[$key];
        $groups[$key] = $newGroup;
    }
    
    return $groups;
}

function acymailing_getLanguages($installed = false){
    $result = array();

    require_once( ABSPATH . 'wp-admin/includes/translation-install.php' );
    $wplanguages = wp_get_available_translations();

    $languages = get_available_languages();
    foreach($languages as $oneLang){
        $langTag = str_replace('_', '-', $oneLang);

        if(strlen($langTag) == 2) $langTag = $langTag.'-'.strtoupper($langTag);

        $lang = new stdClass();
        $lang->sef = empty($wplanguages[$oneLang]['iso'][1]) ? null : $wplanguages[$oneLang]['iso'][1];
        $lang->language = strtolower($langTag);
        $lang->name = empty($wplanguages[$oneLang]) ? $langTag : $wplanguages[$oneLang]['native_name'];
        $lang->exists = file_exists(ACYMAILING_LANGUAGE.$langTag.'.'.ACYMAILING_LANGUAGE_FILE.'.ini');
        $lang->content = true;

        $result[$langTag] = $lang;
    }

    if(!in_array('en-US', array_keys($result))){
        $lang = new stdClass();
        $lang->sef = 'en';
        $lang->language = 'en-us';
        $lang->name = 'English (United States)';
        $lang->exists = file_exists(ACYMAILING_LANGUAGE.'en-US.'.ACYMAILING_LANGUAGE_FILE.'.ini');
        $lang->content = true;

        $result['en-US'] = $lang;
    }

    return $result;
}

function acymailing_languageFolder($code){
    return ACYMAILING_LANGUAGE;
}

function acymailing_cleanSlug($slug){
    $slug = str_replace('-', ' ', $slug);

    $UTF8_LOWER_ACCENTS = array('à' => 'a', 'ô' => 'o', 'ď' => 'd', 'ḟ' => 'f', 'ë' => 'e', 'š' => 's', 'ơ' => 'o', 'ß' => 'ss', 'ă' => 'a', 'ř' => 'r', 'ț' => 't', 'ň' => 'n', 'ā' => 'a', 'ķ' => 'k', 'ŝ' => 's', 'ỳ' => 'y', 'ņ' => 'n', 'ĺ' => 'l', 'ħ' => 'h', 'ṗ' => 'p', 'ó' => 'o', 'ú' => 'u', 'ě' => 'e', 'é' => 'e', 'ç' => 'c', 'ẁ' => 'w', 'ċ' => 'c', 'õ' => 'o', 'ṡ' => 's', 'ø' => 'o', 'ģ' => 'g', 'ŧ' => 't', 'ș' => 's', 'ė' => 'e', 'ĉ' => 'c', 'ś' => 's', 'î' => 'i', 'ű' => 'u', 'ć' => 'c', 'ę' => 'e', 'ŵ' => 'w', 'ṫ' => 't', 'ū' => 'u', 'č' => 'c', 'ö' => 'oe', 'è' => 'e', 'ŷ' => 'y', 'ą' => 'a', 'ł' => 'l', 'ų' => 'u', 'ů' => 'u', 'ş' => 's', 'ğ' => 'g', 'ļ' => 'l', 'ƒ' => 'f', 'ž' => 'z', 'ẃ' => 'w', 'ḃ' => 'b', 'å' => 'a', 'ì' => 'i', 'ï' => 'i', 'ḋ' => 'd', 'ť' => 't', 'ŗ' => 'r', 'ä' => 'ae', 'í' => 'i', 'ŕ' => 'r', 'ê' => 'e', 'ü' => 'ue', 'ò' => 'o', 'ē' => 'e', 'ñ' => 'n', 'ń' => 'n', 'ĥ' => 'h', 'ĝ' => 'g', 'đ' => 'd', 'ĵ' => 'j', 'ÿ' => 'y', 'ũ' => 'u', 'ŭ' => 'u', 'ư' => 'u', 'ţ' => 't', 'ý' => 'y', 'ő' => 'o', 'â' => 'a', 'ľ' => 'l', 'ẅ' => 'w', 'ż' => 'z', 'ī' => 'i', 'ã' => 'a', 'ġ' => 'g', 'ṁ' => 'm', 'ō' => 'o', 'ĩ' => 'i', 'ù' => 'u', 'į' => 'i', 'ź' => 'z', 'á' => 'a', 'û' => 'u', 'þ' => 'th', 'ð' => 'dh', 'æ' => 'ae', 'µ' => 'u', 'ĕ' => 'e', 'œ' => 'oe');
    $slug = str_replace(array_keys($UTF8_LOWER_ACCENTS), array_values($UTF8_LOWER_ACCENTS), $slug);

    $UTF8_UPPER_ACCENTS = array('À' => 'A', 'Ô' => 'O', 'Ď' => 'D', 'Ḟ' => 'F', 'Ë' => 'E', 'Š' => 'S', 'Ơ' => 'O', 'Ă' => 'A', 'Ř' => 'R', 'Ț' => 'T', 'Ň' => 'N', 'Ā' => 'A', 'Ķ' => 'K', 'Ŝ' => 'S', 'Ỳ' => 'Y', 'Ņ' => 'N', 'Ĺ' => 'L', 'Ħ' => 'H', 'Ṗ' => 'P', 'Ó' => 'O', 'Ú' => 'U', 'Ě' => 'E', 'É' => 'E', 'Ç' => 'C', 'Ẁ' => 'W', 'Ċ' => 'C', 'Õ' => 'O', 'Ṡ' => 'S', 'Ø' => 'O', 'Ģ' => 'G', 'Ŧ' => 'T', 'Ș' => 'S', 'Ė' => 'E', 'Ĉ' => 'C', 'Ś' => 'S', 'Î' => 'I', 'Ű' => 'U', 'Ć' => 'C', 'Ę' => 'E', 'Ŵ' => 'W', 'Ṫ' => 'T', 'Ū' => 'U', 'Č' => 'C', 'Ö' => 'Oe', 'È' => 'E', 'Ŷ' => 'Y', 'Ą' => 'A', 'Ł' => 'L', 'Ų' => 'U', 'Ů' => 'U', 'Ş' => 'S', 'Ğ' => 'G', 'Ļ' => 'L', 'Ƒ' => 'F', 'Ž' => 'Z', 'Ẃ' => 'W', 'Ḃ' => 'B', 'Å' => 'A', 'Ì' => 'I', 'Ï' => 'I', 'Ḋ' => 'D', 'Ť' => 'T', 'Ŗ' => 'R', 'Ä' => 'Ae', 'Í' => 'I', 'Ŕ' => 'R', 'Ê' => 'E', 'Ü' => 'Ue', 'Ò' => 'O', 'Ē' => 'E', 'Ñ' => 'N', 'Ń' => 'N', 'Ĥ' => 'H', 'Ĝ' => 'G', 'Đ' => 'D', 'Ĵ' => 'J', 'Ÿ' => 'Y', 'Ũ' => 'U', 'Ŭ' => 'U', 'Ư' => 'U', 'Ţ' => 'T', 'Ý' => 'Y', 'Ő' => 'O', 'Â' => 'A', 'Ľ' => 'L', 'Ẅ' => 'W', 'Ż' => 'Z', 'Ī' => 'I', 'Ã' => 'A', 'Ġ' => 'G', 'Ṁ' => 'M', 'Ō' => 'O', 'Ĩ' => 'I', 'Ù' => 'U', 'Į' => 'I', 'Ź' => 'Z', 'Á' => 'A', 'Û' => 'U', 'Þ' => 'Th', 'Ð' => 'Dh', 'Æ' => 'Ae', 'Ĕ' => 'E', 'Œ' => 'Oe');
    $slug = str_replace(array_keys($UTF8_UPPER_ACCENTS), array_values($UTF8_UPPER_ACCENTS), $slug);

    $slug = trim(strtolower($slug));
    $slug = preg_replace('/(\s|[^A-Za-z0-9\-])+/', '-', $slug);
    $slug = trim($slug, '-');

    return $slug;
}

function acymailing_punycode($email, $method = 'emailToPunycode'){
    if(empty($email)) return $email;

    $explodedAddress = explode('@', $email);
    $newEmail = $explodedAddress[0];

    if (!empty($explodedAddress[1])){
        $domainExploded = explode('.', $explodedAddress[1]);
        $newdomain = '';
        $puc = new acymailingpunycode();

        foreach ($domainExploded as $domainex){
            $domainex = $puc->$method($domainex);
            $newdomain .= $domainex . '.';
        }

        $newdomain = substr($newdomain, 0, -1);
        $newEmail = $newEmail . '@' . $newdomain;
    }

    return $newEmail;
}

function acymailing_select($data, $name, $attribs = null, $optKey = 'value', $optText = 'text', $selected = null, $idtag = false, $translate = false){
    $dropdown = '<select id="'.str_replace(array('[', ']', ' '), '', empty($idtag) ? $name : $idtag).'" name="'.$name.'" '.(empty($attribs) ? '' : $attribs).'>';

    foreach($data as $key => $oneOption){
        $disabled = false;
        if(is_object($oneOption)){
            $value = $oneOption->$optKey;
            $text = $oneOption->$optText;
            if(isset($oneOption->disable)) $disabled = $oneOption->disable;
        }else{
            $value = $key;
            $text = $oneOption;
        }

        if($translate) $text = acymailing_translation($text);

        if(strtolower($value) == '<optgroup>'){
            $dropdown .= '<optgroup label="' . htmlspecialchars($text, ENT_QUOTES, 'UTF-8') . '">';
        }elseif(strtolower($value) == '</optgroup>'){
            $dropdown .= '</optgroup>';
        }else {
            $dropdown .= '<option value="' . htmlspecialchars($value, ENT_QUOTES, 'UTF-8') . '"' . ($value == $selected ? ' selected="selected"' : '') . ($disabled ? ' disabled="disabled"' : '') . '>' . htmlspecialchars($text, ENT_QUOTES, 'UTF-8') . '</option>';
        }
    }

    $dropdown .= '</select>';
    return $dropdown;
}

function acymailing_selectOption($value, $text = '', $optKey = 'value', $optText = 'text', $disable = false){
    $option = new stdClass();
    $option->$optKey = $value;
    $option->$optText = $text;
    $option->disable = $disable;

    return $option;
}

function acymailing_gridID($rowNum, $recId, $checkedOut = false, $name = 'cid', $stub = 'cb'){
    return '<input type="checkbox" id="'.$stub.$rowNum.'" name="'.$name.'[]" value="'.$recId.'" onclick="acymailing.isChecked(this);">';
}

function acymailing_calendar($value, $name, $id, $format = '%Y-%m-%d', $attribs = array()){
    acymailing_addScript(false, ACYMAILING_JS.'datepicker.js?v='.ACYMAILING_MEDIA.'js'.DS.'datepicker.js');
    acymailing_addStyle(false, ACYMAILING_CSS.'datepicker.css?v='.ACYMAILING_MEDIA.'css'.DS.'datepicker.css');

    $currentYear = (int) date("Y");
    $js = 'document.addEventListener("DOMContentLoaded", function(event) {
		    var picker = new Pikaday({
                field: document.getElementById("'.$id.'"),
				firstDay: 1,
				yearRange: ['.($currentYear-100).','.($currentYear+20).'],
				i18n: {
                    previousMonth : "Previous Month",
                    nextMonth     : "Next Month",
                    months        : ["'.acymailing_translation('ACY_JANUARY', true).'","'.acymailing_translation('ACY_FEBRUARY', true).'","'.acymailing_translation('ACY_MARCH', true).'","'.acymailing_translation('ACY_APRIL', true).'","'.acymailing_translation('ACY_MAY', true).'","'.acymailing_translation('ACY_JUNE', true).'","'.acymailing_translation('ACY_JULY', true).'","'.acymailing_translation('ACY_AUGUST', true).'","'.acymailing_translation('ACY_SEPTEMBER', true).'","'.acymailing_translation('ACY_OCTOBER', true).'","'.acymailing_translation('ACY_NOVEMBER', true).'","'.acymailing_translation('ACY_DECEMBER', true).'"],
                    weekdays      : ["'.acymailing_translation('ACY_SUNDAY', true).'","'.acymailing_translation('ACY_MONDAY', true).'","'.acymailing_translation('ACY_TUESDAY', true).'","'.acymailing_translation('ACY_WEDNESDAY', true).'","'.acymailing_translation('ACY_THURSDAY', true).'","'.acymailing_translation('ACY_FRIDAY', true).'","'.acymailing_translation('ACY_SATURDAY', true).'"]
                },
                format: "'.$format.'"
			});
		});';
    acymailing_addScript(true, $js);

    $attribs['type'] = 'text';
    $attribs['id'] = $id;
    $attribs['name'] = $name;
    $attribs['value'] = $value;

    $result = '<input ';
    foreach($attribs as $key => $value){
        $result .= ' '.$key.'="'.addslashes($value).'" ';
    }
    $result .= '/>';
    return $result;
}

function acymailing_boolean($name, $attribs = '', $selected = null, $yes = 'JOOMEXT_YES', $no = 'JOOMEXT_NO', $id = false){
    $options = array('1' => acymailing_translation($yes), '0' => acymailing_translation($no));
    return acymailing_radio($options, $name, $attribs, 'value', 'text', $selected ? 1 : 0, $id);
}

function acymailing_radio($options, $name, $addParams = '', $optKey = 'value', $optText = 'text', $selected = null, $idtag = false){
    $idtag = preg_replace('#[^a-zA-Z0-9_]+#mi', '_', str_replace(array('[', ']'), array('_', ''), $idtag ? $idtag : $name));

    $return = '<div class="acymailingradiogroup">';
    foreach($options as $value => $label){
        if(is_object($label)){
            $value = $label->$optKey;
            $label = $label->$optText;
        }

        $value = htmlspecialchars($value, ENT_QUOTES, 'utf-8');
        $name = htmlspecialchars($name, ENT_QUOTES, 'utf-8');
        $id = $idtag.$value;

        $return .= '<input type="radio" '.$addParams.' name="'.$name.'" value="'.$value.'" id="'.$id.'" '.($value == $selected ? 'checked="checked"' : '').' />';
        $return .= '<label for="'.$id.'" id="'.$id.'-lbl">'.$label.'</label>';
    }
    $return .= '</div>';

    return $return;
}

function acymailing_isAdmin(){
    $page = acymailing_getVar('string', 'page', '');
    return  !in_array($page, array(ACYMAILING_COMPONENT.'_front', 'front'));
}

function acymailing_getUserVar($key, $request, $default = null, $type = 'none'){
    acymailing_session();

    $value = acymailing_getVar($type, $request);

    if($value !== null){
        $_SESSION['acyUserVar'][$key][$request] = $value;
        return $value;
    }else{
        if(isset($_SESSION['acyUserVar'][$key][$request])) return $_SESSION['acyUserVar'][$key][$request];

        return $default;
    }
}

function acymailing_getCMSConfig($varname, $default = null){
    $map = array(
        'offset' => 'timezone_string',
        'list_limit' => 'posts_per_page',
        'sitename' => 'blogname',
        'mailfrom' => 'new_admin_email',
        'feed_email' => 'new_admin_email'
    );

    if(!empty($map[$varname])) $varname = $map[$varname];
    $value = get_option($varname, $default);

    if($varname == 'timezone_string' && empty($value)){
        $value = acymailing_getCMSConfig('gmt_offset');

        if(empty($value))
            $value = 'UTC';
        elseif ($value < 0)
            $value = 'GMT' . $value;
        else
            $value = 'GMT+' . $value;
    }

    if($varname == 'posts_per_page'){
        $possibilities = array(5,10,15,20,25,30,50,100);
        $closest = 5;
        foreach($possibilities as $possibility) {
            if(abs($value - $closest) > abs($value - $possibility)) $closest = $possibility;
        }
        $value = $closest;
    }

    return $value;
}

function acymailing_addPageParam($url, $ajax = false, $front = false){
    preg_match('#^([a-z]+)(?:[^a-z]|$)#Uis', $url, $ctrl);

    $pages = array(
        'list' => array('action'),
        'subscriber' => array('data', 'filter'),
        'newsletter' => array('autonews', 'campaign', 'email', 'followup', 'notification', 'queue', 'simplemail', 'template'),
        'stats' => array('diagram', 'statsurl'),
        'cpanel' => array('bounces', 'fields', 'update')
    );

    foreach($pages as $page => $controllers){
        if(!in_array($ctrl[1], $controllers)) continue;
        $ctrl[1] = $page;
        break;
    }

    if($front){
        if($ajax){
            $link = 'admin-ajax.php?page='.ACYMAILING_COMPONENT.'_front&ctrl='.$url.'&action='.ACYMAILING_COMPONENT.'_frontrouter&'.acymailing_noTemplate();
        }else{
            $link = 'admin.php?page='.ACYMAILING_COMPONENT.'_front&ctrl='.$url;
        }
        $link = 'wp-admin/'.$link;
    }else{
        if($ajax){
            $link = 'admin-ajax.php?page='.ACYMAILING_COMPONENT.'_'.$ctrl[1].'&ctrl='.$url.'&action='.ACYMAILING_COMPONENT.'_router&'.acymailing_noTemplate();
        }else{
            $link = 'admin.php?page='.ACYMAILING_COMPONENT.'_'.$ctrl[1].'&ctrl='.$url;
        }
    }
    
    return $link;
}

function acymailing_redirect($url, $msg = '', $msgType = 'message'){
    if(acymailing_isAdmin() && substr($url, 0, 4) != 'http' && substr($url, 0, 4) != 'www.') $url = acymailing_addPageParam($url);
    @ob_get_clean();
    if(empty($url)) $url = acymailing_rootURI();
    wp_redirect($url);
    exit;
}

function acymailing_getLanguageTag(){
    global $acymailingLanguages;
    if(!isset($acymailingLanguages['currentLanguage'])) $acymailingLanguages['currentLanguage'] = get_bloginfo("language");
    if(strpos($acymailingLanguages['currentLanguage'], '-') === false){
        $acymailingLanguages['currentLanguage'] = $acymailingLanguages['currentLanguage'].'-'.strtoupper($acymailingLanguages['currentLanguage']);
    }
    return $acymailingLanguages['currentLanguage'];
}

function acymailing_getLanguageLocale(){
    return array(get_locale());
}

function acymailing_setLanguage($lang){
    global $acymailingLanguages;
    $previousLanguage = $acymailingLanguages['currentLanguage'];
    $acymailingLanguages['currentLanguage'] = $lang;
    return $previousLanguage;
}

function acymailing_baseURI($pathonly = false){
    if(acymailing_isAdmin()) return acymailing_rootURI().'wp-admin/';
    return acymailing_rootURI();
}

function acymailing_rootURI($pathonly = false, $path = 'siteurl'){
    $siteURL = get_option($path).'/';

    if(is_ssl()){
        $siteURL = str_replace('http://', 'https://', $siteURL);
    }else{
        $siteURL = str_replace('https://', 'http://', $siteURL);
    }
    
    return $siteURL;
}

function acymailing_generatePassword($length = 8){
    $salt = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $pass = array();
    $alphaLength = strlen($salt) - 1;
    for ($i = 0; $i < $length; $i++) {
        $n = mt_rand(0, $alphaLength);
        $pass[] = $salt[$n];
    }
    return implode($pass);
}

function acymailing_currentUserId($email = null){
    if(!empty($email)){
        return acymailing_loadResult('SELECT id FROM '.acymailing_table('users', false).' WHERE user_email = '.acymailing_escapeDB($email));
    }

    return get_current_user_id();
}

function acymailing_currentUserName($userid = null){
    if(!empty($userid)){
        $special = get_user_by('id', $userid);
        return $special->display_name;
    }

    $current_user = wp_get_current_user();
    return $current_user->display_name;
}

function acymailing_currentUserEmail($userid = null){
    if(!empty($userid)){
        $special = get_user_by('id', $userid);
        return $special->user_email;
    }

    $current_user = wp_get_current_user();
    return $current_user->user_email;
}

function acymailing_authorised($action, $assetname = null){
    return acymailing_isAdmin();
}

function acymailing_loadLanguageFile($extension, $basePath = null, $lang = null, $reload = false, $default = true){
    if($extension == ACYMAILING_COMPONENT) $extension = 'com_acymailing';

    global $acymailingLanguages;
    $currentLanguage = acymailing_getLanguageTag();
    if(isset($acymailingLanguages[$currentLanguage][$extension]) && !$reload) return true;

    if(!file_exists(ACYMAILING_FOLDER.'language'.DS.$currentLanguage.'.'.$extension.'.ini')) $currentLanguage = ACYMAILING_DEFAULT_LANGUAGE;
    if(!file_exists(ACYMAILING_FOLDER.'language'.DS.$currentLanguage.'.'.$extension.'.ini')) return false;

    $data = acymailing_fileGetContent(ACYMAILING_FOLDER.'language'.DS.$currentLanguage.'.'.$extension.'.ini');
    $data = str_replace('"_QQ_"', '"', $data);
    $separate = explode("\n", $data);
    foreach($separate as $raw){
        if(strpos($raw, '=') === false) continue;
        $keyval = explode('=', $raw);
        $acymailingLanguages[$acymailingLanguages['currentLanguage']][$extension][$keyval[0]] = trim($keyval[1], "\"\r\n\t ");
    }
}

function acymailing_escapeDB($value){
    return "'".esc_sql($value)."'";
}

function acymailing_query($query){
    global $wpdb;
    $query = acymailing_prepareQuery($query);
    return $wpdb->query($query);
}

function acymailing_loadObjectList($query, $key = '', $offset = null, $limit = null){
    global $wpdb;
    $query = acymailing_prepareQuery($query);

    if(isset($offset)) $query .= ' LIMIT '.$offset.','.$limit;

    $results = $wpdb->get_results($query);
    if(empty($key)) return $results;

    $sorted = array();
    foreach($results as $oneRes){
        $sorted[$oneRes->$key] = $oneRes;
    }

    return $sorted;
}

function acymailing_getColumns($table){
    $result = array();

    $query = 'SHOW COLUMNS FROM '.$table;
    $results = acymailing_loadObjectList($query);
    foreach($results as $oneColumn){
        $result[$oneColumn->Field] = $oneColumn->Type;
    }

    return $result;
}

function acymailing_prepareQuery($query){
    global $wpdb;
    $query = str_replace('#__', $wpdb->prefix, $query);
    if(is_multisite()) $query = str_replace($wpdb->prefix.'users', $wpdb->base_prefix.'users', $query);
    return $query;
}

function acymailing_loadObject($query){
    global $wpdb;
    $query = acymailing_prepareQuery($query);

    return $wpdb->get_row($query);
}

function acymailing_loadResult($query){
    global $wpdb;
    $query = acymailing_prepareQuery($query);

    return $wpdb->get_var($query);
}

function acymailing_loadResultArray($query){
    global $wpdb;
    $query = acymailing_prepareQuery($query);

    return $wpdb->get_col($query);
}

function acymailing_getEscaped($text, $extra = false) {
    $result = esc_sql($text);
    if($extra) $result = addcslashes($result, '%_');

    return $result;
}

function acymailing_getDBError(){
    global $wpdb;
    return $wpdb->last_error;
}

function acymailing_insertObject($table, $element){
    global $wpdb;
    $element = get_object_vars($element);
    $table = acymailing_prepareQuery($table);
    $wpdb->insert($table, $element);
    return $wpdb->insert_id;
}

function acymailing_updateObject($table, $element, $pkey){
    global $wpdb;
    $element = get_object_vars($element);
    $table = acymailing_prepareQuery($table);
    $nbUpdated = $wpdb->update($table, $element, array($pkey => $element[$pkey]));
    return $nbUpdated !== false;
}

function acymailing_getPrefix(){
    global $wpdb;
    return $wpdb->prefix;
}

function acymailing_insertID(){
    global $wpdb;
    return $wpdb->insert_id;
}

function acymailing_getTableList(){
    global $wpdb;
    return acymailing_loadResultArray("SELECT table_name FROM information_schema.tables WHERE table_schema='".$wpdb->dbname."' AND table_name LIKE '".$wpdb->prefix."%'");
}

function acymailing_completeLink($link, $popup = false, $redirect = false){
    if($popup || acymailing_isNoTemplate()) $link .= '&'.acymailing_noTemplate();

    $link = acymailing_addPageParam($link);
    return acymailing_route($link);
}

function acymailing_noTemplate(){
    return 'noheader=1';
}

function acymailing_isNoTemplate(){
    return acymailing_getVar('cmd', 'noheader') == '1';
}

function acymailing_setNoTemplate($status = true){
    if($status) acymailing_setVar('noheader', '1');
    else unset($_REQUEST['noheader']);
}

function acymailing_cmsLoaded(){
    defined('ABSPATH') or die('Restricted access');
}

function acymailing_formOptions($order = null, $task = ''){
    echo '<input type="hidden" name="task" value="'.$task.'"/>';
    echo '<input type="hidden" name="page" value="'.acymailing_getVar('cmd', 'page', '').'"/>';
    echo '<input type="hidden" name="ctrl" value="'.acymailing_getVar('cmd', 'ctrl', '').'"/>';
    if($order) {
        echo '<input type="hidden" name="boxchecked" value="0"/>';
        echo '<input type="hidden" name="filter_order" value="'.$order->value.'"/>';
        echo '<input type="hidden" name="filter_order_Dir" value="'.$order->dir.'"/>';
    }
    echo acymailing_formToken();
}

function acymailing_enqueueMessage($message, $type = 'success'){
    $result = is_array($message) ? implode('<br/>', $message) : $message;

    $type = str_replace(array('notice', 'message'), array('info', 'success'), $type);
    acymailing_session();
    if(empty($_SESSION['acymessage'.$type]) || !in_array($result, $_SESSION['acymessage'.$type])) $_SESSION['acymessage'.$type][] = $result;
}

function acymailing_displayMessages(){
    $types = array('success', 'info', 'warning', 'error', 'notice', 'message');
    acymailing_session();
    foreach($types as $type){
        if(empty($_SESSION['acymessage'.$type])) continue;
        acymailing_display($_SESSION['acymessage'.$type], $type);
        unset($_SESSION['acymessage'.$type]);
    }
}

function acymailing_editCMSUser($userid){
    return ACYMAILING_LIVE.'wp-admin/profile.php?wp_http_referer='.urlencode(ACYMAILING_LIVE.'wp-admin/user.php?update=add&id='.$userid);
}

function acymailing_prepareAjaxURL($url){
   return htmlspecialchars_decode(acymailing_route(acymailing_addPageParam($url, true)));
}

function acymailing_cmsACL(){
    if(!current_user_can('manage_options')) return '';

    $result = '<span class="acyblocktitle">'.acymailing_translation('ACY_ACL').'</span>
       <table class="acymailing_table" cellspacing="1">
            <tr>
                <td class="acykey">'.acymailing_translation('ACY_ACCESS_ROLES').'</td>
                <td>';

    $config = acymailing_config();
    $wp_access = explode(',', $config->get('wp_access', 'administrator'));

    $checkboxes = array();

    $i = 0;
    $roles = acymailing_getGroups();
    foreach($roles as $name => $oneRole){
        $checked = (in_array($name, $wp_access) || $name == 'administrator') ? 'checked="checked"' : '';
        $disabled = $name == 'administrator' ? 'disabled="disabled"' : '';
        $checkboxes[] = '<span style="white-space:nowrap">
            <input type="checkbox" name="config[wp_access][]" id="wp_access_'.$i.'" value="'.$name.'" style="margin-left:10px" '.$checked.' '.$disabled.'/>
            <label for="wp_access_'.$i.'">'.$oneRole->text.'</label>
        </span>';
        $i++;
    }

    $result .= implode(' ', $checkboxes);
    $result .= '</td></tr></table>';

    return $result;
}

function acymailing_isDebug(){
    return defined('WP_DEBUG') && WP_DEBUG;
}

function acymailing_sendMail($to, $subject, $body, $attachments = array(), $headers = array()){
    return wp_mail($to, $subject, $body, $headers, $attachments);
}

function acymailing_date($time = 'now', $format = null, $tz = true, $gregorian = false){
    if($time == 'now') $time = time();
    if(is_numeric($time)) $time = strftime('%Y-%m-%d %H:%M:%S', $time);

    if(!$format || strpos($format, 'ACY_DATE_FORMAT') !== false) $format = 'ACY_DATE_FORMAT_LC1';
    $format = acymailing_translation($format);

    if ($tz === null) {
        $date = new DateTime($time);
        return acymailing_translateDate($date->format($format));
    }

    $cmsOffset = acymailing_getCMSConfig('offset');
    $timezone = new DateTimeZone($cmsOffset);

    if($tz === false){
        if(!is_numeric($cmsOffset)) $cmsOffset = $timezone->getOffset(new DateTime);
        return acymailing_translateDate(date($format, strtotime($time) + $cmsOffset));
    }else{
        $date = new DateTime($time, $timezone);
        return acymailing_translateDate($date->format($format));
    }
}

function acymailing_translateDate($date){
    $map = array(
        'January' => 'ACY_JANUARY',
        'February' => 'ACY_FEBRUARY',
        'March' => 'ACY_MARCH',
        'April' => 'ACY_APRIL',
        'May' => 'ACY_MAY',
        'June' => 'ACY_JUNE',
        'July' => 'ACY_JULY',
        'August' => 'ACY_AUGUST',
        'September' => 'ACY_SEPTEMBER',
        'October' => 'ACY_OCTOBER',
        'November' => 'ACY_NOVEMBER',
        'December' => 'ACY_DECEMBER',
        'Monday' => 'ACY_MONDAY',
        'Tuesday' => 'ACY_TUESDAY',
        'Wednesday' => 'ACY_WEDNESDAY',
        'Thursday' => 'ACY_THURSDAY',
        'Friday' => 'ACY_FRIDAY',
        'Saturday' => 'ACY_SATURDAY',
        'Sunday' => 'ACY_SUNDAY'
    );
    
    foreach($map as $english => $translationKey){
        $translation = acymailing_translation($translationKey);
        if($translation == $translationKey) continue;

        $date = preg_replace('#'.preg_quote($english).'( |,|$)#i', $translation.'$1', $date);
        $date = preg_replace('#'.preg_quote(substr($english, 0, 3)).'( |,|$)#i', mb_substr($translation, 0, 3).'$1', $date);
    }

    return $date;
}

function acymailing_addScript($raw, $script, $type = "text/javascript", $defer = false, $async = false){
    if($raw){
        echo '<script type="'.$type.'">'.$script.'</script>';
    }else{
        echo '<script type="'.$type.'" src="'.$script.'"'.($async ? ' async' : '').($defer ? ' defer' : '').'></script>';
    }
}

function acymailing_addStyle($raw, $style, $type = 'text/css', $media = null, $attribs = array()){
    if($raw){
        echo '<style type="'.$type.'"'.(empty($media) ? '' : ' media="'.$media.'"').'>'.$style.'</style>';
    }else{
        echo '<link rel="stylesheet" href="'.$style.'" type="'.$type.'"'.(empty($media) ? '' : ' media="'.$media.'"').'>';
    }
}

global $acymailingMetaData;
function acymailing_addMetadata($meta, $data, $name = 'name'){
    global $acymailingMetaData;

    $tag = new stdClass();
    $tag->meta = $meta;
    $tag->data = $data;
    $tag->name = $name;

    $acymailingMetaData[] = $tag;
}

add_action('wp_head', 'acymailing_head_wp');
add_action('admin_head', 'acymailing_head_wp');
function acymailing_head_wp(){
    global $acymailingMetaData;

    if(!empty($acymailingMetaData)) {
        foreach ($acymailingMetaData as $metadata) {
            if(empty($metadata->data)) continue;
            echo '<meta '.$metadata->name.'="' . $metadata->meta . '" content="' . $metadata->data . '"/>';
        }
    }

    $acymailingMetaData = array();
}

function acymailing_userEditLink(){
    return acymailing_rootURI().'wp-admin/user-edit.php?user_id=';
}

global $acymailingPlugins;
function acymailing_importPlugin($family, $name = null){
    if(!empty($name)) {
        $plugins = array($name);
    }else {
        $plugins = acymailing_getFolders(ACYMAILING_FOLDER . 'plugins' . DS . $family);
    }

    if(empty($plugins)) return false;

    global $acymailingPlugins;
    foreach($plugins as $onePlugin){
        $pluginFile = ACYMAILING_FOLDER.'plugins'.DS.$family.DS.$onePlugin.DS.$onePlugin.'.php';
        $className = 'plg'.ucfirst($family).ucfirst($onePlugin);

        if(isset($acymailingPlugins[$className]) || !file_exists($pluginFile) || !include_once($pluginFile)) continue;
        if(!class_exists($className)) continue;

        $params = array();
        $acymailingPlugins[$className] = new $className($params, array());
    }

    return true;
}

function acymailing_getPlugin($family, $name = null){
    if(empty($name)){
        if(file_exists(ACYMAILING_FOLDER . 'plugins' . DS . $family)) $plugins = acymailing_getFolders(ACYMAILING_FOLDER . 'plugins' . DS . $family);
        if(empty($plugins)) return array();

        foreach($plugins as &$onePlugin){
            $plugin = new stdClass();
            $plugin->params = array();
            $plugin->name = $onePlugin;
            $onePlugin = $plugin;
        }
        return $plugins;
    }
    
    $plugin = new stdClass();
    $plugin->params = array();
    return $plugin;
}

function acymailing_trigger($method, $args = array()){
    $args = (array) $args;
    $result = array();
    
    global $acymailingPlugins;
    foreach($acymailingPlugins as $onePlugin){
        if(!method_exists($onePlugin, $method)) continue;
        $value = call_user_func_array(array($onePlugin, $method), $args);

        if(isset($value)) $result[] = $value;
    }

    return $result;
}

function acymailing_isPluginEnabled($family, $name){
    return file_exists(ACYMAILING_FOLDER.'plugins'.DS.$family.DS.$name.DS.$name.'.php');
}

function acymailing_filterText($text){
    return $text;
}

function acymailing_getLanguagePath($basePath, $language = null){
    return ACYMAILING_FOLDER.'language';
}

function acymailing_checkPluginsFolders(){
}

function acymailing_askLog($current = true, $message = 'ACY_NOTALLOWED', $type = 'error'){
    $url = acymailing_rootURI().'wp-login.php';
    if($current) $url .= '&redirect_to='.base64_encode(acymailing_currentURL());

    acymailing_redirect($url, acymailing_translation($message), $type);
}

function acymailing_frontendLink($link, $newsletter = true, $popup = false, $complete = false){
    return acymailing_rootURI().acymailing_addPageParam($link, true, true);
}

function acymailing_sef($links){
    return null;
}

function acymailing_addBreadcrumb($title, $link = ''){
}

function acymailing_getMenu(){
    return null;
}

function acymailing_getTitle(){
    ob_start();
    wp_title('');
    $title = ob_get_clean();
    return trim($title);
}

function acymailing_extractArchive($archive, $destination){
    if(substr($archive, strlen($archive)-4) !== '.zip') return false;

    $zip = new ZipArchive;
    $res = $zip->open($archive);

    if($res !== true) return false;

    $zip->extractTo($destination);
    $zip->close();

    return true;
}

function acymailing_setPageTitle($title){
    return true;
}

class acymailingBridgeController {
    var $aliases;
    var $suffix;

    public function __construct($config = array()) {
        global $acymailingCmsUserVars;
        $this->cmsUserVars = $acymailingCmsUserVars;
    }

    public function getName(){
        if(empty($this->_name)){
            $classname = get_class($this);
            $ctrlpos = strpos($classname, 'Controller');
            $this->_name = strtolower(substr($classname, 0, $ctrlpos));
        }

        return $this->_name;
    }

    function display(){
        $view = acymailing_get('view'.$this->suffix.'.'.$this->getName());
        if(!empty($this->alias)) $view->setLayout($this->alias);
        $view->display();
    }

    function registerDefaultTask($task){
        $this->defaulttask = $task;
    }

    function registerTask($alias, $task){
        $this->aliases[$alias] = $task;
    }

    public function cancel() {
        acymailing_setVar('layout', 'listing');
        $this->display();
    }
}

class acymailingView {
    public function __construct($config = array()) {
        global $acymailingCmsUserVars;
        $this->cmsUserVars = $acymailingCmsUserVars;
    }

    public function getName(){
        if(empty($this->_name)){
            $classname = get_class($this);
            $viewpos = strpos($classname, 'View');
            $this->_name = strtolower(substr($classname, $viewpos + 4));
        }

        return $this->_name;
    }

    public function getLayout(){
        return acymailing_getVar('string', 'layout', acymailing_getVar('string', 'task', 'listing'));
    }

    public function setLayout($value){
        acymailing_setVar('layout', $value);
    }

    public function display(){
        echo ob_get_clean();

        $view = $this->getLayout();
        $viewFolder = acymailing_isAdmin() ? ACYMAILING_VIEW : ACYMAILING_VIEW_FRONT;
        if(!file_exists($viewFolder.$this->getName().DS.'tmpl'.DS.$view.'.php')){
            $view = strtolower($view);
            if(!file_exists($viewFolder.$this->getName().DS.'tmpl'.DS.$view.'.php')) {
                $view = 'default';
            }
        }
        include($viewFolder.$this->getName().DS.'tmpl'.DS.$view.'.php');
    }

    public function escape($value){
        return htmlspecialchars($value, ENT_COMPAT, "UTF-8");
    }
}

global $acymailingCmsUserVars;
$acymailingCmsUserVars = new stdClass();
$acymailingCmsUserVars->table = 'users';
$acymailingCmsUserVars->name = 'display_name';
$acymailingCmsUserVars->username = 'user_login';
$acymailingCmsUserVars->id = 'id';
$acymailingCmsUserVars->email = 'user_email';
$acymailingCmsUserVars->registered = 'user_registered';
$acymailingCmsUserVars->blocked = 'user_status';

