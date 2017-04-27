<?php

namespace jcf\models;

use jcf\core;
use jcf\models;

class Fieldset extends core\Model
{
	const POSITION_ADVANCED = 'advanced';
	const POSITION_SIDE = 'side';
	const POSITION_NORMAL = 'normal';

	const PRIO_DEFAULT = 'default';
	const PRIO_HIGH = 'high';
	const PRIO_LOW = 'low';

	public $title;
	public $post_type;
	public $fieldset_id;
	public $fieldsets_order;
	public $position;
	public $priority;

	/**
	 * Return number of registered fields and fieldsets for specific post type
	 * @return array
	 */
	public function getFieldsCounter()
	{
		$fields = $this->_dL->getFields();
		$fieldsets = $this->_dL->getFieldsets();
		$post_types = jcf_get_post_types();
		$taxonomies = jcf_get_taxonomies();

		foreach ( $post_types as $key => $post_type ) {
			$pt = $post_type->name;

			$count[$pt] = array(
				'fieldsets' => 0,
				'fields' => 0,
			);

			if ( empty($fields[$pt]) ) continue;

			$field_keys = array_keys($fields[$pt]);

			if ( !empty($fieldsets[$pt]) ) {
				$count[$pt]['fieldsets'] = count($fieldsets[$pt]);

				foreach ($fieldsets[$pt] as $fieldset) {
					$fieldset_fields = array_keys($fieldset['fields']);
					$live_fields = array_intersect($fieldset_fields, $field_keys);
					$count[$pt]['fields'] += count($live_fields);
				}
			}
		}
		
		foreach ( $taxonomies as $tax_key => $taxonomy ) {
			$count[$tax_key] = array(
				'fieldsets' => 0,
				'fields' => 0,
			);

			if ( empty($fields[$tax_key]) ) continue;

			$field_keys = array_keys($fields[$tax_key]);

			if ( !empty($fieldsets[$tax_key]) ) {
				$count[$tax_key]['fieldsets'] = count($fieldsets[$tax_key]);

				foreach ($fieldsets[$tax_key] as $fieldset) {
					$fieldset_fields = array_keys($fieldset['fields']);
					$live_fields = array_intersect($fieldset_fields, $field_keys);
					$count[$tax_key]['fields'] += count($live_fields);
				}
			}
		}

		return $count;
	}

	/**
	 * Get fields and fieldsets by post_type
	 * @param string $post_type Name post type
	 * @return array
	 */
	public function findByPostType( $post_type )
	{
		$fieldsets = $this->_dL->getFieldsets();
		if ( !empty($fieldsets[$post_type]) )
			return $fieldsets[$post_type];

		return array();
	}

	/**
	 * Get all fieldsets
	 * @return array
	 */
	public function findAll()
	{
		return $this->_dL->getFieldsets();
	}

	/**
	 * Get fieldset by ID
	 * @param string $fieldset_id
	 * @return array
	 */
	public function findById( $fieldset_id )
	{
		$fieldsets = $this->_dL->getFieldsets();
		if ( empty($fieldsets[$this->post_type][$fieldset_id]) ) {
			$this->addError(__('Fieldset not found', \JustCustomFields::TEXTDOMAIN));
			return false;
		}
		return $fieldsets[$this->post_type][$fieldset_id];
	}

	/**
	 * Create new fieldset with $this->_request params
	 * @return boolean
	 */
	public function create()
	{
		if ( empty($this->title) && empty($this->import_data) ) {
			$this->addError(__('Title field is required.', \JustCustomFields::TEXTDOMAIN));
			return false;
		}

		$slug = $this->createSlug();

		$fieldsets = $this->_dL->getFieldsets();

		// check exists
		if ( isset($fieldsets[$this->post_type][$slug]) ) {
			$this->addError(__('Such fieldset already exists.', \JustCustomFields::TEXTDOMAIN));
			return false;
		}

		$fieldsets[$this->post_type][$slug] = array(
			'id' => $slug,
			'title' => $this->title,
			'position' => $this->position,
			'priority' => $this->priority,
			'fields' => array()
		);

		return $this->_save($fieldsets);
	}

	/**
	 * Delete fieldset with $this->_request params
	 * @return boolean
	 */
	public function delete()
	{
		if ( empty($this->fieldset_id) ) {
			$this->addError(__('Wrong params passed.', \JustCustomFields::TEXTDOMAIN));
			return false;
		}

		$fieldsets = $this->_dL->getFieldsets();
		if ( isset($fieldsets[$this->post_type][$this->fieldset_id]) )
			unset($fieldsets[$this->post_type][$this->fieldset_id]);

		return $this->_save($fieldsets);
	}

	/**
	 * Update fieldset with $this->_request params
	 * @return boolean
	 */
	public function update()
	{
		$fieldsets = $this->_dL->getFieldsets();

		if ( empty($fieldsets[$this->post_type][$this->fieldset_id]) ) {
			$this->addError(__('Wrong data passed.', \JustCustomFields::TEXTDOMAIN));
			return false;
		}

		if ( empty($this->title) ) {
			$this->addError(__('Title field is required.', \JustCustomFields::TEXTDOMAIN));
			return false;
		}

		$fieldsets[$this->post_type][$this->fieldset_id]['title'] = $this->title;
		$fieldsets[$this->post_type][$this->fieldset_id]['position'] = $this->position;
		$fieldsets[$this->post_type][$this->fieldset_id]['priority'] = $this->priority;

		return $this->_save($fieldsets);
	}

	/**
	 * Sort fieldsets with $this->_request params
	 * @return boolean
	 */
	public function sort()
	{
		$sort = explode(',', trim($this->fieldsets_order, ','));
		$fieldsets = $this->_dL->getFieldsets();

		$ordered_fieldsets = array();
		foreach ( $sort as $key ) {
			$ordered_fieldsets[$key] = $fieldsets[$this->post_type][$key];
		}

		$fieldsets[$this->post_type] = $ordered_fieldsets;

		if ( !$this->_save($fieldsets) ) {
			$this->addError(__('Sorting isn\'t changed.', \JustCustomFields::TEXTDOMAIN));
			return false;
		}

		return true;
	}

	/**
	 * Create slug for new fieldset
	 * @return string
	 */
	public function createSlug()
	{
		$slug = preg_replace('/[^a-z0-9\-\_\s]/i', '', $this->title);
		$trimed_slug = trim($slug);

		if ( $trimed_slug == '' ) {
			$slug = 'jcf-fieldset-' . rand(0, 10000);
		}
		else {
			$slug = sanitize_title($this->title);
		}
		return $slug;
	}

	/**
	 * Save fieldsets
	 * @param array $fieldsets
	 * @return boolean
	 */
	protected function _save( $fieldsets )
	{
		$this->_dL->setFieldsets($fieldsets);
		$save = $this->_dL->saveFieldsetsData();
		return !empty($save);
	}

	/**
	 * Check what post type kind of given post type ID
	 *
	 * @param string $post_type Post type ID or Prefixed taxonomy ID
	 * @return string
	 */
	public static function getPostTypeKind( $post_type )
	{
		return Field::getPostTypeKind( $post_type );
	}
}
