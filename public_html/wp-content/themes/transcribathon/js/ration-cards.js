var home_url = WP_URLs.home_url;
var network_home_url = WP_URLs.network_home_url;

function escapeRcHtml(html){
    var text = document.createTextNode(html);
    var p = document.createElement('p');
    p.appendChild(text);

    return p.innerHTML;
}
function deleteRcPerson(personId, itemId, userId) {

    jQuery.post(home_url + '/wp-content/themes/transcribathon/admin/inc/custom_scripts/send_ajax_api_request.php', {
        'type': 'DELETE',
        'url': TP_API_HOST + '/tp-api/persons/' + personId
    },
    function(response) {
        console.log(response);
        loadRcPerson(itemId, userId);
    });

}
function loadRcPerson(itemId, userId) {

    jQuery.post(home_url + '/wp-content/themes/transcribathon/admin/inc/custom_scripts/send_ajax_api_request.php', {
        'type': 'GET',
        'url': TP_API_HOST + '/tp-api/persons?ItemId=' + itemId
    }, 
    function(response) {
        var response = JSON.parse(response);
        var allPpl = JSON.parse(response.content);

        const pplListContainer = document.querySelector('#show-list-ppl');

        pplListContainer.innerHTML = '';

        let topPpl = [];
        let listPpl = [];

        for(let person of allPpl) {
            if(person.Description == 'Landlord / Kucevlasnik' || person.Description == 'Submitter Podnositelj prijave') {
                topPpl.push(person);
            } else {
                listPpl.push(person);
            }
        }

        for(let person of topPpl) {
            // let newPerson = document.createElement('div')
            // newPerson.classList = 'top-person-single';
            // console.log(person);
            // newPerson.innerHTML = 
            //     `<i class='fas fa-user' style='float:left;margin-right:5px;'></i>` +
            //     `<p class='person-data'>${escapeRcHtml(person.FirstName)} ${escapeRcHtml(person.LastName)}</p>` +
            //     `<p class='person-description'>Description: ${escapeRcHtml(person.Description)}</p>` +
            //     `<i class='fas fa-trash-alt' onClick='deleteRcPerson(${person.PersonId}, ${itemId}, ${userId});'></i>`;
        
            // pplTopContainer.appendChild(newPerson);

            if(person.Description == 'Landlord / Kucevlasnik') {
                document.querySelector('#landlord-lname').setAttribute('disabled', true);
                document.querySelector('#landlord-lname').value = person.LastName;
                document.querySelector('#landlord-fname').setAttribute('disabled', true);
                document.querySelector('#landlord-fname').value = person.FirstName;

                document.querySelector('#landlord-lname').parentElement.parentElement.style.border = '1px solid #0a72cc';
            } else if(person.Description == 'Submitter Podnositelj prijave') {
                document.querySelector('#submitter-lname').setAttribute('disabled', true);
                document.querySelector('#submitter-lname').value = person.LastName;
                document.querySelector('#submitter-fname').setAttribute('disabled', true);
                document.querySelector('#submitter-fname').value = person.FirstName;

                document.querySelector('#submitter-lname').parentElement.parentElement.style.border = '1px solid #0a72cc';
            }

        }
        let listIndex = 0 ;
        for(let listPerson of listPpl) {
            listIndex += 1;
            let listPersonDescription = listPerson.Description.split('-');

            let listPersonBirthYear = '&nbsp';
            if(listPerson.BirthDate && listPerson.BirthDate.includes('-01-01')) {
                let listPersonBirthArr = listPerson.BirthDate.split('-');
                listPersonBirthYear = listPersonBirthArr[0];
            }

            console.log(listPersonBirthYear);
            let newListPerson = document.createElement('div');
            newListPerson.classList = 'list-person-single';

            newListPerson.innerHTML = 
                `<span class='start-span'> ${listIndex} </span>` +
                `<span class='first-span'>${listPerson.LastName} ${listPerson.FirstName} </span>` +
                `<span class='second-span'>${listPersonBirthYear}</span>` +
                `<span class='third-span'>${listPersonDescription[0] ? listPersonDescription[0] : '&nbsp'}</span>` +
                `<span class='fourth-span'>${listPersonDescription[1] ? listPersonDescription[1] : '&nbsp'}</span>` +
                `<span class='fifth-span'>${listPersonDescription[2] ? listPersonDescription[2] : '&nbsp'}</span>` +
                `<span class='sixth-span'><i class='fas fa-trash-alt' onClick='deleteRcPerson(${listPerson.PersonId}, ${itemId}, ${userId});'></i></span>`;

            pplListContainer.appendChild(newListPerson);
        }
        document.querySelector('#redni-broj-start').textContent = listPpl.length + 1;
        // console.log(topPpl);
        // console.log(listPpl);
    });
}

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

                           // loadPlaceData(itemIde, userIde);
                        
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
           // document.querySelector('#rc-doc-date').textContent = `Zagreb, ${jQuery('#rc-date-entry').val()}`;

            if (startDate != "" && startDate != oldStartDate) {
                jQuery('#startdateDisplay').parent('.item-date-display-container').css('display', 'block')
                jQuery('#startdateDisplay').parent('.item-date-display-container').siblings('.item-date-input-container').css('display', 'none')
                jQuery('#startdateDisplay').html(jQuery('#startdateentry').val())
            }
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
// listedPerson argument is just to make difference between listed people and other people on ration cards, false by default
function saveRcPerson(itemId, userId, firstName, lastName, description, spinner, listedPerson = false) {

    let itemIde = parseInt(document.querySelector('#rc-item-id').textContent);
    let userIde = parseInt(document.querySelector('#rc-user-id').textContent);

    jQuery('#' + spinner + '-spinner').css('display', 'block');

    // let firstName = escapeHtml(jQuery('#person-firstName-input').val());
    // let lastName = escapeHtml(jQuery('#person-lastName-input').val());
    // let birthDate = escapeHtml(jQuery('#rc-bdate').val());
    let birthDate = '';

    if(listedPerson == true) {
        birthDate = document.querySelector('#rc-bdate').value + '-01-01';
    }
    
    console.log(birthDate);


        deathDate = escapeHtml(jQuery('#person-deathDate-input').val().split('/'));

        let birthPlace = '';
        let deathPlace = '';
        let link = '';
    
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
            ItemId: itemIde,
            BirthDate: birthDate
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

                loadRcPerson(itemId, userId);
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
function loadRcDateData(itemId, userId) {
    jQuery.post(home_url + '/wp-content/themes/transcribathon/admin/inc/custom_scripts/send_ajax_api_request.php', {
        'type': 'GET',
        'url': TP_API_HOST + '/tp-api/items/' + itemId
    }, function(response) {
        response = JSON.parse(response);
        let itemData = JSON.parse(response.content);

        // let rationCardDateArr = itemData.DateStartDisplay.split('/');
        // let rationCardDate = `${rationCardDateArr[2]}/${rationCardDateArr[1]}/${rationCardDateArr[0]}`;

        document.querySelector('#rc-date-entry').value = itemData.DateStartDisplay;

        console.log(itemData);

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

        document.querySelector('#show-sub-loc').innerHTML = '';
        document.querySelector('#show-land-loc').innerHTML = '';
        document.querySelector('#show-bot-loc').innerHTML = '';
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
                        document.querySelector('#m-address').style.border = 'none';
                        document.querySelector('#m-address').style.borderBottom = '1px dotted #ccc';
                        document.querySelector('#m-address-check').style.display = 'none';
                        document.querySelector('#kbr').value = '';
                        document.querySelector('#kbr').removeAttribute('disabled');
                        document.querySelector('#kbr').style.border
                        document.querySelector('#kbr').style.borderBottom = '1px dotted #ccc';
                        document.querySelector('#kbr-check').style.display = 'none';
                        document.querySelector('#rc-place-one').style.display = 'inline-block';
                        document.querySelector('#edit-subm-container').style.display = 'none';

                        loadRcPlaceData(itemId, userId);
                });
            })

            let showPlace = document.createElement('div');
            showPlace.classList = 'location-single';

            showPlace.innerHTML = 
                `<img src='${home_url}/wp-content/themes/transcribathon/images/location-icon.svg' height='20px' width='20px' alt='location-icon'>` +
                `<p><b>${escapeRcHtml(submPlace.Name)}</b> (${escapeRcHtml(submPlace.Latitude)}, ${escapeRcHtml(submPlace.Longitude)})</p>` +
                `<p style='margin-top:0px;font-size:12px;'>Description: ${escapeRcHtml(submPlace.Comment)}</p>` +
                `<p style='margin-top:0px;font-size:12px;margin-left:30px;'>Wikidata Reference: <a href='https://wikidata.org/wiki/${escapeRcHtml(submPlace.WikidataId)}' style='text-decoration:none;' target='_blank'>` +
                    `${escapeRcHtml(submPlace.WikidataName)}, ${escapeRcHtml(submPlace.WikidataId)}</a></p>`;

            document.querySelector('#show-sub-loc').appendChild(showPlace);
                

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
                        document.querySelector('#landlord-loc').style.border = 'none'
                        document.querySelector('#landlord-loc').style.borderBottom = '1px dotted #ccc';
                        document.querySelector('#landlord-check').style.display = 'none';
                        document.querySelector('#l-lord-add').style.display = 'inline-block';
                        document.querySelector('#edit-llord-container').style.display = 'none';

                        loadRcPlaceData(itemId, userId);
                });
            })

            let showPlace = document.createElement('div');
            showPlace.classList = 'location-single';

            showPlace.innerHTML = 
                `<img src='${home_url}/wp-content/themes/transcribathon/images/location-icon.svg' height='20px' width='20px' alt='location-icon'>` +
                `<p><b>${escapeRcHtml(lLordPlace.Name)}</b> (${escapeRcHtml(lLordPlace.Latitude)}, ${escapeRcHtml(lLordPlace.Longitude)})</p>` +
                `<p style='margin-top:0px;font-size:12px;'>Description: ${escapeRcHtml(lLordPlace.Comment)}</p>` +
                `<p style='margin-top:0px;font-size:12px;margin-left:30px;'>Wikidata Reference: <a href='https://wikidata.org/wiki/${escapeRcHtml(submPlace.WikidataId)}' style='text-decoration:none;' target='_blank'>` +
                    `${escapeRcHtml(lLordPlace.WikidataName)}, ${escapeRcHtml(lLordPlace.WikidataId)}</a></p>`;

            document.querySelector('#show-land-loc').appendChild(showPlace);

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
                        document.querySelector('#shop-loc').style.border = 'none';
                        document.querySelector('#shop-loc').style.borderBottom = '1px dotted #ccc';
                        document.querySelector('#shop-name').value = '';
                        document.querySelector('#shop-name').removeAttribute('disabled');
                        document.querySelector('#shop-name').style.border = 'none';
                        document.querySelector('#shop-name').style.borderBottom = '1px dotted #ccc';
                        document.querySelector('#shop-check').style.display = 'none';
                        document.querySelector('#shop-name-check').style.display = 'none';
                        document.querySelector('#shop-loc-btn').style.display = 'inline-block';
                        document.querySelector('#edit-shop-container').style.display = 'none';

                        loadRcPlaceData(itemId, userId);
                });
            })

            let showPlace = document.createElement('div');
            showPlace.classList = 'location-single';

            showPlace.innerHTML = 
                `<img src='${home_url}/wp-content/themes/transcribathon/images/location-icon.svg' height='20px' width='20px' alt='location-icon'>` +
                `<p><b>${escapeRcHtml(shopPlace.Name)}</b> (${escapeRcHtml(shopPlace.Latitude)}, ${escapeRcHtml(shopPlace.Longitude)})</p>` +
                `<p style='margin-top:0px;font-size:12px;'>Description: ${escapeRcHtml(shopPlace.Comment)}</p>` +
                `<p style='margin-top:0px;font-size:12px;margin-left:30px;'>Wikidata Reference: <a href='https://wikidata.org/wiki/${escapeRcHtml(submPlace.WikidataId)}' style='text-decoration:none;' target='_blank'>` +
                    `${escapeRcHtml(shopPlace.WikidataName)}, ${escapeRcHtml(shopPlace.WikidataId)}</a></p>`;

            document.querySelector('#show-bot-loc').appendChild(showPlace);

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
    const itemId = parseInt(document.querySelector('#rc-item-id').textContent);
    const userId = parseInt(document.querySelector('#rc-user-id').textContent);
    
    loadRcPlaceData(itemId, userId);
    loadRcPerson(itemId, userId);
    loadRcDateData(itemId, userId);

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
    
            // document.querySelector('#submitter-place').textContent = locOneStreet.value;
            // document.querySelector('#house-nr').textContent = 'Kbr. ' + locOneNumb.value;
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
    
            // document.querySelector('#llord-place').textContent = lLordStreet.value;
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
        
                // document.querySelector('#tr-shop-name').textContent = shopName.value;
                // document.querySelector('#tr-shop-place').textContent = shopStreet.value;
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
    
            saveRcPerson(itemId, userId, firstName, lastName, description, 'submitter');
    
            // document.querySelector('#submitter-lf-name').textContent = lastName + ' ' + firstName;
            // document.querySelector('#reg-num').textContent = document.querySelector('#regnumb').value;
        })
    }

    // Landlord
    const lLordSaveBtn = document.querySelector('#save-l-lord');
    if(lLordSaveBtn) {
        lLordSaveBtn.addEventListener('click', function() {
            let description = 'Landlord / Kucevlasnik';
            let firstName = document.querySelector('#landlord-fname').value;
            let lastName = document.querySelector('#landlord-lname').value;
    
            saveRcPerson(itemId, userId, firstName, lastName, description, 'landlord');
    
            // document.querySelector('#llord-lf-name').textContent = lastName + ' ' + firstName;
            
        })
    }

    // Listed persons
    const lPersSaveBtn = document.querySelector('#save-list-person');

    if(lPersSaveBtn) {
        lPersSaveBtn.addEventListener('click', function() {
            let description = `${document.querySelector('#desc-rel').value} - ${document.querySelector('#desc-voc').value} - ${document.querySelector('#desc-wp').value}`;
            let firstName = document.querySelector('#lst-p-fname').value;
            let lastName = document.querySelector('#lst-p-lname').value;

            console.log(description);
    
            saveRcPerson(itemId, userId, firstName, lastName, description, 'listed-person', true);
    
            document.querySelector('#rc-list-form').reset();
        })
    }
    // Set document language to Croatian if it's Ration Card
    // if(document.querySelector('#rc-form')) {
    //     if(!document.querySelector('#transcription-selected-languages li')) {
    //         document.querySelector('#transcription-language-selector div[value="Hrvatski (Croatian)"]').click();
    //     }
    // }

    const submitForm = document.getElementById('submit-form');

    submitForm.addEventListener('click', function() {
        // Get data from form and pass it to variables
        const submitterLoc = document.getElementById('m-address').value;
        const submitterHouseNr = document.getElementById('kbr').value;
        const formNumber = document.getElementById('regnumb').value;
        const submitterLName = document.getElementById('submitter-lname').value;
        const submitterFName = document.getElementById('submitter-fname').value;
        const landlordLName = document.getElementById('landlord-lname').value;
        const landlordFName = document.getElementById('landlord-fname').value;
        const landlordLoc = document.getElementById('landlord-loc').value;
        const shopName = document.getElementById('shop-name').value;
        const shopLoc = document.getElementById('shop-loc').value;
        const docDate = document.getElementById('rc-date-entry').value;

        // Get listed persons
        const listedPersons = document.querySelector('#show-list-ppl').querySelectorAll('.list-person-single');
        let displayDiv = document.createElement('div');
        displayDiv.Id = 'display-list-ppl';
        // Loop trough nodes, clone them and add them to container
        for(let person of listedPersons) {
            let clonedPerson = person.cloneNode(true);
            displayDiv.appendChild(clonedPerson);
        }
         
        // Build transcription
        const transcriptionTemplate = document.createElement('div');
        transcriptionTemplate.classList = 'transcription-form';
        // Spans need classes, otherwise Tinymce doesn't add them to the editor
        transcriptionTemplate.innerHTML = 
            `<p class='out-first-row'><span class='out-first-span'> Grad Zagreb </span><span class='out-second-span'> A. </span><span class='out-third-span'> Prezime i Ime podnosioca prijave: </span></p>` +
            `<p class='out-second-row'>` +
                `<span class='out-first-span'> &nbsp </span>` +
                `<span class='out-second-span' style='font-size:9px;'> &nbsp REG. BROJ:</span>` +
                `<span class='out-third-span' style='border-bottom: 1px dotted #000;'> ${submitterLName} ${submitterFName} </span>` +
            `</p>` +
            `<p class='out-third-row'>` +
                `<span class='out-first-span'> Ulica, trg ili ina oznaka <span style='border-bottom:1px dotted #000;'> ${submitterLoc} </span> </span>` +
                `<span class='out-second-span'> ${formNumber ? formNumber : '&nbsp'} </span>` +
                `<span class='out-third-span' style='border-bottom: 1px dotted #000;'> ${landlordLName} ${landlordFName} </span>` +
            `</p>` +
            `<p class='out-fourth-row'>` +
                `<span class='out-first-span'> Kbr. <span style='border-bottom:1px dotted #000'> ${submitterHouseNr} </span></span>` +
                `<span class='out-second-span'> &nbsp </span>` +
                `<span class='out-third-span' style='border-bottom: 1px dotted #000;'> ${landlordLoc} </span>` +
            `</p>` +
            `<p class='form-title'> Potrošačka prijavnica </p>` +
            `<p class='form-sub-title'> za kućanstva i samce - samice </p>` +
            `<p class='form-cookies'> Potpisani ovim molim, da mi se izda potrošačka iskaznica, te podjedno izjavljujem pod odgovornošću iz čl. 18 st. 1 </p>` +
            `<p class='form-sub-cookies'> Naredbe o raspodjeli (racioniranju) životnih namirnica, da se u mojem kućanstvu hrane slijedeće osobe: </p>` +
            // Listed people head
            `<p class='rc-list-head'>` +
                `<span class='start-span' style='width: 5%;'> REDNI BROJ </span>` +
                `<span class='first-span'> PREZIME I IME </span>` +
                `<span class='second-span'> GODINA ROĐENJA </span>` +
                `<span class='third-span'> ODNOS PREMA PODNOSIOCU PRIJAVE ODN: STARJEŠINI </span>` +
                `<span class='fourth-span'> ZANIMANJE </span>` +
                `<span class='fifth-span'> MJESTO RADA </span>` +
            `</p>` +
            /// Maybe add other parts of form??
            `<div id='shop-container'>` +
                `<p class='display-shop-label'> Živežne namirnice nabavljat ću: </p>` +
                `<p class='display-shop'>` +
                    ` U radnji: ` +
                    `<span style='border-bottom: 1px dotted #000;min-width:20%;margin:0 10px;'> ${shopName} </span>` +
                    ` ulica ` + 
                    `<span style='border-bottom: 1px dotted #000;min-width:20%;margin:0 10px;'> ${shopLoc} </span>` +
                `</p>` +
            `</div>` +
            `<p class='display-form-date'> Zagreb, <span style='border-bottom: 1px dotted #000;'> ${docDate ? docDate : '&nbsp &nbsp &nbsp'} </span></p>`+
            `<p class='form-footer'> Ova prijavnica stoji din 0*75 i ne smije se skuplje prodavati. </p>` +
            `<p class='form-sub-footer'> Obrazac k. čl. 2 st. 3 naredbe o raspodjeli (racioniranju) životnih namirnica od 27. Siječnja 1941. </p>`;

            // append listed persons to the transcription
            transcriptionTemplate.insertBefore(displayDiv, transcriptionTemplate.querySelector('#shop-container'));

            // append transcription to test div
            tinymce.get('item-page-transcription-text').setContent(transcriptionTemplate.innerHTML);

            if(!document.querySelector('#transcription-selected-languages li')) {
                document.querySelector('#transcription-language-selector div[value="Hrvatski (Croatian)"]').click();
            }

    })

    // Add Prirast/Odpad tables on button click
    const prirastBtn = document.querySelector('#prirast-btn');
    const odpadBtn = document.querySelector('#odpad-btn');

    prirastBtn.addEventListener('click', function() {
        let newPrirast = document.createElement('div');
        newPrirast.Id = 'rc-prirast-list';
        newPrirast.style.width = '100%';
        newPrirast.style.display = 'block';

        let showPrirast = document.querySelector('#prirast-odpad');

        newPrirast.innerHTML =
            `<p style='font-size:9px;font-weight:600;'>PRIRAST: </p>` +
            `<form id='prirast-list-form'>` +
                `<div id='show-prirast-ppl'></div>` +
                `<div class='rc-list-td' style='position: relative;'>`+
                    `<span id='prirast-redni-broj' class='start-span'> 1 </span>` +
                    `<span class='first-span'>` +
                        `<span class='left-half'><input type='text' id='prirast-lname' placeholder=' Prezime' name='plname'></span>` +
                        `<span class='right-half'><input type='text' id='prirast-fname' placeholder=' Ime' name='pfname'></span>` +
                    `</span>` +
                    `<span class='second-span'><input type='text' id='prirast-bdate' name='prirast-bdate'></span>` +
                    `<span class='third-span'><input type='text' id='prirast-rel' name='p-relation'></span>` +
                    `<span class='fourth-span'><input type='text' id='prirast-voc' name='p-vocation'></span>` +
                    `<span class='fifth-span'><input type='text' id='prirast-wp' name='p-workplace'></span>` +
                    `<span class='sixth-span'>` +
                        `<i id='save-prirast-person' class='fas fa-plus'></i>` +
                        `<div id='prirast-spinner' class='spinner-container'>` +
                            `<span class='spinner'></span>` +
                        `</div>` +
                    `</span>` +
                `</div>` +
            `</form>`;

        showPrirast.insertBefore(newPrirast, showPrirast.firstChild);

        prirastBtn.style.display = 'none';
    });

    odpadBtn.addEventListener('click', function() {
        let newOdpad = document.createElement('div');
        newOdpad.Id = 'rc-odpad-list';
        newOdpad.style.width = '100%';
        newOdpad.style.display = 'block';

        let showOdpad = document.querySelector('#prirast-odpad');

        newOdpad.innerHTML =
            `<p style='font-size:9px;font-weight:600;'>ODPAD: </p>` +
            `<form id='odpad-list-form'>` +
                `<div id='show-odpad-ppl'></div>` +
                `<div class='rc-list-td' style='position: relative;'>`+
                    `<span id='odpad-redni-broj' class='start-span'> 1 </span>` +
                    `<span class='first-span'>` +
                        `<span class='left-half'><input type='text' id='odpad-lname' placeholder=' Prezime' name='olname'></span>` +
                        `<span class='right-half'><input type='text' id='odpad-fname' placeholder=' Ime' name='ofname'></span>` +
                    `</span>` +
                    `<span class='second-span'><input type='text' id='odpad-bdate' name='odpad-bdate'></span>` +
                    `<span class='third-span'><input type='text' id='odpad-rel' name='o-relation'></span>` +
                    `<span class='fourth-span'><input type='text' id='odpad-voc' name='o-vocation'></span>` +
                    `<span class='fifth-span'><input type='text' id='odpad-wp' name='o-workplace'></span>` +
                    `<span class='sixth-span'>` +
                        `<i id='save-odpad-person' class='fas fa-plus'></i>` +
                        `<div id='odpad-spinner' class='spinner-container'>` +
                            `<span class='spinner'></span>` +
                        `</div>` +
                    `</span>` +
                `</div>` +
            `</form>`;

        //showOdpad.insertBefore(newOdpad, showOdpad.firstChild);
        if(showOdpad.querySelector('#prirast-list-form')) {
            showOdpad.insertAdjacentElement('beforeend', newOdpad);
        } else {
            showOdpad.insertBefore(newOdpad, showOdpad.firstChild);
        }
        odpadBtn.style.display = 'none';
    });

});

