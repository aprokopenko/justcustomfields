<?php

namespace jcf\migrations;

class m3003 extends \jcf\core\Migration
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
			foreach ($fields as $key => $field) {
				$allFields[$post_type][$key]['_version'] = $this->version;
			}
		}

		$source = \jcf\models\Settings::getDataSourceType();
		$dl = new \jcf\models\DBDataLayer();

		if ( $source != \jcf\models\Settings::CONF_SOURCE_DB ) {
			$dl = new \jcf\models\FilesDataLayer();
		}

		$dl->setFields($fields);
		return $dl->saveFieldsData();
		
	}
}

