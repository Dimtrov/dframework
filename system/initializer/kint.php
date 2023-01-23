<?php

declare(strict_types=1);

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
 *  @license    https://opensource.org/licenses/MPL-2.0 MPL-2.0 License
 *  @link	    https://dimtrov.hebfree.org/works/dframework
 *  @version    3.4.0
 */

 /**
  * Initialisation of framework dependencies
  *
  * @package	dFramework
  * @author		Dimitri Sitchet Tomkeu <devcode.dst@gmail.com>
  * @since      3.4.0
  * @file       /system/dependencies/initializer.php
  */

if (\dFramework\core\utilities\Helpers::is_online() OR \defined('KINT_DIR'))
{
    return;
}

if (\version_compare(PHP_VERSION, '7.1') < 0)
{
    throw new Exception('Kint 5 requires PHP 7.1 or higher');
}

\define('KINT_DIR', SYST_DIR . 'dependencies' . DS . 'kint-php' . DS . 'kint');
\define('KINT_WIN', DIRECTORY_SEPARATOR !== '/');
\define('KINT_PHP72', \version_compare(PHP_VERSION, '7.2') >= 0);
\define('KINT_PHP73', \version_compare(PHP_VERSION, '7.3') >= 0);
\define('KINT_PHP74', \version_compare(PHP_VERSION, '7.4') >= 0);
\define('KINT_PHP80', \version_compare(PHP_VERSION, '8.0') >= 0);
\define('KINT_PHP81', \version_compare(PHP_VERSION, '8.1') >= 0);
\define('KINT_PHP82', \version_compare(PHP_VERSION, '8.2') >= 0);
\define('KINT_PHP83', \version_compare(PHP_VERSION, '8.3') >= 0);

// Dynamic default settings
if (false !== \ini_get('xdebug.file_link_format'))
{
    \Kint\Kint::$file_link_format = \ini_get('xdebug.file_link_format');
}
if (isset($_SERVER['DOCUMENT_ROOT']))
{
    \Kint\Kint::$app_root_dirs = [
        $_SERVER['DOCUMENT_ROOT'] => '<ROOT>',
    ];

    // Suppressed for unreadable document roots (related to open_basedir)
    if (false !== @\realpath($_SERVER['DOCUMENT_ROOT']))
	{
        \Kint\Kint::$app_root_dirs[\realpath($_SERVER['DOCUMENT_ROOT'])] = '<ROOT>';
    }
}

\Kint\Utils::composerSkipFlags();

if ((!\defined('KINT_SKIP_FACADE') || !KINT_SKIP_FACADE) && !\class_exists('Kint'))
{
    \class_alias(\Kint\Kint::class, 'Kint');
}

if (!\defined('KINT_SKIP_HELPERS') || !KINT_SKIP_HELPERS)
{
	if (!\function_exists('dd'))
	{
		/**
		 * Prints a Kint debug report and exits.
		 *
		 * @param array ...$vars
		 *
		 * @codeCoverageIgnore Can't be tested ... exits
		 */
		function dd(...$vars)
		{
			\Kint\Kint::dump(...$vars);
			exit;
		}

		\Kint\Kint::$aliases[] = 'dd';
	}

	if (!\function_exists('d')) {
		/**
		 * Alias of Kint::dump().
		 *
		 * @param mixed ...$args
		 *
		 * @return int|string
		 */
		function d(...$args)
		{
			return \Kint\Kint::dump(...$args);
		}

    	\Kint\Kint::$aliases[] = 'd';
	}

	if (!\function_exists('s')) {
		/**
		 * Alias of Kint::dump(), however the output is in plain text.
		 *
		 * Alias of Kint::dump(), however the output is in plain htmlescaped text
		 * with some minor visibility enhancements added.
		 *
		 * If run in CLI colors are disabled
		 *
		 * @param mixed ...$args
		 *
		 * @return int|string
		 */
		function s(...$args)
		{
			if (false === \Kint\Kint::$enabled_mode)
			{
				return 0;
			}

			$kstash = \Kint\Kint::$enabled_mode;
			$cstash = \Kint\Renderer\CliRenderer::$cli_colors;

			if (\Kint\Kint::MODE_TEXT !== \Kint\Kint::$enabled_mode)
			{
				\Kint\Kint::$enabled_mode = \Kint\Kint::MODE_PLAIN;

				if (PHP_SAPI === 'cli' && true === \Kint\Kint::$cli_detection)
				{
					\Kint\Kint::$enabled_mode = \Kint\Kint::$mode_default_cli;
				}
			}

			\Kint\Renderer\CliRenderer::$cli_colors = false;

			$out = \Kint\Kint::dump(...$args);

			\Kint\Kint::$enabled_mode = $kstash;
			\Kint\Renderer\CliRenderer::$cli_colors = $cstash;

			return $out;
		}

	    \Kint\Kint::$aliases[] = 's';
	}
}
