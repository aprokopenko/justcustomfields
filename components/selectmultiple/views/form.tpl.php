<?php
//Defaults
$instance = wp_parse_args((array) $field_obj->instance, array( 'title' => '', 'description' => '', 'settings' => '' ));

$title = esc_attr($instance['title']);
$settings = esc_attr($instance['settings']);
$description = esc_html($instance['description']);
?>
<p><label for="<?php echo $field_obj->get_field_id('title'); ?>"><?php _e('Title:', \JustCustomFields::TEXTDOMAIN); ?></label> <input class="widefat" id="<?php echo $field_obj->get_field_id('title'); ?>" name="<?php echo $field_obj->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" /></p>

<p><label for="<?php echo $field_obj->get_field_id('settings'); ?>"><?php _e('Settings:', \JustCustomFields::TEXTDOMAIN); ?></label>
	<textarea class="widefat" id="<?php echo $field_obj->get_field_id('settings'); ?>" name="<?php echo $field_obj->get_field_name('settings'); ?>" ><?php echo $settings; ?></textarea>
	<br/><small><?php _e('Parameters like (you can use just "label" if "id" is the same):<br>label1|id1<br>label2|id2<br>label3', \JustCustomFields::TEXTDOMAIN); ?></small></p>
<p><label for="<?php echo $field_obj->get_field_id('description'); ?>"><?php _e('Description:', \JustCustomFields::TEXTDOMAIN); ?></label> <textarea name="<?php echo $field_obj->get_field_name('description'); ?>" id="<?php echo $field_obj->get_field_id('description'); ?>" cols="20" rows="4" class="widefat"><?php echo $description; ?></textarea></p>
