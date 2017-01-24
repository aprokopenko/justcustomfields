<?php
/* @var $visibility_rule array */

use jcf\models\FieldsetVisibility;

if ( empty($visibility_rule) ) {
	$visibility_rule = array(
		'rule_id' => '',
		'visibility_option' => FieldsetVisibility::VISIBILITY_HIDE,
		'join_condition' => FieldsetVisibility::JOIN_AND,
		'based_on' => '',

	);
}

?>
<fieldset id="fieldset_visibility_rules">
	<legend>
		<?php
			if (!empty($scenario) && $scenario == FieldsetVisibility::SCENARIO_UPDATE) {
				_e('Edit rule', \JustCustomFields::TEXTDOMAIN);
			}
			else {
				_e('Add rule', \JustCustomFields::TEXTDOMAIN);
			}
		?>
	</legend>

	<?php // Status for fieldset ?>
	<div class="visibility-options">
		<p><?php _e('You are about to set the visibility option for this fieldset', \JustCustomFields::TEXTDOMAIN); ?></p>
		<input type="radio" name="visibility_option" id="visibility-option-hide" value="<?php echo FieldsetVisibility::VISIBILITY_HIDE; ?>"
			<?php echo ( (!empty($scenario) && $scenario == FieldsetVisibility::SCENARIO_UPDATE) ? checked( $visibility_rule['visibility_option'], 'hide' ) : 'checked' );  ?> />
		<label for="visibility-option-hide"><?php _e('Hide fieldset', \JustCustomFields::TEXTDOMAIN); ?></label>
		<br class="clear"/>
		<input type="radio" name="visibility_option" id="visibility-option-show" value="<?php echo FieldsetVisibility::VISIBILITY_SHOW; ?>"
			<?php checked( $visibility_rule['visibility_option'], 'show' ); ?> />
		<label for="visibility-option-show"><?php _e('Show fieldset', \JustCustomFields::TEXTDOMAIN); ?></label>
	</div>

	<?php // Condition fields for rule ?>
	<div class="join-condition <?php echo ( ((!empty($scenario) && $scenario == FieldsetVisibility::SCENARIO_CREATE) || $rule_id != 0) ? '' : 'hidden' ); ?>" >
		<p>
			<label for="rule-join-condition"><?php _e('Join condition with previous rules with operator:', \JustCustomFields::TEXTDOMAIN); ?></label>
			<br />
			<select name="join_condition" id="rule-join-condition">
				<option value="and" <?php selected($visibility_rule['join_condition'], FieldsetVisibility::JOIN_AND); ?> ><?php _e('AND', \JustCustomFields::TEXTDOMAIN); ?></option>
				<option value="or" <?php selected($visibility_rule['join_condition'], FieldsetVisibility::JOIN_OR); ?> ><?php _e('OR', \JustCustomFields::TEXTDOMAIN); ?></option>
			</select>
		</p>
	</div>

	<p>
		<label for="rule-based-on"><?php _e('Based on:', \JustCustomFields::TEXTDOMAIN); ?></label><br />
		<select name="based_on" id="rule-based-on">
			<option value="" disabled="disabled" <?php echo (!empty($scenario) && $scenario == FieldsetVisibility::SCENARIO_UPDATE) ? '' : 'selected'; ?> >
				<?php _e('Choose option', \JustCustomFields::TEXTDOMAIN); ?>
			</option>

			<?php if ( !empty($templates) ): ?>
				<option value="page_template" <?php selected( $visibility_rule['based_on'], FieldsetVisibility::BASEDON_PAGE_TPL ); ?> >
					<?php _e('Page template', \JustCustomFields::TEXTDOMAIN); ?>
				</option>
			<?php endif; ?>

			<?php if ( !empty($taxonomies) ):?>
				<option value="taxonomy" <?php selected( $visibility_rule['based_on'], FieldsetVisibility::BASEDON_TAXONOMY ); ?> >
					<?php _e('Taxonomy', \JustCustomFields::TEXTDOMAIN); ?>
				</option>
			<?php endif; ?>
		</select>
	</p>

	<div class="rules-options">

		<?php if ( $visibility_rule['based_on'] == FieldsetVisibility::BASEDON_TAXONOMY ) : //Taxonomy options for post type page based on taxonomy ?>
			<?php
				$this->_render('fieldsets/visibility/terms_list', array(
					'taxonomies' => $taxonomies,
					'current_tax' => $visibility_rule['rule_taxonomy'],
					'terms' => $terms,
					'current_term' => $visibility_rule['rule_taxonomy_terms']
				));
			?>
		<?php elseif ( $visibility_rule['based_on'] == FieldsetVisibility::BASEDON_PAGE_TPL ) : //Page template options ?>
			<?php
				$this->_render('fieldsets/visibility/templates_list', array(
					'templates' => $templates,
					'current' => $visibility_rule['rule_templates']
				));
			?>
		<?php endif;?>
	</div>

	<?php // Rule ID ?>
	<input type="hidden" name="rule_id" value="<?php echo $visibility_rule['rule_id']; ?>">

	<?php // Form buttons ?>
	<?php if( (!empty($scenario) && $scenario == FieldsetVisibility::SCENARIO_UPDATE) ): ?>
		<input type="button" class="update_rule_btn button" data-rule_id="<?php echo $_POST['rule_id'];?>" 
			   name="update_rule" value="<?php _e('Update rule', \JustCustomFields::TEXTDOMAIN); ?>"/>
	<?php else: ?>
		<input type="button" class="save_rule_btn button" name="save_rule" value="<?php _e('Save rule', \JustCustomFields::TEXTDOMAIN); ?>"/>
	<?php endif;?>

	<?php if ( !empty($scenario) ): ?>
		<input type="button" class="cancel_rule_btn button" name="cancel_rule" value="<?php _e('Cancel', \JustCustomFields::TEXTDOMAIN); ?>" />
	<?php endif;?>

</fieldset>
