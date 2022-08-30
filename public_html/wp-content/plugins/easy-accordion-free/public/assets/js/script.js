; (function ($) {

	$('body').find('.sp-easy-accordion').each(function () {
		var accordion_id = $(this).attr('id');
		var _this = $(this);
		var ea_active = _this.data('ea-active');
		var ea_mode = _this.data('ea-mode');
		var preloader = _this.data('preloader');
		if (ea_mode === 'vertical') {
			if (ea_active === 'ea-click') {
				$("#" + accordion_id).each(function () {
					$("#" + accordion_id + " > .ea-card > .ea-header").on("click", function () {
						$("#" + accordion_id + " > .ea-card > .sp-collapse").on("hide.bs.spcollapse", function (e) {
							$(this).parent(".ea-card").removeClass("ea-expand");
							$(this).siblings(".ea-header").find(".ea-expand-icon").addClass('fa-plus').removeClass('fa-minus');
							e.stopPropagation();
						})
						$("#" + accordion_id + " > .ea-card > .sp-collapse").on("show.bs.spcollapse", function (e) {
							$(this).parent(".ea-card").addClass("ea-expand");
							$(this).siblings(".ea-header").find(".ea-expand-icon").addClass('fa-minus').removeClass('fa-plus');
							e.stopPropagation();
						})
					});
				});
				$("#" + accordion_id + " > .ea-card .ea-header a ").on('click', function (event) {
					event.preventDefault();
				});
			}
			if (ea_active === 'ea-hover') {
				$("#" + accordion_id + " > .ea-card").mouseover(function () {
					$(this).children(".sp-collapse").spcollapse("show");
					if ($('.sp-collapse.show').length > 1) {
						$(this).children(".sp-collapse").spcollapse("hide");
					}
				});
				$("#" + accordion_id + " > .ea-card > .sp-collapse").on("hide.bs.spcollapse", function (e) {
					$(this).parent(".ea-card").removeClass("ea-expand");
					$(this).siblings(".ea-header").find(".ea-expand-icon").addClass('fa-plus').removeClass('fa-minus');
					e.stopPropagation();
				})
				$("#" + accordion_id + " > .ea-card > .sp-collapse").on("show.bs.spcollapse", function (e) {
					$(this).parent(".ea-card").addClass("ea-expand");
					$(this).siblings(".ea-header").find(".ea-expand-icon").addClass('fa-minus').removeClass('fa-plus');
					e.stopPropagation();
				})
			};
		}
		var preloader_id = $('#' + accordion_id + ' .accordion-preloader').attr('id');
		if (preloader_id) {
			$(document).ready(function () {
				$('#' + preloader_id).animate({ opacity: 0, }, 500).remove();
				$('#' + accordion_id).find('.ea-card').animate({ opacity: 1 }, 500);
			});
		}

		if ($("#" + accordion_id + ' .wp-easy-accordion-iframe-container').length <= 1) {
			$("#" + accordion_id + ' iframe').addClass('wp-ea-iframe').wrap("<div class='wp-easy-accordion-iframe-container'></div>");
		}

	});
})(jQuery);