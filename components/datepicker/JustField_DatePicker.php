<?php

namespace jcf\components\datepicker;

use jcf\core;

/**
 * Class for datapicker
 *
 * @package default
 * @author Alexander Prokopenko
 */
class JustField_DatePicker extends core\JustField {

	/**
	 * Capability
	 *
	 * @var $compatibility
	 */
	public static $compatibility = '3.3+';

	/**
	 * Class constructor
	 **/
	public function __construct() {
		$field_ops = array( 'classname' => 'field_datepicker' );
		parent::__construct( 'datepicker', __( 'Date Picker', 'jcf' ), $field_ops );
	}

	/**
	 * Draw field on post edit form
	 * you can use $this->instance, $this->entry
	 */
	public function field() {
		?>
		<div id="jcf_field-<?php echo esc_attr( $this->id ); ?>"
			 class="jcf_edit_field <?php echo esc_attr( $this->field_options['classname'] ); ?>">
			<?php echo $this->field_options['before_widget']; ?>
			<?php echo $this->field_options['before_title'] . esc_html( $this->instance['title'] ) . $this->field_options['after_title']; ?>

			<div>
				<input id="<?php echo esc_attr( $this->get_field_id( 'val' ) ); ?>"
					   name="<?php echo $this->get_field_name( 'val' ); ?>" type="text"
					   value="<?php echo esc_attr( $this->entry ); ?>" size="14" style="width:150px;"/>
				<span class="dashicons dashicons-calendar-alt"></span>
			</div>

			<script type="text/javascript"><!--
				jQuery(document).ready(function () {
					jQuery("#<?php echo esc_attr( $this->get_field_id( 'val' ) ); ?>").datepicker({
						dateFormat: "<?php echo ! empty( $this->instance['date_format'] ) ? esc_attr( $this->instance['date_format'] ) : 'yy-mm-dd'; ?>"
						<?php if ( ! empty( $this->instance['show_monthes'] ) ) {
							echo ', changeMonth: true, changeYear: true'; } ?>
					});
				});
				--></script>

			<?php if ( ! empty( $this->instance['description'] ) ) : ?>
				<p class="howto"><?php echo esc_html( $this->instance['description'] ); ?></p>
			<?php endif; ?>

			<?php echo $this->field_options['after_widget']; ?>
		</div>
		<?php
	}

	/**
	 * Draw form for edit field
	 */
	public function form() {
		// Defaults.
		$instance = wp_parse_args( (array) $this->instance, array( 'title' => '' ) );

		$title        = esc_attr( $instance['title'] );
		$show_monthes = ! empty( $instance['show_monthes'] ) ? ' checked="checked" ' : '';
		$date_format  = ! empty( $instance['date_format'] ) ? esc_attr( $instance['date_format'] ) : 'yy-mm-dd';
		?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_html_e( 'Title:', 'jcf' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"
				   name="<?php echo $this->get_field_name( 'title' ); ?>" type="text"
				   value="<?php echo esc_attr( $title ); ?>"/>
		</p>
		<p><label for="<?php echo esc_attr( $this->get_field_id( 'show_monthes' ) ); ?>">
				<input class="checkbox"
					   id="<?php echo esc_attr( $this->get_field_id( 'show_monthes' ) ); ?>"
					   name="<?php echo $this->get_field_name( 'show_monthes' ); ?>"
					   type="checkbox"
					   value="1" <?php echo esc_attr( $show_monthes ); ?> /> <?php esc_html_e( 'Show month/year select boxes', 'jcf' ); ?>
			</label></p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'date_format' ) ); ?>"><?php esc_html_e( 'Date format:', 'jcf' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'date_format' ) ); ?>"
				   name="<?php echo $this->get_field_name( 'date_format' ); ?>" type="text"
				   value="<?php echo esc_attr( $date_format ); ?>"/><br/>
			<small><?php esc_html_e( 'Example:', 'jcf' ); ?> yy-mm-dd <a
						href="http://api.jqueryui.com/datepicker/#option-dateFormat"
						target="_blank"><?php esc_html_e( 'look more about date formats', 'jcf' ); ?></a>
			</small>
		</p>
		<?php
	}

	/**
	 *  Save field on post edit form
	 *
	 * @param array $values values.
	 *
	 * @return array
	 */
	public function save( $values ) {
		$values = $values['val'];

		return $values;
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
		$instance                 = $old_instance;
		$instance['title']        = strip_tags( $new_instance['title'] );
		$instance['show_monthes'] = (int) @$new_instance['show_monthes'];
		$instance['date_format']  = @$new_instance['date_format'];

		return $instance;
	}

	/**
	 * Add script for collection and custom scripts and styles from datapicker fields
	 */
	public function add_js() {
		/**
		 * WP version 3.0 and above have datepicker ui-core;
		 */
		wp_enqueue_script( 'jquery-ui-datepicker' );
	}

	/**
	 * Add styles for collection and custom scripts and styles from datapicker fields
	 */
	public function add_css() {
		wp_register_style( 'jcf_ui_datepicker', jcf_plugin_url( 'components/datepicker/ui-theme-smoothness/jquery-ui-1.8.13.custom.css' ) );
		wp_enqueue_style( 'jcf_ui_datepicker' );
	}

}

?>