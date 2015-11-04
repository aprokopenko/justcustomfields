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
			add_action('admin_print_scripts', array($this, 'add_collection_js') );
		}
		add_action( 'wp_ajax_jcf_add_collection_field', array($this, 'ajax_add_field') );
		add_action( 'wp_ajax_jcf_collection_save_field', array($this, 'jcf_ajax_save_field') );
		
	}
	
		/**
	 *	draw field on post edit form
	 *	you can use $this->instance, $this->entry
	 */
	function field( $args ) {
		extract( $args );
		
		echo $before_widget;
		echo $before_title . $this->instance['title'] . $after_title;		
		
		echo $after_widget;
	}
	
	/**
	 *	save field on post edit form
	 */
	function save( $values ){
		$values = $values['val'];
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
?>
		<p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:', JCF_TEXTDOMAIN); ?></label> <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" /></p>
		<p><label for="<?php echo $this->get_field_id('description'); ?>"><?php _e('Description:', JCF_TEXTDOMAIN); ?></label> <textarea name="<?php echo $this->get_field_name('description'); ?>" id="<?php echo $this->get_field_id('description'); ?>" cols="20" rows="4" class="widefat"><?php echo $description; ?></textarea></p>
<?php
	}
	
	/**
	 *	custom get_field functions to add one more deep level
	 */
	function get_field_id_l2( $field, $number ){
		return $this->get_field_id( $number . '-' . $field );
	}

	function get_field_name_l2( $field, $number ){
		return $this->get_field_name( $number . '][' . $field );
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
	 *	add custom scripts
	 */
	function add_collection_js(){
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
	
	public function ajax_add_field(){
		$field_type =  $_POST['field_type'];
		$fieldset_id = $_POST['fieldset_id'];
		$collection_id = $_POST['collection_id'];
		
		$field_obj = jcf_init_field_object($field_type, $fieldset_id, $collection_id);
		$html = $field_obj->do_form();
		jcf_ajax_reposnse($html, 'html');
	}
	
	
	/**
	 * save field from the form callback
	 */
	public function jcf_ajax_save_field(){

		$field_type =  $_POST['field_id'];
		$fieldset_id = $_POST['fieldset_id'];
		$collection_id = $_POST['collection_id'];
		
		$field_obj = jcf_init_field_object($field_type, $fieldset_id, $collection_id);
		$resp = $field_obj->do_update();
		jcf_ajax_reposnse($resp, 'json');

	}
	
}