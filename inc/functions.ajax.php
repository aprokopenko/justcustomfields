<?php
	
	// add fieldset form process
	function jcf_ajax_add_fieldset(){
		$title = strip_tags(trim($_POST['title']));
		if( empty($title) ){
			jcf_ajax_reposnse( array('status' => "0", 'error'=>__('Title field is required.', JCF_TEXTDOMAIN)) );
		}
		
		$slug = preg_replace('/[^a-z0-9\-\_\s]/i', '', $title);
		$trimed_slug = trim($slug);
		if( $trimed_slug == '' ){
			$slug = 'jcf-fieldset-'.rand(0,10000);
		}
		else{
			$slug = sanitize_title( $title );
		}
		$jcf_read_settings = jcf_get_read_settings();
		if( !empty($jcf_read_settings) && ($jcf_read_settings == 'theme' OR $jcf_read_settings == 'global') ){
			$jcf_settings = jcf_get_all_settings_from_file();
			$post_type = jcf_get_post_type();
			$fieldsets = $jcf_settings['fieldsets'][$post_type];
			if( isset($fieldsets[$slug]) ){
				jcf_ajax_reposnse( array('status' => "0", 'error'=>__('Such fieldset already exists.', JCF_TEXTDOMAIN)) );
			}
		}else{
			$fieldsets = jcf_fieldsets_get();
			// check exists
			if( isset($fieldsets[$slug]) ){
				jcf_ajax_reposnse( array('status' => "0", 'error'=>__('Such fieldset already exists.', JCF_TEXTDOMAIN)) );
			}
		}

		// create fiedlset
		$fieldset = array(
			'id' => $slug,
			'title' => $title,
			'fields' => array(),
		);
		if( !empty($jcf_read_settings) && ($jcf_read_settings == 'theme' OR $jcf_read_settings == 'global') ){
			$jcf_settings['fieldsets'][$post_type][$slug] = $fieldset;
			$settings_data = json_encode($jcf_settings);
			 jcf_admin_save_all_settings_in_file($settings_data);
		}else{
			jcf_fieldsets_update($slug, $fieldset);
		}
		jcf_ajax_reposnse( array('status' => "1", $jcf_settings['fieldsets'][$post_type]) );
	}
	
	// delete fieldset link process
	function jcf_ajax_delete_fieldset(){
		$f_id = $_POST['fieldset_id'];
		if( empty($f_id) ){
			//jcf_ajax_reposnse( array('status' => "0", 'error'=>__('Wrong params passed.', JCF_TEXTDOMAIN)) );
		}
		$jcf_read_settings = jcf_get_read_settings();
		if( !empty($jcf_read_settings) && ($jcf_read_settings == 'theme' OR $jcf_read_settings == 'global') ){
			$jcf_settings = jcf_get_all_settings_from_file();
			$key = jcf_get_post_type();
			unset($jcf_settings['fieldsets'][$key][$f_id]);
			$settings_data = json_encode($jcf_settings);
			jcf_admin_save_all_settings_in_file($settings_data);
		}else{
			jcf_fieldsets_update($f_id, NULL);
		}
		
		jcf_ajax_reposnse( array('status' => "1") );
	}
	
	// change fieldset link process
	function jcf_ajax_change_fieldset(){
		$f_id = $_POST['fieldset_id'];
		$jcf_read_settings = jcf_get_read_settings();
		if( !empty($jcf_read_settings) && ($jcf_read_settings == 'theme' OR $jcf_read_settings == 'global') ){
			$jcf_settings = jcf_get_all_settings_from_file();
			$key = jcf_get_post_type();
			$fieldsets = $jcf_settings['fieldsets'][$key];
			$fieldset = $fieldsets[$f_id];
		}else{
			$fieldset = jcf_fieldsets_get($f_id);
		}
		
		ob_start();
		?>
		<div class="jcf_edit_fieldset">
			<h3 class="header"><?php echo __('Edit Fieldset:', JCF_TEXTDOMAIN) . ' ' . $fieldset['title']; ?></h3>
			<div class="jcf_inner_content">
				<form action="#" method="post" id="jcform_edit_fieldset">
					<fieldset>
						<input type="hidden" name="fieldset_id" value="<?php echo $fieldset['id']; ?>" />
						
						<p><label for="jcf_edit_fieldset_title"><?php _e('Title:', JCF_TEXTDOMAIN); ?></label> <input class="widefat" id="jcf_edit_fieldset_title" type="text" value="<?php echo esc_attr($fieldset['title']); ?>" /></p>
						
						<div class="field-control-actions">
							<div class="alignleft">
								<a href="#remove" class="field-control-remove"><?php _e('Delete', JCF_TEXTDOMAIN); ?></a> |
								<a href="#close" class="field-control-close"><?php _e('Close', JCF_TEXTDOMAIN); ?></a>
							</div>
							<div class="alignright">
								<?php echo print_loader_img(); ?>
								<input type="submit" value="<?php _e('Save', JCF_TEXTDOMAIN); ?>" class="button-primary" name="savefield">
							</div>
							<br class="clear"/>
						</div>
					</fieldset>
				</form>
			</div>
		</div>
		<?php
		$html = ob_get_clean();
		jcf_ajax_reposnse($html, 'html');
	}
	
	// save fieldset functions
	function jcf_ajax_update_fieldset(){
		$f_id = $_POST['fieldset_id'];
		$jcf_read_settings = jcf_get_read_settings();
		if( !empty($jcf_read_settings) && ($jcf_read_settings == 'theme' OR $jcf_read_settings == 'global') ){
			$jcf_settings = jcf_get_all_settings_from_file();
			$key = jcf_get_post_type();
			$fieldsets = $jcf_settings['fieldsets'][$key];
			$fieldset = $fieldsets[$f_id];
		}else{
			$fieldset = jcf_fieldsets_get($f_id);
		}

		if(empty($fieldset)){
			jcf_ajax_reposnse( array('status' => "0", 'error'=>__('Wrong data passed.', JCF_TEXTDOMAIN)) );
		}

		$title = strip_tags(trim($_POST['title']));
		if( empty($title) ){
			jcf_ajax_reposnse( array('status' => "0", 'error'=>__('Title field is required.', JCF_TEXTDOMAIN)) );
		}

		if( !empty($jcf_read_settings) && ($jcf_read_settings == 'theme' OR $jcf_read_settings == 'global') ){
			$jcf_settings['fieldsets'][$key][$f_id]['title'] = $title;
			$settings_data = json_encode($jcf_settings);
			jcf_admin_save_all_settings_in_file($settings_data);
		}else{
			$fieldset['title'] = $title;
			jcf_fieldsets_update($f_id, $fieldset);
		}
		jcf_ajax_reposnse( array('status' => "1", 'title' => $title) );
	}
	
	// add field form show
	function jcf_ajax_add_field(){
		
		$field_type =  $_POST['field_type'];
		$fieldset_id = $_POST['fieldset_id'];
		
		$field_obj = jcf_init_field_object($field_type, $fieldset_id);
		$html = $field_obj->do_form();
		jcf_ajax_reposnse($html, 'html');
		
	}

	// save field from the form
	function jcf_ajax_save_field(){

		$field_type =  $_POST['field_id'];
		$fieldset_id = $_POST['fieldset_id'];
		
		$field_obj = jcf_init_field_object($field_type, $fieldset_id);
		$resp = $field_obj->do_update();
		jcf_ajax_reposnse($resp, 'json');

	}
	
	// delete field processor
	function jcf_ajax_delete_field(){
		$field_id = $_POST['field_id'];
		$fieldset_id = $_POST['fieldset_id'];
		
		$field_obj = jcf_init_field_object($field_id, $fieldset_id);
		$field_obj->do_delete();
		
		$resp = array('status' => '1');
		jcf_ajax_reposnse($resp, 'json');
	}
	
	// edit field show form
	function jcf_ajax_edit_field(){
		$field_id = $_POST['field_id'];
		$fieldset_id = $_POST['fieldset_id'];
		
		$field_obj = jcf_init_field_object($field_id, $fieldset_id);
		$html = $field_obj->do_form();
		jcf_ajax_reposnse($html, 'html');
	}
	
	// fields order change
	function jcf_ajax_fields_order(){
		$fieldset_id = $_POST['fieldset_id'];
		$order  = trim($_POST['fields_order'], ',');
		
		$fieldset = jcf_fieldsets_get($fieldset_id);
		$new_fields = explode(',', $order);
		
		$fieldset['fields'] = array();
		foreach($new_fields as $field_id){
			$fieldset['fields'][$field_id] = $field_id;
		}
		
		jcf_fieldsets_update($fieldset_id, $fieldset);
		
		$resp = array('status' => '1');
		jcf_ajax_reposnse($resp, 'json');
	}
	
	// print response (encode to json if needed)
	function jcf_ajax_reposnse( $resp, $format = 'json' ){
		if( $format == 'json' ){
			$resp = json_encode($resp);
			header( "Content-Type: application/json" );
		}
		echo $resp;
		exit();
	}

	// export fields from form
	function jcf_ajax_export_fields_form(){
		$jcf_read_settings = jcf_get_read_settings();
		if( !empty($jcf_read_settings) && ($jcf_read_settings == 'theme' OR  $jcf_read_settings == 'global') ){
			$jcf_settings = jcf_get_all_settings_from_file();
		}else{
			$jcf_settings = jcf_get_all_settings_from_db();
		}

		$post_types = !empty($jcf_settings['post_types']) ? $jcf_settings['post_types'] : jcf_get_post_types();
		$fieldsets =$jcf_settings['fieldsets'];
		$field_settings = $jcf_settings['field_settings'];
		$registered_fields = jcf_get_registered_fields();

		// load template
		include( JCF_ROOT . '/templates/export.tpl.php' );
		exit();
	}

	// export fields
	function jcf_ajax_export_fields(){
		if( $_POST['export_fields'] && !empty($_POST['export_data']) ) {
			$export_data = $_POST['export_data'];
			$export_data = json_encode($export_data);
			$filename = 'jcf_export.json';
			header('Content-Type: text/json; charset=utf-8');
			header("Content-Disposition: attachment;filename=" . $filename);
			header("Content-Transfer-Encoding: binary ");
			echo $export_data;
			exit();
		}
	}

	// import fields
	function jcf_ajax_import_fields(){
		if( !empty($_POST['import-btn']) ){
			if(!empty($_FILES['import_data']['name']) ){
				$path_info = pathinfo($_FILES['import_data']['name']);

				if( $path_info['extension'] == 'json'){
					$uploaddir = get_home_path() . "wp-content/uploads/";
					$uploadfile = $uploaddir . basename($_FILES['import_data']['name']);

					if ( copy($_FILES['import_data']['tmp_name'], $uploadfile) ){
						$post_types = jcf_get_settings_from_file($uploadfile);
					}else{
						$notice = array('error' => 'Error! <strong>The file</strong> has not uploaded!');
					}
				}else{
					$notice = array('error' => 'Error! Check <strong>extension</strong> of the file!');
				}
			}else{
				$notice = array('error' => 'Error! The file is empty!');
			}
		}
		include( JCF_ROOT . '/templates/import.tpl.php' );
		do_action('admin_notices', $notice);
		exit();
	}

	//check file
	function jcf_ajax_check_file(){
		$jcf_read_settings = $_POST['jcf_read_settings'];
		if($jcf_read_settings == 'theme' OR $jcf_read_settings == 'global'){
			$dir = $jcf_read_settings == 'theme' ? get_template_directory() . '/jcf-settings/' : get_home_path() . 'wp-content/jcf-settings/';
			$file = 'jcf_settings.json';
			$msg = "The settings will be written to " . ($jcf_read_settings == 'theme' ? 'your theme folder' : 'folder wp-conten/jcf-settings') . ".\n In case you have settings there, they will be overwritten.\n Please confirm that you want to continue.";
			if( file_exists($dir . $file) ) {
				$resp = array('status' => '1', 'msg' => $msg);
			}else{
				$resp = array('status' => '1', 'file' => '1');
			}
		}else{
			$resp = array('status' => '1');
		}
		jcf_ajax_reposnse($resp, 'json');
	}
?>