<?php
namespace jcf\core;

use jcf\core\DataLayerFactory;
use jcf\models\Migrate;

class PluginLoader
{
	private $_dL;

	public function __construct()
	{
		$this->_dL = DataLayerFactory::create();
	}

	public function checkMigrationsAvailable()
	{
		$version = self::getStorageVersion();

		// if we can't define version at all - it seems to be a new installation. just write current version
		if ( empty($version) ) {
			$this->_dL->saveStorageVersion();
			return false;
		}

		if ( version_compare( $version, \JustCustomFields::VERSION, '<') ) {
			define('JCF_MIGRATE_MODE', true);
			// print notice if we're not on migrate page
			if (empty($_GET['page']) || $_GET['page'] != 'jcf_upgrade') {
				add_action( 'admin_notices', array('\jcf\Models\Migrate', 'adminUpgradeNotice') );
			}
			return true;
		}

		return false;
	}

	public function getStorageVersion()
	{
		if ( ! $version = $this->_dL->getStorageVersion() ) {
			$version = Migrate::guessVersion();
		}

		return $version;
	}

}