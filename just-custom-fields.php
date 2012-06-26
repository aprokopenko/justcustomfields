<?php
/*
Plugin Name: Just Custom Fields for Wordpress
Plugin URI: http://justcoded.com/just-labs/just-custom-fields-for-wordpress-plugin/
Description: This plugin add custom fields for standard and custom post types in WordPress.
Tags: custom, fields, custom fields, meta, post meta, object meta, editor
Author: Alexander Prokopenko
Author URI: http://justcoded.com/
Version: 1.3.4
Donate link: http://justcoded.com/just-labs/just-custom-fields-for-wordpress-plugin/
*/

define('JCF_ROOT', dirname(__FILE__));
define('JCF_TEXTDOMAIN', 'just-custom-fields');

require_once( JCF_ROOT.'/inc/class.field.php' );
require_once( JCF_ROOT.'/inc/functions.fieldset.php' );
require_once( JCF_ROOT.'/inc/functions.fields.php' );
require_once( JCF_ROOT.'/inc/functions.ajax.php' );
require_once( JCF_ROOT.'/inc/functions.post.php' );
require_once( JCF_ROOT.'/inc/functions.themes.php' );

// composants
require_once( JCF_ROOT.'/components/input-text.php' );
require_once( JCF_ROOT.'/components/select.php' );
require_once( JCF_ROOT.'/components/select-multiple.php' );
require_once( JCF_ROOT.'/components/checkbox.php' );
require_once( JCF_ROOT.'/components/textarea.php' );
require_once( JCF_ROOT.'/components/datepicker/datepicker.php' );
require_once( JCF_ROOT.'/components/uploadmedia/uploadmedia.php' );
require_once( JCF_ROOT.'/components/fieldsgroup/fields-group.php' );
require_once( JCF_ROOT.'/components/relatedcontent/related-content.php' );


if(!function_exists('pa')){
function pa($mixed, $stop = false) {
	$ar = debug_backtrace(); $key = pathinfo($ar[0]['file']); $key = $key['basename'].':'.$ar[0]['line'];
	$print = array($key => $mixed); echo( '<pre>'.htmlentities(print_r($print,1)).'</pre>' );
	if($stop == 1) exit();
}
}

add_action('after_setup_theme', 'jcf_init');
function jcf_init(){
	if( !is_admin() ) return;
	
	/**
	 *	load translations
	 */
	load_plugin_textdomain( JCF_TEXTDOMAIN, false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
	
	// init global variables
	global $jcf_fields, $jcf_fieldsets;
	
	// add admin page
	add_action( 'admin_menu', 'jcf_admin_menu' );
	
	// add ajax call processors
	add_action('wp_ajax_jcf_add_fieldset', 'jcf_ajax_add_fieldset');
	add_action('wp_ajax_jcf_delete_fieldset', 'jcf_ajax_delete_fieldset');
	add_action('wp_ajax_jcf_change_fieldset', 'jcf_ajax_change_fieldset');
	add_action('wp_ajax_jcf_update_fieldset', 'jcf_ajax_update_fieldset');
	
	add_action('wp_ajax_jcf_add_field', 'jcf_ajax_add_field');
	add_action('wp_ajax_jcf_save_field', 'jcf_ajax_save_field');
	add_action('wp_ajax_jcf_delete_field', 'jcf_ajax_delete_field');
	add_action('wp_ajax_jcf_edit_field', 'jcf_ajax_edit_field');
	add_action('wp_ajax_jcf_fields_order', 'jcf_ajax_fields_order');
	
	// add $post_type for ajax
	if(!empty($_POST['post_type'])) jcf_set_post_type( $_POST['post_type'] );
	
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
	/**
	 *	to add more fields with your custom plugin:
	 *	- add_action  'jcf_register_fields'
	 *	- include your components files
	 *	- run jcf_field_register('YOUR_COMPONENT_CLASS_NAME');
	 */
	do_action( 'jcf_register_fields' );


	// add post edit/save hooks
	add_action( 'add_meta_boxes', 'jcf_post_load_custom_fields', 10, 1 ); 
	add_action( 'save_post', 'jcf_post_save_custom_fields', 10, 2 );

	
	// add custom styles and scripts
	if( !empty($_GET['page']) && $_GET['page'] == 'just_custom_fields' ){
		add_action('admin_print_styles', 'jcf_admin_add_styles');
		add_action('admin_print_scripts', 'jcf_admin_add_scripts');
	}
}

// hook to add new admin setting page
function jcf_admin_menu(){
	add_options_page(__('Just Custom Fields', JCF_TEXTDOMAIN), __('Just Custom Fields', JCF_TEXTDOMAIN), 'manage_options', 'just_custom_fields', 'jcf_admin_settings_page');
}

/**
 *	Admin main page
 */
function jcf_admin_settings_page(){
	$post_types = jcf_get_post_types( 'object' );
	
	// edit page
	if( !empty($_GET['pt']) && isset($post_types[ $_GET['pt'] ]) ){
		jcf_admin_fields_page( $post_types[ $_GET['pt'] ] );
		return;
	}
	
	// load template
	include( JCF_ROOT . '/templates/settings_page.tpl.php' );
}

/**
 *	Fields UI page
 */
function jcf_admin_fields_page( $post_type ){
	jcf_set_post_type( $post_type->name );
	
	$fieldsets = jcf_fieldsets_get();
	$field_settings = jcf_field_settings_get();
	//pa($fieldsets);
	//pa($field_settings,1);
	
	// load template
	include( JCF_ROOT . '/templates/fields_ui.tpl.php' );
}

/**
 *	javascript localization
 */
function jcf_get_language_strings(){
	global $wp_version;

	$strings = array(
		'hi' => __('Hello there', JCF_TEXTDOMAIN),
		'edit' => __('Edit', JCF_TEXTDOMAIN),
		'delete' => __('Delete', JCF_TEXTDOMAIN),
		'confirm_field_delete' => __('Are you sure you want to delete selected field?', JCF_TEXTDOMAIN),
		'confirm_fieldset_delete' => __("Are you sure you want to delete the fieldset?\nAll fields will be also deleted!", JCF_TEXTDOMAIN),
		'update_image' => __('Update Image', JCF_TEXTDOMAIN),
		'update_file' => __('Update File', JCF_TEXTDOMAIN),
		'yes' => __('Yes', JCF_TEXTDOMAIN),
		'no' => __('No', JCF_TEXTDOMAIN),
		
		'wp_version' => $wp_version,
	);
	$strings = apply_filters('jcf_localize_script_strings', $strings);
	return $strings;
}

// print image with loader
function print_loader_img(){
	return '<img class="ajax-feedback " alt="" title="" src="' . get_bloginfo('url') . '/wp-admin/images/wpspin_light.gif" style="visibility: hidden;">';
}

// set post_type in global variable, so we can use it in internal functions
function jcf_set_post_type( $post_type ){
	global $jcf_post_type;
	$jcf_post_type = $post_type;
}

// return jcf_post_type global variable
function jcf_get_post_type(){
	global $jcf_post_type;
	return $jcf_post_type;
}

// get registered post types
function jcf_get_post_types( $format = 'single' ){
	$params_post = array( 'capability_type' => 'post', 'show_ui' => true ); // To get all post_type depends post
	$params_page = array( 'capability_type' => 'page', 'show_ui' => true ); // To get all post_type depends page
	
	if( $format != 'object' ){
		$post_types = get_post_types( $params_post );
		$page_types = get_post_types( $params_page );
		$post_types = array_merge($post_types, $page_types);
	}
	else{
		$post_types = get_post_types( $params_post, 'object' );
		$page_types = get_post_types( $params_page, 'object' );
		$post_types = array_merge($post_types, $page_types);
	}
	//pa($post_types,1);
	return $post_types;
}

// add custom scripts for plugin settings page
function jcf_admin_add_scripts() {
	wp_register_script(
			'just_custom_fields',
			WP_PLUGIN_URL.'/just-custom-fields/assets/just_custom_fields.js',
			array('jquery', 'json2', 'jquery-form', 'jquery-ui-sortable')
		);
	wp_enqueue_script('just_custom_fields');
	
	// add text domain
	wp_localize_script( 'just_custom_fields', 'jcf_textdomain', jcf_get_language_strings() );
}

// add custom styles for plugin settings page
function jcf_admin_add_styles() { 
	wp_register_style('jcf-styles', WP_PLUGIN_URL.'/just-custom-fields/assets/styles.css');
	wp_enqueue_style('jcf-styles'); 
}




?>
