<?php
/**
 * dFramework
 *
 * The simplest PHP framework for beginners
 * Copyright (c) 2019 - 2021, Dimtrov Sarl
 * This content is released under the Mozilla Public License 2 (MPL-2.0)
 *
 * @package	    dFramework
 * @author	    Dimitri Sitchet Tomkeu <devcode.dst@gmail.com>
 * @copyright	Copyright (c) 2019 - 2021, Dimtrov Sarl. (https://dimtrov.hebfree.org)
 * @copyright	Copyright (c) 2019 - 2021, Dimitri Sitchet Tomkeu. (https://www.facebook.com/dimtrovich)
 * @license	    https://opensource.org/licenses/MPL-2.0 MPL-2.0 License
 * @homepage    https://dimtrov.hebfree.org/works/dframework
 * @version     3.3.0
 */

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * dFramework AuthMiddleware
 *
 * This is an example of authentication middlewares. It check if user is authenticate
 *
 * @author		Dimitri Sitchet Tomkeu <devcode.dst@gmail.com>
 * @since       3.3.0
 * @file        /app/middlewares/AuthMiddleware.php
 */
class AuthMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler) : ResponseInterface
    {
		if (!preg_match('#login#', $request->url))
		{
			$auth = session('auth');

			if (empty($auth))
			{
				return single_service('response')->withHeader('location', link_to('login'));
			}
			/**
			 * @example
			 *
			 * we can also check that the session user still exists in our database and define a request attribute to use it later
			 *
			 * $user = UsersEntity::find($auth['idUser']);
			 * if (!empty($user))
			 * {
			 * 		return single_service('response')->withHeader('location', link_to('login'));
			 * }
			 * else if (1 != $user->activate)
			 * {
			 * 		// do something if the user account is not activate
			 * }
			 * else
			 * {
			 * 		$request = $request->withAttribute('user', $user);
			 * }
			*/
		}

        return $handler->handle($request);
    }
}
