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
	public function __construct()
	{
		parent::__construct();
		//Fields actions
		add_action('wp_ajax_jcf_add_field', array( $this, 'ajaxForm' ));
		add_action('wp_ajax_jcf_save_field', array( $this, 'ajaxSave' ));
		add_action('wp_ajax_jcf_delete_field', array( $this, 'ajaxDelete' ));
		add_action('wp_ajax_jcf_edit_field', array( $this, 'ajaxForm' ));
		add_action('wp_ajax_jcf_fields_order', array( $this, 'ajaxSort' ));
		add_action('wp_ajax_jcf_collection_order', array( $this, 'ajaxCollectionSort' ));
	}

	/**
	 * Renders field form callback
	 */
	public function ajaxForm()
	{
		$model = new models\Field();

		if ( $model->load($_POST) && $field = core\JustFieldFactory::create($model) ) {
			return $this->_renderAjax('fields/form', 'html', array( 'field' => $field ));
		}

		return $this->_renderAjax(null, 'json', array( 'status' => !empty($field), 'error' => $model->getErrors() ));
	}

	/**
	 * Save field data on form submit
	 */
	public function ajaxSave()
	{
		$model = new models\Field();

		if ( $model->load($_POST) && $success = $model->save() ) {
			if ( isset($success['id_base']) && $success['id_base'] == 'collection' ) {
				$jcf = \JustCustomFields::getInstance();
				$registered_fields = $jcf->getFields('collection');

				$post_type_kind = models\Field::getPostTypeKind($model->post_type);
				if ( JustField::POSTTYPE_KIND_TAXONOMY == $post_type_kind ) {
					unset($registered_fields['relatedcontent']);
				}

				ob_start();
				$this->_render('fields/collection', array(
					'collection' => $success['instance'],
					'collection_id' => $success['id'],
					'fieldset_id' => $success['fieldset_id'],
					'registered_fields' => $registered_fields,
					'post_type_kind' => $post_type_kind,
				));
				$success["collection_fields"] = ob_get_clean();
			}

			return $this->_renderAjax(null, 'json', $success);
		}

		return $this->_renderAjax(null, 'json', array( 'status' => false, 'error' => $model->getErrors() ));
	}

	/**
	 * Delete field link callback
	 */
	public function ajaxDelete()
	{
		$model = new models\Field();
		$model->load($_POST) && $success = $model->delete();

		return $this->_renderAjax(null, 'json', array( 'status' => !empty($success), 'error' => $model->getErrors() ));
	}

	/**
	 * Sortable Drop event callback to save changes
	 */
	public function ajaxSort()
	{
		$model = new models\Field();
		$model->load($_POST) && $success = $model->sort();

		return $this->_renderAjax(null, 'json', array( 'status' => !empty($success), 'error' => $model->getErrors() ));
	}

	/**
	 * Sortable Drop event callback to save Collection fields order
	 */
	public function ajaxCollectionSort()
	{
		$model = new models\Field();
		$model->load($_POST) && $success = $model->sortCollection();

		return $this->_renderAjax(null, 'json', array( 'status' => !empty($success), 'error' => $model->getErrors() ));
	}

}
