<?php

namespace jcf\core;

use jcf\models\Settings;

/**
 * Class Migration
 * Used as base class for all specific-version migrations
 * Contains generic functions to speed-up migrations development
 *
 * @package jcf\core
 */
abstract class Migration {
	const FIELDS_KEY = 'fields';
	const FIELDSETS_KEY = 'fieldsets';

	const MODE_TEST = 'test';
	const MODE_UPDATE = 'update';

	/**
	 * Data
	 *
	 * @var array
	 */
	protected $data;

	/**
	 * Mode
	 *
	 * @var string  test|update
	 */
	protected $mode;

	/**
	 * Updated
	 *
	 * @var boolean
	 */
	protected $updated;

	/**
	 * Data Source
	 *
	 * @var string
	 */
	protected $_data_source;

	/**
	 * Network Mode
	 *
	 * @var string
	 */
	protected $_network_mode;


	/**
	 * Migration constructor.
	 * Init main settings, which were similar to all versions
	 */
	public function __construct() {
		$this->_data_source  = Settings::get_data_source_type();
		$this->_network_mode = Settings::get_network_mode();
	}

	/**
	 * This method read Data in a unique way for this particular version
	 *
	 * @return void
	 */
	abstract protected function read_data();

	/**
	 * Test data compatibility with newer version
	 *
	 * @return array
	 */
	abstract protected function test();

	/**
	 * Update $data property to the latest version
	 *
	 * @return void
	 */
	abstract protected function update();

	/**
	 * Function to be called to remove old settings after update
	 */
	protected function cleanup() {
	}

	/**
	 * Compare current active data source with parameter
	 *
	 * @param string $data_source Data source.
	 *
	 * @return bool
	 */
	public function is_data_source( $data_source ) {
		return $this->_data_source === $data_source;
	}

	/**
	 * Check if migration run in test mode (no need to cleanup old settings)
	 *
	 * @return bool
	 */
	public function is_test_mode() {
		return self::MODE_UPDATE !== $this->mode;
	}

	/**
	 * Run compatibility data test
	 *
	 * @param array|null $data Data.
	 *
	 * @return array
	 */
	public function run_test( $data ) {
		$this->set_data( $data );

		return $this->test();
	}

	/**
	 * Update data to match new format
	 *
	 * @param array|null $data Data.
	 * @param string     $mode Mode.
	 *
	 * @return array
	 */
	public function run_update( $data, $mode = 'test' ) {
		$this->set_data( $data );
		$this->mode = ( self::MODE_UPDATE === $mode ) ? self::MODE_UPDATE : self::MODE_TEST;

		$this->updated = $this->update();

		return $this->data;
	}

	/**
	 * Run clean up of old data after update
	 */
	public function run_cleanup() {
		if ( $this->updated ) {
			$this->cleanup();
		}
	}

	/**
	 * Set data from input or read data from settings if input parameter is empty
	 *
	 * @param array|null $data Data.
	 */
	public function set_data( $data ) {
		if ( ! empty( $data ) ) {
			$this->data = $data;
		} else {
			$this->read_data();
		}
	}

	/**
	 * Read DB option based on settings mode
	 *
	 * @param string $key Key.
	 *
	 * @return mixed
	 */
	public function read_db( $key ) {
		return ( Settings::CONF_MS_NETWORK === $this->_network_mode ) ? get_site_option( $key ) : get_option( $key );
	}

	/**
	 * Prepend path with root folder based on FS setting and read file
	 *
	 * @param string $path Path.
	 *
	 * @return string
	 */
	public function read_fs( $path ) {
		$root_folder = $this->get_files_root();

		$file = $root_folder . '/' . ltrim( $path, '/' );
		if ( is_file( $file ) ) {
			return file_get_contents( $file );
		}

		return null;
	}

	/**
	 * Read DB option based on settings mode
	 *
	 * @param string $key Key.
	 *
	 * @return mixed
	 */
	public function clean_db( $key ) {
		return ( Settings::CONF_MS_NETWORK === $this->_network_mode ) ? delete_site_option( $key ) : delete_option( $key );
	}

	/**
	 * Return correct files root
	 *
	 * @return string
	 */
	public function get_files_root() {
		$root_folder = ( Settings::CONF_SOURCE_FS_THEME === $this->_data_source ) ? get_stylesheet_directory() : WP_CONTENT_DIR;

		return $root_folder;
	}


	/**
	 * Find postmeta by $old_slug, convert it with $formatter and save into $new_slug
	 *
	 * @param string $post_type Post type.
	 * @param string $old_slug Old slug.
	 * @param string $new_slug New slug.
	 * @param array  $formatter class object and method name.
	 */
	protected function import_postmeta( $post_type, $old_slug, $new_slug, $formatter ) {
		global $wpdb;

		$blog_ids = array( get_current_blog_id() );
		if ( is_multisite() && (
				( $this->is_data_source( Settings::CONF_SOURCE_DB ) && Settings::CONF_MS_NETWORK == Settings::get_network_mode() )
				|| $this->is_data_source( Settings::CONF_SOURCE_FS_GLOBAL )
			)
		) {
			// TODO: test multisite mode.
			$blog_ids = $wpdb->get_col( "SELECT blog_id FROM $wpdb->blogs" );
		}

		foreach ( $blog_ids as $blog_id ) {
			is_multisite() && switch_to_blog( $blog_id );

			// from and where part to be prepared.
			$from_where = $wpdb->prepare( "FROM $wpdb->postmeta as pm INNER JOIN $wpdb->posts as p ON p.id = pm.post_id WHERE meta_key = %s", $old_slug );

			// add batch in 100 rows to prevent memorey overload.
			$start    = 0;
			$per_page = 1000;
			$count    = $wpdb->get_var( "SELECT count(meta_id) $from_where" );

			while ( $start < $count ) {
				$postmeta_rows = $wpdb->get_results( "SELECT meta_id, post_id, meta_key, meta_value $from_where LIMIT $start, $per_page" );

				if ( ! empty( $postmeta_rows ) ) {
					// update meta one by one.
					foreach ( $postmeta_rows as $postmeta ) {
						$value = call_user_func_array( $formatter, array( $postmeta ) );
						update_post_meta( $postmeta->post_id, $new_slug, $value );
					}
				}

				$start += $per_page;
			}
		}

		is_multisite() && restore_current_blog();
	}
}

