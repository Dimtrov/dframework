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

namespace dFramework\core\http;

use dFramework\core\exception\Logger;
use GuzzleHttp\Psr7\LimitStream;

/**
 * Response Emitter
 *
 * Emits a Response to the PHP Server API.
 *
 * This emitter offers a few changes from the emitters offered by
 * diactoros:
 *
 * - Cookies are emitted using setcookie() to not conflict with ext/session
 * - For fastcgi servers with PHP-FPM session_write_close() is called just
 *   before fastcgi_finish_request() to make sure session data is saved
 *   correctly (especially on slower session backends).
 *
 * @package		dFramework
 * @subpackage	Core
 * @category    Http
 * @author		Dimitri Sitchet Tomkeu <devcode.dst@gmail.com>
 * @link		https://dimtrov.hebfree.org/docs/dframework/middleware
 * @since       3.3.0
 * @credit      CakePHP 4.0 (Cake\Http\ResponseEmitter)
 * @file        /system/core/http/ResponseEmitter.php
 */
class ResponseEmitter
{
    /**
     * {@inheritDoc}
     *
     * @param Response $response Response
     * @param int $maxBufferLength Max buffer length
     */
    public function emit(Response $response, int $maxBufferLength = 8192)
    {
        $file = $line = null;
        if (headers_sent($file, $line))
        {
            $message = "Unable to emit headers. Headers sent in file=$file line=$line";
            if (config('general.environment') === 'dev')
            {
                trigger_error($message, E_USER_WARNING);
            }
            else
            {
                Logger::warning($message, __FILE__, __LINE__);
            }
        }

        $this->emitStatusLine($response);
        $this->emitHeaders($response);
        $this->flush();

        $range = $this->parseContentRange($response->getHeaderLine('Content-Range'));
        if (is_array($range))
        {
            $this->emitBodyRange($range, $response, $maxBufferLength);
        }
        else
        {
            $this->emitBody($response, $maxBufferLength);
        }

        if (function_exists('fastcgi_finish_request'))
        {
            session_write_close();
            fastcgi_finish_request();
        }
    }

    /**
     * Emit the message body.
     *
     * @param Response $response The response to emit
     * @param int $maxBufferLength The chunk size to emit
     * @return void
     */
    protected function emitBody(Response $response, int $maxBufferLength)
    {
        if (in_array($response->getStatusCode(), [204, 304]))
        {
            return;
        }
        $body = $response->getBody();

        if (!$body->isSeekable())
        {
            echo $body;

            return;
        }

        $body->rewind();
        while (!$body->eof())
        {
            echo $body->read($maxBufferLength);
        }
    }

    /**
     * Emit a range of the message body.
     *
     * @param array $range The range data to emit
     * @param Response $response The response to emit
     * @param int $maxBufferLength The chunk size to emit
     * @return void
     */
    protected function emitBodyRange(array $range, Response $response, int $maxBufferLength)
    {
        list($unit, $first, $last, $length) = $range;

        $body = $response->getBody();

        if (!$body->isSeekable())
        {
            $contents = $body->getContents();
            echo substr($contents, $first, $last - $first + 1);

            return;
        }

        $body = new LimitStream($body, -1, $first);
        $body->rewind();
        $pos = 0;
        $length = $last - $first + 1;
        while (!$body->eof() AND $pos < $length)
        {
            if (($pos + $maxBufferLength) > $length)
            {
                echo $body->read($length - $pos);
                break;
            }

            echo $body->read($maxBufferLength);
            $pos = $body->tell();
        }
    }

    /**
     * Emit the status line.
     *
     * Emits the status line using the protocol version and status code from
     * the response; if a reason phrase is available, it, too, is emitted.
     *
     * @param Response $response The response to emit
     * @return void
     */
    protected function emitStatusLine(Response $response)
    {
        $reasonPhrase = $response->getReasonPhrase();
        header(sprintf(
            'HTTP/%s %d%s',
            $response->getProtocolVersion(),
            $response->getStatusCode(),
            ($reasonPhrase ? ' ' . $reasonPhrase : '')
        ));
    }

    /**
     * Emit response headers.
     *
     * Loops through each header, emitting each; if the header value
     * is an array with multiple values, ensures that each is sent
     * in such a way as to create aggregate headers (instead of replace
     * the previous).
     *
     * @param Response $response The response to emit
     * @return void
     */
    protected function emitHeaders(Response $response)
    {
        $cookies = [];
        if (method_exists($response, 'getCookies'))
		{
			$cookies = $response->getCookies();
        }

        foreach ($response->getHeaders() As $name => $values)
        {
            if (strtolower($name) === 'set-cookie')
            {
                $cookies = array_merge($cookies, $values);
                continue;
            }
            $first = true;
            foreach ($values As $value)
            {
                header(sprintf(
                    '%s: %s',
                    $name,
                    $value
                ), $first);
                $first = false;
            }
        }

        $this->emitCookies($cookies);
    }

    /**
     * Emit cookies using setcookie()
     *
     * @param array $cookies An array of Set-Cookie headers.
     * @return void
     */
    protected function emitCookies(array $cookies)
    {
        foreach ($cookies As $cookie)
        {
            if (is_array($cookie))
			{
                setcookie(
                    $cookie['name'],
                    $cookie['value'],
                    $cookie['expire'],
                    $cookie['path'],
                    $cookie['domain'],
                    $cookie['secure'],
                    $cookie['httpOnly']
                );
                continue;
            }

            if (strpos($cookie, '";"') !== false)
            {
                $cookie = str_replace('";"', '{__cookie_replace__}', $cookie);
                $parts = str_replace('{__cookie_replace__}', '";"', explode(';', $cookie));
            }
            else
            {
                $parts = preg_split('/\;[ \t]*/', $cookie);
            }

            list($name, $value) = explode('=', array_shift($parts), 2);
            $data = [
                'name'     => urldecode($name),
                'value'    => urldecode($value),
                'expires'  => 0,
                'path'     => '',
                'domain'   => '',
                'secure'   => false,
                'httponly' => false,
            ];

            foreach ($parts As $part)
            {
                if (strpos($part, '=') !== false)
                {
                    list($key, $value) = explode('=', $part);
                }
                else
                {
                    $key = $part;
                    $value = true;
                }

                $key = strtolower($key);
                $data[$key] = $value;
            }
            if (!empty($data['expires']))
            {
                $data['expires'] = strtotime($data['expires']);
            }
            setcookie(
                $data['name'],
                $data['value'],
                $data['expires'],
                $data['path'],
                $data['domain'],
                $data['secure'],
                $data['httponly']
            );
        }
    }

    /**
     * Loops through the output buffer, flushing each, before emitting
     * the response.
     *
     * @param int|null $maxBufferLevel Flush up to this buffer level.
     * @return void
     */
    protected function flush(?int $maxBufferLevel = null)
    {
        if (null === $maxBufferLevel)
        {
            $maxBufferLevel = ob_get_level();
        }

        while (ob_get_level() > $maxBufferLevel)
        {
            ob_end_flush();
        }
    }

    /**
     * Parse content-range header
     * https://www.w3.org/Protocols/rfc2616/rfc2616-sec14.html#sec14.16
     *
     * @param string $header The Content-Range header to parse.
     * @return array|false [unit, first, last, length]; returns false if no
     *     content range or an invalid content range is provided
     */
    protected function parseContentRange(string $header)
    {
        if (preg_match('/(?P<unit>[\w]+)\s+(?P<first>\d+)-(?P<last>\d+)\/(?P<length>\d+|\*)/', $header, $matches))
        {
            return [
                $matches['unit'],
                (int)$matches['first'],
                (int)$matches['last'],
                $matches['length'] === '*' ? '*' : (int)$matches['length'],
            ];
        }

        return false;
    }
}
