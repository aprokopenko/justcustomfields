<div class="wrap">
	<div>
		<div class="icon32 icon32-posts-page" id="icon-edit"><br></div>
		<h2><?php _e('Just Custom Fields Import', JCF_TEXTDOMAIN); ?>
			<small><a href="?page=just_custom_fields" class="jcf_change_pt"><?php _e('back', JCF_TEXTDOMAIN); ?></a></small></h2>
		<p><?php _e('Add file to import:', JCF_TEXTDOMAIN); ?></p>
		<form action="<?php get_permalink(); ?>" method="post" id="jcf_import_fields" enctype="multipart/form-data" >
			<div>
				<input type="file" name="import_data" /><br />
				<small>file extention: .json</small>
			</div>	
			<div>
				<input type="submit" class="button-primary" name="import-btn" value="<?php _e('Import', JCF_TEXTDOMAIN); ?>" />
			</div>
		</form>
	</div>
	<div class="jcf-import-fields">
		<?php if( $post_types ): ?>
			<form action="<?php get_permalink(); ?>" method="post" id="jcf_save_import_fields">
				<input type="hidden" name="file_name" value="<?php echo $uploadfile; ?>" />
				<ul class="dotted-list jcf-bold">
				<?php foreach( $post_types as $key => $post_type ): ?>
					<li><?php echo 'Content type: ' . $key; ?>
						<input type="hidden" name="import_data[<?php echo $key; ?>]" value="<?php echo $key; ?>" />
						<?php if(!empty($post_type['fieldsets'])) :?>
							<?php foreach( $post_type['fieldsets'] as $fieldset_id =>$fieldset ) : ?>
								<input type="hidden" name="import_data[<?php echo $key; ?>][fieldsets][<?php echo $fieldset_id; ?>][title]" value="<?php echo $fieldset['title']; ?>" />
								<div class="jcf_inner_box" id="jcf_fieldset_<?php echo $fieldset_id; ?>">
									<h3 class="header"><?php _e('Fieldset:', JCF_TEXTDOMAIN); ?> <span><?php echo $fieldset->title; ?></span></h3>
									<div class="jcf_inner_content">
										<table class="wp-list-table widefat fixed" cellspacing="0">
											<thead><tr>
												<th class="check-column">&nbsp;</th>
												<th><?php _e('Field', JCF_TEXTDOMAIN); ?></th>
												<th><?php _e('Type', JCF_TEXTDOMAIN); ?></th>
												<th><?php _e('Enabled', JCF_TEXTDOMAIN); ?></th>
											</tr></thead>
											<tfoot><tr>
												<th class="check-column">&nbsp;</th>
												<th><?php _e('Field', JCF_TEXTDOMAIN); ?></th>
												<th><?php _e('Type', JCF_TEXTDOMAIN); ?></th>
												<th><?php _e('Enabled', JCF_TEXTDOMAIN); ?></th>
											</tr></tfoot>
											<tbody id="the-list-<?php echo $fieldset_id; ?>">
												<?php if( !empty($fieldset['fields'])) : ?>
													<?php foreach($fieldset['fields'] as $field_id => $field) : ?>
													<?php foreach($field as $key_setting => $field_setting): ?>
															<input type="hidden"  name="import_data[<?php echo $key; ?>][fieldsets][<?php echo $fieldset_id; ?>][fields][<?php echo $field_id; ?>][<?php echo $key_setting; ?>]" value="<?php echo $field_setting; ?>" />
													<?php endforeach; ?>
													<tr id="field_row_<?php echo $field_id; ?>">
														<td class="check-column"></td>
														<td><strong><?php echo $field['title']; ?></strong></td>
														<td><?php echo $field['type']; ?></td>
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
				<input type="submit" class="button-primary" name="save_import" value="Save Fields" />
				<input type="button" onclick="window.location.reload();" class="button-primary" name="close_import" value="Close" />
			</form>
		<?php endif; ?>
	</div>
</div>
