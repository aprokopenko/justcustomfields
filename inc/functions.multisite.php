<?php

	/**
	 *	Save miltisite settings from the form
	 *	@param string $settings Multisite settings in present time
	 *	@return string New multisite settings
	 */
	function jcf_save_multisite_settings( $new_value ){
		$current_value = jcf_get_multisite_settings();
		$new_value = trim($new_value);

		if( $current_value ){
			$saved = update_site_option( 'jcf_multisite_setting', $new_value );
		}
		else{
			$saved = add_site_option( 'jcf_multisite_setting', $new_value );
		}
		
		if( $saved ){
			jcf_add_admin_notice('notice', __('<strong>MultiSite settings</strong> has been updated.', JCF_TEXTDOMAIN));
		}
		
		return $new_value;
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
		return JCF_CONF_MS_SITE;
	}

