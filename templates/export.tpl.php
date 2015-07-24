<div class="wrap">
	<div class="icon32 icon32-posts-page" id="icon-edit"><br></div>
	<h2><?php _e('Just Custom Fields Export', JCF_TEXTDOMAIN); ?></h2>
	<div class="jcf-export-fields" id="jcf-export-fields">
		<?php if( $post_types ): ?>
			<form method="post" id="jcf_export_fields" action="<?php echo get_home_url();?>/wp-admin/admin-ajax.php" >
				<input type="hidden" name ="action" value="jcf_export_fields" />
				
				<div id="jcf_save_export_fields_content">
					<p><?php _e('You should choose Fields to export:', JCF_TEXTDOMAIN); ?></p>
					
					<ul class="dotted-list jcf-bold jcf_width66p">
					<?php foreach( $post_types as $key => $post_type ): ?>
						<?php if(!empty($fieldsets[$key])) :?>
						<li class="jcf_export-content-type">
							<h3>
								<span class="jcf_checkbox_block"><input type="checkbox" name="select_content_type" value="" class="jcf-select_content_type"  /></span>
								<?php _e('Content type: ', JCF_TEXTDOMAIN); ?><?php echo $key; ?>
							</h3>
							<input type="hidden" disabled="disabled" name="export_data[<?php echo $key; ?>]" value="<?php echo $key; ?>" />
							
							<?php foreach( $fieldsets[$key] as $fieldset_id => $fieldset ) : ?>
								<input type="hidden" disabled='disabled' 
									   data-fieldset ="export_data_<?php echo $key; ?>_fieldsets_<?php echo $fieldset_id; ?>"  
									   name="export_data[<?php echo $key; ?>][fieldsets][<?php echo $fieldset_id; ?>][title]"
									   value="<?php echo $fieldset['title']; ?>" class="jcf_hidden_fieldset" 
									/>
								<div class="jcf_inner_box" id="jcf_fieldset_<?php echo $fieldset_id; ?>">
									<h3 class="header">
										<span class="jcf_checkbox_block"><input type="checkbox" name="choose_fieldset" value="export_data_<?php echo $key; ?>_fieldsets_<?php echo $fieldset_id; ?>" class="jcf-choose_fieldset" /></span>
										<?php _e('Fieldset:', JCF_TEXTDOMAIN); ?> <span><?php echo $fieldset['title'];  ?></span>
									</h3>
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
														<tr id="field_row_<?php echo $field_id; ?>">
															<td class="check-column">
																<input type="checkbox" class="choose_field" name="choose_field[]" 
																	   value="export_data_<?php echo $key; ?>_fieldsets_<?php echo $fieldset_id; ?>" 
																	   id="export_data_<?php echo $key; ?>_fieldsets_<?php echo $fieldset_id; ?>_fields_<?php echo $field_id; ?>" 
																	/>
															</td>
															<?php if( !empty($field_settings[$key][$field_id]) ):?>
															<?php foreach($field_settings[$key][$field_id] as $key_setting => $field_setting): ?>
																<input type="hidden" disabled=disabled' 
																	   data-fieldset ="export_data_<?php echo $key; ?>_fieldsets_<?php echo $fieldset_id; ?>" 
																	   data-field = 'export_data_<?php echo $key; ?>_fieldsets_<?php echo $fieldset_id; ?>_fields_<?php echo $field_id; ?>'  
																	   name="export_data[<?php echo $key; ?>][fieldsets][<?php echo $fieldset_id; ?>][fields][<?php echo $field_id; ?>][<?php echo $key_setting; ?>]" 
																	   value="<?php echo $field_setting; ?>" 
																	/>
															<?php endforeach; ?>
															<?php endif; ?>
															<td><?php echo $field_settings[$key][$field_id]['title']; ?></td>
															<td><?php echo preg_replace('/\-[0-9]+$/', '', $field_id); ?></td>
															<td><?php echo $field_settings[$key][$field_id]['slug']; ?></td>
															<td><?php if($field_settings[$key][$field_id]['enabled']) _e('Yes', JCF_TEXTDOMAIN); else  _e('No', JCF_TEXTDOMAIN);?></td>
														</tr>
													<?php endforeach; ?>
												<?php endif; ?>
											</tbody>
										</table>
									</div>
								</div>
							<?php endforeach; // foreach post type fielfsets ?>
						</li>
						<?php endif; // if post type fieldsets exists ?>
					<?php endforeach; // foreach post types ?>
					</ul>
				</div>
				<div class="jcf-modal-button">
					<input type="submit" class="button-primary" name="export_fields" value="<?php _e('Export', JCF_TEXTDOMAIN); ?>" />
				</div>
			</form>
		<?php endif; ?>
	</div>
</div>
