<?php

/**
 *	Keep settings in the file of theme
 *	@param string $file		Path to file where to save fields settings
 *	@param string $settings_source		Source setting
 *	@return boolean			Operation status
 */
function jcf_clone_db_settings_to_fs($file, $settings_source){
	$jcf_settings = jcf_get_all_settings_from_db();
	$dir = dirname($file);
	
	if( 
		// check that dir exists (if not try to create) and is writable
		 ( (!is_dir($dir) && ! wp_mkdir_p($dir)) || !is_writable($dir) )
		// try to write settings
		|| ! $saved = jcf_save_all_settings_in_file($jcf_settings, $settings_source)
	)
	{
		// if fail - print error
		$msg = array('error', sprintf( __('<strong>Settings storage update FAILED!</strong>. Please check that directory exists and writable: %s', JCF_TEXTDOMAIN), dirname($dir) ));
		jcf_add_admin_notice($msg[0], $msg[1]);
	}
	else{
		// we have another notification after this func called
		//$msg = array('notice', __('<strong>Fields settings</strong> successfully copied!', JCF_TEXTDOMAIN));
	}
	
	return $saved;
}

/**
 *	Get all settings from file
 *	@return array Array with fields settings from config file
 */
function jcf_get_all_settings_from_file(){
	$filename = jcf_get_settings_file_path();
	if (file_exists($filename)) {
		return jcf_get_settings_from_file($filename);
	}
	else{
		return false;
	}
}

/**
 *	Get settings from file
 *	@param string $uploadfile File name
 *	@return array Array with fields settings from file
 */
function jcf_get_settings_from_file($uploadfile){
	$content = file_get_contents($uploadfile);
	$data = json_decode($content, true);
	return $data;
}

/**
 *	Save settings to file
 *	@param array $data				Array with fields settings
 *	@param string $settings_source	Saving method
 *	@return boolean  TRUE if file has been saved
 */
function jcf_save_all_settings_in_file($data, $settings_source = ''){
	$data = jcf_format_json(json_encode($data));

	if( empty($settings_source) )
		$settings_source = jcf_get_read_settings();
	
	$file = jcf_get_settings_file_path( $settings_source );
	$dir = dirname($file);
	
	// trying to create dir
	if( (!is_dir($dir) && ! wp_mkdir_p($dir)) || !is_writable($dir) ){
		return false;
	}
	
	if( !empty($dir) ){
		$content = $data . "\r\n";
		if( $fp = fopen($file, 'w') ){
			fwrite($fp, $content);
			fclose($fp);
			jcf_set_chmod($file);
			return true;
		}
	}

	return false;
}

/**
 *	Get read sttings
 *	@return string Return read method from file or database
 */
function jcf_get_read_settings(){
	return get_site_option('jcf_read_settings', JCF_CONF_SOURCE_DB);
}

/**
 *	Get file name for all settings
 *  @param string	$jcf_read_settings		Settings storage config
 *	@return string|boolean Return path to file of settings for all fields and false if read medhod from db
 */
function jcf_get_settings_file_path( $jcf_read_settings = null ){
	if( is_null($jcf_read_settings) )
		$jcf_read_settings = jcf_get_read_settings();
	
	if( !empty($jcf_read_settings) && ($jcf_read_settings == JCF_CONF_SOURCE_FS_THEME || $jcf_read_settings == JCF_CONF_SOURCE_FS_GLOBAL) ){
		return ($jcf_read_settings == JCF_CONF_SOURCE_FS_THEME)? get_template_directory() . '/jcf-settings/jcf_settings.json' : get_home_path() . 'wp-content/jcf-settings/jcf_settings.json';
	}
	return false;
}

/**
 *	Function for update saving method
 *	@return string Return read method from file or database
 */
function jcf_update_read_settings(){
	$current_value = jcf_get_read_settings();
	$new_value = $_POST['jcf_read_settings'];

	if( MULTISITE && ($_POST['jcf_multisite_setting'] != JCF_CONF_MS_NETWORK && $new_value == JCF_CONF_SOURCE_FS_GLOBAL) ){
		jcf_add_admin_notice('error', __('<strong>Settings storage update FAILED!</strong>. Your MultiSite Settings do not allow to set global storage in FileSystem', JCF_TEXTDOMAIN));
		return $current_value;
	}
	else{
		if( !empty($current_value) ){
			// if need to copy settings from db to FS
			if( !empty($_POST['jcf_keep_settings']) ){
				if( in_array($new_value, array(JCF_CONF_SOURCE_FS_GLOBAL, JCF_CONF_SOURCE_FS_THEME)) ){
					$file = jcf_get_settings_file_path( $new_value );
					if( jcf_clone_db_settings_to_fs( $file, $new_value ) ){
						jcf_add_admin_notice('notice', __('<strong>Database settings has been imported</strong> to file system.', JCF_TEXTDOMAIN));

						$saved = update_site_option('jcf_read_settings', $new_value);
					} 
					else {
						jcf_add_admin_notice('error', __('<strong>Database settings import to file system FAILED!</strong>. Revert settings storage to Database.', JCF_TEXTDOMAIN));
					}
				}
			}
			else{
				$saved = update_site_option('jcf_read_settings', $new_value);
			}
		}
		else{
			$saved = add_site_option('jcf_read_settings', $new_value);
		}
		
		if( $saved )
			jcf_add_admin_notice('notice', __('<strong>Settings storage</strong> configurations has been updated.', JCF_TEXTDOMAIN));

		return $saved ? $new_value : $current_value;
	}
}

/**
 *	Json formater
 *	@param string $json Data of settings for fields
 *	@return string Return formated json string with settings for fields
 */
function jcf_format_json($json){
	$tabcount = 0;
	$result = '';
	$inquote = false;
	$ignorenext = false;
	$tab = "\t";
	$newline = "\n";

	for( $i = 0; $i < strlen($json); $i++ ){
		$char = $json[$i];
		if( $ignorenext ){
			$result .= $char;
			$ignorenext = false;
		}
		else {
			switch( $char ) {
				case '{':
					$tabcount++;
					$result .= $char . $newline . str_repeat($tab, $tabcount);
					break;
				case '}':
					$tabcount--;
					$result = trim($result) . $newline . str_repeat($tab, $tabcount) . $char;
					break;
				case ',':
					if( $json[$i + 1] != ' ' ){
						$result .= $char . $newline . str_repeat($tab, $tabcount);
						break;
					}
				case '"':
					$inquote = !$inquote;
					$result .= $char;
					break;
				case '\\':
					if ($inquote) $ignorenext = true;
					$result .= $char;
					break;
				default:
					$result .= $char;
			}
		}
	}
	return $result;
}