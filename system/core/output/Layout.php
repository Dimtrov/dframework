<?php
/**
 *  dFramework
 *
 *  The simplest PHP framework for beginners
 *  Copyright (c) 2019, Dimtrov Sarl
 *  This content is released under the Mozilla Public License 2 (MPL-2.0)
 *
 *  @package	dFramework
 *  @author	    Dimitric Sitchet Tomkeu <dev.dimitrisitchet@gmail.com>
 *  @copyright	Copyright (c) 2019, Dimtrov Sarl. (https://dimtrov.hebfree.org)
 *  @copyright	Copyright (c) 2019, Dimitric Sitchet Tomkeu. (https://www.facebook.com/dimtrovich)
 *  @license	https://opensource.org/licenses/MPL-2.0 MPL-2.0 License
 *  @link	    https://dimtrov.hebfree.org/works/dframework
 *  @version    3.0
 *
 */

/**
 * Layout
 *
 * Responsible for sending final output to the browser.
 *
 * @class       Layout
 * @package		dFramework
 * @subpackage	Core
 * @category    Output
 * @author		Dimitri Sitchet Tomkeu <dev.dimitrisitchet@gmail.com>
 * @link		https://dimtrov.hebfree.org/docs/dframework/api/
 * @since       1.0
 * @file		/system/core/output/Layout.php
 */

namespace dFramework\core\output;

use dFramework\core\Config;
use dFramework\core\route\Dispatcher;
use dFramework\core\utilities\Tableau;
use dFramework\core\exception\LoadException;
use phpDocumentor\Reflection\Types\Void_;

/**
 * Class Layout
 */
class Layout
{
    /**
     * @var string
     */
    private static $_layout = 'default';

    private static $_output = '';

    private static $_vars = [];



    private $class = '';


    /**
     * Layout constructor.
     * @param string $layout
     * @param array|null $vars
     */
    public function __construct(string $layout = 'default', ?array $vars = [])
    {
        self::$_layout = $layout;
        $this->putVar($vars);

        $controller = Dispatcher::getController();
        $class = Dispatcher::getClass();
        $method = Dispatcher::getMethod();

        $class = (!empty($class)) ? $class : Config::get('route.default_controller');
        $method = (!empty($method)) ? $method : 'Index';
        $controller = (empty($controller)) ? '' : $controller . '/';

        $this->class = $class;

        self::$_vars['df_pageTitle'] = ucfirst($method) . ' - ' . ucfirst($class);
    }

    /**
     * Ajoute une vue au template
     *
     * @param $view
     * @param array $vars
     * @return Layout
     */
    public function add($view, array $vars = []) : self
    {
        $view = new View($view, array_merge(self::$_vars, $vars), $this->class);
        self::$_output .=  $view->get(false);
        return $this;
    }

    /**
     * Lance la page finale
     */
    public function launch()
    {
        $view = new View('/reserved/layouts' . DS . self::$_layout, self::$_vars);
        $view->render();
    }

    /**
     * Injecte du contenu au code final
     *
     * @param string $content
     * @return Layout
     */
    public function inject(string $content) : self
    {
        self::$_output .= $content;
        return $this;
    }

    /**
     * Ajoutr une variable globale accessible au layout et aux vues
     *
     * @param array $vars
     * @return Layout
     */
    public function putVar(array $vars) : self
    {
        self::$_vars = array_merge(self::$_vars, $vars);
        return $this;
    }
    /**
     * Recupere une variable globale depuis une vue
     *
     * @param null|string $var
     * @return array
     */
    public static function vars(?string $var = null)
    {
        $vars = self::$_vars;
        $vars = Tableau::remove($vars, 'df_css');
        $vars = Tableau::remove($vars, 'df_js');
        $vars = Tableau::remove($vars, 'df_lib_css');
        $vars = Tableau::remove($vars, 'df_lib_js');

        if(null !== $var)
        {
            return $vars[$var] ?? [];
        }
        return $vars;
    }

    /**
     * Ajoute un fichier css au layout
     * 
     * @param string ...$src
     * @return Layout
     */
    public function putCss(string ...$src) : self
    {
        foreach ($src As $var)
        {
            if(!isset(self::$_vars['df_css']) OR (isset(self::$_vars['df_css']) AND !in_array($var, self::$_vars['df_css'])))
            {
                self::$_vars['df_css'][] = $var;
            }
        }
        return $this;
    }
    /**
     * Ajoute un fichier css au layout par appel static depuis une vue
     * 
     * @param string|string[] $src
     * @return void
     */
    public static function addCss($src) : void
    {
        $src = (array) $src;
        $instance = self::instance();
        foreach ($src As $value)
        {
            $instance->putCss($value);
        }
    }

    /**
     * Ajoute un fichier css de librairie au layout
     * 
     * @param string ...$src
     * @return Layout
     */
    public function putLibCss(string ...$src) : self
    {
        foreach ($src As $var)
        {
            if(!isset(self::$_vars['df_lib_css']) OR (isset(self::$_vars['df_lib_css']) AND !in_array($var, self::$_vars['df_lib_css'])))
            {
                self::$_vars['df_lib_css'][] = $var;
            }
        }
        return $this;
    }
    /**
     * Ajoute un fichier css de librairie au layout par appel satic depuis une vue
     * 
     * @param string|string[] $src
     * @return void
     */
    public static function addLibCss($src) : void
    {
        $src = (array) $src;
        $instance = self::instance();
        foreach ($src As $value)
        {
            $instance->putLibCss($value);
        }
    }

    /**
     * Ajoute un fichier js au layout
     * 
     * @param string ...$src
     * @return Layout
     */
    public function putJs(string ...$src) : self
    {
        foreach ($src As $var)
        {
            if(!isset(self::$_vars['df_js']) OR (isset(self::$_vars['df_js']) AND !in_array($var, self::$_vars['df_js'])))
            {
                self::$_vars['df_js'][] = $var;
            }
        }
        return $this;
    }
    /**
     * Ajoute un fichier js au layout par appel static depuis une vue
     * 
     * @param $src
     * @return void
     */
    public static function addJs($src) : void
    {
        $src = (array) $src;
        $instance = self::instance();
        foreach ($src As $value)
        {
            $instance->putJs($value);
        }
    }

    /**
     * Ajoute un fichier js de librairie au layout
     * 
     * @param string ...$src
     * @return Layout
     */
    public function putLibJs(string ...$src): self
    {
        foreach ($src As $var)
        {
            if(!isset(self::$_vars['df_lib_js']) OR (isset(self::$_vars['df_lib_js']) AND !in_array($var, self::$_vars['df_lib_js'])))
            {
                self::$_vars['df_lib_js'][] = $var;
            }
        }
        return $this;
    }
    /**
     * Ajoute un fichier js de librairie au layout par appel satic depuis une vue
     * 
     * @param $src
     * @return void
     */
    public static function addLibJs($src) : void
    {
        $src = (array) $src;
        $instance = self::instance();
        foreach ($src As $value)
        {
            $instance->putLibJs($value);
        }
    }

    /**
     * Definition du titre du document
     * 
     * @param string $title
     * @return Layout
     */
    public function setPageTitle(string $title) : self
    {
        if (!empty($title) AND is_string($title))
        {
            self::$_vars['df_pageTitle'] = trim(htmlspecialchars($title));
        }
        return $this;
    }

    /**
     * Definition du titre du document par appel static depuis une vue
     * 
     * @param string $title
     * @return void
     */
    public static function setTitle(string $title) : void
    {
        self::instance()->setPageTitle($title);
    }

    /**
     * Definition des metas donnees du document
     * 
     * @param string|array $meta
     * @param string|null $value
     * @return Layout
     */
    public function setPageMeta($meta, ?string $value = null) : self
    {
        if(is_array($meta))
        {
            foreach ($meta as $k => $v) 
            {
                $this->setPageMeta($k, $v);
            }
        }
        if(is_string($meta) AND !empty($value))
        {
            self::$_vars['df_pageMeta'][$meta] = $value; 
        }
        return $this;
    }
    /**
     * Definition des metas donnees du document par appel static depuis une vue
     * 
     * @param string|array $meta
     * @param string|null $value
     * @return void
     */
    public static function setMeta($meta, ?string $value = null) : void
    {
        self::instance()->setPageMeta($meta, $value);
    }
    
    /**
     * @param string $layout
     * @return Layout
     */
    public function setLayout(string $layout) : self
    {
        if(!empty($layout) AND is_string($layout))
        {
			if(!file_exists(LAYOUT_DIR.$layout.'.php'))
			{
				throw new LoadException('
					Impossible de charger le template <b>'.$layout.'</b>.
					<br>
					Le fichier &laquo; '.LAYOUT_DIR.$layout.'.php &raquo; n\'existe pas
				');
			}
            self::$_layout = trim(htmlspecialchars($layout));
        }
        return $this;
    }



    private static $_instance = null;
    /**
     * @return Layout
     */
    private static function instance() : self
    {
        if(null === self::$_instance)
        {
            self::$_instance = new Layout();
        }
        return self::$_instance;
    }

    /**
     *  Rend le code de la vue dans le layout
     */
    public static function renderView() : void
    {
        echo self::$_output;
    }
    public static function output() : void
    {
        self::renderView();
    }


    /**
     * @var string[]
     */
    private static $_blocks = [];

    const B_APPEND = 1;
    const B_PREPEND = 2;

    /**
     * Demarre une nouvelle section
     * 
     * @param string $name Le nom du block a demarrer
     * @param int $concat
     */
    public static function block(string $name, int $concat = self::B_APPEND) : void
    {
        $name = strtolower($name);
        ob_start(function ($buffer) use ($name, $concat) {
            if(isset(self::$_blocks[$name]))
            {
                if($concat == self::B_PREPEND)
                {
                    self::$_blocks[$name] = $buffer . self::$_blocks[$name] ;
                }
                else
                {
                    self::$_blocks[$name] .= $buffer;
                }
            }
            else
            {
                self::$_blocks[$name] = $buffer;
            }
        });
    }
    public static function startSection(string $name, int $concat = self::B_APPEND) : void
    {
        self::block($name, $concat);
    }
    /**
     * Stop la capture de section
     */
    public static function end() : void
    {
        ob_end_clean();
    }
    public static function endSection() : void
    {
        self::end();
    }
    /**
     * Affiche le contenu de la section capturée
     * 
     * @param string $name Le nom du bloc a afficher
     * @return void
     */
    public static function show(string $name) : void
    {
        $name = strtolower($name);
        if(!empty(self::$_blocks[$name]) AND $name == 'css')
        {
            echo "<style type=\"text/css\">\n".self::$_blocks[$name]."</style>\n";
        }
        else if(!empty(self::$_blocks[$name]) AND $name == 'js')
        {
            echo "<script type=\"text/javascript\">\n".self::$_blocks[$name]."</script>\n";
        }
        else
        {
            echo self::$_blocks[$name] ?? null;
        }
    }
    public static function getSection(string $name) : void
    {
        self::show($name);
    }


    /**
     * @param null|string $config
     * @return void
     */
    public static function stylesBundle(?string $config = null) : void
    {
        $config = ($config === null) ? self::$_layout : $config;
        $lib_styles = array_merge((array) Config::get('layout.'.$config.'.lib_styles'), self::$_vars['df_lib_css'] ?? []);
        if(!empty($lib_styles))
        {
            lib_styles($lib_styles);
        }
        $styles = array_merge((array) Config::get('layout.'.$config.'.styles'), self::$_vars['df_css'] ?? []);
        if(!empty($styles))
        {
            styles($styles);
        }
        self::show('css');
    }
    /**
     * @param null|string $config
     * @return void
     */
    public static function scriptsBundle(?string $config = null) : void
    {
        $config = ($config === null) ? self::$_layout : $config;
        $lib_scripts = array_merge((array) Config::get('layout.'.$config.'.lib_scripts'), self::$_vars['df_lib_js'] ?? []);
        if(!empty($lib_scripts))
        {
            lib_scripts($lib_scripts);
        }
        $scripts = array_merge((array) Config::get('layout.'.$config.'.scripts'), self::$_vars['df_js'] ?? []);
        if(!empty($scripts))
        {
            scripts($scripts);
        }
        self::show('js');
    }
}