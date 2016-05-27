<?php

namespace jcf\core;

/**
 * 	Main Model
 */
class Model
{
	protected $_errors;
	protected $_messages;
	protected $_dL;

	public function __construct()
	{
		$this->_dL = DataLayerFactory::create();
	}

	/**
	 * Set errors
	 * @param string $error
	 */
	public function addError( $error )
	{
		$this->_errors[] = $error;

		add_action('jcf_print_admin_notice', array( $this, 'printMessages' ));
	}

	/**
	 * Set messages
	 * @param string $message
	 */
	public function addMessage( $message )
	{
		$this->_messages[] = $message;

		add_action('jcf_print_admin_notice', array( $this, 'printMessages' ));
	}

	public function getErrors()
	{
		return $this->_errors;
	}

	/**
	 * Check errors
	 */
	public function hasErrors()
	{
		return !empty($this->_errors);
	}

	/**
	 * Render notices 
	 * @param array $args
	 * @return html
	 */
	public function printMessages( $args = array() )
	{
		if ( empty($this->_messages) && empty($this->_errors) )
			return;

		global $wp_version;
		include( JCF_ROOT . '/views/notices.tpl.php');
	}

	/**
	 * Set request params
	 * @param array $params
	 * @return boolean
	 */
	public function load( $params )
	{
		if ( !empty($params) ) {
			$this->setAttributes($params);
			return true;
		}
		return false;
	}

	/**
	 * Set attributes to model
	 * @param type $params
	 */
	public function setAttributes( $params )
	{
		$self = get_class($this);
		foreach ( $params as $key => $value ) {
			if ( property_exists($self, $key) )
				$this->$key = is_array($value) ? $value : strip_tags(trim($value));
		}
	}

}
