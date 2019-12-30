<?php
/**
 *  dFramework
 *
 *  The simplest PHP framework for beginners
 *  Copyright (c) 2019, Dimtrov Sarl
 *  This content is released under the Mozilla Public License 2 (MPL-2.0)
 *
 *  @package	    dFramework
 *  @author	    Dimitric Sitchet Tomkeu <dev.dimitrisitchet@gmail.com>
 *  @copyright	Copyright (c) 2019, Dimtrov Sarl. (https://dimtrov.hebfree.org)
 *  @copyright	Copyright (c) 2019, Dimitric Sitchet Tomkeu. (https://www.facebook.com/dimtrovich)
 *  @license	    https://opensource.org/licenses/MPL-2.0 MPL-2.0 License
 *  @link	    https://dimtrov.hebfree.org/works/dframework
 *  @version 2.1
 */

namespace dFramework\core\utilities;


class Utilities
{

    /**
     * Recursively strips slashes from all values in an array
     *
     * @param array $values Array of values to strip slashes
     * @return mixed What is returned from calling stripslashes
     * @credit http://book.cakephp.org/2.0/en/core-libraries/global-constants-and-functions.html#stripslashes_deep
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