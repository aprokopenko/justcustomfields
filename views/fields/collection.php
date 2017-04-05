<table class="wp-list-table widefat fixed collection-fields-table" cellspacing="0">
	<thead><tr>
		<th class="check-column">&nbsp;</th>
		<th><?php _e('Title', \JustCustomFields::TEXTDOMAIN); ?></th>
		<th width="150"><?php _e('Type', \JustCustomFields::TEXTDOMAIN); ?></th>
		<th width="50"><?php _e('Width', \JustCustomFields::TEXTDOMAIN); ?></th>
		<th width="100"><?php _e('Enabled', \JustCustomFields::TEXTDOMAIN); ?></th>
	</tr></thead>
	<tfoot><tr>
		<th colspan="5">
			<div class="inner_content">
				<form action="#" method="post" class="jcform_add_collection_field">
					<fieldset>
						<input type="hidden" name="collection_id" value="<?php echo $collection_id; ?>" />
						<input type="hidden" name="fieldset_id" value="<?php echo $fieldset_id; ?>" />
						<label class="nowrap"><?php _e('Add new Field:', \JustCustomFields::TEXTDOMAIN); ?> </label>
						<select name="field_type" class="jcf_add_collection_field">
							<?php foreach( $registered_fields as $field ) :  ?>
								<option value="<?php echo $field['id_base']; ?>"><?php echo esc_html($field['title']); ?></option>
							<?php endforeach; ?>
						</select>
						<input type="submit" class="button show_modal" name="add_field" value="<?php _e('Add', \JustCustomFields::TEXTDOMAIN); ?>" />
						<?php echo jcf_print_loader_img(); ?>
					</fieldset>
				</form>
			</div>
		</th>
	</tr></tfoot>
	<tbody id="the-collection-list-<?php echo $collection_id; ?>" class="ui-sortable">
		<?php if( !empty($collection['fields']) && is_array($collection['fields']) ) : ?>
			<?php foreach($collection['fields'] as $field_id => $field) : ?>
				<tr id="collection_field_row_<?php echo $field_id; ?>">
					<td class="jcf-check-column" align="center">
						<span class="dashicons dashicons-menu drag-handle"></span>
					</td>
					<td>
						<strong><a href="#" rel="<?php echo $field_id; ?>"><?php echo esc_html($field['title']); ?></a></strong>
						<div class="row-actions">
							<span class="edit_collection">
								<a href="#" rel="<?php echo $field_id; ?>" data-collection_id="<?php echo $collection_id; ?>"><?php _e('Edit', \JustCustomFields::TEXTDOMAIN); ?></a>
							</span> |
							<span class="delete_collection" data-collection_id="<?php echo $collection_id; ?>"><a href="#" rel="<?php echo $field_id; ?>" data-collection_id="<?php echo $collection_id; ?>"><?php _e('Delete', \JustCustomFields::TEXTDOMAIN); ?></a></span>
						</div>
					</td>
					<td><?php echo preg_replace('/\-[0-9]+$/', '', $field_id); ?></td>
					<td><?php echo \jcf\components\collection\JustField_Collection::getWidthAlias($field['field_width']); ?>
					</td>
					<td><?php if($field['enabled']) _e('Yes', \JustCustomFields::TEXTDOMAIN); else  _e('No', \JustCustomFields::TEXTDOMAIN);?></td>
				</tr>
			<?php endforeach; ?>
		<?php else : ?>
		<tr><td colspan="5" align="center"><?php _e('Please create fields for this collection', \JustCustomFields::TEXTDOMAIN); ?></td></tr>
		<?php endif; ?>
	</tbody>
</table>