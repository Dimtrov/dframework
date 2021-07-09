<?php
/**
 * dFramework
 *
 * The simplest PHP framework for beginners
 * Copyright (c) 2019 - 2021, Dimtrov Lab's
 * This content is released under the Mozilla Public License 2 (MPL-2.0)
 *
 * @package	    dFramework
 * @author	    Dimitri Sitchet Tomkeu <devcode.dst@gmail.com>
 * @copyright	Copyright (c) 2019 - 2021, Dimtrov Lab's. (https://dimtrov.hebfree.org)
 * @copyright	Copyright (c) 2019 - 2021, Dimitri Sitchet Tomkeu. (https://www.facebook.com/dimtrovich)
 * @license	    https://opensource.org/licenses/MPL-2.0 MPL-2.0 License
 * @homepage    https://dimtrov.hebfree.org/works/dframework
 * @version     3.3.2
 */

namespace dFramework\middlewares;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use InvalidArgumentException;
use dFramework\core\http\ServerRequest;
use dFramework\core\http\Response;
use dFramework\core\utilities\Arr;
use dFramework\core\security\Csrf;
use dFramework\core\utilities\Date;
use dFramework\core\http\cookie\Cookie;
use dFramework\core\utilities\Password;
use dFramework\core\exception\Exception;

/**
 * CsrfProtection
 *
 * Provides CSRF protection & validation.
 *
 * This middleware adds a CSRF token to a cookie. The cookie value is compared to
 * request data, or the X-CSRF-Token header on each PATCH, POST,
 * PUT, or DELETE request.
 *
 * If the request data is missing or does not match the cookie data,
 * an InvalidCsrfTokenException will be raised.
 *
 * This middleware integrates with the FormHelper automatically and when
 * used together your forms will have CSRF tokens automatically added
 * when `$this->form->create(...)` is used in a view.
 *
 * @package		dFramework
 * @subpackage	Middlewares
 * @author		Dimitri Sitchet Tomkeu <devcode.dst@gmail.com>
 * @link		https://dimtrov.hebfree.org/docs/dframework/guide/Api.html
 * @since       3.3.2
 * @credit		CakePHP (Cake\Http\Middleware\CsrfProtectionMiddleware - https://cakephp.org)
 * @file        /system/middlewares/CsrfProtection.php
 */
class CsrfProtection
{
    /**
     * Default config for the CSRF handling.
     *
     *  - `cookieName` The name of the cookie to send.
     *  - `expiry` A strotime compatible value of how long the CSRF token should last.
     *    Defaults to browser session.
     *  - `secure` Whether or not the cookie will be set with the Secure flag. Defaults to false.
     *  - `httpOnly` Whether or not the cookie will be set with the HttpOnly flag. Defaults to false.
     *  - `field` The form field to check. Changing this will also require configuring
     *    FormHelper.
     *
     * @var array
     */
    protected $_defaultConfig = [
        'cookieName' => 'csrfToken',
        'expiry' => 0,
        'secure' => false,
        'httpOnly' => false,
        'field' => '_csrfToken',
    ];

    /**
     * Configuration
     *
     * @var array
     */
    protected $_config = [];

    /**
     * Callback for deciding whether or not to skip the token check for particular request.
     *
     * CSRF protection token check will be skipped if the callback returns `true`.
     *
     * @var callable|null
     */
    protected $whitelistCallback;

    /**
     * Constructor
     *
     * @param array $config Config options. See $_defaultConfig for valid keys.
     */
    public function __construct(array $config = [])
    {
        $this->_config = $config + $this->_defaultConfig;
    }

    /**
     * Checks and sets the CSRF token depending on the HTTP verb.
     *
     * @param ServerRequest $request The request.
     * @param Response $response The response.
     * @param callable $next Callback to invoke the next middleware.
     * @return Response A response
     */
    public function __invoke(ServerRequest $request, Response $response, $next)
    {
        if (
            $this->whitelistCallback !== null
            AND call_user_func($this->whitelistCallback, $request) === true
        )
		{
            return $next($request, $response);
        }

        $cookies = $request->getCookieParams();
        $cookieData = Arr::get($cookies, $this->_config['cookieName']);

        if (is_string($cookieData) AND strlen($cookieData) > 0)
		{
            $params = $request->getAttribute('params');
            $params['_csrfToken'] = $cookieData;
            $request = $request->withAttribute('params', $params);
        }

        $method = $request->getMethod();
        if ($method === 'GET' AND $cookieData === null)
		{
            $token = $this->_createToken();
            $request = $this->_addTokenToRequest($token, $request);
            $response = $this->_addTokenCookie($token, $request, $response);

            return $next($request, $response);
        }
        $request = $this->_validateAndUnsetTokenField($request);

        return $next($request, $response);
    }

    /**
     * Set callback for allowing to skip token check for particular request.
     *
     * The callback will receive request instance as argument and must return
     * `true` if you want to skip token check for the current request.
     *
     * @param callable $callback A callable.
     * @return self
     */
    public function whitelistCallback(callable $callback) : self
    {
        $this->whitelistCallback = $callback;

        return $this;
    }

    /**
     * Checks if the request is POST, PUT, DELETE or PATCH and validates the CSRF token
     *
     * @param ServerRequest $request The request object.
     * @return ServerRequest
     */
    protected function _validateAndUnsetTokenField(ServerRequest $request) : ServerRequest
    {
        if (in_array($request->getMethod(), ['PUT', 'POST', 'DELETE', 'PATCH'], true) OR $request->getData())
		{
            $this->_validateToken($request);
            $body = $request->getParsedBody();
            if (is_array($body))
			{
                unset($body[$this->_config['field']]);
                $request = $request->withParsedBody($body);
            }
        }

        return $request;
    }

    /**
     * Create a new token to be used for CSRF protection
     *
     * @return string
     */
    protected function _createToken()
    {
        return Password::hash(Password::randomBytes(16));
    }

    /**
     * Add a CSRF token to the request parameters.
     *
     * @param string $token The token to add.
     * @param ServerRequest $request The request to augment
     * @return ServerRequest Modified request
     */
    protected function _addTokenToRequest(string $token, ServerRequest $request) : ServerRequest
    {
        $params = $request->getAttribute('params');
        $params['_csrfToken'] = $token;

        return $request->withAttribute('params', $params);
    }

    /**
     * Add a CSRF token to the response cookies.
     *
     * @param string $token The token to add.
     * @param ServerRequest $request The request to validate against.
     * @param Response $response The response.
     * @return Response $response Modified response.
     */
    protected function _addTokenCookie(string $token, ServerRequest $request, Response $response)
    {
        $expiry = Date::make()->setTimestamp($this->_config['expiry']);

        $cookie = new Cookie(
            $this->_config['cookieName'],
            $token,
            $expiry,
            $request->getAttribute('webroot'),
            '',
            (bool)$this->_config['secure'],
            (bool)$this->_config['httpOnly']
        );

        return $response->withCookie($cookie);
    }

    /**
     * Validate the request data against the cookie token.
     *
     * @param ServerRequest $request The request to validate against.
     * @return void
     * @throws Exception When the CSRF token is invalid or missing.
     */
    protected function _validateToken(ServerRequest $request)
    {
        $cookies = $request->getCookieParams();
        $cookie = Arr::get($cookies, $this->_config['cookieName']);
        $post = Arr::get($request->getParsedBody(), $this->_config['field']);
        $header = $request->getHeaderLine('X-CSRF-Token');

        if (!$cookie)
		{
            throw new Exception('Missing CSRF token cookie');
        }

        if (!Password::constantEquals($post, $cookie) AND !Password::constantEquals($header, $cookie))
		{
            throw new Exception('CSRF token mismatch.');
        }
    }
}
