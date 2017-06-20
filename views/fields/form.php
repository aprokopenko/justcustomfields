<?php
/**
 * Form view
 *
 * @package default
 * @author Alexander Prokopenko
 */

$op = ( $field->id_base === $field->id ) ? __( 'Add', \JustCustomFields::TEXTDOMAIN ) : __( 'Edit', \JustCustomFields::TEXTDOMAIN ); ?>
<div class="jcf_edit_modal_shadow">
	<div class="jcf_edit_field">
		<h3 class="header"><?php echo esc_html( $op . ' ' . $field->title ); ?></h3>
		<a href="#close" class="button-link jcf_close field-control-close" type="button"><span
					class="media-modal-icon"></span></a>
		<div class="jcf_inner_content">
			<form action="#" method="post"
				  id="<?php echo( $field->is_collection_field() ? 'jcform_edit_collection_field' : 'jcform_edit_field' ); ?>">
				<fieldset>
					<input type="hidden" name="field_id" value="<?php echo esc_attr( $field->id ); ?>"/>
					<input type="hidden" name="field_number" value="<?php echo esc_attr( $field->number ); ?>"/>
					<input type="hidden" name="field_id_base" value="<?php echo esc_attr( $field->id_base ); ?>"/>
					<input type="hidden" name="fieldset_id" value="<?php echo esc_attr( $field->fieldset_id ); ?>"/>
					<?php if ( $field->is_collection_field() ) : ?>
						<input type="hidden" name="collection_id"
							   value="<?php echo esc_attr( $field->collection_id ); ?>"/>
						<?php
					endif;
					$field->form();
					// need to add slug field too.
					$slug = esc_attr( $field->slug );
					?>
					<p>
						<label for="<?php echo esc_attr( $field->get_field_id( 'slug' ) ); ?>"><?php esc_html_e( 'Slug:', \JustCustomFields::TEXTDOMAIN ); ?></label>
						<input class="widefat" id="<?php echo esc_attr( $field->get_field_id( 'slug' ) ); ?>"
							   name="<?php echo $field->get_field_name( 'slug' ); ?>" type="text"
							   value="<?php echo esc_attr( $slug ); ?>"/>
						<br/>
						<small><?php esc_html_e( 'Machine name, will be used for postmeta field name. (should start from underscore)', \JustCustomFields::TEXTDOMAIN ); ?></small>
					</p>
					<?php
					// enabled field.
					if ( $field->is_new ) {
						$field->instance['enabled'] = 1;
					}
					?>
					<p>
						<label for="<?php echo esc_attr( $field->get_field_id( 'enabled' ) ); ?>">
							<input class="checkbox" type="checkbox"
								   id="<?php echo esc_attr( $field->get_field_id( 'enabled' ) ); ?>"
								   name="<?php echo $field->get_field_name( 'enabled' ); ?>"
								   value="1" <?php checked( true, @$field->instance['enabled'] ); ?> />
							<?php esc_html_e( 'Enabled', \JustCustomFields::TEXTDOMAIN ); ?></label>
					</p>
					<?php if ( $field->is_collection_field() ) : ?>
						<?php if ( 'inputtext' === $field->id_base ) : ?>
							<p>
								<label for="<?php echo esc_attr( $field->get_field_id( 'group_title' ) ); ?>">
									<input class="checkbox" type="checkbox"
										   id="<?php echo esc_attr( $field->get_field_id( 'group_title' ) ); ?>"
										   name="<?php echo $field->get_field_name( 'group_title' ); ?>"
										   value="1" <?php checked( true, @$field->instance['group_title'] ); ?> />
									<?php esc_html_e( 'Use this field as collection item title?', \JustCustomFields::TEXTDOMAIN ); ?>
								</label>
							</p>

						<?php endif; ?>
						<p>
							<label for="<?php echo esc_attr( $field->get_field_id( 'field_width' ) ); ?>">
								<?php esc_html_e( 'Select Field Width', \JustCustomFields::TEXTDOMAIN ); ?></label>
							<select class="widefat"
									id="<?php echo esc_attr( $field->get_field_id( 'field_width' ) ); ?>"
									name="<?php echo $field->get_field_name( 'field_width' ); ?>">
								<?php foreach ( \jcf\components\collection\JustField_Collection::$field_width as $key => $width ) : ?>
									<option value="<?php echo esc_attr( $key ); ?>"<?php echo( @$field->instance['field_width'] == $key ? ' selected' : '' ); ?>>
										<?php echo esc_attr( $width ); ?></option>
								<?php endforeach; ?>
							</select>
						</p>
					<?php endif; ?>
				</fieldset>
				<div class="field-control-actions">
					<div class="alignleft">
						<?php if ( __( 'Add', \JustCustomFields::TEXTDOMAIN ) !== $op ) : ?>
							<a href="#remove"
							   class="field-control-remove submitdelete"><?php esc_html_e( 'Delete', \JustCustomFields::TEXTDOMAIN ); ?></a> |
						<?php endif; ?>
						<a href="#close"
						   class="field-control-close"><?php esc_html_e( 'Close', \JustCustomFields::TEXTDOMAIN ); ?></a>
					</div>
					<div class="alignright">
						<input type="submit" value="<?php esc_html_e( 'Save', \JustCustomFields::TEXTDOMAIN ); ?>"
							   class="jcf-btn-save button-primary" name="savefield">
					</div>
					<br class="clear"/>
				</div>
			</form>
		</div>
	</div>
</div>