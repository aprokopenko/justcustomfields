<?php

namespace jcf\models;

use jcf\core;
use jcf\core\JustField;

/**
 * Class Field
 */
class Field extends core\Model {

	/**
	 * Post type
	 *
	 * @var $post_type
	 */
	public $post_type;

	/**
	 * Field ID
	 *
	 * @var $field_id
	 */
	public $field_id;

	/**
	 * Field Type
	 *
	 * @var $field_type
	 */
	public $field_type;

	/**
	 * Fieldset ID
	 *
	 * @var $fieldset_id
	 */
	public $fieldset_id;

	/**
	 * Collection ID
	 *
	 * @var $collection_id
	 */
	public $collection_id;

	/**
	 * Fields Order
	 *
	 * @var $fields_order
	 */
	public $fields_order;

	/**
	 * Group ID
	 *
	 * @var $group_id
	 */
	public $group_id;

	/**
	 * Get all fields
	 *
	 * @return array
	 */
	public function find_all() {
		return $this->_dl->get_fields();
	}

	/**
	 * Find fields by post_type
	 *
	 * @param string $post_type Post type.
	 *
	 * @return array
	 */
	public function find_by_post_type( $post_type ) {
		$fields = $this->_dl->get_fields();
		if ( ! empty( $fields[ $post_type ] ) ) {
			return $fields[ $post_type ];
		}

		return array();
	}

	/**
	 * Find collection by post_type
	 *
	 * @param string $post_type Post type.
	 *
	 * @return array
	 */
	public function find_collections_by_post_type( $post_type ) {
		$fields      = $this->_dl->get_fields();
		$collections = array();

		if ( ! empty( $fields[ $post_type ] ) ) {
			foreach ( $fields[ $post_type ] as $field_id => $field ) {
				if ( ! empty( $field['fields'] ) ) {
					$collections[ $field_id ] = $field;
				}
			}
		}

		return $collections;
	}

	/**
	 * Save new field
	 *
	 * @param mixed $import Import.
	 *
	 * @return boolean
	 */
	public function save( $import = null ) {
		$field_obj   = core\JustFieldFactory::create( $this );
		$field_index = core\JustFieldFactory::create_field_index( $field_obj->id_base );

		return $field_obj->do_update( $field_index, $import );
	}

	/**
	 * Delete field with $this->_request params
	 *
	 * @return boolean
	 */
	public function delete() {
		$field_obj = core\JustFieldFactory::create( $this );

		return $field_obj->do_delete();
	}

	/**
	 * Sort fields with $this->_request params
	 *
	 * @return boolean
	 */
	public function sort() {
		$order                                                         = trim( $this->fields_order, ',' );
		$fieldsets                                                     = $this->_dl->get_fieldsets();
		$new_fields                                                    = explode( ',', $order );
		$fieldsets[ $this->post_type ][ $this->fieldset_id ]['fields'] = array();

		foreach ( $new_fields as $field_id ) {
			$fieldsets[ $this->post_type ][ $this->fieldset_id ]['fields'][ $field_id ] = $field_id;
		}

		$this->_dl->set_fieldsets( $fieldsets );

		if ( ! $this->_dl->save_fieldsets_data() ) {
			$this->add_error( __( 'Sorting isn\'t changed.', \JustCustomFields::TEXTDOMAIN ) );

			return false;
		}

		return true;
	}

	/**
	 * Sort sollection fields with $this->_request params
	 *
	 * @return boolean
	 */
	public function sort_collection() {
		$fields     = $this->_dl->get_fields();
		$order      = trim( $this->fields_order, ',' );
		$new_sort   = explode( ',', $order );
		$new_fields = array();

		if ( ! empty( $new_sort ) ) {
			foreach ( $new_sort as $field_id ) {
				if ( isset( $fields[ $this->post_type ][ $this->collection_id ]['fields'][ $field_id ] ) ) {
					$new_fields[ $field_id ] = $fields[ $this->post_type ][ $this->collection_id ]['fields'][ $field_id ];
				}
			}
		}

		$fields[ $this->post_type ][ $this->collection_id ]['fields'] = $new_fields;
		$this->_dl->set_fields( $fields );

		if ( ! $this->_dl->save_fields_data() ) {
			$this->add_error( __( 'Sorting isn\'t changed.', \JustCustomFields::TEXTDOMAIN ) );

			return false;
		}

		return true;
	}

	/**
	 * Check what post type kind of given post type ID
	 *
	 * @param string $post_type Post type ID or Prefixed taxonomy ID.
	 *
	 * @return string
	 */
	public static function get_post_type_kind( $post_type ) {
		$kind = JustField::POSTTYPE_KIND_POST;
		if ( 0 === strpos( $post_type, JustField::POSTTYPE_KIND_PREFIX_TAXONOMY ) ) {
			$kind = JustField::POSTTYPE_KIND_TAXONOMY;
		}

		return $kind;
	}

}
