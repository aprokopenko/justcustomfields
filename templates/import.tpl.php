<div class="wrap">
	<div class="icon32 icon32-posts-page" id="icon-edit"><br></div>
	<h2><?php _e('Just Custom Fields Import', JCF_TEXTDOMAIN); ?></h2>
	<p><?php _e('You should choose Fields to import:', JCF_TEXTDOMAIN); ?></p>
	<div class="jcf-import-fields" id="jcf-import-fields">
		<?php if( $post_types ): ?>
			<form action="<?php get_permalink(); ?>" method="post" id="jcf_save_import_fields">
				<div id="jcf_save_import_fields_content">
					<ul class="dotted-list jcf-bold jcf_width66p">
					<?php foreach( $post_types as $key => $post_type ): ?>
						<li class="jcf_export-content-type"><h3><input type="checkbox" name="select_content_type" value="" class="jcf-select_content_type"  /><?php echo 'Content type: ' . $key; ?></h3>
							<input type="hidden" name="import_data[<?php echo $key; ?>]" value="<?php echo $key; ?>" />
							<?php if(!empty($post_type['fieldsets'])) :?>
								<?php foreach( $post_type['fieldsets'] as $fieldset_id =>$fieldset ) : ?>
									<input type="hidden" name="import_data[<?php echo $key; ?>][fieldsets][<?php echo $fieldset_id; ?>][title]" value="<?php echo $fieldset['title']; ?>" />
									<div class="jcf_inner_box" id="jcf_fieldset_<?php echo $fieldset_id; ?>">
										<h3 class="header"><input type="checkbox" name="choose_fieldset" value="import_data_<?php echo $key; ?>_fieldsets_<?php echo $fieldset_id; ?>" class="jcf-choose_fieldset" /><?php _e('Fieldset:', JCF_TEXTDOMAIN); ?> <span><?php echo $fieldset['title'];  ?></span></h3>
										<div class="jcf_inner_content">
											<table class="wp-list-table widefat fixed" cellspacing="0">
												<thead><tr>
													<th class="check-column">&nbsp;</th>
													<th><?php _e('Field', JCF_TEXTDOMAIN); ?></th>
													<th><?php _e('Type', JCF_TEXTDOMAIN); ?></th>
													<th><?php _e('Slug', JCF_TEXTDOMAIN); ?></th>
													<th><?php _e('Enabled', JCF_TEXTDOMAIN); ?></th>
												</tr></thead>
												<tbody id="the-list-<?php echo $fieldset_id; ?>">
													<?php if( !empty($fieldset['fields'])) : ?>
														<?php foreach($fieldset['fields'] as $field_id => $field) : ?>
														<?php foreach($field as $key_setting => $field_setting): ?>
																<input type="hidden" disabled='disabled' data-fieldset ="import_data_<?php echo $key; ?>_fieldsets_<?php echo $fieldset_id; ?>" data-field = 'import_data_<?php echo $key; ?>_fieldsets_<?php echo $fieldset_id; ?>_fields_<?php echo $field_id; ?>'  name="import_data[<?php echo $key; ?>][fieldsets][<?php echo $fieldset_id; ?>][fields][<?php echo $field_id; ?>][<?php echo $key_setting; ?>]" value="<?php echo $field_setting; ?>" />
														<?php endforeach; ?>
														<tr id="field_row_<?php echo $field_id; ?>">
															<td class="check-column"><input type="checkbox" class="choose_field" name="choose_field[]" value="import_data_<?php echo $key; ?>_fieldsets_<?php echo $fieldset_id; ?>_fields_<?php echo $field_id; ?>" /></td>
															<td><strong><?php echo $field['title']; ?></strong></td>
															<td><?php echo $field['type']; ?></td>
															<td><?php echo $field['slug']; ?></td>
															<td><?php if($field['enabled']) _e('Yes', JCF_TEXTDOMAIN); else  _e('No', JCF_TEXTDOMAIN);?></td>
														</tr>
														<?php endforeach; ?>
													<?php else : ?>
														<tr><td colspan="4" align="center"><?php _e('Please check import file ', JCF_TEXTDOMAIN); ?></td></tr>
													<?php endif; ?>
												</tbody>
											</table>
										</div>
									</div>
								<?php endforeach; ?>
							<?php endif; ?>
						</li>
					<?php endforeach; ?>
					</ul>
				</div>
				<div class="jcf-modal-button">
					<input type="submit" class="button-primary" name="save_import" value="Save Fields" />
				</div>
			</form>
		<?php endif; ?>
	</div>
</div>