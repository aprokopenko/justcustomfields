<?php

namespace jcf\models;

use jcf\core;
use jcf\models;

/**
 * Class FilesDataLayer
 */
class FilesDataLayer extends core\DataLayer {

	/**
	 * Source Settings
	 *
	 * @var $_source_settings
	 */
	protected $_source_settings;

	/**
	 * Cache
	 *
	 * @var $_cache
	 */
	static private $_cache;

	const FIELDS_KEY = 'fields';
	const FIELDSETS_KEY = 'fieldsets';
	const STORAGEVER_KEY = 'version';

	/**
	 * FilesDataLayer constructor.
	 *
	 * Init directory source setting to be used in get/update methods
	 */
	public function __construct() {
		$this->_source_settings = models\Settings::get_data_source_type();

		parent::__construct();
	}

	/**
	 * Set $this->_fields property
	 *
	 * @param array $fields Fields.
	 *
	 * @return bool
	 */
	public function set_fields( $fields = null ) {
		if ( ! is_null( $fields ) ) {
			$this->_fields = $fields;

			return;
		}

		$data = $this->get_data_from_file();
		if ( isset( $data[ self::FIELDS_KEY ] ) ) {
			$this->_fields = $data[ self::FIELDS_KEY ];
		}
	}

	/**
	 * Update fields
	 *
	 * @return boolean
	 */
	public function save_fields_data() {
		$data                     = $this->get_data_from_file();
		$data[ self::FIELDS_KEY ] = $this->_fields;

		return $this->_save( $data );
	}

	/**
	 * Get storage version
	 *
	 * @return array
	 */
	public function get_storage_version() {
		$data = $this->get_data_from_file();

		return ! empty( $data[ self::STORAGEVER_KEY ] ) ? $data[ self::STORAGEVER_KEY ] : false;
	}

	/**
	 * Update storage version
	 *
	 * @param float|null $version Version.
	 *
	 * @return boolean
	 */
	public function save_storage_version( $version = null ) {
		$data = $this->get_data_from_file();

		if ( empty( $version ) ) {
			$version = \JustCustomFields::VERSION;
		}

		$data[ self::STORAGEVER_KEY ] = $version;

		return $this->_save( $data );
	}


	/**
	 * Get Fieldsets
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

		$data = $this->get_data_from_file();
		if ( isset( $data[ self::FIELDSETS_KEY ] ) ) {
			$this->_fieldsets = $data[ self::FIELDSETS_KEY ];
		}
	}

	/**
	 * Save fieldsets
	 *
	 * @return boolean
	 */
	public function save_fieldsets_data() {
		$data                        = $this->get_data_from_file();
		$data[ self::FIELDSETS_KEY ] = $this->_fieldsets;

		return $this->_save( $data );
	}

	/**
	 * Get fields and fieldsets from file
	 *
	 * @param string $file File name.
	 *
	 * @return boolean/array Array with fields settings from file
	 */
	public function get_data_from_file( $file = null ) {
		if ( ! $file ) {
			$file = $this->get_config_file_path();
		}

		if ( file_exists( $file ) ) {
			$content = file_get_contents( $file );

			// cache json_decode. on lot of fields it makes a huge load converting this every time.
			$cache_hash = md5( $content );
			if ( ! isset( static::$_cache[ $cache_hash ] ) ) {
				static::$_cache[ $cache_hash ] = json_decode( $content, true );
			}

			$data = static::$_cache[ $cache_hash ];

			return gettype( $data ) == 'string' ? json_decode( $data, true ) : $data;
		}

		return false;
	}

	/**
	 * Get path to file with fields and fieldsets
	 *
	 * @param string $source_settings Source settings.
	 *
	 * @return string/boolean
	 */
	public function get_config_file_path( $source_settings = null ) {
		if ( is_null( $source_settings ) ) {
			$source_settings = $this->_source_settings;
		}

		switch ( $source_settings ) {

			case models\Settings::CONF_SOURCE_FS_THEME:
				$path = get_stylesheet_directory() . '/jcf/config.json';
				break;

			case models\Settings::CONF_SOURCE_FS_GLOBAL:
				$path = WP_CONTENT_DIR . '/jcf/config.json';
				break;

			default:
				return false;
		}

		$path = apply_filters( 'jcf_config_filepath', $path, $source_settings );

		return $path;
	}

	/**
	 * Save all field and fieldsets
	 *
	 * @param array  $data Data.
	 * @param string $file File.
	 *
	 * @return boolean
	 */
	protected function _save( $data, $file = null ) {
		if ( ! $file ) {
			$file = $this->get_config_file_path();
		}

		if ( defined( 'JSON_PRETTY_PRINT' ) ) {
			$data = json_encode( $data, JSON_PRETTY_PRINT );
		} else {
			$data = jcf_format_json( json_encode( $data ) );
		}
		$dir = dirname( $file );

		// trying to create dir.
		if ( ( ! is_dir( $dir ) && ! wp_mkdir_p( $dir ) ) || ! is_writable( $dir ) ) {
			return false;
		}

		if ( ! empty( $dir ) ) {
			if ( $fp = fopen( $file, 'w' ) ) {
				fwrite( $fp, $data . "\r\n" );
				fclose( $fp );
				jcf_set_chmod( $file );

				return true;
			}
		}

		return false;
	}

}
