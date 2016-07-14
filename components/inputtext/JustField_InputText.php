<?php

namespace jcf\components\inputtext;

use jcf\core;

class JustField_InputText extends core\JustField
{

	public function __construct()
	{
		$field_ops = array( 'classname' => 'field_inputtext' );
		parent::__construct('inputtext', __('Input Text', \JustCustomFields::TEXTDOMAIN), $field_ops);
	}

	/**
	 * 	draw field on post edit form
	 * 	you can use $this->instance, $this->entry
	 */
	public function field()
	{
		?>
		<div id="jcf_field-<?php echo $this->id; ?>" class="jcf_edit_field <?php echo $this->fieldOptions['classname']; ?>">
			<?php echo $this->fieldOptions['before_widget']; ?>
				<?php echo $this->fieldOptions['before_title'] . esc_html($this->instance['title']) . $this->fieldOptions['after_title']; ?>
				
				<br />
				<input type="text"
					   name="<?php echo $this->getFieldName('val'); ?>"
					   id="<?php echo $this->getFieldId('val'); ?>"
					   value="<?php echo esc_attr($this->entry); ?>"/>
				<?php if ( $this->instance['description'] != '' ) : ?>
					<p class="howto"><?php echo esc_html($this->instance['description']); ?></p>
				<?php endif; ?>

			<?php echo $this->fieldOptions['after_widget']; ?>
		</div>
		<?php
	}

	/**
	 * draw form for 
	 */
	public function form()
	{
		$instance = wp_parse_args((array) $this->instance, array( 'title' => '', 'description' => '' ));
		$description = esc_html($instance['description']);
		$title = esc_attr($instance['title']);
		?>
		<p><label for="<?php echo $this->getFieldId('title'); ?>"><?php _e('Title:', \JustCustomFields::TEXTDOMAIN); ?></label> <input class="widefat" id="<?php echo $this->getFieldId('title'); ?>" name="<?php echo $this->getFieldName('title'); ?>" type="text" value="<?php echo $title; ?>" /></p>
		<p><label for="<?php echo $this->getFieldId('description'); ?>"><?php _e('Description:', \JustCustomFields::TEXTDOMAIN); ?></label> <textarea name="<?php echo $this->getFieldName('description'); ?>" id="<?php echo $this->getFieldId('description'); ?>" cols="20" rows="4" class="widefat"><?php echo $description; ?></textarea></p>
		<?php
	}

	/**
	 * 	save field on post edit form
	 */
	public function save( $values )
	{
		$values = $values['val'];
		return $values;
	}

	/**
	 * 	update instance (settings) for current field
	 */
	public function update( $new_instance, $old_instance )
	{
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['description'] = strip_tags($new_instance['description']);
		return $instance;
	}

}
