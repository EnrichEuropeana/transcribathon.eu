var home_url = WP_URLs.home_url;

var tct_viewer = (function($, document, window) {

	var osdViewer,
			osdViewerFS,
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
			toggleFS();
		});
		jQuery('#startTranscription').click(function() {
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
		jQuery('#openseadragonFS #closeFilterContainer').click(function() {
			jQuery('#openseadragonFS #filterContainer').hide();
		});

		jQuery('#full-pageFS').click(function() {
			toggleFS();
		});

		jQuery('#full-widthFS').click(function() {
			fullWidthFS();
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
		jQuery('#filterButtonFS').click(function() {
			openFilterOverlay('FS');
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
		})

		jQuery('#transcribeLockFS').click(function() {
			lockWarning()
		})
		jQuery('#transcribeLock').click(function() {
			lockWarning()
		})

		jQuery('#transcribeFS').click(function() {
			//TODO maximize
		})
	},
	addImageFilter = function() {
		jQuery("#openseadragon").append(sliderHtml);
		jQuery("#openseadragonFS").append(sliderHtml);
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
		console.log({
				"@context": "http://iiif.io/api/image/2/context.json",
				"@id": imageLink,
				"height": imageHeight,
				"width": imageWidth,
				"profile": [
					"http://iiif.io/api/image/2/level2.json"
				],
				"protocol": "http://iiif.io/api/image"
			});
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
			// tileSources: {
			// 	"@context": "http://iiif.io/api/image/2/context.json",
			// 	"@id": imageLink,
			// 	"height": imageHeight,
			// 	"width": imageWidth,
			// 	"profile": [
			// 		"http://iiif.io/api/image/2/level2.json"
			// 	],
			// 	"protocol": "http://iiif.io/api/image"
			// },
			maxZoomLevel: 8,
			minZoomLevel: 0.3,
			autoHideControls: false
		});

		osdViewerFS = OpenSeadragon({
			id: "openseadragonFS",
			sequenceMode: false,
			showRotationControl: true,
			showFullPageControl: false,
			toolbar: "buttonsFS",
			homeButton: "homeFS",
			zoomInButton: "zoom-inFS",
			zoomOutButton: "zoom-outFS",
			rotateLeftButton: "rotate-leftFS",
			rotateRightButton: "rotate-rightFS",
			prefixUrl: home_url + "/wp-content/themes/transcribathon/images/osdImages/",
			tileSources: imageLink + '/info.json',
			// tileSources: {
			// 	"@context": "http://iiif.io/api/image/2/context.json",
			// 	"@id": imageLink,
			// 	"height": imageHeight,
			// 	"width": imageWidth,
			// 	"profile": [
			// 		"http://iiif.io/api/image/2/level2.json"
			// 	],
			// 	"protocol": "http://iiif.io/api/image"
			// },
			maxZoomLevel: 8,
			minZoomLevel: 0.3,
			autoHideControls: false,
			preserveImageSizeOnResize: true
		});
		selectionInit();
		sliderInit();
		osdViewerFS.addOnceHandler('resize', function() {
		window.setTimeout(function() {
			var oldBounds = osdViewerFS.viewport.getBounds();
		  var newBounds = new OpenSeadragon.Rect(0, 0, 1, oldBounds.height / oldBounds.width);
		  osdViewerFS.viewport.fitBounds(newBounds, true);}, 20)
		});
	},
	fullWidth = function() {
	  var oldBounds = osdViewer.viewport.getBounds();
	  var newBounds = new OpenSeadragon.Rect(0, 0, 1, oldBounds.height / oldBounds.width);
	  osdViewer.viewport.fitBounds(newBounds, false);
	},
	fullWidthFS = function() {
	  var oldBounds = osdViewerFS.viewport.getBounds();
	  var newBounds = new OpenSeadragon.Rect(0, 0, 1, oldBounds.height / oldBounds.width);
	  osdViewerFS.viewport.fitBounds(newBounds, false);
	},
	toggleFS = function() {
		switchItemPageView();
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

		var brightnessSliderFS = jQuery('#openseadragonFS #brightnessRange')[0];
		var contrastSliderFS = jQuery('#openseadragonFS #contrastRange')[0];
		var saturationSliderFS = jQuery('#openseadragonFS #saturationRange')[0];
		var invertSliderFS = jQuery('#openseadragonFS #invertRange')[0];
		var canvasFS = jQuery('#openseadragonFS .openseadragon-container canvas')[0];

	  var brightness = Number(brightnessSlider.value) + 100;
	  var contrast = (Number(contrastSlider.value) + 100) * 1;
	  var saturation = (Number(saturationSlider.value) + 100) * 1;
		var invert = 0;

	  var bValue = jQuery('#openseadragon #brightnessValue')[0];
	  var cValue = jQuery('#openseadragon #contrastValue')[0];
	  var sValue = jQuery('#openseadragon #saturationValue')[0];

		var bValueFS = jQuery('#openseadragonFS #brightnessValue')[0];
		var cValueFS = jQuery('#openseadragonFS #contrastValue')[0];
		var sValueFS = jQuery('#openseadragonFS #saturationValue')[0];

	  // Update the current slider value (each time you drag the slider handle)
	  brightnessSlider.oninput = function() {
	    brightness = Number(this.value) + 100;
	    canvas.style.filter = "brightness(" + brightness + "%) contrast(" + contrast + "%) saturate(" + saturation + "%) invert(" + invert + ")";
			canvasFS.style.filter = "brightness(" + brightness + "%) contrast(" + contrast + "%) saturate(" + saturation + "%) invert(" + invert + ")";
	    bValue.innerHTML = brightness - 100;
			bValueFS.innerHTML = brightness - 100;
			brightnessSliderFS.value = this.value;
	  }

	  contrastSlider.oninput = function() {
	    contrast = (Number(this.value) + 100) * 1;
	    canvas.style.filter = "brightness(" + brightness + "%) contrast(" + contrast + "%) saturate(" + saturation + "%) invert(" + invert + ")";
			canvasFS.style.filter = "brightness(" + brightness + "%) contrast(" + contrast + "%) saturate(" + saturation + "%) invert(" + invert + ")";
	    cValue.innerHTML = Number(this.value);
			cValueFS.innerHTML = Number(this.value);
			contrastSliderFS.value = this.value;
	  }

	  saturationSlider.oninput = function() {
	    saturation = (Number(this.value) + 100) * 1;
	    canvas.style.filter = "brightness(" + brightness + "%) contrast(" + contrast + "%) saturate(" + saturation + "%) invert(" + invert + ")";
			canvasFS.style.filter = "brightness(" + brightness + "%) contrast(" + contrast + "%) saturate(" + saturation + "%) invert(" + invert + ")";
	    sValue.innerHTML = Number(this.value);
			sValueFS.innerHTML = Number(this.value);
			saturationSliderFS.value = this.value;
	  }

		invertSlider.oninput = function() {
			if(this.checked) {
				invert = 1;
				invertSliderFS.checked = true;
			} else {
				invert = 0;
				invertSliderFS.checked = false;
			}
			canvas.style.filter = "brightness(" + brightness + "%) contrast(" + contrast + "%) saturate(" + saturation + "%) invert(" + invert + ")";
			canvasFS.style.filter = "brightness(" + brightness + "%) contrast(" + contrast + "%) saturate(" + saturation + "%) invert(" + invert + ")";
		}

		invertSliderFS.oninput = function() {
			if(this.checked) {
				invert = 1;
				invertSlider.checked = true;
			} else {
				invert = 0;
				invertSlider.checked = false;
			}
			canvas.style.filter = "brightness(" + brightness + "%) contrast(" + contrast + "%) saturate(" + saturation + "%) invert(" + invert + ")";
			canvasFS.style.filter = "brightness(" + brightness + "%) contrast(" + contrast + "%) saturate(" + saturation + "%) invert(" + invert + ")";
		}

		// Update the current slider value (each time you drag the slider handle)
		brightnessSliderFS.oninput = function() {
			brightness = Number(this.value) + 100;
			canvas.style.filter = "brightness(" + brightness + "%) contrast(" + contrast + "%) saturate(" + saturation + "%) invert(" + invert + ")";
			canvasFS.style.filter = "brightness(" + brightness + "%) contrast(" + contrast + "%) saturate(" + saturation + "%) invert(" + invert + ")";
			bValue.innerHTML = brightness - 100;
			bValueFS.innerHTML = brightness - 100;
			brightnessSlider.value = this.value;
		}

		contrastSliderFS.oninput = function() {
			contrast = (Number(this.value) + 100) * 1;
			canvas.style.filter = "brightness(" + brightness + "%) contrast(" + contrast + "%) saturate(" + saturation + "%) invert(" + invert + ")";
			canvasFS.style.filter = "brightness(" + brightness + "%) contrast(" + contrast + "%) saturate(" + saturation + "%) invert(" + invert + ")";
			cValue.innerHTML = Number(this.value);
			cValueFS.innerHTML = Number(this.value);
			contrastSlider.value = this.value;
		}

		saturationSliderFS.oninput = function() {
			saturation = (Number(this.value) + 100) * 1;
			canvas.style.filter = "brightness(" + brightness + "%) contrast(" + contrast + "%) saturate(" + saturation + "%) invert(" + invert + ")";
			canvasFS.style.filter = "brightness(" + brightness + "%) contrast(" + contrast + "%) saturate(" + saturation + "%) invert(" + invert + ")";
			sValue.innerHTML = Number(this.value);
			sValueFS.innerHTML = Number(this.value);
			saturationSlider.value = this.value;
		}

		invertSliderFS.oninput = function() {
			if(this.checked) {
				invert = 1;
				invertSlider.checked = true;
			} else {
				invert = 0;
				invertSlider.checked = false;
			}
			canvas.style.filter = "brightness(" + brightness + "%) contrast(" + contrast + "%) saturate(" + saturation + "%) invert(" + invert + ")";
			canvasFS.style.filter = "brightness(" + brightness + "%) contrast(" + contrast + "%) saturate(" + saturation + "%) invert(" + invert + ")";
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
			canvasFS.style.filter = "brightness(" + 100 + "%) contrast(" + 100 + "%) saturate(" + 100 + "%) invert(0)";
			sValueFS.innerHTML = 0;
			cValueFS.innerHTML = 0;
			bValueFS.innerHTML = 0;
			brightnessSliderFS.value = 0;
			contrastSliderFS.value = 0;
			saturationSliderFS.value = 0;
			invertSliderFS.checked = false;
			brightness = 100;
			contrast = 100;
			saturation = 100;
		});

		jQuery('#openseadragonFS #filterReset').click(function() {
			canvas.style.filter = "brightness(" + 100 + "%) contrast(" + 100 + "%) saturate(" + 100 + "%) invert(0)";
			sValue.innerHTML = 0;
			cValue.innerHTML = 0;
			bValue.innerHTML = 0;
			brightnessSlider.value = 0;
			contrastSlider.value = 0;
			saturationSlider.value = 0;
			invertSlider.checked = false;
			canvasFS.style.filter = "brightness(" + 100 + "%) contrast(" + 100 + "%) saturate(" + 100 + "%) invert(0)";
			sValueFS.innerHTML = 0;
			cValueFS.innerHTML = 0;
			bValueFS.innerHTML = 0;
			brightnessSliderFS.value = 0;
			contrastSliderFS.value = 0;
			saturationSliderFS.value = 0;
			invertSliderFS.checked = false;
			brightness = 100;
			contrast = 100;
			saturation = 100;
		});
	},
	selectionInit = function() {
		selection = osdViewerFS.selection({
			element:                 null, // html element to use for overlay
			showSelectionControl:    false, // show button to toggle selection mode
			toggleButton:            null, // dom element to use as toggle button
			showConfirmDenyButtons:  true,
			styleConfirmDenyButtons: true,
			returnPixelCoordinates:  true,
			keyboardShortcut:        'c', // key to toggle selection mode
			rect:                    null, // initial selection as an OpenSeadragon.SelectionRect object
			allowRotation:           true, // turn selection rotation on or off as needed
			startRotated:            false, // alternative method for drawing the selection; useful for rotated crops
			startRotatedHeight:      0.1, // only used if startRotated=true; value is relative to image height
			restrictToImage:         false, // true = do not allow any part of the selection to be outside the image
			onSelection:             function(rect) {
				var imgSize = osdViewerFS.viewport.imageToViewportRectangle(rect);
				var osdDiv = document.getElementById('openseadragon1');
				var rectnew = document.createElement('div');
				rectnew.classList.add('rect');
				osdViewerFS.addOverlay({
				  element: rectnew,
				  location: new OpenSeadragon.Rect(imgSize.x, imgSize.y, imgSize.width, imgSize.height)
				});
				// add selected image to the form
				var img = document.createElement('img');
				img.src = imageLink + '/' + rect.x + ',' + rect.y + ',' + rect.width + ',' + rect.height + '/250,/0/default.jpg';
				document.getElementById('selection-tab').appendChild(img);
				selection.disable();
			}, // callback
			prefixUrl:               null, // overwrites OpenSeadragon's option
			borderStyle: { // overwriteable style defaults
			  width:      '1px',
			  color:      '#fff'
			},
			handleStyle: {
			  top:        '50%',
			  left:       '50%',
			  width:      '6px',
			  height:     '6px',
			  margin:     '-4px 0 0 -4px',
			  background: '#000',
			  border:     '1px solid #ccc'
			},
			cornersStyle: {
			  width:      '6px',
			  height:     '6px',
			  background: '#000',
			  border:     '1px solid #ccc'
			}
		});
	},
	fullscreenEdit = function() {
	  console.log('show fullscreen Editor');
	  toggleFSEditor('fs_editor_toggle');
	},
	initTiny = function() {
		//none fs ones
		initTinyWithConfig('#full-view-editor #item-page-transcription-text');
		if(document.querySelector('.tox-tinymce')){
			document.querySelector('.tox-tinymce').style.display = 'block';
		}

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
	getOsdViewerFS = function() {
		return osdViewerFS;
	},
	makeImageSelection = function() {
		console.log('making image selection');
		selection.enable();
	},
	initTinyWithConfig = function(selector) {
	  tinymce.init({
	    selector: selector,
	    inline: true,
		fixed_toolbar_container: selector + '#tcttoolbar',
	    height:120,
	    plugins: ['charmap','paste', 'autoresize', 'table'],
	    toolbar: 'bold italic underline strikethrough removeformat | alignleft aligncenter alignright | missbut unsure side-info | charmap | table',
			resize: true,
	    menubar: false,
		browser_spellcheck: true,
		object_resizing : false,
		paste_auto_cleanup_on_paste : true,
		forced_root_block : false,
	    body_id: 'htranscriptor',
			init_instance_callback: function (editor) {

				editor.on('focus', function (e) {
					// jQuery('#mytoolbar-transcription').height('1px');
					console.log('focusing');
				});
		 		// editor.on('blur', function (e) {
				// 	// jQuery('#mytoolbar-transcription').height('1px');
				// });
				editor.on('blur', function(e) {
					if(document.querySelector('.tox-tinymce')){
                        document.querySelector('.tox-tinymce').style.display = 'none';
					}
				});


			},
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
	};
	$(document).ready(function($) {
		if (jQuery('#image-data-holder').length) {
			init();
		}
	});
	return {
		initTinyWithConfig: initTinyWithConfig,
		makeImageSelection: makeImageSelection ,
		getSelection: getSelection,
		getImageLink: getImageLink,
		getOsdViewer: getOsdViewer,
		getOsdViewerFS: getOsdViewerFS
	}
})(jQuery, document, window);
