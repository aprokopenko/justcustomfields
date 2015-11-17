<?php

/**
 *	Simple Upload media field
 */
class Just_Field_Simple_Media extends Just_Field
{
	public static $compatibility = "4.0+";


	public function __construct(){

		$field_ops = array( 'classname' => 'field_simplemedia' );
		parent::__construct( 'simplemedia', __('Simple Media', JCF_TEXTDOMAIN), $field_ops);
			
	}
	
	/**
	 *	draw field on post edit form
	 *	you can use $this->instance, $this->entry
	 */
	public function field( $args ) {
		extract( $args );
		
		echo $before_widget;
		echo $before_title . $this->instance['title'] . $after_title;
			
		$del_image = WP_PLUGIN_URL.'/just-custom-fields/components/simplemedia/assets/jcf-delimage.png';
		$noimage = $image = WP_PLUGIN_URL.'/just-custom-fields/components/simplemedia/assets/jcf-noimage100x77.jpg';
		
		$delete_class = ' jcf-hide';
		
		$upload_type = $this->instance['type'];
		$upload_text = ($upload_type == 'image')? __('Select image', JCF_TEXTDOMAIN) : __('Select file', JCF_TEXTDOMAIN);

		$value = $link = '#';
		
		if(empty($this->entry)) $this->entry = 0;
		
?>
		<div class="jcf-simple-field jcf-simple-type-<?php echo $upload_type; ?> ">
<?php
			if( !empty($this->entry) ){
				$value = esc_attr( $this->entry );

				$link = wp_get_attachment_url($this->entry);
				$upload_text = ($upload_type == 'image')? __('Update image', JCF_TEXTDOMAIN) : __('Update file', JCF_TEXTDOMAIN);
				$delete_class = '';	
			}
?>
			<div class="jcf-simple-row">
				<div class="jcf-simple-container">
					<?php if( $upload_type == 'image' ) : ?>
						<div class="jcf-simple-image">
							<a href="<?php echo $link; ?>" class="" target="_blank"><img src="<?php echo ((!empty($link) && $link!='#')? $link : $noimage); ?>" height="77" alt="" /></a>
						</div>
					<?php endif; ?>
					<div class="jcf-simple-file-info">
						<input type="hidden" name="<?php echo $this->get_field_name('simplemedia'); ?>" id="<?php echo $this->get_field_id('simplemedia'); ?>" value="true">
						<input type="hidden"
							   id="<?php echo $this->get_field_id('uploaded_file'); ?>"
								name="<?php echo $this->get_field_name('uploaded_file'); ?>"
								value="<?php echo $value; ?>" />
						<p class="<?php echo $delete_class; ?>"><a href="<?php echo $link; ?>" target="_blank"><?php echo basename($link); ?></a></p>
							<a href="#"  id="simplemedia-<?php echo $this->get_field_id('uploaded_file'); ?>" class="button button-large "
							   data-selected_id="<?php echo $this->get_field_id('uploaded_file'); ?>" 
							   data-uploader_title="<?php echo $upload_text; ?>" 
							   data-media_type="<?php echo ($upload_type == 'image'?$upload_type:''); ?>"
							   data-uploader_button_text="<?php echo $upload_text; ?>"><?php echo $upload_text; ?></a>
						<script type="text/javascript">
								//create modal upload pop-up to select Media Files
								jQuery(document).ready(function(){
									var mm_<?php echo $this->get_field_id('uploaded_file', '_'); ?> = new JcfMediaModal({
										calling_selector : "#simplemedia-<?php echo $this->get_field_id('uploaded_file'); ?>",
										cb : function(attachment){
											JcfSimpleMedia.selectMedia(attachment, 
												"<?php echo $this->get_field_id('uploaded_file'); ?>", "<?php echo (( $upload_type == 'image' )?'image':'all');?>"
											);
										}
									});
								});
							</script>
						<a href="#" class="button button-large jcf_simple_delete<?php echo $delete_class; ?>" data-field_id="<?php echo $this->get_field_id('uploaded_file'); ?>"><?php _e('Delete', JCF_TEXTDOMAIN); ?></a>
					</div>
				</div>
				<div class="jcf-delete-layer">
					<img src="<?php echo $del_image; ?>" alt="" />
					<a href="#" class="button button-large jcf_simple_cancel" data-field_id="<?php echo $this->get_field_id('uploaded_file'); ?>"><?php _e('Undo delete', JCF_TEXTDOMAIN); ?></a><br/>
				</div>
			</div>
		</div>

		<?php
		if( $this->instance['description'] != '' )
			echo '<p class="description">' . $this->instance['description'] . '</p>';
		
		echo $after_widget;
		
		return true;
	}
	
	/**
	 *	save field on post edit form
	 */
	public function save( $_values ){
		$value = 0;
		if( empty($_values) ) return $value;
		if( isset($_values['uploaded_file']) && intval($_values['uploaded_file']) ) return $_values['uploaded_file'];
		return $value;
	}
		
	/**
	 *	update instance (settings) for current field
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		
		$instance['title'] 			= strip_tags($new_instance['title']);
		$instance['type'] 			= strip_tags($new_instance['type']);
		$instance['description'] 	= strip_tags($new_instance['description']);
		
		return $instance;
	}

	/**
	 *	print settings form for field
	 */	
	public function form( $instance ) {
		//Defaults
		$instance['type'] = (isset($instance['type']))? $instance['type'] : 'file';
		$instance = wp_parse_args( (array) $instance,
				array( 'title' => '', 'type' => 'file', 'autoresize' => '',
					  'description' => ''));

		$title = esc_attr( $instance['title'] );
		$type = $instance['type'];
		$autoresize = esc_attr( $instance['autoresize'] );
		$description = esc_html($instance['description']);
		?>
		<p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:', JCF_TEXTDOMAIN); ?></label> <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" /></p>
		<p>
			<label for="<?php echo $this->get_field_id('type'); ?>"><?php _e('Type of files:', JCF_TEXTDOMAIN); ?></label>
			<select class="widefat" id="<?php echo $this->get_field_id('type'); ?>" name="<?php echo $this->get_field_name('type'); ?>">
				<option value="file" <?php selected('file', $type);?>><?php _e('All', JCF_TEXTDOMAIN); ?></option>
				<option value="image" <?php selected('image', $type);?>><?php _e('Only Images', JCF_TEXTDOMAIN); ?></option>
			</select>
		</p>
		<p><label for="<?php echo $this->get_field_id('description'); ?>"><?php _e('Description:', JCF_TEXTDOMAIN); ?></label> <textarea name="<?php echo $this->get_field_name('description'); ?>" id="<?php echo $this->get_field_id('description'); ?>" cols="20" rows="4" class="widefat"><?php echo $description; ?></textarea></p>
		<?php
	}
	
	/**
	 *	add custom scripts
	 */
	public function add_js(){
		global $pagenow, $wp_version, $post_ID;
		// only load on select pages 
		if ( ! in_array( $pagenow, array( 'post-new.php', 'post.php', 'media-upload-popup' ) ) ) return;
		wp_enqueue_media( array( 'post' => ( $post_ID ? $post_ID : null ) ) );
		wp_enqueue_script( "jcf-simpleupload-modal", WP_PLUGIN_URL.'/just-custom-fields/components/simplemedia/assets/simplemedia-modal.js', array( 'jquery', 'media-models') );				

		// add text domain if not registered with another component
		global $wp_scripts;
		if( empty($wp_scripts->registered['jcf_fields_group']) && empty($wp_scripts->registered['jcf_related_content']) ){
			wp_localize_script( 'jcf_simplemedia', 'jcf_textdomain', jcf_get_language_strings() );
		}
	}
	
	public function add_css(){
		wp_register_style('jcf_simplemedia',
				WP_PLUGIN_URL.'/just-custom-fields/components/simplemedia/assets/simplemedia.css',
				array('thickbox'));
		wp_enqueue_style('jcf_simplemedia');
	}
	
	
}
?>
