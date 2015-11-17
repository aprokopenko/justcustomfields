<?php

/**
 * Include classes for shortcodes for the frontend usage
 */
function jcf_shortcode_init_fields(){
	// init field classes and fields array
	jcf_field_register( 'Just_Field_Input' );
	jcf_field_register( 'Just_Field_Select' );
	jcf_field_register( 'Just_Field_SelectMultiple' );
	jcf_field_register( 'Just_Field_Checkbox' );
	jcf_field_register( 'Just_Field_Textarea' );
	jcf_field_register( 'Just_Field_DatePicker' );
	jcf_field_register( 'Just_Field_Simple_Media' );
	jcf_field_register( 'Just_Field_Table' );
	jcf_field_register( 'Just_Field_Collection' );
	jcf_field_register( 'Just_Field_RelatedContent' );
	jcf_field_register( 'Just_Field_Upload' );
	jcf_field_register( 'Just_Field_FieldsGroup' );
	/**
	 *	to add more fields with your custom plugin:
	 *	- add_action  'jcf_register_fields'
	 *	- include your components files
	 *	- run jcf_field_register('YOUR_COMPONENT_CLASS_NAME');
	 */
	do_action( 'jcf_register_fields' );
}

/**
 *	Do shortcode
 *	@param array $args Attributes from shortcode
 *	@return string Field content
 */
function jcf_do_shortcode($args){
	extract( shortcode_atts( array(
		'field' => '',
		'post_id' => '',
	), $args ) );
	
	//init registered fields
	jcf_shortcode_init_fields();
	//get post id
	$post_id = !empty($args['post_id']) ? $args['post_id'] : get_the_ID();
	//get post type
	$post_type = get_post_type($post_id);
	//set post type for fields
	jcf_set_post_type($post_type);
	//get field settings
	$field_settings = jcf_field_settings_get();
	//get field id
	foreach($field_settings as $key_field => $field){
		if( strcmp($args['field'], $field['slug']) === 0 ){
			$field_id = $key_field;
			break;
		}
	}
	// init field object and do shortcode
	if( $field_id ){
		$field_obj = jcf_init_field_object($field_id);
		$field_obj->set_post_ID( $post_id );
		
		unset($args['field']);
		return $field_obj->do_shortcode($args);
	}
	else{
		return false;
	}
}

/**
 *	Shortcode [jcf-value]
 *	@param array $args Attributes from shortcode
 *	@return string Field content
 */
function jcf_shortcode_field_value($args){
	if( !empty($args['field']) ){
		return jcf_do_shortcode($args);
	}else{
		return _e('Error! "field" parameter is missing', JCF_TEXTDOMAIN);
	}
}

add_shortcode( 'jcf-value',  'jcf_shortcode_field_value' );
