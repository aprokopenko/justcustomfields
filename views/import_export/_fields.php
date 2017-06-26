<?php
/* @var $pt_key string */
/* @var $post_type_fieldsets array */
/* @var $field_settings array */
?>
<?php foreach ( $post_type_fieldsets as $fieldset_id => $fieldset ) : ?>
	<div class="jcf_inner_box" id="<?php echo esc_attr( "jcf_fieldset_{$pt_key}_{$fieldset_id}" ); ?>">
		<h3 class="header">
			<span class="jcf_checkbox_block">
				<?php echo jcf_html_checkbox( array(
					'name'                  => "selected_data[{$pt_key}][{$fieldset_id}][id]",
					'value'                 => $fieldset_id,
					'class'                 => 'jcf_fieldset_select_all',
					'data-fields_container' => "#jcf_fieldset_fields_{$pt_key}_{$fieldset_id}",
				) ); ?>
			</span>
			<?php esc_html_e( 'Fieldset:', 'jcf' ); ?>
			<span><?php echo esc_html( $fieldset['title'] ); ?></span>
		</h3>
		<div class="jcf_inner_content" id="<?php echo esc_attr( "jcf_fieldset_fields_{$pt_key}_{$fieldset_id}" ); ?>">
			<table class="wp-list-table widefat fixed" cellspacing="0">
				<thead>
				<tr>
					<th class="check-column">&nbsp;</th>
					<th><?php esc_html_e( 'Field', 'jcf' ); ?></th>
					<th><?php esc_html_e( 'Type', 'jcf' ); ?></th>
					<th><?php esc_html_e( 'Slug', 'jcf' ); ?></th>
					<th><?php esc_html_e( 'Enabled', 'jcf' ); ?></th>
				</tr>
				</thead>
				<tbody>
				<?php if ( ! empty( $fieldset['fields'] ) ) : ?>
					<?php foreach ( $fieldset['fields'] as $field_id => $field ) : ?>
						<tr id="<?php echo esc_attr( "jcf_field_{$pt_key}_{$fieldset_id}_{$field_id}" ) ?>">
							<td class="check-column">
								<?php
								// checkbox.
								echo jcf_html_checkbox( array(
									'class'                     => 'jcf_field_select',
									'name'                      => "selected_data[{$pt_key}][{$fieldset_id}][fields][{$field_id}][id]",
									'value'                     => $field_id,
									'data-is_collection'        => (int) preg_match( '/^collection\-/', $field_id ),
									'data-collection_container' => "#jcf_collection_fields_{$pt_key}_{$fieldset_id}_{$field_id}",
								) );
								?>
							</td>

							<?php if ( ! preg_match( '/^collection\-/', $field_id ) ) : ?>
								<td><?php echo esc_html( $field_settings[ $pt_key ][ $field_id ]['title'] ); ?></td>
								<td><?php echo preg_replace( '/\-[0-9]+$/', '', $field_id ); ?></td>
								<td><?php echo esc_html( $field_settings[ $pt_key ][ $field_id ]['slug'] ); ?></td>
								<td><?php if ( $field_settings[ $pt_key ][ $field_id ]['enabled'] ) {
										esc_html_e( 'Yes', 'jcf' );
									} else {
										esc_html_e( 'No', 'jcf' );
									} ?></td>

							<?php else : ?>
								<td>
									<ul>
										<li><?php echo esc_html( $field_settings[ $pt_key ][ $field_id ]['title'] ); ?></li>
										<li><strong><?php esc_html_e( 'Type', 'jcf' ); ?></strong>:
											<em><?php echo preg_replace( '/\-[0-9]+$/', '', $field_id ); ?></em></li>
										<li><strong><?php esc_html_e( 'Slug', 'jcf' ); ?></strong>:
											<em><?php echo esc_attr( $field_settings[ $pt_key ][ $field_id ]['slug'] ); ?></em>
										</li>
										<li><strong><?php esc_html_e( 'Enabled', 'jcf' ); ?></strong>:
											<em><?php if ( $field_settings[ $pt_key ][ $field_id ]['enabled'] ) {
													esc_html_e( 'Yes', 'jcf' );
												} else {
													esc_html_e( 'No', 'jcf' );
												} ?></em></li>
									</ul>
								</td>
								<td colspan="3"
									id="<?php echo esc_attr( "jcf_collection_fields_{$pt_key}_{$fieldset_id}_{$field_id}" ); ?>">
									<table class="wp-list-table widefat fixed fieldset-fields-table" cellspacing="0">
										<tr>
											<th class="check-column">&nbsp;</th>
											<th><?php esc_html_e( 'Field', 'jcf' ); ?></th>
											<th><?php esc_html_e( 'Type', 'jcf' ); ?></th>
											<th><?php esc_html_e( 'Slug', 'jcf' ); ?></th>
											<th><?php esc_html_e( 'Enabled', 'jcf' ); ?></th>
										</tr>
										<?php if ( ! empty( $field_settings[ $pt_key ][ $field_id ]['fields'] )
										           && is_array( $field_settings[ $pt_key ][ $field_id ]['fields'] )
										) :

											$collection_fields = $field_settings[ $pt_key ][ $field_id ]['fields'];
											?>
											<?php foreach ( $collection_fields as $collection_field_id => $collection_field ) : ?>
											<tr>
												<td>
													<?php echo jcf_html_checkbox( array(
														'name'  => "selected_data[{$pt_key}][{$fieldset_id}][fields][{$field_id}][collection_fields][{$collection_field_id}]",
														'value' => 1,
														'class' => 'jcf-collection_field_select',
													) ); ?>
												</td>
												<td><?php echo esc_html( $collection_field['title'] ); ?></td>
												<td><?php echo preg_replace( '/\-[0-9]+$/', '', $collection_field_id ); ?></td>
												<td><?php echo esc_html( $collection_field['slug'] ); ?></td>
												<td><?php if ( $collection_field['enabled'] ) {
														esc_html_e( 'Yes', 'jcf' );
													} else {
														esc_html_e( 'No', 'jcf' );
													} ?>
												</td>
											</tr>
										<?php endforeach; ?>
										<?php endif; ?>
									</table>
								</td>
							<?php endif; // if !collection. ?>
						</tr>
					<?php endforeach; ?>
				<?php endif; ?>
				</tbody>
			</table>
		</div>
	</div>
<?php endforeach; // foreach post type fielfsets. ?>
