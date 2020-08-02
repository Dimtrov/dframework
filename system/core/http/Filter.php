<?php
/**
 * dFramework
 *
 * The simplest PHP framework for beginners
 * Copyright (c) 2019, Dimtrov Sarl
 * This content is released under the Mozilla Public License 2 (MPL-2.0)
 *
 * @package	    dFramework
 * @author	    Dimitri Sitchet Tomkeu <dev.dst@gmail.com>
 * @copyright	Copyright (c) 2019, Dimtrov Sarl. (https://dimtrov.hebfree.org)
 * @copyright	Copyright (c) 2019, Dimitri Sitchet Tomkeu. (https://www.facebook.com/dimtrovich)
 * @license	    https://opensource.org/licenses/MPL-2.0 MPL-2.0 License
 * @homepage    https://dimtrov.hebfree.org/works/dframework
 * @version     3.2.1
 */
 
namespace dFramework\core\http;

use dFramework\core\Config;
use dFramework\core\exception\Exception;
use dFramework\core\exception\LoadException;
use dFramework\core\loader\Injector;
use dFramework\core\loader\Service;

/**
 * Filter
 *
 * Pre-processes global input data for security
 *
 * @package		dFramework
 * @subpackage	Core
 * @category    Http
 * @author		Dimitri Sitchet Tomkeu <dev.dst@gmail.com>
 * @link		https://dimtrov.hebfree.org/docs/dframework/api
 * @since       1.0
 * @file        /system/core/http/Filter.php
 */
class Filter
{
    private $response;
    /**
     * @var array
     */
    private $filters = [];
    /**
     * @var integer
     */
    private $index = 0;
    

    /**
     * Contructor
     */
    public function __construct()
    {
        $this->response = Service::response();
    }

    /**
     * Ajoute un middleware a la chine d'execution
     *
     * @param mixed $filters
     * @return void
     */
    public function add($filters)
    {
        $filters = (array) $filters;

        foreach ($filters As $filter)
        {
            if (is_string($filter))
            {
                $this->filters[] = Injector::singleton($filter);
            }
            if (is_callable($filter) OR is_object($filter)) 
            {
                $this->filters[] = $filter;
            }
        }        
    }
    
    /**
     * Execution du middleware
     *
     * @param Request $request
     * @return Response
     */
    public function process(Request $request) : Response 
    {
        $filter = $this->getFilter();
        $this->index++;

        if (empty($filter)) 
        {
            return $this->response;
        }
        if (is_callable($filter)) 
        {
            return $filter($request, $this->response, [$this, 'process']);
        }

        return $filter->process($request, $this);
    }

    /**
     * Recuperation un filtre actuel
     *
     * @return mixed
     */
    private function getFilter()
    {
        if (isset($this->filters[$this->index]))
        {
            return $this->filters[$this->index];
        }

        return null;
    }

    
    public function run(array $middlewares)
    {
        helper('inflector');

        foreach ($middlewares As $middleware)
        {
            $middlewareArray = explode('|', str_replace(' ', '', $middleware));
            $middlewareName = $middlewareArray[0];
            $runMiddleware = true;
            
            if (isset($middlewareArray[1]))
            {
                $options = explode(':', $middlewareArray[1]);
                $type = $options[0];
                $methods = explode(',', $options[1]);
                
                if ($type == 'except') 
                {
                    if (in_array($this->controller->request->method(), $methods)) 
                    {
                        $runMiddleware = false;
                    }
                } 
                else if ($type == 'only') 
                {
                    if (!in_array($this->controller->request->method(), $methods)) 
                    {
                        $runMiddleware = false;
                    }
                }
            }

            $filename = ucfirst(camelize($middlewareName)) . 'Filter';
            if ($runMiddleware == true) 
            {
                $paths = [
                    // Filtres systeme
                    SYST_DIR . 'filters' . DS . $filename . '.php',
                    // Filtres propores a l'appli
                    APP_DIR . 'filters' . DS . $filename . '.php'
                ];
                $filter_exist = false;

                foreach ($paths As $path)
                {
                    if (file_exists($path))
                    {
                        require_once $path;
                        $filter_exist = true;
                        break;
                    }
                }


                if (true === $filter_exist) 
                {
                    $object = Injector::factory($filename, [$this->controller]);
                    $object->run();
                    
                    $this->controller->filters[$middlewareName] = $object;
                } 
                else 
                {
                    if (Config::get('general.environment') == 'dev') 
                    {
                        LoadException::except('
                            Filters not found
                            <br>
                            Unable to load filter: ' . $filename . '.php
                        ', 404);
                    }
                    else 
                    {
                        Exception::except('Sorry something went wrong.');
                    }
                }
            }

        }
    }
}
