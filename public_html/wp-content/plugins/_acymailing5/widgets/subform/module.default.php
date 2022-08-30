<?php
/**
 * @package	AcyMailing for WordPress
 * @version	5.10.12
 * @author	acyba.com
 * @copyright	(C) 2009-2020 ACYBA S.A.R.L. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

defined('ABSPATH') or die('Restricted access');
?><div class="acymailing_module_form">
	<?php if(!empty($instance['introtext'])) echo '<div class="acymailing_introtext">'.$instance['introtext'].'</div>';

	$listContent = '<table class="acymailing_lists">';
	foreach($visibleListsArray as $myListId){
		$check = '';
		if(!empty($identifiedUser->email) && !empty($allLists[$myListId]->status) && $allLists[$myListId]->status != '-1') $check = 'checked="checked"';

		$listContent .= '
		<tr>
			<td>
				<label for="acylist_'.$myListId.'_'.$formName.'">
				<input type="checkbox" class="acymailing_checkbox" name="subscription[]" id="acylist_'.$myListId.'_'.$formName.'" '.$check.' value="'.$myListId.'"/>';
					$listContent .= acymailing_tooltip($allLists[$myListId]->description,$allLists[$myListId]->name,'',$allLists[$myListId]->name, '');
					$listContent .= '
				</label>
			</td>
		</tr>';
	}
	$listContent .= '</table>';

	if(!empty($visibleListsArray)) echo $listContent; ?>
	<table class="acymailing_form">
		<tr>
		<?php
			foreach($instance['fields'] as $oneField){
				if($oneField == 'name' && empty($extraFields[$oneField])){
					if($displayOutside) echo '<td><label for="user_name_'.$formName.'" class="acy_requiredField">'.$nameCaption.'</label></td>';
					echo '<td class="acyfield_'.$oneField.(!$displayOutside ? ' acy_requiredField' : '').'">
							<input id="user_name_'.$formName.'" '.(!empty($identifiedUser->userid) ? 'readonly="readonly" ' : '');
	
					if(!$displayOutside) echo ' onfocus="if(this.value == \''.$nameCaption.'\') this.value = \'\';" onblur="if(this.value==\'\') this.value=\''.$nameCaption.'\';"';
	
					$value = '';
					if(!empty($identifiedUser->userid)) $value = $identifiedUser->name;
					elseif(!$displayOutside) $value = $nameCaption;
	
					echo ' class="inputbox" type="text" name="user[name]" style="width:80%" value="'.$value.'" title="'.$nameCaption.'"/></td>';
				}elseif($oneField == 'email' && empty($extraFields[$oneField])){
					if($displayOutside) echo '<td for="user_email_'.$formName.'" class="acy_requiredField">'.$emailCaption.'</td>';
					echo '<td class="acyfield_'.$oneField.(!$displayOutside ? ' acy_requiredField' : '').'">
							<input id="user_email_'.$formName.'" '.(!empty($identifiedUser->userid) ? 'readonly="readonly" ' : '');
	
					if(!$displayOutside) echo ' onfocus="if(this.value == \''.$emailCaption.'\') this.value = \'\';" onblur="if(this.value==\'\') this.value=\''.$emailCaption.'\';"';
	
					$value = '';
					if(!empty($identifiedUser->userid)) $value = $identifiedUser->email;
					elseif(!$displayOutside) $value = $emailCaption;
	
					echo ' class="inputbox" type="text" name="user[email]" style="width:80%" value="'.$value.'" title="'.$emailCaption.'" /></td>';
				}elseif($oneField == 'html' && empty($extraFields[$oneField])){
					echo '<td class="acyfield_'.$oneField.'" ';
					if($displayOutside && !$displayInline) echo 'colspan="2"';
					echo '>'.acymailing_translation('RECEIVE').acymailing_boolean("user[html]" ,'title="'.acymailing_translation('RECEIVE').'"',isset($identifiedUser->html) ? $identifiedUser->html : 1,acymailing_translation('HTML'),acymailing_translation('JOOMEXT_TEXT'),'user_html_'.$formName).'</td>';
				}elseif(!empty($extraFields[$oneField])){
					if($extraFields[$oneField]->type == 'category'){
						echo '<td '. ($displayOutside && !$displayInline ? 'colspan="2"' : '').' class="category_warning">Please use Tableless mode to display categories.</td>';
					}else{
						if($displayOutside){
							if(!empty($extraFields[$oneField]->required)) $requireClass = 'class="acy_requiredField"';
							else $requireClass = "";
							echo '<td><label '.((strpos($extraFields[$oneField]->type,'text') !== false) ? 'for="user_'.$oneField.'_'.$formName.'"' : '' ).' '.$requireClass.'>'.$fieldsClass->trans($extraFields[$oneField]->fieldname).'</label></td>';
						}
						$sizestyle = '';
						if(!empty($extraFields[$oneField]->options['size'])){
							$sizestyle = 'style="width:'.(is_numeric($extraFields[$oneField]->options['size']) ? ($extraFields[$oneField]->options['size'].'px') : $extraFields[$oneField]->options['size']).'"';
						}
						if(!empty($extraFields[$oneField]->required) && !$displayOutside) $requireClass = ' acy_requiredField';
						else $requireClass = "";
	
						echo '<td class="acyfield_'.$oneField.$requireClass.'">';
						if(!empty($identifiedUser->userid) && in_array($oneField, array('name','email'))){
							echo '<input id="user_'.$oneField.'_'.$formName.'" readonly="readonly" class="inputbox" type="text" name="user['.$oneField.']" '.$sizestyle.' value="'.@$identifiedUser->$oneField.'" title="'.$oneField.'"/>';
						}else{
							echo $fieldsClass->display($extraFields[$oneField], @$identifiedUser->$oneField,'user['.$oneField.']',!$displayOutside);
						}
	
						echo '</td>';
					}
				}else{
					continue;
				}
				if(!$displayInline) echo '</tr><tr>';
			}
	
			if(empty($identifiedUser->userid) && $config->get('captcha_enabled')) {
				echo '<td class="captchakeymodule">';
				$captchaClass = acymailing_get('class.acycaptcha');
				if($displayOutside){
					$captchaClass->display($formName, true).'</td><td class="captchafieldmodule">';
				}else{
					$captchaClass->display($formName, true);
				}
				if(!$displayInline) echo '</tr><tr>';
			}
			?>
	
			<td <?php if($displayOutside && !$displayInline) echo 'colspan="2"'; ?> class="acysubbuttons">
				<input type="submit" class="button subbutton" value="<?php echo acymailing_translation($instance['subtext']); ?>" name="Submit" onclick="try{ return submitacymailingform('optin','<?php echo $formName;?>'); }catch(err){alert('The form could not be submitted '+err);return false;}"/>
				<?php if($instance['unsub'] == '1' && !empty($countUnsub)){ ?>
					<span style="display: none;"></span>
					<input type="button" class="button unsubbutton" value="<?php echo acymailing_translation($instance['unsubtext']); ?>" name="Submit" onclick="try{ return submitacymailingform('optout','<?php echo $formName;?>'); }catch(err){alert('The form could not be submitted '+err);return false;}"/>
				<?php } ?>
			</td>
		</tr>
	</table>
	<?php
	if(!empty($fieldsClass->excludeValue)){
		$js = "\n"."acymailingModule['excludeValues".$formName."'] = [];";
		foreach($fieldsClass->excludeValue as $namekey => $value){
			$js .= "\n"."acymailingModule['excludeValues".$formName."']['".$namekey."'] = '".$value."';";
		}
		$js .= "\n";
		echo "<script type=\"text/javascript\">
				<!--
				$js
				//-->
				</script>";
	}
	?>
</div>
