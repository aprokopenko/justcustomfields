<?php

namespace jcf\models;

use jcf\core;

class Storage extends core\Model
{
	public $update_storage_version = false;
	
	public function __construct()
	{
		$this->_dL = new DBDataLayer();
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
				$this->addError('Filed migration ' . $migration);
			}
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
				 ((int)str_replace('m', '', $migration) <= (int)str_replace('.', '', $this->_version)) ) {
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

		return $version;
	}
}

