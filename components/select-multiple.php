<?php
/**
 * Class for select multiple list type
 *
 * @package default
 * @author Alexander Prokopenko
 */
class Just_Field_SelectMultiple extends Just_Field{
	
	public function __construct(){
		$field_ops = array( 'classname' => 'field_selectmultiple' );
		parent::__construct('selectmultiple', __('Select Multiple', JCF_TEXTDOMAIN), $field_ops);
	}
	
	/**
	 *	draw field on post edit form
	 *	you can use $this->instance, $this->entry
	 */
	public function field( $args ) {
		extract( $args );
		
		if( !is_array($this->entry) )
			$this->entry = array();

		// prepare options array
		$values = $this->parsed_select_options($this->instance);
			
		echo $before_widget;
		echo $before_title . $this->instance['title'] . $after_title;
		echo '<div class="select_multiple_field">';
		echo '<select name="'.$this->get_field_name('val').'[]" id="'.$this->get_field_id('val').'" multiple="multiple" style="height:200px; width:47%;">';
			foreach( $values as $key => $val ) {
				echo '<option value="'.esc_attr($val).'" '.selected(true, in_array($val, $this->entry), false).'>'.esc_html(ucfirst($key)).'</option>' . "\n";
			}
		echo '</select>' . "\n";
		echo '</div>';
		if( $this->instance['description'] != '' )
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
		$instance['settings'] = strip_tags($new_instance['settings']);
		$instance['description'] = strip_tags($new_instance['description']);
		return $instance;
	}

	/**
	 *	print settings form for field
	 */	
	public function form( $instance ) {
		//Defaults
		$instance = wp_parse_args( (array) $instance, array( 'title' => '', 'description' => '', 'settings' => '' ) );

		$title = esc_attr( $instance['title'] );
		$settings = esc_attr( $instance['settings'] );
		$description = esc_html($instance['description']);
		?>
		<p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:', JCF_TEXTDOMAIN); ?></label> <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" /></p>
		
		<p><label for="<?php echo $this->get_field_id('settings'); ?>"><?php _e('Settings:', JCF_TEXTDOMAIN); ?></label> 
		<textarea class="widefat" id="<?php echo $this->get_field_id('settings'); ?>" name="<?php echo $this->get_field_name('settings'); ?>" ><?php echo $settings; ?></textarea>
		<br/><small><?php _e('Parameters like (you can use just "label" if "id" is the same):<br>label1|id1<br>label2|id2<br>label3', JCF_TEXTDOMAIN); ?></small></p>
		<p><label for="<?php echo $this->get_field_id('description'); ?>"><?php _e('Description:', JCF_TEXTDOMAIN); ?></label> <textarea name="<?php echo $this->get_field_name('description'); ?>" id="<?php echo $this->get_field_id('description'); ?>" cols="20" rows="4" class="widefat"><?php echo $description; ?></textarea></p>
		<?php
	}

	/**
	 * prepare list of options
	 * 
	 * @param array $instance	current instance
	 */
	protected function parsed_select_options($instance){
		$values = array();
		
		$v = explode("\n", $instance['settings']);
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
	 *	print fields values from shortcode
	 */
	public function shortcode_value($args){
		$options = $this->parsed_select_options($this->instance);
		$options = array_flip($options);
		
		if( empty($this->entry) ) return '';
		
		$html = '<ul class="jcf-list">';
		foreach($this->entry as $value){
			$key = preg_replace('/\s+/', '-', $value);
			$key = preg_replace('/[^0-9a-z\-\_]/i', '', $key);
			if(isset($options[$value])){
				$value = $options[$value];
			}
			$html .= "<li class=\"jcf-item jcf-item-$key\">$value</li>\r\n";
		}
		$html .= '</ul>';
		
		return  $args['before_value'] . $html . $args['after_value'];
	}

}
?>