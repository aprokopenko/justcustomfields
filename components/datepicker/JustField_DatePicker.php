<?php

namespace jcf\components\datepicker;

use jcf\core;

class JustField_DatePicker extends core\JustField
{
	public static $compatibility = '3.3+';

	public function __construct()
	{
		$field_ops = array( 'classname' => 'field_datepicker' );
		parent::__construct('datepicker', __('Date Picker', \JustCustomFields::TEXTDOMAIN), $field_ops);
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
				
				<div>
					<input id="<?php echo $this->getFieldId('val'); ?>" name="<?php echo $this->getFieldName('val'); ?>" type="text" value="<?php echo esc_attr($this->entry); ?>" size="14" style="width:150px;" />
					<span class="dashicons dashicons-calendar-alt"></span>
				</div>

				<script type="text/javascript"><!--
					jQuery(document).ready(function() {
						jQuery("#<?php echo $this->getFieldId('val'); ?>").datepicker({
							dateFormat: "<?php echo !empty($this->instance['date_format']) ? esc_attr($this->instance['date_format']) : 'yy-mm-dd'; ?>"
				<?php if ( !empty($this->instance['show_monthes']) ) echo ', changeMonth: true, changeYear: true'; ?>
						});
					});
				--></script>

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
		$instance = wp_parse_args((array) $this->instance, array( 'title' => '' ));

		$title = esc_attr($instance['title']);
		$show_monthes = !empty($instance['show_monthes']) ? ' checked="checked" ' : '';
		$date_format = !empty($instance['date_format']) ? esc_attr($instance['date_format']) : 'yy-mm-dd';
		?>
		<p><label for="<?php echo $this->getFieldId('title'); ?>"><?php _e('Title:', \JustCustomFields::TEXTDOMAIN); ?></label> <input class="widefat" id="<?php echo $this->getFieldId('title'); ?>" name="<?php echo $this->getFieldName('title'); ?>" type="text" value="<?php echo $title; ?>" /></p>
		<p><label for="<?php echo $this->getFieldId('show_monthes'); ?>"><input class="checkbox" id="<?php echo $this->getFieldId('show_monthes'); ?>" name="<?php echo $this->getFieldName('show_monthes'); ?>" type="checkbox" value="1" <?php echo $show_monthes; ?> /> <?php _e('Show month/year select boxes', \JustCustomFields::TEXTDOMAIN); ?></label></p>
		<p><label for="<?php echo $this->getFieldId('date_format'); ?>"><?php _e('Date format:', \JustCustomFields::TEXTDOMAIN); ?></label>
			<input class="widefat" id="<?php echo $this->getFieldId('date_format'); ?>"
				   name="<?php echo $this->getFieldName('date_format'); ?>" type="text"
				   value="<?php echo $date_format; ?>" /><br />
			<small><?php _e('Example:', \JustCustomFields::TEXTDOMAIN); ?> yy-mm-dd <a href="http://api.jqueryui.com/datepicker/#option-dateFormat" target="_blank"><?php _e('look more about date formats', \JustCustomFields::TEXTDOMAIN); ?></a></small>
		</p>
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
		$instance['show_monthes'] = (int) @$new_instance['show_monthes'];
		$instance['date_format'] = @$new_instance['date_format'];
		return $instance;
	}

	public function addJs()
	{
		/**
		 * WP version 3.0 and above have datepicker ui-core;
		 */
		wp_enqueue_script('jquery-ui-datepicker');
	}

	public function addCss()
	{
		wp_register_style('jcf_ui_datepicker', jcf_plugin_url('components/datepicker/ui-theme-smoothness/jquery-ui-1.8.13.custom.css'));
		wp_enqueue_style('jcf_ui_datepicker');
	}

}
?>