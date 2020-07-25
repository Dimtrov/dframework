<?php
/**
 * dFramework
 *
 * The simplest PHP framework for beginners
 * Copyright (c) 2019, Dimtrov Sarl
 * This content is released under the Mozilla Public License 2 (MPL-2.0)
 *
 * @package	    dFramework
 * @author	    Dimitri Sitchet Tomkeu <dev.dst@gmail.com>
 * @copyright	Copyright (c) 2019, Dimtrov Sarl. (https://dimtrov.hebfree.org)
 * @copyright	Copyright (c) 2019, Dimitri Sitchet Tomkeu. (https://www.facebook.com/dimtrovich)
 * @license	    https://opensource.org/licenses/MPL-2.0 MPL-2.0 License
 * @link	    https://dimtrov.hebfree.org/works/dframework
 * @version     3.2.1
 */
 
namespace dFramework\core\output;

use dFramework\core\Config;

/**
 * Language
 * 
 * Handle system messages and localization.
 * Locale-based, built on top of PHP internationalization.
 *
 * @package		dFramework
 * @subpackage	Core
 * @category    Output
 * @author		Dimitri Sitchet Tomkeu <dev.dst@gmail.com>
 * @link		https://dimtrov.hebfree.org/docs/dframework/api
 * @since       3.2.1
 * @file		/system/core/output/Language.php
 */
class Language
{
	/**
	 * Stores the retrieved language lines
	 * from files for faster retrieval on
	 * second use.
	 *
	 * @var array
	 */
	protected $language = [];

	/**
	 * The current language/locale to work with.
	 *
	 * @var string
	 */
	protected $locale;

	/**
	 * Boolean value whether the intl
	 * libraries exist on the system.
	 *
	 * @var boolean
	 */
	protected $intlSupport = false;

	/**
	 * Stores filenames that have been
	 * loaded so that we don't load them again.
	 *
	 * @var array
	 */
	protected $loadedFiles = [];

	//--------------------------------------------------------------------

	public function __construct(string $locale)
	{
		$this->locale = $locale;

		if (class_exists('\MessageFormatter'))
		{
			$this->intlSupport = true;
		};
	}

	/**
	 * Sets the current locale to use when performing string lookups.
	 *
	 * @param string $locale
	 *
	 * @return $this
	 */
	public function setLocale(string $locale = null)
	{
		if (! is_null($locale))
		{
			$this->locale = $locale;
		}

		return $this;
	}

	/**
	 * @return string
	 */
	public function getLocale(): string
	{
		return $this->locale;
	}

	/**
	 * Parses the language string for a file, loads the file, if necessary,
	 * getting the line.
	 *
	 * @param string $line Line.
	 * @param array  $args Arguments.
	 *
	 * @return string|string[] Returns line.
	 */
	public function getLine(string $line, array $args = [])
	{
		// ignore requests with no file specified
		if (! strpos($line, '.'))
		{
			return $line;
		}

		// Parse out the file name and the actual alias.
		// Will load the language file and strings.
        [
			$file,
			$parsedLine,
		] = $this->parseLine($line, $this->locale);

		$output = $this->language[$this->locale][$file][$parsedLine] ?? null;

		if ($output === null AND strpos($this->locale, '-'))
		{
			[$locale] = explode('-', $this->locale, 2);

			[
				$file,
				$parsedLine,
			] = $this->parseLine($line, $locale);

			$output = $this->language[$locale][$file][$parsedLine] ?? null;
		}

		// if still not found, try English
		if (empty($output))
		{
			$this->parseLine($line, 'en');
			$output = $this->language['en'][$file][$parsedLine] ?? null;
		}

		$output = $output ?? $line;

		if (! empty($args))
		{
			$output = $this->formatMessage($output, $args);
		}

		return $output;
	}

	//--------------------------------------------------------------------

	/**
	 * Parses the language string which should include the
	 * filename as the first segment (separated by period).
	 *
	 * @param string $line
	 * @param string $locale
	 *
	 * @return array
	 */
	protected function parseLine(string $line, string $locale): array
	{
		/*
		$file = substr($line, 0, strpos($line, '.'));
		$line = substr($line, strlen($file) + 1);
		*/
		$line = explode('.', $line);
		$file = array_shift($line);
		$line = implode('.', $line);
		
		if (!isset($this->language[$locale][$file]) OR !array_key_exists($line, $this->language[$locale][$file]))
		{
			$this->load($file, $locale);
		}

		return [
			$file,
			$line,
		];
	}

	//--------------------------------------------------------------------

	/**
	 * Advanced message formatting.
	 *
	 * @param string|array $message Message.
	 * @param array	       $args    Arguments.
	 *
	 * @return string|array Returns formatted message.
	 */
	protected function formatMessage($message, array $args = [])
	{
		if (! $this->intlSupport || ! $args)
		{
			return $message;
		}

		if (is_array($message))
		{
			foreach ($message as $index => $value)
			{
				$message[$index] = $this->formatMessage($value, $args);
			}
			return $message;
		}

		return \MessageFormatter::formatMessage($this->locale, $message, $args);
	}

	//--------------------------------------------------------------------

	/**
	 * Loads a language file in the current locale. If $return is true,
	 * will return the file's contents, otherwise will merge with
	 * the existing language lines.
	 *
	 * @param string  $file
	 * @param string  $locale
	 * @param boolean $return
	 *
	 * @return array|null
	 */
	protected function load(string $file, string $locale, bool $return = false)
	{
		if (!array_key_exists($locale, $this->loadedFiles))
		{
			$this->loadedFiles[$locale] = [];
		}
		if (in_array($file, $this->loadedFiles[$locale]))
		{
			// Don't load it more than once.
			return [];
		}
		if (!array_key_exists($locale, $this->language))
		{
			$this->language[$locale] = [];
		}

		if (!array_key_exists($file, $this->language[$locale]))
		{
			$this->language[$locale][$file] = [];
		}

		$path = "lang/{$locale}/{$file}.json";


		





		$lang = $this->requireFile($path);

		if ($return)
		{
			return $lang;
		}

		$this->loadedFiles[$locale][] = $file;

		// Merge our string
		$this->language[$locale][$file] = $lang;
	}

	//--------------------------------------------------------------------

	/**
	 * A simple method for including files that can be
	 * overridden during testing.
	 *
	 * @param string $path
	 *
	 * @return array
	 */
	protected function requireFile(string $path): array
	{
		$files   = Services::locator()->search($path);
		$strings = [];

		foreach ($files as $file)
		{
			// On some OS's we were seeing failures
			// on this command returning boolean instead
			// of array during testing, so we've removed
			// the require_once for now.
			if (is_file($file))
			{
				$strings[] = require $file;
			}
		}

		if (isset($strings[1]))
		{
			$strings = array_replace_recursive(...$strings);
		}
		elseif (isset($strings[0]))
		{
			$strings = $strings[0];
		}

		return $strings;
	}

	//--------------------------------------------------------------------
}
