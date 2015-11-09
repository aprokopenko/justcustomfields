<?php
/**
 * Class for Collection type
 *
 * @package default
 * @author Kirill samojlenko
 */
class Just_Collection extends Just_Field{
	
	public static $compatibility = "4.0+";


	public function __construct(){
		$field_ops = array( 'classname' => 'field_collection' );
		parent::__construct('collection', __('Collection', JCF_TEXTDOMAIN), $field_ops);
		
		add_action('jcf_custom_settings_row', array($this, 'settings_row'));
		
		if( !empty($_GET['page']) && $_GET['page'] == 'just_custom_fields' ){
			//add_action('admin_print_styles', 'jcf_admin_add_styles');
			add_action('admin_print_scripts', array($this, 'get_collection_js') );
		}
		
	}
	
		/**
	 *	draw field on post edit form
	 *	you can use $this->instance, $this->entry
	 */
	function field( $args ) {
		extract( $args );
		
		echo $before_widget;
		echo $before_title . $this->instance['title'] . $after_title;
		echo '<div class="jcf-field-container">';
		foreach($this->instance['fields'] as $field_id => $field){
			$field_obj = jcf_init_field_object($field_id, $this->fieldset_id, $this->id);
			$field_obj->set_slug($field['slug']);
			if(isset($this->entry[$field['slug']])){
				$field_obj->entry = $this->entry[$field['slug']];
			}
			$field_obj->instance = $field;
			$field_obj->is_post_edit = true;
			$field_obj->field($field_obj->field_options);
		}
		echo '</div>';
		
		echo $after_widget;
	}
	
	/**
	 *	save field on post edit form
	 */
	function save( $_values ){
		$values = array();
		foreach($this->instance['fields'] as $field_id => $field){
			$field_obj = jcf_init_field_object($field_id, $this->fieldset_id, $this->id);
			if(isset($_values[$field_id])){
				$values[$field['slug']] = $field_obj->save($_values[$field_id]);
			} else {
				$values[$field['slug']] = $field_obj->save(array('val'=>''));
			}
		}
		return $values;
	}
	
	/**
	 *	update instance (settings) for current field
	 */
	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		//var_dump($new_instance);
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['description'] = strip_tags($new_instance['description']);
		$instance['custom_row'] = true;
		return $instance;
	}
	
	/**
	 *	print settings form for field
	 */	
	function form( $instance ) {
		//Defaults
		$instance = wp_parse_args( (array) $instance, array( 'title' => '', 'description' => '' ) );
		$description = esc_html($instance['description']);
		$title = esc_attr( $instance['title'] );
	}
		
	/**
	 * create custom table on jcf settings fields
	 */
	
	public function settings_row($collection_id)
	{
		$jcf_read_settings = jcf_get_read_settings();
		if( $jcf_read_settings == JCF_CONF_SOURCE_DB ){
			$collection = jcf_field_settings_get($collection_id);		
		}
		else{
			$post_type = jcf_get_post_type();
			$jcf_settings = jcf_get_all_settings_from_file();
			$collection = $jcf_settings['field_settings'][ $post_type->name ][$collection_id];
		}
		$registered_fields = $this->register_fields();
		include( JCF_ROOT . '/components/collection/tpl/fields_ui.tpl.php' );
	}
	
	/**
	 *	add custom scripts from collection fields
	 */
	public function add_js(){
		foreach($this->instance['fields'] as $field_id => $field){
			$field_obj = jcf_init_field_object($field_id, $this->fieldset_id, $this->id);
			if(  method_exists($field_obj, 'add_js')) $field_obj->add_js();
			if(  method_exists($field_obj, 'add_css')) $field_obj->add_css();
		}
	}
	/**
	 *	add custom scripts
	 */
	function get_collection_js(){
		wp_register_script(
				'jcf_collections',
				WP_PLUGIN_URL.'/just-custom-fields/components/collection/assets/collection.js',
				array('jquery')
			);
		wp_enqueue_script('jcf_collections');

	}
	
	/**
	 * registered Fields Type for collection 
	 * @return type
	 */
	public function register_fields()
	{
		$registered_fields = array();
		$fields = array(
			'Just_Field_Input',
			'Just_Field_Select',
			'Just_Field_SelectMultiple',
			'Just_Field_Checkbox',
			'Just_Field_Textarea',
			'Just_Field_DatePicker',
			'Just_Simple_Media',
			'Just_Field_Table'
		);
		
		$fields = apply_filters('jcf_collection_get_registered_fields',$fields);//add new field classes for collection fields list
		
		foreach($fields as $class_name){
			if( !class_exists($class_name) ) continue;
			$field_obj = new $class_name();
			$field = array(
				'id_base' => $field_obj->id_base,
				'class_name' => $class_name,
				'title' => $field_obj->title,
			);

			$registered_fields[$field_obj->id_base] = $field;
		}
		
		return $registered_fields;
	}
	
	/**
	 * 
	 */
	public function delete_field($field_id)
	{
		$option_name = jcf_fields_get_option_name();

		$jcf_read_settings = jcf_get_read_settings();
		if( $jcf_read_settings != JCF_CONF_SOURCE_DB ){
			$jcf_settings = jcf_get_all_settings_from_file();
			$post_type =  jcf_get_post_type();
			$fieldset = $jcf_settings['fieldsets'][$post_type][$this->fieldset_id];
			$field_settings = $jcf_settings['field_settings'][$post_type];

			if( isset($field_settings[$this->id]['fields'][$field_id]) ){
				unset($fieldset['fields'][$this->id]['fields'][$field_id]);
				unset($field_settings[$this->id]['fields'][$field_id]);
			}

			$jcf_settings['fieldsets'][$post_type][$this->fieldset_id] = $fieldset;
			$jcf_settings['field_settings'][$post_type] = $field_settings;
			jcf_save_all_settings_in_file($jcf_settings);
		} else {
			$field_settings = jcf_get_options($option_name);
			if(isset($field_settings[$this->id]['fields'][$field_id])){
				unset($field_settings[$this->id]['fields'][$field_id]);
			}

			jcf_update_options($option_name, $field_settings);
		}
				
	}
	
}