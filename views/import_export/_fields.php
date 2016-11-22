<?php
/* @var $pt_key string */
/* @var $post_type_fieldsets array */
/* @var $field_settings array */
?>
<?php foreach ( $post_type_fieldsets as $fieldset_id => $fieldset ) : ?>
	<div class="jcf_inner_box" id="<?php echo "jcf_fieldset_{$pt_key}_{$fieldset_id}"; ?>">
		<h3 class="header">
			<span class="jcf_checkbox_block">
				<?php echo jcf_html_checkbox(array(
					'name' => "selected_data[{$pt_key}][{$fieldset_id}][id]",
					'value' => $fieldset_id,
					'class' => "jcf_fieldset_select_all",
					'data-fields_container' => "#jcf_fieldset_fields_{$pt_key}_{$fieldset_id}",
					)); ?>
			</span>
			<?php _e('Fieldset:', \JustCustomFields::TEXTDOMAIN); ?> <span><?php echo $fieldset['title']; ?></span>
		</h3>
		<div class="jcf_inner_content" id="<?php echo "jcf_fieldset_fields_{$pt_key}_{$fieldset_id}"; ?>">
			<table class="wp-list-table widefat fixed" cellspacing="0">
				<thead><tr>
					<th class="check-column">&nbsp;</th>
					<th><?php _e('Field', \JustCustomFields::TEXTDOMAIN); ?></th>
					<th><?php _e('Type', \JustCustomFields::TEXTDOMAIN); ?></th>
					<th><?php _e('Slug', \JustCustomFields::TEXTDOMAIN); ?></th>
					<th><?php _e('Enabled', \JustCustomFields::TEXTDOMAIN); ?></th>
				</tr></thead>
				<tbody>
				<?php if ( !empty($fieldset['fields']) ) : ?>
					<?php foreach ( $fieldset['fields'] as $field_id => $field ) : ?>
						<tr id="<?php echo "jcf_field_{$pt_key}_{$fieldset_id}_{$field_id}" ?>">
							<td class="check-column">
								<?php
								// checkbox
								echo jcf_html_checkbox(array(
									'class' => "jcf_field_select",
									'name' => "selected_data[{$pt_key}][{$fieldset_id}][fields][{$field_id}][id]",
									'value' => $field_id,
									'data-is_collection' => (int) preg_match('/^collection\-/', $field_id),
									'data-collection_container' => "#jcf_collection_fields_{$pt_key}_{$fieldset_id}_{$field_id}",
								));
								?>
							</td>

							<?php if ( !preg_match('/^collection\-/', $field_id) ): ?>
								<td><?php echo esc_html($field_settings[$pt_key][$field_id]['title']); ?></td>
								<td><?php echo preg_replace('/\-[0-9]+$/', '', $field_id); ?></td>
								<td><?php echo esc_html($field_settings[$pt_key][$field_id]['slug']); ?></td>
								<td><?php if ( $field_settings[$pt_key][$field_id]['enabled'] ) _e('Yes', \JustCustomFields::TEXTDOMAIN);
									else _e('No', \JustCustomFields::TEXTDOMAIN); ?></td>

							<?php else: ?>
								<td>
									<ul>
										<li><?php echo $field_settings[$pt_key][$field_id]['title']; ?></li>
										<li><strong><?php _e('Type', \JustCustomFields::TEXTDOMAIN); ?></strong>: <em><?php echo preg_replace('/\-[0-9]+$/', '', $field_id); ?></em></li>
										<li><strong><?php _e('Slug', \JustCustomFields::TEXTDOMAIN); ?></strong>: <em><?php echo $field_settings[$pt_key][$field_id]['slug']; ?></em></li>
										<li><strong><?php _e('Enabled', \JustCustomFields::TEXTDOMAIN); ?></strong>: <em><?php if ( $field_settings[$pt_key][$field_id]['enabled'] ) _e('Yes', \JustCustomFields::TEXTDOMAIN);
												else _e('No', \JustCustomFields::TEXTDOMAIN); ?></em></li>
									</ul>
								</td>
								<td colspan="3" id="<?php echo "jcf_collection_fields_{$pt_key}_{$fieldset_id}_{$field_id}"; ?>">
									<table class="wp-list-table widefat fixed fieldset-fields-table" cellspacing="0">
										<tr>
											<th class="check-column">&nbsp;</th>
											<th><?php _e('Field', \JustCustomFields::TEXTDOMAIN); ?></th>
											<th><?php _e('Type', \JustCustomFields::TEXTDOMAIN); ?></th>
											<th><?php _e('Slug', \JustCustomFields::TEXTDOMAIN); ?></th>
											<th><?php _e('Enabled', \JustCustomFields::TEXTDOMAIN); ?></th>
										</tr>
										<?php if ( !empty($field_settings[$pt_key][$field_id]['fields'])
											&& is_array($field_settings[$pt_key][$field_id]['fields']) ) :

											$collection_fields = $field_settings[$pt_key][$field_id]['fields'];
											?>
											<?php foreach ( $collection_fields as $collection_field_id => $collection_field ): ?>
												<tr>
													<td>
														<?php echo jcf_html_checkbox(array(
															'name' => "selected_data[{$pt_key}][{$fieldset_id}][fields][{$field_id}][collection_fields][{$collection_field_id}]",
															'value' => 1,
															'class' => "jcf-collection_field_select",
														)); ?>
													</td>
													<td><?php echo esc_html($collection_field['title']); ?></td>
													<td><?php echo preg_replace('/\-[0-9]+$/', '', $collection_field_id); ?></td>
													<td><?php echo esc_html($collection_field['slug']); ?></td>
													<td><?php if ( $collection_field['enabled'] ) _e('Yes', \JustCustomFields::TEXTDOMAIN);
														else _e('No', \JustCustomFields::TEXTDOMAIN); ?>
													</td>
												</tr>
											<?php endforeach; ?>
										<?php endif; ?>
									</table>
								</td>
							<?php endif; // if !collection ?>
						</tr>
					<?php endforeach; ?>
				<?php endif; ?>
				</tbody>
			</table>
		</div>
	</div>
<?php endforeach; // foreach post type fielfsets ?>