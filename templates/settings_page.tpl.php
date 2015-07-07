<div class="wrap" >
	<form method="post" action="<?php get_permalink(); ?>">
	<div class="icon32 icon32-posts-page" id="icon-edit"><br></div>
	<h2><?php _e('Just Custom Fields', JCF_TEXTDOMAIN); ?></h2>
	<p><?php _e('You should choose Custom Post Type first to configure fields:', JCF_TEXTDOMAIN); ?></p>
	<div class="jcf_columns jcf_width66p">
		<small>Read from: <input type="radio" class="jcf_choose_settings" name="jcf_settings" value="db" id="jcf_read_db" <?php echo (empty($jcf_read_settings) || $jcf_read_settings == 'db' ? 'checked="checked"' : '');  ?>/><label for="jcf_read_db">DataBase</label></small>
		<small><input type="radio" rel="" class="jcf_choose_settings" name="jcf_settings" value="file" id="jcf_read_file" <?php echo (!empty($jcf_read_settings) && $jcf_read_settings == 'file' ? 'checked="checked"' : ''); ?>/><label for="jcf_read_file">File settings</label></small>
	
		<ul class="dotted-list jcf-bold">
		<?php foreach($post_types as $key => $obj) : ?>
			<li><a href="?page=just_custom_fields&amp;pt=<?php echo $key; ?>"><?php echo $obj->label; ?></a></li>
		<?php endforeach; ?>
		</ul>
		<input type="submit" name="keep_settings" value="<?php _e('Insert settings to file', JCF_TEXTDOMAIN); ?>" class="button-primary"  />
	</div>
	<div class="jcf_columns jcf_width33p">
		<a class="button-primary" href="?page=just_custom_fields&amp;export"><?php _e('Export Fields', JCF_TEXTDOMAIN); ?></a><br /><br />
		<a class="button-primary" href="?page=just_custom_fields&amp;import" ><?php _e('Import Fields', JCF_TEXTDOMAIN); ?></a><br />
	</div>
	</form>
</div>
