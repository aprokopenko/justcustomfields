<?php

namespace jcf\components\googlemaps;

use jcf\core;
use jcf\models\Settings;

class JustField_GoogleMaps extends core\JustField
{
	protected static $shorcode_enqueue_script;

	public function __construct()
	{
		$field_ops = array( 'classname' => 'field_googlemaps' );
		parent::__construct('googlemaps', __('Google Maps', \JustCustomFields::TEXTDOMAIN), $field_ops);
	}

	/**
	 * 	draw field on post edit form
	 * 	you can use $this->instance, $this->entry
	 */
	public function field()
	{
		$api_key = Settings::getGoogleMapsApiKey();

		$this->entry = wp_parse_args($this->entry, array('lat' => '', 'lng' => '', 'address' => '',));
		?>
		<div id="jcf_field-<?php echo $this->id; ?>" class="jcf_edit_field jcf_form_inline <?php echo $this->fieldOptions['classname']; ?>">
			<?php echo $this->fieldOptions['before_widget']; ?>
				<?php echo $this->fieldOptions['before_title'] . esc_html($this->instance['title']) . $this->fieldOptions['after_title']; ?>
				<br />

				<?php if ( empty($api_key) ) : ?>
					<strong>Please set Google Maps API Key on Just Custom Fields <a href="<?php echo esc_url( admin_url('options-general.php?page=jcf_settings')); ?>">Settings</a> page.</strong>
				<?php else : ?>
					<div class="jcf_cols">
						<div class="jcf_col1">
							<input type="text" id="<?php echo $this->getFieldId('address'); ?>" name="<?php echo $this->getFieldName('address'); ?>"
								   placeholder="Enter address to search with Google Maps"
								   value="<?php echo esc_attr($this->entry['address']); ?>" >
						</div>
						<div class="jcf_col2">
							<button id="<?php echo $this->getFieldId('search_btn'); ?>" class="button" type="button">
								<span class="dashicons dashicons-search"></span> Find
							</button> &nbsp; or &nbsp;
							<button class="button jcf_googlemaps_toggle_manually" type="button">
								<span class="dashicons dashicons-location"></span> Set coordinates manually
							</button> &nbsp; or &nbsp;
							<span class="widget-control-remove">
								<span class="dashicons dashicons-editor-removeformatting"></span>
								<a id="<?php echo $this->getFieldId('clean_btn'); ?>" href="#" class="widget-control-remove"> Clean</a>
							</span>
						</div>
					</div>
					<div class="clear"></div>
					<div class="jcf_googlemaps_coordinates" style="display: none;">
						<span>Coordinates</span>
						<input type="text" placeholder="Latitude"
								name="<?php echo $this->getFieldName('lat'); ?>"
								id="<?php echo $this->getFieldId('lat'); ?>"
								value="<?php echo esc_attr($this->entry['lat']); ?>"
								>
						<input type="text" placeholder="Longtitude"
								name="<?php echo $this->getFieldName('lng'); ?>"
								id="<?php echo $this->getFieldId('lng'); ?>"
								value="<?php echo esc_attr($this->entry['lng']); ?>"
								>
						<div class="jcf_googlemaps_coordinates_buttons">
							<button id="<?php echo $this->getFieldId('set_btn'); ?>" class="button" type="button">Set Marker</button>
							&nbsp; <a href="#" class="jcf_googlemaps_toggle_manually">hide coordinates</a>
						</div>
					</div>
					<div class="clear"></div>

					<div class="jcf-googlemaps-container" id="<?php echo $this->getFieldId('map'); ?>" style="width: 100%; max-width: 800px; height: 400px;"></div>

					<?php if ( $this->instance['description'] != '' ) : ?>
						<p class="howto"><?php echo esc_html($this->instance['description']); ?></p>
					<?php endif; ?>

					<script>
                      	if ( ! window.jcf_googlemaps ) window.jcf_googlemaps  = [];
						window.jcf_googlemaps.push({
						  'id': '<?php echo esc_attr( $this->id ); ?>',
						  'map_id': '<?php echo $this->getFieldId('map'); ?>',
						  'address_id': '<?php echo $this->getFieldId('address'); ?>',
						  'search_btn_id': '<?php echo $this->getFieldId('search_btn'); ?>',
						  'clean_btn_id': '<?php echo $this->getFieldId('clean_btn'); ?>',
						  'set_btn_id': '<?php echo $this->getFieldId('set_btn'); ?>',
						  'lng_ctrl_id': '#<?php echo $this->getFieldId('lng'); ?>',
						  'lat_ctrl_id': '#<?php echo $this->getFieldId('lat'); ?>',
						  'lat': <?php echo (float)$this->entry['lat']; ?>,
						  'lng': <?php echo (float)$this->entry['lng']; ?>,
						  'markers': []
						})
						<?php if ( $this->isCollectionField() && defined('DOING_AJAX') && DOING_AJAX ) : ?>
                        	jcf_googlemaps_init_field( window.jcf_googlemaps.length -1 );
						<?php endif; ?>
					</script>
				<?php endif; ?>

			<?php echo $this->fieldOptions['after_widget']; ?>
		</div>
		<?php
	}

	/**
	 * 	save field on post edit form
	 */
	public function save( $values )
	{
		$values = array(
			'lat' => $values['lat'],
			'lng' => $values['lng'],
			'address' => $values['address'],
		);
		return $values;
	}

	/**
	 * draw form for 
	 */
	public function form()
	{
		$instance = wp_parse_args((array) $this->instance, array( 'title' => '', 'description' => '', ));
		$description = esc_html($instance['description']);
		$title = esc_attr($instance['title']);
		$api_key = Settings::getGoogleMapsApiKey();
		?>
		<?php if ( empty($api_key) ) : ?>
			<div class="error"><?php _e('Please set Google Maps API Key on Settings page.', JCF_TEXTDOMAIN); ?></div>
		<?php endif; ?>

		<p><label for="<?php echo $this->getFieldId('title'); ?>"><?php _e('Title:', \JustCustomFields::TEXTDOMAIN); ?></label> <input class="widefat" id="<?php echo $this->getFieldId('title'); ?>" name="<?php echo $this->getFieldName('title'); ?>" type="text" value="<?php echo $title; ?>" /></p>
		<p><label for="<?php echo $this->getFieldId('description'); ?>"><?php _e('Description:', \JustCustomFields::TEXTDOMAIN); ?></label> <textarea name="<?php echo $this->getFieldName('description'); ?>" id="<?php echo $this->getFieldId('description'); ?>" cols="20" rows="4" class="widefat"><?php echo $description; ?></textarea></p>
		<?php
	}
	
	/**
	 * 	add custom scripts
	 */
	public function addJs()
	{
		if ( $api_key = Settings::getGoogleMapsApiKey() ) {
			wp_register_script('jcf_googlemaps_api', esc_url('//maps.googleapis.com/maps/api/js?key=' . $api_key), array('jquery'), '3', false);
			wp_enqueue_script('jcf_googlemaps_api');

			wp_register_script('jcf_googlemaps_events', plugins_url( '/assets/googlemaps.js', __FILE__ ), array('jquery', 'jcf_googlemaps_api', 'jcf_edit_post'));
			wp_enqueue_script('jcf_googlemaps_events');
		}
	}

	/**
	 * add custom css
	 */
	public function addCss()
	{
		wp_register_style('jcf_googlemaps', jcf_plugin_url('components/googlemaps/assets/googlemaps.css'), array(  'jcf_edit_post' ));
		wp_enqueue_style('jcf_googlemaps');
	}

	/**
	 * 	update instance (settings) for current field
	 */
	public function update( $new_instance, $old_instance )
	{
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['description'] = strip_tags($new_instance['description']);
		return $instance;
	}
	
	/**
	 * print field values inside the shortcode
	 *
	 * @params array $args	shortcode args
	 */
	public function shortcodeValue( $args )
	{
		$api_key = Settings::getGoogleMapsApiKey();

		if ( empty($api_key) ) {
			return 'Google Maps API Key does not configured correctly.';
		}

		if ( empty($this->entry['lat']) || empty($this->entry['lng']) ) {
			return '<!-- Field values (lat./lng.) are empty -->';
		}

		ob_start();

		if ( ! self::$shorcode_enqueue_script ) :
		?>
			<script>
				if ( ! document.getElementById('googlemaps_api_for_jcf') ) {
				  document.write('<script id="googlemaps_api_for_jcf" src="//maps.googleapis.com/maps/api/js?key=<?php echo esc_attr($api_key); ?>&ver=3"><\/script>');

				  window.jcf_googlemaps = [];
				  window.addEventListener('load', function() {
					for ( var i = 0; i < jcf_googlemaps.length; i++ ) {
					  jcf_googlemap = window.jcf_googlemaps[i];

					  var map = new google.maps.Map(document.getElementById(jcf_googlemap.container_id), {
						zoom: 15,
						center: {lat: jcf_googlemap.lat, lng: jcf_googlemap.lng},
					  });

					  var marker = new google.maps.Marker({
						position: {lat: jcf_googlemap.lat, lng: jcf_googlemap.lng},
						map: map
					  });
					}
				  });
				}
			</script>
		<?php
			self::$shorcode_enqueue_script = 'included';
		endif;
		?>

		<div id="jcf-map-<?php echo $this->id; ?>" class="jcf-map-container" style="min-height: 200px;"></div>
		<script>
          window.jcf_googlemaps.push({
            container_id: 'jcf-map-<?php echo $this->id; ?>',
            lat: <?php echo $this->entry['lat']; ?>,
            lng: <?php echo $this->entry['lng']; ?>,
		  })
		</script>

		<?php

		$content = ob_get_clean();

		return $content;
	}

}
