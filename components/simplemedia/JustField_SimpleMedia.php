<?php

namespace jcf\components\simplemedia;

use jcf\core;

/**
 *    Simple Upload media field
 */
class JustField_SimpleMedia extends core\JustField {

	/**
	 * Capability
	 *
	 * @var $compatibility
	 */
	public static $compatibility = '4.0+';

	/**
	 * Class constructor
	 **/
	public function __construct() {
		$field_ops = array( 'classname' => 'field_simplemedia' );
		parent::__construct( 'simplemedia', __( 'Simple Media', \JustCustomFields::TEXTDOMAIN ), $field_ops );
	}

	/**
	 *    Draw field on post edit form
	 *    you can use $this->instance, $this->entry
	 */
	public function field() {
		$noimage      = $image = jcf_plugin_url( 'components/simplemedia/assets/jcf-noimage100x77.jpg' );
		$delete_class = ' jcf-hide';
		$upload_type  = $this->instance['type'];
		$upload_text  = ( 'image' === $upload_type ) ? __( 'Select image', \JustCustomFields::TEXTDOMAIN ) : __( 'Select file', \JustCustomFields::TEXTDOMAIN );
		$value        = $link = '#';

		if ( empty( $this->entry ) ) {
			$this->entry = 0;
		}
		?>
		<div id="jcf_field-<?php echo esc_attr( $this->id ); ?>"
			 class="jcf_edit_field <?php echo esc_attr( $this->field_options['classname'] ); ?>">
			<?php echo $this->field_options['before_widget']; ?>
			<?php echo $this->field_options['before_title'] . esc_html( $this->instance['title'] ) . $this->field_options['after_title']; ?>
			<div class="jcf-simple-field jcf-simple-type-<?php echo esc_attr( $upload_type ); ?> ">
				<?php
				if ( ! empty( $this->entry ) ) {
					$value        = esc_attr( $this->entry );
					$link         = wp_get_attachment_url( $this->entry );
					$upload_text  = ( 'image' === $upload_type ) ? __( 'Update image', \JustCustomFields::TEXTDOMAIN ) : __( 'Update file', \JustCustomFields::TEXTDOMAIN );
					$delete_class = '';
				}
				?>
				<div class="jcf-simple-row">
					<div class="jcf-simple-container">
						<?php if ( 'image' === $upload_type ) : ?>
							<div class="jcf-simple-image">
								<a href="<?php echo esc_attr( $link ); ?>" class="" target="_blank">
									<img src="<?php echo( ( ! empty( $link ) && '#' !== $link ) ? esc_attr( $link ) : esc_attr( $noimage ) ); ?>"
										 data-noimage="<?php echo esc_attr( $noimage ); ?>" height="77" alt=""/>
								</a>
							</div>
						<?php endif; ?>
						<div class="jcf-simple-file-info">
							<input type="hidden" name="<?php echo $this->get_field_name( 'simplemedia' ); ?>"
								   id="<?php echo esc_attr( $this->get_field_id( 'simplemedia' ) ); ?>" value="true">
							<input type="hidden"
								   id="<?php echo esc_attr( $this->get_field_id( 'uploaded_file' ) ); ?>"
								   name="<?php echo $this->get_field_name( 'uploaded_file' ); ?>"
								   value="<?php echo esc_attr( $value ); ?>"/>
							<p class="<?php echo esc_attr( $delete_class ); ?>"><a
										href="<?php echo esc_attr( $link ); ?>"
										target="_blank"><?php echo esc_attr( basename( $link ) ); ?></a>
							</p>
							<a href="#"
							   id="simplemedia-<?php echo esc_attr( $this->get_field_id( 'uploaded_file' ) ); ?>"
							   class="button button-large "
							   data-selected_id="<?php echo esc_attr( $this->get_field_id( 'uploaded_file' ) ); ?>"
							   data-uploader_title="<?php echo esc_attr( $upload_text ); ?>"
							   data-media_type="<?php echo( 'image' === esc_attr( $upload_type ) ? esc_attr( $upload_type ) : '' ); ?>"
							   data-uploader_button_text="<?php echo esc_attr( $upload_text ); ?>"><?php echo esc_attr( $upload_text ); ?></a>
							<script type="text/javascript">
								//create modal upload pop-up to select Media Files
								jQuery(document).ready(function () {
									var mm_<?php echo esc_attr( $this->get_field_id( 'uploaded_file', '_' ) ); ?> = new JcfMediaModal({
										calling_selector: "#simplemedia-<?php echo esc_attr( $this->get_field_id( 'uploaded_file' ) ); ?>",
										cb: function (attachment) {
											JcfSimpleMedia.selectMedia(attachment,
												"<?php echo esc_html( $this->get_field_id( 'uploaded_file' ) ); ?>", "<?php echo( ( 'image' === $upload_type ) ? 'image' : 'all' ); ?>"
											);
										}
									});
								});
							</script>
							<a href="#"
							   class="button button-large jcf_simple_delete<?php echo esc_attr( $delete_class ); ?>"
							   data-field_id="<?php echo esc_attr( $this->get_field_id( 'uploaded_file' ) ); ?>"><?php esc_html_e( 'Delete', \JustCustomFields::TEXTDOMAIN ); ?></a>
						</div>
					</div>
				</div>
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
		$instance         = wp_parse_args( (array) $this->instance, array(
			'title'       => '',
			'type'        => 'file',
			'autoresize'  => '',
			'description' => '',
		) );
		$instance['type'] = ( isset( $this->instance['type'] ) ) ? $this->instance['type'] : 'file';
		$title            = esc_attr( $instance['title'] );
		$type             = $instance['type'];
		$description      = esc_html( $instance['description'] );
		?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_html_e( 'Title:', \JustCustomFields::TEXTDOMAIN ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"
				   name="<?php echo $this->get_field_name( 'title' ); ?>" type="text"
				   value="<?php echo esc_attr( $title ); ?>"/>
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'type' ) ); ?>"><?php esc_html_e( 'Type of files:', \JustCustomFields::TEXTDOMAIN ); ?></label>
			<select class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'type' ) ); ?>"
					name="<?php echo $this->get_field_name( 'type' ); ?>">
				<option value="file" <?php selected( 'file', $type ); ?>><?php esc_html_e( 'All', \JustCustomFields::TEXTDOMAIN ); ?></option>
				<option value="image" <?php selected( 'image', $type ); ?>><?php esc_html_e( 'Only Images', \JustCustomFields::TEXTDOMAIN ); ?></option>
			</select>
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'description' ) ); ?>"><?php esc_html_e( 'Description:', \JustCustomFields::TEXTDOMAIN ); ?></label>
			<textarea name="<?php echo $this->get_field_name( 'description' ); ?>"
					  id="<?php echo esc_attr( $this->get_field_id( 'description' ) ); ?>" cols="20" rows="4"
					  class="widefat"><?php echo esc_html( $description ); ?></textarea></p>
		<?php
	}

	/**
	 *  Save field on post edit form
	 *
	 * @param array $_values values.
	 *
	 * @return array
	 */
	public function save( $_values ) {
		$value = 0;
		if ( empty( $_values ) ) {
			return $value;
		}
		if ( isset( $_values['uploaded_file'] ) && intval( $_values['uploaded_file'] ) ) {
			return $_values['uploaded_file'];
		}

		return $value;
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
		$instance['type']        = strip_tags( $new_instance['type'] );
		$instance['description'] = strip_tags( $new_instance['description'] );

		return $instance;
	}

	/**
	 *    Add custom scripts
	 */
	public function add_js() {
		global $pagenow, $wp_version, $post_id;
		// only load on select pages.
		if ( ! in_array( $pagenow, array(
			'post-new.php',
			'post.php',
			'media-upload-popup',
			'edit-tags.php',
			'term.php',
		) )
		) {
			return;
		}
		wp_enqueue_media( array( 'post' => ( $post_id ? $post_id : null ) ) );
		wp_enqueue_script( 'jcf-simpleupload-modal', jcf_plugin_url( 'components/simplemedia/assets/simplemedia-modal.js' ), array(
			'jquery',
			'media-models',
			'jcf_edit_post',
		) );

		// add text domain if not registered with another component.
		global $wp_scripts;
		if ( empty( $wp_scripts->registered['jcf_fields_group'] ) && empty( $wp_scripts->registered['jcf_related_content'] ) ) {
			wp_localize_script( 'jcf_simplemedia', 'jcf_textdomain', jcf_get_language_strings() );
		}
	}
	/**
	 *    Add custom styles
	 */
	public function add_css() {
		wp_register_style( 'jcf_simplemedia', jcf_plugin_url( 'components/simplemedia/assets/simplemedia.css' ), array(
			'thickbox',
			'jcf_edit_post',
		) );
		wp_enqueue_style( 'jcf_simplemedia' );
	}

	/**
	 * Print field values inside the shortcode
	 *
	 * @param array $args    shortcode args.
	 *
	 * @return mixed
	 */
	public function shortcode_value( $args ) {
		if ( empty( $this->entry ) ) {
			return '';
		}

		$size  = isset( $args['size'] ) ? $args['size'] : 'thumbnail';
		$value = wp_get_attachment_image( $this->entry, $size );

		return $args['before_value'] . $value . $args['after_value'];
	}

}

?>
