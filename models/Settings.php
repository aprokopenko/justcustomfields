<?php

namespace jcf\models;

use jcf\core;

class Settings extends core\Model
{
	const CONF_MS_NETWORK = 'network';
	const CONF_MS_SITE = 'site';
	const CONF_SOURCE_DB = 'database';
	const CONF_SOURCE_FS_THEME = 'fs_theme';
	const CONF_SOURCE_FS_GLOBAL = 'fs_global';

	public $source;
	public $network;

	/**
	 * Get source settings
	 * @return string
	 */
	public static function getDataSourceType()
	{
		return get_site_option('jcf_source_settings', self::CONF_SOURCE_DB);
	}

	/**
	 * Get network settings
	 * @return string
	 */
	public static function getNetworkMode()
	{
		if ( MULTISITE && $network = get_site_option('jcf_multisite_setting') ) {
			return $network;
		}
		return self::CONF_MS_SITE;
	}

	/**
	 * Save settings
	 * @return boolean
	 */
	public function save()
	{
		$this->_updateNetworkMode();
		$this->_updateDataSource();
	}

	/**
	 * Update source data
	 * @return boolean
	 */
	protected function _updateDataSource()
	{
		if ( empty($this->source) ) {
			$error = __('<strong>Settings storage update FAILED!</strong>. Choose an option for the data storage', \JustCustomFields::TEXTDOMAIN);
			$this->addError($error);
		}

		if ( $this->source == self::CONF_SOURCE_FS_THEME && !is_writable(get_stylesheet_directory() . '/jcf-settings/') ) {
			$error = __('<strong>Settings storage update FAILED!</strong>. Check writable permissions of directory ' . get_stylesheet_directory() . '/jcf-settings/', \JustCustomFields::TEXTDOMAIN);
			$this->addError($error);
		}

		if ( $this->source == self::CONF_SOURCE_FS_GLOBAL && !is_writable(get_home_path() . 'wp-content/jcf-settings/') ) {
			$error = __('<strong>Settings storage update FAILED!</strong>. Check writable permissions of directory ' . get_home_path() . 'wp-content/jcf-settings/', \JustCustomFields::TEXTDOMAIN);
			$this->addError($error);
		}

		if ( MULTISITE && ($this->network != self::CONF_MS_NETWORK && $this->source == self::CONF_SOURCE_FS_GLOBAL) ) {
			$error = __('<strong>Settings storage update FAILED!</strong>. Your MultiSite Settings do not allow to set global storage in FileSystem', \JustCustomFields::TEXTDOMAIN);
			$this->addError($error);
		}

		if ( !$this->hasErrors() && update_site_option('jcf_source_settings', $this->source) ) {
			$message = __('<strong>Settings storage</strong> configurations has been updated.', \JustCustomFields::TEXTDOMAIN);
			$this->addMessage($message);
			return true;
		}

		return false;
	}

	/**
	 * Update network data
	 * @return boolean
	 */
	protected function _updateNetworkMode()
	{
		if ( !MULTISITE )
			return false;

		if ( empty($this->network) ) {
			$error = __('<strong>MultiSite settings update FAILED!</strong> Choose an option for the multisite.', \JustCustomFields::TEXTDOMAIN);
			$this->addError($error);
		}

		if ( !$this->hasErrors() && update_site_option('jcf_multisite_setting', $this->network) ) {
			$message = __('<strong>MultiSite settings</strong> has been updated.', \JustCustomFields::TEXTDOMAIN);
			$this->addMessage($message);
			return true;
		}

		return false;
	}

}
