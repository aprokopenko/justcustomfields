<?php

/**
 *	Fields group field.
 *	allow you to add "table" of fields
 */
class Just_Field_FieldsGroup extends Just_Field{
	
	function Just_Field_FieldsGroup(){
		$field_ops = array( 'classname' => 'field_fieldsgroup' );
		$this->Just_Field( 'fieldsgroup', __('Fields Group', JCF_TEXTDOMAIN), $field_ops);
	}
	
	/**
	 *	draw field on post edit form
	 *	you can use $this->instance, $this->entry
	 */
	function field( $args ) {
		extract( $args );
		
		echo $before_widget;
		echo $before_title . $this->instance['title'] . $after_title;
			
		$del_image = WP_PLUGIN_URL.'/just-custom-fields/components/uploadmedia/assets/jcf-delimage.png';
		$delete_class = ' jcf-hide';
		
		if(empty($this->entry)) $this->entry = array('0' => '');
		// add null element for etalon copy
		$entries = array( '00' => '' ) + (array)$this->entry;

		// get fields
		$fields = array();
		$_fields = explode("\n", $this->instance['fields']);
		foreach($_fields as $line){
			$line = trim($line);
			$field = explode('|', $line);
			if( count($field) == 2 ){
				$fields[ $field[0] ] = $field[1];
			}
		}
		
		if( empty($fields) ){
			echo '<p>'.__('Wrong fields configuration. Please check widget settings.', JCF_TEXTDOMAIN).'</p>';
		}
		
		if( !empty($fields) ) :
		?>
		<div class="jcf-fieldsgroup-field jcf-field-container">
			<?php
			foreach($entries as $key => $entry) : 
			?>
			<div class="jcf-fieldsgroup-row<?php if('00' === $key) echo ' jcf-hide'; ?>">
				<div class="jcf-fieldsgroup-container">
					<?php foreach($fields as $field_name => $field_title) : 
						$field_value = esc_attr(@$entry[$field_name]);
					?>
						<p><?php echo $field_title ?>: <br/>
							<input type="text" value="<?php echo $field_value; ?>" 
								id="<?php echo $this->get_field_id_l2($field_name, $key); ?>" 
								name="<?php echo $this->get_field_name_l2($field_name, $key); ?>">
						</p>
					<?php endforeach; ?>
					<a href="#" class="jcf-btn jcf_delete"><?php _e('Delete', JCF_TEXTDOMAIN); ?></a>
				</div>
				<div class="jcf-delete-layer">
					<img src="<?php echo $del_image; ?>" alt="" />
					<input type="hidden" id="<?php echo $this->get_field_id_l2('__delete__', $key); ?>" name="<?php echo $this->get_field_name_l2('__delete__', $key); ?>" value="" />
					<a href="#" class="jcf-btn jcf_cancel"><?php _e('Cancel', JCF_TEXTDOMAIN); ?></a><br/>
				</div>
			</div>
			<?php endforeach; ?>
			<a href="#" class="jcf-btn jcf_add_more"><?php _e('+ Add another', JCF_TEXTDOMAIN); ?></a>
		</div>
		<?php
		endif; 

		if( $this->instance['description'] != '' )
			echo '<p class="description">' . $this->instance['description'] . '</p>';
		
		echo $after_widget;
		
		return true;
	}
	
	/**
	 *	save field on post edit form
	 */
	function save( $_values ){
		$values = array();
		if(empty($_values)) return $values;
	
		// remove etalon element
		if(isset($_values['00'])) 
			unset($_values['00']);
		
		// fill values
		foreach($_values as $key => $params){
			if(!is_array($params) || !empty($params['__delete__'])){
				continue;
			}
			
			unset($params['__delete__']);
			$values[$key] = $params;
		}
		$values = array_values($values);
		//pa($values,1);
		return $values;
	}
	
	/**
	 *	update instance (settings) for current field
	 */
	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		
		$instance['title'] 			= strip_tags($new_instance['title']);
		$instance['fields'] 		= strip_tags($new_instance['fields']);
		$instance['description'] 	= strip_tags($new_instance['description']);
		
		return $instance;
	}

	/**
	 *	print settings form for field
	 */	
	function form( $instance ) {
		//Defaults
		$instance = wp_parse_args( (array) $instance, array( 'title' => '', 'fields' => '', 'description' => '' ) );

		$title = esc_attr( $instance['title'] );
		$fields = esc_html( $instance['fields'] );
		$description = esc_html($instance['description']);
		?>
		<p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:', JCF_TEXTDOMAIN); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" /></p>
		<p><label for="<?php echo $this->get_field_id('fields'); ?>"><?php _e('Fields:', JCF_TEXTDOMAIN); ?></label> 
			<textarea name="<?php echo $this->get_field_name('fields'); ?>" id="<?php echo $this->get_field_id('fields'); ?>" cols="20" rows="4" class="widefat"><?php echo $fields; ?></textarea>
			<br/><small><?php _e('Format: %fieldname|%fieldtitle<br/><i>Example: price|Product Price', JCF_TEXTDOMAIN); ?></i></small></p>
		<p><label for="<?php echo $this->get_field_id('description'); ?>"><?php _e('Description:', JCF_TEXTDOMAIN); ?></label> 
			<textarea name="<?php echo $this->get_field_name('description'); ?>" id="<?php echo $this->get_field_id('description'); ?>" cols="20" rows="2" class="widefat"><?php echo $description; ?></textarea></p>
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
	 *	add custom scripts
	 */
	function add_js(){
		wp_register_script(
				'jcf_fields_group',
				WP_PLUGIN_URL.'/just-custom-fields/components/fieldsgroup/fields-group.js',
				array('jquery')
			);
		wp_enqueue_script('jcf_fields_group');

		// add text domain if not registered with another component
		global $wp_scripts;
		if( empty($wp_scripts->registered['jcf_related_content']) && empty($wp_scripts->registered['jcf_uploadmedia']) ){
			wp_localize_script( 'jcf_fields_group', 'jcf_textdomain', jcf_get_language_strings() );
		}
	}
	
	function add_css(){
		wp_register_style('jcf_fields_group', WP_PLUGIN_URL.'/just-custom-fields/components/fieldsgroup/fields-group.css');
		wp_enqueue_style('jcf_fields_group');
	}
	
}
?>