<?php

namespace jcf\models;

use jcf\core;

/**
 * Class Settings
 */
class Settings extends core\Model {
	const CONF_MS_NETWORK = 'network';
	const CONF_MS_SITE = 'site';
	const CONF_SOURCE_DB = 'database';
	const CONF_SOURCE_FS_THEME = 'fs_theme';
	const CONF_SOURCE_FS_GLOBAL = 'fs_global';

	const OPT_SOURCE = 'jcf_source_settings';
	const OPT_MULTISITE = 'jcf_multisite_setting';
	const OPT_GOOGLEMAPS = 'jcf_googlemaps_apikey';

	/**
	 * Site domain
	 *
	 * @var $site_domain
	 */
	private static $site_domain;

	/**
	 * Source
	 *
	 * @var $source
	 */
	public $source;

	/**
	 * Network
	 *
	 * @var $network
	 */
	public $network;

	/**
	 * Googlemaps API key
	 *
	 * @var $googlemaps_api_key
	 */
	public $googlemaps_api_key;

	/**
	 * Get source settings
	 *
	 * @param string $default Default.
	 *
	 * @return string
	 */
	public static function get_data_source_type( $default = null ) {
		if ( is_null( $default ) ) {
			$default = self::CONF_SOURCE_DB;
		}

		return get_site_option( self::OPT_SOURCE, $default );
	}

	/**
	 * Get network settings
	 *
	 * @return string
	 */
	public static function get_network_mode() {
		if ( MULTISITE && $network = get_site_option( self::OPT_MULTISITE ) ) {
			return $network;
		}

		return self::CONF_MS_SITE;
	}

	/**
	 * Get google maps api key
	 *
	 * @return string
	 */
	public static function get_google_maps_api_key() {
		$domain              = self::get_site_domain();
		$googlemaps_settings = get_option( self::OPT_GOOGLEMAPS, array() );
		if ( ! empty( $googlemaps_settings[ $domain ] ) ) {
			return $googlemaps_settings[ $domain ];
		}

		return '';
	}

	/**
	 * Save settings
	 */
	public function save() {
		$this->_update_network_mode();
		$this->_update_data_source();
		$this->_update_google_maps_api_key();
	}

	/**
	 * Update source data
	 *
	 * @return boolean
	 */
	protected function _update_data_source() {
		if ( ! $this->validate_data_source() ) {
			return false;
		}

		if ( update_site_option( self::OPT_SOURCE, $this->source ) ) {
			$this->add_message( 'source_updated' );

			return true;
		}

		return false;
	}

	/**
	 * Update network data
	 *
	 * @return boolean
	 */
	protected function _update_network_mode() {
		if ( ! $this->validate_network_mode() ) {
			return false;
		}

		if ( update_site_option( self::OPT_MULTISITE, $this->network ) ) {
			$this->add_message( 'ms_updated' );

			return true;
		}

		return false;
	}

	/**
	 * Update google maps API key according to the domain.
	 *
	 * @return boolean
	 */
	protected function _update_google_maps_api_key() {
		$domain                         = self::get_site_domain();
		$googlemaps_settings            = get_option( self::OPT_GOOGLEMAPS, array() );
		$googlemaps_settings[ $domain ] = $this->googlemaps_api_key;

		return update_option( self::OPT_GOOGLEMAPS, $googlemaps_settings );
	}

	/**
	 * Get API key based on current domain. Sometimes API key can be restricted to domains, so we take settings only for current domain.
	 *
	 * @return mixed
	 */
	protected static function get_site_domain() {
		if ( empty( self::$site_domain ) ) {
			$site_url          = get_site_url();
			self::$site_domain = parse_url( $site_url, PHP_URL_HOST );
		}

		return self::$site_domain;
	}

	/**
	 * Error messages
	 *
	 * @return array
	 */
	public function message_templates() {
		return array(
			'empty_source'           => __( '<strong>Settings storage update FAILED!</strong>. Choose an option for the data storage', 'jcf' ),
			'fs_theme_not_writable'  => __( '<strong>Settings storage update FAILED!</strong>. Check writable permissions of directory ' . get_stylesheet_directory() . '/jcf-settings/', 'jcf' ),
			'fs_global_not_writable' => __( '<strong>Settings storage update FAILED!</strong>. Check writable permissions of directory ' . get_home_path() . 'wp-content/jcf-settings/', 'jcf' ),

			'empty_ms' => __( '<strong>MultiSite settings update FAILED!</strong> Choose an option for the multisite.', 'jcf' ),

			'source_updated' => __( '<strong>Settings storage</strong> configurations has been updated.', 'jcf' ),
			'ms_updated'     => __( '<strong>MultiSite settings</strong> has been updated.', 'jcf' ),
		);
	}

	/**
	 * Validate current value of source attribute
	 *
	 * @return bool
	 */
	public function validate_data_source() {
		if ( empty( $this->source ) ) {
			$this->add_error( 'empty_source' );
		}

		if ( self::CONF_SOURCE_FS_THEME === $this->source ) {
			$path             = apply_filters( 'jcf_config_filepath', get_stylesheet_directory() . '/jcf/config.json', self::CONF_SOURCE_FS_THEME );
			$fs_theme_storage = dirname( $path );
			if ( ! wp_mkdir_p( $fs_theme_storage ) || ! is_writable( $fs_theme_storage ) ) {
				$this->add_error( 'fs_theme_not_writable' );
			}
		}

		if ( self::CONF_SOURCE_FS_GLOBAL === $this->source ) {
			$path              = apply_filters( 'jcf_config_filepath', WP_CONTENT_DIR . '/jcf/config.json', self::CONF_SOURCE_FS_GLOBAL );
			$fs_global_storage = dirname( $path );
			if ( ! wp_mkdir_p( $fs_global_storage ) || ! is_writable( $fs_global_storage ) ) {
				$this->add_error( 'fs_global_not_writable' );
			}
		}

		return ! $this->has_errors();
	}

	/**
	 * Validate current value of network mode attribute
	 *
	 * @return bool
	 */
	public function validate_network_mode() {
		if ( ! MULTISITE ) {
			return false;
		}

		if ( empty( $this->network ) ) {
			$this->network = self::CONF_MS_SITE;
		}

		return ! $this->has_errors();
	}

}
