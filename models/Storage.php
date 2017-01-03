<?php

namespace jcf\models;

use jcf\core;

class Storage extends core\Model
{
	public $update_storage_version = false;

	protected $_version;
	protected $_deprecatedFields;
	protected $_postTypes;
	
	public function __construct()
	{
		parent::__construct();
		$this->_version = $this->_setVersion();
	}
	
	/**
	 * Get storage version of plugin
	 * @return string
	 */
	public function getVersion()
	{
		return $this->_version;
	}
	
	/**
	 * Set storage version
	 * @return string
	 */
	protected function _setVersion()
	{
		return !empty($this->_dL->getStorageVersion()) ? $this->_dL->getStorageVersion() : $this->_getVersionFromFields();
	}
	
	/**
	 * Check deprecated fields from oldest versions
	 * @return boolean
	 */
	public function checkDeprecatedFields()
	{
		$storage = get_option('jcf_read_settings');
		
		if ( empty($storage) ) {
			$this->_checkDeprecatedFieldsFromDB();
			$this->_checkDeprecatedFieldsFromFile();
		} 
		elseif ( $storage == 'database' ) {
			$this->_checkDeprecatedFieldsFromDB();
		}
		else {
			$this->_checkDeprecatedFieldsFromFile();
		}
		
		if ( !empty($this->_deprecatedFields) && !empty($this->_postTypes) ) {
			$this->addError('Fields "' . implode(', ', $this->_deprecatedFields) . '" of "' . implode(',', $this->_postTypes) . '" are no longer supported. Please update your theme to use other available components and remove them to upgrade storage settings to latest version.');
			return true;
		}
		
		return false;
	}
	
	/**
	 * Check deprecated fields from DataBase
	 */
	protected function _checkDeprecatedFieldsFromDB()
	{
		$network = get_site_option('jcf_multisite_setting');
		$post_types = jcf_get_post_types();

		foreach ( $post_types as $post_type => $object ) {
			$allFields[$post_type] = $network == Settings::CONF_MS_NETWORK ? get_site_option('jcf_fields-' . $post_type) : get_option('jcf_fields-' . $post_type);
			$allfieldsets[$post_type] = $network == Settings::CONF_MS_NETWORK ? get_site_option('jcf_fieldsets-' . $post_type) : get_option('jcf_fieldsets-' . $post_type);
		}

		$this->_checkDeprecatedFieldsFromArray( $allFields );
	}
	
	/**
	 * Check deprecated fields from File
	 */
	protected function _checkDeprecatedFieldsFromFile()
	{
		$storage = get_option('jcf_read_settings');

		if ( $storage == models\Settings::CONF_SOURCE_FS_GLOBAL ) {
			$path = WP_CONTENT_DIR . '/jcf-settings/jcf_settings.json';
		}
		else {
			$path = get_stylesheet_directory() . '/jcf-settings/jcf_settings.json';
		}

		$dl = new \jcf\models\FilesDataLayer();
		$data = $dl->getDataFromFile($path);

		$this->_checkDeprecatedFieldsFromArray( $data['field_settings'] );
	}
	
	/**
	 * Check deprecated fields from array
	 * @param array $allFields
	 */
	protected function _checkDeprecatedFieldsFromArray( $allFields )
	{
		foreach ( $allFields as $pt => $fields ) {
			if ( !is_array($fields) ) continue;
			$keys = array_keys($fields);
			$keys = preg_replace('/[0-9\-]+/', '', $keys);

			if ( array_search('uploadmedia', $keys) ) {
				$this->_postTypes[$pt] = $pt;
				$this->_deprecatedFields['uploadmedia'] = 'uploadmedia';
			}

			if ( array_search('fieldsgroup', $keys) ) {
				$this->_postTypes[$pt] = $pt;
				$this->_deprecatedFields['fieldsgroup'] = 'fieldsgroup';
			}
		}
	}
	
	/**
	 * Do migrations
	 * @return boolean
	 */
	public function migrate()
	{
		if ( empty($this->update_storage_version) ) return false;
		
		$allMigrations = $this->_getMigrations();

		foreach ( $allMigrations as $migration ) {
			$migration_class = '\\jcf\\migrations\\' . $migration;
			$m = new $migration_class();

			if ( !$m->up() ) {
				$this->addError('Failed migration ' . $migration);
				break;
			}
			
			$this->_dL->updateStorageVersion($m->version);
		}
		
		if ( empty($this->getErrors()) ) {
			return $this->_dL->updateStorageVersion();
		}

		$this->addError('Just Custom Field settings was not updated');
		return false;
	}
	
	/**
	 * Get migrations list
	 * @return array
	 */
	protected function _getMigrations()
	{
		$migrations = scandir(JCF_ROOT . '/migrations');

		foreach ( $migrations as $key => $migration ) {
			if ( $migration == '.' || $migration == '..' ||
				 version_compare(preg_replace('/[^0-9]+/', '', $migration), preg_replace('/[^0-9]+/', '', $this->_version), '<=')) {
				unset($migrations[$key]);
				continue;
			}

			$migrations[$key] = str_replace('.php', '', $migration);
		}

		return $migrations;
	}
	
	/**
	 * Check version
	 * @return string
	 */
	protected function _getVersionFromFields()
	{
		$fieldsModel = new Field();
		$allFields = $fieldsModel->findAll();

		foreach ( $allFields as $post_type => $fields ) {
			foreach ( $fields as $field ) {
				if ( !empty($field['_version']) ) {
					$version = $field['_version'];
					break;
				}
			}
		}

		if ( empty($version) ) {
			$version = '2.3';
		}

		return $version;
	}
}

