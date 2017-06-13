<?php

namespace jcf\controllers;

use jcf\models;
use jcf\core;

/**
 * 	Import/Export controller
 */
class ImportExportController extends core\Controller {

	/**
	 * ImportExportController constructor.
	 * Init WP hooks
	 */
	public function __construct() {
		parent::__construct();
		add_action( 'admin_menu', array( $this, 'init_routes' ) );
		add_action( 'wp_ajax_jcf_export_fields_form', array( $this, 'ajax_export_form' ) );
		add_action( 'wp_ajax_jcf_export_fields', array( $this, 'ajax_export' ) );
		add_action( 'wp_ajax_jcf_import_fields_form', array( $this, 'ajax_import_form' ) );
	}

	/**
	 * Init routes for import/export page
	 */
	public function init_routes() {
		$page_title = __( 'Import/Export', \JustCustomFields::TEXTDOMAIN );
		add_submenu_page( null, $page_title, $page_title, 'manage_options', 'jcf_import_export', array(
			$this,
			'actionIndex',
		) );
	}

	/**
	 * Render import/export page
	 *
	 * Also process Import if Import form submitted
	 */
	public function actionIndex() {
		$model = new models\ImportExport();
		$model->load( $_POST ) && $model->import();

		/* load template */

		return $this->_render( 'import_export/index', array( 'tab' => 'import_export' ) );
	}

	/**
	 * Shows import form popup with import options
	 * (json file required)
	 */
	public function ajax_import_form() {
		$model = new models\ImportExport();

		if ( $model->load( $_POST ) && $import_data = $model->get_import_fields() ) {
			return $this->_render_ajax( 'import_export/import', 'html', array( 'import_data' => $import_data ) );
		}

		return $this->_render_ajax( null, 'json', array(
			'status' => ! empty( $import_data ),
			'error'  => $model->get_errors(),
		) );
	}

	/**
	 * Shows Export form with fields settings for export
	 */
	public function ajax_export_form() {
		$fieldsets_model = new models\Fieldset();
		$fieldsets       = $fieldsets_model->findAll();

		$fields_model = new models\Field();
		$fields       = $fields_model->findAll();

		/* load template */

		return $this->_render_ajax( 'import_export/export', 'html', array(
			'field_settings' => $fields,
			'fieldsets'      => $fieldsets,
			'post_types'     => jcf_get_post_types(),
		) );
	}

	/**
	 * Export fields callback
	 */
	public function ajax_export() {
		$model = new models\ImportExport();

		if ( $model->load( $_POST ) && $data = $model->export() ) {
			$filename = 'jcf_export' . date( 'Ymd-his' ) . '.json';
			header( 'Content-Disposition: attachment;filename=' . $filename );
			header( 'Content-Transfer-Encoding: binary' );

			return $this->_render_ajax( null, 'json', $data );
		}

		/* export failed - all we can is to redirect back to our page */
		wp_redirect( get_admin_url( null, 'options-general.php?page=jcf_import_export' ) );
	}

}
