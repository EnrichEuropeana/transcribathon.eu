const home_url = WP_URLs.home_url;

const ready = (callback) => {
    if (document.readyState != "loading") callback();
    else document.addEventListener("DOMContentLoaded", callback);
}
// Replacement for jQuery document.ready; It runs the code after DOM is completely loaded
ready(() => {
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

})

document.addEventListener('alpine:init', () => {

    Alpine.data('activeTranscription', (itemId = null) => ({

        source: null, // htr, manual
        requestUri: home_url + '/wp-content/themes/transcribathon/api-request.php/items/' + itemId,

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
                    alert('Transcription set to ' + this.source);
                } else {
                    alert('Transcription could not set to');
                }

            });
        }



    }));

});

