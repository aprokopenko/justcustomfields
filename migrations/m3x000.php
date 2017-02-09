<?php

namespace jcf\migrations;

/**
 * Class m3x000
 * Migration from v2.3 to v3.0. A lot of changes were made:
 * DB:
 *      settings stored the same as file system in 2 keys: jcf-fields, jcf-fieldsets
 *
 * Deprecated fields:
 *      Upload Media, Fields group are deprecated and have been removed.
 *      Fields replaced with "Collections" with new slug.
 *      All data which is possible to move will be migrated into new meta key / slug.
 *
 * @package jcf\migrations
 */
class m3x000 extends \jcf\core\Migration
{
	/**
	 * Read data from storage
	 */
	protected function readData()
	{
		// TODO: Implement readData() method.
	}

	/**
	 * Test compatibility and deprecated fields
	 *
	 * @return bool
	 */
	protected function test()
	{
		// TODO: make real test
		return '<p>There are several <strong>deprecated field types</strong> which are no longer exists in a new version: Upload Media, Fields Group.
				They will be replaced with new field type: Collection. <br>
				If you use field shortcodes on your site - they won\'t work anymore and have to be replaced with new code.<br>
				We will try to migrate post data to new format. To prevent frontend errors we will rename new fields and import old data to them.<br>
				<b>You will need to upgrade your templates to read data from new fields/format.</b>
			</p>
			<ul class="jcf_list">
				<li><strong>Posts</strong> fields Gallery (uploadmedia), Addresses (fieldsgroup) will be converted</li>
				<li><strong>Pages</strong> fields Photos (uploadmedia), Contacts (fieldsgroup) will be converted</li>
			</ul>';
	}

	/**
	 * Update fields and fieldsets attributes
	 *
	 * @return boolean
	 */
	protected function update()
	{
		// TODO: Implement update() method.
		return false; // TODO: replace with true
	}

	//================================================================
	// TODO: move code below to new methods

	/**
	 * Update fields and fieldsets attributes
	 * @return boolean
	 */
	public function up()
	{
		$storage = get_option('jcf_read_settings');

		if ( empty($storage) ) {
			$this->_updateDB();
			$this->_updateFile();
		}
		elseif ( $storage == 'database' ) {
			$this->_updateDB();
		}
		else {
			$this->_updateFile();
		}

		return true;		
	}
	
	/**
	 * Update fields and fieldsets from DataBase
	 */
	protected function _updateDB()
	{
		$network = get_site_option('jcf_multisite_setting');
		$post_types = jcf_get_post_types();

		foreach ( $post_types as $post_type => $object ) {
			$fields[$post_type] = $network == 'network' ? get_site_option('jcf_fields-' . $post_type) : get_option('jcf_fields-' . $post_type);
			$fieldsets[$post_type] = $network == 'network' ? get_site_option('jcf_fieldsets-' . $post_type) : get_option('jcf_fieldsets-' . $post_type);
		}

		if ( empty($fieldsets) ) return false;
			
		$updates = $this->_updateFieldsets($fieldsets, $fields);
		$fields = $updates['fields'];
		$fieldsets = $updates['fieldsets'];

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

		if ( empty($data) )  return false;

		$newdata = array();

		$updates = $this->_updateFieldsets($data['fieldsets'], $data['field_settings']);
		$newdata['fields'] = $updates['fields'];
		$newdata['fieldsets'] = $updates['fieldsets'];

		$dl->setFieldsets($newdata['fieldsets']);
		$dl->setFields($newdata['fields']);
		$dl->saveFieldsetsData();
		$dl->saveFieldsData();
	}
	
	protected function _updateFieldsets($allFieldsets, $allFields)
	{
		global $wpdb;

		foreach ( $allFieldsets as $post_type => $fieldsets ) {
			if ( !is_array($fields) ) continue;
			
			foreach ( $fieldsets as $id => $fieldset ) {
				$allFieldsets[$post_type][$id]['position'] = 'advanced';
				$allFieldsets[$post_type][$id]['priority'] = 'default';
			}
		}

		foreach ( $allFields as $pt => $fields ) {

			if ( !is_array($fields) ) continue;

			foreach ( $fields as $id => $field ) {

				if ( strpos($id, 'uploadmedia') !== false ) {
					
					$allFields[$pt][str_replace('uploadmedia', 'simplemedia', $id)] = $this->_updateUploadMedia($field);

					foreach ( $allFieldsets[$pt] as $fieldset_id => $fieldset ) {

						if ( empty($fieldset['fields']) ) break;

						if ( !array_key_exists($id, $fieldset['fields']) ) continue;

						$allFieldsets[$pt][$fieldset_id]['fields'][str_replace('uploadmedia', 'simplemedia', $id)] = 1;
						unset($allFieldsets[$pt][$fieldset_id]['fields'][$id]);
					}

					unset($allFields[$pt][$id]);
					continue;
				}

				if ( strpos($id, 'fieldsgroup') !== false ) {
					
					$allFields[$pt][str_replace('fieldsgroup', 'collection', $id)] = $this->_updateFieldsgroup($field);

					foreach ( $allFieldsets[$pt] as $fieldset_id => $fieldset ) {

						if ( empty($fieldset['fields']) ) break;

						if ( !array_key_exists($id, $fieldset['fields']) ) continue;

						$allFieldsets[$pt][$fieldset_id]['fields'][str_replace('fieldsgroup', 'collection', $id)] = 1;
						unset($allFieldsets[$pt][$fieldset_id]['fields'][$id]);
					}

					unset($allFields[$pt][$id]);
					continue;
				}

				$allFields[$pt][$id]['_version'] = $this->version;
				$allFields[$pt][$id]['_type'] = preg_replace('/[^a-z]+/', '', $id);

				if ( !empty($field['fields']) && is_array($field['fields']) ) {
					foreach ( $field['fields'] as $fid => $f ) {
						$allFields[$pt][$id]['fields'][$fid]['_type'] = preg_replace('/[^a-z]+/', '', $fid);
						$allFields[$pt][$id]['fields'][$fid]['_version'] =  $this->version;
					}
				}
			}
		}

		return array('fieldsets' => $allFieldsets, 'fields' => $allFields);
	}
	
	protected function _updateUploadMedia($field)
	{
		$new_field = array(
			'title' => $field['title'],
			'type' => $field['type'],
			'description' => $field['description'],
			'slug' => $field['slug'] . '_3',
			'_version' => $this->version,
			'_type' => 'simplemedia',
			'enabled' => $field['enabled'],
		);		

		$posts_meta_data = $wpdb->get_results("SELECT post_id, meta_value FROM " . $wpdb->postmeta . " WHERE meta_key = '" . $field['slug'] . "'");

		if ( !empty($posts_meta_data) ) {
			foreach ( $posts_meta_data as $postmeta ) {
				$meta_value = unserialize($postmeta->meta_value);
				$image_id = $wpdb->get_results("SELECT ID FROM " . $wpdb->posts . " WHERE guid = '" . $meta_value['image'] . "'");

				if ( !empty($image_id) ) {
					update_post_meta($postmeta->post_id, $field['slug'] . '_3', $image_id);
				}
			}
		}
		
		return $new_field;
	}
	
	protected function _updateFieldsgroup($field)
	{
		$new_collection = array(
			'title' => $field['title'],
			'custom_row' => 1,
			'slug' => $field['slug'] . '_3',
			'enabled' => $field['enabled'],
			'_version' => $this->version,
			'_type' => 'collection',
		);

		$collection_fields = explode("\n", $field['fields']);

		if ( !empty($collection_fields) ) {
			foreach ( $collection_fields as $cfield ) {
				$new_field = explode('|', $cfield);
				$new_id_number = !empty($new_id_number) ? $new_id_number + 1 : time();
				$new_collection['fields']['inputtext-' . $new_id_number] = array(
					'title' => $new_field[1],
					'description' => '',
					'slug' => $new_field[0],
					'enabled' => 1,
					'_type' => 'inputtext',
					'_version' => $this->version,
					'field_width' => '100',
					'group_title' => 1
				);
			}
		}

		$posts_meta_data = $wpdb->get_results("SELECT post_id, meta_value FROM " . $wpdb->postmeta . " WHERE meta_key = '" . $field['slug'] . "'");

		if ( !empty($posts_meta_data) ) {
			foreach ( $posts_meta_data as $postmeta ) {
				update_post_meta($postmeta->post_id, $field['slug'] . '_3', unserialize($postmeta->meta_value));
			}
		}
		
		return $new_collection;
	}
}

