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

function removeContactQuestion() {
  jQuery('.contact-question').html("");
}

/*
Updating of Javascript
Function to toggle between partial and full paragraph,
used on Story page for description
 */
// Declaration of replacement for jQuery document.ready, it runs the check if DOM has loaded until it loads
var ready = (callback) => {
    if (document.readyState != "loading") callback();
    else document.addEventListener("DOMContentLoaded", callback);
}
// Replacement for jQuery document.ready; It runs the code after DOM is completely loaded
ready(() => {

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
    // Cover up at the end of Item Metadata
    const coverUp = document.querySelector('.cover-up');
    if(coverUp) {
        coverUp.addEventListener('click', function() {
            itemMetaBtn.click();
            coverUp.style.display = 'none';
        })
    }
});


