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
 *  @link	    https://dimtrov.hebfree.org/works/dframework
 *  @version    3.3.4
 */

namespace dFramework\core\http;

use dFramework\core\exception\HttpException;

/**
 * Negotiator
 *
 * Provides methods to negotiate with the HTTP headers to determine the best
 * type match between what the application supports and what the requesting
 * getServer wants.
 *
 * @package		dFramework
 * @subpackage	Core
 * @category    Http
 * @author		Dimitri Sitchet Tomkeu <devcode.dst@gmail.com>
 * @link		https://dimtrov.hebfree.org/docs/dframework/api/
 * @since       3.2.2
 * @see     	http://tools.ietf.org/html/rfc7231#section-5.3
 * @credit      CodeIgniter\HTTP (https://codeigniter.com)
 * @file        /system/core/http/Negotiator.php
 */
class Negotiator
{

	/**
	 * Request
	 *
	 * @var ServerRequest
	 */
	protected $request;

	//--------------------------------------------------------------------

	/**
	 * Constructor
	 *
	 * @param ServerRequest $request
	 */
	public function __construct(ServerRequest $request = null)
	{
		if (! is_null($request))
		{
			$this->request = $request;
		}
	}

	/**
	 * Stores the request instance to grab the headers from.
	 *
	 * @param ServerRequest $request
	 *
	 * @return self
	 */
	public function setRequest(ServerRequest $request) : self
	{
		$this->request = $request;

		return $this;
	}

	/**
	 * Determines the best content-type to use based on the $supported
	 * types the application says it supports, and the types requested
	 * by the client.
	 *
	 * If no match is found, the first, highest-ranking client requested
	 * type is returned.
	 *
	 * @param array   $supported
	 * @param boolean $strictMatch If TRUE, will return an empty string when no match found.
	 *                             If FALSE, will return the first supported element.
	 *
	 * @return string
	 */
	public function media(array $supported, bool $strictMatch = false): string
	{
		return $this->getBestMatch($supported, $this->request->getHeaderLine('accept'), true, $strictMatch);
	}

	/**
	 * Determines the best charset to use based on the $supported
	 * types the application says it supports, and the types requested
	 * by the client.
	 *
	 * If no match is found, the first, highest-ranking client requested
	 * type is returned.
	 *
	 * @param array $supported
	 *
	 * @return string
	 */
	public function charset(array $supported): string
	{
		$match = $this->getBestMatch($supported, $this->request->getHeaderLine('accept-charset'), false, true);

		// If no charset is shown as a match, ignore the directive
		// as allowed by the RFC, and tell it a default value.
		if (empty($match))
		{
			return 'utf-8';
		}

		return $match;
	}

	/**
	 * Determines the best encoding type to use based on the $supported
	 * types the application says it supports, and the types requested
	 * by the client.
	 *
	 * If no match is found, the first, highest-ranking client requested
	 * type is returned.
	 *
	 * @param array $supported
	 *
	 * @return string
	 */
	public function encoding(array $supported = []): string
	{
		array_push($supported, 'identity');

		return $this->getBestMatch($supported, $this->request->getHeaderLine('accept-encoding'));
	}

	/**
	 * Determines the best language to use based on the $supported
	 * types the application says it supports, and the types requested
	 * by the client.
	 *
	 * If no match is found, the first, highest-ranking client requested
	 * type is returned.
	 *
	 * @param array $supported
	 *
	 * @return string
	 */
	public function language(array $supported): string
	{
		return $this->getBestMatch($supported, $this->request->getHeaderLine('accept-language'));
	}

	//--------------------------------------------------------------------
	// Utility Methods
	//--------------------------------------------------------------------

	/**
	 * Does the grunt work of comparing any of the app-supported values
	 * against a given Accept* header string.
	 *
	 * Portions of this code base on Aura.Accept library.
	 *
	 * @param array   $supported    App-supported values
	 * @param string  $header       header string
	 * @param boolean $enforceTypes If TRUE, will compare media types and sub-types.
	 * @param boolean $strictMatch  If TRUE, will return empty string on no match.
	 *                              If FALSE, will return the first supported element.
	 *
	 * @return string Best match
	 */
	protected function getBestMatch(array $supported, string $header = null, bool $enforceTypes = false, bool $strictMatch = false): string
	{
		if (empty($supported))
		{
			throw new HTTPException('You must provide an array of supported values to all Negotiations.');
		}

		if (empty($header))
		{
			return $strictMatch ? '' : $supported[0];
		}

		$acceptable = $this->parseHeader($header);

		foreach ($acceptable As $accept)
		{
			// if acceptable quality is zero, skip it.
			if ($accept['q'] === 0.0)
			{
				continue;
			}

			// if acceptable value is "anything", return the first available
			if ($accept['value'] === '*' OR $accept['value'] === '*/*')
			{
				return $supported[0];
			}

			// If an acceptable value is supported, return it
			foreach ($supported As $available)
			{
				if ($this->match($accept, $available, $enforceTypes))
				{
					return $available;
				}
			}
		}

		// No matches? Return the first supported element.
		return $strictMatch ? '' : $supported[0];
	}

	/**
	 * Parses an Accept* header into it's multiple values.
	 *
	 * This is based on code from Aura.Accept library.
	 *
	 * @param string $header
	 *
	 * @return array
	 */
	public function parseHeader(string $header): array
	{
		$results    = [];
		$acceptable = explode(',', $header);

		foreach ($acceptable As $value)
		{
			$pairs = explode(';', $value);

			$value = $pairs[0];

			unset($pairs[0]);

			$parameters = [];

			foreach ($pairs As $pair)
			{
				$param = [];
				preg_match(
						'/^(?P<name>.+?)=(?P<quoted>"|\')?(?P<value>.*?)(?:\k<quoted>)?$/', $pair, $param
				);
				$parameters[trim($param['name'])] = trim($param['value']);
			}

			$quality = 1.0;

			if (array_key_exists('q', $parameters))
			{
				$quality = $parameters['q'];
				unset($parameters['q']);
			}

			$results[] = [
				'value'  => trim($value),
				'q'      => (float) $quality,
				'params' => $parameters,
			];
		}

		// Sort to get the highest results first
		usort($results, function ($a, $b) {
			if ($a['q'] === $b['q'])
			{
				$a_ast = substr_count($a['value'], '*');
				$b_ast = substr_count($b['value'], '*');

				// '*/*' has lower precedence than 'text/*',
				// and 'text/*' has lower priority than 'text/plain'
				//
				// This seems backwards, but needs to be that way
				// due to the way PHP7 handles ordering or array
				// elements created by reference.
				if ($a_ast > $b_ast)
				{
					return 1;
				}

				// If the counts are the same, but one element
				// has more params than another, it has higher precedence.
				//
				// This seems backwards, but needs to be that way
				// due to the way PHP7 handles ordering or array
				// elements created by reference.
				if ($a_ast === $b_ast)
				{
					return count($b['params']) - count($a['params']);
				}

				return 0;
			}

			// Still here? Higher q values have precedence.
			return ($a['q'] > $b['q']) ? -1 : 1;
		});

		return $results;
	}

	/**
	 * Match-maker
	 *
	 * @param  array   $acceptable
	 * @param  string  $supported
	 * @param  boolean $enforceTypes
	 * @return boolean
	 */
	protected function match(array $acceptable, string $supported, bool $enforceTypes = false): bool
	{
		$supported = $this->parseHeader($supported);
		if (is_array($supported) AND count($supported) === 1)
		{
			$supported = $supported[0];
		}

		// Is it an exact match?
		if ($acceptable['value'] === $supported['value'])
		{
			return $this->matchParameters($acceptable, $supported);
		}

		// Do we need to compare types/sub-types? Only used
		// by negotiateMedia().
		if ($enforceTypes)
		{
			return $this->matchTypes($acceptable, $supported);
		}

		return false;
	}

	/**
	 * Checks two Accept values with matching 'values' to see if their
	 * 'params' are the same.
	 *
	 * @param array $acceptable
	 * @param array $supported
	 *
	 * @return boolean
	 */
	protected function matchParameters(array $acceptable, array $supported): bool
	{
		if (count($acceptable['params']) !== count($supported['params']))
		{
			return false;
		}

		foreach ($supported['params'] As $label => $value)
		{
			if (! isset($acceptable['params'][$label]) OR $acceptable['params'][$label] !== $value)
			{
				return false;
			}
		}

		return true;
	}

	/**
	 * Compares the types/subtypes of an acceptable Media type and
	 * the supported string.
	 *
	 * @param array $acceptable
	 * @param array $supported
	 *
	 * @return boolean
	 */
	public function matchTypes(array $acceptable, array $supported): bool
	{
		list($aType, $aSubType) = explode('/', $acceptable['value']);
		list($sType, $sSubType) = explode('/', $supported['value']);

		// If the types don't match, we're done.
		if ($aType !== $sType)
		{
			return false;
		}

		// If there's an asterisk, we're cool
		if ($aSubType === '*')
		{
			return true;
		}

		// Otherwise, subtypes must match also.
		return $aSubType === $sSubType;
	}
}
