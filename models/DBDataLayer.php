<?php

namespace jcf\models;

use jcf\core;

class DBDataLayer extends core\DataLayer
{

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

		$option_name = 'jsf-fields';
		$this->_fields = $this->_getOptions($option_name);
	}

	/**
	 * 	Update fields in wp-options
	 */
	public function saveFieldsData()
	{
		return $this->_updateOptions('jsf-fields', $this->_fields);
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
	 * Get options with wp-options
	 * @param string $key
	 * @return array
	 */
	protected function _getOptions( $key )
	{
		$multisite_settings = \jcf\models\Settings::getNetworkMode();
		return $multisite_settings == \jcf\models\Settings::CONF_MS_NETWORK ? get_site_option($key, array()) : get_option($key, array());
	}

	/**
	 * 	Update options with wp-options
	 * 	@param string $key Option name
	 * 	@param array $value Values with option name
	 * 	@return bollean
	 */
	protected function _updateOptions( $key, $value )
	{
		$multisite_settings = \jcf\models\Settings::getNetworkMode();
		$multisite_settings == \jcf\models\Settings::CONF_MS_NETWORK ? update_site_option($key, $value) : update_option($key, $value);
		return true;
	}

}
