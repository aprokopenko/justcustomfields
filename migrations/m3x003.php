<?php

namespace jcf\migrations;

class m3x003 extends \jcf\core\Migration
{
	public $version = '3.003';
	
	/**
	 * Update fields and fieldsets attributes
	 * @return boolean
	 */
	public function up()
	{
		$model = new \jcf\models\Field();
		$allFields = $model->findAll();

		foreach ($allFields as $post_type => $fields) {
			if ( !is_array($fields) ) continue;

			foreach ($fields as $id => $field) {
				if ( !empty($field['fields']) && is_array($field['fields']) ) {
					foreach ( $field['fields'] as $fid => $f ) {
						$allFields[$post_type][$id]['fields'][$fid]['_version'] =  $this->version;
					}
				}
				
				$allFields[$post_type][$id]['_version'] = $this->version;
			}
		}

		$source = \jcf\models\Settings::getDataSourceType();
		$dl = new \jcf\models\DBDataLayer();

		if ( $source != \jcf\models\Settings::CONF_SOURCE_DB ) {
			$dl = new \jcf\models\FilesDataLayer();
		}

		$dl->setFields($allFields);
		return $dl->saveFieldsData();
	}
}

