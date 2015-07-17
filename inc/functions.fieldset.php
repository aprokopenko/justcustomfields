<?php
	
	/**
	 *	all fieldset functions operate with $post_type!
	 */
	function jcf_fieldsets_get( $id = '' ){
		$option_name = jcf_fieldsets_get_option_name();

		$jcf_read_settings = jcf_get_read_settings();
		if( !empty($jcf_read_settings) && ($jcf_read_settings == 'theme' OR $jcf_read_settings == 'global') ){
			$jcf_settings = jcf_get_all_settings_from_file();
			$post_type = jcf_get_post_type();
			$fieldsets = $jcf_settings['fieldsets'][$post_type];
		}else{
			$fieldsets = jcf_get_options($option_name);
		}
		if(!empty($id)){
			return @$fieldsets[$id];
		}

		return $fieldsets;
	}

	function jcf_fieldsets_update( $key, $values = array()){
		$option_name = jcf_fieldsets_get_option_name();

		$jcf_read_settings = jcf_get_read_settings();
		if( !empty($jcf_read_settings) && ($jcf_read_settings == 'theme' OR $jcf_read_settings == 'global') ){
			$jcf_settings = jcf_get_all_settings_from_file();
			$post_type = jcf_get_post_type();
			if( $values === NULL && isset($fieldsets[$key]) ){
				unset($jcf_settings['fieldsets'][$post_type][$key]);
			}
			if( !empty($values) ){
				$jcf_settings['fieldsets'][$post_type][$key] = $values;
			}
			$settings_data = json_encode($jcf_settings);
			 jcf_admin_save_all_settings_in_file($settings_data);
		}else{
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
	
	function jcf_fieldsets_get_option_name(){
		$post_type = jcf_get_post_type();
		return 'jcf_fieldsets-'.$post_type;
	}

?>