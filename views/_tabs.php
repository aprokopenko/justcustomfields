<?php
/**
 * Tabs view
 */
?>

<h2 class="nav-tab-wrapper">
	<a class="nav-tab <?php echo( 'fields' === $tab ? 'nav-tab-active' : '' ); ?>"
	   href="?page=jcf_admin"><?php esc_html_e( 'Fields', 'jcf' ); ?></a>
	<a class="nav-tab <?php echo( 'settings' === $tab ? 'nav-tab-active' : '' ); ?>"
	   href="?page=jcf_settings"><?php esc_html_e( 'Settings', 'jcf' ); ?></a>
	<a class="nav-tab <?php echo( 'import_export' === $tab ? 'nav-tab-active' : '' ); ?>"
	   href="?page=jcf_import_export"><?php esc_html_e( 'Import/Export', 'jcf' ); ?></a>
</h2>

