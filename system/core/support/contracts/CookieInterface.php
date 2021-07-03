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

namespace dFramework\core\support\contracts;

/**
 * Cookie Interface
 *
 * @package		dFramework
 * @subpackage	Core
 * @category    Support\Contracts
 * @author		Dimitri Sitchet Tomkeu <devcode.dst@gmail.com>
 * @link		https://dimtrov.hebfree.org/docs/dframework/api/
 * @since       3.2.2
 * @credit      CakeRequest (CakePHP 3.2.8 http://cakephp.org CakePHP(tm) Project)
 * @file        /system/core/support/contracts/CookieInterface.php
 */
interface CookieInterface
{
    /**
     * Expires attribute format.
     *
     * @var string
     */
    const EXPIRES_FORMAT = 'D, d-M-Y H:i:s T';

    /**
     * Sets the cookie name
     *
     * @param string $name Name of the cookie
     * @return static
     */
    public function withName($name);

    /**
     * Gets the cookie name
     *
     * @return string
     */
    public function getName();

    /**
     * Gets the cookie value
     *
     * @return string|array
     */
    public function getValue();

    /**
     * Gets the cookie value as a string.
     *
     * This will collapse any complex data in the cookie with json_encode()
     *
     * @return string
     */
    public function getStringValue();

    /**
     * Create a cookie with an updated value.
     *
     * @param string|array $value Value of the cookie to set
     * @return static
     */
    public function withValue($value);

    /**
     * Get the id for a cookie
     *
     * Cookies are unique across name, domain, path tuples.
     *
     * @return string
     */
    public function getId();

    /**
     * Get the path attribute.
     *
     * @return string
     */
    public function getPath();

    /**
     * Create a new cookie with an updated path
     *
     * @param string $path Sets the path
     * @return static
     */
    public function withPath($path);

    /**
     * Get the domain attribute.
     *
     * @return string
     */
    public function getDomain();

    /**
     * Create a cookie with an updated domain
     *
     * @param string $domain Domain to set
     * @return static
     */
    public function withDomain($domain);

    /**
     * Get the current expiry time
     *
     * @return \DateTime|\DateTimeImmutable|null Timestamp of expiry or null
     */
    public function getExpiry();

    /**
     * Get the timestamp from the expiration time
     *
     * Timestamps are strings as large timestamps can overflow MAX_INT
     * in 32bit systems.
     *
     * @return string|null The expiry time as a string timestamp.
     */
    public function getExpiresTimestamp();

    /**
     * Builds the expiration value part of the header string
     *
     * @return string
     */
    public function getFormattedExpires();

    /**
     * Create a cookie with an updated expiration date
     *
     * @param \DateTime|\DateTimeImmutable $dateTime Date time object
     * @return static
     */
    public function withExpiry($dateTime);

    /**
     * Create a new cookie that will virtually never expire.
     *
     * @return static
     */
    public function withNeverExpire();

    /**
     * Create a new cookie that will expire/delete the cookie from the browser.
     *
     * This is done by setting the expiration time to 1 year ago
     *
     * @return static
     */
    public function withExpired();

    /**
     * Check if a cookie is expired when compared to $time
     *
     * Cookies without an expiration date always return false.
     *
     * @param \DateTime|\DateTimeImmutable $time The time to test against. Defaults to 'now' in UTC.
     * @return bool
     */
    public function isExpired($time = null);

    /**
     * Check if the cookie is HTTP only
     *
     * @return bool
     */
    public function isHttpOnly();

    /**
     * Create a cookie with HTTP Only updated
     *
     * @param bool $httpOnly HTTP Only
     * @return static
     */
    public function withHttpOnly($httpOnly);

    /**
     * Check if the cookie is secure
     *
     * @return bool
     */
    public function isSecure();

    /**
     * Create a cookie with Secure updated
     *
     * @param bool $secure Secure attribute value
     * @return static
     */
    public function withSecure($secure);

    /**
     * Returns the cookie as header value
     *
     * @return string
     */
    public function toHeaderValue();
}
