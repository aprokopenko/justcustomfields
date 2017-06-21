<?php

namespace jcf\core;

/**
 * SPL autoload registration for plugin to prevent using file includes
 */
class Autoload {

	/**
	 * Class contructor register SPL autoload callback function
	 */
	public function __construct() {
		spl_autoload_register( array( $this, 'loader' ) );
	}

	/**
	 * Search for the class by namespace path and include it if found.
	 *
	 * @param string $class_name Class name.
	 */
	public function loader( $class_name ) {
		$class_path = str_replace( '\\', '/', $class_name );

		/* check if this class is related to the plugin namespace. exit if not */
		if ( strpos( $class_path, 'jcf' ) !== 0 ) {
			return;
		}

		$path = preg_replace( '/^jcf\//', JCF_ROOT . '/', $class_path ) . '.php';

		if ( is_file( $path ) ) {
			require_once( $path );
		}
	}

}

new Autoload();
