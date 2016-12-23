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

