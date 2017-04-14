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
		if ( empty($this->instance['settings']) ) {
			echo '<p>' . __('Please check settings. Values are empty', \JustCustomFields::TEXTDOMAIN) . '</p>';
			return false;
		}

		$single_checkbox = (count($this->instance['settings']) == 1) ? true : false;
		?>
		<div id="jcf_field-<?php echo $this->id; ?>" class="jcf_edit_field <?php echo $this->fieldOptions['classname']; ?>">
			<?php echo $this->fieldOptions['before_widget']; ?>
				<?php echo $this->fieldOptions['before_title'] . esc_html($this->instance['title']) . $this->fieldOptions['after_title']; ?>
				
				<div class="checkboxes-set">
					<div class="checkbox-row">
						<?php foreach ( (array) $this->instance['settings'] as $val ) : ?>
							<?php
							if ( $single_checkbox ) {
								$checked = ($val['id'] == $this->entry) ? true : false;
							}
							else {
								$checked = in_array($val['id'], (array) $this->entry);
							}
							?>
							<label><input type="checkbox" name="<?php echo $this->getFieldName('val') . ($single_checkbox ? '' : '[]'); ?>" id="<?php echo $this->getFieldId('val'); ?>" value="<?php echo esc_attr($val['id']); ?>" <?php echo checked(true, $checked, false); ?>/> <?php echo $val['label']; ?></label>
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
		$instance = wp_parse_args((array) $this->instance, array( 'title' => '', 'settings' => array(), 'description' => '' ));

		$title = esc_attr($instance['title']);
		$settings = $instance['settings'];
		$description = esc_html($instance['description']);
		?>
		<p>
			<label for="<?php echo $this->getFieldId('title'); ?>"><?php _e('Title:', \JustCustomFields::TEXTDOMAIN); ?></label>
			<input class="widefat" id="<?php echo $this->getFieldId('title'); ?>" name="<?php echo $this->getFieldName('title'); ?>" type="text" value="<?php echo $title; ?>" />
		</p>
		<p>
			<label for="<?php echo $this->getFieldId('settings'); ?>"><?php _e('Settings:', \JustCustomFields::TEXTDOMAIN); ?></label>
		<div class="settings"></div>
		<?php
		$multi_field_config = array(
			array(
				'name' => 'label',
				'placeholder' => 'Label',
				'type' => 'text',
			),
			array(
				'name' => 'id',
				'placeholder' => 'ID',
				'type' => 'text',
			),
		);
		?>
		<script type="text/javascript">
			( function( $ ) {

				$(document).ready(function() {
					$('.settings').jcMultiField({
						addButton: { class: 'button' },
						removeButton: { class: 'dashicons dashicons-no-alt' },
						dragHandler: { class: 'dashicons dashicons-menu' },

						fieldId: '<?php echo $this->getFieldName('settings'); ?>',
						structure: <?php echo json_encode( $multi_field_config ) ?>,
						data: <?php echo json_encode( $settings ) ?>,
					});
				});

			}( jQuery ));
		</script>
		<style type="text/css">
			.jcmf-multi-field .handle { background-color: transparent !important; color: #aaa; }
			.jcmf-multi-field .handle.sortable { color: #333; }
			.jcmf-multi-field .button { margin-left: 23px; }
			.jtmce_help .dashicons{ text-decoration: none !important; }
			.jtmce_help_box { max-width: 800px; padding: 0 0 30px; }
			.jtmce_help_box.hidden { display: none;}
		</style>
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
		$instance['settings'] = $this->orderOptions($new_instance['settings']);
		$instance['description'] = strip_tags($new_instance['description']);
		return $instance;
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