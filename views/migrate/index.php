<?php
/* @var $migrations array */
/* @var $warnings  array */
/* @var $errors    array */
?>
<div class="wrap">
	<h1><?php _e('Just Custom Fields', \JustCustomFields::TEXTDOMAIN); ?></h1>
	<h2>Upgrade settings</h2>

	<?php do_action('jcf_print_admin_notice'); ?>

	<p>We found out that you upgraded the plugin to the newer version. Your field settings needs to be upgraded to continue using the plugin.</p>
	<p>Please make sure <strong>you have a backup of your current settings</strong> (database dump if you store them in database or json config file).</p>

	<?php if (empty($migrations)) : ?>
		<div class="jcf_well notice-success">
			<p>Just click the button below to upgrade.</p>
		</div>
	<?php endif; ?>

	<?php if (!empty($deprecated)) : ?>
		<div class="jcf_well notice-error">
			<h3>Warning! There are some problems with the upgrade.</h3>

			<!-- TODO: foreach deprecated warnings -->
			<h4><strong>v3.000 upgrade</strong></h4>
			<p>There are several <strong>deprecated field types</strong> which are no longer exists in a new version: Upload Media, Fields Group.
				They will be replaced with new field type: Collection. <br>
				If you use field shortcodes on your site - they won't work anymore and have to be replaced with new code.<br>
				We will try to migrate post data to new format. To prevent frontend errors we will rename new fields and import old data to them.<br>
				<b>You will need to upgrade your templates to read data from new fields/format.</b>
			</p>
			<ul class="jcf_list">
				<li><strong>Posts</strong> fields Gallery (uploadmedia), Addresses (fieldsgroup) will be converted</li>
				<li><strong>Pages</strong> fields Photos (uploadmedia), Contacts (fieldsgroup) will be converted</li>
			</ul>

			<h4><u>If you're unable to update your theme templates - please downgrade to the previous version.</u></h4>
			<p>You can find previous versions on <a href="https://wordpress.org/plugins/just-custom-fields/developers/" target="_blank">wordpress repository</a>.</p>
			<script>var jcf_migrate_errors = true;</script>
		</div>
	<?php endif; ?>

	<?php if (!empty($migrations)) : ?>
		<div class="jcf_well notice-warning">
			<p>We will launch several upgrade scripts:</p>
			<!-- TODO: foreach migrations -->
			<ul class="jcf_list">
				<li>v3.000 upgrade</li>
				<li>v3.100 upgrade</li>
			</ul>
		</div>
	<?php endif; ?>

	<form method="POST" onsubmit="if(jcf_migrate_errors && !confirm('Are you sure you will be able to update your code?')) return false;">
		<input type="submit" value="Upgrade" name="update_storage_version" class="button button-primary" />
	</form>

<?php include(JCF_ROOT . '/views/_footer.php'); ?>

