<?php

function jcf_shortcode_init_fields(){
	// init field classes and fields array
	jcf_field_register( 'Just_Field_Input' );
	jcf_field_register( 'Just_Field_Select' );
	jcf_field_register( 'Just_Field_SelectMultiple' );
	jcf_field_register( 'Just_Field_Checkbox' );
	jcf_field_register( 'Just_Field_Textarea' );
	jcf_field_register( 'Just_Field_DatePicker' );
	jcf_field_register( 'Just_Field_Upload' );
	jcf_field_register( 'Just_Field_FieldsGroup' );
	jcf_field_register( 'Just_Field_RelatedContent' );
	jcf_field_register( 'Just_Field_Table' );
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
 *	@param array $atts Attributes from shortcode
 *	@return string Field content
 */
function jcf_do_shortcode($atts){
	extract( shortcode_atts( array(
		'class' => '',
		'id' => '',
		'slug' => '',
		'post_id' => ''
	), $atts ) );
	$args['type'] =  preg_replace('/[0-9_]/', '', str_replace('_field_', '', $atts['slug']));
	jcf_shortcode_init_fields();
	$post_id = !empty($atts['post_id']) ? $atts['post_id'] : get_the_ID();
	$args['post_type'] = get_post_type($post_id);
	jcf_set_post_type($args['post_type']);
	$field_settings = jcf_field_settings_get();
	foreach($field_settings as $key_field => $field){
		if( array_search($atts['slug'], $field) == 'slug' ){
			$field_id = $key_field;
			break;
		}
	}
	if( $field_id ){
		$field_obj = jcf_init_field_object($field_id);
		$field_obj->set_post_ID( $post_id );
		$args = array_merge($args, $atts);
		return $field_obj->show_shortcode($args);
	}
	else{
		return false;
	}
}

/**
 *	Shortcode [jcf-field-value]
 *	@param array $atts Attributes from shortcode
 *	@return string Field content
 */
function jcf_shortcode_field_value($atts){
	if( !empty($atts['slug']) ){
		$atts['stype'] = 'value';
		return jcf_do_shortcode($atts);
	}else{
		return _e('Error! Add "slug" to shortcode', JCF_TEXTDOMAIN);
	}
}

/**
 *	Shortcode [jcf-field-label]
 *	@param array $atts Attributes from shortcode
 *	@return string Field content
 */
function jcf_shortcode_field_label($atts){
	if( !empty($atts['slug']) ){
		$atts['stype'] = 'label';
		return jcf_do_shortcode($atts);
	}else{
		return _e('Error! Add "slug" to shortcode', JCF_TEXTDOMAIN);
	}
}

add_shortcode( 'just-field-value',  'jcf_shortcode_field_value' );
add_shortcode( 'just-field-label',  'jcf_shortcode_field_label' );