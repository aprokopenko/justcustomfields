<?php
/**
 * @var $tab string
 * @var $post_type \WP_Post_Type|\WP_Taxonomy
 * @var $post_type_id string
 * @var $post_type_kind string
 * @var $fieldsets array
 * @var $field_settings array
 * @var $collections array
 * @var $registered_fields array
 */
?>
<?php include(JCF_ROOT . '/views/_header.php'); ?>

	<h2><a href="?page=jcf_admin" class="jcf_change_pt"><?php _e('Fields', \JustCustomFields::TEXTDOMAIN); ?></a> &raquo;
		<?php echo $post_type->label; ?> &raquo; <small><?php _e('Configure', \JustCustomFields::TEXTDOMAIN); ?></small>
	</h2>

	<input type="hidden" id="jcf_post_type_hidden" value="<?php echo $post_type_id; ?>" />
	
	<div class="jcf_columns jcf_width66p">
		<div id="jcf_fieldsets">
		
		<?php  // fieldsets loop
		
		if( !empty($fieldsets) ) : ?>
			<?php foreach($fieldsets as $fieldset) : ?>
			<div>
			<div class="jcf_inner_box" id="jcf_fieldset_<?php echo $fieldset['id']; ?>">
				<h3 class="header">
					<span>
						<span class="dashicons dashicons-menu drag-handle"></span>
						<?php _e('Fieldset:', \JustCustomFields::TEXTDOMAIN); ?> <strong><?php echo esc_html($fieldset['title']); ?></strong>
						<small>
							<a href="#" class="jcf_fieldset_change jcf_change_pt show_modal" rel="<?php echo esc_attr($fieldset['id']); ?>"><?php _e('Edit', \JustCustomFields::TEXTDOMAIN); ?></a>
							<a href="#" class="jcf_fieldset_delete jcf_change_pt submitdelete" rel="<?php echo esc_attr($fieldset['id']); ?>"><?php _e('Delete', \JustCustomFields::TEXTDOMAIN); ?></a>
						</small>
					</span>
				</h3>
				<div class="jcf_inner_content">
					<table class="wp-list-table widefat fixed striped fieldset-fields-table" cellspacing="0">
						<thead><tr>
							<th class="jcf-check-column">&nbsp;</th>
							<th><?php _e('Field', \JustCustomFields::TEXTDOMAIN); ?></th>
							<th><?php _e('Slug', \JustCustomFields::TEXTDOMAIN); ?></th>
							<th><?php _e('Type', \JustCustomFields::TEXTDOMAIN); ?></th>
							<th><?php _e('Enabled', \JustCustomFields::TEXTDOMAIN); ?></th>
						</tr></thead>
						<tfoot><tr>
							<th class="jcf-check-column">&nbsp;</th>
							<th><?php _e('Field', \JustCustomFields::TEXTDOMAIN); ?></th>
							<th><?php _e('Slug', \JustCustomFields::TEXTDOMAIN); ?></th>
							<th><?php _e('Type', \JustCustomFields::TEXTDOMAIN); ?></th>
							<th><?php _e('Enabled', \JustCustomFields::TEXTDOMAIN); ?></th>
						</tr></tfoot>
						<tbody id="the-list-<?php echo $fieldset['id']; ?>">
							<?php if( !empty($fieldset['fields']) && is_array($fieldset['fields']) ) : ?>
								<?php foreach($fieldset['fields'] as $field_id => $enabled) :
										if ( empty($field_settings[$field_id]) ) continue;
									?>
									<tr id="field_row_<?php echo $field_id; ?>" class="field_row <?php echo $field_id; ?>">
										<td class="jcf-check-column" align="center">
											<span class="dashicons dashicons-menu drag-handle"></span>
										</td>
										<td>
											<strong><a href="#" rel="<?php echo $field_id; ?>"><?php echo esc_html($field_settings[$field_id]['title']); ?></a></strong>
											<div class="row-actions">
												<span class="edit"><a href="#" rel="<?php echo $field_id; ?>"><?php _e('Edit', \JustCustomFields::TEXTDOMAIN); ?></a></span> |
												<span class="delete"><a href="#" rel="<?php echo $field_id; ?>"><?php _e('Delete', \JustCustomFields::TEXTDOMAIN); ?></a></span>
											</div>
											<?php if ( 'collection' == @$field_settings[$field_id]['_type'] ) : ?>
												<ul>
													<li><strong><?php _e('Type', \JustCustomFields::TEXTDOMAIN); ?></strong>: <?php echo preg_replace('/\-[0-9]+$/', '', $field_id); ?></li>
													<li><strong><?php _e('Slug', \JustCustomFields::TEXTDOMAIN); ?></strong>: <?php echo esc_html($field_settings[$field_id]['slug']); ?></li>
													<li><strong><?php _e('Enabled', \JustCustomFields::TEXTDOMAIN); ?></strong>: <?php if($enabled) _e('Yes', \JustCustomFields::TEXTDOMAIN); else  _e('No', \JustCustomFields::TEXTDOMAIN);?></li>
												</ul>
											<?php endif; ?>
										</td>
										<?php if ( 'collection' != @$field_settings[$field_id]['_type'] ) : ?>
											<td><?php echo esc_html($field_settings[$field_id]['slug']); ?></td>
											<td><?php echo preg_replace('/\-[0-9]+$/', '', $field_id); ?></td>
											<td><?php if ( $enabled ) _e('Yes', \JustCustomFields::TEXTDOMAIN); else  _e('No', \JustCustomFields::TEXTDOMAIN);?></td>
										<?php else: ?>
											<td colspan="3" class="collection_list" data-collection_id="<?php echo $field_id; ?>">
												<?php $this->_render( 'fields/collection', array(
													'collection' => isset($collections[$field_id])? $collections[$field_id] : array(),
													'collection_id' => $field_id,
													'fieldset_id' => $fieldset['id'],
													'registered_fields' => $collections['registered_fields'],
													'post_type_kind' => $post_type_kind,
												)); ?></td>
										<?php endif; ?>
									</tr>
								<?php endforeach; ?>
							<?php else : ?>
							<tr><td colspan="5" align="center"><?php _e('Please create fields for this fieldset', \JustCustomFields::TEXTDOMAIN); ?></td></tr>
							<?php endif; ?>
						</tbody>
					</table>
				</div>
				<?php if ( !empty($registered_fields) ) : ?>
				<div class="jcf_inner_content">
					<form action="#" method="post" class="jcform_add_field">
						<fieldset>
							<input type="hidden" name="fieldset_id" value="<?php echo $fieldset['id']; ?>" />
							<label class="nowrap"><?php _e('Add new Field:', \JustCustomFields::TEXTDOMAIN); ?> </label>
							<select name="field_type" class="jcf_add_field">
								<?php foreach( $registered_fields as $field ) : ?>
									<option value="<?php echo $field['id_base']; ?>"><?php echo esc_html($field['title']); ?></option>
								<?php endforeach; ?>
							</select>
							<input type="submit" class="button show_modal" name="add_field" value="<?php _e('Add', \JustCustomFields::TEXTDOMAIN); ?>" />
							<?php echo jcf_print_loader_img(); ?>
						</fieldset>
					</form>
				</div>
				<?php endif; ?>
			</div>
			<br class="clear"/>
			</div>
			<?php endforeach; ?>
		<?php endif; ?>
		</div>
		
		<?php // Add fieldset Form ?>
		<div class="jcf_columns jcf_width50p">
			<div class="jcf_inner_box">
				<h3 class="header"><?php _e('Add Fieldset', \JustCustomFields::TEXTDOMAIN); ?></h3>
				<div class="jcf_inner_content">
					<form action="#" id="jcform_add_fieldset" method="post" class="jcf_form_horiz">
						<fieldset>
							<label for="jcf_fieldset_title"><?php _e('Title:', \JustCustomFields::TEXTDOMAIN); ?> </label>
							<input type="text" class="text" name="jcf_fieldset_title" id="jcf_fieldset_title" value="" />
							<input type="submit" class="button" name="jcf_add_fieldset" value="<?php _e('Add', \JustCustomFields::TEXTDOMAIN); ?>" />
							<?php echo jcf_print_loader_img(); ?>
						</fieldset>
					</form>
				</div>
			</div>
		</div>
	</div>
	
	<?php // ajax container ?>
	<div class="jcf_columns jcf_width33p" id="jcf_ajax_container">
		<div class="jcf_inner_box" id="jcf_ajax_content"></div>
	</div>
	<div class="jcf_clear"></div>
	
<?php include(JCF_ROOT . '/views/_footer.php'); ?>