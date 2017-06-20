<?php
/* @var $migrations \jcf\core\Migration[] */
/* @var $warnings  array */
/* @var $errors    array */
?>
<div class="wrap">
	<h1><?php esc_html_e( 'Just Custom Fields', 'jcf' ); ?></h1>
	<h2>Upgrade settings</h2>

	<div class="jcf_well notice-success">
		<p>All data upgraded. <a href="<?php echo esc_attr( admin_url( 'options-general.php' ) ); ?>?page=jcf_admin">View
				settings</a></p>
	</div>


	<?php include( JCF_ROOT . '/views/_footer.php' ); ?>
