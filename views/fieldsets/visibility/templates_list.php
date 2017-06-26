<?php

/**
 * Visibility tax list
 *
 * @var array $visibility_rule Visibility Rule
 */

if ( ! empty( $templates ) ) : ?>
	<div class="templates-options">
		<p><?php esc_html_e( 'Choose templates:', 'jcf' ); ?></p>
		<ul class="visibility-list-items visibility-list-items-tpl">
			<?php $i = 1;
			foreach ( $templates as $path => $name ) : ?>
				<li>
					<input type="checkbox" name="rule_templates" value="<?php echo esc_attr( $path ); ?>"
						   id="rule_template_<?php echo $i; ?>"
						<?php checked( in_array( $path, $current ), true ); ?>/>
					<label for="rule_template_<?php echo $i ++; ?>"><?php echo esc_html( $name ); ?></label>
				</li>
			<?php endforeach; ?>
		</ul>
		<br class="clear">
	</div>
<?php else : ?>
	<p><?php esc_html_e( 'No available templates', 'jcf' ); ?></p>
<?php endif; ?>
