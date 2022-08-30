jQuery(document).ready(function($) {
	"use strict";	
	$('div.tct-menulist .tct-menulist-item a.tct-menulist-title').each(function(){
		if(!$(this).hasClass('open')){
			$(this).siblings('div.tct-menulist-content').hide();	
		}
		$(this).click(function(){
			openCloseTcTMenuItem($(this));
		});
	});
		
		
		
});



function openCloseTcTMenuItem(obj){
	"use strict";	
	if(!obj.hasClass('open')){
		jQuery(obj).siblings('div.tct-menulist-content').slideDown('slow',function(){
			jQuery(obj).removeClass('open').addClass('open');
		});
	}else{
		jQuery(obj).siblings('div.tct-menulist-content').slideUp('slow',function(){
			jQuery(obj).removeClass('open');
		});
	}
}
















