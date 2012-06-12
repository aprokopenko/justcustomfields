<?php
/**
 * Textarea field type
 *
 * @package default
 * @author Alexander Prokopenko
 */
class Just_Field_Textarea extends Just_Field{
	
	function Just_Field_Textarea() {
		$field_ops = array( 'classname' => 'field_textarea' );
		$this->Just_Field('textarea', __('Textarea', JCF_TEXTDOMAIN), $field_ops);
	}
	
	/**
	 *	draw field on post edit form
	 *	you can use $this->instance, $this->entry
	 */
	function field( $args ) {
		extract( $args );

		echo $before_widget;
		echo $before_title . $this->instance['title'] . $after_title;
		
		// check editor
		if( !empty($this->instance['editor']) ){
			// WP 3.3+ >> we have new cool function to make wysiwyg field
			if( function_exists('wp_editor') ){
				wp_editor($this->entry, $this->get_field_id('val'), array(
					'textarea_name' => $this->get_field_name('val'),
					'textarea_rows' => 5,
					'media_buttons' => false,
					'tinymce' => array(
						'theme_advanced_buttons1' => 'bold,italic,strikethrough,|,bullist,numlist,blockquote,|,justifyleft,justifycenter,justifyright,|,link,unlink,|,spellchecker,fullscreen,wp_adv',
					),
				));
			}
			// old version >> hook js, print textarea
			else{
				add_action( 'admin_print_footer_scripts', array(&$this, 'customTinyMCE'), 9999 );
				$entry = wpautop($this->entry);
				$entry = esc_html($entry);
				?>
				<textarea class="mceEditor" name="<?php echo $this->get_field_name('val'); ?>" id="<?php echo $this->get_field_id('val'); ?>" rows="5" cols="50"><?php echo $entry?></textarea>
				<?php
			}
		}
		// no editor - print textarea
		else{
			$entry = esc_html($this->entry);
			?>
			<textarea name="<?php echo $this->get_field_name('val'); ?>" id="<?php echo $this->get_field_id('val'); ?>" rows="5" cols="50"><?php echo $entry?></textarea>
			<?php
		} // end if($editor)
		?>
		<?php if( !empty($this->instance['description']) )?>
			<p class="description"><?php echo $this->instance['description']; ?></p>
		<?php
		echo $after_widget;
	}
	
	/**
	 *	save field on post edit form
	 */
	function save( $values ) {
		$values = isset($values['val']) ? $values['val'] : '' ;
		return $values;
	}
	
	/**
	 *	update instance (settings) for current field
	 */
	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['editor'] = (int)@$new_instance['editor'];
		$instance['description'] = strip_tags($new_instance['description']);

		return $instance;
	}

	/**
	 *	print settings form for field
	 */	
	function form( $instance ) {
		//Defaults
		$instance = wp_parse_args( (array) $instance, array( 'title' => '', 'description' => '' ) );
		$title = esc_attr( $instance['title'] );
		$description = esc_html($instance['description']);
		$checked = !empty($instance['editor'])? ' checked="checked" ' : '';
		?>
		<p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:', JCF_TEXTDOMAIN); ?></label> <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" /></p>
		<p><label for="<?php echo $this->get_field_id('editor'); ?>"><input class="checkbox" id="<?php echo $this->get_field_id('editor'); ?>" name="<?php echo $this->get_field_name('editor'); ?>" type="checkbox" value="1" <?php echo $checked; ?> /> <?php _e('Use Editor for this textarea:', JCF_TEXTDOMAIN); ?></label></p>
		<p><label for="<?php echo $this->get_field_id('description'); ?>"><?php _e('Description:', JCF_TEXTDOMAIN); ?></label> <textarea name="<?php echo $this->get_field_name('description'); ?>" id="<?php echo $this->get_field_id('description'); ?>" cols="20" rows="4" class="widefat"><?php echo $description; ?></textarea></p>
		<?php
	}
	
	/**
	 *	load custom script for tiny MCE for editors
	 */
	function customTinyMCE(){
		global $jcf_flag_tiny_mce;
		
		if ( !empty($jcf_flag_tiny_mce) || ! user_can_richedit() )
			return;
		
		// just use standard tinyMCE for our textarea class
		// rewrite toolbar: remove "more" button, add "html" button
		?>
		<script type="text/javascript"><!--
			tinyMCEPreInit.mceInit.editor_selector = 'mceEditor';
			tinyMCEPreInit.mceInit.theme_advanced_buttons1 = 'bold,italic,strikethrough,|,bullist,numlist,blockquote,|,justifyleft,justifycenter,justifyright,|,link,unlink,|,spellchecker,fullscreen,wp_adv,|,code';
			tinyMCE.init(tinyMCEPreInit.mceInit);
		--></script>
		
		<?php
		$jcf_flag_tiny_mce = true;
	}
}
?>