<div class='row'><label><?php _e("External Image URL", "ml-slider-pro") ?></label></div>
<div class='row'><input class='url extimgurl' type='text' name='attachment[<?php echo $this->slide->ID; ?>][extimgurl]' placeholder='Source Image URL' value='<?php echo $extimgurl; ?>' /></div>
<div class='row'><label><?php _e("Link URL", "ml-slider-pro") ?></label></div>
<input class='url' type='text' name='attachment[<?php echo $this->slide->ID; ?>][url]' placeholder='Link to URL' value='<?php echo $url; ?>' />
<div class='new_window'>
	<label>New Window<input type='checkbox' name='attachment[<?php echo $this->slide->ID; ?>][new_window]' <?php echo $target; ?> /></label>
</div>