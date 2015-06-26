<div class="wrap">
	<div class="icon32 icon32-posts-page" id="icon-edit"><br></div>
	<h2><?php _e('Just Custom Fields', JCF_TEXTDOMAIN); ?></h2>
	<p><?php _e('You should choose Custom Post Type first to configure fields:', JCF_TEXTDOMAIN); ?></p>
	<div class="jcf_columns jcf_width66p">
		<ul class="dotted-list jcf-bold">
		<?php foreach($post_types as $key => $obj) : ?>
			<li><a href="?page=just_custom_fields&amp;pt=<?php echo $key; ?>"><?php echo $obj->label; ?></a></li>
		<?php endforeach; ?>
		</ul>
	</div>
	<?php if( MULTISITE ): ?>
		<div class="jcf_columns jcf_width33p">
			<div class="jcf_inner_box">
				<h3 class="header"><?php _e('MultiSite settings:', JCF_TEXTDOMAIN); ?></h3>
				<div class="jcf_inner_content">
					<form action="#" id="jcform_multisite_settings" method="post" class="jcf_form_horiz">
						<fieldset>
							<input type="radio" name="jcf_multisite_setting" id="jcf_setting_global" value="global" <?php echo $multisite_settings == 'global' ? 'checked="checked"' : ''; ?> />
							<label for="jcf_setting_global"><?php _e('Make fields settings global for all network', JCF_TEXTDOMAIN); ?> </label><br />
							<input type="radio" name="jcf_multisite_setting" id="jcf_setting_each" value="each" <?php echo $multisite_settings == 'each' ? 'checked="checked"' : ''; ?> />
							<label for="jcf_setting_each"><?php _e('Fields settings are unique for each site', JCF_TEXTDOMAIN); ?> </label><br />
							<input type="submit" name="jcf_update_settings" value="<?php _e('Update', JCF_TEXTDOMAIN); ?>" />
							<?php echo print_loader_img(); ?>
						</fieldset>
					</form>
				</div>
			</div>
		</div>
	<?php endif; ?>
</div>
