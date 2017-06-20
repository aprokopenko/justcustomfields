<?php
/* @var $import_data array */

$field_settings = $import_data['fields'];
$fieldsets      = $import_data['fieldsets'];
$post_types     = $import_data['post_types'];
?>
<div class="edit-attachment-frame">
	<div class="media-frame-title">
		<h1><?php esc_html_e( 'Just Custom Fields Import', 'jcf' ); ?></h1>
	</div>

	<?php do_action( 'jcf_print_admin_notice' ); ?>

	<div class="media-frame-content jcf-import-fields" id="jcf-import-fields">
		<form action="<?php get_permalink(); ?>" method="post" id="jcf_save_import_fields">
			<div id="jcf_save_import_fields_content">
				<p><?php esc_html_e( 'You should choose Fields to import:', 'jcf' ); ?></p>
				<ul class="dotted-list jcf-bold jcf_width66p">
					<?php foreach ( $post_types as $pt_key => $post_type ) :
						if ( empty( $fieldsets[ $pt_key ] ) ) {
							continue;
						}
						?>
						<li class="jcf_export-content-type">
							<h3>
							<span class="jcf_checkbox_block">
								<input type="checkbox"
									   name="select_content_type"
									   value=""
									   class="jcf_content_type_select_all"
									   data-cpt_container="#<?php echo esc_attr( "jcf_posttype_{$pt_key}" ); ?>"
								/>
							</span>
								<?php esc_html_e( 'Content type: ', 'jcf' ); ?><?php echo esc_html( $post_type['labels']['name'] ); ?>
							</h3>

							<?php
							$post_type_fieldsets = $fieldsets[ $pt_key ];
							?>
							<div id="<?php echo esc_attr( "jcf_posttype_{$pt_key}" ); ?>">
								<?php include( JCF_ROOT . '/views/import_export/_fields.php' ); ?>
							</div>
						</li>
					<?php endforeach; ?>
				</ul>
				<input type="hidden" name="action" value="jcf_import_fields">
				<textarea name="import_source" class="hidden"
						  style="display: none"><?php echo jcf_esc_textarea( json_encode( $import_data ) ); ?></textarea>
			</div>
			<div class="jcf-modal-button">
				<input type="submit" class="button-primary" name="save_import"
					   value="<?php esc_html_e( 'Save Fields ', 'jcf' ); ?>"/>
			</div>
		</form>
	</div>
</div>
