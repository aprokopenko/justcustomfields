<?php

namespace jcf\core;

/**
 * 	Main controller
 */
class Controller
{

	/**
	 * Controller constructor.
	 */
	public function __construct()
	{
		
	}

	/**
	 * Function for render views
	 * @param string $template   file name to be rendered
	 * @param array $params     array of variables to be passed to the view file
	 * @return boolean
	 */
	protected function _render( $template, $params = array() )
	{
		extract($params);
		include( JCF_ROOT . '/views/' . $template . '.php' );

		return true;
	}

	/**
	 * Function for render views inside AJAX request
	 * Echo rendered content directly in output buffer
	 *
	 * @param string $template   file name to be rendered
	 * @param string $format    json|html   control which header content type should be sent
	 * @param array $params     array of variables to be passed to the view file
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
