var home_url = WP_URLs.home_url;

var tct_viewer = (function($, document, window) {
	
	var osdViewer,
			imageData,
			imageLink,
			imageHeight,
			imageWidth,
			selection,
			sliderHtml = '<div class="sliderContainer" id="filterContainer"> ' +
										'  <div id="closeFilterContainer"><i class="fas fa-times"></i></div>' +
								    '  <div class="slidecontainer">' +
								    '    <div id="brightnessIcon" class="sliderIcon"></div>' +
								    '    <input type="range" min="-100" max="100" value="0" class="iiifSlider" id="brightnessRange">' +
								    '    <div id="brightnessValue" class="sliderValue">0</div>' +
								    '  </div>' +
								    '  <div class="slidecontainer">' +
								    '    <div id="contrastIcon" class="sliderIcon"></div>' +
								    '    <input type="range" min="-100" max="100" value="0" class="iiifSlider" id="contrastRange">' +
								    '    <div id="contrastValue" class="sliderValue">0</div>' +
								    '  </div>' +
										'  <div class="slidecontainer">' +
								    '    <div id="saturationIcon" class="sliderIcon"></div>' +
								    '    <input type="range" min="-100" max="100" value="0" class="iiifSlider" id="saturationRange">' +
								    '    <div id="saturationValue" class="sliderValue">0</div>' +
								    '  </div>' +
										'  <div class="slidecontainer invert">' +										   
										    '    <input type="checkbox" class="iiifCheckbox" id="invertRange">' +
 										    '    <div id="inverteIcon" class="sliderIcon">invert</div>' +
										    '  </div>' +
								    '  <div id="filterReset"><div class="resetText">Reset to default</div></div>' +
								    '</div>';
	var init = function() {
		addImageFilter();
		getManifestUrl();
		initTiny();
		// in viewer button functionality
		jQuery('#full-page').click(function() {
			document.querySelector('#transcription-edit-container').style.display = 'none';
			document.querySelector('#transcription-view-container').style.display = 'block';
			toggleFS();
		});
		jQuery('#no-text-placeholder').click(function() {
			jQuery('#switch-tr-view').click();
			toggleFS();

		});
		jQuery('.mtr-active').click(function() {
			document.querySelector('#transcription-edit-container').style.display = 'block';
			document.querySelector('#transcription-view-container').style.display = 'none';
			document.querySelector('#tr-tab').click();
			toggleFS();
		});
		jQuery('#startDescription').click(function() {
			document.querySelector('#desc-tab').click();
			toggleFS();
		});
		jQuery('#startLocation').click(function() {
			document.querySelector('#loc-tab').click();
			toggleFS();
		});
		jQuery('#startEnrichment').click(function() {
			document.querySelector('#tagi-tab').click();
			toggleFS();
		});

		jQuery('#full-width').click(function() {
			fullWidth();
		});

		jQuery('#filterButton').click(function() {
			openFilterOverlay('');
		});

		jQuery('#closeFilterContainer').click(function() {
			jQuery('#filterContainer').hide();
		});

		jQuery('#transcribeIcon').click(function() {
			toggleFS();
			//open transcribe tab
			var i, tabcontent, tablinks;

		  // Hide all tab contents
		  tabcontent = document.getElementsByClassName("tabcontent");
		  for (i = 0; i < tabcontent.length; i++) {
		    tabcontent[i].style.display = "none";
		  }
		  // Make tab icons inactive
		  tablinks = document.getElementsByClassName("tablinks");
		  for (i = 0; i < tablinks.length; i++) {
		    tablinks[i].className = tablinks[i].className.replace(" active", "");
		  }

		  // Show clicked tab content and make icon active
		  document.getElementById("editor-tab").style.display = "block";
			document.getElementsByClassName("tablinks")[0].className += " active";
		});

		jQuery('#transcribe').click(function() {
			if(!jQuery(this).children('i').hasClass('locked')) {
				toggleFS(); 
				if (jQuery('#transcription-section').width() >= 495) {
				//   jQuery('#mytoolbar-transcription').css('height', '1px');
				}
				else {
				//   jQuery('#mytoolbar-transcription').css('height', '1px');
				}
				tinymce.EditorManager.get('item-page-transcription-text').focus();
				jQuery('.tox-tinymce').css('width', jQuery('#mytoolbar-transcription').css('width'))
				//TODO maximize
			}
		});

		jQuery('#transcribeLock').click(function() {
			lockWarning();
		})
		jQuery('#description-open').click(function() {
			toggleFS();
			document.querySelector('#desc-tab').click();
		});
		jQuery('#media-open').click(function() {
			toggleFS();
			document.querySelector('#desc-tab').click();
		});
		jQuery('#date-open').click(function() {
			toggleFS();
			document.querySelector('#tagi-tab').click();
		});
		jQuery('#keywords-open').click(function(e) {
			e.stopPropagation();
			toggleFS();
			document.querySelector('#tagi-tab').click();
		});
		jQuery('#links-open').click(function(e) {
			e.stopPropagation();
			toggleFS();
			document.querySelector('#tagi-tab').click();
		});
		jQuery('#people-open').click(function(e) {
			e.stopPropagation();
			toggleFS();
			document.querySelector('#tagi-tab').click();
		});

	},
	addImageFilter = function() {
		jQuery("#openseadragon").append(sliderHtml);
	},
	getManifestUrl = function() {
		imageData = JSON.parse(jQuery('#image-data-holder').val());
		imageLink = imageData['service']['@id'];
		if (imageData['service']['@id'].substr(0, 4) == "http") {
			imageLink = imageData['service']['@id'];
		}
		else {
			imageLink = "http://" + imageData['service']['@id'];
		}
		imageHeight = imageData['height'];
		imageWidth = imageData['width'];
		initViewers();
	},
	getImageLink = function() {
		return imageLink;
	},
	initViewers = function() {
		
		if(imageLink.substring(0,5) != 'https') {
			imageLink = imageLink.replace('http', 'https');
		}
		osdViewer = OpenSeadragon({
			id: "openseadragon",
			sequenceMode: false,
			showRotationControl: true,
			showFullPageControl: false,
			toolbar: "buttons",
			homeButton: "home",
			zoomInButton: "zoom-in",
			zoomOutButton: "zoom-out",
			rotateLeftButton: "rotate-left",
			rotateRightButton: "rotate-right",
			prefixUrl: home_url + "/wp-content/themes/transcribathon/images/osdImages/",
			tileSources: imageLink + '/info.json',
			maxZoomLevel: 8,
			minZoomLevel: 0.3,
			autoHideControls: false,
			preserveImageSizeOnResize: true
		});
		
		sliderInit();

		osdViewer.addHandler('open',function() { 
			setTimeout(() => {
                fullWidth();
			}, 20);
		});
	},
	fullWidth = function() {
	  var oldBounds = osdViewer.viewport.getBounds();
	  var newBounds = new OpenSeadragon.Rect(0, 0, 1, oldBounds.height / oldBounds.width);
	  osdViewer.viewport.fitBounds(newBounds, false);
	},
	toggleFS = function() {
		switchItemPageView();
		setTimeout(() => {
			fullWidth()
		}, 20);
	},
	// filter functionality
  // create Filter overlay button
  openFilterOverlay = function(sel) {
    jQuery('#openseadragon' + sel + ' #filterContainer').toggle();
  },
	sliderInit = function(sel) {
	  var brightnessSlider = jQuery('#openseadragon #brightnessRange')[0];
	  var contrastSlider = jQuery('#openseadragon #contrastRange')[0];
	  var saturationSlider = jQuery('#openseadragon #saturationRange')[0];
		var invertSlider = jQuery('#openseadragon #invertRange')[0];
	  var canvas = jQuery('#openseadragon .openseadragon-container canvas')[0];

	  var brightness = Number(brightnessSlider.value) + 100;
	  var contrast = (Number(contrastSlider.value) + 100) * 1;
	  var saturation = (Number(saturationSlider.value) + 100) * 1;
		var invert = 0;

	  var bValue = jQuery('#openseadragon #brightnessValue')[0];
	  var cValue = jQuery('#openseadragon #contrastValue')[0];
	  var sValue = jQuery('#openseadragon #saturationValue')[0];

	  // Update the current slider value (each time you drag the slider handle)
	  brightnessSlider.oninput = function() {
	    brightness = Number(this.value) + 100;
	    canvas.style.filter = "brightness(" + brightness + "%) contrast(" + contrast + "%) saturate(" + saturation + "%) invert(" + invert + ")";
	    bValue.innerHTML = brightness - 100;
	  }

	  contrastSlider.oninput = function() {
	    contrast = (Number(this.value) + 100) * 1;
	    canvas.style.filter = "brightness(" + brightness + "%) contrast(" + contrast + "%) saturate(" + saturation + "%) invert(" + invert + ")";
	    cValue.innerHTML = Number(this.value);
	  }

	  saturationSlider.oninput = function() {
	    saturation = (Number(this.value) + 100) * 1;
	    canvas.style.filter = "brightness(" + brightness + "%) contrast(" + contrast + "%) saturate(" + saturation + "%) invert(" + invert + ")";
	    sValue.innerHTML = Number(this.value);
	  }

		invertSlider.oninput = function() {
			if(this.checked) {
				invert = 1;
			} else {
				invert = 0;
			}
			canvas.style.filter = "brightness(" + brightness + "%) contrast(" + contrast + "%) saturate(" + saturation + "%) invert(" + invert + ")";
		}

		jQuery('#openseadragon #filterReset').click(function() {
			canvas.style.filter = "brightness(" + 100 + "%) contrast(" + 100 + "%) saturate(" + 100 + "%) invert(0)";
			sValue.innerHTML = 0;
			cValue.innerHTML = 0;
			bValue.innerHTML = 0;
			brightnessSlider.value = 0;
			contrastSlider.value = 0;
		 	saturationSlider.value = 0;
			invertSlider.checked = false;
			brightness = 100;
			contrast = 100;
			saturation = 100;
		});

	},
	
	fullscreenEdit = function() {
	  console.log('show fullscreen Editor');
	  toggleFSEditor('fs_editor_toggle');
	},
	initTiny = function() {
		//none fs ones
		initTinyWithConfig('#item-page-transcription-text');
		// if(document.querySelector('.tox-tinymce')){
		// 	document.querySelector('.tox-tinymce').style.display = 'block';
		// }
		
	},
	getUrlParameter = function(sParam) {
		var sPageURL = window.location.search.substring(1),
			sURLVariables = sPageURL.split('&'),
			sParameterName,
			i;

		for (i = 0; i < sURLVariables.length; i++) {
			sParameterName = sURLVariables[i].split('=');
			if (sParameterName[0] === sParam) {
				return sParameterName[1] === undefined ? true : decodeURIComponent(sParameterName[1]);
			}
		}
	},
	getSelection = function() {
		return selection;
	},
	getOsdViewer = function() {
		return osdViewer;
	},
	makeImageSelection = function() {
		console.log('making image selection');
		selection.enable();
	},
	initTinyWithConfig = function(selector) {
	  tinymce.init({
	    selector: selector,
		menubar: false,
        inline: true,
		resize: true,
		plugins: 'wordcount table charmap directionality',
		toolbar: 'bold italic underline strikethrough removeformat | alignleft aligncenter alignright alignjustify | table | missbut unsure side-info | charmap undo redo subscript superscript indent ltr rtl wordcount',
		placeholder:' Start transcribing...',
		toolbar_mode: 'floating',
	
			setup: function (editor) {
				
				editor.on('keydown',function(evt){
					if (evt.keyCode==9) {
						editor.execCommand('mceInsertContent', false, '&emsp;&emsp;'); // inserts tab
						evt.preventDefault();
						return false;
					}
				});
				editor.ui.registry.addIcon('missing', '<i class="mce-ico mce-i-missing"></i>');
    			editor.ui.registry.addIcon('unsure', '<i class="mce-ico mce-i-unsure"></i>');
				editor.ui.registry.addIcon('info', '<i class="mce-ico mce-i-pos-in-text"></i>');

				editor.ui.registry.addButton('missbut', {
					tooltip: 'Insert an indicator for missing text',
					icon: 'missing',
					onAction: function () {
						editor.insertContent('<img src="/wp-content/themes/transcribathon/images/tinyMCEImages/missing.gif" style=\"display:inline;\" class=\"tct_missing\" alt=\"missing\" />');
						}
				});
				editor.ui.registry.addButton('unsure', {
					tooltip: 'Mark selected as unclear',
					icon: 'unsure',
					onAction: function () {
						if(editor.selection.getContent({format : 'text'}).split(' ').join('').length < 1){
							editor.insertContent('<span class=\"tct-uncertain\"> ...</span>')
						}else{
							if (editor.selection.getStart().className == "tct-uncertain") {
								var node = editor.selection.getStart();
								node.parentNode.replaceChild(document.createTextNode(node.innerHTML.replace("&nbsp;", "")), node);
							}
							else if (editor.selection.getEnd().className == "tct-uncertain"){
								var node = editor.selection.getEnd();
								node.parentNode.replaceChild(document.createTextNode(node.innerHTML.replace("&nbsp;", "")), node);
							}
							else{
								editor.insertContent('&nbsp;<span class=\"tct-uncertain\">'+editor.selection.getContent({format : 'html'})+'</span>&nbsp;');
							}
						}
					}
				});
        editor.ui.registry.addButton('side-info', {
          tooltip: 'Add a comment',
          text: '',
          icon: 'info',
          onAction: function () {
            if(editor.selection.getContent({format : 'text'}).split(' ').join('').length < 1){
              editor.insertContent(' ');
              editor.insertContent(' ' + '<span class=\"pos-in-text\"> ...</span>' + ' ');
              editor.insertContent(' ');
            }else{
              if (editor.selection.getStart().className == "pos-in-text") {
                var node = editor.selection.getStart();
                node.parentNode.replaceChild(document.createTextNode(node.innerHTML.replace("&nbsp;", "")), node);
              }
              else if (editor.selection.getEnd().className == "pos-in-text"){
                var node = editor.selection.getEnd();
                node.parentNode.replaceChild(document.createTextNode(node.innerHTML.replace("&nbsp;", "")), node);
              }
              else{
                editor.insertContent('&nbsp;<span class=\"pos-in-text\">'+editor.selection.getContent({format : 'html'})+'</span>&nbsp;');
              editor.insertContent(' ');
              }
            }
          }
        });
			},
			style_formats: [
				    {title: 'unclear, please review', inline: 'span', classes: 'tct_unclear'},
				    {title: 'Note', inline: 'span', classes: 'tct_note'},
				    {title: 'Badge', inline: 'span', styles: { display: 'inline-block', border: '1px solid #2276d2', 'border-radius': '5px', padding: '2px 5px', margin: '0 2px', color: '#2276d2' }}
				    ],
	    formats: {
	    alignleft: { selector: 'p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li,table,img', classes: 'left' },
	    aligncenter: { selector: 'p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li,table,img', classes: 'center' },
	    alignright: { selector: 'p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li,table,img', classes: 'right' },
	    alignfull: { selector: 'p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li,table,img', classes: 'full' },
	    bold: { inline: 'span', 'classes': 'bold' },
	    italic: { inline: 'span', 'classes': 'italic' },
	    underline: { inline: 'span', 'classes': 'underline', exact: true },
	    strikethrough: { inline: 'del' },
	    },
	  })
	}; // End of Tinymce
	$(document).ready(function($) {
		if (jQuery('#image-data-holder').length) {
			init();
		}
	});
	return {
		initTinyWithConfig: initTinyWithConfig,
		makeImageSelection: makeImageSelection,
		getSelection: getSelection,
		getImageLink: getImageLink,
		getOsdViewer: getOsdViewer
	}
})(jQuery, document, window);
