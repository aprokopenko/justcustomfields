<?php

namespace jcf\components\selectmultiple;

use jcf\core;

/**
 * Class for select multiple list type
 *
 * @package default
 * @author Alexander Prokopenko
 */
class JustField_SelectMultiple extends core\JustField {

	/**
	 * Class constructor
	 **/
	public function __construct() {
		$field_ops = array( 'classname' => 'field_selectmultiple' );
		parent::__construct( 'selectmultiple', __( 'Select Multiple', \JustCustomFields::TEXTDOMAIN ), $field_ops );
	}

	/**
	 *    Draw field on post edit form
	 *    you can use $this->instance, $this->entry
	 */
	public function field() {
		if ( ! is_array( $this->entry ) ) {
			$this->entry = array();
		}
		// prepare options array.
		$values = $this->parsedSelectOptions( $this->instance );
		?>
		<div id="jcf_field-<?php echo esc_attr( $this->id ); ?>"
			 class="jcf_edit_field <?php echo esc_attr( $this->field_options['classname'] ); ?>">
			<?php echo $this->field_options['before_widget']; ?>
			<?php echo $this->field_options['before_title'] . esc_html( $this->instance['title'] ) . $this->field_options['after_title']; ?>
			<div class="select_multiple_field">
				<select name="<?php echo esc_attr( $this->get_field_name( 'val' ) ); ?>[]"
						id="<?php echo esc_attr( $this->get_field_id( 'val' ) ); ?>" class="jcf-multiple"
						multiple="multiple">
					<?php foreach ( $values as $key => $val ): ?>
						<option value="<?php echo esc_attr( $val ); ?>" <?php echo selected( true, in_array( $val, $this->entry ), false ); ?>><?php echo esc_html( ucfirst( $key ) ); ?></option>
					<?php endforeach; ?>
				</select>
			</div>
			<?php if ( '' !== $this->instance['description'] ) : ?>
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
		$instance = wp_parse_args( (array) $this->instance, array(
			'title'       => '',
			'description' => '',
			'settings'    => '',
		) );

		$title       = esc_attr( $instance['title'] );
		$options     = esc_attr( $instance['options'] );
		$description = esc_html( $instance['description'] );
		?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_html_e( 'Title:', \JustCustomFields::TEXTDOMAIN ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"
				   name="<?php echo $this->get_field_name( 'title' ); ?>" type="text"
				   value="<?php echo esc_attr( $title ); ?>"/>
		</p>

		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'options' ) ); ?>"><?php esc_html_e( 'Settings:', \JustCustomFields::TEXTDOMAIN ); ?></label>
			<textarea class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'options' ) ); ?>"
					  name="<?php echo $this->get_field_name( 'options' ); ?>"><?php echo esc_html( $options ); ?></textarea>
			<br/>
			<small><?php _e( 'Parameters like (you can use just "label" if "id" is the same):<br>label1|id1<br>label2|id2<br>label3', \JustCustomFields::TEXTDOMAIN ); ?></small>
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'description' ) ); ?>"><?php esc_html_e( 'Description:', \JustCustomFields::TEXTDOMAIN ); ?></label>
			<textarea name="<?php echo $this->get_field_name( 'description' ); ?>"
					  id="<?php echo esc_attr( $this->get_field_id( 'description' ) ); ?>" cols="20" rows="4"
					  class="widefat"><?php echo esc_html( $description ); ?></textarea></p>
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
		$instance                = $old_instance;
		$instance['title']       = strip_tags( $new_instance['title'] );
		$instance['options']     = strip_tags( $new_instance['options'] );
		$instance['description'] = strip_tags( $new_instance['description'] );

		return $instance;
	}

	/**
	 * Prepare list of options
	 *
	 * @param array $instance current instance.
	 *
	 * @return array
	 */
	protected function parsedSelectOptions( $instance ) {
		$values = array();

		$v = explode( "\n", $instance['options'] );
		foreach ( $v as $val ) {
			$val = trim( $val );
			if ( strpos( $val, '|' ) !== false ) {
				$a               = explode( '|', $val );
				$values[ $a[0] ] = $a[1];
			} elseif ( ! empty( $val ) ) {
				$values[ $val ] = $val;
			}
		}

		return $values;
	}

	/**
	 * Print field values inside the shortcode
	 *
	 * @param array $args    shortcode args.
	 *
	 * @return mixed
	 */
	public function shortcode_value( $args ) {
		$options = $this->parsedSelectOptions( $this->instance );
		$options = array_flip( $options );

		if ( empty( $this->entry ) ) {
			return '';
		}

		$html = '<ul class="jcf-list">';
		foreach ( $this->entry as $value ) {
			$key = preg_replace( '/\s+/', '-', $value );
			$key = preg_replace( '/[^0-9a-z\-\_]/i', '', $key );
			if ( isset( $options[ $value ] ) ) {
				$value = $options[ $value ];
			}

			$key   = esc_attr( $key );
			$value = esc_html( $value );
			$html  .= "<li class=\"jcf-item jcf-item-$key\">$value</li>\r\n";
		}
		$html .= '</ul>';

		return $args['before_value'] . $html . $args['after_value'];
	}

}

?>