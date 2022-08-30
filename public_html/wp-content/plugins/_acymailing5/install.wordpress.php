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

class acymailingInstall
{
    var $level = 'enterprise';
    var $version = '5.10.12';
    var $update = false;
    var $fromVersion = '';

    function __construct(){
        include_once(rtrim(__DIR__,DS).DS.'back'.DS.'helpers'.DS.'helper.php');
    }

    function addPref(){
        $allPref = array();

        $this->level = ucfirst($this->level);
        $allPref['level'] = $this->level;
        $allPref['version'] = $this->version;
        $allPref['smtp_port'] = '';

        $allPref['from_name'] = get_option('fromname', '');
        $allPref['from_email'] = get_option('admin_email', '');
        $allPref['sendmail_path'] = '';
        $allPref['smtp_host'] = get_option('mailserver_url', '');
        $allPref['smtp_port'] = get_option('mailserver_port', '');
        $allPref['smtp_secured'] = $allPref['smtp_port'] == 465 ? 'ssl' : '';
        $allPref['smtp_auth'] = 1;
        $allPref['smtp_username'] = get_option('mailserver_login', '');
        $allPref['smtp_password'] = get_option('mailserver_pass', '');
        $allPref['mailer_method'] = empty($allPref['smtp_host']) ? 'mail' : 'smtp';

        $allPref['reply_name'] = $allPref['from_name'];
        $allPref['reply_email'] = $allPref['from_email'];
        $allPref['bounce_email'] = $allPref['from_email'];
        $allPref['cron_sendto'] = $allPref['from_email'];

        $allPref['add_names'] = '1';
        $allPref['encoding_format'] = '8bit';
        $allPref['charset'] = 'UTF-8';
        $allPref['word_wrapping'] = '150';
        $allPref['hostname'] = '';
        $allPref['embed_images'] = '0';
        $allPref['embed_files'] = '1';
        $allPref['editor'] = 'acyeditor';
        $allPref['multiple_part'] = '1';

        $allPref['queue_nbmail'] = '40';
        $allPref['queue_nbmail_auto'] = '70';
        $allPref['queue_type'] = 'auto';
        $allPref['queue_try'] = '3';
        $allPref['queue_pause'] = '120';
        $allPref['allow_visitor'] = '1';
        $allPref['require_confirmation'] = '1';
        $allPref['priority_newsletter'] = '3';
        $allPref['allowedfiles'] = 'zip,doc,docx,pdf,xls,txt,gzip,rar,jpg,jpeg,gif,xlsx,pps,csv,bmp,ico,odg,odp,ods,odt,png,ppt,swf,xcf,mp3,wma';
        $allPref['uploadfolder'] = ACYMAILING_MEDIA_FOLDER.'/upload';
        $allPref['confirm_redirect'] = '';
        $allPref['subscription_message'] = '1';
        $allPref['notification_unsuball'] = '';
        $allPref['cron_next'] = '1251990901';
        $allPref['confirmation_message'] = '1';
        $allPref['welcome_message'] = '1';
        $allPref['unsub_message'] = '1';
        $allPref['cron_last'] = '0';
        $allPref['cron_fromip'] = '';
        $allPref['cron_report'] = '';
        $allPref['cron_frequency'] = '900';
        $allPref['cron_sendreport'] = '2';

        $allPref['cron_fullreport'] = '1';
        $allPref['cron_savereport'] = '2';
        $allPref['cron_savepath'] = ACYMAILING_MEDIA_FOLDER.'/logs/report_{year}_{month}.log';
        $allPref['notification_created'] = '';
        $allPref['notification_accept'] = '';
        $allPref['notification_refuse'] = '';
        $allPref['forward'] = '0';

        $allPref['priority_followup'] = '2';
        $allPref['unsub_redirect'] = '';
        $allPref['use_sef'] = '0';
        $allPref['itemid'] = '0';
        $allPref['css_module'] = 'default';
        $allPref['css_frontend'] = 'default';
        $allPref['css_backend'] = '';

        $allPref['unsub_reasons'] = serialize(array('UNSUB_SURVEY_FREQUENT', 'UNSUB_SURVEY_RELEVANT'));
        $allPref['security_key'] = acymailing_generateKey(30);
        $allPref['export_excelsecurity'] = 1;
        $allPref['gdpr_export'] = 0;
        $allPref['anonymous_tracking'] = '0';
        $allPref['anonymizeold'] = '0';

        $allPref['installcomplete'] = '0';

        $allPref['Starter'] = '0';
        $allPref['Essential'] = '1';
        $allPref['Business'] = '2';
        $allPref['Enterprise'] = '3';
        $allPref['Sidekick'] = '4';

        $query = "INSERT IGNORE INTO `#__acymailing_config` (`namekey`,`value`) VALUES ";
        foreach($allPref as $namekey => $value){
            $query .= '('.acymailing_escapeDB($namekey).','.acymailing_escapeDB($value).'),';
        }
        $query = rtrim($query, ',');

        try{
            $res = acymailing_query($query);
        }catch(Exception $e){
            $res = null;
        }
        if($res === null){
            echo isset($e) ? $e->getMessage() : substr(strip_tags(acymailing_getDBError()), 0, 200).'...';
            return false;
        }
        return true;
    }

    function updatePref(){
        try{
            $this->fromVersion = acymailing_loadResult("SELECT `value` FROM `#__acymailing_config` WHERE `namekey` = 'version'");
        }catch(Exception $e){
            $errorMessage = strip_tags(acymailing_getDBError());
            acymailing_display(empty($errorMessage) ? $e->getMessage() : substr($errorMessage, 0, 200).'...', 'error');
            return false;
        }

        if($this->fromVersion == $this->version) return true;

        $this->update = true;

        acymailing_query("REPLACE INTO `#__acymailing_config` (`namekey`,`value`) VALUES ('version',".acymailing_escapeDB($this->version)."),('installcomplete','0')");
    }

    function updateSQL(){
        if(!$this->update) return true;

        if(version_compare($this->fromVersion, '5.9.0', '<')){
        }

        if(version_compare($this->fromVersion, '5.9.7', '<')){
            if(!acymailing_level(3)) {
                $this->updateQuery("UPDATE #__acymailing_fields SET `required` = 0 WHERE `namekey` = 'name'");
            }
        }
    }

    function updateQuery($query){
        try{
            $res = acymailing_query($query);
        }catch(Exception $e){
            $res = null;
        }
        if($res === null) acymailing_enqueueMessage(isset($e) ? $e->getMessage() : substr(strip_tags(acymailing_getDBError()), 0, 200).'...', 'error');
    }
}
