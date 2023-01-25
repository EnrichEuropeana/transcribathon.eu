<?php
// get Document data from API
function _TCT_documents_map( $atts ) {   
    //include theme directory for text hovering
    $theme_sets = get_theme_mods();

    // Build Story page content
    $content = "<div id='documentsMap'></div>";
   
	$content .= "<script src='https://api.mapbox.com/mapbox-gl-js/plugins/mapbox-gl-geocoder/v4.4.1/mapbox-gl-geocoder.min.js'></script>
				<link rel='stylesheet' href='https://api.mapbox.com/mapbox-gl-js/plugins/mapbox-gl-geocoder/v4.4.1/mapbox-gl-geocoder.css' type='text/css' />";
	 $content .= "<script>
		jQuery(document).ready(function() {

			mapboxgl.accessToken = 'pk.eyJ1IjoiZmFuZGYiLCJhIjoiY2pucHoybmF6MG5uMDN4cGY5dnk4aW80NSJ9.U8roKG6-JV49VZw5ji6YiQ';
			var map = new mapboxgl.Map({
				container: 'documentsMap',
				style: 'mapbox://styles/fandf/ck4birror0dyh1dlmd25uhp6y',
				center: [13, 46],
				zoom: 2.25
			});
			map.addControl(
				new MapboxGeocoder({
					accessToken: 'pk.eyJ1IjoiZmFuZGYiLCJhIjoiY2pucHoybmF6MG5uMDN4cGY5dnk4aW80NSJ9.U8roKG6-JV49VZw5ji6YiQ',
					mapboxgl: mapboxgl,
					marker: false
				})
			);
			map.addControl(new mapboxgl.NavigationControl());

			map.on('load', function() {
				console.log('map loaded');
				jQuery.post(home_url + '/wp-content/themes/transcribathon/admin/inc/custom_scripts/send_ajax_api_request.php', {
					'type': 'GET',
					'url': TP_API_HOST + '/tp-api/places/story'
					},
					function(response) {
						var places = JSON.parse(JSON.parse(response).content);
						var geojson = {type: 'FeatureCollection', features: [] }
						var t0 = performance.now();
						for(var i = 0; i < places.length; i++) {
							geojson.features.push({
								type: 'Feature',
								properties: {
									id: places[i].PlaceId,
									name: places[i].Name,
									comment: places[i].Comment,
									itemId: places[i].ItemId,
									storyId: places[i].StoryId,
									itemTitle: places[i].ItemTitle,
									lng: places[i].Longitude,
									lat: places[i].Latitude
								},
								geometry: {
									type: 'Point',
									coordinates: [places[i].Longitude, places[i].Latitude]
									}
							})
						}
						map.loadImage('".home_url()."/wp-content/themes/transcribathon/images/map/locationicon40.png', function(err, icon) {
							if(err) throw err;
							map.addImage('icon', icon);
							map.loadImage('".home_url()."/wp-content/themes/transcribathon/images/map/selectedlocationicon40.png', function(err, storyIcon) {
							if(err) throw err;
							map.addImage('storyIcon', storyIcon);

							map.addSource('all-places', {
								type: 'geojson',
								data: geojson,
								cluster: true,
								clusterMaxZoom: 14,
							    clusterRadius: 65
							});
							map.addLayer({
								id: 'clusters',
								type: 'circle',
								source: 'all-places',
								filter: ['has', 'point_count'],
								paint: {
									'circle-color': [
										'step',
										['get', 'point_count'],
										'#56a6c4',
										12,
										'#7bbbd2',
										90,
										'#9bcadb',
										220,
										'#b7dcea'
									],
									'circle-radius': [
										'step',
										['get', 'point_count'],
										20,
										12,
										25,
										90,
										30,
										220,
										36
									]
								}
							})
							map.addLayer({
								id: 'cluster-count',
								type: 'symbol',
								source: 'all-places',
								filter: ['has', 'point_count'],
								layout: {
									'text-field': '{point_count_abbreviated}',
									'text-font': ['DIN Offc Pro Medium', 'Arial Unicode MS Bold'],
									'text-size': 12
								}
							});
							map.addLayer({
								id: 'unclustered-point',
								type: 'symbol',
								source: 'all-places',
								filter: ['!', ['has', 'point_count']],
								layout: {
									'icon-image': 'icon',
									'icon-size': 1
								}
							});
										
							map.on('click', 'unclustered-point', function (e) {
								console.log(e);
								var features = map.queryRenderedFeatures(e.point, { layers: ['unclustered-point'] });
								console.log(features);
								var sameCoordinates = [];
								for(var i = 0; i < places.length; i++) {
									if(places[i].Longitude === features[0].properties.lng && places[i].Latitude === features[0].properties.lat) {
										sameCoordinates.push(places[i]);
									}
								}
								console.log(sameCoordinates);
								if(sameCoordinates.length > 1) {
									var html = '<div class=\"popupWrapper\">'
										for(var i = 0; i < sameCoordinates.length; i++) {		
											console.log(sameCoordinates[i]);
											if (sameCoordinates[i].ItemId == 0) {
												html += '<div class=\"story-location-header\">Story Location</div>';
											}
											html += '<div class=\"title\">';
												html += '<a href=\"".home_url()."' + (sameCoordinates[i].ItemId == 0 ? '/documents/story?story=' + sameCoordinates[i].StoryId : '/documents/story/item/?item=' + sameCoordinates[i].ItemId) + '\">' + sameCoordinates[i].ItemTitle + '</a>';
											html += '</div>';
											html += '<div class=\"name\">' + (sameCoordinates[i].Name || \"\") + '</div>';
											html += '<div class=\"border\"></div>';
										}
									html += '</div>';
									var popup = new mapboxgl.Popup({offset: 25, closeButton: false})
										.setLngLat(e.lngLat)
										.setHTML(html)
										.addTo(map);
								} else {
									var name = features[0].properties.name;
									var description = features[0].properties.comment;
									var itemId = features[0].properties.itemId;
									var storyId = features[0].properties.storyId;
									var title = features[0].properties.itemTitle;
									var popup = new mapboxgl.Popup({offset: 25, closeButton: false})
										.setLngLat(e.lngLat)
										.setHTML('<div class=\"popupWrapper\">' 
										    + (itemId == 0 ? 
										        '<div class=\"story-location-header\">Story Location</div>' 
										    	: 
										    	''
										    ) 
										    + '<div class=\"title\">'
										    	+ '<a href=\"".home_url()."' + (itemId == 0 ? '/documents/story?story=' + storyId : '/documents/story/item/?item=' + itemId) + '\">' + title + '</a>'
										    +'</div>'
										    + '<div class=\"name\">' + (name || \"\") + '</div>'
										+ '</div>')
									.addTo(map);
								}
							});

							map.on('click', 'clusters', function (e) {
								var features = map.queryRenderedFeatures(e.point, { layers: ['clusters'] });
								var clusterId = features[0].properties.cluster_id;
								 map.getSource('all-places').getClusterExpansionZoom(clusterId, function (err, zoom) {
									if (err)
										return;
									map.easeTo({
										center: features[0].geometry.coordinates,
										zoom: zoom
									});
								});
							});
											
							map.on('mouseenter', 'clusters', function () {
								map.getCanvas().style.cursor = 'pointer';
							});
							map.on('mouseleave', 'clusters', function () {
								map.getCanvas().style.cursor = '';
							});
						});
					});
                });
            });
							  
		});
    </script>";

    return $content;
}
add_shortcode( 'documents_map', '_TCT_documents_map' );
?>