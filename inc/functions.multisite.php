<?php

	/**
	 *	Save miltisite settings from the form
	 *	@param string $settings Multisite settings in present time
	 *	@return string New multisite settings
	 */
	function jcf_save_multisite_settings($settings){
		$new_multisite_setting =  trim($_POST['jcf_multisite_setting']);

		if( $settings )
		{
			$save_settings = update_site_option( 'jcf_multisite_setting', $new_multisite_setting );
		}
		else
		{
			$save_settings = add_site_option( 'jcf_multisite_setting', $new_multisite_setting );
		}
		$notice = $save_settings ? array('notice' => '<strong>Multisite setting</strong> has changed') : array();
		do_action('admin_notices', $notice);
		return $new_multisite_setting;
	}

	/**
	 *	Get multisite settings
	 *	@return string Return multisite settings
	 */
	function jcf_get_multisite_settings(){
		if( MULTISITE && $multisite_setting = get_site_option('jcf_multisite_setting') )
		{
			return $multisite_setting;
		}
		return 'site';
	}

