<?php

namespace jcf\controllers;

use jcf\core;
use jcf\models;

class MigrateController extends core\Controller
{
	public function __construct()
	{
		parent::__construct();

		if ( self::isOldVersion() ) {
			add_action('admin_menu', array( $this, 'initRoutes' ));
		}
	}
	
	public function initRoutes()
	{
		$page_title = __('Updates', \JustCustomFields::TEXTDOMAIN);
		add_submenu_page(null, $page_title, $page_title, 'manage_options', 'jcf_updates', array( $this, 'actionIndex' ));
	}
	
	public function actionIndex()
	{
		$model = new models\Storage();
		$deprecatedFields = array();

		if ( $model->load($_POST) ) {
			$model->migrate();
		}

		$version = $model->getVersion();

		if ( version_compare($version, '2.3', '<=') ) {
			$deprecatedFields = $model->getDeprecatedFields();
		}
		
		return $this->_render('updates/index', array(
			'tab' => 'updates',
			'deprecated_fields' => $deprecatedFields,
		));
	}
	
	public static function isOldVersion()
	{
		$model = new models\Storage();
		$version = $model->getVersion();

		if ( version_compare( $version, \JustCustomFields::VERSION, '<') ) {
			$model->addMessage(__('Seem your Just Custom Field settings are outdated and need to be updated. '
					. '<a href="?page=jcf_updates" class="jcf-btn-save button-primary">Update</a>', \JustCustomFields::TEXTDOMAIN));
			return true;
		}

		return false;
	}
}

