<?php

namespace jcf\core;

use jcf\models\Settings;

abstract class Migration
{
	const FIELDS_KEY = 'fields';
	const FIELDSETS_KEY = 'fieldsets';

	const MODE_TEST = 'test';
	const MODE_UPDATE = 'update';

	/**
	 * @var array
	 */
	protected $data;

	/**
	 * @var string  test|update
	 */
	protected $mode;

	/**
	 * @var boolean
	 */
	protected $updated;

	/**
	 * @var string
	 */
	protected $_data_source;

	/**
	 * @var string
	 */
	protected $_network_mode;


	/**
	 * Migration constructor.
	 * Init main settings, which were similar to all versions
	 */
	public function __construct()
	{
		$this->_data_source = Settings::getDataSourceType();
		$this->_network_mode = Settings::getNetworkMode();
	}

	/**
	 * This method read Data in a unique way for this particular version
	 *
	 * @return void
	 */
	abstract protected function readData();

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
	protected function cleanup(){}

	/**
	 * Compare current active data source with parameter
	 *
	 * @param string $data_source
	 *
	 * @return bool
	 */
	public function isDataSource( $data_source )
	{
		return $this->_data_source === $data_source;
	}

	/**
	 * Check if migration run in test mode (no need to cleanup old settings)
	 *
	 * @return bool
	 */
	public function isTestMode()
	{
		return $this->mode !== self::MODE_UPDATE;
	}

	/**
	 * Run compatibility data test
	 *
	 * @param array|null $data
	 *
	 * @return array
	 */
	public function runTest( $data )
	{
		$this->setData($data);

		return $this->test();
	}

	/**
	 * Update data to match new format
	 *
	 * @param array|null $data
	 * @param string     $mode
	 *
	 * @return array
	 */
	public function runUpdate( $data, $mode = 'test' )
	{
		$this->setData($data);
		$this->mode = ($mode == self::MODE_UPDATE) ? self::MODE_UPDATE : self::MODE_TEST;

		$this->updated = $this->update();

		return $this->data;
	}

	/**
	 * Run clean up of old data after update
	 */
	public function runCleanup()
	{
		if ( $this->updated ) {
			$this->cleanup();
		}
	}

	/**
	 * Set data from input or read data from settings if input parameter is empty
	 *
	 * @param array|null $data
	 *
	 * @return array
	 */
	public function setData( $data )
	{
		if ( ! empty($data) ) {
			$this->data = $data;
		} else {
			$this->readData();
		}
	}

	/**
	 * Read DB option based on settings mode
	 *
	 * @param string $key
	 *
	 * @return mixed
	 */
	public function readDB( $key )
	{
		return ($this->_network_mode == Settings::CONF_MS_NETWORK) ? get_site_option($key) : get_option($key);
	}

	/**
	 * Prepend path with root folder based on FS setting and read file
	 *
	 * @param string $path
	 *
	 * @return string
	 */
	public function readFS( $path )
	{
		$root_folder = $this->getFilesRoot();

		$file = $root_folder . '/' . ltrim($path, '/');
		if ( is_file($file) ) {
			return file_get_contents($file);
		}

		return null;
	}

	/**
	 * Read DB option based on settings mode
	 *
	 * @param string $key
	 *
	 * @return mixed
	 */
	public function cleanDB( $key )
	{
		return ($this->_network_mode == Settings::CONF_MS_NETWORK) ? delete_site_option($key) : delete_option($key);
	}

	/**
	 * Return correct files root
	 *
	 * @return string
	 */
	public function getFilesRoot()
	{
		$root_folder = ($this->_data_source == Settings::CONF_SOURCE_FS_THEME) ? get_stylesheet_directory() : WP_CONTENT_DIR;
		return $root_folder;
	}
}

