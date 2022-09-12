// New update button
function getStuff () {
    fetch(TP_API_HOST + '/tp-api/items/1009666')
    .then((response) => response.json())
    .then((data) => console.log(data));
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
    jQuery('#' + fieldName.replace("StatusId", "").toLowerCase() + '-status-indicator').css("color", color)
    jQuery('#' + fieldName.replace("StatusId", "").toLowerCase() + '-status-indicator').css("background-color", color)
  
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
  
    if (firstName == "" && lastName == "") {
      return 0;
    }
  
    // Prepare data and send API request
    data = {
      FirstName: firstName,
      LastName: lastName,
      BirthPlace: birthPlace,
      DeathPlace: deathPlace,
      Link: null,
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

  function updateSolr() {
    jQuery.post(home_url + '/wp-content/themes/transcribathon/admin/inc/custom_scripts/send_ajax_api_request.php', {
      'type': 'POST',
      'url': TP_API_HOST + '/tp-api/stories/update',
    },
      function(statusResponse) {
      });
  }