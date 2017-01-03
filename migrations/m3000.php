<?php

namespace jcf\migrations;

class m3000 extends \jcf\core\Migration
{
	public $version = '3.000';
	
	/**
	 * Update fields and fieldsets attributes
	 * @return boolean
	 */
	public function up()
	{
		$storage = get_option('jcf_read_settings');

		if ( empty($storage) ) {
			$this->_updateFieldsFromDB();
			$this->_updateFieldsFromFile();
		}
		elseif ( $storage == 'database' ) {
			$this->_updateFieldsFromDB();
		}
		else {
			$this->_updateFieldsFromFile();
		}

		return true;		
	}
	
	/**
	 * Update fields and fieldsets from DataBase
	 */
	protected function _updateFieldsFromDB()
	{
		$network = get_site_option('jcf_multisite_setting');
		$post_types = jcf_get_post_types();

		foreach ( $post_types as $post_type => $object ) {
			$fields[$post_type] = $network == 'network' ? get_site_option('jcf_fields-' . $post_type) : get_option('jcf_fields-' . $post_type);
			$fieldsets[$post_type] = $network == 'network' ? get_site_option('jcf_fieldsets-' . $post_type) : get_option('jcf_fieldsets-' . $post_type);
		}

		$dl = new \jcf\models\DBDataLayer();
		$dl->setFieldsets($fieldsets);
		$dl->setFields($fields);
		$dl->saveFieldsetsData();
		$dl->saveFieldsData();
	}

	/**
	 * Update fields and fieldsets from File
	 */
	protected function _updateFieldsFromFile()
	{
		$storage = get_option('jcf_read_settings');

		if ( $storage == models\Settings::CONF_SOURCE_FS_GLOBAL ) {
			$path = WP_CONTENT_DIR . '/jcf-settings/jcf_settings.json';
		}
		else {
			$path = get_stylesheet_directory() . '/jcf-settings/jcf_settings.json';
		}

		$dl = new \jcf\models\FilesDataLayer();
		$data = $dl->getDataFromFile($path);

		if ( !empty($data) ) {
			$newdata = array();
			$newdata['fields'] = $data['field_settings'];
			unset($data['field_settings']);
			
			$newdata['fieldsets'] = $data['fieldsets'];

			foreach ( $newdata['fieldsets'] as $post_type => $fieldsets ) {
				foreach ( $fieldsets as $key => $fieldset ) {
					$newdata['fieldsets'][$post_type][$key]['position'] = 'advanced';
					$newdata['fieldsets'][$post_type][$key]['priority'] = 'default';
				}
			}
			
			foreach ( $newdata['fields'] as $post_type => $fields ) {
				foreach ( $fields as $key => $field ) {
					$newdata['fields'][$post_type][$key]['_type'] = preg_replace('/[^a-z]+/', '', $key); 
					$newdata['fields'][$post_type][$key]['_version'] =  $this->version;
				}
			}
		}

		$dl->setFieldsets($newdata['fieldsets']);
		$dl->setFields($newdata['fields']);
		$dl->saveFieldsetsData();
		$dl->saveFieldsData();
	}
}

