<?php

if ( !function_exists('pa') ) {
	function pa( $mixed, $stop = false ) {
		$ar = debug_backtrace(); $key = pathinfo($ar[0]['file']); $key = $key['basename'] . ':' . $ar[0]['line'];
		$print = array( $key => $mixed ); echo( '<pre>' . htmlentities(print_r($print, 1)) . '</pre>' );
		if ( $stop == 1 )
			exit();
	}
}

/**
 * Alias for plugins_url with pre-defined second parameter
 * Mostly used to set paths for assets
 *
 * @param string $path  asset or callback path
 * @return string
 */
function jcf_plugin_url( $path ) {
	return plugins_url($path, dirname(__FILE__));
}

/**
 * get registered post types
 * @param string $format
 * @return string 
 */
function jcf_get_post_types( $format = 'single' ) {

	$all_post_types = get_post_types(array( 'show_ui' => true ), 'object');

	$post_types = array();

	foreach ( $all_post_types as $key => $val ) {

		//we should exclude 'revision' and 'nav menu items'
		if ( $val == 'revision' || $val == 'nav_menu_item' )
			continue;

		$post_types[$key] = $val;
	}

	if ( isset($post_types['attachment']) ) {
		unset($post_types['attachment']);
	}

	return $post_types;
}

/**
 * get registered taxonomies
 * @param string $format
 * @return string
 */
function jcf_get_taxonomies( $format = 'names' ) {

	$all_taxonomies = get_taxonomies(array( 'show_ui' => true, 'public' => true ), $format);

	$taxonomies = array();

	foreach ( $all_taxonomies as $key => $val ) {
		$taxonomies[\jcf\core\JustField::POSTTYPE_KIND_PREFIX_TAXONOMY . $key] = $val;
	}

	return $taxonomies;
}

/**
 * Find the correct post type icon name
 *
 * @param array|object $post_type  object
 * @return string
 */
function jcf_get_post_type_icon( $post_type ) {
	if ( is_a($post_type, '\WP_Post_Type') ) {
		$icon = $post_type->menu_icon;
	} elseif ( is_a($post_type, '\WP_Taxonomy') ) {
		$icon = $post_type->hierarchical? 'dashicons-category' : 'dashicons-tag';
	}
	$post_type = (array)$post_type;

	$standard_post_types = array(
		'post' => 'dashicons-admin-post',
		'page' => 'dashicons-admin-page',
		'attachment' => 'dashicons-admin-media',
	);

	if ( empty($icon) && isset($standard_post_types[$post_type['name']]) ) {
		$icon = $standard_post_types[ $post_type['name'] ];
	}

	if ( empty($icon) ) {
		$icon = 'dashicons-admin-post';
	}

	return $icon;
}

/**
 * Similar to WP get_page_templates(), but searches more folder levels
 * Required to support _jmvt theme boilerplate
 *
 * @return array  updated page templates array
 */
function jcf_get_page_templates( $post_type = 'page' ) {
	$post_templates = wp_cache_get( 'jcf_post_templates_depth2', 'themes' );

	if ( ! is_array( $post_templates ) ) {
		$wp_theme = wp_get_theme();
		if ( $wp_theme->errors() && $wp_theme->errors()->get_error_codes() !== array( 'theme_parent_invalid' ) ) {
			return array();
		}

		$files = $wp_theme->get_files( 'php', 2 );
		if ( $wp_theme->parent() ) {
			$parent_files = $wp_theme->parent()->get_files( 'php', 2 );
			$files = array_merge( $parent_files, $files );
		}

		foreach ($files as $file => $full_path) {
			if ( $full_path === __FILE__
			    || preg_match( '#^(core|inc|app|functions.php)/#', $file )
				|| !preg_match( '|Template Name:(.*)$|mi', file_get_contents($full_path), $header )
			) {
				continue;
			}

			$types = array('page');
			if (preg_match('|Template Post Type:(.*)$|mi', file_get_contents($full_path), $type)) {
				$types = explode(',', _cleanup_header_comment($type[1]));
			}

			foreach ($types as $type) {
				$type = sanitize_key($type);
				if ( !isset($post_templates[$type]) ) {
					$post_templates[$type] = array();
				}

				if ( $post_type == $type ) {
					$post_templates[$type][$file] = _cleanup_header_comment($header[1]);
				}
			}
		}

		wp_cache_add( 'jcf_post_templates_depth2', $deep_templates, 'themes', 1800 );
	}

	if ( !empty($post_templates[$post_type]) ) {
		$page_templates = array_merge(array('default' => 'Default'), $post_templates[$post_type]);
	}

	return $page_templates;
}

/**
 * javascript localization
 * @return array
 */
function jcf_get_language_strings() {
	global $wp_version;

	$strings = array(
		'hi' => __('Hello there', \JustCustomFields::TEXTDOMAIN),
		'edit' => __('Edit', \JustCustomFields::TEXTDOMAIN),
		'delete' => __('Delete', \JustCustomFields::TEXTDOMAIN),
		'confirm_field_delete' => __('Are you sure you want to delete selected field?', \JustCustomFields::TEXTDOMAIN),
		'confirm_fieldset_delete' => __("Are you sure you want to delete the fieldset?\nAll fields will be also deleted!", \JustCustomFields::TEXTDOMAIN),
		'err_fieldset_visibility_invalid' => __('You should select Taxonomy term to continue.', \JustCustomFields::TEXTDOMAIN),
		'err_fieldset_visibility_invalid_page' => __('You should select Taxonomy term or Page template to continue.', \JustCustomFields::TEXTDOMAIN),
		'select_image' => __('Select', \JustCustomFields::TEXTDOMAIN),
		'update_image' => __('Update Image', \JustCustomFields::TEXTDOMAIN),
		'update_file' => __('Update File', \JustCustomFields::TEXTDOMAIN),
		'yes' => __('Yes', \JustCustomFields::TEXTDOMAIN),
		'no' => __('No', \JustCustomFields::TEXTDOMAIN),
		'no_term' => __('The term doesn\'t exist', \JustCustomFields::TEXTDOMAIN),
		'no_templates' => __('The template doesn\'t exist', \JustCustomFields::TEXTDOMAIN),
		'slug' => __('Slug', \JustCustomFields::TEXTDOMAIN),
		'type' => __('Type', \JustCustomFields::TEXTDOMAIN),
		'enabled' => __('Enabled', \JustCustomFields::TEXTDOMAIN),
		'wp_version' => $wp_version,
	);
	$strings = apply_filters('jcf_localize_script_strings', $strings);
	return $strings;
}

/**
 * 	Json formater
 * 	@param string $json Data of settings for fields
 * 	@return string Return formated json string with settings for fields
 */
function jcf_format_json( $json ) {
	$result = '';
	$level = 0;
	$in_quotes = false;
	$in_escape = false;
	$ends_line_level = NULL;
	$json_length = strlen( $json );

	for( $i = 0; $i < $json_length; $i++ ) {
		$char = $json[$i];
		$new_line_level = NULL;
		$post = "";
		if( $ends_line_level !== NULL ) {
			$new_line_level = $ends_line_level;
			$ends_line_level = NULL;
		}
		if ( $in_escape ) {
			$in_escape = false;
		} else if( $char === '"' ) {
			$in_quotes = !$in_quotes;
		} else if( ! $in_quotes ) {
			switch( $char ) {
				case '}': case ']':
				$level--;
				$ends_line_level = NULL;
				$new_line_level = $level;
				break;

				case '{': case '[':
				$level++;
				case ',':
					$ends_line_level = $level;
					break;

				case ':':
					$post = " ";
					break;

				case " ": case "\t": case "\n": case "\r":
				$char = "";
				$ends_line_level = $new_line_level;
				$new_line_level = NULL;
				break;
			}
		} else if ( $char === '\\' ) {
			$in_escape = true;
		}
		if( $new_line_level !== NULL ) {
			$result .= "\n".str_repeat( "\t", $new_line_level );
		}
		$result .= $char.$post;
	}

	return $result;
}

/**
 * Set permisiions for file
 * @param string $dir Parent directory path
 * @param string $filename File path
 * @return boolean
 */
function jcf_set_chmod( $filename ) {
	$dir_perms = fileperms(dirname($filename));
	if ( @chmod($filename, $dir_perms) ) {
		return true;
	}
	else {
		return false;
	}
}

/**
 * print image with loader
 * @deprecated
 */
function jcf_print_loader_img() {
	return '';
}

/**
 * safety print html tag attributes
 *
 * @param array $attributes
 * @return string|boolean
 */
function jcf_html_attributes( array $attributes = array(), $echo = true ) {
	if( empty($attributes) ) return '';

	$html = array();
	foreach ( $attributes as $attr => $value ) {
		$html[] = $attr . '="' . esc_attr($value) . '"';
	}
	$html = ' ' . implode(' ', $html);

	if ( $echo )
		echo $html;
	else
		return $html;
}

/**
 * Generate starting HTML tag
 *
 * @param string $tag
 * @param array $attributes
 * @return string
 */
function jcf_html_tag( $tag, array $attributes = array() ) {
	$html_attributes = jcf_html_attributes($attributes, false);
	$html = "<{$tag}{$html_attributes}>";
	return $html;
}

/**
 * Generate input type hidden
 *
 * @param array $attributes
 * @return string
 */
function jcf_html_hidden_input( array $attributes = array() ) {
	$attributes['type'] = 'hidden';
	return jcf_html_tag('input', $attributes);
}

/**
 * Generate input type checkbox
 *
 * @param array $attributes
 * @return string
 */
function jcf_html_checkbox( array $attributes = array() ) {
	$attributes['type'] = 'checkbox';
	return jcf_html_tag('input', $attributes);
}

/**
 * Special function which wraps HTML special characters inside the textarea
 *
 * @param string $value
 * @return string
 */
function jcf_esc_textarea( $value ) {
	$safe_text = htmlspecialchars( $value, ENT_NOQUOTES, get_option( 'blog_charset' ) );
	return $safe_text;
}
