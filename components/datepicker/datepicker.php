<?php
class Just_Field_DatePicker extends Just_Field{
	
	public static $compatibility = '3.3+';

	public function __construct(){
		$field_ops = array( 'classname' => 'field_datepicker' );
		parent::__construct('datepicker', __('Date Picker', JCF_TEXTDOMAIN), $field_ops);
	}
	
	/**
	 *	draw field on post edit form
	 *	you can use $this->instance, $this->entry
	 */
	public function field( $args ) {
		extract( $args );
		
		echo $before_widget;
		echo $before_title . $this->instance['title'] . $after_title;
		echo '<div>';
		echo '<input id="'.$this->get_field_id('val').'" name="'.$this->get_field_name('val').'" type="text" value="'.esc_attr($this->entry).'" size="20" style="width:25%;" />' . "\n";
		echo '</div>';
		?>
		<script type="text/javascript"><!--
			jQuery(document).ready(function(){
				jQuery("#<?php echo $this->get_field_id('val'); ?>").datepicker({
					dateFormat: "<?php echo !empty($this->instance['date_format']) ? $this->instance['date_format'] : 'yy-mm-dd'; ?>"
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
	public function save( $values ){
		$values = $values['val'];
		return $values;
	}

	/**
	 *	update instance (settings) for current field
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['show_monthes'] = (int)@$new_instance['show_monthes'];
		$instance['date_format'] = @$new_instance['date_format'];
		return $instance;
	}

	/**
	 *	print settings form for field
	 */	
	public function form( $instance ) {
		//Defaults
		$instance = wp_parse_args( (array) $instance, array( 'title' => '' ) );

		$title = esc_attr( $instance['title'] );
		$show_monthes = !empty($instance['show_monthes'])? ' checked="checked" ' : '';
		$date_format =  !empty($instance['date_format']) ? $instance['date_format'] : 'yy-mm-dd' ;
		?>
		<p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:', JCF_TEXTDOMAIN); ?></label> <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" /></p>
		<p><label for="<?php echo $this->get_field_id('show_monthes'); ?>"><input class="checkbox" id="<?php echo $this->get_field_id('show_monthes'); ?>" name="<?php echo $this->get_field_name('show_monthes'); ?>" type="checkbox" value="1" <?php echo $show_monthes; ?> /> <?php _e('Show month/year select boxes', JCF_TEXTDOMAIN); ?></label></p>
		<p><label for="<?php echo $this->get_field_id('date_format'); ?>"><?php _e('Date format:', JCF_TEXTDOMAIN); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id('date_format'); ?>"
				   name="<?php echo $this->get_field_name('date_format'); ?>" type="text"
				   value="<?php echo $date_format; ?>" /><br />
			<small><?php _e('Example:', JCF_TEXTDOMAIN);?> yy-mm-dd <a href="http://api.jqueryui.com/datepicker/#option-dateFormat" target="_blank"><?php _e('look more about date formats', JCF_TEXTDOMAIN);?></a></small>
		</p>
		<?php
	}

	public function add_js(){
		/**
		 * WP version 3.0 and above have datepicker ui-core;
		 */
		wp_enqueue_script('jquery-ui-datepicker');
	}
	
	public function add_css(){
		wp_register_style('jcf_ui_datepicker', WP_PLUGIN_URL.'/just-custom-fields/components/datepicker/ui-theme-smoothness/jquery-ui-1.8.13.custom.css');
		wp_enqueue_style('jcf_ui_datepicker');
	}

}
?>