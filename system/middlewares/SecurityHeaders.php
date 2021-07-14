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

use InvalidArgumentException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * SecurityHeaders
 *
 * Handles common security headers in a convenient way
 *
 * @package		dFramework
 * @subpackage	Middlewares
 * @author		Dimitri Sitchet Tomkeu <devcode.dst@gmail.com>
 * @link		https://dimtrov.hebfree.org/docs/dframework/guide/Api.html
 * @since       3.3.2
 * @credit		CakePHP (Cake\Http\Middleware\SecurityHeadersMiddleware - https://cakephp.org)
 * @file        /system/middlewares/SecurityHeaders.php
 */
class SecurityHeaders implements MiddlewareInterface
{
    /** @var string X-Content-Type-Option nosniff */
    public const NOSNIFF = 'nosniff';

    /** @var string X-Download-Option noopen */
    public const NOOPEN = 'noopen';

    /** @var string Referrer-Policy no-referrer */
    public const NO_REFERRER = 'no-referrer';

    /** @var string Referrer-Policy no-referrer-when-downgrade */
    public const NO_REFERRER_WHEN_DOWNGRADE = 'no-referrer-when-downgrade';

    /** @var string Referrer-Policy origin */
    public const ORIGIN = 'origin';

    /** @var string Referrer-Policy origin-when-cross-origin */
    public const ORIGIN_WHEN_CROSS_ORIGIN = 'origin-when-cross-origin';

    /** @var string Referrer-Policy same-origin */
    public const SAME_ORIGIN = 'same-origin';

    /** @var string Referrer-Policy strict-origin */
    public const STRICT_ORIGIN = 'strict-origin';

    /** @var string Referrer-Policy strict-origin-when-cross-origin */
    public const STRICT_ORIGIN_WHEN_CROSS_ORIGIN = 'strict-origin-when-cross-origin';

    /** @var string Referrer-Policy unsafe-url */
    public const UNSAFE_URL = 'unsafe-url';

    /** @var string X-Frame-Option deny */
    public const DENY = 'deny';

    /** @var string X-Frame-Option sameorigin */
    public const SAMEORIGIN = 'sameorigin';

    /** @var string X-Frame-Option allow-from */
    public const ALLOW_FROM = 'allow-from';

    /** @var string X-XSS-Protection block, sets enabled with block */
    public const XSS_BLOCK = 'block';

    /** @var string X-XSS-Protection enabled with block */
    public const XSS_ENABLED_BLOCK = '1; mode=block';

    /** @var string X-XSS-Protection enabled */
    public const XSS_ENABLED = '1';

    /** @var string X-XSS-Protection disabled */
    public const XSS_DISABLED = '0';

    /** @var string X-Permitted-Cross-Domain-Policy all */
    public const ALL = 'all';

    /** @var string X-Permitted-Cross-Domain-Policy none */
    public const NONE = 'none';

    /** @var string X-Permitted-Cross-Domain-Policy master-only */
    public const MASTER_ONLY = 'master-only';

    /** @var string X-Permitted-Cross-Domain-Policy by-content-type */
    public const BY_CONTENT_TYPE = 'by-content-type';

    /** @var string X-Permitted-Cross-Domain-Policy by-ftp-filename */
    public const BY_FTP_FILENAME = 'by-ftp-filename';

    /**
     * Security related headers to set
     *
     * @var array
     */
    protected $headers = [];

    /**
     * X-Content-Type-Options
     *
     * Sets the header value for it to 'nosniff'
     *
     * @link https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/X-Content-Type-Options
     * @return self
     */
    public function noSniff() : self
    {
        $this->headers['x-content-type-options'] = self::NOSNIFF;

        return $this;
    }

    /**
     * X-Download-Options
     *
     * Sets the header value for it to 'noopen'
     *
     * @link https://msdn.microsoft.com/en-us/library/jj542450(v=vs.85).aspx
     * @return self
     */
    public function noOpen() : self
    {
        $this->headers['x-download-options'] = self::NOOPEN;

        return $this;
    }

    /**
     * Referrer-Policy
     *
     * @link https://w3c.github.io/webappsec-referrer-policy
     * @param string $policy Policy value. Available Value: 'no-referrer', 'no-referrer-when-downgrade', 'origin',
     *     'origin-when-cross-origin', 'same-origin', 'strict-origin', 'strict-origin-when-cross-origin', 'unsafe-url'
     * @return self
     */
    public function setReferrerPolicy(string $policy = self::SAME_ORIGIN) : self
    {
        $available = [
            self::NO_REFERRER,
            self::NO_REFERRER_WHEN_DOWNGRADE,
            self::ORIGIN,
            self::ORIGIN_WHEN_CROSS_ORIGIN,
            self::SAME_ORIGIN,
            self::STRICT_ORIGIN,
            self::STRICT_ORIGIN_WHEN_CROSS_ORIGIN,
            self::UNSAFE_URL,
        ];

        $this->checkValues($policy, $available);
        $this->headers['referrer-policy'] = $policy;

        return $this;
    }

    /**
     * X-Frame-Options
     *
     * @link https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/X-Frame-Options
     * @param string $option Option value. Available Values: 'deny', 'sameorigin', 'allow-from <uri>'
     * @param string $url URL if mode is `allow-from`
     * @return self
     */
    public function setXFrameOptions(string $option = self::SAMEORIGIN, ?string $url = null) : self
    {
        $this->checkValues($option, [self::DENY, self::SAMEORIGIN, self::ALLOW_FROM]);

        if ($option === self::ALLOW_FROM)
		{
            if (empty($url))
			{
                throw new InvalidArgumentException('The 2nd arg $url can not be empty when `allow-from` is used');
            }
            $option .= ' ' . $url;
        }

        $this->headers['x-frame-options'] = $option;

        return $this;
    }

    /**
     * X-XSS-Protection
     *
     * @link https://blogs.msdn.microsoft.com/ieinternals/2011/01/31/controlling-the-xss-filter
     * @param string $mode Mode value. Available Values: '1', '0', 'block'
     * @return self
     */
    public function setXssProtection(string $mode = self::XSS_BLOCK) : self
    {
        $mode = $mode;

        if ($mode === self::XSS_BLOCK)
		{
            $mode = self::XSS_ENABLED_BLOCK;
        }

        $this->checkValues($mode, [self::XSS_ENABLED, self::XSS_DISABLED, self::XSS_ENABLED_BLOCK]);
        $this->headers['x-xss-protection'] = $mode;

        return $this;
    }

    /**
     * X-Permitted-Cross-Domain-Policies
     *
     * @link https://www.adobe.com/devnet/adobe-media-server/articles/cross-domain-xml-for-streaming.html
     * @param string $policy Policy value. Available Values: 'all', 'none', 'master-only', 'by-content-type',
     *     'by-ftp-filename'
     * @return self
     */
    public function setCrossDomainPolicy(string $policy = self::ALL) : self
    {
        $this->checkValues($policy, [
            self::ALL,
            self::NONE,
            self::MASTER_ONLY,
            self::BY_CONTENT_TYPE,
            self::BY_FTP_FILENAME,
        ]);
        $this->headers['x-permitted-cross-domain-policies'] = $policy;

        return $this;
    }

    /**
     * Convenience method to check if a value is in the list of allowed args
     *
     * @throws \InvalidArgumentException Thrown when a value is invalid.
     * @param string $value Value to check
     * @param string[] $allowed List of allowed values
     * @return void
     */
    protected function checkValues(string $value, array $allowed) : void
    {
        if (!in_array($value, $allowed, true))
		{
            throw new InvalidArgumentException(sprintf(
                'Invalid arg `%s`, use one of these: %s', $value, implode(', ', $allowed)
            ));
        }
    }

    /**
     * Serve assets if the path matches one.
     *
     * @param ServerRequestInterface $request The request.
     * @param RequestHandlerInterface $handler The request handler.
     * @return ResponseInterface A response.
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler) : ResponseInterface
    {
        $response = $handler->handle($request);
        foreach ($this->headers As $header => $value)
		{
            $response = $response->withHeader($header, $value);
        }

        return $response;
    }
}
