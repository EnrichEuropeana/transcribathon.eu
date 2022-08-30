jQuery(document).ready(function() {
	
	
		
});
function tctTutorialStep(nitem,rootid){
	"use strict";
	jQuery("ul#tct-tutorial-slider-"+rootid+" li").each(function(){
		jQuery(this).removeClass('open');	
	});
	jQuery("li#tct-tutorial-slider-"+rootid+"-"+nitem).removeClass('open').addClass('open');
	
	var img = jQuery('img#img-'+rootid+'-'+nitem);
    var imageUrl = img.attr('src');
	var adr = imageUrl.split('?');
	var d = new Date();
	jQuery('img#img-'+rootid+'-'+nitem).attr('src','');
	jQuery('img#img-'+rootid+'-'+nitem).attr('src',adr[0]+'?'+d.getTime());

}
















