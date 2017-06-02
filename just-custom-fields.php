<?php
/*
Plugin Name: Just Custom Fields for Wordpress
Plugin URI: http://justcoded.com/blog/just-custom-fields-for-wordpress-plugin/
Description: Turn WordPress into more powerful CMS by adding advanced and easy to use custom fields. Read the documentation on http://justcustomfields.com.
Tags: custom, fields, custom fields, meta, post meta, object meta, editor, custom gallery, collection, field group, metabox, fieldsets
Author: JustCoded / Alex Prokopenko
Author URI: http://justcoded.com/
Version: 3.2
*/
define('JCF_ROOT', dirname(__FILE__));
require_once( JCF_ROOT.'/core/Autoload.php' );
require_once( JCF_ROOT.'/functions/helpers.php' );

use jcf\core;
use jcf\controllers;

class JustCustomFields extends core\Singleton {

	/**
	 * Plugin text domain for translations
	 */
	const TEXTDOMAIN = 'just-custom-fields';
	const VERSION = '3.200';

	/**
	 * Textual plugin name
	 *
	 * @var string
	 */
	public static $plugin_name;

	/**
	 * Variable-style plugin name
	 *
	 * @var string
	 */
	public static $plugin_slug = 'just_custom_fields';

	/**
	 * Current plugin version
	 *
	 * @var float
	 */
	public static $version;

	/**
	 * Registered fields components
	 *
	 * @var array
	 */
	protected $_fields;

	/**
	 * Plugin main entry point
	 *
	 * Protected constructor prevents creating another plugin instance with "new" operator
	 */
	protected function __construct() {
		/* init plugin name and version */
		self::$plugin_name = __( 'Just Custom Fields', JustCustomFields::TEXTDOMAIN );
		self::$version     = self::VERSION;

		/* init features, which this plugin is created for */
		$this->initControllers();

		$this->initFields();
		add_action( 'plugins_loaded', array( $this, 'registerCustomComponents' ) );
	}

	/**
	 * Init all controllers to support post edit pages and admin configuration pages
	 */
	public function initControllers() {
		$loader = new core\PluginLoader();
		/* we use wp_doing_ajax to prevent version check under ajax */
		if ( ! wp_doing_ajax() && $loader->checkMigrationsAvailable() ) {
			new controllers\MigrateController();
			new controllers\AdminController();
		} else {
			new controllers\PostTypeController();

			if ( ! is_admin() ) {
				return;
			}

			new controllers\AdminController();
			new controllers\SettingsController();
			new controllers\ImportExportController();
			new controllers\FieldsetController();
			new controllers\FieldController();
		}
	}

	/**
	 * Init field components (field types, which can be added to post type)
	 */
	public function initFields() {
		$this->registerField( 'jcf\components\inputtext\JustField_InputText', true );
		$this->registerField( 'jcf\components\textarea\JustField_Textarea', true );
		$this->registerField( 'jcf\components\select\JustField_Select', true );
		$this->registerField( 'jcf\components\selectmultiple\JustField_SelectMultiple', true );
		$this->registerField( 'jcf\components\checkbox\JustField_Checkbox', true );
		$this->registerField( 'jcf\components\datepicker\JustField_DatePicker', true );
		$this->registerField( 'jcf\components\simplemedia\JustField_SimpleMedia', true );
		$this->registerField( 'jcf\components\collection\JustField_Collection' );
		$this->registerField( 'jcf\components\table\JustField_Table', true );
		$this->registerField( 'jcf\components\relatedcontent\JustField_RelatedContent', true );
		$this->registerField( 'jcf\components\googlemaps\JustField_GoogleMaps', true );
	}

	/**
	 * Launch hook to be able to register mode components from themes and other plugins
	 *
	 *	To add more field components with your custom code:
	 *	- add_action  'jcf_register_fields'
	 *	- include your components files
	 *	- run
	 *  $jcf = new \JustCustomFields();
	 *  $jcf->registerField('namespace\className', $collection_field = true|false);
	 */
	public function registerCustomComponents() {
		do_action( 'jcf_register_fields' );
	}

	/**
	* Register field component
	*
	* @param $class_name
	* @param bool $collection_field
	* @return bool
	*/
	public function registerField( $class_name, $collection_field = false ) {
		if ( strpos( $class_name, '\\' ) === false ) {
			$class_name = '\\' . $class_name;
		}

		$field_obj = new $class_name();

		$field = array(
			'id_base' => $field_obj->id_base,
			'class' => $class_name,
			'title' => $field_obj->title,
			'collection_field' => $collection_field,
		);
		$this->_fields[ $field_obj->id_base ] = $field;
	}

	/**
	 *	Return array of registered fields
	 *
	 * @param bool $collection_only
	 * @return array $collection_fields
	 */
	public function getFields( $collection_only = false ) {
		if ( ! $collection_only ) {
			return $this->_fields;
		}
		/* filter by collection availability */
		$collection_fields = array();
		foreach ( $this->_fields as $f ) {
			if ( ! $f['collection_field'] ) {
				continue;
			}
			$collection_fields[] = $f;
		}

		return $collection_fields;
	}

	/**
	 * Field info (title, id_base, class)
	 *
	 * @param string $id_base
	 * @return array
	 */
	public function getFieldInfo( $id_base ) {
		if ( ! empty( $this->_fields[ $id_base ] ) ) {
			return $this->_fields[ $id_base ];
		}
		return null;
	}

}

JustCustomFields::run();

