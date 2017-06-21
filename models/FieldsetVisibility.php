<?php

namespace jcf\models;

use jcf\core;
use jcf\models;

/**
 * Class FieldsetVisibility
 */
class FieldsetVisibility extends core\Model {
	const SCENARIO_CREATE = 'create';
	const SCENARIO_UPDATE = 'update';

	const BASEDON_PAGE_TPL = 'page_template';
	const BASEDON_TAXONOMY = 'taxonomy';

	const VISIBILITY_SHOW = 'show';
	const VISIBILITY_HIDE = 'hide';

	const JOIN_AND = 'and';
	const JOIN_OR = 'or';

	/**
	 * Scenario
	 *
	 * @var $scenario
	 */
	public $scenario = false;

	/**
	 * Post type
	 *
	 * @var $post_type
	 */
	public $post_type;

	/**
	 * Based On
	 *
	 * @var $based_on
	 */
	public $based_on;

	/**
	 * Fieldset ID
	 *
	 * @var $fieldset_id
	 */
	public $fieldset_id;

	/**
	 * Rule ID
	 *
	 * @var $rule_id
	 */
	public $rule_id;

	/**
	 * Rule
	 *
	 * @var $rule
	 */
	public $rule;

	/**
	 * Rule data
	 *
	 * @var $rule_data
	 */
	public $rule_data;

	/**
	 * Taxonomy
	 *
	 * @var $taxonomy
	 */
	public $taxonomy;

	/**
	 * Term
	 *
	 * @var $term
	 */
	public $term;

	/**
	 * Get visibility rules by post type
	 *
	 * @param string $post_type Post type.
	 *
	 * @return array
	 */
	public function find_by_post_type( $post_type ) {
		$fieldsets        = $this->_dl->get_fieldsets();
		$visibility_rules = array();

		if ( empty( $fieldsets[ $post_type ] ) ) {
			return;
		}

		foreach ( $fieldsets[ $post_type ] as $f_id => $fieldset ) {

			if ( empty( $fieldset['visibility_rules'] ) ) {
				continue;
			}

			$visibility_rules[ $f_id ] = array_values( $fieldset['visibility_rules'] );

			foreach ( $visibility_rules[ $f_id ] as $key => $rule ) {
				if ( self::BASEDON_TAXONOMY !== $rule['based_on'] ) {
					continue;
				}

				$taxonomy = get_taxonomy( $rule['rule_taxonomy'] );
				if ( empty( $taxonomy ) ) {
					unset( $visibility_rules[ $f_id ][ $key ] );
					continue;
				}

				$taxo_terms      = array();
				$taxo_term_ids   = array();
				$taxo_term_names = array();

				foreach ( $rule['rule_taxonomy_terms'] as $term_id ) {
					$term = get_term_by( 'id', $term_id, $rule['rule_taxonomy'] );
					if ( empty( $term ) ) {
						continue;
					}

					$taxo_terms[]      = $term;
					$taxo_term_ids[]   = $term_id;
					$taxo_term_names[] = $term->name;
				}
				$visibility_rules[ $f_id ][ $key ]['taxonomy']   = $taxonomy;
				$visibility_rules[ $f_id ][ $key ]['terms']      = $taxo_terms;
				$visibility_rules[ $f_id ][ $key ]['term_ids']   = $taxo_term_ids;
				$visibility_rules[ $f_id ][ $key ]['term_names'] = $taxo_term_names;
			}
		}

		return $visibility_rules;
	}

	/**
	 * Get form data for visibility rules form
	 *
	 * @return array
	 */
	public function get_form() {
		$output     = array();
		$taxonomies = get_object_taxonomies( $this->post_type, 'objects' );
		$templates  = jcf_get_page_templates( $this->post_type );

		$output['post_type']  = $this->post_type;
		$output['taxonomies'] = $taxonomies;
		$output['scenario']   = $this->scenario;
		$output['templates']  = $templates;

		if ( ! empty( $this->scenario ) && self::SCENARIO_UPDATE === $this->scenario ) {

			$visibility_rule = $this->_get_fieldset_visibility( $this->fieldset_id, $this->rule_id );

			if ( empty( $visibility_rule ) ) {
				$this->add_error( __( 'Visibility rule not found.', 'jcf' ) );

				return false;
			}

			if ( self::BASEDON_TAXONOMY === $visibility_rule['based_on'] ) {
				$terms           = get_terms( $visibility_rule['rule_taxonomy'], array( 'hide_empty' => false ) );
				$output['terms'] = $terms;
			}

			$output['rule_id']         = $this->rule_id;
			$output['fieldset_id']     = $this->fieldset_id;
			$output['visibility_rule'] = $visibility_rule;
		}

		return $output;
	}

	/**
	 * Get visibility rules for fieldset with $this->_request
	 *
	 * @return array
	 */
	public function get_based_on_options() {
		if ( self::BASEDON_PAGE_TPL === $this->based_on ) {
			$options = jcf_get_page_templates( $this->post_type );
		} else {
			$options = get_object_taxonomies( $this->post_type, 'objects' );
		}

		return $options;
	}

	/**
	 * Save visibility rule
	 *
	 * @return array|boolean
	 */
	public function update() {
		$visibility_rules = $this->_get_fieldset_visibility( $this->fieldset_id );

		if ( empty( $this->rule_id ) ) {
			$this->rule_id = time();
		}

		$this->rule_data['rule_id']         = $this->rule_id;
		$visibility_rules[ $this->rule_id ] = $this->rule_data;

		$saved = $this->_save_fieldset_visibility( $this->fieldset_id, $visibility_rules );
		if ( ! $saved ) {
			$this->add_error( __( 'Visibility rule not updated.', 'jcf' ) );

			return false;
		}

		return $visibility_rules;
	}

	/**
	 * Delete visibility rule
	 *
	 * @return array|boolean
	 */
	public function delete() {
		$visibility_rules = $this->_get_fieldset_visibility( $this->fieldset_id );
		if ( isset( $visibility_rules[ $this->rule_id ] ) ) {
			unset( $visibility_rules[ $this->rule_id ] );
		}

		$saved = $this->_save_fieldset_visibility( $this->fieldset_id, $visibility_rules );
		if ( ! $saved ) {
			$this->add_error( __( 'Visibility rule not deleted.', 'jcf' ) );

			return false;
		}

		return ! empty( $visibility_rules ) ? $visibility_rules : true;
	}

	/**
	 * Autocomplete for visibility rule
	 *
	 * @param string $taxonomy Taxonomy.
	 * @param string $term Term.
	 *
	 * @global \WPDB $wpdb
	 * @return array
	 */
	public static function find_taxonomy_terms( $taxonomy, $term ) {
		global $wpdb;

		$query    = "SELECT t.term_id, t.name
			FROM $wpdb->terms AS t
			LEFT JOIN $wpdb->term_taxonomy AS tt ON t.term_id = tt.term_id
			WHERE t.name LIKE '%$term%' AND tt.taxonomy = '$taxonomy'";
		$terms    = $wpdb->get_results( $query );
		$response = array();

		foreach ( $terms as $p ) {
			$response[] = array(
				'id'     => $p->term_id,
				'label'  => $p->name,
				'value'  => $p->name,
				'status' => true,
			);
		}

		return $response;
	}

	/**
	 * Get visibility rules for fieldset $fieldset_id
	 * Use current model post_type
	 *
	 * @param string       $fieldset_id Fiedlset ID.
	 * @param integer|null $rule_id Rule ID.
	 *
	 * @return mixed
	 */
	protected function _get_fieldset_visibility( $fieldset_id, $rule_id = null ) {
		$fieldsets = $this->_dl->get_fieldsets();

		// if we take only fieldset settings - return it.
		if ( is_null( $rule_id ) ) {
			if ( isset( $fieldsets[ $this->post_type ][ $fieldset_id ]['visibility_rules'] ) ) {
				return $fieldsets[ $this->post_type ][ $fieldset_id ]['visibility_rules'];
			}
		}

		// if we search for some specific rule.
		if ( ! is_null( $rule_id )
		     && isset( $fieldsets[ $this->post_type ][ $fieldset_id ]['visibility_rules'][ $rule_id ] )
		) {
			return $fieldsets[ $this->post_type ][ $fieldset_id ]['visibility_rules'][ $rule_id ];
		}

		return array();
	}

	/**
	 * Save visibility rules for fieldset $fieldset_id
	 * Use current model post_type
	 *
	 * @param string $fieldset_id Fieldset ID.
	 * @param string $rules Rules.
	 *
	 * @return boolean
	 */
	protected function _save_fieldset_visibility( $fieldset_id, $rules ) {
		$fieldsets                                                         = $this->_dl->get_fieldsets();
		$fieldsets[ $this->post_type ][ $fieldset_id ]['visibility_rules'] = $rules;

		return $this->_save( $fieldsets );
	}


	/**
	 * Save visibility settings
	 *
	 * @param array $fieldsets Fieldsets.
	 *
	 * @return boolean
	 */
	protected function _save( $fieldsets ) {
		$this->_dl->set_fieldsets( $fieldsets );
		$saved = $this->_dl->save_fieldsets_data();

		return ! empty( $saved );
	}

}
