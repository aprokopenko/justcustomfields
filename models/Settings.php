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
	const OPT_GOOGLEMAPS = 'jcf_googlemaps_apikey';

	private static $site_domain;

	public $source;
	public $network;
	public $googlemaps_api_key;

	/**
	 * Get source settings
	 *
	 * @param string $default
	 * @return string
	 */
	public static function getDataSourceType( $default = null )
	{
		if ( is_null($default) ) {
			$default = self::CONF_SOURCE_DB;
		}
		return get_site_option(self::OPT_SOURCE, $default);
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
	 * Get google maps api key
	 *
	 * @return string
	 */
	public static function getGoogleMapsApiKey()
	{
		$domain = self::getSiteDomain();
		$googlemaps_settings = get_option(self::OPT_GOOGLEMAPS, array());
		if ( !empty($googlemaps_settings[$domain]) ) {
			return $googlemaps_settings[$domain];
		}
		return '';
	}

	/**
	 * Save settings
	 * @return boolean
	 */
	public function save()
	{
		$this->_updateNetworkMode();
		$this->_updateDataSource();
		$this->_updateGoogleMapsApiKey();
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
	 * Update google maps API key according to the domain.
	 *
	 * @return boolean
	 */
	protected function _updateGoogleMapsApiKey()
	{
		$domain = self::getSiteDomain();
		$googlemaps_settings = get_option(self::OPT_GOOGLEMAPS, array());
		$googlemaps_settings[$domain] = $this->googlemaps_api_key;
		return update_option(self::OPT_GOOGLEMAPS, $googlemaps_settings);
	}

	/**
	 * Get API key based on current domain. Sometimes API key can be restricted to domains, so we take settings only for current domain.
	 *
	 * @return mixed
	 */
	protected static function getSiteDomain()
	{
		if ( empty( self::$site_domain ) ) {
			$site_url = get_site_url();
			self::$site_domain = parse_url($site_url, PHP_URL_HOST);
		}

		return self::$site_domain;
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

		if ( $this->source == self::CONF_SOURCE_FS_THEME ) {
			$path = apply_filters('jcf_config_filepath', get_stylesheet_directory() . '/jcf/config.json', self::CONF_SOURCE_FS_THEME);
			$fs_theme_storage = dirname($path);
			if ( !wp_mkdir_p($fs_theme_storage) || !is_writable($fs_theme_storage) ) {
				$this->addError( 'fs_theme_not_writable' );
			}
		}

		if ( $this->source == self::CONF_SOURCE_FS_GLOBAL ) {
			$path = apply_filters('jcf_config_filepath', WP_CONTENT_DIR . '/jcf/config.json', self::CONF_SOURCE_FS_GLOBAL);
			$fs_global_storage = dirname($path);
			if ( !wp_mkdir_p($fs_global_storage) || !is_writable($fs_global_storage) ) {
				$this->addError('fs_global_not_writable');
			}
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
			$this->network = self::CONF_MS_SITE;
		}

		return ! $this->hasErrors();
	}

}
