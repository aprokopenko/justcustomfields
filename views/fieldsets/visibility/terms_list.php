<?php $taxonomy = get_taxonomy($terms[0]->taxonomy); ?>
<?php if ( !empty($terms) ): ?>
	<p><?php _e('Choose ' . $taxonomy->labels->name . ':', \JustCustomFields::TEXTDOMAIN); ?></p>
	<?php if ( count($terms) <= 20 ) : ?>
		<ul class="visibility-list-items">
			<?php $i = 1;
			foreach ( $terms as $term ): ?>
				<li>
					<input type="checkbox" name="rule_taxonomy_terms" value="<?php echo $term->term_id; ?>"
			<?php checked(in_array($term->term_id, $current_term), true); ?>
						   id="rule_taxonomy_term_<?php echo $term->term_id; ?>" />
					<label for="rule_taxonomy_term_<?php echo $term->term_id; ?>"><?php echo $term->name; ?></label>
				</li>
			<?php $i++;
		endforeach; ?>
		</ul>
	<?php else: ?>
		<div class="visibility-terms-ai-wrapper">
			<input type="text" id="new-term" name="newterm" class="newterm form-input-tip" size="16" autocomplete="on" value="">
			<input type="button" class="button termadd" value="Add">
		</div>
		<ul class="visibility-list-items">
			<?php if ( !empty($current_term) ) : ?>
			<?php $i = 1;
			foreach ( $terms as $term ): ?>
						<?php if ( in_array($term->term_id, $current_term) ) : ?>
						<li>
							<input type="checkbox" name="rule_taxonomy_terms" value="<?php echo $term->term_id; ?>"
						<?php checked(true); ?>
								   id="rule_taxonomy_term_<?php echo $term->term_id; ?>" />
							<label for="rule_taxonomy_term_<?php echo $term->term_id; ?>"><?php echo $term->name; ?></label>
						</li>
				<?php endif; ?>
				<?php $i++;
			endforeach; ?>
		<?php endif; ?>
		</ul>
	<?php endif; ?>
	<br class="clear">
<?php else: ?>
	<p><?php _e('No available terms', \JustCustomFields::TEXTDOMAIN); ?></p>
<?php endif; ?>