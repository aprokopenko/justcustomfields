<?php

/**
 * Visibility term list
 *
 */
$taxonomy = get_taxonomy( $terms[0]->taxonomy );

?>
<?php if ( ! empty( $terms ) ) : ?>
	<p><?php esc_html_e( 'Choose ' . $taxonomy->labels->name . ':', 'jcf' ); ?></p>
	<?php if ( count( $terms ) <= 20 ) : ?>
		<ul class="visibility-list-items">
			<?php $i = 1;
			foreach ( $terms as $term ) : ?>
				<li>
					<input type="checkbox" name="rule_taxonomy_terms" value="<?php echo esc_attr( $term->term_id ); ?>"
						<?php checked( in_array( $term->term_id, $current_term ), true ); ?>
						   id="rule_taxonomy_term_<?php echo esc_attr( $term->term_id ); ?>"/>
					<label for="rule_taxonomy_term_<?php echo esc_attr( $term->term_id ); ?>"><?php echo esc_html( $term->name ); ?></label>
				</li>
				<?php $i ++;
			endforeach; ?>
		</ul>
	<?php else : ?>
		<div class="visibility-terms-ai-wrapper">
			<input type="text" id="new-term" name="newterm" class="newterm form-input-tip" size="16" autocomplete="on"
				   value="">
			<input type="button" class="button termadd" value="Add">
		</div>
		<ul class="visibility-list-items">
			<?php if ( ! empty( $current_term ) ) : ?>
				<?php $i = 1;
				foreach ( $terms as $term ) : ?>
					<?php if ( in_array( $term->term_id, $current_term ) ) : ?>
						<li>
							<input type="checkbox" name="rule_taxonomy_terms"
								   value="<?php echo esc_attr( $term->term_id ); ?>"
								<?php checked( true ); ?>
								   id="rule_taxonomy_term_<?php echo esc_attr( $term->term_id ); ?>"/>
							<label for="rule_taxonomy_term_<?php echo esc_attr( $term->term_id ); ?>"><?php echo esc_attr( $term->name ); ?></label>
						</li>
					<?php endif; ?>
					<?php $i ++;
				endforeach; ?>
			<?php endif; ?>
		</ul>
	<?php endif; ?>
	<br class="clear">
<?php else : ?>
	<p><?php esc_html_e( 'No available terms', 'jcf' ); ?></p>
<?php endif; ?>
