<div class="rules">
	<?php if ( !empty($visibility_rules) && is_array($visibility_rules) ): ?>
		<table class="wp-list-table widefat fixed fieldset-visibility-rules">
			<thead>
				<tr>
					<th style="width: 10%;">№</th>
					<th><?php _e('Rule', \JustCustomFields::TEXTDOMAIN); ?></th>
					<th style="width: 20%;"><?php _e('Options', \JustCustomFields::TEXTDOMAIN); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $visibility_rules as $key => $rule ) : ?>
					<?php
					$rule_text = '';
					$rule_text .= ucfirst($rule['visibility_option']);
					$rule_text .= ' when ';

					if ( $rule['based_on'] == 'taxonomy' ) {
						$term_text = '';

						if ( !empty($rule['rule_taxonomy_terms']) ) {
							foreach ( $rule['rule_taxonomy_terms'] as $key_term => $term ) {
								$term_obj = get_term_by('id', $term, $rule['rule_taxonomy']);
								$term_text .= ($key_term != 0 ? ', ' . $term_obj->name : $term_obj->name);
							}
						}

						$tax = get_taxonomy($rule['rule_taxonomy']);
						$rule_text .= '<strong>' . $tax->labels->singular_name . '</strong>';
						$rule_text .= ' in ';
						$rule_text .= '<strong>' . $term_text . '</strong>';
					}
					else {
						$templates = get_page_templates();
						$tpl_text = '';

						foreach ( $rule['rule_templates'] as $key_tpl => $template ) {
							$tpl_name = array_search($template, $templates);
							$tpl_text .= ($key_tpl != 0 ? ', ' . $tpl_name : $tpl_name);
						}

						$rule_text .= '<strong>' . ucfirst(str_replace('_', ' ', $rule['based_on'])) . '</strong>';
						$rule_text .= ' in ';
						$rule_text .= '<strong>' . $tpl_text . '<strong>';
					}
					?>

					<tr class="visibility_rule_<?php echo $key + 1; ?>">
						<td><?php echo ($key + 1); ?></td>
						<td>
							<?php if ( $key != 0 ) : ?>
								<strong><?php echo strtoupper($rule['join_condition']); ?></strong><br/>
							<?php endif; ?>
							<?php echo $rule_text; ?>
						</td>
						<td>
							<a href="#" class="dashicons-before dashicons-edit edit-rule" data-rule_id="<?php echo $key + 1; ?>"></a>
							<a href="#" class="dashicons-before dashicons-no remove-rule" data-rule_id="<?php echo $key + 1; ?>"></a><?php ?>
						</td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	<?php endif; ?>
	<p><input type="button" class="add_rule_btn button" name="add_rule" value="<?php _e('Add rule', \JustCustomFields::TEXTDOMAIN); ?>"/></p>
</div>

