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

use Firebase\JWT\JWT;
use dFramework\core\Config;
use dFramework\core\output\Format;
use dFramework\core\exception\Exception;
use dFramework\core\Controller as CoreController;
use dFramework\core\loader\Service;
use dFramework\core\utilities\Arr;
use dFramework\core\utilities\Str;

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
    const HTTP_NO_CONTENT         = 204;
    const HTTP_NOT_MODIFIED       = 304;
    const HTTP_BAD_REQUEST        = 400;
    const HTTP_UNAUTHORIZED       = 401;
    const HTTP_FORBIDDEN          = 403;
    const HTTP_NOT_FOUND          = 404;
    const HTTP_METHOD_NOT_ALLOWED = 405;
    const HTTP_NOT_ACCEPTABLE     = 406;
    const HTTP_CONFLICT           = 409;
    const HTTP_INVALID_TOKEN      = 498;
    const HTTP_INTERNAL_ERROR     = 500;
    const HTTP_NOT_IMPLEMENTED    = 501;


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
     * @var array Configurations of rest controller 
     */    
    private $_config;
    /**
     * @var string Language variables of rest controller
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
            return $this->methodNotAllowed(lang('rest.unknown_method', null, $this->_locale));
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
                return $this->badRequest(lang('rest.bad_used', [$url], $this->_locale));
            }
            if ($this->_config['handle_exceptions'] === false) 
            {
                throw $ex;
            }
            // If the method doesn't exist, then the error will be caught and an error response shown
           Exception::Throw($ex);
        }
    }
    
    public function __call($name, $arguments)
    {
        $method = Str::toCamel($name);
        if (method_exists($this, $method)) 
        {
            return call_user_func_array([$this, $method], $arguments);
        }    
        throw new Exception("Unknow method " .$name);
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
            exit(Service::emitter()->emit($this->response));
        }
        ob_end_flush();
    }

    /**
     * Renvoi un message d'erreur au client
     * 
     * @param string $message Le message a enyoyer
     * @param int $code Le code de statut de la reponse
     * @param array $errors La liste des erreurs rencontrées
     */
    protected function sendError(?string $message = "Une erreur s'est produite", ?int $code = self::HTTP_INTERNAL_ERROR, ?array $errors = [])
    {
        $message  = empty($message) ? "Une erreur s'est produite" : $message;
        $code  = empty($code) ? self::HTTP_INTERNAL_ERROR : $code;
        
        $response = [
            $this->_config['status_field_name']  => false,
            $this->_config['message_field_name'] => $message,
        ];
        if (!empty($errors)) 
        {
            $response[$this->_config['errors_field_name']] = $errors;
        }
        if ($this->_config['strict_mode'] !== true) 
        {
            $response[$this->_config['code_field_name']] = $code;
            $code = self::HTTP_OK;
        }
        return $this->response($response, $code, true);
    }

    /**
     * Renvoi un message de succes au client
     * 
     * @param string $message Le message a enyoyer
     * @param mixed $result Le resultat de la demande
     * @param int $code Le code de statut de la reponse
     */
    protected function sendSuccess(?string $message = "Resultat", $result = null, ?int $code = self::HTTP_OK)
    {
        $message  = empty($message) ? "Resultat" : $message;
        $code  = empty($code) ? self::HTTP_OK : $code;
        
        $response = [
            $this->_config['status_field_name']  => true,
            $this->_config['message_field_name'] => $message,
        ];
        if (!empty($result)) 
        {
            $response[$this->_config['result_field_name']] = $result;
        }
        return $this->response($response, $code, true);
    }


    /**
     * Renvoi un message d'erreur generaliste
     *
     * @param string $message
     * @param integer $code
     * @param array $errors
     * @return void|Response
     */
    protected function fail(string $message, ?int $code = self::HTTP_INTERNAL_ERROR, ?array $errors = [])
    {
        return $this->sendError($message, $code, $errors);
    }

    /**
     * Renvoi un message de succes generaliste
     *
     * @param string $message
     * @param mixed $result
     * @param integer $code
     * @return void|Response
     */
    protected function success(string $message, $result = null, ?int $code = self::HTTP_OK)
    {
        return $this->sendSuccess($message, $result, $code);
    }

    /**
     * Reponse de type bad request
     *
     * @param string $message
     * @param array|null $errors
     * @return void
     */
    protected function badRequest(string $message, ?array $errors = [])
    {
        return $this->fail($message, self::HTTP_BAD_REQUEST, $errors);
    }

    /**
     * Reponse de type conflict
     *
     * @param string $message
     * @param array|null $errors
     * @return void
     */
    protected function conflict(string $message, ?array $errors = [])
    {
        return $this->fail($message, self::HTTP_CONFLICT, $errors);
    }

    /**
     * Reponse de type created
     *
     * @param string $message
     * @param mixed $result
     * @return void
     */
    protected function created(string $message, $result = null)
    {
        return $this->success($message, $result, self::HTTP_CREATED);
    }
    
    /**
     * Reponse de type forbidden
     *
     * @param string $message
     * @param array|null $errors
     * @return void
     */
    protected function forbidden(string $message, ?array $errors = [])
    {
        return $this->fail($message, self::HTTP_FORBIDDEN, $errors);
    }

    /**
     * Reponse de type internal error
     *
     * @param string $message
     * @param array|null $errors
     * @return void
     */
    protected function internalError(string $message, ?array $errors = [])
    {
        return $this->fail($message, self::HTTP_INTERNAL_ERROR, $errors);
    }

    /**
     * Reponse de type invalid token
     *
     * @param string $message
     * @param array|null $errors
     * @return void
     */
    protected function invalidToken(string $message, ?array $errors = [])
    {
        return $this->fail($message, self::HTTP_INVALID_TOKEN, $errors);
    }

    /**
     * Reponse de type method not allowed
     *
     * @param string $message
     * @param array|null $errors
     * @return void
     */
    protected function methodNotAllowed(string $message, ?array $errors = [])
    {
        return $this->fail($message, self::HTTP_METHOD_NOT_ALLOWED, $errors);
    }

    /**
     * Reponse de type no content
     *
     * @param string $message
     * @param mixed $result
     * @return void
     */
    protected function noContent(string $message, $result = null)
    {
        return $this->success($message, $result, self::HTTP_NO_CONTENT);
    }
    
    /**
     * Reponse de type not acceptable
     *
     * @param string $message
     * @param array|null $errors
     * @return void
     */
    protected function notAcceptable(string $message, ?array $errors = [])
    {
        return $this->fail($message, self::HTTP_NOT_ACCEPTABLE, $errors);
    }

    /**
     * Reponse de type not found
     *
     * @param string $message
     * @param array|null $errors
     * @return void
     */
    protected function notFound(string $message, ?array $errors = [])
    {
        return $this->fail($message, self::HTTP_NOT_FOUND, $errors);
    }

    /**
     * Reponse de type not implemented
     *
     * @param string $message
     * @param array|null $errors
     * @return void
     */
    protected function notImplemented(string $message, ?array $errors = [])
    {
        return $this->fail($message, self::HTTP_NOT_IMPLEMENTED, $errors);
    }

    /**
     * Reponse de type ok
     *
     * @param string $message
     * @param mixed $result
     * @return void
     */
    protected function ok(string $message, $result = null)
    {
        return $this->success($message, $result, self::HTTP_OK);
    }
    
    /**
     * Reponse de type unauthorized
     *
     * @param string $message
     * @param array|null $errors
     * @return void
     */
    protected function unauthorized(string $message, ?array $errors = [])
    {
        return $this->fail($message, self::HTTP_UNAUTHORIZED, $errors);
    }

    
    /**
     * Modifie une configuration du controleur rest
     *
     * @param string $key
     * @param mixed $value
     * @return self
     */
    final protected function config(string $key, $value) : self 
    {
        Arr::setRecursive($this->_config, $key, $value);

        return $this;
    }

    /**
     * Specifie que seules les requetes ajax sont acceptees
     *
     * @return self
     */
    final protected function ajaxOnly() : self
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
    final protected function allowedMethods(string ...$methods) : self
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
    final protected function returnFormat(string $format) : self
    {
        $this->_config['return_format'] = $format;

        return $this;
    }
    /**
     * N'autorise que les acces pas https
     *
     * @return Controller
     */
    final protected function forceHttps() : self
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
    final protected function ipBlacklist(...$params) : self 
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
    final protected function ipWhitelist(...$params) : self 
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
     * @param array $config
     * @return string
     */
    final protected function generateToken(array $data = [], array $config = []) : string
    {
        $jwt_conf = array_merge($this->_config['jwt'], $config);

        $payload = array_merge([
            'iat' => time(),
            'iss' => base_url(),
            'exp' => time() + (60 * $jwt_conf['exp_time'])
        ], $data);
        
        try {
            return JWT::encode($payload, $jwt_conf['key']);
        }
        catch(\Exception $e) {
            return $this->internalError('JWT Exception : ' . $e->getMessage());
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
            return $this->notAcceptable(lang('rest.ajax_only', null, $this->_locale));
        }

        // Verifie si la requete est en https
        if (true !== $this->request->is('https') AND true === $this->_config['force_https']) 
        {
            return $this->forbidden(lang('rest.unsupported', null, $this->_locale));
        }

        // Verifie si la methode utilisee pour la requete est autorisee
        if (true !== in_array(strtoupper($this->request->getMethod()), $this->_config['allowed_methods']))
        {
            return $this->notAcceptable(lang('rest.unknown_method', null,$this->_locale));
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
                return $this->unauthorized(lang('rest.ip_denied', null, $this->_locale));
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
                return $this->unauthorized(lang('rest.ip_unauthorized', null, $this->_locale));
            }
        }

        // Verifie l'authentification du client
        if (false !== $this->_config['auth'] AND true !== $this->request->is('options'))
        {
            if ('bearer' === strtolower($this->_config['auth']))
            {
                $payload = $this->decodeToken($this->getBearerToken(), 'bearer');
                
                if ($payload instanceof \Throwable) 
                {
                    return $this->internalError('JWT Exception : ' . $payload->getMessage());
                }
                $this->payload = $payload;
            }
        }
    }

    /**
     * Decode un token d'autorisation
     *
     * @param string $token
     * @param string $authType
     * @return mixed
     */    
    protected function decodeToken($token, string $authType = 'bearer') 
    {
        if ('bearer' === $authType) 
        {
            try {
                return JWT::decode($token, $this->_config['jwt']['key'], ['HS256']);
            }
            catch(\Throwable $e) {
                return $e;
            }
        }
        return null;
    }

    /**
     * Recupere le token d'acces a partier des headers
     */
    protected function getBearerToken()
    {
        $headers = $this->getAuthorizationHeader();
        if (empty($headers))
        {
            return $this->unauthorized(lang('rest.token_not_found', null, $this->_locale));
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
