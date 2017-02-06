<?php

namespace jcf\models;

use jcf\core;

class Storage extends core\Model
{
	public $update_storage_version = false;

	protected $_version;
	protected $_deprecatedFields;
	
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
	public function getDeprecatedFields()
	{
		$storage = get_option('jcf_read_settings');

		if ( empty($storage) ) return false;

		if ( $storage == Settings::CONF_SOURCE_DB ) {
			return $this->_getDeprecatedFieldsFromDB();
		}

		return $this->_getDeprecatedFieldsFromFile();
	}
	
	/**
	 * Check deprecated fields from DataBase
	 */
	protected function _getDeprecatedFieldsFromDB()
	{
		$network = Settings::getNetworkMode();
		$post_types = jcf_get_post_types();

		foreach ( $post_types as $post_type => $object ) {
			$allFields[$post_type] = $network == Settings::CONF_MS_NETWORK ? get_site_option('jcf_fields-' . $post_type) : get_option('jcf_fields-' . $post_type);
			$allfieldsets[$post_type] = $network == Settings::CONF_MS_NETWORK ? get_site_option('jcf_fieldsets-' . $post_type) : get_option('jcf_fieldsets-' . $post_type);
		}

		return $this->_getDeprecatedFieldsFromArray( $allFields );
	}
	
	/**
	 * Check deprecated fields from File
	 */
	protected function _getDeprecatedFieldsFromFile()
	{
		$storage = get_option('jcf_read_settings');
		$path = get_stylesheet_directory() . '/jcf-settings/jcf_settings.json';

		if ( $storage == models\Settings::CONF_SOURCE_FS_GLOBAL ) {
			$path = WP_CONTENT_DIR . '/jcf-settings/jcf_settings.json';
		}

		$dl = new \jcf\models\FilesDataLayer();
		$data = $dl->getDataFromFile($path);

		return $this->_getDeprecatedFieldsFromArray( $data['field_settings'] );
	}
	
	/**
	 * Check deprecated fields from array
	 * @param array $allFields
	 */
	protected function _getDeprecatedFieldsFromArray( $allFields )
	{
		foreach ( $allFields as $pt => $fields ) {
			if ( !is_array($fields) ) continue;
			
			foreach ( $fields as $id => $field ) {
				if ( strpos($id, 'uploadmedia') !== false || strpos($id, 'fieldsgroup') !== false  ) {
					$this->_deprecatedFields[$pt][] = $field['title'];
				}
			}
		}
		
		return $this->_deprecatedFields;
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
			$this->addMessage('Just Custom Field settings was updated');
			return $this->_dL->updateStorageVersion();
		}

		$this->addError('Just Custom Field settings was not updated');
		return false;
	}
	
	/**
	 * Get migrations list
	 * @return array
	 */
	public function _getMigrations()
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

