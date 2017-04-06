if ( ! window.jcf_googlemaps ) window.jcf_googlemaps  = [];

/**
 * Init field with map
 *
 * @param integer i  Map index key
 */
function jcf_googlemaps_init_field(i) {
  var jcf_googlemap = window.jcf_googlemaps[i];

  var zoom = 15; var marker = true;
  if ( 0 == jcf_googlemap.lat || 0 == jcf_googlemap.lng ) {
    jcf_googlemap.lat = 5.397;
    jcf_googlemap.lng = 5.644;
    zoom = 3;
    marker = false;
  }

  var map = new google.maps.Map(document.getElementById( jcf_googlemap.map_id ), {
    zoom: zoom,
    center: {lat: jcf_googlemap.lat, lng: jcf_googlemap.lng},
  });

  document.getElementById( jcf_googlemap.map_id ).mapIndex = i;

  if ( marker ) {
    var marker = new google.maps.Marker({
      position: {lat: jcf_googlemap.lat, lng: jcf_googlemap.lng},
      map: map,
    });
    jcf_googlemap.markers = [ marker ];
  }

  var geocoder = new google.maps.Geocoder();

  // search button event
  document.getElementById( jcf_googlemap.search_btn_id ).addEventListener('click', function() {
    jcf_googlemaps_geocode_address(geocoder, map, jcf_googlemap);
  });

  // prevent address line enter command
  document.getElementById( jcf_googlemap.address_id ).addEventListener('keypress', function(event) {
    if (event.keyCode == 13) {
      jcf_googlemaps_geocode_address(geocoder, map, jcf_googlemap);
      event.preventDefault();
      event.stopPropagation();
      return false;
    }
  });

  // clean up button
  document.getElementById( jcf_googlemap.clean_btn_id ).addEventListener('click', function (e) {
    e.preventDefault();

    // remove marker
    if ( jcf_googlemap.markers.length ) {
      jcf_googlemap.markers[0].setMap(null);
      jcf_googlemap.markers = [];
    }

    jQuery( jcf_googlemap.lng_ctrl_id ).val('');
    jQuery( jcf_googlemap.lat_ctrl_id ).val('');
    jQuery( '#' + jcf_googlemap.address_id ).val('');

    return false;
  })

  // set button
  document.getElementById( jcf_googlemap.set_btn_id ).addEventListener('click', function (e) {
    var lat = parseFloat( jQuery( jcf_googlemap.lat_ctrl_id ).val() );
    var lng = parseFloat( jQuery( jcf_googlemap.lng_ctrl_id ).val() );

    if ( isNaN(lat) ) { lat = 0.00; }
    if ( isNaN(lng) ) { lng = 0.00; }

    jcf_googlemaps_add_marker({lat: lat, lng: lng}, map, jcf_googlemap);
  });

  // This event listener calls addMarker() when the map is clicked.
  google.maps.event.addListener(map, 'click', function(event) {
    jQuery( '#' + jcf_googlemap.address_id ).val('');
    jcf_googlemaps_add_marker(event.latLng, map, jcf_googlemap);
  });

  window.jcf_googlemaps[i].map = map;
}

function jcf_googlemaps_add_marker(location, map, jcf_googlemap) {
  for (var i = 0; i < jcf_googlemap.markers.length; i++) {
    jcf_googlemap.markers[i].setMap(null);
  }

  var marker = new google.maps.Marker({
    position: location,
    map: map
  });
  map.setCenter(location);

  if ( map.getZoom() == 3 ) {
    map.setZoom(15);
  }

  jcf_googlemap.markers = [ marker ];

  jQuery( jcf_googlemap.lng_ctrl_id ).val(marker.position.lng());
  jQuery( jcf_googlemap.lat_ctrl_id ).val(marker.position.lat());
}

function jcf_googlemaps_geocode_address(geocoder, map, jcf_googlemap) {
  var address = document.getElementById( jcf_googlemap.address_id ).value;
  geocoder.geocode({'address': address}, function(results, status) {
    if (status === google.maps.GeocoderStatus.OK) {
      map.setCenter(results[0].geometry.location);

      jcf_googlemaps_add_marker(results[0].geometry.location, jcf_googlemap.map, jcf_googlemap);
      jQuery( '#' + jcf_googlemap.address_id ).val( results[0].formatted_address );
    } else {
      alert('Unable to find entered address location. Reason: ' + status);
    }
  });
}

/**
 * Add new event for collection open row. We need to call map resize here
 * @param event The jquery UI event
 * @param ui    The jquery UI widget event values
 *
 * @var this  The accordion object
 */
function jcf_googlemaps_collection_opened(event, ui) {
  if ( ui.newPanel.size() && jQuery(ui.newPanel).find('.jcf-googlemaps-container').size() ) {
    var i = jQuery(ui.newPanel).find('.jcf-googlemaps-container').get(0).mapIndex;
    var jcf_googlemap = window.jcf_googlemaps[i];
    google.maps.event.trigger(jcf_googlemap.map, "resize");
    if ( jcf_googlemap.markers.length ) {
      jcf_googlemap.map.setCenter( jcf_googlemap.markers[0].position );
    }
  }
}
jcf_add_action('collection_row_activated', 'googlemaps_resize', jcf_googlemaps_collection_opened);

/**
 * Clean global maps settings variable
 *
 * @param response
 *
 * @var this  Ajax object
 */
function jcf_googlemaps_clean_maps(response) {
  window.jcf_googlemaps  = [];
}
jcf_add_action('taxonomy_term_added', 'googlemaps_taxonomy_term_added', jcf_googlemaps_clean_maps);

/**
 * Run init for all available maps
 */
function jcf_googlemaps_init_maps() {
  for (var i=0; i<window.jcf_googlemaps.length; i++) {
    jcf_googlemaps_init_field(i);
  }
}

/**
 * init map objects for each jcf googlemap object
 */
google.maps.event.addDomListener(window, 'load', function() {
  jcf_googlemaps_init_maps();
});
jcf_add_action('taxonomy_term_added_form_refreshed', 'googlemaps_taxonomy_term_added', jcf_googlemaps_init_maps);

/**
 * DOM ready
 */
jQuery(document).ready(function(){

  jQuery(document).on('click', '.jcf_googlemaps_toggle_manually', function (e) {
    e.preventDefault();

    jQuery(this).closest('.form-field').find('.jcf_googlemaps_coordinates').toggle();
  })

})