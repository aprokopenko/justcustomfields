<?php

/**
 * Visibility form
 *
 * @var array $visibility_rule Visibility Rule
 */
use jcf\models\FieldsetVisibility;

if ( empty( $visibility_rule ) ) {
	$visibility_rule = array(
		'rule_id'           => '',
		'visibility_option' => FieldsetVisibility::VISIBILITY_HIDE,
		'join_condition'    => FieldsetVisibility::JOIN_AND,
		'based_on'          => '',

	);
}

?>
<fieldset id="fieldset_visibility_rules">
	<legend>
		<?php
		if ( ! empty( $scenario ) && FieldsetVisibility::SCENARIO_UPDATE === $scenario ) {
			esc_html_e( 'Edit rule', \JustCustomFields::TEXTDOMAIN );
		} else {
			esc_html_e( 'Add rule', \JustCustomFields::TEXTDOMAIN );
		}
		?>
	</legend>

	<?php // Status for fieldset. ?>
	<div class="visibility-options">
		<p><?php esc_html_e( 'You are about to set the visibility option for this fieldset', \JustCustomFields::TEXTDOMAIN ); ?></p>
		<input type="radio" name="visibility_option" id="visibility-option-hide"
			   value="<?php echo esc_attr( FieldsetVisibility::VISIBILITY_HIDE ); ?>"
			<?php echo( ( ! empty( $scenario ) && FieldsetVisibility::SCENARIO_UPDATE === $scenario ) ? checked( $visibility_rule['visibility_option'], 'hide' ) : 'checked' ); ?> />
		<label for="visibility-option-hide"><?php esc_html_e( 'Hide fieldset', \JustCustomFields::TEXTDOMAIN ); ?></label>
		<br class="clear"/>
		<input type="radio" name="visibility_option" id="visibility-option-show"
			   value="<?php echo esc_attr( FieldsetVisibility::VISIBILITY_SHOW ); ?>"
			<?php checked( $visibility_rule['visibility_option'], 'show' ); ?> />
		<label for="visibility-option-show"><?php esc_html_e( 'Show fieldset', \JustCustomFields::TEXTDOMAIN ); ?></label>
	</div>

	<?php // Condition fields for rule. ?>
	<div class="join-condition <?php echo( ( ( ! empty( $scenario ) && FieldsetVisibility::SCENARIO_CREATE === $scenario ) || 0 !== $rule_id ) ? '' : 'hidden' ); ?>">
		<p>
			<label for="rule-join-condition"><?php esc_html_e( 'Join condition with previous rules with operator:', \JustCustomFields::TEXTDOMAIN ); ?></label>
			<br/>
			<select name="join_condition" id="rule-join-condition">
				<option value="and" <?php selected( $visibility_rule['join_condition'], FieldsetVisibility::JOIN_AND ); ?> ><?php esc_html_e( 'AND', \JustCustomFields::TEXTDOMAIN ); ?></option>
				<option value="or" <?php selected( $visibility_rule['join_condition'], FieldsetVisibility::JOIN_OR ); ?> ><?php esc_html_e( 'OR', \JustCustomFields::TEXTDOMAIN ); ?></option>
			</select>
		</p>
	</div>

	<p>
		<label for="rule-based-on"><?php esc_html_e( 'Based on:', \JustCustomFields::TEXTDOMAIN ); ?></label><br/>
		<select name="based_on" id="rule-based-on">
			<option value=""
					disabled="disabled" <?php echo ( ! empty( $scenario ) && FieldsetVisibility::SCENARIO_UPDATE === $scenario ) ? '' : 'selected'; ?> >
				<?php esc_html_e( 'Choose option', \JustCustomFields::TEXTDOMAIN ); ?>
			</option>

			<?php if ( ! empty( $templates ) ) : ?>
				<option value="page_template" <?php selected( $visibility_rule['based_on'], FieldsetVisibility::BASEDON_PAGE_TPL ); ?> >
					<?php esc_html_e( 'Page template', \JustCustomFields::TEXTDOMAIN ); ?>
				</option>
			<?php endif; ?>

			<?php if ( ! empty( $taxonomies ) ) : ?>
				<option value="taxonomy" <?php selected( $visibility_rule['based_on'], FieldsetVisibility::BASEDON_TAXONOMY ); ?> >
					<?php esc_html_e( 'Taxonomy', \JustCustomFields::TEXTDOMAIN ); ?>
				</option>
			<?php endif; ?>
		</select>
	</p>

	<div class="rules-options">

		<?php if ( FieldsetVisibility::BASEDON_TAXONOMY === $visibility_rule['based_on'] ) : // Taxonomy options for post type page based on taxonomy. ?>
			<?php
			$this->_render( 'fieldsets/visibility/terms_list', array(
				'taxonomies'   => $taxonomies,
				'current_tax'  => $visibility_rule['rule_taxonomy'],
				'terms'        => $terms,
				'current_term' => $visibility_rule['rule_taxonomy_terms'],
			) );
			?>
		<?php elseif ( FieldsetVisibility::BASEDON_PAGE_TPL === $visibility_rule['based_on'] ) : // Page template options. ?>
			<?php
			$this->_render( 'fieldsets/visibility/templates_list', array(
				'templates' => $templates,
				'current'   => $visibility_rule['rule_templates'],
			) );
			?>
		<?php endif; ?>
	</div>

	<?php // Rule ID. ?>
	<input type="hidden" name="rule_id" value="<?php echo esc_attr( $visibility_rule['rule_id'] ); ?>">

	<?php // Form buttons ?>
	<?php if ( ( ! empty( $scenario ) && FieldsetVisibility::SCENARIO_UPDATE === $scenario ) ) : ?>
		<input type="button" class="update_rule_btn button" data-rule_id="<?php echo $_POST['rule_id']; ?>"
			   name="update_rule" value="<?php esc_html_e( 'Update rule', \JustCustomFields::TEXTDOMAIN ); ?>"/>
	<?php else : ?>
		<input type="button" class="save_rule_btn button" name="save_rule"
			   value="<?php esc_html_e( 'Save rule', \JustCustomFields::TEXTDOMAIN ); ?>"/>
	<?php endif; ?>

	<?php if ( ! empty( $scenario ) ) : ?>
		<input type="button" class="cancel_rule_btn button" name="cancel_rule"
			   value="<?php esc_html_e( 'Cancel', \JustCustomFields::TEXTDOMAIN ); ?>"/>
	<?php endif; ?>

</fieldset>
