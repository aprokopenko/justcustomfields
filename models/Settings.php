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

	const OPT_SOURCE = 'jcf_source_settings';
	const OPT_MULTISITE = 'jcf_multisite_setting';

	public $source;
	public $network;

	/**
	 * Get source settings
	 * @return string
	 */
	public static function getDataSourceType()
	{
		return get_site_option(self::OPT_SOURCE, self::CONF_SOURCE_DB);
	}

	/**
	 * Get network settings
	 * @return string
	 */
	public static function getNetworkMode()
	{
		if ( MULTISITE && $network = get_site_option(self::OPT_MULTISITE) ) {
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
		if ( !$this->validateDataSource() )
			return false;

		if ( update_site_option(self::OPT_SOURCE, $this->source) ) {
			$this->addMessage('source_updated');
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
		if ( !$this->validateNetworkMode() )
			return false;

		if ( update_site_option(self::OPT_MULTISITE, $this->network) ) {
			$this->addMessage('ms_updated');
			return true;
		}

		return false;
	}

	/**
	 * error messages
	 *
	 * @return array
	 */
	public function messageTemplates()
	{
		return array(
			'empty_source' => __('<strong>Settings storage update FAILED!</strong>. Choose an option for the data storage', \JustCustomFields::TEXTDOMAIN),
			'fs_theme_not_writable' => __('<strong>Settings storage update FAILED!</strong>. Check writable permissions of directory ' . get_stylesheet_directory() . '/jcf-settings/', \JustCustomFields::TEXTDOMAIN),
			'fs_global_not_writable' => __('<strong>Settings storage update FAILED!</strong>. Check writable permissions of directory ' . get_home_path() . 'wp-content/jcf-settings/', \JustCustomFields::TEXTDOMAIN),
			'ms_settings_conflict' => __('<strong>Settings storage update FAILED!</strong>. Your MultiSite Settings do not allow to set global storage in FileSystem', \JustCustomFields::TEXTDOMAIN),

			'empty_ms' => __('<strong>MultiSite settings update FAILED!</strong> Choose an option for the multisite.', \JustCustomFields::TEXTDOMAIN),

			'source_updated' => __('<strong>Settings storage</strong> configurations has been updated.', \JustCustomFields::TEXTDOMAIN),
			'ms_updated' => __('<strong>MultiSite settings</strong> has been updated.', \JustCustomFields::TEXTDOMAIN),
		);
	}

	/**
	 * Validate current value of source attribute
	 * @return bool
	 */
	public function validateDataSource()
	{
		if ( empty($this->source) ) {
			$this->addError('empty_source');
		}

		$fs_theme_storage = get_stylesheet_directory() . '/jcf-settings/';
		if ( $this->source == self::CONF_SOURCE_FS_THEME && (!wp_mkdir_p($fs_theme_storage) || !is_writable($fs_theme_storage)) ) {
			$this->addError('fs_theme_not_writable');
		}

		$fs_global_storage = get_home_path() . 'wp-content/jcf-settings/';
		if ( $this->source == self::CONF_SOURCE_FS_GLOBAL && (!wp_mkdir_p($fs_global_storage) || !is_writable($fs_global_storage)) ) {
			$this->addError('fs_global_not_writable');
		}

		if ( MULTISITE && ($this->network != self::CONF_MS_NETWORK && $this->source == self::CONF_SOURCE_FS_GLOBAL) ) {
			$this->addError('ms_settings_conflict');
		}

		return ! $this->hasErrors();
	}

	/**
	 * validate current value of network mode attribute
	 * @return bool
	 */
	public function validateNetworkMode()
	{
		if ( !MULTISITE )
			return false;

		if ( empty($this->network) ) {
			$this->addError('empty_ms');
		}

		return ! $this->hasErrors();
	}

}
