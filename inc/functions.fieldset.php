<?php
	
	/**
	 *	all fieldset functions operate with $post_type!
	 */
	
	function jcf_fieldsets_get( $id = '' ){
		$option_name = jcf_fieldsets_get_option_name();
		$fieldsets = get_option($option_name, array());
		
		if(!empty($id)){
			return @$fieldsets[$id];
		}
		
		return $fieldsets;
	}
	
	function jcf_fieldsets_update( $key, $values = array() ){
		$option_name = jcf_fieldsets_get_option_name();
		
		$fieldsets = get_option($option_name, array());
		if( $values === NULL && isset($fieldsets[$key]) ){
			unset($fieldsets[$key]);
		}
		
		if( !empty($values) ){
			$fieldsets[$key] = $values;
		}
		
		update_option($option_name, $fieldsets);
	}
	
	function jcf_fieldsets_get_option_name(){
		$post_type = jcf_get_post_type();
		return 'jcf_fieldsets-'.$post_type;
	}
	
	function jcf_fieldsets_count($post_type){
		$fieldsets = get_option('jcf_fieldsets-'.$post_type, array());
		$count['fieldsets'] = count($fieldsets);
		$count['fields'] = 0;
		foreach($fieldsets as $fieldset){
			if(!empty($fieldset['fields'])){
				$count['fields'] += count($fieldset['fields']);
			}
		}
		return $count;
	}
?>