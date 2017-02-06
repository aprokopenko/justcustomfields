<?php
namespace jcf\models;

use jcf\core\Model;
use jcf\core\DataLayerFactory;

class Migrate extends Model
{
	/**
	 * HTML error message with link to admin upgrade page
	 */
	public static function adminUpgradeNotice()
	{
		$link_text =  __('Update settings', \JustCustomFields::TEXTDOMAIN);
		$link = '<a href="'.admin_url('options-general.php?page=jcf_upgrade').'" class="jcf-btn-save button-primary">'.$link_text.'</a>';

		$warning = __('Thank you for upgrading Just Custom Field plugin. You need to update your settings to continue using the plugin. {link}', \JustCustomFields::TEXTDOMAIN);
		$warning = str_replace('{link}', $link, $warning);

		printf( '<div class="%1$s"><p>%2$s</p></div>', 'notice notice-error', $warning );
	}

	/**
	 * Find field settings and search the latest version found in field settings
	 *
	 * @return bool|int|mixed
	 */
	public static function guessVersion()
	{
		$fields = self::guessFields();

		// we can't guess version if can't find any settings
		if ( empty($fields) ) return false;

		$latest_version = 0;
		foreach ($fields as $post_type => $post_type_fields) {
			foreach ($post_type_fields as $field) {
				if (empty($field['_version'])) continue;
				$latest_version = max($latest_version, $field['_version']);
			}
		}

		return $latest_version;
	}

	/**
	 * Try to find field settings based on source and logic from previos plugin versions
	 * Function actual only for versions less than 3.1.
	 * Other versions has version saved directly in DB or file config.
	 *
	 * @return array|bool
	 */
	public static function guessFields()
	{
		// try to get fields from current data layer. maybe we don't have much changes
		$data_layer = DataLayerFactory::create();
		if ( $fields = $data_layer->getFields() ) {
			return $fields;
		}

		// we can't find the fields now we should try to search them manually
		$data_source = Settings::getDataSourceType();
		$network_mode = Settings::getNetworkMode();
		$post_types = jcf_get_post_types();

		// check old options from DB
		if ( Settings::CONF_SOURCE_DB == $data_source ) {
			$getter = 'get_option';
			if ( Settings::CONF_MS_NETWORK == $network_mode ) {
				$getter = 'get_site_option';
			}

			// ver < 3.1 has key 'jcf-fields'
			if ( $fields = $getter('jcf-fields') ) {
				return $fields;
			}

			// ver < 3.0 has keys based on post types: 'jcf_fields-{$post_type}'
			$grouped_fields = array();
			foreach ( $post_types as $post_type ) {
				$grouped_fields[$post_type] = array();
				if ( $fields = $getter("jcf_fields-{$post_type}") ) {
					$grouped_fields[$post_type] = $fields;
				}
			}
			return $grouped_fields;
		}

		// check old options from file system settings
		if ( Settings::CONF_SOURCE_FS_THEME == $data_source || Settings::CONF_SOURCE_FS_GLOBAL == $data_source ) {
			$base_folder = get_stylesheet_directory();
			if ( Settings::CONF_SOURCE_FS_GLOBAL == $data_source ) {
				$base_folder = WP_CONTENT_DIR;
			}

			$file_pathes = array(
				'jcf/config.json', // starting from v3.1
				'jcf-settings/jcf_settings.json', // before v3.1
			);
			foreach ( $file_pathes as $file ) {
				if ( ! is_file("$base_folder/$file") ) {
					continue;
				}

				$settings = file_get_contents("$base_folder/$file");
				if (empty($settings) || ! $settings = json_decode($settings, true)) {
					continue;
				}

				$settings = (array)$settings;

				// ver >= 3.0 has key 'fields'
				if ( !empty($settings['fields']) ) {
					return $settings['fields'];
				}
				// ver < 3.0 has key 'field_settings'
				if ( !empty($settings['field_settings']) ) {
					return $settings['field_settings'];
				}
			}
		}

		// we didn't find any fields
		return false;
	}
}