var home_url = WP_URLs.home_url;
var network_home_url = WP_URLs.network_home_url;
var map, marker;

function uninstallEventListeners() {
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
    const transcriptionEditor = document.querySelector('#item-page-transcription-text');
    if(noTextSelect && transcriptionEditor) {
        transcriptionEditor.addEventListener('keyup', function() {
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
    // const saveAlltags = document.querySelector('#save-all-tags');
    // if(saveAlltags) {
    //     saveAlltags.addEventListener('click', function() {
    //         if(jQuery('#startdateentry').val().length > 0 || jQuery('#enddateentry').val().length > 0) {
    //             setTimeout(()=>{document.querySelector('#item-date-save-button').click()}, 100);
    //         }
    //         if(jQuery('#person-firstName-input').val().length > 0 || jQuery('#person-lastName-input').val().length > 0) {
    //             setTimeout(()=>{document.querySelector('#save-personinfo-button').click()}, 700);
    //         }
    //         if(jQuery('#keyword-input').val().length > 0) {
    //             setTimeout(()=>{document.querySelector('#keyword-save-button').click()}, 1200);
    //         }
    //         if(jQuery('#link-input-container .link-url-input input').val().length > 0 || jQuery('#link-input-container .link-description-input textarea').val().length > 0) {
    //             setTimeout(()=>{document.querySelector('#link-save-button').click()}, 1600);
    //         }
    //     });
    // }

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

    jQuery('#item-data-content').mousedown(function(event) {
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
                    if(h.getAttribute('id') == 'description-language-custom-selector') {
                        h.innerHTML = this.innerHTML;
                        if(!document.querySelector('#language-sel-placeholder')) {
                            h.insertAdjacentHTML('afterbegin', "<span id='language-sel-placeholder' class='language-select-selected'>Language of Description: </span><span class='desc-margin'>&nbsp</span>");
                        }

                    }
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

  const itemPageKeyWords = document.querySelector('#keyword-input');
  var keyWordList = [];
  if(itemPageKeyWords){
  jQuery.post(home_url + '/wp-content/themes/transcribathon/admin/inc/custom_scripts/send_ajax_api_request.php', {
      'type': 'GET',
      'url': TP_API_HOST + '/tp-api/properties?PropertyType=Keyword'
  },
  function(response) {

    var response = JSON.parse(response);
    var content = JSON.parse(response.content);
    for (var i = 0; i < content.length; i++) {
      keyWordList.push(content[i]['PropertyValue']);
    }
    jQuery( "#keyword-input" ).autocomplete({
      source: keyWordList,
      delay: 100,
      minLength: 3
    });
  });
  }

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
            // if(document.querySelector('#mce-wrapper-transcription.htr-active-tr')) {
            //     tinymce.get('item-page-transcription-text').mode.set('readonly');
            // }
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
        buttonImage: `${home_url}/wp-content/themes/transcribathon/admin/inc/custom_shortcodes/upload-images/icon_calendar.svg`
    });
    jQuery("#startdateentry").val(startDate);
    jQuery("#enddateentry").val(endDate);

    jQuery( "#person-birthDate-input, #person-deathDate-input" ).datepicker({
        dateFormat: "dd/mm/yy",
        changeMonth: true,
        changeYear: true,
        yearRange: "100:+10",
        showOn: "button",
        buttonImage:  `${home_url}/wp-content/themes/transcribathon/admin/inc/custom_shortcodes/upload-images/icon_calendar.svg`
    });
    jQuery("#person-birthDate-input").val(birthDate);
    jQuery("#person-deathDate-input").val(deathDate);

    if(document.querySelector('#item-page-transcription-text')) {
        tct_viewer.initTinyWithConfig('#item-page-transcription-text');
        setToolbarHeight();
        // if(document.querySelector('#mce-wrapper-transcription.htr-active-tr')) {
        //     tinymce.get('item-page-transcription-text').mode.set('readonly');
        // }
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
function switchItemTab(event, tabName, tabControler) {
    // if (tabName == 'info-tab' && !document.querySelector('.single-meta')) {
    //     document.querySelector('#meta-collapse').click();
    // }
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
    document.getElementById(tabControler).classList.add('active');

    if(map) {
        map.resize();
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

    // Move map to map tab
    let mapMap = document.getElementById('full-view-map');
    let fsMapContainer = document.getElementById('full-screen-map-placeholder');
    let mapEditor = document.getElementById('location-section');
    let normalMapContainer = document.getElementById('normal-map');

    if(mapMap != null) {
      if(fsMapContainer.querySelector('#full-view-map') != null) {
        normalMapContainer.appendChild(mapMap);
        normalMapContainer.parentElement.appendChild(mapEditor);
      } else {
        fsMapContainer.appendChild(mapMap);
        fsMapContainer.appendChild(mapEditor);
      }
    }
    // Move People to People tab
    const pplEditor = document.getElementById('tagging-section');
    const pplViewCont = document.getElementById('enrich-view');
    const pplFsCont = document.getElementById('tag-tab');
    if(pplEditor != null) {
        if(pplFsCont.querySelector('#tagging-section') == null) {
            pplFsCont.insertBefore(pplEditor, pplFsCont.querySelector('#ppl-auto-e-container'));
        } else {
            pplViewCont.appendChild(pplEditor);
        }
    }
    // Move Enrichments to Enrichments tab
    const enrichEditor = document.getElementById('description-editor');
    const enrichViewCont = document.getElementById('description-view');
    const enrichFsCont = document.getElementById('description-tab');

    if(enrichEditor != null) {
        if(enrichFsCont.querySelector('#description-editor') == null) {
            enrichFsCont.appendChild(enrichEditor);
        } else {
            enrichViewCont.appendChild(enrichEditor);
        }
    }
    // Move Metadata and Story Description to metadata fs tab
    const fsMetaCont = document.getElementById('full-v-metadata');
    const normalMetaCont = document.getElementById('meta-left');
    const metaData = document.getElementById('meta-container');
    const storyDesc = document.getElementById('storydesc');
    const fsStoryDesc = document.getElementById('full-v-story-description');
    const norStoryDesc = document.getElementById('meta-right');


    if(metaData != null) {
        if(fsMetaCont.querySelector('#meta-container') == null) {
            fsMetaCont.appendChild(metaData);
            fsStoryDesc.appendChild(storyDesc);
        } else {
            normalMetaCont.appendChild(metaData);
            norStoryDesc.appendChild(storyDesc);
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

      window.history.replaceState(null, null, newUrl);
      document.querySelector('#full-width').click();

   } else {
     var descriptionText = jQuery('#item-page-description-text').val();
     var descriptionLanguage = jQuery('#description-language-selector select').val();

     const imgViewer = document.querySelector('#openseadragon');
     const nSContainer = document.querySelector('#full-view-l'); // out of full screen
     // Move viewer
     imgViewer.style.height = '560px';
     nSContainer.prepend(imgViewer);
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
    window.history.replaceState(null, null, newUrl);


   }
    //installEventListeners();
    if(map) {
        map.resize();
    }
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
// Check if the tinymce contains invalid html tags
function isWhitelisted(tinyText) {
    // WIP
    let tester = new DOMParser().parseFromString(('a ' + tinyText), 'text/html');
    if(tester.body.querySelector('*:not(p)') != null) {
        window.alert('Invalid Input');
        return 0;
    }
    return tinyText;
}

// Updates the item description
function updateItemDescription(itemId, userId, editStatusColor, statusCount) {
    jQuery('#item-description-spinner-container').css('display', 'block')

    var descriptionLanguage = jQuery('#description-language-selector select').val();

    updateDataProperty('items', itemId, 'DescriptionLanguage', descriptionLanguage);

    var description = jQuery('#item-page-description-text').val()

    // Check for html tags
    if(isWhitelisted(description) == 0) {
        jQuery('#item-description-spinner-container').css('display', 'none')
        return null;
    } else {
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

                // Update description text and language out of full screen when saving new values
                const descLangCont = document.querySelector('.description-language div');
                var descLanguage = document.querySelector('#description-language-custom-selector').textContent;

                if(descLanguage.includes('Language of Description: ')) {
                    descLanguage = descLanguage.replace('Language of Description: ', '');
                }
                // Update description
                document.querySelector('.current-description').textContent = description;
                // Update Language
                descLangCont.parentElement.style.display = 'block';

                if(descLangCont.querySelector('.language-single')) {
                  descLangCont.querySelector('.language-single').textContent = descLanguage;
                } else {
                  var newDescLang = document.createElement('div');
                  newDescLang.textContent = descLanguage;
                  newDescLang.classList.add('language-single');
                  descLangCont.appendChild(newDescLang);
                }
                amount = 1;
                //
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
}

// Updates the item transcription
function updateItemTranscription(itemId, userId, editStatusColor, statusCount) {
    jQuery('#transcription-update-button').removeClass('theme-color-background');
    jQuery('#transcription-update-button').prop('disabled', true);
    jQuery('#item-transcription-spinner-container').css('display', 'block')

    let checkIfDirty = '';
    var noText = 0;
    if (jQuery('#no-text-checkbox').is(':checked') && document.querySelector('#item-page-transcription-text').textContent == '') {
        noText = 1
    }
    if(noText == 0) {
        checkIfDirty = tinymce.get('item-page-transcription-text').getContent({format : 'text'});
    } else {
        checkIfDirty = 1;
    }

    if(isWhitelisted(checkIfDirty) == 0){
        jQuery('#item-transcription-spinner-container').css('display', 'none')
        return null;
    } else {
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
        if (jQuery('#no-text-checkbox').is(':checked') && document.querySelector('#item-page-transcription-text').textContent == '') {
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

            for (var i = 0; i < JSON.parse(response.content)["Transcriptions"].length; i++) {
                if (JSON.parse(response.content)["Transcriptions"][i]["CurrentVersion"] == 1) {
                    currentTranscription = JSON.parse(response.content)["Transcriptions"][i]["TextNoTags"];
                }
            }

            if(noText === 0) {
                const wordcount = tinymce.get('item-page-transcription-text').plugins.wordcount;
                // var newTranscriptionLength = tinyMCE.editors[jQuery('#item-page-transcription-text').attr('id')].getContent({format : 'text'}).length;
                // var newTranscriptionLength = tinyMCE.editors.get([jQuery('#item-page-transcription-text').attr('id')]).getContent({format : 'text'}).length;
                if(jQuery('#item-page-transcription-text').text()) {
                    var newTranscriptionLength = wordcount.body.getCharacterCountWithoutSpaces();
                }
            } else {
                var newTranscriptionLength = 0;
            }

            // Prepare data and send API request
            data = {
                UserId: userId,
                ItemId: itemId,
                CurrentVersion: 1,
                NoText: noText,
                Languages: transcriptionLanguages,
            }

            if (jQuery('#item-page-transcription-text').html()) {
                data['Text'] = tinymce.get('item-page-transcription-text').getContent({format : 'html'});
                data['TextNoTags'] = tinymce.get('item-page-transcription-text').getContent({format : 'text'});
            } else {
                data['Text'] = "";
                data['TextNoTags'] = "";
            }
            const curTrToUpdate = data['Text'];

            var dataString= JSON.stringify(data);

            jQuery.post(home_url + '/wp-content/themes/transcribathon/admin/inc/custom_scripts/send_ajax_api_request.php', {
                'type': 'POST',
                'url': TP_API_HOST + '/tp-api/transcriptions',
                'data': data
            },
            // Check success and create confirmation message
            function(response) {
                currentTranscription = currentTranscription.replace(/\s+/g, '');

                var amount = newTranscriptionLength - currentTranscription.length
                if (amount > 0) {
                    amount = amount;
                } else {
                    amount = 0;
                }
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
                const selTrLangs = document.querySelectorAll('#transcription-selected-languages ul li');
                const selLanCont = document.querySelector('.transcription-language div');
                if(selLanCont) {
                    const oldLanguages = selLanCont.querySelectorAll('.language-single');
                }

                if (response.code == "200") {

                    if (itemCompletion == "Not Started") {
                        changeStatus(itemId, "Not Started", "Edit", "CompletionStatusId", 2, editStatusColor, statusCount)
                    }
                    if (transcriptionCompletion == "Not Started") {
                        changeStatus(itemId, "Not Started", "Edit", "TranscriptionStatusId", 2, editStatusColor, statusCount)
                    }
                    document.querySelector('.current-transcription').innerHTML = curTrToUpdate;
                    // Remove old languages
                    if(selLanCont) {
                        selLanCont.innerHTML = '';
                    }

                    // Add new Languages
                    for(let langSingle of selTrLangs) {
                        let langEl = document.createElement('div');
                        langEl.classList.add('language-single');
                        let langName = langSingle.textContent.split(' ');
                        langEl.textContent = langName[0];
                        selLanCont.appendChild(langEl);
                    }

                    if(document.querySelector('#no-text-placeholder')) {
                        document.querySelector('#no-text-placeholder').style.display = 'none';
                        document.querySelector('.current-transcription').style.display = 'block';
                        document.querySelector('.current-transcription').style.paddingLeft = '24px';
                        if(curTrToUpdate.length > 699) {
                            document.querySelector('#transcription-collapse-btn').style.display = 'block';
                        }
                    }
                    document.querySelector('#current-tr-view').innerHTML = curTrToUpdate;
                }
                jQuery('#item-transcription-spinner-container').css('display', 'none')

            });
        });
    }
}

// Adds an Item Property
function addItemProperty(itemId, userId, type, editStatusColor, statusCount, propertyValue, e) {
    if (jQuery('#type-' + propertyValue + '-checkbox').is(':checked')) {
        jQuery('#type-' + propertyValue + '-checkbox').attr("checked", true);
    } else {
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
            const keyWordCont = document.querySelector('#doc-type-view');
            let newKeyWord = document.createElement('div');
            newKeyWord.classList.add('keyword-single');
            newKeyWord.textContent = e.name;
            keyWordCont.appendChild(newKeyWord);
        });
    } else {
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
                    const keyWordCont = document.querySelector('#doc-type-view');
                    const oldKeyWords = keyWordCont.querySelectorAll('.keyword-single');
                    for(let keyW of oldKeyWords) {
                        if(keyW.textContent === e.name) {
                            keyWordCont.removeChild(keyW);
                        }
                    }
                });
            } else {
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
                } else {
                    alert(response.content);
                    return 0;
                }
            });
        } else {
            updateDataProperty("items", itemId , fieldName, value);
        }
    } else {
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
    } else {
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

    // Add place role to the location
    let placeRole = "Other";
    if(document.querySelector('#place-role').checked) {
        placeRole = 'CreationPlace';
    }
    // Prepare data and send API request
    locationName = escapeHtml(jQuery('#location-name-display input').val());
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

    description = escapeHtml(jQuery('#location-input-section .location-input-description-container textarea').val());
    wikidata = escapeHtml(jQuery('#location-input-geonames-search-container > input').val().split(";"));

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
            PlaceRole: placeRole,
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
            if(document.querySelector('#location-input-section').style.display == 'block') {
                document.querySelector('#location-input-section').style.display = 'none';
            }
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
    creationDate = 'Other';
    if(document.querySelector('#creation-date').checked) {
        creationDate = 'CreationDate';
    }
    // Prepare data and send API request
    data = {
        DateStartDisplay: jQuery('#startdateentry').val(),
        DateEndDisplay: jQuery('#enddateentry').val(),
        DateRole: creationDate
    }
    startDate = jQuery('#startdateentry').val().split('/');
    if (!isNaN(startDate[2]) && !isNaN(startDate[1]) && !isNaN(startDate[0])) {
        data['DateStart'] = startDate[2] + "-" + startDate[1] + "-" + startDate[0];
    } else if (startDate.length == 1 && startDate[0].length <= 4 && startDate[0].length > 0 && !isNaN(startDate[0])) {
        data['DateStart'] = startDate[0] + "-01-01";
    } else {
        if (startDate[0] != "" && startDate[0] != null) {
            jQuery('#item-date-spinner-container').css('display', 'none')
            alert("Please enter a valid date or year");
            return 0
        }
    }

    endDate = jQuery('#enddateentry').val().split('/');
    if (!isNaN(endDate[2]) && !isNaN(endDate[1]) && !isNaN(endDate[0])) {
        data['DateEnd'] = endDate[2] + "-" + endDate[1] + "-" + endDate[0];
    } else if (endDate.length == 1 && endDate[0].length <=4 && endDate[0].length > 0 && !isNaN(endDate[0])) {
        data['DateEnd'] = endDate[0] + "-01-01";
    } else {
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
            // Update date 'view' when changing date in editor
            // document.querySelector('.date-bottom .start-date').textContent = startDate.join('/');
            // document.querySelector('.date-bottom .end-date').textContent = endDate.join('/');
            // document.querySelector('.date-bottom').style.display = 'block';
            // document.querySelector('.date-top').style.display = 'block';

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
    let personRole = 'PersonMentioned';

    if(document.querySelector('#main-actor').checked) {
        personRole = 'AddressedPerson';
    } else if (document.querySelector('#doc-creator').checked) {
        personRole = 'DocumentCreator';
    }

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
        PersonRole: personRole,
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

            if(document.querySelector('#ppl-role-form')) {
                document.querySelector('#ppl-role-form').reset();
            }


        });
    });
}

function saveKeyword(itemId, userId, editStatusColor, statusCount) {
    jQuery('#item-keyword-spinner-container').css('display', 'block')
    value = escapeHtml(jQuery('#keyword-input').val());

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
    url = escapeHtml(jQuery('#link-input-container .link-url-input input').val());
    description = escapeHtml(jQuery('#link-input-container .link-description-input textarea').val());

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
            const content = JSON.parse(response.content);
            const locContainer = document.querySelector('.location-display-container');
            const storyLoc = locContainer.querySelector('.story-location');

            // Empty the old location list
            locContainer.innerHTML = "";

            for(let location of content) {

                let placeRoleCheck = location['PlaceRole'] == 'CreationPlace' ? 'checked' : '' ;

                locContainer.innerHTML +=
                    `<div id='location-${escapeHtml(location['PlaceId'])}' >` +
                        `<div id='location-data-output-${location['PlaceId']}' class='location-single'>` +
                            `<img src='${home_url}/wp-content/themes/transcribathon/images/location-icon.svg' height='20px' width='20px' alt='location-icon'>` +
                            `<p><b>${htmlDecode(location['Name'])}</b> (${escapeHtml(location['Latitude'])}, ${escapeHtml(location['Longitude'])})</p>` +
                            // Check if there is description : don't add <p> if not
                            `${location['Comment'] ?
                                `<p style='margin-top:0px;font-size:13px;'>Description: <b> ${htmlDecode(location['Comment'])}</b></p>`
                                :
                                ``
                            }` +
                            // Check for Wikidata : ^^
                            `${location['WikidataId'] ?
                                `<p style='margin-top:0px;font-size:13px;margin-left:30px;'>Wikidata Reference: <b><a href='http://wikidata.org/wiki/${location['WikidataId']}' ` +
                                `style='text-decoration:none;' target='_blank'>${escapeHtml(location['WikidataName'])}, ${escapeHtml(location['WikidataId'])}</a></b></p>`
                                :
                                ''
                            }` +
                            `<div class='edit-delete-btns'>` +
                                `<i class='login-required edit-item-data-icon fas fa-pencil theme-color-hover' onClick='openLocationEdit(${escapeHtml(location['PlaceId'])})'></i>` +
                                `<i class='login-required edit-item-data-icon fas fa-trash-alt theme-color-hover' onClick='deleteItemData("places", ${escapeHtml(location['PlaceId'])}, ${itemId}, "place", ${userId})'></i>` +
                            `</div>` +
                        `</div>` +

                        `<div id='location-data-edit-${escapeHtml(location['PlaceId'])}' class='location-data-edit-container' style='display:none;'>` +
                            // Input Top
                            `<div class='location-input-section-top'>` +
                                `<div class='location-input-name-container' style='min-height:25px;'>` +
                                    `<label>Location Name:</label>` +
                                    `<input type='text' class='edit-input' value='${isItString(location['Name'])}' name='' placeholder=''>` +
                                `</div>` +
                                `<div class='location-input-coordinates-container' style='min-height:25px;'>` +
                                    `<label>Coordinates: </label>` +
                                    `<span class='required-field'>*</span>` +
                                    `<input type='text' class='edit-input' value='${isItString(location['Latitude'])}, ${isItString(location['Longitude'])}' name='' placeholder=''>` +
                                `</div>` +
                                `<div style='clear:both;'></div>` +
                            `</div>` +
                            // Description
                            `<div class='location-input-description-container' style='height:50px;'>` +
                                `<label>Description: ` +
                                    `<i class='fas fa-question-circle' style='font-size:16px;cursor:pointer;margin-left:4px;' title='Add more information about this location, e.g. building name, or it's significance to the item...'></i>` +
                                `</label>` +
                                `<textarea rows='2' id='ldsc' class='edit-input gsearch-form' style='resize:none;' type='text'>` +
                                `${location['Comment'] != 'NULL' ?
                                `${escapeHtml(location['Comment'])}`
                                :
                                ``
                                }` +
                                `</textarea>` +
                            `</div>` +
                            // Creation Place
                            `<div class='loc-type'>` +
                                `<label class='loc-checkbox-container' style='width:100%!important;'>` +
                                    `<span style='display:inline-block;width:30%;'> Creation Place ` +
                                    `<i class='fas fa-question-circle' style='font-size:16px;cursor:pointer;margin-left:4px;' title='Is this location the place where the document was created?'></i>` +
                                    `</span>` +
                                    `<span class='loc-check-right' style='float:none!important;display:inline-block;'>` +
                                        `<input type='checkbox' class='loc-type-check' id='place-role-${location['PlaceId']}' name='CreationPlace' value='Creation Place' ${placeRoleCheck}>` +
                                        `<span class='loc-checkmark'></span>` +
                                    `</span>` +
                                `</label>` +
                            `</div>` +
                            // Wikidata ref container
                            `<div class='location-input-geonames-container location-search-container' style='min-height:25px;margin: 5px 0;'>` +
                                `<label>Wikidata Reference:` +
                                    `<i class='fas fa-question-circle' style='font-size:16px;cursor:pointer;margin-left:4px;' title='Identify this location by searching its name or code on WikiData'></i>` +
                                `</label>` +
                                `<input class='edit-input' id='lgns' type='text' placeholder='' name='' value='${isItString(location['WikidataName'])};${isItString(location['WikidataId'])}' >` +
                            `</div>` +
                            // Buttons
                            `<div class='form-buttons-right'>` +
                                `<div class='form-btn-left'>` +
                                    `<button class='theme-color-background edit-location-cancel' onClick='openLocationEdit(${location['PlaceId']})'>` +
                                        `CANCEL` +
                                    `</button>` +
                                `</div>` +
                                `<div class='form-btn-right'>` +
                                    `<button class='theme-color-background edit-location-save' onClick='editItemLocation(${location['PlaceId']}, ${itemId}, ${userId})'>` +
                                        `SAVE` +
                                    `</button>` +
                                `</div>` +
                                // Spinner
                                `<div id='item-location-${location['PlaceId']}-spinner-container' class='spinner-container spinner-container-right'>` +
                                    `<div class='spinner'></div>` +
                                `</div>` +
                                `<div style='clear:both;'></div>` +
                            `</div>` +
                            `<div style='clear:both;'></div>` +
                        `</div>` +
                    `</div>` ;
            }
            if(storyLoc) {
                locContainer.appendChild(storyLoc);
            }

        }
    });
}
// Function to check if it's a string and position to now if we need ',' in front or no
function isItString(qStr, posInStr = 1) {
    if(qStr != undefined && qStr != 'NULL') {
        if(posInStr < 2) {
            return escapeHtml(qStr);
        } else {
            return `, ${escapeHtml(qStr)}`;
        }
    } else {
        return '';
    }
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
            const personOutCont = document.querySelector('#item-person-list');

            personOutCont.innerHTML = '';

            for(let person of content) {

              let docCreatorCheck = person['PersonRole'] == 'DocumentCreator' ? 'checked' : '';
              let addressedPersonCheck = person['PersonRole'] == 'AddressedPerson' ? 'checked' : '';
              let personMentionedCheck = person['PersonRole'] == 'PersonMentioned' ? 'checked' : '';

              person['BirthDate'] = person['BirthDate']
                ? new Date(person['BirthDate']).toLocaleDateString('en-GB')
								: person['BirthDateDisplay'] || null;
              person['DeathDate'] = person['DeathDate']
                ? new Date(person['DeathDate']).toLocaleDateString('en-GB')
								: person['DeathDateDisplay'] || null;

							// careful, using non-strict comparators for checking null and undefined
							const birthArray = [];
							if (person['BirthDate'] != null) { birthArray.push(person['BirthDate']); }
							if (person['BirthPlace'] != null) { birthArray.push(person['BirthPlace']); }
							const birthString = birthArray.join(', ');

							const deathArray = [];
							if (person['DeathDate'] != null) { deathArray.push(person['DeathDate']); }
							if (person['DeathPlace'] != null) { deathArray.push(person['DeathPlace']); }
							const deathString = deathArray.join(', ');

							const personDateArray = [];
							if (birthString) { personDateArray.push('Birth: ' + birthString); }
							if (deathString) { personDateArray.push('Death: ' + deathString); }
							const personDateString = personDateArray.join(' - ');

                personOutCont.innerHTML +=
                    `<div id='person-${person['PersonId']}'>` +
                        `<div class='single-person'>` +
                            `<i class='fas fa-user person-i' style='float:left;margin-right: 5px;'></i>` +
                            `<p class='person-data'>` +
                                `${person['FirstName'] ? htmlDecode(person['FirstName']) : ''}
																 ${person['LastName']  ? htmlDecode(person['LastName'])  : ''}
																	${personDateString   ? ' (' + personDateString + ')'   : ''}` +
                            `</p>` +
                            `${person['Description'] ?
                                `<p class='person-description'>Description: ${htmlDecode(person['Description'])}</p>`
                                :
                                ``
                            }` +
                            `${person['Link'] ?
                                `<p class='person-description'><a href='http://www.wikidata.org/wiki/${escapeHtml(person['Link'])}' target='_blank'>${escapeHtml(person['Link'])}</a></p>`
                                :
                                ``
                            }` +
                            `<div class='edit-del-person'>` +
                                `<i class='login-required edit-item-data-icon fas fa-pencil theme-color-hover' onClick='openPersonEdit(${person['PersonId']})'></i>` +
                                `<i class='login-required edit-item-data-icon fas fa-trash-alt theme-color-hover' onClick='deleteItemData("persons", ${person['PersonId']}, ${itemId}, "person", ${userId})'></i>` +
                            `</div>` +
                            `<div style='clear:both;'></div>` +
                        `</div>` +

                        `<div id='person-data-edit-${person['PersonId']}' class='person-data-edit-container person-item-data-container'>` +
                            `<div class='person-input-names-container'>` +
                                `<input type='text' id='person-${person['PersonId']}-firstName-edit' class='input-response person-input-field person-re-edit'
                                    placeholder='&nbsp First Name' value='${isItString(person['FirstName'])}'>` +
                                `<input type='text' id='person-${person['PersonId']}-lastName-edit' class='input-response person-input-field person-re-edit-right'
                                    placeholder='&nbsp Last Name' value='${isItString(person['LastName'])}'>` +
                            `</div>` +

                            `<div class='person-input-desc-cont'>` +
                                `<div class='person-desc-left' style='margin-bottom: 0!important;'>` +
                                    `<div class='person-description-input'>` +
                                        `<input type='text' id='person-${person['PersonId']}-description-edit' class='input-response person-edit-field'
                                            placeholder='&nbsp; Add more info to this person...' value='${person['Description'] ? htmlDecode(person['Description']) : ''}'>` +
                                    `</div>` +
                                    `<div class='person-description-input'>` +
                                        `<input type='text' id='person-${person['PersonId']}-wiki-edit' class='input-response person-edit-field'
                                            placeholder='&nbsp; Add Wikidata ID to this person.' title='e.g. Wikidata Title ID' value='${person['Link'] ? htmlDecode(person['Link']) : ''}'>` +
                                    `</div>` +
                                `</div>` +
                                `<div class='person-desc-right'>` +
                                    `<form id='ppl-role-form-${person['PersonId']}'>` +
                                        `<div class='person-role-input' style='margin-bottom: 0!important;'>` +
                                            `<label id='document-creator-${person['PersonId']}'>` +
                                                `<input type='radio' id='doc-creator-${person['PersonId']}' name='person-role' value='Document Creator' ${docCreatorCheck}>` +
                                                `<span> Document Creator </span>` +
                                            `</label>` +
                                            `</br>` +
                                            `<label id='important-person-${person['PersonId']}'>` +
                                                `<input type='radio' id='main-actor-${person['PersonId']}' name='person-role' value='Person Addressed' ${addressedPersonCheck}>` +
                                                `<span> Person Addressed </span>` +
                                            `</label>` +
                                            `</br>` +
                                            `<label id='others-${person['PersonId']}'>` +
                                                `<input type='radio' id='other-ppl-${person['PersonId']}' name='person-role' value='Person Mentioned' ${personMentionedCheck}>` +
                                                `<span> Person Mentioned </span>` +
                                            `</label>` +
                                            `</br>` +
                                        `</div>` +
                                    `</form>` +
                                `</div>` +
                            `</div>` +

                            // `<div class='person-description-input'>` +
                            //     `<input type='text' id='person-${person['PersonId']}-description-edit' class='input-response person-edit-field'
                            //         placeholder='&nbsp Add more info about this person...' value='${isItString(person['Description'])}'>` +
                            // `</div>` +

                            // `<div class='person-description-input'>` +
                            //     `<input type='text' id='person-${person['PersonId']}-wiki-edit' class='input-response person-edit-field'
                            //         placeholder='&nbsp Add Wikidata ID to this person' title='e.g. Wikidata Title ID' value='${isItString(person['Link'])}'>` +
                            // `</div>` +

                            `<div class='person-location-birth-inputs' style='margin-top:5px;position:relative;'>` +
                                `<input type='text' id='person-${person['PersonId']}-birthPlace-edit' class='input-response person-input-field person-re-edit'
                                    value='${isItString(person['BirthPlace'])}' placeholder='&nbsp Birth Location'>` +
                                `<span class='input-response'><input type='text' id='person-${person['PersonId']}-birthDate-edit'
                                    class='date-input-response person-input-field datepicker-input-field person-re-edit-right'
                                    value='${isItString(person['BirthDate'])}' placeholder='&nbsp Birth: dd/mm/yyyy'>` +
                            `</div>` +

                            `<div class='person-location-death-inputs' style='margin-top:5px;position:relative;'>` +
                                `<input type='text' id='person-${person['PersonId']}-deathPlace-edit' class='input-response person-input-field person-re-edit'
                                    value='${isItString(person['DeathPlace'])}' placeholder='&nbsp Death Location'>` +
                                `<span class='input-response'><input type='text' id='person-${person['PersonId']}-deathDate-edit'
                                    class='date-input-response person-input-field datepicker-input-field person-re-edit-right'
                                    value='${isItString(person['DeathDate'])}' placeholder='&nbsp Death: dd/mm/yyyy'>` +
                            `</div>` +

                            `<div class='form-buttons-right'>` +
                                `<div class='person-btn-left'>` +
                                    `<button class='theme-color-background prsn-edit-left' onClick='openPersonEdit(${person['PersonId']})'>` +
                                        `CANCEL` +
                                    `</button>` +
                                `</div>` +
                                `<div class='person-btn-right'>` +
                                    `<button class='theme-color-background prsn-edit-right' onClick='editPerson(${person['PersonId']}, ${itemId}, ${userId})'>` +
                                        `SAVE` +
                                    `</button>` +
                                `</div>` +
                                `<div id='item-person-${person['PersonId']}-spinner-container' class='spinner-container spinner-container-left'>` +
                                    `<div class='spinner'></div>` +
                                `</div>` +
                                `<div style='clear:both;'></div>` +
                            `</div>` +
                            `<div style='clear:both;'></div>` +
                        `</div>` +
                    `</div>`;

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
                  escapeHtml(htmlDecode(content['Properties'][i]['PropertyValue'])) +
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
            const extLinkCont = document.querySelector('#item-link-list');;
            var content = JSON.parse(response.content);

            extLinkCont.innerHTML = '';

            for (let property of content['Properties']) {
                if(property['PropertyType'] === 'Link') {
                    let propDesc = null;
                    if(property['PropertyDescription'] != 'NULL') {
                        propDesc = `<div class='prop-desc' style='padding-left:23px;bottom:6px;'> ${htmlDecode(property['PropertyDescription'])} </div>`;
                    }
                    extLinkCont.innerHTML +=
                        `<div id='link-${property['PropertyId']}'>` +
                            `<div id='link-data-output-${property['PropertyId']}' class='link-single'>` +
                                `<div id='link-data-output-display-${property['PropertyId']}' class='link-data-output-content'>` +
                                    `<i class='far fa-external-link' style='margin-left: 3px;margin-right: 5px; color:#0a72cc;font-size:14px;'></i>` +
                                    `<a href='${escapeHtml(property['PropertyValue'])}' target='_blank'>${escapeHtml(htmlDecode(property['PropertyValue']))}</a>` +
                                `</div>` +
                                `<div class='edit-del-link'>` +
                                    `<i class='edit-item-data-icon fas fa-pencil theme-color-hover login-required' onClick='openLinksourceEdit(${property['PropertyId']})'></i>` +
                                    `<i class='edit-item-data-icon delete-item-data fas fa-trash-alt theme-color-hover login-required' onClick='deleteItemData(\"properties\", ${property['PropertyId']}, ${itemId}, \"link\", ${userId})'></i>` +
                                `</div>` +
                                `${propDesc ? propDesc : ''}` +
                            `</div>` +
                            // Edit Container
                            `<div class='link-data-edit-container' id='link-data-edit-${property['PropertyId']}'>` +
                                `<div id='link-${property['PropertyId']}-url-input' class='link-url-input'>` +
                                    `<input type='url' value='${escapeHtml(htmlDecode(property['PropertyValue']))}' placeholder='Enter URL Here'>` +
                                `</div>` +
                                `<div id='link-${property['PropertyId']}-description-input' class='link-description-input'>` +
                                    `<textarea rows='3' type='text' placeholder='' name=''>${escapeHtml(property['PropertyDescription'] != 'NULL' ? htmlDecode(property['PropertyDescription']) : '')}</textarea>` +
                                `</div>` +
                                `<div class='form-buttons-right'>` +
                                    `<div class='link-btn-right'>` +
                                        `<button class='theme-color-background' onClick='editLink(${property['PropertyId']}, ${itemId}, ${userId})'>` +
                                            `SAVE` +
                                        `</button>` +
                                    `</div>` +
                                    `<div class='link-btn-left'>` +
                                        `<button class='theme-color-background' onClick='openLinksourceEdit(${property['PropertyId']})'>` +
                                            `CANCEL` +
                                        `</button>` +
                                    `</div>` +
                                    `<div id='item-link-${property['PropertyId']}-spinner-container' class='spinner-container spinner-container-left'>` +
                                        `<div class='spinner'></div>` +
                                    `</div>` +
                                    `<div style='clear:both;'></div>` +
                                `</div>` +
                            `</div>` +
                        `</div>`
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
    const locId = '#location-' + placeId;
    const locSingle = document.querySelector(locId);
    if (jQuery('#transcribeLock').length) {
        e.preventDefault();
        lockWarning();
        return 0;
    }
    document.querySelector('#location-input-section').style.display = 'none';
    if (locSingle.querySelector('.location-data-edit-container').style.display == 'none') {
        locSingle.querySelector('.location-data-edit-container').style.display = 'block';
        locSingle.querySelector('.location-single').style.dispay = 'none';
    }
    else {
        locSingle.querySelector('.location-data-edit-container').style.display = 'none';
        locSingle.querySelector('.location-single').style.dispay = 'block';
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
      jQuery('#link-data-output-' + propertyId).css('display', 'none');
    }
    else {
      jQuery('#link-data-edit-' + propertyId).css('display', 'none');
      jQuery('#link-data-output-' + propertyId).css('display', 'block');
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
    let placeRole = 'Other';
    if(document.querySelector('#place-role-' + placeId).checked) {
        placeRole = 'Creation Place';
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
              WikidataId: wikidata[1],
              PlaceRole: placeRole
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
    birthDate = jQuery('#person-' + personId + '-birthDate-edit').val();
    deathPlace = jQuery('#person-' + personId + '-deathPlace-edit').val();
    deathDate = jQuery('#person-' + personId + '-deathDate-edit').val();
    description = jQuery('#person-' + personId + '-description-edit').val();
    wiki = jQuery('#person-' + personId + '-wiki-edit').val();
    let personRole = 'PersonMentioned';

    if(document.querySelector('#main-actor-' + personId).checked) {
        personRole = 'AddressedPerson';
    } else if (document.querySelector('#doc-creator-' + personId).checked) {
        personRole = 'DocumentCreator';
    }


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
      PersonRole: personRole,
      ItemId: itemId
    }

		const birthDateArray = birthDate.split('/');
    if (Number.isInteger(birthDateArray[2]) && Number.isInteger(birthDateArray[1]) && Number.isInteger(birthDateArray[0])) {
      data['BirthDate'] = birthDateArray[2] + "-" + birthDateArray[1] + "-" + birthDateArray[0];
      data['BirthDateDisplay'] = null;
    }
    else {
      data['BirthDate'] = null;
      data['BirthDateDisplay'] = birthDate;
    }

		const deathDateArray = deathDate.split('/');
    if (Number.isInteger(deathDateArray[2]) && Number.isInteger(deathDateArray[1]) && Number.isInteger(deathDateArray[0])) {
      data['DeathDate'] = deathDateArray[2] + "-" + deathDateArray[1] + "-" + deathDateArray[0];
      data['DeathDateDisplay'] = null;
    }
    else {
      data['DeathDate'] = null;
      data['DeathDateDisplay'] = deathDate;
    }

    for (var key in data) {
      if (data[key] == "") {
        data[key] = null;
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

function lockWarning() {
    jQuery('#locked-warning-container').css('display', 'block');
}

function setToolbarHeight() {
    if (tinymce.activeEditor != null) {
        jQuery('#item-page-transcription-text').mousedown(function(e){
            e.preventDefault;
            tinymce.activeEditor.focus();
            jQuery('.tox-toolbar__primary').css('width', jQuery('#mytoolbar-transcription').css('width'))
            jQuery('button[aria-label="More..."]').parent().css('position', 'absolute');
            jQuery('button[aria-label="More..."]').parent().css('right', '0px');

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
          style: 'mapbox://styles/fandf/clh6frq6p00re01qu5ysw547b',
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

            if (places.length < 1) {
                return;
            }

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

        let wikiCode = '';
        let wikiName = '';
        if(res.result.place_type.includes('country') || res.result.place_type.includes('place')) {
            wikiCode = res.result.properties['wikidata'];
            wikiName = res.result.text;
        } else {
            if(res.result.properties['wikidata']) {
                wikiCode = `${res.result.properties['wikidata']}`;
                wikiName = res.result.text;
            } else {
                for(let el of res.result.context) {
                    if(el.hasOwnProperty('wikidata') && el.id.includes('place')) {
                        wikiCode = el.wikidata;
                        wikiName = el.text;
                        break
                    }
                }
            }
        }

        jQuery('#location-input-geonames-search-container > input').val(wikiName + ';' + wikiCode);
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

//


async function showActiveTranscription(itemId) {
    let source = null;
    const requestUri = home_url + '/wp-content/themes/transcribathon/api-request.php/items/' + itemId;
    const result = await (await fetch(requestUri)).json();
    source = result.data.TranscriptionSource;


    return source;
}

function htmlDecode(value) {
    var txt = document.createElement("textarea");
    txt.innerHTML = value;
    return txt.value;
}


// Declaration of replacement for jQuery document.ready, it runs the check if DOM has loaded until it loads
var ready = (callback) => {
    if (document.readyState != "loading") callback();
    else document.addEventListener("DOMContentLoaded", callback);
}

// Replacement for jQuery document.ready; It runs the code after DOM is completely loaded
ready(() => {

    // Item Page/Full Screen - Hide tab names when they start to break
    const tabHeadList = document.querySelector('#item-tab-list');
    const tabNames = tabHeadList.querySelectorAll('.tab-h span');

    // Item page full screen image splitter, remove 'editor bar' while resizing screen - item page only
    // Add listener to hide tab names when resizing below min width
    const splitter = document.querySelector('#item-splitter');
    if(splitter) {
        splitter.addEventListener('mousedown', function() {
            tinymce.remove();
            tct_viewer.initTinyWithConfig('#item-page-transcription-text');
            // if(document.querySelector('#mce-wrapper-transcription.htr-active-tr')) {
            //     tinymce.get('item-page-transcription-text').mode.set('readonly');
            // }
        }, false);
        // Hide Tab Names when they start to break
        splitter.addEventListener('mouseleave', function() {
          if(tabHeadList.clientWidth < 730) {
            for(let nameT of tabNames) {
              nameT.style.display = 'none';
            }
          } else if(tabNames[0].style.display = 'none') {
            for(let nameT of tabNames) {
              nameT.style.display = 'inline-block';
            }
          }
        })
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


    // Item page, bind 'escape' key to close login warning if open, or full screen view(if open)
    //const escape = new KeyboardEvent('keydown');
    document.addEventListener('keydown',function(escape) {
        //const itemLogContainer = document.querySelector('#item-page-login-container');
        const fullScreen = document.querySelector('#image-view-container');
        const lockWarning = document.querySelector('#locked-warning-container');

        if(escape.key === 'Escape') {
            if(fullScreen.style.display != 'none') {
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

    // TODO Rewrite language select
    var x, i, j, selElmnt, a, b, c;
    /*look for any elements with the class "language-selector-background":*/
    x = document.getElementsByClassName("language-selector-background");
    for (i = 0; i < x.length; i++) {
      selElmnt = x[i].getElementsByTagName("select")[0];
      /*for each element, create a new DIV that will act as the selected item:*/
      a = document.createElement("div");
      a.setAttribute("class", "language-select-selected");
      if (jQuery(selElmnt).parent().attr('id') == "description-language-selector") {
        a.setAttribute("id", "description-language-custom-selector");
      }
      if (jQuery(selElmnt).parent().attr('id') == "transcription-language-selector") {
        a.setAttribute("id", "transcription-language-custom-selector");
      }

      a.innerHTML = selElmnt.options[selElmnt.selectedIndex].innerHTML;
      x[i].appendChild(a);
      /*for each element, create a new DIV that will contain the option list:*/
      b = document.createElement("div");
      b.setAttribute("class", "language-item-select select-hide");
      for (j = 1; j < selElmnt.length; j++) {
        /*for each option in the original select element,
        create a new DIV that will act as an option item:*/
        c = document.createElement("div");
        c.setAttribute("class", "selected-option");
        c.setAttribute("value", selElmnt.options[j].innerHTML);
        c.innerHTML = selElmnt.options[j].innerHTML;
        b.appendChild(c);
      }
      x[i].appendChild(b);
    }
    // Start of Image slider functions
    /// Test slider
    const sliderContainer = document.querySelector('#inner-slider');
    if(sliderContainer) {
        const sliderImages = JSON.parse(document.querySelector('#slider-images').innerHTML);
        const sliderWidth = sliderContainer.offsetWidth;
        let numOfStickers = Math.floor(sliderWidth/200);
        const storyId = document.querySelector('#story-id').textContent;
        const currentItm = parseInt(document.querySelector('#current-itm').textContent);

        const prevBtn = document.querySelector('.prev-slide');
        const nextBtn = document.querySelector('.next-slide');

        // Get path to the item, if ration cards go to /ration-cards/
        let itemPath = document.querySelector('.storypg-title').innerHTML.includes('otrošačka kartica') ? 'ration-cards' : 'item';

        if(sliderImages.length < numOfStickers) {
            prevBtn.style.display = 'none';
            nextBtn.style.display = 'none';
            numOfStickers = sliderImages.length;
        }
        if(sliderWidth < 750) {
            document.querySelector('#vertical-split').click();
            document.querySelector('#switcher-casephase').style.display = 'none';
        }

        let startSlide = 0;
        let endSlide = numOfStickers;

        // Create initial slides on the screen
        for(let x=0; x < numOfStickers; x++) {
            let imgInfo = sliderImages[x].split(' || ');
            let imgUri = imgInfo[0];
            let imgId = imgInfo[1];
            let imgCompStatus = imgInfo[2];

            sliderContainer.innerHTML +=
                `<div class='slide-sticker' data-value='${x + 1}'>` +
                    `<div class='slide-img-wrap'>` +
                        `<a href='${home_url}/documents/story/${itemPath}/?item=${imgId}' class='slider-link'>` +
                            `<img src='${imgUri}' class='slider-image' alt='slider-img-${x+1}' width='200' height='200'>` +
                        `</a>` +
                        `<div class='image-completion-status' style='background-color:${imgCompStatus};'>` +
                            `<div class='slide-number-wrap'>${x + 1}</div>` +
                        `</div>` +
                    `</div>` +
                `</div>`;
        }
        ////// Second set of variables, after initial slider is rendered
        // Make nodelist of slides so we can manipulate them
        const sliderSlides = sliderContainer.querySelectorAll('.slide-sticker');
        // Get number of dots we need to show on screen
        const numOfSlides = Math.ceil(sliderImages.length / numOfStickers);
        const dotContainer = document.querySelector('#dot-indicators');
        let currentDot = 1;

        // Create dot indicators to jump to desired set of slides
        for(let z = 1; z <= numOfSlides; z++) {
            let singleDot = document.createElement('div');
            singleDot.classList.add('slider-dot');
            singleDot.setAttribute('data-value', (z));
            // Add event to the dot
            singleDot.addEventListener('click', function() {
                currentDot = parseInt(this.getAttribute('data-value'));
                this.classList.add('current');

                endSlide = numOfStickers * z;
                if(endSlide > sliderImages.length) {
                    endSlide = sliderImages.length;
                }
                startSlide = endSlide - numOfStickers;
                slideImages(startSlide, endSlide, sliderSlides, sliderImages, storyId, currentItm);
                activeDot(currentDot);
            });
            dotContainer.appendChild(singleDot);
        }
        // dotContainer.querySelector('div').classList.add('current');
        if(currentItm || currentItm == 0) {
            let currPosition = Math.floor(currentItm/numOfStickers);
            for(let dot of dotContainer.querySelectorAll('.slider-dot')) {
                if(currPosition + 1 == parseInt(dot.getAttribute('data-value'))) {
                    dot.click();
                }
            }
        }

        function slideImages(slideStart, slideEnd, slides, imageInfo, storyid, currItm) {
            let indexOfSlide = 0;
            for(let i = slideStart; i < slideEnd; i++) {
                let imgArr = imageInfo[i].split(' || ');

                slides[indexOfSlide].querySelector('.slider-image').setAttribute('src', imgArr[0]);
                slides[indexOfSlide].querySelector('.slider-link').setAttribute('href', `${home_url}/documents/story/${itemPath}/?item=${imgArr[1]}`);
                slides[indexOfSlide].querySelector('.image-completion-status').style.backgroundColor = imgArr[2];
                slides[indexOfSlide].querySelector('.slide-number-wrap').textContent = i + 1;
                if(i === currItm) {
                    slides[indexOfSlide].querySelector('.slide-img-wrap').classList.add('active');
                } else if(slides[indexOfSlide].querySelector('.slide-img-wrap').classList.contains('active')) {
                    slides[indexOfSlide].querySelector('.slide-img-wrap').classList.remove('active');
                }
                indexOfSlide ++;
            }
        }

        function activeDot(number) {
            const sliderDots = dotContainer.querySelectorAll('.slider-dot');
            for(let dot of sliderDots) {
                if(dot.getAttribute('data-value') < number || dot.getAttribute('data-value') > number) {
                    if(dot.classList.contains('current')) {
                        dot.classList.remove('current');
                    }
                }
            }
        }

        nextBtn.addEventListener('click', function () {

            if(endSlide === sliderImages.length) {
                endSlide = numOfStickers;
                startSlide = 0;
            } else if((endSlide + numOfStickers) > sliderImages.length) {
                endSlide = sliderImages.length;
                startSlide = sliderImages.length - numOfStickers;
            } else {
                endSlide = endSlide + numOfStickers;
                startSlide = startSlide + numOfStickers;
            }

            slideImages(startSlide, endSlide, sliderSlides, sliderImages, storyId, currentItm);
            // change active dot
            const sliderDots = dotContainer.querySelectorAll('.slider-dot');
            let curDot = parseInt(dotContainer.querySelector('.current').getAttribute('data-value'));
            if(curDot == sliderDots.length) {
                sliderDots[curDot-1].classList.remove('current');
                sliderDots[0].classList.add('current');
            } else {
                sliderDots[curDot-1].classList.remove('current');
                sliderDots[curDot].classList.add('current');
            }
        });

        prevBtn.addEventListener('click', function() {
            if(startSlide === 0) {
                endSlide = sliderImages.length;
                startSlide = sliderImages.length - numOfStickers;
            } else if((startSlide - numOfStickers) < 0) {
                startSlide = 0;
                endSlide = numOfStickers;
            } else {
                startSlide -= numOfStickers;
                endSlide -= numOfStickers;
            }
            slideImages(startSlide, endSlide, sliderSlides, sliderImages, storyId, currentItm);
            // Change active dot
            const sliderDots = dotContainer.querySelectorAll('.slider-dot');
            let curDot = parseInt(dotContainer.querySelector('.current').getAttribute('data-value'));
            if(curDot - 2 < 0) {
                sliderDots[curDot - 1].classList.remove('current');
                sliderDots[sliderDots.length-1].classList.add('current');
            } else {
                sliderDots[curDot - 1].classList.remove('current');
                sliderDots[curDot - 2].classList.add('current');
            }
        });
    }


    // Item Page, Open full screen if user comes to page from fullscreen viewer
    if(document.querySelector('#openseadragon')){
        const url_string = window.location.href;
        const url = new URL(url_string);
        const fullScreen = url.searchParams.get('fs');
        if(fullScreen) {
            if(document.querySelector('#full-page')) {
                setTimeout(() => {document.querySelector('#full-page').click();}, 10);
            } else if (document.querySelector('#full-page-rc')) {
                setTimeout(() => {document.querySelector('#full-page-rc').click();}, 10);
            }
        }
    }

    // Switch between transcription view and transcription editor and hide/show 'no-text' selector
    const transcriptionSwitch = document.querySelector('#switch-tr-view');
    if(transcriptionSwitch) {
        const transEditContainer = document.querySelector('#transcription-edit-container');
        const transViewContainer = document.querySelector('#transcription-view-container');
        const transHeader = document.querySelector('.transcription-headline-header span');
        transcriptionSwitch.addEventListener('click', function() {
            if(transEditContainer.style.display == 'none') {
                transEditContainer.style.display = 'block';
                transViewContainer.style.display = 'none';
                transcriptionSwitch.querySelector('i').classList.replace('fa-pencil', 'fa-times');
                if(transcriptionSwitch.classList.contains('htr-trans')) {
                    transHeader.textContent = 'TRANSCRIPTION';
                }
            } else {
                transEditContainer.style.display = 'none';
                transViewContainer.style.display = 'block';
                transcriptionSwitch.querySelector('i').classList.replace('fa-times', 'fa-pencil');
                if(transcriptionSwitch.classList.contains('htr-trans')) {
                    transHeader.textContent = 'HTR TRANSCRIPTION';
                }
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
          if(map) {
            map.resize();
          }
        })
    }

    // Add event listener to the 'Language of Description'
    const langOfDescPH = document.querySelector('#language-sel-placeholder');
    if(langOfDescPH) {
        const currDescLang = document.querySelector('#description-language-custom-selector');
        langOfDescPH.addEventListener('click', function() {
            document.querySelector('#description-language-custom-selector').click();
        });
        currDescLang.insertAdjacentHTML('beforeend', '<i id="del-desc-lang" class="far fa-times" style="margin-left: 5px;"></i>');
    }
    // People input collapse controller
    const pplInput = document.querySelector('#show-ppl-input');
    if(pplInput) {
        pplInput.addEventListener('click', function() {
            document.querySelector('#person-input-container').classList.toggle('show');
        })
    }
    // Auto Generated Enrichments
    /// Story automatic enrichments
    const autoEnrichCont = document.querySelector('#auto-enrich-story');
    const runBtn = document.querySelector('#run-stry-enrich');
    const stryId = parseInt(document.querySelector('#story-id').textContent);
    if(runBtn) {
        runBtn.addEventListener('click', function() {
            // Show the spinner
            document.querySelector('#auto-story-spinner-container').style.display = 'block';

            // Create enrichments via AIT api
            fetch(home_url + "/wp-content/themes/transcribathon/admin/inc/custom_scripts/get_auto_enrichments.php",
            {
                method: "POST",
                headers: {
                    "Content-Type" : "application/json"
                },
                body: JSON.stringify({
                    storyId: stryId,
                    property: "description"
                })
            
            })
            .then(function(response) {
                return response.json();
            })
            .then(function(data) {
                const autoEnrichments = JSON.parse(data);
                console.log(autoEnrichments);
                if(autoEnrichments.total > 0) {

                    let enrichNr = 1;

                    for(let itm of autoEnrichments.items) {

                        let wikiDataArr = itm.body.id.split('/');
                        let wikiDataId = wikiDataArr.pop();
                        // Check if the enrichment type is person or location and assign adequate icon
                        let singleIcon = itm.body.type == 'Person' ?
                            '<i class="fa fa-user enrich-icon"></i>'
                            :
                            `<img class="enrich-icon" src="${home_url}/wp-content/themes/transcribathon/images/location-icon.svg" height="20px" width="20px" alt="location-icon">`;
                        
                        // Create new div for new enrichment, add classes and fill inner html with data
                        let singlEnrich = document.createElement('div');
                        singlEnrich.classList.add('single-annotation' + enrichNr);
                        singlEnrich.innerHTML = 
                            `<p class="type-n-id" style="display:none;">` +
                                `<span class="ann-type">${itm.body.type}</span>` +
                                `<span class="ann-id">${itm.id}</span>` +
                            `</p>` +
                            `<div class="enrich-body-left">` +
                                `<p>` +
                                    singleIcon +
                                    `<span class="enrich-label">${itm.body.prefLabel.en}</span>` +
                                    ` - ` +
                                    `<span class="enrich-wiki"><ahref="https://www.wikidata.org/wiki/${wikiDataId}" target="_blank">Wikidata ID: ${wikiDataId}</a></span>` +
                                `</p>` +
                                `<p class="auto-description">Description: ${itm.body.descriptiion} </p>` +
                            `</div>` +
                            `<div class="enrich-body-right">` +
                                `<div class="slider-track">` +
                                    `<div class="slider-slider"></div>` +
                                `</div>` +
                            `</div>`;

                        autoEnrichCont.appendChild(singlEnrich);

                        singlEnrich.querySelector('.slider-track').addEventListener('click', function() {
                            singlEnrich.classList.toggle('accept');
                        });
                        singlEnrich.querySelector('.slider-slider').addEventListener('click', function(event) {
                            event.stopPropagation();
                            this.parentElement.click();
                        })

                        enrichNr += 1;

                    }


                } else {
                    alert('We are sorry! We haven\'t been able to generate auto enrichments.');
                    document.querySelector('#auto-story-spinner-container').style.display = 'none';
                    return;
                }
                    document.querySelector('#auto-story-spinner-container').style.display = 'none';
                    document.querySelector('#verify-h').style.display = 'block';
                    document.querySelector('#accept-story-enrich').style.display = 'block';

            });


        })
    }
    /// Auto enrich Items
    const autoPplCont = document.querySelector('#ppl-auto-enrich');
    const autoLocCont = document.querySelector('#loc-auto-enrich');
    const autoEnrichBtn = document.querySelector('#run-itm-enrich');
    const url_string = window.location.href;
    const url = new URL(url_string);
    const itemId = parseInt(url.searchParams.get('item'));
    const userId = parseInt(document.querySelector('#missing-info').textContent);

    let autoProp = 'transcription';
    if(autoEnrichBtn) {

        autoEnrichBtn.addEventListener('click', function() {
            document.querySelector('#auto-itm-spinner-container').style.display = 'block';
            jQuery.post(
                home_url + '/wp-content/themes/transcribathon/admin/inc/custom_scripts/send_ajax_api_request.php',{
                  type: 'GET',
                  url: `http://dsi-demo2.ait.ac.at/enrichment-web-test/enrichment/annotation?storyId=${stryId}&itemId=${itemId}&property=${autoProp}&wskey=apidemo`,
                  token: ''
                },
                function(response) {

                    const autoEnrichmentsResponse = JSON.parse(response);
                    const autoEnrichments = JSON.parse(autoEnrichmentsResponse.content);

                    if(autoEnrichments.items) {
                        let itmNr = 1;
                        for(let itm of autoEnrichments.items) {

                            //// refactor this part
                            if(itm.body.type == 'Place') {

                                let wikiDataArr = itm.body.id.split('/');
                                let wikiId = wikiDataArr.pop();
                                let singlIcon = `<img class="enrich-icon" src="${home_url}/wp-content/themes/transcribathon/images/location-icon.svg" height="20px" width="20px" alt="location-icon">`;

                                let singlEnrich = document.createElement('div');
                                singlEnrich.classList.add('single-annotation-' + itmNr);
                                singlEnrich.innerHTML =
                                        `<p class="type-n-id" style="display:none;">` +
                                            `<span class="ann-type">${itm.body.type}</span>` +
                                            `<span class="ext-id">${itm.id}</span>` +
                                            `<span class="ann-id">${itm.body.id}</span>` +
                                        `</p>` +
                                        `<div class="enrich-body-left">` +
                                            `<p>` +
                                                singlIcon +
                                                `<span class="enrich-label">${itm.body.prefLabel.en}</span>` +
                                                ` - ` +
                                                `<span class="enrich-wiki"><a href='https://www.wikidata.org/wiki/${wikiId}' target='_blank'> Wikidata ID: ${wikiId} </a></span>` +
                                            `</p>` +
                                            `<p class='auto-description'>Description: ${itm.body.description ? itm.body.description : ''} </p>` +
                                        `</div>` +
                                        `<div class="enrich-body-right">` +
                                            `<div class="slider-track" ><div class="slider-slider"></div></div>` +
                                        `</div>`;

                                singlEnrich.setAttribute('lat', itm.body.lat);
                                singlEnrich.setAttribute('long', itm.body.long);

                                singlEnrich.querySelector('.slider-track').addEventListener('click', function() {
                                    // document.querySelector('.single-annotation-' + itmNr).classList.toggle('accept');
                                    singlEnrich.classList.toggle('accept');

                                });
                                singlEnrich.querySelector('.slider-slider').addEventListener('click', function(event) {
                                    event.stopPropagation();
                                    this.parentElement.click();
                                });

                                autoLocCont.appendChild(singlEnrich);

                            } else if (itm.body.type == 'Person') {

                                let firstName = itm.body.prefLabel.en;
                                let lastName = itm.body.familyName;
                                // Remove last name from label
                                if(firstName.includes(lastName)) {
                                    firstName = firstName.replace(` ${lastName}`, '');
                                }

                                let wikiDataArr = itm.body.id.split('/');
                                let wikiId = wikiDataArr.pop();
                                let singlIcon = '<i class="fas fa-user enrich-icon"></i>';

                                let singlEnrich = document.createElement('div');
                                singlEnrich.classList.add('single-annotation-' + itmNr);
                                singlEnrich.innerHTML =
                                        `<p class="type-n-id" style="display:none;">` +
                                            `<span class="ann-type">${itm.body.type}</span>` +
                                            `<span class="ext-id">${itm.id}</span>` +
                                            `<span class="ann-id">${itm.body.id}</span>` +
                                        `</p>` +
                                        `<div class="enrich-body-left">` +
                                            `<p>` +
                                                singlIcon +
                                                `<span class="enrich-label"><span class='firstName'>${firstName}</span>  <span class='lastName'>${lastName ? lastName : ''}</span></span>` +
                                                ` - ` +
                                                `<span class="enrich-wiki"><a href='https://www.wikidata.org/wiki/${wikiId}' target='_blank'> Wikidata ID: <span class='wikiId'>${wikiId}</span></a></span>` +
                                            `</p>` +
                                            `<p class='auto-description'>Description: ${itm.body.description ? itm.body.description : ''} (AI generated)</p>` +
                                        `</div>` +
                                        `<div class="enrich-body-right">` +
                                            `<div class="slider-track" ><div class="slider-slider"></div></div>` +
                                        `</div>`;


                                    singlEnrich.querySelector('.slider-track').addEventListener('click', function() {
                                        // document.querySelector('.single-annotation-' + itmNr).classList.toggle('accept');
                                        singlEnrich.classList.toggle('accept');

                                    });
                                    singlEnrich.querySelector('.slider-slider').addEventListener('click', function(event) {
                                        event.stopPropagation();
                                        this.parentElement.click();
                                    });

                                    autoPplCont.appendChild(singlEnrich);

                            }

                            itmNr += 1;
                        }
                    } else  {
                        alert('We are sorry! We haven\'t been able to generate auto enrichments.');
                        document.querySelector('#auto-itm-spinner-container').style.display = 'none';
                        return;
                    }
                    // Show saving Button if there is something to save
                    document.querySelector('#auto-itm-spinner-container').style.display = 'none';
                    if(autoLocCont.querySelector('div') != null) {
                        document.querySelector('#loc-verify').style.display = 'block';
                        document.querySelector('#accept-loc-enrich').style.display = 'block';
                        document.querySelector('#auto-loc-btn').style.display = 'block';
                    }
                    if(autoPplCont.querySelector('div') != null) {
                        document.querySelector('#ppl-verify').style.display = 'block';
                        document.querySelector('#accept-ppl-enrich').style.display = 'block';
                        document.querySelector('#auto-ppl-btn').style.display = 'block';
                    }

                });
        })
    }

    // Submit Location Enrichments
    const locSubmit = document.querySelector('#accept-loc-enrich');
    if(locSubmit) {

        //const enrichLocArr = [];
        locSubmit.addEventListener('click', function() {
            let acceptedEnrich = document.querySelector('#loc-auto-enrich').querySelectorAll('.accept');
            for(let enrichment of acceptedEnrich) {

                // Store data into variables
                let locationName = enrichment.querySelector('.enrich-label').textContent;
                let latitude = (enrichment.getAttribute('lat')).toString();
                let longitude = (enrichment.getAttribute('long')).toString();
                let description = (enrichment.querySelector('.auto-description').textContent).replace('Description: ', '') + ' - Automatically Generated.';
                let wikidata = (enrichment.querySelector('.enrich-wiki a').getAttribute('href')).split('/');
                let wikidataId = wikidata.pop();
                let placeRole = 'Other';
                // Convert lat/long to float number
                latitude = parseFloat(latitude);
                longitude = parseFloat(longitude);

                // Save it also to the 'Place' table
                let data = {
                    Name: locationName,
                    Latitude: latitude,
                    Longitude: longitude,
                    ItemId: itemId,
                    Link: "",
                    Zoom: 10,
                    Comment: description,
                    WikidataName: locationName,
                    WikidataId: wikidataId,
                    PlaceRole: placeRole,
                    UserId: userId,
                    UserGenerated: 0
                }

                jQuery.post(home_url + '/wp-content/themes/transcribathon/admin/inc/custom_scripts/send_ajax_api_request.php', {
                    'type': 'POST',
                    'url': TP_API_HOST + '/tp-api/places',
                    'data': data
                },function(response) {

                });
            }
        })
    }
    // Submit Ppl Enrichments
    const pplSubmit = document.querySelector('#accept-ppl-enrich');
    if(pplSubmit) {
        //const enrichPplArr = [];
        pplSubmit.addEventListener('click', function() {
            let acceptedEnrich = document.querySelector('#ppl-auto-enrich').querySelectorAll('.accept');
            for(let enrichment of acceptedEnrich) {
                // Store data into variables
                let firstName = enrichment.querySelector('.firstName').textContent;
                let lastName = enrichment.querySelector('.lastName').textContent;
                let description = (enrichment.querySelector('.auto-description').textContent).replace('Description: ', '');
                let link = enrichment.querySelector('.wikiId').textContent;

                data = {
                    FirstName: firstName,
                    LastName: lastName,
                    BirthPlace: null,
                    DeathPlace: null,
                    Link: link,
                    Description: description,
                    PersonRole: 'PersonMentioned',
                    ItemId: itemId,
                    BirthDate: null,
                    DeathDate: null
                }

                for (var key in data) {
                    if (data[key] == "") {
                        data[key] = null;
                    }
                }

                jQuery.post(home_url + '/wp-content/themes/transcribathon/admin/inc/custom_scripts/send_ajax_api_request.php', {
                    'type': 'POST',
                    'url': TP_API_HOST + '/tp-api/persons',
                    'data': data
                },
                // Check success and create confirmation message
                function(response) {

                });

            }

        })
    }
    // Submit Story Enrichments
    const storySubmit = document.querySelector('#accept-story-enrich');
    if(storySubmit) {
        const enrichStoryArr = [];
        storySubmit.addEventListener('click', function() {
            let acceptedEnrich = document.querySelector('#auto-enrich-story').querySelectorAll('.accept');
            for(let enrichment of acceptedEnrich) {

                let description = null;
                if(enrichment.querySelector('.auto-description') && enrichment.querySelector('.auto-description').textContent != 'Description: ') {
                    description = (enrichment.querySelector('.auto-description').textContent).replace('Description: ', '');
                }
                let singlEnrichment = {
                    Name: enrichment.querySelector('.enrich-label').textContent,
                    Type: enrichment.querySelector('.ann-type').textContent,
                    WikiData: enrichment.querySelector('.ann-id').textContent,
                    StoryId: stryId,
                    ItemId: null,
                    ExternalAnnotationId: enrichment.querySelector('.ext-id').textContent,
                    Comment: description
                }

                jQuery.post(home_url + '/wp-content/themes/transcribathon/admin/inc/custom_scripts/send_ajax_api_request.php', {
                    'type': 'POST',
                    'data': singlEnrichment,
                    'url': 'http://tp_api_v2/v2/autoenrichments',
                    'token': 'yes'
                  },
                  function(response) {

                  });
            }

        })
    }

    // Get En translation of transcription
    const translateTrBtn = document.querySelector('#translate-tr');
    const translatedCont = document.querySelector('#translated-tr');

    if(translateTrBtn) {
        translateTrBtn.addEventListener('click', function() {
            if(translatedCont.classList.contains('show')) {
                translatedCont.classList.remove('show');
            } else {
                if(translatedCont.classList.contains('translated')) {
                    translatedCont.classList.add('show');
                } else {
                    // Show spinner while we wait for translation
                    document.querySelector('#eng-tr-spinner').style.display = 'block';

                    jQuery.post(
                        home_url + '/wp-content/themes/transcribathon/admin/inc/custom_scripts/send_ajax_api_request.php',{
                          type: 'GET',
                          url: `http://dsi-demo2.ait.ac.at/enrichment-web-test/enrichment/translation/${stryId}/${itemId}?property=${autoProp}&wskey=apidemo`
                        },
                        function(response) {
                            let engTranslation = JSON.parse(response);

                            translatedCont.querySelector('p').innerHTML = engTranslation.content;
                            translatedCont.classList.add('show');
                            translatedCont.classList.add('translated');

                            document.querySelector('#eng-tr-spinner').style.display = 'none';

                        });
                }
            }
        })
    }
    // Get metadata when user click on 'Story Information'
    const storyInfoBtn = document.querySelector('#meta-collapse');
    if(storyInfoBtn){
        storyInfoBtn.addEventListener('click', function() {
    //         let metaCheck = 0;
            const metaWrap = document.querySelector('#meta-wrapper');
            const downIcon = document.querySelector('#angle-i');
            const doubleDownIcon = document.querySelector('#meta-cover i');
    //         if(document.querySelector('.single-meta')) {
            if(document.querySelector('#story-info').style.height == '200px') {
                document.querySelector('#story-info').style.height = 'unset';
                downIcon.classList = 'fas fa-angle-up';
                doubleDownIcon.classList = 'fas fa-angle-double-up';
            } else {
                document.querySelector('#story-info').style.height = '200px';
                downIcon.classList = 'fas fa-angle-down';
                doubleDownIcon.classList = 'fas fa-angle-double-down';
            }
        });
    }

    // Transcription history collapse controller
    const trHistoryColBtn = document.querySelector('#tr-history-collapse-btn');
    if(trHistoryColBtn) {
        const trHistoryContainer = document.querySelector('#transcription-history');
        trHistoryColBtn.addEventListener('click', function() {
            trHistoryContainer.classList.toggle('show');
            if(trHistoryContainer.classList.contains('show')) {
                trHistoryColBtn.querySelector('i').classList = 'far fa-caret-circle-up collapse-icon theme-color'
            } else {
                trHistoryColBtn.querySelector('i').classList = 'far fa-caret-circle-down collapse-icon theme-color'
            }
        })
    }



    installEventListeners();
    initializeMap();

});

function deleteAutoEnrichment(enrichmentId, event) {
    jQuery.post(
        home_url + '/wp-content/themes/transcribathon/admin/inc/custom_scripts/send_ajax_api_request.php',{
          type: 'DELETE',
          url: 'http://tp_api_v2/v2/autoenrichments/' + enrichmentId,
          token: 'yes'
        },
        function(response) {
            event.target.parentElement.remove();
        });
}
async function getMetadata(storyId) {

    const requestUri = home_url + '/wp-content/themes/transcribathon/api-request.php/stories/' + storyId;
    const response = await fetch(requestUri);

    return response.json();

}
