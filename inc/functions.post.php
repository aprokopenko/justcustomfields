<?php	
	
	/**
	 *	callback function for hook "add_meta_boxes"
	 *	call add_meta_box for each fieldset
	 */
	function jcf_post_load_custom_fields( $post_type = '' ){
		// set global post type
		jcf_set_post_type($post_type);
		
		// get read settings
		$jcf_read_settings = jcf_get_read_settings();

		// get fieldsets
		if( $jcf_read_settings == JCF_CONF_SOURCE_DB ){
			$fieldsets = jcf_fieldsets_get();
			$field_settings = jcf_field_settings_get();		
		}
		else{
			$jcf_settings = jcf_get_all_settings_from_file();
			$fieldsets = $jcf_settings['fieldsets'][ $post_type ];
			$field_settings = $jcf_settings['field_settings'][ $post_type ];
		}		

		if(!empty($fieldsets)){
			// remove fieldsets without fields
			foreach($fieldsets as $f_id => $fieldset){
				// check $enabled; add custom js/css for components
				foreach($fieldset['fields'] as $field_id => $enabled){
					if( !$enabled ){
						unset($fieldset['fields'][$field_id]);
						continue;
					}
					$field_obj = jcf_init_field_object($field_id, $fieldset['id']);
					$field_obj->do_add_js();
					$field_obj->do_add_css();
				}
				// if all fields disabled -> remove fieldset
				if( empty($fieldset['fields']) ){
					unset($fieldsets[$f_id]);
				}
			}
			if(!empty($field_obj)) unset($field_obj);

			if( empty($fieldsets) ) return false;

			// add custom styles and scripts
			add_action('admin_print_styles', 'jcf_edit_post_styles');
			add_action('admin_print_scripts', 'jcf_edit_post_scripts');

			foreach($fieldsets as $f_id => $fieldset){
				add_meta_box('jcf_fieldset-'.$f_id, $fieldset['title'], 'jcf_post_show_custom_fields', $post_type, 'advanced', 'default', array($fieldset) );
			}
		}
	}
	
	/**
	 *	prepare and print fieldset html.
	 *	- load each field class
	 *	- print form from each class
	 */
	function jcf_post_show_custom_fields( $post = NULL, $box = NULL ){
		$fieldset = $box['args'][0];

		jcf_print_shortcodes_modal();

		foreach($fieldset['fields'] as $field_id => $enabled){
			if( !$enabled ) continue;

			$field_obj = jcf_init_field_object($field_id, $fieldset['id']);
			$field_obj->set_post_ID( $post->ID );

			echo '<div id="jcf_field-' . $field_id . '" class="jcf_edit_field ' . $field_obj->field_options['classname'] . '">'."\r\n";
			$args = $field_obj->field_options;
			$args['after_title'] .= '<div class="jcf-get-shortcode" rel="' . $field_obj->slug . '">'
					. '<span class="dashicons dashicons-editor-help wp-ui-text-highlight"></span>'
					. '</div>'."\r\n";
			$field_obj->field( $args );

			echo "\r\n </div> \r\n";
		}
		unset($field_obj);
		
		// Use nonce for verification
		global $jcf_noncename;
		if( empty($jcf_noncename) ){
			wp_nonce_field( plugin_basename( __FILE__ ), 'justcustomfields_noncename' );
			$jcf_noncename = true;
		}
	}
	
	/**
	 *	callback function for "save_post" action
	 */
	function jcf_post_save_custom_fields( $post_ID = 0, $post = null ){

		// do not save anything on autosave
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) 
			return;
		
		// verify this came from the our screen and with proper authorization,
		// because save_post can be triggered at other times
		if ( empty($_POST['justcustomfields_noncename']) || !wp_verify_nonce( $_POST['justcustomfields_noncename'], plugin_basename( __FILE__ ) ) )
			return;
		
		// check permissions
		$permission = ('page' == $_POST['post_type'])? 'edit_page' : 'edit_post';
		if ( !current_user_can( $permission, $post_ID ) ) return;
		
		// OK, we're authenticated: we need to find and save the data
		
		// set global post type
		jcf_set_post_type( $_POST['post_type'] );

		// get fieldsets
		$fieldsets = jcf_fieldsets_get();

		// create field class objects and call save function
		foreach($fieldsets as $f_id => $fieldset){
			foreach($fieldset['fields'] as $field_id => $tmp){
				$field_obj = jcf_init_field_object($field_id, $fieldset['id']);
				$field_obj->set_post_ID( $post->ID );

				$field_obj->do_save();
			}
		}

		return false;
	}
	
	/**
	 *	add custom scripts to post edit page
	 */
	function jcf_edit_post_scripts(){

		wp_register_script(
				'jcf_edit_post',
				WP_PLUGIN_URL.'/just-custom-fields/assets/edit_post.js',
				array('jquery')
			);
		wp_enqueue_script('jcf_edit_post');

		do_action('jcf_admin_edit_post_scripts');
	}

	/**
	 *	add custom styles to post edit page
	 */
	function jcf_edit_post_styles(){
		wp_register_style('jcf_edit_post', WP_PLUGIN_URL.'/just-custom-fields/assets/edit_post.css');
		wp_enqueue_style('jcf_edit_post');
		
		do_action('jcf_admin_edit_post_styles');
	}
	
	/**
	 * get modal window for getting shortcodes
	 */
	function jcf_print_shortcodes_modal(){
		?>
		<div class="jcf_shortcodes_tooltip" >
			<div class="jcf_inner_box">
				<h3 class="header"><?php _e('Usage guidelines for field ', JCF_TEXTDOMAIN); ?> 
					<span class="field-name"></span> 
					<a href="#" class="jcf_shortcodes_tooltip-close"><span class="media-modal-icon"></span></a>
				</h3>
				<div class="jcf_inner_content">
					<fieldset class="shortcode_usage">
						<legend><?php _e('Inside the Editor', JCF_TEXTDOMAIN); ?></legend>
						<span class="fieldset-description"><?php _e('To insert the value into your post editor, please copy and paste the code examples below to your editor.', JCF_TEXTDOMAIN); ?></span>
						
						<span class="jcf-relative"><input type="text" readonly="readonly" class="jcf-shortcode jcf-shortcode-value" value="" /><a href="#" class="copy-to-clipboard" title="Copy to clipboard"></a></span><br />
						<small><?php _e('optional parameters: class="myclass" id="myid" post_id="123" label="yes"', JCF_TEXTDOMAIN); ?></small><br /><br />
					</fieldset>
					<fieldset class="template_usage">
						<legend><?php _e('Inside your Templates ', JCF_TEXTDOMAIN); ?></legend>
						<span class="fieldset-description"><?php _e('To print the value or label inside your template (for example in single.php) please use the examples below:', JCF_TEXTDOMAIN); ?></span>
						
						<span class="jcf-relative"><input type="text" readonly="readonly" class="jcf-shortcode jcf-template-value" value=""/><a href="#" class="copy-to-clipboard" title="Copy to clipboard"></a></span><br />
						<small><?php _e('optional parameters: class="myclass" id="myid" post_id="123" label="yes"', JCF_TEXTDOMAIN); ?></small><br /><br />
					</fieldset>
				</div>
			</div>
		</div>
		<?php
	}