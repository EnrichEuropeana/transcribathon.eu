var home_url = WP_URLs.home_url;
var network_home_url = WP_URLs.network_home_url;

function escapeRcHtml(html){
    var text = document.createTextNode(html);
    var p = document.createElement('p');
    p.appendChild(text);

    return p.innerHTML;
}
function updateRcPerson(itemId, userId, firstName, lastName, description, spinner, personId) {

    jQuery('#' + spinner + '-spinner').css('display', 'block');

    birthDate = document.querySelector('#rc-bdate').value != '' ? document.querySelector('#rc-bdate').value + '-01-01' : '';
    // Prepare data and send API request
    let birthPlace = '';
    let deathPlace = '';
    let link = '';

    if (firstName == "" && lastName == "") {
        return 0;
    }

    data = {
        FirstName: firstName,
        LastName: lastName,
        BirthPlace: birthPlace,
        DeathPlace: deathPlace,
        Link: link,
        Description: description,
        ItemId: itemId,
        BirthDate: birthDate
    }
    data['DeathDate'] = null;
  
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
        
        loadRcPerson(itemId, userId);

        jQuery('#' + spinner + '-spinner').css('display', 'none');
      });
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
        const pplPrirastContainer = document.querySelector('#show-prirast-ppl');
        const pplOdpadContainer = document.querySelector('#show-odpad-ppl');

        pplListContainer.innerHTML = '';
        pplPrirastContainer.innerHTML = '';
        pplOdpadContainer.innerHTML = '';

        let topPpl = [];
        let listPpl = [];
        let prirastPpl = [];
        let odpadPpl = [];

        for(let person of allPpl) {
            console.log(person);
            if(person.Description == 'Landlord / Kucevlasnik' || person.Description == 'Submitter Podnositelj prijave') {
                topPpl.push(person);
            } else if (person.Description && person.Description.includes('Prirast')) {
                prirastPpl.push(person);
            } else if (person.Description && person.Description.includes('Odpad')) {
                odpadPpl.push(person);
            } else if (person.Description) {
                listPpl.push(person);
            }
        }

        for(let person of topPpl) {

            if(person.Description == 'Landlord / Kucevlasnik') {
                let landLName = document.querySelector('#landlord-lname');
                let landFName = document.querySelector('#landlord-fname');
                let landCheckmark = document.querySelector('#landlord-name-check');
                let landSave = document.querySelector('#save-l-lord');
                let landDelete = document.querySelector('#delete-l-lord');

                landLName.setAttribute('disabled', true);
                landLName.value = person.LastName;
                landLName.style.cssText = `
                    border-left: 1px solid #0a72cc;
                    border-top: 1px solid #0a72cc;
                    border-right: none;
                    border-bottom: 1px solid #0a72cc;
                `;
                landFName.setAttribute('disabled', true);
                landFName.value = person.FirstName;
                landFName.style.cssText = `
                    border-top: 1px solid #0a72cc;
                    border-right: 1px solid #0a72cc;
                    border-bottom: 1px solid #0a72cc;
                    border-left: none;
                `;
                landCheckmark.style.display = 'block';

                landSave.style.display = 'none';
                landDelete.style.display = 'block';

                // add delete event listener to delete btn
                landDelete.addEventListener('click', function() {
                    jQuery.post(home_url + '/wp-content/themes/transcribathon/admin/inc/custom_scripts/send_ajax_api_request.php', {
                        'type': 'DELETE',
                        'url': TP_API_HOST + '/tp-api/persons/' + person.PersonId
                    }, function(response) {
                        
                        landSave.style.display = 'block';
                        landDelete.style.display = 'none';

                        landFName.value = '';
                        landFName.style.border = 'none';
                        landFName.style.borderBottom = '1px dotted #ccc';
                        landFName.removeAttribute('disabled');

                        landLName.value = '';
                        landLName.style.border = 'none';
                        landLName.style.borderBottom = '1px dotted #ccc';
                        landLName.removeAttribute('disabled');

                        landCheckmark.style.display = 'none';
                    });
                })

            } else if(person.Description == 'Submitter Podnositelj prijave') {
                let subLName = document.querySelector('#submitter-lname');
                let subFName = document.querySelector('#submitter-fname');
                let subCheckmark = document.querySelector('#submitter-check');
                let subSave = document.querySelector('#save-submitter');
                let subDelete = document.querySelector('#delete-submitter');

                subLName.setAttribute('disabled', true);
                subLName.value = person.LastName;
                subLName.style.cssText = `
                    border-top: 1px solid #0a72cc;
                    border-right: none;
                    border-bottom: 1px solid #0a72cc;
                    border-left: 1px solid #0a72cc;
                `;
                // add person ID to the last name input, we need it for updating submitter
                subLName.setAttribute('person-id', person.PersonId);


                subFName.setAttribute('disabled', true);
                subFName.value = person.FirstName; 
                subFName.style.cssText = `
                    border-top: 1px solid #0a72cc;
                    border-right: 1px solid #0a72cc;
                    border-bottom: 1px solid #0a72cc;
                    border-left: none;
                `;
                subCheckmark.style.display = 'block';

                subSave.style.display = 'none';
                subDelete.style.display = 'block';

                subDelete.addEventListener('click', function() {
                    jQuery.post(home_url + '/wp-content/themes/transcribathon/admin/inc/custom_scripts/send_ajax_api_request.php', {
                        'type': 'DELETE',
                        'url': TP_API_HOST + '/tp-api/persons/' + person.PersonId
                    }, function(response) {
                        
                        subSave.style.display = 'block';
                        subDelete.style.display = 'none';

                        subFName.value = '';
                        subFName.style.border = 'none';
                        subFName.style.borderBottom = '1px dotted #ccc';
                        subFName.removeAttribute('disabled');

                        subLName.value = '';
                        subLName.style.border = 'none';
                        subLName.style.borderBottom = '1px dotted #ccc';
                        subLName.removeAttribute('disabled');

                        subCheckmark.style.display = 'none';
                    });
                })
            }

        }
        let listIndex = 0;
        for(let listPerson of listPpl) {
            listIndex += 1;
            let listPersonDescription = listPerson.Description.split('-');
            let lastSpan = `<span class='sixth-span'><i class='fas fa-trash-alt' onClick='deleteRcPerson(${listPerson.PersonId}, ${itemId}, ${userId});'></i></span>`;

            let listPersonBirthYear = '&nbsp';
            if(listPerson.BirthDate && listPerson.BirthDate.includes('-01-01')) {
                let listPersonBirthArr = listPerson.BirthDate.split('-');
                listPersonBirthYear = listPersonBirthArr[0];
            }
            if(listPerson.Description && listPerson.Description.includes('(Submitter)')) {

                let subLName = document.querySelector('#submitter-lname');
                let subFName = document.querySelector('#submitter-fname');
                let subCheckmark = document.querySelector('#submitter-check');
                let subSave = document.querySelector('#save-submitter');
                let subDelete = document.querySelector('#delete-submitter');

                // Change 'delete' icon function, so it just updates submitter description and removes him from list

                let submitterDescription = 'Submitter Podnositelj prijave';
                
                //
                lastSpan = `<span class='sixth-span'><i class='fas fa-trash-alt' onClick='updateRcPerson(${itemId}, ${userId}, "${listPerson.FirstName}", "${listPerson.LastName}", "${submitterDescription}", "listed-person", ${listPerson.PersonId});'></i></span>`;

                subLName.setAttribute('disabled', true);
                subLName.value = listPerson.LastName;
                subLName.style.cssText = `
                    border-top: 1px solid #0a72cc;
                    border-right: none;
                    border-bottom: 1px solid #0a72cc;
                    border-left: 1px solid #0a72cc;
                `;
                // add person ID to the last name input, we need it for updating submitter
                subLName.setAttribute('person-id', listPerson.PersonId);


                subFName.setAttribute('disabled', true);
                subFName.value = listPerson.FirstName; 
                subFName.style.cssText = `
                    border-top: 1px solid #0a72cc;
                    border-right: 1px solid #0a72cc;
                    border-bottom: 1px solid #0a72cc;
                    border-left: none;
                `;
                subCheckmark.style.display = 'block';

                subSave.style.display = 'none';
                subDelete.style.display = 'block';

                subDelete.addEventListener('click', function() {
                    jQuery.post(home_url + '/wp-content/themes/transcribathon/admin/inc/custom_scripts/send_ajax_api_request.php', {
                        'type': 'DELETE',
                        'url': TP_API_HOST + '/tp-api/persons/' + listPerson.PersonId
                    }, function(response) {
                        
                        subSave.style.display = 'block';
                        subDelete.style.display = 'none';

                        subFName.value = '';
                        subFName.style.border = 'none';
                        subFName.style.borderBottom = '1px dotted #ccc';
                        subFName.removeAttribute('disabled');

                        subLName.value = '';
                        subLName.style.border = 'none';
                        subLName.style.borderBottom = '1px dotted #ccc';
                        subLName.removeAttribute('disabled');

                        subCheckmark.style.display = 'none';
                    });
                })
            }

            let newListPerson = document.createElement('div');
            newListPerson.classList = 'list-person-single';

            newListPerson.innerHTML = 
                `<span class='start-span'>&nbsp ${listIndex} &nbsp</span>` +
                `<span class='first-span'>${listPerson.LastName} ${listPerson.FirstName} &nbsp</span>` +
                `<span class='second-span'>${listPersonBirthYear} &nbsp</span>` +
                `<span class='third-span'>${listPersonDescription[0] ? listPersonDescription[0] : '&nbsp'} &nbsp</span>` +
                `<span class='fourth-span'>${listPersonDescription[1] ? listPersonDescription[1] : '&nbsp'} &nbsp</span>` +
                `<span class='fifth-span'>${listPersonDescription[2] ? listPersonDescription[2] : '&nbsp'} &nbsp</span>` +
                lastSpan;

            pplListContainer.appendChild(newListPerson);

        }

        document.querySelector('#redni-broj-start').textContent = (listPpl.length + 1) + ' ';
    
        if(prirastPpl.length > 0) {

            let prirastContainer = document.querySelector('#prirast-container');
            let prirastBtn = document.querySelector('#prirast-btn');

            prirastContainer.style.display = 'block';
            prirastBtn.style.display = 'none';

            let prirastIndex = 0;
            for(let prirast of prirastPpl) {
                prirastIndex += 1;
                let prirastPersonDescription = prirast.Description.split('-');
    
                let prirastPersonBirthYear = '&nbsp';
                if(prirast.BirthDate && prirast.BirthDate.includes('-01-01')) {
                    let prirastPersonBirthArr = prirast.BirthDate.split('-');
                    prirastPersonBirthYear = prirastPersonBirthArr[0];
                }
                
                let newPrirast = document.createElement('div');
                newPrirast.classList = 'list-person-single';
    
                newPrirast.innerHTML =
                    `<span class='start-span'>&nbsp ${prirastIndex} &nbsp</span>` +
                    `<span class='first-span'> ${prirast.LastName} ${prirast.FirstName} &nbsp</span>` +
                    `<span class='second-span'> ${prirastPersonBirthYear} &nbsp</span>` +
                    `<span class='third-span'> ${prirastPersonDescription[0] ? prirastPersonDescription[0] : '&nbsp'} &nbsp</span>` +
                    `<span class='fourth-span'> ${prirastPersonDescription[1] ? prirastPersonDescription[1] : '&nbsp'} &nbsp</span>` +
                    `<span class='fifth-span'> ${prirastPersonDescription[2] ? prirastPersonDescription[2] : '&nbsp'} &nbsp</span>` +
                    `<span class='sixth-span'><i class='fas fa-trash-alt' onClick='deleteRcPerson(${prirast.PersonId}, ${itemId}, ${userId});'></i></span>`;
                
                pplPrirastContainer.appendChild(newPrirast);
            }
            document.querySelector('#prirast-redni-broj').textContent = (prirastPpl.length + 1) + ' ';
        }

        if(odpadPpl.length > 0) {

            let odpadContainer = document.querySelector('#odpad-container');
            let odpadBtn = document.querySelector('#odpad-btn');

            odpadContainer.style.display = 'block';
            odpadBtn.style.display = 'none';

            let odpadIndex = 0;
            for(let odpad of odpadPpl) {
                odpadIndex += 1;
                let odpadPersonDescription = odpad.Description.split('-');
    
                let odpadPersonBirthYear = '&nbsp';
                if(odpad.BirthDate && odpad.BirthDate.includes('-01-01')) {
                    let odpadPersonBirthArr = odpad.BirthDate.split('-');
                    odpadPersonBirthYear = odpadPersonBirthArr[0];
                }
                
                let newOdpad = document.createElement('div');
                newOdpad.classList = 'list-person-single';
    
                newOdpad.innerHTML =
                    `<span class='start-span'>&nbsp ${odpadIndex} &nbsp</span>` +
                    `<span class='first-span'> ${odpad.LastName} ${odpad.FirstName} &nbsp</span>` +
                    `<span class='second-span'> ${odpadPersonBirthYear} &nbsp</span>` +
                    `<span class='third-span'> ${odpadPersonDescription[0] ? odpadPersonDescription[0] : '&nbsp'} &nbsp</span>` +
                    `<span class='fourth-span'> ${odpadPersonDescription[1] ? odpadPersonDescription[1] : '&nbsp'} &nbsp</span>` +
                    `<span class='fifth-span'> ${odpadPersonDescription[2] ? odpadPersonDescription[2] : '&nbsp'} &nbsp</span>` +
                    `<span class='sixth-span'><i class='fas fa-trash-alt' onClick='deleteRcPerson(${odpad.PersonId}, ${itemId}, ${userId});'></i></span>`;
                
                pplOdpadContainer.appendChild(newOdpad);
            }
            document.querySelector('#odpad-redni-broj').textContent = (odpadPpl.length + 1) + ' ';
        }
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

            if (startDate != "" && startDate != oldStartDate) {
                jQuery('#startdateDisplay').parent('.item-date-display-container').css('display', 'block')
                jQuery('#startdateDisplay').parent('.item-date-display-container').siblings('.item-date-input-container').css('display', 'none')
                jQuery('#startdateDisplay').html(jQuery('#startdateentry').val())
            }

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
// Person Type argument is just to make difference between listed people and other people on ration cards, regular by default
function saveRcPerson(itemId, userId, firstName, lastName, description, spinner, personType = 'regular') {

    jQuery('#' + spinner + '-spinner').css('display', 'block');


    // let birthDate = escapeHtml(jQuery('#rc-bdate').val());
    let birthDate = '';

    if(personType == 'rc-list') {
        birthDate = document.querySelector('#rc-bdate').value + '-01-01';
    } else if(personType == 'prirast') {
        birthDate = document.querySelector('#prirast-bdate').value + '-01-01';
    } else if(personType == 'odpad') {
        birthDate = document.querySelector('#odpad-bdate').value + '-01-01';
    }

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
            ItemId: itemId,
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
        'url': TP_API_HOST + '/tp-api/items/' + itemId
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

                jQuery('#' + spinner + '-spinner').css('display', 'none');

                loadRcPerson(itemId, userId);
            })

            //loadPersonData(itemIde, userIde);
            if (taggingCompletion == "Not Started") {
                changeStatus(itemId, "Not Started", "Edit", "TaggingStatusId", 2, "#fff700", 4)
            }
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

        if(itemData.DateStartDisplay) {
            document.querySelector('#rc-date-entry').value = itemData.DateStartDisplay;
        }
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

// Updates the item transcription
function updateRcItemTranscription(itemId, userId, editStatusColor, statusCount) {

    // Char count for 'empty' ration cards, or characters included in form template
    const rcTemplateLength = 937;

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
                
                oldTranscriptionLength = 0;
                newTranscriptionLength = newTranscriptionLength - rcTemplateLength;
                if(currentTranscription != '') {
                    oldTranscriptionLength = currentTranscription.length - rcTemplateLength;
                }
            
                //var amount = newTranscriptionLength - currentTranscription.length;
                var amount = newTranscriptionLength - oldTranscriptionLength;

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
                    }
                    document.querySelector('#current-tr-view').innerHTML = curTrToUpdate;
                }
                jQuery('#item-transcription-spinner-container').css('display', 'none')

            });
        });
    }

}


///////////////////////////
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
        })
    }

    // Listed persons
    const lPersSaveBtn = document.querySelector('#save-list-person');

    if(lPersSaveBtn) {
        lPersSaveBtn.addEventListener('click', function() {
            let description = `${document.querySelector('#desc-rel').value} - ${document.querySelector('#desc-voc').value} - ${document.querySelector('#desc-wp').value}`;
            let firstName = document.querySelector('#lst-p-fname').value;
            let lastName = document.querySelector('#lst-p-lname').value;

            if(firstName == document.querySelector('#submitter-fname').value && lastName == document.querySelector('#submitter-lname').value) {
                let personId = document.querySelector('#submitter-lname').getAttribute('person-id');
                let submitterDescription = description + ' - (Submitter)';
                updateRcPerson(itemId, userId, firstName, lastName, submitterDescription, 'listed-person', personId);
            } else {
                saveRcPerson(itemId, userId, firstName, lastName, description, 'listed-person', 'rc-list');
            }
    
            document.querySelector('#rc-list-form').reset();
        })
    }

    // Generate transcription and append it to the Tinymce texeditor
    const submitForm = document.getElementById('submit-form');

    submitForm.addEventListener('click', function() {

        // Show spinner
        document.querySelector('#rc-submit-spinner').style.display = 'block';
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
        // Loop trough nodes, clone them and add them to container(If we don't clone them, they are removed from RC form, same for all people)
        for(let person of listedPersons) {
            let clonedPerson = person.cloneNode(true);
            displayDiv.appendChild(clonedPerson);
        }
        // Get prirast people
        const prirastPersons = document.querySelector('#show-prirast-ppl').querySelectorAll('.list-person-single');
        let prirastDisplay = document.createElement('div');
        prirastDisplay.Id = 'display-prirast-ppl';
        prirastDisplay.innerHTML = "<p style='font-size:9px;font-weight:600;'>PRIRAST: (ispunjava vlast) </p>";

        for(let person of prirastPersons) {
            let clonedPerson = person.cloneNode(true);
            prirastDisplay.appendChild(clonedPerson);
        }
        // Get odpad people
        const odpadPersons = document.querySelector('#show-odpad-ppl').querySelectorAll('.list-person-single');
        let odpadDisplay = document.createElement('div');
        odpadDisplay.Id = 'display-odpad-ppl';
        odpadDisplay.innerHTML = "<p style='font-size:9px;font-weight:600;'>ODPAD: (ispunjava vlast)</p>";

        for(let person of odpadPersons) {
            let clonedPerson = person.cloneNode(true);
            odpadDisplay.appendChild(clonedPerson);
        }
        // Generate the transcription and fill it with data from Ration Card form
        const transcriptionTemplate = document.createElement('div');
        transcriptionTemplate.classList = 'transcription-form';
        ///////////////
        transcriptionTemplate.innerHTML =
        `<p><b> Grad Zagreb </b></p>` +
        `<div class='rc-first-left-block' contenteditable='false' style='vertical-align:top;'>` +
            `<div class='rc-inner-left'>` + 
                `<p> Ulica, trg ili ina oznaka: <span class='sub-street-out' contenteditable='true'> ${submitterLoc ? submitterLoc : '&nbsp'} </span></p>` +
                `<p> Kbr. <span class='sub-street-out' contenteditable='true'> ${submitterHouseNr ? submitterHouseNr : '&nbsp'} </span></p>` +
            `</div>` +
            `<div class='rc-inner-right'>` +
                `<p> A. </p>` +
                `<p> REG.BROJ: </p>` +
                `<p contenteditable='true'> ${formNumber ? formNumber : '&nbsp'} </p>` +
            `</div>` +
        `</div>` +

        `<div class='rc-first-right-block' contenteditable='false'>` +
            `<p> Prezime i ime podnosioca prijave: </p>` +
            `<p contenteditable='true'>  ${submitterLName ? submitterLName : '&nbsp'} ${submitterFName ? submitterFName : '&nbsp'} </p>` +
            `<p> Prezime i ime i stan kućevlasnika: </p>` +
            `<p contenteditable='true'> ${landlordLName ? landlordLName : '&nbsp'} ${landlordFName ? landlordFName : '&nbsp'} </p>` +
            `<p contenteditable='true'> ${landlordLoc ? landlordLoc : '&nbsp &nbsp'} </p>` +
        `</div>` +

        `<p class='form-title' contenteditable='false'> Potrošačka prijavnica </br>` +
        `za kućanstva i samce - samice </p>` +
        `<p class='form-cookies' contenteditable='false'> Potpisani ovim molim, da mi se izda potrošačka iskaznica, te podjedno izjavljujem pod odgovornošću iz čl. 18 st. 1 </br>` +
        `Naredbe o raspodjeli (racioniranju) životnih namirnica, da se u mojem kućanstvu hrane slijedeće osobe: </p>` +


        // Listed people head
        `<p class='rc-list-head'>` +
            `<span class='start-span' style='width: 5%;' contenteditable='false'> REDNI BROJ &nbsp</span>` +
            `<span class='first-span' contenteditable='false'> PREZIME I IME &nbsp</span>` +
            `<span class='second-span' contenteditable='false'> GODINA ROĐENJA &nbsp</span>` +
            `<span class='third-span' contenteditable='false'> ODNOS PREMA PODNOSIOCU PRIJAVE ODN. STARJEŠINI &nbsp</span>` +
            `<span class='fourth-span' contenteditable='false'> ZANIMANJE &nbsp</span>` +
            `<span class='fifth-span' contenteditable='false'> MJESTO RADA &nbsp</span>` +
        `</p>` +
        `${displayDiv.querySelector('.list-person-single') ? 
            `<span id='list-placeholder'></span>` 
            :
            `<p class='list-person-single'>` +
                `<span class='start-span' style='width: 5%;' > 1 </span>` +
                `<span class='first-span' > &nbsp </span>` +
                `<span class='second-span' > &nbsp </span>` +
                `<span class='third-span' > &nbsp </span>` +
                `<span class='fourth-span' > &nbsp </span>` +
                `<span class='fifth-span' > &nbsp </span>` +
            `</p>` 
        }` +
        /// Prirast
        `${document.querySelector('#show-prirast-ppl').innerHTML != '' ?
            `<span id='prirast-placeholder'></span>`
            :
            `<p style='font-size:9px;font-weight:600;'>PRIRAST: (ispunjava vlast)</p>` +
            `<p class='list-person-single'>` +
                `<span class='start-span' style='width: 5%;' > 1 </span>` +
                `<span class='first-span' > &nbsp </span>` +
                `<span class='second-span' > &nbsp </span>` +
                `<span class='third-span' > &nbsp </span>` +
                `<span class='fourth-span' > &nbsp </span>` +
                `<span class='fifth-span' > &nbsp </span>` +
            `</p>` 
        }` +
        /// Odpad
        `${document.querySelector('#show-odpad-ppl').innerHTML != '' ?
            `<span id='odpad-placeholder'></span>`
            :
            `<p style='font-size:9px;font-weight:600;'>ODPAD: (ispunjava vlast)</p>` +
            `<p class='list-person-single'>` +
                `<span class='start-span' style='width: 5%;' > 1 </span>` +
                `<span class='first-span' > &nbsp </span>` +
                `<span class='second-span' > &nbsp </span>` +
                `<span class='third-span' > &nbsp </span>` +
                `<span class='fourth-span' > &nbsp </span>` +
                `<span class='fifth-span' > &nbsp </span>` +
            `</p>` 
        }` +
        // Zalihe // Without Inline Style, tinymce gets rid of 'span' tags and breaks layout
        `<p class='form-cookies'> Ujedno izjavljujem pod istom odgovornošću, da u mojem kućanstvu postoje slijedeće zalihe životnih namirnica u</br>` +
        `Kilogramima odnosno litrama: </p>` +

        `<div class='rc-namirnice-table' style='line-height:0!important;'>` +
            `<div class='rc-psenica rc-not'>` +
                `<p class='rc-namirnice-name' style='min-height:15px!important;' contenteditable='false'> PŠENICA </p>` +
                `<div class='rc-namirnice-left rc-not rc-inner-bot-div'>` +
                    `<p style='border-bottom:1px solid #000;min-height:15px!important;' contenteditable='false'> ZRNO </p>` +
                    `<p class='rc-namirnice-input'></p>` +
                `</div>` +
                `<div class='rc-namirnice-right rc-not rc-inner-bot-div'>` +
                    `<p class='rc-brasno' contenteditable='false'> BRAŠNO </p>` +
                    `<p class='rc-namirnice-input'></p>` +
                `</div>` +
            `</div>` +

            `<div class='rc-raz rc-not'>` +
                `<p class='rc-namirnice-name' style='min-height:15px!important;' contenteditable='false'> RAŽ </p>` +
                `<div class='rc-namirnice-left rc-not rc-inner-bot-div'>` +
                    `<p style='border-bottom:1px solid #000;min-height:15px!important;' contenteditable='false'> ZRNO </p>` +
                    `<p class='rc-namirnice-input' ></p>` +
                `</div>` +
                `<div class='rc-namirnice-right rc-not rc-inner-bot-div'>` +
                    `<p class='rc-brasno' contenteditable='false'> BRAŠNO </p>` +
                    `<p class='rc-namirnice-input'></p>` +
                `</div>` +
            `</div>` +

            `<div class='rc-jecam rc-not'>` +
                `<p class='rc-namirnice-name' style='min-height:15px!important;' contenteditable='false'> JEČAM </p>` +
                `<div class='rc-namirnice-left rc-not rc-inner-bot-div'>` +
                    `<p style='border-bottom:1px solid #000;min-height:15px!important;' contenteditable='false'> ZRNO </p>` +
                    `<p class='rc-namirnice-input'></p>` +
                `</div>`+
                `<div class='rc-namirnice-right rc-not rc-inner-bot-div'>` +
                    `<p class='rc-brasno' contenteditable='false'> BRAŠNO </p>` +
                    `<p class='rc-namirnice-input'></p>` +
                `</div>` +
            `</div>` +

            `<div class='rc-kukuruz rc-not'>` +
                `<p class='rc-namirnice-name' style='min-height:15px!important;' contenteditable='false'> KUKURUZ </p>` +
                `<div class='kukuruz-left rc-not rc-inner-bot-div'>` +
                    `<p style='border-bottom:1px solid #000;min-height:15px!important;' contenteditable='false'> ZRNO </p>` +
                    `<p class='rc-namirnice-input'></p>` +
                `</div>` +
                `<div class='kukuruz-mid rc-not rc-inner-bot-div'>` +
                    `<p style='border-bottom:1px solid #000;min-height:15px!important;' contenteditable='false'> KLIP </p>` +
                    `<p class='rc-namirnice-input' ></p>` +
                `</div>` +
                `<div class='kukuruz-right rc-not rc-inner-bot-div'>` +
                    `<p class='rc-brasno' contenteditable='false'> BRAŠNO </p>` +
                    `<p class='rc-namirnice-input'></p>` +
                `</div>` +
            `</div>` +

            `<div class='rc-namirnice-head-other'>` +
                `<p class='rc-namirnice-name' contenteditable='false' style='overflow:hidden;text-overflow:ellipsis;'> TJESTENINE </p>` +
                `<p class='rc-namirnice-input'></p>` +
            `</div>` +
        
            `<div class='rc-namirnice-head-other'>` +
                `<p class='rc-namirnice-name' contenteditable='false' style='overflow:hidden;text-overflow:ellipsis;'> JESTIVO ULJE </p>` +
                `<p class='rc-namirnice-input'></p>` +
            `</div>` +

            `<div class='rc-namirnice-head-other'>` +
                `<p class='rc-namirnice-name' contenteditable='false'> MAST </p>` +
                `<p class='rc-namirnice-input'></p>` +
            `</div>` +

            `<div class='rc-namirnice-head-other'>` +
                `<p class='rc-namirnice-name' contenteditable='false'> SOL </p>` +
                `<p class='rc-namirnice-input'></p>` +
            `</div>` +

            `<div class='rc-namirnice-head-other'>` +
                `<p class='rc-namirnice-name' contenteditable='false'> ŠEĆER </p>` +
                `<p class='rc-namirnice-input'></p>` +
            `</div>` +

            `<div class='rc-namirnice-head-other'>` +
                `<p class='rc-namirnice-name' contenteditable='false'> KAVA </p>` +
                `<p class='rc-namirnice-input'></p>` +
            `</div>` +

            `<div class='rc-namirnice-head-other'>` +
                `<p class='rc-namirnice-name' contenteditable='false' style='height:30px;font-size:9px!important;overflow:hidden;text-overflow:ellipsis;'> SAPUN ZA PRANJE </p>` +
                `<p class='rc-namirnice-input'></p>` +
            `</div>` +

            `<div class='rc-namirnice-head-other'>` +
                `<p class='rc-namirnice-name' contenteditable='false' style='overflow:hidden;text-overflow:ellipsis;'> PETROLEJ </p>` +
                `<p class='rc-namirnice-input'></p>` +
            `</div>` +

            `<p class='rc-povecanje' contenteditable='false' style='border-left: 1px solid #000;border-right:1px solid #000;'> POVEĆANJE </p>` +

            `<div class='rc-psenica rc-not rc-bot-div'>` +
                `<p class='rc-hidden-head'> PŠENICA </p>` +
                `<div class='rc-namirnice-left rc-not rc-bot-div' style='height:29px!important;'>` +
                    `<p class='rc-hidden-head'> ZRNO </p>` +
                    `<p class='rc-namirnice-input'></p>` +
                `</div>` +
                `<div class='rc-namirnice-right rc-not rc-bot-div'>` +
                    `<p class='rc-hidden-head'> BRAŠNO </p>` +
                    `<p class='rc-namirnice-input' ></p>` +
                `</div>` +
            `</div>` +

            `<div class='rc-raz rc-not rc-bot-div'>` +
                `<p class='rc-hidden-head'> RAŽ </p>` +
                `<div class='rc-namirnice-left rc-not rc-bot-div' style='height:29px!important;'>` +
                    `<p class='rc-hidden-head'> ZRNO </p>` +
                    `<p class='rc-namirnice-input'></p>` +
                `</div>` +
                `<div class='rc-namirnice-right rc-not rc-bot-div'>` +
                    `<p class='rc-hidden-head'> BRAŠNO </p>` +
                    `<p class='rc-namirnice-input'></p>` +
                `</div>` +
            `</div>` +

            `<div class='rc-jecam rc-not rc-bot-div'>` +
                `<p class='rc-hidden-head'> JEČAM </p>` +
                `<div class='rc-namirnice-left rc-not rc-bot-div' style='height:29px!important;'>` +
                    `<p class='rc-hidden-head'> ZRNO </p>` +
                    `<p class='rc-namirnice-input'></p>` +
                `</div>` +
                `<div class='rc-namirnice-right rc-not rc-bot-div'>` +
                    `<p class='rc-hidden-head'> BRAŠNO </p>` +
                    `<p class='rc-namirnice-input' ></p>` +
                `</div>` +
            `</div>` +

            `<div class='rc-kukuruz rc-not rc-bot-div'>` +
                `<p class='rc-hidden-head'> KUKURUZ </p>` +
                `<div class='kukuruz-left rc-not rc-bot-div' style='height:29px!important;'>` +
                    `<p class='rc-hidden-head'> ZRNO </p>` +
                    `<p class='rc-namirnice-input'></p>` +
                `</div>` +
                `<div class='kukuruz-mid rc-not rc-bot-div' style='height:29px!important;'>` +
                    `<p class='rc-hidden-head'> KLIP </p>` +
                    `<p class='rc-namirnice-input'></p>` +
                `</div>` +
                `<div class='kukuruz-right rc-not rc-bot-div'>` +
                    `<p class='rc-hidden-head'> BRAŠNO </p>` +
                    `<p class='rc-namirnice-input'></p>` +
                `</div>` +
            `</div>` +

            `<div class='rc-namirnice-head-other rc-bot-div'>` +
                `<p class='rc-hidden-head'> TJESTENINE </p>` +
                `<p class='rc-namirnice-input'></p>` +
            `</div>` +
        
            `<div class='rc-namirnice-head-other rc-bot-div'>` +
                `<p class='rc-hidden-head'> JESTIVO ULJE </p>` + 
                `<p class='rc-namirnice-input'></p>` +
            `</div>` +

            `<div class='rc-namirnice-head-other rc-bot-div'>` +
                `<p class='rc-hidden-head'> MAST </p>` +
                `<p class='rc-namirnice-input'></p>` +
            `</div>` +

            `<div class='rc-namirnice-head-other rc-bot-div'>` +
                `<p class='rc-hidden-head'> SOL </p>` +
                `<p class='rc-namirnice-input'></p>` +
            `</div>` +

            `<div class='rc-namirnice-head-other rc-bot-div'>` +
                `<p class='rc-hidden-head'> ŠEĆER </p>` +
                `<p class='rc-namirnice-input'></p>` +
            `</div>` +

            `<div class='rc-namirnice-head-other rc-bot-div'>` +
                `<p class='rc-hidden-head'> KAVA </p>` +
                `<p class='rc-namirnice-input'></p>` +
            `</div>` +

            `<div class='rc-namirnice-head-other rc-bot-div'>` +
                `<p class='rc-hidden-head'> SAPUN ZA PRANJE </p>` +
                `<p class='rc-namirnice-input'></p>` +
            `</div>` +

            `<div class='rc-namirnice-head-other rc-bot-div'>` +
                `<p class='rc-hidden-head'> PETROLEJ </p>` +
                `<p class='rc-namirnice-input'></p>` +
            `</div>` +
        `</div>` +


        `<div id='shop-container'>` +
            `<p class='display-shop-label' contenteditable='false'> Živežne namirnice nabavljat ću: </p>` +
            `<p class='display-shop' contenteditable='false'>` +
                ` U radnji: ` +
                `<span style='border-bottom: 1px dotted #000;min-width:30%;margin:0 10px;display:inline-block;' contenteditable='true'> ${shopName ? shopName : '&nbsp'} </span>` +
                ` ulica ` + 
                `<span style='border-bottom: 1px dotted #000;min-width:30%;margin:0 10px;display:inline-block;' contenteditable='true'> ${shopLoc ? shopLoc : '&nbsp'} </span>` +
            `</p>` +
        `</div>` +
        `<p class='display-form-date' contenteditable='false'> Zagreb, <span style='border-bottom: 1px dotted #000;' contenteditable='true'> ${docDate ? docDate : '&nbsp &nbsp &nbsp'} </span></p>`+
        `<p class='form-signature' contenteditable='false'><span style='display:inline-block;float:right;font-size:10px;font-weight:600;'>POTPIS PODNOSIOCA PRIJAVE</span></p>` +
        `<div style='clear:both;height:5px;' contenteditable='false'></div>` +
        `<p class='form-signature' contenteditable='false'><span style='display:inline-block;float:right;min-width:80px;border-bottom: 1px dotted #000;' contenteditable='true'> &nbsp </span></p>` +
        `<div style='clear:both;' contenteditable='false'></div>` +
        `<p class='form-footer' contenteditable='false'> Ova prijavnica stoji din 0*75 i ne smije se skuplje prodavati. </p>` +
        `<p class='form-sub-footer' contenteditable='false'> Obrazac k. čl. 2 st. 3 naredbe o raspodjeli (racioniranju) životnih namirnica od 27. Siječnja 1941. </p>`;



        //////////////////
        // Spans need classes, otherwise Tinymce doesn't add them to the editor
        // transcriptionTemplate.innerHTML = 
        //     `<p class='out-first-row' contenteditable='false'><span class='out-first-span'> Grad Zagreb &emsp;</span>  <span class='out-second-span'> A. &emsp;</span><span class='out-third-span'> Prezime i Ime podnosioca prijave: </span></p>` +
        //     `<p class='out-second-row'>` +
        //         `<span class='out-first-span' contenteditable='false'> &nbsp </span>` +
        //         `<span class='out-second-span' style='font-size:9px;' contenteditable='false'> &nbsp REG. BROJ: &emsp;</span>` +
        //         `<span class='out-third-span' style='border-bottom: 1px dotted #000;text-align:left;'> ${submitterLName ? submitterLName : '&nbsp'} ${submitterFName ? submitterFName : '&nbsp'} </span>` +
        //     `</p>` +
        //     `<p class='out-second-subrow'>` +
        //         `<span class='out-first-span' contenteditable='false'> &nbsp </span>` +
        //         `<span class='out-second-span' style='font-size:9px;border-bottom: 1px dotted #000;'> ${formNumber ? formNumber : '&nbsp'} &emsp;</span>` +
        //         `<span class='out-third-span' contenteditable='false'> Prezime i ime i stan kućevlasnika: </span>` +
        //     `</p>` +
        //     `<p class='out-third-row'>` +
        //         `<span class='out-first-span' contenteditable='false'> Ulica, trg ili ina oznaka: <span style='border-bottom:1px dotted #000;min-width:70px;display:inline-block;' contenteditable='true'> ${submitterLoc ? submitterLoc : '&nbsp'} </span> </span>` +
        //         `<span class='out-second-span' contenteditable='false'> &nbsp &emsp; </span>` +
        //         `<span class='out-third-span' style='border-bottom: 1px dotted #000;text-align:left;'> ${landlordLName ? landlordLName : '&nbsp'} ${landlordFName ? landlordFName : '&nbsp'} </span>` +
        //     `</p>` +
        //     `<p class='out-fourth-row'>` +
        //         `<span class='out-first-span'> Kbr. <span style='border-bottom:1px dotted #000;display:inline-block;min-width:50px;'> ${submitterHouseNr ? submitterHouseNr : '&nbsp'} &emsp;</span></span>` +
        //         `<span class='out-second-span'> &nbsp </span>` +
        //         `<span class='out-third-span' style='border-bottom: 1px dotted #000;text-align:left;'> ${landlordLoc ? landlordLoc : '&nbsp &nbsp'} </span>` +
        //     `</p>` +
        //     `<p class='form-title' contenteditable='false'> Potrošačka prijavnica </br>` +
        //     `za kućanstva i samce - samice </p>` +
        //     `<p class='form-cookies' contenteditable='false'> Potpisani ovim molim, da mi se izda potrošačka iskaznica, te podjedno izjavljujem pod odgovornošću iz čl. 18 st. 1 </br>` +
        //     `Naredbe o raspodjeli (racioniranju) životnih namirnica, da se u mojem kućanstvu hrane slijedeće osobe: </p>` +
        //     // Listed people head
        //     `<p class='rc-list-head'>` +
        //         `<span class='start-span' style='width: 5%;' contenteditable='false'> REDNI BROJ &nbsp</span>` +
        //         `<span class='first-span' contenteditable='false'> PREZIME I IME &nbsp</span>` +
        //         `<span class='second-span' contenteditable='false'> GODINA ROĐENJA &nbsp</span>` +
        //         `<span class='third-span' contenteditable='false'> ODNOS PREMA PODNOSIOCU PRIJAVE ODN. STARJEŠINI &nbsp</span>` +
        //         `<span class='fourth-span' contenteditable='false'> ZANIMANJE &nbsp</span>` +
        //         `<span class='fifth-span' contenteditable='false'> MJESTO RADA &nbsp</span>` +
        //     `</p>` +
        //     `${displayDiv.querySelector('.list-person-single') ? 
        //         `<span id='list-placeholder'></span>` 
        //         :
        //         `<p class='list-person-single'>` +
        //             `<span class='start-span' style='width: 5%;' > 1 </span>` +
        //             `<span class='first-span' > &nbsp </span>` +
        //             `<span class='second-span' > &nbsp </span>` +
        //             `<span class='third-span' > &nbsp </span>` +
        //             `<span class='fourth-span' > &nbsp </span>` +
        //             `<span class='fifth-span' > &nbsp </span>` +
        //         `</p>` 
        //     }` +
        //     /// Prirast
        //     `${document.querySelector('#show-prirast-ppl').innerHTML != '' ?
        //         `<span id='prirast-placeholder'></span>`
        //         :
        //         `<p style='font-size:9px;font-weight:600;'>PRIRAST: (ispunjava vlast)</p>` +
        //         `<p class='list-person-single'>` +
        //             `<span class='start-span' style='width: 5%;' > 1 </span>` +
        //             `<span class='first-span' > &nbsp </span>` +
        //             `<span class='second-span' > &nbsp </span>` +
        //             `<span class='third-span' > &nbsp </span>` +
        //             `<span class='fourth-span' > &nbsp </span>` +
        //             `<span class='fifth-span' > &nbsp </span>` +
        //         `</p>` 
        //     }` +
        //     /// Odpad
        //     `${document.querySelector('#show-odpad-ppl').innerHTML != '' ?
        //         `<span id='odpad-placeholder'></span>`
        //         :
        //         `<p style='font-size:9px;font-weight:600;'>ODPAD: (ispunjava vlast)</p>` +
        //         `<p class='list-person-single'>` +
        //             `<span class='start-span' style='width: 5%;' > 1 </span>` +
        //             `<span class='first-span' > &nbsp </span>` +
        //             `<span class='second-span' > &nbsp </span>` +
        //             `<span class='third-span' > &nbsp </span>` +
        //             `<span class='fourth-span' > &nbsp </span>` +
        //             `<span class='fifth-span' > &nbsp </span>` +
        //         `</p>` 
        //     }` +
        //     // Zalihe // Without Inline Style, tinymce gets rid of 'span' tags and breaks layout
        //     `<p class='form-cookies'> Ujedno izjavljujem pod istom odgovornošću, da u mojem kućanstvu postoje slijedeće zalihe životnih namirnica u</br>` +
        //     `Kilogramima odnosno litrama: </p>` +

        //     `<div id='zalihe-container'>` +
        //         `<div id='zalihe-head'>` +
        //             `<span style='width:11.6%;height:50px;border-bottom:1px solid #000;border-right:1px solid #000;'>` +
        //                 `<span style='width:100%;height:50%;border-bottom:1px solid #000;'> PŠENICA &nbsp</span>` +
        //                 `<span style='width:50%;font-size:8px;height:50%;'> ZRNO &nbsp</span>` +
        //                 `<span style='width:50%;font-size:8px;height:50%;border-left: 1px solid #000;'> BRAŠNO &nbsp</span>` +
        //             `</span>` +
        //             `<span style='width:11.6%;height:50px;border-bottom:1px solid #000;border-right:1px solid #000;'>` +
        //                 `<span style='width:100%;height:50%;border-bottom:1px solid #000;'> RAŽ &nbsp</span>` +
        //                 `<span style='width:50%;font-size:8px;height:50%;border-right:1px solid #000;'> ZRNO &nbsp</span>` +
        //                 `<span style='width:50%;font-size:8px;height:50%;'> BRAŠNO &nbsp</span>` +
        //             `</span>` +
        //             `<span style='width:11.6%;height:50px;border-bottom:1px solid #000;border-right:1px solid #000;'>` +
        //                 `<span style='width:100%;height:50%;border-bottom:1px solid #000;'> JEČAM &nbsp</span>` +
        //                 `<span style='width:50%;font-size:8px;height:50%;border-right:1px solid #000;'> ZRNO &nbsp</span>` +
        //                 `<span style='width:50%;font-size:8px;height:50%;'> BRAŠNO &nbsp</span>` +
        //             `</span>` +
        //             `<span style='width:17.4%;height:50px;border-bottom:1px solid #000;border-right:1px solid #000;'>` +
        //                 `<span style='width:100%;height:50%;border-bottom:1px solid #000;'> KUKURUZ &nbsp</span>` +
        //                 `<span style='width:33%;font-size:8px;height:50%;border-right:1px solid #000;'> ZRNO &nbsp</span>` +
        //                 `<span style='width:34%;font-size:8px;height:50%;border-right:1px solid #000;'> KLIP &nbsp</span>` +
        //                 `<span style='width:33%;font-size:8px;height:50%;'> BRAŠNO &nbsp</span>` +
        //             `</span>` +

        //             `<span style='width:5.8%;height:50px;border-bottom:1px solid #000;border-right:1px solid #000;'> TJESTENINE &nbsp</span>` +
        //             `<span style='width:5.8%;height:50px;border-bottom:1px solid #000;border-right:1px solid #000;'> JESTIVO ULJE &nbsp</span>` +
        //             `<span style='width:5.8%;height:50px;border-bottom:1px solid #000;border-right:1px solid #000;'> MAST &nbsp</span>` +
        //             `<span style='width:5.8%;height:50px;border-bottom:1px solid #000;border-right:1px solid #000;'> SOL &nbsp</span>` +
        //             `<span style='width:5.8%;height:50px;border-bottom:1px solid #000;border-right:1px solid #000;'> ŠEĆER &nbsp</span>` +
        //             `<span style='width:5.8%;height:50px;border-bottom:1px solid #000;border-right:1px solid #000;'> KAVA &nbsp</span>` +
        //             `<span style='width:5.8%;height:50px;border-bottom:1px solid #000;border-right:1px solid #000;'> SAPUN ZA PRANJE &nbsp</span>` +
        //             `<span style='width:7.2%;height:50px;border-bottom:1px solid #000;'> PETROLEJ &nbsp</span>` +
        //         `</div>` +
        //         `<div id='zalihe-top'>` +
        //             `<span style='width:5.8%;height:50px;'> &nbsp </span>` +
        //             `<span style='width:5.8%;height:50px;'> &nbsp </span>` +
        //             `<span style='width:5.8%;height:50px;'> &nbsp </span>` +
        //             `<span style='width:5.8%;height:50px;'> &nbsp </span>` +
        //             `<span style='width:5.8%;height:50px;'> &nbsp </span>` +
        //             `<span style='width:5.8%;height:50px;'> &nbsp </span>` +
        //             `<span style='width:5.8%;height:50px;'> &nbsp </span>` +
        //             `<span style='width:5.8%;height:50px;'> &nbsp </span>` +
        //             `<span style='width:5.8%;height:50px;'> &nbsp </span>` +
        //             `<span style='width:5.8%;height:50px;'> &nbsp </span>` +
        //             `<span style='width:5.8%;height:50px;'> &nbsp </span>` +
        //             `<span style='width:5.8%;height:50px;'> &nbsp </span>` +
        //             `<span style='width:5.8%;height:50px;'> &nbsp </span>` +
        //             `<span style='width:5.8%;height:50px;'> &nbsp </span>` +
        //             `<span style='width:5.8%;height:50px;'> &nbsp </span>` +
        //             `<span style='width:5.8%;height:50px;'> &nbsp </span>` +
        //             `<span style='width:7.2%;height:50px;border-bottom:1px solid #000;'> &nbsp </span>` +
        //         `</div>` +
        //         `<div id='zalihe-mid'>` +
        //             `<p style='text-align:center;font-size:12px;letter-spacing:1em;'> POVEĆANJE </p>` +
        //         `</div>` +
        //         `<div id='zalihe-bot'>` +
        //             `<span style='width:5.8%;height:50px;'> &nbsp </span>` +
        //             `<span style='width:5.8%;height:50px;'> &nbsp </span>` +
        //             `<span style='width:5.8%;height:50px;'> &nbsp </span>` +
        //             `<span style='width:5.8%;height:50px;'> &nbsp </span>` +
        //             `<span style='width:5.8%;height:50px;'> &nbsp </span>` +
        //             `<span style='width:5.8%;height:50px;'> &nbsp </span>` +
        //             `<span style='width:5.8%;height:50px;'> &nbsp </span>` +
        //             `<span style='width:5.8%;height:50px;'> &nbsp </span>` +
        //             `<span style='width:5.8%;height:50px;'> &nbsp </span>` +
        //             `<span style='width:5.8%;height:50px;'> &nbsp </span>` +
        //             `<span style='width:5.8%;height:50px;'> &nbsp </span>` +
        //             `<span style='width:5.8%;height:50px;'> &nbsp </span>` +
        //             `<span style='width:5.8%;height:50px;'> &nbsp </span>` +
        //             `<span style='width:5.8%;height:50px;'> &nbsp </span>` +
        //             `<span style='width:5.8%;height:50px;'> &nbsp </span>` +
        //             `<span style='width:5.8%;height:50px;'> &nbsp </span>` +
        //             `<span style='width:7.2%;height:50px;border-top:1px solid #000;'> &nbsp </span>` +
        //         `</div>` +
        //     `</div>` +
        //     `<div id='shop-container'>` +
        //         `<p class='display-shop-label' contenteditable='false'> Živežne namirnice nabavljat ću: </p>` +
        //         `<p class='display-shop' contenteditable='false'>` +
        //             ` U radnji: ` +
        //             `<span style='border-bottom: 1px dotted #000;min-width:30%;margin:0 10px;display:inline-block;' contenteditable='true'> ${shopName ? shopName : '&nbsp'} </span>` +
        //             ` ulica ` + 
        //             `<span style='border-bottom: 1px dotted #000;min-width:30%;margin:0 10px;display:inline-block;' contenteditable='true'> ${shopLoc ? shopLoc : '&nbsp'} </span>` +
        //         `</p>` +
        //     `</div>` +
        //     `<p class='display-form-date' contenteditable='false'> Zagreb, <span style='border-bottom: 1px dotted #000;' contenteditable='true'> ${docDate ? docDate : '&nbsp &nbsp &nbsp'} </span></p>`+
        //     `<p class='form-signature' contenteditable='false'><span style='display:inline-block;float:right;font-size:10px;font-weight:600;'>POTPIS PODNOSIOCA PRIJAVE</span></p>` +
        //     `<div style='clear:both;height:5px;' contenteditable='false'></div>` +
        //     `<p class='form-signature' contenteditable='false'><span style='display:inline-block;float:right;min-width:80px;border-bottom: 1px dotted #000;' contenteditable='true'> &nbsp </span></p>` +
        //     `<div style='clear:both;' contenteditable='false'></div>` +
        //     `<p class='form-footer' contenteditable='false'> Ova prijavnica stoji din 0*75 i ne smije se skuplje prodavati. </p>` +
        //     `<p class='form-sub-footer' contenteditable='false'> Obrazac k. čl. 2 st. 3 naredbe o raspodjeli (racioniranju) životnih namirnica od 27. Siječnja 1941. </p>`;

            // append listed persons to the transcription
            transcriptionTemplate.insertBefore(displayDiv, transcriptionTemplate.querySelector('#list-placeholder'));

            ////
            if(document.querySelector('#show-prirast-ppl').innerHTML != '') {
                transcriptionTemplate.insertBefore(prirastDisplay, transcriptionTemplate.querySelector('#prirast-placeholder'));
            }
            if(document.querySelector('#show-odpad-ppl').innerHTML != '') {
                transcriptionTemplate.insertBefore(odpadDisplay, transcriptionTemplate.querySelector('#odpad-placeholder'));
            }
            // Set Transcription as textcontent in
            tinymce.get('item-page-transcription-text').setContent(transcriptionTemplate.innerHTML);

            if(!document.querySelector('#transcription-selected-languages li')) {
                document.querySelector('#transcription-language-selector div[value="Hrvatski (Croatian)"]').click();
            }

            // Generate Description if Form number is entered and save it
            if(formNumber != '') {
                let itemDescription = `Potrošačka kartica prezimena ${submitterLName}, Registracijski Broj kartice: ${formNumber}.`

                data = {
                    Description: itemDescription
                }

                jQuery.post(home_url + '/wp-content/themes/transcribathon/admin/inc/custom_scripts/send_ajax_api_request.php', {
                    'type': 'POST',
                    'url': TP_API_HOST + '/tp-api/items/' + itemId,
                    'data': data
                },
                // Check success and create confirmation message
                function(response) {
                    console.log(response);

                    updateDataProperty('items', itemId, 'DescriptionLanguage', '12');
                    // show description in description tab
                    document.querySelector('#item-page-description-text').textContent = itemDescription;
                    if(!document.querySelector('#description-language-selector').querySelector('#language-sel-placeholder')) {
                        // show description language
                        let newLang = document.createElement('div');
                        newLang.classList = 'language-select-selected';
                        newLang.textContent = 'Hrvatski';
    
                        document.querySelector('#description-language-selector').appendChild(newLang);
                    }

                    
                });
            }
            document.querySelector('#rc-submit-spinner').style.display = 'none';
            document.querySelector('#tr-tab').parentElement.style.display = 'block';
    })

    // Add Prirast/Odpad tables on button click
    const prirastBtn = document.querySelector('#prirast-btn');
    const odpadBtn = document.querySelector('#odpad-btn');

    // Show 'Prirast' Container and hide 'Prirast' Button
    prirastBtn.addEventListener('click', function() {

        document.querySelector('#prirast-container').style.display = 'block';

        prirastBtn.style.display = 'none';
    });
    // Show 'Odpad' container and hide 'Odpad' button
    odpadBtn.addEventListener('click', function() {

        document.querySelector('#odpad-container').style.display = 'block';

        odpadBtn.style.display = 'none';
    });
    // Add saving to the '+' in Odpad Container
    document.querySelector('#save-odpad-person').addEventListener('click', function() {

        let description = `${document.querySelector('#odpad-rel').value} - ${document.querySelector('#odpad-voc').value} - ${document.querySelector('#odpad-wp').value} - (Odpad) `;
        let firstName = document.querySelector('#odpad-fname').value;
        let lastName = document.querySelector('#odpad-lname').value;

        saveRcPerson(itemId, userId, firstName, lastName, description, 'odpad', 'odpad');

        document.querySelector('#odpad-list-form').reset();
    })
    // Add saving to the '+' in Prirast Container
    document.querySelector('#save-prirast-person').addEventListener('click', function() {
        let description = `${document.querySelector('#prirast-rel').value} - ${document.querySelector('#prirast-voc').value} - ${document.querySelector('#prirast-wp').value} - (Prirast)`;
        let firstName = document.querySelector('#prirast-fname').value;
        let lastName = document.querySelector('#prirast-lname').value;

        saveRcPerson(itemId, userId, firstName, lastName, description, 'prirast', 'prirast');

        document.querySelector('#prirast-list-form').reset();
        })

    
});

