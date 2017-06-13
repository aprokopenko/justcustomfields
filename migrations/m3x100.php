<?php

namespace jcf\migrations;

use jcf\models\Settings;

/**
 * Class m3x100
 * Starting from v3.1 we changed keys to store settings:
 * DB:
 *      jcf-fields => jcf_fields
 *      jcf-fieldsets => jcf_fieldsets
 * Filesystem file:
 *      jcf-settings/jcf_settings.json => jcf/config.json
 *
 * @package jcf\migrations
 */
class m3x100 extends \jcf\core\Migration
{
	/**
	 * Read data from storage
	 */
	public function read_data()
	{
		if ( $this->is_data_source( Settings::CONF_SOURCE_DB ) ) {
			$fields = $this->read_db('jcf-fields');
			$fieldsets = $this->read_db('jcf-fieldsets');
			$this->data = array(
				self::FIELDS_KEY => $fields,
				self::FIELDSETS_KEY => $fieldsets,
			);
		}
		else {
			$json = $this->read_fs('jcf-settings/jcf_settings.json');
			$this->data = json_decode($json, true);
		}
	}

	/**
	 * There are no changes in components structure
	 *
	 * @return bool
	 */
	public function test()
	{
		// no compatibility issues
		return false;
	}

	/**
	 * Update fields and fieldsets attributes
	 *
	 * @return boolean
	 */
	public function update()
	{
		// this migration doesn't have any changes in structure
		return true;
	}

	/**
	 * Run clean up after update
	 */
	public function cleanup()
	{
		if ( $this->is_data_source( Settings::CONF_SOURCE_DB ) ) {
			$this->clean_db('jcf-fields');
			$this->clean_db('jcf-fieldsets');
		}
		else {
			$root = $this->get_files_root();
			@unlink( $root . '/jcf-settings/jcf_settings.json' );
			@rmdir( $root . '/jcf-settings' );
		}
	}
}

