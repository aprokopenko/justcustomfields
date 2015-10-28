<table class="wp-list-table widefat fixed" cellspacing="0">
	<thead><tr>
		<!--th class="check-column">&nbsp;</th-->
		<th><?php _e('Title', JCF_TEXTDOMAIN); ?></th>
		<th><?php _e('Type', JCF_TEXTDOMAIN); ?></th>
		<th><?php _e('Cols', JCF_TEXTDOMAIN); ?></th>
		<th><?php _e('Enabled', JCF_TEXTDOMAIN); ?></th>
	</tr></thead>
	<tfoot><tr>
		<!--th class="check-column">&nbsp;</th-->
		<th><?php _e('Title', JCF_TEXTDOMAIN); ?></th>
		<th><?php _e('Type', JCF_TEXTDOMAIN); ?></th>
		<th><?php _e('Cols', JCF_TEXTDOMAIN); ?></th>
		<th><?php _e('Enabled', JCF_TEXTDOMAIN); ?></th>
	</tr></tfoot>
	<tbody id="the-list-<?php echo $fieldset['id']; ?>">
		<?php if( !empty($fieldset['fields']) && is_array($fieldset['fields']) ) : ?>
			<?php foreach($fieldset['fields'] as $field_id => $enabled) : ?>
				<tr id="field_row_<?php echo $field_id; ?>">
					<!--td class="check-column">
						<span class="drag-handle">move</span>
					</td-->
					<td>
						<strong><a href="#" rel="<?php echo $field_id; ?>"><?php echo $field_settings[$field_id]['title']; ?></a></strong>
						<div class="row-actions">
							<span class="edit"><a href="#" rel="<?php echo $field_id; ?>"><?php _e('Edit', JCF_TEXTDOMAIN); ?></a></span> |
							<span class="delete"><a href="#" rel="<?php echo $field_id; ?>"><?php _e('Delete', JCF_TEXTDOMAIN); ?></a></span>
						</div>
					</td>
					<td><?php echo $field_settings[$field_id]['slug']; ?></td>
					<td><?php echo preg_replace('/\-[0-9]+$/', '', $field_id); ?></td>
					<td><?php if($enabled) _e('Yes', JCF_TEXTDOMAIN); else  _e('No', JCF_TEXTDOMAIN);?></td>
				</tr>
			<?php endforeach; ?>
		<?php else : ?>
		<tr><td colspan="4" align="center"><?php _e('Please create fields for this collection', JCF_TEXTDOMAIN); ?></td></tr>
		<?php endif; ?>
	</tbody>
</table>
<?php $registered_fields = jcf_get_registered_fields(); // Add fieldset Form ?>
<div class="jcf_inner_content">
	<form action="#" method="post" class="jcform_add_field">
		<fieldset>
			<input type="hidden" name="fieldset_id" value="<?php echo $fieldset['id']; ?>" />
			<label class="nowrap"><?php _e('Add new Field:', JCF_TEXTDOMAIN); ?> </label>
			<select name="field_type" class="jcf_add_field">
				<?php foreach($registered_fields as $field) : ?>
				<option value="<?php echo $field['id_base']; ?>"><?php echo $field['title']; ?></option>
				<?php endforeach; ?>
			</select>
			<input type="submit" name="add_field" value="<?php _e('Add', JCF_TEXTDOMAIN); ?>" />
			<?php echo print_loader_img(); ?>
		</fieldset>
	</form>
</div>