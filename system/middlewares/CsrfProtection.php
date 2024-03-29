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

use ArrayAccess;
use dFramework\core\exception\Exception;
use dFramework\core\http\cookie\Cookie;
use dFramework\core\http\Response;
use dFramework\core\security\Password;
use dFramework\core\support\contracts\CookieInterface;
use dFramework\core\utilities\Arr;
use dFramework\core\utilities\Str;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use RuntimeException;

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
 * @see 		https://cheatsheetseries.owasp.org/cheatsheets/Cross-Site_Request_Forgery_Prevention_Cheat_Sheet.html#double-submit-cookie
 * @credit		CakePHP (Cake\Http\Middleware\CsrfProtectionMiddleware - https://cakephp.org)
 * @file        /system/middlewares/CsrfProtection.php
 */
class CsrfProtection implements MiddlewareInterface
{
   /**
	* Config for the CSRF handling.
	*
	*  - `cookieName` The name of the cookie to send.
	*  - `expiry` A strotime compatible value of how long the CSRF token should last.
	*    Defaults to browser session.
	*  - `secure` Whether or not the cookie will be set with the Secure flag. Defaults to false.
	*  - `httponly` Whether or not the cookie will be set with the HttpOnly flag. Defaults to false.
	*  - `samesite` "SameSite" attribute for cookies. Defaults to `null`.
	*    Valid values: `CookieInterface::SAMESITE_LAX`, `CookieInterface::SAMESITE_STRICT`,
	*    `CookieInterface::SAMESITE_NONE` or `null`.
	*  - `field` The form field to check. Changing this will also require configuring
	*    FormHelper.
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
   protected $skipCheckCallback;

   /**
	* @var int
	*/
   public const TOKEN_VALUE_LENGTH = 16;

   /**
	* Tokens have an hmac generated so we can ensure
	* that tokens were generated by our application.
	*
	* Should be TOKEN_VALUE_LENGTH + strlen(hmac)
	*
	* We are currently using sha1 for the hmac which
	* creates 40 bytes.
	*
	* @var int
	*/
   public const TOKEN_WITH_CHECKSUM_LENGTH = 56;

   /**
	* Constructor
	*
	* @param array $config Config options. See $_config for valid keys.
	*/
   public function __construct(array $config = [])
   {
	   	$options = config('data.csrf');
		$this->_config = [
			'cookieName' => $options['cookie_name'] ?? 'csrfToken',
			'expiry'     => $options['expire'] ?? 0,
			'secure'     => $options['secure'] ?? false,
			'httponly'   => $options['httponly'] ?? false,
			'samesite'   => $options['samesite'] ?? null,
			'field'      => $options['token_name'] ?? '_csrfToken',
		];

	   if (array_key_exists('httpOnly', $config))
	   {
		   $config['httponly'] = $config['httpOnly'];
		   deprecationWarning('Option `httpOnly` is deprecated. Use lowercased `httponly` instead.');
	   }

	   $this->_config = $config + $this->_config;
   }

   /**
	* Checks and sets the CSRF token depending on the HTTP verb.
	*
	* @param ServerRequestInterface $request The request.
	* @param RequestHandlerInterface $handler The request handler.
	* @return ResponseInterface A response.
	*/
   public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
   {
	   $method = $request->getMethod();
	   $hasData = in_array($method, ['PUT', 'POST', 'DELETE', 'PATCH'], true) OR $request->getParsedBody();

	   if ($hasData AND $this->skipCheckCallback !== null AND call_user_func($this->skipCheckCallback, $request) === true)
	   {
		   $request = $this->_unsetTokenField($request);

		   return $handler->handle($request);
	   }
	   if ($request->getAttribute($this->_config['cookieName']))
	   {
		   throw new RuntimeException(
			   'A CSRF token is already set in the request.' .
			   "\n" .
			   'Ensure you do not have the CSRF middleware applied more than once. ' .
			   'Check both your `Application::middleware()` method and `config/routes.php`.'
		   );
	   }

	   $cookies = $request->getCookieParams();
	   $cookieData = Arr::get($cookies, $this->_config['cookieName']);

	   if (is_string($cookieData) AND strlen($cookieData) > 0)
	   {
		   $request = $request->withAttribute($this->_config['cookieName'], $this->saltToken($cookieData));
	   }

	   if ($method === 'GET' AND $cookieData === null)
	   {
		   $token = $this->createToken();
		   $request = $request->withAttribute($this->_config['cookieName'], $this->saltToken($token));
		   /** @var mixed $response */
		   $response = $handler->handle($request);

		   return $this->_addTokenCookie($token, $request, $response);
	   }

	   if ($hasData)
	   {
		   $this->_validateToken($request);
		   $request = $this->_unsetTokenField($request);
	   }

	   return $handler->handle($request);
   }

   /**
	* Set callback for allowing to skip token check for particular request.
	*
	* The callback will receive request instance as argument and must return
	* `true` if you want to skip token check for the current request.
	*
	* @deprecated 3.3.3 Use skipCheckCallback instead.
	* @param callable $callback A callable.
	* @return self
	*/
   public function whitelistCallback(callable $callback) : self
   {
	   deprecationWarning('`whitelistCallback()` is deprecated. Use `skipCheckCallback()` instead.');
	   $this->skipCheckCallback = $callback;

	   return $this;
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
   public function skipCheckCallback(callable $callback) : self
   {
	   $this->skipCheckCallback = $callback;

	   return $this;
   }

   /**
	* Remove CSRF protection token from request data.
	*
	* @param \ServerRequestInterface $request The request object.
	* @return ServerRequestInterface
	*/
   protected function _unsetTokenField(ServerRequestInterface $request) : ServerRequestInterface
   {
	   $body = $request->getParsedBody();
	   if (is_array($body))
	   {
		   unset($body[$this->_config['field']]);
		   $request = $request->withParsedBody($body);
	   }

	   return $request;
   }

   /**
	* Create a new token to be used for CSRF protection
	*
	* @return string
	* @deprecated 3.3.3 Use {@link createToken()} instead.
	*/
   protected function _createToken() : string
   {
	   deprecationWarning('_createToken() is deprecated. Use createToken() instead.');

	   return $this->createToken();
   }

   /**
	* Test if the token predates salted tokens.
	*
	* These tokens are hexadecimal values and equal
	* to the token with checksum length. While they are vulnerable
	* to BREACH they should rotate over time and support will be dropped
	* in 5.x.
	*
	* @param string $token The token to test.
	* @return bool
	*/
   protected function isHexadecimalToken(string $token) : bool
   {
	   return preg_match('/^[a-f0-9]{' . static::TOKEN_WITH_CHECKSUM_LENGTH . '}$/', $token) === 1;
   }

   /**
	* Create a new token to be used for CSRF protection
	*
	* @return string
	*/
   public function createToken() : string
   {
	   $value = Password::randomBytes(static::TOKEN_VALUE_LENGTH);

	   return base64_encode($value . hash_hmac('sha1', $value, Password::getSalt()));
   }

   /**
	* Apply entropy to a CSRF token
	*
	* To avoid BREACH apply a random salt value to a token
	* When the token is compared to the session the token needs
	* to be unsalted.
	*
	* @param string $token The token to salt.
	* @return string The salted token with the salt appended.
	*/
   public function saltToken(string $token): string
   {
	   if ($this->isHexadecimalToken($token))
	   {
		   return $token;
	   }
	   $decoded = base64_decode($token, true);
	   $length = strlen($decoded);
	   $salt = Password::randomBytes($length);
	   $salted = '';
	   for ($i = 0; $i < $length; $i++)
	   {
		   // XOR the token and salt together so that we can reverse it later.
		   $salted .= chr(ord($decoded[$i]) ^ ord($salt[$i]));
	   }

	   return base64_encode($salted . $salt);
   }

   /**
	* Remove the salt from a CSRF token.
	*
	* If the token is not TOKEN_VALUE_LENGTH * 2 it is an old
	* unsalted value that is supported for backwards compatibility.
	*
	* @param string $token The token that could be salty.
	* @return string An unsalted token.
	*/
   public function unsaltToken(string $token) : string
   {
	   if ($this->isHexadecimalToken($token))
	   {
		   return $token;
	   }
	   $decoded = base64_decode($token, true);
	   if ($decoded === false OR strlen($decoded) !== static::TOKEN_WITH_CHECKSUM_LENGTH * 2)
	   {
		   return $token;
	   }
	   $salted = substr($decoded, 0, static::TOKEN_WITH_CHECKSUM_LENGTH);
	   $salt = substr($decoded, static::TOKEN_WITH_CHECKSUM_LENGTH);

	   $unsalted = '';
	   for ($i = 0; $i < static::TOKEN_WITH_CHECKSUM_LENGTH; $i++)
	   {
		   // Reverse the XOR to desalt.
		   $unsalted .= chr(ord($salted[$i]) ^ ord($salt[$i]));
	   }

	   return base64_encode($unsalted);
   }

   /**
	* Verify that CSRF token was originally generated by the receiving application.
	*
	* @param string $token The CSRF token.
	* @return bool
	*/
   protected function _verifyToken(string $token) : bool
   {
	   // If we have a hexadecimal value we're in a compatibility mode from before
	   // tokens were salted on each request.
	   if ($this->isHexadecimalToken($token))
	   {
		   $decoded = $token;
	   }
	   else
	   {
		   $decoded = base64_decode($token, true);
	   }
	   if (strlen($decoded) <= static::TOKEN_VALUE_LENGTH)
	   {
		   return false;
	   }

	   $key = substr($decoded, 0, static::TOKEN_VALUE_LENGTH);
	   $hmac = substr($decoded, static::TOKEN_VALUE_LENGTH);

	   $expectedHmac = hash_hmac('sha1', $key, Password::getSalt());

	   return hash_equals($hmac, $expectedHmac);
   }

   /**
	* Add a CSRF token to the response cookies.
	*
	* @param string $token The token to add.
	* @param ServerRequestInterface $request The request to validate against.
	* @param ResponseInterface $response The response.
	* @return ResponseInterface $response Modified response.
	*/
   protected function _addTokenCookie(string $token, ServerRequestInterface $request, ResponseInterface $response) : ResponseInterface
   {
	   $cookie = $this->_createCookie($token, $request);
	   if ($response instanceof Response)
	   {
		   return $response->withCookie($cookie);
	   }

	   return $response->withAddedHeader('Set-Cookie', $cookie->toHeaderValue());
   }

   /**
	* Validate the request data against the cookie token.
	*
	* @param ServerRequestInterface $request The request to validate against.
	* @return void
	* @throws Exception When the CSRF token is invalid or missing.
	*/
   protected function _validateToken(ServerRequestInterface $request) : void
   {
	   $cookie = Arr::get($request->getCookieParams(), $this->_config['cookieName']);

	   if (!$cookie OR !is_string($cookie))
	   {
		   throw new Exception('Missing or incorrect CSRF cookie type.');
	   }

	   if (!$this->_verifyToken($cookie))
	   {
		   throw new Exception('Missing or invalid CSRF cookie.');
	   }

	   $body = $request->getParsedBody();
	   if (is_array($body) OR $body instanceof ArrayAccess)
	   {
		   $post = (string) Arr::get($body, $this->_config['field']);
		   if (empty($post))
		   {
			   $post = (string) Arr::get($body, Str::toSnake($this->_config['field']));
		   }
		   $post = $this->unsaltToken($post);
		   if (hash_equals($post, $cookie))
		   {
			   return;
		   }
	   }

	   $header = $request->getHeaderLine('X-CSRF-Token');
	   $header = $this->unsaltToken($header);
	   if (hash_equals($header, $cookie))
	   {
		   return;
	   }

	   throw new Exception('CSRF token from either the request body or request headers did not match or is missing.');
   }

   /**
	* Create response cookie
	*
	* @param string $value Cookie value
	* @param ServerRequestInterface $request The request object.
	* @return CookieInterface
	*/
   protected function _createCookie(string $value, ServerRequestInterface $request): CookieInterface
   {
		return new Cookie(
		   $this->_config['cookieName'],
		   $value,
		   $this->_config['expiry'] ?: null,
		   '/',
		   '',
		   $this->_config['secure'],
		   $this->_config['httponly']
		);
   }
}
