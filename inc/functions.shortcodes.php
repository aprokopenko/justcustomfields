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

// shortcode [jcf_field]
function jcf_shortcode_field_value($atts){
	extract( shortcode_atts( array(
		'class' => '',
		'id' => '',
		'slug' => '',
		'post_id' => ''
	), $atts ) );
	if( !empty($atts['slug']) ){
		$type =  preg_replace('/[0-9_]/', '', str_replace('_field_', '',$atts['slug']));
		jcf_shortcode_init_fields();
		$field = jcf_get_registered_fields($type);
		$field_obj = new $field['class_name']();
		$field_obj->set_slug( $atts['slug'] );
		$post_id = !empty($atts['post_id']) ? $atts['post_id'] : get_the_ID();
		$field_obj->set_post_ID( $post_id );
		$args['type'] = $type;
		$args['post_type'] = get_post_type();
		$args = array_merge($args, $atts);
		return $field_obj->show_shortcode($args);
	}else{
		return _e('Error! Add "slug" to shortcode', JCF_TEXTDOMAIN);
	}
}

add_shortcode( 'just-field-value',  'jcf_shortcode_field_value' );