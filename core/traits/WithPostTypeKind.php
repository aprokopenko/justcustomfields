<?php

namespace jcf\core\traits;

use jcf\core\JustField;

trait WithPostTypeKind {

	/**
	 * Check what post type kind of given post type ID
	 *
	 * @param string $post_type Post type ID or Prefixed taxonomy ID
	 * @return string
	 */
	public static function getPostTypeKind( $post_type )
	{
		$kind = JustField::POSTTYPE_KIND_POST;
		if ( 0 === strpos($post_type, JustField::POSTTYPE_KIND_PREFIX_TAXONOMY) ) {
			$kind = JustField::POSTTYPE_KIND_TAXONOMY;
		}
		return $kind;
	}

}