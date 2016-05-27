<?php

namespace jcf\core;

/**
 * General class for all data layers
 */
abstract class DataLayer
{
	protected $_fields;
	protected $_fieldsets;

	public function __construct()
	{
		$this->setFields();
		$this->setFieldsets();
	}

	public function getFields()
	{
		return $this->_fields;
	}

	public function getFieldsets()
	{
		return $this->_fieldsets;
	}

	abstract public function setFields( $fields = null );

	abstract public function saveFieldsData();

	abstract public function setFieldsets( $fieldsets = null );

	abstract public function saveFieldsetsData();
}
