<?php

/**
 *	function to get link to the thumbnail script
 */
function jcf_get_thumb_path( $image, $size = '100x77' ){
	$dirname = str_replace('\\', '/', dirname(__FILE__));
	$cachedir = array_shift( explode('/plugins/', $dirname) ) . '/uploads/jcfupload';
	
	$new_size = explode('x', $size);
	
	// check file extension
	$filetype = wp_check_filetype($image);
	if( empty($filetype['ext']) ){
		return '#';
	}
	$ext = $filetype['ext'];
	
	// check if thumb already exists:
	$hash = md5($image.$new_size[0].'x'.$new_size[1]);
	$thumbfile = $cachedir . '/' . $hash . '.' . $ext;
	if( is_file($thumbfile) ){
		return get_bloginfo('wpurl') . '/wp-content/uploads/jcfupload/' . basename($thumbfile);
	}
	else{
		return get_bloginfo('wpurl') . '/wp-content/plugins/just-custom-fields/components/uploadmedia/thump.php?image='.rawurlencode($image).'&amp;size='.$size;
	}
}

/**
 *	Upload media field
 */
class Just_Field_Upload extends Just_Field{
	
	function Just_Field_Upload(){

		$field_ops = array( 'classname' => 'field_uploadmedia' );
		$this->Just_Field( 'uploadmedia', __('Upload Media', JCF_TEXTDOMAIN), $field_ops);
		
		add_action('admin_head' , array($this , 'add_MediaUploader_js'));
	}
	
	/**
	 *	draw field on post edit form
	 *	you can use $this->instance, $this->entry
	 */
	function field( $args ) {
		extract( $args );
		
		echo $before_widget;
		echo $before_title . $this->instance['title'] . $after_title;
			
		$del_image = WP_PLUGIN_URL.'/just-custom-fields/components/uploadmedia/assets/jcf-delimage.png';
		$noimage = $image = WP_PLUGIN_URL.'/just-custom-fields/components/uploadmedia/assets/jcf-noimage100x77.jpg';
		
		$upload_text = __('Upload', JCF_TEXTDOMAIN);
		$delete_class = ' jcf-hide';
		
		$upload_type = $this->instance['type'];

		$value = '#';
			
		$img_title = $img_descr = '';
		
		if(empty($this->entry)) $this->entry = array('0' => '');
		// add null element for etalon copy
		$entries = array( '00' => '' ) + (array)$this->entry;
		?>
		<div class="jcf-upload-field jcf-upload-type-<?php echo $upload_type; ?> jcf-field-container">
			<?php
			foreach($entries as $key => $entry) : 
				if( !empty($entry) ){
					$value = esc_attr( $entry['image'] );
					$image = jcf_get_thumb_path( $entry['image'] );
					$upload_text = ($upload_type == 'image')? __('Update image', JCF_TEXTDOMAIN) : __('Update file', JCF_TEXTDOMAIN);
					$delete_class = '';
	
					$img_title = esc_attr( @$entry['title'] );
					$img_descr = esc_attr( @$entry['description'] );
				}
			?>
			<div class="jcf-upload-row<?php if('00' === $key) echo ' jcf-hide'; ?>">
				<div class="jcf-upload-container">
					<?php if( $upload_type == 'image' ) : ?>
					<div class="jcf-upload-image">
						<a href="<?php echo $value; ?>" class="jcf-btn" target="_blank"><img src="<?php echo $image; ?>" height="77" alt="" /></a>
					</div>
					<?php endif; ?>
					<div class="jcf-upload-file-info">
						<input type="hidden"
							   id="<?php echo $this->get_field_id_l2('uploaded_file', $key); ?>"
								name="<?php echo $this->get_field_name_l2('uploaded_file', $key); ?>"
								value="<?php echo $value; ?>" />
						<p class="<?php echo $delete_class; ?>"><a href="<?php echo $value; ?>" target="_blank"><?php echo basename($value); ?></a></p>
						<?php if($this->instance['alt_title']) : ?>
							<p><?php _e('Title:', JCF_TEXTDOMAIN); ?> <br/>
								<input type="text" value="<?php echo $img_title; ?>" 
									id="<?php echo $this->get_field_id_l2('alt_title', $key); ?>" 
									name="<?php echo $this->get_field_name_l2('alt_title', $key); ?>"></p>
						<?php endif; ?>
						<?php if($this->instance['alt_descr']) : ?>
							<p><?php _e('Description:', JCF_TEXTDOMAIN); ?> <br/>
								<textarea cols="95" row="3"
									id="<?php echo $this->get_field_id_l2('alt_descr', $key); ?>" 
									name="<?php echo $this->get_field_name_l2('alt_descr', $key); ?>"
									><?php echo $img_descr; ?></textarea></p>
						<?php endif; ?>
						<a href="media-upload.php?jcf_media=true&amp;type=<?php echo $upload_type; ?>&amp;TB_iframe=true" class="jcf-btn jcf_upload"
								rel="<?php echo $this->get_field_id_l2('uploaded_file', $key); ?>"><?php echo $upload_text; ?></a>
						<a href="#" class="jcf-btn jcf_delete<?php echo $delete_class; ?>"><?php _e('Delete', JCF_TEXTDOMAIN); ?></a>
					</div>
				</div>
				<div class="jcf-delete-layer">
					<img src="<?php echo $del_image; ?>" alt="" />
					<input type="hidden" id="<?php echo $this->get_field_id_l2('delete', $key); ?>" name="<?php echo $this->get_field_name_l2('delete', $key); ?>" value="" />
					<a href="#" class="jcf-btn jcf_cancel"><?php _e('Cancel', JCF_TEXTDOMAIN); ?></a><br/>
				</div>
			</div>
			<?php endforeach; ?>
			<a href="#" class="jcf-btn jcf_add_more"><?php if($upload_type == 'image') _e('+ Add another image', JCF_TEXTDOMAIN); else _e('+ Add another file', JCF_TEXTDOMAIN); ?></a>
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
		$values = array();
		if(empty($_values)) return $values;
	
		// get autoresize property.
		$autoresize = '';
		if( !empty($this->instance['autoresize']) ){
			$autoresize = explode('x', $this->instance['autoresize']);
			if( count($autoresize) != 2 ) $autoresize = '';
		}
	
		// remove etalon element
		if(isset($_values['00'])) 
			unset($_values['00']);
		
		foreach($_values as $key => $params){
			if(!empty($params['delete'])){
				continue;
			}
			if(!empty($params['uploaded_file']) && $params['uploaded_file'] != '#'){
				$value = $params['uploaded_file'];
				
				$file = array(
					'image' => $value,
					'title' => $params['alt_title'],
					'description' => $params['alt_descr'],
				);
				
				if(!empty($autoresize)){
					// wordpress resize
					$imagepath = ABSPATH . str_replace( get_bloginfo('home').'/', '', $value );
					$thumbpath = image_resize($imagepath, $autoresize[0], $autoresize[1]);
					// get link
					if( is_string($thumbpath) && $thumbpath != '' ){
						@chmod($thumbpath, 0777);
						$value = get_bloginfo('home').'/' . str_replace(ABSPATH, '', $thumbpath);
						$file['image'] = $value;
					}
				}
				
				$values[$key] = $file;
			}
		}
		$values = array_values($values);
		//pa($values,1);
		return $values;
	}
	
	/**
	 *	update instance (settings) for current field
	 */
	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		
		$instance['title'] 			= strip_tags($new_instance['title']);
		$instance['type'] 			= strip_tags($new_instance['type']);
		$instance['autoresize'] 	= strip_tags($new_instance['autoresize']);
		$instance['description'] 	= strip_tags($new_instance['description']);
		$instance['alt_title'] 		= strip_tags(@$new_instance['alt_title']);
		$instance['alt_descr'] 		= strip_tags(@$new_instance['alt_descr']);
		
		return $instance;
	}

	/**
	 *	print settings form for field
	 */	
	function form( $instance ) {
		//Defaults
		$instance['type'] = ($instance['type'])? $instance['type'] : 'file';
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
		<p>
			<label for="<?php echo $this->get_field_id('autoresize'); ?>"><?php _e('Auto resize', JCF_TEXTDOMAIN); ?></label> 
			<input id="<?php echo $this->get_field_id('autoresize'); ?>" name="<?php echo $this->get_field_name('autoresize'); ?>" type="text" value="<?php echo $autoresize; ?>" />
			<br/><small><?php _e('Set dimensions to autoresize (in px).<br/><i>Example: 200x160', JCF_TEXTDOMAIN); ?></i></small>
		</p>

		<p><label for="<?php echo $this->get_field_id('alt_title'); ?>"><input type="checkbox" id="<?php echo $this->get_field_id('alt_title'); ?>" name="<?php echo $this->get_field_name('alt_title'); ?>" <?php if(!empty($instance['alt_title'])) echo 'checked="checked"'; ?> /> <?php _e('Enable alternative text', JCF_TEXTDOMAIN); ?></label></p>
		<p><label for="<?php echo $this->get_field_id('alt_descr'); ?>"><input type="checkbox" id="<?php echo $this->get_field_id('alt_descr'); ?>" name="<?php echo $this->get_field_name('alt_descr'); ?>" <?php if(!empty($instance['alt_descr'])) echo 'checked="checked"'; ?> /> <?php _e('Enable alternative description', JCF_TEXTDOMAIN); ?></label></p>

		<p><label for="<?php echo $this->get_field_id('description'); ?>"><?php _e('Description:', JCF_TEXTDOMAIN); ?></label> <textarea name="<?php echo $this->get_field_name('description'); ?>" id="<?php echo $this->get_field_id('description'); ?>" cols="20" rows="4" class="widefat"><?php echo $description; ?></textarea></p>
		<?php
	}

	/**
	 *	custom get_field functions to add one more deep level
	 */
	function get_field_id_l2( $field, $number ){
		return $this->get_field_id( $number . '-' . $field );
	}

	function get_field_name_l2( $field, $number ){
		return $this->get_field_name( $number . '][' . $field );
	}
	
	/**
	 *	add custom scripts
	 */
	function add_js(){
		wp_register_script(
				'jcf_uploadmedia',
				WP_PLUGIN_URL.'/just-custom-fields/components/uploadmedia/assets/uploadmedia.js',
				array('jquery','media-upload','thickbox')
			);
		wp_enqueue_script('jcf_uploadmedia');

		// add text domain if not registered with another component
		global $wp_scripts;
		if( empty($wp_scripts->registered['jcf_fields_group']) && empty($wp_scripts->registered['jcf_related_content']) ){
			wp_localize_script( 'jcf_uploadmedia', 'jcf_textdomain', jcf_get_language_strings() );
		}
	}
	
	function add_css(){
		wp_register_style('jcf_uploadmedia',
				WP_PLUGIN_URL.'/just-custom-fields/components/uploadmedia/assets/uploadmedia.css',
				array('thickbox'));
		wp_enqueue_style('jcf_uploadmedia');
	}
	
	/**
	 *	this add js script to the Upload Media wordpress popup
	 */
	function add_MediaUploader_js(){
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