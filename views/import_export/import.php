<?php
/* @var $import_data array */

$field_settings = $import_data['fields'];
$fieldsets = $import_data['fieldsets'];
$post_types = $import_data['post_types'];
?>
<div class="edit-attachment-frame">
	<div class="media-frame-title">
		<h1><?php _e('Just Custom Fields Import', \JustCustomFields::TEXTDOMAIN); ?></h1>
	</div>

	<?php do_action('jcf_print_admin_notice'); ?>
	
	<div class="media-frame-content jcf-import-fields" id="jcf-import-fields">
		<form action="<?php get_permalink(); ?>" method="post" id="jcf_save_import_fields">
			<div id="jcf_save_import_fields_content">
				<p><?php _e('You should choose Fields to import:', \JustCustomFields::TEXTDOMAIN); ?></p>
				<ul class="dotted-list jcf-bold jcf_width66p">
				<?php foreach( $post_types as $pt_key => $post_type ):
					if ( empty($fieldsets[$pt_key]) ) continue;
					?>
					<li class="jcf_export-content-type">
						<h3>
							<span class="jcf_checkbox_block">
								<input type="checkbox"
									   name="select_content_type"
									   value=""
									   class="jcf_content_type_select_all"
									   data-cpt_container="#<?php echo "jcf_posttype_{$pt_key}"; ?>"
								/>
							</span>
							<?php _e('Content type: ', \JustCustomFields::TEXTDOMAIN); ?><?php echo esc_html($post_type['labels']['name']); ?>
						</h3>

						<?php
							$post_type_fieldsets = $fieldsets[$pt_key];
						?>
						<div id="<?php echo "jcf_posttype_{$pt_key}"; ?>">
							<?php include(JCF_ROOT . '/views/import_export/_fields.php'); ?>
						</div>
					</li>
				<?php endforeach; ?>
				</ul>
				<input type="hidden" name="action" value="jcf_import_fields">
				<textarea name="import_source" class="hidden" style="display: none"><?php echo jcf_esc_textarea( json_encode($import_data) ); ?></textarea>
			</div>
			<div class="jcf-modal-button">
				<input type="submit" class="button-primary" name="save_import" value="<?php _e('Save Fields ', \JustCustomFields::TEXTDOMAIN); ?>" />
			</div>
		</form>
	</div>
</div>
