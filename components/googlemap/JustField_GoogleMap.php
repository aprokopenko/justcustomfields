<?php

namespace jcf\components\googlemap;

use jcf\core;

class JustField_GoogleMap extends core\JustField
{

	public function __construct()
	{
		$field_ops = array( 'classname' => 'field_googlemap' );
		parent::__construct('googlemap', __('Google Map', \JustCustomFields::TEXTDOMAIN), $field_ops);
	}

	/**
	 * 	draw field on post edit form
	 * 	you can use $this->instance, $this->entry
	 */
	public function field()
	{
		$markers = str_replace('-', '_', $this->getFieldId('markers'));
		$function_prefix = str_replace('-', '', $this->id);
		?>
		<div id="jcf_field-<?php echo $this->id; ?>" class="jcf_edit_field <?php echo $this->fieldOptions['classname']; ?>">
					<?php echo $this->fieldOptions['before_widget']; ?>
						<?php echo $this->fieldOptions['before_title'] . esc_html($this->instance['title']) . $this->fieldOptions['after_title']; ?>
						<br />

						<div id="floating-panel">
							<input id="jcf-geocode-address-<?php echo $this->id; ?>" type="textbox" value="">
							<input id="jcf-geocode-submit-<?php echo $this->id; ?>" type="button" value="Find">
						</div>
						<div id="jcf-map-<?php echo $this->id; ?>" style="width: 100%; height: 400px;"></div>
						<input type="hidden" 
								name="<?php echo $this->getFieldName('lat'); ?>"
								id="<?php echo $this->getFieldId('lat'); ?>"
								value="<?php echo esc_attr($this->entry['lat']); ?>"/>
						<input type="hidden" 
								name="<?php echo $this->getFieldName('lng'); ?>"
								id="<?php echo $this->getFieldId('lng'); ?>"
								value="<?php echo esc_attr($this->entry['lng']); ?>"/>
						<script>
							var <?php echo $markers; ?> = [];
							
							function <?= $function_prefix; ?>addMarker(location, map)
							{
								for (var i = 0; i < <?php echo $markers; ?>.length; i++) {
									<?php echo $markers; ?>[i].setMap(null);
								}

								map.setCenter(location);
								var marker = new google.maps.Marker({
									position: location,
									map: map
								});

								<?php echo $markers; ?>.push(marker);
								jQuery('#<?php echo $this->getFieldId('lng'); ?>').val(marker.position.lng());
								jQuery('#<?php echo $this->getFieldId('lat'); ?>').val(marker.position.lat());
							}
							
							function <?= $function_prefix; ?>geocodeAddress(geocoder, resultsMap)
							{
								var address = document.getElementById('jcf-geocode-address-<?php echo $this->id; ?>').value;
								geocoder.geocode({'address': address}, function(results, status) {
									if (status === google.maps.GeocoderStatus.OK) {
										resultsMap.setCenter(results[0].geometry.location);
										<?= $function_prefix; ?>addMarker(results[0].geometry.location, resultsMap);
									} else {
										alert('Geocode was not successful for the following reason: ' + status);
									}
								});
							}

							google.maps.event.addDomListener(window, 'load', function() {
								var map = new google.maps.Map(document.getElementById('jcf-map-<?php echo $this->id; ?>'), {
									zoom: 2,
									center: {lat: 5.397, lng: 5.644},
								});

								<?php if ( !( empty($this->entry['lng']) || empty($this->entry['lat']) ) ) : ?>
									var lat = <?php echo $this->entry['lat']; ?>;
									var lng = <?php echo $this->entry['lng']; ?>;
									var mlocation = {lat: lat, lng: lng};
									<?= $function_prefix; ?>addMarker(mlocation, map);
								<?php endif; ?>

								var geocoder = new google.maps.Geocoder();
								document.getElementById('jcf-geocode-submit-<?php echo $this->id; ?>').addEventListener('click', function() {
									<?= $function_prefix; ?>geocodeAddress(geocoder, map);
								});

								// This event listener calls addMarker() when the map is clicked.
								google.maps.event.addListener(map, 'click', function(event) {
									<?= $function_prefix; ?>addMarker(event.latLng, map);
								});
							});
						</script>
						<?php if ( $this->instance['description'] != '' ) : ?>
							<p class="howto"><?php echo esc_html($this->instance['description']); ?></p>
						<?php endif; ?>

			<?php echo $this->fieldOptions['after_widget']; ?>
		</div>
		<?php
	}

	/**
	 * draw form for 
	 */
	public function form()
	{
		$instance = wp_parse_args((array) $this->instance, array( 'title' => '', 'description' => '', 'api_key' => '' ));
		$description = esc_html($instance['description']);
		$title = esc_attr($instance['title']);
		$api_key = esc_attr($instance['api_key']);
		?>
		<p><label for="<?php echo $this->getFieldId('title'); ?>"><?php _e('Title:', \JustCustomFields::TEXTDOMAIN); ?></label> <input class="widefat" id="<?php echo $this->getFieldId('title'); ?>" name="<?php echo $this->getFieldName('title'); ?>" type="text" value="<?php echo $title; ?>" /></p>
		<p><label for="<?php echo $this->getFieldId('description'); ?>"><?php _e('Description:', \JustCustomFields::TEXTDOMAIN); ?></label> <textarea name="<?php echo $this->getFieldName('description'); ?>" id="<?php echo $this->getFieldId('description'); ?>" cols="20" rows="4" class="widefat"><?php echo $description; ?></textarea></p>
		<p>
			<label for="<?php echo $this->getFieldId('api_key'); ?>"><?php _e('API Key:', \JustCustomFields::TEXTDOMAIN); ?></label> 
			<input class="widefat" id="<?php echo $this->getFieldId('api_key'); ?>" name="<?php echo $this->getFieldName('api_key'); ?>" type="text" value="<?php echo $api_key; ?>" />
			<a href="https://developers.google.com/maps/documentation/javascript/get-api-key" target="_blank" ><?php _e('Get API KEY', \JustCustomFields::TEXTDOMAIN); ?></a>
		</p>
		<?php
	}
	
	/**
	 * 	add custom scripts
	 */
	public function addJs()
	{
		$instance = wp_parse_args((array) $this->instance, array( 'title' => '', 'description' => '', 'api_key' => '' ));
		$api_key = esc_attr($instance['api_key']);
		
		wp_enqueue_script('google-map', '//maps.googleapis.com/maps/api/js?key=' . $api_key, array('jquery'), '3', false);
	}

	/**
	 * 	save field on post edit form
	 */
	public function save( $values )
	{
		$values = array('lat' => $values['lat'], 'lng' => $values['lng']);
		return $values;
	}

	/**
	 * 	update instance (settings) for current field
	 */
	public function update( $new_instance, $old_instance )
	{
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['description'] = strip_tags($new_instance['description']);
		$instance['api_key'] = strip_tags($new_instance['api_key']);
		return $instance;
	}
	
	/**
	 * print field values inside the shortcode
	 *
	 * @params array $args	shortcode args
	 */
	public function shortcodeValue( $args )
	{
		if ( empty($this->entry) ) return '';

		$markers = str_replace('-', '_', $this->getFieldId('markers'));
		$function_prefix = str_replace('-', '', $this->id);
		$instance = wp_parse_args((array) $this->instance, array( 'title' => '', 'description' => '', 'api_key' => '' ));
		$api_key = esc_attr($instance['api_key']);

		ob_start();
		?>
		<div id="jcf-map-<?php echo $this->id; ?>" style="width: 100%; height: 400px;"></div>
		
		<script>
			var <?php echo $markers; ?> = [];

			function <?= $function_prefix; ?>addMarker(location, map)
			{
				for (var i = 0; i < <?php echo $markers; ?>.length; i++) {
					<?php echo $markers; ?>[i].setMap(null);
				}

				map.setCenter(location);
				var marker = new google.maps.Marker({
					position: location,
					map: map
				});

				<?php echo $markers; ?>.push(marker);
				jQuery('#<?php echo $this->getFieldId('lng'); ?>').val(marker.position.lng());
				jQuery('#<?php echo $this->getFieldId('lat'); ?>').val(marker.position.lat());
			}

			function <?= $function_prefix; ?>geocodeAddress(geocoder, resultsMap)
			{
				var address = document.getElementById('jcf-geocode-address-<?php echo $this->id; ?>').value;
				geocoder.geocode({'address': address}, function(results, status) {
					if (status === google.maps.GeocoderStatus.OK) {
						resultsMap.setCenter(results[0].geometry.location);
						<?= $function_prefix; ?>addMarker(results[0].geometry.location, resultsMap);
					} else {
						alert('Geocode was not successful for the following reason: ' + status);
					}
				});
			}

			google.maps.event.addDomListener(window, 'load', function() {
				var map = new google.maps.Map(document.getElementById('jcf-map-<?php echo $this->id; ?>'), {
					zoom: 2,
					center: {lat: 5.397, lng: 5.644},
				});

				<?php if ( !( empty($this->entry['lng']) || empty($this->entry['lat']) ) ) : ?>
					var lat = <?php echo $this->entry['lat']; ?>;
					var lng = <?php echo $this->entry['lng']; ?>;
					var mlocation = {lat: lat, lng: lng};
					<?= $function_prefix; ?>addMarker(mlocation, map);
				<?php endif; ?>

				var geocoder = new google.maps.Geocoder();
				document.getElementById('jcf-geocode-submit-<?php echo $this->id; ?>').addEventListener('click', function() {
					<?= $function_prefix; ?>geocodeAddress(geocoder, map);
				});

				// This event listener calls addMarker() when the map is clicked.
				google.maps.event.addListener(map, 'click', function(event) {
					<?= $function_prefix; ?>addMarker(event.latLng, map);
				});
			});
		</script>
		<?php
		$content = ob_get_clean();

		return $content;
	}

}


