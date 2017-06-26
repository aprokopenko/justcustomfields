<?php

namespace jcf\core;

/**
 * Main Model
 */
class Model {
	/**
	 * Errors
	 *
	 * @var array
	 */
	protected $_errors;

	/**
	 * Messages
	 *
	 * @var array
	 */
	protected $_messages;

	/**
	 * Message templates
	 *
	 * @var array
	 */
	protected $_msg_tpls = null;

	/**
	 * Data layers
	 *
	 * @var \jcf\models\DataLayer
	 */
	protected $_dl;

	/**
	 * Model constructor.
	 * generate DataLayer object (file system or DB settings storage)
	 */
	public function __construct() {
		$this->_dl = DataLayerFactory::create();
	}

	/**
	 * Special method to return pre-defined error messages in specific model.
	 * You can use keys to easily set errors by keys
	 *
	 * @return array
	 */
	public function message_templates() {
		return array();
	}

	/**
	 * Set errors
	 *
	 * @param string $error Error.
	 */
	public function add_error( $error ) {
		if ( is_null( $this->_msg_tpls ) ) {
			$this->_msg_tpls = $this->message_templates();
		}

		if ( isset( $this->_msg_tpls[ $error ] ) ) {
			$error = $this->_msg_tpls[ $error ];
		}
		$this->_errors[] = $error;

		add_action( 'jcf_print_admin_notice', array( $this, 'print_messages' ) );
	}

	/**
	 * Set messages
	 *
	 * @param string $message Message.
	 */
	public function add_message( $message ) {
		if ( is_null( $this->_msg_tpls ) ) {
			$this->_msg_tpls = $this->message_templates();
		}

		if ( isset( $this->_msg_tpls[ $message ] ) ) {
			$message = $this->_msg_tpls[ $message ];
		}
		$this->_messages[] = $message;

		add_action( 'jcf_print_admin_notice', array( $this, 'print_messages' ) );
	}

	/**
	 * Get errors
	 */
	public function get_errors() {
		return $this->_errors;
	}

	/**
	 * Check errors
	 */
	public function has_errors() {
		return ! empty( $this->_errors );
	}

	/**
	 * Render notices
	 *
	 * @param array $args Args.
	 *
	 * @return html
	 */
	public function print_messages( $args = array() ) {
		if ( empty( $this->_messages ) && empty( $this->_errors ) ) {
			return;
		}

		global $wp_version;
		include( JCF_ROOT . '/views/_notices.php' );
	}

	/**
	 * Set request params
	 *
	 * @param array $params Params.
	 *
	 * @return boolean
	 */
	public function load( $params ) {
		if ( ! empty( $params ) ) {
			$this->set_attributes( $params );

			return true;
		}

		return false;
	}

	/**
	 * Set attributes to model
	 *
	 * @param type $params Params.
	 */
	public function set_attributes( $params ) {
		$self = get_class( $this );
		foreach ( $params as $key => $value ) {
			if ( property_exists( $self, $key ) ) {
				$this->$key = is_array( $value ) ? $value : strip_tags( trim( $value ) );
			}
		}
	}

	/**
	 * Get current DataLayer storage version
	 *
	 * @return float
	 */
	public function get_storage_version() {
		return $this->_dl->get_storage_version();
	}
}
