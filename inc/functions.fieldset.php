<?php
	
	/**
	 *	all fieldset functions operate with $post_type!
	 */
	function jcf_fieldsets_get( $id = '' ){
		$option_name = jcf_fieldsets_get_option_name();

		$jcf_read_settings = jcf_get_read_settings();
		if( $jcf_read_settings != JCF_CONF_SOURCE_DB ){
			$jcf_settings = jcf_get_all_settings_from_file();
			$post_type = jcf_get_post_type();
			if(isset($jcf_settings['fieldsets'][$post_type])){
				$fieldsets = $jcf_settings['fieldsets'][$post_type];
			} else $fieldsets = array();
		}
		else{
			$fieldsets = jcf_get_options($option_name);
		}
		if(!empty($id)){
			return @$fieldsets[$id];
		}

		return $fieldsets;
	}

	/**
	 * update one fieldset settings
	 * @param string $key	fieldset id
	 * @param array $values		fieldset settings
	 */
	function jcf_fieldsets_update( $key, $values = array()){
		$option_name = jcf_fieldsets_get_option_name();

		$jcf_read_settings = jcf_get_read_settings();
		if( $jcf_read_settings != JCF_CONF_SOURCE_DB ){
			$jcf_settings = jcf_get_all_settings_from_file();
			$post_type = jcf_get_post_type();
	
			if( $values === NULL && isset($jcf_settings['fieldsets'][$post_type][$key]) ){
				unset($jcf_settings['fieldsets'][$post_type][$key]);
			}
			if( !empty($values) ){
				$jcf_settings['fieldsets'][$post_type][$key] = $values;
			}
			jcf_save_all_settings_in_file($jcf_settings);
		}
		else{
			$fieldsets = jcf_get_options($option_name);
			if( $values === NULL && isset($fieldsets[$key]) ){
				unset($fieldsets[$key]);
			}

			if( !empty($values) ){
				$fieldsets[$key] = $values;
			}

			jcf_update_options($option_name, $fieldsets);
		}
	}
	
	/**
	 * return db fieldset name
	 * @return string
	 */
	function jcf_fieldsets_get_option_name(){
		$post_type = jcf_get_post_type();
		return 'jcf_fieldsets-'.$post_type;
	}
	
	/**
	 * return number of registered fields and fieldsets for specific post type
	 * @param string $post_type
	 * @return int
	 */
	function jcf_fieldsets_count($post_type){
		$jcf_read_settings = jcf_get_read_settings();
		if( $jcf_read_settings != JCF_CONF_SOURCE_DB ){
			$jcf_settings = jcf_get_all_settings_from_file();
			if(isset($jcf_settings['fieldsets'][$post_type])){
				$fieldsets = $jcf_settings['fieldsets'][$post_type];
			} else {
				$fieldsets = array();
			}
			
		} 
		else {
			$fieldsets = jcf_get_options('jcf_fieldsets-'.$post_type);
		}
		
		if(!empty($fieldsets)){
			$count['fieldsets'] = count($fieldsets);
			$count['fields'] = 0;
			foreach($fieldsets as $fieldset){
				if(!empty($fieldset['fields'])){
					$count['fields'] += count($fieldset['fields']);
				}
			}
		}
		else{
			$count = array('fieldsets' => 0, 'fields' => 0);
		}
		return $count;
	}

	/**
	 * update order fieldsets
	 * @param array $keys Fieldsets keys
	 */
	function jcf_fieldsets_order($keys = array()){
		$option_name = jcf_fieldsets_get_option_name();
		$new_fieldsets = array();
		$jcf_read_settings = jcf_get_read_settings();
		if( $jcf_read_settings != JCF_CONF_SOURCE_DB ){
			$jcf_settings = jcf_get_all_settings_from_file();
			$post_type = jcf_get_post_type();

			foreach($keys as $key){
				$new_fieldsets[$key] = $jcf_settings['fieldsets'][$post_type][$key];
				unset($jcf_settings['fieldsets'][$post_type][$key]);
			}
			$jcf_settings['fieldsets'][$post_type] = $new_fieldsets;
			jcf_save_all_settings_in_file($jcf_settings);
		}
		else{
			$fieldsets = jcf_get_options($option_name);
			foreach($keys as $key){
				$new_fieldsets[$key] = $fieldsets[$key];
				unset($fieldsets[$key]);
			}
			jcf_update_options($option_name, $new_fieldsets);
		}
	}