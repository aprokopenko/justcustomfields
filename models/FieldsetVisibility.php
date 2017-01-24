<?php

namespace jcf\models;

use jcf\core;
use jcf\models;

class FieldsetVisibility extends core\Model
{
	const SCENARIO_CREATE = 'create';
	const SCENARIO_UPDATE = 'update';

	const BASEDON_PAGE_TPL = 'page_template';
	const BASEDON_TAXONOMY = 'taxonomy';

	const VISIBILITY_SHOW = 'show';
	const VISIBILITY_HIDE = 'hide';

	const JOIN_AND = 'and';
	const JOIN_OR = 'or';

	public $scenario = false;
	public $post_type;
	public $based_on;
	public $fieldset_id;
	public $rule_id;
	public $rule;
	public $rule_data;
	public $taxonomy;
	public $term;

	/**
	 * Get visibility rules by post type
	 * @param string $post_type
	 * @return array
	 */
	public function findByPostType($post_type) 
	{
		$fieldsets = $this->_dL->getFieldsets();
		$visibility_rules = array();

		if ( empty($fieldsets[$post_type]) ) return;

		foreach ($fieldsets[$post_type] as $f_id => $fieldset) {

			if ( empty($fieldset['visibility_rules']) ) continue;

			$visibility_rules[$f_id] = array_values($fieldset['visibility_rules']);

			foreach ( $visibility_rules[$f_id] as $key => $rule ) {
				if ( $rule['based_on'] !== self::BASEDON_TAXONOMY ) continue;

				$taxonomy = get_taxonomy( $rule['rule_taxonomy'] );
				if ( empty($taxonomy) ) {
					unset($visibility_rules[$f_id][$key]);
					continue;
				}

				$taxo_terms = array();
				$taxo_term_ids = array();
				$taxo_term_names = array();

				foreach ( $rule['rule_taxonomy_terms'] as $term_id ) {
					/* @var $term \WP_Term */
					$term = get_term_by('id', $term_id, $rule['rule_taxonomy']);
					if ( empty($term) ) continue;

					$taxo_terms[] = $term;
					$taxo_term_ids[] = $term_id;
					$taxo_term_names[] = $term->name;
				}
				$visibility_rules[$f_id][$key]['taxonomy'] = $taxonomy;
				$visibility_rules[$f_id][$key]['terms'] = $taxo_terms;
				$visibility_rules[$f_id][$key]['term_ids'] = $taxo_term_ids;
				$visibility_rules[$f_id][$key]['term_names'] = $taxo_term_names;
			}
		}
		return $visibility_rules;
	}

	/**
	 * Get form data for visibility rules form
	 * @return array
	 */
	public function getForm()
	{
		$output = array();
		$taxonomies = get_object_taxonomies($this->post_type, 'objects');
		$templates = jcf_get_page_templates($this->post_type);

		$output['post_type'] = $this->post_type;
		$output['taxonomies'] = $taxonomies;
		$output['scenario'] = $this->scenario;
		$output['templates'] = $templates;

		if ( !empty($this->scenario) && $this->scenario == self::SCENARIO_UPDATE ) {

			$visibility_rule = $this->_getFieldsetVisibility($this->fieldset_id, $this->rule_id);

			if ( empty($visibility_rule) ) {
				$this->addError(__('Visibility rule not found.', \JustCustomFields::TEXTDOMAIN));
				return false;
			}

			if ( $visibility_rule['based_on'] == self::BASEDON_TAXONOMY ) {
				$terms = get_terms($visibility_rule['rule_taxonomy'], array( 'hide_empty' => false ));
				$output['terms'] = $terms;
			}

			$output['rule_id'] = $this->rule_id;
			$output['fieldset_id'] = $this->fieldset_id;
			$output['visibility_rule'] = $visibility_rule;
		}

		return $output;
	}

	/**
	 * Get visibility rules for fieldset with $this->_request
	 * @return array
	 */
	public function getBasedOnOptions()
	{
		if ( $this->based_on == self::BASEDON_PAGE_TPL ) {
			$options = jcf_get_page_templates($this->post_type);
		}
		else {
			$options = get_object_taxonomies($this->post_type, 'objects');
		}

		return $options;
	}

	/**
	 * Save visibility rule
	 * @return array|boolean
	 */
	public function update()
	{
		$visibility_rules = $this->_getFieldsetVisibility($this->fieldset_id);

		if ( empty($this->rule_id) ) $this->rule_id = time();

		$this->rule_data['rule_id'] = $this->rule_id;
		$visibility_rules[$this->rule_id] = $this->rule_data;

		$saved = $this->_saveFieldsetVisibility($this->fieldset_id, $visibility_rules);
		if ( !$saved ) {
			$this->addError(__('Visibility rule not updated.', \JustCustomFields::TEXTDOMAIN));
			return false;
		}

		return $visibility_rules;
	}

	/**
	 * Delete visibility rule
	 * @return array|boolean
	 */
	public function delete()
	{
		$visibility_rules = $this->_getFieldsetVisibility($this->fieldset_id);
		if ( isset($visibility_rules[$this->rule_id]) )
			unset($visibility_rules[$this->rule_id]);

		$saved = $this->_saveFieldsetVisibility($this->fieldset_id, $visibility_rules);
		if ( !$saved ) {
			$this->addError(__('Visibility rule not deleted.', \JustCustomFields::TEXTDOMAIN));
			return false;
		}

		return !empty($visibility_rules) ? $visibility_rules : true;
	}

	/**
	 * Autocomplete for visibility rule
	 * @param string $taxonomy
	 * @param string $term
	 * @global \WPDB $wpdb
	 * @return array
	 */
	public static function findTaxonomyTerms($taxonomy, $term)
	{
		global $wpdb;

		$query = "SELECT t.term_id, t.name
			FROM $wpdb->terms AS t
			LEFT JOIN $wpdb->term_taxonomy AS tt ON t.term_id = tt.term_id
			WHERE t.name LIKE '%$term%' AND tt.taxonomy = '$taxonomy'";
		$terms = $wpdb->get_results($query);
		$response = array();

		foreach ( $terms as $p ) {
			$response[] = array(
				'id' => $p->term_id,
				'label' => $p->name,
				'value' => $p->name,
				'status' => true
			);
		}

		return $response;
	}

	/**
	 * Get visibility rules for fieldset $fieldset_id
	 * Use current model post_type
	 *
	 * @param string $fieldset_id
	 * @param integer|null $rule_id
	 * @return mixed
	 */
	protected function _getFieldsetVisibility( $fieldset_id, $rule_id = null )
	{
		$fieldsets = $this->_dL->getFieldsets();

		// if we take only fieldset settings - return it
		if ( is_null($rule_id) ) {
			if ( isset($fieldsets[$this->post_type][$fieldset_id]['visibility_rules']) )
				return $fieldsets[$this->post_type][$fieldset_id]['visibility_rules'];
		}

		// if we search for some specific rule
		if ( !is_null($rule_id)
			&& isset($fieldsets[$this->post_type][$fieldset_id]['visibility_rules'][$rule_id])
		) {
			return $fieldsets[$this->post_type][$fieldset_id]['visibility_rules'][$rule_id];
		}

		return array();
	}

	/**
	 * Save visibility rules for fieldset $fieldset_id
	 * Use current model post_type
	 *
	 * @param string $fieldset_id
	 * @return boolean
	 */
	protected function _saveFieldsetVisibility( $fieldset_id, $rules )
	{
		$fieldsets = $this->_dL->getFieldsets();
		$fieldsets[$this->post_type][$fieldset_id]['visibility_rules'] = $rules;
		return $this->_save($fieldsets);
	}


	/**
	 * Save visibility settings
	 * @param array $fieldsets
	 * @return boolean
	 */
	protected function _save( $fieldsets )
	{
		$this->_dL->setFieldsets($fieldsets);
		$saved = $this->_dL->saveFieldsetsData();
		return !empty($saved);
	}

}
