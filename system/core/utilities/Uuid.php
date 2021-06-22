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
 *  @version    3.3.0
 */

namespace dFramework\core\utilities;

/**
 * Uuid
 *
 * @package		dFramework
 * @subpackage	Core
 * @category    Utilities
 * @author		Dimitri Sitchet Tomkeu <devcode.dst@gmail.com>
 * @link		https://dimtrov.hebfree.org/docs/dframework/api/
 * @since       3.3.0
 * @credit		https://www.php.net/manual/en/function.uniqid.php#94959
 * @file        /system/core/utilities/Uuid.php
 */
class Uuid
{
	/**
	 * Generate an UUID V3
	 *
	 * @param string $namespace
	 * @param string $name
	 * @return string|false
	 */
	public static function v3(string $namespace, string $name)
	{
	  	if (!self::is_valid($namespace))
	  	{
		  	return false;
	  	}
	  	$nhex = str_replace(array('-','{','}'), '', $namespace);

	  	$nstr = '';

	  	for ($i = 0; $i < strlen($nhex); $i+=2)
	  	{
			$nstr .= chr(hexdec($nhex[$i].$nhex[$i+1]));
	  	}

	  	$hash = md5($nstr . $name);

	  	return sprintf('%08s-%04s-%04x-%04x-%12s',
  			substr($hash, 0, 8),
  			substr($hash, 8, 4),
  			(hexdec(substr($hash, 12, 4)) & 0x0fff) | 0x3000,
  			(hexdec(substr($hash, 16, 4)) & 0x3fff) | 0x8000,
  			substr($hash, 20, 12)
	  	);
	}

	/**
	 * Generate an UUID V4
	 *
	 * @return string
	 */
	public static function v4() : string
	{
	  	return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
  			mt_rand(0, 0xffff), mt_rand(0, 0xffff),
  			mt_rand(0, 0xffff),
  			mt_rand(0, 0x0fff) | 0x4000,
  			mt_rand(0, 0x3fff) | 0x8000,
  			mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
	  	);
	}

	/**
	 * Generate an UUID V5
	 *
	 * @param string $namespace
	 * @param string $name
	 * @return string|false
	 */
	public static function v5(string $namespace, string $name)
	{
	  	if (!self::is_valid($namespace))
		{
			return false;
		}
		$nhex = str_replace(array('-','{','}'), '', $namespace);

	  	$nstr = '';
  		for ($i = 0; $i < strlen($nhex); $i+=2)
		{
			$nstr .= chr(hexdec($nhex[$i].$nhex[$i+1]));
	  	}
  		$hash = sha1($nstr . $name);

	  	return sprintf('%08s-%04s-%04x-%04x-%12s',
  			substr($hash, 0, 8),
  			substr($hash, 8, 4),
  			(hexdec(substr($hash, 12, 4)) & 0x0fff) | 0x5000,
  			(hexdec(substr($hash, 16, 4)) & 0x3fff) | 0x8000,
  			substr($hash, 20, 12)
	  	);
	}

	/**
	 * Check if the uuid is valid
	 *
	 * @param string $uuid
	 * @return bool
	 */
	public static function is_valid(string $uuid) : bool
	{
	  	return preg_match('/^\{?[0-9a-f]{8}\-?[0-9a-f]{4}\-?[0-9a-f]{4}\-?'.
						'[0-9a-f]{4}\-?[0-9a-f]{12}\}?$/i', $uuid) === 1;
	}
}
