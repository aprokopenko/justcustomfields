<?php
/**
 * Form view
 *
 * @var $fieldset array
 * @var $post_type string
 * @var $taxonomies \WP_Taxonomy[]
 * @var $templates array
 * @var $post_type_kind string
 *
 * @package default
 */

use jcf\models\Fieldset;
use jcf\core\JustField;

?>
<div class="jcf_edit_modal_shadow">
	<div class="jcf_edit_fieldset">
		<h3 class="header"><?php echo esc_html__( 'Edit Fieldset:', 'jcf' ) . ' ' . esc_html( $fieldset['title'] ); ?></h3>
		<a href="#close" class="button-link jcf_close field-control-close" type="button"><span
					class="media-modal-icon"></span></a>
		<div class="jcf_inner_content">
			<form action="#" method="post" id="jcform_edit_fieldset">
				<fieldset>
					<input type="hidden" name="fieldset_id" value="<?php echo esc_attr( $fieldset['id'] ); ?>"/>

					<p>
						<label for="jcf_edit_fieldset_title"><?php esc_html_e( 'Title:', 'jcf' ); ?></label>
						<input class="widefat" id="jcf_edit_fieldset_title" type="text" name="title"
							   value="<?php echo esc_attr( $fieldset['title'] ); ?>"/>
					</p>
					<?php if ( JustField::POSTTYPE_KIND_POST === $post_type_kind ) : ?>
						<p>
							<label for="jcf_edit_fieldset_position"><?php esc_html_e( 'Position:', 'jcf' ); ?></label><br>
							<select id="jcf_edit_fieldset_position" name="position" style="width:100%;">
								<option value="<?php echo esc_attr( Fieldset::POSITION_ADVANCED ); ?>" <?php echo selected( Fieldset::POSITION_ADVANCED, @$fieldset['position'] ); ?>>
									Advanced
								</option>
								<option value="<?php echo esc_attr( Fieldset::POSITION_SIDE ); ?>" <?php echo selected( Fieldset::POSITION_SIDE, @$fieldset['position'] ); ?>>
									Sidebar
								</option>
								<option value="<?php echo esc_attr( Fieldset::POSITION_NORMAL ); ?>" <?php echo selected( Fieldset::POSITION_NORMAL, @$fieldset['position'] ); ?>>
									Normal
								</option>
							</select>
						</p>
						<p>
							<label for="jcf_edit_fieldset_priority"><?php esc_html_e( 'Priority:', 'jcf' ); ?></label><br>
							<select id="jcf_edit_fieldset_priority" name="priority" style="width:100%;">
								<option value="<?php echo esc_attr( Fieldset::PRIO_DEFAULT ); ?>" <?php echo selected( Fieldset::PRIO_DEFAULT, @$fieldset['priority'] ); ?>>
									Default
								</option>
								<option value="<?php echo esc_attr( Fieldset::PRIO_HIGH ); ?>" <?php echo selected( Fieldset::PRIO_HIGH, @$fieldset['priority'] ); ?>>
									High
								</option>
								<option value="<?php echo esc_attr( Fieldset::PRIO_LOW ); ?>" <?php echo selected( Fieldset::PRIO_LOW, @$fieldset['priority'] ); ?>>
									Low
								</option>
							</select>
						</p>
					<?php endif; ?>

					<?php if ( JustField::POSTTYPE_KIND_POST === $post_type_kind && ( ! empty( $templates ) || ! empty( $taxonomies ) ) ) : ?>
						<div class="field-control-actions">
							<h4>
								<a href="#" class="visibility_toggle">
									<?php esc_html_e( 'Visibility rules', 'jcf' ); ?>
									<span class="<?php echo ! empty( $fieldset['visibility_rules'] ) ? 'dashicons-arrow-up-alt2' : 'dashicons-arrow-down-alt2' ?> dashicons-before"></span>
								</a>
							</h4>
							<div id="visibility"
								 class="<?php echo ! empty( $fieldset['visibility_rules'] ) ? '' : 'hidden' ?>">
								<?php if ( ! empty( $fieldset['visibility_rules'] ) ) : ?>
									<?php $this->_render( 'fieldsets/visibility/rules', array(
										'visibility_rules' => $fieldset['visibility_rules'],
										'post_type'        => $post_type,
									) ); ?>
								<?php else : ?>
									<?php $this->ajax_get_visibility_form(); ?>
								<?php endif; ?>
							</div>
							<br class="clear"/>
						</div>
					<?php endif; ?>
				</fieldset>
				<div class="field-control-actions">
					<div class="alignleft">
						<a href="#remove"
						   class="field-control-remove submitdelete"><?php esc_html_e( 'Delete', 'jcf' ); ?></a>
						|
						<a href="#close"
						   class="field-control-close"><?php esc_html_e( 'Close', 'jcf' ); ?></a>
					</div>
					<div class="alignright">
						<input type="submit" value="<?php esc_html_e( 'Save', 'jcf' ); ?>"
							   class="button-primary jcf-btn-save" name="savefield">
					</div>
					<br class="clear"/>
				</div>
			</form>
		</div>
	</div>
</div>
