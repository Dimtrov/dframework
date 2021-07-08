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
 *  @version    3.3.2
 */

use dFramework\core\utilities\Inflector;

/**
 * dFramework Inflector Helpers
 *
 * @package		dFramework
 * @subpackage	Helpers
 * @category	Helpers
 * @since 		3.0
 * @credit		CodeIgniter - by EllisLab Dev Team - https://codeigniter.com/user_guide/helpers/inflector_helper.html
 * @credit		CakePHP (Cake\Utilities\Inflector - https://cakephp.org)
 */

// --------------------------------------------------------------------


if (!function_exists('camelize'))
{
	/**
	 * Camelize
	 *
	 * Takes multiple words separated by spaces or underscores and camelizes them
	 *
	 * @param	string	$str	Input string
	 * @return	string
	 */
	function camelize(string $str) : string
	{
		return Inflector::camelize($str);
	}
}

if (!function_exists('classify'))
{
	 /**
     * Returns model class name ("Person" for the database table "people".) for given database table.
     *
     * @param string $tableName Name of database table to get class name for
     * @return string Class name
     */
    function classify(string $tableName) : string
    {
        return Inflector::classify($tableName);
    }
}

if (!function_exists('dasherize'))
{
	/**
     * Returns the input CamelCasedString as an dashed-string.
     *
     * Also replaces underscores with dashes
     *
     * @param string $string The string to dasherize.
     * @return string Dashed version of the input string
     */
    function dasherize(string $string) : string
    {
        return Inflector::dasherize($string);
    }
}

if (!function_exists('delimit'))
{
	/**
     * Expects a CamelCasedInputString, and produces a lower_case_delimited_string
     *
     * @param string $string String to delimit
     * @param string $delimiter the character to use as a delimiter
     * @return string delimited string
     */
    function delimit(string $string, string $delimiter = '_') : string
    {
        return Inflector::delimit($string, $delimiter);
    }
}

if (!function_exists('humanize'))
{
	/**
	 * Humanize
	 *
	 * Takes multiple words separated by the separator and changes them to spaces
	 *
	 * @param	string	$str		Input string
	 * @param 	string	$separator	Input separator
	 * @return	string
	 */
	function humanize(string $str, string $separator = '_') : string
	{
		return Inflector::humanize($str, $separator);
	}
}

if (!function_exists('is_countable'))
{
	/**
	 * Checks if the given word has a plural version.
	 *
	 * @param	string	$word	Word to check
	 * @return	bool
	 */
	function is_countable(string $word) : bool
	{
		return ! in_array(
			strtolower($word),
			array(
				'audio',
				'bison',
				'chassis',
				'compensation',
				'coreopsis',
				'data',
				'deer',
				'education',
				'emoji',
				'equipment',
				'fish',
				'furniture',
				'gold',
				'information',
				'knowledge',
				'love',
				'rain',
				'money',
				'moose',
				'nutrition',
				'offspring',
				'plankton',
				'pokemon',
				'police',
				'rice',
				'series',
				'sheep',
				'species',
				'swine',
				'traffic',
				'wheat'
			)
		);
	}
}

if (!function_exists('plural'))
{
	/**
	 * Plural
	 *
	 * Takes a singular word and makes it plural
	 *
	 * @param	string	$str	Input string
	 * @return	string
	 */
	function plural($str)
	{
		$result = strval($str);

		if ( ! is_countable($result))
		{
			return $result;
		}

		$plural_rules = array(
			'/(quiz)$/'                => '\1zes',      // quizzes
			'/^(ox)$/'                 => '\1\2en',     // ox
			'/([m|l])ouse$/'           => '\1ice',      // mouse, louse
			'/(matr|vert|ind)ix|ex$/'  => '\1ices',     // matrix, vertex, index
			'/(x|ch|ss|sh)$/'          => '\1es',       // search, switch, fix, box, process, address
			'/([^aeiouy]|qu)y$/'       => '\1ies',      // query, ability, agency
			'/(hive)$/'                => '\1s',        // archive, hive
			'/(?:([^f])fe|([lr])f)$/'  => '\1\2ves',    // half, safe, wife
			'/sis$/'                   => 'ses',        // basis, diagnosis
			'/([ti])um$/'              => '\1a',        // datum, medium
			'/(p)erson$/'              => '\1eople',    // person, salesperson
			'/(m)an$/'                 => '\1en',       // man, woman, spokesman
			'/(c)hild$/'               => '\1hildren',  // child
			'/(buffal|tomat)o$/'       => '\1\2oes',    // buffalo, tomato
			'/(bu|campu)s$/'           => '\1\2ses',    // bus, campus
			'/(alias|status|virus)$/'  => '\1es',       // alias
			'/(octop)us$/'             => '\1i',        // octopus
			'/(ax|cris|test)is$/'      => '\1es',       // axis, crisis
			'/s$/'                     => 's',          // no change (compatibility)
			'/$/'                      => 's',
		);

		foreach ($plural_rules as $rule => $replacement)
		{
			if (preg_match($rule, $result))
			{
				$result = preg_replace($rule, $replacement, $result);
				break;
			}
		}

		return $result;
	}
}

if (!function_exists('pluralize'))
{
	/**
     * Return $word in plural form.
     *
     * @param string $word Word in singular
     * @return string Word in plural
     */
    function pluralize(string $word) : string
    {
        return Inflector::pluralize($word);
    }
}

if (!function_exists('singular'))
{
	/**
	 * Singular
	 *
	 * Takes a plural word and makes it singular
	 *
	 * @param	string	$str	Input string
	 * @return	string
	 */
	function singular($str)
	{
		$result = strval($str);

		if ( ! is_countable($result))
		{
			return $result;
		}

		$singular_rules = array(
			'/(matr)ices$/'		=> '\1ix',
			'/(vert|ind)ices$/'	=> '\1ex',
			'/^(ox)en/'		=> '\1',
			'/(alias)es$/'		=> '\1',
			'/([octop|vir])i$/'	=> '\1us',
			'/(cris|ax|test)es$/'	=> '\1is',
			'/(shoe)s$/'		=> '\1',
			'/(o)es$/'		=> '\1',
			'/(bus|campus)es$/'	=> '\1',
			'/([m|l])ice$/'		=> '\1ouse',
			'/(x|ch|ss|sh)es$/'	=> '\1',
			'/(m)ovies$/'		=> '\1\2ovie',
			'/(s)eries$/'		=> '\1\2eries',
			'/([^aeiouy]|qu)ies$/'	=> '\1y',
			'/([lr])ves$/'		=> '\1f',
			'/(tive)s$/'		=> '\1',
			'/(hive)s$/'		=> '\1',
			'/([^f])ves$/'		=> '\1fe',
			'/(^analy)ses$/'	=> '\1sis',
			'/((a)naly|(b)a|(d)iagno|(p)arenthe|(p)rogno|(s)ynop|(t)he)ses$/' => '\1\2sis',
			'/([ti])a$/'		=> '\1um',
			'/(p)eople$/'		=> '\1\2erson',
			'/(m)en$/'		=> '\1an',
			'/(s)tatuses$/'		=> '\1\2tatus',
			'/(c)hildren$/'		=> '\1\2hild',
			'/(n)ews$/'		=> '\1\2ews',
			'/(quiz)zes$/'		=> '\1',
			'/([^us])s$/'		=> '\1'
		);

		foreach ($singular_rules as $rule => $replacement)
		{
			if (preg_match($rule, $result))
			{
				$result = preg_replace($rule, $replacement, $result);
				break;
			}
		}

		return $result;
	}
}

if (!function_exists('singularize'))
{
	/**
     * Return $word in singular form.
     *
     * @param string $word Word in plural
     * @return string Word in singular
     */
    function singularize (string $word) : string
    {
		return Inflector::singularize($word);
	}
}

if (!function_exists('tableize'))
{
	/**
     * Returns corresponding table name for given model $className. ("people" for the model class "Person").
     *
     * @param string $className Name of class to get database table name for
     * @return string Name of the database table for given class
     */
    function tableize(string $className) : string
    {
        return Inflector::tableize($className);
    }
}

if (!function_exists('underscore'))
{
	/**
	 * Underscore
	 *
	 * Takes multiple words separated by spaces and underscores them
	 *
	 * @param	string	$str	Input string
	 * @return	string
	 */
	function underscore(string $str) : string
	{
		return Inflector::underscore($str);
	}
}

if (!function_exists('variable'))
{
	 /**
     * Returns camelBacked version of an underscored string.
     *
     * @param string $string String to convert.
     * @return string in variable form
     */
    function variable(string $string) : string
	{
		return Inflector::variable($string);
	}
}
