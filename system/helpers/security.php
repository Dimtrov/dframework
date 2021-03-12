<?php

if (! function_exists('sanitize_filename'))
{
	/**
	 * Sanitize a filename to use in a URI.
	 *
	 * @param string $filename
	 *
	 * @return string
	 */
	function sanitize_filename(string $filename, bool $relative_path = false): string
	{
        $bad = [
            '../',
            '<!--',
            '-->',
            '<',
            '>',
            "'",
            '"',
            '&',
            '$',
            '#',
            '{',
            '}',
            '[',
            ']',
            '=',
            ';',
            '?',
            '%20',
            '%22',
            '%3c', // <
            '%253c', // <
            '%3e', // >
            '%0e', // >
            '%28', // (
            '%29', // )
            '%2528', // (
            '%26', // &
            '%24', // $
            '%3f', // ?
            '%3b', // ;
            '%3d',       // =
        ];
		if (! $relative_path)
		{
			$bad[] = './';
			$bad[] = '/';
		}

		$str = remove_invisible_characters($filename, false);

		do
		{
			$old = $str;
			$str = str_replace($bad, '', $str);
		}
		while ($old !== $str);

		return stripslashes($str);
	}
}

//--------------------------------------------------------------------

if (! function_exists('strip_image_tags'))
{
	/**
	 * Strip Image Tags
	 *
	 * @param  string $str
	 * @return string
	 */
	function strip_image_tags(string $str): string
	{
		return preg_replace([
			'#<img[\s/]+.*?src\s*=\s*(["\'])([^\\1]+?)\\1.*?\>#i',
			'#<img[\s/]+.*?src\s*=\s*?(([^\s"\'=<>`]+)).*?\>#i',
		], '\\2', $str
		);
	}
}

//--------------------------------------------------------------------

if (! function_exists('encode_php_tags'))
{
	/**
	 * Convert PHP tags to entities
	 *
	 * @param  string $str
	 * @return string
	 */
	function encode_php_tags(string $str): string
	{
		return str_replace(['<?', '?>'], ['&lt;?', '?&gt;'], $str);
	}
}

//--------------------------------------------------------------------
