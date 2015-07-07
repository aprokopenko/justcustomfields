<div class="wrap">
	<div class="jcf_columns jcf_width66p">
		<div class="icon32 icon32-posts-page" id="icon-edit"><br></div>
		<h2><?php _e('Just Custom Fields Export', JCF_TEXTDOMAIN); ?>
			<small><a href="?page=just_custom_fields" class="jcf_change_pt"><?php _e('back', JCF_TEXTDOMAIN); ?></a></small></h2>
		<p><?php _e('You should choose Fields to export:', JCF_TEXTDOMAIN); ?></p>
		<form method="post" id="jcf_export_fields" action="<?php echo get_home_url();?>/wp-admin/admin-ajax.php" >
		<input type="hidden" name ="action" value="jcf_export_fields" />
		<ul class="jcf-export-list">
		<?php foreach($post_types as $key => $post_type) : ?>
			<li><input type="checkbox" name="export_data[<?php echo $key; ?>]" value="<?php echo $key; ?>" class="jcf_post_type" id="jcf_post_type_<?php echo $key; ?>" /><label for="jcf_post_type_<?php echo $key; ?>"><?php echo is_array($post_type) ? $post_type['label'] : $post_type->label; ?></label>
				<ul>
				<?php foreach($fieldsets[$key] as $fieldset): ?>
					<li><input type="checkbox" name="export_data[<?php echo $key; ?>][fieldsets][<?php echo $fieldset['id']; ?>][title]" value="<?php echo $fieldset['title']; ?>" class="jcf_fieldset" id="jcf_fieldset_<?php echo $fieldset['id']; ?>" /><label for="jcf_fieldset_<?php echo $fieldset['id']; ?>"><?php echo 'fieldset: ' . $fieldset['title']; ?></label>
						<ul>
							<?php foreach ($fieldset['fields'] as $field_id => $enabled): ?>
							<li>
								<input type="checkbox" name="export_data[<?php echo $key; ?>][fieldsets][<?php echo $fieldset['id']; ?>][fields][<?php echo $field_id; ?>][title]" value="<?php echo $field_settings[$key][$field_id]['title']; ?>" class="jcf_field" data-id="<?php echo $field_id; ?>" id="jcf_field_<?php echo $field_id; ?>" /><label for="jcf_field_<?php echo $field_id; ?>"><?php echo $field_settings[$key][$field_id]['title'] . ' (' . preg_replace('/\-[0-9]+$/', '', $field_id) .')'; ?></label>
								<div id="jcf_field_settings_<?php echo $field_id; ?>" class="jcf_hide_field_settings">
								<input type="hidden" disabled="disabled" name="export_data[<?php echo $key; ?>][fieldsets][<?php echo $fieldset['id']; ?>][fields][<?php echo $field_id; ?>][type]" value="<?php echo preg_replace('/\-[0-9]+$/', '', $field_id); ?>" />
								<?php foreach($field_settings[$key][$field_id] as $key_setting => $field_setting): ?>
									<?php if($key_setting != 'title' && $key_setting != 'slug'):?>
										<input type="hidden" disabled="disabled" name="export_data[<?php echo $key; ?>][fieldsets][<?php echo $fieldset['id']; ?>][fields][<?php echo $field_id; ?>][<?php echo $key_setting; ?>]" value="<?php echo $field_setting; ?>" />
									<?php endif; ?>
								<?php endforeach; ?>
								</div>
							<?php endforeach; ?>
						</ul>
					</li>
				<?php endforeach;?>
				</ul>
			</li>
		<?php endforeach; ?>
		</ul>
		<div class="alignleft">
			<input type="submit" class="button-primary" name="export_fields" value="<?php _e('Export', JCF_TEXTDOMAIN); ?>" />
		</div>
		</form>
	</div>
	<?php // ajax container ?>
	<div class="jcf_columns jcf_width33p" id="jcf_ajax_container">
		<div class="jcf_inner_box" id="jcf_ajax_content"></div>
	</div>
	<div class="jcf_clear"></div>
</div>
