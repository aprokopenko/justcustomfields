<?php

namespace jcf\migrations;

class m3000 extends \jcf\core\Migration
{
	public $version = '3.000';
	
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
		$dl->saveFieldsetsDataData();
		$dl->saveFieldsData();
	}

	protected function _updateFieldsFromFile()
	{
		$storage = get_option('jcf_read_settings');

		if ( $storage == models\Settings::CONF_SOURCE_FS_GLOBAL ) {
			$path = WP_CONTENT_DIR . '/jcf-settings/jcf_settings.json';
		}
		else {
			$path = get_stylesheet_directory() . '/jcf-settings/jcf_settings.json';
		}
		
		if ( file_exists($path) ) {
			$content = file_get_contents($file);
			$data = gettype($content) == 'string' ? json_decode($content, true) : $content;
		}
		
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
		
		if ( defined('JSON_PRETTY_PRINT') ) {
			$newdata = json_encode($newdata, JSON_PRETTY_PRINT);
		}
		else {
			$newdata = jcf_format_json(json_encode($newdata));
		}
		$dir = dirname($path);

		// trying to create dir
		if ( (!is_dir($dir) && !wp_mkdir_p($dir)) || !is_writable($dir) ) {
			return false;
		}

		if ( !empty($dir) ) {
			if ( $fp = fopen($path, 'w') ) {
				fwrite($fp, $newdata . "\r\n");
				fclose($fp);
				jcf_set_chmod($path);
				return true;
			}
		}
		return false;
	}
}

