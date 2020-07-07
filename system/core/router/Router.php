<?php
/**
 *  dFramework
 *
 *  The simplest PHP framework for beginners
 *  Copyright (c) 2019, Dimtrov Sarl
 *  This content is released under the Mozilla Public License 2 (MPL-2.0)
 *
 *  @package	dFramework
 *  @author	    Dimitri Sitchet Tomkeu <dev.dst@gmail.com>
 *  @copyright	Copyright (c) 2019, Dimtrov Sarl. (https://dimtrov.hebfree.org)
 *  @copyright	Copyright (c) 2019, Dimitri Sitchet Tomkeu. (https://www.facebook.com/dimtrovich)
 *  @license	https://opensource.org/licenses/MPL-2.0 MPL-2.0 License
 *  @homepage	https://dimtrov.hebfree.org/works/dframework
 *  @version    3.2
 */

namespace dFramework\core\router;

use dFramework\core\Config;
use dFramework\core\exception\RouterException;
use dFramework\core\utilities\Tableau;
use dFramework\core\loader\Service;

/**
 * Router
 *
 * Make a route
 *
 * @package		dFramework
 * @subpackage	Core
 * @category    Route
 * @author		Dimitri Sitchet Tomkeu <dev.dst@gmail.com>
 * @link		https://dimtrov.hebfree.org/docs/dframework/api/
 * @since       2.0
 * @file	    /system/core/route/Router.php
 */

class Router
{
    /**
     * @var string
     */
    private $url;
    
    /**
     * @var array
     */
    private $routes = [
        'POST'   => [],
        'GET'    => [],
        'PUT'    => [],
        'PATCH'  => [],
        'DELETE' => [],
    ];
    /**
     * @var array
     */
    private $envRoutes = [
        'dev'  => [],
        'prod' => [],
        'test' => []
    ];
    /**
     * @var array
     */
    private $namedRoutes = [];
    /**
     * @var array
     */
    private $envNamedRoutes = [
        'dev'  => [],
        'prod' => [],
        'test' => []
    ];
    /**
     * @var array
     */
    private $redirectedRoutes = [];
    /**
     * @var array
     */
    private $envRedirectedRoutes = [
        'dev'  => [],
        'prod' => [],
        'test' => []
    ];

    /**
     * @var array
     */
    private $config = [
        'default_controller' => 'Home',
        'default_method'     => 'index',
        'auto_route'         => true,
    ];
    /**
     * @var array
     */
    private $placeholders = [
        'alpha'    => '[a-zA-Z]+',
        'alphanum' => '[a-zA-Z0-9]+',
        'any'      => '[^ /]+',
        'num'      => '[0-9]+',
        'slug'     => '[a-z0-9-]+',
    ];
    

    /**
     * Router constructor.
     */
    private function __construct()
    {
        $this->url = Service::request()->url ?? '/';
     
        $this->envRoutes = [
            'dev'  => $this->routes,
            'prod' => $this->routes,
            'test' => $this->routes,
        ];

        $this->config = array_merge($this->config, (array) Config::get('route'));
    }

    /**
     * @var null
     */
    private static $_instance = null;

    /**
     * @return Router
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
     * @throws RouterException
     * @throws \ReflectionException
     */
    public static function init()
    {
        $instance = self::instance();

        $routes                 = $instance->config;
        $environments           = $routes['environment'] ?? [];
        $groups                 = $routes['group'] ?? [];
        $instance->placeholders = array_merge($instance->placeholders, (array) $routes['placeholders'] ?? []);

        $routes = Tableau::remove($routes, 'default_controller');
        $routes = Tableau::remove($routes, 'default_method');
        $routes = Tableau::remove($routes, 'auto_route');
        $routes = Tableau::remove($routes, 'environment');
        $routes = Tableau::remove($routes, 'group');
        $routes = Tableau::remove($routes, 'placeholders');
      
        $instance
            ->mapEnvironments($environments)
            ->mapGroups($groups)
            ->mapRoutes($routes)
            ->run();
    }

    /**
     * @param string $name
     * @param array $params
     * @return mixed
     * @throws RouterException
     */
    public static function url(string $name, array $params = [])
    {
        $instance = self::instance();
        if (!isset($instance->namedRoutes[$name]))
        {
            RouterException::except('No route matches this name', 404);
        }
        return $instance->namedRoutes[$name]->getUrl($params);
    }



    


    /**
     * Delivre les placeholders enregistres
     *
     * @return array
     */
    public static function getPlaceholders() : array
    {
        return self::instance()->placeholders ?? [];
    }

    /**
     * Recuperation des routes d'environnement
     *
     * @param array $environments
     * @param array $routes
     * @return Router
     */
    private function mapEnvironments(array $environments) : self
    {
        foreach ($environments As $key => &$value) 
        {
            if (!is_string($key) OR !is_array($value)) 
            {
                RouterException::show('Definition des routes d\'environnement mal formée');
            }
            
            foreach ($value As $k => $v) 
            {
                if (is_string($k)) 
                {
                    $v = (array) $v;

                    $route = [];
                    /**
                     * Controleur ou callable a lancer
                     */
                    $route[0] = $v[0] ?? null;
                    /**
                     * Methode HTTP autorisee
                     */
                    $route[1] = $v[1] ?? null;
                    /**
                     * Route de redirection
                     */
                    $route[2] = $v[2] ?? null;
                    /**
                     * Nom de la route
                     */
                    $route[3] = $v[3] ?? null;
                    /**
                     * Filtre a utiliser
                     */
                    $route[4] = $v[4] ?? [];
                    
                    // Ajout de la nouvelle route mappee
                    $this->mapRoutes([$k => $route], $key);
                }
            }
        }

        return $this;
    }

    /**
     * Parse les collections de chemins et les ajoutes aux chemins
     *
     * @param array $groups
     * @param array $routes
     * @return Router
     */
    private function mapGroups(array $groups) : self
    {
        foreach ($groups As $key => &$value) 
        {
            if (!is_string($key) OR !is_array($value)) 
            {
                RouterException::show('Definition de la collection de routes mal formée');
            }

            $prefix = $key;
            $filters = $value[0] ?? [];
            unset($value[0]);

            foreach ($value As $k => $v) 
            {
                if (is_int($k) AND is_array($v))
                {
                    foreach ($v As $x => $y) 
                    {
                        foreach ($y As &$a) 
                        {
                            $a = (array) $a;
                            $a[4] = array_merge($filters, $a[4] ?? []);
                        }
                        $v[$prefix.'/'.$x] = $y;
                        unset($v[$x]);
                    }
                    $this->mapGroups($v);
                }
                else if (is_string($k)) 
                {
                    $v = (array) $v;

                    $route = [];
                    /**
                     * Controleur ou callable a lancer
                     */
                    if (!empty($v[0])) 
                    {
                        if (is_string($v[0]))
                        {
                            $route[0] = trim($v[0][0] === '/' ? $v[0] : $prefix.'/'.$v[0], '/');
                        }
                        else 
                        {
                            $route[0] = $v[0];
                        }
                    }
                    else 
                    {
                        $route[0] = null;
                    } 
                    /**
                     * Methode HTTP autorisee
                     */
                    $route[1] = $v[1] ?? null;
                    /**
                     * Route de redirection
                     */
                    $route[2] = $v[2] ?? null;
                    /**
                     * Nom de la route
                     */
                    $route[3] = $v[3] ?? null;
                    /**
                     * Filtre a utiliser
                     */
                    $route[4] = array_merge($filters, (array)($v[4] ?? []));
                    
                    // Ajout de la nouvelle route mappee
                    $this->mapRoutes([$prefix.'/'.$k => $route]);
                }
            }
        }

        return $this;
    }

    /**
     * Captures les chemins definis pour les ajouter aux routes
     *
     * @param array $routes
     * @param string|null $environment
     * @return Router
     */
    private function mapRoutes(array $routes, ?string $environment = null) : self
    {
        foreach ($routes As $key => $value)
        {
            $value = (array) $value;
            $path = $key;
            
            $callable   = $value[0];
            $methods    = empty($value[1]) ? 'get|post|put|patch|delete' : $value[1];
            $name       = $value[2] ?? null;
            $redirected = $value[3] ?? null;
            $filters    = $value[4] ?? [];

            $methods = explode('|', $methods);
            foreach ($methods As $method) 
            {
                if (in_array(strtolower($method), ['get', 'post', 'put', 'patch', 'delete']))
                {
                    if (empty($redirected))
                    {
                        $this->add($path, $callable, $method, $name, $environment);
                    }
                    else 
                    {
                        $this->addRedirected($path, $redirected, $method, $name, $environment);
                    }
                }
            }
        }

        return $this;
    }


    /**
     * Ajoute un chemin aux routes de l'application
     * 
     * @param string $path
     * @param string|callable $callable
     * @param string $method
     * @param string|null $name
     * @param string|null $environment
     * @return Route
     */
    private function add(string $path, $callable, string $method, ?string $name = null, ?string $environment)
    {
        $route = new Route($path, $callable);
        $method = strtoupper($method);
        $environment = strtolower($environment);

        if (in_array($environment, ['dev', 'prod', 'test']))
        {
            $this->envRoutes[$environment][$method][] = $route; 
            
            if (!empty($name)) 
            {
                $this->envNamedRoutes[$environment][$name] = $route;
            }
            else if (is_string($callable))
            {
                $this->envNamedRoutes[$environment][$callable] = $route;
            }
        }
        else 
        {
            $this->routes[$method][] = $route;
        
            if (!empty($name)) 
            {
                $this->namedRoutes[$name] = $route;
            }
            else if (is_string($callable))
            {
                $this->namedRoutes[$callable] = $route;
            }    
        }
        
        return $route;
    }

    /**
     * Ajoute une route de redirection aux routes de l'application
     * 
     * @param string $path
     * @param string $redirected
     * @param string $method
     * @param string|null $name
     * @return Route
     */
    private function addRedirected(string $path, string $redirected, string $method, ?string $name = null, ?string $environment = null)
    {
        $route = new Route($path, '');
        $method = strtoupper($method);
        $environment = strtolower($environment);

        if (in_array($environment, ['dev', 'prod', 'test'])) 
        {
            $this->envRedirectedRoutes[$environment][$method][] = [$route, $redirected];
            
            if (!empty($name)) 
            {
                $this->envNamedRoutes[$environment][$name] = $route;
            }
        }
        else 
        {
            $this->redirectedRoutes[$method][] = [$route, $redirected];
            
            if (!empty($name)) 
            {
                $this->namedRoutes[$name] = $route;
            }
        }
        
        return $route;
    }

    /**
     * @return
     * @throws RouterException
     */
    private function run()
    {
        $method = strtoupper(Service::request()->method());
        
        if (empty($method) OR !isset($this->routes[$method]))
        {
            if ('cli' !== php_sapi_name())
            {
                throw new RouterException('REQUEST_METHOD does not exist', 405);
            }
        }
        $environment = strtolower(Config::get('general.environment'));
        

        $routes = $this->envRedirectedRoutes[$environment][$method] ?? [];
        /**
         * On fouille d'abord les routes de redirection de l'environnement actuel
         */
        if (!empty($routes))
        {
            foreach ($routes As $value) 
            {
                $redirected = explode('|', $value[1] ?? '');
                $route = $value[0];
                $namedRoute = $this->envNamedRoutes[$method][$redirected[0]] ?? null;
                
                $statusCode = $redirected[1] ?? 302;
                if (100 < $statusCode OR $statusCode > 699) {
                    $statusCode = 302;
                }

                if ($route->match($this->url))
                {
                    $response = Service::response();
                    $response->statusCode($statusCode);
                    
                    if (!empty($namedRoute))
                    {
                        $response->location(ltrim(base_url(), '/').'/'.rtrim($namedRoute->getPath(), '/'));
                        $response->send();
                    }
                    else if (filter_var($redirected[0], FILTER_VALIDATE_URL)) 
                    {
                        $response->location($redirected[0]);
                        $response->send();
                    }
                    return;
                }
            }
        }
        $routes = $this->envRoutes[$environment][$method] ?? [];
        /**
         * On fouille ensuite les routes classiques de l'environnement actuel
         */
        if (!empty($routes))
        {
            foreach ($routes As $route) 
            {
                if ($route->match($this->url)) 
                {
                    return $route->call();
                }
            }
        }

        
        $routes = $this->redirectedRoutes[$method] ?? [];
        /**
         * On fouille apres les routes de redirection standards
         */
        if (!empty($routes))
        {
            foreach ($routes As $value) 
            {
                $redirected = explode('|', $value[1] ?? '');
                $route = $value[0];
                $namedRoute = $this->namedRoutes[$redirected[0]] ?? null;
                
                $statusCode = $redirected[1] ?? 302;
                if (100 < $statusCode OR $statusCode > 699) {
                    $statusCode = 302;
                }

                if ($route->match($this->url))
                {
                    $response = Service::response();
                    $response->statusCode($statusCode);
                    
                    if (!empty($namedRoute))
                    {
                        $response->location(ltrim(base_url(), '/').'/'.rtrim($namedRoute->getPath(), '/'));
                        $response->send();
                    }
                    else if (filter_var($redirected[0], FILTER_VALIDATE_URL)) 
                    {
                        $response->location($redirected[0]);
                        $response->send();
                    }
                    return;
                }
            }
        }
        $routes = $this->routes[$method] ?? [];
        /**
         * On fouille ensuite les routes classiques standards
         */
        if (!empty($routes))
        {
            foreach ($routes As $route)
            {
                if ($route->match($this->url))
                {
                    return $route->call();
                }
            }
        }

        
        if (true === $this->config['auto_route'])
        {
            return Dispatcher::init();
        }

    }
}
