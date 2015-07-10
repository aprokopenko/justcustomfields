<?php

	// add fieldset form import
	function jcf_import_add_fieldset($title_fieldset='', $option_name = '', $slug = ''){
		$title = !empty($title_fieldset) ? $title_fieldset : strip_tags(trim($_POST['title']));
		if( empty($title) ){
			return false;
		}
		if( empty($slug) ) {
			$slug = preg_replace('/[^a-z0-9\-\_\s]/i', '', $title);
			$slug = 'jcf-fieldset-'.rand(0,10000);
		}

		$jcf_read_settings = jcf_get_read_settings();
		if( !empty($jcf_read_settings) && $jcf_read_settings == 'theme' ){
			$jcf_settings = jcf_get_all_settings_from_file();
			$post_type = !empty($option_name) ? $option_name :  jcf_get_post_type();
			$fieldsets = $jcf_settings['fieldsets'][$post_type];
			if( isset($fieldsets[$slug]) ){
				return $slug;
			}
		}else{
			$fieldsets = jcf_fieldsets_get('', 'jcf_fieldsets-' . $option_name);
			if( isset($fieldsets[$slug]) ){
				return $slug;
			}
		}

		// create fiedlset
		$fieldset = array(
			'id' => $slug,
			'title' => $title,
			'fields' => array(),
		);
		if( !empty($jcf_read_settings) && $jcf_read_settings == 'theme' ){
			$jcf_settings['fieldsets'][$post_type][$slug] = $fieldset;
			$settings_data = json_encode($jcf_settings);
			 jcf_admin_save_all_settings_in_file($settings_data);
		}else{
			jcf_fieldsets_update($slug, $fieldset, 'jcf_fieldsets-' . $option_name);
		}
		return $slug;
	}

	// add field from import
	function jcf_import_add_field($field_id, $fieldset_id, $params, $option_name){
		$field_obj = jcf_init_field_object($field_id, $fieldset_id, 'jcf_fields-' . $option_name);
		if($field_obj->slug == $params['slug']){
			$resp = $field_obj->do_update($params, $option_name);
		}else{
			$field_obj = jcf_init_field_object($params['type'], $fieldset_id, 'jcf_fields-' . $option_name);
			$resp = $field_obj->do_update($params, $option_name);
		}

		return $resp;
	}
