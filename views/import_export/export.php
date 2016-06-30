<div class="edit-attachment-frame">
	<div class="media-frame-title">
		<h1><?php _e('Just Custom Fields Export', \JustCustomFields::TEXTDOMAIN); ?></h1>
	</div>
	<div class="media-frame-content">
		<div class="jcf-export-fields" id="jcf-export-fields">
			<?php if ( $post_types ): ?>
				<form method="post" id="jcf_export_fields" action="<?php echo get_site_url(); ?>/wp-admin/admin-ajax.php" >
					<input type="hidden" name ="action" value="jcf_export_fields" />

					<div id="jcf_save_export_fields_content">
						<p><?php _e('You should choose Fields to export:', \JustCustomFields::TEXTDOMAIN); ?></p>

						<ul class="dotted-list jcf-bold jcf_width66p">
							<?php foreach ( $post_types as $key => $post_type ):
								if ( empty($fieldsets[$key]) ) continue;
								?>
								<li class="jcf_export-content-type">
									<h3>
										<span class="jcf_checkbox_block">
											<input type="checkbox"
												   name="select_content_type"
												   value=""
												   class="jcf_content_type_select_all"
												   data-cpt_container="#<?php echo "jcf_posttype_{$key}"; ?>"
											/>
										</span>
										<?php _e('Content type: ', \JustCustomFields::TEXTDOMAIN); ?><?php echo $key; ?>
									</h3>

									<?php
										$pt_key = $key;
										$post_type_fieldsets = $fieldsets[$key];
									?>
									<div id="<?php echo "jcf_posttype_{$pt_key}"; ?>">
										<?php include(JCF_ROOT . '/views/import_export/_fields.php'); ?>
									</div>
								</li>
							<?php endforeach; // foreach post types  ?>
						</ul>
					</div>
					<div class="jcf-modal-button">
						<input type="submit" class="button-primary" name="export_fields" value="<?php _e('Export', \JustCustomFields::TEXTDOMAIN); ?>" />
					</div>
				</form>
			<?php endif; // if (post types) ?>
		</div>
	</div>
</div>