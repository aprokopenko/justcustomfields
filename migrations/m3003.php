<?php

namespace jcf\migrations;

class m3003 extends \jcf\core\Migration
{
	public $version = '3.003';
	
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
		$network = \jcf\models\Settings::getNetworkMode();
	}
}

