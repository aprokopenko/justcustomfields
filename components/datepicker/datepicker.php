<?php
class Just_Field_DatePicker extends Just_Field{
	
	function Just_Field_DatePicker(){
		$field_ops = array( 'classname' => 'field_datepicker' );
		$this->Just_Field('datepicker', __('Date Picker', JCF_TEXTDOMAIN), $field_ops);
	}
	
	/**
	 *	draw field on post edit form
	 *	you can use $this->instance, $this->entry
	 */
	function field( $args ) {
		extract( $args );
		
		echo $before_widget;
		echo $before_title . $this->instance['title'] . $after_title;
		echo '<div>';
		echo '<input id="'.$this->get_field_id('val').'" name="'.$this->get_field_name('val').'" type="text" value="'.esc_attr($this->entry).'" size="40" style="width:47%;" />' . "\n";
		echo '</div>';
		?>
		<script type="text/javascript"><!--
			jQuery(document).ready(function(){
				jQuery("#<?php echo $this->get_field_id('val'); ?>").datepicker({
					dateFormat: "yy-mm-dd"
					<?php if(!empty($this->instance['show_monthes'])) echo ', changeMonth: true, changeYear: true'; ?>
				});
			});
		--></script>
		<?php
		echo $after_widget;
	}
	
	/**
	 *	save field on post edit form
	 */
	function save( $values ){
		$values = $values['val'];
		return $values;
	}
	
	/**
	 *	update instance (settings) for current field
	 */
	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['show_monthes'] = (int)@$new_instance['show_monthes'];
		return $instance;
	}

	/**
	 *	print settings form for field
	 */	
	function form( $instance ) {
		//Defaults
		$instance = wp_parse_args( (array) $instance, array( 'title' => '' ) );

		$title = esc_attr( $instance['title'] );
		$show_monthes = !empty($instance['show_monthes'])? ' checked="checked" ' : '';
		?>
		<p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:', JCF_TEXTDOMAIN); ?></label> <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" /></p>
		<p><label for="<?php echo $this->get_field_id('show_monthes'); ?>"><input class="checkbox" id="<?php echo $this->get_field_id('show_monthes'); ?>" name="<?php echo $this->get_field_name('show_monthes'); ?>" type="checkbox" value="1" <?php echo $show_monthes; ?> /> <?php _e('Show month/year select boxes', JCF_TEXTDOMAIN); ?></label></p>
		<?php
	}
	
	function add_js(){
		wp_register_script(
				'jcf_ui_datepicker',
				WP_PLUGIN_URL.'/just-custom-fields/components/datepicker/ui-datepicker.min.js',
				array('jquery', 'jquery-ui-core')
			);
		wp_enqueue_script('jcf_ui_datepicker');
	}
	
	function add_css(){
		wp_register_style('jcf_ui_datepicker', WP_PLUGIN_URL.'/just-custom-fields/components/datepicker/ui-theme-smoothness/jquery-ui-1.8.13.custom.css');
		wp_enqueue_style('jcf_ui_datepicker');
	}
	
}
?>