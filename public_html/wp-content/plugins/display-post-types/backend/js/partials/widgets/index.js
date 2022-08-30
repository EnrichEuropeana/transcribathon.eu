( function( $ ) {

	var widget = jQuery('#widgets-right, #elementor-editor-wrapper, #widgets-editor');

	// Add event triggers to the show/hide items.
	widget.on('change', 'select.dpt-post-type', function() {
		showPosttypeContent( $(this) );
	});

	widget.on('change', 'select.dpt-taxonomy', function() {
		showTerms( $(this) );
	});

	widget.on('change', 'select.dpt-styles', function() {
		showColumnCount( $(this) );
	});

	widget.on('change', 'select.dpt-img-aspect', function() {
		showCroppos( $(this) );
	});

	widget.on('change', 'input[type="checkbox"].spcheckbox', function() {
		showElemOptions( $(this) );
	});

	widget.on('change', '.style_sup-checklist input[type="checkbox"]', function() {
		var _this = $(this),
			value = $(this).val(), wrapper, custEx;
		if ('excerpt' === value) {
			wrapper = _this.closest('.widget-content');
			custEx = wrapper.find('.e_length, .e_teaser');
			if (_this.prop('checked')) {
				custEx.show();
			} else {
				custEx.hide();
			}
		}
	});

	$( document ).on( 'click', '.dpt-settings-toggle', function( event ) {
		var _this = $( this );
		event.preventDefault();
		_this.next( '.dpt-settings-content' ).slideToggle('fast');
		_this.toggleClass( 'toggle-active' );
	});
	
	function showPosttypeContent( pType ) {
		var postType  = pType.val(),
			container = pType.closest('.widget-content'),
			toggle    = container.find('.dpt-settings-toggle'),
			wrapper   = container.find('.dpt-settings-content'),
			taxSelec  = wrapper.find( 'select.dpt-taxonomy' ),
			postPanel = [
				'.post_ids',
				'.taxonomy',
				'.offset',
				'.orderby',
				'.order',
			];
		if (postType) {
			toggle.show();
			if ('page' === postType) {
				wrapper.find('.pages').show();
				wrapper.find(postPanel.join(',')).hide();
			} else {
				wrapper.find(postPanel.join(',')).show();
				wrapper.find('.pages, .terms, .relation').hide();
				taxSelec.find( 'option' ).hide();
				taxSelec.find( '.' + postType ).show();
				taxSelec.find( '.always-visible' ).show();
				taxSelec.val('');
			}
			if ('post' !== postType) {
				wrapper.addClass('not-post');
			} else {
				wrapper.removeClass('not-post');
			}
		} else {
			toggle.hide();
			wrapper.hide();
		}
	}

	function showColumnCount( st ) {
		var style = st.val(),
			wrapper = st.closest('.widget-content'),
			align = [ 'dpt-list1', 'dpt-list2' ],
			multicol = [ 'dpt-grid1', 'dpt-grid2', 'dpt-slider1' ],
			excerpt = [ 'dpt-list1', 'dpt-grid1' ],
			slider  = ['dpt-slider1'],
			styleSupItems = [],
			supported, custEx;

		if (multicol.includes(style)) {
			wrapper.find('.col_narr').show();
		} else {
			wrapper.find('.col_narr').hide();
		}

		if ( align.includes(style) ) {
			wrapper.find('.img_align').show();
		} else {
			wrapper.find('.img_align').hide();
		}

		supported = wrapper.find('.style_sup');
		supported.find('.style_sup-checklist li').hide();
		supported.find('.' + style).show();

		$.each($('.style_sup-checklist input[type="checkbox"]'), function(){
			styleSupItems.push($(this).val());
		});

		if (excerpt.includes(style)) {
			if (styleSupItems.includes('excerpt')) {
				wrapper.find('.e_length, .e_teaser').show();
			}
		} else {
			wrapper.find('.e_length, .e_teaser').hide();
		}

		if (slider.includes(style)) {
			wrapper.find('.autotime').show();
			wrapper.find('.show_pgnation').hide();
		} else {
			wrapper.find('.show_pgnation').show();
			wrapper.find('.autotime').hide();
		}
	}

	function showTerms( taxonomy ) {
		var wrapper = taxonomy.closest('.dpt-settings-content');
		if ( taxonomy.val() ) {
			wrapper.find('.terms, .relation').show();
			wrapper.find('.terms').find( '.terms-checklist li' ).hide();
			wrapper.find('.terms').find( '.terms-checklist .' + taxonomy.val() ).show();
		} else {
			wrapper.find('.terms, .relation').hide();
		}
	}

	function showCroppos( crop ) {
		var cropping  = crop.val(),
			wrapper = crop.closest('.widget-content');

		if ('' !== cropping) {
			wrapper.find('.image_crop').show();
		} else {
			wrapper.find('.image_crop').hide();
		}
	}

	function showElemOptions( option ) {
		var value = option.val(), wrapper, custEx, crop, style;
		var align = [ 'dpt-list1', 'dpt-list2' ]
		if ('excerpt' === value) {
			wrapper = option.closest('.dpt-settings-content');
			custEx = wrapper.find('.e_length, .e_teaser');
			if (option.prop('checked')) {
				custEx.show();
			} else {
				custEx.hide();
			}
		}
		if ('thumbnail' === value) {
			wrapper = option.closest('.dpt-settings-content');
			custEx = wrapper.find('.pl_holder, .img_aspect');
			if (option.prop('checked')) {
				custEx.show();
				crop  = wrapper.find('select.dpt-img-aspect').val();
				style = wrapper.closest('.widget-content').find('select.dpt-styles').val();
				if (crop) wrapper.find('.image_crop').show();
				if ( align.includes(style) ) wrapper.find('.img_align').show();
			} else {
				custEx.hide();
			}
		}
		if ('meta' === value) {
			wrapper = option.closest('.dpt-settings-content');
			custEx = wrapper.find('.meta1');
			if (option.prop('checked')) {
				custEx.show();
			} else {
				custEx.hide();
			}
		}
		if ('category' === value) {
			wrapper = option.closest('.dpt-settings-content');
			custEx = wrapper.find('.meta2');
			if (option.prop('checked')) {
				custEx.show();
			} else {
				custEx.hide();
			}
		}
	}
}( jQuery ) );
