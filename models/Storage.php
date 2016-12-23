<?php

namespace jcf\models;

use jcf\core;

class Storage extends core\Model
{
	public $update_storage_version = false;
	
	public function __construct()
	{
		parent::__construct();
		$this->_version = $this->_setVersion();
	}
	
	public function getVersion()
	{
		return $this->_version;
	}
	
	protected function _setVersion()
	{
		return !empty($this->_dL->getStorageVersion()) ? $this->_dL->getStorageVersion() : $this->_getVersionFromFields();
	}
	
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

