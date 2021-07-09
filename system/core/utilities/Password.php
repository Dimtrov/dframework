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

use RuntimeException;

/**
 * Password
 *
 * Utilitaires de securité & mot de passe
 *
 * @package		dFramework
 * @subpackage	Core
 * @category    Utilities
 * @author		Dimitri Sitchet Tomkeu <devcode.dst@gmail.com>
 * @link		https://dimtrov.hebfree.org/docs/dframework/api/
 * @since       3.3.2
 * @file        /system/core/utilities/Password.php
 */
class Password
{
    /**
     * Hash user's password with SHA512, base64_encode, ROT13 and salts !
     *
     * @since 3.0
     * @param string $password
     * @return string
     */
    public static function hash(string $password) : string
    {
        $salt = (string) config('data.encryption.salt');
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
    public static function compare(string $pass, string $hash) : bool
    {
        return ($hash === self::hash($pass));
    }

    /**
     * Genere un mot de passe aleatoire d'une longueur specifiee
     *
     * @since 3.0
     * @param int $lenght
     * @return string
     */
    public static function random(int $lenght = 8) : string
    {
        $lenght = (empty($lenght) OR !is_int($lenght)) ? 8 : $lenght;
        $characters = array_merge(range(0, 9), range('a', 'z'), range('A', 'Z'));
        shuffle($characters);
        $nbr_char = count($characters) - 1;
        $password = '';
        for ($i = 0; $i < $lenght; $i++)
        {
            $password .= $characters[rand(0, $nbr_char)];
        }

        return (string) $password;
    }

	/**
     * Get random bytes from a secure source.
     *
     * This method will fall back to an insecure source an trigger a warning
     * if it cannot find a secure source of random data.
     *
     * @param int $length The number of bytes you want.
     * @return string Random bytes in binary.
     */
    public static function randomBytes(int $length) : string
    {
        if (function_exists('random_bytes'))
		{
            return random_bytes($length);
        }
        if (!function_exists('openssl_random_pseudo_bytes'))
		{
            throw new RuntimeException(
                'You do not have a safe source of random data available. ' .
                'Install either the openssl extension, or paragonie/random_compat. ' .
                'Or use Security::insecureRandomBytes() alternatively.'
            );
        }

        $bytes = openssl_random_pseudo_bytes($length, $strongSource);
        if (!$strongSource)
		{
            trigger_error(
                'openssl was unable to use a strong source of entropy. ' .
                'Consider updating your system libraries, or ensuring ' .
                'you have more available entropy.',
                E_USER_WARNING
            );
        }

        return $bytes;
    }

	/**
     * A timing attack resistant comparison that prefers native PHP implementations.
     *
     * @param string $original The original value.
     * @param string $compare The comparison value.
     * @return bool
     * @see https://github.com/resonantcore/php-future/
     */
    public static function constantEquals(string $original, string $compare) : bool
    {
        if (!is_string($original) OR !is_string($compare)) {
            return false;
        }
        if (function_exists('hash_equals'))
		{
            return hash_equals($original, $compare);
        }

		$originalLength = mb_strlen($original, '8bit');
        $compareLength = mb_strlen($compare, '8bit');
        if ($originalLength !== $compareLength)
		{
            return false;
        }

		$result = 0;
        for ($i = 0; $i < $originalLength; $i++)
		{
            $result |= (ord($original[$i]) ^ ord($compare[$i]));
        }

        return $result === 0;
    }
}
