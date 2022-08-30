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

class plgAcymailingOnline{
	function __construct(&$subject, $config){
		if(!isset($this->params)){
			$plugin = acymailing_getPlugin('acymailing', 'online');
			$this->params = new acyParameter($plugin->params);
		}
	}

	function acymailing_getPluginType(){
		$onePlugin = new stdClass();
		$onePlugin->name = acymailing_translation('WEBSITE_LINKS');
		$onePlugin->function = 'acymailingtagonline_show';
		$onePlugin->help = 'plugin-online';

		return $onePlugin;
	}

	function acymailingtagonline_show(){

		$others = array();
		$others['readonline'] = array('default' => acymailing_translation('VIEW_ONLINE', true), 'desc' => acymailing_translation('VIEW_ONLINE_LINK'));

		?>
		<script language="javascript" type="text/javascript">
			<!--
			var selectedTag = '';
			function changeTag(tagName){
				selectedTag = tagName;
				defaultText = [];
				<?php
					$k = 0;
					foreach($others as $tagname => $tag){
						echo "document.getElementById('tr_$tagname').className = 'row$k';";
						echo "defaultText['$tagname'] = '".$tag['default']."';";
						$k = 1-$k;
					}
				?>
				document.getElementById('tr_' + tagName).className = 'selectedrow';
				document.adminForm.tagtext.value = defaultText[tagName];
				setOnlineTag();
			}

			function setOnlineTag(){
				if(!selectedTag) changeTag('readonline');
				otherinfo = '';
				setTag('<a href=' + '"{' + selectedTag + otherinfo + '}{/' + selectedTag + '}" target="_blank" style="text-decoration:none;"><span class="acymailing_online">' + document.adminForm.tagtext.value + '</span></a>');
			}
			//-->
		</script>
		<?php
		echo acymailing_translation('FIELD_TEXT').' : <input type="text" name="tagtext" size="100px" onchange="setOnlineTag();" /><br /><br />';
		echo '<div class="onelineblockoptions">
				<table class="acymailing_table" cellpadding="1">';
		$k = 0;
		foreach($others as $tagname => $tag){
			echo '<tr style="cursor:pointer" class="row'.$k.'" onclick="changeTag(\''.$tagname.'\');" id="tr_'.$tagname.'" ><td class="acytdcheckbox" ></td><td>'.$tag['desc'].'</td></tr>';
			$k = 1 - $k;
		}
		echo '</table></div>';
	}

	function acymailing_replacetags(&$email, $send = true){
		$match = '#(?:{|%7B)(readonline)([^}]*)(?:}|%7D)(.*)(?:{|%7B)/(readonline)(?:}|%7D)#Uis';
		$variables = array('body', 'altbody');
		$found = false;
		$results = array();
		foreach($variables as $var){
			if(empty($email->$var)) continue;
			$found = preg_match_all($match, $email->$var, $results[$var]) || $found;
			if(empty($results[$var][0])) unset($results[$var]);
		}

		if(!$found) return;

		$tags = array();

		foreach($results as $var => $allresults){
			foreach($allresults[0] as $i => $oneTag){
				if(isset($tags[$oneTag])) continue;
				$arguments = explode('|', strip_tags(str_replace('%7C', '|', $allresults[2][$i])));
				$tag = new stdClass();
				$tag->type = $allresults[1][$i];
				for($j = 0, $a = count($arguments); $j < $a; $j++){
					$args = explode(':', $arguments[$j]);
					$arg0 = trim($args[0]);
					if(empty($arg0)) continue;
					if(isset($args[1])){
						$tag->$arg0 = $args[1];
					}else{
						$tag->$arg0 = true;
					}
				}

				$addkey = empty($email->key) ? '' : '&key='.$email->key;
				$lang = empty($email->language) ? '' : '&lang='.$email->language;

				$link = '';
				if($tag->type == 'readonline'){
					$link = acymailing_frontendLink('archive&task=view&mailid='.$email->mailid.'&subid={subtag:subid}-{subtag:key}'.$addkey.$lang.'&'.acymailing_noTemplate());
				}

				if(empty($allresults[3][$i])){
					$tags[$oneTag] = $link;
				}else{
					$tags[$oneTag] = '<a style="text-decoration:none;" href="'.$link.'"><span class="acymailing_online">'.$allresults[3][$i].'</span></a>';
				}
			}
		}

		$email->body = str_replace(array_keys($tags), $tags, $email->body);
		if(!empty($email->altbody)) $email->altbody = str_replace(array_keys($tags), $tags, $email->altbody);
	}
}//endclass
