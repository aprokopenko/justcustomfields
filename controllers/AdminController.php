<?php

namespace jcf\controllers;

use jcf\models;
use jcf\core;

class AdminController extends core\Controller
{

	/**
	 * Init all wp-actions
	 */
	public function __construct()
	{
		parent::__construct();
		add_action('admin_menu', array( $this, 'adminMenu' ));

		if ( isset($_GET['page']) && strpos($_GET['page'], 'jcf_') !== FALSE ) {
			add_action('admin_print_scripts', array( $this, 'addScripts' ));
			add_action('admin_print_styles', array( $this, 'addStyles' ));
			//add_action('admin_print_scripts', array( $this, 'addCollectionJs' ));
		}
	}

	/**
	 * Init menu item and index page for plugin
	 */
	public function adminMenu()
	{
		$page_title = \JustCustomFields::$pluginName;
		$page_slug = \JustCustomFields::$pluginSlug;

		add_options_page($page_title, $page_title, 'manage_options', 'jcf_admin', array( $this, 'actionIndex' ));
	}

	/**
	 * Render index page
	 */
	public function actionIndex()
	{
		$model = new models\Fieldset();
		$count_fields = $model->getFieldsCounter();
		$post_types = jcf_get_post_types('object');

		// load template
		return $this->_render('admin/index', array(
					'tab' => 'fields',
					'post_types' => $post_types,
					'count_fields' => $count_fields
		));
	}

	/**
	 * 	Include scripts
	 */
	public function addScripts()
	{
		$slug = \JustCustomFields::$pluginSlug;
		wp_register_script(
				$slug, WP_PLUGIN_URL . '/just-custom-fields/assets/just_custom_fields.js', array( 'jquery', 'json2', 'jquery-form', 'jquery-ui-sortable' )
		);
		wp_enqueue_script($slug);
		wp_enqueue_script('jquery-ui-autocomplete');

		// add text domain
		wp_localize_script($slug, 'jcf_textdomain', jcf_get_language_strings());
	}

	/**
	 * Include styles
	 */
	public function addStyles()
	{
		$slug = \JustCustomFields::$pluginName;
		wp_register_style($slug, WP_PLUGIN_URL . '/just-custom-fields/assets/styles.css');
		wp_enqueue_style($slug);
	}

	/**
	 * 	Add collection script
	 */
	// TODO: move this to collections component?
	/*
	public function addCollectionJs()
	{
		wp_register_script(
				'jcf_collections', WP_PLUGIN_URL . '/just-custom-fields/components/collection/assets/collection.js', array( 'jquery' )
		);
		wp_enqueue_script('jcf_collections');
	}
	*/
}
