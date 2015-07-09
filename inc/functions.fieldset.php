<?php
	
	/**
	 *	all fieldset functions operate with $post_type!
	 */
	function jcf_fieldsets_get( $id = '', $option_name = '' ){
		if(empty($option_name)){
			$option_name = jcf_fieldsets_get_option_name();
		}
		$fieldsets = jcf_get_options($option_name);

		if(!empty($id)){
			return @$fieldsets[$id];
		}

		return $fieldsets;
	}
	

	function jcf_fieldsets_update( $key, $values = array(), $option_name = '' ){
		if(empty($option_name)){
			$option_name = jcf_fieldsets_get_option_name();
		}

		$fieldsets = jcf_get_options($option_name);
		if( $values === NULL && isset($fieldsets[$key]) ){
			unset($fieldsets[$key]);
		}
		
		if( !empty($values) ){
			$fieldsets[$key] = $values;
		}
		
		jcf_update_options($option_name, $fieldsets);
	}
	
	function jcf_fieldsets_get_option_name(){
		$post_type = jcf_get_post_type();
		return 'jcf_fieldsets-'.$post_type;
	}
	
?>