<?php

namespace jcf\models;

use jcf\core;

class FilesDataLayer extends core\DataLayer
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

		$data = $this->getDataFromFile();
		$this->_fields = $data['field_settings'];
	}

	/**
	 * 	Update fields
	 */
	public function saveFieldsData()
	{
		$data = $this->getDataFromFile();
		$data['field_settings'] = $this->_fields;
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
		$this->_fieldsets = $data['fieldsets'];
	}

	/**
	 * Save fieldsets
	 */
	public function saveFieldsetsData()
	{
		$data = $this->getDataFromFile();
		$data['fieldsets'] = $this->_fieldsets;
		return $this->_save($data);
	}

	/**
	 * 	Get fields and fieldsets from file
	 * 	@param string $uploadfile File name
	 * 	@return boolean/array Array with fields settings from file
	 */
	public function getDataFromFile( $file = false )
	{
		$source = \jcf\models\Settings::getDataSourceType();

		if ( !$file )
			$file = $this->_getConfigFilePath($source);

		if ( file_exists($file) ) {
			$content = file_get_contents($file);
			$data = json_decode($content, true);
			return gettype($data) == 'string' ? json_decode($data, true) : $data;
		}

		return false;
	}

	/**
	 * Get path to file with fields and fieldsets
	 * @param string $source_settings
	 * @return string/boolean
	 */
	protected function _getConfigFilePath( $source_settings )
	{
		if ( !empty($source_settings) && ($source_settings == \jcf\models\Settings::CONF_SOURCE_FS_THEME || $source_settings == \jcf\models\Settings::CONF_SOURCE_FS_GLOBAL) ) {
			return ($source_settings == \jcf\models\Settings::CONF_SOURCE_FS_THEME) ? get_stylesheet_directory() . '/jcf-settings/jcf_settings.json' : get_home_path() . 'wp-content/jcf-settings/jcf_settings.json';
		}
		return false;
	}

	/**
	 * Save all field and fieldsets
	 * @param array $data
	 * @param string $file
	 * @return boolean
	 */
	protected function _save( $data, $file = false )
	{
		$source = \jcf\models\Settings::getDataSourceType();

		if ( !$file ) {
			$file = $this->_getConfigFilePath($source);
		}

		$data = jcf_format_json(json_encode($data));
		$dir = dirname($file);

		// trying to create dir
		if ( (!is_dir($dir) && !wp_mkdir_p($dir)) || !is_writable($dir) ) {
			return false;
		}

		if ( !empty($dir) ) {
			$content = $data . "\r\n";
			if ( $fp = fopen($file, 'w') ) {
				fwrite($fp, $content);
				fclose($fp);
				jcf_set_chmod($file);
				return true;
			}
		}
		return false;
	}

}
