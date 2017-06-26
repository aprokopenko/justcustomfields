<?php

namespace jcf\components\textarea;

use jcf\core;

/**
 * Textarea field type
 *
 * @package default
 * @author Alexander Prokopenko
 */
class JustField_Textarea extends core\JustField {

	/**
	 * Class constructor
	 **/
	public function __construct() {
		$field_ops = array( 'classname' => 'field_textarea' );
		parent::__construct( 'textarea', __( 'Textarea', 'jcf' ), $field_ops );
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
			<?php
			if ( ! empty( $this->instance['editor'] ) ) : // check editor.

				ob_start();

				wp_editor( $this->entry, $this->get_field_id( 'val' ), array(
					'textarea_name' => $this->get_field_name( 'val' ),
					'textarea_rows' => 5,
					'media_buttons' => true,
					'wpautop'       => true,
					'quicktags'     => false,
					'tinymce'       => array(
						'theme_advanced_buttons1' => 'bold,italic,strikethrough,|,bullist,numlist,blockquote,|,justifyleft,justifycenter,justifyright,|,link,unlink,|,spellchecker,fullscreen,wp_adv',
					),
				) );
				echo ob_get_clean();

				if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) :
				?>
				<script type="text/javascript">
					jQuery(document).ready(function () {
						tinymce.execCommand('mceRemoveEditor', false, '<?php echo esc_attr( $this->get_field_id( 'val' ) ); ?>');
						tinymce.execCommand('mceAddEditor', false, '<?php echo esc_attr( $this->get_field_id( 'val' ) ); ?>');
					})
				</script>
			<?php endif; ?>

			<?php if ( $this->is_taxonomy_field() ) : ?>
				<script>
					jQuery(document).ready(function () {
						jQuery(document).on('mousedown click keydown', '#submit', function () {
							tinymce.triggerSave();
						});
					});
				</script>
			<?php endif; ?>

			<?php else : // no editor - print textarea.              ?>
			<?php $entry = esc_html( $this->entry ); ?>
				<textarea name="<?php echo $this->get_field_name( 'val' ); ?>"
						  id="<?php echo esc_attr( $this->get_field_id( 'val' ) ); ?>" rows="5"
						  cols="50"><?php echo esc_attr( $entry ) ?></textarea>
			<?php endif; ?>

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
		$instance    = wp_parse_args( (array) $this->instance, array( 'title' => '', 'description' => '' ) );
		$title       = esc_attr( $instance['title'] );
		$description = esc_html( $instance['description'] );
		$checked     = ! empty( $instance['editor'] ) ? ' checked="checked" ' : '';
		?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_html_e( 'Title:', 'jcf' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"
				   name="<?php echo $this->get_field_name( 'title' ); ?>" type="text"
				   value="<?php echo esc_attr( $title ); ?>"/>
		</p>
		<p><label for="<?php echo esc_attr( $this->get_field_id( 'editor' ) ); ?>">
				<input class="checkbox"
					   id="<?php echo esc_attr( $this->get_field_id( 'editor' ) ); ?>"
					   name="<?php echo $this->get_field_name( 'editor' ); ?>"
					   type="checkbox"
					   value="1" <?php echo esc_attr( $checked ); ?> /> <?php esc_html_e( 'Use Editor for this textarea:', 'jcf' ); ?>
			</label></p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'description' ) ); ?>"><?php esc_html_e( 'Description:', 'jcf' ); ?></label>
			<textarea name="<?php echo $this->get_field_name( 'description' ); ?>"
					  id="<?php echo esc_attr( $this->get_field_id( 'description' ) ); ?>" cols="20" rows="4"
					  class="widefat"><?php echo esc_attr( $description ); ?></textarea></p>
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
		$values = isset( $values['val'] ) ? $values['val'] : '';

		if ( $this->instance['editor'] ) {
			$values = wpautop( $values );
		}

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
		$instance['editor']      = (int) @$new_instance['editor'];
		$instance['description'] = strip_tags( $new_instance['description'] );

		return $instance;
	}

}

?>