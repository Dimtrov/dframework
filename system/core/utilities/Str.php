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

use Jawira\CaseConverter\Convert;
use dFramework\core\support\traits\Macroable;

/**
 * Str
 *
 * @package		dFramework
 * @subpackage	Core
 * @category    Utilities
 * @author		Dimitri Sitchet Tomkeu <devcode.dst@gmail.com>
 * @link		https://dimtrov.hebfree.org/docs/dframework/api/
 * @since       2.1
 * @credit      CakePHP (Cake\Utility\Text - https://cakephp.org)
 * @credit      Laravel (Illuminate\Support\Str - https://laravel.com)
 * @file        /system/core/utilities/Str.php
 */
class Str
{
    use Macroable;

	/**
     * Default transliterator.
     *
     * @var \Transliterator Transliterator instance.
     */
    protected static $_defaultTransliterator;

    /**
     * Default transliterator id string.
     *
     * @var string $_defaultTransliteratorId Transliterator identifier string.
     */
    protected static $_defaultTransliteratorId = 'Any-Latin; Latin-ASCII; [\u0080-\u7fff] remove';

	/**
     * Default html tags who must not be count for truncate text.
     *
     * @var array
     */
    protected static $_defaultHtmlNoCount = [
        'style',
        'script',
    ];

    public static function __callStatic($name, $arguments)
    {
        /**
         * Conversion de casse d'ecriture
         */
        if (\preg_match("#^to(.*)(Case)?$#", $name))
        {
            $available_case = ['camel', 'pascal', 'snake', 'ada', 'macro', 'kebab', 'train', 'cobol', 'lower', 'upper', 'title', 'sentence', 'dot'];

            $name = preg_replace("#Case$#", '', $name);
            $name = str_replace('to', '', \strtolower($name));
            if (\in_array($name, $available_case))
            {
                $name = 'to'.\ucfirst($name);
                return (new Convert($arguments[0]))->$name();
            }
            return $arguments[0];
        }
    }


    /**
     * Return the remainder of a string after a given value.
     *
     * @param  string  $subject
     * @param  string  $search
     * @return string
     */
    public static function after(string $subject, string $search) : string
    {
        return $search === '' ? $subject : array_reverse(explode($search, $subject, 2))[0];
    }

    /**
     * Transliterate a UTF-8 value to ASCII.
     *
     * @param  string  $value
     * @param  string  $language
     * @return string
     */
    public static function ascii(string $value, string $language = 'en') : string
    {
        $languageSpecific = static::languageSpecificCharsArray($language);

        if (! is_null($languageSpecific))
        {
            $value = str_replace($languageSpecific[0], $languageSpecific[1], $value);
        }

        foreach (static::charsArray() as $key => $val)
        {
            $value = str_replace($val, $key, $value);
        }

        return preg_replace('/[^\x20-\x7E]/u', '', $value);
    }

    /**
     * Get the portion of a string before a given value.
     *
     * @param  string  $subject
     * @param  string  $search
     * @return string
     */
    public static function before(string $subject, string $search) : string
    {
        return $search === '' ? $subject : explode($search, $subject)[0];
    }

    /**
     * Cleans up a Text::insert() formatted string with given $options depending on the 'clean' key in
     * $options. The default method used is text but html is also available. The goal of this function
     * is to replace all whitespace and unneeded markup around placeholders that did not get replaced
     * by Text::insert().
     *
     * @param string $str String to clean.
     * @param array $options Options list.
     * @return string
     */
    public static function cleanInsert(string $str, array $options) : string
    {
        $clean = $options['clean'];
        if (!$clean)
		{
            return $str;
        }
        if ($clean === true)
		{
            $clean = ['method' => 'text'];
        }
        if (!is_array($clean))
		{
            $clean = ['method' => $options['clean']];
        }
        switch ($clean['method'])
		{
            case 'html':
                $clean += [
                    'word' => '[\w,.]+',
                    'andText' => true,
                    'replacement' => '',
                ];
                $kleenex = sprintf(
                    '/[\s]*[a-z]+=(")(%s%s%s[\s]*)+\\1/i',
                    preg_quote($options['before'], '/'),
                    $clean['word'],
                    preg_quote($options['after'], '/')
                );
                $str = preg_replace($kleenex, $clean['replacement'], $str);
                if ($clean['andText'])
				{
                    $options['clean'] = ['method' => 'text'];
                    $str = static::cleanInsert($str, $options);
                }
                break;
            case 'text':
                $clean += [
                    'word' => '[\w,.]+',
                    'gap' => '[\s]*(?:(?:and|or)[\s]*)?',
                    'replacement' => '',
                ];

                $kleenex = sprintf(
                    '/(%s%s%s%s|%s%s%s%s)/',
                    preg_quote($options['before'], '/'),
                    $clean['word'],
                    preg_quote($options['after'], '/'),
                    $clean['gap'],
                    $clean['gap'],
                    preg_quote($options['before'], '/'),
                    $clean['word'],
                    preg_quote($options['after'], '/')
                );
                $str = preg_replace($kleenex, $clean['replacement'], $str);
                break;
        }

        return $str;
    }

    /**
	 * Clean UTF-8 strings
	 *
	 * Ensures strings contain only valid UTF-8 characters.
	 *
	 * @param	string	$str	String to clean
	 * @return	string
	 */
	public static function cleanStr(string $str)
	{
		if (self::isAscii($str) === FALSE)
		{
			if (MB_ENABLED)
			{
				$str = mb_convert_encoding($str, 'UTF-8', 'UTF-8');
			}
			else if (ICONV_ENABLED)
			{
				$str = @iconv('UTF-8', 'UTF-8//IGNORE', $str);
			}
		}

		return $str;
    }
	/**
	 * @deprecated 3.3.1
	 * @see \dFramework\core\utilities\Str::cleanStr()
	 */
    public static function clean_string(string $str)
	{
		return self::cleanStr($str);
	}

    /**
     * Determine if a given string contains a given substring.
     *
     * @param  string  $haystack
     * @param  string|array  $needles
     * @return bool
     */
    public static function contains(string $haystack, $needles) : bool
    {
        foreach ((array) $needles As $needle)
        {
            if ($needle !== '' AND mb_strpos($haystack, $needle) !== false)
            {
                return true;
            }
        }

        return false;
    }

    /**
     * Determine if a given string contains all array values.
     *
     * @param  string  $haystack
     * @param  array  $needles
     * @return bool
     */
    public static function containsAll(string $haystack, array $needles) : bool
    {
        foreach ($needles as $needle)
        {
            if (! static::contains($haystack, $needle))
            {
                return false;
            }
        }

        return true;
    }

	/**
	 * Convert to UTF-8
	 *
	 * Attempts to convert a string to UTF-8.
	 *
	 * @param	string	$str		Input string
	 * @param	string	$encoding	Input encoding
	 * @return	string|false	$str encoded in UTF-8 or FALSE on failure
	 */
	public function convert_to_utf8($str, $encoding)
	{
		if (MB_ENABLED)
		{
			return mb_convert_encoding($str, 'UTF-8', $encoding);
		}
		if (ICONV_ENABLED)
		{
			return @iconv($encoding, 'UTF-8', $str);
		}

		return FALSE;
	}

    /**
     * Determine if a given string ends with a given substring.
     *
     * @param  string  $haystack
     * @param  string|array  $needles
     * @return bool
     */
    public static function endsWith(string $haystack, $needles) : bool
    {
        foreach ((array) $needles as $needle)
        {
            if (substr($haystack, -strlen($needle)) === (string) $needle)
            {
                return true;
            }
        }

        return false;
    }

    /**
     * Extracts an excerpt from the text surrounding the phrase with a number of characters on each side
     * determined by radius.
     *
     * @param string $text String to search the phrase in
     * @param string $phrase Phrase that will be searched for
     * @param int $radius The amount of characters that will be returned on each side of the founded phrase
     * @param string $ellipsis Ending that will be appended
     * @return string Modified string
     */
    public static function excerpt(string $text, string $phrase, int $radius = 100, string $ellipsis = '...') : string
    {
        if (empty($text) OR empty($phrase))
        {
            return static::truncate($text, $radius * 2, ['ellipsis' => $ellipsis]);
        }

        $append = $prepend = $ellipsis;

        $phraseLen = mb_strlen($phrase);
        $textLen = mb_strlen($text);

        $pos = mb_stripos($text, $phrase);
        if ($pos === false)
        {
            return mb_substr($text, 0, $radius) . $ellipsis;
        }

        $startPos = $pos - $radius;
        if ($startPos <= 0)
        {
            $startPos = 0;
            $prepend = '';
        }

        $endPos = $pos + $phraseLen + $radius;
        if ($endPos >= $textLen)
        {
            $endPos = $textLen;
            $append = '';
        }

        $excerpt = mb_substr($text, $startPos, $endPos - $startPos);
        $excerpt = $prepend . $excerpt . $append;

        return $excerpt;
    }

    /**
     * Cap a string with a single instance of a given value.
     *
     * @param  string  $value
     * @param  string  $cap
     * @return string
     */
    public static function finish(string $value, string $cap) : string
    {
        $quoted = preg_quote($cap, '/');

        return preg_replace('/(?:'.$quoted.')+$/u', '', $value).$cap;
    }

    /**
     * Highlights a given phrase in a text. You can specify any expression in highlighter that
     * may include the \1 expression to include the $phrase found.
     *
     * ### Options:
     *
     * - `format` The piece of HTML with that the phrase will be highlighted
     * - `html` If true, will ignore any HTML tags, ensuring that only the correct text is highlighted
     * - `regex` A custom regex rule that is used to match words, default is '|$tag|iu'
     * - `limit` A limit, optional, defaults to -1 (none)
     *
     * @param string $text Text to search the phrase in.
     * @param string|array $phrase The phrase or phrases that will be searched.
     * @param array $options An array of HTML attributes and options.
     * @return string The highlighted text
     */
    public static function highlight(string $text, $phrase, array $options = [])
    {
        if (empty($phrase))
		{
            return $text;
        }

        $defaults = [
            'format' => '<span class="highlight">\1</span>',
            'html' => false,
            'regex' => '|%s|iu',
            'limit' => -1,
        ];
        $options += $defaults;

        $html = $format = $limit = null;
        /**
         * @var bool $html
         * @var string|array $format
         * @var int $limit
         */
        extract($options);

        if (is_array($phrase))
		{
            $replace = [];
            $with = [];

            foreach ($phrase As $key => $segment)
			{
                $segment = '(' . preg_quote($segment, '|') . ')';
                if ($html)
				{
                    $segment = "(?![^<]+>)$segment(?![^<]+>)";
                }

                $with[] = is_array($format) ? $format[$key] : $format;
                $replace[] = sprintf($options['regex'], $segment);
            }

            return preg_replace($replace, $with, $text, $limit);
        }

        $phrase = '(' . preg_quote($phrase, '|') . ')';
        if ($html)
		{
            $phrase = "(?![^<]+>)$phrase(?![^<]+>)";
        }

        return preg_replace(sprintf($options['regex'], $phrase), $format, $text, $limit);
    }

    /**
     * Replaces variable placeholders inside a $str with any given $data. Each key in the $data array
     * corresponds to a variable placeholder name in $str.
     * Example:
     * ```
     * Str::insert(':name is :age years old.', ['name' => 'Bob', 'age' => '65']);
     * ```
     * Returns: Bob is 65 years old.
     *
     * Available $options are:
     *
     * - before: The character or string in front of the name of the variable placeholder (Defaults to `:`)
     * - after: The character or string after the name of the variable placeholder (Defaults to null)
     * - escape: The character or string used to escape the before character / string (Defaults to `\`)
     * - format: A regex to use for matching variable placeholders. Default is: `/(?<!\\)\:%s/`
     *   (Overwrites before, after, breaks escape / clean)
     * - clean: A boolean or array with instructions for Text::cleanInsert
     *
     * @param string $str A string containing variable placeholders
     * @param array $data A key => val array where each key stands for a placeholder variable name
     *     to be replaced with val
     * @param array $options An array of options, see description above
     * @return string
     */
    public static function insert(string $str, $data, array $options = []) : string
    {
        $defaults = [
            'before' => ':', 'after' => null, 'escape' => '\\', 'format' => null, 'clean' => false,
        ];
        $options += $defaults;
        $format = $options['format'];
        $data = (array)$data;
        if (empty($data))
		{
            return $options['clean'] ? static::cleanInsert($str, $options) : $str;
        }

        if (!isset($format))
		{
            $format = sprintf(
                '/(?<!%s)%s%%s%s/',
                preg_quote($options['escape'], '/'),
                str_replace('%', '%%', preg_quote($options['before'], '/')),
                str_replace('%', '%%', preg_quote($options['after'], '/'))
            );
        }

        if (strpos($str, '?') !== false && is_numeric(key($data)))
		{
            $offset = 0;
            while (($pos = strpos($str, '?', $offset)) !== false)
			{
                $val = array_shift($data);
                $offset = $pos + strlen($val);
                $str = substr_replace($str, $val, $pos, 1);
            }

            return $options['clean'] ? static::cleanInsert($str, $options) : $str;
        }

        $dataKeys = array_keys($data);
        $hashKeys = array_map('crc32', $dataKeys);
        $tempData = array_combine($dataKeys, $hashKeys);
        krsort($tempData);

        foreach ($tempData As $key => $hashVal)
		{
            $key = sprintf($format, preg_quote($key, '/'));
            $str = preg_replace($key, $hashVal, $str);
        }
        $dataReplacements = array_combine($hashKeys, array_values($data));

		foreach ($dataReplacements As $tmpHash => $tmpValue)
		{
            $tmpValue = is_array($tmpValue) ? '' : $tmpValue;
            $str = str_replace($tmpHash, $tmpValue, $str);
        }

        if (!isset($options['format']) AND isset($options['before']))
		{
            $str = str_replace($options['escape'] . $options['before'], $options['before'], $str);
        }

        return $options['clean'] ? static::cleanInsert($str, $options) : $str;
    }

    /**
     * Determine if a given string matches a given pattern.
     *
     * @param  string|array  $pattern
     * @param  string  $value
     * @return bool
     */
    public static function is(string $pattern, string $value) : bool
    {
        $patterns = Arr::wrap($pattern);

        if (empty($patterns))
        {
            return false;
        }

        foreach ($patterns As $pattern)
        {
            // If the given value is an exact match we can of course return true right
            // from the beginning. Otherwise, we will translate asterisks and do an
            // actual pattern match against the two strings to see if they match.
            if ($pattern == $value)
            {
                return true;
            }

            $pattern = preg_quote($pattern, '#');

            // Asterisks are translated into zero-or-more regular expression wildcards
            // to make it convenient to check if the strings starts with the given
            // pattern such as "library/*", making any string check convenient.
            $pattern = str_replace('\*', '.*', $pattern);

            if (preg_match('#^'.$pattern.'\z#u', $value) === 1)
            {
                return true;
            }
        }

        return false;
    }

    /**
	 * Is ASCII?
	 *
	 * Tests if a string is standard 7-bit ASCII or not.
	 *
	 * @param	string	$str	String to check
	 * @return	bool
	 */
	public static function isAscii(string $str) : bool
	{
		return (preg_match('/[^\x00-\x7F]/S', $str) === 0);
	}
	/**
	 * @deprecated 3.3.1
	 * @see \dFramework\core\utilities\Str::isAscii()
	 */
	public static function is_ascii(string $str) : bool
	{
		return static::isAscii($str);
	}

    /**
     * Return the length of the given string.
     *
     * @param  string  $value
     * @param  string  $encoding
     * @return int
     */
    public static function length(string $value, string $encoding = null) : int
    {
        if ($encoding)
        {
            return mb_strlen($value, $encoding);
        }

        return mb_strlen($value);
    }

    /**
     * Limit the number of characters in a string.
     *
     * @param  string  $value
     * @param  int     $limit
     * @param  string  $end
     * @return string
     */
    public static function limit(string $value, int $limit = 100, string $end = '...') : string
    {
        if (mb_strwidth($value, 'UTF-8') <= $limit)
        {
            return $value;
        }

        return rtrim(mb_strimwidth($value, 0, $limit, '', 'UTF-8')).$end;
    }

    /**
     * Convert the given string to lower-case.
     *
     * @param  string  $value
     * @return string
     */
    public static function lower(string $value) : string
    {
        return mb_strtolower($value, 'UTF-8');
    }

    /**
     * Parse a Class@method style callback into class and method.
     *
     * @param  string  $callback
     * @param  string|null  $default
     * @return array
     */
    public static function parseCallback(string $callback, ?string $default = null) : array
    {
        return static::contains($callback, '@') ? explode('@', $callback, 2) : [$callback, $default];
    }

    /**
     * Generate a more truly "random" alpha-numeric string.
     *
     * @param  int  $length
     * @return string
     */
    public static function random(int $length = 16) : string
    {
        $string = '';

        while (($len = strlen($string)) < $length)
        {
            $size = $length - $len;

            $bytes = random_bytes($size);

            $string .= substr(str_replace(['/', '+', '='], '', base64_encode($bytes)), 0, $size);
        }

        return $string;
    }

	/**
     * Removes the last word from the input text.
     *
     * @param string $text The input text
     * @return string
     */
    public static function removeLastWord(string $text) : string
    {
        $spacepos = mb_strrpos($text, ' ');
        if ($spacepos !== false)
		{
            $lastWord = mb_strrpos($text, $spacepos);

            // Some languages are written without word separation.
            // We recognize a string as a word if it doesn't contain any full-width characters.
            if (mb_strwidth($lastWord) === mb_strlen($lastWord))
			{
                $text = mb_substr($text, 0, $spacepos);
            }

            return $text;
        }

        return '';
    }

    /**
     * Replace a given value in the string sequentially with an array.
     *
     * @param  string  $search
     * @param  array   $replace
     * @param  string  $subject
     * @return string
     */
    public static function replaceArray(string $search, array $replace, string $subject) : string
    {
        $segments = explode($search, $subject);

        $result = array_shift($segments);

        foreach ($segments as $segment)
        {
            $result .= (array_shift($replace) ?? $search).$segment;
        }

        return $result;
    }

    /**
     * Replace the first occurrence of a given value in the string.
     *
     * @param  string  $search
     * @param  string  $replace
     * @param  string  $subject
     * @return string
     */
    public static function replaceFirst(string $search, string $replace, string $subject) : string
    {
        if ($search == '')
        {
            return $subject;
        }

        $position = strpos($subject, $search);

        if ($position !== false)
        {
            return substr_replace($subject, $replace, $position, strlen($search));
        }

        return $subject;
    }

    /**
     * Replace the last occurrence of a given value in the string.
     *
     * @param  string  $search
     * @param  string  $replace
     * @param  string  $subject
     * @return string
     */
    public static function replaceLast(string $search, string $replace, string $subject) : string
    {
        $position = strrpos($subject, $search);

        if ($position !== false)
        {
            return substr_replace($subject, $replace, $position, strlen($search));
        }

        return $subject;
    }

    /**
	 * Remove ASCII control characters
	 *
	 * Removes all ASCII control characters except horizontal tabs,
	 * line feeds, and carriage returns, as all others can cause
	 * problems in XML.
	 *
	 * @param	string	$str	String to clean
	 * @return	string
	 */
	public function safe_ascii_for_xml(string $str)
	{
		return remove_invisible_characters($str, FALSE);
	}

    /**
     * Returns a string with all spaces converted to dashes (by default),
     * characters transliterated to ASCII characters, and non word characters removed.
     *
     * ### Options:
     *
     * - `replacement`: Replacement string. Default '-'.
     * - `transliteratorId`: A valid transliterator id string.
     *   If `null` (default) the transliterator (identifier) set via
     *   `setTransliteratorId()` or `setTransliterator()` will be used.
     *   If `false` no transliteration will be done, only non words will be removed.
     * - `preserve`: Specific non-word character to preserve. Default `null`.
     *   For e.g. this option can be set to '.' to generate clean file names.
     *
     * @param string $string the string you want to slug
     * @param array $options If string it will be use as replacement character
     *   or an array of options.
     * @return string
     * @see setTransliterator()
     * @see setTransliteratorId()
     */
    public static function slug(string $string, array $options = []) : string
    {
        if (is_string($options))
		{
            $options = ['replacement' => $options];
        }
        $options += [
            'replacement' => '-',
            'transliteratorId' => null,
            'preserve' => null,
        ];

        if ($options['transliteratorId'] !== false)
		{
            $string = static::transliterate($string, $options['transliteratorId']);
        }

        $regex = '^\p{Ll}\p{Lm}\p{Lo}\p{Lt}\p{Lu}\p{Nd}';
        if ($options['preserve'])
		{
            $regex .= preg_quote($options['preserve'], '/');
        }
        $quotedReplacement = preg_quote($options['replacement'], '/');
        $map = [
            '/[' . $regex . ']/mu' => $options['replacement'],
            sprintf('/^[%s]+|[%s]+$/', $quotedReplacement, $quotedReplacement) => '',
        ];
        if (is_string($options['replacement']) AND strlen($options['replacement']) > 0)
		{
            $map[sprintf('/[%s]+/mu', $quotedReplacement)] = $options['replacement'];
        }
        $string = preg_replace(array_keys($map), $map, $string);

        return $string;
    }

    /**
     * Begin a string with a single instance of a given value.
     *
     * @param  string  $value
     * @param  string  $prefix
     * @return string
     */
    public static function start(string $value, string $prefix) : string
    {
        $quoted = preg_quote($prefix, '/');

        return $prefix.preg_replace('/^(?:'.$quoted.')+/u', '', $value);
    }

    /**
     * Determine if a given string starts with a given substring.
     *
     * @param  string  $haystack
     * @param  string|array  $needles
     * @return bool
     */
    public static function startsWith(string $haystack, $needles) : bool
    {
        foreach ((array) $needles As $needle)
        {
            if ($needle !== '' AND substr($haystack, 0, strlen($needle)) === (string) $needle)
            {
                return true;
            }
        }

        return false;
    }

	/**
     * Get string length.
     *
     * ### Options:
     *
     * - `html` If true, HTML entities will be handled as decoded characters.
     * - `trimWidth` If true, the width will return.
     *
     * @param string $text The string being checked for length
     * @param array $options An array of options.
     * @return int
     */
    public static function strlen(string $text, array $options) : int
    {
        if (empty($options['trimWidth']))
		{
            $strlen = 'mb_strlen';
        }
		else
		{
            $strlen = 'mb_strwidth';
        }

        if (empty($options['html']))
		{
            return $strlen($text);
        }

        $pattern = '/&[0-9a-z]{2,8};|&#[0-9]{1,7};|&#x[0-9a-f]{1,6};/i';
        $replace = preg_replace_callback(
            $pattern,
            function ($match) use ($strlen) {
                $utf8 = html_entity_decode($match[0], ENT_HTML5 | ENT_QUOTES, 'UTF-8');

                return str_repeat(' ', $strlen($utf8, 'UTF-8'));
            },
            $text
        );

        return $strlen($replace);
    }

	/**
     * Returns the portion of string specified by the start and length parameters.
	 *
     * ### Options:
     *
     * - `html` If true, HTML entities will be handled as decoded characters.
     * - `trimWidth` If true, will be truncated with specified width.
     *
     * @param string $text The input string.
     * @param int $start The position to begin extracting.
     * @param int|null $length The desired length.
     * @param array $options An array of options.
     * @return string
     */
    protected static function substr(string $text, int $start, ?int $length = null, array $options) : string
    {
        if (empty($options['trimWidth']))
		{
            $substr = 'mb_substr';
        }
		else
		{
            $substr = 'mb_strimwidth';
        }

        $maxPosition = self::strlen($text, ['trimWidth' => false] + $options);
        if ($start < 0)
		{
            $start += $maxPosition;
            if ($start < 0)
			{
                $start = 0;
            }
        }
        if ($start >= $maxPosition)
		{
            return '';
        }

        if ($length === null)
		{
            $length = self::strlen($text, $options);
        }

        if ($length < 0)
		{
            $text = self::substr($text, $start, null, $options);
            $start = 0;
            $length += self::strlen($text, $options);
        }

        if ($length <= 0)
		{
            return '';
        }

        if (empty($options['html']))
		{
            return (string) $substr($text, $start, $length);
        }

        $totalOffset = 0;
        $totalLength = 0;
        $result = '';

        $pattern = '/(&[0-9a-z]{2,8};|&#[0-9]{1,7};|&#x[0-9a-f]{1,6};)/i';
        $parts = preg_split($pattern, $text, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
        foreach ($parts As $part)
		{
            $offset = 0;

            if ($totalOffset < $start)
			{
                $len = self::strlen($part, ['trimWidth' => false] + $options);
                if ($totalOffset + $len <= $start)
				{
                    $totalOffset += $len;
                    continue;
                }

                $offset = $start - $totalOffset;
                $totalOffset = $start;
            }

            $len = self::strlen($part, $options);
            if ($offset !== 0 OR $totalLength + $len > $length)
			{
                if (
                    strpos($part, '&') === 0 AND preg_match($pattern, $part)
					AND $part !== html_entity_decode($part, ENT_HTML5 | ENT_QUOTES, 'UTF-8')
                )
				{
                    // Entities cannot be passed substr.
                    continue;
                }

                $part = $substr($part, $offset, $length - $totalLength);
                $len = self::strlen($part, $options);
            }

            $result .= $part;
            $totalLength += $len;
            if ($totalLength >= $length)
			{
				break;
            }
        }

        return $result;
    }

    /**
     * Truncates text starting from the end.
     *
     * Cuts a string to the length of $length and replaces the first characters
     * with the ellipsis if the text is longer than length.
     *
     * ### Options:
     *
     * - `ellipsis` Will be used as beginning and prepended to the trimmed string
     * - `exact` If false, $text will not be cut mid-word
     *
     * @param string $text String to truncate.
     * @param int $length Length of returned string, including ellipsis.
     * @param array $options An array of options.
     * @return string Trimmed string.
     */
    public static function tail(string $text, int $length = 100, array $options = []) : string
    {
        $default = [
            'ellipsis' => '...', 'exact' => true,
        ];
        $options += $default;
        $exact = $ellipsis = null;
        /**
         * @var string $ellipsis
         * @var bool $exact
         */
        extract($options);

        if (mb_strlen($text) <= $length)
		{
            return $text;
        }

        $truncate = mb_substr($text, mb_strlen($text) - $length + mb_strlen($ellipsis));
        if (!$exact)
		{
            $spacepos = mb_strpos($truncate, ' ');
            $truncate = $spacepos === false ? '' : trim(mb_substr($truncate, $spacepos));
        }

        return $ellipsis . $truncate;
    }

    /**
     * Convert the given string to title case.
     *
     * @param  string  $value
     * @return string
     */
    public static function title(string $value) : string
    {
        return mb_convert_case($value, MB_CASE_TITLE, 'UTF-8');
    }

	/**
     * Tokenizes a string using $separator, ignoring any instance of $separator that appears between
     * $leftBound and $rightBound.
     *
     * @param string $data The data to tokenize.
     * @param string $separator The token to split the data on.
     * @param string $leftBound The left boundary to ignore separators in.
     * @param string $rightBound The right boundary to ignore separators in.
     * @return mixed Array of tokens in $data or original input if empty.
     */
    public static function tokenize(string $data, string $separator = ',', string $leftBound = '(', string $rightBound = ')')
    {
        if (empty($data))
		{
            return [];
        }

        $depth = 0;
        $offset = 0;
        $buffer = '';
        $results = [];
        $length = strlen($data);
        $open = false;

        while ($offset <= $length)
		{
            $tmpOffset = -1;
            $offsets = [
                strpos($data, $separator, $offset),
                strpos($data, $leftBound, $offset),
                strpos($data, $rightBound, $offset)
			];
            for ($i = 0; $i < 3; $i++)
			{
                if ($offsets[$i] !== false AND ($offsets[$i] < $tmpOffset OR $tmpOffset == -1))
				{
                    $tmpOffset = $offsets[$i];
                }
            }
            if ($tmpOffset !== -1)
			{
                $buffer .= substr($data, $offset, ($tmpOffset - $offset));
                if (!$depth AND $data[$tmpOffset] == $separator)
				{
                    $results[] = $buffer;
                    $buffer = '';
                }
				else
				{
                    $buffer .= $data[$tmpOffset];
                }
                if ($leftBound != $rightBound)
				{
                    if ($data[$tmpOffset] == $leftBound)
					{
                        $depth++;
                    }
                    if ($data[$tmpOffset] == $rightBound)
					{
                        $depth--;
                    }
                }
				else
				{
                    if ($data[$tmpOffset] == $leftBound)
					{
                        if (!$open)
						{
                            $depth++;
                            $open = true;
                        }
						else
						{
                            $depth--;
                        }
                    }
                }
                $offset = ++$tmpOffset;
            }
			else
			{
                $results[] = $buffer . substr($data, $offset);
                $offset = $length + 1;
            }
        }
        if (empty($results) AND !empty($buffer))
		{
            $results[] = $buffer;
        }

        if (!empty($results))
		{
            return array_map('trim', $results);
        }
        return [];
    }

    /**
     * Creates a comma separated list where the last two items are joined with 'and', forming natural language.
     *
     * @param string[] $list The list to be joined.
     * @param string $separator The separator used to join all the other items together. Defaults to ', '.
     * @param string $and The word used to join the last and second last items together with. Defaults to 'and'.
     * @return string The glued together string.
     */
    public static function toList(array $list, string $separator = ', ', string $and = 'and')
    {
        if (count($list) > 1)
		{
            return implode($separator, array_slice($list, 0, -1)) . ' ' . $and . ' ' . array_pop($list);
        }

        return array_pop($list);
    }

    /**
     * Transliterate string.
     *
     * @param string $string String to transliterate.
     * @param \Transliterator|string|null $transliterator Either a Transliterator
     *   instance, or a transliterator identifier string. If `null`, the default
     *   transliterator (identifier) set via `setTransliteratorId()` or
     *   `setTransliterator()` will be used.
     * @return string
     * @see https://secure.php.net/manual/en/transliterator.transliterate.php
     */
    public static function transliterate(string $string, $transliterator = null)
    {
        if (!$transliterator)
		{
            $transliterator = static::$_defaultTransliterator ?: static::$_defaultTransliteratorId;
        }

        return transliterator_transliterate($transliterator, $string);
    }

    /**
     * Truncates text.
     *
     * Cuts a string to the length of $length and replaces the last characters
     * with the ellipsis if the text is longer than length.
     *
     * ### Options:
     *
     * - `ellipsis` Will be used as ending and appended to the trimmed string
     * - `exact` If false, $text will not be cut mid-word
     * - `html` If true, HTML tags would be handled correctly
     * - `trimWidth` If true, $text will be truncated with the width
     *
     * @param string $text String to truncate.
     * @param int $length Length of returned string, including ellipsis.
     * @param array $options An array of HTML attributes and options.
     * @return string Trimmed string.
     */
    public static function truncate(string $text, int $length = 100, array $options = []) : string
    {
        $default = [
            'ellipsis' => '...', 'exact' => true, 'html' => false, 'trimWidth' => false,
        ];
        if (!empty($options['html']) AND strtolower(mb_internal_encoding()) === 'utf-8')
		{
            $default['ellipsis'] = "\xe2\x80\xa6";
        }
        $options += $default;

        $prefix = '';
        $suffix = $options['ellipsis'];

        if ($options['html'])
		{
            $ellipsisLength = self::strlen(strip_tags($options['ellipsis']), $options);

            $truncateLength = 0;
            $totalLength = 0;
            $openTags = [];
            $truncate = '';

            preg_match_all('/(<\/?([\w+]+)[^>]*>)?([^<>]*)/', $text, $tags, PREG_SET_ORDER);
            foreach ($tags As $tag)
			{
                $contentLength = 0;
                if (!in_array($tag[2], static::$_defaultHtmlNoCount, true))
				{
                    $contentLength = self::strlen($tag[3], $options);
                }

                if ($truncate === '')
				{
                    if (!preg_match('/img|br|input|hr|area|base|basefont|col|frame|isindex|link|meta|param/i', $tag[2]))
					{
                        if (preg_match('/<[\w]+[^>]*>/', $tag[0]))
						{
                            array_unshift($openTags, $tag[2]);
                        }
						elseif (preg_match('/<\/([\w]+)[^>]*>/', $tag[0], $closeTag))
						{
                            $pos = array_search($closeTag[1], $openTags, true);
                            if ($pos !== false)
							{
                                array_splice($openTags, $pos, 1);
                            }
                        }
                    }

                    $prefix .= $tag[1];

                    if ($totalLength + $contentLength + $ellipsisLength > $length)
					{
                        $truncate = $tag[3];
                        $truncateLength = $length - $totalLength;
                    }
					else
					{
                        $prefix .= $tag[3];
                    }
                }

                $totalLength += $contentLength;
                if ($totalLength > $length)
				{
                    break;
                }
            }

            if ($totalLength <= $length)
			{
                return $text;
            }

            $text = $truncate;
            $length = $truncateLength;

            foreach ($openTags as $tag)
			{
                $suffix .= '</' . $tag . '>';
            }
        }
		else
		{
            if (self::strlen($text, $options) <= $length)
			{
                return $text;
            }
            $ellipsisLength = self::strlen($options['ellipsis'], $options);
        }

        $result = self::substr($text, 0, $length - $ellipsisLength, $options);

        if (!$options['exact'])
		{
            if (self::substr($text, $length - $ellipsisLength, 1, $options) !== ' ')
			{
                $result = self::removeLastWord($result);
            }

            // If result is empty, then we don't need to count ellipsis in the cut.
            if (!strlen($result))
			{
                $result = self::substr($text, 0, $length, $options);
            }
        }

        return $prefix . $result . $suffix;
    }

	/**
     * Make a string's first character uppercase.
     *
     * @param  string  $string
     * @return string
     */
    public static function ucfirst(string $string) : string
    {
        return static::upper(static::substr($string, 0, 1)).static::substr($string, 1);
    }

    /**
     * Convert the given string to upper-case.
     *
     * @param  string  $value
     * @return string
     */
    public static function upper(string $value) : string
    {
        return mb_strtoupper($value, 'UTF-8');
    }

    /**
     * Limit the number of words in a string.
     *
     * @param  string  $value
     * @param  int     $words
     * @param  string  $end
     * @return string
     */
    public static function words(string $value, int $words = 100, string $end = '...') : string
    {
        preg_match('/^\s*+(?:\S++\s*+){1,'.$words.'}/u', $value, $matches);

        if (! isset($matches[0]) OR static::length($value) === static::length($matches[0]))
        {
            return $value;
        }

        return rtrim($matches[0]).$end;
    }


	/**
     * Returns the replacements for the ascii method.
     *
     * Note: Adapted from Stringy\Stringy.
     *
     * @see https://github.com/danielstjules/Stringy/blob/3.1.0/LICENSE.txt
     *
     * @return array
     */
    protected static function charsArray() : array
    {
        static $charsArray;

        if (isset($charsArray))
        {
            return $charsArray;
        }

        return $charsArray = [
            '0'    => ['°', '₀', '۰', '０'],
            '1'    => ['¹', '₁', '۱', '１'],
            '2'    => ['²', '₂', '۲', '２'],
            '3'    => ['³', '₃', '۳', '３'],
            '4'    => ['⁴', '₄', '۴', '٤', '４'],
            '5'    => ['⁵', '₅', '۵', '٥', '５'],
            '6'    => ['⁶', '₆', '۶', '٦', '６'],
            '7'    => ['⁷', '₇', '۷', '７'],
            '8'    => ['⁸', '₈', '۸', '８'],
            '9'    => ['⁹', '₉', '۹', '９'],
            'a'    => ['à', 'á', 'ả', 'ã', 'ạ', 'ă', 'ắ', 'ằ', 'ẳ', 'ẵ', 'ặ', 'â', 'ấ', 'ầ', 'ẩ', 'ẫ', 'ậ', 'ā', 'ą', 'å', 'α', 'ά', 'ἀ', 'ἁ', 'ἂ', 'ἃ', 'ἄ', 'ἅ', 'ἆ', 'ἇ', 'ᾀ', 'ᾁ', 'ᾂ', 'ᾃ', 'ᾄ', 'ᾅ', 'ᾆ', 'ᾇ', 'ὰ', 'ά', 'ᾰ', 'ᾱ', 'ᾲ', 'ᾳ', 'ᾴ', 'ᾶ', 'ᾷ', 'а', 'أ', 'အ', 'ာ', 'ါ', 'ǻ', 'ǎ', 'ª', 'ა', 'अ', 'ا', 'ａ', 'ä', 'א'],
            'b'    => ['б', 'β', 'ب', 'ဗ', 'ბ', 'ｂ', 'ב'],
            'c'    => ['ç', 'ć', 'č', 'ĉ', 'ċ', 'ｃ'],
            'd'    => ['ď', 'ð', 'đ', 'ƌ', 'ȡ', 'ɖ', 'ɗ', 'ᵭ', 'ᶁ', 'ᶑ', 'д', 'δ', 'د', 'ض', 'ဍ', 'ဒ', 'დ', 'ｄ', 'ד'],
            'e'    => ['é', 'è', 'ẻ', 'ẽ', 'ẹ', 'ê', 'ế', 'ề', 'ể', 'ễ', 'ệ', 'ë', 'ē', 'ę', 'ě', 'ĕ', 'ė', 'ε', 'έ', 'ἐ', 'ἑ', 'ἒ', 'ἓ', 'ἔ', 'ἕ', 'ὲ', 'έ', 'е', 'ё', 'э', 'є', 'ə', 'ဧ', 'ေ', 'ဲ', 'ე', 'ए', 'إ', 'ئ', 'ｅ'],
            'f'    => ['ф', 'φ', 'ف', 'ƒ', 'ფ', 'ｆ', 'פ', 'ף'],
            'g'    => ['ĝ', 'ğ', 'ġ', 'ģ', 'г', 'ґ', 'γ', 'ဂ', 'გ', 'گ', 'ｇ', 'ג'],
            'h'    => ['ĥ', 'ħ', 'η', 'ή', 'ح', 'ه', 'ဟ', 'ှ', 'ჰ', 'ｈ', 'ה'],
            'i'    => ['í', 'ì', 'ỉ', 'ĩ', 'ị', 'î', 'ï', 'ī', 'ĭ', 'į', 'ı', 'ι', 'ί', 'ϊ', 'ΐ', 'ἰ', 'ἱ', 'ἲ', 'ἳ', 'ἴ', 'ἵ', 'ἶ', 'ἷ', 'ὶ', 'ί', 'ῐ', 'ῑ', 'ῒ', 'ΐ', 'ῖ', 'ῗ', 'і', 'ї', 'и', 'ဣ', 'ိ', 'ီ', 'ည်', 'ǐ', 'ი', 'इ', 'ی', 'ｉ', 'י'],
            'j'    => ['ĵ', 'ј', 'Ј', 'ჯ', 'ج', 'ｊ'],
            'k'    => ['ķ', 'ĸ', 'к', 'κ', 'Ķ', 'ق', 'ك', 'က', 'კ', 'ქ', 'ک', 'ｋ', 'ק'],
            'l'    => ['ł', 'ľ', 'ĺ', 'ļ', 'ŀ', 'л', 'λ', 'ل', 'လ', 'ლ', 'ｌ', 'ל'],
            'm'    => ['м', 'μ', 'م', 'မ', 'მ', 'ｍ', 'מ', 'ם'],
            'n'    => ['ñ', 'ń', 'ň', 'ņ', 'ŉ', 'ŋ', 'ν', 'н', 'ن', 'န', 'ნ', 'ｎ', 'נ'],
            'o'    => ['ó', 'ò', 'ỏ', 'õ', 'ọ', 'ô', 'ố', 'ồ', 'ổ', 'ỗ', 'ộ', 'ơ', 'ớ', 'ờ', 'ở', 'ỡ', 'ợ', 'ø', 'ō', 'ő', 'ŏ', 'ο', 'ὀ', 'ὁ', 'ὂ', 'ὃ', 'ὄ', 'ὅ', 'ὸ', 'ό', 'о', 'و', 'ို', 'ǒ', 'ǿ', 'º', 'ო', 'ओ', 'ｏ', 'ö'],
            'p'    => ['п', 'π', 'ပ', 'პ', 'پ', 'ｐ', 'פ', 'ף'],
            'q'    => ['ყ', 'ｑ'],
            'r'    => ['ŕ', 'ř', 'ŗ', 'р', 'ρ', 'ر', 'რ', 'ｒ', 'ר'],
            's'    => ['ś', 'š', 'ş', 'с', 'σ', 'ș', 'ς', 'س', 'ص', 'စ', 'ſ', 'ს', 'ｓ', 'ס'],
            't'    => ['ť', 'ţ', 'т', 'τ', 'ț', 'ت', 'ط', 'ဋ', 'တ', 'ŧ', 'თ', 'ტ', 'ｔ', 'ת'],
            'u'    => ['ú', 'ù', 'ủ', 'ũ', 'ụ', 'ư', 'ứ', 'ừ', 'ử', 'ữ', 'ự', 'û', 'ū', 'ů', 'ű', 'ŭ', 'ų', 'µ', 'у', 'ဉ', 'ု', 'ူ', 'ǔ', 'ǖ', 'ǘ', 'ǚ', 'ǜ', 'უ', 'उ', 'ｕ', 'ў', 'ü'],
            'v'    => ['в', 'ვ', 'ϐ', 'ｖ', 'ו'],
            'w'    => ['ŵ', 'ω', 'ώ', 'ဝ', 'ွ', 'ｗ'],
            'x'    => ['χ', 'ξ', 'ｘ'],
            'y'    => ['ý', 'ỳ', 'ỷ', 'ỹ', 'ỵ', 'ÿ', 'ŷ', 'й', 'ы', 'υ', 'ϋ', 'ύ', 'ΰ', 'ي', 'ယ', 'ｙ'],
            'z'    => ['ź', 'ž', 'ż', 'з', 'ζ', 'ز', 'ဇ', 'ზ', 'ｚ', 'ז'],
            'aa'   => ['ع', 'आ', 'آ'],
            'ae'   => ['æ', 'ǽ'],
            'ai'   => ['ऐ'],
            'ch'   => ['ч', 'ჩ', 'ჭ', 'چ'],
            'dj'   => ['ђ', 'đ'],
            'dz'   => ['џ', 'ძ', 'דז'],
            'ei'   => ['ऍ'],
            'gh'   => ['غ', 'ღ'],
            'ii'   => ['ई'],
            'ij'   => ['ĳ'],
            'kh'   => ['х', 'خ', 'ხ'],
            'lj'   => ['љ'],
            'nj'   => ['њ'],
            'oe'   => ['ö', 'œ', 'ؤ'],
            'oi'   => ['ऑ'],
            'oii'  => ['ऒ'],
            'ps'   => ['ψ'],
            'sh'   => ['ш', 'შ', 'ش', 'ש'],
            'shch' => ['щ'],
            'ss'   => ['ß'],
            'sx'   => ['ŝ'],
            'th'   => ['þ', 'ϑ', 'θ', 'ث', 'ذ', 'ظ'],
            'ts'   => ['ц', 'ც', 'წ'],
            'ue'   => ['ü'],
            'uu'   => ['ऊ'],
            'ya'   => ['я'],
            'yu'   => ['ю'],
            'zh'   => ['ж', 'ჟ', 'ژ'],
            '(c)'  => ['©'],
            'A'    => ['Á', 'À', 'Ả', 'Ã', 'Ạ', 'Ă', 'Ắ', 'Ằ', 'Ẳ', 'Ẵ', 'Ặ', 'Â', 'Ấ', 'Ầ', 'Ẩ', 'Ẫ', 'Ậ', 'Å', 'Ā', 'Ą', 'Α', 'Ά', 'Ἀ', 'Ἁ', 'Ἂ', 'Ἃ', 'Ἄ', 'Ἅ', 'Ἆ', 'Ἇ', 'ᾈ', 'ᾉ', 'ᾊ', 'ᾋ', 'ᾌ', 'ᾍ', 'ᾎ', 'ᾏ', 'Ᾰ', 'Ᾱ', 'Ὰ', 'Ά', 'ᾼ', 'А', 'Ǻ', 'Ǎ', 'Ａ', 'Ä'],
            'B'    => ['Б', 'Β', 'ब', 'Ｂ'],
            'C'    => ['Ç', 'Ć', 'Č', 'Ĉ', 'Ċ', 'Ｃ'],
            'D'    => ['Ď', 'Ð', 'Đ', 'Ɖ', 'Ɗ', 'Ƌ', 'ᴅ', 'ᴆ', 'Д', 'Δ', 'Ｄ'],
            'E'    => ['É', 'È', 'Ẻ', 'Ẽ', 'Ẹ', 'Ê', 'Ế', 'Ề', 'Ể', 'Ễ', 'Ệ', 'Ë', 'Ē', 'Ę', 'Ě', 'Ĕ', 'Ė', 'Ε', 'Έ', 'Ἐ', 'Ἑ', 'Ἒ', 'Ἓ', 'Ἔ', 'Ἕ', 'Έ', 'Ὲ', 'Е', 'Ё', 'Э', 'Є', 'Ə', 'Ｅ'],
            'F'    => ['Ф', 'Φ', 'Ｆ'],
            'G'    => ['Ğ', 'Ġ', 'Ģ', 'Г', 'Ґ', 'Γ', 'Ｇ'],
            'H'    => ['Η', 'Ή', 'Ħ', 'Ｈ'],
            'I'    => ['Í', 'Ì', 'Ỉ', 'Ĩ', 'Ị', 'Î', 'Ï', 'Ī', 'Ĭ', 'Į', 'İ', 'Ι', 'Ί', 'Ϊ', 'Ἰ', 'Ἱ', 'Ἳ', 'Ἴ', 'Ἵ', 'Ἶ', 'Ἷ', 'Ῐ', 'Ῑ', 'Ὶ', 'Ί', 'И', 'І', 'Ї', 'Ǐ', 'ϒ', 'Ｉ'],
            'J'    => ['Ｊ'],
            'K'    => ['К', 'Κ', 'Ｋ'],
            'L'    => ['Ĺ', 'Ł', 'Л', 'Λ', 'Ļ', 'Ľ', 'Ŀ', 'ल', 'Ｌ'],
            'M'    => ['М', 'Μ', 'Ｍ'],
            'N'    => ['Ń', 'Ñ', 'Ň', 'Ņ', 'Ŋ', 'Н', 'Ν', 'Ｎ'],
            'O'    => ['Ó', 'Ò', 'Ỏ', 'Õ', 'Ọ', 'Ô', 'Ố', 'Ồ', 'Ổ', 'Ỗ', 'Ộ', 'Ơ', 'Ớ', 'Ờ', 'Ở', 'Ỡ', 'Ợ', 'Ø', 'Ō', 'Ő', 'Ŏ', 'Ο', 'Ό', 'Ὀ', 'Ὁ', 'Ὂ', 'Ὃ', 'Ὄ', 'Ὅ', 'Ὸ', 'Ό', 'О', 'Ө', 'Ǒ', 'Ǿ', 'Ｏ', 'Ö'],
            'P'    => ['П', 'Π', 'Ｐ'],
            'Q'    => ['Ｑ'],
            'R'    => ['Ř', 'Ŕ', 'Р', 'Ρ', 'Ŗ', 'Ｒ'],
            'S'    => ['Ş', 'Ŝ', 'Ș', 'Š', 'Ś', 'С', 'Σ', 'Ｓ'],
            'T'    => ['Ť', 'Ţ', 'Ŧ', 'Ț', 'Т', 'Τ', 'Ｔ'],
            'U'    => ['Ú', 'Ù', 'Ủ', 'Ũ', 'Ụ', 'Ư', 'Ứ', 'Ừ', 'Ử', 'Ữ', 'Ự', 'Û', 'Ū', 'Ů', 'Ű', 'Ŭ', 'Ų', 'У', 'Ǔ', 'Ǖ', 'Ǘ', 'Ǚ', 'Ǜ', 'Ｕ', 'Ў', 'Ü'],
            'V'    => ['В', 'Ｖ'],
            'W'    => ['Ω', 'Ώ', 'Ŵ', 'Ｗ'],
            'X'    => ['Χ', 'Ξ', 'Ｘ'],
            'Y'    => ['Ý', 'Ỳ', 'Ỷ', 'Ỹ', 'Ỵ', 'Ÿ', 'Ῠ', 'Ῡ', 'Ὺ', 'Ύ', 'Ы', 'Й', 'Υ', 'Ϋ', 'Ŷ', 'Ｙ'],
            'Z'    => ['Ź', 'Ž', 'Ż', 'З', 'Ζ', 'Ｚ'],
            'AE'   => ['Æ', 'Ǽ'],
            'Ch'   => ['Ч'],
            'Dj'   => ['Ђ'],
            'Dz'   => ['Џ'],
            'Gx'   => ['Ĝ'],
            'Hx'   => ['Ĥ'],
            'Ij'   => ['Ĳ'],
            'Jx'   => ['Ĵ'],
            'Kh'   => ['Х'],
            'Lj'   => ['Љ'],
            'Nj'   => ['Њ'],
            'Oe'   => ['Œ'],
            'Ps'   => ['Ψ'],
            'Sh'   => ['Ш', 'ש'],
            'Shch' => ['Щ'],
            'Ss'   => ['ẞ'],
            'Th'   => ['Þ', 'Θ', 'ת'],
            'Ts'   => ['Ц'],
            'Ya'   => ['Я', 'יא'],
            'Yu'   => ['Ю', 'יו'],
            'Zh'   => ['Ж'],
            ' '    => ["\xC2\xA0", "\xE2\x80\x80", "\xE2\x80\x81", "\xE2\x80\x82", "\xE2\x80\x83", "\xE2\x80\x84", "\xE2\x80\x85", "\xE2\x80\x86", "\xE2\x80\x87", "\xE2\x80\x88", "\xE2\x80\x89", "\xE2\x80\x8A", "\xE2\x80\xAF", "\xE2\x81\x9F", "\xE3\x80\x80", "\xEF\xBE\xA0"],
        ];
    }

    /**
     * Returns the language specific replacements for the ascii method.
     *
     * Note: Adapted from Stringy\Stringy.
     *
     * @see https://github.com/danielstjules/Stringy/blob/3.1.0/LICENSE.txt
     *
     * @param  string  $language
     * @return array|null
     */
    protected static function languageSpecificCharsArray(string $language) : ?array
    {
        static $languageSpecific;

        if (! isset($languageSpecific)) {
            $languageSpecific = [
                'bg' => [
                    ['х', 'Х', 'щ', 'Щ', 'ъ', 'Ъ', 'ь', 'Ь'],
                    ['h', 'H', 'sht', 'SHT', 'a', 'А', 'y', 'Y'],
                ],
                'da' => [
                    ['æ', 'ø', 'å', 'Æ', 'Ø', 'Å'],
                    ['ae', 'oe', 'aa', 'Ae', 'Oe', 'Aa'],
                ],
                'de' => [
                    ['ä',  'ö',  'ü',  'Ä',  'Ö',  'Ü'],
                    ['ae', 'oe', 'ue', 'AE', 'OE', 'UE'],
                ],
                'he' => [
                    ['א', 'ב', 'ג', 'ד', 'ה', 'ו'],
                    ['ז', 'ח', 'ט', 'י', 'כ', 'ל'],
                    ['מ', 'נ', 'ס', 'ע', 'פ', 'צ'],
                    ['ק', 'ר', 'ש', 'ת', 'ן', 'ץ', 'ך', 'ם', 'ף'],
                ],
                'ro' => [
                    ['ă', 'â', 'î', 'ș', 'ț', 'Ă', 'Â', 'Î', 'Ș', 'Ț'],
                    ['a', 'a', 'i', 's', 't', 'A', 'A', 'I', 'S', 'T'],
                ],
            ];
        }

        return $languageSpecific[$language] ?? null;
    }

	/**
     * Get the default transliterator.
     *
     * @return \Transliterator|null Either a Transliterator instance, or `null`
     *   in case no transliterator has been set yet.
     * @since 3.3.2
     */
    public static function getTransliterator() : ?\Transliterator
    {
        return static::$_defaultTransliterator;
    }

    /**
     * Set the default transliterator.
     *
     * @param \Transliterator $transliterator A `Transliterator` instance.
     * @return void
     * @since 3.3.2
     */
    public static function setTransliterator(\Transliterator $transliterator)
    {
        static::$_defaultTransliterator = $transliterator;
    }

    /**
     * Get default transliterator identifier string.
     *
     * @return string Transliterator identifier.
     */
    public static function getTransliteratorId() : string
    {
        return static::$_defaultTransliteratorId;
    }

    /**
     * Set default transliterator identifier string.
     *
     * @param string $transliteratorId Transliterator identifier.
     * @return void
     */
    public static function setTransliteratorId(string $transliteratorId)
    {
        static::setTransliterator(transliterator_create($transliteratorId));
        static::$_defaultTransliteratorId = $transliteratorId;
    }
}
