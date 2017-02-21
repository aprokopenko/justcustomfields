<?php

namespace jcf\models;

use jcf\core;
use jcf\models;

/**
 * Class DBDataLayer
 *
 * Define DataLayer for Database storage. Can save in single or Multisite modes
 */
class DBDataLayer extends core\DataLayer
{
	const FIELDS_OPTION = 'jcf_fields';
	const FIELDSETS_OPTION = 'jcf_fieldsets';
	const STORAGEVER_OPTION = 'jcf_storage_version';

	/**
	 * Setting chosen by site administrator
	 *
	 * @var string
	 */
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
	 * Setter/Init for $this->_fields property
	 * @param array $fields
	 */
	public function setFields( $fields = null )
	{
		if ( !is_null($fields) ) {
			$this->_fields = $fields;
			return;
		}

		$this->_fields = $this->_getOptions( self::FIELDS_OPTION );
	}

	/**
	 * Update fields in wp-options or wp-site-options table
	 */
	public function saveFieldsData()
	{
		return $this->_updateOptions(self::FIELDS_OPTION, $this->_fields);
	}

	/**
	 * Setter/Init for Fieldsets
	 * @param array $fieldsets
	 */
	public function setFieldsets( $fieldsets = null )
	{
		if ( !is_null($fieldsets) ) {
			$this->_fieldsets = $fieldsets;
			return;
		}

		$this->_fieldsets = $this->_getOptions( self::FIELDSETS_OPTION );
	}

	/**
	 * Save fieldsets
	 */
	public function saveFieldsetsData()
	{
		return $this->_updateOptions(self::FIELDSETS_OPTION, $this->_fieldsets);
	}

	/**
	 * Get storage version
	 * @return array
	 */
	public function getStorageVersion()
	{
		return $this->_getOptions( self::STORAGEVER_OPTION, '' );
	}
	
	/**
	 * Update storage version
	 * @param float|null $version
	 * @return boolean
	 */
	public function saveStorageVersion($version = null)
	{
		if ( empty($version) ) {
			$version = \JustCustomFields::VERSION;
		}

		return $this->_updateOptions(self::STORAGEVER_OPTION, $version);
	}
	
	/**
	 * Check NetworkMode to be set to global (multisite)
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
	 * @param mixed  $default
	 * @return array
	 */
	protected function _getOptions( $key, $default = array() )
	{
		return $this->isSettingsGlobal() ? get_site_option($key, $default) : get_option($key, $default);
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
