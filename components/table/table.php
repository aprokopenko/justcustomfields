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
		
		if(empty($this->entry)) $this->entry = array('0' => '');
		// add null element for etalon copy
		$entries = (array)$this->entry;

		// get fields
		$_col_names = explode("\n", $this->instance['col_names']);
		foreach($_col_names as $line){
			$line = trim($line);
			$col_name = explode('|', $line);
			if( count($col_name) == 2 ){
				$col_names[ $col_name[0] ] = $col_name[1];
			}
		}
		
		if( empty($col_names) ){
			echo '<p>'.__('Wrong col names configuration. Please check widget settings.', JCF_TEXTDOMAIN).'</p>';
		}
		
		$count_cols = count($col_names);
		$first_td = array();
		
		if( !empty($col_names) ) :
		?>
		<div class="jcf-table jcf-field-container">
			<table class="sortable wp-list-table widefat fixed">
				<?php foreach($entries as $key => $entry) : ?>
					<?php if( $key == 0 ): ?>
						<thead>
					<?php endif; ?>		
					<tr <?php echo $key == 0 ? 'class="table-header"' : ''; ?>>
						<?php if( $key == 0 ): ?>
							<th>Options</th>
						<?php else : ?>
							<td>
								<span class="drag-handle" >move</span>
								<span class="jcf_delete_row" >delete</span>
							</td>
						<?php endif; ?>
						<?php foreach($col_names as $col_name => $col_title) : 
							//if( !empty($entry[$col_name]) ) $col_title = esc_attr(@$entry[$col_name]);
						?>
							<?php if( $key == 0 ): ?>
								<th><?php echo $col_name; ?>
<!--									<input type="text" value="<?php //echo $col_title; ?>" 
									id="<?php //echo $this->get_field_id_l2($col_name, $key); ?>" 
									name="<?php //echo $this->get_field_name_l2($col_name, $key); ?> ">
								</th>-->
								<?php $first_td[] = '<td><input type="text" value="" 
										id="' . $this->get_field_id_l2($col_name, '00') . '" 
										name="' . $this->get_field_name_l2($col_name, '00') . '"></td>';
								?>
							<?php else : ?>
								<td><input type="text" value="<?php echo $col_title; ?>" 
									id="<?php echo $this->get_field_id_l2($col_name, $key); ?>" 
									name="<?php echo $this->get_field_name_l2($col_name, $key); ?> ">
								</td>
							<?php endif; ?>
						<?php endforeach; ?>
					</tr>
					<?php if( $key == 0 ): ?>
						</thead>
					<?php endif; ?>	
				<?php endforeach; ?>
				<tr class="hide">
					<td>
						<span class="drag-handle" >move</span>
						<span class="jcf_delete_row" >delete</span>
					</td>
					<?php foreach($first_td as $td): ?>
						<?php echo $td; ?>
					<?php endforeach; ?>
				</tr>
			</table>
			<p><a href="#" class="jcf-btn jcf_add_row"><?php _e('+ Add row', JCF_TEXTDOMAIN); ?></a></p>
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
		return $values;
	}
	
	/**
	 *	update instance (settings) for current field
	 */
	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['col_names'] = strip_tags($new_instance['col_names']);
		$instance['description'] = strip_tags($new_instance['description']);
		
		return $instance;
	}

	/**
	 *	print settings form for field
	 */		
	function form( $instance ) {
		//Defaults
		$instance = wp_parse_args( (array) $instance, array( 'title' => '', 'col_names' => '', 'description' => '' ) );

		$title = esc_attr( $instance['title'] );
		$col_names = esc_html( $instance['col_names'] );
		$description = esc_html($instance['description']);
		?>
		<p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:', JCF_TEXTDOMAIN); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" /></p>
		<p><label for="<?php echo $this->get_field_id('fields'); ?>"><?php _e('Col names:', JCF_TEXTDOMAIN); ?></label> 
			<textarea name="<?php echo $this->get_field_name('col_names'); ?>" id="<?php echo $this->get_field_id('col_names'); ?>" cols="20" rows="4" class="widefat"><?php echo $col_names; ?></textarea>
			<br/><small><?php _e('Format: %colname|%coltitle<br/><i>Example: username|User name', JCF_TEXTDOMAIN); ?></i></small></p>
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