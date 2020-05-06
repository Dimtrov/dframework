<?php
/**
 *  dFramework
 *
 *  The simplest PHP framework for beginners
 *  Copyright (c) 2019, Dimtrov Sarl
 *  This content is released under the Mozilla Public License 2 (MPL-2.0)
 *
 *  @package	dFramework
 *  @author	    Dimitri Sitchet Tomkeu <dev.dimitrisitchet@gmail.com>
 *  @copyright	Copyright (c) 2019, Dimtrov Sarl. (https://dimtrov.hebfree.org)
 *  @copyright	Copyright (c) 2019, Dimitri Sitchet Tomkeu. (https://www.facebook.com/dimtrovich)
 *  @license	https://opensource.org/licenses/MPL-2.0 MPL-2.0 License
 *  @link	    https://dimtrov.hebfree.org/works/dframework
 *  @version    3.1
 */


namespace dFramework\core\utilities;

use Jawira\CaseConverter\Convert;

/**
 * Chaine
 *
 * @package		dFramework
 * @subpackage	Core
 * @category    Utilities
 * @author		Dimitri Sitchet Tomkeu <dev.dimitrisitchet@gmail.com>
 * @link		https://dimtrov.hebfree.org/docs/dframework/api/
 * @since       2.1
 * @credit      CakeRequest (http://cakephp.org CakePHP(tm) Project)
 * @file        /system/core/utilities/Chaine.php
 */

class Chaine
{

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
     * Tokenizes a string using $separator, ignoring any instance of $separator that appears between
     * $leftBound and $rightBound.
     *
     * @param string $data The data to tokenize.
     * @param string $separator The token to split the data on.
     * @param string $leftBound The left boundary to ignore separators in.
     * @param string $rightBound The right boundary to ignore separators in.
     * @return mixed Array of tokens in $data or original input if empty.
     */
    public static function tokenize($data, $separator = ',', $leftBound = '(', $rightBound = ')') {
        if (empty($data)) {
            return [];
        }

        $depth = 0;
        $offset = 0;
        $buffer = '';
        $results = [];
        $length = strlen($data);
        $open = false;

        while ($offset <= $length) {
            $tmpOffset = -1;
            $offsets = array(
                strpos($data, $separator, $offset),
                strpos($data, $leftBound, $offset),
                strpos($data, $rightBound, $offset)
            );
            for ($i = 0; $i < 3; $i++) {
                if ($offsets[$i] !== false && ($offsets[$i] < $tmpOffset || $tmpOffset == -1)) {
                    $tmpOffset = $offsets[$i];
                }
            }
            if ($tmpOffset !== -1) {
                $buffer .= substr($data, $offset, ($tmpOffset - $offset));
                if (!$depth && $data[$tmpOffset] == $separator) {
                    $results[] = $buffer;
                    $buffer = '';
                } else {
                    $buffer .= $data[$tmpOffset];
                }
                if ($leftBound != $rightBound) {
                    if ($data[$tmpOffset] == $leftBound) {
                        $depth++;
                    }
                    if ($data[$tmpOffset] == $rightBound) {
                        $depth--;
                    }
                } else {
                    if ($data[$tmpOffset] == $leftBound) {
                        if (!$open) {
                            $depth++;
                            $open = true;
                        } else {
                            $depth--;
                        }
                    }
                }
                $offset = ++$tmpOffset;
            } else {
                $results[] = $buffer . substr($data, $offset);
                $offset = $length + 1;
            }
        }
        if (empty($results) && !empty($buffer)) {
            $results[] = $buffer;
        }

        if (!empty($results)) {
            return array_map('trim', $results);
        }
        return [];
    }
}