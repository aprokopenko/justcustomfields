<?php
	
	/**
	 *	register field in global variable. contain info like id_base, title and class name
	 */
	function jcf_field_register( $class_name ){
		global $jcf_fields;

		
		// check class exists and try to create class object to get title
		if( !class_exists($class_name) ) return false;

		//check field compatibility with WP version
		if( !$class_name::checkCompatibility($class_name::$compatibility) ) return false;
		
		$field_obj = new $class_name();

		$field = array(
			'id_base' => $field_obj->id_base,
			'class_name' => $class_name,
			'title' => $field_obj->title,
		);
		
		$jcf_fields[$field_obj->id_base] = $field;
	}

	/**
	 *	return array of registered fields (or concrete field by id_base)
	 */
	function jcf_get_registered_fields( $id_base = '' ){
		global $jcf_fields;

		if( !empty($id_base) ){
			return @$jcf_fields[$id_base];
		}

		return $jcf_fields;
	}

	/**
	 *	set fields in wp-options
	 */
	function jcf_field_settings_update( $key, $values = array(), $fieldset_id = ''){
		$option_name = jcf_fields_get_option_name();

		$jcf_read_settings = jcf_get_read_settings();
		if( $jcf_read_settings != JCF_CONF_SOURCE_DB ){
			$jcf_settings = jcf_get_all_settings_from_file();
			$post_type =  jcf_get_post_type();
			$fieldset = $jcf_settings['fieldsets'][$post_type][$fieldset_id];
			if( isset($jcf_settings['field_settings']) && isset($jcf_settings['field_settings'][$post_type]) ){
				$field_settings = $jcf_settings['field_settings'][$post_type];				
			} else $field_settings = array();

			if( $values === NULL && isset($field_settings[$key]) ){
				unset($fieldset['fields'][$key]);
				unset($field_settings[$key]);
			}

			if( !empty($values) ){
				$fieldset['fields'][$key] = $values['enabled'];
				$field_settings[$key] = $values;
			}

			$jcf_settings['fieldsets'][$post_type][$fieldset_id] = $fieldset;
			$jcf_settings['field_settings'][$post_type] = $field_settings;
			jcf_save_all_settings_in_file($jcf_settings);
		}
		else{
			$field_settings = jcf_get_options($option_name);
			if( $values === NULL && isset($field_settings[$key]) ){
				unset($field_settings[$key]);
			}

			if( !empty($values) ){
				$field_settings[$key] = $values;
			}

			jcf_update_options($option_name, $field_settings);
		}
	}

	/**
	 *	get fields from wp-options
	 */
	function jcf_field_settings_get( $id = '', $select_from_db = false){
		$option_name = jcf_fields_get_option_name();
		$jcf_read_settings = jcf_get_read_settings();
		
		if( empty($select_from_db) && $jcf_read_settings != JCF_CONF_SOURCE_DB ){
			$jcf_settings = jcf_get_all_settings_from_file();
			$post_type =  str_replace('jcf_fields-', '', $option_name);
			$field_settings = $jcf_settings['field_settings'][$post_type];
		} 
		else {
			$field_settings = jcf_get_options($option_name);
		}

		if(!empty($id)){
			return @$field_settings[$id];
		}

		return $field_settings;
	}

	/**
	 *	init field object
	 */
	function jcf_init_field_object( $field_mixed, $fieldset_id = '', $collection_id = ''){
		// $field_mixed can be real field id or only id_base
		$id_base = preg_replace('/\-([0-9]+)/', '', $field_mixed);
		$field = jcf_get_registered_fields( $id_base );
		$field_obj = new $field['class_name']();

		$field_obj->set_fieldset( $fieldset_id );
		$field_obj->set_collection( $collection_id );
		$field_obj->set_id( $field_mixed );
		//if is not new field and include to cillection
		if(!$field_obj->is_new && $collection_id){
			$collection_obj = new Just_Field_Collection();
			$collection_obj->set_fieldset($fieldset_id);
			$collection_obj->set_id($collection_id);
			$field = $collection_obj->instance['fields'][$field_mixed];
			$field_obj->set_slug($field['slug']);
			$field_obj->instance = $field;
		}

		return $field_obj;
	}

	/**
	 * get next index for save new instance
	 * because of ability to import fields now, we can't use DB to save AI. 
	 * we will use timestamp for this
	 */
	function jcf_get_fields_index( $id_base ){
		return time();
	}
	
	// option name in wp-options table
	function jcf_fields_get_option_name(){
		$post_type = jcf_get_post_type();
		return 'jcf_fields-'.$post_type;
	}

	/**
	 *	parse "Settings" param for checkboxes/selects/multiple selects
	 */
	function jcf_parse_field_settings( $string ){
		$values = array();
		$v = explode("\n", $string);
		foreach($v as $val){
			$val = trim($val);
			if(strpos($val, '|') !== FALSE ){
				$a = explode('|', $val);
				$values[$a[0]] = $a[1];
			}
			elseif(!empty($val)){
				$values[$val] = $val;
			}
		}
		$values = array_flip($values);
		return $values;
	}

