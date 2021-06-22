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
 *  @version    3.3.0
 */

namespace dFramework\core\utilities;

use dFramework\core\Config;
use dFramework\core\exception\Exception;
use Firebase\JWT\JWT As Firebase;
use Throwable;

/**
 * Jwt
 *
 * Utilitaires de manipulation de token
 *
 * @package		dFramework
 * @subpackage	Core
 * @category    Utilities
 * @author		Dimitri Sitchet Tomkeu <dev.dst@gmail.com>
 * @since       3.3.0
 * @file        /system/core/utilities/Jwt.php
 */
class Jwt
{
	/**
	 * @var array
	 */
	private $config;

	/**
	 * @var string
	 */
	private $locale;

	/**
	 * @var self
	 */
	private static $_instance = null;

	public function __construct()
	{
		$config = Config::get('rest');

		$this->config = array_merge([
			'key'      => 'df_jwt_key',
			'exp_time' => 5,
			'distinct' => false,
			'algo'     => 'HS256'
		], $config['jwt'] ?? []);

		$this->config['public_key'] = $this->config['public_key'] ?? $this->config['key'];

		$this->locale = $config['language'] ?? 'en';
	}

	/**
	 * Return a singleton
	 *
	 * @return self
	 */
	public static function instance() : self
	{
		if (null === self::$_instance)
		{
			self::$_instance = new self;
		}
		return self::$_instance;
	}

	/**
	 * Renvoi les configurations jwt appropriees
	 *
	 * @param array $config
	 * @return object
	 */
	private static function config(array $config = []) : object
	{
		return (object) array_merge(self::instance()->config, $config);
	}

    /**
     * Genere un token d'authentification
     *
     * @param array $data
     * @param array $config
     * @return string
	 * @throws Exception
     */
    public static function encode(array $data = [], array $config = []) : string
    {
		$conf = self::config($config);

        $payload = [
            'iat' => time(),
            'iss' => base_url(),
            'exp' => time() + (60 * $conf->exp_time)
        ];

		if ($conf->distinct === true)
		{
			$payload['data'] = $data;
		}
		else
		{
			$payload = array_merge($payload, $data);
		}

        try {
            return Firebase::encode($payload, $conf->key, $conf->algo);
        }
        catch(Throwable $e) {
			throw new Exception('JWT Exception : ' . $e->getMessage(), 0, $e);
        }
    }

	/**
	 * Recupere le payload du token entrant
	 *
	 * @param bool $full
	 * @param array $config
	 * @return mixed
	 * @throws Exception
	 */
	public static function payload(bool $full = false, array $config = [])
	{
		$token = self::getToken();
		$conf = self::config($config);

		if (empty($token))
        {
			throw new Exception(lang('rest.token_not_found', null, self::instance()->locale));
        }

		$payload = self::decode($token, $config);

		$returned = $payload;
		if ($conf->distinct === true)
		{
			$returned = $payload->data ?? $payload;
		}

		if (true !== $full)
		{
			unset($returned->iat, $returned->iss, $returned->exp);
		}

		return $returned;
	}

	/**
     * Decode un token d'authentification
     *
     * @param string $token
     * @param array $config
	 * @throws Exception
     * @return object
     */
    protected static function decode(string $token, array $config = []) : object
    {
		$conf = self::config($config);

        try {
			return Firebase::decode($token, $conf->public_key, (array) $conf->algo);
		}
		catch(Throwable $e) {
			throw new Exception('JWT Exception : ' . $e->getMessage(), 0, $e);
		}
	}

	/**
     * Recupere le token d'acces a partier des headers
	 *
	 * @return string|null
     */
    private static function getToken() : ?string
    {
        $authorization = self::getAuthorization();

		if (!empty($authorization) AND preg_match('/Bearer\s(\S+)/', $authorization, $matches))
        {
            return $matches[1];
        }

		return null;
    }

	/**
	 * Recupere le header "Authorization"
	 *
	 * @return string|null
	 */
	private static function getAuthorization() : ?string
    {
        $header = null;

        if (isset($_SERVER['Authorization']))
        {
            $header = trim($_SERVER['Authorization']);
        }
        else if (isset($_SERVER['HTTP_AUTHORIZATION']))
        {
            // Ngnix or fast CGI
            $header = trim($_SERVER['HTTP_AUTHORIZATION']);
        }
        else if (function_exists('apache_request_headers'))
        {
            $requestHeaders = apache_request_headers();

            $requestHeaders = array_combine(
                array_map('ucwords', array_keys($requestHeaders)),
                array_values(($requestHeaders))
            );
            if (isset($requestHeaders['Authorization']))
            {
                $header = trim($requestHeaders['Authorization']);
            }
        }

        return $header;
    }
}
