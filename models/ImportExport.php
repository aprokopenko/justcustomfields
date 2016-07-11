<?php

namespace jcf\models;

use jcf\core;
use jcf\models;

class ImportExport extends core\Model
{
	public $action;
	public $selected_data;
	public $import_source;
	public $file_name;

	/**
	 * Get fields for import
	 */
	public function getImportFields()
	{
		if ( $this->action != 'jcf_import_fields_form' || !$this->validateImportFile() ) return;

		/* @var $files_dL FilesDataLayer */
		$import_file = $_FILES['import_data']['tmp_name'];
		$files_dL = core\DataLayerFactory::create('file');
		$data = $files_dL->getDataFromFile( $import_file );
		unlink($import_file);

		if ( empty($data['post_types']) ) {
			$error = __('IMPORT FAILED! File do not contain fields settings data..', \JustCustomFields::TEXTDOMAIN);
			$this->addError($error);
			return;
		}

		return $data;

	}

	/**
	 * Check that uploaded file has correct format to be imported
	 *
	 * @return bool
	 */
	public function validateImportFile()
	{
		if ( empty($_FILES['import_data']['name']) ) {
			$error = __('IMPORT FAILED! Import file is missing.', \JustCustomFields::TEXTDOMAIN);
			$this->addError($error);
			return false;
		}

		if ( !is_readable($_FILES['import_data']['tmp_name']) ) {
			$error = __('IMPORT FAILED! Can\'t read uploaded file.', \JustCustomFields::TEXTDOMAIN);
			$this->addError($error);
			return false;
		}

		$path_info = pathinfo($_FILES['import_data']['name']);

		if ( $path_info['extension'] !== 'json' ) {
			$error = __('IMPORT FAILED! Please upload correct file format.', \JustCustomFields::TEXTDOMAIN);
			$this->addError($error);
			return false;
		}

		return true;
	}

	/**
	 * Process selected import fields and save them to data layer
	 *
	 * @return bool|void
	 */
	public function import()
	{
		if ( $this->action != 'jcf_import_fields' || empty($this->selected_data) || empty($this->import_source) ) return;

		$dl_fields = $this->_dL->getFields();
		$dl_fieldsets = $this->_dL->getFieldsets();

		// we take origin import source and remove elements which are not selected
		$import_source = json_decode(stripslashes($this->import_source), true);
		$import_data = $this->_processSelectedData($import_source['fieldsets'], $import_source['fields'], $import_source['post_types']);

		// update fieldsets
		foreach ($import_data['fieldsets'] as $cpt_id => $fieldsets) {
			foreach ($fieldsets as $fieldset_id => $fieldset) {
				// if fieldset not exists - just copy it from import data
				if ( !isset($dl_fieldsets[$cpt_id][$fieldset_id]) ) {
					$dl_fieldsets[$cpt_id][$fieldset_id] = $fieldset;
					continue;
				}

				// for existed fieldset we merge fields list inside the template. All new will be added at the end
				$dl_fieldsets[$cpt_id][$fieldset_id]['fields'] = array_merge(
					$dl_fieldsets[$cpt_id][$fieldset_id]['fields'],
					$fieldset['fields']
				);
			}
		}

		// update fields
		foreach ($import_data['fields'] as $cpt_id => $fields) {
			foreach ($fields as $field_id => $field) {
				// if field not exists - just copy it from import data
				if ( !isset($dl_fields[$cpt_id][$field_id]) ) {
					$dl_fields[$cpt_id][$field_id] = $field;
					continue;
				}

				// for existed field we merge collection fields first
				if ( preg_match('/^collection/', $field_id) ) {
					$field['fields'] = array_merge(
						$dl_fields[$cpt_id][$field_id]['fields'],
						$field['fields']
					);
				}

				// not merge all settings
				$dl_fields[$cpt_id][$field_id] = array_merge(
					$dl_fields[$cpt_id][$field_id],
					$field
				);
			}
		}

		// save to data layer
		$this->_dL->setFields($dl_fields);
		$this->_dL->setFieldsets($dl_fieldsets);
		$import_status = $this->_dL->saveFieldsData() && $this->_dL->saveFieldsetsData();

		if ( $import_status ) {
			$this->addMessage(__('<strong>Import</strong> has been completed successfully!', \JustCustomFields::TEXTDOMAIN));
		}
		else {
			$this->addError(__('<strong>Import failed!</strong> Please check that your import file has right format.', \JustCustomFields::TEXTDOMAIN));
		}

		return $import_status;
	}

	/**
	 * Generate final array to be exported based on input
	 *
	 * @return array
	 */
	public function export()
	{
		if ( empty($this->selected_data) || !is_array($this->selected_data) ) {
			$this->addError(__('<strong>Export failed!</strong> Please select fields to export.', \JustCustomFields::TEXTDOMAIN));
			return array();
		}

		$fieldsets_model = new models\Fieldset();
		$fieldsets_data = $fieldsets_model->findAll();

		$fields_model = new models\Field();
		$fields_data = $fields_model->findAll();

		$post_types = jcf_get_post_types();

		$data = $this->_processSelectedData($fieldsets_data, $fields_data, $post_types);

		//pa($data,1);
		return $data;
	}

	/**
	 * Build clean settings arrays based on selected params
	 *
	 * @param array $fieldsets_data
	 * @param array $fields_data
	 * @param array $post_types
	 * @return array
	 */
	protected function _processSelectedData(array $fieldsets_data, array $fields_data, array $post_types)
	{
		$data = array(
			'fieldsets' => array(),
			'fields' => array(),
			'post_types' => array(),
		);
		foreach ($this->selected_data as $cpt_id => $fieldsets) {
			$data['post_types'][$cpt_id] = $post_types[$cpt_id];

			foreach ($fieldsets as $fieldset_id => $fieldset) {
				$fieldset = array_merge($fieldsets_data[$cpt_id][$fieldset_id], $fieldset);

				foreach ( $fieldset['fields'] as $field_id => $field_params ) {
					// if not collection - simple copy field settings
					if ( !preg_match('/^collection/', $field_id) ) {
						$field = $fields_data[$cpt_id][$field_id];
					}
					// for collection we define which one fields we should disable
					else {
						$collection = $fields_data[$cpt_id][$field_id];
						$collection_fields = array();
						if ( !empty($field_params['collection_fields']) ) {
							foreach ($field_params['collection_fields'] as $collection_field_id => $is_exported) {
								$collection_fields[$collection_field_id] = $collection['fields'][$collection_field_id];
							}
						}

						$field = array_merge($collection, array('fields' => $collection_fields));
					}

					$fieldset['fields'][$field_id] = 1;
					$data['fields'][$cpt_id][$field_id] = $field;
				}

				$data['fieldsets'][$cpt_id][$fieldset_id] = $fieldset;
			}
		}

		//pa($data,1);
		return $data;
	}
}
