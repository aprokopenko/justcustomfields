<?php

namespace jcf\components\checkbox;

use jcf\core;

/**
 * Class for group of checkboxes
 *
 * @package default
 * @author Alexander Prokopenko
 */
class JustField_Checkbox extends core\JustField
{

	public function __construct()
	{
		$field_ops = array( 'classname' => 'field_checkbox' );
		parent::__construct('checkbox', __('Checkbox', \JustCustomFields::TEXTDOMAIN), $field_ops);
	}

	/**
	 * 	draw field on post edit form
	 * 	you can use $this->instance, $this->entry
	 */
	public function field()
	{
		// prepare options array
		$values = $this->parsedSelectOptions($this->instance);

		if ( empty($values) ) {
			echo '<p>' . __('Please check settings. Values are empty', \JustCustomFields::TEXTDOMAIN) . '</p>';
			return false;
		}

		$single_checkbox = (count($values) == 1) ? true : false;
		?>
		<div id="jcf_field-<?php echo $this->id; ?>" class="jcf_edit_field <?php echo $this->fieldOptions['classname']; ?>">
			<?php echo $this->fieldOptions['before_widget']; ?>
				<?php echo $this->fieldOptions['before_title'] . esc_html($this->instance['title']) . $this->fieldOptions['after_title']; ?>
				
				<div class="checkboxes-set">
					<div class="checkbox-row">
						<?php foreach ( (array) $values as $key => $val ) : ?>
							<?php
							if ( $single_checkbox ) {
								$checked = ($val == $this->entry) ? true : false;
							}
							else {
								$checked = in_array($val, (array) $this->entry);
							}
							?>
							<label><input type="checkbox" name="<?php echo $this->getFieldName('val') . ($single_checkbox ? '' : '[]'); ?>" id="<?php echo $this->getFieldId('val'); ?>" value="<?php echo esc_attr($val); ?>" <?php echo checked(true, $checked, false); ?>/> <?php echo $key; ?></label>
						<?php endforeach; ?>
					</div>
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
		// Defaults
		$instance = wp_parse_args((array) $this->instance, array( 'title' => '', 'settings' => '', 'description' => '' ));

		$title = esc_attr($instance['title']);
		$settings = esc_attr($instance['settings']);
		$description = esc_html($instance['description']);
		?>
		<p>
			<label for="<?php echo $this->getFieldId('title'); ?>"><?php _e('Title:', \JustCustomFields::TEXTDOMAIN); ?></label>
			<input class="widefat" id="<?php echo $this->getFieldId('title'); ?>" name="<?php echo $this->getFieldName('title'); ?>" type="text" value="<?php echo $title; ?>" />
		</p>
		<p>
			<label for="<?php echo $this->getFieldId('settings'); ?>"><?php _e('Settings:', \JustCustomFields::TEXTDOMAIN); ?></label> 
			<textarea class="widefat" id="<?php echo $this->getFieldId('settings'); ?>" name="<?php echo $this->getFieldName('settings'); ?>" ><?php echo $settings; ?></textarea>
			<br/><small><?php _e('Parameters like (you can use just "label" if "id" is the same):<br>label1|id1<br>label2|id2<br>label3', \JustCustomFields::TEXTDOMAIN); ?></small>
		</p>
		<p>
			<label for="<?php echo $this->getFieldId('description'); ?>"><?php _e('Description:', \JustCustomFields::TEXTDOMAIN); ?></label>
			<textarea name="<?php echo $this->getFieldName('description'); ?>" id="<?php echo $this->getFieldId('description'); ?>" cols="20" rows="4" class="widefat"><?php echo $description; ?></textarea>
		</p>
		<?php
	}

	/**
	 * 	save field on post edit form
	 */
	public function save( $values )
	{
		$values = isset($values['val']) ? $values['val'] : '';
		return $values;
	}

	/**
	 * 	update instance (settings) for current field
	 */
	public function update( $new_instance, $old_instance )
	{
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['settings'] = strip_tags($new_instance['settings']);
		$instance['description'] = strip_tags($new_instance['description']);
		return $instance;
	}

	/**
	 * prepare list of options
	 * 
	 * @param array $instance	current instance
	 * @return array
	 */
	protected function parsedSelectOptions( $instance )
	{
		$values = array();
		$v = explode("\n", $instance['settings']);

		foreach ( $v as $val ) {
			$val = trim($val);

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
	 * 	print fields values from shortcode
	 */
	public function shortcodeValue( $args )
	{
		$options = $this->parsedSelectOptions($this->instance);
		$options = array_flip($options);

		if ( empty($this->entry) )
			return '';

		$html = '<ul class="jcf-list">';
		foreach ( $this->entry as $value ) {
			$key = preg_replace('/\s+/', '-', $value);
			$key = preg_replace('/[^0-9a-z\-\_]/i', '', $key);
			if ( isset($options[$value]) ) {
				$value = $options[$value];
			}
			$key = esc_attr($key);
			$value = esc_html($value);
			$html .= "<li class=\"jcf-item jcf-item-$key\">$value</li>\r\n";
		}
		$html .= '</ul>';

		return $args['before_value'] . $html . $args['after_value'];
	}

}
?>