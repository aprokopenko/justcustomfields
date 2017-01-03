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
	
	/**
	 * Check version of fields storage
	 * @return boolean
	 */
	protected function _checkStorageVersion()
	{
		$model = new models\Storage();
		$version = $model->getVersion();
		
		if ( !empty($_POST) ) {
			$model->load($_POST); 
			$model->migrate();
		}

		if ( $version !== \JustCustomFields::VERSION ) {

			if ( $model->checkDeprecatedFields() ) return false;

			ob_start();
			?>

			<form action="#" method="post" id="jcf_update_storage">
				<input type="submit" value="<?php _e('Update', \JustCustomFields::TEXTDOMAIN); ?>" 
					   class="jcf-btn-save button-primary" name="update_storage_version">			
			</form>

			<?php $update_button = ob_get_clean();
			return $model->addMessage('Seem your Just Custom Field settings are outdated and need to be updated.' . $update_button);
		}

		return true;
	}
}

