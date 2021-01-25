<?php
/**
 * dFramework
 *
 * The simplest PHP framework for beginners
 * Copyright (c) 2019 - 2021, Dimtrov Lab's
 * This content is released under the Mozilla Public License 2 (MPL-2.0)
 *
 * @package	    dFramework
 * @author	    Dimitri Sitchet Tomkeu <dev.dst@gmail.com>
 * @copyright	Copyright (c) 2019 - 2021, Dimtrov Lab's. (https://dimtrov.hebfree.org)
 * @copyright	Copyright (c) 2019 - 2021, Dimitri Sitchet Tomkeu. (https://www.facebook.com/dimtrovich)
 * @license	    https://opensource.org/licenses/MPL-2.0 MPL-2.0 License
 * @homepage    https://dimtrov.hebfree.org/works/dframework
 * @version     3.2.3
 */
 
namespace dFramework\core\http;

use dFramework\core\loader\Service;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Filter
 *
 * Gestionnaire de filtre http (middleware)
 *
 * @package		dFramework
 * @subpackage	Core
 * @category    Http
 * @author		Dimitri Sitchet Tomkeu <dev.dst@gmail.com>
 * @link		https://dimtrov.hebfree.org/docs/dframework/filter
 * @since       3.2.0
 * @file        /system/core/http/Filter.php
 */
class Filter implements RequestHandlerInterface
{
    /**
     * @var ResponseInterface
     */
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
                $this->filters[] = Service::container()->get($filter);
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
     * @param ServerRequestInterface $request
     * @return Response
     */
    public function handle(ServerRequestInterface $request) : ResponseInterface
    {
        $filter = $this->getFilter();
        
        if (empty($filter)) 
        {
            return $this->response;
        }
        if (is_callable($filter)) 
        {
            return $filter($request, $this->response, [$this, 'handle']);
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
        $filter = null;

        if (isset($this->filters[$this->index]))
        {
            $filter = $this->filters[$this->index];
        }
        $this->index++;

        return $filter;
    }
}
