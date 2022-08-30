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

class plgAcymailingPosts{

	function __construct(&$subject, $config){
		if(!isset($this->params)){
			$plugin = acymailing_getPlugin('acymailing', 'posts');
			$this->params = new acyParameter($plugin->params);
		}
		$this->name = 'wpposts';
		$this->acypluginsHelper = acymailing_get('helper.acyplugins');
	}

	function acymailing_getPluginType(){

		if($this->params->get('frontendaccess') == 'none' && !acymailing_isAdmin()) return;
		$onePlugin = new stdClass();
		$onePlugin->name = acymailing_translation('ACY_POSTS');
		$onePlugin->function = 'acymailingposts_show';
		$onePlugin->help = 'plugin-posts';

		return $onePlugin;
	}

	function acymailingposts_show(){

		$pageInfo = new stdClass();
		$pageInfo->filter = new stdClass();
		$pageInfo->filter->order = new stdClass();
		$pageInfo->limit = new stdClass();
		$pageInfo->elements = new stdClass();

		$paramBase = ACYMAILING_COMPONENT.'.'.$this->name;

		$pageInfo->filter->order->value = acymailing_getUserVar($paramBase.".filter_order", 'filter_order', 'post.id', 'cmd');
		$pageInfo->filter->order->dir = acymailing_getUserVar($paramBase.".filter_order_Dir", 'filter_order_Dir', 'desc', 'word');
		if(strtolower($pageInfo->filter->order->dir) !== 'desc') $pageInfo->filter->order->dir = 'asc';
		$pageInfo->search = acymailing_getUserVar($paramBase.".search", 'search', '', 'string');
		$pageInfo->search = strtolower(trim($pageInfo->search));
		$pageInfo->filter_cat = acymailing_getUserVar($paramBase.".filter_cat", 'filter_cat', '0', 'int');
		$pageInfo->limit->value = acymailing_getUserVar($paramBase.'.list_limit', 'limit', acymailing_getCMSConfig('list_limit'), 'int');
		$pageInfo->limit->start = acymailing_getUserVar($paramBase.'.limitstart', 'limitstart', 0, 'int');
		$pageInfo->contentfilter = acymailing_getUserVar($paramBase.".contentfilter", 'contentfilter', 'created', 'string');
		$pageInfo->contentorder = acymailing_getUserVar($paramBase.".contentorder", 'contentorder', 'id', 'string');
		$pageInfo->contentorderdir = acymailing_getUserVar($paramBase.".contentorderdir", 'contentorderdir', 'DESC', 'string');
		$pageInfo->cols = acymailing_getUserVar($paramBase.".cols", 'cols', '1', 'string');
		$pageInfo->pictheight = acymailing_getUserVar($paramBase.".pictheight", 'pictheight', '150', 'string');
		$pageInfo->pictwidth = acymailing_getUserVar($paramBase.".pictwidth", 'pictwidth', '150', 'string');
		$pageInfo->pict = acymailing_getUserVar($paramBase.".pict", 'pict', '1', 'string');
		$pageInfo->pictauto = acymailing_getUserVar($paramBase.".autopict", 'autopict', '1', 'string');
		$pageInfo->readmore = acymailing_getUserVar($paramBase.".readmore", 'readmore', '1', 'string');
		$pageInfo->readmoreauto = acymailing_getUserVar($paramBase.".readmoreauto", 'readmoreauto', '1', 'string');
		$pageInfo->wrap = acymailing_getUserVar($paramBase.".wrap", 'wrap', '0', 'string');
		$pageInfo->clickable = acymailing_getUserVar($paramBase.".clickable", 'clickable', '1', 'string');
		$pageInfo->clickableauto = acymailing_getUserVar($paramBase.".clickableauto", 'clickableauto', '1', 'string');

		$query = 'SELECT SQL_CALC_FOUND_ROWS post.ID AS id, post_title AS title FROM #__posts AS post ';

		$searchFields = array('post.id', 'post.post_title');

		if(!empty($pageInfo->search)){
			$searchVal = '\'%'.acymailing_getEscaped($pageInfo->search, true).'%\'';
			$filters[] = implode(" LIKE $searchVal OR ", $searchFields)." LIKE ".$searchVal;
		}

		if(!empty($pageInfo->filter_cat)){
			$query .= 'JOIN #__term_relationships AS tr ON post.id = tr.object_id 
					JOIN #__term_taxonomy AS tt ON tt.term_taxonomy_id = tr.term_taxonomy_id ';
			$filters[] = 'tt.term_id = '.intval($pageInfo->filter_cat);
		}

		$filters[] = 'post.post_type = "post"';
		$query .= ' WHERE ('.implode(') AND (', $filters).')';
		if(!empty($pageInfo->filter->order->value)) $query .= ' ORDER BY '.$pageInfo->filter->order->value.' '.$pageInfo->filter->order->dir;

		$rows = acymailing_loadObjectList($query, '', $pageInfo->limit->start, $pageInfo->limit->value);

		if(!empty($pageInfo->search)) $rows = acymailing_search($pageInfo->search, $rows);

		$pageInfo->elements->total = acymailing_loadResult('SELECT FOUND_ROWS()');
		$pageInfo->elements->page = count($rows);

		$pagination = new acyPagination($pageInfo->elements->total, $pageInfo->limit->start, $pageInfo->limit->value);

		$type = acymailing_getVar('string', 'type');

		?>
		<script language="javascript" type="text/javascript">
			<!--
			var selectedContents = [];
			function applyContent(contentid, rowClass){
				var tmp = selectedContents.indexOf(contentid);
				if(tmp != -1){
					window.document.getElementById('content' + contentid).className = rowClass;
					delete selectedContents[tmp];
				}else{
					window.document.getElementById('content' + contentid).className = 'selectedrow';
					selectedContents.push(contentid);
				}
				updateTag();
			}

			var selectedCat = [];
			function applyAuto(catid, rowClass){
				if(catid == 'all'){
					if(window.document.getElementById('catall').className == 'selectedrow'){
						window.document.getElementById('catall').className = rowClass;
					}else{
						window.document.getElementById('catall').className = 'selectedrow';
						var key;
						for(key in selectedCat){
							if(!selectedCat.hasOwnProperty(key) || isNaN(key)) continue;
							window.document.getElementById('cat' + key).className = rowClass;
							delete selectedCat[key];
						}
					}
				}else{
					window.document.getElementById('catall').className = 'row0';
					if(selectedCat[catid]){
						window.document.getElementById('cat' + catid).className = rowClass;
						delete selectedCat[catid];
					}else{
						window.document.getElementById('cat' + catid).className = 'selectedrow';
						selectedCat[catid] = 'selectedone';
					}
				}
				updateTagAuto();
			}

			function updateTag(){
				var tag = '';
				var otherinfo = '';
				var tmp = 0;

				<?php
				?>
				for(var i = 0; i < document.adminForm.cbdisplay.length; i++){
					if(!document.adminForm.cbdisplay[i].checked) continue;
					if(tmp == 0){
						tmp += 1;
						otherinfo += "| display:" + document.adminForm.cbdisplay[i].value;
					}else{
						otherinfo += ", " + document.adminForm.cbdisplay[i].value;
					}
				}

				if(document.adminForm.contentformat && document.adminForm.contentformat.value){
					otherinfo += '| format:' + document.adminForm.contentformat.value;
				}

				<?php
				?>
				for(i = 0; i < document.adminForm.clickable.length; i++){
					if(document.adminForm.clickable[i].checked && document.adminForm.clickable[i].value == '0'){
						otherinfo += '| nolink';
					}
				}

				<?php
				?>
				if(document.adminForm.wrap.value && document.adminForm.wrap.value != 0 && !isNaN(document.adminForm.wrap.value)){
					otherinfo += "| wrap:" + document.adminForm.wrap.value;
				}

				<?php
				?>
				for(i = 0; i < document.adminForm.readmore.length; i++){
					if(document.adminForm.readmore[i].checked && document.adminForm.readmore[i].value == '0'){
						otherinfo += '| noreadmore';
					}
				}

				<?php
				?>
				for(i = 0; i < document.adminForm.pict.length; i++){
					if(!document.adminForm.pict[i].checked) continue;
					if(document.adminForm.pict[i].value != '1') otherinfo += '| pict:' + document.adminForm.pict[i].value;

					if(document.adminForm.pict[i].value == 'resized'){
						document.getElementById('pictsize').style.display = '';
						if(document.adminForm.pictwidth.value) otherinfo += '| maxwidth:' + document.adminForm.pictwidth.value;
						if(document.adminForm.pictheight.value) otherinfo += '| maxheight:' + document.adminForm.pictheight.value;
					}else{
						document.getElementById('pictsize').style.display = 'none';
					}
				}

				for(var key in selectedContents){
					if(selectedContents.hasOwnProperty(key) && selectedContents[key] && !isNaN(key)){
						tag = tag + '{<?php echo $this->name; ?>:' + selectedContents[key] + otherinfo + '}<br />';
					}
				}
				setTag(tag);
			}


			function updateTagAuto(){
				var otherinfo = '';
				var tmp = 0;

				for(var i = 0; i < document.adminForm.cbdisplayauto.length; i++){
					if(!document.adminForm.cbdisplayauto[i].checked) continue;
					if(tmp == 0){
						tmp += 1;
						otherinfo += "| display:" + document.adminForm.cbdisplayauto[i].value;
					}else{
						otherinfo += ", " + document.adminForm.cbdisplayauto[i].value;
					}
				}

				if(document.adminForm.contentformatauto && document.adminForm.contentformatauto.value){
					otherinfo += '| format:' + document.adminForm.contentformatauto.value;
				}
				if(document.getElementById('contentformatautoinvert').value == 1) otherinfo += '| invert';

				for(i = 0; i < document.adminForm.clickableauto.length; i++){
					if(document.adminForm.clickableauto[i].checked && document.adminForm.clickableauto[i].value == '0'){
						otherinfo += '| nolink';
					}
				}

				if(document.adminForm.wrapauto.value && document.adminForm.wrapauto.value != 0 && !isNaN(document.adminForm.wrapauto.value)){
					otherinfo += "| wrap:" + document.adminForm.wrapauto.value;
				}

				for(i = 0; i < document.adminForm.readmoreauto.length; i++){
					if(document.adminForm.readmoreauto[i].checked && document.adminForm.readmoreauto[i].value == '0'){
						otherinfo += '| noreadmore';
					}
				}

				for(i = 0; i < document.adminForm.autopict.length; i++){
					if(!document.adminForm.autopict[i].checked) continue;
					if(document.adminForm.autopict[i].value != '1') otherinfo += '| pict:' + document.adminForm.autopict[i].value;

					if(document.adminForm.autopict[i].value == 'resized'){
						document.getElementById('pictsizeauto').style.display = '';
						if(document.adminForm.pictwidthauto.value) otherinfo += '| maxwidth:' + document.adminForm.pictwidthauto.value;
						if(document.adminForm.pictheightauto.value) otherinfo += '| maxheight:' + document.adminForm.pictheightauto.value;
					}else{
						document.getElementById('pictsizeauto').style.display = 'none';
					}
				}

				<?php
				?>
				if(document.adminForm.cols.value){
					otherinfo += "| cols:" + document.adminForm.cols.value;
				}

				<?php
				?>
				if(document.adminForm.max_article.value){
					otherinfo += "| max:" + document.adminForm.max_article.value;
				}

				<?php
				?>
				if(document.adminForm.contentorder.value){
					otherinfo += "| order:" + document.adminForm.contentorder.value + "," + document.adminForm.contentorderdir.value;
				}

				<?php if($type == 'autonews'){
				?>
				if(document.adminForm.min_article.value){
					otherinfo += "| min:" + document.adminForm.min_article.value;
				}

				<?php
				?>
				if(document.adminForm.contentfilter && document.adminForm.contentfilter.value){
					otherinfo += "| filter:" + document.adminForm.contentfilter.value;
				}
				<?php }
				?>
				var tag = '{auto<?php echo $this->name; ?>:';
				for(var icat in selectedCat){
					if(selectedCat.hasOwnProperty(icat) && selectedCat[icat] == 'selectedone'){
						tag += icat + '-';
					}
				}
				tag += otherinfo + '}<br />';

				setTag(tag);
			}
			//-->
		</script>
		<?php
		$choice = array();
		$choice[] = acymailing_selectOption("1", acymailing_translation('JOOMEXT_YES'));
		$choice[] = acymailing_selectOption("0", acymailing_translation('JOOMEXT_NO'));

		$valImages = array();
		$valImages[] = acymailing_selectOption("1", acymailing_translation('JOOMEXT_YES'));
		$valImages[] = acymailing_selectOption("resized", acymailing_translation('RESIZED'));
		$valImages[] = acymailing_selectOption("0", acymailing_translation('JOOMEXT_NO'));

		$column = array();
		for($i = 1; $i < 11; $i++){
			$column[] = acymailing_selectOption("$i", $i);
		}

		$fieldsDisplay = array();
		$fieldsDisplay[] = array('title' => 'title', 'label' => 'ACY_TITLE', 'checked' => 'yes');
		$fieldsDisplay[] = array('title' => 'image', 'label' => 'ACY_IMAGE', 'checked' => '');
		$fieldsDisplay[] = array('title' => 'description', 'label' => 'ACY_DESCRIPTION', 'checked' => 'yes');

		$tabs = acymailing_get('helper.acytabs');

		echo $tabs->startPane($this->name.'_tab');

		echo $tabs->startPanel(acymailing_translation('TAG_ELEMENTS'), $this->name.'_listings');
		?>
		<br style="font-size:1px"/>
		<div class="onelineblockoptions">
			<table width="100%" class="acymailing_table">
				<tr id="format" class="acyplugformat">
					<td valign="top">
						<?php echo acymailing_translation('FORMAT'); ?>
					</td>
					<td valign="top" colspan="2">
						<?php echo $this->acypluginsHelper->getFormatOption($this->name); ?>
					</td>
					<td nowrap="nowrap" valign="top"><?php echo acymailing_translation('DISPLAY_PICTURES'); ?></td>
					<td nowrap="nowrap"><?php echo acymailing_radio($valImages, 'pict', 'size="1" onclick="updateTag();"', 'value', 'text', $pageInfo->pict); ?>
						<span id="pictsize" <?php if($pageInfo->pict != 'resized') echo 'style="display:none;"'; ?> ><br/>
						<label for="pictwidth"><?php echo acymailing_translation('CAPTCHA_WIDTH') ?></label>
						<input id="pictwidth" name="pictwidth" type="text" onchange="updateTag();" value="<?php echo $pageInfo->pictwidth; ?>" style="width:30px;"/>
						x <label for="pictheight"><?php echo acymailing_translation('CAPTCHA_HEIGHT') ?></label>
						<input id="pictheight" name="pictheight" type="text" onchange="updateTag();" value="<?php echo $pageInfo->pictheight; ?>" style="width:30px;"/>
					</span>
					</td>
				</tr>
				<tr>
					<td nowrap="nowrap"><?php echo acymailing_translation('DISPLAY'); ?></td>
					<?php
					$i = 1;
					foreach($fieldsDisplay as $oneField){
						if($i == 5){
							echo '</tr><tr><td/>';
							$i = 1;
						}
						echo '<td nowrap="nowrap"><input type="checkbox" name="cbdisplay" value="'.$oneField['title'].'" id="'.$oneField['title'].'" '.(($oneField['checked'] == 'yes') ? 'checked' : '').' onclick="updateTag();"/><label style="margin-left:5px" for="'.$oneField['title'].'">'.trim(acymailing_translation($oneField['label']), ':').'</label></td>';
						$i++;
					}
					while($i != 5){
						echo '<td/>';
						$i++;
					}
					?>
				</tr>
				<tr>
					<td nowrap="nowrap"><?php echo acymailing_translation('CLICKABLE_TITLE'); ?></td>
					<td nowrap="nowrap" colspan="2"><?php echo acymailing_radio($choice, 'clickable', 'size="1" onclick="updateTag();"', 'value', 'text', $pageInfo->clickable); ?></td>

					<td nowrap="nowrap"><?php echo acymailing_translation('JOOMEXT_READ_MORE'); ?></td>
					<td nowrap="nowrap"><?php echo acymailing_radio($choice, 'readmore', 'size="1" onclick="updateTag();"', 'value', 'text', $pageInfo->readmore); ?></td>
				</tr>
				<tr>
					<td nowrap="nowrap" colspan="5"><?php echo acymailing_translation_sprintf('TRUNCATE_AFTER', '<input type="text" name="wrap" style="width:50px" value="0" onchange="updateTag();"/>'); ?></td>
				</tr>
			</table>
		</div>
		<div class="onelineblockoptions">
			<table class="acymailing_table_options">
				<tr>
					<td nowrap="nowrap" width="100%">
						<input placeholder="<?php echo acymailing_translation('ACY_SEARCH'); ?>" type="text" name="search" id="acymailingsearch" value="<?php echo $pageInfo->search; ?>" class="text_area" onchange="document.adminForm.submit();"/>
						<button class="acymailing_button" onclick="this.form.submit();"><?php echo acymailing_translation('JOOMEXT_GO'); ?></button>
						<button class="acymailing_button" onclick="document.getElementById('acymailingsearch').value='';this.form.submit();"><?php echo acymailing_translation('JOOMEXT_RESET'); ?></button>
					</td>
					<td nowrap="nowrap">
						<?php echo $this->_categories($pageInfo->filter_cat); ?>
					</td>
				</tr>
			</table>
			<table class="acymailing_table" cellpadding="1" width="100%">
				<thead>
				<tr>
					<th></th>
					<th class="title">
						<?php echo acymailing_gridSort(acymailing_translation('FIELD_TITLE'), 'a.title', $pageInfo->filter->order->dir, $pageInfo->filter->order->value); ?>
					</th>
					<th class="title">
						<?php echo acymailing_gridSort(acymailing_translation('TAG_CATEGORIES'), 'b.title', $pageInfo->filter->order->dir, $pageInfo->filter->order->value); ?>
					</th>
					<th class="title titleid">
						<?php echo acymailing_gridSort(acymailing_translation('ACY_ID'), 'a.id', $pageInfo->filter->order->dir, $pageInfo->filter->order->value); ?>
					</th>
				</tr>
				</thead>
				<tfoot>
				<tr>
					<td colspan="4">
						<?php
						echo $pagination->getListFooter();
						if(ACYMAILING_J30){
							$paginationNb = array();
							foreach(array(5, 10, 15, 20, 25, 30, 50, 100) as $oneOption){
								$paginationNb[] = acymailing_selectOption($oneOption, $oneOption);
							}
							$paginationNb[] = acymailing_selectOption(0, acymailing_translation('ACY_ALL'));
							echo 'Display #'.acymailing_select($paginationNb, 'limit', 'size="1" style="width:60px" onchange="acymailing.submitform();"', 'value', 'text', $pageInfo->limit->value).'<br />';
						}
						echo $pagination->getResultsCounter();
						?>
					</td>
				</tr>
				</tfoot>
				<tbody>
				<?php
				$k = 0;
				if(!empty($rows)){
					foreach($rows as $row){
						?>
						<tr id="content<?php echo $row->id; ?>" class="<?php echo "row$k"; ?>" onclick="applyContent(<?php echo $row->id.",'row$k'" ?>);" style="cursor:pointer;">
							<td class="acytdcheckbox"></td>
							<td style="text-align:center;">
								<?php
								if(!empty($row->title)) echo $row->title;
								?>
							</td>
							<td style="text-align:center;">
								<?php

								$cats = acymailing_loadResultArray('SELECT cat.name FROM #__terms AS cat 
																	JOIN #__term_taxonomy AS tt ON tt.term_id = cat.term_id 
																	JOIN #__term_relationships AS tr ON tt.term_taxonomy_id = tr.term_taxonomy_id
																	WHERE tt.taxonomy = "category" AND tr.object_id = '.intval($row->id).' 
																	ORDER BY cat.name ASC');
								if(!empty($cats)) echo implode(',', $cats);

								?>
							</td>
							<td style="text-align:center;">
								<?php
								echo $row->id;
								?>
							</td>
						</tr>
						<?php
						$k = 1 - $k;
					}
				}
				?>
				</tbody>
			</table>
		</div>
		<input type="hidden" name="boxchecked" value="0"/>
		<input type="hidden" name="filter_order" value="<?php echo $pageInfo->filter->order->value; ?>"/>
		<input type="hidden" name="filter_order_Dir" value="<?php echo $pageInfo->filter->order->dir; ?>"/>

		<?php
		echo $tabs->endPanel();
		echo $tabs->startPanel(acymailing_translation('TAG_CATEGORIES'), $this->name.'_auto');
		?>

		<br style="font-size:1px"/>
		<div class="onelineblockoptions">
			<table width="100%" class="acymailing_table">
				<tr id="formatauto" class="acyplugformat">
					<td valign="top">
						<?php echo acymailing_translation('FORMAT'); ?>
					</td>
					<td valign="top">
						<?php echo $this->acypluginsHelper->getFormatOption($this->name, 'TOP_LEFT', false, 'updateTagAuto'); ?>
					</td>
					<td nowrap="nowrap" valign="top">
						<?php echo acymailing_translation('DISPLAY_PICTURES'); ?>
					</td>
					<td nowrap="nowrap" colspan="2"><?php echo acymailing_radio($valImages, 'autopict', 'size="1" onclick="updateTagAuto();"', 'value', 'text', $pageInfo->pictauto); ?>
						<span id="pictsizeauto" <?php if($pageInfo->pictauto != 'resized') echo 'style="display:none;"'; ?> ><br/>
						<label for="pictwidthauto"><?php echo acymailing_translation('CAPTCHA_WIDTH') ?></label>
						<input id="pictwidthauto" name="pictwidthauto" type="text" onchange="updateTagAuto();" value="<?php echo $pageInfo->pictwidth; ?>" style="width:30px;"/>
						x <label for="pictheightauto"><?php echo acymailing_translation('CAPTCHA_HEIGHT') ?></label>
						<input id="pictheightauto" name="pictheightauto" type="text" onchange="updateTagAuto();" value="<?php echo $pageInfo->pictheight; ?>" style="width:30px;"/>
					</span>
					</td>
				</tr>
				<tr>
					<?php
					?>
					<td nowrap="nowrap"><?php echo acymailing_translation('DISPLAY'); ?></td>
					<?php
					$i = 1;
					foreach($fieldsDisplay as $oneField){
						if($i == 5){
							echo '</tr><tr><td/>';
							$i = 1;
						}
						echo '<td nowrap="nowrap"><input type="checkbox" name="cbdisplayauto" value="'.$oneField['title'].'" id="'.$oneField['title'].'auto" '.(($oneField['checked'] == 'yes') ? 'checked' : '').' onclick="updateTagAuto();"/><label style="margin-left:5px" for="'.$oneField['title'].'auto">'.trim(acymailing_translation($oneField['label']), ':').'</label></td>';
						$i++;
					}
					while($i != 5){
						echo '<td/>';
						$i++;
					}
					?>
				</tr>
				<tr>
					<td nowrap="nowrap"><?php echo acymailing_translation('CLICKABLE_TITLE'); ?></td>
					<td nowrap="nowrap" colspan="2"><?php echo acymailing_radio($choice, 'clickableauto', 'size="1" onclick="updateTagAuto();"', 'value', 'text', $pageInfo->clickableauto); ?></td>
					<td nowrap="nowrap"><?php echo acymailing_translation('JOOMEXT_READ_MORE'); ?></td>
					<td nowrap="nowrap"><?php echo acymailing_radio($choice, 'readmoreauto', 'size="1" onclick="updateTagAuto();"', 'value', 'text', $pageInfo->readmoreauto); ?></td>
				</tr>
				<tr>
					<td>
						<?php echo acymailing_translation('FIELD_COLUMNS'); ?>
					</td>
					<td nowrap="nowrap" colspan="2">
						<?php echo acymailing_select($column, 'cols', 'size="1" onchange="updateTagAuto();" style="width:100px;"', 'value', 'text', $pageInfo->cols); ?>
					</td>
					<td nowrap="nowrap" colspan="2"><?php echo acymailing_translation_sprintf('TRUNCATE_AFTER', '<input type="text" name="wrapauto" style="width:50px" value="0" onchange="updateTagAuto();"/>'); ?></td>
				</tr>
				<tr>
					<td nowrap="nowrap">
						<label for="max_article"><?php echo acymailing_translation('MAX_ARTICLE'); ?></label>
					</td>
					<td nowrap="nowrap" colspan="2">
						<input type="text" id="max_article" name="max_article" style="width:50px" value="20" onchange="updateTagAuto();"/>
					</td>
					<td nowrap="nowrap">
						<?php echo acymailing_translation('ACY_ORDER'); ?>
					</td>
					<td nowrap="nowrap">
						<?php
						$values = array('ID' => 'ACY_ID', 'post_date' => 'CREATED_DATE', 'post_modified' => 'MODIFIED_DATE', 'post_title' => 'FIELD_TITLE');
						echo $this->acypluginsHelper->getOrderingField($values, $pageInfo->contentorder, $pageInfo->contentorderdir);
						?>
					</td>
				</tr>
				<?php if($type == 'autonews'){ ?>
					<tr>
						<td nowrap="nowrap">
							<label for="min_article"><?php echo acymailing_translation('MIN_ARTICLE'); ?></label>
						</td nowrap="nowrap">
						<td nowrap="nowrap" colspan="2">
							<input type="text" id="min_article" name="min_article" style="width:50px" value="1" onchange="updateTagAuto();"/>
						</td>
						<td nowrap="nowrap">
							<?php echo acymailing_translation('JOOMEXT_FILTER'); ?>
						</td>
						<td nowrap="nowrap">
							<?php $filter = acymailing_get('type.contentfilter');
							$filter->onclick = 'updateTagAuto();';
							echo $filter->display('contentfilter', $pageInfo->contentfilter, false); ?>
						</td>
					</tr>
				<?php } ?>
			</table>
		</div>
		<div class="onelineblockoptions">
			<table class="acymailing_table" cellpadding="1" width="100%">
				<tr id="catall" class="<?php echo "row0"; ?>" onclick="applyAuto('all','<?php echo "row0" ?>');" style="cursor:pointer;">
					<td class="acytdcheckbox"></td>
					<td><?php echo acymailing_translation('ACY_ALL'); ?></td>
				</tr>
				<?php
				$k = 1;
				if(!empty($this->catvalues)){
					foreach($this->catvalues as $oneCat){
						if(empty($oneCat->value)) continue;
						?>
						<tr id="cat<?php echo $oneCat->value ?>" class="<?php echo "row$k"; ?>" onclick="applyAuto(<?php echo $oneCat->value ?>,'<?php echo "row$k" ?>');" style="cursor:pointer;">
							<td class="acytdcheckbox"></td>
							<td>
								<?php
								echo $oneCat->text;
								?>
							</td>
						</tr>
						<?php
						$k = 1 - $k;
					}
				}
				?>
			</table>
		</div>
		<?php
		echo $tabs->endPanel();
		echo $tabs->endPane();
	}
	private function _categories($filter_cat){
		$cats = acymailing_loadObjectList('SELECT cat.term_id AS id, tt.parent AS parent_id, cat.name AS title 
											FROM #__terms AS cat 
											JOIN #__term_taxonomy AS tt ON tt.term_id = cat.term_id
											WHERE tt.taxonomy = "category"
											ORDER BY id ASC');
		if(!empty($cats)){
			foreach($cats as $oneCat){
				$this->cats[$oneCat->parent_id][] = $oneCat;
			}
		}

		$this->catvalues[] = acymailing_selectOption(0, acymailing_translation('ACY_ALL'));
		$this->_handleChildren();
		return acymailing_select($this->catvalues, 'filter_cat', 'class="inputbox" size="1" onchange="document.adminForm.submit( );"', 'value', 'text', intval($filter_cat));
	}

	private function _handleChildren($parent_id = 0, $level = 0){
		if(empty($this->cats[$parent_id])) return;
		foreach($this->cats[$parent_id] as $cat){
			$this->catvalues[] = acymailing_selectOption($cat->id, str_repeat(" - - ", $level).$cat->title);
			$this->_handleChildren($cat->id, $level + 1);
		}
	}

	function acymailing_replacetags(&$email){
		$this->_replaceAuto($email);
		$this->_replaceOne($email);
	}

	private function _replaceAuto(&$email){
		$this->acymailing_generateautonews($email);
		if(empty($this->tags)) return;
		$this->acypluginsHelper->replaceTags($email, $this->tags, true);
	}

	function acymailing_generateautonews(&$email){
		$tags = $this->acypluginsHelper->extractTags($email, 'auto'.$this->name);
		$return = new stdClass();
		$return->status = true;
		$return->message = '';

		if(empty($tags)) return $return;

		foreach($tags as $oneTag => $parameter){
			if(isset($this->tags[$oneTag])) continue;
			$allcats = explode('-', $parameter->id);
			$selectedArea = array();
			foreach($allcats as $oneCat){
				if(empty($oneCat)) continue;
				$selectedArea[] = intval($oneCat);
			}

			$query = 'SELECT DISTINCT a.id FROM #__posts AS a ';
			$where = array();
			$where[] = 'a.post_type = "post"';

			if(!empty($selectedArea)){
				$query .= 'JOIN #__term_relationships AS tr ON tr.object_id = a.ID 
							JOIN #__term_taxonomy AS tt ON tt.term_taxonomy_id = tr.term_taxonomy_id AND tt.taxonomy = "category" ';
				$where[] = 'tt.term_id IN ('.implode(',', $selectedArea).')';
			}

			if(!empty($parameter->filter) && !empty($email->params['lastgenerateddate'])){
				$condition = 'a.post_date > \''.date('Y-m-d H:i:s', $email->params['lastgenerateddate'] - date('Z')).'\'';
				if($parameter->filter == 'modified'){
					$condition .= ' OR a.post_modified > \''.date('Y-m-d H:i:s', $email->params['lastgenerateddate'] - date('Z')).'\'';
				}
				$where[] = $condition;
			}

			if(empty($parameter->unpublished)) $where[] = 'a.post_status = "publish"';

			if(!empty($where)) $query .= ' WHERE ('.implode(') AND (', $where).')';

			if(!empty($parameter->order)){
				$ordering = explode(',', $parameter->order);
				if($ordering[0] == 'rand'){
					$query .= ' ORDER BY rand()';
				}else{
					$query .= ' ORDER BY a.'.acymailing_secureField(trim($ordering[0])).' '.acymailing_secureField(trim($ordering[1]));
				}
			}

			$start = '';
			if(!empty($parameter->start)) $start = intval($parameter->start).',';
			if(empty($parameter->max)) $parameter->max = 20;
			$query .= ' LIMIT '.$start.intval($parameter->max);


			$allArticles = acymailing_loadResultArray($query);

			if(!empty($parameter->min) && count($allArticles) < $parameter->min){
				$return->status = false;
				$return->message = 'Not enough posts content for the tag '.$oneTag.' : '.count($allArticles).' / '.$parameter->min;
			}

			$stringTag = '';
			if(!empty($allArticles)){
				if(file_exists(ACYMAILING_MEDIA.'plugins'.DS.'auto'.$this->name.'.php')){
					ob_start();
					require(ACYMAILING_MEDIA.'plugins'.DS.'auto'.$this->name.'.php');
					$stringTag = ob_get_clean();
				}else{
					$arrayElements = array();
					unset($parameter->id);
					$numArticle = 1;
					foreach($allArticles as $oneArticleId){
						$numArticle++;
						$args = array();
						$args[] = $this->name.':'.$oneArticleId;
						foreach($parameter as $oneParam => $val){
							if($oneParam == 'invert' && $numArticle % 2 == 0) continue;
							if(is_bool($val)){
								$args[] = $oneParam;
							}else $args[] = $oneParam.':'.$val;
						}
						$arrayElements[] = '{'.implode('|', $args).'}';
					}
					$stringTag = $this->acypluginsHelper->getFormattedResult($arrayElements, $parameter);
				}
			}
			$this->tags[$oneTag] = $stringTag;
		}
		return $return;
	}

	private function _replaceOne(&$email){

		$tags = $this->acypluginsHelper->extractTags($email, $this->name);
		if(empty($tags)) return;

		$this->readmore = empty($email->template->readmore) ? acymailing_translation('JOOMEXT_READ_MORE') : '<img src="'.ACYMAILING_LIVE.$email->template->readmore.'" alt="'.acymailing_translation('JOOMEXT_READ_MORE', true).'" />';

		$tagsReplaced = array();
		foreach($tags as $i => $oneTag){
			if(isset($tagsReplaced[$i])) continue;
			$tagsReplaced[$i] = $this->_replaceContent($oneTag);
		}

		$this->acypluginsHelper->replaceTags($email, $tagsReplaced, true);
	}

	private function _replaceContent(&$tag){
		if(!empty($tag->display)){
			$tag->display = explode(',', $tag->display);

			foreach($tag->display as $i => $oneDisplay){
				$oneDisplay = trim($oneDisplay);
				$tag->$oneDisplay = true;
			}
		}

		$content = acymailing_loadObject('SELECT * FROM #__posts WHERE ID = '.intval($tag->id).' LIMIT 1');

		if(empty($content)){
			if(acymailing_isAdmin()) acymailing_enqueueMessage('The post n°"'.$tag->id.'" could not be loaded', 'notice');
			return '';
		}

		$varFields = array();
		foreach($content as $fieldName => $oneField){
			$varFields['{'.$fieldName.'}'] = $oneField;
		}
		$afterTitle = '';
		$afterArticle = '';
		$contentText = '';
		$customFields = array();

		$link = get_permalink($tag->id);
		$varFields['{link}'] = $link;

		if(!empty($tag->description) && !empty($content->post_content)){
			$readmorePos = strpos($content->post_content, '<!--more-->');
			if(empty($tag->noreadmore) && $readmorePos !== false) $content->post_content = substr($content->post_content, 0, $readmorePos);
			$contentText .= $this->acypluginsHelper->wrapText($content->post_content, $tag);
		}

		if(!empty($tag->category)) $customFields[] = array(acymailing_translation('ACY_CATEGORY'), $content->cattitle);

		$varFields['{readmore}'] = '<a class="readmore_link" style="text-decoration:none;" target="_blank" href="'.$link.'"><span class="acymailing_readmore">'.$this->readmore.'</span></a>';
		$afterArticle .= empty($tag->noreadmore) ? '<br/>'.$varFields['{readmore}'] : '';

		$format = new stdClass();
		$format->tag = $tag;
		$format->title = empty($tag->notitle) ? $content->post_title : '';
		$format->afterTitle = $afterTitle;
		$format->afterArticle = $afterArticle;

		$mainPicture = wp_get_attachment_image_src(get_post_thumbnail_id($tag->id), 'single-post-thumbnail');
		$varFields['{pictURL}'] = empty($mainPicture[0]) ?  '': $mainPicture[0];
		$format->imagePath = $varFields['{pictURL}'];

		$format->description = $contentText;
		$format->link = empty($tag->nolink) ? $link : '';
		$format->cols = empty($tag->nbcols) ? 1 : intval($tag->nbcols);
		$format->customFields = $customFields;
		$result = '<div class="acymailing_content">'.$this->acypluginsHelper->getStandardDisplay($format).'</div>';

		if(!empty($tag->template) && file_exists(ACYMAILING_MEDIA.'plugins'.DS.$tag->template.'.php')){
			ob_start();
			require(ACYMAILING_MEDIA.'plugins'.DS.$tag->template.'.php');
			$result = ob_get_clean();
			$result = str_replace(array_keys($varFields), $varFields, $result);
		}

		$result = $this->acypluginsHelper->removeJS($result);
		$result = $this->acypluginsHelper->managePicts($tag, $result);

		return $result;
	}
}//endclass

