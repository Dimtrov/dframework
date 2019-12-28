<?php
/**
 * Created by PhpStorm.
 * User: Dimitri Sitchet
 * Date: 03/12/2019
 * Time: 10:19
 */

namespace dFramework\core\utilities;


class Utilities
{




    /**
     * Recursively strips slashes from all values in an array
     *
     * @param array $values Array of values to strip slashes
     * @return mixed What is returned from calling stripslashes
     * @link http://book.cakephp.org/2.0/en/core-libraries/global-constants-and-functions.html#stripslashes_deep
     */
    public static function stripslashes_deep($values)
    {
        if (is_array($values))
        {
            foreach ($values as $key => $value)
            {
                $values[$key] = stripslashes_deep($value);
            }
        }
        else
        {
            $values = stripslashes($values);
        }
        return $values;
    }










}