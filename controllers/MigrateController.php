<?php

namespace jcf\controllers;

use jcf\core;
use jcf\models;

class MigrateController extends core\Controller
{
	public function __construct()
	{
		parent::__construct();
		add_action('admin_menu', array( $this, 'initRoutes' ));
	}
	
	public function initRoutes()
	{
		$page_title = \JustCustomFields::$pluginName;

		add_options_page($page_title, $page_title, 'manage_options', 'jcf_upgrade', array( $this, 'actionIndex' ));
	}
	
	public function actionIndex()
	{
		$model = new models\Migrate();

		// check that we have something to migrate.
		$version = $model->getStorageVersion();
		if ( ! version_compare( $version, \JustCustomFields::VERSION, '<') ) {
			return $this->actionUpgraded();
		}

		$migrations = $model->findMigrations();

		// check form submit and migrate
		if ( $model->load($_POST) ) {
			if ( $model->migrate($migrations) ) {
				return $this->actionUpgraded();
			}
		}
		// if no submit we test migrate to show possible warnings
		else {
			$warnings = $model->testMigrate($migrations);
		}

		$model->isStorageWritable();
		$errors = $model->getErrors();

		return $this->_render('migrate/index', array(
			'migrations' => $migrations,
			'warnings' => $warnings,
			'errors' =>   $errors,
		));
	}

	public function actionUpgraded()
	{
		return $this->_render('migrate/upgraded');
	}

}

