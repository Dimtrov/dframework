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
 *  @version    3.4.0
 */

namespace dFramework\core\models\cast;

use Exception;
use RuntimeException;
use stdClass;

/**
 * JsonCast
 *
 * @package		dFramework
 * @subpackage	Core
 * @category	Models/Cast
 * @author		Dimitri Sitchet Tomkeu <devcode.dst@gmail.com>
 * @link		https://dimtrov.hebfree.org/docs/dframework/api/
 * @since       3.4.0
 * @credit		CodeIngiter 4 - https://github.com/codeigniter4/framework/tree/master/system/Entity/Cast/JsonCast.php
 * @file		/system/core/models/cast/JsonCast.php
 */
class JsonCast extends BaseCast
{
    /**
     * {@inheritDoc}
     */
    public static function get($value, array $params = [])
    {
        $associative = in_array('array', $params, true);

        $tmp = $value !== null ? ($associative ? [] : new stdClass()) : null;

        if (function_exists('json_decode')
            && (
                (is_string($value)
                    && strlen($value) > 1
                    && in_array($value[0], ['[', '{', '"'], true))
                || is_numeric($value)
            )
        ) {
			$tmp = json_decode($value, $associative, 512);

			$codeError = json_last_error();
			if ($codeError != JSON_ERROR_NONE) {
				throw self::throwError($codeError);
			}
        }

        return $tmp;
    }

    /**
     * {@inheritDoc}
     */
    public static function set($value, array $params = []): string
    {
        if (function_exists('json_encode')) {
			$value = json_encode($value, JSON_UNESCAPED_UNICODE);

			$codeError = json_last_error();
			if (JSON_ERROR_NONE != $codeError) {
				throw self::throwError($codeError);
			}
        }

        return $value;
    }


	private static function throwError(int $code) : RuntimeException
	{
		switch ($code) {
			case JSON_ERROR_DEPTH:
				return new \RuntimeException('JsonCast - Profondeur maximale atteinte');
			case JSON_ERROR_STATE_MISMATCH:
				return new \RuntimeException('JsonCast - Inadéquation des modes ou underflow');
			case JSON_ERROR_CTRL_CHAR:
				return new \RuntimeException('JsonCast - Erreur lors du contrôle des caractères');
			case JSON_ERROR_SYNTAX:
				return new \RuntimeException('JsonCast - Erreur de syntaxe ; JSON malformé');
			case JSON_ERROR_UTF8:
				return new \RuntimeException('JsonCast - Caractères UTF-8 malformés, probablement une erreur d\'encodage');
			default:
				return new \RuntimeException('JsonCast - Erreur inconnue');
		}
	}
}
