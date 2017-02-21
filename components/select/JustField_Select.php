<?php

namespace jcf\components\select;

use jcf\core;

/**
 * Class for select list type
 *
 * @package default
 * @author Alexander Prokopenko
 */
class JustField_Select extends core\JustField
{

	public function __construct()
	{
		$field_ops = array( 'classname' => 'field_select' );
		parent::__construct('select', __('Select', \JustCustomFields::TEXTDOMAIN), $field_ops);
	}

	/**
	 * 	draw field on post edit form
	 * 	you can use $this->instance, $this->entry
	 */
	public function field()
	{
		$values = $this->parsedSelectOptions($this->instance);
		?>
		<div id="jcf_field-<?php echo $this->id; ?>" class="jcf_edit_field <?php echo $this->fieldOptions['classname']; ?>">
			<?php echo $this->fieldOptions['before_widget']; ?>
				<?php echo $this->fieldOptions['before_title'] . esc_html($this->instance['title']) . $this->fieldOptions['after_title']; ?>
				<div class="select-field">
					<select name="<?php echo $this->getFieldName('val'); ?>" id="<?php echo $this->getFieldId('val'); ?>">
						<?php if (!empty($this->instance['empty_option'])) : ?>
							<option value="" <?php echo selected($this->instance['empty_option'], $this->entry, false); ?>><?php echo esc_attr($this->instance['empty_option']); ?></option>
						<?php endif; ?>
						<?php foreach ( (array) $values as $key => $val ) : ?>
							<option value="<?php echo esc_attr($val); ?>" <?php echo selected($val, $this->entry, false); ?>><?php echo esc_html(ucfirst($key)); ?></option>
						<?php endforeach; ?>
					</select>
				</div>
			<?php if ( !empty($this->instance['description']) ) : ?>
				<p class="howto"><?php echo esc_html($this->instance['description']); ?></p>
			<?php endif; ?>
			<?php echo $this->fieldOptions['after_widget']; ?>
		</div>
		<?php
	}

	/**
	 * draw form for edit field
	 */
	public function form()
	{
		//Defaults
		$instance = wp_parse_args((array) $this->instance, array( 'title' => '', 'description' => '', 'options' => '', 'empty_option' => '' ));
		$title = esc_attr($instance['title']);
		$options = esc_attr($instance['options']);
		$description = esc_html($instance['description']);
		$empty_option = esc_attr($instance['empty_option']);
		?>
		<p><label for="<?php echo $this->getFieldId('title'); ?>"><?php _e('Title:', \JustCustomFields::TEXTDOMAIN); ?></label> <input class="widefat" id="<?php echo $this->getFieldId('title'); ?>" name="<?php echo $this->getFieldName('title'); ?>" type="text" value="<?php echo $title; ?>" /></p>
		<p><label for="<?php echo $this->getFieldId('options'); ?>"><?php _e('Options:', \JustCustomFields::TEXTDOMAIN); ?></label> 
			<textarea class="widefat" id="<?php echo $this->getFieldId('options'); ?>" name="<?php echo $this->getFieldName('options'); ?>" ><?php echo $options; ?></textarea>
			<br/><small><?php _e('Parameters like (you can use just "label" if "id" is the same):<br>label1|id1<br>label2|id2<br>label3', \JustCustomFields::TEXTDOMAIN); ?></small></p>
		<p><label for="<?php echo $this->getFieldId('empty_option'); ?>"><?php _e('Empty option:', \JustCustomFields::TEXTDOMAIN); ?></label><input class="widefat" id="<?php echo $this->getFieldId('empty_option'); ?>" name="<?php echo $this->getFieldName('empty_option'); ?>" placeholder="ex. Choose item from the list"" type="text" value="<?php echo $empty_option; ?>" />
			<br/><small><?php _e('Leave blank to disable empty option', \JustCustomFields::TEXTDOMAIN); ?></small></p>
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
		$instance['options'] = strip_tags($new_instance['options']);
		$instance['description'] = strip_tags($new_instance['description']);
		$instance['empty_option'] = strip_tags($new_instance['empty_option']);
		return $instance;
	}

	/**
	 * prepare list of options
	 * 
	 * @param array $instance	current instance
	 * @return array
	 */
	public function parsedSelectOptions( $instance )
	{
		$values = array();
		$settings = $instance['options'];

		$v = explode("\n", $settings);
		foreach ( $v as $val ) {
			$val = trim($val);
			if ( empty($val) ) continue;
			if ( strpos($val, '|') !== FALSE ) {
				$a = explode('|', $val);
				$values[$a[0]] = $a[1];
			}
			elseif ( !empty($val) ) {
				$values[$val] = $val;
			}
		}

		return $values;
	}

	/**
	 * print field values inside the shortcode
	 * 
	 * @params array $args	shortcode args
	 */
	public function shortcodeValue( $args )
	{
		$options = $this->parsedSelectOptions($this->instance);
		$options = array_flip($options);
		$value = $this->entry;

		if ( isset($options[$this->entry]) ) {
			$value = $options[$this->entry];
		}
		return $args['before_value'] . esc_html($value) . $args['after_value'];
	}

}
