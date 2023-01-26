var home_url = WP_URLs.home_url;
var network_home_url = WP_URLs.network_home_url;
var map, marker;

jQuery(document).ready(function() {
  jQuery(".search-page-mobile-facets").click(function () {
    jQuery(this).siblings('.search-content-left').animate({ "left": -35 },    "slow");
  });
  jQuery(".facet-close-button").click(function () {
    jQuery(this).parents('.search-content-left').animate({ "left": -500 }, "slow");
  });
});

function installEventListeners() {

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

    /*if the user clicks anywhere outside the select box,
    then close all select boxes:*/
    document.addEventListener("click", closeAllSelect, false);

  // Something something for comments that are missing from Item page
  // jQuery('.notes-questions').keyup(function() {
  // var block_data = jQuery(this).val();
  //         if(block_data.length==0){
  //         jQuery('.notes-questions-submit').css('display','none');
  //         }else{
  //     jQuery('.notes-questions-submit').css('display','block');
  //     }
  // });


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
// Calls script to draw linechart on the profile page
function getTCTlinePersonalChart(what,start,ende,holder,uid){
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
// function switchItem(itemId, userId, statusColor, progressSize, itemOrder, itemAmount, firstItem, lastItem) {
//   jQuery('.full-spinner-container').css('display', 'block');
//   loadPlaceData(itemId, userId);
//   loadPersonData(itemId, userId);
//   loadKeywordData(itemId, userId);
//   loadLinkData(itemId, userId);
//   jQuery('#location-input-section .item-page-save-button').attr('onclick', "saveItemLocation(" + itemId + ", " + userId + ", " + statusColor + ", " + progressSize + ")");
//   jQuery('#save-personinfo-button').attr('onclick', "savePerson(" + itemId + ", " + userId + ", " + statusColor + ", " + progressSize + ")");
//   jQuery('#keyword-save-button').attr('onclick', "saveKeyword(" + itemId + ", " + userId + ", " + statusColor + ", " + progressSize + ")");
//   jQuery('#link-save-button').attr('onclick', "saveLink(" + itemId + ", " + userId + ", " + statusColor + ", " + progressSize + ")");
//   jQuery('#description-update-button').attr('onclick', "updateItemDescription(" + itemId + ", " + userId + ", " + statusColor + ", " + progressSize + ")");
//   jQuery('#transcription-update-button').attr('onclick', "updateItemTranscription(" + itemId + ", " + userId + ", " + statusColor + ", " + progressSize + ")");

//   jQuery.post(home_url + '/wp-content/themes/transcribathon/admin/inc/custom_scripts/send_ajax_api_request.php', {
//     'type': 'GET',
//     'url': TP_API_HOST + '/tp-api/items/' + itemId
//   },
//     function(response) {
//       var response = JSON.parse(response);
//       var content = JSON.parse(response.content);
//       var transcriptions = content['Transcriptions'];
//       // swap the image in the iiif viewer
//       imageData = JSON.parse(JSON.parse(response.content)['ImageLink']);
//       imageLink = imageData['service']['@id'];
//       if (imageData['service']['@id'].substr(0, 4) == "http") {
//         imageLink = imageData['service']['@id'];
//       }
//       else {
//         imageLink = "http://" + imageData['service']['@id'];
//       }
//       imageHeight = imageData['height'];
//       imageWidth = imageData['width'];

//       var newTileSource = {
// 				"@context": "http://iiif.io/api/image/2/context.json",
// 				"@id": imageLink,
// 				"height": imageHeight,
// 				"width": imageWidth,
// 				"profile": [
// 					"http://iiif.io/api/image/2/level2.json"
// 				],
// 				"protocol": "http://iiif.io/api/image"
// 			}
//       tct_viewer.getOsdViewer().open(newTileSource);
//       tct_viewer.getOsdViewerFS().open(newTileSource);
//       // update the map (deleting old marker, drawing new ones and panning the map to show the new markers
//       jQuery('.marker').remove();
//       var bounds = new mapboxgl.LngLatBounds();

//       content['Places'].forEach(function(marker) {
//         var el = document.createElement('div');
//         el.className = 'marker savedMarker fas fa-map-marker-alt';
//         var popup = new mapboxgl.Popup({offset: 0, closeButton: false})
//           .setHTML('<div class=\"popupWrapper\"><div class=\"name\">' + marker.Name + '</div><div class=\"comment\">' + marker.Comment + '</div></div>');
//         bounds.extend([marker.Longitude, marker.Latitude]);
//         new mapboxgl.Marker({element: el, anchor: 'bottom'})
//           .setLngLat([marker.Longitude, marker.Latitude])
//           .setPopup(popup)
//           .addTo(map);
//       });

//       if(bounds.isEmpty()) {
// 	map.fitBounds([
//           [51.844, 64.837],
//           [-19.844, 25.877]
//         ]);
//       } else {
//         map.fitBounds(bounds, {padding: {top: 50, bottom:20, left: 20, right: 20}});
//       }
//       for (var i = 0; i < transcriptions.length; i++) {
//         if (transcriptions[i]['CurrentVersion'] == 1) {
//           var currentTranscription =  transcriptions[i];
//           break;
//         }
//       }
//       for (var i = 0; i < transcriptions.length; i++) {
//         if (transcriptions[i]['CurrentVersion'] == 1) {
//           jQuery('#item-page-transcription-text').html(transcriptions[i]['TextNoTags']);
//           if (transcriptions[i]['NoText'] == "1") {
//             jQuery('#no-text-checkbox').prop('checked', true);
//           }
//           else {
//             jQuery('#no-text-checkbox').prop('checked', false);
//           }
//           jQuery('#transcription-selected-languages').html("");
//           for (var j = 0; j < transcriptions[i]['Languages'].length; j++) {
//             jQuery('#transcription-selected-languages').append(
//               '<ul>' +
//                 '<li class="theme-colored-data-box">' +
//                   transcriptions[i]['Languages'][j]['Name'] + ' (' + transcriptions[i]['Languages'][j]['NameEnglish'] + ')' +
//                   '<i class="far fa-times" onclick="removeTranscriptionLanguage(' + transcriptions[i]['Languages'][j]['LanguageId'] + ', this)"></i>' +
//                 '</li>' +
//               '</ul>'
//               )
//               jQuery("#transcription-language-selector option[value='" + transcriptions[i]['Languages'][j]['LanguageId'] + "'").prop("disabled", true);
//           }
//         }
//         else {
//             jQuery.post(home_url + '/wp-content/themes/transcribathon/admin/inc/custom_scripts/get_userinfo.php', {
//               'userId': userId,
//               'index': i,
//             },
//               function(response) {
//                 var transcriptionHistory = "";
//                 var response = JSON.parse(response);
//                 var user = response['user']['data'];
//                 var index = response['index'];
//                 transcriptionHistory +='<div class="transcription-toggle" data-toggle="collapse" data-target="#transcription-' + index + '">';
//                     transcriptionHistory +='<i class="fas fa-calendar-day" style= "margin-right: 6px;"></i>';
//                     transcriptionHistory +='<span class="day-n-time">';
//                         transcriptionHistory += transcriptions[index]["Timestamp"];
//                     transcriptionHistory +='</span>';
//                     transcriptionHistory +='<i class="fas fa-user-alt" style="margin: 0 6px;"></i>';
//                     transcriptionHistory +='<span class="day-n-time">';
//                         transcriptionHistory +='<a target=\"_blank\" href="' + network_home_url + 'profile/' + user['user_nicename'] + '">';
//                             transcriptionHistory += user['user_nicename'];
//                         transcriptionHistory += '</a>';
//                     transcriptionHistory += '</span>';
//                     transcriptionHistory += '<i class="fas fa-angle-down" style= "float:right;"></i>';
//                 transcriptionHistory += '</div>';

//                 transcriptionHistory += '<div id="transcription-' + index + '" class="collapse transcription-history-collapse-content">';
//                     transcriptionHistory += '<p>';
//                         transcriptionHistory += transcriptions[index]['TextNoTags'];
//                     transcriptionHistory += '</p>';
//                     transcriptionHistory += "<input class='transcription-comparison-button theme-color-background' type='button'" +
//                                                 "onClick='compareTranscription(\"" + transcriptions[index]['TextNoTags'].replace(/'/g, "&#039;").replace(/"/g, "&quot;") + "\", " +
//                                                 "\"" + currentTranscription['TextNoTags'].replace(/'/g, "&#039;").replace(/"/g, "&quot;") + "\"," + index + ")' " +
//                                                 "value='Compare to current transcription'>";
//                     transcriptionHistory += '<div id="transcription-comparison-output-' + index + '" class="transcription-comparison-output"></div>';
//                 transcriptionHistory += '</div>';
//                 jQuery('#transcription-history').append(transcriptionHistory);
//               }
//             );
//         }
//       }
//       var properties = content['Properties'];
//       jQuery('.category-checkbox').each(function() {
//         jQuery(this).prop('checked', false);
//       })
//       for (var i = 0; i < properties.length; i++) {
//         if (properties[i]['PropertyType'] == 'Category') {
//           jQuery('#type-' + properties[i]['PropertyValue'] + '-checkbox').prop('checked', true)
//         }
//       }
//       jQuery('#item-page-description-text').val("");
//       jQuery('#item-page-description-text').val(content['Description']);
//       if (content['DescriptionLanguage'] != "0") {
//         jQuery('#description-language-selector select').val('\"' + content['DescriptionLanguage'] + '\"');
//         jQuery('#description-language-custom-selector').html(jQuery('#description-language-selector select option[value="' + content['DescriptionLanguage'] + '"').text());
//       }
//       else {
//         jQuery('#description-language-selector select').val(null);
//         jQuery('#description-language-custom-selector').html('Select Language');
//       }
//       var title = jQuery('#additional-information-area .item-page-section-headline').html();
//       var titleWords = title.split(" ");
//       titleWords[titleWords.length - 1] = itemOrder;
//       title = titleWords.join(" ");
//       jQuery('#additional-information-area .item-page-section-headline').html(title);

//       if (itemOrder > 1) {
//         jQuery('#prev-item-main-view, #prev-item-full-view').html(
//           '<button id="viewer-previous-item" onclick="switchItem(' + (itemId + -1) + ', ' + userId + ', \'' + statusColor + '\', ' + progressSize + ', ' + (itemOrder - 1) + ', ' + itemAmount + ')" ' +
//               'type="button" style="cursor: pointer;">' +
//             '<a><i class="fas fa-chevron-left" style="font-size: 20px; color: black;"></i></a>' +
//           '</button>'
//         )
//         jQuery('.item-navigation-prev').html(
//           '<li><a title="first" href="' + home_url + '/documents/story/item?item=' + firstItem  + '"><i class="fal fa-angle-double-left"></i></a></li>' +
//           '<li class="rgt"><a title="previous" href="' + home_url + '/documents/story/item?item=' + (itemId - 1) + '"><i class="fal fa-angle-left"></i></a></li>'
//         )
//       }
//       else {
//         jQuery('#prev-item-main-view, #prev-item-full-view').html("");
//         jQuery('.item-navigation-prev').html("");
//       }
//       if (itemOrder < itemAmount) {
//         jQuery('#next-item-main-view, #next-item-full-view').html(
//           '<button id="viewer-next-item" onclick="switchItem(' + (itemId + 1) + ', ' + userId + ', \'' + statusColor + '\', ' + progressSize + ', ' + (itemOrder + 1) + ', ' + itemAmount + ')" ' +
//               'type="button" style="cursor: pointer;">' +
//             '<a><i class="fas fa-chevron-right" style="font-size: 20px; color: black;"></i></a>' +
//           '</button>'
//         )
//         jQuery('.item-navigation-next').html(
//           '<li class="rgt"><a title="next" href="' + home_url + '/documents/story/item?item=' + (itemId + 1) + '"><i class="fal fa-angle-right"></i></a></li>' +
//           '<li class="rgt"><a title="last" href="' + home_url + '/documents/story/item?item=' + lastItem + '"><i class="fal fa-angle-double-right"></i></a></li>'
//         )
//       }
//       else {
//         jQuery('#next-item-main-view, #next-item-full-view').html("");
//         jQuery('.item-navigation-next').html("");
//       }
//       jQuery('.slider-current-item-pointer').remove();
//     //   jQuery('[data-slick-index=' + (itemOrder - 1) + ']').append("<div class='slider-current-item-pointer'></div>");
//       jQuery('#transcription-status-indicator').css('color', content['TranscriptionStatusColorCode']);
//       jQuery('#transcription-status-indicator').css('background-color', content['TranscriptionStatusColorCode']);
//       jQuery('#description-status-indicator').css('color', content['DescriptionStatusColorCode']);
//       jQuery('#description-status-indicator').css('background-color', content['DescriptionStatusColorCode']);
//       jQuery('#location-status-indicator').css('color', content['LocationStatusColorCode']);
//       jQuery('#location-status-indicator').css('background-color', content['LocationStatusColorCode']);
//       jQuery('#tagging-status-indicator').css('color', content['TaggingStatusColorCode']);
//       jQuery('#tagging-status-indicator').css('background-color', content['TaggingStatusColorCode']);

//       jQuery.post(home_url + '/wp-content/themes/transcribathon/admin/inc/custom_scripts/send_ajax_api_request.php', {
//         'type': 'GET',
//         'url': TP_API_HOST + '/tp-api/completionStatus',
//       },
//         function(statusResponse) {
//           var statusResponse = JSON.parse(statusResponse);
//           var statusContent = JSON.parse(statusResponse.content);

//           var progressData = [
//             content['TranscriptionStatusName'],
//             content['DescriptionStatusName'],
//             content['LocationStatusName'],
//             content['TaggingStatusName'],
//           ];
//           var progressCount = [];
//           progressCount['Not Started'] = 0;
//           progressCount['Edit'] = 0;
//           progressCount['Review'] = 0;
//           progressCount['Completed'] = 0;
//           for(var i = 0; i < progressData.length; i++) {
//               progressCount[progressData[i]] += 1;
//           }
//           for(var i = 0; i < statusContent.length; i++) {
//             var percentage = (progressCount[statusContent[i]['Name']] / progressData.length) * 100;
//             jQuery('#progress-bar-overlay-' + statusContent[i]['Name'].replace(' ', '-') + '-section').html(percentage + '%');
//             jQuery('#progress-bar-' + statusContent[i]['Name'].replace(' ', '-') + '-section').html(percentage + '%');
//             jQuery('#progress-bar-' + statusContent[i]['Name'].replace(' ', '-') + '-section').css('width', percentage + '%');
//             jQuery('#progress-doughnut-overlay-' + statusContent[i]['Name'].replace(' ', '-') + '-section').html(percentage + '%');
//             statusDoughnutChart.data.datasets[0].data[i] = progressCount[statusContent[i]['Name']];
//           }
//           statusDoughnutChart.update();
//         }
//       );
//       jQuery('.full-spinner-container').css('display', 'none');
//     }
//   );
// }
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
// function updateSolr() {
//   jQuery.post(home_url + '/wp-content/themes/transcribathon/admin/inc/custom_scripts/send_ajax_api_request.php', {
//     'type': 'POST',
//     'url': TP_API_HOST + '/tp-api/stories/update',
//   },
//     function(statusResponse) {
//     });
// }

//// Automatic Enrichments OLD!!!!
// function getEnrichments(storyId, itemId, savedEnrichmentIds) {
//   jQuery.post(home_url + '/wp-content/themes/transcribathon/admin/inc/custom_scripts/send_ajax_api_request.php', {
//       'type': 'POST',
//       'url': 'https://dsi-demo.ait.ac.at/enrichment-web/enrichment/annotation/' + storyId + '/' + itemId + '?wskey=apidemo&crosschecked=false'
//   },
//   function(response) {
//     var response = JSON.parse(response);
//     var content = JSON.parse(response.content);
//     savedEnrichmentIds = savedEnrichmentIds.split(",");

//     content.items.forEach((item, i) => {
//       var name = item.body.prefLabel.en;
//       var type = item.body.type;
//       var wikiData = item.body.id.includes("wikidata") ? item.body.id : "";
//       var id = item.id;
//       var itemHtml = '<tr id="received-enrichment-' + (i + savedEnrichmentIds.length - 1) + '">' +
//                       '<td>' + name + '</td>' +
//                       '<td>' + type + '</td>' +
//                       '<td>' + '<a target="_blank" href="'+ wikiData +'">' + wikiData.split("/").pop() + '</a>' + '</td>' +
//                       '<td>' +
//                           '<label class="switch">' +
//                               '<input type="checkbox" onChange="saveEnrichment(\'' + name + '\', \'' + type + '\', \'' + wikiData + '\', ' + itemId + ', \'' + id + '\', ' + (i + savedEnrichmentIds.length - 1) + ')">' +
//                               '<span class="slider round"></span>' +
//                           '</label>' +
//                       '</td>' +
//                     '</tr>';
//       if (!savedEnrichmentIds.find(enrichment => enrichment == item.id)) {
//         jQuery('#automatic-enrichments-list').append(itemHtml);
//       }
//     })
//   });
// }
// function saveEnrichment(name, type, wikiData, itemId, id, index) {
//   if (jQuery('#received-enrichment-' + index + ' input').attr('checked') == "checked") {
//     data = {
//       Name: name,
//       Type: type,
//       WikiData: wikiData,
//       ItemId: itemId,
//       ExternalId: id
//     }
//     var dataString= JSON.stringify(data);
//     jQuery.post(home_url + '/wp-content/themes/transcribathon/admin/inc/custom_scripts/send_ajax_api_request.php', {
//       'type': 'POST',
//       'data': data,
//       'url': TP_API_HOST + '/tp-api/automatedEnrichments'
//     },
//     function(response) {
//       var response = JSON.parse(response);
//     });
//   }
//   else {
//     data = {
//       Name: name,
//       Type: type,
//       WikiData: wikiData,
//       ItemId: itemId,
//       ExternalId: id
//     }

//     var dataString= JSON.stringify(data);
//     jQuery.post(home_url + '/wp-content/themes/transcribathon/admin/inc/custom_scripts/send_ajax_api_request.php', {
//       'type': 'POST',
//       'data': data,
//       'url': TP_API_HOST + '/tp-api/automatedEnrichments/delete'
//     },
//     function(response) {
//     });
//   }
// }
// function initializeMap() {
//   //reinitialising map
//   var url_string = window.location.href;
//   var url = new URL(url_string);
//   var itemId = url.searchParams.get('item');
//   var coordinates = jQuery('.location-input-coordinates-container.location-input-container > input ')[0];

//   mapboxgl.accessToken = 'pk.eyJ1IjoiZmFuZGYiLCJhIjoiY2pucHoybmF6MG5uMDN4cGY5dnk4aW80NSJ9.U8roKG6-JV49VZw5ji6YiQ';

//   jQuery('#addMapMarker').click(function() {
//     var el = document.createElement('div');
//     el.className = 'marker';

//     var icon = document.createElement('i');
//     icon .className = 'fas fa-map-marker-plus';
//     if(typeof marker !== 'undefined') {
//       marker.remove();
//     }
//     marker = new mapboxgl.Marker({element: el, draggable: true})
//       .setLngLat(map.getCenter())
//       .addTo(map);

//     var lngLat = marker.getLngLat();
//     coordinates.value = lngLat.lat + ', ' + lngLat.lng;
//     marker.on('dragend', onDragEnd);
//   });
//   if (jQuery('#full-view-map').length) {
//       jQuery('.map-placeholder').css('display', 'none');
//       map = new mapboxgl.Map({
//         container: 'full-view-map',
//         style: 'mapbox://styles/fandf/ck4birror0dyh1dlmd25uhp6y',
//         center: [16, 49],
//         zoom: 2.25,
//         scrollZoom: false
//       });
//       map.addControl(new mapboxgl.NavigationControl());

//       var bounds = new mapboxgl.LngLatBounds();

//       jQuery.post(
//         home_url
//         + '/wp-content/themes/transcribathon/admin/inc/custom_scripts/send_ajax_api_request.php',
//         {
//           type: 'GET',
//           url: TP_API_HOST + '/tp-api/places/story/' + itemId
//         },
//         function(response) {
//           const content  = JSON.parse(response).content;
//           const places  = JSON.parse(content);
//           places.filter(place => place.Latitude != 0 || place.Longitude != 0).forEach(function(marker) {
//             var el = document.createElement('div');
//             el.className = 'marker savedMarker ' + (marker.ItemId == 0 ? "storyMarker" : "");
//             var popup = new mapboxgl.Popup({offset: 35, closeButton: false})
//               .setHTML('<div class=\"popupWrapper\">' + (marker.ItemId == 0 ? '<div class=\"story-location-header\">Story Location</div>' : '') + '<div class=\"name\">' + (marker.Name || marker.ItemTitle || "") + '</div><div class=\"comment\">' + (marker.Comment || "") + '</div></div>');
//             bounds.extend([marker.Longitude, marker.Latitude]);
//             new mapboxgl.Marker({element: el, anchor: 'bottom'})
//               .setLngLat([marker.Longitude, marker.Latitude])
//               .setPopup(popup)
//               .addTo(map);
//           });
//           if(places && places.length === 1) {
//             map.flyTo({
//               center: [
//                 bounds._ne.lng,
//                 bounds._ne.lat
//               ],
//               zoom: 5,
//               essential: true
//             });
//           } else {
//             map.fitBounds(bounds, {padding: {top: 100, bottom:100, left: 100, right: 100}});
//           }
//         }
//       );
//       // fetch(home_url + '/tp-api/places/story/' + itemId)
//       //   .then(function(response) {
//       //     return response.json();
//       //   })
//       //   .then(function(places) {
//       //     places.filter(place => place.Latitude != 0 || place.Longitude != 0).forEach(function(marker) {
//       //       var el = document.createElement('div');
//       //       el.className = 'marker savedMarker ' + (marker.ItemId == 0 ? "storyMarker" : "");
//       //       var popup = new mapboxgl.Popup({offset: 35, closeButton: false})
//       //       .setHTML('<div class=\"popupWrapper\">' + (marker.ItemId == 0 ? '<div class=\"story-location-header\">Story Location</div>' : '') + '<div class=\"name\">' + (marker.Name || marker.ItemTitle || "") + '</div><div class=\"comment\">' + (marker.Comment || "") + '</div></div>');
//       //       bounds.extend([marker.Longitude, marker.Latitude]);
//       //       new mapboxgl.Marker({element: el, anchor: 'bottom'})
//       //         .setLngLat([marker.Longitude, marker.Latitude])
//       //       .setPopup(popup)
//       //         .addTo(map);
//       //     });
//       //   if(places && places.length === 1) {
//       //     map.flyTo({
//       //       center: [
//       //       bounds._ne.lng,
//       //       bounds._ne.lat
//       //       ],
//       //       zoom: 5,
//       //       essential: true
//       //     });
//       //   } else {
//       //     map.fitBounds(bounds, {padding: {top: 100, bottom:100, left: 100, right: 100}});
//       //   }
//       // });
//     var geocoder = new MapboxGeocoder({
//       accessToken: mapboxgl.accessToken,
//       mapboxgl: mapboxgl,
//             marker: false,
//       language: 'en-EN'
//     });

//     geocoder.on('result', function(res) {
//       jQuery('#location-input-section').addClass('show');
//       jQuery('#location-input-geonames-search-container > input').val(res.result['text_en-EN'] + '; ' + res.result.properties.wikidata);
//       var el = document.createElement('div');
//       el.className = 'marker';

//       var icon = document.createElement('div');
//       icon .className = 'marker newMarker';
//       if(typeof marker !== 'undefined') {
//         marker.remove();
//       }
//       marker = new mapboxgl.Marker({element: el, draggable: true, element: icon})
//         .setLngLat(res.result.geometry.coordinates)
//         .addTo(map);
//         var lngLat = marker.getLngLat();
//       coordinates.value = lngLat.lat + ', ' + lngLat.lng;
//       marker.on('dragend', onDragEnd);
//     })

//       //map.addControl(geocoder, 'bottom-left');
//     jQuery('#location-input-section .location-input-name-container input').remove()
//     jQuery('#location-input-section .location-input-name-container.location-input-container')[0].appendChild(geocoder.onAdd(map));
//     var marker;
//     jQuery('#addMarker').click(function() {
//       var el = document.createElement('div');
//       el.className = 'marker';
//       // make a marker for each feature and add to the map
//       marker = new mapboxgl.Marker({element: el, draggable: true})
//         .setLngLat(map.getCenter())
//         .addTo(map);
//       marker.on('dragend', onDragEnd);
//     });
//     function onDragEnd() {
//       var lngLat = marker.getLngLat();
//       coordinates.value = lngLat.lat + ', ' + lngLat.lng;
//     }
//   }
//   jQuery('#location-input-section > div:nth-child(4) > button:nth-child(1)').click(function() {
//   marker.setDraggable(false);
//   marker.getElement().classList.remove('fa-map-marker-plus');
//   //marker.getElement().classList.add('fa-map-marker-alt');
//   marker.getElement().classList.add('savedMarker');
//   // set the popup
//   var name = jQuery('#location-input-section > div:nth-child(1) > div:nth-child(1) > input:nth-child(3)').val();
//   var desc = jQuery('#location-input-section > div:nth-child(2) > textarea:nth-child(3)').val();
//   var popup = new mapboxgl.Popup({offset: 25, closeButton: false})
//           .setHTML('<div class=\"popupWrapper\"><div class=\"name\">' + name + '</div><div class=\"comment\">' + desc + '</div></div>');
//   marker.setPopup(popup);
//   // allow multiple markers to be added
//   marker = undefined;
//   });
// }
/*
Updating of Javascript
Function to toggle between partial and full paragraph,
used on Story page for description
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

    /// Search page fix
        // Not sure where is this on site, needs more testing
        const singleResultDescriptions = document.querySelectorAll('.search-page-single-result-description');

        if(singleResultDescriptions) {
            for(const singleResult of singleResultDescriptions) {
                if(singleResult.scrollHeight > singleResult.clientHeight) {
                    singleResult.siblings.style.display = '-webkit-inline-box';
                }
            }
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


    // Metadata collapse button on StoryPage
    const metaBtn = document.querySelector('#meta-collapse-btn');
    const metaContainer = document.querySelector('.js-container');
    const metaStickers = document.querySelectorAll('.meta-sticker');
    if(metaBtn){
        metaBtn.addEventListener('click', function() {
            if(metaContainer.style.height <= '110px') {
                metaContainer.style.height = 'unset';
                document.querySelector('#meta-show-more').style.display = 'none';
            } else {
                metaContainer.style.height = '110px';
                document.querySelector('#meta-show-more').style.display = 'block';
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
            if (!itemLogContainer) { return; }
            if(itemLogContainer.style.display != 'none' || lockWarning.style.display != 'none') {
                itemLogContainer.style.display = 'none';
                lockWarning.style.display = 'none';
            } else if(fullScreen.style.display != 'none') {
                switchItemPageView();
            }
        }
    });


    // Item page, enrichments, language-selector
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
        // const prevBtn = document.querySelector('.prev-set');
        // const nextBtn = document.querySelector('.next-set');
        // Buttons to move by step size
        const nextSet = document.querySelector('.next-slide');
        const prevSet = document.querySelector('.prev-slide');
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
            // prevBtn.style.display = 'none';
            // nextBtn.style.display = 'none';
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
                    // console.log(slideN);
                    slideN += step;
                } else {
                    leftSpanNumb.textContent = imgSticker.length - step + 1;
                    rightSpanNumb.textContent = imgSticker.length;
                    slideN = imgSticker.length - 1;

                    for(let y = imgSticker.length-1; y > imgSticker.length - (step+1); y--) {
                        if(imgSticker[y-step]){
                            imgSticker[y-step].style.display = 'none';
                        imgSticker[y].style.display = 'inline-block';
                    }
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
                        if(imgSticker[z+step]){
                            imgSticker[z+step].style.display = 'none';
                            imgSticker[z].style.display = 'inline-block';
                        }
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

            // // Sliding images by 1 slide -- right (+)
            // nextBtn.addEventListener('click', function() {
            //     imgSticker[slideN].style.display = 'inline-block';
            //     imgSticker[slideN - step].style.display = 'none';
            //     leftSpanNumb.textContent = slideN + 2 - step;
            //     rightSpanNumb.textContent = slideN + 1;

            //     slideN += 1;
            //     if(slideN >= imgSticker.length -1) {
            //         slideN = imgSticker.length - 1;
            //     }
            // })
            // // Sliding images by 1 slide -- left (-)
            // prevBtn.addEventListener('click', function() {
            //     if(slideN >= imgSticker.length-1 && imgSticker[slideN].style.display === 'inline-block'){
            //         imgSticker[slideN].style.display = 'none';
            //         imgSticker[slideN-step].style.display = 'inline-block';
            //         slideN = imgSticker.length-1;
            //     } else {
            //         slideN -= 1;
            //         if(slideN < step) {
            //             slideN = step;
            //         }
            //         imgSticker[slideN].style.display = 'none';
            //         imgSticker[slideN-step].style.display = 'inline-block';
            //     }
            //     leftSpanNumb.textContent = slideN - step +1;
            //     rightSpanNumb.textContent = slideN;
            // })
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


});

