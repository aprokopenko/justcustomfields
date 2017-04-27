<?php

namespace jcf\models;

use jcf\core;
use jcf\core\JustField;

class Field extends core\Model
{
	public $post_type;
	public $field_id;
	public $field_type;
	public $fieldset_id;
	public $collection_id;
	public $fields_order;
	public $group_id;

	/**
	 * Get all fields
	 * @return array
	 */
	public function findAll()
	{
		return $this->_dL->getFields();
	}

	/**
	 * Find fields by post_type
	 * @param string $post_type
	 * @return array
	 */
	public function findByPostType( $post_type )
	{
		$fields = $this->_dL->getFields();
		if ( !empty($fields[$post_type]) )
			return $fields[$post_type];

		return array();
	}

	/**
	 * Find collection by post_type
	 * @param string $post_type
	 * @return array
	 */
	public function findCollectionsByPostType( $post_type )
	{
		$fields = $this->_dL->getFields();
		$collections = array();

		if ( !empty($fields[$post_type]) ) {
			foreach ( $fields[$post_type] as $field_id => $field ) {
				if ( !empty($field['fields']) )
					$collections[$field_id] = $field;
			}
		}

		return $collections;
	}

	/**
	 * Save new field
	 * @return boolean
	 */
	public function save( $import = null )
	{
		$field_obj = core\JustFieldFactory::create($this);
		$field_index = core\JustFieldFactory::createFieldIndex($field_obj->idBase);

		return $field_obj->doUpdate($field_index, $import);
	}

	/**
	 * Delete field with $this->_request params
	 * @return boolean
	 */
	public function delete()
	{
		$field_obj = core\JustFieldFactory::create($this);

		return $field_obj->doDelete();
	}

	/**
	 * Sort fields with $this->_request params
	 * @return boolean
	 */
	public function sort()
	{
		$order = trim($this->fields_order, ',');
		$fieldsets = $this->_dL->getFieldsets();
		$new_fields = explode(',', $order);
		$fieldsets[$this->post_type][$this->fieldset_id]['fields'] = array();

		foreach ( $new_fields as $field_id ) {
			$fieldsets[$this->post_type][$this->fieldset_id]['fields'][$field_id] = $field_id;
		}

		$this->_dL->setFieldsets($fieldsets);

		if ( !$this->_dL->saveFieldsetsData() ) {
			$this->addError(__('Sorting isn\'t changed.', \JustCustomFields::TEXTDOMAIN));
			return false;
		}

		return true;
	}

	/**
	 * Sort sollection fields with $this->_request params
	 * @return boolean
	 */
	public function sortCollection()
	{
		$fields = $this->_dL->getFields();
		$order = trim($this->fields_order, ',');
		$new_sort = explode(',', $order);
		$new_fields = array();

		if ( !empty($new_sort) ) {
			foreach ( $new_sort as $field_id ) {
				if ( isset($fields[$this->post_type][$this->collection_id]['fields'][$field_id]) ) {
					$new_fields[$field_id] = $fields[$this->post_type][$this->collection_id]['fields'][$field_id];
				}
			}
		}

		$fields[$this->post_type][$this->collection_id]['fields'] = $new_fields;
		$this->_dL->setFields($fields);

		if ( !$this->_dL->saveFieldsData() ) {
			$this->addError(__('Sorting isn\'t changed.', \JustCustomFields::TEXTDOMAIN));
			return false;
		}
		return true;
	}

	/**
	 * Check what post type kind of given post type ID
	 *
	 * @param string $post_type Post type ID or Prefixed taxonomy ID
	 * @return string
	 */
	public static function getPostTypeKind( $post_type )
	{
		$kind = JustField::POSTTYPE_KIND_POST;
		if ( 0 === strpos($post_type, JustField::POSTTYPE_KIND_PREFIX_TAXONOMY) ) {
			$kind = JustField::POSTTYPE_KIND_TAXONOMY;
		}
		return $kind;
	}

}
