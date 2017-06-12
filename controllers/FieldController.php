<?php

namespace jcf\controllers;

use jcf\models;
use jcf\core;
use jcf\core\JustField;

class FieldController extends core\Controller
{

	/**
	 * Init all wp-actions
	 */
	public function __construct() {
		parent::__construct();
		/* Fields actions */
		add_action( 'wp_ajax_jcf_add_field', array( $this, 'ajax_form' ) );
		add_action( 'wp_ajax_jcf_save_field', array( $this, 'ajax_save' ) );
		add_action( 'wp_ajax_jcf_delete_field', array( $this, 'ajax_delete' ) );
		add_action( 'wp_ajax_jcf_edit_field', array( $this, 'ajax_form' ) );
		add_action( 'wp_ajax_jcf_fields_order', array( $this, 'ajax_sort' ) );
		add_action( 'wp_ajax_jcf_collection_order', array( $this, 'ajax_collection_sort' ) );
	}

	/**
	 * Renders field form callback
	 */
	public function ajax_form() {
		$model = new models\Field();

		if ( $model->load( $_POST ) && $field = core\JustFieldFactory::create( $model ) ) {
			return $this->_render_ajax( 'fields/form', 'html', array( 'field' => $field ) );
		}

		return $this->_render_ajax( null, 'json', array( 'status' => !empty( $field ), 'error' => $model->get_errors() ) );
	}

	/**
	 * Save field data on form submit
	 */
	public function ajax_save() {
		$model = new models\Field();

		if ( $model->load( $_POST ) && $success = $model->save() ) {
			if ( isset( $success['id_base'] ) && 'collection' === $success['id_base'] ) {
				$jcf = \JustCustomFields::get_instance();
				$registered_fields = $jcf->get_fields( 'collection' );

				$post_type_kind = models\Field::get_post_type_kind( $model->post_type );
				if ( JustField::POSTTYPE_KIND_TAXONOMY == $post_type_kind ) {
					unset( $registered_fields['relatedcontent'] );
				}

				ob_start();
				$this->_render('fields/collection', array(
					'collection' => $success['instance'],
					'collection_id' => $success['id'],
					'fieldset_id' => $success['fieldset_id'],
					'registered_fields' => $registered_fields,
					'post_type_kind' => $post_type_kind,
				));
				$success['collection_fields'] = ob_get_clean();
			}

			return $this->_render_ajax( null, 'json', $success );
		}

		return $this->_render_ajax( null, 'json', array( 'status' => false, 'error' => $model->get_errors() ) );
	}

	/**
	 * Delete field link callback
	 */
	public function ajax_delete() {
		$model = new models\Field();
		$model->load( $_POST ) && $success = $model->delete();

		return $this->_render_ajax( null, 'json', array( 'status' => !empty( $success ), 'error' => $model->get_errors() ) );
	}

	/**
	 * Sortable Drop event callback to save changes
	 */
	public function ajax_sort() {
		$model = new models\Field();
		$model->load( $_POST ) && $success = $model->sort();

		return $this->_render_ajax( null, 'json', array( 'status' => !empty( $success ), 'error' => $model->get_errors() ) );
	}

	/**
	 * Sortable Drop event callback to save Collection fields order
	 */
	public function ajax_collection_sort() {
		$model = new models\Field();
		$model->load( $_POST ) && $success = $model->sortCollection();

		return $this->_render_ajax( null, 'json', array( 'status' => !empty( $success ), 'error' => $model->get_errors() ) );
	}

}
