<?php 
/**
 * dFramework
 *
 * The simplest PHP framework for beginners
 * Copyright (c) 2019 - 2021, Dimtrov Sarl
 * This content is released under the Mozilla Public License 2 (MPL-2.0)
 *
 * @package	    dFramework
 * @author	    Dimitri Sitchet Tomkeu <dev.dst@gmail.com>
 * @copyright	Copyright (c) 2019 - 2021, Dimtrov Sarl. (https://dimtrov.hebfree.org)
 * @copyright	Copyright (c) 2019 - 2021, Dimitri Sitchet Tomkeu. (https://www.facebook.com/dimtrovich)
 * @license	    https://opensource.org/licenses/MPL-2.0 MPL-2.0 License
 * @homepage    https://dimtrov.hebfree.org/works/dframework
 * @version     3.3.0
 */

namespace dFramework\components\rest;

use dFramework\core\Config;
use dFramework\core\Controller as CoreController;
use dFramework\core\exception\Exception;
use dFramework\core\output\Format;
use Firebase\JWT\JWT;

/**
 * dFramework Rest Controller
 * 
 * A fully RESTful server implementation for dFramework (inspired by CodeIgniter) using one library, one config file and one controller.
 *
 * @package		dFramework
 * @subpackage	Components
 * @category    Rest
 * @author		Dimitri Sitchet Tomkeu <dev.dst@gmail.com>
 * @link		https://dimtrov.hebfree.org/docs/dframework/api/
 * @since       3.1
 * @credit      CI Rest Server - by Chris Kacerguis <chriskacerguis@gmail.com> - https://github.com/chriskacerguis/ci-restserver
 * @file        /system/components/rest/Controller.php
 */
class Controller extends CoreController
{
    /**
     * Common HTTP status codes and their respective description.
     *
     * @link http://www.restapitutorial.com/httpstatuscodes.html
     */
    const HTTP_OK                 = 200;
    const HTTP_CREATED            = 201;
    const HTTP_NOT_MODIFIED       = 304;
    const HTTP_BAD_REQUEST        = 400;
    const HTTP_UNAUTHORIZED       = 401;
    const HTTP_FORBIDDEN          = 403;
    const HTTP_NOT_FOUND          = 404;
    const HTTP_METHOD_NOT_ALLOWED = 405;
    const HTTP_NOT_ACCEPTABLE     = 406;
    const HTTP_INTERNAL_ERROR     = 500;


    /**
     * List of allowed REST format.
     *
     * @var array
     */
    private $allowed_format = [
        'json',
        'array',
        'csv',
    // 'html',
        'jsonp',
        'php',
        'serialized',
        'xml',
    ];
    /**
     * List all supported methods, the first will be the default format.
     *
     * @var array
     */
    protected $_supported_formats = [
        'json'       => 'application/json',
        'array'      => 'application/json',
        'csv'        => 'application/csv',
    // 'html'       => 'text/html',
        'jsonp'      => 'application/javascript',
        'php'        => 'text/plain',
        'serialized' => 'application/vnd.php.serialized',
        'xml'        => 'application/xml',
    ];
    /**
     * Configurations of rest controller 
     */    
    private $_config;
    /**
     * Language variables of rest controller
     */
    private $_locale;


    protected $payload;


    
    public function __construct()
    {
        $this->_config = Config::get('rest');

        $locale = $this->_config['language'] ?? null;
        $locale = !empty($locale) ? $locale : Config::get('general.language');
        $this->_locale = !empty($locale) ? $locale : 'en';
    }

    public function _remap($method, ?array $params = [])
    {
        $class = get_called_class();
        
        // Sure it exists, but can they do anything with it?
        if (!method_exists($class, $method)) 
        {
            return $this->send_error(
                lang('rest.unknown_method', null, $this->_locale), 
                self::HTTP_METHOD_NOT_ALLOWED
            );
        }

        // Call the controller method and passed arguments
        try {
            $instance = new $class;
            $instance->initialize($this->request, $this->response);

            return call_user_func_array([$instance, $method], (array) $params);
        } 
        catch (\Throwable $ex) {
            if (Config::get('general.environment') !== 'dev') 
            {
                $url = explode('?', $this->request->getRequestTarget())[0];
                return $this->send_error(
                    'Mauvaise utilisation de < '.$url.' >. Veuillez consulter la documentation de votre fournisseur', 
                    self::HTTP_BAD_REQUEST
                );
            }
            if ($this->_config['handle_exceptions'] === false) 
            {
                throw $ex;
            }
            // If the method doesn't exist, then the error will be caught and an error response shown
           Exception::Throw($ex);
        }
    }
    
    /**
     * Verifie si les informations du processus sont valide ou pas
     * 
     * @throws Exception
     */
    protected function checkProcess()
    {
        $this->_checkDevProcess();
        $this->_checkClientProcess();
    }

    /**
     * Rend une reponse au client
     * 
     * @param $data Les donnees a renvoyer
     * @param int $status Le statut de la reponse
     * @param bool $die Specifie si on bloqur l'execution de tout autre script apres avoir envoyer les donnees ou pas
     */
    protected function response($data, int $status = self::HTTP_OK, bool $die = false)
    {
        ob_start();
        
        // If the HTTP status is not NULL, then cast as an integer
        if ($status !== null) 
        {
            // So as to be safe later on in the process
            $status = (int) $status;
        }

        // If data is NULL and no HTTP status code provided, then display, error and exit
        if ($data === null AND $status === null) 
        {
            $status = self::HTTP_NOT_FOUND;
        }

        $this->response = $this->response
            ->withCharset(strtolower(Config::get('general.charset') ?? 'utf-8'))
            ->withStatus($status);

        $this->_parseResponse($data);
        
        if ($die === false) 
        {
            // Display the data and exit execution
            return $this->response;
        } 
        else 
        {
            $this->response->send();
            exit;
        }
        ob_end_flush();
    }

    /**
     * Renvoi un message d'erreur au client
     * 
     * @param string $error_msg Le message a enyoyer
     * @param int $http_code Le code de statut de la reponse
     */
    protected function send_error(string $error_msg = "Une erreur s'est produite", int $http_code = self::HTTP_INTERNAL_ERROR)
    {
        return $this->response([
            $this->_config['status_field_name']  => false,
            $this->_config['message_field_name'] => $error_msg,
        ], $http_code, true);
    }
    
    /**
     * Specifie que seules les requetes ajax sont acceptees
     *
     * @return self
     */
    final protected function ajax_only() : self
    {
        $this->_config['ajax_only'] = true;
        return $this;
    }
    /**
     * Definit les methodes authorisees par le web service
     * 
     * @param string ...$methods
     * @return Controller
     */
    final protected function allowed_methods(string ...$methods) : self
    {
        $this->_config['allowed_methods'] = array_map(function($str) {
            return strtoupper($str);
        }, $methods);

        return $this;
    }
    /**
     * Definit le format de donnees a renvoyer au client
     * 
     * @param string $format
     * @return Controller
     */
    final protected function return_format(string $format) : self
    {
        $this->_config['return_format'] = $format;

        return $this;
    }
    /**
     * N'autorise que les acces pas https
     *
     * @return Controller
     */
    final protected function force_https() : self
    {
        $this->_config['force_https'] = true;

        return $this;
    }    
    /**
     * auth
     *
     * @param  string|false $type
     * @return Controller
     */
    final protected function auth($type) : self 
    {
        $this->_config['auth'] = $type;

        return $this;
    }    
    /**
     * Definit la liste des adresses IP a bannir
     * Le premier argument doit etre un boolean specifiant si on active la blacklist ou pas
     * Les autres arguments sont des IP a bannir. Si le premier argument vaut "false", la suite ne sert plus a rien
     *
     * @param  mixed $params
     * @return Controller
     */
    final protected function ip_blacklist(...$params) : self 
    {
        $this->_config['ip_blacklist_enabled'] = true;

        $params = func_get_args();
        $enable = array_shift($params);
        
        if (is_bool($enable)) 
        {
            $this->_config['ip_blacklist_enabled'] = (bool) $enable;
        } 
        else 
        {
            array_unshift($params, $enable);
        }
        $this->_config['ip_blacklist'] = array_merge($this->_config['ip_blacklist'] ?? [], $params);

        return $this;
    }    
    /**
     * Definit la liste des adresses IP qui sont autorisees a acceder a la ressources
     * Le premier argument doit etre un boolean specifiant si on active la whitelist ou pas
     * Les autres arguments sont des IP a autoriser. Si le premier argument vaut "false", la suite ne sert plus a rien
     *
     * @param  mixed $params
     * @return self
     */
    final protected function ip_whitelist(...$params) : self 
    {
        $this->_config['ip_whitelist_enabled'] = true;

        $params = func_get_args();
        $enable = array_shift($params);
        
        if (is_bool($enable)) 
        {
            $this->_config['ip_whitelist_enabled'] = (bool) $enable;
        } 
        else 
        {
            array_unshift($params, $enable);
        }
        $this->_config['ip_whitelist'] = array_merge($this->_config['ip_whitelist'] ?? [], $params);

        return $this;
    }

    /**
     * Genere un token d'authentification
     *
     * @param array $data
     * @return string
     */
    final protected function generateToken(array $data = []) : string
    {
        $jwt_conf = $this->_config['jwt'];

        $payload = array_merge([
            'iat' => time(),
            'iss' => base_url(),
            'exp' => time() + (60 * $jwt_conf['exp_time'])
        ], $data);
        
        try {
            return JWT::encode($payload, $jwt_conf['key']);
        }
        catch(\Exception $e) {
            return $this->send_error('JWT Exception : ' . $e->getMessage(), self::HTTP_INTERNAL_ERROR);
        }
    }

    
    /**
     * Formatte les donnees a envoyer au bon format
     * 
     * @param $data Les donnees a envoyer
     */
    private function _parseResponse($data)
    {
        $format = strtolower($this->_config['return_format']);

        // If the format method exists, call and return the output in that format
        if (method_exists(Format::class, 'to_'.$format)) 
        {
            // CORB protection
            // First, get the output content.
            $output = Format::factory($data)->{'to_'.$format}();

            // Set the format header
            // Then, check if the client asked for a callback, and if the output contains this callback :
            if (isset($this->request->query['callback']) AND $format == 'json' AND preg_match('/^'.$this->request->query['callback'].'/', $output)) 
            {
                $this->response = $this->response->withType($this->_supported_formats['jsonp']);
            } 
            else 
            {
                $this->response = $this->response->withType($this->_supported_formats[$format]);
            }

            // An array must be parsed as a string, so as not to cause an array to string error
            // Json is the most appropriate form for such a data type
            if ($format === 'array') 
            {
                $output = Format::factory($output)->{'to_json'}();
            }
        } 
        else 
        {
            // If an array or object, then parse as a json, so as to be a 'string'
            if (is_array($data) OR is_object($data)) 
            {
                $data = Format::factory($data)->{'to_json'}();
            }
            // Format is not supported, so output the raw data as a string
            $output = $data;
        }

        $this->response = $this->response->withStringBody($output);
    }

    /**
     * Verifie si les informations fournis par le developpeurs du ws sont conforme aux attentes du composant
     * 
     * @throws Exception
     */
    private function _checkDevProcess()
    {
        if (! in_array(strtolower($this->_config['return_format']), $this->allowed_format))
        {
            throw new Exception('Le format de retour "'.$this->_config['return_format'].'" n\'est pas pris en compte');
        }
    }

    /**
     * Verifie si les informations fournis par le client du ws sont conforme aux attentes du developpeur
     * 
     * @throws Exception
     */
    private function _checkClientProcess()
    {
        // Verifie si la requete est en ajax
        if (true !== $this->request->is('ajax') AND true === $this->_config['ajax_only'])
        {
            return $this->send_error(
                lang('rest.ajax_only', null, $this->_locale), 
                self::HTTP_NOT_ACCEPTABLE
            );
        }

        // Verifie si la requete est en https
        if (true !== $this->request->is('https') AND true === $this->_config['force_https']) 
        {
            return $this->send_error(
                lang('rest.unsupported', null, $this->_locale), 
                self::HTTP_FORBIDDEN
            );
        }

        // Verifie si la methode utilisee pour la requete est autorisee
        if (true !== in_array(strtoupper($this->request->getMethod()), $this->_config['allowed_methods']))
        {
            return $this->send_error(
                lang('rest.unknown_method', null,$this->_locale), 
                self::HTTP_NOT_ACCEPTABLE
            );
        }

        // Verifie que l'ip qui emet la requete n'est pas dans la blacklist
        if (true === $this->_config['ip_blacklist_enabled'])
        {
            $this->_config['ip_blacklist'] = join(',', $this->_config['ip_blacklist']);
            
            // Match an ip address in a blacklist e.g. 127.0.0.0, 0.0.0.0
            $pattern = sprintf('/(?:,\s*|^)\Q%s\E(?=,\s*|$)/m', $this->request->clientIp());
            
            // Returns 1, 0 or FALSE (on error only). Therefore implicitly convert 1 to TRUE
            if (preg_match($pattern, $this->_config['ip_blacklist'])) 
            {
                return $this->send_error(
                    lang('rest.ip_denied', null, $this->_locale), 
                    self::HTTP_UNAUTHORIZED
                );
            }
        }

        // Verifie que l'ip qui emet la requete est dans la whitelist
        if (true === $this->_config['ip_whitelist_enabled'])
        {
            $whitelist = $this->_config['ip_whitelist'];
            array_push($whitelist, '127.0.0.1', '0.0.0.0');

            foreach ($whitelist as &$ip) 
            {
                // As $ip is a reference, trim leading and trailing whitespace, then store the new value
                // using the reference
                $ip = trim($ip);
            }

            if (true !== in_array($this->request->clientIp(), $whitelist)) 
            {
                return $this->send_error(
                    lang('rest.ip_unauthorized', null, $this->_locale), 
                    self::HTTP_UNAUTHORIZED
                );
            }
        }

        // Verifie l'authentification du client
        if (false !== $this->_config['auth'] AND true !== $this->request->is('options'))
        {
            if ('bearer' === strtolower($this->_config['auth']))
            {
                $token = $this->getBearerToken();
                try {
                    $this->payload = JWT::decode($token, $this->_config['jwt']['key'], ['HS256']);
                }
                catch(\Exception $e) {
                    return $this->send_error('JWT Exception : ' . $e->getMessage(), self::HTTP_INTERNAL_ERROR);
                }
            }
        }
    }


    /**
     * Recupere le token d'acces a partier des headers
     */
    protected function getBearerToken()
    {
        $headers = $this->getAuthorizationHeader();
        if (empty($headers))
        {
            return $this->send_error(
                lang('rest.token_not_found', null, $this->_locale), 
                self::HTTP_UNAUTHORIZED
            );
        }
        if (preg_match('/Bearer\s(\S+)/', $headers, $matches))
        {
            return $matches[1];
        }
    }
    protected function getAuthorizationHeader()
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
