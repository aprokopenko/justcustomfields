<?php
	
	/**
	 *	all fieldset functions operate with $post_type!
	 */
	
	function jcf_fieldsets_get( $id = '' ){
		global $jcf_multisite_settings;
		$option_name = jcf_fieldsets_get_option_name();
		$fieldsets = $jcf_multisite_settings == 'global' ? get_site_option($option_name, array()) : get_option($option_name, array());
		
		if(!empty($id)){
			return @$fieldsets[$id];
		}
		
		return $fieldsets;
	}
	
	function jcf_fieldsets_update( $key, $values = array() ){
		global $jcf_multisite_settings;
		$option_name = jcf_fieldsets_get_option_name();
		
		$fieldsets = $jcf_multisite_settings == 'global' ? get_site_option($option_name, array()) : get_option($option_name, array());
		if( $values === NULL && isset($fieldsets[$key]) ){
			unset($fieldsets[$key]);
		}
		
		if( !empty($values) ){
			$fieldsets[$key] = $values;
		}
		
		$jcf_multisite_settings == 'global' ? update_site_option($option_name, $fieldsets) : update_option($option_name, $fieldsets);
	}
	
	function jcf_fieldsets_get_option_name(){
		$post_type = jcf_get_post_type();
		return 'jcf_fieldsets-'.$post_type;
	}
	
?>