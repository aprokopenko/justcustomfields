<?php

namespace jcf\controllers;

use jcf\models;
use jcf\core;

/**
 * 	Fieldset controller
 */
class FieldsetController extends core\Controller {

	/**
	 * Init all wp-actions
	 */
	public function __construct() {

		parent::__construct();
		add_action( 'admin_menu', array( $this, 'init_routes' ) );

		/* Fieldset actions */
		add_action( 'wp_ajax_jcf_add_fieldset', array( $this, 'ajax_create' ) );
		add_action( 'wp_ajax_jcf_delete_fieldset', array( $this, 'ajax_delete' ) );
		add_action( 'wp_ajax_jcf_change_fieldset', array( $this, 'ajax_get_form' ) );
		add_action( 'wp_ajax_jcf_update_fieldset', array( $this, 'ajax_update' ) );
		add_action( 'wp_ajax_jcf_order_fieldsets', array( $this, 'ajax_sort' ) );

		/* Visibility options */
		add_action( 'wp_ajax_jcf_get_rule_options', array( $this, 'ajax_get_visibility_options' ) );
		add_action( 'wp_ajax_jcf_get_taxonomy_terms', array( $this, 'ajax_get_taxonomy_terms' ) );
		add_action( 'wp_ajax_jcf_save_visibility_rules', array( $this, 'ajax_save_visibility' ) );
		add_action( 'wp_ajax_jcf_add_visibility_rules_form', array( $this, 'ajax_get_visibility_form' ) );
		add_action( 'wp_ajax_jcf_delete_visibility_rule', array( $this, 'ajax_delete_visibility' ) );
		add_action( 'wp_ajax_jcf_visibility_autocomplete', array( $this, 'ajax_visibility_autocomplete' ) );
	}

	/**
	 * Init routes for settings page with fieldsets and fields
	 */
	public function init_routes() {
		$page_title = __( 'Fields', \JustCustomFields::TEXTDOMAIN );
		add_submenu_page( null, $page_title, $page_title, 'manage_options', 'jcf_fieldset_index', array(
			$this,
			'actionIndex',
		) );
	}

	/**
	 * Render settings page with fieldsets and fields
	 */
	public function actionIndex() {
		$post_type_id   = $_GET['pt'];
		$post_type_kind = models\Fieldset::get_post_type_kind( $post_type_id );

		$jcf            = \JustCustomFields::get_instance();
		$fieldset_model = new models\Fieldset();
		$field_model    = new models\Field();

		$fieldsets                        = $fieldset_model->find_by_post_type( $post_type_id );
		$fields                           = $field_model->find_by_post_type( $post_type_id );
		$collections                      = $field_model->find_collections_by_post_type( $post_type_id );
		$collections['registered_fields'] = $jcf->get_fields( 'collection' );
		$registered_fields                = $jcf->get_fields();

		if ( core\JustField::POSTTYPE_KIND_TAXONOMY === $post_type_kind ) {
			$post_types = jcf_get_taxonomies( 'objects' );
			/* taxonomies are linked to Posts, so we don't need related content here. */
			unset( $registered_fields['relatedcontent'] );
			unset( $collections['registered_fields']['relatedcontent'] );
		} else {
			$post_types = jcf_get_post_types( 'object' );
		}


		/* load template */
		$template_params = array(
			'tab'               => 'fields',
			'post_type'         => $post_types[ $post_type_id ],
			'post_type_id'      => $post_type_id,
			'post_type_kind'    => $post_type_kind,
			'fieldsets'         => $fieldsets,
			'field_settings'    => $fields,
			'collections'       => $collections,
			'registered_fields' => $registered_fields,
		);

		return $this->_render( 'fieldsets/index', $template_params );
	}

	/**
	 * Save NEW fieldset to the data storage (callback)
	 */
	public function ajax_create() {
		$model = new models\Fieldset();
		$model->load( $_POST ) && $success = $model->create();

		return $this->_render_ajax( null, 'json', array(
			'status' => ! empty( $success ),
			'error'  => $model->get_errors(),
		) );
	}

	/**
	 * Delete fieldset link callback
	 */
	public function ajax_delete() {
		$model = new models\Fieldset();
		$model->load( $_POST ) && $success = $model->delete();

		return $this->_render_ajax( null, 'json', array(
			'status' => ! empty( $success ),
			'error'  => $model->get_errors(),
		) );
	}

	/**
	 * Form html on fieldset Update request
	 */
	public function ajax_get_form() {
		$model = new models\Fieldset();

		if ( $model->load( $_POST ) && $fieldset = $model->find_by_id( $model->fieldset_id ) ) {
			$taxonomies = get_object_taxonomies( $model->post_type, 'objects' );
			$templates  = jcf_get_page_templates( $model->post_type );

			$post_type_kind = $model->get_post_type_kind( $model->post_type );

			return $this->_render_ajax( 'fieldsets/form', 'html', array(
				'fieldset'       => $fieldset,
				'post_type'      => $model->post_type,
				'taxonomies'     => $taxonomies,
				'templates'      => $templates,
				'post_type_kind' => $post_type_kind,
			) );
		}

		return $this->_render_ajax( null, 'json', array(
			'status' => ! empty( $fieldset ),
			'error'  => $model->get_errors(),
		) );
	}

	/**
	 * Update fieldset on form submit
	 */
	public function ajax_update() {
		$model = new models\Fieldset();
		$model->load( $_POST ) && $success = $model->update();

		return $this->_render_ajax( null, 'json', array(
			'status' => ! empty( $success ),
			'title'  => $model->title,
			'error'  => $model->get_errors(),
		) );
	}

	/**
	 * Fieldsets order change callback
	 */
	public function ajax_sort() {
		$model = new models\Fieldset();
		$model->load( $_POST ) && $success = $model->sort();

		return $this->_render_ajax( null, 'json', array(
			'status' => ! empty( $success ),
			'error'  => $model->get_errors(),
		) );
	}

	/**
	 * Add form for new rule functions callback
	 */
	public function ajax_get_visibility_form() {
		$model = new models\FieldsetVisibility();

		if ( $model->load( $_POST ) && $form_data = $model->get_form() ) {
			if ( ! empty( $model->scenario ) ) {
				return $this->_render_ajax( 'fieldsets/visibility/form', 'html', $form_data );
			}

			return $this->_render( 'fieldsets/visibility/form', $form_data );
		}

		return $this->_render_ajax( null, 'json', array(
			'status' => ! empty( $form_data ),
			'error'  => $model->get_errors(),
		) );
	}

	/**
	 * Get base options for visibility rules functions callback
	 */
	public function ajax_get_visibility_options() {
		$model = new models\FieldsetVisibility();

		if ( $model->load( $_POST ) && $result = $model->get_based_on_options() ) {
			$template = 'taxonomies_list';
			$options  = array( 'taxonomies' => $result );

			if ( models\FieldsetVisibility::BASEDON_PAGE_TPL === $model->based_on ) {
				$template = 'templates_list';
				$options  = array( 'templates' => $result );
			}

			return $this->_render_ajax( 'fieldsets/visibility/' . $template, 'html', $options );
		}

		return $this->_render_ajax( null, 'json', array(
			'status' => ! empty( $result ),
			'error'  => $model->get_errors(),
		) );
	}

	/**
	 * Get taxonomy terms options functions callback
	 */
	public function ajax_get_taxonomy_terms() {
		$taxonomy = $_POST['taxonomy'];
		$terms    = get_terms( $taxonomy, array( 'hide_empty' => false ) );

		return $this->_render_ajax( 'fieldsets/visibility/terms_list', 'html', array(
			'terms'        => $terms,
			'taxonomy'     => $taxonomy,
			'current_term' => array(),
		) );
	}

	/**
	 * Save rules for visibility functions callback
	 */
	public function ajax_save_visibility() {
		$model = new models\FieldsetVisibility();

		if ( $model->load( $_POST ) && $rules = $model->update() ) {
			return $this->_render_ajax( 'fieldsets/visibility/rules', 'html', array(
				'visibility_rules' => $rules,
				'post_type'        => $model->post_type,
			) );
		}

		return $this->_render_ajax( null, 'json', array(
			'status' => ! empty( $rules ),
			'error'  => $model->get_errors(),
		) );
	}

	/**
	 * Delete rule for visibility functions callback
	 */
	public function ajax_delete_visibility() {
		$model = new models\FieldsetVisibility();

		if ( $model->load( $_POST ) && $rules = $model->delete() ) {
			return $this->_render_ajax( 'fieldsets/visibility/rules', 'html', array(
				'visibility_rules' => $rules,
				'post_type'        => $model->post_type,
			) );
		}

		return $this->_render_ajax( array( 'status' => ! empty( $rules ), 'error' => $model->get_errors() ), 'json' );
	}

	/**
	 * Autocomplete for input for taxonomy terms in visibility form
	 */
	public function ajax_visibility_autocomplete() {
		$taxonomy = strip_tags( trim( $_POST['taxonomy'] ) );
		$term     = strip_tags( trim( $_POST['term'] ) );
		$result   = models\FieldsetVisibility::find_taxonomy_terms( $taxonomy, $term );

		return $this->_render_ajax( null, 'json', $result );
	}

}
