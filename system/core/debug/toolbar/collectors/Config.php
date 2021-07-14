<?php
/**
 *  dFramework
 *
 *  The simplest PHP framework for beginners
 *  Copyright (c) 2019 - 2021, Dimtrov Lab's
 *  This content is released under the Mozilla Public License 2 (MPL-2.0)
 *
 *  @package	dFramework
 *  @author	    Dimitri Sitchet Tomkeu <dev.dst@gmail.com>
 *  @copyright	Copyright (c) 2019 - 2021, Dimtrov Lab's. (https://dimtrov.hebfree.org)
 *  @copyright	Copyright (c) 2019 - 2021, Dimitri Sitchet Tomkeu. (https://www.facebook.com/dimtrovich)
 *  @license	https://opensource.org/licenses/MPL-2.0 MPL-2.0 License
 *  @homepage	https://dimtrov.hebfree.org/works/dframework
 *  @version    3.3.0
 */

namespace dFramework\core\debug\toolbar\collectors;

use dFramework\core\dFramework;
use dFramework\core\Config as CoreConfig;

/**
 * Config
 *
 * Debug toolbar configuration
 *
 * @package		dFramework
 * @subpackage	Core
 * @category 	Debug/toolbar
 * @author		Dimitri Sitchet Tomkeu <dev.dst@gmail.com>
 * @link		https://dimtrov.hebfree.org/docs/dframework/api/
 * @since       3.3.0
 * @credit		CodeIgniter 4.0 - CodeIgniter\Debug\Toolbar\Collectors\Config
 * @file		/system/core/debug/toolbar/collectors/Config.php
 */
class Config
{
	/**
	 * Return toolbar config values as an array.
	 *
	 * @return array
	 */
	public static function display(): array
	{
		$config = (object) CoreConfig::get('general');

		return [
			'dFrameworkVersion' => dFramework::VERSION,
			'serverVersion'     => $_SERVER['SERVER_SOFTWARE'] ?? '',
			'phpVersion'        => phpversion(),
			'os'                => PHP_OS_FAMILY,
			'phpSAPI'           => php_sapi_name(),
			'environment'       => $config->environment ?? 'dev',
			'baseURL'           => $config->base_url ?? '',
			'documentRoot'      => $_SERVER['DOCUMENT_ROOT'] ?? WEBROOT,
			'locale'            => $config->language ?? 'en',
		];
	}
}
