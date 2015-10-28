<?php

/**
 *	Upload media field
 */
class Just_Simple_Upload extends Just_Field{
	
	function Just_Simple_Upload(){

		$field_ops = array( 'classname' => 'field_simplemedia' );
		$this->Just_Field( 'simplemedia', __('Simple Upload Media', JCF_TEXTDOMAIN), $field_ops);
		
		add_action('admin_head' , array($this , 'add_SimpleUploader_js'));
	}
	
	/**
	 *	draw field on post edit form
	 *	you can use $this->instance, $this->entry
	 */
	function field( $args ) {
		extract( $args );
		//var_dump($args);
		echo $before_widget;
		echo $before_title . $this->instance['title'] . $after_title;
			
		$del_image = WP_PLUGIN_URL.'/just-custom-fields/components/uploadmedia/assets/jcf-delimage.png';
		$noimage = $image = WP_PLUGIN_URL.'/just-custom-fields/components/uploadmedia/assets/jcf-noimage100x77.jpg';
		
		$upload_text = __('Upload', JCF_TEXTDOMAIN);
		$delete_class = ' jcf-hide';
		
		$upload_type = $this->instance['type'];

		$value = '#';
		//var_dump($this->entry);
		if(empty($this->entry)) $this->entry = 0;
		
?>
		<div class="jcf-upload-field jcf-upload-type-<?php echo $upload_type; ?> jcf-field-container">
<?php
		if( !empty($this->entry) ){
			$value = esc_attr( $this->entry );
			
			$link = wp_get_attachment_url($this->entry);
			$upload_text = ($upload_type == 'image')? __('Update image', JCF_TEXTDOMAIN) : __('Update file', JCF_TEXTDOMAIN);
			$delete_class = '';	
		}
?>
			<div class="jcf-upload-row">
				<div class="jcf-upload-container">
					<?php if( $upload_type == 'image' ) : ?>
						<div class="jcf-upload-image">
							<a href="<?php echo $link; ?>" class="jcf-btn" target="_blank"><img src="<?php echo $link; ?>" height="77" alt="" /></a>
						</div>
					<?php endif; ?>
					<div class="jcf-upload-file-info">
						<input type="hidden"
							   id="<?php echo $this->get_field_id('uploaded_file'); ?>"
								name="<?php echo $this->get_field_name('uploaded_file'); ?>"
								value="<?php echo $value; ?>" />
						<p class="<?php echo $delete_class; ?>"><a href="<?php echo $link; ?>" target="_blank"><?php echo basename($link); ?></a></p>
						<a href="media-upload.php?jcf_media=true&amp;type=<?php echo $upload_type; ?>&amp;TB_iframe=true" class="jcf-btn jcf_upload"
								rel="<?php echo $this->get_field_id('uploaded_file'); ?>"><?php echo $upload_text; ?></a>
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
		wp_register_script(
				'jcf_simplemedia',
				WP_PLUGIN_URL.'/just-custom-fields/components/simplemedia/assets/simplemedia.js',
				array('jquery','media-upload','thickbox')
			);
		wp_enqueue_script('jcf_simplemedia');

		// add text domain if not registered with another component
		global $wp_scripts;
		if( empty($wp_scripts->registered['jcf_fields_group']) && empty($wp_scripts->registered['jcf_related_content']) ){
			wp_localize_script( 'jcf_simplemedia', 'jcf_textdomain', jcf_get_language_strings() );
		}
	}
	
	function add_css(){
		wp_register_style('jcf_simplemedia',
				WP_PLUGIN_URL.'/just-custom-fields/components/uploadmedia/assets/uploadmedia.css',
				array('thickbox'));
		wp_enqueue_style('jcf_simplemedia');
	}
	
	/**
	 *	this add js script to the Upload Media wordpress popup
	 */
	function add_SimpleUploader_js(){
		global $pagenow;
		if ($pagenow != 'media-upload.php' || empty($_GET ['jcf_media']))
			return;
		
		// Gets the right label depending on the caller widget
		switch ($_GET ['type'])
		{
			case 'image': $button_label = __('Select Picture', JCF_TEXTDOMAIN); break;
			case 'file': $button_label = __('Select File', JCF_TEXTDOMAIN); break;
			default: $button_label = __('Insert into Post', JCF_TEXTDOMAIN); break;
		}
		// Overrides the label when displaying the media uploader panels
		?>
			<script type="text/javascript">
				jQuery(document).ready(function(){
					jQuery('#media-items').bind('DOMSubtreeModified' , function(){
						jQuery('td.savesend input[type="submit"]').val("<?php echo $button_label?>");
					});
				});
			</script>
		<?php
	}
	
}
?>