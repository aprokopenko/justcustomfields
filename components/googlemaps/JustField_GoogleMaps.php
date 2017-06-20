<?php

namespace jcf\components\googlemaps;

use jcf\core;
use jcf\models\Settings;

/**
 * Class for googleMaps
 *
 * @package default
 * @author Alexander Prokopenko
 */
class JustField_GoogleMaps extends core\JustField {

	/**
	 * Shortcode Script
	 *
	 * @var $shorcode_enqueue_script
	 */
	protected static $shorcode_enqueue_script;

	/**
	 * Class constructor
	 **/
	public function __construct() {
		$field_ops = array( 'classname' => 'field_googlemaps' );
		parent::__construct( 'googlemaps', __( 'Google Maps', 'jcf' ), $field_ops );
	}

	/**
	 *    Draw field on post edit form
	 *    you can use $this->instance, $this->entry
	 */
	public function field() {
		$api_key = Settings::get_google_maps_api_key();

		$this->entry = wp_parse_args( $this->entry, array( 'lat' => '', 'lng' => '', 'address' => '' ) );
		?>
		<div id="jcf_field-<?php echo esc_attr( $this->id ); ?>"
			 class="jcf_edit_field jcf_form_inline <?php echo esc_attr( $this->field_options['classname'] ); ?>">
			<?php echo $this->field_options['before_widget']; ?>
			<?php echo $this->field_options['before_title'] . esc_html( $this->instance['title'] ) . $this->field_options['after_title']; ?>
			<br/>

			<?php if ( empty( $api_key ) ) : ?>
				<strong>Please set Google Maps API Key on Just Custom Fields <a
							href="<?php echo esc_url( admin_url( 'options-general.php?page=jcf_settings' ) ); ?>">Settings</a>
					page.</strong>
			<?php else : ?>
				<div class="jcf_cols">
					<div class="jcf_col1">
						<input type="text" id="<?php echo esc_attr( $this->get_field_id( 'address' ) ); ?>"
							   name="<?php echo $this->get_field_name( 'address' ); ?>"
							   placeholder="Enter address to search with Google Maps"
							   value="<?php echo esc_attr( $this->entry['address'] ); ?>">
					</div>
					<div class="jcf_col2">
						<button id="<?php echo esc_attr( $this->get_field_id( 'search_btn' ) ); ?>" class="button"
								type="button">
							<span class="dashicons dashicons-search"></span> Find
						</button> &nbsp; or &nbsp;
						<button class="button jcf_googlemaps_toggle_manually" type="button">
							<span class="dashicons dashicons-location"></span> Set coordinates manually
						</button> &nbsp; or &nbsp;
						<span class="widget-control-remove">
								<span class="dashicons dashicons-editor-removeformatting"></span>
								<a id="<?php echo esc_attr( $this->get_field_id( 'clean_btn' ) ); ?>" href="#"
								   class="widget-control-remove"> Clean</a>
							</span>
					</div>
				</div>
				<div class="clear"></div>
				<div class="jcf_googlemaps_coordinates" style="display: none;">
					<span>Coordinates</span>
					<input type="text" placeholder="Latitude"
						   name="<?php echo $this->get_field_name( 'lat' ); ?>"
						   id="<?php echo esc_attr( $this->get_field_id( 'lat' ) ); ?>"
						   value="<?php echo esc_attr( $this->entry['lat'] ); ?>"
					>
					<input type="text" placeholder="Longtitude"
						   name="<?php echo $this->get_field_name( 'lng' ); ?>"
						   id="<?php echo esc_attr( $this->get_field_id( 'lng' ) ); ?>"
						   value="<?php echo esc_attr( $this->entry['lng'] ); ?>"
					>
					<div class="jcf_googlemaps_coordinates_buttons">
						<button id="<?php echo esc_attr( $this->get_field_id( 'set_btn' ) ); ?>" class="button"
								type="button">Set
							Marker
						</button>
						&nbsp; <a href="#" class="jcf_googlemaps_toggle_manually">hide coordinates</a>
					</div>
				</div>
				<div class="clear"></div>

				<div class="jcf-googlemaps-container" id="<?php echo esc_attr( $this->get_field_id( 'map' ) ); ?>"
					 style="width: 100%; max-width: 800px; height: 400px;"></div>

			<?php if ( '' !== $this->instance['description'] ) : ?>
				<p class="howto"><?php echo esc_html( $this->instance['description'] ); ?></p>
			<?php endif; ?>

				<script>
					if (!window.jcf_googlemaps) window.jcf_googlemaps = [];
					window.jcf_googlemaps.push({
						'id': '<?php echo esc_attr( $this->id ); ?>',
						'map_id': '<?php echo esc_attr( $this->get_field_id( 'map' ) ); ?>',
						'address_id': '<?php echo esc_attr( $this->get_field_id( 'address' ) ); ?>',
						'search_btn_id': '<?php echo esc_attr( $this->get_field_id( 'search_btn' ) ); ?>',
						'clean_btn_id': '<?php echo esc_attr( $this->get_field_id( 'clean_btn' ) ); ?>',
						'set_btn_id': '<?php echo esc_attr( $this->get_field_id( 'set_btn' ) ); ?>',
						'lng_ctrl_id': '#<?php echo esc_attr( $this->get_field_id( 'lng' ) ); ?>',
						'lat_ctrl_id': '#<?php echo esc_attr( $this->get_field_id( 'lat' ) ); ?>',
						'lat': <?php echo (float) $this->entry['lat']; ?>,
						'lng': <?php echo (float) $this->entry['lng']; ?>,
						'markers': []
					});
					<?php if ( $this->is_collection_field() && defined( 'DOING_AJAX' ) && DOING_AJAX ) : ?>
					jcf_googlemaps_init_field(window.jcf_googlemaps.length - 1);
					<?php endif; ?>
				</script>
			<?php endif; ?>

			<?php echo $this->field_options['after_widget']; ?>
		</div>
		<?php
	}

	/**
	 * Save field on post edit form
	 *
	 * @param array $values Values.
	 *
	 * @return array
	 */
	public function save( $values ) {
		$values = array(
			'lat'     => $values['lat'],
			'lng'     => $values['lng'],
			'address' => $values['address'],
		);

		return $values;
	}

	/**
	 * Draw form for field
	 */
	public function form() {
		$instance    = wp_parse_args( (array) $this->instance, array( 'title' => '', 'description' => '' ) );
		$description = esc_html( $instance['description'] );
		$title       = esc_attr( $instance['title'] );
		$api_key     = Settings::get_google_maps_api_key();
		?>
		<?php if ( empty( $api_key ) ) : ?>
			<div class="error"><?php esc_html_e( 'Please set Google Maps API Key on Settings page.', 'jcf' ); ?></div>
		<?php endif; ?>

		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_html_e( 'Title:', 'jcf' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"
				   name="<?php echo $this->get_field_name( 'title' ); ?>" type="text"
				   value="<?php echo esc_attr( $title ); ?>"/>
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'description' ) ); ?>"><?php esc_html_e( 'Description:', 'jcf' ); ?></label>
			<textarea name="<?php echo $this->get_field_name( 'description' ); ?>"
					  id="<?php echo esc_attr( $this->get_field_id( 'description' ) ); ?>" cols="20" rows="4"
					  class="widefat"><?php echo $description; ?></textarea></p>
		<?php
	}

	/**
	 *    Add custom scripts
	 */
	public function add_js() {
		if ( $api_key = Settings::get_google_maps_api_key() ) {
			wp_register_script( 'jcf_googlemaps_api', esc_url( '//maps.googleapis.com/maps/api/js?key=' . $api_key ), array( 'jquery' ), '3', false );
			wp_enqueue_script( 'jcf_googlemaps_api' );

			wp_register_script( 'jcf_googlemaps_events', plugins_url( '/assets/googlemaps.js', __FILE__ ), array(
				'jquery',
				'jcf_googlemaps_api',
				'jcf_edit_post',
			) );
			wp_enqueue_script( 'jcf_googlemaps_events' );
		}
	}

	/**
	 * Add custom css
	 */
	public function add_css() {
		wp_register_style( 'jcf_googlemaps', jcf_plugin_url( 'components/googlemaps/assets/googlemaps.css' ), array( 'jcf_edit_post' ) );
		wp_enqueue_style( 'jcf_googlemaps' );
	}

	/**
	 * Update instance (settings) for current field
	 *
	 * @param array $new_instance New instance.
	 * @param array $old_instance Old instance.
	 *
	 * @return array
	 */
	public function update( $new_instance, $old_instance ) {
		$instance                = $old_instance;
		$instance['title']       = strip_tags( $new_instance['title'] );
		$instance['description'] = strip_tags( $new_instance['description'] );

		return $instance;
	}

	/**
	 * Print field values inside the shortcode
	 *
	 * @param array $args shortcode args.
	 *
	 * @return mixed
	 */
	public function shortcode_value( $args ) {
		$api_key = Settings::get_google_maps_api_key();

		if ( empty( $api_key ) ) {
			return 'Google Maps API Key does not configured correctly.';
		}

		if ( empty( $this->entry['lat'] ) || empty( $this->entry['lng'] ) ) {
			return '<!-- Field values (lat./lng.) are empty -->';
		}

		ob_start();

		if ( ! self::$shorcode_enqueue_script ) :
			?>
			<script>
				if (!document.getElementById('googlemaps_api_for_jcf')) {
					document.write('<script id="googlemaps_api_for_jcf" src="//maps.googleapis.com/maps/api/js?key=<?php echo esc_attr( $api_key ); ?>&ver=3"><\/script>');

					window.jcf_googlemaps = [];
					window.addEventListener('load', function () {
						for (var i = 0; i < jcf_googlemaps.length; i++) {
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

		<div id="jcf-map-<?php echo esc_attr( $this->id ); ?>" class="jcf-map-container"
			 style="min-height: 200px;"></div>
		<script>
			window.jcf_googlemaps.push({
				container_id: 'jcf-map-<?php echo esc_attr( $this->id ); ?>',
				lat: <?php echo esc_attr( $this->entry['lat'] ); ?>,
				lng: <?php echo esc_attr( $this->entry['lng'] ); ?>,
			})
		</script>

		<?php

		$content = ob_get_clean();

		return $content;
	}

}
