<?php

namespace jcf\components\inputtext;

use jcf\core;

/**
 * Class for InputText
 *
 * @package default
 * @author Alexander Prokopenko
 */
class JustField_InputText extends core\JustField {

	/**
	 * Class constructor
	 **/
	public function __construct() {
		$field_ops = array( 'classname' => 'field_inputtext' );
		parent::__construct( 'inputtext', __( 'Input Text', 'jcf' ), $field_ops );
	}

	/**
	 *    Draw field on post edit form
	 *    you can use $this->instance, $this->entry
	 */
	public function field() {
		?>
		<div id="jcf_field-<?php echo esc_attr( $this->id ); ?>"
			 class="jcf_edit_field <?php echo esc_attr( $this->field_options['classname'] ); ?>">
			<?php echo $this->field_options['before_widget']; ?>
			<?php echo $this->field_options['before_title'] . esc_html( $this->instance['title'] ) . $this->field_options['after_title']; ?>

			<br/>
			<input type="text"
				   name="<?php echo $this->get_field_name( 'val' ); ?>"
				   id="<?php echo esc_attr( $this->get_field_id( 'val' ) ); ?>"
				   value="<?php echo esc_attr( $this->entry ); ?>"/>
			<?php if ( '' !== $this->instance['description'] ) : ?>
				<p class="howto"><?php echo esc_html( $this->instance['description'] ); ?></p>
			<?php endif; ?>

			<?php echo $this->field_options['after_widget']; ?>
		</div>
		<?php
	}

	/**
	 * Draw form for
	 */
	public function form() {
		$instance    = wp_parse_args( (array) $this->instance, array( 'title' => '', 'description' => '' ) );
		$description = esc_html( $instance['description'] );
		$title       = esc_attr( $instance['title'] );
		?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_html_e( 'Title:', 'jcf' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"
				   name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>"/>
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'description' ) ); ?>"><?php esc_html_e( 'Description:', 'jcf' ); ?></label>
			<textarea name="<?php echo$this->get_field_name( 'description' ); ?>"
					  id="<?php echo  esc_attr( $this->get_field_id( 'description' ) ); ?>" cols="20" rows="4"
					  class="widefat"><?php echo esc_attr( $description ); ?></textarea></p>
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
		$instance['description'] = strip_tags( $new_instance['description'] );

		return $instance;
	}

}
