<?php

namespace jcf\core;

/**
 * Abstract class for all data layers
 * Define methods to be defined in every child DataLayer
 */
abstract class DataLayer {
	/**
	 * Fields settings
	 *
	 * @var array
	 */
	protected $_fields = array();

	/**
	 * Fieldset settings
	 *
	 * @var array
	 */
	protected $_fieldsets = array();

	/**
	 * DataLayer constructor.
	 *
	 * On create find fields and fieldsets
	 */
	public function __construct() {
		$this->set_fields();
		$this->set_fieldsets();
	}

	/**
	 * Fields settings getter
	 *
	 * @return array
	 */
	public function get_fields() {
		return $this->_fields;
	}

	/**
	 * Fieldsets settings getter
	 *
	 * @return array
	 */
	public function get_fieldsets() {
		return $this->_fieldsets;
	}

	/**
	 * Method to get version of storage
	 *
	 * @return array
	 */
	abstract public function get_storage_version();

	/**
	 * Method to update version of storage till last
	 *
	 * @param float|null $version Version.
	 *
	 * @return boolean
	 */
	abstract public function save_storage_version( $version = null );

	/**
	 * Fields settings setter
	 *
	 * @param array|null $fields Fields.
	 *
	 * @return mixed
	 */
	abstract public function set_fields( $fields = null );

	/**
	 * Method to save fields settings into the storage collector
	 *
	 * @return mixed
	 */
	abstract public function save_fields_data();

	/**
	 * Fieldsets settings setter
	 *
	 * @param array|null $fieldsets Fieldsets.
	 *
	 * @return mixed
	 */
	abstract public function set_fieldsets( $fieldsets = null );

	/**
	 * Method to save fieldsets settings into the storage collector
	 *
	 * @return mixed
	 */
	abstract public function save_fieldsets_data();
}
