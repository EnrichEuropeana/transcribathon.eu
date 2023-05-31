const home_url = WP_URLs.home_url;

const ready = (callback) => {
    if (document.readyState != "loading") callback();
    else document.addEventListener("DOMContentLoaded", callback);
}
// Replacement for jQuery document.ready; It runs the code after DOM is completely loaded
ready(() => {

    // When the user clicks the button(pen on the image viewer), open the login modal
    jQuery('#default-lock-login').click(function() {
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
    const compTrCollapse = document.querySelector('#transcription-collapse-btn');
    if(compTrCollapse) {
        const htrCont = document.querySelector('#htr-container');
        const mtrCont = document.querySelector('#transcription-container');
        compTrCollapse.addEventListener('click', function() {
            if(htrCont.style.height == '600px') {
                htrCont.style.height = 'unset';
                mtrCont.style.height = 'unset';
                compTrCollapse.textContent = 'Show Less';
            } else {
                htrCont.style.height = '600px';
                mtrCont.style.height = '600px';
                compTrCollapse.textContent = 'Show More';
            }
        })
    }

    if(document.querySelector('#image-json-link')) {
        const imJaLink = document.querySelector('#image-json-link').textContent;
        const imgHeight = document.querySelector('#image-height').textContent;
        const imgWidth = document.querySelector('#image-width').textContent;

	      let imJLink = imJaLink;
	      if (imJaLink.substring(0, 5) != 'https' && imJaLink.substring(0, 4) != 'http') {
	          imJLink = 'https://' + imJaLink;
	      } else if (imJaLink.substring(0, 5) == 'http:') {
	           imJLink = imJaLink.replace('http','https');
	      }


        const viewer = OpenSeadragon({
            id: "openseadragon",
            showRotationControl: true,
            toolbar: "buttons",
            homeButton: "home",
            zoomInButton: "zoom-in",
            zoomOutButton: "zoom-out",
            rotateLeftButton: "rotate-left",
            rotateRightButton: "rotate-right",
            prefixUrl: home_url + "/wp-content/themes/transcribathon/images/osdImages/",
            tileSources: imJLink,
        });
    }
})

document.addEventListener('alpine:init', () => {

    Alpine.data('activeTranscription', (itemId = null) => ({

        source: null, // htr, manual
        requestUri: home_url + '/wp-content/themes/transcribathon/api-request.php/items/' + itemId,
				solrImportWrapper: home_url + '/wp-content/themes/transcribathon/solr-import-request.php',
				solrApiCommand: '/solr/Items/dataimport?command=delta-import&commit=true',

        async init () {

            const result = await (await fetch(this.requestUri)).json();
            this.source = result.data.TranscriptionSource;

            this.$watch('source', async () => {

                const data = {
                    TranscriptionSource: this.source
                };

                const setSource = await (await fetch(this.requestUri, {
                    method: 'PUT',
                    body: JSON.stringify(data),
                })).json();

                if (setSource.success) {
										const solrUpdate =  await (await fetch(this.solrImportWrapper + this.solrApiCommand)).json();
										console.log(solrUpdate);
                    alert('Transcription set to ' + this.source);
                } else {
                    alert('Transcription could not set to');
                }

            });
        }



    }));

});

