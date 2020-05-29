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
use Josantonius\Json\Json;

/**
 * Utils
 *
 * Utilitaires generals
 *
 * @package		dFramework
 * @subpackage	Core
 * @category    Utilities
 * @author		Dimitri Sitchet Tomkeu <dev.dimitrisitchet@gmail.com>
 * @link		https://dimtrov.hebfree.org/docs/dframework/api/
 * @since       2.1
 * @file        /system/core/utilities/Utils.php
 */

class Utils
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
     * Recupere le contenu d'un fichier JSON et le renvoie sous forme de tableau
     * 
     * @since 3.0
     * @param string $filename Chemin vers le fichier json a recuperer
     * @return array|false
     */
    public function json2arr(string $filename)
    {
        return Json::fileToArray($filename);
    }

    /**
     * Enregistre le contenu d'un tableau au format JSON dans un fichier 
     * 
     * @since 3.0
     * @param array $array Tableau a sauvegarder
     * @param string $filename Chemin vers le fichier json de sauvegarde
     * @return bool
     */
    public function arr2json(array $array, string $filename) : bool
    {
        return Json::arrayToFile($array, $filename);
    }

    /**
     * Hash user's password with SHA512, base64_encode, ROT13 and salts !
     * 
     * @since 3.0
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

    /**
     * Compare password and Hash user's
     *
     * @since 3.0
     * @param string $pass
     * @param string $hash
     * @return bool
     */
    public static function passcompare(string $pass, string $hash) : bool
    {
        return ($hash === self::hashpass($pass));
    }
    
    /**
     * Genere un mot de passe aleatoire d'une longueur specifiee
     *
     * @since 3.0
     * @param int $lenght
     * @return string
     */
    public static function randomPass(int $lenght = 8) : string
    {
        $lenght = (empty($lenght) OR !is_int($lenght)) ? 8 : $lenght;
        $characters = array_merge(range(0, 9), range('a', 'z'), range('A', 'Z'));
        shuffle($characters);
        $nbr_char = count($characters) - 1;
        $password = '';
        for($i = 0; $i < $lenght; $i++) 
        {
            $password .= $characters[rand(0, $nbr_char)];
        }
        return (string) $password;
    }

}