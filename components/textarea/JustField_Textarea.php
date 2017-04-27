<?php

namespace jcf\components\textarea;

use jcf\core;

/**
 * Textarea field type
 *
 * @package default
 * @author Alexander Prokopenko
 */
class JustField_Textarea extends core\JustField
{

	public function __construct()
	{
		$field_ops = array( 'classname' => 'field_textarea' );
		parent::__construct('textarea', __('Textarea', \JustCustomFields::TEXTDOMAIN), $field_ops);
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
				<?php
				if ( !empty($this->instance['editor']) ) : // check editor

					ob_start();
					/**
					 * @todo have bug with switching editor/text after ajax field loading, now disabled this functionality
					 * @author Kirill Samojlenko
					 */
					wp_editor($this->entry, $this->getFieldId('val'), array(
						'textarea_name' => $this->getFieldName('val'),
						'textarea_rows' => 5,
						'media_buttons' => true,
						'wpautop' => true,
						'quicktags' => false,
						'tinymce' => array(
							'theme_advanced_buttons1' => 'bold,italic,strikethrough,|,bullist,numlist,blockquote,|,justifyleft,justifycenter,justifyright,|,link,unlink,|,spellchecker,fullscreen,wp_adv',
						),
					));
					echo ob_get_clean();

					if ( defined('DOING_AJAX') && DOING_AJAX ) :
						?>
						<script type="text/javascript">
							jQuery(document).ready(function() {
								tinymce.execCommand('mceRemoveEditor', false, '<?php echo $this->getFieldId('val'); ?>');
								tinymce.execCommand('mceAddEditor', false, '<?php echo $this->getFieldId('val'); ?>');
							})
						</script>
					<?php  endif;  ?>

					<?php if ( $this->isTaxonomyField() ) : ?>
					<script>
						jQuery(document).ready(function() {
							jQuery(document).on( 'mousedown click keydown', '#submit', function() {
							  tinymce.triggerSave();
							});
						});
					</script>
					<?php endif; ?>

				<?php else: // no editor - print textarea  ?>
					<?php $entry = esc_html($this->entry); ?>
					<textarea name="<?php echo $this->getFieldName('val'); ?>" id="<?php echo $this->getFieldId('val'); ?>" rows="5" cols="50"><?php echo $entry ?></textarea>
				<?php endif; ?>

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
		$instance = wp_parse_args((array) $this->instance, array( 'title' => '', 'description' => '' ));
		$title = esc_attr($instance['title']);
		$description = esc_html($instance['description']);
		$checked = !empty($instance['editor']) ? ' checked="checked" ' : '';
		?>
		<p><label for="<?php echo $this->getFieldId('title'); ?>"><?php _e('Title:', \JustCustomFields::TEXTDOMAIN); ?></label> <input class="widefat" id="<?php echo $this->getFieldId('title'); ?>" name="<?php echo $this->getFieldName('title'); ?>" type="text" value="<?php echo $title; ?>" /></p>
		<p><label for="<?php echo $this->getFieldId('editor'); ?>"><input class="checkbox" id="<?php echo $this->getFieldId('editor'); ?>" name="<?php echo $this->getFieldName('editor'); ?>" type="checkbox" value="1" <?php echo $checked; ?> /> <?php _e('Use Editor for this textarea:', \JustCustomFields::TEXTDOMAIN); ?></label></p>
		<p><label for="<?php echo $this->getFieldId('description'); ?>"><?php _e('Description:', \JustCustomFields::TEXTDOMAIN); ?></label> <textarea name="<?php echo $this->getFieldName('description'); ?>" id="<?php echo $this->getFieldId('description'); ?>" cols="20" rows="4" class="widefat"><?php echo $description; ?></textarea></p>
		<?php
	}

	/**
	 * 	save field on post edit form
	 */
	public function save( $values )
	{
		$values = isset($values['val']) ? $values['val'] : '';

		if ( $this->instance['editor'] ) {
			$values = wpautop($values);
		}
		return $values;
	}

	/**
	 * 	update instance (settings) for current field
	 */
	public function update( $new_instance, $old_instance )
	{
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['editor'] = (int) @$new_instance['editor'];
		$instance['description'] = strip_tags($new_instance['description']);
		return $instance;
	}

}
?>