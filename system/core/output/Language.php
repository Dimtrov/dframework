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
 * @link	    https://dimtrov.hebfree.org/works/dframework
 * @version     3.3.4
 */

namespace dFramework\core\output;

use dFramework\core\Config;
use dFramework\core\loader\FileLocator;
use dFramework\core\loader\Service;
use dFramework\core\utilities\Arr;

/**
 * Language
 *
 * Handle system messages and localization.
 * Locale-based, built on top of PHP internationalization.
 *
 * @package		dFramework
 * @subpackage	Core
 * @category    Output
 * @author		Dimitri Sitchet Tomkeu <devcode.dst@gmail.com>
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

	public function __construct()
	{
		if (class_exists('\MessageFormatter'))
		{
			$this->intlSupport = true;
		};
	}

	/**
	 * Sets the current locale to use when performing string lookups.
	 *
	 * @param string|null $locale
	 * @return self
	 */
	public function setLocale(?string $locale = null) : self
	{
		$this->findLocale($locale);

		return $this;
	}

	/**
	 * Gets the current locale, with a fallback to the default
     * locale if none is set.
     *
	 * @return string
	 */
	public function getLocale():  string
	{
		if (empty($this->locale))
		{
			$this->findLocale();
		}
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
	public function getLine(string $line, ?array $args = [])
	{
		// ignore requests with no file specified
		if (! strpos($line, '.'))
		{
			return $line;
		}
		if (empty($args))
		{
			$args = [];
		}

		// Parse out the file name and the actual alias.
		// Will load the language file and strings.
        [
			$file,
			$parsedLine,
		] = $this->parseLine($line, $this->locale);

		$output = Arr::getRecursive($this->language[$this->locale][$file], $parsedLine);

		if ($output === null AND strpos($this->locale, '-'))
		{
			[$locale] = explode('-', $this->locale, 2);

			[
				$file,
				$parsedLine,
			] = $this->parseLine($line, $locale);

			$output = Arr::getRecursive($this->language[$locale][$file], $parsedLine);
		}

		// if still not found, try English
		if (empty($output))
		{
			$this->parseLine($line, 'en');

			$output = Arr::getRecursive($this->language[$this->locale][$file], $parsedLine);
			//$output = $this->language['en'][$file][$parsedLine] ?? null;
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
		$file = substr($line, 0, strpos($line, '.'));
		$line = substr($line, strlen($file) + 1);
		/*
		$line = explode('.', $line);
		$file = array_shift($line);
		$line = implode('.', $line);
		*/

		if (!isset($this->language[$locale][$file]) OR !array_key_exists($line, $this->language[$locale][$file]))
		{
			$this->load($file, $locale);
		}

		return [
			$file,
			$line,
		];
	}

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
		if (!$this->intlSupport OR !$args)
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

		$lang = FileLocator::lang($file, $locale);

		if ($return)
		{
			return $lang;
		}

		$this->loadedFiles[$locale][] = $file;

		// Merge our string
		$this->language[$locale][$file] = $lang;
	}

	/**
	 * Cherche la locale appropriee par rapport a la requete de l'utilisateur
	 *
	 * @param string|null $locale
	 * @return string
	 */
	public static function searchLocale(?string $locale = null) : string
	{
		$config = Config::get('general');

		if (empty($locale))
		{
			$locale = Service::negotiator()->language($config['supported_locales']);
		}
		if (empty($locale))
		{
			$locale = $config['general.language'];
		}

		return self::normalizeLocale(empty($locale) ? 'en' : $locale);
	}
	private function findLocale(?string $locale = null) : string
	{
		$this->locale = self::searchLocale($locale);
		Config::set('general.language', $this->locale);

		return $this->locale;
	}
	/**
	 * Valide la langue entree
	 *
	 * @param string $locale
	 * @return string
	 */
	private static function normalizeLocale(string $locale) : string
	{
		return $locale;
	}
}
