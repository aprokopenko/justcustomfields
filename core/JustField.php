<?php

namespace jcf\core;

use jcf\models;
use jcf\core;

class JustField
{
	const POSTTYPE_KIND_PREFIX_TAXONOMY = 'TAX_';
	const POSTTYPE_KIND_PREFIX_POST = '';

	const POSTTYPE_KIND_TAXONOMY = 'taxonomy';
	const POSTTYPE_KIND_POST = 'post';

	/**
	 * Root id for all fields of this type (field type)
	 * @var string
	 */
	public $idBase;
	public static $compatibility = '3.0+'; // compatibility with WP version + it >=, - it <
	public $title; // Name for this field type.
	public $slug = null;
	public $fieldOptions = array(
		'classname' => 'jcf_custom_field',
		'before_widget' => '<div class="form-field">',
		'after_widget' => '</div>',
		'before_title' => '<label>',
		'after_title' => ':</label>'
	);
	public $isNew = false;

	/**
	 * check for change field name if it edit on post edit page
	 */
	public $isPostEdit = false;

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
	public $fieldsetId = '';
	public $collectionId = '';
	public $postType;
	public $postTypeKind = 'post';

	/**
	 * this is field settings (like title, slug etc)
	 * 
	 * @var array
	 */
	public $instance = array();
	public $postID = 0;

	/**
	 * Field data for each post
	 * @var mixed
	 */
	public $entry = null;
	public $fieldErrors = array();

	/**
	 * DataLayer to save instances to
	 *
	 * @var \jcf\core\DataLayer
	 */
	protected $_dL;

	/**
	 * Constructor
	 */
	public function __construct( $id_base, $title, $field_options = array() )
	{
		$this->idBase = $id_base;
		$this->title = $title;
		$this->fieldOptions = array_merge($this->fieldOptions, $field_options);

		// init data layer
		$this->_dL = DataLayerFactory::create();
	}

	/**
	 * check field compatibility with WP version
	 * @deprecated
	 */
	public static function checkCompatibility( $compatibility )
	{
		global $wp_version;

		$operator = '<';
		if ( strpos($compatibility, '+') ) {
			$compatibility = substr($compatibility, 0, -1);
			$operator = '>=';
		}
		elseif ( strpos($compatibility, '-') ) {
			$compatibility = substr($compatibility, 0, -1);
		}

		if ( !version_compare($wp_version, $compatibility, $operator) )
			return false;
		return true;
	}

	/**
	 * check, that this field is part of collection
	 */
	public function isCollectionField()
	{
		if ( !empty($this->collectionId) )
			return true;
		return false;
	}

	/**
	 * Check if this field is created for taxonomy.
	 */
	public function isTaxonomyField()
	{
		return ( self::POSTTYPE_KIND_TAXONOMY === $this->postTypeKind );
	}

	/**
	 * set class property $this->fieldsetId
	 * @param   string  $fieldset_id  fieldset string ID
	 */
	public function setFieldset( $fieldset_id )
	{
		$this->fieldsetId = $fieldset_id;
	}

	/**
	 * set class property $this->collectionId
	 * @param   string  $fieldset_id  fieldset string ID
	 */
	public function setCollection( $collection_id )
	{
		$this->collectionId = $collection_id;
	}

	/**
	 * set class propreties "id", "number"
	 * load instance and entries for this field
	 * @param  string  $id  field id (cosist of id_base + number)
	 */
	public function setId( $id )
	{
		$this->id = $id;

		// this is add request. so number is 0
		if ( $this->id == $this->idBase ) {
			$this->number = 0;
			$this->isNew = true;
		}
		// parse number
		else {
			$this->number = str_replace($this->idBase . '-', '', $this->id);

			// load instance data
			$fields = $this->_dL->getFields();
			if ( isset($fields[$this->postType][$this->id]) )
				$this->instance = (array) $fields[$this->postType][$this->id];

			if ( !empty($this->instance) ) {
				$this->slug = $this->instance['slug'];
				$this->fieldOptions['after_title'] .= '<div class="jcf-get-shortcode" rel="' . $this->slug .'">
					<span class="dashicons dashicons-editor-help wp-ui-text-highlight"></span>
				</div>';
			}
		}
	}

	/**
	 * setter for slug
	 * @param  string  $slug  field slug
	 */
	public function setSlug( $slug )
	{
		$this->slug = $this->validateInstanceSlug($slug);
	}

	/**
	 * set post ID and load entry from wp-postmeta
	 * @param  int  $post_ID  post ID variable
	 */
	public function setPostID( $post_ID, $key_from_collection = FALSE )
	{
		$this->postID = $post_ID;

		if ( !empty($this->collectionId) ) {
			// load entry
			if ( !empty($this->slug) ) {
				$fields = $this->_dL->getFields();
				if ( empty($fields[$this->postType][$this->collectionId]) )
					return;

				$collection_slug = $fields[$this->postType][$this->collectionId]['slug'];
				$data = $this->get_meta_data($this->postID, $collection_slug, true);

				if ( isset($data[$key_from_collection][$this->slug]) ) {
					$this->entry = $data[$key_from_collection][$this->slug];
				}
			}
		}
		else {
			// load entry
			if ( !empty($this->slug) ) {
				$this->entry = $this->get_meta_data($this->postID, $this->slug, true);
			}
		}
	}

	/**
	 * Get meta data from post or term based on current postTypeKind
	 *
	 * @param int    $object_id Post or Term ID
	 * @param string $meta_key  Meta data key (identifier)
	 * @param bool   $single    Value is single or not.
	 *
	 * @return mixed|null
	 */
	public function get_meta_data($object_id, $meta_key, $single = false)
	{
		if ( self::POSTTYPE_KIND_POST == $this->postTypeKind ) {
			return get_post_meta($object_id, $meta_key, $single);
		} elseif ( self::POSTTYPE_KIND_TAXONOMY == $this->postTypeKind ) {
			return get_term_meta($object_id, $meta_key, $single);
		} else {
			return null;
		}
	}
	
	/**
	 * Set post type
	 * @param string $post_type
	 */
	public function setPostType( $post_type )
	{
		$this->postType = $post_type;
		if ( 0 === strpos($this->postType, self::POSTTYPE_KIND_PREFIX_TAXONOMY) ) {
			$this->postTypeKind = self::POSTTYPE_KIND_TAXONOMY;
		} else {
			$this->postTypeKind = self::POSTTYPE_KIND_POST;
		}
	}

	/**
	 * generate unique id attribute based on id_base and number
	 * @param  string  $str  string to be converted
	 * @return string
	 */
	public function getFieldId( $str, $delimeter = '-' )
	{
		/**
		 * if is field of collection and itst post edit page create collection field id
		 */
		$params = array(
			'post_type' => $this->postType,
			'field_id' => $this->collectionId,
			'fieldset_id' => $this->fieldsetId
		);
		$field_model = new models\Field();
		$field_model->load($params);

		if ( $this->isCollectionField() && $this->isPostEdit ) {
			$collection = core\JustFieldFactory::create($field_model);
			return str_replace('-', $delimeter, 'field' . $delimeter . $collection->idBase . $delimeter . $collection->number . $delimeter
					. \jcf\components\collection\JustField_Collection::$currentCollectionFieldKey . $delimeter . $this->id . $delimeter . $str);
		}
		return 'field' . $delimeter . $this->idBase . $delimeter . $this->number . $delimeter . $str;
	}

	/**
	 * generate unique name attribute based on id_base and number
	 * @param  string  $str  string to be converted
	 * @return string
	 */
	public function getFieldName( $str )
	{
		/**
		 * if is field of collection and itst post edit page create collection field name
		 */
		$params = array(
			'post_type' => $this->postType,
			'field_id' => $this->collectionId,
			'fieldset_id' => $this->fieldsetId
		);
		$field_model = new models\Field();
		$field_model->load($params);

		if ( $this->isCollectionField() && $this->isPostEdit ) {
			$collection = core\JustFieldFactory::create($field_model);
			return 'field-' . $collection->idBase . '[' . $collection->number . '][' . \jcf\components\collection\JustField_Collection::$currentCollectionFieldKey . '][' . $this->id . '][' . $str . ']';
		}
		return 'field-' . $this->idBase . '[' . $this->number . '][' . $str . ']';
	}

	/**
	 * validates instance. normalize different field values
	 * @param array $instance
	 */
	public function validateInstance( & $instance )
	{
		if ( $instance['_version'] >= 1.4 ) {
			$instance['slug'] = $this->validateInstanceSlug($instance['slug']);
		}
	}

	/**
	 * validate that slug has first underscore
	 * @param string $slug
	 * @return string
	 */
	public function validateInstanceSlug( $slug )
	{
		$slug = trim($slug);

		if ( !empty($slug) && $slug{0} != '_' && !$this->isCollectionField() ) {
			$slug = '_' . $slug;
		}
		return $slug;
	}

	/**
	 * get valid value for instance version
	 * @param array $instance
	 * @return float
	 */
	public function getInstanceVersion( $instance )
	{
		if ( empty($instance['_version']) )
			return 1.34;
		else
			return $instance['_version'];
	}

	/**
	 * method to save field instance to the storage
	 * call $this->update inside
	 * @param array $params for update field
	 * @return boolean
	 */
	public function doUpdate( $field_index, $params = null )
	{
		$input = !is_null($params) ? $params : $_POST['field-' . $this->idBase][$this->number];
		// remove all slashed from values
		foreach ( $input as $var => $value ) {
			if ( is_string($value) ) {
				$input[$var] = stripslashes($value);
			}
		}
		// validate: title should be always there
		if ( empty($input['title']) ) {
			return array( 'status' => '0', 'error' => __('Title field is required.', \JustCustomFields::TEXTDOMAIN) );
		}

		// get values from real class:
		$instance = $this->update($input, $this->instance);
		$instance['title'] = strip_tags($instance['title']);
		$instance['slug'] = strip_tags($input['slug']);
		$instance['enabled'] = (int) @$input['enabled'];

		if ( $this->idBase == 'inputtext' )
			$instance['group_title'] = (int) @$input['group_title'];

		// starting from vers. 1.4 all new fields should be marked with version of the plugin
		if ( $this->isNew ) {
			$instance['_version'] = \JustCustomFields::VERSION;
		}
		// for old records: set 1.34 - last version without versioning the fields
		if ( empty($instance['_version']) ) {
			$instance['_version'] = 1.34;
		}
		$instance['_type'] = $this->idBase;

		// new from version 1.4: validation/normalization
		$this->validateInstance($instance);

		// check for errors
		// IMPORTANT: experimental function
		if ( !empty($this->fieldErrors) ) {
			$errors = implode('\n', $this->fieldErrors);
			return array( 'status' => '0', 'error' => $errors );
		}

		if ( $this->isNew ) {
			$this->number = $field_index;
			$this->id = $this->idBase . '-' . $this->number;
		}

		// check slug field
		if ( empty($instance['slug']) ) {
			$instance['slug'] = '_field_' . $this->idBase . '__' . $this->number;
		}

		$fields = $this->_dL->getFields();

		if ( !$this->isCollectionField() ) {
			// update fieldset
			$fieldsets = $this->_dL->getFieldsets();
			$fieldsets[$this->postType][$this->fieldsetId]['fields'][$this->id] = $instance['enabled'];
			$this->_dL->setFieldsets($fieldsets);
			$this->_dL->saveFieldsetsData();

			$fields[$this->postType][$this->id] = $instance;
		}
		else {
			$instance['field_width'] = $input['field_width'];

			if ( isset($input['group_title']) )
				$instance['group_title'] = true;

			$fields[$this->postType][$this->collectionId]['fields'][$this->id] = $instance;
		}

		$this->_dL->setFields($fields);
		if ( !$this->_dL->saveFieldsData() ) {
			return array(
				'status' => 0,
				'error' => __('Unable to write changes to storage.', \JustCustomFields::TEXTDOMAIN)
			);
		}

		// return status
		$res = array(
			'status' => '1',
			'id' => $this->id,
			'id_base' => $this->idBase,
			'fieldset_id' => $this->fieldsetId,
			'collection_id' => $this->collectionId,
			'is_new' => $this->isNew,
			'instance' => $instance
		);
		return $res;
	}

	/**
	 * method to delete field from the storage
	 * @return boolean
	 */
	public function doDelete()
	{
		$fields = $this->_dL->getFields();

		if ( !empty($this->collectionId) ) {
			unset($fields[$this->postType][$this->collectionId]['fields'][$this->id]);
		}
		else {
			$fieldsets = $this->_dL->getFieldsets();
			unset($fieldsets[$this->postType][$this->fieldsetId]['fields'][$this->id]);
			unset($fields[$this->postType][$this->id]);

			$this->_dL->setFieldsets($fieldsets);
			$this->_dL->saveFieldsetsData();
		}

		$this->_dL->setFields($fields);
		if ( !$this->_dL->saveFieldsData() ) {
			return false;
		}

		return true;
	}

	/**
	 * method to save data from edit post page to postmeta
	 * call $this->save()
	 *
	 * @return boolean;
	 */
	public function doSave()
	{
		// check that number and post_ID is set
		if ( empty($this->postID) || empty($this->number) )
			return false;

		// check that we have data in POST
		if ( $this->idBase != 'checkbox' && (
				empty($_POST['field-' . $this->idBase][$this->number]) ||
				!is_array($_POST['field-' . $this->idBase][$this->number])
				)
		) {
			return false;
		}

		$input = @$_POST['field-' . $this->idBase][$this->number];

		// get real values
		$values = $this->save($input);
		// save to post meta
		$this->update_meta_data($this->postID, $this->slug, $values);

		return true;
	}

	/**
	 * Update meta data for post or term based on current postTypeKind
	 *
	 * @param int    $object_id  Post or Term ID
	 * @param string $meta_key   Meta data key (identifier)
	 * @param mixed  $meta_value Meta value to be saved.
	 *
	 * @return mixed|null
	 */
	public function update_meta_data($object_id, $meta_key, $meta_value)
	{
		if ( self::POSTTYPE_KIND_POST == $this->postTypeKind ) {
			return update_post_meta($object_id, $meta_key, $meta_value);
		} elseif ( self::POSTTYPE_KIND_TAXONOMY == $this->postTypeKind ) {
			return update_term_meta($object_id, $meta_key, $meta_value);
		} else {
			return null;
		}
	}
	
	/**
	 * method that call $this->add_js to enqueue scripts in head section
	 * do this only on post edit page and if at least one field is exists.
	 * do this only once
	 */
	public function doAddJs()
	{
		if ( method_exists($this, 'addJs') ) {
			$this->addJs();
		}
	}

	/**
	 * method that call $this->add_css to enqueue styles in head section
	 * do this only on post edit page and if at least one field is exists.
	 * do this only once
	 */
	public function doAddCss()
	{
		if ( method_exists($this, 'addCss') ) {
			$this->addCss();
		}
	}

	/**
	 * Echo the field post edit form.
	 *
	 * Subclasses should over-ride this function to generate their field code.
	 *
	 * @param array $args  Field options data
	 */
	public function field()
	{
		die('function cf_Field::field() must be over-ridden in a sub-class.');
	}

	/**
	 * Pre-process submitted form values
	 *
	 * Subclasses should over-ride this function to generate their field code.
	 *
	 * @param array $values Form submitted values
	 */
	public function save( $values )
	{
		die('function cf_Field::save() must be over-ridden in a sub-class.');
	}

	/**
	 * Update a particular instance.
	 *
	 * This function should check that $new_instance is set correctly.
	 * The newly calculated value of $instance should be returned.
	 * If "false" is returned, the instance won't be saved/updated.
	 *
	 * @param array $new_instance New settings for this instance as input by the user via form()
	 * @param array $old_instance Old settings for this instance
	 * @return array Settings to save or bool false to cancel saving
	 */
	public function update( $new_instance, $old_instance )
	{
		return $new_instance;
	}

	/**
	 * Echo the settings update form
	 *
	 * @param array $instance Current settings
	 * @return string
	 */
	public function form()
	{
		echo '<p class="no-options-field">' . __('There are no options for this field.', \JustCustomFields::TEXTDOMAIN) . '</p>';
		return 'noform';
	}

	/**
	 * Print shortcode
	 * 
	 * @param array $args shortcode attributes
	 * @return string
	 */
	public function doShortcode( $args )
	{
		$args = array_merge(array(
			'id' => '',
			'class' => '',
			'field' => '',
			'post_id' => '',
			'label' => false,
				), $args);

		$class_names = array(
			"jcf-value",
			"jcf-value-{$this->idBase}",
			"jcf-value-{$this->idBase}-{$this->slug}",
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
			$sc .= $this->shortcodeLabel($args);

		$sc .= $this->shortcodeValue($args);
		$sc .= '</div>';
		return $sc;
	}

	/**
	 * Print field label inside shortcode call
	 * 
	 * @param array $args	shortcode args
	 * @return string
	 */
	public function shortcodeLabel( $args )
	{
		return $args['before_label'] . esc_html($this->instance['title']) . $args['after_label'];
	}

	/**
	 * Print fields values from shortcode
	 * 
	 * @param array $args	shortcode args
	 * @return string
	 */
	public function shortcodeValue( $args )
	{
		return $args['before_value'] . esc_html($this->entry) . $args['after_value'];
	}
}
