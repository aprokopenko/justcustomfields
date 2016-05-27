<?php
/*
Plugin Name: Just Custom Fields for Wordpress
Plugin URI: http://justcoded.com/blog/just-custom-fields-for-wordpress-plugin/
Description: This plugin add ability to extend your Posts (and custom post types) with additional fields.
Tags: custom, fields, custom fields, meta, post meta, object meta, editor, custom gallery
Author: Alexander Prokopenko
Author URI: http://justcoded.com/
Version: 3.0b
*/

define('JCF_ROOT', dirname(__FILE__));
require_once( JCF_ROOT.'/core/Autoload.php' );
require_once( JCF_ROOT.'/functions/helpers.php' );

use jcf\core;
use jcf\controllers;

class JustCustomFields extends core\Singleton
{

	/**
	 * Plugin text domain for translations
	 */
	const TEXTDOMAIN = 'just-custom-fields';
	const VERSION = '2.300';

	/**
	 * Textual plugin name
	 *
	 * @var string
	 */
	public static $pluginName;

	/**
	 * Variable-style plugin name
	 *
	 * @var string
	 */
	public static $pluginSlug = 'just_custom_fields';

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
	 * protected constructor prevents creating another plugin instance with "new" operator
	 */
	protected function __construct()
	{
		// init plugin name and version
		self::$pluginName = __('Just Custom Fields', JustCustomFields::TEXTDOMAIN);
		self::$version = 3.000;

		// init features, which this plugin is created for
		$this->initControllers();
		$this->initFields();
	}

	/**
	 * Init all controllers to support post edit pages and admin configuration pages
	 */
	public function initControllers()
	{
		new controllers\PostTypeController();

		if ( !is_admin() ) return;

 		new controllers\AdminController();
		new controllers\SettingsController();
		new controllers\ImportExportController();
		new controllers\FieldsetController();
		new controllers\FieldController();
	}

	/**
	 * Init field components (field types, which can be added to post type)
	 */
	public function initFields()
	{
		$this->registerField( 'jcf\components\inputtext\Just_Field_InputText', true );
		$this->registerField( 'jcf\components\select\Just_Field_Select', true );
		$this->registerField( 'jcf\components\selectmultiple\Just_Field_SelectMultiple', true );
		$this->registerField( 'jcf\components\checkbox\Just_Field_Checkbox', true );
		$this->registerField( 'jcf\components\textarea\Just_Field_Textarea', true );
		$this->registerField( 'jcf\components\datepicker\Just_Field_DatePicker', true );
		$this->registerField( 'jcf\components\simplemedia\Just_Field_SimpleMedia', true );
		$this->registerField( 'jcf\components\table\Just_Field_Table', true );
		$this->registerField( 'jcf\components\collection\Just_Field_Collection' );
		$this->registerField( 'jcf\components\relatedcontent\Just_Field_RelatedContent' );
		
		/**
		 *	to add more fields with your custom plugin:
		 *	- add_action  'jcf_register_fields'
		 *	- include your components files
		 *	- run 
		 *  $jcf = new JustCustomFields();
		 *  $jcf->registerField('namespace\className', $collection_field = true|false);
		 */
		do_action( 'jcf_register_fields' );
	}

	/**
	 * register field component
	 *
	 * @param $class_name
	 * @param bool $collection_field
	 * @return bool
	 */
	public function registerField( $class_name, $collection_field = false )
	{
		if ( strpos($class_name, '\\') === FALSE ) $class_name = '\\' . $class_name;

		$field_obj = new $class_name();

		$field = array(
			'id_base' => $field_obj->idBase,
			'class' => $class_name,
			'title' => $field_obj->title,
			'collection_field' => $collection_field,
		);
		$this->_fields[$field_obj->idBase] = $field;
	}
	
	/**
	 *	return array of registered fields
	 */
	public function getFields( $collection_only = false )
	{
		if ( ! $collection_only )
			return $this->_fields;
		
		// filter by collection availability
		$collection_fields = array();
		foreach ($this->_fields as $f) {
			if ( !$f['collection_field'] ) continue;
			$collection_fields[] = $f;
		}
		
		return $collection_fields;
	}
	
	/**
	 * Field info (title, id_base, class)
	 * @param string $id_base
	 * @return array
	 */
	public function getFieldInfo($id_base)
	{
		if ( !empty($this->_fields[$id_base]) ) {
			return $this->_fields[$id_base];
		}
		return null;
	}

}

JustCustomFields::run();

