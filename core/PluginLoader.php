<?php
namespace jcf\core;

use jcf\core\DataLayerFactory;
use jcf\models\Migrate;

/**
 * Class PluginLoader
 * Perform version check and show migration warning if needed
 *
 * @package jcf\core
 */
class PluginLoader
{
	/**
	 * @var DataLayer
	 */
	private $_dL;

	/**
	 * PluginLoader constructor.
	 */
	public function __construct()
	{
		$this->_dL = DataLayerFactory::create();
	}

	/**
	 * Check plugin version, compare it with JCF version and print notice in case we need to upgrade
	 *
	 * @return bool
	 */
	public function checkMigrationsAvailable()
	{
		$version = $this->getStorageVersion();

		// if we can't define version at all - it seems to be a new installation. just write current version
		if ( empty($version) ) {
			$this->_dL->saveStorageVersion();
			return false;
		}

		if ( version_compare( $version, \JustCustomFields::VERSION, '<') ) {
			define('JCF_MIGRATE_MODE', true);
			// print notice if we're not on migrate page
			if (empty($_GET['page']) || $_GET['page'] != 'jcf_upgrade') {
				add_action( 'admin_notices', array('\jcf\models\Migrate', 'adminUpgradeNotice') );
			}
			return true;
		}

		return false;
	}

	/**
	 * Try to find storage version by setting in db/file
	 * If not found - it will try to guess it based on fields settings
	 *
	 * @return array|bool|int|mixed
	 */
	public function getStorageVersion()
	{
		if ( ! $version = $this->_dL->getStorageVersion() ) {
			$version = Migrate::guessVersion();
		}

		return $version;
	}

}