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
		/*
		$model = new models\Storage();
		$deprecatedFields = array();

		if ( $model->load($_POST) ) {
			$model->migrate();
		}

		$version = $model->getVersion();

		if ( version_compare($version, '2.3', '<=') ) {
			$deprecatedFields = $model->getDeprecatedFields();
		}
		*/
		return $this->_render('migrate/index', array(
			'migrations' => array(),
			'deprecated' => array(),
			//'deprecated_fields' => $deprecatedFields,
		));
	}

}

