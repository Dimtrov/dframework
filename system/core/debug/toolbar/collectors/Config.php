<?php

namespace dFramework\core\debug\toolbar\collectors;

use dFramework\core\dFramework;
use dFramework\core\Config as CoreConfig;

/**
 * Debug toolbar configuration
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
			'dfVersion'   => dFramework::VERSION,
			'phpVersion'  => phpversion(),
			'phpSAPI'     => php_sapi_name(),
			'environment' => $config->environment ?? 'dev',
			'baseURL'     => $config->base_url ?? site_url(),
			'locale'      => $config->language ?? 'en',
		];
	}
}
