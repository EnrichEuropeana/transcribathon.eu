var home_url = WP_URLs.home_url;
var network_home_url = WP_URLs.network_home_url;
var map, marker;

jQuery(window).load(function() {
    initializeMap();
});

function uninstallEventListeners() {
    jQuery(".datepicker-input-field").datepicker("destroy");
    tinymce.remove();
    if(map){
        map.resize();
    }
}

function installEventListeners() {
    //initializeMap();
    if(map){ 
        map.resize();
    }

    const noTextSelect = document.querySelector('#no-text-selector');
    if(noTextSelect) {
        document.querySelector('#item-page-transcription-text').addEventListener('keyup', function() {
            if(document.querySelector('#item-page-transcription-text').innerHTML != '' || document.querySelector('#item-page-transcription-text').textContent != '') {
                noTextSelect.style.display = 'none';
            } else {
                noTextSelect.style.display = 'block';
            }
        })
    }
    // Location editor collapse
    const locEditor = document.querySelector('#location-position');
    const locInput = document.querySelector('#location-input-section');
    if(locEditor) {
        locEditor.querySelector('.fa-plus-circle').addEventListener('click', function() {
            if(locInput.style.display == 'none') {
                locInput.style.display = 'block';
            } else {
                locInput.style.display = 'none';
            }
        })
    }
    if(locInput) {
        document.querySelector('#location-name-display input').addEventListener('keyup', function() {
            if(document.querySelector('#location-name-display input').value != ''){
                document.querySelector('#loc-name-check i').style.display = 'block';
            } else {
                document.querySelector('#loc-name-check i').style.display = 'none';
            }
        })
        document.querySelector('#loc-save-lock').addEventListener('click', function() {
          if(document.querySelector('#loc-save-lock i').classList.contains('fa-lock-open')) {
              document.querySelector('#loc-save-lock i').classList.remove('fa-lock-open');
              document.querySelector('#loc-save-lock i').classList.add('fa-lock');
              document.querySelector('#loc-coord').disabled = true;
          } else {
              document.querySelector('#loc-save-lock i').classList.remove('fa-lock');
              document.querySelector('#loc-save-lock i').classList.add('fa-lock-open');
              document.querySelector('#loc-coord').disabled = false;
          }
      })
    }
    // Location clear input button
    const clearLocation = document.querySelector('#clear-loc-input');
    if(clearLocation) {
        clearLocation.addEventListener('click', function() {
            document.querySelector('#location-name-display input').value = '';
            document.querySelector('.location-input-coordinates-container input').value = '';
            document.querySelector('#location-input-geonames-search-container input').value = '';
            document.querySelector('.location-input-description-container textarea').value = '';
        } )
    }
    // show/hide keyword input
    const keywordToggle = document.querySelector('#item-page-keyword-headline');
    if(keywordToggle) {
      keywordToggle.addEventListener('click', function() {
        if(document.querySelector('#keyword-input-container').style.display === 'none') {
          document.querySelector('#keyword-input-container').style.display = 'inline-block';
        } else {
          document.querySelector('#keyword-input-container').style.display = 'none';
        }
      })
    }
    // Add event listener to save all of the tagging
    const saveAlltags = document.querySelector('#save-all-tags');
    if(saveAlltags) {
        saveAlltags.addEventListener('click', function() {
            if(jQuery('#startdateentry').val().length > 0 || jQuery('#enddateentry').val().length > 0) {
                setTimeout(()=>{document.querySelector('#item-date-save-button').click()}, 100);
            }
            if(jQuery('#person-firstName-input').val().length > 0 || jQuery('#person-lastName-input').val().length > 0) {
                setTimeout(()=>{document.querySelector('#save-personinfo-button').click()}, 500);
            }
            if(jQuery('#keyword-input').val().length > 0) {
                setTimeout(()=>{document.querySelector('#keyword-save-button').click()}, 900);
            }
            if(jQuery('#link-input-container .link-url-input input').val().length > 0 || jQuery('#link-input-container .link-description-input textarea').val().length > 0) {
                setTimeout(()=>{document.querySelector('#link-save-button').click()}, 1300);
            }
        });
    }

    // When the user clicks the button(pen on the image viewer), open the login modal
    jQuery('#lock-login').click(function() {
        jQuery('#default-login-container').css('display', 'block');
    })
    
    // When the user clicks on <span> (x), close the modal
    jQuery('.item-login-close').click(function() {
        jQuery('#default-login-container').css('display', 'none');
        if(jQuery('.site-navigation').hasClass("fullscreen")){
            jQuery("nav").removeClass("fullscreen");
            jQuery(".site-navigation").css('display', 'none');
        }
    })

    //Prevent users of editing fields that need logged in user
    jQuery('.login-required').mousedown(function(event) {
        // Checks if document is locked
        if (jQuery('#login').length) {
            event.preventDefault();
            jQuery('#default-login-container').css('display', 'block');
            jQuery(".site-navigation").addClass("fullscreen");
            jQuery(".site-navigation").css('display', 'block');
        }
    })

    jQuery('#mce-wrapper-transcription').mousedown(function(event) {
        // Checks if document is locked
        if (jQuery('#transcribeLock').length) {
            event.preventDefault();
            lockWarning();
        }
    })

    jQuery('#item-page-description-text').mousedown(function(event) {
        // Checks if document is locked
        if (jQuery('#transcribeLock').length) {
            event.preventDefault();
            lockWarning();
        }
    })
    ////
    jQuery('.edit-item-data-icon').mousedown(function(event) {
        // Checks if document is locked
        if (jQuery('#transcribeLock').length) {
            event.preventDefault();
            lockWarning();
        }
    })

    var options = document.getElementsByClassName('selected-option');
    for (var i = 0; i < options.length; i++) {
        options[i].addEventListener("click", function(e) {
            /*when an item is clicked, update the original select box,
            and the selected item:*/
            var y, i, k, s, h;
            s = this.parentNode.parentNode.getElementsByTagName("select")[0];
            h = this.parentNode.previousSibling;
            for (i = 0; i < s.length; i++) {
                if (s.options[i].innerHTML == this.innerHTML) {
                    s.selectedIndex = i;
                    h.innerHTML = this.innerHTML;
                    y = this.parentNode.getElementsByClassName("same-as-selected");
                    for (k = 0; k < y.length; k++) {
                        y[k].removeAttribute("class");
                    }
                    this.setAttribute("class", "same-as-selected");
                    break;
                }
            }
            h.click();
        });
    }

    var selectors = document.getElementsByClassName('language-select-selected');
    for (var i = 0; i < selectors.length; i++) {
        selectors[i].addEventListener("click", function(e) {
            /*when the select box is clicked, close any other select boxes,
            and open/close the current select box:*/
            e.stopPropagation();
            closeAllSelect(this);
            this.nextSibling.classList.toggle("select-hide");
            this.classList.toggle("select-arrow-active");
        });
    }

    /*if the user clicks anywhere outside the select box,
    then close all select boxes:*/
    document.addEventListener("click", closeAllSelect, false);

    jQuery('.edit-item-date').click(function() {
        if(jQuery('#transcribeLock').length) {
            event.preventDefault();
            lockWarning();
        } else {
            jQuery(this).parent('.item-date-display-container').css('display', 'none');
            jQuery(this).parent('.item-date-display-container').siblings('.item-date-input-container').css('display', 'inline-block');
        }
    })

//   const itemPageKeyWords = document.querySelector('#keyword-input');
//   let flag = true;
//   var keyWordList = [];
//   if(itemPageKeyWords && flag){
//   jQuery.post(home_url + '/wp-content/themes/transcribathon/admin/inc/custom_scripts/send_ajax_api_request.php', {
//       'type': 'GET',
//       'url': TP_API_HOST + '/tp-api/properties?PropertyType=Keyword'
//   },
//   function(response) {
   
//     var response = JSON.parse(response);
//     var content = JSON.parse(response.content);
//     for (var i = 0; i < content.length; i++) {
//       keyWordList.push(content[i]['PropertyValue']);
//     }
//     jQuery( "#keyword-input" ).autocomplete({
//       source: keyWordList,
//       delay: 100,
//       minLength: 1
//     });
//     console.log(flag);
//     console.log(keyWordList);
//     flag = false;
//     console.log(flag);
//   });
//   }

    // New transcription langauge selected
    jQuery('#transcription-language-custom-selector').siblings('.language-item-select').children('.selected-option').click(function(){
        jQuery('#no-text-selector').css('display','none');
        jQuery('#transcription-selected-languages ul').append(
            '<li class="selected-lang">'
              + jQuery('#transcription-language-selector option:selected').text()
              + '<i class="far fa-times" onClick="removeTranscriptionLanguage(' + jQuery('#transcription-language-selector option:selected').val() + ', this)"></i>'
            + '</li>');
        jQuery('#transcription-language-selector option:selected').prop("disabled", true);
        var transcriptionText = jQuery('#item-page-transcription-text').text();
        if(transcriptionText.length != 0) {
            jQuery('#transcription-update-button').addClass('theme-color-background');
            jQuery('#transcription-update-button').prop('disabled', false);
            jQuery('#transcription-update-button .language-tooltip-text').css('display', 'none');
            jQuery('#no-text-selector').css('display', 'none');
        }
    })

    jQuery('#description-area textarea').keyup(function() {
        var text = jQuery(this).val();
        var language = jQuery('#description-language-selector select').val();
        if(text.length == 0) {
            jQuery('#description-update-button').css('display','none');
        } else {
            jQuery('#description-update-button').css('display','block');
            if (language != null) {
                jQuery('#description-update-button').addClass('theme-color-background');
                jQuery('#description-update-button').prop('disabled', false);
                jQuery('#description-update-button .language-tooltip-text').css('display', 'none');
            }
        }
    });

    jQuery('#description-language-custom-selector').siblings('.language-item-select').children('.selected-option').click(function(){
        var text = jQuery('#description-area textarea').val();
        if(text.length == 0) {
            jQuery('#description-update-button').css('display','none');
        } else {
            jQuery('#description-update-button').css('display','block');
            jQuery('#description-update-button').addClass('theme-color-background');
            jQuery('#description-update-button .language-tooltip-text').css('display', 'none');
            jQuery('#description-update-button').prop('disabled', false);
        }
    });

    // Show/Hide Transcription Save button
    jQuery('#item-page-transcription-text').keyup(function() {
        jQuery('#no-text-selector').css('display','none');
        var transcriptionText = jQuery('#item-page-transcription-text').text();
        var languages = jQuery('#transcription-selected-languages ul').children().length;
        if(transcriptionText.length != 0) {
            jQuery('#transcription-update-button').css('display', 'block');
            jQuery('#no-text-selector').css('display', 'none');
            if (languages > 0) {
                jQuery('#transcription-update-button').addClass('theme-color-background');
                jQuery('#transcription-update-button').prop('disabled', false);
                jQuery('#transcription-update-button .language-tooltip-text').css('display', 'none');
            }
        } else {
            jQuery('#transcription-update-button').css('display', 'none');
        }
        if(transcriptionText.length == 0 && languages == 0) {
            jQuery('#no-text-selector').css('display','block');
        }
    });

    jQuery('#no-text-selector input').click(function(event) {
        var checked = this.checked;
        var transcriptionText = jQuery('#item-page-transcription-text').text();
        if (checked == true) {
            if(transcriptionText.length == 0) {
                jQuery('#transcription-language-custom-selector select').attr("disabled", "disabled");
                jQuery('#transcription-language-custom-selector select').addClass("disabled-dropdown");
                tinymce.remove();
                jQuery('#transcription-update-button').addClass('theme-color-background');
                jQuery('#transcription-update-button').prop('disabled', false);
                jQuery('#transcription-update-button .language-tooltip-text').css('display', 'none');
                jQuery('#transcription-update-button').css('display', 'block');
            } else {
                alert("Please remove the transcription text first, if the document has nothing to transcribe");
                event.preventDefault();
                event.stopPropagation();
            }
        } else {
            jQuery('#transcription-language-selector select').removeAttr("disabled");
            jQuery('#transcription-language-selector select').removeClass("disabled-dropdown");
            tct_viewer.initTinyWithConfig('#item-page-transcription-text');
            setToolbarHeight();
            jQuery('#transcription-update-button').removeClass('theme-color-background');
            jQuery('#transcription-update-button').prop('disabled', true);
            jQuery('#transcription-update-button .language-tooltip-text').css('display', 'block');
        }
    })

    var startDate = jQuery("#startdateentry").val();
    var endDate = jQuery("#enddateentry").val();
    var birthDate = jQuery("#person-birthDate-input").val();
    var deathDate = jQuery("#person-deathDate-input").val();

    jQuery( ".datepicker-input-field" ).datepicker({
        dateFormat: "dd/mm/yy",
        changeMonth: true,
        changeYear: true,
        yearRange: "100:+10",
        showOn: "button",
        buttonImage: `${home_url}/public_html/wp-content/themes/transcribathon/admin/inc/custom_shortcodes/upload-images/icon_calendar.svg`
    });
    jQuery("#startdateentry").val(startDate);
    jQuery("#enddateentry").val(endDate);

    jQuery( "#person-birthDate-input, #person-deathDate-input" ).datepicker({
        dateFormat: "dd/mm/yy",
        changeMonth: true,
        changeYear: true,
        yearRange: "100:+10",
        showOn: "button",
        buttonImage:  `${home_url}/public_html/wp-content/themes/transcribathon/admin/inc/custom_shortcodes/upload-images/icon_calendar.svg`
    });
    jQuery("#person-birthDate-input").val(birthDate);
    jQuery("#person-deathDate-input").val(deathDate);

    if(document.querySelector('#item-page-transcription-text')) {
        tct_viewer.initTinyWithConfig('#item-page-transcription-text');
        setToolbarHeight();
    }

} // End of event listeners

function closeAllSelect(elmnt) {
    /*a function that will close all select boxes in the document,
    except the current select box:*/
    var x, y, i, arrNo = [];
    x = document.getElementsByClassName("language-item-select");
    y = document.getElementsByClassName("language-select-selected");
    for (i = 0; i < y.length; i++) {
        if (elmnt == y[i]) {
            arrNo.push(i)
        } else {
            y[i].classList.remove("select-arrow-active");
        }
    }
    for (i = 0; i < x.length; i++) {
        if (arrNo.indexOf(i)) {
            x[i].classList.add("select-hide");
        }
    }
}

// Switches between different tabs within the item page image view
function switchItemTab(event, tabName) {
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
    document.getElementById(tabName).style.display = "block";
    if(event.currentTarget) {
      event.currentTarget.className += " active";
    }
    
}

// View switch in full screen mode 
// TODO CLEAN THIS UP!
function switchItemView(event, viewName) {
    // Transcription Language selection
    const langHoldContainer = document.querySelector('#popout-language-holder'); // Container to add langauges to when switching view
    const langContainer = document.querySelector('.transcription-mini-metadata'); // Container with language selector
    const panelRContainer = document.querySelector('#transcription-edit-container'); // Language and Save button holder on default view & edit tr container
    const langCstmSelect = document.querySelector('#transcription-language-custom-selector'); // Used to chanage its text content in different views
    // Used to 'Remove' view only if it's not default tab
    const transcriptionView = document.querySelector('#transcription-view-container');
    // Make tab icons inactive
    icons = document.getElementsByClassName("view-switcher-icons");
    for (i = 0; i < icons.length; i++) {
        icons[i].className = icons[i].className.replace(" active", "");
        icons[i].className = icons[i].className.replace(" theme-color", "");
    }
    // Make icon active
    event.currentTarget.className += " active";
    event.currentTarget.className += " theme-color";
    switch(viewName) {
        case 'horizontal':
        //Popup test
            if(panelRContainer.querySelector('.transcription-mini-metadata') == null) {
                panelRContainer.appendChild(langContainer);
                langCstmSelect.textContent = 'Language(s) of the Document:';
            }

            jQuery("#item-image-section").css("width", '')
            jQuery("#item-image-section").css("height", '')
            jQuery("#image-view-container").removeClass("panel-container-vertical")
            jQuery("#image-view-container").addClass("panel-container-horizontal")
            jQuery("#item-image-section").removeClass("panel-top")
            jQuery("#item-image-section").removeClass("image-popout")
            jQuery("#item-image-section").addClass("panel-left")
            jQuery("#item-data-section").removeClass("panel-bottom")
            jQuery("#item-data-section").removeClass("data-popout")
            jQuery("#item-data-section").removeClass("data-closed")
            jQuery("#item-data-section").addClass("panel-right")
            jQuery("#item-splitter").removeClass("splitter-horizontal")
            jQuery("#item-splitter").addClass("splitter-vertical")
            jQuery("#item-data-section").draggable({ handle: "#item-data-header" })
            jQuery("#item-data-section").draggable('disable')
            jQuery( "#item-data-section" ).resizable()
            jQuery( "#item-data-section" ).resizable('disable')
            jQuery( "#item-data-section" ).removeClass("ui-resizable")
            jQuery( ".ui-resizable-handle" ).css("display", "none")
            jQuery("#location-common-map").addClass("full-map-container")
      
            jQuery("#item-image-section").resizable_split({
                handleSelector: "#item-splitter",
                resizeHeight: false,
                resizeWidth: true,
                // testing tinymce toolbar bugfix
                onDragStart: function( event, ui ) {
              tinymce.activeEditor.fire('blur');
            }
            });
      
            jQuery("#item-data-section").css("top", "")
            jQuery("#item-data-section").css("left", "")
            jQuery("#item-data-section").css("position", "relative")
      
            jQuery("#item-data-content").css("display", 'block')
            jQuery("#item-tab-list").css("display", 'block')
            jQuery("#item-status-doughnut-chart").css("display", 'block')
            break;
        case 'vertical':
            //Popup test
            if(langHoldContainer.querySelector('.transcription-mini-metadat') == null) {
                langHoldContainer.appendChild(langContainer);
                langCstmSelect.textContent = 'Language(s):';
            }
            if(transcriptionView.style.display == 'block') {
                transcriptionView.style.display = 'none';
                panelRContainer.style.display = 'block';
            }

  
            jQuery("#item-image-section").css("width", '')
            jQuery("#item-image-section").css("height", '')
            jQuery("#image-view-container").removeClass("panel-container-horizontal")
            jQuery("#image-view-container").addClass("panel-container-vertical")
            jQuery("#item-image-section").removeClass("panel-left")
            jQuery("#item-image-section").removeClass("image-popout")
            jQuery("#item-image-section").addClass("panel-top")
            jQuery("#item-data-section").removeClass("panel-right")
            jQuery("#item-data-section").removeClass("data-popout")
            jQuery("#item-data-section").removeClass("data-closed")
            jQuery("#item-data-section").addClass("panel-bottom")
            jQuery("#item-splitter").removeClass("splitter-vertical")
            jQuery("#item-splitter").addClass("splitter-horizontal")
            jQuery("#item-data-section").draggable({ handle: "#item-data-header" })
            jQuery("#item-data-section").draggable('disable')
            jQuery( "#item-data-section" ).resizable()
            jQuery( "#item-data-section" ).resizable('disable')
            jQuery( "#item-data-section" ).removeClass("ui-resizable")
            jQuery( ".ui-resizable-handle" ).css("display", "none")
      
            jQuery("#item-image-section").resizable_split({
                handleSelector: "#item-splitter",
                resizeHeight: true,
                resizeWidth: false,
                onDragStart: function( event, ui ) {
                    tinymce.activeEditor.fire('blur');
                  }
            });
            jQuery("#item-data-section").css("top", "")
            jQuery("#item-data-section").css("left", "")
            jQuery("#item-data-section").css("position", "relative")
      
            jQuery("#item-data-content").css("display", 'block')
            jQuery("#item-tab-list").css("display", 'block')
            
            break;
        case 'popout':
            if(langHoldContainer.querySelector('.transcription-mini-metadat') == null) {
                langHoldContainer.appendChild(langContainer);
                langCstmSelect.textContent = 'Language(s):';
            }
            if(transcriptionView.style.display == 'block') {
                transcriptionView.style.display = 'none';
                panelRContainer.style.display = 'block';
            }

  
            jQuery("#item-image-section").css("width", '100%')
            jQuery("#item-image-section").css("height", '100%')
            jQuery("#item-image-section").addClass("image-popout")
            jQuery("#item-data-section").addClass("data-popout")
            jQuery("#image-view-container").removeClass("panel-container-horizontal")
            jQuery("#image-view-container").removeClass("panel-container-vertical")
            jQuery("#item-image-section").removeClass("panel-left")
            jQuery("#item-image-section").removeClass("panel-top")
            jQuery("#item-data-section").removeClass("panel-right")
            jQuery("#item-data-section").removeClass("panel-bottom")
            jQuery("#item-data-section").removeClass("data-closed")
            jQuery("#item-splitter").removeClass("splitter-vertical")
            jQuery("#item-splitter").removeClass("splitter-horizontal")
            jQuery( "#item-data-section" ).resizable({
               handles: "n, e, s, w, se, ne, sw, nw" ,
               resize: function(event, ui) {
              }
            })
            jQuery("#item-data-section").draggable({ handle: "#item-data-header" })
            jQuery("#item-data-section").draggable('enable')
            jQuery( "#item-data-section" ).resizable()
            jQuery( "#item-data-section" ).resizable('enable')
            const resizeHandles = document.querySelectorAll('.ui-resizable-handle');
            if(resizeHandles){
              for(let handle of resizeHandles){
                  handle.addEventListener('mouseover', function() {
                      document.querySelector('#item-page-transcription-text').blur();
                      map.resize();
                  })
              }
            }
      
            jQuery("#item-data-content").css("display", 'block')
            jQuery("#item-tab-list").css("display", 'block')
      
            break;
        case 'closewindow':
  
            jQuery("#item-image-section").css("width", '100%')
            jQuery("#item-image-section").css("height", '100%')
            jQuery("#item-data-section").css("width", '')
            jQuery("#item-data-section").css("height", '')
            jQuery("#item-data-section").css("left", '')
            jQuery("#item-data-section").css("bottom", '')
            jQuery("#item-data-section").css("top", '')
            jQuery("#item-data-section").css("right", '')
            jQuery("#item-image-section").addClass("image-popout")
            jQuery("#item-data-section").addClass("data-closed")
            jQuery("#image-view-container").removeClass("panel-container-horizontal")
            jQuery("#image-view-container").removeClass("panel-container-vertical")
            jQuery("#item-image-section").removeClass("panel-left")
            jQuery("#item-image-section").removeClass("panel-top")
            jQuery("#item-data-section").removeClass("panel-right")
            jQuery("#item-data-section").removeClass("panel-bottom")
            jQuery("#item-data-section").removeClass("data-popout")
            jQuery("#item-splitter").removeClass("splitter-vertical")
            jQuery("#item-splitter").removeClass("splitter-horizontal")
            jQuery( "#item-data-section" ).resizable({ handles: "n, e, s, w, se, ne, sw, nw" })
            jQuery("#item-data-section").draggable({ handle: "#item-data-header" })
            jQuery("#item-data-section").draggable('disable')
            jQuery( "#item-data-section" ).resizable()
            jQuery( "#item-data-section" ).resizable('disable')
            jQuery( "#item-data-section" ).removeClass("ui-resizable")
            jQuery( ".ui-resizable-handle" ).css("display", "none")
      
            jQuery("#item-data-content").css("display", 'none')
            jQuery("#item-tab-list").css("display", 'none')
            break;
    }
}

// Compares two transcriptions to highlight changes
function compareTranscription(oldTranscription, newTranscription, index) {
    var dmp = new diff_match_patch();
    var text1 = oldTranscription;
    var text2 = newTranscription;
    // Compare transcriptions
    var d = dmp.diff_main(text1, text2);
    // Highlight changes
    dmp.diff_cleanupSemantic(d);
    var ds = dmp.diff_prettyHtml(d);
    jQuery("#transcription-comparison-output-" + index).html(ds);
}

// Full screen toggle
function switchItemPageView() {
    uninstallEventListeners();

    let mapMap = document.getElementById('full-view-map');
    let fsMapContainer = document.getElementById('full-screen-map-placeholder');
    let normalMapContainer = document.getElementById('normal-map');
    if(mapMap != null) {
      if(fsMapContainer.querySelector('#full-view-map') != null) {
        normalMapContainer.appendChild(mapMap);
      } else {
        fsMapContainer.appendChild(mapMap);
      }
    }

 
   if (jQuery('#full-view-container').css('display') == 'block') {
     var descriptionText = jQuery('#item-page-description-text').val();
     var descriptionLanguage = jQuery('#description-language-selector select').val();
 
     //
     const fSContainer = document.querySelector('#item-image-section');
     const imgViewer = document.querySelector('#openseadragon');
     const nSContainer = document.querySelector('#full-view-l'); // out of full screen
     //
     //switch to image view
     imgViewer.style.height = '100vh';
     
     fSContainer.appendChild(imgViewer);
     jQuery('.site-footer').css('display', 'none')
     jQuery('#full-view-container').css('display', 'none')
     jQuery('#image-view-container').css('display', 'flex')
     jQuery('.full-container').css('position', 'static')
     jQuery('#item-view-switcher').css('position', 'absolute')
     jQuery('#item-view-switcher').css('z-index', '9999991')
     jQuery('#item-view-switcher').css('left', '50%')
     jQuery('#item-view-switcher').css('top', '0')
     jQuery('._tct_footer').css('display', 'none')
     jQuery('#image-slider-section').css('display', 'none')
     jQuery('#title-n-progress').css('display', 'none')
     jQuery('#viewer-n-transcription').css('display', 'none')
     jQuery('#location-n-enrichments').css('display', 'none')
     jQuery('#story-info').css('display', 'none')
     jQuery('.main-navigation').css('display', 'none')
     jQuery('#wpadminbar').css('display', 'none')
 
      // Open full screen if users refreshes page while in full screen mode
      let urlLocation = new URL(window.location);
      let urlParameter = new URLSearchParams(urlLocation.search);
      if(!urlParameter.has('fs')){
          urlParameter.append('fs', true);
      }
      let newUrl = '?' + urlParameter.toString()
      console.log(urlParameter.toString());
      window.history.replaceState(null, null, newUrl);
      document.querySelector('#full-width').click();
 
   } else {
     var descriptionText = jQuery('#item-page-description-text').val();
     var descriptionLanguage = jQuery('#description-language-selector select').val();

     const imgViewer = document.querySelector('#openseadragon');
     const nSContainer = document.querySelector('#full-view-l'); // out of full screen
     // Move viewer
     imgViewer.style.height = '600px';
     nSContainer.appendChild(imgViewer);
     //switch to full view
     jQuery('.site-footer').css('display', 'block')
     jQuery('.item-page-slider').css('visibility', 'unset')
     jQuery('#full-view-container').css('display', 'block')
     jQuery('#image-view-container').css('display', 'none')
     jQuery('.full-container').css('position', 'relative')
     jQuery('._tct_footer').css('display', 'block')
     jQuery('.main-navigation').css('display', 'block')
     jQuery('#wpadminbar').css('display', 'block')
     jQuery('#image-slider-section').css('display', 'block')
     jQuery('#title-n-progress').css('display', 'block')
     jQuery('#viewer-n-transcription').css('display', 'block')
     jQuery('#location-n-enrichments').css('display', 'block')
     jQuery('#story-info').css('display', 'block')

    // Open full screen if users refreshes page while in full screen mode
    const urlLocation = new URL(window.location);
    const urlParameter = new URLSearchParams(urlLocation.search);
    urlParameter.delete('fs');
    let newUrl = '?' + urlParameter.toString()
    console.log(urlParameter.toString());
    window.history.replaceState(null, null, newUrl);
    // console.log(document.location.pathname);
 
   }
    installEventListeners();
}

// Updates specified data over the API
function updateDataProperty(dataType, id, fieldName, value) {
    // Prepare data and send API request
    data = {
        };
    data[fieldName] = value;
  
    var dataString= JSON.stringify(data);
    jQuery.post(home_url + '/wp-content/themes/transcribathon/admin/inc/custom_scripts/send_ajax_api_request.php', {
        'type': 'POST',
        'url': TP_API_HOST + '/tp-api/' + dataType + '/' + id,
        'data': data
    },
    // Check success and create confirmation message
    function(response) {
        var response = JSON.parse(response);
        if (response.code == "200") {
            return 1;
        } else {
            alert(response.content);
        }
    });
}

// Updates the item description
function updateItemDescription(itemId, userId, editStatusColor, statusCount) {
    jQuery('#item-description-spinner-container').css('display', 'block')
  
    var descriptionLanguage = jQuery('#description-language-selector select').val();
    updateDataProperty('items', itemId, 'DescriptionLanguage', descriptionLanguage);
  
    var description = jQuery('#item-page-description-text').val()
  
    // Prepare data and send API requestI
    data = {
              Description: description
            }
    var dataString= JSON.stringify(data);
  
      jQuery.post(home_url + '/wp-content/themes/transcribathon/admin/inc/custom_scripts/send_ajax_api_request.php', {
          'type': 'GET',
          'url': TP_API_HOST + '/tp-api/items/' + itemId
      },
      function(response) {
          // Check success and create confirmation message
          var response = JSON.parse(response);
      var descriptionCompletion = JSON.parse(response.content)["DescriptionStatusName"];
      var oldDescription = JSON.parse(response.content)["Description"];
  
      jQuery.post(home_url + '/wp-content/themes/transcribathon/admin/inc/custom_scripts/send_ajax_api_request.php', {
        'type': 'POST',
        'url': TP_API_HOST + '/tp-api/items/' + itemId,
        'data': data
      },
      // Check success and create confirmation message
      function(response) {
        /*
        if (oldDescription != null) {
          var amount = description.length - oldDescription.length;
        }
        else {
          var amount = description.length;
        }
        if (amount > 0) {
          amount = amount + 10;
        }
        else {
          amount = 10;
        }*/
        amount = 1;
  
        scoreData = {
                      ItemId: itemId,
                      UserId: userId,
                      ScoreType: "Description",
                      Amount: amount
                    }
        jQuery.post(home_url + '/wp-content/themes/transcribathon/admin/inc/custom_scripts/send_ajax_api_request.php', {
            'type': 'POST',
            'url': TP_API_HOST + '/tp-api/scores',
            'data': scoreData
        },
        // Check success and create confirmation message
        function(response) {
          console.log(response);
        })
        var response = JSON.parse(response);
        if (response.code == "200") {
          if (descriptionCompletion == "Not Started") {
            changeStatus(itemId, "Not Started", "Edit", "DescriptionStatusId", 2, editStatusColor, statusCount)
          }
          jQuery('#description-update-button').css('display', 'none')
        }
        jQuery('#item-description-spinner-container').css('display', 'none')
      });
      });
}

// Updates the item transcription
function updateItemTranscription(itemId, userId, editStatusColor, statusCount) {
  jQuery('#transcription-update-button').removeClass('theme-color-background');
  jQuery('#transcription-update-button').prop('disabled', true);
  jQuery('#item-transcription-spinner-container').css('display', 'block')

  // Get languages
  var transcriptionLanguages = [];
  jQuery("#transcription-language-selector option").each(function() {
    var nextLanguage = {};
    if (jQuery(this).prop('disabled') == true && jQuery(this).val() != "") {
      nextLanguage.LanguageId = jQuery(this).val();
      transcriptionLanguages.push(nextLanguage);
    }
  });
  var noText = 0;
  if (jQuery('#no-text-checkbox').is(':checked')) {
    noText = 1
  }

  jQuery.post(home_url + '/wp-content/themes/transcribathon/admin/inc/custom_scripts/send_ajax_api_request.php', {
    'type': 'GET',
    'url': TP_API_HOST + '/tp-api/items/' + itemId
  },
    function(response) {
      var response = JSON.parse(response);
      var itemCompletion = JSON.parse(response.content)["CompletionStatusName"];
      var transcriptionCompletion = JSON.parse(response.content)["TranscriptionStatusName"];
      var currentTranscription = "";
    
      console.log(JSON.parse(response.content)["Transcriptions"]);
      for (var i = 0; i < JSON.parse(response.content)["Transcriptions"].length; i++) {
        if (JSON.parse(response.content)["Transcriptions"][i]["CurrentVersion"] == 1) {
          currentTranscription = JSON.parse(response.content)["Transcriptions"][i]["TextNoTags"];
        }
      }
      console.log(currentTranscription);
      
      const wordcount = tinymce.get('item-page-transcription-text').plugins.wordcount;
      // var newTranscriptionLength = tinyMCE.editors[jQuery('#item-page-transcription-text').attr('id')].getContent({format : 'text'}).length;
      // var newTranscriptionLength = tinyMCE.editors.get([jQuery('#item-page-transcription-text').attr('id')]).getContent({format : 'text'}).length;
      if(jQuery('#item-page-transcription-text').text()) {
        var newTranscriptionLength = wordcount.body.getCharacterCountWithoutSpaces();
      }     
      console.log(newTranscriptionLength);
      // Prepare data and send API request
      data = {
          UserId: userId,
          ItemId: itemId,
          CurrentVersion: 1,
          NoText: noText,
          Languages: transcriptionLanguages,
          }
      
      if (jQuery('#item-page-transcription-text').html()) {
        data['Text'] = tinymce.get('item-page-transcription-text').getContent({format : 'html'}).replace(/'/g, "\\'");
        data['TextNoTags'] = tinymce.get('item-page-transcription-text').getContent({format : 'text'}).replace(/'/g, "\\'");
        
      }
      else {
        data['Text'] = "";
        data['TextNoTags'] = "";
      }
      
      var dataString= JSON.stringify(data);
      
      jQuery.post(home_url + '/wp-content/themes/transcribathon/admin/inc/custom_scripts/send_ajax_api_request.php', {
        'type': 'POST',
        'url': TP_API_HOST + '/tp-api/transcriptions',
        'data': data
      },
      // Check success and create confirmation message
      function(response) {
        
        var amount = newTranscriptionLength - currentTranscription.length
        if (amount > 0) {
          amount = amount;
        }
        else { 
          amount = 0;
        }
        console.log('ammount' + amount);
        scoreData = {
                      ItemId: itemId,
                      UserId: userId,
                      ScoreType: "Transcription",
                      Amount: amount
                    }
        jQuery.post(home_url + '/wp-content/themes/transcribathon/admin/inc/custom_scripts/send_ajax_api_request.php', {
            'type': 'POST',
            'url': TP_API_HOST + '/tp-api/scores',
            'data': scoreData
        },
        // Check success and create confirmation message
        function(response) {
        })
        updateSolr();

        var response = JSON.parse(response);
        if (response.code == "200") {
          if (itemCompletion == "Not Started") {
            changeStatus(itemId, "Not Started", "Edit", "CompletionStatusId", 2, editStatusColor, statusCount)
          }
          if (transcriptionCompletion == "Not Started") {
            changeStatus(itemId, "Not Started", "Edit", "TranscriptionStatusId", 2, editStatusColor, statusCount)
          }
        }
        jQuery('#item-transcription-spinner-container').css('display', 'none')
      });
    });
  
}

// Adds an Item Property
function addItemProperty(itemId, userId, type, editStatusColor, statusCount, propertyValue, e) {
    if (jQuery('#type-' + propertyValue + '-checkbox').is(':checked')) {
      jQuery('#type-' + propertyValue + '-checkbox').attr("checked", true);
    }
    else {
      jQuery('#type-' + propertyValue + '-checkbox').attr("checked", false);
    }
    // Prepare data and send API request
    propertyId = e.value;
    data = {
              ItemId: itemId,
              PropertyId: propertyId,
              UserGenerated: 1
            }
    var dataString= JSON.stringify(data);
    if (e.checked) {
      jQuery.post(home_url + '/wp-content/themes/transcribathon/admin/inc/custom_scripts/send_ajax_api_request.php', {
          'type': 'POST',
          'url': TP_API_HOST + '/tp-api/itemProperties',
          'data': data
      },
      function(response) {
      });
    }
    else {
      jQuery.post(home_url + '/wp-content/themes/transcribathon/admin/inc/custom_scripts/send_ajax_api_request.php', {
          'type': 'GET',
          'url': TP_API_HOST + '/tp-api/itemProperties?ItemId=' + itemId + '&PropertyId=' + propertyId,
      },
      // Check success and create confirmation message
      function(response) {
        var response = JSON.parse(response);
        if (response.code == "200") {
          var itemPropertyId = JSON.parse(response.content)[0]['ItemPropertyId'];
          jQuery.post(home_url + '/wp-content/themes/transcribathon/admin/inc/custom_scripts/send_ajax_api_request.php', {
            'type': 'DELETE',
            'url': TP_API_HOST + '/tp-api/itemProperties/' + itemPropertyId
          },
          // Check success and create confirmation message
          function(response) {
          });
        }
        else {
          alert(response.content);
        }
      });
    }
    if (type == "category") {
      jQuery.post(home_url + '/wp-content/themes/transcribathon/admin/inc/custom_scripts/send_ajax_api_request.php', {
        'type': 'GET',
        'url': TP_API_HOST + '/tp-api/items/' + itemId
      },
      function(response) {
        // Check success and create confirmation message
        var response = JSON.parse(response);
        var descriptionCompletion = JSON.parse(response.content)["DescriptionStatusName"];
        if (descriptionCompletion == "Not Started") {
          changeStatus(itemId, "Not Started", "Edit", "DescriptionStatusId", 2, editStatusColor, statusCount)
        }
      })
    }
}
// Change progress status
function changeStatus (itemId, oldStatus, newStatus, fieldName, value, color, statusCount, e) {
    jQuery('#' + fieldName.replace("StatusId", "").toLowerCase() + '-status-changer').css('background-color', color);
    jQuery('#' + fieldName.replace("StatusId", "").toLowerCase() + '-status-indicator').text(newStatus);
  
    if (fieldName != "CompletionStatusId") {
      if (oldStatus == null) {
        jQuery.post(home_url + '/wp-content/themes/transcribathon/admin/inc/custom_scripts/send_ajax_api_request.php', {
          'type': 'GET',
          'url': TP_API_HOST + '/tp-api/items/' + itemId
        },
        // Check success and create confirmation message
        function(response) {
          var response = JSON.parse(response);
          if (response.code == "200") {
            var content = JSON.parse(response.content);
  
            oldStatus = content[fieldName.replace("Id", "Name")];
            updateDataProperty("items", itemId , fieldName, value);
          }
          else {
            alert(response.content);
            return 0;
          }
        });
      }
      else {
            updateDataProperty("items", itemId , fieldName, value);
      }
    }
    else {
      updateDataProperty("items", itemId , fieldName, value);
      jQuery('.status-dropdown-content').removeClass('show')
    }
    updateSolr();
}

function removeTranscriptionLanguage(languageId, e) {
    jQuery("#transcription-language-selector option[value='" + languageId + "']").prop("disabled", false)
    jQuery("#transcription-language-selector select").val("")
    jQuery(e.closest("li")).remove()
    var transcriptionText = jQuery('#item-page-transcription-text').text();
    var languages = jQuery('#transcription-selected-languages ul').children().length;
    if(transcriptionText.length != 0 && languages > 0) {
      jQuery('#transcription-update-button').addClass('theme-color-background');
      jQuery('#transcription-update-button').prop('disabled', false);
      jQuery('#transcription-update-button .language-tooltip-text').css('display', 'none');
      jQuery('#no-text-selector').css('display', 'none');
    }
    else {
      jQuery('#transcription-update-button').removeClass('theme-color-background');
      jQuery('#transcription-update-button').prop('disabled', true);
      jQuery('#transcription-update-button .language-tooltip-text').css('display', 'block');
    }
    if(transcriptionText.length == 0 && languages == 0) {
      jQuery('#no-text-selector').css('display','block');
    }
}


function saveItemLocation(itemId, userId, editStatusColor, statusCount) {
    jQuery('#item-location-spinner-container').css('display', 'block')
    // Prepare data and send API request
    locationName = jQuery('#location-name-display input').val();
    [latitude, longitude] = jQuery('#location-input-section .location-input-coordinates-container input').val().split(',');
    if (latitude != null) {
      latitude = latitude.trim();
    }
    if (longitude != null) {
      longitude = longitude.trim();
    }
    if (isNaN(latitude) || isNaN(longitude)) {
      jQuery('#location-input-section .location-input-coordinates-container span').css('display', 'block');
      jQuery('#item-location-spinner-container').css('display', 'none')
      return 0;
    }
    if (locationName == null || locationName == "") {
      jQuery('#location-name-display span').css('display', 'block');
      jQuery('#item-location-spinner-container').css('display', 'none')
      return 0;
    }
  
    if (jQuery('#location-input-section .location-input-name-container input').val() == "") {
      jQuery('#location-input-section .location-input-name-container span').css('display', 'block');
      jQuery('#item-location-spinner-container').css('display', 'none')
      return 0;
    }
  
    description = jQuery('#location-input-section .location-input-description-container textarea').val();
    wikidata = jQuery('#location-input-geonames-search-container > input').val().split(";");
  
    jQuery.post(home_url + '/wp-content/themes/transcribathon/admin/inc/custom_scripts/send_ajax_api_request.php', {
      'type': 'GET',
      'url': TP_API_HOST + '/tp-api/items/' + itemId
    },
      function(response) {
        var response = JSON.parse(response);
        var locationCompletion = JSON.parse(response.content)["LocationStatusName"];
        var data = {
                  Name: locationName,
                  Latitude: latitude,
                  Longitude: longitude,
                  ItemId: itemId,
                  Link: "",
                  Zoom: 10,
                  Comment: description,
                  WikidataName: wikidata[0],
                  WikidataId: wikidata[1],
                  UserId: userId,
                  UserGenerated: 1
                }
        jQuery.post(home_url + '/wp-content/themes/transcribathon/admin/inc/custom_scripts/send_ajax_api_request.php', {
            'type': 'POST',
            'url': TP_API_HOST + '/tp-api/places',
            'data': data
        },
        // Check success and create confirmation message
        function(response) {
          scoreData = {
                        ItemId: itemId,
                        UserId: userId,
                        ScoreType: "Location",
                        Amount: 1
                      }
          jQuery.post(home_url + '/wp-content/themes/transcribathon/admin/inc/custom_scripts/send_ajax_api_request.php', {
              'type': 'POST',
              'url': TP_API_HOST + '/tp-api/scores',
              'data': scoreData
          },
          // Check success and create confirmation message
          function(response) {
          })
  
          loadPlaceData(itemId, userId);
          if (locationCompletion == "Not Started") {
            changeStatus(itemId, "Not Started", "Edit", "LocationStatusId", 2, editStatusColor, statusCount)
          }
          jQuery('#location-input-section').removeClass('show')
          jQuery('#location-input-section input').val("")
          jQuery('#location-input-section textarea').val("")
          jQuery('#item-location-spinner-container').css('display', 'none')
        });
    });
}

function saveItemDate(itemId, userId, editStatusColor, statusCount) {
    if (jQuery('#transcribeLock').length) {
      lockWarning();
      return 0;
    }
    jQuery('#item-date-spinner-container').css('display', 'block')
    // Prepare data and send API request
    data = {
      DateStartDisplay: jQuery('#startdateentry').val(),
      DateEndDisplay: jQuery('#enddateentry').val()
    }
    startDate = jQuery('#startdateentry').val().split('/');
    if (!isNaN(startDate[2]) && !isNaN(startDate[1]) && !isNaN(startDate[0])) {
      data['DateStart'] = startDate[2] + "-" + startDate[1] + "-" + startDate[0];
    }
    else if (startDate.length == 1 && startDate[0].length <= 4 && startDate[0].length > 0 && !isNaN(startDate[0])) {
      data['DateStart'] = startDate[0] + "-01-01";
    }
    else {
      if (startDate[0] != "" && startDate[0] != null) {
        jQuery('#item-date-spinner-container').css('display', 'none')
        alert("Please enter a valid date or year");
        return 0
      }
    }
  
    endDate = jQuery('#enddateentry').val().split('/');
    if (!isNaN(endDate[2]) && !isNaN(endDate[1]) && !isNaN(endDate[0])) {
      data['DateEnd'] = endDate[2] + "-" + endDate[1] + "-" + endDate[0];
    }
    else if (endDate.length == 1 && endDate[0].length <=4 && endDate[0].length > 0 && !isNaN(endDate[0])) {
      data['DateEnd'] = endDate[0] + "-01-01";
    }
    else {
      if (endDate[0] != "" && endDate[0] != null) {
        jQuery('#item-date-spinner-container').css('display', 'none')
        alert("Please enter a valid date or year");
        return 0
      }
    }
  
    var dataString= JSON.stringify(data);
    jQuery.post(home_url + '/wp-content/themes/transcribathon/admin/inc/custom_scripts/send_ajax_api_request.php', {
      'type': 'GET',
      'url': TP_API_HOST + '/tp-api/items/' + itemId
    },
      function(response) {
        var response = JSON.parse(response);
        var taggingCompletion = JSON.parse(response.content)["TaggingStatusName"];
        var oldStartDate = JSON.parse(response.content)["DateStart"];
        var oldEndDate = JSON.parse(response.content)["DateEnd"];
        jQuery.post(home_url + '/wp-content/themes/transcribathon/admin/inc/custom_scripts/send_ajax_api_request.php', {
            'type': 'POST',
            'url': TP_API_HOST + '/tp-api/items/' + itemId,
            'data': data
        },
        // Check success and create confirmation message
        function(response) {
          if (startDate != "" && startDate != oldStartDate) {
            jQuery('#startdateDisplay').parent('.item-date-display-container').css('display', 'block')
            jQuery('#startdateDisplay').parent('.item-date-display-container').siblings('.item-date-input-container').css('display', 'none')
            jQuery('#startdateDisplay').html(jQuery('#startdateentry').val())
          }
          if (endDate != "" && endDate != oldEndDate) {
            jQuery('#enddateDisplay').parent('.item-date-display-container').css('display', 'block')
            jQuery('#enddateDisplay').parent('.item-date-display-container').siblings('.item-date-input-container').css('display', 'none')
            jQuery('#enddateDisplay').html(jQuery('#enddateentry').val())
          }
          scoreData = {
                        ItemId: itemId,
                        UserId: userId,
                        ScoreType: "Enrichment",
                        Amount: 1
                      }
          jQuery.post(home_url + '/wp-content/themes/transcribathon/admin/inc/custom_scripts/send_ajax_api_request.php', {
              'type': 'POST',
              'url': TP_API_HOST + '/tp-api/scores',
              'data': scoreData
          },
          // Check success and create confirmation message
          function(response) {
          })
  
          if (taggingCompletion == "Not Started") {
            changeStatus(itemId, "Not Started", "Edit", "TaggingStatusId", 2, editStatusColor, statusCount)
          }
          jQuery('#item-date-save-button').css('display', 'none')
          jQuery('#item-date-spinner-container').css('display', 'none')
        });
      });
}

function savePerson(itemId, userId, editStatusColor, statusCount) {
    jQuery('#item-person-spinner-container').css('display', 'block')
  
    firstName = jQuery('#person-firstName-input').val();
    lastName = jQuery('#person-lastName-input').val();
    birthPlace = jQuery('#person-birthPlace-input').val();
    birthDate = jQuery('#person-birthDate-input').val().split('/');
    deathPlace = jQuery('#person-deathPlace-input').val();
    deathDate = jQuery('#person-deathDate-input').val().split('/');
    description = jQuery('#person-description-input-field').val();
    link = jQuery('#person-wiki-input-field').val();
  
    if (firstName == "" && lastName == "") {
      return 0;
    }
  
    // Prepare data and send API request
    data = {
      FirstName: firstName,
      LastName: lastName,
      BirthPlace: birthPlace,
      DeathPlace: deathPlace,
      Link: link,
      Description: description,
      ItemId: itemId
    }
    if (!isNaN(birthDate[2]) && !isNaN(birthDate[1]) && !isNaN(birthDate[0])) {
      data['BirthDate'] = birthDate[2] + "-" + birthDate[1] + "-" + birthDate[0];
    }
    else {
      data['BirthDate'] = null;
    }
    if (!isNaN(deathDate[2]) && !isNaN(deathDate[1]) && !isNaN(deathDate[0])) {
      data['DeathDate'] = deathDate[2] + "-" + deathDate[1] + "-" + deathDate[0];
    }
    else {
      data['DeathDate'] = null;
    }
  
    for (var key in data) {
      if (data[key] == "") {
        data[key] = null;
      }
    }
  
    var dataString= JSON.stringify(data);
    jQuery.post(home_url + '/wp-content/themes/transcribathon/admin/inc/custom_scripts/send_ajax_api_request.php', {
      'type': 'GET',
      'url': TP_API_HOST + '/tp-api/items/' + itemId
    },
    function(response) {
      var response = JSON.parse(response);
      var taggingCompletion = JSON.parse(response.content)["TaggingStatusName"];
      jQuery.post(home_url + '/wp-content/themes/transcribathon/admin/inc/custom_scripts/send_ajax_api_request.php', {
          'type': 'POST',
          'url': TP_API_HOST + '/tp-api/persons',
          'data': data
      },
      // Check success and create confirmation message
      function(response) {
        scoreData = {
                      ItemId: itemId,
                      UserId: userId,
                      ScoreType: "Enrichment",
                      Amount: 1
                    }
        jQuery.post(home_url + '/wp-content/themes/transcribathon/admin/inc/custom_scripts/send_ajax_api_request.php', {
            'type': 'POST',
            'url': TP_API_HOST + '/tp-api/scores',
            'data': scoreData
        },
        // Check success and create confirmation message
        function(response) {
        })
  
        loadPersonData(itemId, userId);
        if (taggingCompletion == "Not Started") {
          changeStatus(itemId, "Not Started", "Edit", "TaggingStatusId", 2, editStatusColor, statusCount)
        }
        jQuery('#person-input-container').removeClass('show')
        jQuery('#person-input-container input').val("")
        jQuery('#item-person-spinner-container').css('display', 'none')
      });
    });
}

function saveKeyword(itemId, userId, editStatusColor, statusCount) {
    jQuery('#item-keyword-spinner-container').css('display', 'block')
    value = jQuery('#keyword-input').val();
  
    if (value != "" && value != null) {
      // Prepare data and send API request
      data = {
        PropertyValue: value,
        PropertyType: "Keyword"
      }
  
      var dataString= JSON.stringify(data);
      jQuery.post(home_url + '/wp-content/themes/transcribathon/admin/inc/custom_scripts/send_ajax_api_request.php', {
        'type': 'GET',
        'url': TP_API_HOST + '/tp-api/items/' + itemId
      },
      function(response) {
        var response = JSON.parse(response);
        var taggingCompletion = JSON.parse(response.content)["TaggingStatusName"];
        jQuery.post(home_url + '/wp-content/themes/transcribathon/admin/inc/custom_scripts/send_ajax_api_request.php', {
            'type': 'POST',
            'url': TP_API_HOST + '/tp-api/properties?ItemId=' + itemId,
            'data': data
        },
        // Check success and create confirmation message
        function(response) {
          scoreData = {
                        ItemId: itemId,
                        UserId: userId,
                        ScoreType: "Enrichment",
                        Amount: 1
                      }
          jQuery.post(home_url + '/wp-content/themes/transcribathon/admin/inc/custom_scripts/send_ajax_api_request.php', {
              'type': 'POST',
              'url': TP_API_HOST + '/tp-api/scores',
              'data': scoreData
          },
          // Check success and create confirmation message
          function(response) {
          })
  
          loadKeywordData(itemId, userId);
          if (taggingCompletion == "Not Started") {
            changeStatus(itemId, "Not Started", "Edit", "TaggingStatusId", 2, editStatusColor, statusCount)
          }
          jQuery('#keyword-input-container').removeClass('show')
          jQuery('#keyword-input-container input').val("")
          jQuery('#item-keyword-spinner-container').css('display', 'none')
        });
      });
    }
}

function saveLink(itemId, userId, editStatusColor, statusCount, e) {
    jQuery('#item-link-spinner-container').css('display', 'block')
    url = jQuery('#link-input-container .link-url-input input').val();
    description = jQuery('#link-input-container .link-description-input textarea').val();
  
    if (url != "" && url != null) {
      // Prepare data and send API request
      data = {
        PropertyValue: url,
        PropertyDescription: description,
        PropertyType: "Link"
      }
      var dataString= JSON.stringify(data);
      jQuery.post(home_url + '/wp-content/themes/transcribathon/admin/inc/custom_scripts/send_ajax_api_request.php', {
        'type': 'GET',
        'url': TP_API_HOST + '/tp-api/items/' + itemId
      },
      function(response) {
        var response = JSON.parse(response);
        var taggingCompletion = JSON.parse(response.content)["TaggingStatusName"];
        jQuery.post(home_url + '/wp-content/themes/transcribathon/admin/inc/custom_scripts/send_ajax_api_request.php', {
            'type': 'POST',
            'url': TP_API_HOST + '/tp-api/properties?ItemId=' + itemId,
            'data': data
        },
        // Check success and create confirmation message
        function(response) {
          scoreData = {
                        ItemId: itemId,
                        UserId: userId,
                        ScoreType: "Enrichment",
                        Amount: 1
                      }
          jQuery.post(home_url + '/wp-content/themes/transcribathon/admin/inc/custom_scripts/send_ajax_api_request.php', {
              'type': 'POST',
              'url': TP_API_HOST + '/tp-api/scores',
              'data': scoreData
          },
          // Check success and create confirmation message
          function(response) {
          })
  
          loadLinkData(itemId, userId);
          if (taggingCompletion == "Not Started") {
            changeStatus(itemId, "Not Started", "Edit", "TaggingStatusId", 2, editStatusColor, statusCount)
          }
          jQuery('#link-input-container').removeClass('show')
          jQuery('#link-input-container input').val("")
          jQuery('#link-input-container textarea').val("")
          jQuery('#item-link-spinner-container').css('display', 'none')
        });
      });
    }
}

function loadPlaceData(itemId, userId) {
    // Get new location list
    jQuery.post(home_url + '/wp-content/themes/transcribathon/admin/inc/custom_scripts/send_ajax_api_request.php', {
        'type': 'GET',
        'url': TP_API_HOST + '/tp-api/places?ItemId=' + itemId
    },
    function(response) {
      var response = JSON.parse(response);
      if (response.code == "200") {
        var content = JSON.parse(response.content);
        jQuery('#item-location-list ul').html('')
  
        for (var i = 0; i < content.length; i++) {
          if (content[i]['Comment'] != "NULL" && content[i]['Comment'] != null) {
            var comment = content[i]['Comment'];
          }
          else {
              var comment = "";
          }
          var placeHtml = "";
          placeHtml +=
            '<li id="location-' + content[i]['PlaceId'] + '">' +
              '<div class="item-data-output-element-header collapse-controller" data-toggle="collapse" href="#location-data-output-' + content[i]['PlaceId'] + '">' +
                  '<h6>' +
                  escapeHtml(content[i]['Name']) +
                  '</h6>' +
                  '<i class="fas fa-angle-down"' +  'style= "float:right;"></i>' +
                  '<div style="clear:both;"></div>' +
                '</div>' +
                              '<div id="location-data-output-' + content[i]['PlaceId'] + '" class="collapse">' +
                              '<div id="location-data-output-display-' + content[i]['PlaceId'] + '" class="location-data-output-content">' +
                                  '<span>' +
                                      'Description: ' +
                                      escapeHtml(comment) +
                                  '</span></br>' +
                                  '<span>' +
                                      'Wikidata: ' +
                                      '<a href="' + 'http://wikidata.org/wiki/' + content[i]['WikiDataId'] +'" style="text-decoration: none;" target="_blank">' +
                                      content[i]['WikidataName'] + ', ' + content[i]['WikidataId'] +
                                      '</a>' +
                                  '</span>' +
                                 '<div style="display:flex;"><span style="width:86%;"></span>' + '<span style="width:14%;">' +
  
                                  '<i class="edit-item-data-icon fas fa-pencil theme-color-hover login-required"' +
                                                      'onClick="openLocationEdit(' + content[i]['PlaceId'] + ')"></i>' +
                                  '<i class="edit-item-data-icon fas fa-trash-alt theme-color-hover login-required"' +
                                                      'onClick="deleteItemData(\'places\', ' + content[i]['PlaceId'] + ', ' + itemId + ', \'place\', ' + userId + ')"></i>' +
                                  '</span></div>' +
                              '</div>' +
                              '<div id="location-data-edit-' + escapeHtml(content[i]['PlaceId']) + '" class="location-data-edit-container">' +
                                  '<div class="location-input-section-top">' +
                                      '<div class="location-input-name-container">' +
                                          '<label>Location Name:</label>' +
                                          '<input type="text" class="edit-input" value="' + escapeHtml(content[i]['Name']) + '" name="" placeholder="">' +
                                      '</div>' +
                                      '<div class="location-input-coordinates-container">' +
                                          '<label>Coordinates: </label>' +
                                          '<span class="required-field">*</span>' +
                                          '<input type="text" class="edit-input" value="' + escapeHtml(content[i]['Latitude']) + ', ' + escapeHtml(content[i]['Longitude']) + '" name="" placeholder="">' +
                                      '</div>' +
                                      "<div style='clear:both;'></div>" +
                                  '</div>' +
  
                                  '<div class="location-input-geonames-container location-search-container" style="margin:5px 0;">' +
                                  '<label>WikiData:</label>';
                                  if (content[i]['WikidataName'] != "NULL" && content[i]['WikidataId'] != "NULL") {
                                    placeHtml +=
                                      '<input type="text" id="lgns" class="edit-input" placeholder="" name="" value="' + escapeHtml(content[i]['WikidataName']) + '; ' + escapeHtml(content[i]['WikidataId']) + '"/>';
                                  }
                                  else {
                                    placeHtml +=
                                      '<input type="text" id="lgns" class="edit-input" placeholder="" name=""/>';
                                  }
                                  placeHtml +=
                                '</div>' +
  
                                  '<div class="location-input-description-container" style="height:50px;">' +
                                      '<label>Description:<i class="fas fa-question-circle" style="font-size:16px; cursor:pointer; margin-left:4px;" title="Add more information to this location, e.g. the building name, or its significance to the item"></i></label>' +
                                      '<textarea rows= "2" style="resize:none;" class="gsearch-form edit-input" type="text" id="ldsc" placeholder="" name="">' + comment + '</textarea>' +
                                  '</div>' +
  
                                  "<div class='form-buttons-right'>" +
                                      "<button onClick='editItemLocation(" + content[i]['PlaceId'] + ", " + itemId + ", " + userId + ")' " +
                                                  "class='item-page-save-button edit-location-save theme-color-background'>" +
                                          "SAVE" +
                                      "</button>" +
  
                                      "<button class='theme-color-background edit-location-cancel' onClick='openLocationEdit(" + content[i]['PlaceId'] + ")'>" +
                                          "CANCEL" +
                                      "</button>" +
  
                                      '<div id="item-location-' + content[i]['PlaceId'] +'-spinner-container" class="spinner-container spinner-container-right">' +
                                          '<div class="spinner"></div>' +
                                      "</div>" +
                                      "<div style='clear:both;'></div>" +
                                  "</div>" +
                                  "<div style='clear:both;'></div>" +
                                 "</div>" +
                          "</div>" +
            '</li>';
  
          jQuery('#item-location-list ul').append(placeHtml);
        }
      }
    });
}
// TODO there is better and more efficient way to do this
function loadPersonData(itemId, userId) {
    // Get new person list
    jQuery.post(home_url + '/wp-content/themes/transcribathon/admin/inc/custom_scripts/send_ajax_api_request.php', {
        'type': 'GET',
        'url': TP_API_HOST + '/tp-api/persons?ItemId=' + itemId
    },
    function(response) {
      var response = JSON.parse(response);
      if (response.code == "200") {
        var content = JSON.parse(response.content);
        jQuery('#item-person-list ul').html('')
  
        for (var i = 0; i < content.length; i++) {
          if (content[i]['FirstName'] != "NULL" && content[i]['FirstName'] != null) {
            var firstName = escapeHtml(content[i]['FirstName']);
          }
          else {
              var firstName = "";
          }
          if (content[i]['LastName'] != "NULL" && content[i]['LastName'] != null) {
              var lastName = escapeHtml(content[i]['LastName']);
          }
          else {
              var lastName = "";
          }
          if (content[i]['BirthPlace'] != "NULL" && content[i]['BirthPlace'] != null) {
              var birthPlace = escapeHtml(content[i]['BirthPlace']);
          }
          else {
              var birthPlace = "";
          }
          if (content[i]['BirthDate'] != "NULL" && content[i]['BirthDate'] != null) {
              var birthTimestamp = Date.parse(content[i]['BirthDate']);
              var birthDate = new Date(birthTimestamp);
              birthDate = ("0" + birthDate.getDate()).slice(-2) + '/' + ("0" + (birthDate.getMonth() + 1)).slice(-2) + '/' + birthDate.getFullYear();
          }
          else {
              var birthDate = "";
          }
          if (content[i]['DeathPlace'] != "NULL" && content[i]['DeathPlace'] != null) {
              var deathPlace = escapeHtml(content[i]['DeathPlace']);
          }
          else {
              var deathPlace = "";
          }
          if (content[i]['DeathDate'] != "NULL" && content[i]['DeathDate'] != null) {
              var deathTimestamp = Date.parse(content[i]['DeathDate']);
              var deathDate = new Date(deathTimestamp);
              deathDate = ("0" + deathDate.getDate()).slice(-2) + '/' + ("0" + (deathDate.getMonth() + 1)).slice(-2) + '/' + deathDate.getFullYear();
          }
          else {
              var deathDate = "";
          }
          if (content[i]['Description'] != "NULL" && content[i]['Description'] != null) {
              var description = escapeHtml(content[i]['Description']);
          }
          else {
              var description = "";
          }
          if (content[i]['Link'] != "NULL" && content[i]['Link'] != null) {
              var wikidata = escapeHtml(content[i]['Link']);
          }
          else {
              var wikidata = "";
          }
  
          var personHeadline = '<span class="item-name-header">' +
          firstName + ' ' + lastName + ' ' +
          '</span>';
          if (birthDate != "") {
            if (deathDate != "") {
              personHeadline += '<span class="item-name-header">(' + birthDate + ' - ' + deathDate + ')</span>';
            }
            else {
              personHeadline += '<span class="item-name-header">(Birth: ' + birthDate + ')</span>';
            }
          }
          else {
            if (deathDate != "") {
              personHeadline += '<span class="item-name-header">(Death: ' + deathDate + ')</span>';
            }
            else {
              if (description != "") {
                personHeadline += "<span class='person-dots'>(" + description + ")</span>";
              }
            }
          }
          var personHtml =
          '<li id="person-' + content[i]['PersonId'] + '">' +
            '<div class="item-data-output-element-header collapse-controller" data-toggle="collapse" href="#person-data-output-' + content[i]['PersonId'] + '">' +
              '<h6 class="person-data-ouput-headline">' +
              personHeadline +
              '</h6>' +
              '<span class="person-dots" style="width=10px; white-space: nowrap; text-overflow:ellipsis;"></span>' +
              '<i class="fas fa-angle-down" style= "float:right;"></i>' +
              '<div style="clear:both;"></div>' +
            '</div>' +
            '<div id="person-data-output-' + content[i]['PersonId'] + '" class="collapse">' +
              '<div id="person-data-output-display-' + content[i]['PersonId'] + '" class="person-data-output-content">' +
                '<div>' +
                    '<table border="0">' +
                      '<tr>' +
                        '<th></th>' +
                        '<th>Birth</th>' +
                        '<th>Death</th>' +
                      '</tr>' +
                      '<tr>' +
                        '<th>Date</th>' +
                        '<td>' +
                         birthDate +
                        '</td>' +
                        '<td>' +
                        deathDate +
                        '</td>' +
                      '</tr>' +
                      '<tr>' +
                        '<th>Location</th>' +
                        '<td>' +
                        birthPlace +
                        '</td>' +
                        '<td>' +
                        deathPlace +
                        '</td>' +
                      '</tr>' +
                  '</table>' +
                    /*'<div class="person-data-output-birthDeath">' +
                        '<span>' +
                            'Birth Location: ' +
                            birthPlace +
                        '</span>' +
                          '</br>' +
                        '<span>' +
                            'Death Location: ' +
                            deathPlace +
                        '</span>' +
                    '</div>' +
                    '<div class="person-data-output-birthDeath">' +
                        '<span>' +
                            'Birth Date: ' +
                            birthDate +
                        '</span>' +
                        '</br>' +
                        '<span>' +
                            'Death Date: ' +
                            deathDate +
                        '</span>' +
  
                        '</br>' +
                    '</div>' +
                    '<div style="clear:both;"></div>' +*/
                '</div>' +
                '<div class="person-data-output-button">'+
                        '<span>'+
                            'Description: '+
                            description +
                        '</span>' +
                        '<i class="login-required edit-item-data-icon fas fa-pencil theme-color-hover"' +
                                            'onClick="openPersonEdit(' + content[i]['PersonId'] +')"></i>' +
                        '<i class="login-required edit-item-data-icon fas fa-trash-alt theme-color-hover"' +
                                            'onClick="deleteItemData(\'persons\', ' + content[i]['PersonId'] + ', ' + itemId + ', \'person\', ' + userId + ')"></i>' +
                '</div>' +
                '<div style="clear:both;"></div>' +
              '</div>' +
  
              '<div class="person-data-edit-container person-item-data-container" id="person-data-edit-' + content[i]['PersonId'] + '">' +
                '<div class="person-input-names-container">';
                  if (firstName != "") {
                    personHtml += '<input type="text" id="person-' + content[i]['PersonId'] + '-firstName-edit"  placeholder="First Name" class="input-response person-input-field person-re-edit" value="' + firstName + '" style="outline:none;">'
                  }
                  else {
                    personHtml += '<input type="text" id="person-' + content[i]['PersonId'] + '-firstName-edit" class="input-response person-input-field person-re-edit" placeholder="First Name" style="outline:none;">'
                  }
  
                  if (lastName != "") {
                    personHtml += '<input type="text" id="person-' + content[i]['PersonId'] + '-lastName-edit" class="input-response person-input-field person-re-edit-right" placeholder="Last Name" value="' + lastName + '" style="outline:none;">'
                  }
                  else {
                    personHtml += '<input type="text" id="person-' + content[i]['PersonId'] + '-lastName-edit" class="input-response person-input-field person-re-edit-right" placeholder="Last Name" style="outline:none;">'
                  }
                personHtml +=
                '</div>' +
  
                '<div class="person-description-input">' +
                      //   '<label>Description:</label><br/>' +
                        '<input type="text" id="person-' + content[i]['PersonId'] + '-description-edit" class="input-response person-input-field" placeholder="&nbsp Add more information to this person..." value="' + description + '">' +
                      //   '<i class="fas fa-question-circle" style="font-size:16px; cursor:pointer; margin-left:4px;" title="Add more information to this person, e.g. their profession, or their significance to the item"></i>' +
                '</div>' +
  
                '<div class="person-description-input">' +
                //   '<label>Description:</label><br/>' +
                  '<input type="text" id="person-' + content[i]['PersonId'] + '-wiki-edit" class="input-response person-input-field" placeholder="&nbsp Add Wikidata ID to this person..." value="' + wikidata + '">' +
                //   '<i class="fas fa-question-circle" style="font-size:16px; cursor:pointer; margin-left:4px;" title="Add more information to this person, e.g. their profession, or their significance to the item"></i>' +
                '</div>' +
  
                '<div class="person-location-birth-inputs" style="margin-top:5px;position:relative;">';
                  if (birthPlace != "") {
                    personHtml += '<input type="text" id="person-' + content[i]['PersonId'] + '-birthPlace-edit" class="input-response person-input-field person-re-edit" value="' + birthPlace + '" placeholder="Birth Location" style="outline:none;">'
                  }
                  else {
                    personHtml += '<input type="text" id="person-' + content[i]['PersonId'] + '-birthPlace-edit" class="input-response person-input-field person-re-edit" placeholder="Birth Location" style="outline:none;">'
                  }
  
                  if (birthDate != "") {
                    personHtml += '<span class="input-response"><input type="text" id="person-' + content[i]['PersonId'] + '-birthDate-edit" class="date-input-response person-input-field datepicker-input-field person-re-edit-right" value="' + birthDate + '" placeholder="Birth: dd/mm/yyyy" style="outline:none;"></span>'
                  }
                  else {
                    personHtml += '<span class="input-response"><input type="text" id="person-' + content[i]['PersonId'] + '-birthDate-edit" class="date-input-response person-input-field datepicker-input-field person-re-edit-right" placeholder="Birth: dd/mm/yyyy" style="outline:none;"></span>'
                  }
                  personHtml +=
                  '</div>' +
  
                  '<div class="person-location-death-inputs" style="margin-top:5px;position:relative;">';
                    if (deathPlace != "") {
                      personHtml += '<input type="text" id="person-' + content[i]['PersonId'] + '-deathPlace-edit" class="input-response person-input-field person-re-edit" value="' + deathPlace + '" placeholder="Death Location" style="outline:none;">'
                    }
                    else {
                      personHtml += '<input type="text" id="person-' + content[i]['PersonId'] + '-deathPlace-edit" class="input-response person-input-field person-re-edit" placeholder="Death Location" style="outline:none;">'
                    }
  
                    if (deathDate != "") {
                      personHtml += '<span class="input-response"><input type="text" id="person-' + content[i]['PersonId'] + '-deathDate-edit" class="date-input-response person-input-field datepicker-input-field person-re-edit-right" value="' + deathDate + '" placeholder="Death: dd/mm/yyyy" style="outline:none;"></span>'
                    }
                    else {
                      personHtml += '<span class="input-response"><input type="text" id="person-' + content[i]['PersonId'] + '-deathDate-edit" class="date-input-response person-input-field datepicker-input-field person-re-edit-right" placeholder="Death: dd/mm/yyyy" style="outline:none;"></span>'
                    }
                    personHtml +=
                    '</div>' +
  
                    '<div class="form-buttons-right">' +
                        "<button class='edit-location-save theme-color-background'" +
                                    "onClick='editPerson(" + content[i]['PersonId'] + ", " + itemId + ", " + userId + ")'>" +
                            "SAVE" +
                        "</button>" +
  
                        "<button id='save-personinfo-button' class='theme-color-background edit-location-cancel' onClick='openPersonEdit(" + content[i]['PersonId'] + ")'>" +
                            "CANCEL" +
                        "</button>" +
  
                        '<div id="item-person-' + content[i]['PersonId'] + '-spinner-container" class="spinner-container spinner-container-left">' +
                            '<div class="spinner"></div>' +
                        "</div>" +
                        '<div style="clear:both;"></div>' +
                    '</div>' +
                    '<div style="clear:both;"></div>' +
                  '</div>' +
                '</div>' +
              '</li>'
          jQuery('#item-person-list ul').append(personHtml)
          jQuery( ".datepicker-input-field" ).datepicker({
              dateFormat: "dd/mm/yy",
              changeMonth: true,
              changeYear: true,
              yearRange: "100:+10",
              showOn: "button",
              buttonImage:  `${home_url}/public_html/wp-content/themes/transcribathon/admin/inc/custom_shortcodes/upload-images/icon_calendar.svg`
            });
        }
      }
    });
}

function loadKeywordData(itemId, userId) {
    // Get new keyword list
    jQuery.post(home_url + '/wp-content/themes/transcribathon/admin/inc/custom_scripts/send_ajax_api_request.php', {
        'type': 'GET',
        'url': TP_API_HOST + '/tp-api/items/' + itemId
    },
    function(response) {
      var response = JSON.parse(response);
      if (response.code == "200") {
        var content = JSON.parse(response.content);
        jQuery('#item-keyword-list').html('')
        for (var i = 0; i < content['Properties'].length; i++) {
          if (content['Properties'][i]['PropertyType'] == "Keyword") {
            jQuery('#item-keyword-list').append(
              '<div id="'+ content['Properties'][i]['PropertyId'] + '" class="keyword-single">' +
                  escapeHtml(content['Properties'][i]['PropertyValue']) +
                  '<i style="margin-left:5px;" class="login-required delete-item-datas far fa-times"' +
                      'onClick="deleteItemData(\'properties\', ' + content['Properties'][i]['PropertyId'] + ', ' + itemId + ', \'keyword\', ' + userId + ')"></i>' +
              '</div>'
            )
          }
        }
      }
    });
}

function loadLinkData(itemId, userId) {
    // Get new link list
    jQuery.post(home_url + '/wp-content/themes/transcribathon/admin/inc/custom_scripts/send_ajax_api_request.php', {
        'type': 'GET',
        'url': TP_API_HOST + '/tp-api/items/' + itemId
    },
    function(response) {
      var response = JSON.parse(response);
      if (response.code == "200") {
        var content = JSON.parse(response.content);
        jQuery('#item-link-list ul').html('')
        for (var i = 0; i < content['Properties'].length; i++) {
          if (content['Properties'][i]['PropertyType'] == "Link") {
            if (content['Properties'][i]['PropertyDescription'] != "NULL" && content['Properties'][i]['PropertyDescription'] != null) {
              var description = escapeHtml(content['Properties'][i]['PropertyDescription']);
            }
            else {
              var description = "";
            }
            jQuery('#item-link-list ul').append(
              '<li id="link-' + content['Properties'][i]['PropertyId'] + '">' +
                '<div id="link-data-output-' + content['Properties'][i]['PropertyId'] + '" class="">' +
                  '<div id="link-data-output-display-' + content['Properties'][i]['PropertyId'] + '" class="link-data-output-content">' +
                      '<div class="item-data-output-element-header">' +
                          '<a href="' + content['Properties'][i]['PropertyValue'] + '" target="_blank" class="link-data-ouput-headline">' +
                          escapeHtml(content['Properties'][i]['PropertyValue']) +
                          '</a>' +
  
                          '<i class="edit-item-data-icon fas fa-pencil theme-color-hover"' +
                          'onClick="openLinksourceEdit(' + content['Properties'][i]['PropertyId'] + ')"></i>' +
                          '<i class="edit-item-data-icon delete-item-data fas fa-times theme-color-hover"' +
                                        'onClick="deleteItemData(\'properties\', ' + content['Properties'][i]['PropertyId'] + ', ' + itemId + ', \'link\', ' + userId + ')"></i>' +
                          '<div style="clear:both;"></div>' +
                      '</div>' +
                      '<div>' +
                        '<span>' +
                          'Description: ' +
                          escapeHtml(description) +
                        '</span>' +
                      '</div>' +
                    '</div>' +
  
                    '<div class="link-data-edit-container" id="link-data-edit-' + content['Properties'][i]['PropertyId'] +'">' +
                        // '<div>' +
                        //   "<span>Link:</span><br/>" +
                        // '</div>' +
  
                        '<div id="link-' + content['Properties'][i]['PropertyId'] +'-url-input" class="link-url-input">' +
                          '<input type="url" value="' + escapeHtml(content['Properties'][i]['PropertyValue']) + '">' +
                        '</div>' +
  
                        '<div id="link-' + content['Properties'][i]['PropertyId'] +'-description-input" class="link-description-input" >' +
                          // '<label>Additional description:</label><br/>' +
                          '<textarea rows= "3" type="text" placeholder="" name="">' + escapeHtml(description) + '</textarea>' +
                        '</div>' +
  
                        '<div class="form-buttons-right">' +

                            "<button class='theme-color-background edit-location-cancel' onClick='openLinksourceEdit(" + content['Properties'][i]['PropertyId'] + ")'>" +
                                "CANCEL" +
                            "</button>" +

                            "<button type='submit' class='theme-color-background edit-location-save' id='link-save-button'" +
                                  "onClick='editLink(" + content['Properties'][i]['PropertyId'] + ", " + itemId + ", " + userId + ")'>" +
                              "SAVE" +
                            "</button>" +

  
                            '<div id="item-link-' + content['Properties'][i]['PropertyId'] + '-spinner-container" class="spinner-container spinner-container-left">' +
                            '<div class="spinner"></div>' +
                            "</div>" +
                            '<div style="clear:both;"></div>' +
                        '</div>' +
                        '<div style="clear:both;"></div>' +
                    '</div>' +
                '</div>' +
              '</li>'
            )
          }
        }
      }
    });
}

function deleteItemData(type, id, itemId, section, userId) {
    jQuery.post(home_url + '/wp-content/themes/transcribathon/admin/inc/custom_scripts/send_ajax_api_request.php', {
      'type': 'DELETE',
      'url': TP_API_HOST + '/tp-api/' + type + '/' + id
    },
    function(response) {
      switch (section) {
        case "place":
            loadPlaceData(itemId, userId);
            break;
        case "person":
            loadPersonData(itemId, userId);
            break;
        case "keyword":
            loadKeywordData(itemId, userId);
            break;
        case "link":
            loadLinkData(itemId, userId);
            break;
      }
    });
}

function stripHTML(dirtyString) {
    var container = document.createElement('div');
    var text = document.createTextNode(dirtyString);
    container.appendChild(text);
    return container.innerHTML; // innerHTML will be a xss safe string
}

function openLocationEdit(placeId) {
    if (jQuery('#transcribeLock').length) {
      event.preventDefault();
      lockWarning();
      return 0;
    }
    if (jQuery('#location-data-edit-' + placeId).css('display') == 'none') {
      jQuery('#location-data-edit-' + placeId).css('display', 'block');
      jQuery('#location-data-output-display-' + placeId).css('display', 'none');
    }
    else {
      jQuery('#location-data-edit-' + placeId).css('display', 'none');
      jQuery('#location-data-output-display-' + placeId).css('display', 'block');
    }
}

function openPersonEdit(personId) {
    if (jQuery('#transcribeLock').length) {
      event.preventDefault();
      lockWarning();
      return 0;
    }
    if (jQuery('#person-data-edit-' + personId).css('display') == 'none') {
      jQuery('#person-data-edit-' + personId).css('display', 'block');
      jQuery('#person-data-output-display-' + personId).css('display', 'none');
    }
    else {
      jQuery('#person-data-edit-' + personId).css('display', 'none');
      jQuery('#person-data-output-display-' + personId).css('display', 'block');
    }
}

function openLinksourceEdit(propertyId) {
    if (jQuery('#transcribeLock').length) {
      event.preventDefault();
      lockWarning();
      return 0;
    }
    if (jQuery('#link-data-edit-' + propertyId).css('display') == 'none') {
      jQuery('#link-data-edit-' + propertyId).css('display', 'block');
      jQuery('#link-data-output-display-' + propertyId).css('display', 'none');
    }
    else {
      jQuery('#link-data-edit-' + propertyId).css('display', 'none');
      jQuery('#link-data-output-display-' + propertyId).css('display', 'block');
    }
}

function editItemLocation(placeId, itemId, userId) {
    jQuery('#item-location-' + placeId + '-spinner-container').css('display', 'block')
    // Prepare data and send API request
    locationName = jQuery('#location-data-edit-' + placeId + ' .location-input-name-container input').val();
    [latitude, longitude] = jQuery('#location-data-edit-' + placeId + ' .location-input-coordinates-container input').val().split(',');
    if (latitude != null) {
      latitude = latitude.trim();
    }
    if (longitude != null) {
      longitude = longitude.trim();
    }
    if (isNaN(latitude) || isNaN(longitude)) {
      jQuery('location-data-edit-' + placeId + ' .location-input-coordinates-container span').css('display', 'block');
      jQuery('#item-location-' + placeId + '-spinner-container').css('display', 'none')
      return 0;
    }
  
    description = jQuery('#location-data-edit-' + placeId + ' .location-input-description-container textarea').val();
    wikidata = jQuery('#location-data-edit-' + placeId + '  .location-input-geonames-container input').val().split(";");
    // alert(wikidata[1]);
    data = {
              Name: locationName,
              Latitude: latitude,
              Longitude: longitude,
              Comment: description,
              WikidataName: wikidata[0],
              WikidataId: wikidata[1]
            }
    var dataString= JSON.stringify(data);
  
    jQuery.post(home_url + '/wp-content/themes/transcribathon/admin/inc/custom_scripts/send_ajax_api_request.php', {
        'type': 'POST',
        'url': TP_API_HOST + '/tp-api/places/' + placeId,
        'data': data
    },
    // Check success and create confirmation message
    function(response) {
      loadPlaceData(itemId, userId);
  
      openLocationEdit(placeId);
      jQuery('#item-location-' + placeId + '-spinner-container').css('display', 'none')
    });
}

function editPerson(personId, itemId, userId) {
    jQuery('#item-person-' + personId + '-spinner-container').css('display', 'block')
  
    firstName = jQuery('#person-' + personId + '-firstName-edit').val();
    lastName = jQuery('#person-' + personId + '-lastName-edit').val();
    birthPlace = jQuery('#person-' + personId + '-birthPlace-edit').val();
    birthDate = jQuery('#person-' + personId + '-birthDate-edit').val().split('/');
    deathPlace = jQuery('#person-' + personId + '-deathPlace-edit').val();
    deathDate = jQuery('#person-' + personId + '-deathDate-edit').val().split('/');
    description = jQuery('#person-' + personId + '-description-edit').val();
    wiki = jQuery('#person-' + personId + '-wiki-edit').val();
  
    if (firstName == "" && lastName == "") {
      return 0;
    }
    // Prepare data and send API request
    data = {
      FirstName: firstName,
      LastName: lastName,
      BirthPlace: birthPlace,
      DeathPlace: deathPlace,
      Link: wiki,
      Description: description,
      ItemId: itemId
    }
    if (!isNaN(birthDate[2]) && !isNaN(birthDate[1]) && !isNaN(birthDate[0])) {
      data['BirthDate'] = birthDate[2] + "-" + birthDate[1] + "-" + birthDate[0];
    }
    else {
      data['BirthDate'] = null;
    }
    if (!isNaN(deathDate[2]) && !isNaN(deathDate[1]) && !isNaN(deathDate[0])) {
      data['DeathDate'] = deathDate[2] + "-" + deathDate[1] + "-" + deathDate[0];
    }
    else {
      data['DeathDate'] = null;
    }
  
    for (var key in data) {
      if (data[key] == "") {
        data[key] = null;
      }
    }

    function editLink(linkId, itemId, userId) {
        jQuery('#item-link-' + linkId + '-spinner-container').css('display', 'block')
        url = jQuery('#link-' + linkId + '-url-input input').val();
        description = jQuery('#link-' + linkId + '-description-input textarea').val();
      
        if (url != "" && url != null) {
          // Prepare data and send API request
          data = {
            PropertyValue: url,
            PropertyDescription: description,
            PropertyType: "Link"
          }
          var dataString= JSON.stringify(data);
      
          jQuery.post(home_url + '/wp-content/themes/transcribathon/admin/inc/custom_scripts/send_ajax_api_request.php', {
              'type': 'POST',
              'url': TP_API_HOST + '/tp-api/properties/' + linkId,
              'data': data
          },
          // Check success and create confirmation message
          function(response) {
            loadLinkData(itemId, userId);
            openLinksourceEdit(linkId);
            jQuery('#item-link-' + linkId + '-spinner-container').css('display', 'none')
          });
        }
    }
  
    jQuery.post(home_url + '/wp-content/themes/transcribathon/admin/inc/custom_scripts/send_ajax_api_request.php', {
        'type': 'POST',
        'url': TP_API_HOST + '/tp-api/persons/' + personId,
        'data': data
    },
    // Check success and create confirmation message
    function(response) {
      loadPersonData(itemId, userId);
      openPersonEdit(personId);
      jQuery('#item-person-' + personId + '-spinner-container').css('display', 'none')
    });
}

function lockWarning() {
    jQuery('#locked-warning-container').css('display', 'block');
}

function setToolbarHeight() {
    if (tinymce.activeEditor != null) {
        jQuery('#item-page-transcription-text').mousedown(function(e){
            e.preventDefault;
            tinymce.activeEditor.focus();
            jQuery('.tox-toolbar__group').css('width', jQuery('#mytoolbar-transcription').css('width'))
            
            if(document.querySelector('.tox-tinymce')){
                document.querySelector('.tox-tinymce').style.display = 'block';
            }
        })
    }
}

function escapeHtml(text) {
    if(typeof text === "string") {
      return text
          .replace(/&/g, "&amp;")
          .replace(/</g, "&lt;")
          .replace(/>/g, "&gt;")
          .replace(/"/g, "&quot;")
          .replace(/'/g, "&#039;");
    } else {
      return text;
    }
}

jQuery(document).delegate('#item-page-description-text', 'keydown', function(e) {
    var keyCode = e.keyCode || e.which;
  
    if (keyCode == 9) {
      e.preventDefault();
      var start = this.selectionStart;
      var end = this.selectionEnd;
      // set textarea value to: text before caret + tab + text after caret
      jQuery(this).val(jQuery(this).val().substring(0, start)
                  + "\t"
                  + jQuery(this).val().substring(end));
      // put caret at right position again
      this.selectionStart =
      this.selectionEnd = start + 1;
    }
});

function updateSolr() {
    jQuery.post(home_url + '/wp-content/themes/transcribathon/admin/inc/custom_scripts/send_ajax_api_request.php', {
      'type': 'POST',
      'url': TP_API_HOST + '/tp-api/stories/update',
    },
      function(statusResponse) {
      });
}

function initializeMap() {
    //reinitialising map
    var url_string = window.location.href;
    var url = new URL(url_string);
    var itemId = url.searchParams.get('item');
    var coordinates = jQuery('.location-input-coordinates-container.location-input-container > input ')[0];
  
    mapboxgl.accessToken = 'pk.eyJ1IjoiZmFuZGYiLCJhIjoiY2pucHoybmF6MG5uMDN4cGY5dnk4aW80NSJ9.U8roKG6-JV49VZw5ji6YiQ';
  
    jQuery('#addMapMarker').click(function() {
      var el = document.createElement('div');
      el.className = 'marker';
  
      var icon = document.createElement('i');
      icon .className = 'fas fa-map-marker-plus';
      if(typeof marker !== 'undefined') {
        marker.remove();
      }
      marker = new mapboxgl.Marker({element: el, draggable: true})
        .setLngLat(map.getCenter())
        .addTo(map);
  
      var lngLat = marker.getLngLat();
      coordinates.value = lngLat.lat + ', ' + lngLat.lng;
      marker.on('dragend', onDragEnd);
    });
    if (jQuery('#full-view-map').length) {
        jQuery('.map-placeholder').css('display', 'none');
        map = new mapboxgl.Map({
          container: 'full-view-map',
          style: 'mapbox://styles/fandf/ck4birror0dyh1dlmd25uhp6y',
          center: [16, 49],
          zoom: 2.25,
          scrollZoom: false
        });
        map.addControl(new mapboxgl.NavigationControl());
  
        var bounds = new mapboxgl.LngLatBounds();
  
        jQuery.post(
          home_url
          + '/wp-content/themes/transcribathon/admin/inc/custom_scripts/send_ajax_api_request.php',
          {
            type: 'GET',
            url: TP_API_HOST + '/tp-api/places/story/' + itemId
          },
          function(response) {
            const content  = JSON.parse(response).content;
            const places  = JSON.parse(content);
            places.filter(place => place.Latitude != 0 || place.Longitude != 0).forEach(function(marker) {
              var el = document.createElement('div');
              el.className = 'marker savedMarker ' + (marker.ItemId == 0 ? "storyMarker" : "");
              var popup = new mapboxgl.Popup({offset: 35, closeButton: false})
                .setHTML('<div class=\"popupWrapper\">' + (marker.ItemId == 0 ? '<div class=\"story-location-header\">Story Location</div>' : '') + '<div class=\"name\">' + (marker.Name || marker.ItemTitle || "") + '</div><div class=\"comment\">' + (marker.Comment || "") + '</div></div>');
              bounds.extend([marker.Longitude, marker.Latitude]);
              new mapboxgl.Marker({element: el, anchor: 'bottom'})
                .setLngLat([marker.Longitude, marker.Latitude])
                .setPopup(popup)
                .addTo(map);
            });
            if(places && places.length === 1) {
              map.flyTo({
                center: [
                  bounds._ne.lng,
                  bounds._ne.lat
                ],
                zoom: 5,
                essential: true
              });
            } else {
              map.fitBounds(bounds, {padding: {top: 100, bottom:100, left: 100, right: 100}});
            }
          }
        );
        // fetch(home_url + '/tp-api/places/story/' + itemId)
        //   .then(function(response) {
        //     return response.json();
        //   })
        //   .then(function(places) {
        //     places.filter(place => place.Latitude != 0 || place.Longitude != 0).forEach(function(marker) {
        //       var el = document.createElement('div');
        //       el.className = 'marker savedMarker ' + (marker.ItemId == 0 ? "storyMarker" : "");
        //       var popup = new mapboxgl.Popup({offset: 35, closeButton: false})
        //       .setHTML('<div class=\"popupWrapper\">' + (marker.ItemId == 0 ? '<div class=\"story-location-header\">Story Location</div>' : '') + '<div class=\"name\">' + (marker.Name || marker.ItemTitle || "") + '</div><div class=\"comment\">' + (marker.Comment || "") + '</div></div>');
        //       bounds.extend([marker.Longitude, marker.Latitude]);
        //       new mapboxgl.Marker({element: el, anchor: 'bottom'})
        //         .setLngLat([marker.Longitude, marker.Latitude])
        //       .setPopup(popup)
        //         .addTo(map);
        //     });
        //   if(places && places.length === 1) {
        //     map.flyTo({
        //       center: [
        //       bounds._ne.lng,
        //       bounds._ne.lat
        //       ],
        //       zoom: 5,
        //       essential: true
        //     });
        //   } else {
        //     map.fitBounds(bounds, {padding: {top: 100, bottom:100, left: 100, right: 100}});
        //   }
        // });
      var geocoder = new MapboxGeocoder({
        accessToken: mapboxgl.accessToken,
        mapboxgl: mapboxgl,
              marker: false,
        language: 'en-EN'
      });
  
      geocoder.on('result', function(res) {
        jQuery('#location-input-section').addClass('show');
        jQuery('#location-input-geonames-search-container > input').val(res.result['text_en-EN'] + '; ' + res.result.properties.wikidata);
        var el = document.createElement('div');
        el.className = 'marker';
  
        var icon = document.createElement('div');
        icon .className = 'marker newMarker';
        if(typeof marker !== 'undefined') {
          marker.remove();
        }
        marker = new mapboxgl.Marker({element: el, draggable: true, element: icon})
          .setLngLat(res.result.geometry.coordinates)
          .addTo(map);
          var lngLat = marker.getLngLat();
          if(jQuery('#loc-save-lock i').hasClass('fa-lock-open')) {
            coordinates.value = lngLat.lat + ', ' + lngLat.lng;
          }
        marker.on('dragend', onDragEnd);
      })
  
        //map.addControl(geocoder, 'bottom-left');
      jQuery('#location-input-section .location-input-name-container input').remove()
      jQuery('#location-input-section .location-input-name-container.location-input-container')[0].appendChild(geocoder.onAdd(map));
      var marker;
      jQuery('#addMarker').click(function() {
        var el = document.createElement('div');
        el.className = 'marker';
        // make a marker for each feature and add to the map
        marker = new mapboxgl.Marker({element: el, draggable: true})
          .setLngLat(map.getCenter())
          .addTo(map);
        marker.on('dragend', onDragEnd);
      });
      function onDragEnd() {
        var lngLat = marker.getLngLat();
        coordinates.value = lngLat.lat + ', ' + lngLat.lng;
      }
    }
    jQuery('#location-input-section > div:nth-child(4) > button:nth-child(1)').click(function() {
    marker.setDraggable(false);
    marker.getElement().classList.remove('fa-map-marker-plus');
    //marker.getElement().classList.add('fa-map-marker-alt');
    marker.getElement().classList.add('savedMarker');
    // set the popup
    var name = jQuery('#location-input-section > div:nth-child(1) > div:nth-child(1) > input:nth-child(3)').val();
    var desc = jQuery('#location-input-section > div:nth-child(2) > textarea:nth-child(3)').val();
    var popup = new mapboxgl.Popup({offset: 25, closeButton: false})
            .setHTML('<div class=\"popupWrapper\"><div class=\"name\">' + name + '</div><div class=\"comment\">' + desc + '</div></div>');
    marker.setPopup(popup);
    // allow multiple markers to be added
    marker = undefined;
    });
}

// Declaration of replacement for jQuery document.ready, it runs the check if DOM has loaded until it loads
var ready = (callback) => {
    if (document.readyState != "loading") callback();
    else document.addEventListener("DOMContentLoaded", callback);
}

// Replacement for jQuery document.ready; It runs the code after DOM is completely loaded
ready(() => {
    /////////// Paragraph Collapse Toggler, on Story Page and Item Page - story/item page only
    const paraToggler = document.querySelector('.descMore');
    if(paraToggler) {
        paraToggler.addEventListener('click', descToggler, false);
    }
 
  ///////////Login Container Toggler -over the whole site
    const logContainer = document.querySelector('#default-login-container');
    const logButton = document.querySelector('#default-lock-login');
    const closeLogContainer = document.querySelector('.item-login-close');

    if(logButton) {
        logButton.addEventListener('click', function() {
            logContainer.style.display = 'block';
        }, false);
        closeLogContainer.addEventListener('click', function() {
            logContainer.style.display = 'none';
        }, false);
    }
 
    //Item page full screen image splitter, remove 'editor bar' while resizing screen - item page only
    const splitter = document.querySelector('#item-splitter');
    if(splitter) {
        splitter.addEventListener('mousedown', function() {
            tinymce.remove();
            tct_viewer.initTinyWithConfig('#item-page-transcription-text');
        }, false);
    }
    // Item page, bind 'escape' key to close login warning if open, or full screen view(if open)
    //const escape = new KeyboardEvent('keydown');
    document.addEventListener('keydown',function(escape) {
        const itemLogContainer = document.querySelector('#item-page-login-container');
        const fullScreen = document.querySelector('#image-view-container');
        const lockWarning = document.querySelector('#locked-warning-container');

        if(escape.key === 'Escape') {
            if(itemLogContainer.style.display != 'none' || lockWarning.style.display != 'none') {
                itemLogContainer.style.display = 'none';
                lockWarning.style.display = 'none';
            } else if(fullScreen.style.display != 'none') {
                switchItemPageView();
            }
        }
    });
    // Item page, close login-modal/locked-warning on mouse press
    window.addEventListener('mousedown', function(event) {
        const itemLogContainer = document.querySelector('#item-page-login-container');
        const lockWarning = document.querySelector('#locked-warning-container');

        if(event.target == itemLogContainer) {
            itemLogContainer.style.display = 'none';
        } else if(event.target == lockWarning) {
            lockWarning.style.display = 'none';
        }
    });
    // Item page, close container with status indicators on click outside of window
    window.addEventListener('click', function(event) {
        let targetElement = event.target;
        const statusDropCont = document.querySelector('.status-dropdown-content');
        if(statusDropCont) {
            if(!targetElement.classList.contains('status-dropdown-content') && !targetElement.classList.contains('status-indicator')) {
                statusDropCont.classList.remove('show');
            }
        }
    });
    // Item page, enrichments, language-selector
    // Get all elements with class 'language-selector-background'
    const languageSelector = document.querySelectorAll('.language-selector-background');
    for(i = 0; i < languageSelector.length; i++) {
        const selectedElement = languageSelector[i].querySelector('select');
        // For each element, create a new DIV that will act as the selected item
        const selectorDiv = document.createElement('div');
        selectorDiv.classList.add('language-select-selected');
        // Separate transcription and description selectors
        if(selectedElement.parentElement.id === 'transcription-language-selector') {
            selectorDiv.id = 'transcription-language-custom-selector';
        } else if(selectedElement.parentElement.id === 'description-language-selector') {
            selectorDiv.id = 'description-language-custom-selector';
        }
        selectorDiv.textContent = selectedElement.options[selectedElement.selectedIndex].textContent;
        languageSelector[i].appendChild(selectorDiv);
        // For each element, create option list div
        const optionDiv = document.createElement('div');
        optionDiv.classList.add('language-item-select','select-hide');

        for(j = 0; j < selectedElement.length; j++) {
            if(selectedElement.options[j].textContent != 'Language(s) of the Document:'){// For each option, create DIV that will act as option item
            const optionItemDiv = document.createElement('div');
            optionItemDiv.classList.add('selected-option');
            optionItemDiv.textContent = selectedElement.options[j].textContent;
            optionDiv.appendChild(optionItemDiv);}
        }
        languageSelector[i].appendChild(optionDiv);
    }
    // Start of Image slider functions
    // New Js for Image slider
    const imgSliderCheck = document.querySelector('#img-slider');
    if(imgSliderCheck) {
        // function to show/hide images
        function showImages(start, end, images) {
            for(let img of images) {
                if(img.getAttribute('data-value') < start || img.getAttribute('data-value') > end) {
                    img.style.display = 'none';
                } else {
                    img.style.display = 'inline-block';
                }
            }
        }
        // Only item page(start slider with the item on the page)
        const currentItem = document.querySelector('#slide-start');
        //
        const imgStickers = document.querySelectorAll('.slide-sticker');
        const windowWidth = document.querySelector('#img-slider').clientWidth;
        let sliderStart = 1; // First Image to the left
        let sliderEnd = 0; // Last Image to the right
        const nextSet = document.querySelector('.next-slide');
        const prevSet = document.querySelector('.prev-slide');
        const leftSpanNumb = document.querySelector('#left-num');
        const rightSpanNumb = document.querySelector('#right-num');
        let currentDot = 1;
        let step = 0; // number of images on screen
        if(windowWidth > 1200) {
            step = 9;
        } else if(windowWidth > 800) {
            step = 5;
        } else {
            step = 3;
        }

        sliderEnd = step;

        if(imgStickers.length <= step){
            prevSet.style.display = 'none';
            nextSet.style.display = 'none';
        }
        leftSpanNumb.textContent = sliderStart;
        rightSpanNumb.textContent = sliderEnd;
        // check if there are more images than it fits on the screen
        if(nextSet.style.display != 'none') {
            showImages(sliderStart, sliderEnd, imgStickers);
        }
        // Slider dots
        const dotContainer = document.querySelector('#dot-indicators');
        const numberDots = Math.ceil(imgStickers.length / step);
        for(let i = 0; i < numberDots; i++) {
            const sliderDot = document.createElement('div');
            sliderDot.classList.add('slider-dot');
            sliderDot.setAttribute('data-value', (i+1));
            dotContainer.appendChild(sliderDot);
        }

        const sliderDots = document.querySelectorAll('.slider-dot');
        
        for(let dot of sliderDots) {
            dot.addEventListener('click', function() {
                currentDot = parseInt(dot.getAttribute('data-value'));
                dot.classList.add('current');
                if(dot.getAttribute('data-value') * step > imgStickers.length) {
                    sliderStart = (imgStickers.length - step) + 1;
                    sliderEnd = imgStickers.length;
                } else {
                    sliderEnd = parseInt(dot.getAttribute('data-value')) * step;
                    sliderStart = (sliderEnd - step) + 1;
                }
                showImages(sliderStart, sliderEnd, imgStickers);
                leftSpanNumb.textContent = sliderStart;
                rightSpanNumb.textContent = sliderEnd;
                for(let dot of sliderDots) {
                    if(dot.getAttribute('data-value') < currentDot || dot.getAttribute('data-value') > currentDot) {
                        if(dot.classList.contains('current')){
                            dot.classList.remove('current');
                        }
                    }
                }
            })
        }
        if(currentItem) {
          let currentPosition = Math.floor(parseInt(currentItem.textContent) / step);
          for(let dot of sliderDots) {
            if(currentPosition + 1 === parseInt(dot.getAttribute('Data-value'))){
              dot.click();
            }
          }
        }
        nextSet.addEventListener('click', function() {
            currentDot += 1;
            if(currentDot > numberDots ) {
                currentDot = 1;
            }
            if(rightSpanNumb.textContent == imgStickers.length) {
                sliderStart = 1;
                sliderEnd = step;
            } else if(sliderEnd + step <= imgStickers.length) {
                sliderStart = sliderStart + step;
                sliderEnd = sliderEnd + step;
            } else {
                sliderStart = (imgStickers.length - step) + 1;
                sliderEnd = imgStickers.length;
            }
            showImages(sliderStart, sliderEnd, imgStickers);
            leftSpanNumb.textContent = sliderStart;
            rightSpanNumb.textContent = sliderEnd;
            for(let dot of sliderDots) {
                if(parseInt(dot.getAttribute('data-value')) < currentDot || parseInt(dot.getAttribute('data-value')) > currentDot) {
                    if(dot.classList.contains('current')){
                        dot.classList.remove('current');
                    }
                } else {
                    dot.classList.add('current');
                }
            }
        })
        prevSet.addEventListener('click', function() {
            if(currentDot - 1 < 1) {
                currentDot = numberDots;
            } else {
                currentDot -= 1;
            }
            if(leftSpanNumb.textContent == '1') {
                sliderEnd = imgStickers.length;
                sliderStart = (imgStickers.length - step) + 1;
            } else if(sliderStart - step < 1) {
                sliderStart = 1;
                sliderEnd = step;
            } else {
                sliderEnd = sliderEnd - step;
                sliderStart = sliderStart - step;
            }
            showImages(sliderStart, sliderEnd, imgStickers);
            leftSpanNumb.textContent = sliderStart;
            rightSpanNumb.textContent = sliderEnd;
            for(let dot of sliderDots) {
                if(parseInt(dot.getAttribute('data-value')) < currentDot || parseInt(dot.getAttribute('data-value')) > currentDot) {
                    if(dot.classList.contains('current')){
                        dot.classList.remove('current');
                    }
                } else {
                    dot.classList.add('current');
                }
            }
        })
    }

    // Item Page, Open full screen if user comes to page from fullscreen viewer
    if(document.querySelector('#openseadragon')){
        const url_string = window.location.href;
        const url = new URL(url_string);
        const fullScreen = url.searchParams.get('fs');
        if(fullScreen) {
            setTimeout(() => {document.querySelector('#full-page').click();}, 10);
        }
    }

    // Switch between transcription view and transcription editor and hide/show 'no-text' selector
    const transcriptionSwitch = document.querySelector('#switch-tr-view');
    if(transcriptionSwitch) {
        const transEditContainer = document.querySelector('#transcription-edit-container');
        const transViewContainer = document.querySelector('#transcription-view-container');
        transcriptionSwitch.addEventListener('click', function() {
            if(transEditContainer.style.display == 'none') {
                transEditContainer.style.display = 'block';
                transViewContainer.style.display = 'none';
                transcriptionSwitch.querySelector('i').classList.replace('fa-pencil', 'fa-times');
            } else {
                transEditContainer.style.display = 'none';
                transViewContainer.style.display = 'block';
                transcriptionSwitch.querySelector('i').classList.replace('fa-times', 'fa-pencil');
            }
        });

    }

    // Full screen story description collapse
    const fullStoryCollapse = document.querySelector('#story-full-collapse');
    if(fullStoryCollapse) {
        const fullStoryContainer = document.querySelector('#full-v-story-description');
        fullStoryCollapse.addEventListener('click', function() {
            if(fullStoryContainer.style.maxHeight == '40vh') {
                fullStoryContainer.style.maxHeight = 'unset';
                fullStoryCollapse.textContent = 'Show Less';
            } else {
                fullStoryContainer.style.maxHeight = '40vh';
                fullStoryCollapse.textContent = 'Show More';
            }
        })
    }
    // Change complete item status
    const changeItemStatus = document.querySelector('.change-all-status');
    const itemStatusOptions = document.querySelector('#item-status-selector');
    if(changeItemStatus) {
        changeItemStatus.addEventListener('click', function() {
            if(itemStatusOptions.style.display == 'none') {
                itemStatusOptions.style.display = 'block';
            } else {
                itemStatusOptions.style.display = 'none';
            }
        })
        // Update current page headings with new status
        const allNS = document.querySelector('#all-not-started');
        const allEd = document.querySelector('#all-edit');
        const allRe = document.querySelector('#all-review');
        const allCo = document.querySelector('#all-complete');

        const transcriptionH = document.querySelector('#startTranscription .status-display');
        const locationH = document.querySelector('#startLocation .status-display');
        const enrichmentH = document.querySelector('#startDescription .status-display');


        allNS.addEventListener('click',function() {
            
            transcriptionH.style.backgroundColor = '#eeeeee';
            transcriptionH.querySelector('span').textContent = 'NOT STARTED';

            locationH.style.backgroundColor = '#eeeeee';
            locationH.querySelector('span').textContent = 'NOT STARTED';

            enrichmentH.style.backgroundColor = '#eeeeee';
            enrichmentH.querySelector('span').textContent = 'NOT STARTED';
        })

        allEd.addEventListener('click', function() {
          transcriptionH.style.backgroundColor = '#fff700';
          transcriptionH.querySelector('span').textContent = 'EDIT';

          locationH.style.backgroundColor = '#fff700';
          locationH.querySelector('span').textContent = 'EDIT';

          enrichmentH.style.backgroundColor = '#fff700';
          enrichmentH.querySelector('span').textContent = 'EDIT';
        })

        allRe.addEventListener('click', function() {
          transcriptionH.style.backgroundColor = '#ffc720';
          transcriptionH.querySelector('span').textContent = 'REVIEW';

          locationH.style.backgroundColor = '#ffc720';
          locationH.querySelector('span').textContent = 'REVIEW';

          enrichmentH.style.backgroundColor = '#ffc720';
          enrichmentH.querySelector('span').textContent = 'REVIEW';
        })

        allCo.addEventListener('click', function() {
          transcriptionH.style.backgroundColor = '#61e02f';
          transcriptionH.querySelector('span').textContent = 'COMPLETED';

          locationH.style.backgroundColor = '#61e02f';
          locationH.querySelector('span').textContent = 'COMPLETED';

          enrichmentH.style.backgroundColor = '#61e02f';
          enrichmentH.querySelector('span').textContent = 'COMPLETED';
        })
    }

    const splitResize = document.querySelector('#item-splitter');
    if(splitResize) {
        splitResize.addEventListener('mouseup', function() {
            map.resize();
        })
    }
    
 
});