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
 *  @homepage	https://dimtrov.hebfree.org/works/dframework
 *  @version    3.0
 */


namespace dFramework\core\utilities;

use dFramework\core\Config;
 
/**
 * Utilities
 *
 * Utilitaires generals
 *
 * @package		dFramework
 * @subpackage	Core
 * @category    Utilities
 * @author		Dimitri Sitchet Tomkeu <dev.dimitrisitchet@gmail.com>
 * @link		https://dimtrov.hebfree.org/docs/dframework/api/
 * @since       2.1
 * @file        /system/core/utilities/Utilities.php
 */

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
                $values[$key] = self::stripslashes_deep($value);
            }
        }
        else
        {
            $values = stripslashes($values);
        }
        return $values;
    }

    /**
     * Hash user's password with SHA512, base64_encode, ROT13 and salts !
     * 
     * @param string $password
     * @return string 
     */
    public static function hashpass(string $password) : string 
    {
        $salt = (string) Config::get('data.encryption.salt');
        return hash('SHA512', 
            base64_encode(
                str_rot13(
                    hash('SHA256', 
                        str_rot13('df' . $salt . $password . 'df' . $salt)
                    )
                )
            )
        );
    }


    public static function randomPass(int $lenght = 8) : string 
    {
        $lenght = (empty($lenght) OR !is_int($lenght)) ? 8 : $lenght;
        $characters = array_merge(range(0, 9), range('a', 'z'), range('A', 'Z'));
        shuffle($characters);
        $password = array_rand($characters, $lenght);
        if(is_array($password))
        {
            return join('', $password);
        }
        return (string) $password;
    }

}