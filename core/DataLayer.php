<?php

namespace jcf\core;

/**
 * Abstract class for all data layers
 * Define methods to be defined in every child DataLayer
 */
abstract class DataLayer
{
	/**
	 * fields settings
	 *
	 * @var array
	 */
	protected $_fields = array();

	/**
	 * fieldset settings
	 *
	 * @var array
	 */
	protected $_fieldsets = array();

	/**
	 * DataLayer constructor.
	 *
	 * On create find fields and fieldsets
	 */
	public function __construct()
	{
		$this->setFields();
		$this->setFieldsets();
	}

	/**
	 * Fields settings getter
	 *
	 * @return array
	 */
	public function getFields()
	{
		return $this->_fields;
	}

	/**
	 * Fieldsets settings getter
	 *
	 * @return array
	 */
	public function getFieldsets()
	{
		return $this->_fieldsets;
	}

	/**
	 * Method to get version of storage
	 * 
	 * @return array
	 */
	abstract public function getStorageVersion();

	/**
	 * Method to update version of storage till last
	 *
	 * @param float|null $version
	 * @return boolean
	 */
	abstract public function saveStorageVersion( $version = null );

	/**
	 * Fields settings setter
	 *
	 * @param array|null $fields
	 * @return mixed
	 */
	abstract public function setFields( $fields = null );

	/**
	 * Method to save fields settings into the storage collector
	 *
	 * @return mixed
	 */
	abstract public function saveFieldsData();

	/**
	 * Fieldsets settings setter
	 *
	 * @param array|null $fields
	 * @return mixed
	 */
	abstract public function setFieldsets( $fieldsets = null );

	/**
	 * Method to save fieldsets settings into the storage collector
	 *
	 * @return mixed
	 */
	abstract public function saveFieldsetsData();
}
