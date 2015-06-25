<?php
/**
 * Class for select multiple list type
 *
 * @package default
 * @author Sergey Samoylov
 */
class Just_Field_Table extends Just_Field{
	
	function Just_Field_Table(){
		$field_ops = array( 'classname' => 'field_table' );
		$this->Just_Field('table', __('Table', JCF_TEXTDOMAIN), $field_ops);
	}
	
	/**
	 *	draw field on post edit form
	 *	you can use $this->instance, $this->entry
	 */
	function field( $args ) {
		extract( $args );
		
		echo $before_widget;
		echo $before_title . $this->instance['title'] . $after_title;
		?>
		<table class="sortable">
		<?php for( $i = 0; $i <= $this->instance['rows']; $i++ ): ?>
			<tr>
			<?php for( $j = 0; $j <= $this->instance['cols']; $j++ ): ?>
					<?php if( $i == 0 ): ?>
						<?php if( $j == 0 ): ?>
							<th><span class="drag-handle" >move</span></th>
						<?php else : ?>
							<th><input type="text" name="<?php echo $this->get_field_name('val');?>" id="<?php echo $this->get_field_id('val'); ?>" value="" placeholder="column name"/></th>
						<?php endif ; ?>
					<?php else: ?>
						<?php if( $j == 0 ): ?>
							<td><span class="drag-handle" >move</span></td>
						<?php else : ?>
							<td><input type="text" name="<?php echo $this->get_field_name('val');?>" id="<?php echo $this->get_field_id('val'); ?>" value=""/></td>
						<?php endif ; ?>
						
					<?php endif; ?>
			<?php endfor; ?>
			</tr>
		<?php endfor; ?>
		</table>
		<p><a href="#" class="jcf-btn jcf_add_row"><?php _e('+ Add row', JCF_TEXTDOMAIN); ?></a></p>
		<?php
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
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['cols'] = strip_tags($new_instance['cols']);
		$instance['rows'] = strip_tags($new_instance['rows']);
		$instance['description'] = strip_tags($new_instance['description']);
		return $instance;
	}

	/**
	 *	print settings form for field
	 */	
	function form( $instance ) {
		//Defaults
		$instance = wp_parse_args( (array) $instance, array( 'title' => '' ) );

		$title = esc_attr( $instance['title'] );
		$cols = esc_attr( $instance['cols'] );
		$rows = esc_attr( $instance['rows'] );
		$description = esc_html($instance['description']);
		?>
		<p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:', JCF_TEXTDOMAIN); ?></label> <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" /></p>
		<p><label for="<?php echo $this->get_field_id('cols'); ?>"><?php _e('Cols:', JCF_TEXTDOMAIN); ?></label> <input class="widefat" id="<?php echo $this->get_field_id('cols'); ?>" name="<?php echo $this->get_field_name('cols'); ?>" type="text" value="<?php echo $cols; ?>" /></p>
		<p><label for="<?php echo $this->get_field_id('rows'); ?>"><?php _e('Rows:', JCF_TEXTDOMAIN); ?></label> <input class="widefat" id="<?php echo $this->get_field_id('rows'); ?>" name="<?php echo $this->get_field_name('rows'); ?>" type="text" value="<?php echo $rows; ?>" /></p>
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
	
	function add_js(){
		wp_register_script(
				'jcf_table',
				WP_PLUGIN_URL.'/just-custom-fields/components/table/table.js',
				array('jquery')
			);
		wp_enqueue_script('jcf_table');

		// add text domain if not registered with another component
		global $wp_scripts;
		wp_localize_script( 'jcf_table', 'jcf_textdomain', jcf_get_language_strings() );
	}
	
	function add_css(){
		wp_register_style('jcf_table', WP_PLUGIN_URL.'/just-custom-fields/components/table/table.css');
		wp_enqueue_style('jcf_table');
	}
	
}