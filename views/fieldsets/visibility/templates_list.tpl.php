<?php if ( !empty($templates) ): ?>
	<div class="templates-options">
		<p>
		<p><?php _e('Choose templates:', \JustCustomFields::TEXTDOMAIN); ?></p>
		<ul class="visibility-list-items">
			<?php $i = 1;
			foreach ( $templates as $name => $slug ): ?>
				<li>
					<input type="checkbox" name="rule_templates" value="<?php echo $slug; ?>" id="rule_template_<?php echo $i; ?>"
		<?php checked(in_array($slug, $current), true); ?>/>
					<label for="rule_template_<?php echo $i; ?>"><?php echo $name; ?></label>
				</li>
		<?php $i++;
	endforeach; ?>
		</ul>
		<br class="clear">
		</p>
	</div>
<?php else: ?>
	<p><?php _e('No available templates', \JustCustomFields::TEXTDOMAIN); ?></p>
<?php endif; ?>
