var home_url = WP_URLs.home_url;

var ready = (callback) => {
    if (document.readyState != "loading") callback();
    else document.addEventListener("DOMContentLoaded", callback);
}
// Replacement for jQuery document.ready; It runs the code after DOM is completely loaded
ready(() => {
    let imJaLink = document.querySelector('#image-json-link').textContent;
	const imgHeight = document.querySelector('#image-height').textContent;
    const imgWidth = document.querySelector('#image-width').textContent;
	const heightRatio = imgHeight / imgWidth;
	
	if(imJaLink.substring(0, 5) != 'https' && imJaLink.substring(0, 4) != 'http') {
		var imJLink = 'https://' + imJaLink;
	} else if(imJaLink.substring(0, 5) == 'http:') {
		var imJLink = imJaLink.replace('http','https');
	} else {
		var imJLink = imJaLink;
	}

    var viewer = OpenSeadragon({
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