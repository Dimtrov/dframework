<?php
/**
 * Class AppController
 *
 * AppController provides a convenient place for loading components
 * and performing functions that are needed by all your controllers.
 * Extend this class in any new controllers:
 *     class HomeController extends AppController
 *
 * For security be sure to declare any new methods as protected or private.
 */

use dFramework\core\Controller;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

class AppController extends Controller
{
	/**
	 * Constructor.
	 */
	public function initialize(ServerRequestInterface $request, ResponseInterface $response)
	{
		// Do Not Edit This Line
		parent::initialize($request, $response);
	
		// Preload any models, libraries, etc, here.
		//--------------------------------------------------------------------
		// E.g.:
		// $this->input = service('input');
	}
}
