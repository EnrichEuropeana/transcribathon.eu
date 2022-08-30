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

class acymailing_subform_widget extends WP_Widget
{
    function __construct()
    {
        parent::__construct(
            'acymailing_subform_widget',
            'AcyMailing subscription form',
            array('description' => 'Form used by your users to subscribe to the contact lists in order to receive your newsletters')
        );
    }

    public function form($instance)
    {
        require_once(rtrim(dirname(dirname(__DIR__)), DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.'back'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'helper.php');

        $params = array(
            'title' => 'Receive our newsletters',
            'displists' => 'None',
            'hiddenlists' => 'All',
            'fields' => 'name,email',
            'textmode' => '1',
            'subtext' => 'SUBSCRIBECAPTION',
            'unsub' => '0',
            'unsubtext' => 'UNSUBSCRIBECAPTION',
            'introtext' => '',
            'posttext' => '',
            'userinfo' => '1',
            'mode' => 'tableless',
            'source' => 'widget __i__',
            'redirect' => ''
        );

        foreach($params as $oneParam => &$value){
            if(isset($instance[$oneParam])) $value = $instance[$oneParam];
            $value = esc_attr($value);
        }

        echo '
		<style type="text/css">
			.acyWPconfig{
				font-weight: bold;
				display: block;
			}
			
			.acymailingradiogroup input[type="radio"]{
				margin-top: 0;
			}
			
			.acymailingradiogroup label{
				margin-right: 6px;
			}
			
			.acyblock{
                margin: 10px 0;
			}
			
			.acyblock .widget-top{
			    cursor: pointer !important;
			}
			
			.acyblock.widget:not(.open) .toggle-indicator:before{
			    content: "\f140" !important;
			}
		</style>';

        $control = $this->number;

        echo '<div class="acyblock widget" id="mainopt_acywidget">
                <div class="widget-top">
                    <div class="widget-title-action">
                        <button type="button" class="widget-action hide-if-no-js" aria-expanded="false">
                            <span class="toggle-indicator" aria-hidden="true"></span>
                        </button>
                    </div>
                    <div class="widget-title"><h3>Main options</h3></div>
                </div>
                <div class="widget-inside">';

        echo '<p><label class="acyWPconfig" for="'.$this->get_field_id('title').'">'.acymailing_translation('ACY_TITLE').'</label>
			<input type="text" class="widefat" id="'.$this->get_field_id('title').'" name="'.$this->get_field_name('title').'" value="'.$params['title'].'" /></p>';

        echo '<p><label class="acyWPconfig">'.acymailing_translation('VISIBLE_LISTS').'</label>';
        $link = acymailing_completeLink('chooselist&task='.$this->get_field_id('displists').'&values='.$params['displists'].'&control='.$control, true);
        echo '<input type="text" id="'.$control.$this->get_field_id('displists').'" name="'.$this->get_field_name('displists').'" style="width:100px" value="'.$params['displists'].'">';
        echo acymailing_popup($link, '<button class="button" onclick="return false">'.acymailing_translation('SELECT').'</button>', '', 650, 375, 'link'.$control.$this->get_field_id('displists')).'</p>';

        echo '<p><label class="acyWPconfig">'.acymailing_translation('AUTO_SUBSCRIBE_TO').'</label>';
        $link = acymailing_completeLink('chooselist&task='.$this->get_field_id('hiddenlists').'&values='.$params['hiddenlists'].'&control='.$control, true);
        echo '<input type="text" id="'.$control.$this->get_field_id('hiddenlists').'" name="'.$this->get_field_name('hiddenlists').'" style="width:100px" value="'.$params['hiddenlists'].'">';
        echo acymailing_popup($link, '<button class="button" onclick="return false">'.acymailing_translation('SELECT').'</button>', '', 650, 375, 'link'.$control.$this->get_field_id('hiddenlists')).'</p>';

        echo '<p><label class="acyWPconfig">'.acymailing_translation('DISP_FIELDS').'</label>';
        $link = acymailing_completeLink('chooselist&task=customfields&values='.$params['fields'].'&control='.$control, true);
        echo '<input type="text" id="'.$control.'customfields" name="'.$this->get_field_name('fields').'" style="width:100px" value="'.$params['fields'].'">';
        echo acymailing_popup($link, '<button class="button" onclick="return false">'.acymailing_translation('SELECT').'</button>', '', 650, 375, 'link'.$control.'customfields').'</p>';

        echo '<p><label class="acyWPconfig">'.acymailing_translation('DISP_TEXT_MODE').'</label>';
        echo acymailing_boolean($this->get_field_name('textmode'), '', $params['textmode'], 'ACY_TEXT_INSIDE', 'ACY_TEXT_OUTSIDE', $this->get_field_id('textmode')).'</p>';

        echo '<p><label class="acyWPconfig" for="'.$this->get_field_id('subtext').'">'.acymailing_translation('CAPT_SUB').'</label>
			<input type="text" class="widefat" id="'.$this->get_field_id('subtext').'" name="'.$this->get_field_name('subtext').'" value="'.$params['subtext'].'" /></p>';

        $options = array();
        $options[] = acymailing_selectOption('inline', acymailing_translation('ACY_MODE_HORIZONTAL'));
        $options[] = acymailing_selectOption('vertical', acymailing_translation('ACY_MODE_VERTICAL'));
        $options[] = acymailing_selectOption('tableless', acymailing_translation('ACY_MODE_TABLELESS'));
        echo '<p><label class="acyWPconfig">'.acymailing_translation('DISPLAY_MODE').'</label>';
        echo acymailing_radio($options, $this->get_field_name('mode'), '', 'value', 'text', $params['mode'], $this->get_field_id('mode')).'</p>';

        echo '</div>
            </div>
            <div class="acyblock widget" id="advopt_acywidget">
                <div class="widget-top">
                    <div class="widget-title-action">
                        <button type="button" class="widget-action hide-if-no-js" aria-expanded="false">
                            <span class="toggle-indicator" aria-hidden="true"></span>
                        </button>
                    </div>
                    <div class="widget-title"><h3>Advanced options</h3></div>
                </div>
                <div class="widget-inside">';

        echo '<p><label class="acyWPconfig">'.acymailing_translation('DISP_UNSUB_BUTTON').'</label>';
        $onclick = "var disp = 'none'; if(this.value == 1){disp = 'block';} document.getElementById('".$this->get_field_id('unsubtextrow')."').style.display = disp;";
        echo acymailing_boolean($this->get_field_name('unsub'), 'onclick="'.$onclick.'"', $params['unsub'], 'JOOMEXT_YES', 'JOOMEXT_NO', $this->get_field_id('unsub')).'</p>';

        echo '<p id="'.$this->get_field_id('unsubtextrow').'" '.($params['unsub'] == '0' ? 'style="display:none;"' : '').'><label class="acyWPconfig" for="'.$this->get_field_id('unsubtext').'">'.acymailing_translation('CAPT_UNSUB').'</label>
			<input type="text" class="widefat" id="'.$this->get_field_id('unsubtext').'" name="'.$this->get_field_name('unsubtext').'" value="'.$params['unsubtext'].'" /></p>';

        echo '<p><label class="acyWPconfig" for="'.$this->get_field_id('introtext').'">'.acymailing_translation('INTRO_TEXT').'</label>
			<textarea class="widefat" id="'.$this->get_field_id('introtext').'" name="'.$this->get_field_name('introtext').'" >'.$params['introtext'].'</textarea></p>';

        echo '<p><label class="acyWPconfig" for="'.$this->get_field_id('posttext').'">'.acymailing_translation('POST_TEXT').'</label>
			<textarea class="widefat" id="'.$this->get_field_id('posttext').'" name="'.$this->get_field_name('posttext').'" >'.$params['posttext'].'</textarea></p>';

        echo '<p><label class="acyWPconfig">'.acymailing_translation('MODULE_AUTOID').'</label>';
        echo acymailing_boolean($this->get_field_name('userinfo'), '', $params['userinfo'], 'JOOMEXT_YES', 'JOOMEXT_NO', $this->get_field_id('userinfo')).'</p>';

        echo '<p><label class="acyWPconfig" for="'.$this->get_field_id('source').'">'.acymailing_tooltip('You can filter your subscribers in the mass actions based on the source, it indicates from where your subscribers came from (a subscription form / an imported file / on account creation).', acymailing_translation('ACY_SOURCE'), '', acymailing_translation('ACY_SOURCE')).'</label>
			<input type="text" class="widefat" id="'.$this->get_field_id('source').'" name="'.$this->get_field_name('source').'" value="'.$params['source'].'" /></p>';

        echo '<p><label class="acyWPconfig" for="'.$this->get_field_id('redirect').'">'.acymailing_tooltip('The user will be redirected to this URL after subscription. If no URL is specified, the widget is refreshed using ajax.', acymailing_translation('REDIRECT_LINK'), '', acymailing_translation('REDIRECT_LINK')).'</label>
			<input type="text" class="widefat" id="'.$this->get_field_id('redirect').'" name="'.$this->get_field_name('redirect').'" value="'.$params['redirect'].'" /></p>';

        echo '</div></div>';
    }

    public function widget($args, $instance)
    {
        require_once(rtrim(dirname(dirname(__DIR__)), DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.'back'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'helper.php');
        $config = acymailing_config();

        echo $args['before_widget'];

        $params = new acyParameter($instance);
        acymailing_initModule($params);

        $title = apply_filters('widget_title', $instance['title']);
        if(!empty($title)) echo $args['before_title'].$title.$args['after_title'];

        $displayOutside = $instance['textmode'] == '0';
        $instance['fields'] = explode(',', $instance['fields']);
        $formName = acymailing_getModuleFormName();

        $identifiedUser = null;
        $currentUserEmail = acymailing_currentUserEmail();
        if ($instance['userinfo'] == '1' && !empty($currentUserEmail)) {
            $userClass = acymailing_get('class.subscriber');
            $identifiedUser = $userClass->get($currentUserEmail);
        }

        $visibleLists = trim($instance['displists']);
        $hiddenLists = trim($instance['hiddenlists']);
        $visibleListsArray = array();
        $hiddenListsArray = array();
        $listsClass = acymailing_get('class.list');
        if (empty($identifiedUser->subid)) {
            $allLists = $listsClass->getLists('listid');
        } else {
            $allLists = $userClass->getSubscription($identifiedUser->subid, 'listid');
        }

        $allLists = $listsClass->onlyCurrentLanguage($allLists);
        $allLists = $listsClass->onlyAllowedLists($allLists);

        if (strpos($visibleLists, ',') || is_numeric($visibleLists)) {
            $allvisiblelists = explode(',', $visibleLists);
            foreach($allLists as $oneList){
                if($oneList->published && in_array($oneList->listid, $allvisiblelists)) $visibleListsArray[] = $oneList->listid;
            }
        } elseif (strtolower($visibleLists) == 'all') {
            foreach ($allLists as $oneList) {
                if ($oneList->published) {
                    $visibleListsArray[] = $oneList->listid;
                }
            }
        }

        if (strpos($hiddenLists, ',') || is_numeric($hiddenLists)) {
            $allhiddenlists = explode(',', $hiddenLists);
            foreach($allLists as $oneList){
                if($oneList->published && in_array($oneList->listid, $allhiddenlists)) $hiddenListsArray[] = $oneList->listid;
            }
        } elseif (strtolower($hiddenLists) == 'all') {
            $visibleListsArray = array();
            foreach ($allLists as $oneList) {
                if (!empty($oneList->published)) {
                    $hiddenListsArray[] = $oneList->listid;
                }
            }
        }

        if (!empty($visibleListsArray) && !empty($hiddenListsArray)) {
            $visibleListsArray = array_diff($visibleListsArray, $hiddenListsArray);
        }

        if (!empty($identifiedUser->subid)) {
            $countSub = 0;
            $countUnsub = 0;
            foreach ($visibleListsArray as $idOneList) {
                if ($allLists[$idOneList]->status == -1) {
                    $countSub++;
                }elseif($allLists[$idOneList]->status == 1) $countUnsub++;
            }
            foreach ($hiddenListsArray as $idOneList) {
                if ($allLists[$idOneList]->status == -1) {
                    $countSub++;
                }elseif($allLists[$idOneList]->status == 1) $countUnsub++;
            }
        }

        $hiddenLists = implode(',', $hiddenListsArray);

        $nameCaption = acymailing_translation('NAMECAPTION');
        $emailCaption = acymailing_translation('EMAILCAPTION');

        $fieldsClass = acymailing_get('class.fields');
        $fieldsClass->origin = 'module';
        $fieldsClass->prefix = 'user_';
        $fieldsClass->suffix = '_'.$formName;
        $fieldsClass->currentUserEmail = empty($currentUserEmail) ? '' : $currentUserEmail;
        $extraFields = $fieldsClass->getFields('module', $identifiedUser);


        if (acymailing_level(3)) {
            $newOrdering = array();
            $requiredFields = array();
            $validMessages = array();
            $checkFields = array();
            $checkFieldsType = array();
            $checkFieldsRegexp = array();
            $validCheckMsg = array();

            foreach ($extraFields as $fieldnamekey => $oneField) {
                if (!isset($extraFields[$fieldnamekey]->options)) {
                    $extraFields[$fieldnamekey]->options = array();
                }

                if (in_array($fieldnamekey, $instance['fields'])) {
                    $newOrdering[] = $fieldnamekey;
                }
                if (in_array($oneField->type, array('text', 'date')) && $params->get('fieldsize', '80%') && (empty($extraFields[$fieldnamekey]->options['size']) || $params->get('fieldsize', '80%') < $extraFields[$fieldnamekey]->options['size'])) {
                    $extraFields[$fieldnamekey]->options['size'] = $params->get('fieldsize', '80%');
                }
                if (strlen($params->get($fieldnamekey.'text')) > 1) {
                    $extraFields[$fieldnamekey]->fieldname = $params->get($fieldnamekey.'text');
                }
                if ($oneField->namekey == 'email' && (empty($oneField->options['checkcontent']) || 'regexp' !== $oneField->options['checkcontent'])) continue;

                if ($oneField->type == 'text' && !empty($oneField->options['checkcontent']) && in_array($fieldnamekey, explode(',', $params->get('fields')))) {
                    $checkFields[] = $fieldnamekey;
                    $checkFieldsType[] = $oneField->options['checkcontent'];
                    if ($oneField->options['checkcontent'] == 'regexp') {
                        $checkFieldsRegexp[] = $oneField->options['regexp'];
                    }
                    if (!empty($oneField->options['errormessagecheckcontent'])) {
                        $validCheckMsg[] = addslashes($fieldsClass->trans($oneField->options['errormessagecheckcontent']));
                    } elseif (!empty($oneField->options['errormessage'])) {
                        $validCheckMsg[] = addslashes($fieldsClass->trans($oneField->options['errormessage']));
                    } else {
                        $validCheckMsg[] = addslashes(acymailing_translation_sprintf('FIELD_CONTENT_VALID', $fieldsClass->trans($oneField->fieldname)));
                    }
                }

                if ($oneField->namekey == 'email') continue;
                if (!empty($oneField->required)) {
                    $requiredFields[] = $fieldnamekey;
                    if (!empty($oneField->options['errormessage'])) {
                        $validMessages[] = addslashes($fieldsClass->trans($oneField->options['errormessage']));
                    } else {
                        $validMessages[] = addslashes(acymailing_translation_sprintf('FIELD_VALID', $fieldsClass->trans($oneField->fieldname)));
                    }
                }
            }
            $instance['fields'] = $newOrdering;

            $js = "
                acymailingModule['level'] = 'enterprise';
                ";

            if (!empty($requiredFields)) {
                $js .= "acymailingModule['reqFields".$formName."'] = Array('".implode("','", $requiredFields)."');
                    acymailingModule['validFields".$formName."'] = Array('".implode("','", $validMessages)."');
                    ";
            }
            if (!empty($checkFields)) {
                $js .= "acymailingModule['checkFields".$formName."'] = Array('".implode("','", $checkFields)."');
                    acymailingModule['checkFieldsType".$formName."'] = Array('".implode("','", $checkFieldsType)."');
                    acymailingModule['validCheckFields".$formName."'] = Array('".implode("','", $validCheckMsg)."');
                    ";
                if (!empty($checkFieldsRegexp)) {
                    $js .= "acymailingModule['checkFieldsRegexp".$formName."'] = Array('".implode("','", $checkFieldsRegexp)."');
                    ";
                }
            }

            echo "<script type=\"text/javascript\">
                    <!--
                        $js
                    //-->
					</script>";

            $arrayForCondDisplay = array();
            foreach ($instance['fields'] as $oneField) {
                $arrayForCondDisplay[$oneField] = $extraFields[$oneField];
            }
            $js = $fieldsClass->prepareConditionalDisplay($arrayForCondDisplay, 'user', 'mod_'.$instance['mode'], $formName);
            if (!empty($js)) {
                acymailing_addScript(true, $js);
            }
        }

?>
<div class="acymailing_module" id="acymailing_module_<?php echo $formName; ?>">
    <div class="acymailing_fulldiv" id="acymailing_fulldiv_<?php echo $formName; ?>" >
        <form id="<?php echo $formName; ?>" action="<?php echo htmlspecialchars_decode(acymailing_rootURI().acymailing_addPageParam('sub', true, true)); ?>" onsubmit="return submitacymailingform('optin','<?php echo $formName;?>')" method="post" name="<?php echo $formName ?>" <?php if(!empty($fieldsClass->formoption)) echo $fieldsClass->formoption; ?> >

            <?php
            if($instance['mode'] == 'tableless'){
                include(__DIR__.DS.'module.tableless.php');
            }else{
                $displayInline = $instance['mode'] != 'vertical';
                include(__DIR__.DS.'module.default.php');
            }

            ?>
            <input type="hidden" name="page" value="front"/>
            <input type="hidden" name="ctrl" value="sub"/>
            <input type="hidden" name="task" value="notask"/>
            <input type="hidden" name="option" value="<?php echo ACYMAILING_COMPONENT ?>"/>

            <?php
            if(empty($instance['redirect'])){
                echo '<input type="hidden" name="ajax" value="1"/>';
            }else{
                echo '<input type="hidden" name="ajax" value="0"/>';
                echo '<input type="hidden" name="redirect" value="'.esc_attr($instance['redirect']).'"/>';
            }
            ?>
            <input type="hidden" name="acy_source" value="<?php echo esc_attr($instance['source']); ?>" />
            <?php if(!empty($identifiedUser->userid)){ ?><input type="hidden" name="visiblelists" value="<?php echo $visibleLists; ?>"/><?php } ?>
            <input type="hidden" name="hiddenlists" value="<?php echo $hiddenLists; ?>"/>
            <input type="hidden" name="acyformname" value="<?php echo $formName; ?>" />

            <?php if(!empty($instance['posttext'])) echo '<div class="acymailing_finaltext">'.$instance['posttext'].'</div>'; ?>
        </form>
    </div>
</div>
<?php
        echo $args['after_widget'];
    }
}

