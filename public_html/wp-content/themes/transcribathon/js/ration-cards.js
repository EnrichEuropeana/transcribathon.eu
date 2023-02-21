var home_url = WP_URLs.home_url;
var network_home_url = WP_URLs.network_home_url;


// Ration Cards get address from mapbox and save place
function getRCLocation(query, description, locName, autoCompleteContainer) {
    let source = null;
    let resContainer = document.querySelector(autoCompleteContainer);
    let itemIde = parseInt(document.querySelector('#rc-item-id').textContent);
    let userIde = parseInt(document.querySelector('#rc-user-id').textContent);
    resContainer.innerHTML = '';
    const requestUri = `https://api.mapbox.com/geocoding/v5/mapbox.places/${encodeURIComponent(query)}.json?country=hr&proximity=15.96%2C45.81&types=place%2Caddress%2Ccountry&access_token=pk.eyJ1IjoiZmFuZGYiLCJhIjoiY2pucHoybmF6MG5uMDN4cGY5dnk4aW80NSJ9.U8roKG6-JV49VZw5ji6YiQ`;
    //const requestUri = `https://api.mapbox.com/geocoding/v5/mapbox.places/${encodeURIComponent(query)}.json?types=place%2Caddress%2Ccountry&access_token=pk.eyJ1IjoiZmFuZGYiLCJhIjoiY2pucHoybmF6MG5uMDN4cGY5dnk4aW80NSJ9.U8roKG6-JV49VZw5ji6YiQ`;
    
    resContainer.parentElement.querySelector('.spinner-container').style.display = 'block';

    fetch(requestUri).then(response => response.json()).then(data => {
        source = data.features;
        console.log(source);

        resContainer.parentElement.querySelector('.spinner-container').style.display = 'none';

        source.forEach(element => {
            let wikiCode = '';
            let wikiName = '';
            let elWiki = '';
            if(element.place_type.includes('country') || element.place_type.includes('place')) {
                elWiki = `${element.text} ${element.properties['wikidata']}`;
                wikiCode = element.properties['wikidata'];
                wikiName = element.text;
            } else {
                if(element.properties['wikidata']) {
                    elWiki = `${element.text}, ${element.properties['wikidata']}`;
                    wikiCode = `${element.properties['wikidata']}`;
                    wikiName = element.text;
                } else {
                    for(let el of element.context) {
                        if(el.hasOwnProperty('wikidata')) {
                            elWiki = `${el.text}, ${el.wikidata}`;
                            wikiCode = el.wikidata;
                            wikiName = el.text;
                            break
                        }
                    }
                }
            }
            let newEl = document.createElement('div');
            newEl.classList.add('res-single');
            newEl.innerHTML = `<p>${element.place_name}, ${elWiki}, ${element.center}</p>`;
            newEl.addEventListener('click', function() {

                resContainer.parentElement.querySelector('.spinner-container').style.display = 'block';
              //  jQuery('#rc-place-spinner-container').css('display', 'block')
                const lat = element.center[1].toString();
                const lon = element.center[0].toString();

                jQuery.post(home_url + '/wp-content/themes/transcribathon/admin/inc/custom_scripts/send_ajax_api_request.php', {
                    'type': 'GET',
                    'url': TP_API_HOST + '/tp-api/items/' + itemIde
                },
                function(response) {
                    var response = JSON.parse(response);
                    var locationCompletion = JSON.parse(response.content)["LocationStatusName"];
                    const data = {
                        Name: locName,
                        Latitude: lat,
                        Longitude: lon,
                        ItemId: itemIde,
                        Link: "",
                        Zoom: 10,
                        Comment: description,
                        WikidataName: wikiName,
                        WikidataId: wikiCode,
                        UserId: userIde,
                        UserGenerated: 1
                    };
                    console.log(data);
                    jQuery.post(home_url + '/wp-content/themes/transcribathon/admin/inc/custom_scripts/send_ajax_api_request.php', {
                        'type': 'POST',
                        'url': TP_API_HOST + '/tp-api/places',
                        'data': data
                    },
                    // Check success and create confirmation message
                    function(response) {
                        console.log(response);
                        if(document.querySelector('#location-input-section').style.display == 'block') {
                            document.querySelector('#location-input-section').style.display = 'none';
                        }
                        scoreData = {
                            ItemId: itemIde,
                            UserId: userIde,
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
                            resContainer.parentElement.querySelector('.spinner-container').style.display = 'none';
                            
                            newEl.parentElement.style.display = 'none';

                            loadPlaceData(itemIde, userIde);
                        
                            loadRcPlaceData(itemIde, userIde);
                        })
            

                        if (locationCompletion == "Not Started") {
                            changeStatus(itemIde, "Not Started", "Edit", "LocationStatusId", 2, "#fff700", 4)
                        }

                    });
                });
                
            })
            resContainer.appendChild(newEl);
            resContainer.style.display = 'block';
        })
    });

}
// Save Ration Card date
function saveRcDate() {
    let itemIde = parseInt(document.querySelector('#rc-item-id').textContent);
    let userIde = parseInt(document.querySelector('#rc-user-id').textContent);

    if (jQuery('#transcribeLock').length) {
        lockWarning();
        return 0;
    }
    jQuery('#rc-date-spinner-container').css('display', 'block')
    // Prepare data and send API request
    data = {
        DateStartDisplay: jQuery('#rc-date-entry').val(),
        DateEndDisplay: ''
    }
    startDate = jQuery('#rc-date-entry').val().split('/');
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

    // endDate = jQuery('#enddateentry').val().split('/');
    // if (!isNaN(endDate[2]) && !isNaN(endDate[1]) && !isNaN(endDate[0])) {
    //     data['DateEnd'] = endDate[2] + "-" + endDate[1] + "-" + endDate[0];
    // } else if (endDate.length == 1 && endDate[0].length <=4 && endDate[0].length > 0 && !isNaN(endDate[0])) {
    //     data['DateEnd'] = endDate[0] + "-01-01";
    // } else {
    //     if (endDate[0] != "" && endDate[0] != null) {
    //         jQuery('#item-date-spinner-container').css('display', 'none')
    //         alert("Please enter a valid date or year");
    //         return 0
    //     }
    // }

    var dataString= JSON.stringify(data);
    jQuery.post(home_url + '/wp-content/themes/transcribathon/admin/inc/custom_scripts/send_ajax_api_request.php', {
        'type': 'GET',
        'url': TP_API_HOST + '/tp-api/items/' + itemIde
    },
    function(response) {
        var response = JSON.parse(response);
        var taggingCompletion = JSON.parse(response.content)["TaggingStatusName"];
        var oldStartDate = JSON.parse(response.content)["DateStart"];
        var oldEndDate = JSON.parse(response.content)["DateEnd"];
        jQuery.post(home_url + '/wp-content/themes/transcribathon/admin/inc/custom_scripts/send_ajax_api_request.php', {
            'type': 'POST',
            'url': TP_API_HOST + '/tp-api/items/' + itemIde,
            'data': data
        },
        // Check success and create confirmation message
        function(response) {
            // Update date 'view' when changing date in editor
            // document.querySelector('.date-bottom .start-date').textContent = startDate.join('/');
            // document.querySelector('.date-bottom .end-date').textContent = endDate.join('/');
            // document.querySelector('.date-bottom').style.display = 'block';
            // document.querySelector('.date-top').style.display = 'block';
            document.querySelector('#rc-doc-date').textContent = `Zagreb, ${jQuery('#rc-date-entry').val()}`;

            // if (startDate != "" && startDate != oldStartDate) {
            //     jQuery('#startdateDisplay').parent('.item-date-display-container').css('display', 'block')
            //     jQuery('#startdateDisplay').parent('.item-date-display-container').siblings('.item-date-input-container').css('display', 'none')
            //     jQuery('#startdateDisplay').html(jQuery('#startdateentry').val())
            // }
            // if (endDate != "" && endDate != oldEndDate) {
            //     jQuery('#enddateDisplay').parent('.item-date-display-container').css('display', 'block')
            //     jQuery('#enddateDisplay').parent('.item-date-display-container').siblings('.item-date-input-container').css('display', 'none')
            //     jQuery('#enddateDisplay').html(jQuery('#enddateentry').val())
            // }
            scoreData = {
                ItemId: itemIde,
                UserId: userIde,
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
                changeStatus(itemIde, "Not Started", "Edit", "TaggingStatusId", 2, "#fff700", 4)
            }
            // jQuery('#item-date-save-button').css('display', 'none')
            jQuery('#rc-date-spinner-container').css('display', 'none')
        });
    });
}
// Save Ration Card Persons
function saveRcPerson(firstName, lastName, description, spinner) {

    let itemIde = parseInt(document.querySelector('#rc-item-id').textContent);
    let userIde = parseInt(document.querySelector('#rc-user-id').textContent);

    jQuery('#' + spinner + '-spinner').css('display', 'block');

    // let firstName = escapeHtml(jQuery('#person-firstName-input').val());
    // let lastName = escapeHtml(jQuery('#person-lastName-input').val());
    let birthDate = escapeHtml(jQuery('#rc-bdate').val());

    // if (firstName == "" && lastName == "") {
    //     return 0;
    // }
    /////////
        //jQuery('#item-person-spinner-container').css('display', 'block')
    
        //firstName = escapeHtml(jQuery('#person-firstName-input').val());
        //lastName = escapeHtml(jQuery('#person-lastName-input').val());
        birthPlace = escapeHtml(jQuery('#person-birthPlace-input').val());
        birthDate = escapeHtml(jQuery('#person-birthDate-input').val().split('/'));
        deathPlace = escapeHtml(jQuery('#person-deathPlace-input').val());
        deathDate = escapeHtml(jQuery('#person-deathDate-input').val().split('/'));
        //description = escapeHtml(jQuery('#person-description-input-field').val());
        link = escapeHtml(jQuery('#person-wiki-input-field').val());
    
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
            ItemId: itemIde
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


    ///////7

    // Prepare data and send API request
    // data = {
    //     FirstName: firstName,
    //     LastName: lastName,
    //     BirthPlace: null,
    //     DeathPlace: null,
    //     Link: null,
    //     Description: null,
    //     ItemId: itemIde
    // }
    // if (!isNaN(birthDate)) {
    //     data['BirthDate'] = birthDate;
    // }
    // else {
    //     data['BirthDate'] = null;
    // }

    // for (var key in data) {
    //     if (data[key] == "") {
    //         data[key] = null;
    //     }
    // }

    // console.log(data);

    var dataString= JSON.stringify(data);
    jQuery.post(home_url + '/wp-content/themes/transcribathon/admin/inc/custom_scripts/send_ajax_api_request.php', {
        'type': 'GET',
        'url': TP_API_HOST + '/tp-api/items/' + itemIde
    },
    function(response) {
        // console.log(response);
        var response = JSON.parse(response);
        var taggingCompletion = JSON.parse(response.content)["TaggingStatusName"];
        jQuery.post(home_url + '/wp-content/themes/transcribathon/admin/inc/custom_scripts/send_ajax_api_request.php', {
            'type': 'POST',
            'url': TP_API_HOST + '/tp-api/persons',
            'data': data
        },
        // Check success and create confirmation message
        function(response) {
            console.log(response);

            scoreData = {
                ItemId: itemIde,
                UserId: userIde,
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
                console.log(response);
                jQuery('#' + spinner + '-spinner').css('display', 'none');
            })

            //loadPersonData(itemIde, userIde);
            if (taggingCompletion == "Not Started") {
                changeStatus(itemIde, "Not Started", "Edit", "TaggingStatusId", 2, "#fff700", 4)
            }
            // jQuery('#person-input-container').removeClass('show')
            // jQuery('#person-input-container input').val("")
            // jQuery('#item-person-spinner-container').css('display', 'none')
        });
    });
}

// Load Rc places
function loadRcPlaceData(itemId, userId) {
    // Get new location list
    jQuery.post(home_url + '/wp-content/themes/transcribathon/admin/inc/custom_scripts/send_ajax_api_request.php', {
        'type': 'GET',
        'url': TP_API_HOST + '/tp-api/places?ItemId=' + itemId
    },
    function(response) {
        var response = JSON.parse(response);
        var allPlaces = JSON.parse(response.content);
        let submPlace = {};
        let lLordPlace = {};
        let shopPlace = {};
        console.log(allPlaces);

        for(let pl of allPlaces) {
            if(pl.Comment == 'Submitter Address/ Adresa Domacinstva') {
                submPlace = pl;
            } else if (pl.Comment == 'Property owner Address/ Adresa Kucevlasnika') {
                lLordPlace = pl;
            } else if (pl.Comment == 'Shop Address/ Adresa Trgovine') {
                shopPlace = pl;
            }
        }

        // Submitter address -add delete button after saving it
        if(Object.keys(submPlace).length > 0) {
            // let editSubmBtn = document.querySelector('#edit-subm');
            document.querySelector('#rc-place-one').style.display = 'none';
            document.querySelector('#edit-subm-container').style.display = 'inline-block';
            document.querySelector('#m-address-check').style.display = 'inline-block';
            document.querySelector('#kbr-check').style.display ='inline-block';

            // Fill the input with existing data
            let submAddressArr = submPlace.Name.split(', ');
            document.querySelector('#m-address').value = submAddressArr[0];
            document.querySelector('#m-address').setAttribute('disabled', true);
            document.querySelector('#m-address').style.border = '1px solid #0a72cc';
            document.querySelector('#kbr').value = submAddressArr[1];
            document.querySelector('#kbr').setAttribute('disabled', true);
            document.querySelector('#kbr').style.border = '1px solid #0a72cc';

            let deleteSubmBtn = document.querySelector('#del-subm');

            deleteSubmBtn.addEventListener('click', function() {
                jQuery.post(home_url + '/wp-content/themes/transcribathon/admin/inc/custom_scripts/send_ajax_api_request.php', {
                    'type': 'DELETE',
                    'url': TP_API_HOST + '/tp-api/places/' + submPlace.PlaceId
                    },
                    function(response) {
                        document.querySelector('#m-address').value = '';
                        document.querySelector('#m-address').removeAttribute('disabled');
                        document.querySelector('#m-address').style.border = '1px solid #ccc';
                        document.querySelector('#m-address-check').style.display = 'none';
                        document.querySelector('#kbr').value = '';
                        document.querySelector('#kbr').removeAttribute('disabled');
                        document.querySelector('#kbr').style.border = '1px solid #ccc';
                        document.querySelector('#kbr-check').style.display = 'none';
                        document.querySelector('#rc-place-one').style.display = 'inline-block';
                        document.querySelector('#edit-subm-container').style.display = 'none';

                        loadPlaceData(itemId, userId);
                });
            })

            // editSubmBtn.addEventListener('click', function() {

            //     data = {
            //         Name: locationName,
            //         Latitude: latitude,
            //         Longitude: longitude,
            //         Comment: description,
            //         WikidataName: wikidata[0],
            //         WikidataId: wikidata[1]
            //     }

            //     var dataString= JSON.stringify(data);
      
            //     jQuery.post(home_url + '/wp-content/themes/transcribathon/admin/inc/custom_scripts/send_ajax_api_request.php', {
            //         'type': 'POST',
            //         'url': TP_API_HOST + '/tp-api/places/' + placeId,
            //         'data': data
            //     },
            //     // Check success and create confirmation message
            //     function(response) {
            //     });

            // })
        }

        // Landlord address - add delete button after saving
        if(Object.keys(lLordPlace).length > 0) {

            document.querySelector('#l-lord-add').style.display = 'none';
            document.querySelector('#edit-llord-container').style.display = 'inline-block';
            document.querySelector('#landlord-check').style.display = 'inline-block';
            // Fill the input with existing data and disable it
            document.querySelector('#landlord-loc').value = lLordPlace.Name;
            document.querySelector('#landlord-loc').setAttribute('disabled', true);
            document.querySelector('#landlord-loc').style.border = '1px solid #0a72cc';

            let deleteLLordBtn = document.querySelector('#del-llord');

            deleteLLordBtn.addEventListener('click', function() {
                jQuery.post(home_url + '/wp-content/themes/transcribathon/admin/inc/custom_scripts/send_ajax_api_request.php', {
                    'type': 'DELETE',
                    'url': TP_API_HOST + '/tp-api/places/' + lLordPlace.PlaceId
                    },
                    function(response) {
                        document.querySelector('#landlord-loc').value = '';
                        document.querySelector('#landlord-loc').removeAttribute('disabled');
                        document.querySelector('#landlord-loc').style.border = '1px solid #ccc';
                        document.querySelector('#landlord-check').style.display = 'none';
                        document.querySelector('#l-lord-add').style.display = 'inline-block';
                        document.querySelector('#edit-llord-container').style.display = 'none';

                        loadPlaceData(itemId, userId);
                });
            })

        }

        // Shop address - add delete button after saving
        if(Object.keys(shopPlace).length > 0) {

            document.querySelector('#shop-loc-btn').style.display = 'none';
            document.querySelector('#edit-shop-container').style.display = 'inline-block';
            document.querySelector('#shop-check').style.display = 'inline-block';
            document.querySelector('#shop-name-check').style.display = 'inline-block';
            // Fill the input with existing data and disable it
            let shopAddressArr = shopPlace.Name.split(', ');
            document.querySelector('#shop-name').value = shopAddressArr[0];
            document.querySelector('#shop-name').setAttribute('disabled', true);
            document.querySelector('#shop-name').style.border = '1px solid #0a72cc';
            document.querySelector('#shop-loc').value = shopAddressArr[1];
            document.querySelector('#shop-loc').setAttribute('disabled', true);
            document.querySelector('#shop-loc').style.border = '1px solid #0a72cc';


            let deleteShopBtn = document.querySelector('#del-shop');

            deleteShopBtn.addEventListener('click', function() {
                jQuery.post(home_url + '/wp-content/themes/transcribathon/admin/inc/custom_scripts/send_ajax_api_request.php', {
                    'type': 'DELETE',
                    'url': TP_API_HOST + '/tp-api/places/' + shopPlace.PlaceId
                    },
                    function(response) {
                        document.querySelector('#shop-loc').value = '';
                        document.querySelector('#shop-loc').removeAttribute('disabled');
                        document.querySelector('#shop-loc').style.border = '1px solid #ccc';
                        document.querySelector('#shop-name').value = '';
                        document.querySelector('#shop-name').removeAttribute('disabled');
                        document.querySelector('#shop-name').style.border = '1px solid #ccc';
                        document.querySelector('#shop-check').style.display = 'none';
                        document.querySelector('#shop-name-check').style.display = 'none';
                        document.querySelector('#shop-loc-btn').style.display = 'inline-block';
                        document.querySelector('#edit-shop-container').style.display = 'none';

                        loadPlaceData(itemId, userId);
                });
            })

        }
       
    });
}



// Declaration of replacement for jQuery document.ready, it runs the check if DOM has loaded until it loads
var ready = (callback) => {
    if (document.readyState != "loading") callback();
    else document.addEventListener("DOMContentLoaded", callback);
}

// Replacement for jQuery document.ready; It runs the code after DOM is completely loaded
ready(() => {

    // Check if there are already locations
    let itemId = parseInt(document.querySelector('#rc-item-id').textContent);
    let userId = parseInt(document.querySelector('#rc-user-id').textContent);
    
    loadRcPlaceData(itemId, userId);

    // Ration Cards Javascript
    // Submitter Address
    const locOne = document.querySelector('#rc-place-one');
    const locOneStreet = document.querySelector('#m-address');
    const locOneNumb = document.querySelector('#kbr');
    if(locOne) {
        locOne.addEventListener('click' , function() {
            let queryLoc = `${locOneStreet.value} ${locOneNumb.value}`;
            let description = 'Submitter Address/ Adresa Domacinstva';
            let locName = `${locOneStreet.value}, ${locOneNumb.value}`;
    
            getRCLocation(queryLoc, description, locName, '#m-address-res');

            locOneStreet.setAttribute('disabled', true);
            locOneStreet.style.border = '1px solid #0a72cc';
            locOneNumb.setAttribute('disabled', true);
            locOneNumb.style.border = '1px solid #0a72cc';
            
           // document.querySelector('#rc-place-one').classList = 'fas fa-check';
            // document.querySelector('#rc-place-one').style.display = 'none';
            // document.querySelector('#edit-subm-container').style.display = 'inline-block';
    
            document.querySelector('#submitter-place').textContent = locOneStreet.value;
            document.querySelector('#house-nr').textContent = 'Kbr. ' + locOneNumb.value;
        })
    }

    // Landlord Adress
    const lLordBtn = document.querySelector('#l-lord-add');
    const lLordStreet = document.querySelector('#landlord-loc');
    if(lLordBtn) {
        lLordBtn.addEventListener('click', function() {
            let queryLoc = `Zagreb, ${lLordStreet.value}`;
            let description = 'Property owner Address/ Adresa Kucevlasnika';
    
            getRCLocation(queryLoc, description, lLordStreet.value, '#landlord-loc-res');

            lLordStreet.setAttribute('disabled', true);
            lLordStreet.style.border = '1px solid #0a72cc';
    
            document.querySelector('#llord-place').textContent = lLordStreet.value;
        })
    }

    // Shop Address 
    // Shop Address 
    const shopBtn = document.querySelector('#shop-loc-btn');
    const shopStreet = document.querySelector('#shop-loc');
    const shopName = document.querySelector('#shop-name');

    if(shopBtn) {
        shopBtn.addEventListener('click', function() {
            if(shopName.value == '' || shopStreet.value == '') {
                window.alert('Please fil both Shop Name and Shop Street!')
            } else {
                let queryLoc = `${shopStreet.value}`;
                let description = 'Shop Address/ Adresa Trgovine'
                let locName = `${shopName.value}, ${shopStreet.value}`;
        
                getRCLocation(queryLoc, description, locName, '#shop-loc-res');
    
                shopStreet.setAttribute('disabled', true);
        
                document.querySelector('#tr-shop-name').textContent = shopName.value;
                document.querySelector('#tr-shop-place').textContent = shopStreet.value;
            }
        })
    }


    // Date
    const rcDateBtn = document.querySelector('#save-rc-date');
    
    if(rcDateBtn) {
        rcDateBtn.addEventListener('click', function() {
            saveRcDate()
        })
    }


    // People
    // Form Submitter
    const submitSaveBtn = document.querySelector('#save-submitter');
    if(submitSaveBtn) {
        submitSaveBtn.addEventListener('click', function() {
            let description = 'Submitter Podnositelj prijave';
            let firstName = document.querySelector('#submitter-fname').value;
            let lastName = document.querySelector('#submitter-lname').value;
    
            saveRcPerson(firstName, lastName, description, 'submitter');
    
            document.querySelector('#submitter-lf-name').textContent = lastName + ' ' + firstName;
            document.querySelector('#reg-num').textContent = document.querySelector('#regnumb').value;
        })
    }

    // Landlord
    const lLordSaveBtn = document.querySelector('#save-l-lord');
    if(lLordSaveBtn) {
        lLordSaveBtn.addEventListener('click', function() {
            let description = 'Landlord / Kucevlasnik';
            let firstName = document.querySelector('#landlord-fname').value;
            let lastName = document.querySelector('#landlord-lname').value;
    
            saveRcPerson(firstName, lastName, description, 'landlord');
    
            document.querySelector('#llord-lf-name').textContent = lastName + ' ' + firstName;
            
        })
    }

    // Listed persons
    const lPersSaveBtn = document.querySelector('#save-list-person');

    if(lPersSaveBtn) {
        lPersSaveBtn.addEventListener('click', function() {
            let description = `${document.querySelector('#desc-rel').value} - ${document.querySelector('#desc-voc').value}
                - ${document.querySelector('#desc-wp').value}`;
            let firstName = document.querySelector('#lst-p-fname').value;
            let lastName = document.querySelector('#lst-p-lname').value;
    
            saveRcPerson(firstName, lastName, description, 'listed-person');
    
            let addedToList = document.createElement('tr');
            let lastChild = document.querySelector('.rc-list-td');
            addedToList.classList.add('rc-list-display')
            
            addedToList.innerHTML =
                    `<td>${lastName} ${firstName} </td>` +
                    `<td class='rc-person-second-col'>${escapeHtml(jQuery('#rc-bdate').val())}</td>` +
                    `<td>${document.querySelector('#desc-rel').value}</td>` +
                    `<td>${document.querySelector('#desc-voc').value}</td>` +
                    `<td>${document.querySelector('#desc-wp').value}</td>` +
                    `<td class='btn-col'>&nbsp</td>`; 
            
           let addedToTrList = addedToList.cloneNode(true);
            document.querySelector('#rc-table tbody').insertBefore(addedToList, lastChild);
            document.querySelector('#tr-list-table tbody').appendChild(addedToTrList);
    
            document.querySelector('#rc-list-form').reset();
        })
    }
    // Set document language to Croatian if it's Ration Card
    if(document.querySelector('#rc-form')) {
        document.querySelector('#transcription-language-selector div[value="Hrvatski (Croatian)"]').click();
    }

});

