<?php

namespace jcf\controllers;

use jcf\models;
use jcf\core;

class FieldsetController extends core\Controller
{

	/**
	 * Init all wp-actions
	 */
	public function __construct()
	{
		parent::__construct();
		add_action('admin_menu', array( $this, 'initRoutes' ));

		//Fieldset actions
		add_action('wp_ajax_jcf_add_fieldset', array( $this, 'ajaxCreate' ));
		add_action('wp_ajax_jcf_delete_fieldset', array( $this, 'ajaxDelete' ));
		add_action('wp_ajax_jcf_change_fieldset', array( $this, 'ajaxGetForm' ));
		add_action('wp_ajax_jcf_update_fieldset', array( $this, 'ajaxUpdate' ));
		add_action('wp_ajax_jcf_order_fieldsets', array( $this, 'ajaxSort' ));

		//Visibility options
		add_action('wp_ajax_jcf_get_rule_options', array( $this, 'ajaxGetVisibilityOptions' ));
		add_action('wp_ajax_jcf_get_taxonomy_terms', array( $this, 'ajaGetTaxonomyTerms' ));
		add_action('wp_ajax_jcf_save_visibility_rules', array( $this, 'ajaxSaveVisibility' ));
		add_action('wp_ajax_jcf_add_visibility_rules_form', array( $this, 'ajaxGetVisibilityForm' ));
		add_action('wp_ajax_jcf_delete_visibility_rule', array( $this, 'ajaxDeleteVisibility' ));
		add_action('wp_ajax_jcf_visibility_autocomplete', array( $this, 'ajaxVisibilityAutocomplete' ));
	}

	/**
	 * Init routes for settings page with fieldsets and fields
	 */
	public function initRoutes()
	{
		$page_title = __('Fields', \JustCustomFields::TEXTDOMAIN);
		add_submenu_page(null, $page_title, $page_title, 'manage_options', 'jcf_fieldset_index', array( $this, 'actionIndex' ));
	}

	/**
	 * Render settings page with fieldsets and fields
	 */
	public function actionIndex()
	{
		$post_type_id = $_GET['pt'];
		$post_type_kind = models\Fieldset::getPostTypeKind($post_type_id);

		$jcf = \JustCustomFields::getInstance();
		$fieldset_model = new models\Fieldset();
		$field_model = new models\Field();

		$fieldsets = $fieldset_model->findByPostType($post_type_id);
		$fields = $field_model->findByPostType($post_type_id);
		$collections = $field_model->findCollectionsByPostType($post_type_id);
		$collections['registered_fields'] = $jcf->getFields('collection');
		$registered_fields = $jcf->getFields();

		if ( core\JustField::POSTTYPE_KIND_TAXONOMY == $post_type_kind ) {
			$post_types = jcf_get_taxonomies('objects');
			// taxonomies are linked to Posts, so we don't need related content here.
			unset( $registered_fields['relatedcontent'] );
			unset( $collections['registered_fields']['relatedcontent'] );
		} else {
			$post_types = jcf_get_post_types('object');
		}


		// load template
		$template_params = array(
			'tab' => 'fields',
			'post_type' => $post_types[$post_type_id],
			'post_type_id' => $post_type_id,
			'post_type_kind' => $post_type_kind,
			'fieldsets' => $fieldsets,
			'field_settings' => $fields,
			'collections' => $collections,
			'registered_fields' => $registered_fields,
		);
		return $this->_render('fieldsets/index', $template_params);
	}

	/**
	 * Save NEW fieldset to the data storage (callback)
	 */
	public function ajaxCreate()
	{
		$model = new models\Fieldset();
		$model->load($_POST) && $success = $model->create();

		return $this->_renderAjax(null, 'json', array( 'status' => !empty($success), 'error' => $model->getErrors() ));
	}

	/**
	 * Delete fieldset link callback
	 */
	public function ajaxDelete()
	{
		$model = new models\Fieldset();
		$model->load($_POST) && $success = $model->delete();

		return $this->_renderAjax(null, 'json', array( 'status' => !empty($success), 'error' => $model->getErrors() ));
	}

	/**
	 * Form html on fieldset Update request
	 */
	public function ajaxGetForm()
	{
		$model = new models\Fieldset();

		if ( $model->load($_POST) && $fieldset = $model->findById($model->fieldset_id) ) {
			$taxonomies = get_object_taxonomies($model->post_type, 'objects');
			$templates = jcf_get_page_templates($model->post_type);

			$post_type_kind = $model->getPostTypeKind($model->post_type);

			return $this->_renderAjax('fieldsets/form', 'html', array(
				'fieldset' => $fieldset,
				'post_type' => $model->post_type,
				'taxonomies' => $taxonomies,
				'templates' => $templates,
				'post_type_kind' => $post_type_kind,
			));
		}

		return $this->_renderAjax(null, 'json', array( 'status' => !empty($fieldset), 'error' => $model->getErrors() ));
	}

	/**
	 * Update fieldset on form submit
	 */
	public function ajaxUpdate()
	{
		$model = new models\Fieldset();
		$model->load($_POST) && $success = $model->update();

		return $this->_renderAjax(null, 'json', array(
			'status' => !empty($success),
			'title' => $model->title,
			'error' => $model->getErrors()
		));
	}

	/**
	 * Fieldsets order change callback
	 */
	public function ajaxSort()
	{
		$model = new models\Fieldset();
		$model->load($_POST) && $success = $model->sort();

		return $this->_renderAjax(null, 'json', array( 'status' => !empty($success), 'error' => $model->getErrors() ));
	}

	/**
	 * add form for new rule functions callback
	 */
	public function ajaxGetVisibilityForm()
	{
		$model = new models\FieldsetVisibility();

		if ( $model->load($_POST) && $form_data = $model->getForm() ) {
			if ( !empty($model->scenario) ) {
				return $this->_renderAjax('fieldsets/visibility/form', 'html', $form_data);
			}

			return $this->_render('fieldsets/visibility/form', $form_data);
		}

		return $this->_renderAjax(null, 'json', array( 'status' => !empty($form_data), 'error' => $model->getErrors() ));
	}

	/**
	 * get base options for visibility rules functions callback
	 */
	public function ajaxGetVisibilityOptions()
	{
		$model = new models\FieldsetVisibility();

		if ( $model->load($_POST) && $result = $model->getBasedOnOptions() ) {
			$template = 'taxonomies_list';
			$options = array( 'taxonomies' => $result );

			if ( $model->based_on == models\FieldsetVisibility::BASEDON_PAGE_TPL ) {
				$template = 'templates_list';
				$options = array( 'templates' => $result );
			}

			return $this->_renderAjax('fieldsets/visibility/' . $template, 'html', $options);
		}

		return $this->_renderAjax(null, 'json', array( 'status' => !empty($result), 'error' => $model->getErrors() ));
	}

	/**
	 * Get taxonomy terms options functions callback
	 */
	public function ajaGetTaxonomyTerms()
	{
		$taxonomy = $_POST['taxonomy'];
		$terms = get_terms($taxonomy, array( 'hide_empty' => false ));

		return $this->_renderAjax('fieldsets/visibility/terms_list', 'html', array(
			'terms' => $terms,
			'taxonomy' => $taxonomy,
			'current_term' => array()
		));
	}

	/**
	 * Save rules for visibility functions callback
	 */
	public function ajaxSaveVisibility()
	{
		$model = new models\FieldsetVisibility();

		if ( $model->load($_POST) && $rules = $model->update() ) {
			return $this->_renderAjax('fieldsets/visibility/rules', 'html', array(
				'visibility_rules' => $rules,
				'post_type' => $model->post_type
			));
		}

		return $this->_renderAjax(null, 'json', array( 'status' => !empty($rules), 'error' => $model->getErrors() ));
	}

	/**
	 * Delete rule for visibility functions callback
	 */
	public function ajaxDeleteVisibility()
	{
		$model = new models\FieldsetVisibility();

		if ( $model->load($_POST) && $rules = $model->delete() ) {
			return $this->_renderAjax('fieldsets/visibility/rules', 'html', array(
				'visibility_rules' => $rules,
				'post_type' => $model->post_type
			));
		}

		return $this->_renderAjax(array( 'status' => !empty($rules), 'error' => $model->getErrors() ), 'json');
	}

	/**
	 * Autocomplete for input for taxonomy terms in visibility form
	 */
	public function ajaxVisibilityAutocomplete()
	{
		$taxonomy = strip_tags(trim($_POST['taxonomy']));
		$term = strip_tags(trim($_POST['term']));
		$result = models\FieldsetVisibility::findTaxonomyTerms($taxonomy, $term);

		return $this->_renderAjax(null, 'json', $result);
	}

}
