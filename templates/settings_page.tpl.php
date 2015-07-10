<div class="wrap">
	<dl class="jcf_tabs">
		<a href="?page=just_custom_fields&amp;tab=fields" class="jcf_tabs_link"><dt <?php echo ($jcf_tabs == 'fields' ? 'class="jcf_selected"' : '');?>><?php _e('Fields', JCF_TEXTDOMAIN); ?></dt></a>
		<dd <?php echo ($jcf_tabs == 'fields' ? 'class="jcf_selected"' : '');?>>
			<div class="jcf_tab-content">
				<div class="jcf_inner-tab-content" >
					<form method="post" action="<?php get_permalink(); ?>">
						<div class="icon32 icon32-posts-page" id="icon-edit"><br></div>
						<h3><?php _e('Just Custom Fields', JCF_TEXTDOMAIN); ?></h3>
						<p><?php _e('You should choose Custom Post Type first to configure fields:', JCF_TEXTDOMAIN); ?></p>
						<ul class="dotted-list jcf-bold">
						<?php foreach($post_types as $key => $obj) : ?>
							<li><a href="?page=just_custom_fields&amp;pt=<?php echo $key; ?>"><?php echo $obj->label; ?></a></li>
						<?php endforeach; ?>
						</ul>
						<input type="submit" name="keep_settings" value="<?php _e('Insert settings from db to file', JCF_TEXTDOMAIN); ?>" class="button-primary"  />
					</form>
				</div>
			</div>
		</dd>
		<a href="?page=just_custom_fields&amp;tab=settings" class="jcf_tabs_link"><dt <?php echo ($jcf_tabs == 'settings' ? 'class="jcf_selected"' : '');?>><?php _e('Settings', JCF_TEXTDOMAIN); ?></dt></a>
		<dd <?php echo ($jcf_tabs == 'settings' ? 'class="jcf_selected"' : '');?>>
			<div class="jcf_tab-content">
				<div class="jcf_inner-tab-content" >
					<div class="jcf_inner_content">
						<form action="<?php get_permalink(); ?>" id="jcform_multisite_settings" method="post" class="jcf_form_horiz">

							<?php if( MULTISITE ): ?>
								<h3 class="header"><?php _e('MultiSite settings:', JCF_TEXTDOMAIN); ?></h3>
								<fieldset>
									<input type="radio" name="jcf_multisite_setting" id="jcf_setting_global" value="network" <?php echo $jcf_multisite_settings == 'network' ? 'checked="checked"' : ''; ?> />
									<label for="jcf_setting_global"><?php _e('Make fields settings global for all network', JCF_TEXTDOMAIN); ?> </label><br />
									<input type="radio" name="jcf_multisite_setting" id="jcf_setting_each" value="site" <?php echo $jcf_multisite_settings == 'site' ? 'checked="checked"' : ''; ?> />
									<label for="jcf_setting_each"><?php _e('Fields settings are unique for each site', JCF_TEXTDOMAIN); ?> </label><br /><br />
								</fieldset>
							<?php endif; ?>

							<h3 class="header"><?php _e('Saving method:', JCF_TEXTDOMAIN); ?></h3>
							<input type="radio" class="jcf_choose_settings" name="jcf_read_settings" value="db" id="jcf_read_db" <?php echo (empty($jcf_read_settings) || $jcf_read_settings == 'db' ? 'checked="checked"' : '');  ?>/><label for="jcf_read_db">Database. You can't edit or move settings without export/import features (default)</label><br />
							<input type="radio" rel="" class="jcf_choose_settings" name="jcf_read_settings" value="theme" id="jcf_read_file" <?php echo (!empty($jcf_read_settings) && $jcf_read_settings == 'theme' ? 'checked="checked"' : ''); ?>/><label for="jcf_read_file">File system: Current theme folder. Field configuration is saved to the current theme folder in json format and can be copied to another site easily.</label><br />
							<?php if( MULTISITE && $jcf_multisite_settings == 'network' ) :?>
								<input type="radio" rel="" class="jcf_choose_settings" name="jcf_read_settings" value="global" id="jcf_read_file_global" <?php echo (!empty($jcf_read_settings) && $jcf_read_settings == 'global' ? 'checked="checked"' : ''); ?>/><label for="jcf_read_file_global">File system: Global (/wp-content/jcf-settings). Field configuration is saved to the wp-content folder in json format and can be copied to another site easily.</label><br />
							<?php endif;?>
							<br /><br />
							<br /><br />
							<input type="submit" class="button-primary" name="jcf_update_settings" value="<?php _e('Save all settings', JCF_TEXTDOMAIN); ?>" />
						</form>
					</div>
				</div>
			</div>
		</dd>
		<a href="?page=just_custom_fields&amp;tab=import_export" class="jcf_tabs_link"><dt <?php echo ($jcf_tabs == 'import_export' ? 'class="jcf_selected"' : '');?>><?php _e('Import/Export', JCF_TEXTDOMAIN); ?></dt></a>
		<dd <?php echo ($jcf_tabs == 'import_export' ? 'class="jcf_selected"' : '');?>>
			<div class="jcf_tab-content">
				<div class="jcf_inner-tab-content" >
					<div class="jcf_inner_content">
						<div class="jcf_columns jcf_width50p ">
							<div class="jcf_inner_content">
								<h3 >Import</h3>
								<p>---------------<br />
								If you have Just Custom Fields configuration file you can import some specific settings from it to your current WordPress installation. Please choose your configuration file and press "Import Wizard" button</p>
								<div>
									<div class="icon32 icon32-posts-page" id="icon-edit"><br></div>
									<p><?php _e('Add file to import:', JCF_TEXTDOMAIN); ?></p>
									<form action="<?php get_permalink(); ?>" method="post" id="jcf_import_fields" enctype="multipart/form-data" >
										<div>
											<input type="file" name="import_data" /><br />
											<small>file extention: .json</small>
										</div>
										<div>
											<input type="submit" class="button-primary" name="import-btn" value="<?php _e('Import', JCF_TEXTDOMAIN); ?>" />
										</div>
									</form>
								</div>
							</div>
						</div>
						<div class="jcf_columns jcf_width50p ">
							<div class="jcf_inner_content">
								<h3 >Export</h3>
								<p>--------------<br />
								You can export specific field settings and move them to another site if needed. Just click "Export Wizard" button to start.</p>
								<a class="button-primary" href="?page=just_custom_fields&amp;export"><?php _e('Export Fields', JCF_TEXTDOMAIN); ?></a><br /><br />
							</div>
						</div>
					</div>

				</div>
			</div>
		</dd>
	</dl>
</div>
