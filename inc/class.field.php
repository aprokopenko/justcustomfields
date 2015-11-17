<?php

class Just_Field{
	
	/**
	 * Root id for all fields of this type (field type)
	 * @var string
	 */
	public $id_base;	
	public static $compatibility = '3.0+'; // compatibility with WP version + it >=, - it <
	public $title;				// Name for this field type.
	public $slug = null;
	public $field_options = array(
		'classname' => 'jcf_custom_field',
		'before_widget' => '<div class="form-field">',
		'after_widget' => '</div>',
		'before_title' => '<label>',
		'after_title' => ':</label>',
	);
	
	public $is_new = false;
	
	/**
	 * check for change field name if it edit on post edit page
	 */
	public $is_post_edit = false;


	/**
	 * Unique ID number of the current instance
	 * 
	 * @var integer 
	 */
	public $number = false;	
	
	/**
	 * Unique ID string of the current instance (id_base-number)
	 * 
	 * @var string
	 */
	public $id = false;
	public $fieldset_id = '';
	public $collection_id = '';
	public $post_type;
	
	/**
	 * this is field settings (like title, slug etc)
	 * 
	 * @var array
	 */
	public $instance = array(); 
	
	public $post_ID = 0;
	
	/**
	 * Field data for each post
	 * @var mixed
	 */
	public $entry = null;
	
	public $field_errors = array();
	
	
	/** 
	 *	Constructor
	 */
	public function __construct( $id_base, $title, $field_options = array() ){
		$this->id_base = $id_base;
		$this->title = $title;
		$this->field_options = array_merge($this->field_options, $field_options);
		$this->post_type = jcf_get_post_type();
	}
	
	/**
	 * check field compatibility with WP version
	 */
	
	public static function checkCompatibility($compatibility){
		global $wp_version;
		
		$operator = '<';
		if(strpos($compatibility, '+')){
			$compatibility = substr($compatibility, 0, -1);
			$operator = '>=';
		} elseif(strpos($compatibility, '-')){
			$compatibility = substr($compatibility, 0, -1);			
		}
		
		if(!version_compare($wp_version, $compatibility, $operator)) return false;
		return true;
	}

	/**
	 * check, that this field is part of collection
	 */
	
	public function is_collection_field(){
		if(!empty($this->collection_id)) return true;
		return false;
	}

		/**
	 *	set class property $this->fieldset_id
	 *	@param   string  $fieldset_id  fieldset string ID
	 */
	public function set_fieldset( $fieldset_id ){
		$this->fieldset_id = $fieldset_id;
	}
	
	/**
	 *	set class property $this->collection_id
	 *	@param   string  $fieldset_id  fieldset string ID
	 */
	public function set_collection( $collection_id ){
		$this->collection_id = $collection_id;
	}
	
	/**
	 *	set class propreties "id", "number"
	 *	load instance and entries for this field
	 *	@param  string  $id  field id (cosist of id_base + number)
	 */
	public function set_id( $id ){
		$this->id = $id;
		// this is add request. so number is 0
		if( $this->id == $this->id_base ){
			$this->number = 0;
			$this->is_new = true;
		}
		// parse number
		else{
			$this->number = str_replace($this->id_base.'-', '', $this->id);

			// load instance data
			$this->instance =(array)jcf_field_settings_get( $this->id );
			if( !empty($this->instance) ){
				$this->slug = $this->instance['slug'];
			}
		}
	}
	
	/**
	 *	setter for slug
	 *	@param  string  $slug  field slug
	 */
	public function set_slug( $slug ){
		$this->slug = $this->validate_instance_slug($slug);
	}

	/**
	 *	set post ID and load entry from wp-postmeta
	 *	@param  int  $post_ID  post ID variable
	 */
	public function set_post_ID( $post_ID ){
		$this->post_ID = $post_ID;
		// load entry
		if( !empty($this->slug) ){
			$this->entry = get_post_meta($this->post_ID, $this->slug, true);
		}
	}
	
	/**
	 *	generate unique id attribute based on id_base and number
	 *	@param  string  $str  string to be converted
	 */
	public function get_field_id( $str, $delimeter = '-' ){
		/**
		 * if is field of collection and itst post edit page create collection field id
		 */
		if( $this->is_collection_field() && $this->is_post_edit ){
			$collection = jcf_init_field_object($this->collection_id, $this->fieldset_id);
			return str_replace('-',$delimeter,'field'.$delimeter.$collection->id_base.$delimeter.$collection->number.$delimeter
					.Just_Field_Collection::$current_collection_field_key.$delimeter.$this->id.$delimeter.$str);
		}
		return 'field'.$delimeter.$this->id_base.$delimeter.$this->number.$delimeter.$str;
	}

	/**
	 *	generate unique name attribute based on id_base and number
	 *	@param  string  $str  string to be converted
	 */
	public function get_field_name( $str ){
		/**
		 * if is field of collection and itst post edit page create collection field name
		 */
		if( $this->is_collection_field() && $this->is_post_edit ){
			$collection = jcf_init_field_object($this->collection_id, $this->fieldset_id);
			return 'field-'.$collection->id_base.'['.$collection->number.']['.Just_Field_Collection::$current_collection_field_key.']['.$this->id.']['.$str.']';
		}
		return 'field-'.$this->id_base.'['.$this->number.']['.$str.']';
	}
	
	/**
	 * validates instance. normalize different field values
	 * @param array $instance
	 */
	public function validate_instance( & $instance ){
		if( $instance['_version'] >= 1.4 ){
			$instance['slug'] = $this->validate_instance_slug($instance['slug']);
		}
	}
	
	/**
	 * validate that slug has first underscore
	 * @param string $slug
	 */
	public function validate_instance_slug( $slug ){
		$slug = trim($slug);
		if( !empty($slug) && $slug{0} != '_' && !$this->is_collection_field() ){
			$slug = '_' . $slug;
		}
		return $slug;
	}
	
	/**
	 * get valid value for instance version
	 * @param array $instance
	 * @return float
	 */
	public function get_instance_version( $instance ){
		if( empty($instance['_version']) ) return 1.34;
		else return $instance['_version'];
	}
	
	/**
	 *	function to show add/edit form to edit field settings
	 *	call $this->form inside
	 */
	public function do_form(){
		ob_start();
		
		$op = ($this->id_base == $this->id)? __('Add', JCF_TEXTDOMAIN) : __('Edit', JCF_TEXTDOMAIN);
		?>
		<div class="jcf_edit_field">
			<h3 class="header"><?php echo $op . ' ' . $this->title; ?></h3>
			<div class="jcf_inner_content">
				<form action="#" method="post" id="<?php echo ( $this->is_collection_field() ? 'jcform_edit_collection_field':'jcform_edit_field');?>">
					<fieldset>
						<input type="hidden" name="field_id" value="<?php echo $this->id; ?>" />
						<input type="hidden" name="field_number" value="<?php echo $this->number; ?>" />
						<input type="hidden" name="field_id_base" value="<?php echo $this->id_base; ?>" />
						<input type="hidden" name="fieldset_id" value="<?php echo $this->fieldset_id; ?>" />
						<?php if( $this->is_collection_field() ) : ?>
							<input type="hidden" name="collection_id" value="<?php echo $this->collection_id; ?>" />
						<?php
							endif;
							$this->form( $this->instance );
							
							// need to add slug field too
							$slug = esc_attr($this->slug);
						?>
						<p>
							<label for="<?php echo $this->get_field_id('slug'); ?>"><?php _e('Slug:', JCF_TEXTDOMAIN); ?></label>
							<input class="widefat" id="<?php echo $this->get_field_id('slug'); ?>" name="<?php echo $this->get_field_name('slug'); ?>" type="text" value="<?php echo $slug; ?>" />
							<br/><small><?php _e('Machine name, will be used for postmeta field name. (should start from underscore)', JCF_TEXTDOMAIN); ?></small>
						</p>
						<?php
							// enabled field
							if( $this->is_new ){
								$this->instance['enabled'] = 1;
							}
						?>
						<p>
							<label for="<?php echo $this->get_field_id('enabled'); ?>">
								<input class="checkbox" type="checkbox" 
										id="<?php echo $this->get_field_id('enabled'); ?>"
										name="<?php echo $this->get_field_name('enabled'); ?>"
										value="1" <?php checked(true, @$this->instance['enabled']); ?> />
								<?php _e('Enabled', JCF_TEXTDOMAIN); ?></label>
						</p>
						<?php if($this->is_collection_field()) : ?>
							<?php if($this->id_base == 'inputtext') : ?>
								<p>
									<label for="<?php echo $this->get_field_id('group_title'); ?>">
										<input class="checkbox" type="checkbox" 
											id="<?php echo $this->get_field_id('group_title'); ?>"
											name="<?php echo $this->get_field_name('group_title'); ?>"
											value="1" <?php checked(true, @$this->instance['group_title']); ?> />
										<?php _e('Use this field as collection item title?', JCF_TEXTDOMAIN); ?>
									</label>
								</p>
						
							<?php endif; ?>
							<p>
								<label for="<?php echo $this->get_field_id('field_width'); ?>"><?php _e('Select Field Width', JCF_TEXTDOMAIN); ?></label>
								<select class="widefat" 
										id="<?php echo $this->get_field_id('field_width'); ?>"
										name="<?php echo $this->get_field_name('field_width'); ?>">
									<?php foreach(Just_Field_Collection::$field_width as $key => $width) : ?>
										<option value="<?php echo $key; ?>"<?php echo (@$this->instance['field_width']==$key?' selected':''); ?>>
											<?php echo $width; ?></option>
									<?php endforeach; ?>
								</select> 
									
							</p>
						<?php endif; ?>
						<div class="field-control-actions">
							<div class="alignleft">
								<?php if( $op != __('Add', JCF_TEXTDOMAIN) ) : ?>
								<a href="#remove" class="field-control-remove"><?php _e('Delete', JCF_TEXTDOMAIN); ?></a> |
								<?php endif; ?>
								<a href="#close" class="field-control-close"><?php _e('Close', JCF_TEXTDOMAIN); ?></a>
							</div>
							<div class="alignright">
								<?php echo print_loader_img(); ?>
								<input type="submit" value="<?php _e('Save', JCF_TEXTDOMAIN); ?>" class="button-primary" name="savefield">
							</div>
							<br class="clear"/>
						</div>
					</fieldset>
				</form>
			</div>
		</div>
		<?php		
		$html = ob_get_clean();
		return $html;
	}
	
	/**
	 *	function to save field instance to the database
	 *	call $this->update inside
	 *	@param array $params for update field
	 */
	public function do_update($params = array()){
		
		$input = !empty($params) ? $params : $_POST['field-'.$this->id_base][$this->number];
		// remove all slashed from values
		foreach($input as $var => $value){
			if( is_string($value) ){
				$input[$var] = stripslashes($value);
			}
		}
		// validate: title should be always there
		if( empty($input['title']) ){
			return array('status' => '0', 'error' => __('Title field is required.', JCF_TEXTDOMAIN));
		}
		
		// get values from real class:
		$instance = $this->update($input, $this->instance);
		$instance['title'] = strip_tags($instance['title']);
		$instance['slug'] = strip_tags($input['slug']);
		$instance['enabled'] = (int)@$input['enabled'];

		// starting from vers. 1.4 all new fields should be marked with version of the plugin
		if( $this->is_new ){
			$instance['_version'] = JCF_VERSION;
		}
		// for old records: set 1.34 - last version without versioning the fields
		if( empty($instance['_version']) ){
			$instance['_version'] = 1.34;
		}
		
		// new from version 1.4: validation/normalization
		$this->validate_instance( $instance );
		
		// check for errors
		// IMPORTANT: experimental function
		if( !empty($this->field_errors) ){
			$errors = implode('\n', $this->field_errors);
			return array('status' => '0', 'error' => $errors);
		}
		
		if( $this->is_new ){
			$this->number = jcf_get_fields_index( $this->id_base );
			$this->id = $this->id_base . '-' . $this->number;
		}
		if( !$this->is_collection_field() ){
			// update fieldset
			$fieldset = jcf_fieldsets_get( $this->fieldset_id );
			$fieldset['fields'][$this->id] = $instance['enabled']; 
			jcf_fieldsets_update( $this->fieldset_id, $fieldset );

			// check slug field
			if( empty($instance['slug']) ){
				$instance['slug'] = '_field_' . $this->id_base . '__' . $this->number;
			}
			// save
			jcf_field_settings_update($this->id, $instance, $this->fieldset_id);

			// return status
			$res = array(
				'status' => '1',
				'id' => $this->id,
				'id_base' => $this->id_base,
				'fieldset_id' => $this->fieldset_id,
				'is_new' => $this->is_new,
				'instance' => $instance,
			);			
		} else {
			$collection = jcf_init_field_object($this->collection_id, $this->fieldset_id);
			// check slug field
			if( empty($instance['slug']) ){
				$instance['slug'] = '_field_' . $this->id_base . '__' . $this->number;
			}
			$instance['field_width'] = $input['field_width'];
			if(isset($input['group_title'])) $instance['group_title'] = true;
			$collection->instance['fields'][$this->id] = $instance;
			// save
			jcf_field_settings_update($this->collection_id, $collection->instance, $this->fieldset_id);
		
			// return status
			$res = array(
				'status' => '1',
				'id' => $this->id,
				'id_base' => $this->id_base,
				'fieldset_id' => $this->fieldset_id,
				'collection_id' => $this->collection_id,
				'is_new' => $this->is_new,
				'instance' => $instance,
			);		
			
		}
		return $res;
	}

	/**
	 *	function to delete field from the database
	 */
	public function do_delete(){
		// remove from fieldset:
		$fieldset = jcf_fieldsets_get( $this->fieldset_id );
		if( isset($fieldset['fields'][$this->id]) )
			unset($fieldset['fields'][$this->id]);
		jcf_fieldsets_update( $this->fieldset_id, $fieldset );

		// remove from fields array
		jcf_field_settings_update($this->id, NULL, $this->fieldset_id);
	}

	/**
	 *	function to save data from edit post page to postmeta
	 *	call $this->save()
	 */
	public function do_save(){
		// check that number and post_ID is set
		if( empty($this->post_ID) || empty($this->number) ) return false;
		
		// check that we have data in POST
		if( $this->id_base != 'checkbox' && (
				empty($_POST['field-'.$this->id_base][$this->number]) ||
				!is_array($_POST['field-'.$this->id_base][$this->number])
			)
		   )
		{
			return false;
		}

		$input = @$_POST['field-'.$this->id_base][$this->number];
		// get real values
		$values = $this->save( $input );
		// save to post meta
		update_post_meta($this->post_ID, $this->slug, $values);
		return true;
	}
	
	/**
	 *	function that call $this->add_js to enqueue scripts in head section
	 *	do this only on post edit page and if at least one field is exists.
	 *	do this only once
	 */
	public function do_add_js(){
		global $jcf_included_assets;
		
		if( !empty($jcf_included_assets['scripts'][get_class($this)]) )
			return false;
		
		if( method_exists($this, 'add_js') ){
			add_action( 'jcf_admin_edit_post_scripts', array($this, 'add_js'), 10 );
		}

		$jcf_included_assets['scripts'][get_class($this)] = 1;
	}
	
	/**
	 *	function that call $this->add_css to enqueue styles in head section
	 *	do this only on post edit page and if at least one field is exists.
	 *	do this only once
	 */
	public function do_add_css(){
		global $jcf_included_assets;
		
		if( !empty($jcf_included_assets['styles'][get_class($this)]) )
			return false;
		
		if( method_exists($this, 'add_css') ){
			add_action( 'jcf_admin_edit_post_styles', array($this, 'add_css'), 10 );
		}

		$jcf_included_assets['styles'][get_class($this)] = 1;
	}

	/** Echo the field post edit form.
	 *
	 * Subclasses should over-ride this function to generate their field code.
	 *
	 * @param array $args  Field options data
	 */
	public function field($args) {
		die('function cf_Field::field() must be over-ridden in a sub-class.');
	}
	
	
	/** Pre-process submitted form values
	 *
	 * Subclasses should over-ride this function to generate their field code.
	 *
	 * @param array $values Form submitted values
	 */
	public function save($values) {
		die('function cf_Field::save() must be over-ridden in a sub-class.');
	}

	/** Update a particular instance.
	 *
	 * This function should check that $new_instance is set correctly.
	 * The newly calculated value of $instance should be returned.
	 * If "false" is returned, the instance won't be saved/updated.
	 *
	 * @param array $new_instance New settings for this instance as input by the user via form()
	 * @param array $old_instance Old settings for this instance
	 * @return array Settings to save or bool false to cancel saving
	 */
	public function update($new_instance, $old_instance) {
		return $new_instance;
	}

	/** Echo the settings update form
	 *
	 * @param array $instance Current settings
	 */
	public function form($instance) {
		echo '<p class="no-options-field">' . __('There are no options for this field.', JCF_TEXTDOMAIN) . '</p>';
		return 'noform';
	}

	/**
	 * print shortcode
	 * 
	 * @param array $args shortcode attributes
	 * @return string
	 */
	public function do_shortcode($args){
		$args = shortcode_atts( array(
			'id' => '',
			'class' => '',
			'field' => '',
			'post_id' => '',
			'label' => false,
		), $args );
		
		$class_names = array(
			"jcf-value",
			"jcf-value-{$this->id_base}",
			"jcf-value-{$this->id_base}-{$this->slug}",
		);
		if ( !empty($args['class']) ) {
			$class_names[] = $args['class'];
		}
		
		$class = implode(' ', $class_names);
		
		$id = "jcf-value-{$this->id}";
		if ( !empty($args['id']) ) {
			$id = $args['id'];
		}
		
		$sc = '<div class="' . $class . '" id="' . $id . '">';
		
		$args['before_label'] = '<div class="jcf-field-label">';
		$args['after_label'] = '</div>';
		$args['before_value'] = '<div class="jcf-field-content">';
		$args['after_value'] = '</div>';

		if ( !empty($args['label']) )
			$sc .= $this->shortcode_label($args);
		
		$sc .= $this->shortcode_value($args);
		
		$sc .= '</div>';
		
		return $sc;
	}
	
	/**
	 * print field label inside shortcode call
	 * 
	 * @param array $args	shortcode args
	 */
	public function shortcode_label($args){
		return $args['before_label'] . $this->instance['title'] . $args['after_label'];
	}

	/**
	 * print fields values from shortcode
	 * 
	 * @param array $args	shortcode args
	 */
	public function shortcode_value($args){
		return  $args['before_value'] . $this->entry . $args['after_value'];
	}
	
}


