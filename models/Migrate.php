<?php

namespace jcf\models;

use jcf\core\Migration;
use jcf\core\Model;
use jcf\core\DataLayerFactory;

/**
 * Class Migrate
 */
class Migrate extends Model {
	/**
	 * Form button name property
	 * Used for $model->load()
	 *
	 * @var string
	 */
	public $upgrade_storage;

	/**
	 * HTML error message with link to admin upgrade page
	 */
	public static function adminUpgradeNotice() {
		$link_text = __( 'Update settings', \JustCustomFields::TEXTDOMAIN );
		$link      = '<a href="' . admin_url( 'options-general.php?page=jcf_upgrade' ) . '" class="jcf-btn-save button-primary">' . $link_text . '</a>';

		$warning = __( 'Thank you for upgrading Just Custom Field plugin. You need to update your settings to continue using the plugin. {link}', \JustCustomFields::TEXTDOMAIN );
		$warning = str_replace( '{link}', $link, $warning );

		printf( '<div class="%1$s"><p>%2$s</p></div>', 'notice notice-error', $warning );
	}

	/**
	 * Search available migrations
	 * set protected $_version, $_migrations properties
	 *
	 * @return Migration[]
	 */
	public function find_migrations() {
		$version = $this->_dl->get_storage_version();
		if ( ! $version ) {
			$version = self::guessVersion();
		}

		$migrations = array();
		if ( $migration_files = $this->_getMigrationFiles( $version ) ) {
			foreach ( $migration_files as $ver => $file ) {
				$class_name = '\\jcf\\migrations\\' . preg_replace( '/\.php$/', '', basename( $file ) );

				require_once $file;
				$migrations[ $ver ] = new $class_name();
			}
		}

		return $migrations;
	}

	/**
	 * Scan migrations directory and filter outdated migration based on current version
	 *
	 * @param float $version Float version.
	 *
	 * @return array
	 */
	protected function _getMigrationFiles( $version ) {
		$folder = JCF_ROOT . '/migrations';
		$files  = scandir( $folder );

		$migrations = array();

		foreach ( $files as $key => $file ) {
			if ( '.' === $file || '..' === $file || ! is_file( $folder . '/' . $file )
			     || ! preg_match( '/^m([\dx]+)/', $file, $match )
			) {
				continue;
			}

			$mig_version = str_replace( 'x', '.', $match[1] );
			if ( version_compare( $mig_version, $version, '<=' ) ) {
				continue;
			}

			$migrations[ $mig_version ] = $folder . '/' . $file;
		}
		ksort( $migrations );

		return $migrations;
	}

	/**
	 * Do test run to check that we can migrate or need to show warnings
	 *
	 * @param Migration[] $migrations Migrations.
	 *
	 * @return array
	 */
	public function test_migrate( $migrations ) {
		$data     = null;
		$warnings = array();

		foreach ( $migrations as $ver => $m ) {
			if ( $warning = $m->run_test( $data ) ) {
				$warnings[ $ver ] = $warning;
			}
			$data = $m->run_update( $data );
		}

		return $warnings;
	}

	/**
	 * Run migrations
	 *
	 * @param Migration[] $migrations Migrations.
	 *
	 * @return boolean
	 */
	public function migrate( $migrations ) {
		if ( ! empty( $migrations ) ) {
			set_time_limit( 0 );

			$data = null;
			foreach ( $migrations as $ver => $m ) {
				$data = $m->run_update( $data, Migration::MODE_UPDATE );
			}

			$fields    = $data[ Migration::FIELDS_KEY ];
			$fieldsets = $data[ Migration::FIELDSETS_KEY ];

			$fields = $this->_update_fields_version( $fields );

			$this->_dl->set_fields( $fields );
			$this->_dl->set_fieldsets( $fieldsets );
			$updated = $this->_dl->save_fields_data() && $this->_dl->save_fieldsets_data();
		} else {
			$migrations = array();
			$updated    = true;
		}

		// do cleanup.
		if ( $updated ) {
			$this->_dl->save_storage_version();
			foreach ( $migrations as $ver => $m ) {
				$m->run_cleanup();
			}

			return true;
		} else {
			$this->add_error( 'Error! Upgrade failed. Please contact us through github to help you and update migration scripts.' );
		}
	}

	/**
	 * Check that current active storage is writable
	 * Required for Filesystem
	 * Set error
	 *
	 * @return boolean
	 */
	public function is_storage_writable() {
		$data_source = Settings::get_data_source_type();

		// if we use filesystem we need to know it's writable.
		if ( Settings::CONF_SOURCE_DB !== $data_source ) {
			$filepath = $this->_dl->get_config_file_path();
			$filedir  = dirname( $filepath );
			if ( ( ! is_dir( $filedir ) && ! wp_mkdir_p( $filedir ) ) || ! wp_is_writable( $filedir ) ) {
				$this->add_error( 'Error! Please check that settings directory is writable "' . dirname( $filepath ) . '"' );
			} elseif ( is_file( $filepath ) && ! wp_is_writable( $filepath ) ) {
				$this->add_error( 'Error! Please check that settings file is writable "' . ( $filepath ) . '"' );
			}
		}

		return ! $this->has_errors();
	}

	/**
	 * Set fields version to all fields
	 *
	 * @param array $fields_data Fields data.
	 *
	 * @return array
	 */
	public function _update_fields_version( $fields_data ) {
		$version = \JustCustomFields::$version;

		foreach ( $fields_data as $post_type => $fields ) {
			if ( ! is_array( $fields ) ) {
				continue;
			}

			foreach ( $fields as $id => $field ) {
				// collection also has fields inside.
				if ( ! empty( $field['fields'] ) && is_array( $field['fields'] ) ) {
					foreach ( $field['fields'] as $fid => $f ) {
						$fields_data[ $post_type ][ $id ]['fields'][ $fid ]['_version'] = $version;
					}
				}

				// update field version.
				$fields_data[ $post_type ][ $id ]['_version'] = $version;
			}
		}

		return $fields_data;
	}

	/**
	 * Find field settings and search the latest version found in field settings
	 * Actual only for version less than 3.1
	 *
	 * @return bool|int|mixed
	 */
	public static function guessVersion() {
		// check data source key exists. in v2.3 it was different key.
		if ( ! $data_source = Settings::get_data_source_type( '' ) ) {
			$data_source = get_site_option( 'jcf_read_settings', Settings::CONF_SOURCE_DB );
			update_site_option( Settings::OPT_SOURCE, $data_source );
		}

		$fields = self::guess_fields();

		// we can't guess version if can't find any settings.
		if ( empty( $fields ) ) {
			return false;
		}

		$latest_version = 0;
		foreach ( $fields as $post_type => $post_type_fields ) {
			foreach ( $post_type_fields as $field ) {
				if ( empty( $field['_version'] ) ) {
					continue;
				}
				$latest_version = max( $latest_version, $field['_version'] );
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
	public static function guess_fields() {
		// try to get fields from current data layer. maybe we don't have much changes.
		$data_layer = DataLayerFactory::create();
		if ( $fields = $data_layer->get_fields() ) {
			return $fields;
		}

		// we can't find the fields now we should try to search them manually.
		$data_source  = Settings::get_data_source_type();
		$network_mode = Settings::get_network_mode();
		$post_types   = jcf_get_post_types();

		// check old options from DB.
		if ( Settings::CONF_SOURCE_DB === $data_source ) {
			$getter = 'get_option';
			if ( Settings::CONF_MS_NETWORK === $network_mode ) {
				$getter = 'get_site_option';
			}

			// ver < 3.1 has key 'jcf-fields'.
			if ( $fields = $getter( 'jcf-fields' ) ) {
				return $fields;
			}

			// ver < 3.0 has keys based on post types: 'jcf_fields-{$post_type}'.
			$grouped_fields = array();
			foreach ( $post_types as $pt => $post_type ) {
				$grouped_fields[ $pt ] = array();
				if ( $fields = $getter( "jcf_fields-{$pt}" ) ) {
					$grouped_fields[ $pt ] = $fields;
				}
			}

			return $grouped_fields;
		}

		// check old options from file system settings.
		if ( Settings::CONF_SOURCE_FS_THEME === $data_source || Settings::CONF_SOURCE_FS_GLOBAL === $data_source ) {
			$base_folder = get_stylesheet_directory();
			if ( Settings::CONF_SOURCE_FS_GLOBAL === $data_source ) {
				$base_folder = WP_CONTENT_DIR;
			}

			$file_pathes = array(
				'jcf/config.json', // starting from v3.1.
				'jcf-settings/jcf_settings.json', // before v3.1.
			);
			foreach ( $file_pathes as $file ) {
				if ( ! is_file( "$base_folder/$file" ) ) {
					continue;
				}

				$settings = file_get_contents( "$base_folder/$file" );
				if ( empty( $settings ) || ! $settings = json_decode( $settings, true ) ) {
					continue;
				}

				$settings = (array) $settings;

				// ver >= 3.0 has key 'fields'.
				if ( ! empty( $settings['fields'] ) ) {
					return $settings['fields'];
				}
				// ver < 3.0 has key 'field_settings'.
				if ( ! empty( $settings['field_settings'] ) ) {
					return $settings['field_settings'];
				}
			}
		}

		// we didn't find any fields.
		return false;
	}
}