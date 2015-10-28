<?php
/**
 * Class for Collection type
 *
 * @package default
 * @author Kirill samojlenko
 */
class Just_Collection extends Just_Field{
	
	function Just_Collection(){
		$field_ops = array( 'classname' => 'field_collection' );
		$this->Just_Field('collection', __('Collection', JCF_TEXTDOMAIN), $field_ops);
		add_action('jcf_custom_settings_row', array($this, 'settings_row'));
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
	
	public function settings_row($field_id){
		
		$jcf_read_settings = jcf_get_read_settings();
		if( $jcf_read_settings == JCF_CONF_SOURCE_DB ){
			$fieldsets = jcf_fieldsets_get($field_id);
			$field_settings = jcf_field_settings_get($field_id);		
		}
		else{
			$post_type = jcf_get_post_type();
			$jcf_settings = jcf_get_all_settings_from_file();
			$fieldsets = $jcf_settings['fieldsets'][ $post_type->name ][$field_id];
			$field_settings = $jcf_settings['field_settings'][ $post_type->name ][$field_id];
		}
			// init field classes and fields array
	jcf_field_register( 'Just_Field_Input' );
	jcf_field_register( 'Just_Field_Select' );
	jcf_field_register( 'Just_Field_SelectMultiple' );
	jcf_field_register( 'Just_Field_Checkbox' );
	jcf_field_register( 'Just_Field_Textarea' );
	jcf_field_register( 'Just_Field_DatePicker' );
	jcf_field_register( 'Just_Simple_Upload' );
	jcf_field_register( 'Just_Field_Upload' );
	jcf_field_register( 'Just_Field_FieldsGroup' );
	jcf_field_register( 'Just_Field_RelatedContent' );
	jcf_field_register( 'Just_Field_Table' );
	jcf_field_register( 'Just_Collection' );
		include( JCF_ROOT . '/components/collection/tpl/fields_ui.tpl.php' );
	}
	
	
	
}