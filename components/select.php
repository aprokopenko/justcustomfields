<?php
/**
 * Class for select list type
 *
 * @package default
 * @author Alexander Prokopenko
 */
class Just_Field_Select extends Just_Field{
	
	public function __construct(){
		$field_ops = array( 'classname' => 'field_select' );
		parent::__construct('select', __('Select', JCF_TEXTDOMAIN), $field_ops);
	}
	
	/**
	 *	draw field on post edit form
	 *	you can use $this->instance, $this->entry
	 */
	public function field( $args ) {
		extract( $args );
		
		$values = $this->parsed_select_options($this->instance);
		
		echo $before_widget;
		echo $before_title . $this->instance['title'] . $after_title;
		echo '<div class="select-field">';
		echo '<select name="'.$this->get_field_name('val').'" id="'.$this->get_field_id('val').'" style="width: 47%;">';
			echo '<option value="'.esc_attr($this->instance['empty_option']).'" '.selected($this->instance['empty_option'], $this->entry, false).'>'.esc_attr($this->instance['empty_option']).'</option>';
			foreach( (array) $values as $key => $val ) {
				echo '<option value="'.esc_attr($val).'" '.selected($val, $this->entry, false).'>'.esc_html(ucfirst($key)).'</option>' . "\n";
			}
		echo '</select>' . "\n";
		echo '</div>';
		if( !empty($this->instance['description']) )
			echo '<p class="description">' . $this->instance['description'] . '</p>';
		echo $after_widget;
	}
	
	/**
	 *	save field on post edit form
	 */
	public function save( $values ){
		$values = $values['val'];
		
		return $values;
	}
	
	/**
	 *	update instance (settings) for current field
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['options'] = strip_tags($new_instance['options']);
		$instance['description'] = strip_tags($new_instance['description']);
		$instance['empty_option'] = strip_tags($new_instance['empty_option']);
		return $instance;
	}
	/**
	 *	print settings form for field
	 */	
	public function form( $instance ) {
		//Defaults
		$instance = wp_parse_args( (array) $instance, array( 'title' => '', 'description' => '', 'options' => '', 'empty_option' => '' ) );
		$title = esc_attr( $instance['title'] );
		$options = esc_attr( $this->get_instance_select_options($instance) );
		$description = esc_html($instance['description']);
		$empty_option = esc_attr( $instance['empty_option']);
		
		?>
		<p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:', JCF_TEXTDOMAIN); ?></label> <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" /></p>
		<p><label for="<?php echo $this->get_field_id('options'); ?>"><?php _e('Options:', JCF_TEXTDOMAIN); ?></label> 
		<textarea class="widefat" id="<?php echo $this->get_field_id('options'); ?>" name="<?php echo $this->get_field_name('options'); ?>" ><?php echo $options; ?></textarea>
		<br/><small><?php _e('Parameters like (you can use just "label" if "id" is the same):<br>label1|id1<br>label2|id2<br>label3', JCF_TEXTDOMAIN); ?></small></p>
		<p><label for="<?php echo $this->get_field_id('empty_option'); ?>"><?php _e('Empty option:', JCF_TEXTDOMAIN); ?></label><input class="widefat" id="<?php echo $this->get_field_id('empty_option'); ?>" name="<?php echo $this->get_field_name('empty_option'); ?>" placeholder="ex. Choose item from the list"" type="text" value="<?php echo $empty_option; ?>" />
		<br/><small><?php _e('Leave blank to disable empty option', JCF_TEXTDOMAIN); ?></small></p>
		<p><label for="<?php echo $this->get_field_id('description'); ?>"><?php _e('Description:', JCF_TEXTDOMAIN); ?></label> <textarea name="<?php echo $this->get_field_name('description'); ?>" id="<?php echo $this->get_field_id('description'); ?>" cols="20" rows="4" class="widefat"><?php echo $description; ?></textarea></p>
		<?php
	}
	
	/**
	 * get current options settings based on plugin version
	 * 
	 * @param array $instance	current instance
	 */
	public function get_instance_select_options( $instance ){
		// from version 1.4 key for storing select options changed to match it's meaning
		if( $this->get_instance_version($instance) < 1.4 && empty($instance['options']) && !empty($instance['settings']) ){
			return $instance['settings'];
		}
		else{
			return $instance['options'];
		}
	}
	
	/**
	 * prepare list of options
	 * 
	 * @param array $instance	current instance
	 */
	public function parsed_select_options($instance){
		$values = array();
		$settings = $this->get_instance_select_options($instance);
		
		$v = explode("\n", $settings);
		foreach($v as $val){
			$val = trim($val);
			if(strpos($val, '|') !== FALSE ){
				$a = explode('|', $val);
				$values[$a[0]] = $a[1];
			}
			elseif(!empty($val)){
				$values[$val] = $val;
			}
		}
		
		return $values;
	}

	/**
	 * print field values inside the shortcode
	 * 
	 * @params array $args	shortcode args
	 */
	public function shortcode_value( $args ){
		
		$options = $this->parsed_select_options($this->instance);
		$options = array_flip($options);
		$value = $this->entry;
		if ( isset($options[$this->entry]) ){
			$value = $options[$this->entry];
		}
		
		return  $args['before_value'] . $value . $args['after_value'];
	}
}
