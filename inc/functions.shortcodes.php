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
	//get type of field
	$args['type'] =  preg_replace('/[0-9_]/', '', str_replace('_field_', '', $atts['slug']));
	//init registered fields
	jcf_shortcode_init_fields();
	//get post id
	$post_id = !empty($atts['post_id']) ? $atts['post_id'] : get_the_ID();
	//get post type
	$args['post_type'] = get_post_type($post_id);
	//set post type for fields
	jcf_set_post_type($args['post_type']);
	//get field settings
	$field_settings = jcf_field_settings_get();
	//get field id
	foreach($field_settings as $key_field => $field){
		if( array_search($atts['slug'], $field) == 'slug' ){
			$field_id = $key_field;
			break;
		}
	}
	//init field object and do shortcode
	if( $field_id ){
		$field_obj = jcf_init_field_object($field_id);
		$field_obj->set_post_ID( $post_id );
		$args = array_merge($args, $atts);
		if( $args['stype'] == 'value' ){
			return $field_obj->show_shortcode_values($args);
		}
		elseif( $args['stype'] == 'label' ){
			return $field_obj->show_shortcode_label($args);
		}
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

add_shortcode( 'jcf-value',  'jcf_shortcode_field_value' );
add_shortcode( 'jcf-label',  'jcf_shortcode_field_label' );