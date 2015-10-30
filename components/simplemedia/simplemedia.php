<?php

/**
 *	Upload media field
 */
class Just_Simple_Media extends Just_Field{
	
	function Just_Simple_Media(){

		$field_ops = array( 'classname' => 'field_simplemedia' );
		$this->Just_Field( 'simplemedia', __('Simple Media Upload', JCF_TEXTDOMAIN), $field_ops);
		
		//add_action('admin_head' , array($this , 'add_admin_js'));
		//add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
		
	}
	
	/**
	 *	draw field on post edit form
	 *	you can use $this->instance, $this->entry
	 */
	function field( $args ) {
		
		global $wp_version;
		extract( $args );
		//var_dump($args);
		echo $before_widget;
		echo $before_title . $this->instance['title'] . $after_title;
			
		$del_image = WP_PLUGIN_URL.'/just-custom-fields/components/simplemedia/assets/jcf-delimage.png';
		$noimage = $image = WP_PLUGIN_URL.'/just-custom-fields/components/simplemedia/assets/jcf-noimage100x77.jpg';
		
		$upload_text = __('Upload', JCF_TEXTDOMAIN);
		$delete_class = ' jcf-hide';
		
		$upload_type = $this->instance['type'];

		$value = $link = '#';
		//var_dump($this->entry);
		if(empty($this->entry)) $this->entry = 0;
		
?>
		<div class="jcf-simple-field jcf-simple-type-<?php echo $upload_type; ?> jcf-field-container">
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
							<a href="<?php echo $link; ?>" class="jcf-btn" target="_blank"><img src="<?php echo $link; ?>" height="77" alt="" /></a>
						</div>
					<?php endif; ?>
					<div class="jcf-simple-file-info">
						<input type="hidden"
							   id="<?php echo $this->get_field_id('uploaded_file'); ?>"
								name="<?php echo $this->get_field_name('uploaded_file'); ?>"
								value="<?php echo $value; ?>" />
						<p class="<?php echo $delete_class; ?>"><a href="<?php echo $link; ?>" target="_blank"><?php echo basename($link); ?></a></p>
							<a href="#"  id="simpleselect-<?php echo $this->get_field_id('uploaded_file'); ?>" class="jcf-btn"
							   data-selected_id="<?php echo $this->get_field_id('uploaded_file'); ?>" 
							   data-uploader_title="<?php echo $upload_text; ?>" 
							   data-media_type="<?php echo ($upload_type == 'image'?$upload_type:''); ?>"
							   data-uploader_button_text="<?php echo $upload_text; ?>"><?php echo $upload_text; ?></a>
							<script>
								var mm_<?php echo md5($this->get_field_id('uploaded_file')); ?> = new MediaModal({
									calling_selector : "#simpleselect-<?php echo $this->get_field_id('uploaded_file'); ?>",
									cb : function(attachment){
										SimpleMedia.selectMedia(attachment, 
											"<?php echo $this->get_field_id('uploaded_file'); ?>, \n\
											<?php echo (( $upload_type == 'image' )?'image':'all');?>"
										);
									}
								});
							</script>
						<a href="" class="jcf-btn"
						<a href="#" class="jcf-btn jcf_delete<?php echo $delete_class; ?>"><?php _e('Delete', JCF_TEXTDOMAIN); ?></a>
					</div>
				</div>
				<div class="jcf-delete-layer">
					<img src="<?php echo $del_image; ?>" alt="" />
					<input type="hidden" id="<?php echo $this->get_field_id('delete'); ?>" name="<?php echo $this->get_field_name('delete'); ?>" value="" />
					<a href="#" class="jcf-btn jcf_cancel"><?php _e('Cancel', JCF_TEXTDOMAIN); ?></a><br/>
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
	function save( $_values ){
		$value = 0;
		
		if( empty($_values) ) return $value;
		if( ! empty($_values['delete']) ) return $value;
		if( ! $_values['uploaded_file'] ) return $value;
		if( intval($_values['uploaded_file']) ) return $_values['uploaded_file'];
		return $this->get_media_id($_values['uploaded_file']);
	}
	
	/**
	 * get media id by url
	 * @global type $wpdb
	 * @param type $url
	 * @return type
	 */
	function get_media_id ( $url ) {
		global $wpdb;
		$media_id = 0;
		preg_match( '|' . get_bloginfo('url') . '|i', $url, $matches );
		if ( isset( $matches ) and 0 < count( $matches ) ) {
			$url = preg_replace( '/([^?]+).*/', '\1', $url ); 
			$guid = preg_replace( '/(.+)-\d+x\d+\.(\w+)/', '\1.\2', $url ); 
			$media_id = $wpdb->get_var( $wpdb->prepare( "SELECT `ID` FROM $wpdb->posts WHERE `guid` = '%s'", $guid ) );
			if ( $media_id ) {
				$media_id = intval( $media_id );
			}
		} 
		return $media_id;
	}
	
	/**
	 *	update instance (settings) for current field
	 */
	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		
		$instance['title'] 			= strip_tags($new_instance['title']);
		$instance['type'] 			= strip_tags($new_instance['type']);
		$instance['description'] 	= strip_tags($new_instance['description']);
		
		return $instance;
	}

	/**
	 *	print settings form for field
	 */	
	function form( $instance ) {
		//Defaults
		$instance['type'] = (isset($instance['type']))? $instance['type'] : 'file';
		$instance = wp_parse_args( (array) $instance,
				array( 'title' => '', 'type' => 'file', 'autoresize' => '',
					  'description' => __('Press "Upload" button, upload file or select in the library. Then choose Link "None" and "Full size" and press "Select File".', JCF_TEXTDOMAIN) ) );

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
	function add_js(){
		global $pagenow, $wp_version, $post_ID;
		// only load on select pages
		if ( ! in_array( $pagenow, array( 'post-new.php', 'post.php', 'media-upload-popup' ) ) ) return;
		wp_enqueue_media( array( 'post' => ( $post_ID ? $post_ID : null ) ) );
		wp_enqueue_script( "jcf-simpleupload-modal", WP_PLUGIN_URL.'/just-custom-fields/components/simplemedia/assets/simpleselect-modal.js', array( 'jquery', 'media-models') );				

		// add text domain if not registered with another component
		global $wp_scripts;
		if( empty($wp_scripts->registered['jcf_fields_group']) && empty($wp_scripts->registered['jcf_related_content']) ){
			wp_localize_script( 'jcf_simplemedia', 'jcf_textdomain', jcf_get_language_strings() );
		}
	}
	
	function add_css(){
		wp_register_style('jcf_simplemedia',
				WP_PLUGIN_URL.'/just-custom-fields/components/simplemedia/assets/simplemedia.css',
				array('thickbox'));
		wp_enqueue_style('jcf_simplemedia');
	}
	
	
}
?>