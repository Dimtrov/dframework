<?php
/**
 *  dFramework
 *
 *  The simplest PHP framework for beginners
 *  Copyright (c) 2019 - 2021, Dimtrov Lab's
 *  This content is released under the Mozilla Public License 2 (MPL-2.0)
 *
 *  @package	dFramework
 *  @author	    Dimitri Sitchet Tomkeu <devcode.dst@gmail.com>
 *  @copyright	Copyright (c) 2019 - 2021, Dimtrov Lab's. (https://dimtrov.hebfree.org)
 *  @copyright	Copyright (c) 2019 - 2021, Dimitri Sitchet Tomkeu. (https://www.facebook.com/dimtrovich)
 *  @license	https://opensource.org/licenses/MPL-2.0 MPL-2.0 License
 *  @homepage	https://dimtrov.hebfree.org/works/dframework
 *  @version    3.3.0
 */

 namespace dFramework\core\http\cookie;

use ArrayIterator;
use Countable;
use DateTimeImmutable;
use DateTimeZone;
use Exception;
use InvalidArgumentException;
use IteratorAggregate;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use dFramework\core\support\contracts\CookieInterface;

/**
 * Cookie Collection
 *
 * Provides an immutable collection of cookies objects. Adding or removing
 * to a collection returns a *new* collection that you must retain.
 *
 * @package		dFramework
 * @subpackage	Core
 * @category    Support\Contracts
 * @author		Dimitri Sitchet Tomkeu <devcode.dst@gmail.com>
 * @link		https://dimtrov.hebfree.org/docs/dframework/api/
 * @since       3.2.2
 * @credit      CakeRequest (CakePHP 3.2.8 http://cakephp.org CakePHP(tm) Project)
 * @file        /system/core/http/cookie/CookieCollection.php
 */
class CookieCollection implements IteratorAggregate, Countable
{
    /**
     * Cookie objects
     *
     * @var \dFramework\core\support\contracts\CookieInterface[]
     */
    protected $cookies = [];

    /**
     * Constructor
     *
     * @param array $cookies Array of cookie objects
     */
    public function __construct(array $cookies = [])
    {
        $this->checkCookies($cookies);
        foreach ($cookies As $cookie)
		{
            $this->cookies[$cookie->getId()] = $cookie;
        }
    }

    /**
     * Create a Cookie Collection from an array of Set-Cookie Headers
     *
     * @param array $header The array of set-cookie header values.
     * @return static
     */
    public static function createFromHeader(array $header)
    {
        $cookies = static::parseSetCookieHeader($header);

        return new static($cookies);
    }

    /**
     * Create a new collection from the cookies in a ServerRequest
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request The request to extract cookie data from
     * @return static
     */
    public static function createFromServerRequest(ServerRequestInterface $request)
    {
        $data = $request->getCookieParams();
        $cookies = [];
        foreach ($data As $name => $value)
		{
            $cookies[] = new Cookie($name, $value);
        }

        return new static($cookies);
    }

    /**
     * Get the number of cookies in the collection.
     *
     * @return int
     */
    public function count()
    {
        return count($this->cookies);
    }

    /**
     * Add a cookie and get an updated collection.
     *
     * Cookies are stored by id. This means that there can be duplicate
     * cookies if a cookie collection is used for cookies across multiple
     * domains. This can impact how get(), has() and remove() behave.
     *
     * @param \dFramework\core\support\contracts\CookieInterface $cookie Cookie instance to add.
     * @return static
     */
    public function add(CookieInterface $cookie)
    {
        $new = clone $this;
        $new->cookies[$cookie->getId()] = $cookie;

        return $new;
    }

    /**
     * Get the first cookie by name.
     *
     * @param string $name The name of the cookie.
     * @return \dFramework\core\support\contracts\CookieInterface|null
     */
    public function get(string $name) : ?CookieInterface
    {
        $key = mb_strtolower($name);
        foreach ($this->cookies As $cookie)
		{
            if (mb_strtolower($cookie->getName()) === $key)
			{
                return $cookie;
            }
        }

        return null;
    }

    /**
     * Check if a cookie with the given name exists
     *
     * @param string $name The cookie name to check.
     * @return bool True if the cookie exists, otherwise false.
     */
    public function has(string $name) : bool
    {
        $key = mb_strtolower($name);
        foreach ($this->cookies As $cookie)
		{
            if (mb_strtolower($cookie->getName()) === $key)
			{
                return true;
            }
        }

        return false;
    }

    /**
     * Create a new collection with all cookies matching $name removed.
     *
     * If the cookie is not in the collection, this method will do nothing.
     *
     * @param string $name The name of the cookie to remove.
     * @return static
     */
    public function remove($name)
    {
        $new = clone $this;
        $key = mb_strtolower($name);
        foreach ($new->cookies As $i => $cookie)
		{
            if (mb_strtolower($cookie->getName()) === $key)
			{
                unset($new->cookies[$i]);
            }
        }

        return $new;
    }

    /**
     * Checks if only valid cookie objects are in the array
     *
     * @param array $cookies Array of cookie objects
     * @return void
     * @throws \InvalidArgumentException
     */
    protected function checkCookies(array $cookies)
    {
        foreach ($cookies As $index => $cookie) {
            if (!$cookie instanceof CookieInterface)
			{
                throw new InvalidArgumentException(
                    sprintf(
                        'Expected `%s[]` as $cookies but instead got `%s` at index %d',
                        static::class,
                        $cookie,
                        $index
                    )
                );
            }
        }
    }

    /**
     * Gets the iterator
     *
     * @return \ArrayIterator
     */
    public function getIterator()
    {
        return new ArrayIterator($this->cookies);
    }

    /**
     * Add cookies that match the path/domain/expiration to the request.
     *
     * This allows CookieCollections to be used as a 'cookie jar' in an HTTP client
     * situation. Cookies that match the request's domain + path that are not expired
     * when this method is called will be applied to the request.
     *
     * @param \Psr\Http\Message\RequestInterface $request The request to update.
     * @param array $extraCookies Associative array of additional cookies to add into the request. This
     *   is useful when you have cookie data from outside the collection you want to send.
     * @return \Psr\Http\Message\RequestInterface An updated request.
     */
    public function addToRequest(RequestInterface $request, array $extraCookies = [])
    {
        $uri = $request->getUri();
        $cookies = $this->findMatchingCookies(
            $uri->getScheme(),
            $uri->getHost(),
            $uri->getPath() ?: '/'
        );
        $cookies = array_merge($cookies, $extraCookies);
        $cookiePairs = [];
        foreach ($cookies As $key => $value)
		{
            $cookie = sprintf("%s=%s", rawurlencode($key), rawurlencode($value));
            $size = strlen($cookie);
            if ($size > 4096)
			{

            }
            $cookiePairs[] = $cookie;
        }

        if (empty($cookiePairs))
		{
            return $request;
        }

        return $request->withHeader('Cookie', implode('; ', $cookiePairs));
    }

    /**
     * Find cookies matching the scheme, host, and path
     *
     * @param string $scheme The http scheme to match
     * @param string $host The host to match.
     * @param string $path The path to match
     * @return array An array of cookie name/value pairs
     */
    protected function findMatchingCookies($scheme, $host, $path)
    {
        $out = [];
        $now = new DateTimeImmutable('now', new DateTimeZone('UTC'));
        foreach ($this->cookies As $cookie) {
            if ($scheme === 'http' AND $cookie->isSecure())
			{
                continue;
            }
            if (strpos($path, $cookie->getPath()) !== 0)
			{
                continue;
            }
            $domain = $cookie->getDomain();
            $leadingDot = substr($domain, 0, 1) === '.';
            if ($leadingDot)
			{
                $domain = ltrim($domain, '.');
            }

            if ($cookie->isExpired($now))
			{
                continue;
            }

            $pattern = '/' . preg_quote($domain, '/') . '$/';
            if (!preg_match($pattern, $host))
			{
                continue;
            }

            $out[$cookie->getName()] = $cookie->getValue();
        }

        return $out;
    }

    /**
     * Create a new collection that includes cookies from the response.
     *
     * @param \Psr\Http\Message\ResponseInterface $response Response to extract cookies from.
     * @param \Psr\Http\Message\RequestInterface $request Request to get cookie context from.
     * @return static
     */
    public function addFromResponse(ResponseInterface $response, RequestInterface $request)
    {
        $uri = $request->getUri();
        $host = $uri->getHost();
        $path = $uri->getPath() ?: '/';

        $cookies = static::parseSetCookieHeader($response->getHeader('Set-Cookie'));
        $cookies = $this->setRequestDefaults($cookies, $host, $path);
        $new = clone $this;
        foreach ($cookies As $cookie)
		{
            $new->cookies[$cookie->getId()] = $cookie;
        }
        $new->removeExpiredCookies($host, $path);

        return $new;
    }

    /**
     * Apply path and host to the set of cookies if they are not set.
     *
     * @param array $cookies An array of cookies to update.
     * @param string $host The host to set.
     * @param string $path The path to set.
     * @return array An array of updated cookies.
     */
    protected function setRequestDefaults(array $cookies, $host, $path)
    {
        $out = [];
        foreach ($cookies As $name => $cookie)
		{
            if (!$cookie->getDomain())
			{
                $cookie = $cookie->withDomain($host);
            }
            if (!$cookie->getPath())
			{
                $cookie = $cookie->withPath($path);
            }
            $out[] = $cookie;
        }

        return $out;
    }

    /**
     * Parse Set-Cookie headers into array
     *
     * @param array $values List of Set-Cookie Header values.
     * @return \dFramework\core\http\cookie\Cookie[] An array of cookie objects
     */
    protected static function parseSetCookieHeader($values)
    {
        $cookies = [];
        foreach ($values As $value)
		{
            $value = rtrim($value, ';');
            $parts = preg_split('/\;[ \t]*/', $value);

            $name = false;
            $cookie = [
                'value' => '',
                'path' => '',
                'domain' => '',
                'secure' => false,
                'httponly' => false,
                'expires' => null,
                'max-age' => null,
            ];
            foreach ($parts As $i => $part)
			{
                if (strpos($part, '=') !== false)
				{
                    list($key, $value) = explode('=', $part, 2);
                }
				else
				{
                    $key = $part;
                    $value = true;
                }
                if ($i === 0)
				{
                    $name = $key;
                    $cookie['value'] = urldecode($value);
                    continue;
                }
                $key = strtolower($key);
                if (array_key_exists($key, $cookie) AND !strlen($cookie[$key]))
				{
                    $cookie[$key] = $value;
                }
            }
            try {
                $expires = null;
                if ($cookie['max-age'] !== null)
				{
                    $expires = new DateTimeImmutable('@' . (time() + $cookie['max-age']));
                }
				elseif ($cookie['expires'])
				{
                    $expires = new DateTimeImmutable('@' . strtotime($cookie['expires']));
                }
            }
			catch (Exception $e) {
                $expires = null;
            }

            try {
                $cookies[] = new Cookie(
                    $name,
                    $cookie['value'],
                    $expires,
                    $cookie['path'],
                    $cookie['domain'],
                    $cookie['secure'],
                    $cookie['httponly']
                );
            } catch (Exception $e) {
                // Don't blow up on invalid cookies
            }
        }

        return $cookies;
    }

    /**
     * Remove expired cookies from the collection.
     *
     * @param string $host The host to check for expired cookies on.
     * @param string $path The path to check for expired cookies on.
     * @return void
     */
    protected function removeExpiredCookies($host, $path)
    {
        $time = new DateTimeImmutable('now', new DateTimeZone('UTC'));
        $hostPattern = '/' . preg_quote($host, '/') . '$/';

        foreach ($this->cookies As $i => $cookie)
		{
            $expired = $cookie->isExpired($time);
            $pathMatches = strpos($path, $cookie->getPath()) === 0;
            $hostMatches = preg_match($hostPattern, $cookie->getDomain());
            if ($pathMatches AND $hostMatches AND $expired)
			{
                unset($this->cookies[$i]);
            }
        }
    }
}
