<div class="wrap">
	<div class="icon32 icon32-posts-page" id="icon-edit"><br></div>
	<h2><?php _e('Just Custom Fields', JCF_TEXTDOMAIN); ?></h2>
	
	<?php do_action('jcf_print_admin_notice'); ?>
	
	<h2 class="nav-tab-wrapper">
		<a class="nav-tab <?php echo ($jcf_tabs == 'fields' ? 'nav-tab-active' : '');?>" href="?page=just_custom_fields&amp;tab=fields"><?php _e('Fields', JCF_TEXTDOMAIN); ?></a>
		<a class="nav-tab <?php echo ($jcf_tabs == 'settings' ? 'nav-tab-active' : '');?>" href="?page=just_custom_fields&amp;tab=settings"><?php _e('Settings', JCF_TEXTDOMAIN); ?></a>
		<a class="nav-tab <?php echo ($jcf_tabs == 'import_export' ? 'nav-tab-active' : '');?>" href="?page=just_custom_fields&amp;tab=import_export"><?php _e('Import/Export', JCF_TEXTDOMAIN); ?></a>
	</h2>
	
	<?php // Fields list ?>
	<?php if( $jcf_tabs == 'fields' ): ?>
	<div class="jcf_tab-content">
		<div class="jcf_inner-tab-content" >
			<div class="icon32 icon32-posts-page" id="icon-edit"><br></div>
			<p><?php _e('You should choose Custom Post Type first to configure fields for it:', JCF_TEXTDOMAIN); ?></p>
			<div>
				<ul class="dotted-list jcf-bold">
				<?php foreach($post_types as $key => $obj) : ?>
					<?php $fieldsets_count = jcf_fieldsets_count($key); ?>
					<li>
						<a class="jcf_tile jcf_tile_<?php echo $key; ?>" href="?page=just_custom_fields&amp;pt=<?php echo $key; ?>">
							<span class="jcf_tile_icon"></span>
							<span class="jcf_tile_title"><?php echo $obj->label; ?>
								<span class="jcf_tile_info">
									<?php _e('Added Fieldsets: ', JCF_TEXTDOMAIN); ?><?php echo $fieldsets_count['fieldsets']; ?>
									<?php _e('Total Fields:  ', JCF_TEXTDOMAIN); ?><?php echo $fieldsets_count['fields']; ?>
								</span>
							</span>
						</a>
					</li>
				<?php endforeach; ?>
				</ul>
			</div>
		</div>
	</div>
	
	<?php // Settings boxes ?>
	<?php elseif( $jcf_tabs == 'settings' ): ?>
	<div class="jcf_tab-content">
		<div class="jcf_inner-tab-content" >
			<form action="<?php get_permalink(); ?>" id="jcform_settings" method="post" class="jcf_form_horiz" onsubmit="return initSettings();">
				
				<?php if( MULTISITE ): ?>
					<div class="card pressthis">
						<h3 class="header"><?php _e('MultiSite settings:', JCF_TEXTDOMAIN); ?></h3>
						<fieldset>
							<input type="radio" name="jcf_multisite_setting" id="jcf_setting_global" 
								   value="<?php echo JCF_CONF_MS_NETWORK; ?>" <?php checked($jcf_multisite_settings, JCF_CONF_MS_NETWORK); ?> />
							<label for="jcf_setting_global"><?php _e('Make fields settings global for all network', JCF_TEXTDOMAIN); ?> </label><br />
							
							<input type="radio" name="jcf_multisite_setting" id="jcf_setting_each" 
								   value="<?php echo JCF_CONF_MS_SITE; ?>" <?php checked($jcf_multisite_settings, JCF_CONF_MS_SITE); ?> />
							<label for="jcf_setting_each"><?php _e('Fields settings are unique for each site', JCF_TEXTDOMAIN); ?> </label><br /><br />
						</fieldset>
					</div>
				<?php endif; ?>
				
				<div class="card pressthis">
					<h3 class="header"><?php _e('Settings storage configuration:', JCF_TEXTDOMAIN); ?></h3>
					
					<input type="radio" class="jcf_choose_settings" name="jcf_read_settings" 
						   value="<?php echo JCF_CONF_SOURCE_DB; ?>" id="jcf_read_db" <?php checked($jcf_read_settings, JCF_CONF_SOURCE_DB); ?>/>
					<label for="jcf_read_db"><?php _e('<b>Database</b>. You can\'t edit or move settings without export/import features (default)' , JCF_TEXTDOMAIN); ?></label><br />
					
					<input type="radio" rel="" class="jcf_choose_settings" name="jcf_read_settings" 
						   value="<?php echo JCF_CONF_SOURCE_FS_THEME; ?>" id="jcf_read_file"  <?php checked($jcf_read_settings, JCF_CONF_SOURCE_FS_THEME); ?>/>
					<label for="jcf_read_file"><?php _e('<b>File system: Current theme folder</b>. Fields configuration is saved to the current theme folder in json format and can be copied to another site easily.' , JCF_TEXTDOMAIN); ?></label><br />
					
					<?php $show_fs_global = MULTISITE && $jcf_multisite_settings == JCF_CONF_MS_NETWORK; ?>
					<input type="radio" rel="" <?php if(!$show_fs_global) echo 'style="display:none;"'; ?> 
						   class="jcf_choose_settings" name="jcf_read_settings" 
						   value="<?php echo JCF_CONF_SOURCE_FS_GLOBAL; ?>" id="jcf_read_file_global"  <?php checked($jcf_read_settings, JCF_CONF_SOURCE_FS_GLOBAL); ?>/>
					<label for="jcf_read_file_global" <?php if(!$show_fs_global) echo 'style="display:none;"'; ?>><?php _e('<b>File system: Global</b> (/wp-content/jcf-settings/*). Fields configuration is saved to the wp-content folder in json format and can be copied to another site easily.' , JCF_TEXTDOMAIN); ?></label><br />
					
					<input type="hidden" name="jcf_keep_settings" value="1" disabled="disabled" />
				</div>
				<br /><br />
				<?php wp_nonce_field("just-nonce"); ?>
				<input type="submit" class="button-primary jcf_update_settings" name="jcf_update_settings" value="<?php _e('Save all settings', JCF_TEXTDOMAIN); ?>" />
			</form>
		</div>
	</div>
	
	<?php // IMPORT / EXPORT TAB ?>
	<?php elseif( $jcf_tabs == 'import_export' ): ?>
	<div class="jcf_tab-content">
		<div class="jcf_inner-tab-content" >
			<div class="jcf_columns jcf_width40p mrgr20">
				<div class="card pressthis">
					<h3 class="header"><?php _e('Import', JCF_TEXTDOMAIN); ?></h3>
					<div class="jcf_inner_content offset0">
						<p>
							<?php _e('If you have Just Custom Fields configuration file you can import some specific settings from it to your current WordPress installation.<br/><br/>Please choose your configuration file and press "Import Wizard" button' , JCF_TEXTDOMAIN); ?>
						</p>
						<div>
							<div class="icon32 icon32-posts-page" id="icon-edit"><br></div>
							<form action="<?php get_permalink(); ?>" method="post" id="jcf_import_fields" enctype="multipart/form-data" >
								<input type="hidden" name ="action" value="jcf_import_fields" />
								<p><?php _e('Add file to import:', JCF_TEXTDOMAIN); ?>
									<input type="file" id="import_data_file" name="import_data" /><br />
									<small><?php _e('file extention: .json', JCF_TEXTDOMAIN); ?></small>
								</p>
								<div>
									<input type="submit" class="button-primary" name="import-btn" value="<?php _e('Import', JCF_TEXTDOMAIN); ?>" />
								</div>
							</form>
							<div id="res"></div>
							<iframe id="hiddenframe" name="hiddenframe" style="width:0px; height:0px; border:0px"></iframe>
						</div>
					</div>
				</div>
			</div>
			<div class="jcf_columns jcf_width40p">
				<div class="card pressthis">
					<h3 class="header"><?php _e('Export', JCF_TEXTDOMAIN); ?></h3>
					<div class="jcf_inner_content offset0">
						<p>
						<?php _e('You can export specific field settings and move them to another site if needed. Just click "Export Wizard" button to start.' , JCF_TEXTDOMAIN); ?></p>
						<a class="button-primary" id="export-button" href="#"><?php _e('Export Wizard', JCF_TEXTDOMAIN); ?></a><br /><br />
					</div>
				</div>
			</div>
		</div>
	</div>
	<?php endif; ?>
</div>
