<?php

namespace jcf\core;

class Singleton
{
	/**
	 * Refers to a single instance of this class. 
	 */
	protected static $instance = null;

	/**
	 * Returns the *Singleton* instance of this class.
	 *
	 * @return Singleton A single instance of this class.
	 */
	public static function getInstance()
	{
		if ( null === static::$instance ) {
			static::$instance = new static();
		}

		return static::$instance;
	}

	/**
	 * Alias for creating object of *Singleton* pattern
	 * 
	 * @return Singleton A single instance of this class.
	 */
	public static function run()
	{
		return static::getInstance();
	}

	/**
	 * Protected constructor to prevent creating a new instance of the
	 * *Singleton* via the `new` operator from outside of this class.
	 */
	protected function __construct()
	{
	}

	/**
	 * Private clone method to prevent cloning of the instance of the
	 * *Singleton* instance.
	 * 
	 * @return void
	 */
	private function __clone()
	{
	}

	/**
	 * Private unserialize method to prevent unserializing of the *Singleton*
	 * instance.
	 *
	 * @return void
	 */
	private function __wakeup()
	{
	}

}
