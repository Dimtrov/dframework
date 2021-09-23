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
 *  @version    3.4.0
 */

namespace dFramework\core\controllers;

use dFramework\core\Entity;
use dFramework\core\exception\Exception;
use dFramework\core\loader\Service;
use dFramework\core\output\Format;
use dFramework\core\utilities\Arr;
use dFramework\core\utilities\Jwt;
use dFramework\middlewares\Cors;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use ReflectionAnnotatedClass;
use ReflectionAnnotatedMethod;
use Throwable;

/**
 * RestController
 *
 * A foundation controller for REST API
 *
 * @package		dFramework
 * @subpackage	Core
 * @category	Controllers
 * @author		Dimitri Sitchet Tomkeu <devcode.dst@gmail.com>
 * @link		https://dimtrov.hebfree.org/docs/dframework/api/Controller.html
 * @since       3.4.0
 * @credit      CI Rest Server - by Chris Kacerguis <chriskacerguis@gmail.com> - https://github.com/chriskacerguis/ci-restserver
 * @file		/system/core/controllers/RestController.php
 */
class RestController extends BaseController
{
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

	/**
	 * @var array|object Payload from jwt token
	 */
    protected $payload;

	/**
     * {@inheritdoc}
     */
	public function initialize(ServerRequestInterface $request, ResponseInterface $response)
	{
		parent::initialize($request, $response);
	}
	public function __construct()
    {
		parent::__construct();

        $this->_config = config('rest');

        $locale = $this->_config['language'] ?? null;
        $locale = !empty($locale) ? $locale : config('general.language');
        $this->_locale = !empty($locale) ? $locale : 'en';
    }
	/**
     * @param string $method
     * @param array|null $params
     * @return \dFramework\core\http\Response|mixed|void
     * @throws Throwable
     */
    public function _remap(string $method, ?array $params = [])
    {
        $class = get_called_class();

        // Sure it exists, but can they do anything with it?
        if (!method_exists($class, $method))
        {
            return $this->methodNotAllowed(lang('rest.unknown_method', null, $this->_locale));
        }

        // Call the controller method and passed arguments
        try {
            $instance = Service::injector()->get($class);
            $instance->initialize($this->request, $this->response);

            require_once __DIR__ . DS . 'annotations.php';

            $reflection = new ReflectionAnnotatedClass($instance);
            $this->execAnnotations($reflection);

            $reflection = new ReflectionAnnotatedMethod($instance, $method);
            $this->execAnnotations($reflection);

            $this->checkProcess();
			$instance->payload = $this->payload;

            return Service::injector()->call([$instance, $method], (array) $params);
        }
        catch (Throwable $ex) {
            if (!on_dev())
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


	/**
	 * Formatte les données à renvoyer lorsqu'il s'agit des objets de la classe Entity
	 *
	 * @param mixed $element
	 * @return mixed
	 */
	protected function formatEntity($element)
	{
		if ($element instanceof Entity)
		{
			if (method_exists($element, 'format'))
			{
				$element = $element->format();
			}
			else
			{
				$element = $element->toArray();
			}
		}

		return $element;
	}

	/**
     * Genere un token d'authentification
     *
     * @param array $data
     * @param array $config
     * @return string
     */
    protected function generateToken(array $data = [], array $config = []) : string
    {
        try {
            return Jwt::encode($data, $config);
        }
        catch(\Exception $e) {
            return $this->internalError($e->getMessage());
        }
    }

	/**
     * Decode un token d'autorisation
     *
     * @param string $token
     * @param string $authType
     * @param array $config
     * @return mixed
     */
    protected function decodeToken(string $token, string $authType = 'bearer', array $config = [])
    {
        if ('bearer' === $authType)
        {
            try {
                return JWT::decode($token, $config);
            }
            catch(Throwable $e) {
                return $e;
            }
        }
        return null;
    }

    /**
     * Recupere le token d'acces a partier des headers
	 *
	 * @return string|null
     */
    protected function getBearerToken() : ?string
    {
		return Jwt::getToken();
    }

	/**
	 * Recupere le header "Authorization"
	 *
	 * @return string|null
	 */
	protected function getAuthorizationHeader() : ?string
    {
		return Jwt::getAuthorization();
    }


    /**
     * Verifie si les informations du processus sont valide ou pas
     *
     * @throws Exception
     */
    final protected function checkProcess()
    {
        $this->_checkDevProcess();

        if (!$this->_checkClientProcess())
        {
            Service::emitter()->emit($this->response);
            exit;
        }
    }

	/**
     * Rend une reponse au client
     *
     * @param mixed $data Les donnees a renvoyer
     * @param int $status Le statut de la reponse
     * @param bool $die Specifie si on bloqur l'execution de tout autre script apres avoir envoyer les donnees ou pas
     */
    final protected function response($data, int $status = self::HTTP_OK, bool $die = false)
    {
		ob_start();
		$die = $this->_config['die_mode'] ?? null === true ? true : $die;

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
            ->withCharset(strtolower(config('general.charset') ?? 'utf-8'))
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
     * @return \dFramework\core\http\Response|void
     */
    protected function sendError(?string $message = "Une erreur s'est produite", ?int $code = self::HTTP_INTERNAL_ERROR, ?array $errors = [])
    {
        $message  = empty($message) ? "Une erreur s'est produite" : $message;
        $code  = empty($code) ? self::HTTP_INTERNAL_ERROR : $code;

		$response = [
			$this->_config['message_field_name'] ?? 'message' => $message
		];
		if (!empty($this->_config['status_field_name']))
		{
			$response[$this->_config['status_field_name']] = false;
		}
		if (!empty($this->_config['code_field_name']))
		{
			$response[$this->_config['code_field_name']] = $code;
		}

        if (!empty($errors))
        {
            $response[$this->_config['errors_field_name'] ?? 'errors'] = $errors;
        }
        if ($this->_config['strict_mode'] !== true)
        {
            $code = self::HTTP_OK;
        }
        return $this->response($response, $code);
    }

    /**
     * Renvoi un message de succes au client
     *
     * @param string $message Le message a enyoyer
     * @param mixed $result Le resultat de la demande
     * @param int $code Le code de statut de la reponse
     * @return \dFramework\core\http\Response
     */
    protected function sendSuccess(?string $message = "Resultat", $result = null, ?int $code = self::HTTP_OK)
    {
        $message  = empty($message) ? "Resultat" : $message;
        $code  = empty($code) ? self::HTTP_OK : $code;

        $response = [
			$this->_config['message_field_name'] ?? 'message' => $message
		];
		if (!empty($this->_config['status_field_name']))
		{
			$response[$this->_config['status_field_name']] = true;
		}

		if (is_array($result))
        {
            $result = array_map(function($element) {
                return $this->formatEntity($element);
            }, $result);
        }

		$response[$this->_config['result_field_name'] ?? 'result'] = $this->formatEntity($result);

		return $this->response($response, $code);
    }

	/**
     * Renvoi un message d'erreur generaliste
     *
     * @param string $message
     * @param integer $code
     * @param array $errors
     * @return \dFramework\core\http\Response|void
     */
    final protected function fail(string $message, ?int $code = self::HTTP_INTERNAL_ERROR, ?array $errors = [])
    {
        return $this->sendError($message, $code, $errors);
    }

    /**
     * Renvoi un message de succes generaliste
     *
     * @param string $message
     * @param mixed $result
     * @param integer $code
     * @return \dFramework\core\http\Response|void
     */
    final protected function success(string $message, $result = null, ?int $code = self::HTTP_OK)
    {
        return $this->sendSuccess($message, $result, $code);
    }

    /**
     * Reponse de type bad request
     *
     * @param string $message
     * @param array|null $errors
     * @return \dFramework\core\http\Response|void
     */
    final protected function badRequest(string $message, ?array $errors = [])
    {
        return $this->fail($message, self::HTTP_BAD_REQUEST, $errors);
    }

    /**
     * Reponse de type conflict
     *
     * @param string $message
     * @param array|null $errors
     * @return \dFramework\core\http\Response|void
     */
    final protected function conflict(string $message, ?array $errors = [])
    {
        return $this->fail($message, self::HTTP_CONFLICT, $errors);
    }

    /**
     * Reponse de type created
     *
     * @param string $message
     * @param mixed $result
     * @return \dFramework\core\http\Response|void
     */
    final protected function created(string $message, $result = null)
    {
        return $this->success($message, $result, self::HTTP_CREATED);
    }

    /**
     * Reponse de type forbidden
     *
     * @param string $message
     * @param array|null $errors
     * @return \dFramework\core\http\Response|void
     */
    final protected function forbidden(string $message, ?array $errors = [])
    {
        return $this->fail($message, self::HTTP_FORBIDDEN, $errors);
    }

    /**
     * Reponse de type internal error
     *
     * @param string $message
     * @param array|null $errors
     * @return \dFramework\core\http\Response|void
     */
    final protected function internalError(string $message, ?array $errors = [])
    {
        return $this->fail($message, self::HTTP_INTERNAL_ERROR, $errors);
    }

    /**
     * Reponse de type invalid token
     *
     * @param string $message
     * @param array|null $errors
     * @return \dFramework\core\http\Response|void
     */
    final protected function invalidToken(string $message, ?array $errors = [])
    {
        return $this->fail($message, self::HTTP_INVALID_TOKEN, $errors);
    }

    /**
     * Reponse de type method not allowed
     *
     * @param string $message
     * @param array|null $errors
     * @return \dFramework\core\http\Response|void
     */
    final protected function methodNotAllowed(string $message, ?array $errors = [])
    {
        return $this->fail($message, self::HTTP_METHOD_NOT_ALLOWED, $errors);
    }

    /**
     * Reponse de type no content
     *
     * @param string $message
     * @param mixed $result
     * @return \dFramework\core\http\Response|void
     */
    final protected function noContent(string $message, $result = null)
    {
        return $this->success($message, $result, self::HTTP_NO_CONTENT);
    }

    /**
     * Reponse de type not acceptable
     *
     * @param string $message
     * @param array|null $errors
     * @return \dFramework\core\http\Response|void
     */
    final protected function notAcceptable(string $message, ?array $errors = [])
    {
        return $this->fail($message, self::HTTP_NOT_ACCEPTABLE, $errors);
    }

    /**
     * Reponse de type not found
     *
     * @param string $message
     * @param array|null $errors
     * @return \dFramework\core\http\Response|void
     */
    final protected function notFound(string $message, ?array $errors = [])
    {
        return $this->fail($message, self::HTTP_NOT_FOUND, $errors);
    }

    /**
     * Reponse de type not implemented
     *
     * @param string $message
     * @param array|null $errors
     * @return \dFramework\core\http\Response|void
     */
    final protected function notImplemented(string $message, ?array $errors = [])
    {
        return $this->fail($message, self::HTTP_NOT_IMPLEMENTED, $errors);
    }

    /**
     * Reponse de type ok
     *
     * @param string $message
     * @param mixed $result
     * @return \dFramework\core\http\Response|void
     */
    final protected function ok(string $message, $result = null)
    {
        return $this->success($message, $result, self::HTTP_OK);
    }

    /**
     * Reponse de type unauthorized
     *
     * @param string $message
     * @param array|null $errors
     * @return \dFramework\core\http\Response|void
     */
    final protected function unauthorized(string $message, ?array $errors = [])
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
     * Recupere les donnees envoyees en POST
     *
     * @param true|string|null $key Si $key === true, retourne les donnees dans un objet
     * @return mixed
     */
    final protected function postFields($key = null)
    {
        $post = $this->request->getParsedBody();
        $input = $this->request->input();

        if (!empty($input))
        {
            $input = json_decode($input, true);
            $post = array_merge($post, $input);
        }
        if (!empty($key) AND $key !== true)
        {
            return $post[$key] ?? null;
        }
        return $key === true ? (object) $post : $post;
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
     * @return self
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
     * @return self
     */
    final protected function returnFormat(string $format) : self
    {
        $this->_config['return_format'] = $format;

        return $this;
    }

    /**
     * N'autorise que les acces pas https
     *
     * @return self
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
     * @return self
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
     * @return self
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
     * Formatte les donnees a envoyer au bon format
     *
     * @param mixed $data Les donnees a envoyer
     */
    private function _parseResponse($data)
    {
        $format = strtolower($this->_config['return_format']);
        $formatter = 'to'.ucfirst($format);

        // If the format method exists, call and return the output in that format
        if (method_exists(Format::class, $formatter))
        {
            // CORB protection
            // First, get the output content.
            $output = Format::factory($data)->{$formatter}();

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
                $output = Format::factory($output)->{'toJson'}();
            }
        }
        else
        {
            // If an array or object, then parse as a json, so as to be a 'string'
            if (is_array($data) OR is_object($data))
            {
                $data = Format::factory($data)->{'toJson'}();
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
     * @return bool
     * @throws Exception
     */
    private function _checkClientProcess() : bool
    {
        // Verifie si la requete est en ajax
        if (true !== $this->request->is('ajax') AND true === $this->_config['ajax_only'])
        {
            $response = $this->notAcceptable(lang('rest.ajax_only', null, $this->_locale));
            if ($response instanceof ResponseInterface)
            {
                $this->response = $response;
            }
            return false;
        }

        // Verifie si la requete est en https
        if (true !== $this->request->is('https') AND true === $this->_config['force_https'])
        {
            $response = $this->forbidden(lang('rest.unsupported', null, $this->_locale));
            if ($response instanceof ResponseInterface)
            {
                $this->response = $response;
            }
            return false;
        }

        // Verifie si la methode utilisee pour la requete est autorisee
        if (true !== in_array(strtoupper($this->request->getMethod()), $this->_config['allowed_methods']))
        {
            $response = $this->notAcceptable(lang('rest.unknown_method', null,$this->_locale));
            if ($response instanceof ResponseInterface)
            {
                $this->response = $response;
            }
            return false;
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
                $response = $this->unauthorized(lang('rest.ip_denied', null, $this->_locale));
                if ($response instanceof ResponseInterface)
                {
                    $this->response = $response;
                }
                return false;
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
                $response = $this->unauthorized(lang('rest.ip_unauthorized', null, $this->_locale));
                if ($response instanceof ResponseInterface)
                {
                    $this->response = $response;
                }
                return false;
            }
        }

        // Verifie l'authentification du client
        if (false !== $this->_config['auth'] AND true !== $this->request->is('options'))
        {
            if ('bearer' === strtolower($this->_config['auth']))
            {
                $payload = $this->decodeToken($this->getBearerToken(), 'bearer');

                if ($payload instanceof Throwable)
                {
					$response = $this->invalidToken($payload->getMessage());
                    if ($response instanceof ResponseInterface)
                    {
                        $this->response = $response;
                    }
                    return false;
                }
                $this->payload = $payload;
            }
        }

        return true;
    }

	/**
     * @param ReflectionAnnotatedClass|ReflectionAnnotatedMethod $reflection
     */
    private function execAnnotations($reflection)
    {
        if ($annotation = $reflection->getAnnotation('Auth'))
        {
            $this->auth($annotation->value);
        }
        if ($annotation = $reflection->getAnnotation('Methods'))
        {
            $this->allowedMethods(... (array) $annotation->value);
        }
        if ($annotation = $reflection->getAnnotation('AjaxOnly'))
        {
            $this->ajaxOnly();
        }
        if ($annotation = $reflection->getAnnotation('IpBlackList'))
        {
            $this->ipBlacklist(... (array) $annotation->value);
        }
        if ($annotation = $reflection->getAnnotation('IpWhiteList'))
        {
            $this->ipWhitelist(... (array) $annotation->value);
        }
        if ($annotation = $reflection->getAnnotation('ForceHttps'))
        {
            $this->forceHttps();
        }
        if ($annotation = $reflection->getAnnotation('Cors'))
        {
            $config = [];
            $value = $annotation->value;

            $origin = $value['origin'] ?? null;
            if ($origin === false OR $origin === 'false')
            {
                $config['AllowOrigin'] = false;
            }
            elseif (!empty($origin))
            {
                $config['AllowOrigin'] = (array) $origin;
            }

            $this->runMiddleware(new Cors($config));
        }
    }
}
