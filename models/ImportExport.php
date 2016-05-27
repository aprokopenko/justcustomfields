<?php

namespace jcf\models;

use jcf\core;
use jcf\models;

class ImportExport extends core\Model
{
	public $action;
	public $import_data;
	public $file_name;

	/**
	 * Get fields for import
	 */
	public function getImportFields()
	{
		if ( $this->action == 'jcf_import_fields' ) return;

		if ( empty($_FILES['import_data']['name']) ) {
			$error = __('<strong>Import FAILED!</strong> Import file is missing.', \JustCustomFields::TEXTDOMAIN);
			$this->addError($error);
			return;
		}

		if ( !is_readable($_FILES['import_data']['tmp_name']) ) {
			$error = __('<strong>Import FAILED!</strong> Can\'t read uploaded file.', \JustCustomFields::TEXTDOMAIN);
			$this->addError($error);
			return;
		}

		$path_info = pathinfo($_FILES['import_data']['name']);

		if ( $path_info['extension'] !== 'json' ) {
			$error = __('<strong>Import FAILED!</strong> Please upload correct file format.', \JustCustomFields::TEXTDOMAIN);
			$this->addError($error);
			return;
		}

		$file_Layer = core\DataLayerFactory::create('file');
		$data['post_types'] = $file_Layer->getDataFromFile($_FILES['import_data']['tmp_name']);
		unlink($_FILES['import_data']['tmp_name']);

		if ( empty($data['post_types']) ) {
			$error = __('<strong>Import FAILED!</strong> File do not contain fields settings data..', \JustCustomFields::TEXTDOMAIN);
			$this->addError($error);
			return;
		}

		return $data;

	}

	public function import()
	{
		$data = $this->import_data;
		$old_fields = $this->_dL->getFields();
		$old_fieldsets = $this->_dL->getFieldsets();
		$fieldset_model = new models\Fieldset();

		foreach ( $data as $pt_name => $post_type ) {
			if ( !is_array($post_type) || empty($post_type['fieldsets']) ) continue;

			foreach ( $post_type['fieldsets'] as $fieldset_id => $fieldset ) {

				//Closing import because data of file is bad
				if ( empty($fieldset_id) ) {
					$this->addError(__('Error! Please check <strong>import file</strong>', \JustCustomFields::TEXTDOMAIN));
					break;
				}

				$fieldset_model->title = $fieldset['title'];
				$fieldset_id = !empty($old_fieldsets[$pt_name][$fieldset_id]) ? $fieldset_id : $fieldset_model->createSlug();
				$old_fieldsets[$pt_name][$fieldset_id]['id'] = $fieldset_id;
				$old_fieldsets[$pt_name][$fieldset_id]['title'] = $fieldset['title'];

				//Continue if fieldset doesn't have fields
				if ( empty($fieldset['fields']) ) continue;

				//Check old fields for fieldset
				if ( !empty($old_fields[$pt_name]) ) {
					foreach ( $old_fields[$pt_name] as $old_field_id => $old_field ) {
						$old_slugs[] = $old_field['slug'];
						$old_field_ids[$old_field['slug']] = $old_field_id;
					}
				}

				foreach ( $fieldset['fields'] as $field_id => $field ) {
					$id_base = preg_replace('/\-([0-9]+)/', '', $field_id);
					$slug_checking = !empty($old_slugs) ? in_array($field['slug'], $old_slugs) : false;
					$new_field_id = !$slug_checking ? $field_id : $old_field_ids[$field['slug']];
					$old_fields[$pt_name][$new_field_id] = $field;
					$old_fieldsets[$pt_name][$fieldset_id]['fields'][$new_field_id] = $field['enabled'];

					if ( $id_base !== 'collection' )  continue;

					//Continue if collection doesn't have fields
					if ( empty($field['fields']) || !is_array($field['fields']) ) continue;

					//Check old fields for collection
					if ( !empty($old_fields[$pt_name][$new_field_id]['fields']) ) {
						foreach ( $old_fields[$pt_name][$new_field_id]['fields'] as $old_collection_field_id => $old_collection_field ) {
							$old_collection_slugs[] = $old_collection_field['slug'];
							$old_collection_field_ids[$old_collection_field['slug']] = $old_collection_field_id;
						}
					}

					foreach ( $field['fields'] as $field_key => $field_values ) {
						$collection_field_slug_checking = !empty($old_collection_slugs) ? in_array($field_values['slug'], $old_collection_slugs) : false;
						$new_collection_field_id = !$collection_field_slug_checking ? $field_key : $old_collection_field_ids[$field_values['slug']];
						$old_fields[$pt_name][$new_field_id]['fields'][$new_collection_field_id] = $field_values;
					}
				}
			}
		}

		$this->_dL->setFields($old_fields);
		$this->_dL->setFieldsets($old_fieldsets);
		$import_status = $this->_dL->saveFieldsData() && $this->_dL->saveFieldsetsData();

		if ( $import_status ) {
			$this->addMessage(__('<strong>Import</strong> has been completed successfully!', \JustCustomFields::TEXTDOMAIN));
		}
		else {
			$this->addError(__('<strong>Import failed!</strong> Please check that your import file has right format.', \JustCustomFields::TEXTDOMAIN));
		}

		return $import_status;
	}

}
