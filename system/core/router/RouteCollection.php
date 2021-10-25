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
 *  @version    3.3.5
 */

namespace dFramework\core\router;

use dFramework\core\exception\RouterException;

/**
 * Contains a collection of routes.
 *
 * Provides an interface for adding/removing routes
 * and parsing/generating URLs with the routes it contains.
 *
 * @package		dFramework
 * @subpackage	Core
 * @category    Router
 * @author		Dimitri Sitchet Tomkeu <devcode.dst@gmail.com>
 * @link		https://dimtrov.hebfree.org/docs/dframework/api
 * @since       2.0
 * @credit		CodeIgniter 4.0 (CodeIgniter\Router\RouteCollection)
 * @file        /system/core/router/RouteCollection.php
 */
class RouteCollection
{
    /**
     * Routes config
     *
     * @var array
     */
    protected $config = [
        /**
         * @var string The name of the default controller to use when no other controller is specified.
         */
        'default_controller'  => 'Home',
        /**
         * @var string The name of the default method to use when no other method has been specified.
         */
        'default_method'      => 'index',
        /**
         * @var string The placeholder used when routing 'resources' when no other placeholder has been specified.
         */
        'default_placeholder' => 'any',
        /**
         * @var boolean Whether to match URI against Controllers when it doesn't match defined routes.
         */
        'auto_route'          => true,
    ];
	/**
	 * Global middleware
	 *
	 * @var array
	 */
	protected $middlewares = [];
    /**
	 * Defined placeholders that can be used
	 * within the
	 *
	 * @var array
	 */
	protected $placeholders = [
		'any'      => '.*',
		'segment'  => '[^/]+',
		'alphanum' => '[a-zA-Z0-9]+',
		'num'      => '[0-9]+',
		'alpha'    => '[a-zA-Z]+',
		'hash'     => '[^/]+',
		'slug'     => '[a-z0-9-]+',
    ];
    /**
	 * An array of all routes and their mappings.
	 *
	 * @var array
	 */
	protected $routes = [
		'*'       => [],
		'options' => [],
		'get'     => [],
		'head'    => [],
		'post'    => [],
		'put'     => [],
		'delete'  => [],
		'trace'   => [],
		'connect' => [],
		'cli'     => [],
	];
    /**
	 * The default list of HTTP methods (and CLI for command line usage)
	 * that is allowed if no other method is provided.
	 *
	 * @var array
	 */
	protected $defaultHTTPMethods = [
		'options',
		'get',
		'head',
		'post',
		'put',
		'delete',
		'trace',
		'connect',
		'cli',
	];

	/**
	 * The current method that the script is being called by.
	 *
	 * @var string
	 */
	protected $HTTPVerb;
	/**
	 * The name of the current group, if any.
	 *
	 * @var string
	 */
	protected $group = null;
    /**
	 * Stores copy of current options being applied during creation.
	 *
	 * @var null
	 */
	protected $options = null;

	/**
	 * Array of routes options
	 *
	 * @var array
	 */
	protected $routesOptions = [];
	/**
	 * A callable that will be shown
	 * when the route cannot be matched.
	 *
	 * @var string|\Closure
	 */
	protected $override404;


    //--------------------------------------------------------------------

    /**
	 * Registers a new constraint with the system. Constraints are used
	 * by the routes as placeholders for regular expressions to make defining
	 * the routes more human-friendly.
	 *
	 * You can pass an associative array as $placeholder, and have
	 * multiple placeholders added at once.
	 *
	 * @param string|array $placeholder
	 * @param string       $pattern
	 *
	 * @return self
	 */
	public function placeholder($placeholder, string $pattern = null) : self
	{
		if (!is_array($placeholder))
		{
			$placeholder = [$placeholder => $pattern];
		}

		$this->placeholders = array_merge($placeholder, $this->placeholders);

		return $this;
    }
	 /**
	 * Registers a new global middleware
	 *
	 * You can pass an associative array as $middleware, and have
	 * multiple middlewares added at once.
	 *
	 * @param string|array|object|callable $middleware
	 *
	 * @return self|array
	 */
	public function middlewares($middleware = null)
	{
		if (empty($middleware))
		{
			return $this->middlewares;
		}
		if (!is_array($middleware))
		{
			$middleware = [$middleware];
		}

		$this->middlewares = array_merge($this->middlewares, $middleware);

		return $this;
    }

    /**
     * Get/Set autorouting
     *
	 * If TRUE, the system will attempt to match the URI against
	 * Controllers by matching each segment against folders/files
	 * in APPPATH/Controllers, when a match wasn't found against
	 * defined routes.
	 *
	 * If FALSE, will stop searching and do NO automatic routing.
	 *
	 * @param bool|null $value
	 * @return bool|self
	 */
	public function autoRoute(?bool $value = null)
	{
        if (is_null($value))
        {
            return $this->config['auto_route'];
        }

		$this->config['auto_route'] = $value;

		return $this;
	}
    /**
	 * Get/Set the name of the default controller
	 *
     * @param string|null $value
	 * @return string|self
	 */
	public function defaultController(?string $value = null)
	{
        if (empty($value))
        {
            return $this->config['default_controller'] ?? 'Home';
        }

        $this->config['default_controller'] = filter_var(ucfirst($value), FILTER_SANITIZE_STRING);

        return $this;
	}
	/**
	 * Get/Set the name of the default method to use within the controller.
	 *
     * @param string|null $value
	 * @return string|self
	 */
	public function defaultMethod(?string $value = null)
	{
        if (empty($value))
        {
            return $this->config['default_method'] ?? 'index';
        }

        $this->config['default_method'] = filter_var(strtolower($value), FILTER_SANITIZE_STRING);

        return $this;
	}
    /**
	 * Get/Set the current HTTP Verb being used.
	 *
     * @param string|null $value
	 * @return string|self
	 */
	public function HTTPVerb(?string $value = null)
	{
        if (empty($value))
        {
            return $this->HTTPVerb;
        }

        $this->HTTPVerb = $value;

        return $this;
	}
	/**
	 * Get / set 404 override
	 *
	 * Sets the class/method that should be called if routing doesn't
	 * find a match. It can be either a closure or the controller/method
	 * name exactly like a route is defined: Users::index
	 *
	 * This setting is passed to the Router class and handled there.
	 *
	 * @param callable|false|null $callable
	 *
	 * @return mixed
	 */
	public function override_404($callable = null)
	{
		if (null === $callable)
		{
			return $this->override404;
		}
		if (false === $callable)
		{
			$callable = null;
		}
		$this->override404 = $callable;

		return $this;
	}
	//--------------------------------------------------------------------

    /**
     * Specifies a route that is only available to GET requests.
     *
     * @param string $path
     * @param string|callable $to
     * @param array|null $options
     * @return self
     */
    public function get(string $path, $to, ?array $options = null) : self
    {
        $this->create('get', $path, $to, $options);

        return $this;
    }
    /**
     * Specifies a route that is only available to POST requests.
     *
     * @param string $path
     * @param string|callable $to
     * @param array|null $options
     * @return self
     */
    public function post(string $path, $to, ?array $options = null) : self
    {
        $this->create('post', $path, $to, $options);

        return $this;
    }
    /**
     * Specifies a route that is only available to PUT requests.
     *
     * @param string $path
     * @param string|callable $to
     * @param array|null $options
     * @return self
     */
    public function put(string $path, $to, ?array $options = null) : self
    {
        $this->create('put', $path, $to, $options);

        return $this;
    }
    /**
     * Specifies a route that is only available to PATCH requests.
     *
     * @param string $path
     * @param string|callable $to
     * @param array $options
     * @return self
     */
    public function patch(string $path, $to, ?array $options = null) : self
    {
        $this->create('patch', $path, $to, $options);

        return $this;
    }
    /**
     * Specifies a route that is only available to HEAD requests.
     *
     * @param string $path
     * @param string|callable $to
     * @param array $options
     * @return self
     */
    public function head(string $path, $to, ?array $options = null) : self
    {
        $this->create('head', $path, $to, $options);

        return $this;
    }
    /**
     * Specifies a route that is only available to OPTIONS requests.
     *
     * @param string $path
     * @param string|callable $to
     * @param array $options
     * @return self
     */
    public function options(string $path, $to, ?array $options = null) : self
    {
        $this->create('options', $path, $to, $options);

        return $this;
    }
    /**
     * Specifies a route that is only available to DELETE requests.
     *
     * @param string $path
     * @param string|callable $to
     * @param array $options
     * @return self
     */
    public function delete(string $path, $to, ?array $options = null) : self
    {
        $this->create('delete', $path, $to, $options);

        return $this;
    }

    /**
     * Adds a single route to the collection.
     *
     * @param string $path
     * @param string|callable $to
     * @param array $options
     * @return self
     */
    public function add(string $path, $to, ?array $options = null) : self
    {
        $this->create('*', $path, $to, $options);

        return $this;
    }
    /**
	 * A shortcut method to add a number of routes at a single time.
	 * It does not allow any options to be set on the route, or to
	 * define the method used.
	 *
	 * @param array $routes
	 * @param array $options
	 *
	 * @return self
	 */
	public function map(array $routes = [], ?array $options = null) : self
	{
		foreach ($routes As $path => $to)
		{
			$this->add($path, $to, $options);
		}

		return $this;
    }
    /**
	 * Specifies a single route to match for multiple HTTP Verbs.
	 *
	 * Example:
	 *  $route->match( ['get', 'post'], 'users/(:num)', 'users/$1);
	 *
	 * @param string|array $verbs
	 * @param string       $path
	 * @param string|array $to
	 * @param array        $options
	 *
	 * @return self
	 */
	public function match($verbs, string $path, $to, ?array $options = null) : self
	{
        if (is_string($verbs))
        {
            $verbs = explode('|', $verbs);
        }
		foreach ($verbs as $verb)
		{
			$verb = strtolower($verb);

			$this->{$verb}($path, $to, $options);
		}

		return $this;
	}
    /**
	 * Adds a temporary redirect from one route to another. Used for
	 * redirecting traffic from old, non-existing routes to the new
	 * moved routes.
	 *
	 * @param string  $path   The pattern to match against
	 * @param string  $to     Either a route name or a URI to redirect to
	 * @param integer $status The HTTP status code that should be returned with this redirect
	 *
	 * @return self
	 */
	public function redirect(string $path, string $to, int $status = 302) : self
	{
		// Use the named route's pattern if this is a named route.
		if (array_key_exists($to, $this->routes['*']))
		{
			$to = $this->routes['*'][$to]['route'];
		}
		else if (array_key_exists($to, $this->routes['get']))
		{
			$to = $this->routes['get'][$to]['route'];
		}

		$this->create('*', $path, $to, ['redirect' => $status]);

		return $this;
	}

    /**
	 * Group a series of routes under a single URL segment. This is handy
	 * for grouping items into an admin area, like:
	 *
	 * Example:
	 *     // Creates route: admin/users
	 *     $route->group('admin', function() {
	 *            $route->resource('users');
	 *     });
	 *
	 * @param string $name The name to group/prefix the routes with.
	 * @param $params
	 *
	 * @return void
	 */
	public function group(string $name, ...$params)
	{
		$oldGroup   = $this->group;
		$oldOptions = $this->options;

		// To register a route, we'll set a flag so that our router
		// so it will see the group name.
		$this->group = ltrim($oldGroup . '/' . $name, '/');

		$callback = array_pop($params);

		if ($params && is_array($params[0]))
		{
			$this->options = array_shift($params);
		}

		if (is_callable($callback))
		{
			$callback($this);
		}

		$this->group          = $oldGroup;
		$this->options = $oldOptions;
	}
    /**
	 * Limits the routes to a specified ENVIRONMENT or they won't run.
	 *
	 * @param string   $env
	 * @param \Closure $callback
	 *
	 * @return self
	 */
	public function environment(string $env, \Closure $callback) : self
	{
		if (config('general.environment') === $env)
		{
			$callback($this);
		}

		return $this;
	}

    //--------------------------------------------------------------------

    //--------------------------------------------------------------------
	// HTTP Verb-based routing
	//--------------------------------------------------------------------
	// Routing works here because, as the routes Config file is read in,
	// the various HTTP verb-based routes will only be added to the in-memory
	// routes if it is a call that should respond to that verb.
	//
	// The options array is typically used to pass in an 'as' or var, but may
	// be expanded in the future. See the docblock for 'add' method above for
	// current list of globally available options.
	//

	/**
	 * Creates a collections of HTTP-verb based routes for a controller.
	 *
	 * Possible Options:
	 *      'controller'    - Customize the name of the controller used in the 'to' route
	 *      'placeholder'   - The regex used by the Router. Defaults to '(:any)'
	 *      'websafe'   -	- '1' if only GET and POST HTTP verbs are supported
	 *
	 * Example:
	 *
	 *      $route->resource('photos');
	 *
	 *      // Generates the following routes:
	 *      HTTP Verb | Path        | Action        | Used for...
	 *      ----------+-------------+---------------+-----------------
	 *      GET         /photos             index           an array of photo objects
	 *      GET         /photos/new         new             an empty photo object, with default properties
	 *      GET         /photos/{id}/edit   edit            a specific photo object, editable properties
	 *      GET         /photos/{id}        show            a specific photo object, all properties
	 *      POST        /photos             create          a new photo object, to add to the resource
	 *      DELETE      /photos/{id}        delete          deletes the specified photo object
	 *      PUT/PATCH   /photos/{id}        update          replacement properties for existing photo
	 *
	 *  If 'websafe' option is present, the following paths are also available:
	 *
	 *      POST		/photos/{id}/delete delete
	 *      POST        /photos/{id}        update
	 *
	 * @param string $name    The name of the resource/controller to route to.
	 * @param array  $options An list of possible ways to customize the routing.
	 *
	 * @return self
	 */
	public function resource(string $name, ?array $options = null) : self
	{
		// In order to allow customization of the route the
		// resources are sent to, we need to have a new name
		// to store the values in.
		$new_name = ucfirst($name);

		// If a new controller is specified, then we replace the
		// $name value with the name of the new controller.
		if (isset($options['controller']))
		{
			$controller = explode('/', filter_var($options['controller'], FILTER_SANITIZE_STRING));
			$last_index = count($controller) - 1;
			$controller[$last_index] = ucfirst($controller[$last_index]);
			$new_name = implode('/', $controller);
		}

		// In order to allow customization of allowed id values
		// we need someplace to store them.
		$id = $this->placeholders[$this->config['default_placeholder']] ?? '(:segment)';

		if (isset($options['placeholder']))
		{
			$id = $options['placeholder'];
		}

		// Make sure we capture back-references
		$id = '(' . trim($id, '()') . ')';

        $methods = isset($options['only'])
            ? (is_string($options['only']) ? explode(',', $options['only']) : $options['only'])
            : ['index', 'show', 'create', 'update', 'delete', 'new', 'edit'];

		if (isset($options['except']))
		{
			$options['except'] = is_array($options['except']) ? $options['except'] : explode(',', $options['except']);
			$c                 = count($methods);
			for ($i = 0; $i < $c; $i ++)
			{
				if (in_array($methods[$i], $options['except']))
				{
					unset($methods[$i]);
				}
			}
		}

		$as = $name;
		if (!empty($this->group))
		{
			$as = str_replace(['/', '\\'], '.', $this->group) . '.' . $as;
		}
		$as = strtolower($as);

		if (in_array('index', $methods))
		{
			$this->get($name, $new_name . '::index', array_merge($options ?? [], ['as' => $as.'.index']));
		}
		if (in_array('new', $methods))
		{
			$this->get($name . '/new', $new_name . '::new', array_merge($options ?? [], ['as' => $as.'.new']));
		}
		if (in_array('edit', $methods))
		{
			$this->get($name . '/' . $id . '/edit', $new_name . '::edit/$1', array_merge($options ?? [], ['as' => $as.'.edit']));
		}
		if (in_array('show', $methods))
		{
			$this->get($name . '/' . $id, $new_name . '::show/$1', array_merge($options ?? [], ['as' => $as.'.show']));
		}
		if (in_array('create', $methods))
		{
			$this->post($name, $new_name . '::create', array_merge($options ?? [], ['as' => $as.'.create']));
		}
		if (in_array('update', $methods))
		{
			$this->put($name . '/' . $id, $new_name . '::update/$1', array_merge($options ?? [], ['as' => $as.'.update_put']));
			$this->patch($name . '/' . $id, $new_name . '::update/$1', array_merge($options ?? [], ['as' => $as.'.update_patch']));
		}
		if (in_array('delete', $methods))
		{
			$this->delete($name . '/' . $id, $new_name . '::delete/$1', array_merge($options ?? [], ['as' => $as.'.delete']));
		}

		// Web Safe? delete needs checking before update because of method name
		if (isset($options['websafe']))
		{
			if (in_array('delete', $methods))
			{
				$this->post($name . '/' . $id . '/delete', $new_name . '::delete/$1',  array_merge($options ?? [], ['as' => $as.'.delete_post']));
			}
			if (in_array('update', $methods))
			{
				$this->post($name . '/' . $id, $new_name . '::update/$1',  array_merge($options ?? [], ['as' => $as.'.update_post']));
			}
		}

		return $this;
	}
	/**
	 * Creates a collections of HTTP-verb based routes for a presenter controller.
	 *
	 * Possible Options:
	 *      'controller'    - Customize the name of the controller used in the 'to' route
	 *      'placeholder'   - The regex used by the Router. Defaults to '(:any)'
	 *
	 * Example:
	 *
	 *      $route->presenter('photos');
	 *
	 *      // Generates the following routes:
	 *      HTTP Verb | Path        | Action        | Used for...
	 *      ----------+-------------+---------------+-----------------
	 *      GET         /photos             index           showing all array of photo objects
	 *      GET         /photos/show/{id}   show            showing a specific photo object, all properties
	 *      GET         /photos/new         new             showing a form for an empty photo object, with default properties
	 *      POST        /photos/create      create          processing the form for a new photo
	 *      GET         /photos/edit/{id}   edit            show an editing form for a specific photo object, editable properties
	 *      POST        /photos/update/{id} update          process the editing form data
	 *      GET         /photos/remove/{id} remove          show a form to confirm deletion of a specific photo object
	 *      POST        /photos/delete/{id} delete          deleting the specified photo object
	 *
	 * @param string $name    The name of the controller to route to.
	 * @param array  $options An list of possible ways to customize the routing.
	 *
	 * @return self
	 */
	public function presenter(string $name, ?array $options = null) : self
	{
		// In order to allow customization of the route the
		// resources are sent to, we need to have a new name
		// to store the values in.
		$newName = ucfirst($name);

		// If a new controller is specified, then we replace the
		// $name value with the name of the new controller.
		if (isset($options['controller']))
		{
			$controller = explode('/', filter_var($options['controller'], FILTER_SANITIZE_STRING));
			$last_index = count($controller) - 1;
			$controller[$last_index] = ucfirst($controller[$last_index]);
			$newName = implode('/', $controller);
		}

		// In order to allow customization of allowed id values
		// we need someplace to store them.
		$id = $this->placeholders[$this->config['default_placeholder']] ?? '(:segment)';

		if (isset($options['placeholder']))
		{
			$id = $options['placeholder'];
		}

		// Make sure we capture back-references
		$id = '(' . trim($id, '()') . ')';

        $methods = isset($options['only'])
            ? (is_string($options['only']) ? explode(',', $options['only']) : $options['only'])
            : ['index', 'show', 'new', 'create', 'edit', 'update', 'remove', 'delete'];

		if (isset($options['except']))
		{
			$options['except'] = is_array($options['except']) ? $options['except'] : explode(',', $options['except']);
			$c                 = count($methods);
			for ($i = 0; $i < $c; $i ++)
			{
				if (in_array($methods[$i], $options['except']))
				{
					unset($methods[$i]);
				}
			}
		}

		$as = $name;
		if (!empty($this->group))
		{
			$as = str_replace(['/', '\\'], '.', $this->group) . '.' . $as;
		}
		$as = strtolower($as);

		if (in_array('index', $methods))
		{
			$this->get($name, $newName . '::index', array_merge($options ?? [], ['as' => $as.'.index']));
		}
		if (in_array('show', $methods))
		{
			$this->get($name . '/show/' . $id, $newName . '::show/$1', array_merge($options ?? [], ['as' => $as.'.show']));
		}
		if (in_array('new', $methods))
		{
			$this->get($name . '/new', $newName . '::new', array_merge($options ?? [], ['as' => $as.'.new']));
		}
		if (in_array('create', $methods))
		{
			$this->post($name . '/create', $newName . '::create', array_merge($options ?? [], ['as' => $as.'.create']));
		}
		if (in_array('edit', $methods))
		{
			$this->get($name . '/edit/' . $id, $newName . '::edit/$1', array_merge($options ?? [], ['as' => $as.'.edit']));
		}
		if (in_array('update', $methods))
		{
			$this->post($name . '/update/' . $id, $newName . '::update/$1', array_merge($options ?? [], ['as' => $as.'.update']));
		}
		if (in_array('remove', $methods))
		{
			$this->get($name . '/remove/' . $id, $newName . '::remove/$1', array_merge($options ?? [], ['as' => $as.'.remove']));
		}
		if (in_array('delete', $methods))
		{
			$this->post($name . '/delete/' . $id, $newName . '::delete/$1', array_merge($options ?? [], ['as' => $as.'.delete']));
		}
		if (in_array('show', $methods))
		{
			$this->get($name . '/' . $id, $newName . '::show/$1', array_merge($options ?? [], ['as' => $as.'.show']));
		}
		if (in_array('create', $methods))
		{
			$this->post($name, $newName . '::create', array_merge($options ?? [], ['as' => $as.'.create']));
		}

		return $this;
	}

    //--------------------------------------------------------------------

    /**
	 * Determines if the route is a redirecting route.
	 *
	 * @param string $from
	 * @return boolean
	 */
	public function isRedirect(string $from) : bool
	{
		foreach ($this->routes['*'] As $name => $route)
		{
			// Named route?
			if ($name === $from OR key($route['route']) === $from)
			{
				return isset($route['redirect']) AND is_numeric($route['redirect']);
			}
		}

		return false;
    }
    /**
	 * Checks a route (using the "from") to see if it's filtered or not.
	 *
	 * @param string $search
	 * @return boolean
	 */
	public function isFiltered(string $search) : bool
	{
		return isset($this->routesOptions[$search]['middlewares']);
    }
    /**
	 * Returns the filter that should be applied for a single route, along
	 * with any parameters it might have. Parameters are found by splitting
	 * the parameter name on a colon to separate the filter name from the parameter list,
	 * and the splitting the result on commas. So:
	 *
	 *    'role:admin,manager'
	 *
	 * has a filter of "role", with parameters of ['admin', 'manager'].
	 *
	 * @param string $search
	 * @return string|string[]
	 */
	public function getFilterForRoute(string $search)
	{
		if (! $this->isFiltered($search))
		{
			return '';
		}

		return $this->routesOptions[$search]['middlewares'];
	}

    //--------------------------------------------------------------------

	/**
	 * Grabs the HTTP status code from a redirecting Route.
	 *
	 * @param string $from
	 * @return integer
	 */
	public function redirectCode(string $from) : int
	{
		foreach ($this->routes['*'] As $name => $route)
		{
			// Named route?
			if ($name === $from OR key($route['route']) === $from)
			{
				return $route['redirect'] ?? 0;
			}
		}

		return 0;
    }
    /**
	 * Returns one or all routes options
	 *
	 * @param string $from
	 * @return array
	 */
	public function routesOptions(string $from = null) : array
	{
        if (empty($from))
        {
            return $this->routesOptions;
        }

        return $this->routesOptions[$from] ?? [];
    }
    /**
	 * Returns the raw array of available routes.
	 *
	 * @param mixed $verb
	 * @param bool $with_name
	 * @return array
	 */
	public function getRoutes($verb = null, bool $with_name = false) : array
	{
		if (empty($verb))
		{
			$verb = $this->HTTPVerb();
		}
		$verb = strtolower($verb);
		$routes = [];

		if (isset($this->routes[$verb]))
		{
			// Keep current verb's routes at the beginning so they're matched
			// before any of the generic, "add" routes.
			if (isset($this->routes['*']))
			{
				$extraRules = array_diff_key($this->routes['*'], $this->routes[$verb]);
				$collection = array_merge($this->routes[$verb], $extraRules);
			}
			foreach ($collection as $name => $r)
			{
				$key          = key($r['route']);
				if ($with_name === false)
				{
					$routes[$key] = $r['route'][$key];
				}
				else
				{
					$routes[$key] = [
						'name' => $name,
						'handler' => $r['route'][$key]
					];
				}
			}
		}

		return $routes;
	}
    /**
	 * Reset the routes, so that a FeatureTestCase can provide the
	 * explicit ones needed for it.
	 */
	public function resetRoutes()
	{
		$this->routes = ['*' => []];
		foreach ($this->defaultHTTPMethods as $verb)
		{
			$this->routes[$verb] = [];
		}
	}


    //--------------------------------------------------------------------

    /**
	 * Attempts to look up a route based on it's destination.
	 *
	 * If a route exists:
	 *
	 *      'path/(:any)/(:any)' => 'Controller::method/$1/$2'
	 *
	 * This method allows you to know the Controller and method
	 * and get the route that leads to it.
	 *
	 *      // Equals 'path/$param1/$param2'
	 *      reverseRoute('Controller::method', $param1, $param2);
	 *
	 * @param string $search
	 * @param array  ...$params
	 *
	 * @return string|false
	 */
	public function reverseRoute(string $search, ...$params)
	{
		// Named routes get higher priority.
		foreach ($this->routes as $verb => $collection)
		{
			if (array_key_exists($search, $collection))
			{
				return $this->fillRouteParams(key($collection[$search]['route']), $params);
			}
		}

		// If it's not a named route, then loop over
		// all routes to find a match.
		foreach ($this->routes as $verb => $collection)
		{
			foreach ($collection as $route)
			{
				$from = key($route['route']);
				$to   = $route['route'][$from];

				// ignore closures
				if (! is_string($to))
				{
					continue;
				}

				// Lose any namespace slash at beginning of strings
				// to ensure more consistent match.
				$to     = ltrim($to, '\\');
				$search = ltrim($search, '\\');

				// If there's any chance of a match, then it will
				// be with $search at the beginning of the $to string.
				if (strpos($to, $search) !== 0)
				{
					continue;
				}

				// Ensure that the number of $params given here
				// matches the number of back-references in the route
				if (substr_count($to, '$') !== count($params))
				{
					continue;
				}

				return $this->fillRouteParams($from, $params);
			}
		}

		// If we're still here, then we did not find a match.
		return false;
	}
	/**
	 * Does the heavy lifting of creating an actual route. You must specify
	 * the request method(s) that this route will work for. They can be separated
	 * by a pipe character "|" if there is more than one.
	 *
	 * @param string       $verb
	 * @param string       $from
	 * @param string|array $to
	 * @param array        $options
	 */
	protected function create(string $verb, string $from, $to, ?array $options = null)
	{
		$prefix    = (is_null($this->group) ? '' : $this->group) . '/';

		$from = filter_var($prefix . ltrim($from, '/'), FILTER_SANITIZE_STRING);

		// While we want to add a route within a group of '/',
		// it doesn't work with matching, so remove them...
		if ($from !== '/')
		{
			$from = trim($from, '/');
		}

        $options = array_merge_recursive((array) $this->options, (array) $options);

		// Are we offsetting the binds?
		// If so, take care of them here in one
		// fell swoop.
		if (isset($options['offset']) AND is_string($to))
		{
			// Get a constant string to work with.
			$to = preg_replace('/(\$\d+)/', '$X', $to);

			for ($i = (int) $options['offset'] + 1; $i < (int) $options['offset'] + 7; $i ++)
			{
				$to = preg_replace_callback(
						'/\$X/', function ($m) use ($i) {
							return '$' . $i;
						}, $to, 1
				);
			}
		}

		// Replace our regex pattern placeholders with the actual thing
		// so that the Router doesn't need to know about any of this.
		foreach ($this->placeholders as $tag => $pattern)
		{
			$from = str_ireplace(':' . $tag, $pattern, $from);
        }

		if (!empty($options['as']))
		{
			$name = $options['as'];
		}
		else
		{
			$name = $from;
			if (!empty($this->group))
			{
				$name = str_replace(['/', '\\'], '.', $this->group) . '.' . $name;
			}
			$name = strtolower($name);
		}

		if (isset($this->routes[$verb][$name]))
		{
			return;
		}

		if (is_string($to) AND $to[0] !== '/')
		{
			$to = $prefix.$to;
		}

		$this->routes[$verb][$name] = [
			'route' => [$from => $to],
		];

		$this->routesOptions[$from] = $options;

		// Is this a redirect?
		if (isset($options['redirect']) AND is_numeric($options['redirect']))
		{
			$this->routes['*'][$name]['redirect'] = $options['redirect'];
		}
	}
    /**
	 * Given a
	 *
	 * @param string     $from
	 * @param array|null $params
	 *
	 * @return string
	 */
	protected function fillRouteParams(string $from, ?array $params = null) : string
	{
		// Find all of our back-references in the original route
		preg_match_all('/\(([^)]+)\)/', $from, $matches);

		if (empty($matches[0]))
		{
			return '/' . ltrim($from, '/');
		}

		// Build our resulting string, inserting the $params in
		// the appropriate places.
		foreach ($matches[0] as $index => $pattern)
		{
			// Ensure that the param we're inserting matches
			// the expected param type.
			$pos = strpos($from, $pattern);

			if (isset($params[$index]))
			{
				if (preg_match("|{$pattern}|", $params[$index]))
				{
					$from = substr_replace($from, $params[$index], $pos, strlen($pattern));
				}
				else
				{
					throw new RouterException("Invalid parameter type");
				}
			}
			else
			{
				$from = substr_replace($from, '', $pos, strlen($pattern));
			}
		}

		return '/' . ltrim($from, '/');
	}
}
