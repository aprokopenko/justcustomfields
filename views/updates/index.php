<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

include(JCF_ROOT . '/views/_header.php'); ?>

<?php if ( !jcf\controllers\MigrateController::isOldVersion() ): ?>
	<h2><a href = "<?=  get_admin_url() . 'options-general.php?page=jcf_admin'?>">&larr; <?= __('Back', \JustCustomFields::TEXTDOMAIN); ?></a></h2>
<?php else: ?>

	<h2>Apply migrations </h2>
	<?php if ( !empty($deprecated_fields) ) : 
		$allFields = array(); ?>

		<?php foreach ( $deprecated_fields as $pt => $fields ) :
			$allFields[] = implode(', ', $fields) . ' in ' . $pt;
		endforeach; ?>

		<div class="error" style="margin-top: 10px;">
			<?php _e('Fields ' . implode('; ', $allFields) . ' are no longer supported.
					Upon upgrade their configuration will be deleted. The site will continue working, however the data in Edit interface will be LOST.
					If you can’t update your site to use new fields and update data is too time consuming please downgrade to your previous plugin version.
					Old version packages can be found here:', \JustCustomFields::TEXTDOMAIN);?>
		</div>

	<?php endif; ?>

	<form method="POST">
		<input type="submit" value="Update" name="update_storage_version" class="button button-primary" />
	</form>
<?php endif; ?>
<?php include(JCF_ROOT . '/views/_footer.php'); ?>

