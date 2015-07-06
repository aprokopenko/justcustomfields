<?php
/*
Plugin Name: Just Custom Fields for Wordpress
Plugin URI: http://justcoded.com/just-labs/just-custom-fields-for-wordpress-plugin/
Description: This plugin add custom fields for standard and custom post types in WordPress.
Tags: custom, fields, custom fields, meta, post meta, object meta, editor
Author: Alexander Prokopenko
Author URI: http://justcoded.com/
Version: 1.4.1
Donate link: http://justcoded.com/just-labs/just-custom-fields-for-wordpress-plugin/
*/

define('JCF_ROOT', dirname(__FILE__));
define('JCF_TEXTDOMAIN', 'just-custom-fields');
define('JCF_VERSION', 1.41);

require_once( JCF_ROOT.'/inc/class.field.php' );
require_once( JCF_ROOT.'/inc/functions.fieldset.php' );
require_once( JCF_ROOT.'/inc/functions.fields.php' );
require_once( JCF_ROOT.'/inc/functions.ajax.php' );
require_once( JCF_ROOT.'/inc/functions.post.php' );
require_once( JCF_ROOT.'/inc/functions.themes.php' );
require_once( JCF_ROOT.'/inc/functions.import.php' );

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

	add_action('wp_ajax_jcf_export_fields', 'jcf_ajax_export_fields');
	add_action('wp_ajax_jcf_import_fields', 'jcf_ajax_import_fields');

	add_action('wp_ajax_jcf_update_read_settings', 'jcf_ajax_update_read_settings');
	
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
	$jcf_read_settings = get_read_settings();
	// edit page
	if( !empty($_GET['pt']) && isset($post_types[ $_GET['pt'] ]) ){
		jcf_admin_fields_page( $post_types[ $_GET['pt'] ] );
		return;
	}

	if( isset($_GET['export']) ) {
		jcf_admin_export_page();
		return;
	}

	if( isset($_GET['import']) ) {
		jcf_admin_import_page();
		return;
	}
	
	if( isset($_GET['keep_settings']) ) {
		jcf_admin_keep_settings();
	}


	// load template
	include( JCF_ROOT . '/templates/settings_page.tpl.php' );
}

/**
 *	Fields UI page
 */
function jcf_admin_fields_page( $post_type ){
	jcf_set_post_type( $post_type->name );
	$jcf_read_settings = get_read_settings();
	if( !empty($jcf_read_settings) && $jcf_read_settings == 'file' ){
		$jcf_settings = jcf_get_all_settings_from_file();
		$key = $post_type->name;
		$fieldsets = $jcf_settings->fieldsets->$key;
		$field_settings = (array)$jcf_settings->field_settings->$key;
	}else{
		$fieldsets = jcf_fieldsets_get();
		$field_settings = jcf_field_settings_get();		
	}

	// load template
	include( JCF_ROOT . '/templates/fields_ui.tpl.php' );
}

/**
 *	Keep settings in the file of theme
 */
function jcf_admin_keep_settings(){
	$jcf_settings = jcf_get_all_settings_from_db();
	$post_types = $jcf_settings['post_types'];
	$fieldsets =$jcf_settings['fieldsets'];
	$field_settings = $jcf_settings['field_settings'];

	$settings_data = json_encode($jcf_settings);

	if( !is_dir(get_template_directory() . '/jcf-settings/') ){
		if( mkdir(get_template_directory() . '/jcf-settings/') ){
			$save = jcf_admin_save_all_settings_in_file($settings_data);
		}
	}else {
		$save = jcf_admin_save_all_settings_in_file($settings_data);
	}
	if($save){
		echo _e('The file is saved');
	}
}

/**
 *	Export page
 */
function jcf_admin_export_page(){
	$jcf_read_settings = get_read_settings();
	if( !empty($jcf_read_settings) && $jcf_read_settings == 'file' ){
		$jcf_settings = jcf_get_all_settings_from_file();
		$post_types = (array)$jcf_settings->post_types;
		$fieldsets = (array)$jcf_settings->fieldsets;
		$field_settings = (array)$jcf_settings->field_settings;
	}else{
		$jcf_settings = jcf_get_all_settings_from_db();
		$post_types = $jcf_settings['post_types'];
		$fieldsets =$jcf_settings['fieldsets'];
		$field_settings = $jcf_settings['field_settings'];
	}

	if( $_POST['export_fields'] && !empty($_POST['export_data']) ) {
		$export_data = $_POST['export_data'];
		$export_data = json_encode($export_data);
		$filename = 'export.json';
		header('Content-Type: text/json; charset=utf-8');
		header("Content-Disposition: attachment;filename=" . $filename);
		header("Content-Transfer-Encoding: binary ");
		echo $export_data;
	}

	// load template
	include( JCF_ROOT . '/templates/export.tpl.php' );
}

/**
 *	Import page
 */
function jcf_admin_import_page(){
	if($_FILES['import_data']){
		$path_info = pathinfo($_FILES['import_data']['name']);

		if( $path_info['extension'] == 'json'){
			$uploaddir = get_home_path() . "wp-content/uploads/";
			$uploadfile = $uploaddir . basename($_FILES['import_data']['name']);

			if ( copy($_FILES['import_data']['tmp_name'], $uploadfile) ){
				$post_types = jcf_get_settings_from_file($uploadfile);
			}else{
				echo "<h3>Error! The file wasn't loaded!</h3>";
			}
		}else{
			echo "<h3>Error! Check extension of the file!</h3>";
		}
	}else{
		if( $_POST['save_import'] ){
			$import_data = $_POST['import_data'];
			jcf_admin_save_settings_in_db($import_data);
		}
	}

	// load template
	include( JCF_ROOT . '/templates/import.tpl.php' );
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
	
	$all_post_types = get_post_types(array('show_ui' => true ), 'object');
	
	$post_types = array();
	
	foreach($all_post_types as $key=>$val){
		
		//we should exclude 'revision' and 'nav menu items'
		if($val == 'revision' || $val == 'nav_menu_item') continue;
		
		$post_types[$key] = $val;
	}
	
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

// get all settings from db
function jcf_get_all_settings_from_db(){
	global $wpdb;
	$sql_posts_id="SELECT id, post_type FROM " . $wpdb->base_prefix . "posts WHERE post_type = 'post' OR post_type = 'page' OR post_type = 'attachment'";
	$posts = $wpdb->get_results($sql_posts_id);
	$jcf_settings = array();
	$post_types = jcf_get_post_types();
	$fieldsets = array();
	$field_settings = array();
	$field_options = array();
	foreach($post_types as $key => $value){
		$fieldsets[$key] = jcf_fieldsets_get('', 'jcf_fieldsets-'.$key);
		$field_settings[$key] = jcf_field_settings_get('', 'jcf_fields-'.$key);
		foreach($posts as $post){
			foreach($field_settings[$key] as $fskey => $field_setting){
				$field_setting = (array)$field_setting;
				$field_options[$post->post_type][$post->id][$field_setting['slug']] = get_post_meta($post->id, $field_setting['slug'], true);
				if(empty($field_options[$post->post_type][$post->id][$field_setting['slug']])){
					unset($field_options[$post->post_type][$post->id][$field_setting['slug']]);
				}
			}
			if(empty($field_options[$post->post_type][$post->id])){
				unset($field_options[$post->post_type][$post->id]);
			}
		}
	}

	$jcf_settings = array(
		'post_types' => $post_types,
		'fieldsets' => $fieldsets,
		'field_settings' => $field_settings,
		'field_options' => $field_options,
	);
	return $jcf_settings;
}

// get all settings from file
function jcf_get_all_settings_from_file(){
	$filename = get_file_settings_name();
	if (file_exists($filename)) {
		$jcf_settings = jcf_get_settings_from_file($filename);
		return $jcf_settings;
	}else{
		echo _e('The file of settings is not found');
		return false;
	}
}

// get settings from file
function jcf_get_settings_from_file($uploadfile){
	$file = fopen($uploadfile, "r");
	$contents = fread($file, filesize($uploadfile));
	fclose($file);
	$data = json_decode($contents);

	return $data;
}

// save settings to file
function jcf_admin_save_all_settings_in_file($data){
	$fp = fopen(get_template_directory() . '/jcf-settings/jcf_settings.json', 'w+');
	$text = $data . "\r\n";
	$fw = fwrite($fp, $text);
	fclose($fp);
	return true;
}

// save settings in db
function jcf_admin_save_settings_in_db($data){
	foreach($data as $key => $post_type ){
		if(is_array($post_type) && !empty($post_type['fieldsets'])){
			foreach($post_type['fieldsets'] as $fieldset_id => $fieldset){
				$status_fieldset = jcf_import_add_fieldset($fieldset['title'], $key);
				if( empty($status_fieldset) ){
					echo 'Import Error, please check import file'; exit();
				}else{
					$fieldset_id = $status_fieldset;
				}

				if(!empty($fieldset['fields'])){
					foreach($fieldset['fields'] as $field_id => $field){
						$status_field = jcf_import_add_field($field['type'], $fieldset_id, $field, $key );
					}
				}
			}
			if( !empty($status_fieldset) ){
				echo 'Import was success, all fields was imported';
				if( $_POST['file_name'] ){
					unlink($_POST['file_name']);
				}
				
			}
		}
	}
	return true;
}

// get read sttings
function get_read_settings(){
	$jcf_read_settings = get_option('jcf_read_settings');
	return $jcf_read_settings;
}
	
// get file name for all settings
function get_file_settings_name(){
	return get_template_directory() . '/jcf-settings/jcf_settings.json';
}
?>
