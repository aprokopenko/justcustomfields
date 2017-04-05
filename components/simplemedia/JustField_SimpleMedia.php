<?php

namespace jcf\components\simplemedia;

use jcf\core;

/**
 * 	Simple Upload media field
 */
class JustField_SimpleMedia extends core\JustField
{
	public static $compatibility = "4.0+";

	public function __construct()
	{
		$field_ops = array( 'classname' => 'field_simplemedia' );
		parent::__construct('simplemedia', __('Simple Media', \JustCustomFields::TEXTDOMAIN), $field_ops);
	}

	/**
	 * 	draw field on post edit form
	 * 	you can use $this->instance, $this->entry
	 */
	public function field()
	{
		$noimage = $image = jcf_plugin_url('components/simplemedia/assets/jcf-noimage100x77.jpg');
		$delete_class = ' jcf-hide';
		$upload_type = $this->instance['type'];
		$upload_text = ($upload_type == 'image') ? __('Select image', \JustCustomFields::TEXTDOMAIN) : __('Select file', \JustCustomFields::TEXTDOMAIN);
		$value = $link = '#';

		if ( empty($this->entry) )
			$this->entry = 0;
		?>
		<div id="jcf_field-<?php echo $this->id; ?>" class="jcf_edit_field <?php echo $this->fieldOptions['classname']; ?>">
			<?php echo $this->fieldOptions['before_widget']; ?>
				<?php echo $this->fieldOptions['before_title'] . esc_html($this->instance['title']) . $this->fieldOptions['after_title']; ?>
				<div class="jcf-simple-field jcf-simple-type-<?php echo $upload_type; ?> ">
					<?php
					if ( !empty($this->entry) ) {
						$value = esc_attr($this->entry);
						$link = wp_get_attachment_url($this->entry);
						$upload_text = ($upload_type == 'image') ? __('Update image', \JustCustomFields::TEXTDOMAIN) : __('Update file', \JustCustomFields::TEXTDOMAIN);
						$delete_class = '';
					}
					?>
					<div class="jcf-simple-row">
						<div class="jcf-simple-container">
						<?php if ( $upload_type == 'image' ) : ?>
							<div class="jcf-simple-image">
								<a href="<?php echo $link; ?>" class="" target="_blank">
									<img src="<?php echo ((!empty($link) && $link != '#') ? $link : $noimage); ?>" data-noimage="<?php echo $noimage; ?>" height="77" alt="" />
								</a>
							</div>
						<?php endif; ?>
							<div class="jcf-simple-file-info">
								<input type="hidden" name="<?php echo $this->getFieldName('simplemedia'); ?>" id="<?php echo $this->getFieldId('simplemedia'); ?>" value="true">
								<input type="hidden"
									   id="<?php echo $this->getFieldId('uploaded_file'); ?>"
									   name="<?php echo $this->getFieldName('uploaded_file'); ?>"
									   value="<?php echo $value; ?>" />
								<p class="<?php echo $delete_class; ?>"><a href="<?php echo $link; ?>" target="_blank"><?php echo basename($link); ?></a></p>
								<a href="#"  id="simplemedia-<?php echo $this->getFieldId('uploaded_file'); ?>" class="button button-large "
								   data-selected_id="<?php echo $this->getFieldId('uploaded_file'); ?>" 
								   data-uploader_title="<?php echo $upload_text; ?>" 
								   data-media_type="<?php echo ($upload_type == 'image' ? $upload_type : ''); ?>"
								   data-uploader_button_text="<?php echo esc_attr($upload_text); ?>"><?php echo $upload_text; ?></a>
								<script type="text/javascript">
									//create modal upload pop-up to select Media Files
									jQuery(document).ready(function() {
										var mm_<?php echo $this->getFieldId('uploaded_file', '_'); ?> = new JcfMediaModal({
											calling_selector: "#simplemedia-<?php echo $this->getFieldId('uploaded_file'); ?>",
											cb: function( attachment ) {
												JcfSimpleMedia.selectMedia(attachment,
													"<?php echo $this->getFieldId('uploaded_file'); ?>", "<?php echo (( $upload_type == 'image' ) ? 'image' : 'all'); ?>"
													);
											}
										});
									});
								</script>
								<a href="#" class="button button-large jcf_simple_delete<?php echo $delete_class; ?>" data-field_id="<?php echo $this->getFieldId('uploaded_file'); ?>"><?php _e('Delete', \JustCustomFields::TEXTDOMAIN); ?></a>
							</div>
						</div>
					</div>
				</div>

				<?php if ( $this->instance['description'] != '' ) : ?>
					<p class="howto"><?php echo esc_html($this->instance['description']); ?></p>
				<?php endif; ?>
			<?php echo $this->fieldOptions['after_widget']; ?>
		</div>
		<?php
	}

	/**
	 * draw form for edit field
	 */
	public function form()
	{
		//Defaults
		$instance = wp_parse_args((array) $this->instance, array( 'title' => '', 'type' => 'file', 'autoresize' => '',
			'description' => '' ));
		$instance['type'] = (isset($this->instance['type'])) ? $this->instance['type'] : 'file';
		$title = esc_attr($instance['title']);
		$type = $instance['type'];
		$description = esc_html($instance['description']);
		?>
		<p><label for="<?php echo $this->getFieldId('title'); ?>"><?php _e('Title:', \JustCustomFields::TEXTDOMAIN); ?></label> 
			<input class="widefat" id="<?php echo $this->getFieldId('title'); ?>" name="<?php echo $this->getFieldName('title'); ?>" type="text" value="<?php echo $title; ?>" /></p>
		<p>
			<label for="<?php echo $this->getFieldId('type'); ?>"><?php _e('Type of files:', \JustCustomFields::TEXTDOMAIN); ?></label>
			<select class="widefat" id="<?php echo $this->getFieldId('type'); ?>" name="<?php echo $this->getFieldName('type'); ?>">
				<option value="file" <?php selected('file', $type); ?>><?php _e('All', \JustCustomFields::TEXTDOMAIN); ?></option>
				<option value="image" <?php selected('image', $type); ?>><?php _e('Only Images', \JustCustomFields::TEXTDOMAIN); ?></option>
			</select>
		</p>
		<p><label for="<?php echo $this->getFieldId('description'); ?>"><?php _e('Description:', \JustCustomFields::TEXTDOMAIN); ?></label> <textarea name="<?php echo $this->getFieldName('description'); ?>" id="<?php echo $this->getFieldId('description'); ?>" cols="20" rows="4" class="widefat"><?php echo $description; ?></textarea></p>
		<?php
	}

	/**
	 * 	save field on post edit form
	 */
	public function save( $_values )
	{
		$value = 0;
		if ( empty($_values) )
			return $value;
		if ( isset($_values['uploaded_file']) && intval($_values['uploaded_file']) )
			return $_values['uploaded_file'];
		return $value;
	}

	/**
	 * 	update instance (settings) for current field
	 */
	public function update( $new_instance, $old_instance )
	{
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['type'] = strip_tags($new_instance['type']);
		$instance['description'] = strip_tags($new_instance['description']);
		return $instance;
	}

	/**
	 * 	add custom scripts
	 */
	public function addJs()
	{
		global $pagenow, $wp_version, $post_ID;
		// only load on select pages 
		if ( !in_array($pagenow, array( 'post-new.php', 'post.php', 'media-upload-popup', 'edit-tags.php', 'term.php' )) )
			return;
		wp_enqueue_media(array( 'post' => ( $post_ID ? $post_ID : null ) ));
		wp_enqueue_script("jcf-simpleupload-modal", jcf_plugin_url('components/simplemedia/assets/simplemedia-modal.js'), array( 'jquery', 'media-models', 'jcf_edit_post' ));

		// add text domain if not registered with another component
		global $wp_scripts;
		if ( empty($wp_scripts->registered['jcf_fields_group']) && empty($wp_scripts->registered['jcf_related_content']) ) {
			wp_localize_script('jcf_simplemedia', 'jcf_textdomain', jcf_get_language_strings());
		}
	}

	public function addCss()
	{
		wp_register_style('jcf_simplemedia', jcf_plugin_url('components/simplemedia/assets/simplemedia.css'), array( 'thickbox', 'jcf_edit_post' ));
		wp_enqueue_style('jcf_simplemedia');
	}

	/**
	 * print field values inside the shortcode
	 *
	 * @params array $args	shortcode args
	 */
	public function shortcodeValue( $args )
	{
		if ( empty($this->entry) ) return '';

		$size = isset($args['size'])? $args['size'] : 'thumbnail';
		$value = wp_get_attachment_image($this->entry, $size);

		return $args['before_value'] . $value . $args['after_value'];
	}

}
?>
