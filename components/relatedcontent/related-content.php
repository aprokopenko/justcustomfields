<?php

/**
 *	Fields group field.
 *	allow you to add "table" of fields
 */
class Just_Field_RelatedContent extends Just_Field{
	
	function Just_Field_RelatedContent(){
		$field_ops = array( 'classname' => 'field_relatedcontent' );
		$this->Just_Field( 'relatedcontent', __('Related Content', JCF_TEXTDOMAIN), $field_ops);
		
		// add wp_ajax for autocomplete response
		add_action('wp_ajax_jcf_related_content_autocomplete', array($this, 'autocomplete'));
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
		
		if(empty($this->entry)) $this->entry = array('0' => 0);
		// add null element for etalon copy
		$entries = array( '00' => '' ) + (array)$this->entry;
		//pa($entries,1);
		
		// get posts data
		$type = $this->instance['input_type'];
		$post_type = $this->instance['post_type'];

		$post_types = jcf_get_post_types('object');
		
		if( $type == 'select' ){
			// get posts list
			global $wpdb;
			
			if( $post_type != 'any' ){
				$post_type_where = " post_type = '$post_type' ";
			}
			else{
				// get all post types
				$post_type_where = "( post_type = '" . implode("' OR post_type = '", array_keys($post_types)) . "' )";
			}
			$query = "SELECT ID, post_title, post_status, post_type
				FROM $wpdb->posts
				WHERE $post_type_where AND (post_status = 'publish' OR post_status = 'draft')
				ORDER BY post_title";
			$posts = $wpdb->get_results($query);
			
			$options = array();
			foreach($posts as $p){
				$draft = ( $p->post_status == 'draft' )? ' (DRAFT)' : '';
				$type_label = ( $post_type == 'any' )? ' / '.$post_types[$p->post_type]->labels->singular_name : '';
				$options[ "".$p->ID."" ] = $p->post_title . $draft . $type_label;
			}
		}
		elseif( $type == 'autocomplete' && !empty($this->entry[0]) ){
			global $wpdb;
			$query = "SELECT ID, post_title, post_status, post_type
				FROM $wpdb->posts
				WHERE ID IN(" . implode(',', $this->entry) . ")";
			$posts = $wpdb->get_results($query);
			
			$options = array();
			foreach($posts as $p){
				$draft = ( $p->post_status == 'draft' )? ' (DRAFT)' : '';
				$type_label = ( $post_type == 'any' )? ' / '.$post_types[$p->post_type]->labels->singular_name : '';
				$options[ "".$p->ID."" ] = esc_attr($p->post_title . $draft . $type_label);
			}
		}
		
		?>
		<div class="jcf-relatedcontent-field jcf-field-container">
			<?php
			foreach($entries as $key => $entry) : 
			?>
			<div class="jcf-relatedcontent-row<?php if('00' === $key) echo ' jcf-hide'; ?>">
				<div class="jcf-relatedcontent-container">
					<p>
						<a href="#" class="jcf-btn jcf_delete"><?php _e('Delete', JCF_TEXTDOMAIN); ?></a>
						<?php if( $type == 'select' ) : ?>
							<select id="<?php echo $this->get_field_id_l2('related_id', $key); ?>" 
								name="<?php echo $this->get_field_name_l2('related_id', $key); ?>">
								<option value="">&nbsp;</option>
								<?php foreach($options as $val => $label) : ?>
								<option value="<?php echo $val; ?>" <?php selected($val, $entry); ?>><?php echo $label; ?></option>
								<?php endforeach; ?>
							</select>
						<?php else : // input field for autocomplete ?>
							<input type="text" value="<?php echo @$options[$entry]; ?>" 
								id="<?php echo $this->get_field_id_l2('related_title', $key); ?>" 
								name="<?php echo $this->get_field_name_l2('related_title', $key); ?>" 
								alt="<?php echo $post_type; ?>" />
							<input type="hidden" value="<?php echo $entry; ?>" 
								id="<?php echo $this->get_field_id_l2('related_id', $key); ?>" 
								name="<?php echo $this->get_field_name_l2('related_id', $key); ?>" />
						<?php endif; ?>
					</p>
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
			if(!is_array($params) || !empty($params['__delete__']) || empty($params['related_id'])){
				continue;
			}
			
			unset($params['__delete__']);
			$values[$key] = $params['related_id'];
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
		$instance['post_type'] 		= strip_tags($new_instance['post_type']);
		$instance['input_type'] 	= strip_tags($new_instance['input_type']);
		$instance['description'] 	= strip_tags($new_instance['description']);
		
		return $instance;
	}

	/**
	 *	print settings form for field
	 */	
	function form( $instance ) {
		//Defaults
		$instance = wp_parse_args( (array) $instance, array( 'title' => '', 'post_type' => 'page', 'input_type' => 'autocomplete',
				'description' => __('Start typing entry Title to see the list.', JCF_TEXTDOMAIN) ) );

		$title = esc_attr( $instance['title'] );
		$description = esc_html($instance['description']);
		
		$post_types = jcf_get_post_types( 'object' );
		
		?>
		<p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:', JCF_TEXTDOMAIN); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" /></p>
		
		<p><label for="<?php echo $this->get_field_id('post_type'); ?>"><?php _e('Post type:', JCF_TEXTDOMAIN); ?></label> 
			<select name="<?php echo $this->get_field_name('post_type'); ?>" id="<?php echo $this->get_field_id('post_type'); ?>">
				<option value="any" <?php selected('any', $instance['post_type']); ?>><?php _e('All', JCF_TEXTDOMAIN); ?></option>
				<?php foreach($post_types as $pt_id => $pt) : ?>
				<option value="<?php echo $pt_id; ?>" <?php selected($pt_id, $instance['post_type']); ?>><?php echo $pt->label; ?></option>
				<?php endforeach; ?>
			</select>
		</p>

		<p><label for="<?php echo $this->get_field_id('input_type'); ?>"><?php _e('Input type:', JCF_TEXTDOMAIN); ?></label> 
			<select name="<?php echo $this->get_field_name('input_type'); ?>" id="<?php echo $this->get_field_id('input_type'); ?>">
				<option value="autocomplete" <?php selected('autocomplete', $instance['input_type']); ?>><?php _e('Autocomplete', JCF_TEXTDOMAIN); ?></option>
				<option value="select" <?php selected('select', $instance['input_type']); ?>><?php _e('Dropdown list', JCF_TEXTDOMAIN); ?></option>
			</select>
		</p>
		
		<p><label for="<?php echo $this->get_field_id('description'); ?>"><?php _e('Description:', JCF_TEXTDOMAIN); ?></label> 
			<textarea name="<?php echo $this->get_field_name('description'); ?>" id="<?php echo $this->get_field_id('description'); ?>" cols="20" rows="2" class="widefat"><?php echo $description; ?></textarea></p>
		<?php
	}
	
	/**
	 *	autocomplete
	 */
	function autocomplete(){
		$term = $_POST['term'];
		if(empty($term)) die('');
		
		$post_type = $_POST['post_types'];
		
		$post_types = jcf_get_post_types('object');

		if( $post_type != 'any' ){
			$post_type_where = " post_type = '$post_type' ";
		}
		else{
			// get all post types
			$post_type_where = "( post_type = '" . implode("' OR post_type = '", array_keys($post_types)) . "' )";
		}
		
		global $wpdb;
		$query = "SELECT ID, post_title, post_status, post_type
			FROM $wpdb->posts
			WHERE $post_type_where AND (post_status = 'publish' OR post_status = 'draft') AND post_title LIKE '%$term%'
			ORDER BY post_title";
		$posts = $wpdb->get_results($query);
		
		$response = array();
		foreach($posts as $p){
			$draft = ( $p->post_status == 'draft' )? ' (DRAFT)' : '';
			$type_label = ( $post_type != 'any' )? '' : ' / '.$post_types[$p->post_type]->labels->singular_name;
			$response[] = array(
				'id' => $p->ID,
				'label' => $p->post_title . $draft . $type_label,
				'value' => $p->post_title . $draft . $type_label,
				'type' => $p->post_type,
				'status' => $p->post_status
			);
		}
		
		$json = json_encode($response);
		
		header( "Content-Type: application/json" );
		echo $json;
		exit();
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
		// ui autocomplete
		// TODO: check that autocomplete is realy required
		wp_register_script(
				'ui-autocomplete',
				WP_PLUGIN_URL.'/just-custom-fields/components/relatedcontent/assets/jquery-ui-1.8.14.autocomplete.min.js',
				array('jquery', 'jquery-ui-core', 'jquery-ui-widget', 'jquery-ui-position')
			);
		wp_enqueue_script('ui-autocomplete');
		// multi script
		wp_register_script(
				'jcf_related_content',
				WP_PLUGIN_URL.'/just-custom-fields/components/relatedcontent/related-content.js',
				array('jquery')
			);
		wp_enqueue_script('jcf_related_content');

		// add text domain if not registered with another component
		global $wp_scripts;
		if( empty($wp_scripts->registered['jcf_fields_group']) && empty($wp_scripts->registered['jcf_uploadmedia']) ){
			wp_localize_script( 'jcf_related_content', 'jcf_textdomain', jcf_get_language_strings() );
		}
	}

	function add_css(){
		wp_register_style('ui-autocomplete', WP_PLUGIN_URL.'/just-custom-fields/components/relatedcontent/assets/jquery-ui-1.8.14.autocomplete.css');
		wp_enqueue_style('ui-autocomplete');

		wp_register_style('jcf_related_content', WP_PLUGIN_URL.'/just-custom-fields/components/relatedcontent/related-content.css');
		wp_enqueue_style('jcf_related_content');
	}
	
}
?>