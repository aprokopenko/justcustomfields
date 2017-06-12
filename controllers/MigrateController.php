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
class MigrateController extends core\Controller {
	/**
	 * MigrateController constructor.
	 * Init WP hooks
	 */
	public function __construct() {
		parent::__construct();
		add_action( 'admin_menu', array( $this, 'init_routes' ) );
	}

	/**
	 * Replace main menu "Just Custom Fields" with migration page
	 */
	public function init_routes() {
		$page_title = \JustCustomFields::$plugin_name;

		add_options_page( $page_title, $page_title, 'manage_options', 'jcf_upgrade', array( $this, 'action_index' ) );
	}

	/**
	 * Migration information/form/submit
	 *
	 * @return bool
	 */
	public function action_index() {
		$model = new models\Migrate();

		// check that we have something to migrate.
		$version = $model->get_storage_version();
		if ( ! version_compare( $version, \JustCustomFields::VERSION, '<' ) ) {
			return $this->action_upgraded();
		}

		$migrations = $model->find_migrations();

		/* check form submit and migrate */
		if ( $model->load($_POST) ) {
			if ( $model->migrate( $migrations ) ) {
				return $this->action_upgraded();
			}
		} // if no submit we test migrate to show possible warnings
		else {
			$warnings = $model->test_migrate( $migrations );
		}

		$model->is_storage_writable();
		$errors = $model->get_errors();

		return $this->_render('migrate/index', array(
			'migrations' => $migrations,
			'warnings' => $warnings,
			'errors' => $errors,
		));
	}

	/**
	 * Success page
	 *
	 * @return bool
	 */
	public function action_upgraded() {
		return $this->_render( 'migrate/upgraded' );
	}

}

