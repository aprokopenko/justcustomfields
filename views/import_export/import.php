<div class="wrap">
	<div class="icon32 icon32-posts-page" id="icon-edit"><br></div>
	<h2><?php _e('Just Custom Fields Import', \JustCustomFields::TEXTDOMAIN); ?></h2>
	
	<?php do_action('jcf_print_admin_notice'); ?>
	
	<div class="jcf-import-fields" id="jcf-import-fields">
		<?php if( $post_types ): ?>
			<form action="<?php get_permalink(); ?>" method="post" id="jcf_save_import_fields">
				<div id="jcf_save_import_fields_content">
					<p><?php _e('You should choose Fields to import:', \JustCustomFields::TEXTDOMAIN); ?></p>
					<ul class="dotted-list jcf-bold jcf_width66p">
					<?php foreach( $post_types as $key => $post_type ): ?>
						<li class="jcf_export-content-type">
							<h3>
								<span class="jcf_checkbox_block"><input type="checkbox" name="select_content_type" value="" class="jcf-select_content_type"  /></span>
								<?php _e('Content type: ', \JustCustomFields::TEXTDOMAIN); ?><?php echo $key; ?>
							</h3>
							<input type="hidden" name="import_data[<?php echo $key; ?>]" value="<?php echo $key; ?>" />
							
							<?php if(!empty($post_type['fieldsets'])) :?>
								<?php foreach( $post_type['fieldsets'] as $fieldset_id => $fieldset ) : ?>
									<input type="hidden" disabled='disabled' 
										   data-fieldset ="import_data_<?php echo $key; ?>_fieldsets_<?php echo $fieldset_id; ?>"  
										   name="import_data[<?php echo $key; ?>][fieldsets][<?php echo $fieldset_id; ?>][title]" 
										   value="<?php echo $fieldset['title']; ?>" class="jcf_hidden_fieldset" 
										/>
									<div class="jcf_inner_box" id="jcf_fieldset_<?php echo $fieldset_id; ?>">
										<h3 class="header">
											<span class="jcf_checkbox_block">
												<input type="checkbox" name="choose_fieldset" 
													   value="import_data_<?php echo $key; ?>_fieldsets_<?php echo $fieldset_id; ?>" 
													   class="jcf-choose_fieldset" 
													/>
											</span>
											<?php _e('Fieldset:', \JustCustomFields::TEXTDOMAIN); ?> <span><?php echo $fieldset['title'];  ?></span>
										</h3>
										<div class="jcf_inner_content">
											<table class="wp-list-table widefat fixed" cellspacing="0">
												<thead><tr>
													<th class="check-column">&nbsp;</th>
													<th><?php _e('Field', \JustCustomFields::TEXTDOMAIN); ?></th>
													<th><?php _e('Type', \JustCustomFields::TEXTDOMAIN); ?></th>
													<th><?php _e('Slug', \JustCustomFields::TEXTDOMAIN); ?></th>
													<th><?php _e('Enabled', \JustCustomFields::TEXTDOMAIN); ?></th>
												</tr></thead>
												<tbody id="the-list-<?php echo $fieldset_id; ?>">
													<?php if( !empty($fieldset['fields'])) : ?>
														<?php foreach($fieldset['fields'] as $field_id => $field) : ?>
															<?php foreach($field as $key_setting => $field_setting): ?>
																<?php if($key_setting != 'fields'):?>
																	<input type="hidden" disabled='disabled' 
																		   data-fieldset ="import_data_<?php echo $key; ?>_fieldsets_<?php echo $fieldset_id; ?>" 
																		   data-field = 'import_data_<?php echo $key; ?>_fieldsets_<?php echo $fieldset_id; ?>_fields_<?php echo $field_id; ?>'  
																		   name="import_data[<?php echo $key; ?>][fieldsets][<?php echo $fieldset_id; ?>][fields][<?php echo $field_id; ?>][<?php echo $key_setting; ?>]" 
																		   value="<?php echo $field_setting; ?>" 
																		/>
																<?php endif; ?>
															<?php endforeach; ?>
															<tr id="field_row_<?php echo $field_id; ?>">
																<td class="check-column">
																	<input type="checkbox" class="choose_field" name="choose_field[]" 
																		   value="import_data_<?php echo $key; ?>_fieldsets_<?php echo $fieldset_id; ?>" 
																		   id="import_data_<?php echo $key; ?>_fieldsets_<?php echo $fieldset_id; ?>_fields_<?php echo $field_id; ?>" 
																		/>
																	<input type="hidden" disabled='disabled' 
																		data-collection="import_data_<?php echo $key; ?>_fieldsets_<?php echo $fieldset_id; ?>_fields_<?php echo $field_id; ?>"  
																		name="import_data[<?php echo $key; ?>][fieldsets][<?php echo $fieldset_id; ?>][fields][<?php echo $field_id; ?>][title]"
																		value="<?php echo $field['title']; ?>" class="jcf_collection_settings" 
																	 />
																	<input type="hidden" disabled='disabled' 
																		data-collection="import_data_<?php echo $key; ?>_fieldsets_<?php echo $fieldset_id; ?>_fields_<?php echo $field_id; ?>"  
																		name="import_data[<?php echo $key; ?>][fieldsets][<?php echo $fieldset_id; ?>][fields][<?php echo $field_id; ?>][slug]"
																		value="<?php echo $field['slug']; ?>" class="jcf_collection_settings" 
																	 />
																	<input type="hidden" disabled='disabled' 
																		data-collection="import_data_<?php echo $key; ?>_fieldsets_<?php echo $fieldset_id; ?>_fields_<?php echo $field_id; ?>"  
																		name="import_data[<?php echo $key; ?>][fieldsets][<?php echo $fieldset_id; ?>][fields][<?php echo $field_id; ?>][enabled]"
																		value="<?php echo $field['enabled']; ?>" class="jcf_collection_settings" 
																	 />
																</td>
																<?php if(preg_replace('/\-[0-9]+$/', '', $field_id) != 'collection'): ?>
																	<td><strong><?php echo $field['title']; ?></strong></td>
																	<td><?php echo preg_replace('/\-[0-9]+$/', '', $field_id); ?></td>
																	<td><?php echo $field['slug']; ?></td>
																	<td><?php if($field['enabled']) _e('Yes', \JustCustomFields::TEXTDOMAIN); else  _e('No', \JustCustomFields::TEXTDOMAIN);?></td>
																<?php else: ?>
																	<td>
																		<ul>
																			<li><?php echo $field['title']; ?></li>
																			<li><strong><?php _e('Type', \JustCustomFields::TEXTDOMAIN); ?></strong>: <em><?php echo preg_replace('/\-[0-9]+$/', '', $field_id); ?></em></li>
																			<li><strong><?php _e('Slug', \JustCustomFields::TEXTDOMAIN); ?></strong>: <em><?php echo $field['slug']; ?></em></li>
																			<li><strong><?php _e('Enabled', \JustCustomFields::TEXTDOMAIN); ?></strong>: <em><?php if($field['enabled']) _e('Yes', \JustCustomFields::TEXTDOMAIN); else  _e('No', \JustCustomFields::TEXTDOMAIN);?></em></li>
																		</ul>
																	</td>
																	<td colspan="3" >
																		<table class="wp-list-table widefat fixed fieldset-fields-table" cellspacing="0">
																			<tr>
																				<th class="check-column">&nbsp;</th>
																				<th><?php _e('Field', \JustCustomFields::TEXTDOMAIN); ?></th>
																				<th><?php _e('Type', \JustCustomFields::TEXTDOMAIN); ?></th>
																				<th><?php _e('Slug', \JustCustomFields::TEXTDOMAIN); ?></th>
																				<th><?php _e('Enabled', \JustCustomFields::TEXTDOMAIN); ?></th>
																			</tr>
																		<?php if( !empty($field['fields']) && is_array($field['fields']) ): ?>
																			<?php foreach($field['fields'] as $collection_field_id => $collection_field_value):  ?>
																				<tr>
																					<td>
																						<input type="checkbox" class="choose_collection_field" name="choose_collection_field[]"
																						data-fieldset_id="import_data_<?php echo $key; ?>_fieldsets_<?php echo $fieldset_id; ?>"
																						value="import_data_<?php echo $key; ?>_fieldsets_<?php echo $fieldset_id; ?>_fields_<?php echo $field_id; ?>" 
																						id="import_data_<?php echo $key; ?>_fieldsets_<?php echo $fieldset_id; ?>_fields_<?php echo $field_id; ?>_<?php echo $key_setting; ?>_<?php echo $collection_field_id;?>" 
																					 />
																					<?php foreach($collection_field_value as $c_key => $c_value): ?>
																						<input type="hidden" disabled='disabled'
																							   data-collection="import_data_<?php echo $key; ?>_fieldsets_<?php echo $fieldset_id; ?>_fields_<?php echo $field_id; ?>_<?php echo $key_setting; ?>_<?php echo $collection_field_id;?>"
																							   data-fieldset="import_data_<?php echo $key; ?>_fieldsets_<?php echo $fieldset_id; ?>" 
																							   data-field='import_data_<?php echo $key; ?>_fieldsets_<?php echo $fieldset_id; ?>_fields_<?php echo $field_id; ?>_<?php echo $key_setting; ?>_<?php echo $collection_field_id;?>'  
																							   name="import_data[<?php echo $key; ?>][fieldsets][<?php echo $fieldset_id; ?>][fields][<?php echo $field_id; ?>][<?php echo $key_setting; ?>][<?php echo $collection_field_id;?>][<?php echo $c_key;?>]" 
																							   value="<?php echo $c_value ; ?>"
																							/>
																					<?php endforeach; ?>
																					</td>
																					<td><?php echo $collection_field_value['title']; ?></td>
																					<td><?php echo preg_replace('/\-[0-9]+$/', '', $collection_field_id); ?></td>
																					<td><?php echo $collection_field_value['slug']; ?></td>
																					<td><?php if($collection_field_value['enabled']) _e('Yes', \JustCustomFields::TEXTDOMAIN); else  _e('No', \JustCustomFields::TEXTDOMAIN);?></td>
																				</tr>
																			<?php endforeach; ?>
																		<?php endif; ?>	
																		</table>
																	</td>
																<?php endif;?>																
															</tr>
														<?php endforeach; ?>
													<?php else : ?>
														<tr><td colspan="4" align="center"><?php _e('Please check import file ', \JustCustomFields::TEXTDOMAIN); ?></td></tr>
													<?php endif; ?>
												</tbody>
											</table>
										</div>
									</div>
								<?php endforeach; // foreach post type fieldsets ?>
							<?php endif; // if !empty post type fieldsets ?>
						</li>
					<?php endforeach; ?>
					</ul>
				</div>
				<div class="jcf-modal-button">
					<input type="submit" class="button-primary" name="save_import" value="<?php _e('Save Fields ', \JustCustomFields::TEXTDOMAIN); ?>" />
				</div>
			</form>
		<?php endif; ?>
	</div>
</div>
