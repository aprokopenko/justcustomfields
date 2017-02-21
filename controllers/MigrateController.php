<?php

namespace jcf\controllers;

use jcf\core;
use jcf\models;

/**
 * Class MigrateController
 * Perform migrate operations
 *
 * @package jcf\controllers
 */
class MigrateController extends core\Controller
{
	/**
	 * MigrateController constructor.
	 * Init WP hooks
	 */
	public function __construct()
	{
		parent::__construct();
		add_action('admin_menu', array( $this, 'initRoutes' ));
	}

	/**
	 * Replace main menu "Just Custom Fields" with migration page
	 */
	public function initRoutes()
	{
		$page_title = \JustCustomFields::$pluginName;

		add_options_page($page_title, $page_title, 'manage_options', 'jcf_upgrade', array( $this, 'actionIndex' ));
	}

	/**
	 * Migration information/form/submit
	 *
	 * @return bool
	 */
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

	/**
	 * Success page
	 *
	 * @return bool
	 */
	public function actionUpgraded()
	{
		return $this->_render('migrate/upgraded');
	}

}

