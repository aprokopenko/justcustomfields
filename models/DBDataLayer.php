<?php

namespace jcf\models;

use jcf\core;
use jcf\models;

/**
 * Class DBDataLayer
 *
 * Define DataLayer for Database storage. Can save in single or Multisite modes
 */
class DBDataLayer extends core\DataLayer {
	const FIELDS_OPTION = 'jcf_fields';
	const FIELDSETS_OPTION = 'jcf_fieldsets';
	const STORAGEVER_OPTION = 'jcf_storage_version';

	/**
	 * Setting chosen by site administrator
	 *
	 * @var string
	 */
	protected $_network_mode;

	/**
	 * DBDataLayer constructor.
	 *
	 * Init network mode settings to be used in get/update methods
	 */
	public function __construct() {
		$this->_network_mode = models\Settings::get_network_mode();

		parent::__construct();
	}

	/**
	 * Setter/Init for $this->_fields property
	 *
	 * @param array $fields Field.
	 *
	 * @return bool
	 */
	public function set_fields( $fields = null ) {
		if ( ! is_null( $fields ) ) {
			$this->_fields = $fields;

			return;
		}

		$this->_fields = $this->_get_options( self::FIELDS_OPTION );
	}

	/**
	 * Update fields in wp-options or wp-site-options table
	 */
	public function save_fields_data() {
		return $this->_update_options( self::FIELDS_OPTION, $this->_fields );
	}

	/**
	 * Setter/Init for Fieldsets
	 *
	 * @param array $fieldsets Fieldset.
	 *
	 * @return bool
	 */
	public function set_fieldsets( $fieldsets = null ) {
		if ( ! is_null( $fieldsets ) ) {
			$this->_fieldsets = $fieldsets;

			return;
		}

		$this->_fieldsets = $this->_get_options( self::FIELDSETS_OPTION );
	}

	/**
	 * Save fieldsets
	 */
	public function save_fieldsets_data() {
		return $this->_update_options( self::FIELDSETS_OPTION, $this->_fieldsets );
	}

	/**
	 * Get storage version
	 *
	 * @return array
	 */
	public function get_storage_version() {
		return $this->_get_options( self::STORAGEVER_OPTION, '' );
	}

	/**
	 * Update storage version
	 *
	 * @param float|null $version Version.
	 *
	 * @return boolean
	 */
	public function save_storage_version( $version = null ) {
		if ( empty( $version ) ) {
			$version = \JustCustomFields::VERSION;
		}

		return $this->_update_options( self::STORAGEVER_OPTION, $version );
	}

	/**
	 * Check NetworkMode to be set to global (multisite)
	 *
	 * @return bool
	 */
	protected function is_settings_global() {
		return models\Settings::CONF_MS_NETWORK === $this->_network_mode;
	}

	/**
	 * Get options with wp-options
	 *
	 * @param string $key Key.
	 * @param mixed  $default Default.
	 *
	 * @return array
	 */
	protected function _get_options( $key, $default = array() ) {
		return $this->is_settings_global() ? get_site_option( $key, $default ) : get_option( $key, $default );
	}

	/**
	 * Update options with wp-options
	 *
	 * @param string $key Option name.
	 * @param array  $value Values with option name.
	 *
	 * @return boolean
	 */
	protected function _update_options( $key, $value ) {
		$this->is_settings_global() ? update_site_option( $key, $value ) : update_option( $key, $value );

		return true;
	}

}
