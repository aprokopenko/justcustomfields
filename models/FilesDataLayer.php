<?php

namespace jcf\models;

use jcf\core;
use jcf\models;

class FilesDataLayer extends core\DataLayer
{
	protected $_sourceSettings;
	static private $_cache;

	/**
	 * FilesDataLayer constructor.
	 *
	 * Init directory source setting to be used in get/update methods
	 */
	public function __construct()
	{
		$this->_sourceSettings = models\Settings::getDataSourceType();

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

		$data = $this->getDataFromFile();
		if ( isset($data['fields']) ) {
			$this->_fields = $data['fields'];
		}
	}

	/**
	 * Update fields
	 * @return boolean
	 */
	public function saveFieldsData()
	{
		$data = $this->getDataFromFile();
		$data['fields'] = $this->_fields;
		return $this->_save($data);
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

		$data = $this->getDataFromFile();
		if ( isset($data['fieldsets']) ) {
			$this->_fieldsets = $data['fieldsets'];
		}
	}

	/**
	 * Save fieldsets
	 * @return boolean
	 */
	public function saveFieldsetsData()
	{
		$data = $this->getDataFromFile();
		$data['fieldsets'] = $this->_fieldsets;
		return $this->_save($data);
	}

	/**
	 * 	Get fields and fieldsets from file
	 * 	@param string $file File name
	 * 	@return boolean/array Array with fields settings from file
	 */
	public function getDataFromFile( $file = null )
	{
		if ( !$file )
			$file = $this->_getConfigFilePath();

		if ( file_exists($file) ) {
			$content = file_get_contents($file);

			// cache json_decode. on lot of fields it makes a huge load converting this every time
			$cache_hash = md5($content);
			if ( !isset(static::$_cache[$cache_hash]) ) {
				static::$_cache[$cache_hash] = json_decode($content, true);
			}

			$data = static::$_cache[$cache_hash];
			return gettype($data) == 'string' ? json_decode($data, true) : $data;
		}

		return false;
	}

	/**
	 * Get path to file with fields and fieldsets
	 * @param string $source_settings
	 * @return string/boolean
	 */
	protected function _getConfigFilePath( $source_settings = null )
	{
		if ( is_null($source_settings) ) {
			$source_settings = $this->_sourceSettings;
		}

		switch ($source_settings) {

			case models\Settings::CONF_SOURCE_FS_THEME:
				$path = get_stylesheet_directory() . '/jcf-settings/jcf_settings.json';
				break;

			case models\Settings::CONF_SOURCE_FS_GLOBAL:
				$path = WP_CONTENT_DIR . '/jcf-settings/jcf_settings.json';
				break;

			default:
				return false;
		}

		return $path;
	}

	/**
	 * Save all field and fieldsets
	 * @param array $data
	 * @param string $file
	 * @return boolean
	 */
	protected function _save( $data, $file = null )
	{
		if ( !$file ) {
			$file = $this->_getConfigFilePath();
		}

		if ( defined('JSON_PRETTY_PRINT') ) {
			$data = json_encode($data, JSON_PRETTY_PRINT);
		}
		else {
			$data = jcf_format_json(json_encode($data));
		}
		$dir = dirname($file);

		// trying to create dir
		if ( (!is_dir($dir) && !wp_mkdir_p($dir)) || !is_writable($dir) ) {
			return false;
		}

		if ( !empty($dir) ) {
			if ( $fp = fopen($file, 'w') ) {
				fwrite($fp, $data . "\r\n");
				fclose($fp);
				jcf_set_chmod($file);
				return true;
			}
		}
		return false;
	}

}
