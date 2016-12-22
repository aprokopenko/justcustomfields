<?php

namespace jcf\controllers;

use jcf\core;
use jcf\models;

class MigrateController extends core\Controller
{
	public function __construct()
	{
		$this->_checkStorageVersion();
	}
	
	protected function _checkStorageVersion()
	{
		$model = new models\Storage();
		
		if ( !empty($_POST) ) {
			$model->load($_POST); 
			$model->migrate();
		}

		if ( $model->getVersion() !== \JustCustomFields::VERSION ) {
			ob_start();
			$this->_render('admin/update_storage');
			$update_button = ob_get_clean();
			return $model->addMessage('Seem your Just Custom Field settings are outdated and need to be updated.' . $update_button);
		}

		return true;
	}
}

