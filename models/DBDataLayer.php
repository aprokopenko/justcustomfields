<?php

namespace jcf\models;

use jcf\core;
use jcf\models;

class DBDataLayer extends core\DataLayer
{
	protected $_networkMode;

	/**
	 * DBDataLayer constructor.
	 *
	 * Init network mode settings to be used in get/update methods
	 */
	public function __construct()
	{
		$this->_networkMode = models\Settings::getNetworkMode();

		parent::__construct();
	}

	/**
	 * Set $this->_fields property
	 * @param array $fields
	 */
	public function setFields( $fields = null )
	{
		if ( !is_null($fields) ) {
			$this->_fields = $fields;
			return;
		}

		$option_name = 'jcf-fields';
		$this->_fields = $this->_getOptions($option_name);
	}

	/**
	 * 	Update fields in wp-options
	 */
	public function saveFieldsData()
	{
		return $this->_updateOptions('jcf-fields', $this->_fields);
	}

	/**
	 * Get Fieldsets
	 * @param array $fieldsets
	 */
	public function setFieldsets( $fieldsets = null )
	{
		if ( !is_null($fieldsets) ) {
			$this->_fieldsets = $fieldsets;
			return;
		}

		$option_name = 'jcf-fieldsets';
		$this->_fieldsets = $this->_getOptions($option_name);
	}

	/**
	 * Save fieldsets
	 */
	public function saveFieldsetsData()
	{
		return $this->_updateOptions('jcf-fieldsets', $this->_fieldsets);
	}

	/**
	 * Check NetworkMode to be set to global
	 *
	 * @return bool
	 */
	protected function isSettingsGlobal()
	{
		return $this->_networkMode == models\Settings::CONF_MS_NETWORK;
	}

	/**
	 * Get options with wp-options
	 * @param string $key
	 * @return array
	 */
	protected function _getOptions( $key )
	{
		return $this->isSettingsGlobal() ? get_site_option($key, array()) : get_option($key, array());
	}

	/**
	 * 	Update options with wp-options
	 * 	@param string $key Option name
	 * 	@param array $value Values with option name
	 * 	@return boolean
	 */
	protected function _updateOptions( $key, $value )
	{
		$this->isSettingsGlobal() ? update_site_option($key, $value) : update_option($key, $value);
		return true;
	}

}
