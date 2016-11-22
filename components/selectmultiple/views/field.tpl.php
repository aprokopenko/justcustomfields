<div id="jcf_field-<?php echo $this->id; ?>" class="jcf_edit_field <?php echo $this->fieldOptions['classname']; ?>">
	<div class="form-field">
		<label><?php echo $this->instance['title']; ?>:</label>
		<div class="jcf-get-shortcode" rel="<?php echo $this->slug; ?>">
			<span class="dashicons dashicons-editor-help wp-ui-text-highlight"></span>
		</div>
		<div class="select_multiple_field">
			<select name="<?php echo $this->getFieldName('val'); ?>[]" id="<?php echo $this->getFieldId('val'); ?>" multiple="multiple" style="height:200px; width:47%;">
				<?php foreach ( $values as $key => $val ): ?>
					<option value="<?php echo esc_attr($val); ?>" <?php echo selected(true, in_array($val, $this->entry), false); ?>><?php echo esc_html(ucfirst($key)); ?></option>
				<?php endforeach; ?>
			</select>
		</div>
		<?php if ( $this->instance['description'] != '' ) : ?>
			<p class="description"><?php echo $this->instance['description']; ?></p>
		<?php endif; ?>
	</div>
</div>
