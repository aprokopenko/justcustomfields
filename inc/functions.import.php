<?php

	// add fieldset form import
	function jcf_import_add_fieldset($title_fieldset='', $option_name = ''){
		$title = !empty($title_fieldset) ? $title_fieldset : strip_tags(trim($_POST['title']));
		if( empty($title) ){
			return false;
		}

		$slug = preg_replace('/[^a-z0-9\-\_\s]/i', '', $title);
		$slug = 'jcf-fieldset-'.rand(0,10000);

		$fieldsets = jcf_fieldsets_get('', 'jcf_fieldsets-' . $option_name);
		// check exists
		if( isset($fieldsets[$slug]) ){
			return false;
		}

		// create fiedlset
		$fieldset = array(
			'id' => $slug,
			'title' => $title,
			'fields' => array(),
		);
		jcf_fieldsets_update($slug, $fieldset, 'jcf_fieldsets-' . $option_name);
		return $slug;
	}


	// add field from import
	function jcf_import_add_field($field_type, $fieldset_id, $params, $option_name){

		$field_obj = jcf_init_field_object($field_type, $fieldset_id);
		$resp = $field_obj->do_update($params, $option_name);
		return $resp;
	}
