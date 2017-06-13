<?php

namespace jcf\core;

use jcf\models;
use jcf\core;

/**
 * Class JustField
 */
class JustField {
	const POSTTYPE_KIND_PREFIX_TAXONOMY = 'TAX_';
	const POSTTYPE_KIND_PREFIX_POST = '';

	const POSTTYPE_KIND_TAXONOMY = 'taxonomy';
	const POSTTYPE_KIND_POST = 'post';

	/**
	 * Root id for all fields of this type (field type)
	 *
	 * @var string
	 */
	public $id_base;

	/**
	 * Compatibility with WP version + it >=, - it <
	 *
	 * @var string
	 */
	public static $compatibility = '3.0+';

	/**
	 * Name for this field type
	 *
	 * @var string
	 */
	public $title;

	/**
	 * Slug for this field type.
	 *
	 * @var string
	 */
	public $slug = null;

	/**
	 * Field options for this field type.
	 *
	 * @var array
	 */
	public $field_options = array(
		'classname'     => 'jcf_custom_field',
		'before_widget' => '<div class="form-field">',
		'after_widget'  => '</div>',
		'before_title'  => '<label>',
		'after_title'   => ':</label>',
	);

	/**
	 * Root id for all fields of this type (field type)
	 *
	 * @var string
	 */
	public $is_new = false;

	/**
	 * Check for change field name if it edit on post edit page
	 *
	 * @var bool
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

	/**
	 * Unique fieldset ID string of the current instance (id_base-number)
	 *
	 * @var string
	 */
	public $fieldset_id = '';

	/**
	 * Unique collection ID string of the current instance (id_base-number)
	 *
	 * @var string
	 */
	public $collection_id = '';

	/**
	 * Post type
	 *
	 * @var string
	 */
	public $post_type;

	/**
	 * Post type kind
	 *
	 * @var string
	 */
	public $post_type_kind = 'post';

	/**
	 * This is field settings (like title, slug etc)
	 *
	 * @var array
	 */
	public $instance = array();

	/**
	 * Unique post ID string of the current instance (id_base-number)
	 *
	 * @var int
	 */
	public $post_id = 0;

	/**
	 * Field data for each post
	 *
	 * @var mixed
	 */
	public $entry = null;

	/**
	 * Field errors for each post
	 *
	 * @var array
	 */
	public $field_errors = array();

	/**
	 * DataLayer to save instances to
	 *
	 * @var \jcf\core\DataLayer
	 */
	protected $_dl;

	/**
	 * Constructor
	 *
	 * @param int    $id_base ID base.
	 * @param string $title Title.
	 * @param array  $field_options Field options.
	 */
	public function __construct( $id_base, $title, $field_options = array() ) {
		$this->id_base       = $id_base;
		$this->title         = $title;
		$this->field_options = array_merge( $this->field_options, $field_options );

		/* init data layer */
		$this->_dl = DataLayerFactory::create();
	}

	/**
	 * Check field compatibility with WP version
	 *
	 * @param string $compatibility Compability.
	 *
	 * @return bool
	 * @deprecated
	 */
	public static function check_compatibility( $compatibility ) {
		global $wp_version;

		$operator = '<';
		if ( strpos( $compatibility, '+' ) ) {
			$compatibility = substr( $compatibility, 0, - 1 );
			$operator      = '>=';
		} elseif ( strpos( $compatibility, '-' ) ) {
			$compatibility = substr( $compatibility, 0, - 1 );
		}

		if ( ! version_compare( $wp_version, $compatibility, $operator ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Check, that this field is part of collection
	 */
	public function is_collection_field() {
		if ( ! empty( $this->collection_id ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Check if this field is created for taxonomy.
	 */
	public function is_taxonomy_field() {
		return ( self::POSTTYPE_KIND_TAXONOMY === $this->post_type_kind );
	}

	/**
	 * Set class property $this->fieldset_id
	 *
	 * @param string $fieldset_id fieldset string ID.
	 */
	public function set_fieldset( $fieldset_id ) {
		$this->fieldset_id = $fieldset_id;
	}

	/**
	 * Set class property $this->collection_id
	 *
	 * @param string $collection_id fieldset string ID.
	 */
	public function set_collection( $collection_id ) {
		$this->collection_id = $collection_id;
	}

	/**
	 * Set class propreties "id", "number"
	 * load instance and entries for this field
	 *
	 * @param  string $id field id (cosist of id_base + number).
	 */
	public function set_id( $id ) {
		$this->id = $id;

		// this is add request. so number is 0.
		if ( $this->id == $this->id_base ) {
			$this->number = 0;
			$this->is_new = true;
		} // parse number
		else {
			$this->number = str_replace( $this->id_base . '-', '', $this->id );

			// load instance data.
			$fields = $this->_dl->get_fields();
			if ( isset( $fields[ $this->post_type ][ $this->id ] ) ) {
				$this->instance = (array) $fields[ $this->post_type ][ $this->id ];
			}

			if ( ! empty( $this->instance ) ) {
				$this->slug                         = $this->instance['slug'];
				$this->field_options['after_title'] .= '<div class="jcf-get-shortcode" rel="' . $this->slug . '">
					<span class="dashicons dashicons-editor-help wp-ui-text-highlight"></span>
				</div>';
			}
		}
	}

	/**
	 * Setter for slug
	 *
	 * @param  string $slug field slug.
	 */
	public function set_slug( $slug ) {
		$this->slug = $this->validate_instance_slug( $slug );
	}

	/**
	 * Set post ID and load entry from wp-postmeta
	 *
	 * @param  int  $post_id post ID variable.
	 * @param  bool $key_from_collection key from collection variable.
	 */
	public function set_post_id( $post_id, $key_from_collection = false ) {
		$this->post_id = $post_id;

		if ( ! empty( $this->collection_id ) ) {
			// load entry.
			if ( ! empty( $this->slug ) ) {
				$fields = $this->_dl->get_fields();
				if ( empty( $fields[ $this->post_type ][ $this->collection_id ] ) ) {
					return;
				}

				$collection_slug = $fields[ $this->post_type ][ $this->collection_id ]['slug'];
				$data            = $this->get_meta_data( $this->post_id, $collection_slug, true );

				if ( isset( $data[ $key_from_collection ][ $this->slug ] ) ) {
					$this->entry = $data[ $key_from_collection ][ $this->slug ];
				}
			}
		} else {
			// load entry.
			if ( ! empty( $this->slug ) ) {
				$this->entry = $this->get_meta_data( $this->post_id, $this->slug, true );
			}
		}
	}

	/**
	 * Get meta data from post or term based on current post_type_kind
	 *
	 * @param int    $object_id Post or Term ID.
	 * @param string $meta_key Meta data key (identifier).
	 * @param bool   $single Value is single or not.
	 *
	 * @return mixed|null
	 */
	public function get_meta_data( $object_id, $meta_key, $single = false ) {
		if ( self::POSTTYPE_KIND_POST == $this->post_type_kind ) {
			return get_post_meta( $object_id, $meta_key, $single );
		} elseif ( self::POSTTYPE_KIND_TAXONOMY == $this->post_type_kind ) {
			return get_term_meta( $object_id, $meta_key, $single );
		} else {
			return null;
		}
	}

	/**
	 * Set post type
	 *
	 * @param string $post_type Post type.
	 */
	public function set_post_type( $post_type ) {
		$this->post_type = $post_type;
		if ( 0 === strpos( $this->post_type, self::POSTTYPE_KIND_PREFIX_TAXONOMY ) ) {
			$this->post_type_kind = self::POSTTYPE_KIND_TAXONOMY;
		} else {
			$this->post_type_kind = self::POSTTYPE_KIND_POST;
		}
	}

	/**
	 * Generate unique id attribute based on id_base and number
	 *
	 * @param  string $str       string to be converted.
	 * @param  string $delimeter string delimiter.
	 *
	 * @return string
	 */
	public function get_field_id( $str, $delimeter = '-' ) {
		/**
		 * If is field of collection and itst post edit page create collection field id.
		 */
		$params      = array(
			'post_type'   => $this->post_type,
			'field_id'    => $this->collection_id,
			'fieldset_id' => $this->fieldset_id,
		);
		$field_model = new models\Field();
		$field_model->load( $params );

		if ( $this->is_collection_field() && $this->is_post_edit ) {
			$collection = core\JustFieldFactory::create( $field_model );

			return str_replace( '-', $delimeter, 'field' . $delimeter . $collection->id_base . $delimeter . $collection->number . $delimeter
			                                     . \jcf\components\collection\JustField_Collection::$current_collection_field_key . $delimeter . $this->id . $delimeter . $str );
		}

		return 'field' . $delimeter . $this->id_base . $delimeter . $this->number . $delimeter . $str;
	}

	/**
	 * Generate unique name attribute based on id_base and number
	 *
	 * @param  string $str string to be converted.
	 *
	 * @return string
	 */
	public function get_field_name( $str ) {
		/**
		 * If is field of collection and itst post edit page create collection field name
		 */
		$params      = array(
			'post_type'   => $this->post_type,
			'field_id'    => $this->collection_id,
			'fieldset_id' => $this->fieldset_id,
		);
		$field_model = new models\Field();
		$field_model->load( $params );

		if ( $this->is_collection_field() && $this->is_post_edit ) {
			$collection = core\JustFieldFactory::create( $field_model );

			return 'field-' . $collection->id_base . '[' . $collection->number . '][' . \jcf\components\collection\JustField_Collection::$current_collection_field_key . '][' . $this->id . '][' . $str . ']';
		}

		return 'field-' . $this->id_base . '[' . $this->number . '][' . $str . ']';
	}

	/**
	 * Validates instance. normalize different field values
	 *
	 * @param array $instance Instance.
	 */
	public function validate_instance( & $instance ) {
		if ( $instance['_version'] >= 1.4 ) {
			$instance['slug'] = $this->validate_instance_slug( $instance['slug'] );
		}
	}

	/**
	 * Validate that slug has first underscore
	 *
	 * @param string $slug Slug.
	 *
	 * @return string
	 */
	public function validate_instance_slug( $slug ) {
		$slug = trim( $slug );

		if ( ! empty( $slug ) && '_' !== $slug{0} && ! $this->is_collection_field() ) {
			$slug = '_' . $slug;
		}

		return $slug;
	}

	/**
	 * Get valid value for instance version
	 *
	 * @param array $instance Instance.
	 *
	 * @return float
	 */
	public function get_instance_version( $instance ) {
		if ( empty( $instance['_version'] ) ) {
			return 1.34;
		} else {
			return $instance['_version'];
		}
	}

	/**
	 * Method to save field instance to the storage
	 * call $this->update inside
	 *
	 * @param array $field_index for update field.
	 * @param array $params      for update field.
	 *
	 * @return array
	 */
	public function do_update( $field_index, $params = null ) {
		$input = ! is_null( $params ) ? $params : $_POST[ 'field-' . $this->id_base ][ $this->number ];
		// remove all slashed from values.
		foreach ( $input as $var => $value ) {
			if ( is_string( $value ) ) {
				$input[ $var ] = stripslashes( $value );
			}
		}
		// validate: title should be always there.
		if ( empty( $input['title'] ) ) {
			return array( 'status' => '0', 'error' => __( 'Title field is required.', \JustCustomFields::TEXTDOMAIN ) );
		}

		// get values from real class.
		$instance            = $this->update( $input, $this->instance );
		$instance['title']   = strip_tags( $instance['title'] );
		$instance['slug']    = strip_tags( $input['slug'] );
		$instance['enabled'] = (int) @$input['enabled'];

		if ( $this->id_base === 'inputtext' ) {
			$instance['group_title'] = (int) @$input['group_title'];
		}

		// starting from vers. 1.4 all new fields should be marked with version of the plugin.
		if ( $this->is_new ) {
			$instance['_version'] = \JustCustomFields::VERSION;
		}
		// for old records: set 1.34 - last version without versioning the fields.
		if ( empty( $instance['_version'] ) ) {
			$instance['_version'] = 1.34;
		}
		$instance['_type'] = $this->id_base;

		// new from version 1.4: validation/normalization.
		$this->validate_instance( $instance );

		// check for errors
		// IMPORTANT: experimental function.
		if ( ! empty( $this->field_errors ) ) {
			$errors = implode( '\n', $this->field_errors );

			return array( 'status' => '0', 'error' => $errors );
		}

		if ( $this->is_new ) {
			$this->number = $field_index;
			$this->id     = $this->id_base . '-' . $this->number;
		}

		// check slug field.
		if ( empty( $instance['slug'] ) ) {
			$instance['slug'] = '_field_' . $this->id_base . '__' . $this->number;
		}

		$fields = $this->_dl->get_fields();

		if ( ! $this->is_collection_field() ) {
			// update fieldset.
			$fieldsets                                                                  = $this->_dl->get_fieldsets();
			$fieldsets[ $this->post_type ][ $this->fieldset_id ]['fields'][ $this->id ] = $instance['enabled'];
			$this->_dl->set_fieldsets( $fieldsets );
			$this->_dl->save_fieldsets_data();

			$fields[ $this->post_type ][ $this->id ] = $instance;
		} else {
			$instance['field_width'] = $input['field_width'];

			if ( isset( $input['group_title'] ) ) {
				$instance['group_title'] = true;
			}

			$fields[ $this->post_type ][ $this->collection_id ]['fields'][ $this->id ] = $instance;
		}

		$this->_dl->set_fields( $fields );
		if ( ! $this->_dl->save_fields_data() ) {
			return array(
				'status' => 0,
				'error'  => __( 'Unable to write changes to storage.', \JustCustomFields::TEXTDOMAIN ),
			);
		}

		// return status.
		$res = array(
			'status'        => '1',
			'id'            => $this->id,
			'id_base'       => $this->id_base,
			'fieldset_id'   => $this->fieldset_id,
			'collection_id' => $this->collection_id,
			'is_new'        => $this->is_new,
			'instance'      => $instance,
		);

		return $res;
	}

	/**
	 * Method to delete field from the storage
	 *
	 * @return boolean
	 */
	public function do_delete() {
		$fields = $this->_dl->get_fields();

		if ( ! empty( $this->collection_id ) ) {
			unset( $fields[ $this->post_type ][ $this->collection_id ]['fields'][ $this->id ] );
		} else {
			$fieldsets = $this->_dl->get_fieldsets();
			unset( $fieldsets[ $this->post_type ][ $this->fieldset_id ]['fields'][ $this->id ] );
			unset( $fields[ $this->post_type ][ $this->id ] );

			$this->_dl->set_fieldsets( $fieldsets );
			$this->_dl->save_fieldsets_data();
		}

		$this->_dl->set_fields( $fields );
		if ( ! $this->_dl->save_fields_data() ) {
			return false;
		}

		return true;
	}

	/**
	 * Method to save data from edit post page to postmeta
	 * call $this->save()
	 *
	 * @return boolean;
	 */
	public function do_save() {
		// check that number and post_ID is set.
		if ( empty( $this->post_id ) || empty( $this->number ) ) {
			return false;
		}

		// check that we have data in POST.
		if ( 'checkbox' !== $this->id_base && (
				empty( $_POST[ 'field-' . $this->id_base ][ $this->number ] ) ||
				! is_array( $_POST[ 'field-' . $this->id_base ][ $this->number ] )
			)
		) {
			return false;
		}

		$input = @$_POST[ 'field-' . $this->id_base ][ $this->number ];

		// get real values.
		$values = $this->save( $input );
		// save to post meta.
		$this->update_meta_data( $this->post_id, $this->slug, $values );

		return true;
	}

	/**
	 * Update meta data for post or term based on current post_type_kind
	 *
	 * @param int    $object_id Post or Term ID.
	 * @param string $meta_key Meta data key (identifier).
	 * @param mixed  $meta_value Meta value to be saved.
	 *
	 * @return mixed|null
	 */
	public function update_meta_data( $object_id, $meta_key, $meta_value ) {
		if ( self::POSTTYPE_KIND_POST == $this->post_type_kind ) {
			return update_post_meta( $object_id, $meta_key, $meta_value );
		} elseif ( self::POSTTYPE_KIND_TAXONOMY == $this->post_type_kind ) {
			return update_term_meta( $object_id, $meta_key, $meta_value );
		} else {
			return null;
		}
	}

	/**
	 * Method that call $this->add_js to enqueue scripts in head section
	 * do this only on post edit page and if at least one field is exists.
	 * do this only once
	 */
	public function do_add_js() {
		if ( method_exists( $this, 'add_js' ) ) {
			$this->add_js();
		}
	}

	/**
	 * Method that call $this->add_css to enqueue styles in head section
	 * do this only on post edit page and if at least one field is exists.
	 * do this only once
	 */
	public function do_add_css() {
		if ( method_exists( $this, 'add_css' ) ) {
			$this->add_css();
		}
	}

	/**
	 * Echo the field post edit form.
	 *
	 * Subclasses should over-ride this function to generate their field code.
	 */
	public function field() {
		die( 'function cf_Field::field() must be over-ridden in a sub-class.' );
	}

	/**
	 * Pre-process submitted form values
	 *
	 * Subclasses should over-ride this function to generate their field code.
	 *
	 * @param array $values Form submitted values.
	 */
	public function save( $values ) {
		die( 'function cf_Field::save() must be over-ridden in a sub-class.' );
	}

	/**
	 * Update a particular instance.
	 *
	 * This function should check that $new_instance is set correctly.
	 * The newly calculated value of $instance should be returned.
	 * If "false" is returned, the instance won't be saved/updated.
	 *
	 * @param array $new_instance New settings for this instance as input by the user via form().
	 * @param array $old_instance Old settings for this instance.
	 *
	 * @return array Settings to save or bool false to cancel saving
	 */
	public function update( $new_instance, $old_instance ) {
		return $new_instance;
	}

	/**
	 * Echo the settings update form
	 *
	 * @return string
	 */
	public function form() {
		echo '<p class="no-options-field">' . __( 'There are no options for this field.', \JustCustomFields::TEXTDOMAIN ) . '</p>';

		return 'noform';
	}

	/**
	 * Print shortcode
	 *
	 * @param array $args shortcode attributes.
	 *
	 * @return string
	 */
	public function do_shortcode( $args ) {
		$args = array_merge( array(
			'id'      => '',
			'class'   => '',
			'field'   => '',
			'post_id' => '',
			'label'   => false,
		), $args );

		$class_names = array(
			'jcf-value',
			"jcf-value-{$this->id_base}",
			"jcf-value-{$this->id_base}-{$this->slug}",
		);

		if ( ! empty( $args['class'] ) ) {
			$class_names[] = $args['class'];
		}

		$class = implode( ' ', $class_names );
		$id    = "jcf-value-{$this->id}";

		if ( ! empty( $args['id'] ) ) {
			$id = $args['id'];
		}

		$sc                   = '<div class="' . $class . '" id="' . $id . '">';
		$args['before_label'] = '<div class="jcf-field-label">';
		$args['after_label']  = '</div>';
		$args['before_value'] = '<div class="jcf-field-content">';
		$args['after_value']  = '</div>';

		if ( ! empty( $args['label'] ) ) {
			$sc .= $this->shortcode_label( $args );
		}

		$sc .= $this->shortcode_value( $args );
		$sc .= '</div>';

		return $sc;
	}

	/**
	 * Print field label inside shortcode call
	 *
	 * @param array $args shortcode args.
	 *
	 * @return string
	 */
	public function shortcode_label( $args ) {
		return $args['before_label'] . esc_html( $this->instance['title'] ) . $args['after_label'];
	}

	/**
	 * Print fields values from shortcode
	 *
	 * @param array $args shortcode args.
	 *
	 * @return string
	 */
	public function shortcode_value( $args ) {
		return $args['before_value'] . esc_html( $this->entry ) . $args['after_value'];
	}
}
