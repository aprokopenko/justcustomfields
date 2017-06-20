<?php

/**
 * Visibility tax list
 *
 * @var array $visibility_rule Visibility Rule
 */
if ( ! empty( $taxonomies ) ) : ?>
	<div class="taxonomy-options">
		<p>
			<label for="rule-taxonomy"><?php esc_html_e( 'Choose taxonomy:', \JustCustomFields::TEXTDOMAIN ); ?></label>
			<br class="clear"/>
			<select name="rule_taxonomy" id="rule-taxonomy">
				<option value=""
						disabled="disabled" <?php selected( empty( $current_tax ) ); ?> ><?php esc_html_e( 'Choose taxonomy', \JustCustomFields::TEXTDOMAIN ); ?></option>
				<?php foreach ( $taxonomies as $slug => $taxonomy ) : ?>
					<?php if ( 'post_format' !== $slug ) : ?>
						<option value="<?php echo esc_attr( $slug ); ?>" <?php selected( $current_tax, $slug ); ?> ><?php echo esc_html( $taxonomy->labels->singular_name ); ?></option>
					<?php endif; ?>
				<?php endforeach; ?>
			</select>
		</p>
		<div class="taxonomy-terms-options">
			<?php if ( ! empty( $terms ) ) : ?>
				<?php
				$this->_render( 'fieldsets/visibility/terms_list', array(
					'terms'        => $terms,
					'current_term' => $current_term,
				) );
				?>
			<?php endif; ?>
		</div>
	</div>
<?php else : ?>
	<p><?php esc_html_e( 'No available taxonomies', \JustCustomFields::TEXTDOMAIN ); ?></p>
<?php endif; ?>
