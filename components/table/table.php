<?php
/**
 * Class for select multiple list type
 *
 * @package default
 * @author Sergey Samoylov
 */
class Just_Field_Table extends Just_Field{
	
	public function __construct(){
		$field_ops = array( 'classname' => 'field_table' );
		parent::__construct('table', __('Table', JCF_TEXTDOMAIN), $field_ops);
	}
	
	/**
	 *	draw field on post edit form
	 *	you can use $this->instance, $this->entry
	 */
	
	public function field( $args ) {
		extract( $args );
		
		echo $before_widget;
		echo $before_title . $this->instance['title'] . $after_title;
		
		if(empty($this->entry)) $this->entry = array('0' => '');
		// add null element for etalon copy
		$entries = (array)$this->entry;

		// get fields
		$columns = $this->parse_columns_options();

		if( empty($columns) ){
			echo '<p>'.__('Wrong columns configuration. Please check widget settings.', JCF_TEXTDOMAIN).'</p>';
		}

		$count_cols = count($columns);
		$table_head = '<thead>';
		$rows = '';
		
		foreach($entries as $key => $entry){
			if( $key == 0 ){
				$table_head .= '<tr ' . ($key == 0 ? 'class="table-header"' : '') . '><th class="jcf_option_column">Options</th>';
				$first_row = '<tr class="hide"><td>
						<span class="drag-handle" >' . __('move', JCF_TEXTDOMAIN) . '</span>
						<span class="jcf_delete_row" >' . __('delete', JCF_TEXTDOMAIN) . '</span>
					</td>';
			}

			$rows .= '<tr><td>
						<span class="drag-handle" >' . __('move', JCF_TEXTDOMAIN) . '</span>
						<span class="jcf_delete_row" >' . __('delete', JCF_TEXTDOMAIN) . '</span>
					</td>';

			foreach($columns as $col_name => $col_title){
				if( $key == 0 ){
					$table_head .= '<th>' . $col_name . '</th>';
					$first_row .= '<td><input type="text" value=""
									id="' . $this->get_field_id_l2($col_name, '00') . '"
									name="' . $this->get_field_name_l2($col_name, '00') . '"></td>';
				}
				
				$rows .= '<td><input type="text" value="' . ( !empty($entry)? esc_attr($entry[$col_name]) : '' ) . '"
					id="' . $this->get_field_id_l2($col_name, $key) . '"
					name="' . $this->get_field_name_l2($col_name, $key) . '">
				</td>';
			}

			if( $key == 0 ){
				$table_head .= '</tr></thead>';
				$first_row .= '</tr>';
			}
			$rows .= '</tr>';
		}

		if( !empty($columns) ) :
		?>
		<div class="jcf-table jcf-field-container">
			<table class="sortable wp-list-table widefat fixed">
				<?php echo $table_head; ?>
				<?php echo $rows; ?>
				<?php echo $first_row; ?>
			</table>
			<p><a href="#" class="button button-large jcf_add_row"><?php _e('+ Add row', JCF_TEXTDOMAIN); ?></a></p>
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
	public function save( $_values ){
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
	public function update( $new_instance, $old_instance ) {
		$instance = $old_instance;

		$instance['title'] = strip_tags($new_instance['title']);
		$instance['columns'] = strip_tags($new_instance['columns']);
		$instance['description'] = strip_tags($new_instance['description']);
		
		return $instance;
	}

	/**
	 *	print settings form for field
	 */		
	public function form( $instance ) {
		//Defaults
		$instance = wp_parse_args( (array) $instance, array( 'title' => '', 'columns' => '', 'description' => '' ) );

		$title = esc_attr( $instance['title'] );
		$columns = esc_html( $instance['columns'] );
		$description = esc_html($instance['description']);
		?>
		<p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:', JCF_TEXTDOMAIN); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" /></p>
		<p><label for="<?php echo $this->get_field_id('fields'); ?>"><?php _e('Columns:', JCF_TEXTDOMAIN); ?></label>
			<textarea name="<?php echo $this->get_field_name('columns'); ?>" id="<?php echo $this->get_field_id('columns'); ?>" cols="20" rows="4" class="widefat"><?php echo $columns; ?></textarea>
			<br/><small><?php _e('Format: %colname|%coltitle<br/><i>Example: username|User name', JCF_TEXTDOMAIN); ?></i></small></p>
		<p><label for="<?php echo $this->get_field_id('description'); ?>"><?php _e('Description:', JCF_TEXTDOMAIN); ?></label> 
			<textarea name="<?php echo $this->get_field_name('description'); ?>" id="<?php echo $this->get_field_id('description'); ?>" cols="20" rows="2" class="widefat"><?php echo $description; ?></textarea></p>
		<?php
	}
	
	/**
	 *	custom get_field functions to add one more deep level
	 */
	protected function get_field_id_l2( $field, $number ){
		return $this->get_field_id( $number . '-' . $field );
	}

	protected function get_field_name_l2( $field, $number ){
		return $this->get_field_name( $number . '][' . $field );
	}
	
	public function add_js(){
		global $wp_version;
		if($wp_version <= 3.2){
			// ui core
			wp_register_script(
				'jcf-jquery-ui-core',
				WP_PLUGIN_URL.'/just-custom-fields/assets/jquery-ui.min.js',
				array('jquery')
			);
			wp_enqueue_script('jcf-jquery-ui-core');
			wp_register_script(
				'jcf_table',
				WP_PLUGIN_URL.'/just-custom-fields/components/table/table.js',
				array('jcf-jquery-ui-core')
			);
			wp_enqueue_script('jcf_table');
		}
		else{
			wp_register_script(
				'jcf_table',
				WP_PLUGIN_URL.'/just-custom-fields/components/table/table.js',
				array('jquery')
			);
			wp_enqueue_script('jcf_table');
		}
		

		// add text domain if not registered with another component
		global $wp_scripts;
		wp_localize_script( 'jcf_table', 'jcf_textdomain', jcf_get_language_strings() );
	}
	
	public function add_css(){
		wp_register_style('jcf_table', WP_PLUGIN_URL.'/just-custom-fields/components/table/table.css');
		wp_enqueue_style('jcf_table');
	}
	
	/**
	 * parse columns from settings
	 * @return array
	 */
	protected function parse_columns_options(){
		$columns = array();
		$_columns = explode("\n", $this->instance['columns']);
		foreach($_columns as $line){
			$line = trim($line);
			if(strpos($line, '|') !== FALSE ){
				$col_name = explode('|', $line);
				$columns[ $col_name[0] ] = $col_name[1];
			}elseif(!empty($line)){
				$columns[$line] = $line;
			}
		}
		return $columns;
	}
	
	/**
	 *	print fields values from shortcode
	 */
	public function shortcode_value($args){
		$columns = $this->parse_columns_options();
		if(empty($columns) || empty($this->entry)) return '';

		$count_cols = count($columns);
		$thead_columns = '';
		$html = $rows = '';
		foreach($this->entry as $key => $entry){
			$rows .= '<tr class="jcf-table-row jcf-table-row-i' . $key . '">';
			foreach($columns as $col_name => $col_title){
				if( $key == 0 ){
					$thead_columns .= '<th class="jcf-table-cell jcf-table-cell-' . esc_attr($col_name) . '">' . esc_html($col_title) . '</th>';
				}
				$rows .= '<td class="jcf-table-cell jcf-table-cell-' . esc_attr($col_name) . '">' . esc_html($entry[$col_name]) . '</td>';
			}
			$rows .= '</tr>';
		}

		$html .= '<table class="jcf-table">';
		$html .= '<thead><tr>' . $thead_columns .'</tr></thead>';
		$html .= $rows;
		$html .= '</table>';

		return  $args['before_value'] . $html . $args['after_value'];
	}

}