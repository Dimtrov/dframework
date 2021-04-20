<?php
/**
 * dFramework
 *
 * The simplest PHP framework for beginners
 * Copyright (c) 2019 - 2021, Dimtrov Lab's
 * This content is released under the Mozilla Public License 2 (MPL-2.0)
 *
 * @package	    dFramework
 * @author	    Dimitri Sitchet Tomkeu <dev.dst@gmail.com>
 * @copyright	Copyright (c) 2019 - 2021, Dimtrov Lab's. (https://dimtrov.hebfree.org)
 * @copyright	Copyright (c) 2019 - 2021, Dimitri Sitchet Tomkeu. (https://www.facebook.com/dimtrovich)
 * @license	    https://opensource.org/licenses/MPL-2.0 MPL-2.0 License
 * @homepage    https://dimtrov.hebfree.org/works/dframework
 * @version     3.2.3
 */

namespace dFramework\core\http;

use dFramework\core\exception\RouterException;
use dFramework\core\loader\Service;

/**
 * 
 * Handle a redirect response
 *
 * @package		dFramework
 * @subpackage	Core
 * @category    Http
 * @author		Dimitri Sitchet Tomkeu <dev.dst@gmail.com>
 * @link		https://dimtrov.hebfree.org/docs/dframework/api/
 * @since       3.2.3
 * @credit      CodeIgniter 4.0
 * @file        /system/core/http/Redirection.php
 */
class Redirection extends Response
{
    /**
	 * Sets the URI to redirect to and, optionally, the HTTP status code to use.
	 * If no code is provided it will be automatically determined.
	 *
	 * @param string       $uri    The URI to redirect to
	 * @param integer|null $code   HTTP status code
	 * @param string       $method
	 *
	 * @return $this
	 */
	public function to(string $uri, int $code = null, string $method = 'auto')
	{
		// If it appears to be a relative URL, then convert to full URL
		// for better security.
		if (strpos($uri, 'http') !== 0)
		{
			$url = current_url(true)->resolveRelativeURI($uri);
			$uri = (string) $url;
		}
		return $this->redirect($uri, $method, $code);
    }
    
    /**
	 * Sets the URI to redirect to but as a reverse-routed or named route
	 * instead of a raw URI.
	 *
	 * @param string       $route
	 * @param array        $params
	 * @param integer|null $code
	 * @param string       $method
	 *
	 * @return $this
	 */
	public function route(string $route, array $params = [], int $code = 302, string $method = 'auto')
	{
		$routes = Service::routes(true);

		$route = $routes->reverseRoute($route, ...$params);

		if (! $route)
		{
            RouterException::except('Invalid Redirect route', $route.' route cannot be found while reverse-routing.');
		}

		return $this->redirect(site_url($route), $method, $code);
    }
    
    /**
	 * Helper function to return to previous page.
	 *
	 * Example:
	 *  return redirect()->back();
	 *
	 * @param integer|null $code
	 * @param string       $method
	 *
	 * @return $this
	 */
	public function back(int $code = null, string $method = 'auto')
	{
		return $this->redirect(previous_url(), $method, $code);
    }
}
