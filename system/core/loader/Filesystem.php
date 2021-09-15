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
 *  @homepage	https://dimtrov.hebfree.org/works/dframework
 *  @version    3.3.4
 */

namespace dFramework\core\loader;

use dFramework\core\exception\LoadException;
use ErrorException;
use FilesystemIterator;
use Symfony\Component\Finder\Finder;
use dFramework\core\support\traits\Macroable;

/**
 * Filesystem
 *
 * @package		dFramework
 * @subpackage	Core
 * @category    Loader
 * @author		Dimitri Sitchet Tomkeu <devcode.dst@gmail.com>
 * @since       3.3.0
 * @file		/system/core/loader/Filesystem.php
 */
class Filesystem
{
    use Macroable;

    /**
     * Determine if a file or directory exists.
     *
     * @param  string  $path
     * @return bool
     */
    public static function exists(string $path) : bool
    {
        return file_exists($path);
    }

    /**
     * Get the contents of a file.
     *
     * @param  string  $path
     * @param  bool  $lock
     * @return string
     */
    public static function get(string $path, bool $lock = false) : string
    {
        if (self::isFile($path))
        {
            return $lock ? self::sharedGet($path) : file_get_contents($path);
        }

        throw new LoadException("File does not exist at path {$path}");
    }

    /**
     * Get contents of a file with shared access.
     *
     * @param  string  $path
     * @return string
     */
    public static function sharedGet(string $path) : string
    {
        $contents = '';

        $handle = fopen($path, 'rb');

        if ($handle)
        {
            try {
                if (flock($handle, LOCK_SH))
                {
                    clearstatcache(true, $path);

                    $contents = fread($handle, self::size($path) ?: 1);

                    flock($handle, LOCK_UN);
                }
            }
            finally {
                fclose($handle);
            }
        }

        return $contents;
    }

    /**
     * Get the returned value of a file.
     *
     * @param  string  $path
     * @return mixed
     */
    public static function getRequire(string $path)
    {
        if (self::isFile($path))
        {
            return require $path;
        }

        throw new LoadException("File does not exist at path {$path}");
    }

    /**
     * Require the given file once.
     *
     * @param  string  $file
     * @return mixed
     */
    public static function requireOnce(string $file)
    {
        require_once $file;
    }

    /**
     * Get the MD5 hash of the file at the given path.
     *
     * @param  string  $path
     * @return string
     */
    public static function hash(string $path) : string
    {
        return md5_file($path);
    }

    /**
     * Write the contents of a file.
     *
     * @param  string  $path
     * @param  string  $contents
     * @param  bool  $lock
     * @return int|bool
     */
    public static function put(string $path, string $contents, bool $lock = false)
    {
        return file_put_contents($path, $contents, $lock ? LOCK_EX : 0);
    }

    /**
     * Write the contents of a file, replacing it atomically if it already exists.
     *
     * @param  string  $path
     * @param  string  $content
     * @return void
     */
    public static function replace(string $path, string $content)
    {
        // If the path already exists and is a symlink, get the real path...
        clearstatcache(true, $path);

        $path = realpath($path) ?: $path;

        $tempPath = tempnam(dirname($path), basename($path));

        // Fix permissions of tempPath because `tempnam()` creates it with permissions set to 0600...
        chmod($tempPath, 0777 - umask());

        file_put_contents($tempPath, $content);

        rename($tempPath, $path);
    }

    /**
     * Prepend to a file.
     *
     * @param  string  $path
     * @param  string  $data
     * @return int
     */
    public static function prepend(string $path, string $data) : int
    {
        if (self::exists($path))
        {
            return self::put($path, $data.self::get($path));
        }

        return self::put($path, $data);
    }

    /**
     * Append to a file.
     *
     * @param  string  $path
     * @param  string  $data
     * @return int
     */
    public static function append(string $path, string $data) : int
    {
        return file_put_contents($path, $data, FILE_APPEND);
    }

    /**
     * Get or set UNIX mode of a file or directory.
     *
     * @param  string  $path
     * @param  int|null  $mode
     * @return mixed
     */
    public static function chmod(string $path, ?int $mode = null)
    {
        if ($mode) {
            return chmod($path, $mode);
        }

        return substr(sprintf('%o', fileperms($path)), -4);
    }

    /**
     * Delete the file at a given path.
     *
     * @param  string|array  $paths
     * @return bool
     */
    public static function delete($paths) : bool
    {
        $paths = is_array($paths) ? $paths : func_get_args();

        $success = true;

        foreach ($paths as $path)
        {
            try {
                if (! @unlink($path))
                {
                    $success = false;
                }
            }
            catch (ErrorException $e) {
                $success = false;
            }
        }

        return $success;
    }

    /**
     * Move a file to a new location.
     *
     * @param  string  $path
     * @param  string  $target
     * @return bool
     */
    public static function move(string $path, string $target) : bool
    {
        return rename($path, $target);
    }

    /**
     * Copy a file to a new location.
     *
     * @param  string  $path
     * @param  string  $target
     * @return bool
     */
    public static function copy(string $path, string $target) : bool
    {
        return copy($path, $target);
    }

    /**
     * Create a hard link to the target file or directory.
     *
     * @param  string  $target
     * @param  string  $link
     * @return bool|void
     */
    public static function link(string $target, string $link)
    {
        if (! is_windows())
        {
            return symlink($target, $link);
        }

        $mode = self::isDirectory($target) ? 'J' : 'H';

        exec("mklink /{$mode} ".escapeshellarg($link).' '.escapeshellarg($target));
    }

    /**
     * Extract the file name from a file path.
     *
     * @param  string  $path
     * @return string
     */
    public static function name(string $path) : string
    {
        return pathinfo($path, PATHINFO_FILENAME);
    }

    /**
     * Extract the trailing name component from a file path.
     *
     * @param  string  $path
     * @return string
     */
    public static function basename(string $path) : string
    {
        return pathinfo($path, PATHINFO_BASENAME);
    }

    /**
     * Extract the parent directory from a file path.
     *
     * @param  string  $path
     * @return string
     */
    public static function dirname(string $path) : string
    {
        return pathinfo($path, PATHINFO_DIRNAME);
    }

    /**
     * Extract the file extension from a file path.
     *
     * @param  string  $path
     * @return string
     */
    public static function extension(string $path) : string
    {
        return pathinfo($path, PATHINFO_EXTENSION);
    }

    /**
     * Get the file type of a given file.
     *
     * @param  string  $path
     * @return string
     */
    public static function type(string $path) : string
    {
        return filetype($path);
    }

    /**
     * Get the mime-type of a given file.
     *
     * @param  string  $path
     * @return string|false
     */
    public static function mimeType(string $path)
    {
        return finfo_file(finfo_open(FILEINFO_MIME_TYPE), $path);
    }

    /**
     * Get the file size of a given file.
     *
     * @param  string  $path
     * @return int
     */
    public static function size(string $path) : int
    {
        return filesize($path);
    }

    /**
     * Get the file's last modification time.
     *
     * @param  string  $path
     * @return int
     */
    public static function lastModified(string $path) : int
    {
        return filemtime($path);
    }

    /**
     * Determine if the given path is a directory.
     *
     * @param  string  $directory
     * @return bool
     */
    public static function isDirectory(string $directory) : bool
    {
        return is_dir($directory);
    }

    /**
     * Determine if the given path is readable.
     *
     * @param  string  $path
     * @return bool
     */
    public static function isReadable(string $path) : bool
    {
        return is_readable($path);
    }

    /**
     * Determine if the given path is writable.
     *
     * @param  string  $path
     * @return bool
     */
    public static function isWritable(string $path) : bool
    {
        return is_writable($path);
    }

    /**
     * Determine if the given path is a file.
     *
     * @param  string  $file
     * @return bool
     */
    public static function isFile(string $file) : bool
    {
        return is_file($file);
    }

    /**
     * Find path names matching a given pattern.
     *
     * @param  string  $pattern
     * @param  int     $flags
     * @return array
     */
    public static function glob(string $pattern, int $flags = 0) : array
    {
        return glob($pattern, $flags);
    }

    /**
     * Get an array of all files in a directory.
     *
     * @param  string  $directory
     * @param  bool  $hidden
     * @param  string  $sortBy
     * @return \Symfony\Component\Finder\SplFileInfo[]
     */
    public static function files(string $directory, bool $hidden = false, string $sortBy = 'name') : array
    {
		$files = Finder::create()->files()->ignoreDotFiles(! $hidden)->in($directory)->depth(0);

		switch (strtolower($sortBy))
		{
			case 'type':
				$files = $files->sortByType();
				break;
			case 'modifiedtime':
			case 'modified':
				$files = $files->sortByModifiedTime();
				break;
			case 'changedtime':
			case 'changed':
				$files = $files->sortByChangedTime();
				break;
			case 'accessedtime':
			case 'accessed':
				$files = $files->sortByAccessedTime();
				break;
			default:
				$files = $files->sortByName();
				break;
		}

		return iterator_to_array($files, false);
    }

    /**
     * Get all of the files from the given directory (recursive).
     *
     * @param  string  $directory
	 * @param  bool  $hidden
     * @param  string  $sortBy
     * @return \Symfony\Component\Finder\SplFileInfo[]
     */
    public static function allFiles(string $directory, bool $hidden = false, string $sortBy = 'name') : array
    {
		$files = Finder::create()->files()->ignoreDotFiles(! $hidden)->in($directory);

		switch (strtolower($sortBy))
		{
			case 'type':
				$files = $files->sortByType();
				break;
			case 'modifiedtime':
			case 'modified':
				$files = $files->sortByModifiedTime();
				break;
			case 'changedtime':
			case 'changed':
				$files = $files->sortByChangedTime();
				break;
			case 'accessedtime':
			case 'accessed':
				$files = $files->sortByAccessedTime();
				break;
			default:
				$files = $files->sortByName();
				break;
		}

        return iterator_to_array($files, false);
    }

    /**
     * Get all of the directories within a given directory.
     *
     * @param  string  $directory
     * @return array
     */
    public static function directories(string $directory) : array
    {
        $directories = [];

        foreach (Finder::create()->in($directory)->directories()->depth(0)->sortByName() As $dir)
        {
            $directories[] = $dir->getPathname();
        }

        return $directories;
    }

    /**
     * Create a directory.
     *
     * @param  string  $path
     * @param  int     $mode
     * @param  bool    $recursive
     * @param  bool    $force
     * @return bool
     */
    public static function makeDirectory(string $path, int $mode = 0755, bool $recursive = false, bool $force = false) : bool
    {
        if ($force)
        {
            return @mkdir($path, $mode, $recursive);
        }

        return mkdir($path, $mode, $recursive);
    }

    /**
     * Move a directory.
     *
     * @param  string  $from
     * @param  string  $to
     * @param  bool  $overwrite
     * @return bool
     */
    public static function moveDirectory(string $from, string $to, bool $overwrite = false) : bool
    {
        if ($overwrite AND self::isDirectory($to) AND ! self::deleteDirectory($to))
        {
            return false;
        }

        return @rename($from, $to) === true;
    }

    /**
     * Copy a directory from one location to another.
     *
     * @param  string  $directory
     * @param  string  $destination
     * @param  int|null  $options
     * @return bool
     */
    public static function copyDirectory(string $directory, string $destination, ?int $options = null) : bool
    {
        if (!self::isDirectory($directory))
        {
            return false;
        }

        $options = $options ?: FilesystemIterator::SKIP_DOTS;

        // If the destination directory does not actually exist, we will go ahead and
        // create it recursively, which just gets the destination prepared to copy
        // the files over. Once we make the directory we'll proceed the copying.
        if (!self::isDirectory($destination))
        {
            self::makeDirectory($destination, 0777, true);
        }

        $items = new FilesystemIterator($directory, $options);

        foreach ($items As $item)
        {
            // As we spin through items, we will check to see if the current file is actually
            // a directory or a file. When it is actually a directory we will need to call
            // back into this function recursively to keep copying these nested folders.
            $target = $destination.'/'.$item->getBasename();

            if ($item->isDir())
            {
                $path = $item->getPathname();

                if (! self::copyDirectory($path, $target, $options))
                {
                    return false;
                }
            }

            // If the current items is just a regular file, we will just copy this to the new
            // location and keep looping. If for some reason the copy fails we'll bail out
            // and return false, so the developer is aware that the copy process failed.
            else
            {
                if (! self::copy($item->getPathname(), $target))
                {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Recursively delete a directory.
     *
     * The directory itself may be optionally preserved.
     *
     * @param  string  $directory
     * @param  bool    $preserve
     * @return bool
     */
    public static function deleteDirectory(string $directory, bool $preserve = false) : bool
    {
        if (! self::isDirectory($directory))
        {
            return false;
        }

        $items = new FilesystemIterator($directory);

        foreach ($items As $item)
        {
            // If the item is a directory, we can just recurse into the function and
            // delete that sub-directory otherwise we'll just delete the file and
            // keep iterating through each file until the directory is cleaned.
            if ($item->isDir() AND ! $item->isLink())
            {
                self::deleteDirectory($item->getPathname());
            }

            // If the item is just a file, we can go ahead and delete it since we're
            // just looping through and waxing all of the files in this directory
            // and calling directories recursively, so we delete the real path.
            else
            {
                self::delete($item->getPathname());
            }
        }

        if (! $preserve)
        {
            @rmdir($directory);
        }

        return true;
    }

    /**
     * Remove all of the directories within a given directory.
     *
     * @param  string  $directory
     * @return bool
     */
    public static function deleteDirectories($directory) : bool
    {
        $allDirectories = self::directories($directory);

        if (! empty($allDirectories))
        {
            foreach ($allDirectories As $directoryName)
            {
                self::deleteDirectory($directoryName);
            }

            return true;
        }

        return false;
    }

    /**
     * Empty the specified directory of all files and folders.
     *
     * @param  string  $directory
     * @return bool
     */
    public static function cleanDirectory($directory) : bool
    {
        return self::deleteDirectory($directory, true);
    }
}
