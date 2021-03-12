<?php
/**
 *  dFramework
 *
 *  The simplest PHP framework for beginners
 *  Copyright (c) 2019 - 2021, Dimtrov Lab's
 *  This content is released under the Mozilla Public License 2 (MPL-2.0)
 *
 *  @package	dFramework
 *  @author	    Dimitri Sitchet Tomkeu <dev.dst@gmail.com>
 *  @copyright	Copyright (c) 2019 - 2021, Dimtrov Lab's. (https://dimtrov.hebfree.org)
 *  @copyright	Copyright (c) 2019 - 2021, Dimitri Sitchet Tomkeu. (https://www.facebook.com/dimtrovich)
 *  @license	https://opensource.org/licenses/MPL-2.0 MPL-2.0 License
 *  @link	    https://dimtrov.hebfree.org/works/dframework
 *  @version    3.2.3
 */
 
namespace dFramework\core\output;

use dFramework\core\Config;
use dFramework\core\debug\Toolbar;
use dFramework\core\exception\LoadException;
use dFramework\core\http\Response;
use dFramework\core\loader\Load;
use dFramework\core\loader\Service;
use dframework\core\router\Dispatcher;
use Exception;
use Psr\Http\Message\ResponseInterface;

/**
 * View
 *
 * Responsible for sending final output to the browser.
 *
 * @package		dFramework
 * @subpackage	Core
 * @category    Output
 * @author		Dimitri Sitchet Tomkeu <dev.dst@gmail.com>
 * @link		https://dimtrov.hebfree.org/docs/dframework/api/
 * @since       1.0
 * @file		/system/core/output/View.php
 */
class View
{
	/**
	 * Data that is made available to the Views.
	 *
	 * @var array
	 */
	protected $data = [];
	/**
	 * Cache stats about our performance here,

     * @var array
	 */
	protected $performanceData = [];
    /**
	 * Should we store performance info?
	 *
	 * @var boolean
	 */
	protected $debug = false;
	/**
	 * The render variables
	 *
	 * @var array
	 */
	protected $renderVars = [];
    /**
	 * Number of loaded views
	 *
	 * @var integer
	 */
	protected $viewsCount = 0;

	/**
	 * The name of the layout being used, if any.
	 * Set by the `extend` method used within views.
	 *
	 * @var string
	 */
	protected $layout;

	/**
	 * Holds the sections and their data.
	 *
	 * @var array
	 */
	protected $sections = [];

	/**
	 * The name of the current section being rendered,
	 * if any.
	 *
	 * @var string
	 */
	protected $currentSection;

    protected $options = [];

    protected $config = [
        'view_path' => VIEW_DIR
    ];

    /**
     * Controleur relatif a charger
     *
     * @var string
     */
    protected $controller = '';
    /**
     * Vue a charger
     *
     * @var string
     */
    protected $view = '';
    /**
     * code html du rendu final
     *
     * @var string
     */
    protected $output = '';

    /**
     * @var array
     */
    protected $_styles = [];
    /**
     * @var array
     */
    protected $_lib_styles = [];
    /**
     * @var array
     */
    protected $_scripts = [];
    /**
     * @var array
     */
    protected $_lib_scripts = [];

    protected $_page_vars = [];
    
    /**
     * @var Response
     */
    private $response;

    
    /**
     * Constructeur
     *
     * @param array|null $data
     * @param string|null $controller
     * @param array|null $options
     * @param Response|null $response
     */
    public function __construct(?array $data = [], ?string $controller= '', ?array $options = [], ?array $config = [], $response = null)
    {
        $this->data = (array) $data;
        $this->options = (array) $options;
        $this->controller = strtolower(trim($controller, DS));
       
        $this->response = ($response instanceof Response OR $response instanceof ResponseInterface) ? $response : Service::response();
        $this->config = array_merge($this->config, (array) $config);
        Load::helper('assets');
		
        $class = Dispatcher::getClass();
        $method = Dispatcher::getMethod();
		
		$this->title(ucfirst($method) . ' - ' . ucfirst($class));

        $this->debug = true;
    }
    /**
     * set displaying view 
     *
     * @param string $view
     * @return self
     */
    public function display(string $view) : self 
    {
        $this->view = $view;
        
        return $this;
    }

    public function __get(string $name)
    {
        $name = strtolower($name);

        if ($name == 'form')
        {
            if (empty($this->form)) 
            {
                $this->form = Load::library('Form');
            }
            return $this->form;
        }
        throw new Exception($name .' is not a member property of '.__CLASS__);
    }
    public function __toString()
    {
        return $this->get();
    }

    /**
     * Recupere et retourne le code html de la vue creee
     *
     * @param bool|string $compress
     * @return string
     */
    public function get($compress = 'auto') : string 
    {    
        $this->create();
        return $this->compressView($this->output, $compress);
    }
    /**
     * Affiche la vue generee au navigateur
     * 
     * @return void
     */
    public function render()
    {
        echo $this->get(Config::get('general.compress_output'));
    }
    
	/**
	 * Used within layout views to include additional views.
	 *
	 * @param string     $view
	 * @param array|null $options
	 * @return string
	 */
	public function insert(string $view, array $options = null): string
	{
        $view = preg_replace('#\.php$#i', '', $view).'.php';
        $view = str_replace(' ', '', $view);
        if ($view[0] !== '/' AND !file_exists(rtrim(VIEW_DIR.$this->controller.DS, DS).DS.$view)) 
        {
            if (file_exists(VIEW_DIR.'partials'.DS.$view)) 
            {
                $view = '/partials/'.$view;
            }
            if (file_exists(VIEW_DIR.trim(dirname($this->view), '/\\').DS.$view)) {
                $view = '/'.trim(dirname($this->view), '/\\').'/'.$view;
            }
        }

        return $this->compressView(
            $this->makeView($view, $options), 
            Config::get('general.compress_output')
        );
	}
    /**
	 * Specifies that the current view should extend an existing layout.
	 *
	 * @param string $layout
	 * @return self
	 */
	public function layout(?string $layout) : self
	{
        $this->layout = $layout;

        return $this;
    }

    /**
	 * Starts holds content for a section within the layout.
	 *
	 * @param string $name
	 */
	public function start(string $name)
	{
		$this->currentSection = $name;

		ob_start();
	}
	/**
	 *
	 *
	 * @throws \RuntimeException
	 */
	public function stop()
	{
		$contents = ob_get_clean();

		if (empty($this->currentSection))
		{
			throw new \RuntimeException('View themes, no current section.');
		}

		// Ensure an array exists so we can store multiple entries for this.
		if (! array_key_exists($this->currentSection, $this->sections))
		{
			$this->sections[$this->currentSection] = [];
		}
		$this->sections[$this->currentSection][] = $contents;

		$this->currentSection = null;
    }
    
    /**
	 * Renders a section's contents.
	 *
	 * @param string $sectionName
	 */
	public function show(string $sectionName)
	{
		if (! isset($this->sections[$sectionName]))
		{
			echo '';

			return;
		}

        $start = $end = '' ;
        if ($sectionName === 'css')
        {
            $start = "<style type=\"text/css\">\n";
            $end   = "</style>\n";
        }
        if ($sectionName === 'js')
        {
            $start = "<script type=\"text/javascript\">\n";
            $end = "</script>\n";
        }

        echo $start;
		foreach ($this->sections[$sectionName] As $key => $contents)
		{
			echo $contents;
			unset($this->sections[$sectionName][$key]);
        }
        echo $end;

        return;
    }
    /**
     * Affichage rapide du contenu principal
     *
     * @return void
     */
    public function renderView()
    {
        $this->show('content');
    }

	/**
	 * Get or Set page title 
	 *
	 * @param string|null $title 
	 * @return string|null 
	 */
	public function title(?string $title = null)
	{
		if (empty($title))
		{
			return $this->_page_vars['title'] ?? '';
		}
		
		$this->_page_vars['title'] = esc($title);
	}
	
	/**
	 * Get or Set page meta tags 
	 *
	 * @param string $key 
	 * @param string|null $value 
	 * @return string|null 
	 */
	public function meta(string $key, ?string $value = null)
	{
		if (empty($value)) 
		{
			return $this->_page_vars['meta'][$key] ?? '';
		}
		
		$this->_page_vars['meta'][$key] = esc($value);
	}
	
    	/**
	 * Extract first bit of a long string and add ellipsis
	 *
	 * @param  string  $string
	 * @param  integer $length
	 * @return string
	 */
	public function excerpt(string $string, int $length = 20): string
	{
		return (strlen($string) > $length) ? substr($string, 0, $length - 3) . '...' : $string;
	}

    /**
	 * Sets several pieces of view data at once.
	 *
	 * @param array  $data
	 * @return self
	 */
	public function addData(array $data = []): self
	{
		$this->data = array_merge($this->data, $data);

		return $this;
	}
	/**
	 * Sets a single piece of view data.
	 *
	 * @param string $name
	 * @param mixed  $value
	 * @return View
	 */
	public function setVar(string $name, $value = null): self
	{
		$this->data[$name] = $value;

		return $this;
	}
    /**
	 * Removes all of the view data from the system.
	 *
	 * @return View
	 */
	public function resetData(): self
	{
		$this->data = [];

		return $this;
	}
	/**
	 * Returns the current data that will be displayed in the view.
	 *
	 * @return array
	 */
	public function getData(): array
	{
		return $this->data;
	}

	/**
	 * Returns the performance data that might have been collected
	 * during the execution. Used primarily in the Debug Toolbar.
	 *
	 * @return array
	 */
	public function getPerformanceData(): array
	{
		return $this->performanceData;
	}

    /**
     * Ajoute un fichier css de librairie a la vue
     * 
     * @param string ...$src
     * @return self
     */
    public function addLibCss(string ...$src) : self
    {
        foreach ($src As $var)
        {
            if (!isset($this->_lib_styles) OR (isset($this->_lib_styles) AND !in_array($var, $this->_lib_styles)))
            {
                $this->_lib_styles[] = $var;
            }
        }

        return $this;
    }
    /**
     * Ajoute un fichier css a la vue
     * 
     * @param string ...$src
     * @return self
     */
    public function addCss(string ...$src) : self
    {
        foreach ($src As $var)
        {
            if (!isset($this->_styles) OR (isset($this->_styles) AND !in_array($var, $this->_styles)))
            {
                $this->_styles[] = $var;
            }
        }

        return $this;
    }
    /**
     * Compile les fichiers de style de l'instance et genere les link:href vers ceux-ci
     *
     * @param string $groups
     * @return void
     */
    public function stylesBundle(string ...$groups)
    {
        $groups = (array) (empty($groups) ? $this->layout : $groups);
        $lib_styles = $styles = [];

        foreach ($groups As $group)
        {
            $lib_styles = array_merge(
                $lib_styles,
                (array) Config::get('layout.'.$group.'.lib_styles'), 
                $this->_lib_styles ?? []
            );
            $styles = array_merge(
                $styles,
                (array) Config::get('layout.'.$group.'.styles'), 
                $this->_styles ?? []
            );
        }

        if (!empty($lib_styles))
        {
            lib_styles(array_unique($lib_styles));
        }
        if (!empty($styles))
        {
            styles(array_unique($styles));
        }

        $this->show('css');
    }

    /**
     * Ajoute un fichier js de librairie a la vue
     * 
     * @param string ...$src
     * @return self
     */
    public function addLibJs(string ...$src): self
    {
        foreach ($src As $var)
        {
            if (!isset($this->_lib_scripts) OR (isset($this->_lib_scripts) AND !in_array($var, $this->_lib_scripts)))
            {
                $this->_lib_scripts[] = $var;
            }
        }

        return $this;
    }
    /**
     * Ajoute un fichier js a la vue
     * 
     * @param string ...$src
     * @return self
     */
    public function addJs(string ...$src) : self
    {
        foreach ($src As $var)
        {
            if (!isset($this->_scripts) OR (isset($this->_scripts) AND !in_array($var, $this->_scripts)))
            {
                $this->_scripts[] = $var;
            }
        }

        return $this;
    }
    /**
     * Compile les fichiers de script de l'instance et genere les link:href vers ceux-ci
     *
     * @param string ...$groups
     * @return void
     */
    public function scriptsBundle(string ...$groups) : void
    {
        $groups = (array) (empty($groups) ? $this->layout : $groups);
        $lib_scripts = $scripts = [];

        foreach ($groups As $group)
        {
            $lib_scripts = array_merge(
                $lib_scripts,
                (array) Config::get('layout.'.$group.'.lib_scripts'), 
                $this->_lib_scripts ?? []
            );
            $scripts = array_merge(
                $scripts,
                (array) Config::get('layout.'.$group.'.scripts'), 
                $this->_scripts ?? []
            );
        }

        if (!empty($lib_scripts))
        {
            lib_scripts(array_unique($lib_scripts));
        }
        if (!empty($scripts))
        {
            scripts(array_unique($scripts));
        }

        $this->show('js');
    }


    //--------------------------------------------------------------------
    
    /**
	 * Logs performance data for rendering a view.
	 *
	 * @param float  $start
	 * @param float  $end
	 * @param string $view
	 */
	protected function logPerformance(float $start, float $end, string $view)
	{
		$this->performanceData[] = [
			'start' => $start,
			'end'   => $end,
			'view'  => $view,
		];
	}
    
    /**
     * Permet de lancer la creation de la vue 
     *
     * @return void
     */
    private function create()
    {
        $this->output = $this->makeView($this->view, $this->options); 
    } 
    /**
     * Cree une vue demandee et retourne son code html
     *
     * @param string $view
     * @param array $options
     * @param string $viewPath
     * @return string
     */
    protected function makeView(string $view, array $options = null) : string
    {
        $viewPath = $this->config['view_path'];

        $view = preg_replace('#\.(php|tpl|html?)$#i', '', $view);
        $this->renderVars['start'] = microtime(true);
        $this->renderVars['view']    = $view;
		$this->renderVars['options'] = $options;

        $this->renderVars['file'] = $viewPath.str_replace(' ', '', trim($view, '/'));            
        if ($viewPath === VIEW_DIR AND stripos($view, '/') !== 0)
        {
            $this->renderVars['file'] = rtrim($viewPath.$this->controller.DS, DS).DS.str_replace(' ', '', $view);
        }
        $this->renderVars['file'] = str_replace('/', DS, $this->renderVars['file']);

        $ext = 'php';
        foreach (['php', 'tpl', 'html'] As $value) 
        {
            if (view_exist($this->renderVars['file'], $value)) 
            {
                $ext = $value;
                break;
            }
        }
        if ('php' !== $ext)
        {
            return $this->smarty(str_replace($viewPath, '', $this->renderVars['file']), $ext);
        }
        
        $this->renderVars['file'] .= '.php';
        
        // Was it cached?
		if (isset($this->renderVars['options']['cache_name']))
		{
			if ($output = Service::cache()->read($this->renderVars['options']['cache_name']))
			{
				$this->logPerformance($this->renderVars['start'], microtime(true), $this->renderVars['view']);
				return $output;
			}
		}

        
        if (! is_file($this->renderVars['file']))
        {
            throw new LoadException('Le fichier "'.$this->renderVars['file'].'" n\'exite pas', 404);
        }    
                
        // Make our view data available to the view.
        extract($this->data);

        ob_start();
        include_once($this->renderVars['file']); // PHP will be processed
        $output = ob_get_contents();
        @ob_end_clean();
        
        if (!empty($this->renderVars['options']['layout'])) 
        {
            $this->layout = $this->renderVars['options']['layout'];
        }
        if (! is_null($this->layout) AND empty($this->currentSection))
		{
			$layoutView   = $this->layout;
			$this->layout = null;
			$output       = $this->makeView(
                trim($layoutView, '/'), 
                $options, 
                LAYOUT_DIR
            );
		}

		$this->logPerformance($this->renderVars['start'], microtime(true), $this->renderVars['view']);

        if (isset($this->renderVars['options']['compress_output']) AND is_bool($this->renderVars['options']['compress_output']))
        {
            $output = $this->compressView($output, $this->renderVars['options']['compress_output']);
        }

        // Render debugbar
        if ($this->debug AND (!isset($options['debug']) || $options['debug'] === true))
		{
			/* if (in_array(\dFramework\core\debug\toolbar\collectors\Views::class, Toolbar::COLLECTORS))
			{
				// Clean up our path names to make them a little cleaner
				foreach (['APP_DIR', 'SYST_DIR', 'ROOTPATH'] As $path)
				{
					if (strpos($this->renderVars['file'], constant($path)) === 0)
					{
						$this->renderVars['file'] = str_replace(constant($path), $path . '/', $this->renderVars['file']);
						break;
					}
				}
				$this->renderVars['file'] = ++$this->viewsCount . ' ' . $this->renderVars['file'];
				$output                   = '<!-- DEBUG-VIEW START ' . $this->renderVars['file'] . ' -->' . PHP_EOL
					. $output . PHP_EOL
					. '<!-- DEBUG-VIEW ENDED ' . $this->renderVars['file'] . ' -->' . PHP_EOL;
			} */
		}
        
        // Should we cache?
		if (!empty($this->renderVars['options']['cache_name']) OR !empty($this->renderVars['options']['cache_time']))
		{
            Service::cache()->write(
                $this->renderVars['options']['cache_name'], 
                $output, 
                (int) $this->renderVars['options']['cache_time']
            );
		}

        return $output;
    }
    /**
     * Compresse le code html d'une vue
     *
     * @param string $output
     * @param bool|string $compress
     * @return string
     */
    private function compressView(string $output, $compress = 'auto') : string 
    {
        if (!in_array($compress, [true, false, 'true', 'false'], true)) {
            $compress = Config::get('general.environment') !== 'dev';
        }
        return (true === $compress) ? trim(preg_replace('/\s+/', ' ', $output)) : $output;
    }
     /**
     * Creer et affiche une vue smarty
     *
     * @param string $file
     * @return string
     */
    private function smarty(string $file, string $ext = '.tpl') : string 
    {
        $smarty = new Smarty();
        $smarty->assign($this->getData());
        $smarty->display($file.'.'.str_replace('.', '', $ext));
           
        return '';
    }
}
