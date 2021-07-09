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
use dFramework\core\exception\HttpException;
use dFramework\core\output\Format;

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
class BodyParser
{
/**
     * Registered Parsers
     *
     * @var array
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
     * - `json` Set to false to disable json body parsing.
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
                [$this, 'decodeJson']
            );
        }
        if ($options['xml'])
		{
            $this->addParser(
                ['application/xml', 'text/xml'],
                [$this, 'decodeXml']
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
     * @param callable $parser The parser function. Must return an array of data to be inserted
     *   into the request.
     * @return self
     */
    public function addParser(array $types, callable $parser) : self
    {
        foreach ($types As $type)
		{
            $type = strtolower($type);
            $this->parsers[$type] = $parser;
        }

        return $this;
    }

    /**
     * Apply the middleware.
     *
     * Will modify the request adding a parsed body if the content-type is known.
     *
     * @param ServerRequestInterface $request The request.
     * @param ResponseInterface $response The response.
     * @param callable $next Callback to invoke the next middleware.
     * @return ResponseInterface A response
     */
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, $next)
    {
        if (!in_array($request->getMethod(), $this->methods, true))
		{
            return $next($request, $response);
        }
        list($type) = explode(';', $request->getHeaderLine('Content-Type'));
        $type = strtolower($type);
        if (!isset($this->parsers[$type]))
		{
            return $next($request, $response);
        }

        $parser = $this->parsers[$type];
        $result = $parser($request->getBody()->getContents());
        if (!is_array($result))
		{
            throw new HttpException('Bad Request', 400);
        }
        $request = $request->withParsedBody($result);

        return $next($request, $response);
    }

    /**
     * Decode JSON into an array.
     *
     * @param string $body The request body to decode
     * @return array
     */
    protected function decodeJson($body)
    {
        return json_decode($body, true);
    }

    /**
     * Decode XML into an array.
     *
     * @param string $body The request body to decode
     * @return array
     */
    protected function decodeXml($body)
    {
        $format = new Format($body, Format::XML_FORMAT);

		return $format->toArray();
    }
}
