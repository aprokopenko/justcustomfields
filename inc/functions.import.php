<?php

	/**
	 *	Add fieldset form import
	 *	@param string $title_fieldset Feildset name
	 *	@param string $slug Fieldset slug
	 *	@return string|boolean Return slug if fieldset has saved and false if not
	 */
	function jcf_import_add_fieldset($title_fieldset='', $slug = ''){
		$title = !empty($title_fieldset) ? $title_fieldset : strip_tags(trim($_POST['title']));
		if( empty($title) ){
			return false;
		}
		if( empty($slug) ) {
			$slug = preg_replace('/[^a-z0-9\-\_\s]/i', '', $title);
			$slug = 'jcf-fieldset-'.rand(0,10000);
		}

		$fieldsets = jcf_fieldsets_get();
		if( isset($fieldsets[$slug]) ){
			return $slug;
		}

		// create fiedlset
		$fieldset = array(
			'id' => $slug,
			'title' => $title,
			'fields' => array(),
		);
		jcf_fieldsets_update($slug, $fieldset);
		return $slug;
	}

	/**
	 *	Add field from import
	 *	@param string $field_id Field id
	 *	@param string $fieldset_id Fieldset id
	 *	@param array $params Attributes of field
	 *	@return array Attributes of field
	 */
	function jcf_import_add_field($field_id, $fieldset_id, $params){
		$field_obj = jcf_init_field_object($field_id, $fieldset_id);
		if($field_obj->slug == $params['slug']){
			$resp = $field_obj->do_update($params);
		}else{
			$field_id = preg_replace('/\-([0-9]+)/', '', $field_id);
			$field_obj = jcf_init_field_object($field_id, $fieldset_id);
			$resp = $field_obj->do_update($params);
		}

		return $resp;
	}

	/**
	 *	Get all settings from db
	 *	@return array Return all settings for fieldsets and fields
	 */
	function jcf_get_all_settings_from_db(){
		$post_types = jcf_get_post_types();
		$jcf_settings = array();
		$fieldsets = array();
		$field_settings = array();
		$field_options = array();
		foreach($post_types as $key => $value){
			jcf_set_post_type($key);
			$fieldsets[$key] = jcf_fieldsets_get();
			$field_settings[$key] = jcf_field_settings_get('', true);
		}

		$jcf_settings = array(
			'post_types' => $post_types,
			'fieldsets' => $fieldsets,
			'field_settings' => $field_settings,
		);
		return $jcf_settings;
	}

	/**
	 *	Save fields from import to file config or db
	 *	@param array $data Array with fieldsets and fields settings from import file
	 *	@return boolean|int Return save status
	 */
	function jcf_admin_save_settings($data){
		foreach($data as $key => $post_type ){
			jcf_set_post_type($key);
			if(is_array($post_type) && !empty($post_type['fieldsets'])){
				foreach($post_type['fieldsets'] as $fieldset_id => $fieldset){
					$status_fieldset = jcf_import_add_fieldset($fieldset['title'], $fieldset_id);
					if( empty($status_fieldset) ){
						$notice = array('error' => 'Error! Please check <strong>import file</strong>');
						do_action('admin_notices', $notice);
						return false;
					}else{
						$fieldset_id = $status_fieldset;
						if(!empty($fieldset['fields'])){
							$old_fields = jcf_field_settings_get();
							if(!empty($old_fields)){
								foreach($old_fields as $old_field_id => $old_field){
									$old_slugs[] = $old_field['slug'];
									$old_field_ids[$old_field['slug']] = $old_field_id;
								}
							}
							foreach($fieldset['fields'] as $field_id => $field){
								$slug_checking = !empty($old_slugs) ? in_array($field['slug'], $old_slugs) : false;
								if($slug_checking){
									$status_field = jcf_import_add_field($old_field_ids[$field['slug']], $fieldset_id, $field);
								}else{
									$status_field = jcf_import_add_field($field_id, $fieldset_id, $field);
								}
							}
						}
					}
				}
				if( !empty($status_fieldset) ){
					if( $_POST['file_name'] ){
						unlink($_POST['file_name']);
					}

				}
			}
		}
		return $status_fieldset;
	}
