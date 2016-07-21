<?php

namespace jcf\models;

use jcf\core;

class Shortcodes extends core\Model
{

	/**
	 * 	Do shortcode
	 * 	@param array $args Attributes from shortcode
	 * 	@return string Field content
	 */
	protected function _initShortcode( $args )
	{
		extract(shortcode_atts(array(
			'field' => '',
			'post_id' => '',
						), $args));

		//get post id
		$post_id = !empty($args['post_id']) ? $args['post_id'] : get_the_ID();
		//get post type
		$post_type = get_post_type($post_id);
		//get field settings
		$field_settings = $this->_dL->getFields();
		if ( empty($field_settings[$post_type]) )
			return false;

		//get field id
		foreach ( $field_settings[$post_type] as $key_field => $field ) {
			if ( strcmp($args['field'], $field['slug']) === 0 ) {
				$field_id = $key_field;
				break;
			}
		}
		// init field object and do shortcode
		if ( !empty($field_id) ) {
			$field_model = new Field();
			$field_model->post_type = $post_type;
			$field_model->field_id = $field_id;
			$field_obj = core\JustFieldFactory::create($field_model);
			if ( !$field_obj ) return false;

			$field_obj->setPostID($post_id);

			unset($args['field']);
			return $field_obj->doShortcode($args);
		}
		else {
			return false;
		}
	}

	/**
	 * 	Shortcode [jcf-value]
	 * 	@param array $args Attributes from shortcode
	 * 	@return string Field content
	 */
	public function getFieldValue( $args )
	{
		if ( !empty($args['field']) ) {
			return $this->_initShortcode($args);
		}
		else {
			return _e('Error! "field" parameter is missing', \JustCustomFields::TEXTDOMAIN);
		}
	}

}
