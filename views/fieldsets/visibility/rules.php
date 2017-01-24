<?php
/* @var $visibility_rules array */

$templates = jcf_get_page_templates($post_type);
$rule_text_pattern = '{show_hide} when <strong>{based_on}</strong> in <strong>{terms}</strong>';
$row_index = 1;
?>
<div class="rules">
	<?php
	if ( !empty($visibility_rules) && is_array($visibility_rules) ):
		?>
		<table class="wp-list-table widefat fixed fieldset-visibility-rules">
			<thead>
				<tr>
					<th style="width: 10%;">#</th>
					<th><?php _e('Rule', \JustCustomFields::TEXTDOMAIN); ?></th>
					<th style="width: 20%;"><?php _e('Options', \JustCustomFields::TEXTDOMAIN); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $visibility_rules as $rule_key => $rule ) :

					if ( $rule['based_on'] == 'taxonomy' ) {
						$tax = get_taxonomy($rule['rule_taxonomy']);
						$based_on = $tax->labels->singular_name;

						$terms_list = array();
						if ( !empty($rule['rule_taxonomy_terms']) ) {
							foreach ( $rule['rule_taxonomy_terms'] as $term_id ) {
								$term_obj = get_term_by('id', $term_id, $rule['rule_taxonomy']);
								$terms_list[] = $term_obj->name;
							}
						}
						$terms = implode(', ', $terms_list);
					}
					else {
						$based_on = 'Page Template';

						$tpl_selected = array();
						foreach ( $rule['rule_templates'] as $tpl ) {
							if ( !isset($templates[$tpl]) ) continue;

							$tpl_selected[] = $templates[$tpl];
						}
						$terms = implode(', ', $tpl_selected);
					}

					// generate name based on patter
					$rule_text = strtr($rule_text_pattern, array(
						'{show_hide}' => ucfirst($rule['visibility_option']),
						'{based_on}' => $based_on,
						'{terms}' => $terms,
					));
					?>
					<tr class="visibility_rule_<?php echo $rule['rule_id']; ?>">
						<td><?php echo $row_index++; ?></td>
						<td>
							<?php if ( $row_index > 2 ) : ?>
								<strong><?php echo strtoupper($rule['join_condition']); ?></strong><br/>
							<?php endif; ?>
							<?php echo $rule_text; ?>
						</td>
						<td>
							<a href="#" class="dashicons-before dashicons-edit edit-rule" data-rule_id="<?php echo $rule['rule_id']; ?>"></a>
							<a href="#" class="dashicons-before dashicons-no remove-rule" data-rule_id="<?php echo $rule['rule_id']; ?>"></a><?php ?>
						</td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	<?php endif; ?>
	<p><input type="button" class="add_rule_btn button" name="add_rule" value="<?php _e('Add rule', \JustCustomFields::TEXTDOMAIN); ?>"/></p>
</div>

