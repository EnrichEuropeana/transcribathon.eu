var home_url = WP_URLs.home_url;
var network_home_url = WP_URLs.network_home_url;
var map, marker;

jQuery(window).load(function() {
 initializeMap();
});
jQuery(document).ready(function() {
  jQuery(".search-page-mobile-facets").click(function () {
    jQuery(this).siblings('.search-content-left').animate({ "left": -35 },    "slow");
  });
  jQuery(".facet-close-button").click(function () {
    jQuery(this).parents('.search-content-left').animate({ "left": -500 }, "slow");
  });
 

});
function uninstallEventListeners() {

  jQuery(".datepicker-input-field").datepicker("destroy");
  tinymce.remove();
}
function installEventListeners() {
    initializeMap();

    // Location editor collapse
    const locEditor = document.querySelector('#location-position');
    const locInput = document.querySelector('#location-input-section');
    if(locEditor) {
        locEditor.addEventListener('click', function() {
            if(locInput.style.display == 'none') {
                locInput.style.display = 'block';
            } else {
                locInput.style.display = 'none';
            }
        })
    }
    // Probably to be removed, ads label when description language is already selected
    const descLangLabel = document.querySelector('.desc-lang-label');
    if(descLangLabel) {
        if(document.querySelector('#description-language-custom-selector').textContent === 'Select Language') {
            descLangLabel.style.display = 'none';
        } else {
            descLangLabel.style.display = 'inline-block';
        }
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
    // Transcription history header, on click hide transcription to make space for tr history
    const historyTr = document.querySelector('#transcription-history-collapse-heading');
    if(historyTr){
        let trView = document.querySelector('#transcription-view');
        let trHistory = document.querySelector('#transcription-history');
        historyTr.parentElement.addEventListener('click', function () {
            if(trHistory.style.display === 'none') {
                trView.style.maxHeight = '100px';
                trView.style.overflowY = 'hidden';
                trHistory.style.display = 'block';
            } else {
                trView.style.maxHeight = 'unset';
                trView.style.overflowY = 'scroll';
                trHistory.style.display = 'none';
            }
        })
    }
    const defaultLogContainer = document.querySelector('#default-login-container');
    // When the user clicks the button(pen on the image viewer), open the login modal
    jQuery('#lock-login').click(function() {
        jQuery('#default-login-container').css('display', 'block');
      })
      jQuery('#lock-loginFS').click(function() {
        jQuery("nav").addClass("fullscreen");
        jQuery(".site-navigation").css('display', 'block');
        jQuery('#default-login-container').css('display', 'block');
      })
    
    // Item Page, Full screen transcription view, toggle between view and edit
    const editButton = document.querySelector('#tr-view-start-transcription');
    if(editButton) {
        editButton.addEventListener('click', function() {
            if(document.querySelector('#tr-view-btn-i').classList.contains('fa-pencil')) {
                document.querySelector('#transcription-view').style.display = 'none';
                document.querySelector('#tr-history').style.visibility = 'hidden';
                document.querySelector('#mce-wrapper-transcription').style.display = 'block';
                document.querySelector('#tr-view-btn-i').classList.remove('fa', 'fa-pencil');
                document.querySelector('#tr-view-btn-i').classList.add('fas', 'fa-times');
                document.querySelector('#editor-tab').style.overflowY = 'hidden';
                document.querySelector('#transcription-section').style.height = 'inherit';
            } else {
                document.querySelector('#transcription-view').style.display = 'block';
                document.querySelector('#tr-history').style.visibility = 'unset';
                document.querySelector('#mce-wrapper-transcription').style.display = 'none';
                document.querySelector('#tr-view-btn-i').classList.remove('fas', 'fa-times');
                document.querySelector('#tr-view-btn-i').classList.add('fa', 'fa-pencil');
                document.querySelector('#editor-tab').style.overflowY = 'auto';
                document.querySelector('#transcription-section').style.height = 'fit-content';
            }
        }, true);
    }
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
    ///
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
    // Not sure where is this on site, needs more testing
    const singleResultDescriptions = document.querySelectorAll('.search-page-single-result-description');

    if(singleResultDescriptions) {
        for(const singleResult of singleResultDescriptions) {
            if(singleResult.scrollHeight > singleResult.clientHeight) {
                singleResult.siblings.style.display = '-webkit-inline-box';
            }
        }
    }
    // switcher between stories and items on search documents page
    const itemTabBtn = document.querySelector('.search-page-item-tab-button');
    const storyTabBtn = document.querySelector('.search-page-story-tab-button');
    if(itemTabBtn) {
        // Switch to Item view
        itemTabBtn.addEventListener('click', function() {
            document.querySelector('#search-page-item-tab').style.display = 'block';
            document.querySelector('#search-page-story-tab').style.display = 'none';
            itemTabBtn.classList.add('theme-color-background');
            storyTabBtn.classList.remove('theme-color-background');
            document.querySelector('.story-results').style.display = 'none';
            document.querySelector('.item-results').style.display = 'block';
            document.querySelector('.story-facet-content').style.display = 'none';
            document.querySelector('.item-facet-content').style.display = 'block';
            // document.querySelector('.story-search-f').style.display = 'none';
            // document.querySelector('.item-search-f').style.display = 'block';
        });
        // Switch to Story view
        storyTabBtn.addEventListener('click', function() {
            document.querySelector('#search-page-item-tab').style.display = 'none';
            document.querySelector('#search-page-story-tab').style.display = 'block';
            storyTabBtn.classList.add('theme-color-background');
            itemTabBtn.classList.remove('theme-color-background');
            document.querySelector('.story-results').style.display = 'block';
            document.querySelector('.item-results').style.display = 'none';
            document.querySelector('.story-facet-content').style.display = 'block';
            document.querySelector('.item-facet-content').style.display = 'none';
            // document.querySelector('.story-search-f').style.display = 'block';
            // document.querySelector('.item-search-f').style.display = 'none';
        });
    }
    // List/Grid Switch - Document Search Page
    const gridRadioBtn = document.querySelector('.search-results-grid-radio');
    const listRdioBtn = document.querySelector('.search-results-list-radio');
    const singleSearchResults = document.querySelectorAll('.search-page-single-result');

    if(gridRadioBtn) {
        // switch to grid view
        gridRadioBtn.addEventListener('click', function() {
            // replace classes of the single result item to get list view instead of grid
            for(const singleResult of singleSearchResults) {
                singleResult.classList.replace('list-overview', 'maingridview');
            }
            gridRadioBtn.querySelector('label').classList.add('theme-color-background');
            gridRadioBtn.querySelector('i').classList.remove('theme-color');
            listRdioBtn.querySelector('label').classList.remove('theme-color-background');
            listRdioBtn.querySelector('i').classList.add('theme-color');
        })
        // switch back to list view
        listRdioBtn.addEventListener('click', function() {
            for(const singleResult of singleSearchResults) {
                singleResult.classList.replace('maingridview', 'list-overview');
            }
            gridRadioBtn.querySelector('label').classList.remove('theme-color-background');
            gridRadioBtn.querySelector('i').classList.add('theme-color');
            listRdioBtn.querySelector('label').classList.add('theme-color-background');
            listRdioBtn.querySelector('i').classList.remove('theme-color');
        })
    }
    const url = new URL(window.location.href);
    const itemPagination = url.searchParams.get('pi');
    if(itemPagination) {
        itemTabBtn.classList.add('theme-color-background');
        storyTabBtn.classList.remove('theme-color-background');
        document.querySelector('.story-results').style.display = 'none';
        document.querySelector('.item-results').style.display = 'block';
        document.querySelector('.story-facet-content').style.display = 'none';
        document.querySelector('.item-facet-content').style.display = 'block';
    }
    // Search Test
    const searchInput = document.querySelector('#storySearch');
    const searchHidden = document.querySelector('#itemSearch');
    const subBtn = document.querySelector('.search-submit');
    subBtn.addEventListener('click', function() {
        searchHidden.value = searchInput.value;
    }, false);
    ////////
    const searchInputV = document.querySelector('#storySearchV');
    const searchHiddenV = document.querySelector('#itemSearchV');
    const subBtnV = document.querySelector('.search-submitV');
    if(searchInputV) {
        subBtnV.addEventListener('click', function() {
            searchHiddenV.value = searchInputV.value;
        }, false);
    }

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
  jQuery('#startdateentry, #enddateentry').on("change paste keyup", function() {
    var dateText = jQuery(this).val();
    // if(dateText.length > 0) {
    //   jQuery('#item-date-save-button').css('display','block');
    // }
    // else {
    //   jQuery('#item-date-save-button').css('display','none');
    // }
  })
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
    }
  })
  jQuery('.notes-questions').keyup(function() {
  var block_data = jQuery(this).val();
          if(block_data.length==0){
          jQuery('.notes-questions-submit').css('display','none');
          }else{
      jQuery('.notes-questions-submit').css('display','block');
      }
  });
  jQuery('#description-area textarea').keyup(function() {
    var text = jQuery(this).val();
    var language = jQuery('#description-language-selector select').val();
    if(text.length == 0) {
      jQuery('#description-update-button').css('display','none');
    }
    else {
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
    }
    else {
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
      if (languages > 0) {
        jQuery('#transcription-update-button').addClass('theme-color-background');
        jQuery('#transcription-update-button').prop('disabled', false);
        jQuery('#transcription-update-button .language-tooltip-text').css('display', 'none');
      }
    }
    else {
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
      }
      else {
        alert("Please remove the transcription text first, if the document has nothing to transcribe");
        event.preventDefault();
        event.stopPropagation();
      }
    }
    else {
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
}
//////////////// end of eventlisteners
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
  event.currentTarget.className += " active";
  //if (tabName == "help-tab") {
  //  jQuery('#tutorial-help-item-page').slick('refresh')
  //}
  if(document.querySelector('#transcription-history').style.display === 'block') {
      document.querySelector('#tr-history').click();
  }
}
function addPopUpLanguage() {
    document.querySelector('#mce-wrapper-transcription').style.display = 'none';
    document.querySelector('.transcription-mini-metadata').style.display = 'block';
}
function addPopUpTranscription() {
    document.querySelector('#mce-wrapper-transcription').style.display = 'block';
    // document.querySelector('.transcription-mini-metadata').style.display = 'none';
}
// Switches between different views within the item page image view
function switchItemView(event, viewName) {
  // Transcription Language selection
  const trSaveBtn = document.querySelector('#transcription-update-button');
  const descSaveBtn = document.querySelector('#description-update-button');
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
        // if(transToolBar.querySelector('.transcription-mini-metadata') !== null) {
        //     transContainer.appendChild(langSelecta);
        // }
      // On switching views hide 'transcription-view' and show editor
      document.querySelector('#transcription-view').style.display = 'block';
      document.querySelector('#tr-history').style.visibility = 'unset';
      document.querySelector('#editor-tab').style.overflowY = 'auto';
      document.querySelector('#transcription-status-indicator').style.display = 'none';
      document.querySelector('#mce-wrapper-transcription').style.display = 'none';
      // document.querySelector('.transcription-mini-metadata').style.display = 'none';
      document.querySelector('#tr-view-btn-i').style.display = 'inline-block';
      if(document.querySelector('#tr-save-btn').querySelector('#transcription-update-button') == null){
          document.querySelector('#tr-save-btn').appendChild(trSaveBtn);
      }
      if(document.querySelector('#desc-save-btn').querySelector('#description-update-button') == null){
        document.querySelector('#desc-save-btn').appendChild(descSaveBtn);
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
        // if(transToolBar.querySelector('.transcription-mini-metadata') === null) {
        //     transToolBar.appendChild(langSelecta);
        // }
        // On switching views hide 'transcription-view' and show editor
        document.querySelector('#transcription-view').style.display = 'none';
        document.querySelector('#mce-wrapper-transcription').style.display = 'block';
        // document.querySelector('.transcription-mini-metadata').style.display = 'block';
        document.querySelector('#transcription-status-indicator').style.display = 'unset';
        if(document.querySelector('.transcirption-view-head').querySelector('#transcription-update-button') === null){
            document.querySelector('.transcirption-view-head').appendChild(trSaveBtn);
        }
        if(document.querySelector('#description-collapse-heading').querySelector('#description-update-button') === null){
            document.querySelector('#description-collapse-heading').appendChild(descSaveBtn);
        }
        if(document.querySelector('#transcription-history').style.display === 'block') {
            document.querySelector('#tr-history').click();
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
          resizeWidth: false
      });
      jQuery("#item-data-section").css("top", "")
      jQuery("#item-data-section").css("left", "")
      jQuery("#item-data-section").css("position", "relative")

      jQuery("#item-data-content").css("display", 'block')
      jQuery("#item-tab-list").css("display", 'block')
      jQuery("#item-status-doughnut-chart").css("display", 'block')
      break;
    case 'popout':
    
        // Move language seletor from bottom to header
        // transToolBar.appendChild(langSelecta);
        // On switching views hide 'transcription-view' and show editor
        document.querySelector('#transcription-view').style.display = 'none';
        document.querySelector('#mce-wrapper-transcription').style.display = 'block';
        // document.querySelector('.transcription-mini-metadata').style.display = 'block';
        document.querySelector('#transcription-status-indicator').style.display = 'unset';
        if(document.querySelector('.transcirption-view-head').querySelector('#transcription-update-button') === null){
            document.querySelector('.transcirption-view-head').appendChild(trSaveBtn);
        }
        if(document.querySelector('#description-collapse-heading').querySelector('#description-update-button') === null){
            document.querySelector('#description-collapse-heading').appendChild(descSaveBtn);
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
            // jQuery('#tutorial-help-item-page').slick('refresh')
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
            })
        }
      }

      jQuery("#item-data-content").css("display", 'block')
      jQuery("#item-tab-list").css("display", 'block')
      jQuery("#item-status-doughnut-chart").css("display", 'block')

      break;
    case 'closewindow':

        // Move language selector back to bottom
        // if(transToolBar.querySelector('.transcription-mini-metadata') !== null) {
        //     transContainer.appendChild(langSelecta);
        // }
              // On switching views hide 'transcription-view' and show editor
        document.querySelector('#transcription-view').style.display = 'none';
        document.querySelector('#mce-wrapper-transcription').style.display = 'block';
        // document.querySelector('.transcription-mini-metadata').style.display = 'block';

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
      jQuery("#item-status-doughnut-chart").css("display", 'none')
      break;
    }
}
// Calls script to draw linechart on the profile page
function getTCTlinehlChart(what,start,ende,holder,uid){
  "use strict";
  jQuery.post(home_url + "/wp-content/themes/transcribathon/admin/inc/custom_profiletabs/scripts/linechart-script.php",
  {
    'q':'get-ln-chart',
    'kind':what,
    'start':start,
    'ende':ende,
    'uid':uid,
    'holder':holder
  },
  function(res) {
    if(res.status === "ok"){
      jQuery('#'+holder).fadeTo(1,0.01,function(){
        jQuery('#'+holder).html(res.content).fadeTo(400,1);
      });

    }else{
      alert(res.content);
    }
	});
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
// Switches between image view and full view on the item page
function switchItemPageView() {
   uninstallEventListeners();

  if (jQuery('#full-view-container').css('display') == 'block') {
    var descriptionText = jQuery('#item-page-description-text').val();
    var descriptionLanguage = jQuery('#description-language-selector select').val();

    //switch to image view
    jQuery('.site-footer').css('display', 'none')
    jQuery('.item-page-slider').css('visibility', 'hidden')
    jQuery('#full-view-container').css('display', 'none')
    jQuery('#image-view-container').css('display', 'flex')
    jQuery('.full-container').css('position', 'static')
    jQuery('#item-view-switcher').css('position', 'absolute')
    jQuery('#item-view-switcher').css('z-index', '9999991')
    jQuery('#item-view-switcher').css('left', '50%')
    jQuery('#item-view-switcher').css('top', '0')
    jQuery('._tct_footer').css('display', 'none')
    // jQuery('#item-progress-section').css('display', 'none')
    jQuery('.main-navigation').css('display', 'none')
    jQuery('#wpadminbar').css('display', 'none')

    // move image content
    //jQuery('#item-image-section').html(jQuery('#full-view-image').html())
    //jQuery('#full-view-image').html('')

    // move editor content
    jQuery('#editor-tab').html(jQuery('#full-view-editor').html())
    jQuery('#full-view-editor').html('')

    // move tagging content
    jQuery('#full-view-map').css('height', '400px');
    jQuery('#location-section').css('display', 'block');
    document.querySelector('.location-output').style.display = 'none';
    jQuery('#tagging-section').removeAttr('hidden');
    jQuery('#tagging-tab').html(jQuery('#full-view-tagging').html())
    jQuery('#full-view-tagging').html('')

    // move info content
    jQuery('#info-tab').html(jQuery('#full-view-info').html())
    jQuery('#full-view-info').html('')
    jQuery('#info-collapse-icon').css( 'display', 'none')
    jQuery('#info-collapse-headline-container').css( 'pointer-events', 'none' )
    jQuery('#info-collapsable').addClass('show')

    // move autoEnrichment content
    jQuery('#autoEnrichment-tab').html(jQuery('#full-view-autoEnrichment').html())
    jQuery('#full-view-autoEnrichment').html('')
    jQuery('#automatic-enrichment-collapse-icon').css( 'display', 'none')
    jQuery('#automaticEnrichment-collapse-headline').css( 'pointer-events', 'none' )
    jQuery('#enrichment-collapsable').addClass('show')

    // move help content
    jQuery('#editor-help').html(jQuery('#full-view-help').html())
    jQuery('#full-view-help').html('')

    jQuery('#item-page-description-text').val(descriptionText);
    jQuery('#description-language-selector select').val(descriptionLanguage);
    // show info collapse
    jQuery('#info-section').attr('hidden', true);

  } else {
    var descriptionText = jQuery('#item-page-description-text').val();
    var descriptionLanguage = jQuery('#description-language-selector select').val();
    //switch to full view
    jQuery('.site-footer').css('display', 'block')
    jQuery('.item-page-slider').css('visibility', 'unset')
    jQuery('#full-view-container').css('display', 'block')
    jQuery('#image-view-container').css('display', 'none')
    jQuery('.full-container').css('position', 'relative')
    jQuery('._tct_footer').css('display', 'block')
    // jQuery('#item-progress-section').css('display', 'block')
    jQuery('.main-navigation').css('display', 'block')
    jQuery('#wpadminbar').css('display', 'block')
    // move image content
    //jQuery('#full-view-image').html(jQuery('#item-image-section').html())
    //jQuery('#item-image-section').html('')

    // move editor content
    jQuery('#full-view-editor').html(jQuery('#editor-tab').html())
    jQuery('#editor-tab').html('')

    // move tagging content
    jQuery('#full-view-map').css('height', '100%');
    jQuery('#tagging-section').attr('hidden', true);
    jQuery('#location-section').css('display', 'none');
    document.querySelector('.location-output').style.display = 'block';
    jQuery('#full-view-tagging').html(jQuery('#tagging-tab').html())
    jQuery('#tagging-tab').html('')

    // move info content
    jQuery('#full-view-info').html(jQuery('#info-tab').html())
    jQuery('#info-tab').html('')
    jQuery('#info-collapse-icon').css( 'display', 'block')
    jQuery('#info-collapse-headline-container').css( 'pointer-events', 'all' )
    jQuery('#info-collapsable').removeClass('show')

    // move autoEnrichment content
    jQuery('#full-view-autoEnrichment').html(jQuery('#autoEnrichment-tab').html())
    jQuery('#autoEnrichment-tab').html('')
    jQuery('#automatic-enrichment-collapse-icon').css( 'display', 'block')
    jQuery('#automaticEnrichment-collapse-headline').css( 'pointer-events', 'all' )
    jQuery('#enrichment-collapsable').removeClass('show')

    //initTinyWithConfig('#full-view-editor #item-page-description-text');
    /*
    tinymce.init({
      selector: '#full-view-editor #item-page-transcription-text',
      inline: true
    });
    tinymce.init({
      selector: '#full-view-editor #item-page-description-text',
      inline: true
    });
    */
    // hide info collapse
    jQuery('#info-section').removeAttr('hidden');

    jQuery('#item-page-description-text').val(descriptionText);
    jQuery('#description-language-selector select').val(descriptionLanguage);

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
    }
    else {
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
// Updates the item description
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
      for (var i = 0; i < JSON.parse(response.content)["Transcriptions"].length; i++) {
        if (JSON.parse(response.content)["Transcriptions"][i]["CurrentVersion"] == 1) {
          currentTranscription = JSON.parse(response.content)["Transcriptions"][i]["TextNoTags"];
        }
      }
      // var newTranscriptionLength = tinyMCE.editors[jQuery('#item-page-transcription-text').attr('id')].getContent({format : 'text'}).length;
      // var newTranscriptionLength = tinyMCE.editors.get([jQuery('#item-page-transcription-text').attr('id')]).getContent({format : 'text'}).length;
      if(jQuery('#item-page-transcription-text').text()) {
        var newTranscriptionLength = (document.querySelector('#item-page-transcription-text').textContent).length;
        console.log(newTranscriptionLength);
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
        data['Text'] = tinyMCE.editors[jQuery('#item-page-transcription-text').attr('id')].getContent({format : 'html'}).replace(/'/g, "\\'");
        data['TextNoTags'] = tinyMCE.editors[jQuery('#item-page-transcription-text').attr('id')].getContent({format : 'text'}).replace(/'/g, "\\'");
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
        console.log(amount);
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
  // jQuery('#' + fieldName.replace("StatusId", "").toLowerCase() + '-status-indicator').innerHTML(newStatus);
  
  document.querySelector('#' + fieldName.replace("StatusId", "").toLowerCase() + "-status-indicator").textContent = newStatus;
  document.querySelector('#' + fieldName.replace("StatusId", "").toLowerCase() + '-status-indicator').parentElement.style.backgroundColor = color;

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
                      '<input type="text" id="person-' + content[i]['PersonId'] + '-description-edit" class="input-response person-input-field" value="' + description + '">' +
                    //   '<i class="fas fa-question-circle" style="font-size:16px; cursor:pointer; margin-left:4px;" title="Add more information to this person, e.g. their profession, or their significance to the item"></i>' +
              '</div>' +

              '<div class="person-description-input">' +
              //   '<label>Description:</label><br/>' +
                '<input type="text" id="person-' + content[i]['PersonId'] + '-wiki-edit" class="input-response person-input-field" value="' + wikidata + '">' +
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
                          "<button type='submit' class='theme-color-background edit-location-save' id='link-save-button'" +
                                "onClick='editLink(" + content['Properties'][i]['PropertyId'] + ", " + itemId + ", " + userId + ")'>" +
                            "SAVE" +
                          "</button>" +

                          "<button class='theme-color-background edit-location-cancel' onClick='openLinksourceEdit(" + content['Properties'][i]['PropertyId'] + ")'>" +
                            "CANCEL" +
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
function getMoreTops(myid,base,limit,kind,cp,subject,showshortnames){
	"use strict";
	document.getElementById("top-transcribers-spinner").style.display = "block";

	jQuery.post("/wp-content/themes/transcribathon/admin/inc/custom_widgets/tct-top-transcribers/skript/tct-top-transcribers-skript.php",{'q':'gtttrs','myid':myid,'base':base,'limit':limit,'kind':kind,'cp':cp,'subject':subject,'shortnames':showshortnames}, function(res) {

    if(res.stat === "ok"){
			jQuery('#tu_list_'+myid).html(res.content);
			jQuery('#ttnav_'+myid).html(res.ttnav);
		}else{
			alert(res.content);
		}
	});
}
function getMoreTopsPage(myid,limit,kind,cp,subject,showshortnames){
	"use strict";
	var load = document.getElementById("top-transcribers-spinner");
	load.style.display = "block";
	var base = document.getElementById("page_input_" + subject).value;
	if (isNaN(base) || base == ""){
		load.style.display = "none";
		document.getElementById("pageWarning_" + subject).style.display = "block";
		return 0;
	}
	else{
		base = (parseInt(base)-1) * limit;
	}

	jQuery.post("/wp-content/themes/transcribathon/admin/inc/custom_widgets/tct-top-transcribers/skript/tct-top-transcribers-skript.php",{'q':'gtttrs','myid':myid,'base':base,'limit':limit,'kind':kind,'cp':cp,'subject':subject,'shortnames':showshortnames}, function(res) {
		if(res.stat === "ok"){
			jQuery('#tu_list_'+myid).html(res.content);
			jQuery('#ttnav_'+myid).html(res.ttnav);
		}else{
			alert(res.content);
		}
	});
}
/* Surf Members in teams */
function getMoreTeamTops(myid,base,limit,tid){
	"use strict";
	document.getElementById("loadingGif_" + subject).style.display = "block";
	jQuery.post("/wp-content/themes/transcribathon/admin/inc/custom_widgets/tct-top-transcribers/skript/tct-top-transcribers-skript.php",{'q':'gtttmtrs','myid':myid,'base':base,'limit':limit,'tid':tid}, function(res) {
		if(res.stat === "ok"){
			jQuery('#tu_list_'+myid).html(res.content);
			jQuery('#ttnav_'+myid).html(res.ttnav);
		}else{
			alert(res.content);
		}
	});
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
function generateTeamCode() {
  var result           = '';
  var characters       = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
  var charactersLength = characters.length;
  for ( var i = 0; i < 10; i++ ) {
     result += characters.charAt(Math.floor(Math.random() * charactersLength));
  }
  return result;
}

function editTeam(teamId) {
  jQuery('#team-' + teamId + '-spinner-container').css('display', 'block')
  name = jQuery('#admin-team-' + teamId + '-name').val();
  shortName = jQuery('#admin-team-' + teamId + '-shortName').val();
  description = jQuery('#admin-team-' + teamId + '-description').val();
  code = jQuery('#admin-team-' + teamId + '-code').val();

  // Prepare data and send API request
  data = {
    Name: name,
    ShortName: shortName,
    Description: description,
    Code: code
  }
  var dataString= JSON.stringify(data);

  jQuery.post(home_url + '/wp-content/themes/transcribathon/admin/inc/custom_scripts/send_ajax_api_request.php', {
      'type': 'POST',
      'url': TP_API_HOST + '/tp-api/teams/' + teamId,
      'data': data
  },
  // Check success and create confirmation message
  function(response) {
    jQuery('#team-' + teamId + '-spinner-container').css('display', 'none')
  });
}
function exitTm(pID,cuID,tID,txt){
	"use strict";
	if(confirm(txt)){
		jQuery('div#ismember_list').html("<p class=\"smallloading\"></p>");
		jQuery.post(home_url + "/wp-content/themes/transcribathon/admin/inc/custom_profiletabs/scripts/team-script.php",{'q':'pls-ex-it-tm','pid':pID,'cuid':cuID,'tid':tID}, function(res) {
			if(res.status === "ok"){
				if(res.success !== "yes"){
					alert("Sorry, this did not work. Please try again\n\n");
				}
				jQuery('div#ismember_list').html(res.content);
				if(res.refresh_caps !== 'no'){
					jQuery('div#isparticipant_list').html(res.refresh_caps);
				}
				jQuery('div#openteams_list').html(res.openteams);
			}else{
				alert("Sorry, an error occured. Please try again\n\n");
			}
		});
	}
}
function getTeamTabContent(pID,cuID){
	"use strict";
	jQuery.post(home_url + "/wp-content/themes/transcribathon/admin/inc/custom_profiletabs/scripts/team-script.php",{'q':'init-teamtab','pid':pID,'cuid':cuID}, function(res) {
		if(res.status === "ok"){
			jQuery('div#ismember_list').html(res.teamlist);
			jQuery('div#isparticipant_list').html(res.campaignlist);
			jQuery('div#openteams_list').html(res.openteams);
		}else{
			alert("Sorry, an error occured. Please try again\n\n"+JSON.stringify(res));
		}
  });
}
function chkTmCd(pID,cuID){
	"use strict";
	var cd = jQuery("input#tct-mem-code").val();
	jQuery('form#tct-tmcd-frm').html("<p class=\"smallloading\"></p>");
	jQuery.post(home_url + "/wp-content/themes/transcribathon/admin/inc/custom_profiletabs/scripts/team-script.php",{'q':'chk-tm-cd','pid':pID,'cuid':cuID,'tidc':cd}, function(res) {
		if(res.status === "ok"){
			if(res.success !== "yes"){
				alert("Sorry, this did not work. Please try again\n\n");
			}
			jQuery('form#tct-tmcd-frm').html(res.content);
			jQuery('form#tct-tmcd-frm div.message').delay( 3000 ).slideUp( 400 );
			if(res.refresh !== 'no'){
				jQuery('div#ismember_list').html(res.refresh);
			}
			if(res.refresh_caps !== 'no'){
				jQuery('div#isparticipant_list').html(res.refresh_caps);
			}
		}else{
			alert("Sorry, an error occured.\n\n");
		}
	});
}
function openTmCreator(pid,cuid,obj){
	"use strict";
	if(pid !== cuid){
		alert("ups, something went wrong.\nIt appears as if you are not working\nin your own profile...");
	}else{
		if(!jQuery('form#tct-crtrtm-frm').is(':visible')){
			jQuery('input#qcmpgncd').val('');
			jQuery('input#qtmnm').val('');
			jQuery('textarea#qtsdes').val('');
			jQuery('input#qtmcd').val('');
			jQuery('form#tct-crtrtm-frm').slideDown(function(){
				obj.text(obj.attr('data-rel-close'));
			});
		}else{
			jQuery('form#tct-crtrtm-frm').slideUp(function(){
				obj.text(obj.attr('data-rel-open'));
			});
		}
	}
}
function checkCode(fid,txt){
	"use strict";
	if(jQuery('input#'+fid).val().split(' ').join('').length >7){
		jQuery.post(home_url + "/wp-content/themes/transcribathon/admin/inc/custom_profiletabs/scripts/team-script.php",{'q':'check-code','cd':jQuery('input#'+fid).val()}, function(res) {
			if(res.allgood === "no"){
				alert(res.message);
				jQuery('input#'+fid).focus();
			}
		});
	}else if(jQuery('input#'+fid).val().split(' ').join('').length >0){
		alert(txt);
	}
}
function tct_generateCode(fid){
	"use strict";
	jQuery('a#'+fid+'-but').fadeTo(1,0,function(){
		jQuery('p#'+fid+'-waiter').css({'position':'absolute','margin-top':'-10px','margin-left':'15px','display':'block'});
		jQuery.post(home_url + "/wp-content/themes/transcribathon/admin/inc/custom_profiletabs/scripts/team-script.php",{'q':'get-code'}, function(res) {
			jQuery('input#'+fid).val(res.content);
			jQuery('a#'+fid+'-but').fadeTo(1,1);
			jQuery('p#'+fid+'-waiter').hide();

		});
	});
	jQuery('input#'+fid).keyup(function(e){
    if(e.keyCode == 46 || e.keyCode == 8) {
        jQuery('input#'+fid).val('');
    }
});
}
function joinTeam(pid,cuid,tid){
	"use strict";
	jQuery('div#openteams_list').html("<p class=\"smallloading\"></p>");
	jQuery('div#ismember_list').html("<p class=\"smallloading\"></p>");
	jQuery.post("/wp-content/themes/transcribathon/admin/inc/custom_profiletabs/scripts/team-script.php",{'q':'join-team','pid':pid,'cuid':cuid,'tid':tid}, function(res) {
		if(res.status === "ok"){
			jQuery('div#openteams_messageholder').hide();
			jQuery('div#openteams_messageholder').html(res.message);
			jQuery('div#openteams_messageholder').show().delay( 3000 ).slideUp( 400,function(){jQuery('div#openteams_messageholder').html('');});
			jQuery('div#ismember_list').html(res.teamlist);
			jQuery('div#openteams_list').html(res.openteams);
		}else{
			alert('sorry, an error occured. Please reload page.');
		}
	});

}
function svTeam(pid,cuid){
	"use strict";
	var tmp = jQuery('a#svTmBut').text();
	var sollich = 0;
	var errs=0;
	var errtxt = "";
	if(jQuery('input#qtmnm').val().split(' ').join('') === ""){
		errs++;
		errtxt += "- "+jQuery('input#qtmnm').attr('data-rel-missing');
	}
	if(jQuery('input#qtmshnm').val().split(' ').join('') === ""){
		errs++;
		errtxt += "- "+jQuery('input#qtmshnm').attr('data-rel-missing');
	}
	if(errs > 0){
		alert(errtxt);
		jQuery('p#svtm-waiter').replaceWith("<a class=\"tct-vio-but\" id=\"svTmBut\" onclick=\"svTeam('"+pid+"','"+cuid+"'); return false;\">"+tmp+"</a>");
		sollich = 0;
	}else{
		jQuery('a#svTmBut').replaceWith("<p id=\"svtm-waiter\" class=\"smallloading\"></p>");
		if(jQuery('input#qcmpgncd').val().split(' ').join('') !== ""){
      /*
			jQuery.post(home_url + "/wp-content/themes/transcribathon/admin/inc/custom_profiletabs/scripts/team-script.php",{'q':'check-tmcpgn-cd','cd':jQuery('input#qcmpgncd').val(),'pid':pid}, function(res) {
				if(res.allright === "ok"){*/

					var transport = {};
					transport.q = 'crt-nw-tm';
					transport.qttl = jQuery('input#qtmnm').val();
					transport.qtshtl = jQuery('input#qtmshnm').val();
					transport.pid = pid;
					transport.cuid = cuid;
					transport.tcd = jQuery('input#qtmcd').val();
					transport.ccd = jQuery('input#qcmpgncd').val();
					transport.tdes = jQuery('textarea#qtsdes').val();
					jQuery.post(home_url + "/wp-content/themes/transcribathon/admin/inc/custom_profiletabs/scripts/team-script.php",transport, function(res) {
						if(res.status === "ok"){
							jQuery('div#team-creation-feedback').hide();
							jQuery('div#team-creation-feedback').html(res.message);
							jQuery('div#team-creation-feedback').show().delay( 3000 ).slideUp( 400,function(){jQuery('div#team-creation-feedback').html('');});

							if(res.success > 0){
								//reset team-creator-form
								jQuery('p#svtm-waiter').replaceWith("<a class=\"tct-vio-but\" id=\"svTmBut\" onclick=\"svTeam('"+pid+"','"+cuid+"'); return false;\">"+tmp+"</a>");
								jQuery('input#qcmpgncd').val('');
								jQuery('input#qtmnm').val('');
								jQuery('input#qtmcd').val('');
								jQuery('textarea#qtsdes').val('');
								jQuery('form#tct-crtrtm-frm').slideUp(function(){
									jQuery('a#open-tm-crt-but').text(jQuery('a#open-tm-crt-but').attr('data-rel-open'));
								});
								if(res.teamlist !== 'no'){
									jQuery('div#ismember_list').html(res.teamlist);
								}
								if(res.campaignlist !== 'no'){
									jQuery('div#isparticipant_list').html(res.campaignlist);
								}
							}
						}else{
							alert('Sorry, an error occured');
						}
						jQuery('input#'+fid).val(res.content);
						jQuery('a#'+fid+'-but').fadeTo(1,1);
						jQuery('p#'+fid+'-waiter').hide();

					});

		}else{
			sollich = 1;
		}
	}
	if(sollich>0){
		transport = {};
		transport.q = 'crt-nw-tm';
		transport.qttl = jQuery('input#qtmnm').val();
		transport.qtshtl = jQuery('input#qtmshnm').val();
		transport.pid = pid;
		transport.cuid = cuid;
		transport.tcd = jQuery('input#qtmcd').val();
		transport.ccd = jQuery('input#qcmpgncd').val();
		transport.tdes = jQuery('textarea#qtsdes').val();
		jQuery.post(home_url + "/wp-content/themes/transcribathon/admin/inc/custom_profiletabs/scripts/team-script.php",transport, function(res) {
			if(res.status === "ok"){
				jQuery('div#team-creation-feedback').hide();
				jQuery('div#team-creation-feedback').html(res.message);
				jQuery('div#team-creation-feedback').show().delay( 3000 ).slideUp( 400,function(){jQuery('div#team-creation-feedback').html('');});
				if(res.success > 0){
					//reset team-creator-form
					jQuery('p#svtm-waiter').replaceWith("<a class=\"tct-vio-but\" id=\"svTmBut\" onclick=\"svTeam('"+pid+"','"+cuid+"'); return false;\">"+tmp+"</a>");
					jQuery('input#qcmpgncd').val('');
					jQuery('input#qtmnm').val('');
					jQuery('textarea#qtsdes').val('');
					jQuery('form#tct-crtrtm-frm').slideUp(function(){
						jQuery('a#open-tm-crt-but').text(jQuery('a#open-tm-crt-but').attr('data-rel-open'));
					});
					if(res.teamlist !== 'no'){
						jQuery('div#ismember_list').html(res.teamlist);
					}
					if(res.campaignlist !== 'no'){
						jQuery('div#isparticipant_list').html(res.campaignlist);
					}
				}
			}else{
				alert('Sorry, an error occured');
			}
			jQuery('a#'+fid+'-but').fadeTo(1,1);
			jQuery('p#'+fid+'-waiter').hide();
		});
	}
}
function removeTm(pid,cuid,tid){
	"use strict";
	if(confirm(jQuery('a#teamsdeleter').attr('data-rel-realy'))){
		jQuery.post(home_url + "/wp-content/themes/transcribathon/admin/inc/custom_profiletabs/scripts/team-script.php",{'q':'rem-tmfg','pid':pid,'cuid':cuid,'tid':tid}, function(res) {
			if(res.status === "ok"){
				if(res.message !== "no"){
					alert(res.message);
				}else{
					if(res.teamlist !== 'no'){
						jQuery('div#ismember_list').html(res.teamlist);
					}
					if(res.campaignlist !== 'no'){
						jQuery('div#isparticipant_list').html(res.campaignlist);
					}
					disablePopup();
				}
			}else{
				alert('Sorry, an error occured');
			}
		});
	}
}
function chkTeamname(nmfldid){
	"use strict";
	jQuery.post(home_url + "/wp-content/themes/transcribathon/admin/inc/custom_profiletabs/scripts/team-script.php",{'q':'chk-teamname','title':nmfldid.val(),'myself':'new'}, function(res) {
		if(res.status === "ok"){
			if(res.usable != "ok"){
				alert(res.message);
				nmfldid.val('').focus();
			}else{
				// do nothing - title is unique
			}
		}else{
			alert('Sorry, an error occured');
		}
	});
}
function checkAbbr(nmfldid){
	"use strict";
	jQuery.post(home_url + "/wp-content/themes/transcribathon/admin/inc/custom_profiletabs/scripts/team-script.php",{'q':'chk-teamshortname','title':nmfldid.val(),'myself':'new'}, function(res) {
		if(res.status === "ok"){
			if(res.usable != "ok"){
				alert(res.message);
				nmfldid.val('').focus();
			}else{
				// do nothing - title is unique
			}
		}else{
			alert('Sorry, an error occured');
		}
	});
}
function checkExAbbr(fid,txt,tid){
	"use strict";
	jQuery.post(home_url + "/wp-content/themes/transcribathon/admin/inc/custom_profiletabs/scripts/team-script.php",{'q':'chk-teamshortname','title':jQuery('#'+fid).val(),'myself':tid}, function(res) {
		if(res.status === "ok"){
			if(res.usable != "ok"){
				alert(res.message);
				jQuery('#'+fid).val('');
			}else{
				// do nothing - title is unique
			}
		}else{
			alert('Sorry, an error occured');
		}
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
            jQuery('.tox-tinymce').css('width', jQuery('#mytoolbar-transcription').css('width'))
            
            if(document.querySelector('.tox-tinymce')){
                document.querySelector('.tox-tinymce').style.display = 'block';
            }
        })
    }
}
// // Storyboxes
function tct_storybox_getNextTwelve(modID,stand,cols){
	"use strict";
	var ids = jQuery('#tct_storyboxidholder_'+modID+' div.tct_sry_'+stand).text().split(',').join('|');
	//alert(jQuery('#tct_storyboxidholder_'+modID+' div.tct_sry_'+stand).text());
	if(jQuery('#tct_storyboxidholder_'+modID+' div.tct_sry_'+stand).text() != ""){
		//alert("'q':'gmbxs','ids':"+ids+",'cols':"+cols);
		jQuery('#tct_storyboxmore_'+modID).removeClass('smallloading').addClass('smallloading');
		jQuery.post(home_url + "/wp-content/themes/transcribathon/admin/inc/custom_widgets/tct-storyboxes/skript/loadboxes.php",{'q':'gmbxs','ids':ids,'cols':cols}, function(res) {
			// alert(JSON.stringify(res));
			if(res.status === "ok"){
				jQuery('#doc-results_'+modID+' div.tableholder div.tablegrid').append(res.boxes);
				if(jQuery('#tct_storyboxidholder_'+modID+' div.tct_sry_'+(parseInt(stand)+1)).text() != ""){
					jQuery('#tct_storyboxmore_'+modID).attr('onclick',"tct_storybox_getNextTwelve('"+modID+"','"+(parseInt(stand)+1)+"','"+cols+"'); return false;").removeClass('smallloading');
				}else{
				   	jQuery('#tct_storyboxmore_'+modID).removeClass('smallloading').remove();
				}
			}else{
				alert('Sorry, an error occured');
			}
		});
	}
}
// Itemboxes
function tct_itembox_getNextTwelve(modID,stand,cols){
	"use strict";
	var ids = jQuery('#tct_itemboxidholder_'+modID+' div.tct_sry_'+stand).text().split(',').join('|');
	// alert(jQuery('#tct_itemboxidholder_'+modID+' div.tct_sry_'+stand).text());
	if(jQuery('#tct_itemboxidholder_'+modID+' div.tct_sry_'+stand).text() != ""){
    // alert("'q':'gmbxs','ids':"+ids+",'cols':"+cols);
		jQuery('#tct_itemboxmore_'+modID).removeClass('smallloading').addClass('smallloading');
		jQuery.post(home_url + "/wp-content/themes/transcribathon/admin/inc/custom_widgets/tct-itemboxes/skript/loadboxes.php",{'q':'gmbxs','ids':ids,'cols':cols}, function(res) {
			// alert(JSON.stringify(res));
			if(res.status === "ok"){
				jQuery('#doc-results_'+modID+' div.tableholder div.tablegrid').append(res.boxes);
				if(jQuery('#tct_itemboxidholder_'+modID+' div.tct_sry_'+(parseInt(stand)+1)).text() != ""){
					jQuery('#tct_itemboxmore_'+modID).attr('onclick',"tct_itembox_getNextTwelve('"+modID+"','"+(parseInt(stand)+1)+"','"+cols+"'); return false;").removeClass('smallloading');
				}else{
				   	jQuery('#tct_itemboxmore_'+modID).removeClass('smallloading').remove();
				}
			}else{
				alert('Sorry, an error occured');
			}
		});
	}
}
function switchItem(itemId, userId, statusColor, progressSize, itemOrder, itemAmount, firstItem, lastItem) {
  jQuery('.full-spinner-container').css('display', 'block');
  loadPlaceData(itemId, userId);
  loadPersonData(itemId, userId);
  loadKeywordData(itemId, userId);
  loadLinkData(itemId, userId);
  jQuery('#location-input-section .item-page-save-button').attr('onclick', "saveItemLocation(" + itemId + ", " + userId + ", " + statusColor + ", " + progressSize + ")");
  jQuery('#save-personinfo-button').attr('onclick', "savePerson(" + itemId + ", " + userId + ", " + statusColor + ", " + progressSize + ")");
  jQuery('#keyword-save-button').attr('onclick', "saveKeyword(" + itemId + ", " + userId + ", " + statusColor + ", " + progressSize + ")");
  jQuery('#link-save-button').attr('onclick', "saveLink(" + itemId + ", " + userId + ", " + statusColor + ", " + progressSize + ")");
  jQuery('#description-update-button').attr('onclick', "updateItemDescription(" + itemId + ", " + userId + ", " + statusColor + ", " + progressSize + ")");
  jQuery('#transcription-update-button').attr('onclick', "updateItemTranscription(" + itemId + ", " + userId + ", " + statusColor + ", " + progressSize + ")");

  jQuery.post(home_url + '/wp-content/themes/transcribathon/admin/inc/custom_scripts/send_ajax_api_request.php', {
    'type': 'GET',
    'url': TP_API_HOST + '/tp-api/items/' + itemId
  },
    function(response) {
      var response = JSON.parse(response);
      var content = JSON.parse(response.content);
      var transcriptions = content['Transcriptions'];
      // swap the image in the iiif viewer
      imageData = JSON.parse(JSON.parse(response.content)['ImageLink']);
      imageLink = imageData['service']['@id'];
      if (imageData['service']['@id'].substr(0, 4) == "http") {
        imageLink = imageData['service']['@id'];
      }
      else {
        imageLink = "http://" + imageData['service']['@id'];
      }
      imageHeight = imageData['height'];
      imageWidth = imageData['width'];

      var newTileSource = {
				"@context": "http://iiif.io/api/image/2/context.json",
				"@id": imageLink,
				"height": imageHeight,
				"width": imageWidth,
				"profile": [
					"http://iiif.io/api/image/2/level2.json"
				],
				"protocol": "http://iiif.io/api/image"
			}
      tct_viewer.getOsdViewer().open(newTileSource);
      tct_viewer.getOsdViewerFS().open(newTileSource);
      // update the map (deleting old marker, drawing new ones and panning the map to show the new markers
      jQuery('.marker').remove();
      var bounds = new mapboxgl.LngLatBounds();

      content['Places'].forEach(function(marker) {
        var el = document.createElement('div');
        el.className = 'marker savedMarker fas fa-map-marker-alt';
        var popup = new mapboxgl.Popup({offset: 0, closeButton: false})
          .setHTML('<div class=\"popupWrapper\"><div class=\"name\">' + marker.Name + '</div><div class=\"comment\">' + marker.Comment + '</div></div>');
        bounds.extend([marker.Longitude, marker.Latitude]);
        new mapboxgl.Marker({element: el, anchor: 'bottom'})
          .setLngLat([marker.Longitude, marker.Latitude])
          .setPopup(popup)
          .addTo(map);
      });

      if(bounds.isEmpty()) {
	map.fitBounds([
          [51.844, 64.837],
          [-19.844, 25.877]
        ]);
      } else {
        map.fitBounds(bounds, {padding: {top: 50, bottom:20, left: 20, right: 20}});
      }
      for (var i = 0; i < transcriptions.length; i++) {
        if (transcriptions[i]['CurrentVersion'] == 1) {
          var currentTranscription =  transcriptions[i];
          break;
        }
      }
      for (var i = 0; i < transcriptions.length; i++) {
        if (transcriptions[i]['CurrentVersion'] == 1) {
          jQuery('#item-page-transcription-text').html(transcriptions[i]['TextNoTags']);
          if (transcriptions[i]['NoText'] == "1") {
            jQuery('#no-text-checkbox').prop('checked', true);
          }
          else {
            jQuery('#no-text-checkbox').prop('checked', false);
          }
          jQuery('#transcription-selected-languages').html("");
          for (var j = 0; j < transcriptions[i]['Languages'].length; j++) {
            jQuery('#transcription-selected-languages').append(
              '<ul>' +
                '<li class="theme-colored-data-box">' +
                  transcriptions[i]['Languages'][j]['Name'] + ' (' + transcriptions[i]['Languages'][j]['NameEnglish'] + ')' +
                  '<i class="far fa-times" onclick="removeTranscriptionLanguage(' + transcriptions[i]['Languages'][j]['LanguageId'] + ', this)"></i>' +
                '</li>' +
              '</ul>'
              )
              jQuery("#transcription-language-selector option[value='" + transcriptions[i]['Languages'][j]['LanguageId'] + "'").prop("disabled", true);
          }
        }
        else {
            jQuery.post(home_url + '/wp-content/themes/transcribathon/admin/inc/custom_scripts/get_userinfo.php', {
              'userId': userId,
              'index': i,
            },
              function(response) {
                var transcriptionHistory = "";
                var response = JSON.parse(response);
                var user = response['user']['data'];
                var index = response['index'];
                transcriptionHistory +='<div class="transcription-toggle" data-toggle="collapse" data-target="#transcription-' + index + '">';
                    transcriptionHistory +='<i class="fas fa-calendar-day" style= "margin-right: 6px;"></i>';
                    transcriptionHistory +='<span class="day-n-time">';
                        transcriptionHistory += transcriptions[index]["Timestamp"];
                    transcriptionHistory +='</span>';
                    transcriptionHistory +='<i class="fas fa-user-alt" style="margin: 0 6px;"></i>';
                    transcriptionHistory +='<span class="day-n-time">';
                        transcriptionHistory +='<a target=\"_blank\" href="' + network_home_url + 'profile/' + user['user_nicename'] + '">';
                            transcriptionHistory += user['user_nicename'];
                        transcriptionHistory += '</a>';
                    transcriptionHistory += '</span>';
                    transcriptionHistory += '<i class="fas fa-angle-down" style= "float:right;"></i>';
                transcriptionHistory += '</div>';

                transcriptionHistory += '<div id="transcription-' + index + '" class="collapse transcription-history-collapse-content">';
                    transcriptionHistory += '<p>';
                        transcriptionHistory += transcriptions[index]['TextNoTags'];
                    transcriptionHistory += '</p>';
                    transcriptionHistory += "<input class='transcription-comparison-button theme-color-background' type='button'" +
                                                "onClick='compareTranscription(\"" + transcriptions[index]['TextNoTags'].replace(/'/g, "&#039;").replace(/"/g, "&quot;") + "\", " +
                                                "\"" + currentTranscription['TextNoTags'].replace(/'/g, "&#039;").replace(/"/g, "&quot;") + "\"," + index + ")' " +
                                                "value='Compare to current transcription'>";
                    transcriptionHistory += '<div id="transcription-comparison-output-' + index + '" class="transcription-comparison-output"></div>';
                transcriptionHistory += '</div>';
                jQuery('#transcription-history').append(transcriptionHistory);
              }
            );
        }
      }
      var properties = content['Properties'];
      jQuery('.category-checkbox').each(function() {
        jQuery(this).prop('checked', false);
      })
      for (var i = 0; i < properties.length; i++) {
        if (properties[i]['PropertyType'] == 'Category') {
          jQuery('#type-' + properties[i]['PropertyValue'] + '-checkbox').prop('checked', true)
        }
      }
      jQuery('#item-page-description-text').val("");
      jQuery('#item-page-description-text').val(content['Description']);
      if (content['DescriptionLanguage'] != "0") {
        jQuery('#description-language-selector select').val('\"' + content['DescriptionLanguage'] + '\"');
        jQuery('#description-language-custom-selector').html(jQuery('#description-language-selector select option[value="' + content['DescriptionLanguage'] + '"').text());
      }
      else {
        jQuery('#description-language-selector select').val(null);
        jQuery('#description-language-custom-selector').html('Select Language');
      }
      var title = jQuery('#additional-information-area .item-page-section-headline').html();
      var titleWords = title.split(" ");
      titleWords[titleWords.length - 1] = itemOrder;
      title = titleWords.join(" ");
      jQuery('#additional-information-area .item-page-section-headline').html(title);

      if (itemOrder > 1) {
        jQuery('#prev-item-main-view, #prev-item-full-view').html(
          '<button id="viewer-previous-item" onclick="switchItem(' + (itemId + -1) + ', ' + userId + ', \'' + statusColor + '\', ' + progressSize + ', ' + (itemOrder - 1) + ', ' + itemAmount + ')" ' +
              'type="button" style="cursor: pointer;">' +
            '<a><i class="fas fa-chevron-left" style="font-size: 20px; color: black;"></i></a>' +
          '</button>'
        )
        jQuery('.item-navigation-prev').html(
          '<li><a title="first" href="' + home_url + '/documents/story/item?item=' + firstItem  + '"><i class="fal fa-angle-double-left"></i></a></li>' +
          '<li class="rgt"><a title="previous" href="' + home_url + '/documents/story/item?item=' + (itemId - 1) + '"><i class="fal fa-angle-left"></i></a></li>'
        )
      }
      else {
        jQuery('#prev-item-main-view, #prev-item-full-view').html("");
        jQuery('.item-navigation-prev').html("");
      }
      if (itemOrder < itemAmount) {
        jQuery('#next-item-main-view, #next-item-full-view').html(
          '<button id="viewer-next-item" onclick="switchItem(' + (itemId + 1) + ', ' + userId + ', \'' + statusColor + '\', ' + progressSize + ', ' + (itemOrder + 1) + ', ' + itemAmount + ')" ' +
              'type="button" style="cursor: pointer;">' +
            '<a><i class="fas fa-chevron-right" style="font-size: 20px; color: black;"></i></a>' +
          '</button>'
        )
        jQuery('.item-navigation-next').html(
          '<li class="rgt"><a title="next" href="' + home_url + '/documents/story/item?item=' + (itemId + 1) + '"><i class="fal fa-angle-right"></i></a></li>' +
          '<li class="rgt"><a title="last" href="' + home_url + '/documents/story/item?item=' + lastItem + '"><i class="fal fa-angle-double-right"></i></a></li>'
        )
      }
      else {
        jQuery('#next-item-main-view, #next-item-full-view').html("");
        jQuery('.item-navigation-next').html("");
      }
      jQuery('.slider-current-item-pointer').remove();
    //   jQuery('[data-slick-index=' + (itemOrder - 1) + ']').append("<div class='slider-current-item-pointer'></div>");
      jQuery('#transcription-status-indicator').css('color', content['TranscriptionStatusColorCode']);
      jQuery('#transcription-status-indicator').css('background-color', content['TranscriptionStatusColorCode']);
      jQuery('#description-status-indicator').css('color', content['DescriptionStatusColorCode']);
      jQuery('#description-status-indicator').css('background-color', content['DescriptionStatusColorCode']);
      jQuery('#location-status-indicator').css('color', content['LocationStatusColorCode']);
      jQuery('#location-status-indicator').css('background-color', content['LocationStatusColorCode']);
      jQuery('#tagging-status-indicator').css('color', content['TaggingStatusColorCode']);
      jQuery('#tagging-status-indicator').css('background-color', content['TaggingStatusColorCode']);

      jQuery.post(home_url + '/wp-content/themes/transcribathon/admin/inc/custom_scripts/send_ajax_api_request.php', {
        'type': 'GET',
        'url': TP_API_HOST + '/tp-api/completionStatus',
      },
        function(statusResponse) {
          var statusResponse = JSON.parse(statusResponse);
          var statusContent = JSON.parse(statusResponse.content);

          var progressData = [
            content['TranscriptionStatusName'],
            content['DescriptionStatusName'],
            content['LocationStatusName'],
            content['TaggingStatusName'],
          ];
          var progressCount = [];
          progressCount['Not Started'] = 0;
          progressCount['Edit'] = 0;
          progressCount['Review'] = 0;
          progressCount['Completed'] = 0;
          for(var i = 0; i < progressData.length; i++) {
              progressCount[progressData[i]] += 1;
          }
          for(var i = 0; i < statusContent.length; i++) {
            var percentage = (progressCount[statusContent[i]['Name']] / progressData.length) * 100;
            jQuery('#progress-bar-overlay-' + statusContent[i]['Name'].replace(' ', '-') + '-section').html(percentage + '%');
            jQuery('#progress-bar-' + statusContent[i]['Name'].replace(' ', '-') + '-section').html(percentage + '%');
            jQuery('#progress-bar-' + statusContent[i]['Name'].replace(' ', '-') + '-section').css('width', percentage + '%');
            jQuery('#progress-doughnut-overlay-' + statusContent[i]['Name'].replace(' ', '-') + '-section').html(percentage + '%');
            statusDoughnutChart.data.datasets[0].data[i] = progressCount[statusContent[i]['Name']];
          }
          statusDoughnutChart.update();
        }
      );
      jQuery('.full-spinner-container').css('display', 'none');
    }
  );
}
function removeContactQuestion() {
  jQuery('.contact-question').html("");
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
function getEnrichments(storyId, itemId, savedEnrichmentIds) {
  jQuery.post(home_url + '/wp-content/themes/transcribathon/admin/inc/custom_scripts/send_ajax_api_request.php', {
      'type': 'POST',
      'url': 'https://dsi-demo.ait.ac.at/enrichment-web/enrichment/annotation/' + storyId + '/' + itemId + '?wskey=apidemo&crosschecked=false'
  },
  function(response) {
    var response = JSON.parse(response);
    var content = JSON.parse(response.content);
    savedEnrichmentIds = savedEnrichmentIds.split(",");

    content.items.forEach((item, i) => {
      var name = item.body.prefLabel.en;
      var type = item.body.type;
      var wikiData = item.body.id.includes("wikidata") ? item.body.id : "";
      var id = item.id;
      var itemHtml = '<tr id="received-enrichment-' + (i + savedEnrichmentIds.length - 1) + '">' +
                      '<td>' + name + '</td>' +
                      '<td>' + type + '</td>' +
                      '<td>' + '<a target="_blank" href="'+ wikiData +'">' + wikiData.split("/").pop() + '</a>' + '</td>' +
                      '<td>' +
                          '<label class="switch">' +
                              '<input type="checkbox" onChange="saveEnrichment(\'' + name + '\', \'' + type + '\', \'' + wikiData + '\', ' + itemId + ', \'' + id + '\', ' + (i + savedEnrichmentIds.length - 1) + ')">' +
                              '<span class="slider round"></span>' +
                          '</label>' +
                      '</td>' +
                    '</tr>';
      if (!savedEnrichmentIds.find(enrichment => enrichment == item.id)) {
        jQuery('#automatic-enrichments-list').append(itemHtml);
      }
    })
  });
}
function saveEnrichment(name, type, wikiData, itemId, id, index) {
  if (jQuery('#received-enrichment-' + index + ' input').attr('checked') == "checked") {
    data = {
      Name: name,
      Type: type,
      WikiData: wikiData,
      ItemId: itemId,
      ExternalId: id
    }
    var dataString= JSON.stringify(data);
    jQuery.post(home_url + '/wp-content/themes/transcribathon/admin/inc/custom_scripts/send_ajax_api_request.php', {
      'type': 'POST',
      'data': data,
      'url': TP_API_HOST + '/tp-api/automatedEnrichments'
    },
    function(response) {
      var response = JSON.parse(response);
    });
  }
  else {
    data = {
      Name: name,
      Type: type,
      WikiData: wikiData,
      ItemId: itemId,
      ExternalId: id
    }

    var dataString= JSON.stringify(data);
    jQuery.post(home_url + '/wp-content/themes/transcribathon/admin/inc/custom_scripts/send_ajax_api_request.php', {
      'type': 'POST',
      'data': data,
      'url': TP_API_HOST + '/tp-api/automatedEnrichments/delete'
    },
    function(response) {
    });
  }
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
      coordinates.value = lngLat.lat + ', ' + lngLat.lng;
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
/*
Updating of Javascript
Function to toggle between partial and full paragraph,
used on Story page for description, and on Item page for transcription.
 */
function descToggler() {
    let buttonDesc = document.querySelector('.descMore');
    if(buttonDesc.previousSibling.style.maxHeight === '202px') {
            buttonDesc.previousSibling.style.maxHeight = 'unset';
            buttonDesc.textContent = 'Show Less';
    } else {
            buttonDesc.previousSibling.style.maxHeight = '202px';
            buttonDesc.textContent = 'Show More';
    }
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
    // People collapse on Item page
    // TODO write single function for all collapses on item page
    // const singlePeople = document.querySelectorAll('.single-person');
    // if(singlePeople) {
    //     for(let person of singlePeople) {
    //         if(person.querySelector('.person-description')){
    //             person.style.cursor = 'pointer';
    //             person.addEventListener('click', function() {
    //                 if(person.querySelector('.person-description').style.display === 'none') {
    //                     person.querySelector('.person-description').style.display = 'block';
    //                 } else if(person.querySelector('.person-description').style.display === 'block') {
    //                     person.querySelector('.person-description').style.display = 'none';
    //                 }
    //         });
    //         }
    //     }
    // }

    // const singleLinks = document.querySelectorAll('.link-single');
    // if(singleLinks) {
    //     for(let link of singleLinks) {
    //         if(link.querySelector('.link-description')) {
    //             link.style.cursor = 'pointer';
    //             link.addEventListener('click', function() {
    //                 if(link.querySelector('.link-description').style.display === 'none') {
    //                     link.querySelector('.link-description').style.display = 'block';
    //                 } else {
    //                     link.querySelector('.link-description').style.display = 'none';
    //                 }
    //             })
    //         }
    //     }
    // }
    // Metadata collapse button on StoryPage
    const metaBtn = document.querySelector('#meta-collapse-btn');
    const metaContainer = document.querySelector('.js-container');
    const metaStickers = document.querySelectorAll('.meta-sticker');
    if(metaBtn){
        metaBtn.addEventListener('click', function() {
            if(metaContainer.style.height <= '170px') {
                metaContainer.style.height = 'unset';
            } else {
                metaContainer.style.height = '170px';
            }
        })
    }
    // Transcription collapse on item page
    const itemTrBtn = document.querySelector('#itemBtn');
    if(itemTrBtn) {
        itemTrBtn.addEventListener('click', function() {
            if(itemTrBtn.previousSibling.style.height === '401px') {
                itemTrBtn.previousSibling.style.height = 'unset';
                document.querySelector('#transcription-container').style.height = 'unset';
                itemTrBtn.textContent = 'Show Less';
            } else {
                itemTrBtn.previousSibling.style.height = '401px';
                document.querySelector('#transcription-container').style.height = '580px';
                itemTrBtn.textContent = 'Show More';
            }
        })
    }
    // HTR transcription collapse
    const itemHtrBtn = document.querySelector('#htrMore');
    if(itemHtrBtn) {
        itemHtrBtn.addEventListener('click', function() {
            if(itemHtrBtn.previousSibling.style.height === '401px') {
                itemHtrBtn.previousSibling.style.height = 'unset';
                document.querySelector('#htr-transcription').style.height = 'unset';
                itemHtrBtn.textContent = 'Show Less';
            } else {
                itemHtrBtn.previousSibling.style.height = '401px';
                document.querySelector('#htr-transcription').style.height = '580px';
                itemHtrBtn.textContent = 'Show More';
            }
        })
    }
    // Item Page full screen, switching between Transcription and Description Tab
    const transcriptionTab = document.querySelector('#tr-tab');
    // switching to Transcription
    if(transcriptionTab) {
        transcriptionTab.addEventListener('click', function() {
            document.querySelector('#desc-part').setAttribute('hidden', 'true');
            document.querySelector('#tr-history').removeAttribute('hidden');
            document.querySelector('#transcription-section').removeAttribute('hidden');
            document.querySelector('#editor-tab').style.overflowY = 'none';
        })
    }
    // switching to description
    const descriptionTab = document.querySelector('#desc-tab');
    if(descriptionTab) {
        descriptionTab.addEventListener('click', function() {
            document.querySelector('#desc-part').removeAttribute('hidden');
            document.querySelector('#tr-history').setAttribute('hidden', 'true');
            document.querySelector('#transcription-section').setAttribute('hidden', 'true');
        })
    }
    // Item Page, check if the property is Empty and hide the header
    const itemProperties = document.querySelectorAll('.js-check');
    if(itemProperties) {
        for(const property of itemProperties) {
            if(!property.firstChild) {
                property.previousSibling.setAttribute('hidden', 'true');
            }
        }
    }
    // Item Page, metadata collapse controller
    const itemMetaBtn = document.querySelector('#item-meta-collapse');
    if(itemMetaBtn) {
        itemMetaBtn.addEventListener('click', function() {
            if(itemMetaBtn.nextSibling.style.height <= '300px') {
                itemMetaBtn.nextSibling.style.height = 'unset';
                if(coverUp){
                    coverUp.style.display = 'none';
                }
            } else {
                itemMetaBtn.nextSibling.style.height = '300px';
                if(coverUp) {
                    coverUp.style.display = 'block';
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
    ///////// Contact Us Form - contact us only
    const contactFormSuccess = document.querySelector('.sow-contact-form-success');
    const contactQuestion = document.querySelector('.contact-question');
    // Remove text on submit?
    if(contactFormSuccess){
        contactQuestion.textContent = "";
    }
    // set textarea to 7 rows
    const fieldContainer = document.querySelector('.sow-form-field-textarea');
    if(fieldContainer) {
        const formSpan = fieldContainer.querySelector('span');
        formSpan.querySelector('textarea').rows = "7";
    }
    //Item page full screen image splitter, remove 'editor bar' while resizing screen - item page only
    const splitter = document.querySelector('#item-splitter');
    if(splitter) {
        splitter.addEventListener('mousedown', function() {
            tinymce.remove();
            tct_viewer.initTinyWithConfig('#item-page-transcription-text');
        }, false);
    }
    //Search document page, collapse triggers - only on search page?
    const searchMore = document.querySelectorAll('.show-more');
    const searchLess = document.querySelectorAll('.show-less');
    if(searchMore) {
        for(const more of searchMore) {
            more.addEventListener('click', function() {
            more.style.display = "none";
            }, false);
        }
        for(const less of searchLess) {
            less.addEventListener('click', function() {
                const parentOfLess = less.parentElement;
                parentOfLess.previousSibling.style.display = 'block';
            }, false);
        }
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

        for(j = 1; j < selectedElement.length; j++) {
            // For each option, create DIV that will act as option item
            const optionItemDiv = document.createElement('div');
            optionItemDiv.classList.add('selected-option');
            optionItemDiv.textContent = selectedElement.options[j].textContent;
            optionDiv.appendChild(optionItemDiv);
        }
        languageSelector[i].appendChild(optionDiv);
    }
    // Start of Image slider functions
    // Image slider on top of the story and item page
    const imgSliderCheck = document.querySelector('#img-slider');
    if(imgSliderCheck) {
        const imgSticker = document.querySelectorAll('.slide-sticker');
        const windowWidth = document.querySelector('#img-slider').clientWidth;
        let sliderStart = null;
        if(document.querySelector('#slide-start')){
          sliderStart = parseInt(document.querySelector('#slide-start').textContent) + 1;
        } 
        // Buttons to move by 1
        const prevBtn = document.querySelector('.prev-slide');
        const nextBtn = document.querySelector('.next-slide');
        // Buttons to move by step size
        const nextSet = document.querySelector('.next-set');
        const prevSet = document.querySelector('.prev-set');
        // Spans showing current images on the screen
        const leftSpanNumb = document.querySelector('#left-num');
        const rightSpanNumb = document.querySelector('#right-num');
        // change number of visible images based on screen width
        let slideN; // follows current images
        let step; // holds the step for moving right/left(increment/decrement)
        if(windowWidth > 1200) {
            slideN = 9;
            step = 9;
        } else if(windowWidth > 800) {
            slideN = 5;
            step = 5;
        } else {
            slideN = 3;
            step = 3;
        }
        // Remove buttons if there is less images than step
        if(imgSticker.length <= step){
            prevBtn.style.display = 'none';
            nextBtn.style.display = 'none';
            prevSet.style.display = 'none';
            nextSet.style.display = 'none';
        }
        // Check if the slider is on current page, by checking if there is nextSet button
        if(nextSet) {
            // Add number to show what is the last image on screen
            if(imgSticker.length <= step) {
                rightSpanNumb.textContent = imgSticker.length;
            } else {
                rightSpanNumb.textContent = step;
            }
            if(sliderStart != null) {
              if(slideN > sliderStart) {
                slideN = slideN;
              } else {
                slideN = sliderStart;
              
              for(let img of imgSticker){
                if(img.getAttribute('data-value') < (slideN-step)+1 || img.getAttribute('data-value') > slideN) {
                  img.style.display = 'none';
                  img.setAttribute('loading', 'lazy');
                }
                leftSpanNumb.textContent = slideN - step + 1;
                rightSpanNumb.textContent = slideN;
              }}
            }
            
            // Sliding images by value of slideN(number of slides that depends on screen width) -- right (+)
            nextSet.addEventListener('click', function() {
                if(slideN + step < imgSticker.length) {
                    leftSpanNumb.textContent = slideN + 1;
                    rightSpanNumb.textContent = slideN + step;
                    for(let x = 0; x < step; x++) {
                        imgSticker[x + (slideN)].style.display = 'inline-block';
                        imgSticker[(slideN-1) - x].style.display = 'none';
                    }
                    console.log(slideN);
                    slideN += step;
                } else {
                    leftSpanNumb.textContent = imgSticker.length - step + 1;
                    rightSpanNumb.textContent = imgSticker.length;
                    slideN = imgSticker.length - 1;
                    
                    for(let y = imgSticker.length-1; y > imgSticker.length - (step+1); y--) {
                        imgSticker[y-step].style.display = 'none';
                        imgSticker[y].style.display = 'inline-block';
                    }
                }
            })
            // Sliding images by value of slideN(number of slides that depends on screen width) -- left (-)
            prevSet.addEventListener('click', function() {
                if(slideN - step > step) {
                    for(let c = slideN; c > slideN-step; c--){
                        imgSticker[c].style.display = 'none';
                        imgSticker[c-step].style.display = 'inline-block';
                    }
                    slideN -= step;
                    leftSpanNumb.textContent = slideN - step +2;
                    rightSpanNumb.textContent = slideN + 1;
                } else {
                    for(let z = 0; z < step; z++) {
                        imgSticker[z+step].style.display = 'none';
                        imgSticker[z].style.display = 'inline-block';
                    }
                    leftSpanNumb.textContent = 1;
                    rightSpanNumb.textContent = step;
                    slideN = step;
                }
            })
            // Hide all images that are not supossed to be on the screen
            for(const img of imgSticker) {
                if(img.getAttribute('data-value') > slideN) {
                    img.style.display = 'none';
                    img.setAttribute('loading', 'lazy');
                }
            }

            // Sliding images by 1 slide -- right (+)
            nextBtn.addEventListener('click', function() {
                imgSticker[slideN].style.display = 'inline-block';
                imgSticker[slideN - step].style.display = 'none';
                leftSpanNumb.textContent = slideN + 2 - step;
                rightSpanNumb.textContent = slideN + 1;
    
                slideN += 1;
                if(slideN >= imgSticker.length -1) {
                    slideN = imgSticker.length - 1;
                }
            })
            // Sliding images by 1 slide -- left (-)
            prevBtn.addEventListener('click', function() {
                if(slideN >= imgSticker.length-1 && imgSticker[slideN].style.display === 'inline-block'){
                    imgSticker[slideN].style.display = 'none';
                    imgSticker[slideN-step].style.display = 'inline-block';
                    slideN = imgSticker.length-1;
                } else {
                    slideN -= 1;
                    if(slideN < step) {
                        slideN = step;
                    }
                    imgSticker[slideN].style.display = 'none';
                    imgSticker[slideN-step].style.display = 'inline-block';
                }
                leftSpanNumb.textContent = slideN - step +1;
                rightSpanNumb.textContent = slideN;
            })
            // If it's last item, move slider to the end
            if(slideN == imgSticker.length){
              nextSet.click();
            }
        } // End of slider functions
    }
    // Cover up at the end of Item Metadata
    const coverUp = document.querySelector('.cover-up');
    if(coverUp) {
        coverUp.addEventListener('click', function() {
            itemMetaBtn.click();
            coverUp.style.display = 'none';
        })
    }
    // Item Page, Open full screen if user comes to page from fullscreen viewer
    if(document.querySelector('#openseadragon')){
        const url_string = window.location.href;
        const url = new URL(url_string);
        const fullScreen = url.searchParams.get('fs');
        if(fullScreen) {
            setTimeout(() => {document.querySelector('#full-page').click();}, 100);
        }
    }
    //Quick fix to move languages until finding long term solution
    const fullScreenBtn = document.querySelector('#full-page');
    
    if(fullScreenBtn) {
        const langContainer = document.querySelector('#lang-holder');
        const langSelecta = document.querySelector('.transcription-mini-metadata');
        // move languages below title
        langContainer.appendChild(langSelecta);
    }

    // When the user scrolls down 60px from the top of the document, resize the navbar's padding
    //and the logo's font size
    if(document.querySelector('#_transcribathon_partnerlogo')) {
        window.onscroll = function() {scrollFunction()};

        function scrollFunction() {
            if (document.body.scrollTop > 60 || document.documentElement.scrollTop > 60) {
                document.getElementById("_transcribathon_partnerlogo").style.height = "56px";
                document.getElementById("_transcribathon_partnerlogo").style.width = "56px";
                document.getElementById("_transcribathon_partnerlogo").style.marginLeft = "33px";
            } else {
                document.getElementById("_transcribathon_partnerlogo").style.height = "120px";
                document.getElementById("_transcribathon_partnerlogo").style.width = "120px";
                document.getElementById("_transcribathon_partnerlogo").style.marginLeft = "0px";
            }
        }}
        // if(itemDateCheck === true) {
        //     installEventListeners();
        //     itemDateCheck = false;
        //     console.log(itemDateCheck);
        // }

});


