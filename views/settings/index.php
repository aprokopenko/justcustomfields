<?php
/* @var $source string */
/* @var $network string */
/* @var $googlemaps_api_key string */

use jcf\models\Settings;
?>
<?php include(JCF_ROOT . '/views/_header.php'); ?>

<div class="jcf_tab-content">
	<div class="jcf_inner-tab-content" >
		<form action="<?php get_permalink(); ?>" id="jcform_settings" method="post" class="jcf_form_horiz" onsubmit="return initSettings();">

			<div class="card pressthis">
				<h3 class="header"><?php _e('Settings storage configuration:', \JustCustomFields::TEXTDOMAIN); ?></h3>

				<input type="radio" class="jcf_choose_settings" name="source"
					   value="<?php echo Settings::CONF_SOURCE_DB; ?>" id="jcf_read_db" <?php  checked($source, Settings::CONF_SOURCE_DB); ?>/>
				<label for="jcf_read_db"><?php _e('<b>Database</b>. Useful when you have only 1 site installation', \JustCustomFields::TEXTDOMAIN); ?></label><br />

				<input type="radio" rel="" class="jcf_choose_settings" name="source" 
					   value="<?php echo Settings::CONF_SOURCE_FS_THEME; ?>" id="jcf_read_file"  <?php checked($source, Settings::CONF_SOURCE_FS_THEME); ?>/>
				<label for="jcf_read_file">
					<?php _e('<b>File system: Current theme folder</b>. Fields configuration is saved to the current theme folder in json format and can be copied to another site easily.' , \JustCustomFields::TEXTDOMAIN); ?>
				</label><br />

				<input type="radio" rel="" class="jcf_choose_settings" name="source"
					   value="<?php echo Settings::CONF_SOURCE_FS_GLOBAL; ?>" id="jcf_read_file_global"  <?php checked($source, Settings::CONF_SOURCE_FS_GLOBAL); ?>/>
				<label for="jcf_read_file_global">
					<?php _e('<b>File system: Global</b> (/wp-content/jcf/*). Fields configuration is saved to the wp-content folder in json format and can be copied to another site easily.' , \JustCustomFields::TEXTDOMAIN); ?>
				</label><br />
			</div>

			<?php if( MULTISITE && $source == Settings::CONF_SOURCE_DB ): ?>
				<div class="card pressthis">
					<h3 class="header"><?php _e('Database MultiSite Options:', \JustCustomFields::TEXTDOMAIN); ?></h3>
					<fieldset>
						<input type="radio" name="network" id="jcf_setting_global"
							   value="<?php echo Settings::CONF_MS_NETWORK; ?>" <?php checked($network, Settings::CONF_MS_NETWORK); ?> />
						<label for="jcf_setting_global"><?php _e('Make fields settings global for all network', \JustCustomFields::TEXTDOMAIN); ?> </label><br />

						<input type="radio" name="network" id="jcf_setting_each"
							   value="<?php echo Settings::CONF_MS_SITE; ?>" <?php checked($network, Settings::CONF_MS_SITE); ?> />
						<label for="jcf_setting_each"><?php _e('Fields settings are unique for each site', \JustCustomFields::TEXTDOMAIN); ?> </label><br /><br />
					</fieldset>
				</div>
			<?php endif; ?>

			<div class="card pressthis">
				<h3 class="header"><?php _e('Google Maps Settings:', \JustCustomFields::TEXTDOMAIN); ?></h3>

				<label for="googlemaps_api_key"><?php _e('Google Maps API Key', \JustCustomFields::TEXTDOMAIN); ?></label>

				<input type="text" name="googlemaps_api_key" id="googlemaps_api_key" class="regular-text"
					   value="<?php echo esc_attr($googlemaps_api_key) ?>" /><br />

				<p><a href="https://developers.google.com/maps/documentation/javascript/get-api-key#get-an-api-key" target="_blank" >Click here</a> to generate your API Key.</p>
				<p><small>Usually API key is domain related, so if you moved your site to a new domain - please update the API key.</small></p>
				<br />
			</div>

			<br /><br />
			<?php wp_nonce_field("just-nonce"); ?>
			<input type="submit" class="button-primary jcf_update_settings" name="jcf_update_settings" value="<?php _e('Save all settings', \JustCustomFields::TEXTDOMAIN); ?>" />
		</form>
	</div>
</div>

<?php include(JCF_ROOT . '/views/_footer.php'); ?>
