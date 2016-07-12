<?php

namespace jcf\core;

/**
 * Class JustFieldFactory
 *
 * Creates JustField child object based on loaded Field model
 */
class JustFieldFactory
{

	/**
	 * Create Just_Field object of the required type
	 * 
	 * @param \jcf\models\Field $field
	 * @return \jcf\core\JustField
	 */
	public static function create( \jcf\models\Field $field )
	{
		// $field_mixed can be real field id or only id_base
		$field_mixed = !empty($field->field_id) ? $field->field_id : $field->field_type;
		$id_base = preg_replace('/\-([0-9]+)/', '', $field_mixed);

		$jcf = \JustCustomFields::run();
		$field_info = $jcf->getFieldInfo($id_base);

		if ( empty($field_info['class']) || !class_exists($field_info['class']) ) {
			return null;
		}

		$model = new $field_info['class']();
		$model->setPostType($field->post_type);
		$model->setFieldset($field->fieldset_id);
		$model->setCollection($field->collection_id);
		$model->setId($field_mixed);

		if ( !$model->isNew && $field->collection_id ) {
			$collection = new \jcf\components\collection\JustField_Collection();
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
	 *
	 * @param string $id_base  DEPRECATED!
	 * @return integer
	 */
	public static function createFieldIndex( $id_base )
	{
		return time();
	}

}
