<?php

namespace jcf\components\selectmultiple;

use jcf\core;

/**
 * Class for select multiple list type
 *
 * @package default
 * @author Alexander Prokopenko
 */
class JustField_SelectMultiple extends core\JustField
{

	public function __construct()
	{
		$field_ops = array( 'classname' => 'field_selectmultiple' );
		parent::__construct('selectmultiple', __('Select Multiple', \JustCustomFields::TEXTDOMAIN), $field_ops);
	}

	/**
	 * 	draw field on post edit form
	 * 	you can use $this->instance, $this->entry
	 */
	public function field()
	{
		if ( !is_array($this->entry) )
			$this->entry = array();
		// prepare options array
		?>
		<div id="jcf_field-<?php echo $this->id; ?>" class="jcf_edit_field <?php echo $this->fieldOptions['classname']; ?>">
			<?php echo $this->fieldOptions['before_widget']; ?>
				<?php echo $this->fieldOptions['before_title'] . esc_html($this->instance['title']) . $this->fieldOptions['after_title']; ?>
				<div class="select_multiple_field">
					<select name="<?php echo $this->getFieldName('val'); ?>[]" id="<?php echo $this->getFieldId('val'); ?>" class="jcf-multiple" multiple="multiple">
						<?php foreach ( (array) $this->instance['options'] as $val ) : ?>
						<option value="<?php echo esc_attr($val['id']); ?>" <?php echo selected(true, in_array($val['id'], $this->entry), false); ?>><?php echo esc_attr($val['label']); ?></option>
					<?php endforeach; ?>
					</select>
				</div>
				<?php if ( $this->instance['description'] != '' ) : ?>
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
		$instance = wp_parse_args((array) $this->instance, array( 'title' => '', 'description' => '', 'settings' => array() ));

		$title = esc_attr($instance['title']);
		$options = $instance['options'];
		$description = esc_html($instance['description']);
		?>
		<p><label for="<?php echo $this->getFieldId('title'); ?>"><?php _e('Title:', \JustCustomFields::TEXTDOMAIN); ?></label> <input class="widefat" id="<?php echo $this->getFieldId('title'); ?>" name="<?php echo $this->getFieldName('title'); ?>" type="text" value="<?php echo $title; ?>" /></p>

		<p><label for="<?php echo $this->getFieldId('options'); ?>"><?php _e('Settings:', \JustCustomFields::TEXTDOMAIN); ?></label>
		<div class="options"></div>
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
					$('.options').jcMultiField({
						addButton: { class: 'button' },
						removeButton: { class: 'dashicons dashicons-no-alt' },
						dragHandler: { class: 'dashicons dashicons-menu' },

						fieldId: '<?php echo $this->getFieldName('options'); ?>',
						structure: <?php echo json_encode( $multi_field_config ) ?>,
						data: <?php echo json_encode( $options ) ?>,
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
		$instance['options'] = $this->orderOptions($new_instance['options']);
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