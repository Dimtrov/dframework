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

namespace dFramework\core\support;

use ReflectionClass;
use Composer\Script\Event;

/**
 * ComposerScripts
 *
 * These scripts are used by Composer during installs and updates
 * to move files to locations within the system folder so that end-users
 * do not need to use Composer to install a package, but can simply
 * download
 *
 * @codeCoverageIgnore
 * @package		dFramework
 * @subpackage	Core
 * @category    Support
 * @author		Dimitri Sitchet Tomkeu <dev.dst@gmail.com>
 * @link		https://dimtrov.hebfree.org/docs/dframework/api/Controller.html
 * @since       3.3.0
 * @file		/system/core/support/ComposerScripts.php
 */
class ComposerScripts
{
	/**
	 * @var string Base path to use.
	 */
	protected static $basePath = 'dependencies/';

	private static $composer_files = [];

	private static $dframework_files = [];

    /**
	 * After composer install/update, this is called to move
	 * the bare-minimum required files for our dependencies
	 * to appropriate locations.
	 *
	 * @throws \ReflectionException
	 */
	
	/**
     * @param Event $event
     */
    public static function postInstall(Event $event)
    {
        self::run($event);
	}
	/**
     * @param Event $event
     */
    public static function postUpdate(Event $event)
    {
        self::run($event);
	}

	private static function run(Event $event)
	{
		$autoload_classmap_file = $event->getComposer()->getConfig()->get('vendor-dir').'/autoload_classmap.php';
		if (file_exists($autoload_classmap_file) AND !in_array($autoload_classmap_file, \get_included_files())) 
		{
			self::$composer_files = require $autoload_classmap_file;
		}

        $autoload_classmap_file = dirname(__DIR__, 2).'/constants/.classmap.php';
		if (file_exists($autoload_classmap_file) AND !in_array($autoload_classmap_file, \get_included_files())) 
		{
			self::$dframework_files = require $autoload_classmap_file;
		}

		foreach ($composer_files as $class => $path) 
		{
			if (array_key_exists($class, self::$dframework_files) AND strpos($class, 'dFramework\\') == false) 
			{
				self::moveFile($path, self::$dframework_files[$class]);
			}
		}
	}



	//--------------------------------------------------------------------

	/**
	 * Move a file.
	 *
	 * @param string $source
	 * @param string $destination
	 *
	 * @return boolean
	 */
	protected static function moveFile(string $source, string $destination): bool
	{
		$source = realpath($source);

		if (empty($source))
		{
			die('Cannot move file. Source path invalid.');
		}
		if (! is_file($source))
		{
			return false;
		}
		return copy($source, $destination);
	}

	/**
	 * Determine file path of a class.
	 *
	 * @param string $class
	 *
	 * @return string
	 * @throws \ReflectionException
	 */
	protected static function getClassFilePath(string $class)
	{
		$reflector = new ReflectionClass($class);

		return $reflector->getFileName();
	}

	/**
	 * A recursive remove directory method.
	 *
	 * @param $dir
	 */
	protected static function removeDir($dir)
	{
		if (is_dir($dir))
		{
			$objects = scandir($dir);
			foreach ($objects as $object)
			{
				if ($object !== '.' && $object !== '..')
				{
					if (filetype($dir . '/' . $object) === 'dir')
					{
						static::removeDir($dir . '/' . $object);
					}
					else
					{
						unlink($dir . '/' . $object);
					}
				}
			}
			reset($objects);
			rmdir($dir);
		}
	}

	protected static function copyDir($source, $dest)
	{
		$dir = opendir($source);
		@mkdir($dest);

		while (false !== ( $file = readdir($dir)))
		{
			if (( $file !== '.' ) && ( $file !== '..' ))
			{
				if (is_dir($source . '/' . $file))
				{
					static::copyDir($source . '/' . $file, $dest . '/' . $file);
				}
				else
				{
					copy($source . '/' . $file, $dest . '/' . $file);
				}
			}
		}

		closedir($dir);
	}
}
