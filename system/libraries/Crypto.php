<?php
/**
 * dFramework
 *
 * The simplest PHP framework for beginners
 * Copyright (c) 2019, Dimtrov Sarl
 * This content is released under the Mozilla Public License 2 (MPL-2.0)
 *
 * @package	    dFramework
 * @author	    Dimitric Sitchet Tomkeu <dev.dimitrisitchet@gmail.com>
 * @copyright	Copyright (c) 2019, Dimtrov Sarl. (https://dimtrov.hebfree.org)
 * @copyright	Copyright (c) 2019, Dimitric Sitchet Tomkeu. (https://www.facebook.com/dimtrovich)
 * @license	    https://opensource.org/licenses/MPL-2.0 MPL-2.0 License
 * @homepage    https://dimtrov.hebfree.org/works/dframework
 * @version     3.0
 */

 use dFramework\core\Config;
 use dFramework\core\exception\Exception;

/**
 * Crypto
 *
 * Encryption standart class
 *
 * @package		dFramework
 * @subpackage	Library
 * @author		Dimitri Sitchet Tomkeu <dev.dimitrisitchet@gmail.com>
 * @link		https://dimtrov.hebfree.org/docs/dframework/guide/Crypto.html
 * @since       2.0
 */


class dF_Crypto
{
    /**
     * @var string
     */
    protected $algo = 'aes-128-cbc';
    /**
     * @var string
     */
    protected $key;
    /**
     * @var string
     */
    protected $driver = 'openssl';
    /**
     * @var bool
     */
    protected $add_hmac = true;


    /**
     * dF_Crypto constructor.
     */
    public function __construct()
    {
        $config = Config::get('data.encryption');

        $this->key = $config['key'] ?? $this->key;
        $this->algo = strtolower(trim($config['algo'] ?? $this->algo));
        $this->add_hmac = $config['add_hmac'] ?? $this->add_hmac;
    }


    /**
     * @param array $params
     */
    public function set(array $params)
    {
        if(!empty($params['algo']))
        {
            $this->algo = strtolower(trim($params['algo']));
        }
        if(!empty($params['key']) AND is_string($params['key']))
        {
            $this->key = $params['key'];
        }
        if(isset($params['add_hmac']) AND is_bool($params['add_hmac']))
        {
            $this->add_hmac = $params['add_hmac'];
        }
    }

    /**
     * @param int $length
     * @return string
     */
    public function genKey(int $length) : string
    {
        return $this->create_key($length);
    }

    /**
     * @param string $data
     * @return string
     */
    public function encrypt($data) : ?string
    {
        if (($data = $this->{'_'.$this->driver.'_encrypt'}($data)) === FALSE)
        {
            return FALSE;
        }
        $data = base64_encode($data);
        $hmac_key = $this->hkdf($this->key, NULL, NULL, 'authentication');
        return ((true === $this->add_hmac) ? hash_hmac('sha512', $data, $hmac_key, false) : '').$data;
    }

    /**
     * @param string $data
     * @return string
     */
    public function decrypt($data) : ?string
    {
        if(true === $this->add_hmac)
        {
            $digest_size = 64 * 2;
            if (strlen($data) <= $digest_size)
            {
                return FALSE;
            }
            $hmac_input = substr($data, 0, $digest_size);
            $data = substr($data, $digest_size);

            $hmac_key = $this->hkdf($this->key, NULL, NULL, 'authentication');
            $hmac_check = hash_hmac('sha512', $data, $hmac_key, false);

            // Time-attack-safe comparison
            $diff = 0;
            for ($i = 0; $i < $digest_size; $i++)
            {
                $diff |= ord($hmac_input[$i]) ^ ord($hmac_check[$i]);
            }
            if ($diff !== 0)
            {
                return FALSE;
            }
        }
        $data = base64_decode($data);
        return $this->{'_'.$this->driver.'_decrypt'}($data);
    }


    /**
     * Encrypt via OpenSSL
     *
     * @param	string	$data	Input data
     * @return	string
     */
    private function _openssl_encrypt($data)
    {
        $iv = ($iv_size = openssl_cipher_iv_length($this->algo))
            ? $this->create_key($iv_size)
            : NULL;
        $data = openssl_encrypt($data, $this->algo, $this->key, OPENSSL_RAW_DATA, $iv);

        return ($data === FALSE)
            ? FALSE
            :$iv.$data;
    }

    /**
     * Decrypt via OpenSSL
     *
     * @param	string	$data	Input data
     * @param	array	$params	Input parameters
     * @return	string
     */
    private function _openssl_decrypt($data)
    {
        if ($iv_size = openssl_cipher_iv_length($this->algo))
        {
            $iv = substr($data, 0, $iv_size);
            $data = substr($data, $iv_size);
        }
        else
        {
            $iv = NULL;
        }
        return openssl_decrypt($data, $this->algo, $this->key, OPENSSL_RAW_DATA, $iv);
    }


    /**
     * Create a random key
     *
     * @param	int	$length	Output length
     * @return	string
     */
    protected function create_key(int $length)
    {
        if (function_exists('random_bytes'))
        {
            try {
                return random_bytes($length);
            }
            catch (Exception $e)
            {
                Exception::Throw($e);
            }
        }
        elseif (defined('MCRYPT_DEV_URANDOM'))
        {
            return mcrypt_create_iv($length, MCRYPT_DEV_URANDOM);
        }
        $is_secure = true;
        return openssl_random_pseudo_bytes($length, $is_secure);
    }

    /**
     * HKDF
     *
     * @link    https://tools.ietf.org/rfc/rfc5869.txt
     * @param   string  $key    Input key
     * @param   null|string  $salt    Optional salt
     * @param   int|null   $length    Output length (defaults to the selected digest size)
     * @param   string $info Optional context/application-specific info
     * @return  string    A pseudo-random key
     */
    protected function hkdf($key, $salt = NULL, $length = NULL, $info = '')
    {
        if (empty($length) OR ! is_int($length))
        {
            $length = 64;
        }
        elseif ($length > (255 * 64))
        {
            return FALSE;
        }
        strlen($salt) OR $salt = str_repeat("\0", 64);

        $prk = hash_hmac('sha512', $key, $salt, TRUE);
        $key = '';
        for ($key_block = '', $block_index = 1; strlen($key) < $length; $block_index++)
        {
            $key_block = hash_hmac('sha512', $key_block.$info.chr($block_index), $prk, TRUE);
            $key .= $key_block;
        }
        return substr($key, 0, $length);
    }
}