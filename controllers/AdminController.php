<?php

namespace jcf\controllers;

use jcf\models;
use jcf\core;

class AdminController extends core\Controller
{

	/**
	 * Init all wp-actions
	 */
	public function __construct() {
		parent::__construct();
		if ( ! defined( 'JCF_MIGRATE_MODE' ) ) {
			add_action( 'admin_menu', array( $this, 'admin_menu' ) );
		}

		if ( isset( $_GET['page'] ) && strpos( $_GET['page'], 'jcf_' ) !== false ) {
			add_action( 'admin_print_scripts', array( $this, 'add_scripts' ) );
			add_action( 'admin_print_styles', array( $this, 'add_styles' ) );
		} else {
			add_action( 'admin_init', array( $this, 'register_edit_assets' ) );
		}

		add_action( 'admin_print_scripts', array( $this, 'localize_scripts' ) );
	}

	/**
	 * Init menu item and index page for plugin
	 */
	public function admin_menu() {
		$page_title = \JustCustomFields::$plugin_name;
		$page_slug = \JustCustomFields::$plugin_slug;

		add_options_page( $page_title, $page_title, 'manage_options', 'jcf_admin', array( $this, 'action_index' ) );
	}

	/**
	 * Render index page
	 */
	public function action_index() {
		$model = new models\Fieldset();
		$count_fields = $model->get_fields_counter();
		$post_types = jcf_get_post_types( 'object' );
		$taxonomies = jcf_get_taxonomies( 'objects' );

		/*load template*/
		return $this->_render('admin/index', array(
					'tab' => 'fields',
					'post_types' => $post_types,
					'taxonomies' => $taxonomies,
					'count_fields' => $count_fields,
		));
	}

	/**
	 * Include scripts
	 */
	public function add_scripts() {
		$slug = \JustCustomFields::$plugin_slug;
		wp_register_script(
			$slug,
			jcf_plugin_url( 'assets/just_custom_fields.js' ),
			array( 'jquery', 'json2', 'jquery-form', 'jquery-ui-sortable' )
		);
		wp_enqueue_script( $slug );
		wp_enqueue_script( 'jquery-ui-autocomplete' );
	}

	/**
	 * JS localization text strings
	 */
	public function localize_scripts() {
		/* add text domain */
		$i18n_slug = 'just-custom-fields-i18n';
		wp_register_script( $i18n_slug, jcf_plugin_url( 'assets/jcf_i18n.js' ) );
		wp_localize_script( $i18n_slug, 'jcf_textdomain', jcf_get_language_strings() );
		wp_enqueue_script( $i18n_slug );
	}

	/**
	 * Include styles
	 */
	public function add_styles() {
		$slug = \JustCustomFields::$plugin_name;
		wp_register_style( $slug, jcf_plugin_url( 'assets/styles.css' ), array( 'media-views' ) );
		wp_enqueue_style( $slug );
	}

	/**
	 * Register post/term edit assets which can be used in dependency.
	 */
	public function register_edit_assets() {
		wp_register_script(
			'jcf_edit_post',
				jcf_plugin_url( 'assets/edit_post.js' ),
				array( 'jquery', 'tags-box' )
		);

		wp_register_style( 'jcf_edit_post', jcf_plugin_url( 'assets/edit_post.css' ) );
	}

}
