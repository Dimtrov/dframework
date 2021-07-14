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

use Closure;
use dFramework\core\exception\HttpException;
use dFramework\core\output\Format;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * BodyParser
 *
 * Parse encoded request body data.
 *
 * Enables JSON and XML request payloads to be parsed into the request's
 * Provides CSRF protection & validation.
 *
 * You can also add your own request body parsers using the `addParser()` method.
 *
 * @package		dFramework
 * @subpackage	Middlewares
 * @author		Dimitri Sitchet Tomkeu <devcode.dst@gmail.com>
 * @link		https://dimtrov.hebfree.org/docs/dframework/guide/Api.html
 * @since       3.3.2
 * @credit		CakePHP (Cake\Http\Middleware\BodyParserMiddleware - https://cakephp.org)
 * @file        /system/middlewares/BodyParser.php
 */
class BodyParser implements MiddlewareInterface
{
    /**
     * Registered Parsers
     *
     * @var \Closure[]
     */
    protected $parsers = [];

    /**
     * The HTTP methods to parse data on.
     *
     * @var string[]
     */
    protected $methods = ['PUT', 'POST', 'PATCH', 'DELETE'];

    /**
     * Constructor
     *
     * ### Options
     *
     * - `json` Set to false to disable JSON body parsing.
     * - `xml` Set to true to enable XML parsing. Defaults to false, as XML
     *   handling requires more care than JSON does.
     * - `methods` The HTTP methods to parse on. Defaults to PUT, POST, PATCH DELETE.
     *
     * @param array $options The options to use. See above.
     */
    public function __construct(array $options = [])
    {
        $options += ['json' => true, 'xml' => false, 'methods' => null];
        if ($options['json'])
		{
            $this->addParser(
                ['application/json', 'text/json'],
                Closure::fromCallable([$this, 'decodeJson'])
            );
        }
        if ($options['xml'])
		{
            $this->addParser(
                ['application/xml', 'text/xml'],
                Closure::fromCallable([$this, 'decodeXml'])
            );
        }
        if ($options['methods'])
		{
            $this->setMethods($options['methods']);
        }
    }

    /**
     * Set the HTTP methods to parse request bodies on.
     *
     * @param string[] $methods The methods to parse data on.
     * @return self
     */
    public function setMethods(array $methods) : self
    {
        $this->methods = $methods;

        return $this;
    }

    /**
     * Get the HTTP methods to parse request bodies on.
     *
     * @return string[]
     */
    public function getMethods() : array
    {
        return $this->methods;
    }

    /**
     * Add a parser.
     *
     * Map a set of content-type header values to be parsed by the $parser.
     *
     * ### Example
     *
     * An naive CSV request body parser could be built like so:
     *
     * ```
     * $parser->addParser(['text/csv'], function ($body) {
     *   return str_getcsv($body);
     * });
     * ```
     *
     * @param string[] $types An array of content-type header values to match. eg. application/json
     * @param \Closure $parser The parser function. Must return an array of data to be inserted
     *   into the request.
     * @return self
     */
    public function addParser(array $types, Closure $parser) : self
    {
        foreach ($types As $type)
		{
            $type = strtolower($type);
            $this->parsers[$type] = $parser;
        }

        return $this;
    }

    /**
     * Get the current parsers
     *
     * @return \Closure[]
     */
    public function getParsers(): array
    {
        return $this->parsers;
    }

    /**
     * Apply the middleware.
     *
     * Will modify the request adding a parsed body if the content-type is known.
     *
     * @param ServerRequestInterface $request The request.
     * @param RequestHandlerInterface $handler The request handler.
     * @return ResponseInterface A response.
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (!in_array($request->getMethod(), $this->methods, true))
		{
            return $handler->handle($request);
        }

        [$type] = explode(';', $request->getHeaderLine('Content-Type'));
        $type = strtolower($type);
        if (!isset($this->parsers[$type]))
		{
            return $handler->handle($request);
        }

        $parser = $this->parsers[$type];
        $result = $parser($request->getBody()->getContents());
        if (!is_array($result))
		{
            throw new HttpException('Bad Request', 400);
        }
        $request = $request->withParsedBody($result);

        return $handler->handle($request);
    }

    /**
     * Decode JSON into an array.
     *
     * @param string $body The request body to decode
     * @return array|null
     */
    protected function decodeJson(string $body) : ?array
    {
        if ($body === '')
		{
            return [];
        }
        $decoded = json_decode($body, true);
        if (json_last_error() === JSON_ERROR_NONE)
		{
            return (array) $decoded;
        }

        return null;
    }

    /**
     * Decode XML into an array.
     *
     * @param string $body The request body to decode
     * @return array
     */
    protected function decodeXml(string $body) : array
    {
        $format = new Format($body, Format::XML_FORMAT);

		return $format->toArray();
    }
}
