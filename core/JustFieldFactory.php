<?php

namespace jcf\core;

class JustFieldFactory
{

	/**
	 * Create Just_Field object of the required type
	 * 
	 * @param \jcf\models\Field $field
	 * @return \jcf\models\Just_Field
	 */
	public static function create( \jcf\models\Field $field )
	{
		// $field_mixed can be real field id or only id_base
		$field_mixed = !empty($field->field_id) ? $field->field_id : $field->field_type;
		$id_base = preg_replace('/\-([0-9]+)/', '', $field_mixed);

		$jcf = \JustCustomFields::run();
		$field_info = $jcf->getFieldInfo($id_base);

		$model = new $field_info['class']();
		$model->setPostType($field->post_type);
		$model->setFieldset($field->fieldset_id);
		$model->setCollection($field->collection_id);
		$model->setId($field_mixed);

		if ( !$model->is_new && $field->collection_id ) {
			$collection = new \jcf\components\collection\Just_Field_Collection();
			$collection->setPostType($field->post_type);
			$collection->setFieldset($field->fieldset_id);
			$collection->setId($field->collection_id);

			$field_instance = $collection->instance['fields'][$field_mixed];
			$model->setSlug($field_instance['slug']);
			$model->instance = $field_instance;
		}

		return $model;
	}

	/**
	 * get next index for save new instance
	 * because of ability to import fields now, we can't use DB to save AI. 
	 * we will use timestamp for this
	 */
	public static function createFieldIndex( $id_base )
	{
		return time();
	}

}
