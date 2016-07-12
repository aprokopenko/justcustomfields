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

	return $post_types;
}

/**
 * Find the correct post type icon name
 *
 * @param array|object $post_type  object
 * @return string
 */
function jcf_get_post_type_icon( $post_type ) {
	$post_type = (array)$post_type;
	$icon = $post_type['menu_icon'];

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
function jcf_get_page_templates( $page_templates = array() ) {
	$deep_templates = wp_cache_get( 'jcf_page_deep2_templates', 'themes' );
	if ( ! is_array( $deep_templates ) ) {
		$wp_theme = wp_get_theme();
		$files = $wp_theme->get_files('php', 2);

		foreach ( $files as $file => $full_path ) {
			if ( ! preg_match( '|Template\sName:(.*)$|mi', file_get_contents( $full_path ), $header ) )
				continue;
			$deep_templates[ $file ] = _cleanup_header_comment( $header[1] );
		}

		wp_cache_add( 'jcf_page_deep2_templates', $deep_templates, 'themes' );
	}

	$page_templates = array_merge(array('default' => 'Default'), $page_templates, $deep_templates);
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
	$tabcount = 0;
	$result = '';
	$inquote = false;
	$ignorenext = false;
	$tab = "\t";
	$newline = "\n";

	for ( $i = 0; $i < strlen($json); $i++ ) {
		$char = $json[$i];
		if ( $ignorenext ) {
			$result .= $char;
			$ignorenext = false;
		}
		else {
			switch ( $char )
			{
				case '{':
					$tabcount++;
					$result .= $char . $newline . str_repeat($tab, $tabcount);
					break;
				case '}':
					$tabcount--;
					$result = trim($result) . $newline . str_repeat($tab, $tabcount) . $char;
					break;
				case ',':
					if ( $json[$i + 1] != ' ' ) {
						$result .= $char . $newline . str_repeat($tab, $tabcount);
						break;
					}
				case '"':
					$inquote = !$inquote;
					$result .= $char;
					break;
				case '\\':
					if ( $inquote )
						$ignorenext = true;
					$result .= $char;
					break;
				default:
					$result .= $char;
			}
		}
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