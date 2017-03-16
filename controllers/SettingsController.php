<?php

namespace jcf\controllers;

use jcf\models;
use jcf\core;

class SettingsController extends core\Controller
{

	/**
	 * Init all wp-actions
	 */
	public function __construct()
	{
		parent::__construct();
		add_action('admin_menu', array( $this, 'initRoutes' ));
	}

	/**
	 * Init routes for settings page
	 */
	public function initRoutes()
	{
		$page_title = __('Settings', \JustCustomFields::TEXTDOMAIN);
		add_submenu_page(null, $page_title, $page_title, 'manage_options', 'jcf_settings', array( $this, 'actionIndex' ));
	}

	/**
	 * Render settings page
	 */
	public function actionIndex()
	{
		$model = new models\Settings();
		$model->load($_POST) && $model->save();

		$source = $model::getDataSourceType();
		$network = $model::getNetworkMode();
		$googlemaps_api_key = $model::getGoogleMapsApiKey();

		// load template
		return $this->_render('settings/index', array(
					'tab' => 'settings',
					'source' => $source,
					'network' => $network,
					'googlemaps_api_key' => $googlemaps_api_key,
		));
	}

}
