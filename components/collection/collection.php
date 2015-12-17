<?php
/**
 * Class for Collection type
 *
 * @package default
 * @author Kirill samojlenko
 */
class Just_Field_Collection extends Just_Field{
	
	public static $compatibility = "4.0+";
	
	public static $current_collection_field_key = 0;
	
	public static $field_width = array(
		'100' => '100%',
		'75' => '75%',
		'50' => '50%',
		'33' => '33%',
		'25' => '25%',
	);


	public function __construct(){
		$field_ops = array( 'classname' => 'field_collection' );
		parent::__construct('collection', __('Collection', JCF_TEXTDOMAIN), $field_ops);
		
		add_action('jcf_custom_settings_row', array('Just_Field_Collection', 'settings_row'),10,2);
		
		if( !empty($_GET['page']) && $_GET['page'] == 'just_custom_fields' ){
			//add_action('admin_print_styles', 'jcf_admin_add_styles');
			add_action('admin_print_scripts', array($this, 'add_collection_js') );
		}
		add_action('wp_ajax_jcf_collection_order', array( 'Just_Field_Collection', 'ajax_collection_fields_order' ));
		add_action('wp_ajax_jcf_collection_add_new_field_group', array( 'Just_Field_Collection', 'ajax_return_collection_field_group' ));
		
	}
	
	/**
	 *	draw field on post edit form
	 *	you can use $this->instance, $this->entry
	 */
	function field( $args ) {
		extract( $args );
		
		self::$current_collection_field_key = 0;
		if(empty($this->entry)) $this->entry = array('0' => '');
		$entries = (array)$this->entry;
		echo $before_widget;
		echo $before_title . $this->instance['title'] . $after_title;
		
		if( empty($this->instance['fields']) ) {
			echo '<p class="error">Collection element has no fields registered. Please check component settings</p>';
			echo $after_widget;
			return;
		}
?>
		<div class="collection_fields">
<?php
			foreach($entries as $key => $fields){
?>
				<div class="collection_field_group">
					<h3>
						<span class="dashicons dashicons-editor-justify"></span>
						<span class="collection_group_title">
						<?php
							$group_title = $this->instance['title'].' Item';
							foreach($this->instance['fields'] as $field_id => $field){
								if(isset($field['group_title'])){
									if(isset($fields[$field['slug']])) $group_title = $group_title.' : '.$fields[$field['slug']];
									break;
								}
							}
							echo $group_title;
						 ?>
						</span>
						<a href="#" class="collection_undo_remove_group"><?php _e('UNDO',JCF_TEXTDOMAIN); ?></a>
						<span class="dashicons dashicons-trash"></span>
						
					</h3>
					<div class="collection_field_group_entry">
<?php					
						foreach($this->instance['fields'] as $field_id => $field){
							echo '<div class="collection_field_border jcf_collection_'.(intval($field['field_width'])?$field['field_width']:'100').'">';
							$field_obj = jcf_init_field_object($field_id, $this->fieldset_id, $this->id);
							$field_obj->set_slug($field['slug']);
							if(isset($fields[$field['slug']])){
								$field_obj->entry = $fields[$field['slug']];
							}
							$field_obj->instance = $field;
							$field_obj->is_post_edit = true;
							$field_obj->field($field_obj->field_options);
							echo '</div>';
						}
?>

						<div class="clr"></div>
					</div>
				</div>
<?php
				self::$current_collection_field_key = self::$current_collection_field_key + 1;
			}
?>
			<div class="clr"></div>
			<input type="button" value="<?php echo sprintf(__('Add %s Item', JCF_TEXTDOMAIN),$this->instance['title']); ?>" 
				   class="button button-large jcf_add_more_collection"
				   data-collection_id="<?php echo $this->id; ?>"
				   data-fieldset_id="<?php echo $this->fieldset_id; ?>"
				   name="jcf_add_more_collection">
			<div class="clr"></div>
		</div>
<?php
		echo $after_widget;
	}
	
	/**
	 * return empty collection fields group
	 */
	public static function ajax_return_collection_field_group(){
		$fieldset_id = $_POST['fieldset_id'];
		$collection_id = $_POST['collection_id'];
		$collection = jcf_init_field_object($collection_id, $fieldset_id);
		self::$current_collection_field_key = $_POST['group_id'];
	?>
			<div class="collection_field_group">
				<h3>
					<span class="dashicons dashicons-editor-justify"></span>
					<span class="collection_group_title">
					<?php echo $collection->instance['title'].' Item'; ?>
					</span>
					<span class="dashicons dashicons-trash"></span>

				</h3>
				<div class="collection_field_group_entry">
<?php					
					foreach($collection->instance['fields'] as $field_id => $field){
						echo '<div class="collection_field_border jcf_collection_'.(intval($field['field_width'])?$field['field_width']:'100').'">';
						$field_obj = jcf_init_field_object($field_id, $collection->fieldset_id, $collection->id);
						$field_obj->set_slug($field['slug']);
						$field_obj->instance = $field;
						$field_obj->is_post_edit = true;
						$field_obj->field($field_obj->field_options);
						echo '</div>';
					}
?>
					<div class="clr"></div>
				</div>
			</div>
<?php
		die();
	}
	/**
	 *	save field on post edit form
	 */
	function save( $_values ){
		$values = array();
		foreach($_values as $_value){
			$item = array();
			foreach($this->instance['fields'] as $field_id => $field){
				$field_obj = jcf_init_field_object($field_id, $this->fieldset_id, $this->id);
				if(isset($_value[$field_id])){
					$item[$field['slug']] = $field_obj->save($_value[$field_id]);
				} else {
					$item[$field['slug']] = $field_obj->save(array('val'=>''));
				}
			}
			$values[] = $item;
		}
		return $values;
	}
	
	/**
	 *	update instance (settings) for current field
	 */
	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);
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
		<?php
	}
	
	/**
	 *	add script for collection and custom scripts and styles from collection fields
	 */
	public function add_js(){
		
		wp_register_script(
			'jcf_collection_post_edit',
			WP_PLUGIN_URL.'/just-custom-fields/components/collection/assets/collection_post_edit.js',
			array('jquery')
		);
		wp_enqueue_script('jquery-ui-accordion');
		wp_enqueue_script('jcf_collection_post_edit');
		foreach($this->instance['fields'] as $field_id => $field){
			$field_obj = jcf_init_field_object($field_id, $this->fieldset_id, $this->id);
			if(  method_exists($field_obj, 'add_js')) $field_obj->add_js();
			if(  method_exists($field_obj, 'add_css')) $field_obj->add_css();
		}
	}
	
	/**
	 *	add custom  styles from collection
	 */
	
	public function add_css(){
		wp_register_style('jcf_collection',
				WP_PLUGIN_URL.'/just-custom-fields/components/collection/assets/collection.css',
				array('thickbox'));
		wp_enqueue_style('jcf_collection');
	}
	
	/**
	 *	add custom scripts for jcf fildset edit page
	 */
	public function add_collection_js(){
		wp_register_script(
				'jcf_collections',
				WP_PLUGIN_URL.'/just-custom-fields/components/collection/assets/collection.js',
				array('jquery')
			);
		wp_enqueue_script('jcf_collections');

	}
	
	/**
	 * Get nice name for width attribute
	 * 
	 * @param string $width_key
	 * @return string|null
	 */
	public static function get_width_alias( $width_key ) {
		if ( isset(self::$field_width[$width_key]) ){
			return self::$field_width[$width_key];
		}
		
		return null;
	}
	
	/**
	 * create custom table on jcf settings fields
	 */
	
	public static function settings_row($collection_id, $fieldset_id)
	{
		$jcf_read_settings = jcf_get_read_settings();
		if( $jcf_read_settings == JCF_CONF_SOURCE_DB ){
			$collection = jcf_field_settings_get($collection_id);		
		}
		else{
			$post_type = jcf_get_post_type();
			$jcf_settings = jcf_get_all_settings_from_file();
			$collection = $jcf_settings['field_settings'][ $post_type ][$collection_id];
		}
		$registered_fields = self::register_fields();
		include( JCF_ROOT . '/components/collection/templates/fields_ui.tpl.php' );
	}
	
	/**
	 * registered Fields Type for collection 
	 * @return type
	 */
	public static function register_fields()
	{
		$registered_fields = array();
		$fields = array(
			'Just_Field_Input',
			'Just_Field_Select',
			'Just_Field_SelectMultiple',
			'Just_Field_Checkbox',
			'Just_Field_Textarea',
			'Just_Field_DatePicker',
			'Just_Field_Simple_Media',
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
	 * delete field from collection
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
	
	public static function ajax_collection_fields_order(){
		
		$fieldset_id = $_POST['fieldset_id'];
		$collection_id = $_POST['collection_id'];
		$collection = jcf_init_field_object($collection_id, $fieldset_id);
		$order  = trim($_POST['fields_order'], ',');
		
		$new_fields = explode(',', $order);
		$new_order = array();		
		
		if(! empty($new_fields) && ! empty($collection->instance['fields'])){
			foreach($new_fields as $field_id){
				if(isset($collection->instance['fields'][$field_id])){
					$new_order[$field_id] = $collection->instance['fields'][$field_id];					
				}
			}
		}
		$collection->instance['fields'] = $new_order;
		jcf_field_settings_update($collection_id, $collection->instance, $fieldset_id);
		
		$resp = array('status' => '1');
		jcf_ajax_reposnse($resp, 'json');
	}
	
}