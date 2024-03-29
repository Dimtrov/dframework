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

namespace dFramework\core\utilities;

use dFramework\core\security\Password;
use Josantonius\Json\Json;

/**
 * Utils
 *
 * Utilitaires generals
 *
 * @package		dFramework
 * @subpackage	Core
 * @category    Utilities
 * @author		Dimitri Sitchet Tomkeu <devcode.dst@gmail.com>
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
    public static function jsonToArray(string $filename)
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
    public static function arrayToJson(array $array, string $filename) : bool
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
    public static function hashPass(string $password) : string
    {
        return Password::hash($password);
    }

    /**
     * Compare password and Hash user's
     *
     * @since 3.0
     * @param string $pass
     * @param string $hash
     * @return bool
     */
    public static function comparePass(string $pass, string $hash) : bool
    {
        return Password::compare($pass, $hash);
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
		return Password::random($lenght);
    }
}
