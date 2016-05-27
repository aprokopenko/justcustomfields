<?php

namespace jcf\core;

use jcf\models;

class DataLayerFactory
{

	/**
	 * Create data layer object
	 * @param string $layer  database|fs_theme|fs_global / similar to models\Settings::CONF_SOURCE_*
	 * @return \jcf\models\DataLayer
	 */
	public static function create( $source_type = null )
	{
		if ( is_null($source_type) ) {
			$source_type = models\Settings::getDataSourceType();
		}
		$layer_class = ($source_type == models\Settings::CONF_SOURCE_DB) ? 'DBDataLayer' : 'FilesDataLayer';
		$layer_class = '\\jcf\\models\\' . $layer_class;
		return new $layer_class();
	}

}
