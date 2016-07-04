<?php if ( !empty($templates) ): ?>
	<div class="templates-options">
		<p><?php _e('Choose templates:', \JustCustomFields::TEXTDOMAIN); ?></p>
		<ul class="visibility-list-items visibility-list-items-tpl">
			<?php $i = 1;
			foreach ( $templates as $path => $name ): ?>
				<li>
					<input type="checkbox" name="rule_templates" value="<?php echo $path; ?>"
							 id="rule_template_<?php echo $i; ?>"
							<?php checked(in_array($path, $current), true); ?>/>
					<label for="rule_template_<?php echo $i++; ?>"><?php echo $name; ?></label>
				</li>
			<?php endforeach; ?>
		</ul>
		<br class="clear">
	</div>
<?php else: ?>
	<p><?php _e('No available templates', \JustCustomFields::TEXTDOMAIN); ?></p>
<?php endif; ?>
