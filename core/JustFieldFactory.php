<?php

namespace jcf\core;

/**
 * Class JustFieldFactory
 *
 * Creates JustField child object based on loaded Field model
 */
class JustFieldFactory {

	/**
	 * Create Just_Field object of the required type
	 *
	 * @param \jcf\models\Field $field Field.
	 *
	 * @return \jcf\core\JustField
	 */
	public static function create( \jcf\models\Field $field ) {
		// $field_mixed can be real field id or only id_base
		$field_mixed = ! empty( $field->field_id ) ? $field->field_id : $field->field_type;
		$id_base     = preg_replace( '/\-([0-9]+)/', '', $field_mixed );

		$jcf        = \JustCustomFields::run();
		$field_info = $jcf->get_field_info( $id_base );

		if ( empty( $field_info['class'] ) || ! class_exists( $field_info['class'] ) ) {
			return null;
		}

		$model = new $field_info['class']();
		$model->set_post_type( $field->post_type );
		$model->set_fieldset( $field->fieldset_id );
		$model->set_collection( $field->collection_id );
		$model->set_id( $field_mixed );

		if ( ! $model->is_new && $field->collection_id ) {
			$collection = new \jcf\components\collection\JustField_Collection();
			$collection->set_post_type( $field->post_type );
			$collection->set_fieldset( $field->fieldset_id );
			$collection->set_id( $field->collection_id );

			$field_instance = $collection->instance['fields'][ $field_mixed ];
			$model->set_slug( $field_instance['slug'] );
			$model->instance = $field_instance;
		}

		return $model;
	}

	/**
	 * Get next index for save new instance
	 * because of ability to import fields now, we can't use DB to save AI.
	 * we will use timestamp for this
	 *
	 * @param string $id_base DEPRECATED!.
	 *
	 * @return integer
	 */
	public static function create_field_index( $id_base ) {
		return time();
	}

}
