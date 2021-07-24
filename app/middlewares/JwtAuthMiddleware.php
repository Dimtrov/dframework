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

use dFramework\core\loader\Service;
use dFramework\core\output\Format;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use dFramework\core\utilities\Jwt;

/**
 * dFramework JwtAuthMiddleware
 *
 * This is an example of jwt-authentication middlewares. It check if user is authenticate from bearer token
 *
 * @author		Dimitri Sitchet Tomkeu <devcode.dst@gmail.com>
 * @since       3.3.0
 * @file        /app/middlewares/JwtAuthMiddleware.php
 */
class JwtAuthMiddleware implements MiddlewareInterface
{
    /**
     * @var array
     */
    private $config;

    /**
     * @var \dFramework\core\http\Response
     */
    private $response;

	/**
	 * @var array
	 */
	private $contentType = [
        'json'       => 'application/json',
        'array'      => 'application/json',
        'csv'        => 'application/csv',
    // 'html'       => 'text/html',
        'jsonp'      => 'application/javascript',
        'php'        => 'text/plain',
        'serialized' => 'application/vnd.php.serialized',
        'xml'        => 'application/xml',
    ];

    /**
     * Initialize middleware resources
     */
    public function __construct()
    {
        $this->response = Service::response(true);
        $this->config = config('rest');
    }

    /**
     * Process working
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler) : ResponseInterface
    {
		$token = Jwt::getToken();

        if (empty($token)) {
            return $this->respond(lang('rest.token_not_found', null, $this->config['language']));
        }
        try {
			$payload = Jwt::decode($token);

			/**
			 * @example
			 *
			 * we can also check that the token user still exists in our database and define a request attribute to use it later
			 *
			 * $user = UsersEntity::find($payload->idUser);
			 * if (empty($user))
			 * {
			 * 		return $this->respond(lang('rest.invalid_credentials', null, $this->config['language']));
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
        catch(Throwable $e) {
            return $this->respond($e->getMessage());
        }

        return $handler->handle($request);
    }

    /**
     * Send an error response
     */
    private function respond(string $message) : ResponseInterface
    {
        $format = strtolower($this->config['return_format']);

		if (!array_key_exists($format, $this->contentType))
		{
			$format = 'json';
		}

		if ($this->config['strict_mode'] === true)
		{
			$this->response = $this->response->withStatus(498);
		}

		$output = Format::factory([
			$this->config['status_field_name']  => false,
			$this->config['message_field_name'] => $message,
			$this->config['code_field_name']    => 498
		])->{'to'.ucfirst($format)}();

        $this->response = $this->response->withStringBody($output);
		$this->response = $this->response->withCharset(strtolower(config('general.charset') ?? 'utf-8'));
		$this->response = $this->response->withType($this->contentType[$format]);

		return $this->response;
    }
}
