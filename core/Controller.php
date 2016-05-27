<?php

namespace jcf\core;

/**
 * 	Main controller
 */
class Controller
{

	public function __construct()
	{
		
	}

	/**
	 * 	Function for render views
	 */
	protected function _render( $template, $params = array() )
	{
		extract($params);
		include( JCF_ROOT . '/views/' . $template . '.tpl.php' );
	}

	/**
	 * 	Function for render views by AJAX
	 */
	protected function _renderAjax( $template = null, $format, $params = array() )
	{
		if ( $format == 'json' ) {
			$responce = json_encode($params);
			header("Content-Type: application/json; charset=" . get_bloginfo('charset'));
		}
		else {
			header("Content-Type: text/html; charset=" . get_bloginfo('charset'));
			ob_start();
			$this->_render($template, $params);
			$responce = ob_get_clean();
		}
		echo $responce;
		exit();
	}

}
